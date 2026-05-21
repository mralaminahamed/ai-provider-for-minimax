<?php
/**
 * AI Provider for MiniMax — plugin bootstrap.
 *
 * Loads the autoloader, registers the MiniMax provider with the
 * WordPress AI Client registry, and initialises the wp-admin settings page.
 *
 * @package AlAminAhamed\MiniMaxAiProvider
 * @author  Al Amin Ahamed
 * @link    https://github.com/mralaminahamed/ai-provider-for-minimax
 * @since   1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       AI Provider for MiniMax
 * Plugin URI:        https://github.com/mralaminahamed/ai-provider-for-minimax
 * Description:       MiniMax AI provider for the WordPress AI Client. Not affiliated with MiniMax.
 * Version:           1.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Al Amin Ahamed
 * Author URI:        https://alaminahamed.com
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       alamin-ai-provider-for-minimax
 * Domain Path:       /languages
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider;

use WordPress\AiClient\AiClient;
use AlAminAhamed\MiniMaxAiProvider\Provider\MiniMaxProvider;
use AlAminAhamed\MiniMaxAiProvider\Settings\MiniMaxSettings;

define( 'MINIMAX_PLUGIN_FILE', __FILE__ );

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Registers the MiniMax provider with the AI Client.
 *
 * @since 1.0.0
 *
 * @return void
 */
function register_provider(): void {
	if ( ! class_exists( AiClient::class ) ) {
		return;
	}

	$registry = AiClient::defaultRegistry();

	if ( $registry->hasProvider( MiniMaxProvider::class ) ) {
		return;
	}

	$registry->registerProvider( MiniMaxProvider::class );
}

add_action( 'init', __NAMESPACE__ . '\\register_provider', 5 );

/**
 * Initialize settings page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function init_settings(): void {
	MiniMaxSettings::init();
}

add_action( 'init', __NAMESPACE__ . '\\init_settings', 5 );
