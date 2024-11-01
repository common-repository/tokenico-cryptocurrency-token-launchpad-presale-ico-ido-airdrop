<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite\Pages;

use BeycanPress\TokenicoLite\Models\Sale;
use BeycanPress\TokenicoLite\PluginHero\Page;
use BeycanPress\TokenicoLite\PluginHero\Table;
use BeycanPress\TokenicoLite\PluginHero\Helpers;

/**
 * Sales list page
 */
class SalesList extends Page
{
    /**
     * Class construct
     * @return void
     */
    public function __construct()
    {
        parent::__construct([
            'pageName' => esc_html__('TokenICO', 'tokenico'),
            'subMenuPageName' => esc_html__('Sale list', 'tokenico'),
            'slug' => 'tokenico_sale_list',
            'priority' => 1,
            'subMenu' => true,
        ]);
    }

    /**
     * @return void
     */
    public function page(): void
    {
        $sale = new Sale();

        if (isset($_GET['id']) && $sale->delete(['id' => absint($_GET['id'])])) {
            Helpers::notice(esc_html__('Successfully deleted!', 'tokenico'), 'success', true);
        }

        $table = (new Table($sale))->setColumns([
                'transactionHash'     => esc_html__('Transaction Hash', 'tokenico'),
                'presaleId'         => esc_html__('Presale id', 'tokenico'),
                'network'           => esc_html__('Network', 'tokenico'),
                'receiverAddress'   => esc_html__('Receiver address', 'tokenico'),
                'quantityPurchased' => esc_html__('Quantity purchased', 'tokenico'),
                'purchaseAmount'    => esc_html__('Purchase amount', 'tokenico'),
                'sent'              => esc_html__('Sent', 'tokenico'),
                'soldAt'            => esc_html__('Sold at', 'tokenico'),
                'delete'            => esc_html__('Delete', 'tokenico')
        ])
        ->setOrderQuery(['soldAt', 'desc'])
        ->setOptions([
            'search' => [
                'id' => 'search-box',
                'title' => esc_html__('Search...', 'tokenico')
            ]
        ])
        ->addHooks([
            'network' => function ($sale) {
                $network = json_decode($sale->network);
                return esc_html($network->name);
            },
            'transactionHash' => function ($sale) {
                // @phpcs:ignore
                return '<a href="' . esc_url($sale->transactionUrl) . '" target="_blank">' . esc_html($sale->transactionHash) . '</a>';
            },
            'receiverAddress' => function ($sale) {
                // @phpcs:ignore
                return '<a href="' . esc_url($sale->receiverAddressUrl) . '" target="_blank">' . esc_html($sale->receiverAddress) . '</a>';
            },
            'quantityPurchased' => function ($sale) {
                $token = json_decode($sale->token);
                return esc_html($sale->quantityPurchased . " " . $token->symbol);
            },
            'purchaseAmount' => function ($sale) {
                $network = json_decode($sale->network);
                return esc_html($sale->purchaseAmount . " " . $network->nativeCurrency->symbol);
            },
            'soldAt' => function ($sale) {
                return (new \DateTime($sale->soldAt))->setTimezone(
                    new \DateTimeZone(wp_timezone_string())
                )->format('d M Y H:i');
            },
            'delete' => function ($sale) {
                if (!$sale->sent) {
                    return '';
                }
                // @phpcs:ignore
                return '<a class="button" href="' . Helpers::getCurrentUrl() . '&id=' . $sale->id . '">' . esc_html__('Delete', 'tokenico') . '</a>';
            },
            'sent' => function ($sale) {
                if ($sale->sent) {
                    return esc_html__('Sent', 'tokenico');
                } else {
                    return esc_html__('No sent', 'tokenico');
                }
            }
        ])
        ->setSortableColumns([
            'soldAt'
        ])
        ->createDataList(function (Sale $model) {
            if (isset($_GET['s']) && !empty($_GET['s'])) {
                $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : null;
                return array_values($model->search($s));
            }
            return null;
        });

        Helpers::viewEcho('pages/sales-list', [
            'table' => $table
        ]);
    }
}
