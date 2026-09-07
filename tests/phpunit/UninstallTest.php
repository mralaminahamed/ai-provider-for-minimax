<?php
/**
 * Tests for the uninstall handler.
 *
 * @package MiniMax\Tests
 */

declare(strict_types=1);

namespace MiniMax\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Deleting the plugin should take its rows with it, and nothing else.
 *
 * @since 1.6.0
 */
class UninstallTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Absolute path to the uninstall handler.
	 *
	 * @return string
	 */
	private function file(): string {
		return dirname( __DIR__, 2 ) . '/uninstall.php';
	}

	/**
	 * The handler exists at the path WordPress looks for.
	 *
	 * WordPress runs `uninstall.php` from the plugin root and nowhere else, so
	 * the location is the contract.
	 *
	 * @return void
	 */
	public function test_uninstall_handler_is_where_wordpress_expects_it(): void {
		$this->assertFileExists( $this->file() );
	}

	/**
	 * Loading it outside an uninstall does nothing at all.
	 *
	 * The file is world-readable and web-reachable in most installs, so without
	 * the guard a request straight to it would drop the settings.
	 *
	 * @return void
	 */
	public function test_it_refuses_to_run_outside_an_uninstall(): void {
		/*
		 * Run in its own process, which is the only way to see the guard work:
		 * the constant another test defines would still be set here, and
		 * `delete_option()` does not exist outside WordPress, so reaching it
		 * would be a fatal error rather than a deletion.
		 */
		$status = 0;
		$output = array();
		exec(
			escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $this->file() ) . ' 2>&1',
			$output,
			$status
		);

		$this->assertSame( 0, $status, implode( "\n", $output ) );
		$this->assertSame( '', trim( implode( "\n", $output ) ) );
	}

	/**
	 * It removes the two rows the plugin created.
	 *
	 * @return void
	 */
	public function test_it_deletes_the_rows_the_plugin_created(): void {
		$deleted_options    = array();
		$deleted_transients = array();

		Functions\when( 'delete_option' )->alias(
			static function ( $name ) use ( &$deleted_options ) {
				$deleted_options[] = $name;
				return true;
			}
		);
		Functions\when( 'delete_transient' )->alias(
			static function ( $name ) use ( &$deleted_transients ) {
				$deleted_transients[] = $name;
				return true;
			}
		);

		$this->run_cleanup();

		$this->assertSame( array( 'minimax_settings' ), $deleted_options );
		$this->assertSame( array( 'minimax_models_cache' ), $deleted_transients );
	}

	/**
	 * It does not delete credentials.
	 *
	 * `connectors_ai_minimax_api_key` is written by the WordPress Connectors
	 * screen and `wp_ai_client_credentials` is shared by every AI provider on
	 * the site — deleting the second would take other providers' keys with it.
	 * An API key cannot be recovered either, only reissued, so neither is
	 * something to remove on a guess.
	 *
	 * @return void
	 */
	public function test_it_leaves_credentials_alone(): void {
		$deleted = array();

		Functions\when( 'delete_option' )->alias(
			static function ( $name ) use ( &$deleted ) {
				$deleted[] = $name;
				return true;
			}
		);
		Functions\when( 'delete_transient' )->justReturn( true );

		$this->run_cleanup();

		$this->assertNotContains( 'wp_ai_client_credentials', $deleted );
		$this->assertNotContains( 'connectors_ai_minimax_api_key', $deleted );
	}

	/**
	 * Loads the handler and calls its cleanup function.
	 *
	 * The file guards on `WP_UNINSTALL_PLUGIN` and branches on `is_multisite()`,
	 * both of which are stubbed here so the single-site path runs.
	 *
	 * @return void
	 */
	private function run_cleanup(): void {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'plugin/plugin.php' );
		}

		Functions\when( 'is_multisite' )->justReturn( false );

		require_once $this->file();

		$this->assertTrue( function_exists( 'minimax_uninstall_cleanup' ) );
	}
}
