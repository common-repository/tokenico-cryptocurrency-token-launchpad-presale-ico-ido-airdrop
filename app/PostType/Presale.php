<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite\PostType;

use BeycanPress\TokenicoLite\Lang;
use BeycanPress\TokenicoLite\Settings;
use BeycanPress\TokenicoLite\PluginHero\Hook;
use BeycanPress\TokenicoLite\PluginHero\Helpers;
use BeycanPress\TokenicoLite\Services\PresaleContract;
use BeycanPress\TokenicoLite\Entity\Presale as PresaleEntity;

class Presale
{
    /**
     * @var PresaleEntity|null
     */
    private ?PresaleEntity $presale = null;

    /**
     * Class construct
     * @return void
     */
    public function __construct()
    {
        global $pagenow;

        add_action('init', [$this, 'init']);

        if (is_admin()) {
            add_action('init', function (): void {
                new Metabox();
            }, 9);

            $postId = isset($_GET['post']) ? absint($_GET['post']) : null;
            if (!is_array($postId)) {
                $this->presale = new PresaleEntity($postId);
                if (isset($_GET['post_type']) && 'presale' != $_GET['post_type']) {
                    return;
                } elseif (!isset($_GET['post_type']) && 'presale' != $this->presale->post_type) {
                    return;
                }

                if ('post.php' == $pagenow || 'post-new.php' == $pagenow) {
                    $this->loadAssets();
                }
            }
        }
    }

    /**
     * @return void
     */
    public function init(): void
    {
        register_post_type(
            'presale',
            [
                'labels' => [
                    'name'               => esc_html__('Presales', 'tokenico'),
                    'singular_name'      => esc_html__('Presale', 'tokenico'),
                    'add_new'            => esc_html__('Add new', 'tokenico'),
                    'add_new_item'       => esc_html__('Add new presale', 'tokenico'),
                    'edit_item'          => esc_html__('Edit presale', 'tokenico'),
                    'search_items'       => esc_html__('Search presale', 'tokenico'),
                    'not_found'          => esc_html__('No presale found', 'tokenico'),
                    'not_found_in_trash' => esc_html__('No presale found in Trash', 'tokenico'),
                ],
                'public'              => true,
                'publicly_queryable'  => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'query_var'           => true,
                'exclude_from_search' => false,
                'capability_type'     => 'post',
                'rewrite'             => ['slug' => 'presale'],
                'supports'            => ['title', 'editor']
            ]
        );

        if (2 != get_option('tokenico_flush_rewrite')) {
            flush_rewrite_rules(false);
            update_option('tokenico_flush_rewrite', 2);
        }

        add_filter('manage_presale_posts_columns', [$this, 'columns']);
        add_action('manage_presale_posts_custom_column', [$this, 'column'], 10, 2);
    }

    /**
     * @return void
     */
    private function loadAssets(): void
    {
        add_action('admin_enqueue_scripts', function (): void {
            $mcSol = Helpers::addScript('mc-solana.js');
            $deps = array_values(Hook::callFilter('be_js_providers', [
                'evm' => Helpers::addScript('evm-chains-provider.js'),
                'solana' => Helpers::addScript('solana-provider.js', [$mcSol]),
            ]));

            $deps[] = 'jquery';

            Helpers::addScript('sweetalert2.js');
            Helpers::addStyle('admin.css');
            Helpers::addScript('ethers.js');
            $key = Helpers::addScript('admin.min.js', $deps);
            wp_localize_script($key, 'Tokenico', [
                'providers' => [],
                'lang' => Lang::get(),
                'key' => md5(home_url() . time()),
                'networks' => Settings::getNetworks(),
                'contracts' => PresaleContract::getContracts(),
                'presaleStatus' => $this->presale->post_status,
                'apiUrl' => Helpers::getAPI('RestAPI')->getUrl(),
                'deployedContracts' => PresaleContract::getDeployedContracts(),
            ]);
        });
    }

    /**
     * @param array<string,string> $columns
     * @return array<string,string>
     */
    public function columns(array $columns): array
    {
        unset($columns['date']);
        $columns['shortcode'] = esc_html__('Shortcode', 'tokenico');
        $columns['token'] = esc_html__('Token', 'tokenico');
        $columns['network'] = esc_html__('Network', 'tokenico');
        $columns['totalSales'] = esc_html__('Total sales', 'tokenico');
        $columns['remainingLimit'] = esc_html__('Remaining limit', 'tokenico');
        $columns['status'] = esc_html__('Status', 'tokenico');

        $columns['date'] = esc_html__('Date');

        return $columns;
    }

    /**
     * @param string $column
     * @param mixed $presaleId
     * @return void
     */
    public function column(string $column, mixed $presaleId): void
    {
        $presale = new PresaleEntity(absint($presaleId));

        if (!$presale->network) {
            echo '';
        } else {
            $network = json_decode($presale->network);
            $token = json_decode($presale->token);
            if ('shortcode' == $column) {
                echo '[tokenico-presale id="' . esc_html($presale->ID) . '"]';
            } elseif ('token' == $column) {
                echo esc_html($token?->name);
            } elseif ('network' == $column) {
                echo esc_html($network->name);
            } elseif ('totalSales' == $column) {
                echo esc_html($presale->getTotalSales());
            } elseif ('remainingLimit' == $column) {
                echo esc_html($presale->getRemainingLimit());
            } elseif ('status' == $column) {
                $status = $presale->getStatus();
                if ('started' == $status) {
                    echo esc_html__('Presale started', 'tokenico');
                } elseif ('ended' == $status) {
                    echo esc_html__('Presale ended', 'tokenico');
                } else {
                    echo esc_html__('Presale not started', 'tokenico');
                }
            }
        }
    }
}
