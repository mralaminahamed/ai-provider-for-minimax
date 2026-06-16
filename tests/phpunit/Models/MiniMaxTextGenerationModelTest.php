<?php
/**
 * Tests for MiniMaxTextGenerationModel.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Models
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Models;

use AlAminAhamed\MiniMaxAiProvider\Models\MiniMaxTextGenerationModel;
use AlAminAhamed\MiniMaxAiProvider\Provider\MiniMaxProvider;
use AlAminAhamed\MiniMaxAiProvider\Tests\AbstractTextGenerationModelTest;

/**
 * Class MiniMaxTextGenerationModelTest
 *
 * @since 1.0.0
 */
class MiniMaxTextGenerationModelTest extends AbstractTextGenerationModelTest {

	protected function getModelClass(): string {
		return MiniMaxTextGenerationModel::class;
	}

	protected function getProviderModelId(): string {
		return 'MiniMax-M2.7';
	}

	protected function getProviderName(): string {
		return 'MiniMax';
	}

	protected function getCustomHeaderName(): string {
		return 'MiniMax-Provider';
	}

	protected function createModel( string $modelId ): object {
		return MiniMaxProvider::model( $modelId );
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
