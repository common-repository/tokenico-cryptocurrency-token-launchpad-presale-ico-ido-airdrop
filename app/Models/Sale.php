<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite\Models;

use BeycanPress\TokenicoLite\PluginHero\Model\AbstractModel;

/**
 * Sale table model
 */
class Sale extends AbstractModel
{
    /**
     * @var string
     */
    public string $tableName = 'tokenico_sale';

    /**
     * @var string
     */
    public string $version = '1.0.0';

    /**
     * Sale constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'transactionHash' => [
                'type' => 'string',
                'length' => 100,
                'index' => [
                    'type' => 'unique'
                ]
            ],
            'transactionUrl' => [
                'type' => 'text'
            ],
            'presaleId' => [
                'type' => 'integer',
            ],
            'network' => [
                'type' => 'text'
            ],
            'token' => [
                'type' => 'text'
            ],
            'receiverAddress' => [
                'type' => 'string',
                'length' => 70,
            ],
            'receiverAddressUrl' => [
                'type' => 'text'
            ],
            'quantityPurchased' => [
                'type' => 'float',
            ],
            'purchaseAmount' => [
                'type' => 'float',
            ],
            'sent' => [
                'type' => 'boolean'
            ],
            'soldAt' => [
                'type' => 'timestamp',
                'default' => 'current_timestamp',
            ],
        ]);

        if ($this->version !== get_option('tokenico_sale_version')) {
            update_option('tokenico_sale_version', $this->version);
            $this->query("ALTER TABLE `wp_tokenico_sale` CHANGE `transactionHash` `transactionHash` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;"); // phpcs:ignore
        }
    }

    /**
     * @param int $presaleId
     * @param string $receiverAddress
     * @return float
     */
    public function getPurchaseAmount(int $presaleId, string $receiverAddress): float
    {
        return (float) $this->getVar(
            $this->prepare(
                "SELECT SUM(purchaseAmount) FROM {$this->tableName} 
                WHERE receiverAddress = '%s' AND presaleId = %d",
                [$receiverAddress, $presaleId]
            )
        );
    }

    /**
     * @param int $presaleId
     * @param string $receiverAddress
     * @return float
     */
    public function getQuantityPurchased(int $presaleId, string $receiverAddress): float
    {
        return (float) $this->getVar(
            $this->prepare(
                "SELECT SUM(quantityPurchased) FROM {$this->tableName} 
                WHERE receiverAddress = '%s' AND presaleId = %d",
                [$receiverAddress, $presaleId]
            )
        );
    }
}
