<?php
/**
 * MiniMax AI Provider.
 *
 * @package MiniMax\MiniMaxAiProvider\Provider
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Provider;

use WordPress\AiClient\AiClient;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use MiniMax\MiniMaxAiProvider\Availability\ProviderAvailability;
use MiniMax\MiniMaxAiProvider\Metadata\ModelMetadataDirectory;
use MiniMax\MiniMaxAiProvider\Models\ImageGenerationModel;
use MiniMax\MiniMaxAiProvider\Models\TextGenerationModel;
use MiniMax\MiniMaxAiProvider\Models\TextToSpeechConversionModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for the MiniMax provider.
 *
 * @since 1.0.0
 */
class Provider extends AbstractApiProvider {

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

		/*
		 * Image and speech are checked before text because both are narrower
		 * claims. A model that declared text alongside either would be a
		 * metadata bug, but if one ever does, sending it to the narrower
		 * endpoint fails loudly rather than returning a picture or an audio
		 * file described as text.
		 */
		foreach ( $capabilities as $capability ) {
			if ( $capability->isImageGeneration() ) {
				return new ImageGenerationModel( $model_metadata, $provider_metadata );
			}
		}

		foreach ( $capabilities as $capability ) {
			if ( $capability->isTextToSpeechConversion() ) {
				return new TextToSpeechConversionModel( $model_metadata, $provider_metadata );
			}
		}

		foreach ( $capabilities as $capability ) {
			if ( $capability->isTextGeneration() ) {
				return new TextGenerationModel( $model_metadata, $provider_metadata );
			}
		}

		throw new RuntimeException(
			'Unsupported model capabilities: ' . implode( ', ', $capabilities ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
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
			'https://platform.minimax.io/user-center/basic-information/interface-key',
			RequestAuthenticationMethod::apiKey(),
		);

		if ( version_compare( AiClient::VERSION, '1.2.0', '>=' ) ) { // @phpstan-ignore-line
			if ( function_exists( '__' ) ) {
				$provider_metadata_args[] = __( 'High-performance AI models for text, image and speech generation.', 'alamin-ai-provider-for-minimax' );
			} else {
				$provider_metadata_args[] = 'High-performance AI models for text, image and speech generation.';
			}
		}

		if ( version_compare( AiClient::VERSION, '1.3.0', '>=' ) ) { // @phpstan-ignore-line
			$provider_metadata_args[] = dirname( __DIR__, 2 ) . '/assets/images/minimax.svg';
		}

		return new ProviderMetadata( ...$provider_metadata_args );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createProviderAvailability(): ProviderAvailabilityInterface {
		return new ProviderAvailability();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface {
		return new ModelMetadataDirectory();
	}
}
