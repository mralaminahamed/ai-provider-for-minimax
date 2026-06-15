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
 * Version:           1.3.0
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

/**
 * Declare credential availability to the AI plugin.
 *
 * The AI plugin's has_ai_credentials() only checks connectors that store an
 * API key as a flat WP option. MiniMax stores its key under the nested
 * wp_ai_client_credentials option or the WP 7.0 Connectors page option, so
 * we must hook this filter explicitly.
 *
 * @since 1.2.0
 *
 * @param bool $has_credentials Current credential status.
 * @return bool
 */
function declare_credentials( bool $has_credentials ): bool {
	if ( $has_credentials ) {
		return true;
	}

	$api_key = getenv( 'MINIMAX_API_KEY' );
	if ( ! empty( $api_key ) ) {
		return true;
	}

	// Key stored by WordPress Connectors page (WP 7.0+).
	$connectors_key = get_option( 'connectors_ai_minimax_api_key', '' );
	if ( ! empty( $connectors_key ) ) {
		return true;
	}

	// Key stored via legacy wp_ai_client_credentials option.
	$option      = get_option( 'wp_ai_client_credentials', array() );
	$credentials = is_array( $option ) ? ( $option['minimax'] ?? array() ) : array();
	$key         = is_array( $credentials ) ? ( $credentials['api_key'] ?? '' ) : '';

	return ! empty( $key );
}

add_filter( 'wpai_has_ai_credentials', __NAMESPACE__ . '\\declare_credentials' );

/**
 * Short-circuit the valid credentials check when MiniMax key is configured.
 *
 * @since 1.2.0
 *
 * @param bool|null $valid Current validity status; null means "use default check".
 * @return bool|null
 */
function declare_valid_credentials( $valid ) {
	if ( true === $valid ) {
		return true;
	}

	return declare_credentials( false ) ? true : null;
}

add_filter( 'wpai_pre_has_valid_credentials_check', __NAMESPACE__ . '\\declare_valid_credentials' );
