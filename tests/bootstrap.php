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
