<?php
/**
 * Tests for the plugin bootstrap and its autoloader.
 *
 * @package MiniMax\Tests
 */

declare(strict_types=1);

namespace MiniMax\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The plugin has to be able to load itself.
 *
 * @since 1.6.0
 */
class BootstrapTest extends TestCase {

	/**
	 * Absolute path to the plugin root.
	 *
	 * @return string
	 */
	private function root(): string {
		return dirname( __DIR__, 2 );
	}

	/**
	 * Every class loads with no Composer autoloader anywhere.
	 *
	 * This is the whole point of the change, so it is asserted the only way
	 * that actually proves it: in a fresh PHP process that loads
	 * `includes/autoload.php` and nothing else. Asserting it in-process would
	 * prove nothing, because PHPUnit itself is booted by Composer.
	 *
	 * A copy installed from git has no `vendor/`, and the plugin used to bail
	 * silently in exactly that situation — activated, registered no provider,
	 * explained nothing.
	 *
	 * @return void
	 */
	public function test_classes_load_without_composer(): void {
		$classes = array(
			'MiniMax\\MiniMaxAiProvider\\Provider\\Provider',
			'MiniMax\\MiniMaxAiProvider\\Models\\TextGenerationModel',
			'MiniMax\\MiniMaxAiProvider\\Models\\ImageGenerationModel',
			'MiniMax\\MiniMaxAiProvider\\Models\\TextToSpeechConversionModel',
			'MiniMax\\MiniMaxAiProvider\\Metadata\\ModelMetadataDirectory',
			'MiniMax\\MiniMaxAiProvider\\Settings\\Settings',
			'MiniMax\\MiniMaxAiProvider\\Availability\\ProviderAvailability',
			'AI_Provider_For_MiniMax',
		);

		/*
		 * The AI Client is loaded too, because the provider and model classes
		 * extend its base classes. WordPress supplies it from
		 * `wp-includes/php-ai-client/` at runtime; the dev-only side install
		 * stands in for it here. What this test is about is that the *plugin*
		 * needs no Composer autoloader of its own.
		 */
		$script = sprintf(
			'define("ABSPATH", "/tmp/"); define("MINIMAX_VERSION", "test");' .
			'require %s;' .
			'require %s;' .
			'foreach (%s as $c) { if (!class_exists($c, true)) { echo "MISSING: $c"; exit(1); } }' .
			'echo "ok";',
			var_export( $this->root() . '/tools/ai-client/vendor/autoload.php', true ),
			var_export( $this->root() . '/includes/autoload.php', true ),
			var_export( $classes, true )
		);

		$output = array();
		$status = 0;
		exec( escapeshellarg( PHP_BINARY ) . ' -r ' . escapeshellarg( $script ) . ' 2>&1', $output, $status );

		$this->assertSame( 0, $status, implode( "\n", $output ) );
		$this->assertSame( 'ok', trim( implode( "\n", $output ) ) );
	}

	/**
	 * The bootstrap does not reach for Composer.
	 *
	 * @return void
	 */
	public function test_bootstrap_does_not_require_composer(): void {
		$source = (string) file_get_contents( $this->root() . '/alamin-ai-provider-for-minimax.php' );

		$this->assertStringContainsString( "require_once __DIR__ . '/includes/autoload.php';", $source );
		$this->assertStringNotContainsString( 'vendor/autoload.php', $source );
	}

	/**
	 * Nothing under `vendor/` ships in the zip.
	 *
	 * `composer.json` requires `php` and `ext-json` and nothing else, so there
	 * is no runtime dependency to ship. The official WordPress AI provider
	 * plugins ship no vendor directory either.
	 *
	 * @return void
	 */
	public function test_vendor_is_excluded_from_the_package(): void {
		$distignore = (string) file_get_contents( $this->root() . '/.distignore' );
		$lines      = array_map( 'trim', explode( "\n", $distignore ) );

		foreach ( array( 'vendor/', 'composer.json', 'composer.lock' ) as $entry ) {
			$this->assertContains( $entry, $lines, "{$entry} would ship in the plugin zip." );
		}
	}

	/**
	 * There is no runtime dependency that would need Composer.
	 *
	 * If one is ever added, the bundled autoloader stops being sufficient and
	 * this fails rather than the plugin fataling on a site.
	 *
	 * @return void
	 */
	public function test_no_runtime_dependencies(): void {
		$composer = json_decode( (string) file_get_contents( $this->root() . '/composer.json' ), true );

		$this->assertIsArray( $composer );
		$this->assertSame(
			array( 'php', 'ext-json' ),
			array_keys( (array) $composer['require'] )
		);
	}
}
