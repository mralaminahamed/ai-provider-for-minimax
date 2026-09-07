<?php
/**
 * Tests for ModelMetadataDirectory.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests\Metadata
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests\Metadata;

use Brain\Monkey\Functions;
use MiniMax\MiniMaxAiProvider\Metadata\ModelMetadataDirectory;
use MiniMax\MiniMaxAiProvider\Models\TextToSpeechConversionModel;
use MiniMax\MiniMaxAiProvider\Tests\AbstractModelMetadataDirectoryTest;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;

/**
 * Class ModelMetadataDirectoryTest
 *
 * @since 1.0.0
 */
class ModelMetadataDirectoryTest extends AbstractModelMetadataDirectoryTest {

	protected function createDirectory(): ModelMetadataDirectoryInterface {
		return new ModelMetadataDirectory();
	}

	protected function getKnownModelId(): string {
		return 'MiniMax-M3';
	}

	protected function getExpectedModelCount(): int {
		return 17;
	}

	/**
	 * Test all 8 fallback model IDs are present.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_all_fallback_model_ids_present(): void {
		$ids = array_map(
			static fn( $m ) => $m->getId(),
			$this->directory->listModelMetadata()
		);

		$expected = array(
			'MiniMax-M3',
			'MiniMax-M2.7',
			'MiniMax-M2.7-highspeed',
			'MiniMax-M2.5',
			'MiniMax-M2.5-highspeed',
			'MiniMax-M2.1',
			'MiniMax-M2.1-highspeed',
			'MiniMax-M2',
		);

		foreach ( $expected as $model_id ) {
			$this->assertContains( $model_id, $ids, "Missing model: {$model_id}" );
		}
	}

	/**
	 * Test highspeed variants are included in the model list.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_highspeed_variants_present(): void {
		$this->assertTrue( $this->directory->hasModelMetadata( 'MiniMax-M2.7-highspeed' ) );
		$this->assertTrue( $this->directory->hasModelMetadata( 'MiniMax-M2.5-highspeed' ) );
		$this->assertTrue( $this->directory->hasModelMetadata( 'MiniMax-M2.1-highspeed' ) );
	}

	/**
	 * Test model names match expected display strings.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_get_model_metadata_correct_names(): void {
		$this->assertEquals( 'MiniMax M3', $this->directory->getModelMetadata( 'MiniMax-M3' )->getName() );
		$this->assertEquals( 'MiniMax M2.7', $this->directory->getModelMetadata( 'MiniMax-M2.7' )->getName() );
		$this->assertEquals( 'MiniMax M2.7 Highspeed', $this->directory->getModelMetadata( 'MiniMax-M2.7-highspeed' )->getName() );
		$this->assertEquals( 'MiniMax M2', $this->directory->getModelMetadata( 'MiniMax-M2' )->getName() );
	}
	/**
	 * Every model accepts arbitrary passthrough options.
	 *
	 * The SDK's base class has always merged `customOptions` into the request
	 * body; the option was simply never declared, so no caller could reach it.
	 * All three official WordPress providers declare it.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_all_models_support_custom_options(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );

			if ( 'image-01' === $model->getId() ) {
				continue;
			}

			$this->assertContains( 'customOptions', $names, "Model {$model->getId()} cannot take custom options" );
		}
	}

	/**
	 * Only MiniMax-M3 advertises image input.
	 *
	 * Vision has worked since the first release — the SDK turns an image
	 * message part into an `image_url` content part — but was never declared,
	 * so the AI Client would not route an image prompt here. The M2 series is
	 * text only and must not claim otherwise.
	 *
	 * Asserted on the modality values rather than on the presence of the
	 * `inputModalities` option: the speech models declare that option too, to
	 * say they take text, and a name-only check counted them as vision.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_only_m3_declares_image_input(): void {
		$with_vision = array();

		foreach ( $this->directory->listModelMetadata() as $model ) {
			foreach ( $model->getSupportedOptions() as $option ) {
				if ( 'inputModalities' !== (string) $option->getName() ) {
					continue;
				}

				foreach ( (array) $option->getSupportedValues() as $combination ) {
					foreach ( (array) $combination as $modality ) {
						if ( $modality instanceof ModalityEnum && $modality->isImage() ) {
							$with_vision[] = $model->getId();
							continue 4;
						}
					}
				}
			}
		}

		$this->assertSame( array( 'MiniMax-M3' ), $with_vision );
	}

	/**
	 * The speech models are speech models, and nothing else.
	 *
	 * Declaring `textGeneration` on one would route prompts to an endpoint that
	 * answers with an audio file.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_speech_models_declare_only_text_to_speech(): void {
		foreach ( array_keys( TextToSpeechConversionModel::MODELS ) as $model_id ) {
			$model        = $this->directory->getModelMetadata( $model_id );
			$capabilities = array_map(
				static fn( $capability ) => (string) $capability,
				$model->getSupportedCapabilities()
			);

			$this->assertSame( array( 'text_to_speech_conversion' ), $capabilities, $model_id );
		}
	}

	/**
	 * The configured default model is the one the AI Client reaches first.
	 *
	 * The setting has existed since 1.0.0 and nothing read it. Order is how the
	 * choice has to be expressed: the registry keeps matching models in
	 * `listModelMetadata()` order, and `PromptBuilder` falls back to "the first
	 * candidate discovered" when the caller names neither a model nor a
	 * preference list.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_configured_default_model_is_listed_first(): void {
		Functions\when( 'get_option' )->justReturn( array( 'default_model' => 'MiniMax-M2.5' ) );

		$ids = array_map( static fn( $m ) => $m->getId(), ( new ModelMetadataDirectory() )->listModelMetadata() );

		$this->assertSame( 'MiniMax-M2.5', $ids[0] );
	}

	/**
	 * Choosing a default drops nothing from the catalogue.
	 *
	 * Reordering, not filtering: a caller who names another model still gets it.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_choosing_a_default_does_not_hide_other_models(): void {
		$all = array_map( static fn( $m ) => $m->getId(), ( new ModelMetadataDirectory() )->listModelMetadata() );

		Functions\when( 'get_option' )->justReturn( array( 'default_model' => 'MiniMax-M2.5' ) );

		$reordered = array_map( static fn( $m ) => $m->getId(), ( new ModelMetadataDirectory() )->listModelMetadata() );

		sort( $all );
		sort( $reordered );
		$this->assertSame( $all, $reordered );
	}

	/**
	 * A default naming a model the catalogue no longer carries costs nothing.
	 *
	 * The live list changes under the site, so a stale setting should be
	 * ignored rather than treated as an error.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_a_stale_default_leaves_the_order_alone(): void {
		$before = array_map( static fn( $m ) => $m->getId(), ( new ModelMetadataDirectory() )->listModelMetadata() );

		Functions\when( 'get_option' )->justReturn( array( 'default_model' => 'MiniMax-Retired-99' ) );

		$after = array_map( static fn( $m ) => $m->getId(), ( new ModelMetadataDirectory() )->listModelMetadata() );

		$this->assertSame( $before, $after );
	}
}
