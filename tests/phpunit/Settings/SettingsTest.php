<?php
/**
 * Tests for Settings.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests\Settings
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests\Settings;

use Brain\Monkey\Functions;
use MiniMax\MiniMaxAiProvider\Models\ImageGenerationModel;
use MiniMax\MiniMaxAiProvider\Models\TextToSpeechConversionModel;
use MiniMax\MiniMaxAiProvider\Settings\Settings;
use MiniMax\MiniMaxAiProvider\Tests\AbstractSettingsTest;

/**
 * Class SettingsTest
 *
 * @since 1.0.0
 */
class SettingsTest extends AbstractSettingsTest {

	protected function getSettingsClass(): string {
		return Settings::class;
	}

	protected function getOptionKey(): string {
		return 'minimax_settings';
	}

	/**
	 * The default-model dropdown offers text models, and only text models.
	 *
	 * The field is labelled "for text generation" but listed everything the
	 * directory knew about, so `image-01` was already offered as a text model.
	 * Adding the speech models would have put eight more in the same list.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_model_dropdown_lists_only_text_models(): void {
		if ( ! defined( 'MINIMAX_PLUGIN_FILE' ) ) {
			define( 'MINIMAX_PLUGIN_FILE', dirname( __DIR__, 3 ) . '/alamin-ai-provider-for-minimax.php' );
		}

		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'selected' )->justReturn( '' );

		ob_start();
		Settings::render_model_field();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( 'MiniMax-M3', $html );
		$this->assertStringNotContainsString( ImageGenerationModel::MODEL_ID, $html );

		foreach ( array_keys( TextToSpeechConversionModel::MODELS ) as $speech_id ) {
			$this->assertStringNotContainsString( $speech_id, $html );
		}
	}

	/**
	 * The settings page offers a way to actually check the key.
	 *
	 * The old banner said "MiniMax is connected" on the strength of a key being
	 * present in the database, which is a different claim from one MiniMax has
	 * agreed to. The page now says what it knows and offers to find out.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_settings_page_offers_a_connection_test(): void {
		if ( ! defined( 'MINIMAX_PLUGIN_FILE' ) ) {
			define( 'MINIMAX_PLUGIN_FILE', dirname( __DIR__, 3 ) . '/alamin-ai-provider-for-minimax.php' );
		}

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_option' )->alias(
			static function ( $name ) {
				return 'connectors_ai_minimax_api_key' === $name ? 'a-key' : array();
			}
		);
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'admin_url' )->alias( static fn( $path = '' ) => 'https://example.test/wp-admin/' . $path );
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'selected' )->justReturn( '' );
		Functions\when( 'wp_nonce_field' )->justReturn( '' );
		Functions\when( 'settings_fields' )->justReturn( '' );
		Functions\when( 'do_settings_sections' )->justReturn( '' );
		Functions\when( 'submit_button' )->alias(
			static function ( $text = 'Save Changes' ) {
				echo '<button>' . $text . '</button>';
			}
		);

		ob_start();
		Settings::render_settings_page();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( Settings::TEST_ACTION, $html );
		$this->assertStringContainsString( 'Test connection', $html );

		// The old wording claimed more than the plugin knew.
		$this->assertStringNotContainsString( 'MiniMax is connected', $html );
	}
}
