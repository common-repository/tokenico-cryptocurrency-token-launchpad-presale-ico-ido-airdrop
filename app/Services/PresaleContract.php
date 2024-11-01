<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite\Services;

use BeycanPress\TokenicoLite\PluginHero\Hook;
use BeycanPress\TokenicoLite\PluginHero\Helpers;

class PresaleContract
{
    /**
     *
     * @var object|null
     */
    private static ?object $contracts = null;

    /**
     * @param string $contract
     * @return object
     */
    public static function get(string $contract): object
    {
        self::getContracts();

        $version = self::$contracts->$contract->version ?? 'v1';

        return (object) [
            'version' => $version,
            'abi' => self::$contracts->$contract->$version,
            'byteCode' => self::$contracts->$contract->byteCode ?? null,
        ];
    }

    /**
     * @return object
     */
    public static function getContracts(): object
    {
        if (is_null(self::$contracts)) {
            self::$contracts = json_decode(
                file_get_contents(Helpers::getProp('pluginDir') . 'resources/presale-contracts.json')
            );
        }

        return (object) self::$contracts;
    }

    /**
     * @param string $networkId
     * @param string|null $version
     * @return array<string,mixed>|object
     */
    public static function getAbi(string $networkId, string $version = null): array|object
    {
        if (0 === strpos($networkId, '0x')) {
            $contract = self::get('evm');
        } else {
            $contract = self::get($networkId);
        }

        if (is_null($version)) {
            $version = $contract->version;
        }

        return $contract->abi;
    }

    /**
     * @return object
     */
    public static function getDeployedContracts(): object
    {
        $defaultContracts = [
            // ethereum mainnet
            '0x1' => [
                'v1' => '0x43930137a5a10acbef6c79c1f19ed5a15db513c5'
            ],
            // bnb smart chain mainnet
            '0x38' => [
                'v1' => '0x43930137a5a10aCbEf6C79c1f19ED5a15Db513C5'
            ],
            // avalanche mainnet
            '0xa86a' => [
                'v1' => '0x43930137a5a10aCbEf6C79c1f19ED5a15Db513C5'
            ],
            // polygon mainnet
            '0x89' => [
                'v1' => '0x43930137a5a10acbef6c79c1f19ed5a15db513c5'
            ],
            // ethereum sepolia testnet
            '0xaa36a7' => [
                'v1' => '0xFed275dCC6988989123204d27D26aD96A5AB0596'
            ],
            // bnb smart chain testnet
            '0x61' => [
                'v1' => '0xd7906f776d7d50b0e3e585135039d524386cb4db'
            ],
            // avalanche fuji testnet
            '0xa869' => [
                'v1' => '0xD3a23741F199703C8025ad2D6f5327EFAe6876Bd'
            ],
            // polygon mumbai testnet
            '0x13881' => [
                'v1' => '0xfed275dcc6988989123204d27d26ad96a5ab0596'
            ],
            'solana' => [
                'v1' => 'HeXZiyduAmAaYABvrh4bU94TdzB2TkwFuNXfgi1PYFwS'
            ],
            'solana-devnet' => [
                'v1' => 'HeXZiyduAmAaYABvrh4bU94TdzB2TkwFuNXfgi1PYFwS'
            ],
            'tron-testnet' => [
                'v1' => 'THza3uGmYZphN6f248qkiQg2SHcoRZd9QB'
            ],
        ];

        $deployedContracts = (array) get_option('tokenico_deployed_contracts', []);

        $contracts = (object) array_merge($deployedContracts, $defaultContracts);

        return (object) Hook::callFilter('deployed_contracts', $contracts);
    }

    /**
     * @param string $networkId
     * @param string $address
     * @param string $version
     * @return void
     */
    public static function saveDeployedContract(string $networkId, string $address, string $version): void
    {
        $deployedContracts = self::getDeployedContracts();

        if (!isset($deployedContracts->$networkId)) {
            $deployedContracts->$networkId = new \stdClass();
        }

        if (is_array($deployedContracts->$networkId)) {
            $deployedContracts->$networkId[$version] = $address;
        } else {
            $deployedContracts->$networkId->$version = $address;
        }

        update_option('tokenico_deployed_contracts', $deployedContracts);
    }
}
