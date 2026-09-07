<?php
/**
 * PSR-4 autoloader for the plugin's own classes.
 *
 * The plugin used to load Composer's autoloader, which meant a copy installed
 * from git rather than from a built zip had no `vendor/` and silently did
 * nothing — it activated, registered no provider, and said nothing about it.
 *
 * There was never a runtime dependency to justify that. `composer.json`
 * requires `php` and `ext-json` and nothing else; everything in `vendor/` is
 * development tooling, so Composer's autoloader existed only to map this
 * plugin's own namespace onto `includes/`. That is what this file does, in a
 * form that ships with the source and cannot go missing.
 *
 * The three official WordPress AI provider plugins do the same and ship no
 * vendor directory at all.
 *
 * @package MiniMax\MiniMaxAiProvider
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix   = 'MiniMax\\MiniMaxAiProvider\\';
		$base_dir = __DIR__ . '/';
		$length   = strlen( $prefix );

		if ( 0 !== strncmp( $class_name, $prefix, $length ) ) {
			return;
		}

		$relative = substr( $class_name, $length );
		$file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';

		/*
		 * Checked rather than assumed: a namespaced class that does not exist
		 * on disk is a typo somewhere, and requiring it would turn that into a
		 * fatal error instead of a "class not found" the caller can handle.
		 */
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

// The plugin's orchestrator is not namespaced, so PSR-4 cannot reach it.
require_once dirname( __DIR__ ) . '/class-ai-provider-for-minimax.php';
