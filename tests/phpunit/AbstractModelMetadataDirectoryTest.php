<?php
/**
 * Abstract base test for ModelMetadataDirectory implementations.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

/**
 * Class AbstractModelMetadataDirectoryTest
 *
 * @since 1.3.2
 */
abstract class AbstractModelMetadataDirectoryTest extends TestCase {

	/**
	 * @var ModelMetadataDirectoryInterface
	 * @since 1.3.2
	 */
	protected ModelMetadataDirectoryInterface $directory;

	/**
	 * @since 1.3.2
	 *
	 * @return ModelMetadataDirectoryInterface
	 */
	abstract protected function createDirectory(): ModelMetadataDirectoryInterface;

	/**
	 * @since 1.3.2
	 *
	 * @return string A valid model ID known to exist in this directory.
	 */
	abstract protected function getKnownModelId(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return int Expected total number of fallback models.
	 */
	abstract protected function getExpectedModelCount(): int;

	/**
	 * Set up Brain Monkey and directory before each test.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->justReturn( array() );
		$this->directory = $this->createDirectory();
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
	 * Test directory implements the interface.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_implements_interface(): void {
		$this->assertInstanceOf( ModelMetadataDirectoryInterface::class, $this->directory );
	}

	/**
	 * Test list model metadata returns an array.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_list_model_metadata_returns_array(): void {
		$this->assertIsArray( $this->directory->listModelMetadata() );
	}

	/**
	 * Test fallback models list is non-empty.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_fallback_models_are_returned(): void {
		$this->assertNotEmpty( $this->directory->listModelMetadata() );
	}

	/**
	 * Test fallback model list contains expected number of models.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_fallback_model_count(): void {
		$this->assertCount( $this->getExpectedModelCount(), $this->directory->listModelMetadata() );
	}

	/**
	 * Test has model metadata returns true for known model.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_has_model_metadata_returns_true_for_known(): void {
		$this->assertTrue( $this->directory->hasModelMetadata( $this->getKnownModelId() ) );
	}

	/**
	 * Test has model metadata returns false for unknown model.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_has_model_metadata_returns_false_for_unknown(): void {
		$this->assertFalse( $this->directory->hasModelMetadata( 'unknown-model' ) );
	}

	/**
	 * Test get model metadata returns correct ModelMetadata instance.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_model_metadata_returns_model(): void {
		$model = $this->directory->getModelMetadata( $this->getKnownModelId() );

		$this->assertInstanceOf( ModelMetadata::class, $model );
		$this->assertEquals( $this->getKnownModelId(), $model->getId() );
	}

	/**
	 * Test get model metadata throws for unknown model ID.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_get_model_metadata_throws_for_unknown(): void {
		$this->expectException( InvalidArgumentException::class );

		$this->directory->getModelMetadata( 'unknown-model' );
	}

	/**
	 * Test all fallback models have text generation capability.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_have_text_generation_capability(): void {
		foreach ( $this->textModels() as $model ) {
			$caps = $model->getSupportedCapabilities();
			$this->assertNotEmpty( $caps, "Model {$model->getId()} has no capabilities" );
			$this->assertTrue(
				$caps[0]->isTextGeneration(),
				"Model {$model->getId()} first capability is not text generation"
			);
		}
	}

	/**
	 * Test all fallback models have human-readable names.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_fallback_models_have_names(): void {
		foreach ( $this->directory->listModelMetadata() as $model ) {
			$this->assertNotEmpty( $model->getName(), "Model {$model->getId()} has empty name" );
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
			$this->assertInstanceOf( ModelMetadata::class, $model );
		}
	}

	/**
	 * Test all fallback models support the temperature option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_temperature_option(): void {
		foreach ( $this->textModels() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'temperature', $names, "Model {$model->getId()} missing temperature option" );
		}
	}

	/**
	 * Test all fallback models support the maxTokens option.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_all_models_support_max_tokens_option(): void {
		foreach ( $this->textModels() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'maxTokens', $names, "Model {$model->getId()} does not support maxTokens option" );
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
		foreach ( $this->textModels() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'topP', $names, "Model {$model->getId()} missing topP option" );
		}
	}

	/**
	 * Options MiniMax documents as ignored are not declared as supported.
	 *
	 * These three used to be asserted the other way round, which is how they
	 * came to be declared at all. `SupportedOption` is what the AI Client reads
	 * to decide which model can satisfy a request: declaring an option MiniMax
	 * ignores routes a caller who needs it to a provider that will quietly
	 * disregard it and answer anyway.
	 *
	 * @link https://platform.minimax.io/docs/api-reference/text-openai-api
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_ignored_options_are_not_declared_as_supported(): void {
		$ignored = array( 'presencePenalty', 'frequencyPenalty', 'stopSequences' );

		foreach ( $this->directory->listModelMetadata() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );

			foreach ( $ignored as $option ) {
				$this->assertNotContains(
					$option,
					$names,
					"Model {$model->getId()} declares {$option}, which the MiniMax API ignores"
				);
			}
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
		foreach ( $this->textModels() as $model ) {
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
		foreach ( $this->textModels() as $model ) {
			$names = array_map( static fn( $opt ) => (string) $opt->getName(), $model->getSupportedOptions() );
			$this->assertContains( 'functionDeclarations', $names, "Model {$model->getId()} missing functionDeclarations option" );
		}
	}
	/**
	 * Every model advertises chat history, not just single-shot generation.
	 *
	 * The endpoint takes a `messages` array and the SDK already sends one, so
	 * this was always true — it just was not declared, and the AI Client routes
	 * on the declaration. Every official WordPress provider declares it.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_all_models_support_chat_history(): void {
		foreach ( $this->textModels() as $model ) {
			$caps = $model->getSupportedCapabilities();

			$has_chat_history = false;
			$has_text         = false;

			foreach ( $caps as $cap ) {
				$has_chat_history = $has_chat_history || $cap->isChatHistory();
				$has_text         = $has_text || $cap->isTextGeneration();
			}

			$this->assertTrue( $has_chat_history, "Model {$model->getId()} does not declare chatHistory" );
			$this->assertTrue( $has_text, "Model {$model->getId()} does not declare textGeneration" );
		}
	}

	/**
	 * Only the models that generate text.
	 *
	 * The catalogue is no longer uniform: `image-01` generates images and
	 * declares none of the chat options. Assertions about temperature or system
	 * instructions apply to the text models and would be wrong applied to it.
	 *
	 * @since 1.5.0
	 *
	 * @return array<int, \WordPress\AiClient\Providers\Models\DTO\ModelMetadata>
	 */
	protected function textModels(): array {
		return array_values(
			array_filter(
				$this->directory->listModelMetadata(),
				static function ( $model ) {
					foreach ( $model->getSupportedCapabilities() as $cap ) {
						if ( $cap->isTextGeneration() ) {
							return true;
						}
					}
					return false;
				}
			)
		);
	}

}
