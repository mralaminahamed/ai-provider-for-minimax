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
}
