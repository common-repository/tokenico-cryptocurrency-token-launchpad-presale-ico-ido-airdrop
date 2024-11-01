<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

// @phpcs:disable PSR1.Files.SideEffects
// @phpcs:disable PSR12.Files.FileHeader
// @phpcs:disable Generic.Files.LineLength
// @phpcs:disable Generic.Files.InlineHTML

/**
 * Plugin Name:  TokenICO - Cryptocurrency (Token), Launchpad (Presale), ICO & IDO, Airdrop Lite
 * Version:      1.2.10
 * Plugin URI:   https://wordpress.org/plugins/tokenico-cryptocurrency-token-launchpad-presale-ico-ido-airdrop
 * Description:  Cryptocurrency (Token), Launchpad (Presale), ICO & IDO, Airdrop Lite
 * Author URI:   https://beycanpress.com
 * Author:       BeycanPress LLC
 * Tags:         Cryptocurrency (Token), Launchpad (Presale), ICO & IDO, Airdrop Binance Smart Chain token presale, Ethereum token presale, Avalanche token airdrop, Polygon token airdrop, Binance Smart Chain token airdrop, Ethereum token airdrop, Avalanche token airdrop, Polygon token airdrop, Binance Smart Chain token ICO, Ethereum token ICO, Avalanche token ICO, Polygon token ICO, Binance Smart Chain token launchpad, Ethereum token launchpad, Avalanche token launchpad, Polygon token launchpad, Binance Smart Chain token sale, Ethereum token sale, Avalanche token sale, Polygon token sale, Binance Smart Chain token crowdsale, Ethereum token crowdsale, Avalanche token crowdsale, Polygon token crowdsale, Binance Smart Chain token airdrop, Ethereum token airdrop, Avalanche token airdrop, Polygon token airdrop
 * Text Domain:  tokenico
 * License:      GPLv3
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:  /languages
 * Requires at least: 5.0
 * Tested up to: 6.6.2
 * Requires PHP: 8.1
*/

require __DIR__ . '/vendor/autoload.php';
new \BeycanPress\TokenicoLite\Loader(__FILE__);
