<?php
/**
 * Tests for MiniMaxSettings.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Settings
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Settings;

use AlAminAhamed\MiniMaxAiProvider\Settings\MiniMaxSettings;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Class MiniMaxSettingsTest
 *
 * @since 1.0.0
 */
class MiniMaxSettingsTest extends TestCase {

	/**
	 * Set up Brain Monkey before each test.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Tear down Brain Monkey after each test.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test option key constant value.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_option_key_constant(): void {
		$this->assertEquals( 'minimax_settings', MiniMaxSettings::OPTION_KEY );
	}

	/**
	 * Test sanitize settings returns array for valid input.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_returns_array(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => 'MiniMax-M2.7',
				'temperature'   => 1.0,
				'max_tokens'    => 2048,
			)
		);

		$this->assertIsArray( $result );
	}

	/**
	 * Test sanitize settings stores model ID.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_stores_model(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => 'MiniMax-M2.7',
				'temperature'   => 1.0,
				'max_tokens'    => 2048,
			)
		);

		$this->assertEquals( 'MiniMax-M2.7', $result['default_model'] );
	}

	/**
	 * Test sanitize settings clamps temperature below 0 to 0.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_temperature_min(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => '',
				'temperature'   => -5.0,
				'max_tokens'    => 100,
			)
		);

		$this->assertEquals( 0.0, $result['temperature'] );
	}

	/**
	 * Test sanitize settings clamps temperature above 2 to 2.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_temperature_max(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => '',
				'temperature'   => 99.9,
				'max_tokens'    => 100,
			)
		);

		$this->assertEquals( 2.0, $result['temperature'] );
	}

	/**
	 * Test sanitize settings accepts temperature within valid range.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_accepts_valid_temperature(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => '',
				'temperature'   => 0.7,
				'max_tokens'    => 100,
			)
		);

		$this->assertEquals( 0.7, $result['temperature'] );
	}

	/**
	 * Test sanitize settings clamps max_tokens of 0 to 1.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_max_tokens_min(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 0,
			)
		);

		$this->assertEquals( 1, $result['max_tokens'] );
	}

	/**
	 * Test sanitize settings clamps max_tokens above 200000 to 200000.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_max_tokens_max(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 999999,
			)
		);

		$this->assertEquals( 200000, $result['max_tokens'] );
	}

	/**
	 * Test sanitize settings handles empty input array.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_handles_empty_array(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings( array() );

		$this->assertArrayHasKey( 'default_model', $result );
		$this->assertArrayHasKey( 'temperature', $result );
		$this->assertArrayHasKey( 'max_tokens', $result );
	}

	/**
	 * Test sanitize settings returns empty array for null input.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_returns_empty_for_null(): void {
		$result = MiniMaxSettings::sanitize_settings( null );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test sanitize settings returns empty array for non-array input.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_returns_empty_for_string(): void {
		$result = MiniMaxSettings::sanitize_settings( 'not-an-array' );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test get settings returns default temperature when option is empty.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_settings_default_temperature(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = MiniMaxSettings::get_settings();

		$this->assertEquals( 0.7, $settings['temperature'] );
	}

	/**
	 * Test get settings returns default max_tokens when option is empty.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_settings_default_max_tokens(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = MiniMaxSettings::get_settings();

		$this->assertEquals( 4096, $settings['max_tokens'] );
	}

	/**
	 * Test get settings returns saved temperature value.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_settings_returns_saved_temperature(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'temperature' => 1.5,
				'max_tokens'  => 8192,
			)
		);

		$settings = MiniMaxSettings::get_settings();

		$this->assertEquals( 1.5, $settings['temperature'] );
	}

	/**
	 * Test get settings returns saved max_tokens value.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_settings_returns_saved_max_tokens(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'temperature' => 1.5,
				'max_tokens'  => 8192,
			)
		);

		$settings = MiniMaxSettings::get_settings();

		$this->assertEquals( 8192, $settings['max_tokens'] );
	}

	/**
	 * Test get settings returns defaults when option is not an array.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_settings_returns_defaults_for_non_array(): void {
		Functions\when( 'get_option' )->justReturn( false );

		$settings = MiniMaxSettings::get_settings();

		$this->assertEquals( 0.7, $settings['temperature'] );
		$this->assertEquals( 4096, $settings['max_tokens'] );
	}

	/**
	 * Test sanitize settings falls back to default temperature for non-numeric value.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_defaults_for_non_numeric_temperature(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model' => '',
				'temperature'   => 'hot',
				'max_tokens'    => 100,
			)
		);

		$this->assertEquals( 0.7, $result['temperature'] );
	}

	/**
	 * Test sanitize settings falls back to default max_tokens for non-numeric value.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_sanitize_settings_defaults_for_non_numeric_max_tokens(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = MiniMaxSettings::sanitize_settings(
			array(
				'default_model'  => '',
				'temperature'    => 1.0,
				'max_tokens'     => 'lots',
			)
		);

		$this->assertEquals( 4096, $result['max_tokens'] );
	}
}
