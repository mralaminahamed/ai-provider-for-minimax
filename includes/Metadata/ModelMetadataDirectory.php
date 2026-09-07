<?php
/**
 * MiniMax Model Metadata Directory.
 *
 * @package MiniMax\MiniMaxAiProvider\Metadata
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Metadata;

use MiniMax\MiniMaxAiProvider\Models\ImageGenerationModel;
use MiniMax\MiniMaxAiProvider\Models\TextToSpeechConversionModel;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Model metadata directory for MiniMax.
 *
 * @since 1.0.0
 */
class ModelMetadataDirectory implements ModelMetadataDirectoryInterface {

	/**
	 * The one chat model that accepts images.
	 *
	 * @since 1.5.0
	 */
	public const VISION_MODEL = 'MiniMax-M3';

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
	 * What these models can do.
	 *
	 * `chatHistory` was missing, and every official WordPress provider declares
	 * it. It means the model accepts a conversation rather than a single
	 * prompt — which MiniMax has always done, because the OpenAI-compatible
	 * endpoint takes a `messages` array and the SDK already sends one. Without
	 * the declaration the AI Client will not route a chat request here, so the
	 * plugin was declining work it could do.
	 *
	 * @since 1.5.0
	 *
	 * @return list<CapabilityEnum>
	 */
	private function capabilities(): array {
		return array(
			CapabilityEnum::textGeneration(),
			CapabilityEnum::chatHistory(),
		);
	}

	/**
	 * What `image-01` can do.
	 *
	 * Kept apart from the text models deliberately. Declaring `textGeneration`
	 * on an image model would route prompts to an endpoint that answers with a
	 * picture, and declaring image options on a chat model would advertise an
	 * aspect ratio that nothing reads.
	 *
	 * @since 1.5.0
	 *
	 * @return list<CapabilityEnum>
	 */
	private function image_capabilities(): array {
		return array(
			CapabilityEnum::imageGeneration(),
		);
	}

	/**
	 * The image options MiniMax honours.
	 *
	 * `outputMediaAspectRatio` and `outputMediaOrientation` both map onto
	 * MiniMax's single `aspect_ratio` field. `candidateCount` is `n`, and
	 * `outputFileType` chooses between a URL and inline base64.
	 *
	 * There is no `outputMimeType`: MiniMax does not let a caller pick the
	 * image format.
	 *
	 * @since 1.5.0
	 *
	 * @return list<SupportedOption>
	 */
	private function image_options(): array {
		return array(
			new SupportedOption( OptionEnum::outputMediaAspectRatio() ),
			new SupportedOption( OptionEnum::outputMediaOrientation() ),
			new SupportedOption( OptionEnum::candidateCount() ),
			new SupportedOption( OptionEnum::outputFileType() ),
		);
	}

	/**
	 * What the `speech-*` models can do.
	 *
	 * `textToSpeechConversion` rather than `speechGeneration`: the distinction
	 * the SDK draws is between speaking a text that was given (conversion) and
	 * producing speech from an instruction (generation). MiniMax's T2A endpoint
	 * takes a `text` field and reads it, which is the former.
	 *
	 * @since 1.6.0
	 *
	 * @return list<CapabilityEnum>
	 */
	private function speech_capabilities(): array {
		return array(
			CapabilityEnum::textToSpeechConversion(),
		);
	}

	/**
	 * The speech options MiniMax honours.
	 *
	 * `outputSpeechVoice` is `voice_setting.voice_id`, which the API requires —
	 * the model supplies a default when the caller names no voice.
	 * `outputMimeType` is `audio_setting.format`, and `outputFileType` chooses
	 * between MiniMax's hex-encoded audio and a URL.
	 *
	 * Speed, volume, pitch, emotion, sample rate, bitrate and language boost are
	 * all real MiniMax parameters with no option in the SDK to carry them; they
	 * are reachable through `customOptions` and the
	 * `minimax_convert_text_to_speech_params` filter rather than being declared
	 * as something they are not.
	 *
	 * @since 1.6.0
	 *
	 * @return list<SupportedOption>
	 */
	private function speech_options(): array {
		return array(
			new SupportedOption( OptionEnum::inputModalities(), array( array( ModalityEnum::text() ) ) ),
			new SupportedOption( OptionEnum::outputModalities(), array( array( ModalityEnum::audio() ) ) ),
			new SupportedOption(
				OptionEnum::outputMimeType(),
				array( 'audio/mpeg', 'audio/wav', 'audio/flac', 'audio/opus' )
			),
			new SupportedOption( OptionEnum::outputSpeechVoice() ),
			new SupportedOption( OptionEnum::outputFileType() ),
			new SupportedOption( OptionEnum::customOptions() ),
		);
	}

