<?php
/**
 * Abstract base test for Provider implementations.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests;

use PHPUnit\Framework\TestCase;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;

/**
 * Class AbstractProviderTest
 *
 * @since 1.3.2
 */
abstract class AbstractProviderTest extends TestCase {

	/**
	 * @since 1.3.2
	 *
	 * @return string FQCN of the provider class under test.
	 */
	abstract protected function getProviderClass(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Expected base URL for the provider.
	 */
	abstract protected function getExpectedBaseUrl(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Expected provider metadata ID.
	 */
	abstract protected function getExpectedProviderId(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Expected provider metadata name.
	 */
	abstract protected function getExpectedProviderName(): string;

	/**
	 * Test provider has correct base URL.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_has_correct_base_url(): void {
		$class = $this->getProviderClass();
		$this->assertEquals( $this->getExpectedBaseUrl(), $class::url() );
	}

	/**
	 * Test provider metadata has correct ID.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_correct_id(): void {
		$class = $this->getProviderClass();
		$this->assertEquals( $this->getExpectedProviderId(), $class::metadata()->getId() );
	}

	/**
	 * Test provider metadata has correct name.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_correct_name(): void {
		$class = $this->getProviderClass();
		$this->assertEquals( $this->getExpectedProviderName(), $class::metadata()->getName() );
	}

	/**
	 * Test provider metadata has correct type.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_correct_type(): void {
		$class = $this->getProviderClass();
		$this->assertEquals( ProviderTypeEnum::cloud(), $class::metadata()->getType() );
	}

	/**
	 * Test provider metadata has API key authentication.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_api_key_auth(): void {
		$class = $this->getProviderClass();
		$this->assertEquals(
			RequestAuthenticationMethod::apiKey(),
			$class::metadata()->getAuthenticationMethod()
		);
	}

	/**
	 * Test provider returns a model metadata directory.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_has_model_metadata_directory(): void {
		$class = $this->getProviderClass();
		$this->assertInstanceOf( ModelMetadataDirectoryInterface::class, $class::modelMetadataDirectory() );
	}

	/**
	 * Test provider metadata has a credentials URL.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_has_credentials_url(): void {
		$class = $this->getProviderClass();
		$url   = $class::metadata()->getCredentialsUrl();

		$this->assertNotEmpty( $url );
		$this->assertStringStartsWith( 'https://', $url );
	}

	/**
	 * Test provider logo SVG file exists on disk.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_provider_logo_file_exists(): void {
		$class = $this->getProviderClass();
		$path  = $class::metadata()->getLogoPath();

		$this->assertNotEmpty( $path );
		$this->assertFileExists( $path );
		$this->assertStringEndsWith( '.svg', $path );
	}
}
