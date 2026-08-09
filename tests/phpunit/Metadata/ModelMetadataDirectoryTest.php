<?php
/**
 * Tests for ModelMetadataDirectory.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests\Metadata
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests\Metadata;

use MiniMax\MiniMaxAiProvider\Metadata\ModelMetadataDirectory;
use MiniMax\MiniMaxAiProvider\Tests\AbstractModelMetadataDirectoryTest;
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
		return 9;
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
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_only_m3_declares_image_input(): void {
		$with_vision = array();

		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );

			if ( in_array( 'inputModalities', $names, true ) ) {
				$with_vision[] = $model->getId();
			}
		}

		$this->assertSame( array( 'MiniMax-M3' ), $with_vision );
	}

}
