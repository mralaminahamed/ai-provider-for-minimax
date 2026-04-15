<?php
/**
 * Tests for MiniMaxProvider.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Provider
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Provider;

use AlAminAhamed\MiniMaxAiProvider\Provider\MiniMaxProvider;
use PHPUnit\Framework\TestCase;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;

/**
 * Class MiniMaxProviderTest
 *
 * @since 1.0.0
 */
class MiniMaxProviderTest extends TestCase {

	/**
	 * Test provider has correct base URL.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_provider_has_correct_base_url(): void {
		$base_url = MiniMaxProvider::url();

		$this->assertEquals( 'https://api.minimax.io/v1', $base_url );
	}

	/**
	 * Test provider metadata has correct ID.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_correct_id(): void {
		$metadata = MiniMaxProvider::metadata();

		$this->assertEquals( 'minimax', $metadata->getId() );
	}

	/**
	 * Test provider metadata has correct name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_correct_name(): void {
		$metadata = MiniMaxProvider::metadata();

		$this->assertEquals( 'MiniMax', $metadata->getName() );
	}

	/**
	 * Test provider metadata has correct type.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_correct_type(): void {
		$metadata = MiniMaxProvider::metadata();

		$this->assertEquals( ProviderTypeEnum::cloud(), $metadata->getType() );
	}

	/**
	 * Test provider metadata has correct authentication method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_provider_metadata_has_api_key_auth(): void {
		$metadata = MiniMaxProvider::metadata();

		$this->assertEquals( RequestAuthenticationMethod::apiKey(), $metadata->getAuthenticationMethod() );
	}

	/**
	 * Test provider metadata directory is set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_provider_has_model_metadata_directory(): void {
		$directory = MiniMaxProvider::modelMetadataDirectory();

		$this->assertInstanceOf(
			\WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface::class,
			$directory
		);
	}
}
