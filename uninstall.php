<?php
/**
 * Uninstall handler.
 *
 * Runs when the plugin is deleted from the Plugins screen — not on deactivation.
 *
 * @package MiniMax\\MiniMaxAiProvider
 */

declare(strict_types=1);

// Only WordPress may run this, and only while deleting the plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Removes the rows this plugin created.
 *
 * Two of them, and only two:
 *
 * - `minimax_settings` — the settings the options page writes.
 * - `minimax_models_cache` — the cached model catalogue.
 *
 * Credentials are deliberately left alone. `connectors_ai_minimax_api_key`
 * is written by the WordPress Connectors screen and `wp_ai_client_credentials`
 * is shared by every AI provider on the site, so removing either would be this
 * plugin deleting a row it did not create — and in the second case, deleting
 * other providers' keys along with its own. An API key is also not something to
 * destroy on a guess: it cannot be recovered, only reissued.
 *
 * @return void
 */
function minimax_uninstall_cleanup(): void {
	delete_option( 'minimax_settings' );
	delete_transient( 'minimax_models_cache' );
}

/*
 * Options and transients are per site, so a network uninstall has to visit each
 * one. `get_sites()` is capped: on a very large network the remainder is left
 * rather than the request timing out half way through, which would delete some
 * sites' rows and not others with no record of where it stopped.
 */
if ( is_multisite() ) {
	$site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 1000,
		)
	);

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		minimax_uninstall_cleanup();
		restore_current_blog();
	}
} else {
	minimax_uninstall_cleanup();
}
