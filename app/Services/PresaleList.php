<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite\Services;

use BeycanPress\TokenicoLite\Lang;
use BeycanPress\TokenicoLite\Settings;
use BeycanPress\TokenicoLite\Entity\Presale;
use BeycanPress\TokenicoLite\PluginHero\Hook;
use BeycanPress\TokenicoLite\PluginHero\Helpers;
use BeycanPress\TokenicoLite\Services\PresaleContract;

class PresaleList
{
    /**
     * @var array<int,object>
     */
    private array $presales;

    /**
     * @var array<string>
     */
    private array $deps = [];

    /**
     * @var bool
     */
    private bool $assetsLoaded = false;

    /**
     * @var bool
     */
    private bool $assetsCanLoad = false;

    /**
     * @return void
     */
    public function initSc(): void
    {
        add_action('init', function (): void {
            add_shortcode('tokenico-presale-list', function () {
                $this->assetsCanLoad = true;

                $presales = $this->getPresales();
                $networks = Settings::getNetworks();

                Helpers::addStyle('main.min.css');
                $key = Helpers::addScript('list.min.js', ['jquery']);

                wp_localize_script($key, 'Tokenico', [
                    'apiUrl' => Helpers::getAPI('RestAPI')->getUrl(),
                ]);

                return Helpers::view('presale/list', compact('networks', 'presales'));
            });

            add_shortcode('tokenico-presale', function ($atts) {
                $this->assetsCanLoad = true;

                extract(shortcode_atts([
                    'id' => null
                ], $atts));

                if (!$id) {
                    return esc_html__('Not found id parameter!', 'tokenico');
                }

                $presale = new Presale(absint($id));
                if ($presale->isAvailable()) {
                    // phpcs:ignore
                    if ('publish' != $presale->post_status || 'presale' != $presale->post_type) {
                        return esc_html__('Not found presale!', 'tokenico');
                    }
                } else {
                    return esc_html__('Not found presale!', 'tokenico');
                }

                $this->registerPresale($presale);
                $token = json_decode($presale->token);

                return Helpers::view('presale/content', compact('presale', 'token'));
            });
        });

        add_filter('the_content', function ($content) {
            global $post;

            // phpcs:ignore
            if ('presale' == $post->post_type) {
                $this->assetsCanLoad = true;

                if (isset($_GET['preview'])) {
                    $content .= esc_html__('Presales are not available for preview!', 'tokenico');
                } else {
                    $presale = new Presale(absint($post->ID));
                    $this->registerPresale($presale);
                    $token = json_decode($presale->token);
                    $content .= Helpers::view('presale/content', compact('presale', 'token'));
                }
            }

            return $content;
        });

        add_action('wp_footer', [$this, 'loadAssets']);
    }

    /**
     * @param object $presales
     * @return string
     */
    public function getItems(object $presales): string
    {
        return Helpers::view('presale/item', [
            'presales' => $presales
        ]);
    }

    /**
     * @param array<mixed> $filter
     * @param integer $page
     * @return object
     */
    public function getPresales(array $filter = [], int $page = 1): object
    {
        $args = [
            'post_type'      => 'presale',
            'post_status'    => 'publish',
            'order'          => 'DESC',
            'posts_per_page' => 9,
            'paged'          => $page,
            'meta_query'     => []
        ];

        if (isset($filter['status']) && 'all' != $filter['status']) {
            if ('started' == $filter['status']) {
                $args['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'key' => 'startDate',
                        'value' => date('Y-m-d H:i:s'),
                        'compare' => '<=',
                        'type' => 'datetime'
                    ],
                    [
                        'key' => 'endDate',
                        'value' => date('Y-m-d H:i:s'),
                        'compare' => '>=',
                        'type' => 'datetime'
                    ],
                    [
                        'key' => 'remainingLimit',
                        'value' => 0,
                        'compare' => '!='
                    ],
                ];
            } elseif ('not-started' == $filter['status']) {
                $args['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'key' => 'startDate',
                        'value' => date('Y-m-d H:i:s'),
                        'compare' => '>=',
                        'type' => 'datetime'
                    ],
                ];
            } elseif ('ended' == $filter['status']) {
                $args['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'key' => 'endDate',
                        'value' => date('Y-m-d H:i:s'),
                        'compare' => '<=',
                        'type' => 'datetime'
                    ]
                ];
            }
        }
        if (isset($filter['network']) && 'all' != $filter['network']) {
            $args['meta_query'] = array_merge([
                [
                    'key' => 'networkId',
                    'value' => $filter['network']
                ]
            ], $args['meta_query']);
        }

        return new \WP_Query($args);
    }

    /**
     * @param Presale $presale
     * @return void
     */
    public function registerPresale(Presale $presale): void
    {
        $network = json_decode($presale->network);

        $providers = Hook::callFilter('fe_js_providers', [
            'tron' => 'tron-provider.js',
            'evm' => 'evm-chains-provider.js',
            'solana' => 'solana-provider.js',
        ]);

        if (!in_array($providers[$network->code], $this->deps)) {
            $this->deps[] = Helpers::addScript($providers[$network->code]);
        }

        $contractAbi = PresaleContract::getAbi(
            $network->code,
            $presale->contractVersion
        );

        $contract = [
            'abi' => $contractAbi,
            'version' => $presale->contractVersion,
            'address' => $presale->contractAddress
        ];

        $token = json_decode($presale->token);
        $token->program = $presale->tokenProgram ?? null;

        $this->presales[$presale->ID] = [
            'token' => $token,
            'id' => $presale->ID,
            'key' => $presale->key,
            'network' => $network,
            'contract' => $contract,
            'args' => json_decode($presale->args ?? '{}'),
            'exchangeRate' => floatval($presale->exchangeRate),
            'minContribution' => floatval($presale->minContribution),
            'maxContribution' => floatval($presale->maxContribution),
            'instantTransfer' => $presale->instantTransfer ? true : false,
        ];
    }

    /**
     * @return void
     */
    public function loadAssets(): void
    {
        if ($this->assetsLoaded || !$this->assetsCanLoad) {
            return;
        }

        $this->assetsLoaded = true;

        Helpers::addStyle('main.min.css');
        Helpers::addScript('sweetalert2.js');

        $key = Helpers::addScript('main.min.js', array_merge(
            ['jquery'],
            $this->deps
        ));

        wp_localize_script($key, 'Tokenico', [
            'providers' => [],
            'lang' => Lang::get(),
            'presales' => $this->presales ?? [],
            'wcProjectId' => Settings::get('wcProjectId'),
            'apiUrl' => Helpers::getAPI('RestAPI')->getUrl(),
            'walletImages' => [
                'phantom' => Helpers::getImageUrl('phantom.png'),
                'slope' => Helpers::getImageUrl('slope.png'),
                'solflare' => Helpers::getImageUrl('solflare.webp'),
                'torus' => Helpers::getImageUrl('torus.png'),
                'walletconnect' => Helpers::getImageUrl('walletconnect.png'),
                'coinbasewallet' => Helpers::getImageUrl('coinbasewallet.png'),
                'bitget' => Helpers::getImageUrl('bitget.jpeg'),
                'okx' => Helpers::getImageUrl('okx.png'),
                'tokenpocket' => Helpers::getImageUrl('tokenpocket.png'),
                'tronlink' => Helpers::getImageUrl('tronlink.webp'),
            ]
        ]);

        Helpers::viewEcho('wallet-modal');
    }
}
