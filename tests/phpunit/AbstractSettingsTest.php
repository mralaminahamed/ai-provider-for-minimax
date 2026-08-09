<?php
/**
 * Abstract base test for Settings implementations.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractSettingsTest
 *
 * @since 1.3.2
 */
abstract class AbstractSettingsTest extends TestCase {

	/**
	 * @since 1.3.2
	 *
	 * @return string FQCN of the Settings class under test.
	 */
	abstract protected function getSettingsClass(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Expected value of the OPTION_KEY constant.
	 */
	abstract protected function getOptionKey(): string;

	/**
	 * @since 1.3.2
	 *
	 * @param array $input
	 * @return array
	 */
	protected function callSanitize( array $input ): array {
		$class = $this->getSettingsClass();
		return $class::sanitize_settings( $input );
	}

	/**
	 * @since 1.3.2
	 *
	 * @return array
	 */
	protected function callGetSettings(): array {
		$class = $this->getSettingsClass();
		return $class::get_settings();
	}

	/**
	 * Set up Brain Monkey before each test.
	 *
	 * @since 1.3.2
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
	 * @since 1.3.2
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
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_option_key_constant(): void {
		$class = $this->getSettingsClass();
		$this->assertEquals( $this->getOptionKey(), $class::OPTION_KEY );
	}

	/**
	 * Test sanitize settings returns array for valid input.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_returns_array(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => 'test-model-id',
				'temperature'   => 1.0,
				'max_tokens'    => 2048,
			)
		);

		$this->assertIsArray( $result );
	}

	/**
	 * Test sanitize settings stores model ID.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_stores_model(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => 'test-model-id',
				'temperature'   => 1.0,
				'max_tokens'    => 2048,
			)
		);

		$this->assertEquals( 'test-model-id', $result['default_model'] );
	}

	/**
	 * Test sanitize settings clamps temperature below 0 to 0.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_temperature_min(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
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
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_temperature_max(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
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
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_accepts_valid_temperature(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
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
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_max_tokens_min(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 0,
			)
		);

		$this->assertEquals( 1, $result['max_tokens'] );
	}

	/**
	 * Test sanitize settings clamps max_tokens to MiniMax-M3's context limit.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_max_tokens_max(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 9999999,
			)
		);

		$this->assertEquals( 1000000, $result['max_tokens'] );
	}

	/**
	 * Test sanitize settings handles empty input array.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_handles_empty_array(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize( array() );

		$this->assertArrayHasKey( 'default_model', $result );
		$this->assertArrayHasKey( 'temperature', $result );
		$this->assertArrayHasKey( 'max_tokens', $result );
	}

	/**
	 * Test sanitize settings returns empty array for null input.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_returns_empty_for_null(): void {
		$class  = $this->getSettingsClass();
		$result = $class::sanitize_settings( null );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test sanitize settings returns empty array for non-array input.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_returns_empty_for_string(): void {
		$class  = $this->getSettingsClass();
		$result = $class::sanitize_settings( 'not-an-array' );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test sanitize settings clamps top_p below 0 to 0.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_top_p_below_zero(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 100,
				'top_p'         => -1.0,
			)
		);

		$this->assertEquals( 0.0, $result['top_p'] );
	}

	/**
	 * Test sanitize settings clamps top_p above 1 to 1.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_clamps_top_p_above_one(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 100,
				'top_p'         => 5.0,
			)
		);

		$this->assertEquals( 1.0, $result['top_p'] );
	}

	/**
	 * Test sanitize settings accepts a valid top_p value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_accepts_valid_top_p(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 100,
				'top_p'         => 0.5,
			)
		);

		$this->assertEquals( 0.5, $result['top_p'] );
	}

	/**
	 * Test sanitize settings falls back to default top_p for non-numeric value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_defaults_for_non_numeric_top_p(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 100,
				'top_p'         => 'high',
			)
		);

		$this->assertEquals( 1.0, $result['top_p'] );
	}









	/**
	 * Test sanitize settings result contains all four expected keys.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_returns_all_four_keys(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => 'test-model-id',
				'temperature'   => 1.0,
				'max_tokens'    => 2048,
				'top_p'         => 0.9,
			)
		);

		$this->assertArrayHasKey( 'default_model', $result );
		$this->assertArrayHasKey( 'temperature', $result );
		$this->assertArrayHasKey( 'max_tokens', $result );
		$this->assertArrayHasKey( 'top_p', $result );

		// The MiniMax API ignores both penalties, so they are no longer stored.
		$this->assertArrayNotHasKey( 'presence_penalty', $result );
		$this->assertArrayNotHasKey( 'frequency_penalty', $result );
	}

	/**
	 * Test sanitize settings falls back to default temperature for non-numeric value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_defaults_for_non_numeric_temperature(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
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
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_sanitize_settings_defaults_for_non_numeric_max_tokens(): void {
		Functions\when( 'sanitize_text_field' )->returnArg();

		$result = $this->callSanitize(
			array(
				'default_model' => '',
				'temperature'   => 1.0,
				'max_tokens'    => 'lots',
			)
		);

		$this->assertEquals( 4096, $result['max_tokens'] );
	}

	/**
	 * Test get settings returns default temperature value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_default_temperature(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = $this->callGetSettings();

		$this->assertEquals( 0.7, $settings['temperature'] );
	}

	/**
	 * Test get settings returns default max_tokens value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_default_max_tokens(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = $this->callGetSettings();

		$this->assertEquals( 4096, $settings['max_tokens'] );
	}

	/**
	 * Test get settings returns default top_p value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_default_top_p(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = $this->callGetSettings();

		$this->assertEquals( 1.0, $settings['top_p'] );
	}



	/**
	 * Test get settings returns the class default model when nothing is saved.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_default_model_is_default(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$class    = $this->getSettingsClass();
		$settings = $this->callGetSettings();

		$this->assertSame( $class::DEFAULT_MODEL, $settings['default_model'] );
		$this->assertNotSame( '', $settings['default_model'] );
	}

	/**
	 * Test get settings merges saved values over defaults.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_merges_saved_values(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'temperature' => 1.5,
				'max_tokens'  => 8192,
			)
		);

		$settings = $this->callGetSettings();

		$this->assertEquals( 1.5, $settings['temperature'] );
		$this->assertEquals( 8192, $settings['max_tokens'] );
	}

	/**
	 * Test get settings returns saved top_p value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_saved_top_p(): void {
		Functions\when( 'get_option' )->justReturn( array( 'top_p' => 0.9 ) );

		$settings = $this->callGetSettings();

		$this->assertEquals( 0.9, $settings['top_p'] );
	}



	/**
	 * Test get settings returns saved default_model value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_saved_default_model(): void {
		Functions\when( 'get_option' )->justReturn( array( 'default_model' => 'test-model-id' ) );

		$settings = $this->callGetSettings();

		$this->assertEquals( 'test-model-id', $settings['default_model'] );
	}

	/**
	 * Test get settings result contains all six expected keys.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_returns_all_four_keys(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = $this->callGetSettings();

		$this->assertArrayHasKey( 'default_model', $settings );
		$this->assertArrayHasKey( 'temperature', $settings );
		$this->assertArrayHasKey( 'max_tokens', $settings );
		$this->assertArrayHasKey( 'top_p', $settings );
		$this->assertArrayNotHasKey( 'presence_penalty', $settings );
		$this->assertArrayNotHasKey( 'frequency_penalty', $settings );
	}

	/**
	 * Test get settings returns defaults when saved option is not an array.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_settings_returns_defaults_for_non_array_option(): void {
		Functions\when( 'get_option' )->justReturn( false );

		$settings = $this->callGetSettings();

		$this->assertEquals( 0.7, $settings['temperature'] );
		$this->assertEquals( 4096, $settings['max_tokens'] );
	}
}
