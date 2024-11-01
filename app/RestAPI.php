<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite;

use WP_REST_Request;
use BeycanPress\TokenicoLite\Models\Sale;
use BeycanPress\TokenicoLite\Entity\Presale;
use BeycanPress\TokenicoLite\PluginHero\Helpers;
use BeycanPress\TokenicoLite\PluginHero\Http\Response;
use BeycanPress\TokenicoLite\Services\PresaleContract;

class RestAPI extends PluginHero\BaseAPI
{
    /**
     * Class construct
     * @return void
     */
    public function __construct()
    {
        $this->addRoutes([
            'tokenico-api' => [
                'get-presales' => [
                    'callback' => 'getPresales',
                    'methods' => ['GET']
                ],
                'filter-presales' => [
                    'callback' => 'filterPresales',
                    'methods' => ['GET']
                ],
                'claim-successful' => [
                    'callback' => 'claimSuccessful',
                    'methods' => ['POST']
                ],
                'save-sale-transaction' => [
                    'callback' => 'saveSaleTransaction',
                    'methods' => ['POST']
                ],
                'save-deployed-contract' => [
                    'callback' => 'saveDeployedContract',
                    'methods' => ['POST']
                ],
                'get-dates' => [
                    'callback' => 'getDates',
                    'methods' => ['GET']
                ],
                'update-description' => [
                    'callback' => 'updateDescription',
                    'methods' => ['POST']
                ],
            ]
        ]);
    }

    /**
     * @param WP_REST_Request $request
     * @return void
     */
    public function getPresales(WP_REST_Request $request): void
    {
        $page = absint($request->get_param('page'));
        $filter = array_map('sanitize_text_field', $request->get_param('filter'));

        $presaleList = new Services\PresaleList();
        $presales = $presaleList->getPresales($filter, $page);

        Response::success(null, $presaleList->getItems($presales));
    }

    /**
     * @param WP_REST_Request $request
     * @return void
     */
    public function filterPresales(WP_REST_Request $request): void
    {
        $filter = array_map('sanitize_text_field', $request->get_param('filter'));

        $presaleList = new Services\PresaleList();
        $presales = $presaleList->getPresales($filter);

        Response::success(null, [
            // phpcs:ignore
            'maxPage' => $presales->max_num_pages,
            'content' => $presaleList->getItems($presales),
        ]);
    }
/**
     * @param WP_REST_Request $request
     * @return void
     */
    public function saveDeployedContract(WP_REST_Request $request): void
    {
        $version = $request->get_param('version');
        $address = $request->get_param('address');
        $networkId = $request->get_param('networkId');

        PresaleContract::saveDeployedContract($networkId, $address, $version);

        Response::success();
    }

    /**
     * @param WP_REST_Request $request
     * @return void
     */
    public function saveSaleTransaction(WP_REST_Request $request): void
    {
        $presale = new Presale(absint($request->get_param('presaleId')));
        $purchaseAmount = floatval($request->get_param('purchaseAmount'));

        $presale->setMeta('totalSales', $presale->totalSales + $purchaseAmount);
        $presale->setMeta('remainingLimit', $presale->totalSaleLimit - $presale->totalSales);

        (new Sale())->insert([
            'presaleId' => $request->get_param('presaleId'),
            'token' => json_encode($request->get_param('token')),
            'network' => json_encode($request->get_param('network')),
            'purchaseAmount' => $request->get_param('purchaseAmount'),
            'quantityPurchased' => $request->get_param('quantityPurchased'),
            'transactionUrl' => $request->get_param('transactionUrl'),
            'transactionHash' => $request->get_param('transactionHash'),
            'receiverAddress' => $request->get_param('receiverAddress'),
            'receiverAddressUrl' => $request->get_param('receiverAddressUrl'),
            'sent' => 'true' == $request->get_param('sent') ? true : false,
            'soldAt' => Helpers::getUTCTime()->format('Y-m-d H:i:s')
        ]);

        $token = (object) $request->get_param('token');

        Response::success(Helpers::view('presale/real-content', compact('presale', 'token')));
    }

    /**
     * @param WP_REST_Request $request
     * @return void
     */
    public function claimSuccessful(WP_REST_Request $request): void
    {
        $presaleId = $request->get_param('presaleId');
        $receiverAddress = $request->get_param('receiverAddress');

        try {
            (new Sale())->update([
                'sent' => true
            ], [
                'receiverAddress' => $receiverAddress,
                'presaleId' => $presaleId,
                'sent' => false
            ]);
        } catch (\Throwable $th) {
        }

        Response::success();
    }

    /**
     * @param WP_REST_Request $request
     * @return void
     */
    public function getDates(WP_REST_Request $request): void
    {
        $networkId = $request->get_param('networkId');
        $endDate = strtotime($request->get_param('endDate'));
        $startDate = strtotime($request->get_param('startDate'));
        $utcTime = strtotime(wp_date("Y-m-d H:i:s", null, new \DateTimeZone('UTC')));

        $args = [
            'post_type'      => 'presale',
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key' => 'networkId',
                    'value' => $networkId,
                    'compare' => '='
                ],
            ]
        ];

        $presales = get_posts($args);

        if (!empty($presales)) {
            foreach ($presales as $presale) {
                $presale = new Presale($presale->ID);

                if ('started' == $presale->getStatus() || 'notStarted' == $presale->getStatus()) {
                    Response::error(
                        // phpcs:ignore
                        "This network has a presale that has not yet finished. Therefore, you cannot publish a new presale. You can wait for finish this presale or you can delete it!"
                    );
                }
            }
        }

        Response::success(null, compact('startDate', 'endDate'));
    }

    /**
     * @param WP_REST_Request $request
     * @return void
     */
    public function updateDescription(WP_REST_Request $request): void
    {
        $postId = $request->get_param('postId');
        $content = $request->get_param('content');
        $title = $request->get_param('title');

        wp_update_post([
            'ID' => $postId,
            'post_title' => $title,
            'post_content' => $content
        ]);

        Response::success();
    }
}
