<?php
/**
 * AI Provider for MiniMax
 *
 * @package           MiniMax
 * @author            Al Amin Ahamed
 * @copyright         2026 Al Amin Ahamed
 * @license           GPL-2.0-or-later
 * @link              https://github.com/mralaminahamed/ai-provider-for-minimax
 *
 * @wordpress-plugin
 * Plugin Name:       AI Provider for MiniMax
 * Plugin URI:        https://github.com/mralaminahamed/ai-provider-for-minimax
 * Description:       MiniMax AI provider for the WordPress AI Client. Not affiliated with MiniMax.
 * Version:           1.5.1
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Author:            Al Amin Ahamed
 * Author URI:        https://alaminahamed.com
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       alamin-ai-provider-for-minimax
 * Domain Path:       /languages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MINIMAX_VERSION', '1.5.1' );
define( 'MINIMAX_PLUGIN_FILE', __FILE__ );
define( 'MINIMAX_URL', plugin_dir_url( __FILE__ ) );
define( 'MINIMAX_PATH', plugin_dir_path( __FILE__ ) );

/*
 * Bail rather than fatal when the autoloader is absent.
 *
 * A plugin installed from git rather than from a built zip has no vendor
 * directory, and requiring a file that is not there takes the whole site down
 * instead of just this plugin.
 */
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Get the main plugin instance.
 *
 * @since 1.5.0
 *
 * @return AI_Provider_For_MiniMax Plugin instance.
 */
function ai_provider_for_minimax(): AI_Provider_For_MiniMax {
	return AI_Provider_For_MiniMax::get_instance();
}

ai_provider_for_minimax()->init();
