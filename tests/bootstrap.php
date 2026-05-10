<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests
 */

declare(strict_types=1);

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

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
