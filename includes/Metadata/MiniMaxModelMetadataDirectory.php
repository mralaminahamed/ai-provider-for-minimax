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
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Model metadata directory for MiniMax.
 *
 * @since 1.0.0
 */
class MiniMaxModelMetadataDirectory implements ModelMetadataDirectoryInterface {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	public function listModelMetadata(): array {
		$models = $this->fetch_models_from_api();

		if ( ! empty( $models ) ) {
			return array_values( $models );
		}

		return array_values( $this->get_fallback_models() );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @param string $model_id The model identifier.
	 */
	public function hasModelMetadata( string $model_id ): bool {
		return $this->get( $model_id ) !== null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @param string $model_id The model identifier.
	 * @throws InvalidArgumentException If model metadata not found.
	 */
	public function getModelMetadata( string $model_id ): ModelMetadata {
		$model = $this->get( $model_id );

		if ( null === $model ) {
			throw new InvalidArgumentException(
				sprintf( 'Model metadata not found for model: %s', $model_id ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			);
		}

		return $model;
	}

	/**
	 * Get model metadata by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $model_id Model ID.
	 * @return ModelMetadata|null
	 */
	private function get( string $model_id ): ?ModelMetadata {
		$all_models = $this->listModelMetadata();

		foreach ( $all_models as $model ) {
			if ( $model->getId() === $model_id ) {
				return $model;
			}
		}

		return null;
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

		if ( is_array( $cached ) ) {
			$valid = array();
			foreach ( $cached as $item ) {
				if ( $item instanceof ModelMetadata ) {
					$valid[] = $item;
				}
			}
			return $valid;
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
			set_transient( $transient_key, array(), 5 * MINUTE_IN_SECONDS );
			return array();
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code < 200 || $status_code >= 300 ) {
			set_transient( $transient_key, array(), 5 * MINUTE_IN_SECONDS );
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			set_transient( $transient_key, array(), 5 * MINUTE_IN_SECONDS );
			return array();
		}

		$capabilities = array(
			CapabilityEnum::textGeneration(),
		);
		$options      = array(
			new SupportedOption( OptionEnum::temperature() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::presencePenalty() ),
			new SupportedOption( OptionEnum::frequencyPenalty() ),
			new SupportedOption( OptionEnum::stopSequences() ),
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::functionDeclarations() ),
		);

		$models = array();
		foreach ( $data['data'] as $model_data ) {
			if ( ! is_array( $model_data ) ) {
				continue;
			}

			$id = isset( $model_data['id'] ) && is_string( $model_data['id'] ) ? $model_data['id'] : '';
			if ( '' === $id ) {
				continue;
			}

			$name_raw = $model_data['name'] ?? $id;
			$name     = is_string( $name_raw ) ? $name_raw : $id;

			$models[] = new ModelMetadata( $id, $name, $capabilities, $options );
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
		$capabilities = array(
			CapabilityEnum::textGeneration(),
		);
		$options      = array(
			new SupportedOption( OptionEnum::temperature() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::presencePenalty() ),
			new SupportedOption( OptionEnum::frequencyPenalty() ),
			new SupportedOption( OptionEnum::stopSequences() ),
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::functionDeclarations() ),
		);

		$model_list = array(
			array( 'MiniMax-M2.7', 'MiniMax M2.7' ),
			array( 'MiniMax-M2.7-highspeed', 'MiniMax M2.7 Highspeed' ),
			array( 'MiniMax-M2.5', 'MiniMax M2.5' ),
			array( 'MiniMax-M2.5-highspeed', 'MiniMax M2.5 Highspeed' ),
			array( 'MiniMax-M2.1', 'MiniMax M2.1' ),
			array( 'MiniMax-M2.1-highspeed', 'MiniMax M2.1 Highspeed' ),
			array( 'MiniMax-M2', 'MiniMax M2' ),
			array( 'MiniMax-M1', 'MiniMax M1' ),
			array( 'MiniMax-Text-01', 'MiniMax Text-01' ),
		);

		$models = array();
		foreach ( $model_list as $item ) {
			$models[] = new ModelMetadata( $item[0], $item[1], $capabilities, $options );
		}

		return $models;
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

		if ( ! function_exists( 'get_option' ) ) {
			return '';
		}

		// Key stored by WordPress Connectors page (WP 7.0+).
		$connectors_key = get_option( 'connectors_ai_minimax_api_key', '' );
		if ( is_string( $connectors_key ) && '' !== $connectors_key ) {
			return $connectors_key;
		}

		// Key stored via legacy wp_ai_client_credentials option.
		$option = get_option( 'wp_ai_client_credentials', array() );
		if ( ! is_array( $option ) ) {
			return '';
		}
		$credentials = $option['minimax'] ?? array();
		if ( ! is_array( $credentials ) ) {
			return '';
		}
		$api_key_value = $credentials['api_key'] ?? '';
		return is_string( $api_key_value ) ? $api_key_value : '';
	}
}
