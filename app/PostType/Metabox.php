<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite\PostType;

// @phpcs:disable Generic.Files.LineLength

use CSF;
use BeycanPress\TokenicoLite\Settings;
use BeycanPress\TokenicoLite\Entity\Presale;

class Metabox
{
    /**
     * Class construct
     * @return void
     */
    public function __construct()
    {
        $postId = isset($_GET['post']) ? absint($_GET['post']) : null;
        if (!is_array($postId)) {
            $presale = new Presale($postId);

            $presaleData = 'tokenicoPresaleData';
            CSF::createMetabox($presaleData, [
                'title'     => esc_html__('Presale options', 'tokenico'),
                'post_type' => 'presale',
                'data_type' => 'unserialize',
            ]);

            $notes = [
                "The balance of token you want to sell must be in the wallet where you publish the presale, equal to the total amount you will sell. Example You will sell  worth 10 COINs. You will give 10 TOKENs for 1 COIN. That is, you must have a minimum of 100 TOKENs in your wallet.",

                "Also, please check that your server time is correct according to the time zone. If not true. Although the pre-sale starts on your site, it will not start on the blockchain network. This produces an unexpected error message!",

                '<b>"There is not enough balance in your wallet for the token you want to sell!"</b>',

                "If you are getting an error like above. This means you do not have a balance equal to the total amount of tokens you want to sell. This also calculates the remaining amount of unfinished presales you have previously created.",

                '<b>"Insufficient liquidity by the seller."</b>',

                "If you receive an error as above, this indicates that you do not have a balance in your wallet to be transferred to the customer. Please keep as many tokens in your wallet as you will sell in the pre-sale.",

                "<b>Additionally, the funds will be automatically transferred to the wallet where you published the presale.</b>",

                "<b>IMPORTANT NOTE:</b><br> TokenICO works by mapping the data on your WordPress site to the data in the Contract pre-sale contract during the pre-sale. If you are using a cache mechanism on the page where the presale is located, the plugin may malfunction due to data synchronization.",

                "<b>IMPORTANT NODE:</b><br> Also, please, if you plan to do more than one presale, do them in sequence. Because for each pre-sale you give a spending limit according to the relevant pre-sale",

                "If you have some problem with publish presale, you can follow <a href='https://beycanpress.gitbook.io/tokenico-docs/' target='_blank'>documentation</a>",

                "Presale creation commissions: <br>
                <b>EVM networks</b>: 0.1 Native coin (ETH, BNB, AVAX, MATIC) <br>
                <b>Solana</b>: 0.5 SOL <br>
                <b>Tron</b>: 500 TRX <br>"
            ];

            CSF::createSection($presaleData, [
                'fields' => [
                    [
                        'id'    => 'importantNote',
                        'type'  => 'content',
                        'title' => esc_html__('Important note :', 'tokenico'),
                        'class' => 'important-note-content',
                        'content' => implode('<br><br>', $notes)
                    ],
                    [
                        'id'    => 'totalSales',
                        'type'  => 'number',
                        'title' => esc_html__('Total sales :', 'tokenico'),
                        'default' => 0
                    ],
                    [
                        'id'    => 'remainingLimit',
                        'type'  => 'number',
                        'title' => esc_html__('Remaining limit :', 'tokenico'),
                        'default' => 0
                    ],
                    [
                        'id'    => 'token',
                        'type'  => 'text',
                        'title' => esc_html__('Token :', 'tokenico')
                    ],
                    [
                        'id'    => 'key',
                        'type'  => 'text',
                        'title' => esc_html__('Presale Key :', 'tokenico')
                    ],
                    [
                        'id'    => 'network',
                        'type'  => 'text',
                        'title' => esc_html__('Network :', 'tokenico')
                    ],
                    [
                        'id'    => 'args',
                        'type'  => 'text',
                        'title' => esc_html__('Arguments :', 'tokenico')
                    ],
                    [
                        'id'    => 'contractAddress',
                        'type'  => 'text',
                        'title' => esc_html__('Contract address :', 'tokenico')
                    ],
                    [
                        'id'    => 'contractVersion',
                        'type'  => 'text',
                        'title' => esc_html__('Contract version :', 'tokenico')
                    ],
                    [
                        'id'    => 'networkId',
                        'type'  => 'select',
                        'title' => esc_html__('Blockchain network :', 'tokenico'),
                        'options' => Settings::getNetworksForList()
                    ],
                    [
                        'id'    => 'tokenProgram',
                        'type'  => 'select',
                        'title' => esc_html__('Token program :', 'tokenico'),
                        'options' => [
                            'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA' => 'TokenProgram (Standard SPL)',
                            'TokenzQdBNbLqP5VEhdkAS6EPFLC1PHnBqCXEpPxuEb' => 'TokenProgram 2022 (Taxable SPL)',
                        ],
                        'default' => 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA',
                        'dependency' => ['networkId', 'any', 'solana,solana-devnet']
                    ],
                    [
                        'id'    => 'tokenAddress',
                        'type'  => 'text',
                        'title' => esc_html__('Token contract address :', 'tokenico'),
                        'sanitize' => function ($val) {
                            return sanitize_text_field($val);
                        },
                        'validate' => function ($val) {
                            $val = sanitize_text_field($val);
                            if (empty($val)) {
                                return esc_html__('Token address cannot be empty.', 'tokenico');
                            }
                        }
                    ],
                    [
                        'id'    => 'totalSaleLimit',
                        'type'  => 'number',
                        'title' => esc_html__('Total sale limit : (Native coin)', 'tokenico'),
                        'desc' => sprintf(
                            esc_html__(
                                'The total amount of native coins you want to earn. (The native coin of the network where you will publish the contract) %s
                            
                                This total should be the native coin value you want to earn. Together with the Exchange rate the total amount of tokens you will sell is determined. For example, if you want to earn 100 ETH in total, this value should be 100. If you are going to sell a total of 100000 TOKEN, the exchange rate should be 1000. For 1 ETH you will give 1000 TOKEN. This will total 100000 TOKEN.Also, The total sales limit cannot be a digit number.',
                                'tokenico'
                            ),
                            '<br><br>'
                        ),
                        'attributes' => [
                            'min' => 0
                        ],
                        'sanitize' => function ($val) {
                            return floatval($val);
                        },
                        'validate' => function ($val) {
                            $val = floatval($val);
                            if (empty($val)) {
                                return esc_html__('Total limit cannot be empty.', 'tokenico');
                            } elseif ($val < 0) {
                                return esc_html__('Total limit cannot be less than 0!', 'tokenico');
                            }
                        }
                    ],
                    [
                        'id'    => 'minContribution',
                        'type'  => 'number',
                        'title' => esc_html__('Min contribution : (Native coin)', 'tokenico'),
                        'desc' => esc_html__('Minimum purchase limit for a user. (The native coin of the network where you will publish the contract)', 'tokenico'),
                        'attributes' => [
                            'min' => 0
                        ],
                        'sanitize' => function ($val) {
                            return floatval($val);
                        },
                        'validate' => function ($val) {
                            $val = floatval($val);
                            if (empty($val)) {
                                return esc_html__('Min contribution cannot be empty.', 'tokenico');
                            } elseif ($val < 0) {
                                return esc_html__('Min contribution cannot be less than 0!', 'tokenico');
                            }
                        }
                    ],
                    [
                        'id'    => 'maxContribution',
                        'type'  => 'number',
                        'title' => esc_html__('Max contribution : (Native coin)', 'tokenico'),
                        'desc' => esc_html__('Maximum purchase limit for a user. (The native coin of the network where you will publish the contract)', 'tokenico'),
                        'attributes' => [
                            'min' => 0
                        ],
                        'sanitize' => function ($val) {
                            return floatval($val);
                        },
                        'validate' => function ($val) {
                            $val = floatval($val);
                            if (empty($val)) {
                                return esc_html__('Max contribution cannot be empty.', 'tokenico');
                            } elseif ($val < 0) {
                                return esc_html__('Max contribution cannot be less than 0!', 'tokenico');
                            }
                        }
                    ],
                    [
                        'id'    => 'exchangeRate',
                        'type'  => 'number',
                        'title' => esc_html__('Exchange rate :', 'tokenico'),
                        'desc' => esc_html__('Example: 1 COIN = 100000 TOKEN. Also, The exchange rate cannot be a digit number like 100.10', 'tokenico') . '<br>' . 'If you want sale 10000000 TOKEN, and if you want make 100 Native coin (Total sale limit, ETH, BNB, etc.) <br> 10000000 TOKEN / 100 BNB = 100000 TOKEN you will give for 1 BNB',
                        'attributes' => [
                            'min' => 0
                        ],
                        'sanitize' => function ($val) {
                            return floatval($val);
                        },
                        'validate' => function ($val) {
                            $val = floatval($val);
                            if (empty($val)) {
                                return esc_html__('Exchange rate cannot be empty.', 'tokenico');
                            } elseif ($val < 0) {
                                return esc_html__('Exchange rate cannot be less than 0!', 'tokenico');
                            }
                        }
                    ],
                    // [
                    //     'id'    => 'usdRate',
                    //     'type'  => 'number',
                    //     'title' => esc_html__('Token amount for USD rate :', 'tokenico'),
                    //     'desc' => esc_html__('Example: 1 TOKEN = * USD', 'tokenico'),
                    //     'attributes' => [
                    //         'min' => 0
                    //     ],
                    //     'sanitize' => function($val) {
                    //         return floatval($val);
                    //     },
                    //     'validate' => function($val) {
                    //         $val = floatval($val);
                    //         if (empty($val)) {
                    //             return esc_html__('USD rate cannot be empty.', 'tokenico');
                    //         } elseif ($val < 0) {
                    //             return esc_html__('USD rate cannot be less than 0!', 'tokenico');
                    //         }
                    //     }
                    // ),
                    [
                        'id'    => 'startDate',
                        'type'  => 'text',
                        'title' => esc_html__('Start date :', 'tokenico'),
                        'desc' => esc_html__('It\'s date to start the presale. (Adjust the time according to the UTC time zone)', 'tokenico'),
                        'attributes' => [
                            'type' => 'datetime-local',
                            'autocomplete' => 'off'
                        ],
                        'validate' => function ($val) {
                            if (empty($val)) {
                                return esc_html__('Start date cannot be empty.', 'tokenico');
                            }
                        },
                    ],
                    [
                        'id'    => 'endDate',
                        'type'  => 'text',
                        'title' => esc_html__('End date :', 'tokenico'),
                        'desc' => esc_html__('It\'s date to end the presale. (Adjust the time according to the UTC time zone)', 'tokenico'),
                        'attributes' => [
                            'type' => 'datetime-local',
                            'autocomplete' => 'off'
                        ],
                        'validate' => function ($val) {
                            if (empty($val)) {
                                return esc_html__('End date cannot be empty.', 'tokenico');
                            }
                        },
                    ],
                    [
                        'id'    => 'instantTransfer',
                        'type'  => 'switcher',
                        'title' => esc_html__('Instant transfer :', 'tokenico'),
                        'desc' => esc_html__('It will be transferred instantly when the payment is completed. If it is closed, they can get the tokens they bought with the claim button after the presale is over.', 'tokenico'),
                        'default' => true
                    ],
                ]
            ]);

            // phpcs:ignore
            if ('presale' == $presale->post_type && 'publish' == $presale->post_status) {
                $presaleStatus = 'presaleStatus';
                CSF::createMetabox($presaleStatus, [
                    'title'     => esc_html__('Presale status', 'tokenico'),
                    'post_type' => 'presale',
                    'data_type' => 'unserialize',
                    'context'   => 'side',
                ]);

                CSF::createMetabox('shortcode', [
                    'title'     => esc_html__('Shortcode', 'tokenico'),
                    'post_type' => 'presale',
                    'data_type' => 'unserialize',
                    'context'   => 'side',
                ]);

                CSF::createSection('shortcode', [
                    'fields' => [
                        [
                            'id'    => 'shortcode',
                            'type'  => 'content',
                            'content' => sprintf('[tokenico-presale id="%s"]', $presale->ID)
                        ]
                    ]
                ]);

                CSF::createMetabox('actions', [
                    'title'     => esc_html__('Actions', 'tokenico'),
                    'post_type' => 'presale',
                    'data_type' => 'unserialize',
                    'context'   => 'side',
                ]);

                CSF::createSection('actions', [
                    'fields' => [
                        [
                            'id'    => 'updateDesc',
                            'type'  => 'content',
                            'content' => sprintf('<a href="#" class="button button-primary tico-update-desc" data-post-id="' . esc_attr($presale->ID) . '">%s</a>', esc_html__('Update title & description', 'tokenico'))
                        ]
                    ]
                ]);

                if ($presale->network) {
                    CSF::createSection($presaleStatus, [
                        'fields' => [
                            [
                                'id'    => 'totalSales',
                                'type'  => 'content',
                                'title' => esc_html__('Total sales :', 'tokenico'),
                                'content' => $presale->getTotalSales()
                            ],
                            [
                                'id'    => 'remainingLimit',
                                'type'  => 'content',
                                'title' => esc_html__('Remaining limit :', 'tokenico'),
                                'content' => $presale->getRemainingLimit()
                            ]
                        ]
                    ]);
                }
            }
        }
    }
}
