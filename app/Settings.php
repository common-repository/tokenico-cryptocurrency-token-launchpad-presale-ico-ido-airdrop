<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite;

// @phpcs:disable Generic.Files.LineLength

use BeycanPress\TokenicoLite\PluginHero\Hook;
use BeycanPress\TokenicoLite\PluginHero\Setting;
use BeycanPress\TokenicoLite\PluginHero\Helpers;

class Settings extends Setting
{
    /**
     * @var array<string,mixed>
     */
    private static array $networks = [];

    /**
     * Settings constructor.
     */
    public function __construct()
    {
        $parent = Helpers::getPage('SalesList')->getSlug();

        parent::__construct(esc_html__('Settings', 'tokenico'), $parent);

        self::createSection([
            'id'     => 'general_options',
            'title'  => esc_html__('General options', 'tokenico'),
            'icon'   => 'fa fa-cog',
            'fields' => [
                [
                    'id'      => 'dds',
                    'title'   => esc_html__('Data deletion status', 'tokenico'),
                    'type'    => 'switcher',
                    'default' => false,
                    'help'    => esc_html__('This setting is passive come by default. You enable this setting. All data created by the plug-in will be deleted while removing the plug-in.', 'tokenico')
                ],
                [
                    'id'    => 'wcProjectId',
                    'type'  => 'text',
                    'title' => esc_html__('WalletConnect Project ID', 'tokenico'),
                    'help'  => esc_html__('Please enter a Project ID for WalletConnect to work.', 'tokenico'),
                    'desc'  => '<a href="https://cloud.walletconnect.com/" target="_blank">Get WalletConnect Project ID</a>',
                    'sanitize' => function ($val) {
                        return sanitize_text_field($val);
                    }
                ],
                [
                    'id'   => 'progressShowingStyle',
                    'type' => 'select',
                    'title' => esc_html__('Progress showing style', 'tokenico'),
                    'desc' => esc_html__('Is the remaining limit above the progress bar token-based? Or is it native coin-based? You can choose what you want to show.', 'tokenico'),
                    'options' => [
                        'token-based' => esc_html__('Token based', 'tokenico'),
                        'coin-based' => esc_html__('Coin based', 'tokenico'),
                    ]
                ]
            ]
        ]);

        self::createSection([
            'id'     => 'evmBasedNetworks',
            'title'  => esc_html__('EVM Based networks', 'tokenico'),
            'icon'   => 'fab fa-ethereum',
            'fields' => [
                [
                    'id'      => 'evmBasedNetworks',
                    'title'   => esc_html__('Networks', 'tokenico'),
                    'type'    => 'group',
                    'help'    => esc_html__('Add networks you want to sell.', 'tokenico'),
                    'button_title' => esc_html__('Add new', 'tokenico'),
                    'default' => [
                        [
                            'name' =>  'Ethereum Mainnet',
                            'rpcUrl' =>  'https://ethereum-rpc.publicnode.com',
                            'id' =>  1,
                            'explorerUrl' =>  'https://etherscan.io/',
                            'active' => true,
                            'nativeCurrency' => [
                                'symbol' =>  'ETH',
                                'decimals' =>  18,
                            ],
                        ],
                        [
                            'name' =>  'BNB Chain Mainnet',
                            'rpcUrl' =>  'https://bsc-rpc.publicnode.com',
                            'id' =>  56,
                            'explorerUrl' =>  'https://bscscan.com/',
                            'active' => true,
                            'nativeCurrency' => [
                                'symbol' =>  'BNB',
                                'decimals' =>  18,
                            ],
                        ],
                        [
                            'name' =>  'Avalanche Network',
                            'rpcUrl' =>  'https://api.avax.network/ext/bc/C/rpc',
                            'id' =>  43114,
                            'explorerUrl' =>  'https://cchain.explorer.avax.network/',
                            'active' => true,
                            'nativeCurrency' => [
                                'symbol' =>  'AVAX',
                                'decimals' =>  18,
                            ],
                        ],
                        [
                            'name' =>  'Polygon Mainnet',
                            'rpcUrl' =>  'https://polygon-rpc.com/',
                            'id' =>  137,
                            'explorerUrl' =>  'https://polygonscan.com/',
                            'active' => true,
                            'nativeCurrency' => [
                                'symbol' =>  'MATIC',
                                'decimals' =>  18,
                            ]
                        ],
                        [
                            "name" => "Ethereum Sepolia Testnet",
                            "rpcUrl" => "https://ethereum-sepolia-rpc.publicnode.com",
                            'id' =>  11155111,
                            "explorerUrl" => "https://sepolia.etherscan.io/",
                            'active' => false,
                            'nativeCurrency' => [
                                'symbol' =>  'ETH',
                                'decimals' =>  18,
                            ]
                        ],
                        [
                            'name' =>  'BNB Chain Testnet',
                            "rpcUrl" => "https://bsc-testnet.publicnode.com",
                            'id' =>  97,
                            'explorerUrl' =>  'https://testnet.bscscan.com/',
                            'active' => false,
                            'nativeCurrency' => [
                                'symbol' =>  'BNB',
                                'decimals' =>  18,
                            ]
                        ],
                        [
                            'name' =>  'Avalanche FUJI C-Chain Testnet',
                            'rpcUrl' =>  'https://api.avax-test.network/ext/bc/C/rpc',
                            'id' =>  43113,
                            'explorerUrl' =>  'https://cchain.explorer.avax-test.network',
                            'active' => false,
                            'nativeCurrency' => [
                                'symbol' =>  'AVAX',
                                'decimals' =>  18,
                            ]
                        ],
                        [
                            'name' =>  'Polygon Mumbai Testnet',
                            'rpcUrl' => 'https://rpc-mumbai.maticvigil.com/',
                            'id' =>  80001,
                            'explorerUrl' =>  'https://mumbai.polygonscan.com',
                            'active' => false,
                            'nativeCurrency' => [
                                'symbol' =>  'MATIC',
                                'decimals' =>  18,
                            ]
                        ]
                    ],
                    'sanitize' => function ($val) {
                        if (is_array($val)) {
                            foreach ($val as $key => &$value) {
                                $value['id'] = absint($value['id']);
                                $value['name'] = sanitize_text_field($value['name']);
                                $value['rpcUrl'] = sanitize_text_field($value['rpcUrl']);
                                $value['explorerUrl'] = sanitize_text_field($value['explorerUrl']);
                                $value['nativeCurrency']['symbol'] = strtoupper(sanitize_text_field($value['nativeCurrency']['symbol']));
                                $value['nativeCurrency']['decimals'] = absint($value['nativeCurrency']['decimals']);
                            }
                        }

                        return $val;
                    },
                    'validate' => function ($val) {
                        if (is_array($val)) {
                            foreach ($val as $key => $value) {
                                if (empty($value['name'])) {
                                    return esc_html__('Network name cannot be empty.', 'tokenico');
                                } elseif (empty($value['rpcUrl'])) {
                                    return esc_html__('Network RPC URL cannot be empty.', 'tokenico');
                                } elseif (empty($value['id'])) {
                                    return esc_html__('Chain ID cannot be empty.', 'tokenico');
                                } elseif (empty($value['explorerUrl'])) {
                                    return esc_html__('Explorer URL cannot be empty.', 'tokenico');
                                } elseif (empty($value['nativeCurrency']['symbol'])) {
                                    return esc_html__('Native currency symbol cannot be empty.', 'tokenico');
                                } elseif (empty($value['nativeCurrency']['decimals'])) {
                                    return esc_html__('Native currency Decimals cannot be empty.', 'tokenico');
                                }
                            }
                        } else {
                            return esc_html__('You must add at least one blockchain network!', 'tokenico');
                        }
                    },
                    'fields'    => [
                        [
                            'title' => esc_html__('Network name', 'tokenico'),
                            'id'    => 'name',
                            'type'  => 'text'
                        ],
                        [
                            'title' => esc_html__('Network RPC URL', 'tokenico'),
                            'id'    => 'rpcUrl',
                            'type'  => 'text',
                            'help'    => esc_html__('Because the default RPC addresses of blockchain networks are public and used by everyone. Sometimes they can restrict you so some times you need a special RPC address.', 'tokenico'),
                            'desc'    => esc_html__('The current RPC address of the network or your custom RPC address.', 'tokenico'),
                        ],
                        [
                            'title' => esc_html__('Chain ID', 'tokenico'),
                            'id'    => 'id',
                            'type'  => 'number'
                        ],
                        [
                            'title' => esc_html__('Explorer URL', 'tokenico'),
                            'id'    => 'explorerUrl',
                            'type'  => 'text'
                        ],
                        [
                            'id'      => 'active',
                            'title'   => esc_html__('Active/Passive', 'tokenico'),
                            'type'    => 'switcher',
                            'help'    => esc_html__('Use this network for presales?', 'tokenico'),
                            'default' => true,
                        ],
                        [
                            'id'     => 'nativeCurrency',
                            'type'   => 'fieldset',
                            'title'  => esc_html__('Native currency', 'tokenico'),
                            'fields' => [
                                [
                                    'id'    => 'symbol',
                                    'type'  => 'text',
                                    'title' => esc_html__('Symbol', 'tokenico')
                                ],
                                [
                                    'id'    => 'decimals',
                                    'type'  => 'number',
                                    'title' => esc_html__('Decimals', 'tokenico')
                                ],
                            ],
                        ]
                    ],
                ],
            ]
        ]);

        self::createSection([
            'id'     => 'solanaSettings',
            'title'  => esc_html__('Solana', 'tokenico'),
            'icon'   => 'fas fa-project-diagram',
            'fields' => [
                [
                    'id'      => 'solanaMainnetActive',
                    'title'   => esc_html__('Mainnet Active/Passive', 'tokenico'),
                    'type'    => 'switcher',
                    'help'    => esc_html__('Use this network for presales?', 'tokenico'),
                    'default' => true,
                ],
                [
                    'id'      => 'solanaDevnetActive',
                    'title'   => esc_html__('Devnet Active/Passive', 'tokenico'),
                    'type'    => 'switcher',
                    'help'    => esc_html__('Use this network for presales?', 'tokenico'),
                    'default' => true,
                ],
                [
                    'id'     => 'solanaMainnetInfo',
                    'title'  => esc_html__('Mainnet info', 'tokenico'),
                    'type'   => 'content',
                    'content' => esc_html__('The Solana mainnet RPC server can usually block you after a few uses. This is because the RPC server is public. That\'s why you need to get service from special RPC service providers. You can obtain a Websocket address from the relevant service providers to activate QR payments on the Solana network.', 'tokenico') . '<br><br>' . esc_html__('You can use it as an alternative to the Official Solana RPC address below or take a look at other providers.', 'tokenico') .
                    '<br><br>
                    > https://solana-mainnet.rpc.extrnode.com <br>
                    > https://try-rpc.mainnet.solana.blockdaemon.tech <br><br>
                    Other alternatives: <br><br>
                    <a href="https://www.quicknode.com/" target="_blank">https://www.quicknode.com/</a> (recommended) <br>
                    <a href="https://www.alchemy.com/overviews/solana-rpc" target="_blank">https://www.alchemy.com/overviews/solana-rpc</a> <br>
                    <a href="https://rpc.ankr.com/solana" target="_blank">https://rpc.ankr.com/solana</a> <br>
                    <a href="https://getblock.io/nodes/sol/" target="_blank">https://getblock.io/nodes/sol/</a> <br>
                    ',
                ],
                [
                    'title' => esc_html__('Mainnet RPC URL', 'tokenico'),
                    'id'    => 'solanaMainnetRpcUrl',
                    'type'  => 'text',
                    'help'    => esc_html__('Because the default RPC addresses of blockchain networks are public and used by everyone. Sometimes they can restrict you so some times you need a special RPC address.', 'tokenico'),
                    'desc'    => sprintf(esc_html__('Click for more help: %s', 'tokenico'), '<a href="https://beycanpress.gitbook.io/tokenico-docs/overview/node-providers" target="_blank">Solana RPC Endpoints</a>'),
                ],
            ]
        ]);

        self::createSection([
            'id'     => 'tronSettings',
            'title'  => esc_html__('Tron', 'tokenico'),
            'icon'   => 'fas fa-project-diagram',
            'fields' => [
                [
                    'id'      => 'tronMainnetActive',
                    'title'   => esc_html__('Mainnet Active/Passive', 'tokenico'),
                    'type'    => 'switcher',
                    'help'    => esc_html__('Use this network for presales?', 'tokenico'),
                    'default' => true,
                ],
                [
                    'id'      => 'tronTestnetActive',
                    'title'   => esc_html__('Tron Nile Testnet Active/Passive', 'tokenico'),
                    'type'    => 'switcher',
                    'help'    => esc_html__('Use this network for presales?', 'tokenico'),
                    'default' => true,
                ],
            ]
        ]);

        Hook::callAction('settings');

        self::createSection([
            'id'     => 'backup',
            'title'  => esc_html__('Backup', 'tokenico'),
            'icon'   => 'fa fa-shield',
            'fields' => [
                [
                    'type'  => 'backup',
                    'title' => esc_html__('Backup', 'tokenico')
                ]
            ]
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    public static function getNetworks(): array
    {
        $networks = self::get('evmBasedNetworks');

        if (!empty(self::$networks) || !$networks) {
            return self::$networks;
        }

        foreach ($networks as $network) {
            // Active/Passive control
            if (isset($network['active']) && '1' != $network['active']) {
                continue;
            }

            $id = intval($network['id']);
            $hexId = '0x' . dechex($id);

            self::$networks[$hexId] = [
                'id' => $id,
                'hexId' => $hexId,
                'code' => 'evm',
                'name' => $network['name'],
                'rpcUrl' => $network['rpcUrl'],
                'explorerUrl' => $network['explorerUrl'],
                'nativeCurrency' => $network['nativeCurrency'],
            ];
        }

        $solana = [
            'code' => 'solana',
            'explorerUrl' => 'https://solscan.io/',
            'nativeCurrency' => [
                'symbol' => 'SOL',
                'decimals' => 9,
            ]
        ];

        $tron = [
            'code' => 'tron',
            'nativeCurrency' => [
                'symbol' => 'TRX',
                'decimals' => 6,
            ]
        ];

        if (boolval(self::get('solanaMainnetActive'))) {
            self::$networks['solana'] = array_merge([
                'testnet' => false,
                'name' => 'Solana Mainnet',
                'rpcUrl' => self::get('solanaMainnetRpcUrl')
            ], $solana);
        }

        if (boolval(self::get('solanaDevnetActive'))) {
            self::$networks['solana-devnet'] = array_merge([
                'testnet' => true,
                'name' => 'Solana Devnet',
                'rpcUrl' => 'https://api.devnet.solana.com'
            ], $solana);
        }

        if (boolval(self::get('tronMainnetActive'))) {
            self::$networks['tron'] = array_merge([
                'testnet' => false,
                'name' => 'Tron Mainnet',
                'explorerUrl' => 'https://tronscan.org/#/'
            ], $tron);
        }

        if (boolval(self::get('tronTestnetActive'))) {
            self::$networks['tron-testnet'] = array_merge([
                'testnet' => true,
                'name' => 'Tron Nile Testnet',
                'explorerUrl' => 'https://nile.tronscan.org/#/'
            ], $tron);
        }

        return Hook::callFilter('networks', self::$networks);
    }

    /**
     * @return array<string,string>
     */
    public static function getNetworksForList(): array
    {
        $networks = self::getNetworks();

        if (!$networks) {
            return [];
        }

        return self::arrayMapAssoc(function ($key, $val) {
            return [$key, $val['name']];
        }, $networks);
    }

    /**
     * @param \Closure $f
     * @param array<string,mixed> $a
     * @return array<string,mixed>
     */
    private static function arrayMapAssoc(\Closure $f, array $a): array
    {
        return array_column(array_map($f, array_keys($a), $a), 1, 0);
    }
}
