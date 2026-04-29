<?php
/**
 * MiniMax AI Provider.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Provider
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Provider;

use WordPress\AiClient\AiClient;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\ApiBasedImplementation\ListModelsApiBasedProviderAvailability;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use AlAminAhamed\MiniMaxAiProvider\Metadata\MiniMaxModelMetadataDirectory;
use AlAminAhamed\MiniMaxAiProvider\Models\MiniMaxTextGenerationModel;

/**
 * Class for the MiniMax provider.
 *
 * @since 1.0.0
 */
class MiniMaxProvider extends AbstractApiProvider {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function baseUrl(): string {
		return 'https://api.minimax.io/v1';
	}

	/**
	 * Creates a model instance for the given metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param ModelMetadata    $model_metadata    Model metadata.
	 * @param ProviderMetadata $provider_metadata Provider metadata.
	 * @return ModelInterface The created model instance.
	 * @throws RuntimeException If no supported capability is found.
	 */
	protected static function createModel(
		ModelMetadata $model_metadata,
		ProviderMetadata $provider_metadata
	): ModelInterface {
		$capabilities = $model_metadata->getSupportedCapabilities();

		foreach ( $capabilities as $capability ) {
			if ( $capability->isTextGeneration() ) {
				return new MiniMaxTextGenerationModel( $model_metadata, $provider_metadata );
			}
		}

		throw new RuntimeException(
			esc_html( 'Unsupported model capabilities: ' . implode( ', ', $capabilities ) )
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createProviderMetadata(): ProviderMetadata {
		$provider_metadata_args = array(
			'minimax',
			'MiniMax',
			ProviderTypeEnum::cloud(),
			'https://platform.minimax.io',
			RequestAuthenticationMethod::apiKey(),
		);

		if ( version_compare( AiClient::VERSION, '1.2.0', '>=' ) ) {
			if ( function_exists( '__' ) ) {
				$provider_metadata_args[] = __( 'High-performance AI models for coding and text generation.', 'alamin-ai-provider-for-minimax' );
			} else {
				$provider_metadata_args[] = 'High-performance AI models for coding and text generation.';
			}
		}

		return new ProviderMetadata( ...$provider_metadata_args );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createProviderAvailability(): ProviderAvailabilityInterface {
		return new ListModelsApiBasedProviderAvailability(
			static::modelMetadataDirectory()
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface {
		return new MiniMaxModelMetadataDirectory();
	}
}
