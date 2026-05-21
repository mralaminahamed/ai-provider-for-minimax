<?php
/**
 * Tests for MiniMaxTextGenerationModel.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Models
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Models;

use AlAminAhamed\MiniMaxAiProvider\Metadata\MiniMaxModelMetadataDirectory;
use AlAminAhamed\MiniMaxAiProvider\Models\MiniMaxTextGenerationModel;
use AlAminAhamed\MiniMaxAiProvider\Provider\MiniMaxProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class MiniMaxTextGenerationModelTest
 *
 * @since 1.0.0
 */
class MiniMaxTextGenerationModelTest extends TestCase {

	/**
	 * Test model is created correctly.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_model_is_created_correctly(): void {
		$model = MiniMaxProvider::model( 'MiniMax-M2.7' );

		$this->assertInstanceOf( MiniMaxTextGenerationModel::class, $model );
	}

	/**
	 * Test model has correct metadata.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_model_has_correct_metadata(): void {
		$model = MiniMaxProvider::model( 'MiniMax-M2.7' );

		$this->assertEquals( 'MiniMax-M2.7', $model->metadata()->getId() );
	}

	/**
	 * Test model has MiniMax provider header.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_model_has_provider_metadata(): void {
		$model = MiniMaxProvider::model( 'MiniMax-M2.7' );

		$this->assertEquals( 'MiniMax', $model->providerMetadata()->getName() );
	}

	/**
	 * Test all 9 fallback models can be instantiated.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_all_fallback_models_are_creatable(): void {
		$model_ids = array(
			'MiniMax-M2.7',
			'MiniMax-M2.7-highspeed',
			'MiniMax-M2.5',
			'MiniMax-M2.5-highspeed',
			'MiniMax-M2.1',
			'MiniMax-M2.1-highspeed',
			'MiniMax-M2',
			'MiniMax-M1',
			'MiniMax-Text-01',
		);

		foreach ( $model_ids as $model_id ) {
			$model = MiniMaxProvider::model( $model_id );
			$this->assertInstanceOf( MiniMaxTextGenerationModel::class, $model );
			$this->assertEquals( $model_id, $model->metadata()->getId() );
		}
	}
}
