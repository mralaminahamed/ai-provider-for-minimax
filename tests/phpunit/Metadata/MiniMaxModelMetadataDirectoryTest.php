<?php
/**
 * Tests for MiniMaxModelMetadataDirectory.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Metadata
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Metadata;

use AlAminAhamed\MiniMaxAiProvider\Metadata\MiniMaxModelMetadataDirectory;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Class MiniMaxModelMetadataDirectoryTest
 *
 * @since 1.0.0
 */
class MiniMaxModelMetadataDirectoryTest extends TestCase {

	/**
	 * @var MiniMaxModelMetadataDirectory
	 */
	private $directory;

	/**
	 * Set up test fixtures.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->justReturn( array() );
		$this->directory = new MiniMaxModelMetadataDirectory();
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
	 * Test directory implements interface.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_implements_interface(): void {
		$this->assertInstanceOf(
			\WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface::class,
			$this->directory
		);
	}

	/**
	 * Test list model metadata returns array.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_list_model_metadata_returns_array(): void {
		$models = $this->directory->listModelMetadata();

		$this->assertIsArray( $models );
	}

	/**
	 * Test fallback models are returned.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_fallback_models_are_returned(): void {
		$models = $this->directory->listModelMetadata();

		$this->assertNotEmpty( $models );

		$model_ids = array_map(
			static function ( $model ) {
				return $model->getId();
			},
			$models
		);

		$this->assertContains( 'MiniMax-M2.7', $model_ids );
	}

	/**
	 * Test has model metadata returns boolean.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_has_model_metadata_returns_bool(): void {
		$result = $this->directory->hasModelMetadata( 'MiniMax-M2.7' );

		$this->assertIsBool( $result );
		$this->assertTrue( $result );
	}

	/**
	 * Test has model metadata returns false for unknown model.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_has_model_metadata_returns_false_for_unknown(): void {
		$result = $this->directory->hasModelMetadata( 'unknown-model' );

		$this->assertFalse( $result );
	}

	/**
	 * Test get model metadata returns model.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_model_metadata_returns_model(): void {
		$model = $this->directory->getModelMetadata( 'MiniMax-M2.7' );

		$this->assertInstanceOf(
			\WordPress\AiClient\Providers\Models\DTO\ModelMetadata::class,
			$model
		);
		$this->assertEquals( 'MiniMax-M2.7', $model->getId() );
	}

	/**
	 * Test get model metadata throws for unknown model.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_model_metadata_throws_for_unknown(): void {
		$this->expectException(
			\WordPress\AiClient\Common\Exception\InvalidArgumentException::class
		);

		$this->directory->getModelMetadata( 'unknown-model' );
	}

	/**
	 * Test model has text generation capability.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_model_has_text_generation_capability(): void {
		$model = $this->directory->getModelMetadata( 'MiniMax-M2.7' );
		$caps  = $model->getSupportedCapabilities();

		$this->assertNotEmpty( $caps );
		$this->assertTrue( $caps[0]->isTextGeneration() );
	}

	/**
	 * Test fallback model list contains exactly 9 models.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_fallback_model_count(): void {
		$this->assertCount( 9, $this->directory->listModelMetadata() );
	}

	/**
	 * Test all 9 fallback model IDs are present.
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
	 * Test all fallback models have non-empty display names.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_all_fallback_models_have_names(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$this->assertNotEmpty( $model->getName(), "Empty name for model: {$model->getId()}" );
		}
	}

	/**
	 * Test all fallback models support the maxTokens option.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_all_models_support_max_tokens_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$option_names = array_map(
				static fn( $opt ) => (string) $opt->getName(),
				$model->getSupportedOptions()
			);
			$this->assertContains(
				'maxTokens',
				$option_names,
				"Model {$model->getId()} does not support maxTokens option"
			);
		}
	}

	/**
	 * Test model names match expected display strings.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_get_model_metadata_correct_names(): void {
		$this->assertEquals( 'MiniMax M2.7', $this->directory->getModelMetadata( 'MiniMax-M2.7' )->getName() );
		$this->assertEquals( 'MiniMax M2.7 Highspeed', $this->directory->getModelMetadata( 'MiniMax-M2.7-highspeed' )->getName() );
		$this->assertEquals( 'MiniMax M1', $this->directory->getModelMetadata( 'MiniMax-M1' )->getName() );
		$this->assertEquals( 'MiniMax Text-01', $this->directory->getModelMetadata( 'MiniMax-Text-01' )->getName() );
	}

	/**
	 * Test all fallback models support the temperature option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_temperature_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'temperature', $names, "Model {$model->getId()} missing temperature option" );
		}
	}

	/**
	 * Test all fallback models support the topP option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_top_p_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'topP', $names, "Model {$model->getId()} missing topP option" );
		}
	}

	/**
	 * Test all fallback models support the presencePenalty option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_presence_penalty_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'presencePenalty', $names, "Model {$model->getId()} missing presencePenalty option" );
		}
	}

	/**
	 * Test all fallback models support the frequencyPenalty option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_frequency_penalty_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'frequencyPenalty', $names, "Model {$model->getId()} missing frequencyPenalty option" );
		}
	}

	/**
	 * Test all fallback models support the stopSequences option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_stop_sequences_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'stopSequences', $names, "Model {$model->getId()} missing stopSequences option" );
		}
	}

	/**
	 * Test all fallback models support the systemInstruction option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_system_instruction_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'systemInstruction', $names, "Model {$model->getId()} missing systemInstruction option" );
		}
	}

	/**
	 * Test all fallback models support the functionDeclarations option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_function_declarations_option(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'functionDeclarations', $names, "Model {$model->getId()} missing functionDeclarations option" );
		}
	}

	/**
	 * Test all fallback model IDs are unique.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_model_ids_are_unique(): void {
		$ids = array_map( static fn( $m ) => $m->getId(), $this->directory->listModelMetadata() );

		$this->assertCount( count( $ids ), array_unique( $ids ) );
	}

	/**
	 * Test all fallback model IDs are non-empty strings.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_model_ids_are_non_empty_strings(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$this->assertIsString( $model->getId() );
			$this->assertNotEmpty( $model->getId() );
		}
	}

	/**
	 * Test all fallback models are ModelMetadata instances.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_are_model_metadata_instances(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$this->assertInstanceOf(
				\WordPress\AiClient\Providers\Models\DTO\ModelMetadata::class,
				$model
			);
		}
	}
}
