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
		$directory      = new MiniMaxModelMetadataDirectory();
		$provider_meta  = MiniMaxProvider::getProviderMetadata();
		$model_metadata = $directory->getModelMetadata( 'MiniMax-M2.7' );

		$model = MiniMaxProvider::createModel( $model_metadata, $provider_meta );

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
		$directory      = new MiniMaxModelMetadataDirectory();
		$provider_meta  = MiniMaxProvider::getProviderMetadata();
		$model_metadata = $directory->getModelMetadata( 'MiniMax-M2.7' );

		$model = MiniMaxProvider::createModel( $model_metadata, $provider_meta );

		$this->assertEquals( 'MiniMax-M2.7', $model->getModelMetadata()->getId() );
	}

	/**
	 * Test model has MiniMax provider header.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_model_has_minimax_provider_header(): void {
		$directory      = new MiniMaxModelMetadataDirectory();
		$provider_meta  = MiniMaxProvider::getProviderMetadata();
		$model_metadata = $directory->getModelMetadata( 'MiniMax-M2.7' );

		$model = MiniMaxProvider::createModel( $model_metadata, $provider_meta );

		$headers = $model->getDefaultHeaders();

		$this->assertArrayHasKey( 'OpenCode-Provider', $headers );
		$this->assertEquals( 'wordpress-plugin', $headers['OpenCode-Provider'] );
	}
}
