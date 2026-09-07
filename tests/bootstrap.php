<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests
 */

declare(strict_types=1);

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// WordPress\AiClient\* is provided by WordPress core at runtime and is kept out
// of the plugin's own vendor/ by the root composer.json `replace`. For the test
// suite only, load a dev-only side install of wordpress/php-ai-client so the
// plugin's provider/model classes can resolve their base classes. Populated by
// `composer stubs:install`.
$minimax_ai_client_autoload = dirname( __DIR__ ) . '/tools/ai-client/vendor/autoload.php';
if ( file_exists( $minimax_ai_client_autoload ) ) {
	require_once $minimax_ai_client_autoload;
}

// WordPress constants used by the plugin.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

/*
 * A minimal `WP_Error`, for the code paths that branch on one.
 *
 * Brain Monkey stubs functions, not classes, and the suite runs without
 * WordPress loaded — so `is_wp_error()` can be stubbed but the object it is
 * asked about has to come from somewhere.
 */
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {

		/**
		 * @var string
		 */
		private string $code;

		/**
		 * @var string
		 */
		private string $message;

		public function __construct( string $code = '', string $message = '' ) {
			$this->code    = $code;
			$this->message = $message;
		}

		public function get_error_code(): string {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}
	}
}

/*
 * A real `__()`, defined once for the whole suite.
 *
 * The plugin branches on `function_exists( '__' )`, so whether it is defined
 * changes which path runs. Brain Monkey defines a function the moment a test
 * stubs one and does not remove it afterwards — only the expectation — so a
 * single test stubbing `__` used to switch that branch on for every test that
 * ran after it, which then failed with "not defined nor mocked". Defining it
 * here makes the answer the same for every test, and the same as production,
 * where WordPress has always provided it.
 *
 * Brain Monkey can still redefine this per test where a test cares.
 */
if ( ! function_exists( '__' ) ) {
	/**
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function __( string $text, string $domain = 'default' ): string { // phpcs:ignore WordPress.NamingConventions
		return $text;
	}
}