	/**
	 * Metadata for one model, chosen by what that model actually is.
	 *
	 * The live `/v1/models` response does not say what a model can do, so the
	 * id is the only signal available.
	 *
	 * @since 1.5.0
	 *
	 * @param string $id   Model id.
	 * @param string $name Human-readable name.
	 * @return ModelMetadata
	 */
	private function build_model( string $id, string $name ): ModelMetadata {
		if ( ImageGenerationModel::MODEL_ID === $id ) {
			return new ModelMetadata( $id, $name, $this->image_capabilities(), $this->image_options() );
		}

		if ( TextToSpeechConversionModel::is_speech_model( $id ) ) {
			return new ModelMetadata( $id, $name, $this->speech_capabilities(), $this->speech_options() );
		}

		/*
		 * Vision is an M3 feature; the M2 series is text only. The live
		 * `/v1/models` response does not say so, which is why this is keyed on
		 * the id.
		 */
		return new ModelMetadata( $id, $name, $this->capabilities(), $this->supported_options( self::VISION_MODEL === $id ) );
	}

	/**
	 * The generation options MiniMax actually honours.
	 *
	 * `SupportedOption` is a promise, not a wish list. The AI Client uses it to
	 * decide which model can satisfy a request, so declaring an option here
	 * routes callers who need that option to MiniMax — and if MiniMax ignores
	 * it, they get a silently wrong answer rather than an error.
	 *
	 * Three options were declared here and are documented by MiniMax as
	 * ignored on the OpenAI-compatible endpoint:
	 *
	 * - `presencePenalty`  — sent as `presence_penalty`, ignored
	 * - `frequencyPenalty` — sent as `frequency_penalty`, ignored
	 * - `stopSequences`    — sent as `stop`, ignored
	 *
	 * They are gone. A caller that needs stop sequences should be routed to a
	 * provider that implements them, which is exactly what removing them
	 * achieves.
	 *
	 * @link https://platform.minimax.io/docs/api-reference/text-openai-api
	 *
	 * @since 1.5.0
	 *
	 * @param bool $vision Whether this model reads images.
	 * @return list<SupportedOption>
	 */
	private function supported_options( bool $vision = false ): array {
		$options = array(
			new SupportedOption( OptionEnum::temperature() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::functionDeclarations() ),

			/*
			 * A passthrough for anything the SDK does not model, and the reason
			 * `thinking` and `service_tier` do not need one option each. The
			 * base class already merges these into the request body — the
			 * option was simply never declared, so no caller could reach it.
			 * All three official WordPress providers declare it.
			 */
			new SupportedOption( OptionEnum::customOptions() ),
		);

		/*
		 * Only MiniMax-M3 reads images. The SDK's OpenAI-compatible text model
		 * already converts an image message part into an `image_url` content
		 * part, so vision has worked since the first release and was never
		 * advertised — the AI Client will not route an image prompt to a model
		 * that does not declare it.
		 *
		 * Video is left out: MiniMax documents M3 as accepting it, but nothing
		 * in the SDK's OpenAI-compatible message builder emits a video part, so
		 * declaring it would promise a route that cannot be built.
		 */
		if ( $vision ) {
			$options[] = new SupportedOption(
				OptionEnum::inputModalities(),
				array(
					array( ModalityEnum::text() ),
					array( ModalityEnum::text(), ModalityEnum::image() ),
				)
			);
		}

		return $options;
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

			$models[] = $this->build_model( $id, $name );
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
		// Mirrors the official MiniMax chat-completion model catalogue.
		// See https://platform.minimax.io/docs/api-reference/api-overview.
		$model_list = array(
			array( 'MiniMax-M3', 'MiniMax M3' ),
			array( 'MiniMax-M2.7', 'MiniMax M2.7' ),
			array( 'MiniMax-M2.7-highspeed', 'MiniMax M2.7 Highspeed' ),
			array( 'MiniMax-M2.5', 'MiniMax M2.5' ),
			array( 'MiniMax-M2.5-highspeed', 'MiniMax M2.5 Highspeed' ),
			array( 'MiniMax-M2.1', 'MiniMax M2.1' ),
			array( 'MiniMax-M2.1-highspeed', 'MiniMax M2.1 Highspeed' ),
			array( 'MiniMax-M2', 'MiniMax M2' ),

			// Image generation, served from a different endpoint.
			array( ImageGenerationModel::MODEL_ID, 'MiniMax Image 01' ),
		);

		// Speech, served from `/v1/t2a_v2`.
		foreach ( TextToSpeechConversionModel::MODELS as $speech_id => $speech_name ) {
			$model_list[] = array( $speech_id, $speech_name );
		}

		$models = array();
		foreach ( $model_list as $item ) {
			$models[] = $this->build_model( $item[0], $item[1] );
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
