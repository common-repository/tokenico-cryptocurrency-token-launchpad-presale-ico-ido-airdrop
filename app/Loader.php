<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite;

use BeycanPress\TokenicoLite\PluginHero\Helpers;

class Loader extends PluginHero\Plugin
{
    /**
     * Class construct
     * @param string $pluginFile
     * @return void
     */
    public function __construct(string $pluginFile)
    {
        $api = new RestAPI();

        parent::__construct([
            'pluginFile' => $pluginFile,
            'pluginKey' => 'tokenico',
            'textDomain' => 'tokenico',
            'settingKey' => 'tokenicoSettings',
            'api' => $api
        ]);

        Helpers::addFunc(
            'getTimer',
            /**
             * @param string $date
             * @param string $endOrStart
             * @param int $template
             * @return string
             */
            function (string $date, string $endOrStart = 'start', int $template = 1): string {
                $createTime = strtotime($date);
                $currentTime = time();
                $dtCreate = \DateTime::createFromFormat('U', (string) $createTime);
                $dtCurrent = \DateTime::createFromFormat('U', (string) $currentTime);
                $diff = 'start' == $endOrStart ? $dtCreate->diff($dtCurrent) : $dtCurrent->diff($dtCreate);
                return Helpers::view('countdown-' . $template, [
                    'diff' => $diff, 'endOrStart' => $endOrStart, 'date' => $date
                ]);
            }
        );

        Helpers::feedback(
            true,
            'tokenico-cryptocurrency-token-launchpad-presale-ico-ido-airdrop'
        );

        add_action('plugins_loaded', function (): void {
            // phpcs:disable
            if (defined('TOKENICO_PREMIUM')) {
                add_action('admin_notices', function (): void {
                    ?>
                        <div class="notice notice-error">
                            <p><?php echo __('TokenICO is not activated because TokenICO Premium is already activated. Please deactivate TokenICO Premium to activate TokenICO.', 'tokenico'); ?></p>
                        </div>
                    <?php
                });
            } else {
                if ('' == Settings::get('wcProjectId')) {
                    // @phpcs:ignore
                    Helpers::adminNotice(esc_html__('TokenICO: The purchase process will not work because you have not entered the WalletConnect Project ID. Please obtain a WalletConnect Project ID and add it to the relevant field in the settings.', 'tokenico'), 'error');
                }
                new PostType\Presale();
            }
            // phpcs:enable
        });
    }

    /**
     * @return void
     */
    public function adminProcess(): void
    {
        add_action('plugins_loaded', function (): void {
            if (!defined('TOKENICO_PREMIUM')) {
                new Pages\SalesList();

                Helpers::adminNotice(
                    sprintf("<b>If you want to make your pre-sales commission-free, %s here to buy TokenICO Premium.</b>", '<a href="https://beycanpress.com/product/tokenico/" target="_blank">click</a>'), // @phpcs:ignore
                    'info',
                    true
                );

                add_action('init', function (): void {
                    new Settings();
                }, 9);
            }
        });
    }

    /**
     * @return void
     */
    public function frontEndProcess(): void
    {
        add_action('plugins_loaded', function (): void {
            if (!defined('TOKENICO_PREMIUM')) {
                (new Services\PresaleList())->initSc();
            }
        });
    }

    /**
     * @return void
     */
    public static function activation(): void
    {
        (new Models\Sale())->createTable();
    }

    /**
     * @return void
     */
    public static function deactivation(): void
    {
        delete_option('tokenico_flush_rewrite');
    }

    /**
     * @return void
     */
    public static function uninstall(): void
    {
        $settings = get_option(Helpers::getProp('settingKey'));
        if (isset($settings['dds']) && $settings['dds']) {
            delete_option(Helpers::getProp('settingKey'));
            (new Models\Sale())->drop();
        }
    }
}
