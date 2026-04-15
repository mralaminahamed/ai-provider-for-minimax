<?php
/**
 * MiniMax Model Metadata Directory.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Metadata
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Metadata;

use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Models\Capabilities\TextGenerationCapability;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;

/**
 * Model metadata directory for MiniMax.
 *
 * @since 1.0.0
 */
class MiniMaxModelMetadataDirectory implements ModelMetadataDirectoryInterface {

	/**
	 * Get all model metadata.
	 *
	 * @since 1.0.0
	 *
	 * @return ModelMetadata[]
	 */
	public function get_all(): array {
		$models = $this->fetch_models_from_api();

		if ( ! empty( $models ) ) {
			return $models;
		}

		return $this->get_fallback_models();
	}

	/**
	 * Get model metadata by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $model_id Model ID.
	 * @return ModelMetadata|null
	 */
	public function get( string $model_id ): ?ModelMetadata {
		$all_models = $this->get_all();

		foreach ( $all_models as $model ) {
			if ( $model->getId() === $model_id ) {
				return $model;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	public function listModelMetadata(): array {
		return $this->get_all();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	public function hasModelMetadata( string $model_id ): bool {
		return $this->get( $model_id ) !== null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @throws InvalidArgumentException If model metadata not found.
	 */
	public function getModelMetadata( string $model_id ): ModelMetadata {
		$model = $this->get( $model_id );

		if ( null === $model ) {
			throw new InvalidArgumentException(
				esc_html( "Model metadata not found for model: {$model_id}" )
			);
		}

		return $model;
	}

	/**
	 * Fetch models from the MiniMax API.
	 *
	 * @since 1.0.0
	 *
	 * @return ModelMetadata[]
	 */
	private function fetch_models_from_api(): array {
		$api_key = $this->get_api_key();

		if ( empty( $api_key ) ) {
			return array();
		}

		$transient_key = 'minimax_models_cache';
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			'https://api.minimax.io/v1/models',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return array();
		}

		$models = array();
		foreach ( $data['data'] as $model_data ) {
			if ( ! isset( $model_data['id'] ) ) {
				continue;
			}

			$models[] = new ModelMetadata(
				$model_data['id'],
				$model_data['id'],
				array(
					new TextGenerationCapability(
						CapabilityEnum::text_generation(),
						array(
							'max_tokens'      => 204800,
							'context_window'  => 204800,
							'supports_vision' => false,
						)
					),
				)
			);
		}

		set_transient( $transient_key, $models, HOUR_IN_SECONDS );

		return $models;
	}

	/**
	 * Get fallback models when API is not available.
	 *
	 * @since 1.0.0
	 *
	 * @return ModelMetadata[]
	 */
	private function get_fallback_models(): array {
		return array(
			new ModelMetadata(
				'MiniMax-M2.7',
				'MiniMax M2.7',
				array(
					new TextGenerationCapability(
						CapabilityEnum::text_generation(),
						array(
							'max_tokens'      => 204800,
							'context_window'  => 204800,
							'supports_vision' => false,
						)
					),
				)
			),
			new ModelMetadata(
				'MiniMax-M2.5',
				'MiniMax M2.5',
				array(
					new TextGenerationCapability(
						CapabilityEnum::text_generation(),
						array(
							'max_tokens'      => 204800,
							'context_window'  => 204800,
							'supports_vision' => false,
						)
					),
				)
			),
			new ModelMetadata(
				'MiniMax-M2.1',
				'MiniMax M2.1',
				array(
					new TextGenerationCapability(
						CapabilityEnum::text_generation(),
						array(
							'max_tokens'      => 204800,
							'context_window'  => 204800,
							'supports_vision' => false,
						)
					),
				)
			),
			new ModelMetadata(
				'MiniMax-M2',
				'MiniMax M2',
				array(
					new TextGenerationCapability(
						CapabilityEnum::text_generation(),
						array(
							'max_tokens'      => 204800,
							'context_window'  => 204800,
							'supports_vision' => false,
						)
					),
				)
			),
		);
	}

	/**
	 * Get the API key.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_api_key(): string {
		$api_key = getenv( 'MINIMAX_API_KEY' );

		if ( ! empty( $api_key ) ) {
			return $api_key;
		}

		if ( function_exists( 'get_option' ) ) {
			$option = get_option( 'wp_ai_client_credentials', array() );
			return $option['minimax']['api_key'] ?? '';
		}

		return '';
	}
}
