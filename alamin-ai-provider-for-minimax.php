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
 * Version:           1.6.0
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

define( 'MINIMAX_VERSION', '1.6.0' );
define( 'MINIMAX_PLUGIN_FILE', __FILE__ );
define( 'MINIMAX_URL', plugin_dir_url( __FILE__ ) );
define( 'MINIMAX_PATH', plugin_dir_path( __FILE__ ) );

/*
 * The plugin's own autoloader, which ships with the source.
 *
 * This used to load Composer's, and bail silently when `vendor/` was absent —
 * so a copy installed from git activated, registered nothing and explained
 * nothing. There is no runtime dependency to justify Composer here:
 * `composer.json` requires `php` and `ext-json`, and everything in `vendor/`
 * is development tooling.
 */
require_once __DIR__ . '/includes/autoload.php';

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
