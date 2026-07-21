<?php
/**
 * Tests for MiniMaxModelMetadataDirectory.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Metadata
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Metadata;

use AlAminAhamed\MiniMaxAiProvider\Metadata\MiniMaxModelMetadataDirectory;
use AlAminAhamed\MiniMaxAiProvider\Tests\AbstractModelMetadataDirectoryTest;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;

/**
 * Class MiniMaxModelMetadataDirectoryTest
 *
 * @since 1.0.0
 */
class MiniMaxModelMetadataDirectoryTest extends AbstractModelMetadataDirectoryTest {

	protected function createDirectory(): ModelMetadataDirectoryInterface {
		return new MiniMaxModelMetadataDirectory();
	}

	protected function getKnownModelId(): string {
		return 'MiniMax-M3';
	}

	protected function getExpectedModelCount(): int {
		return 8;
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
}
