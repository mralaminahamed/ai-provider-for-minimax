<?php
/**
 * Abstract base test for TextGenerationModel implementations.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;

/**
 * Class AbstractTextGenerationModelTest
 *
 * @since 1.3.2
 */
abstract class AbstractTextGenerationModelTest extends TestCase {

	/**
	 * @since 1.3.2
	 *
	 * @return string FQCN of the model class under test.
	 */
	abstract protected function getModelClass(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string A valid model ID to use in tests.
	 */
	abstract protected function getProviderModelId(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Expected provider name string.
	 */
	abstract protected function getProviderName(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Custom HTTP header name injected by this model.
	 */
	abstract protected function getCustomHeaderName(): string;

	/**
	 * @since 1.3.2
	 *
	 * @param string $modelId
	 * @return object
	 */
	abstract protected function createModel( string $modelId ): object;

	/**
	 * Set up Brain Monkey before each test.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->justReturn( array() );
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
	 * Test model is created correctly.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_model_is_created_correctly(): void {
		$model = $this->createModel( $this->getProviderModelId() );

		$this->assertInstanceOf( $this->getModelClass(), $model );
	}

	/**
	 * Test model has correct metadata ID.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_model_has_correct_metadata_id(): void {
		$model = $this->createModel( $this->getProviderModelId() );

		$this->assertEquals( $this->getProviderModelId(), $model->metadata()->getId() );
	}

	/**
	 * Test model has correct provider name.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_model_has_correct_provider_name(): void {
		$model = $this->createModel( $this->getProviderModelId() );

		$this->assertEquals( $this->getProviderName(), $model->providerMetadata()->getName() );
	}

	/**
	 * Test model injects custom provider header via createRequest.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_model_injects_custom_provider_header(): void {
		$model       = $this->createModel( $this->getProviderModelId() );
		$model_class = $this->getModelClass();

		$reflection = new \ReflectionMethod( $model_class, 'createRequest' );
		$reflection->setAccessible( true );

		$request = $reflection->invoke(
			$model,
			HttpMethodEnum::POST(),
			'chat/completions',
			array( 'Content-Type' => 'application/json' ),
			null
		);

		$header = $this->getCustomHeaderName();

		$this->assertTrue( $request->hasHeader( $header ) );
		$this->assertEquals( 'wordpress-plugin', $request->getHeaderAsString( $header ) );
	}
}
