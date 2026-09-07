<?php
/**
 * MiniMax Text-to-Speech Conversion Model.
 *
 * @package MiniMax\MiniMaxAiProvider\Models
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Models;

use MiniMax\MiniMaxAiProvider\Provider\Provider;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Files\DTO\File;
use WordPress\AiClient\Messages\DTO\Message;
use WordPress\AiClient\Messages\DTO\MessagePart;
use WordPress\AiClient\Messages\Enums\MessageRoleEnum;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiBasedModel;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Http\Util\ResponseUtil;
use WordPress\AiClient\Providers\Models\TextToSpeechConversion\Contracts\TextToSpeechConversionModelInterface;
use WordPress\AiClient\Results\DTO\Candidate;
use WordPress\AiClient\Results\DTO\GenerativeAiResult;
use WordPress\AiClient\Results\DTO\TokenUsage;
use WordPress\AiClient\Results\Enums\FinishReasonEnum;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text-to-speech for MiniMax, over the synchronous T2A endpoint.
 *
 * MiniMax's speech API is not OpenAI-shaped in any respect, so unlike the text
 * and image models there is no compatible base class to lean on: the request is
 * built and the response parsed here.
 *
 * | OpenAI                          | MiniMax                              |
 * |---------------------------------|--------------------------------------|
 * | `POST /audio/speech`            | `POST /t2a_v2`                       |
 * | `input`                         | `text`                               |
 * | `voice: "alloy"`                | `voice_setting: { voice_id: … }`     |
 * | `response_format: "mp3"`        | `audio_setting: { format: "mp3" }`   |
 * | binary body                     | JSON, audio hex-encoded in `data`    |
 *
 * The last row is the awkward one. MiniMax answers with JSON and puts the audio
 * in `data.audio` as a hex string — not base64, and not a binary body — so it is
 * decoded here and handed to the SDK as a data URI.
 *
 * Only the synchronous endpoint is implemented. MiniMax also offers an async
 * task API for texts up to 1,000,000 characters and a WebSocket stream; both
 * need machinery this plugin does not have (polling, or a persistent socket),
 * and neither is worth blocking speech support on.
 *
 * @link https://platform.minimax.io/docs/api-reference/speech-t2a-http
 *
 * @since 1.6.0
 */
class TextToSpeechConversionModel extends AbstractApiBasedModel implements TextToSpeechConversionModelInterface {

	/**
	 * The speech models MiniMax serves from the T2A endpoint.
	 *
	 * Kept here rather than in the metadata directory because this is also the
	 * list that decides whether a model id is a speech model at all — the live
	 * `/v1/models` response does not say what a model can do.
	 *
	 * @since 1.6.0
	 *
	 * @var array<string, string>
	 */
	public const MODELS = array(
		'speech-2.8-hd'    => 'MiniMax Speech 2.8 HD',
		'speech-2.8-turbo' => 'MiniMax Speech 2.8 Turbo',
		'speech-2.6-hd'    => 'MiniMax Speech 2.6 HD',
		'speech-2.6-turbo' => 'MiniMax Speech 2.6 Turbo',
		'speech-02-hd'     => 'MiniMax Speech 02 HD',
		'speech-02-turbo'  => 'MiniMax Speech 02 Turbo',
		'speech-01-hd'     => 'MiniMax Speech 01 HD',
		'speech-01-turbo'  => 'MiniMax Speech 01 Turbo',
	);

	/**
	 * The voice used when the caller does not name one.
	 *
	 * `voice_setting.voice_id` is required by the API, so there has to be a
	 * default or every unconfigured request fails. This is the voice MiniMax
	 * uses in its own documentation example.
	 *
	 * @since 1.6.0
	 */
	public const DEFAULT_VOICE = 'English_expressive_narrator';

	/**
	 * The longest text the synchronous endpoint accepts.
	 *
	 * @since 1.6.0
	 */
	public const MAX_CHARACTERS = 10000;

	/**
	 * MIME types the caller may ask for, mapped to MiniMax's format names.
	 *
	 * MiniMax also accepts `pcm`, `pcmu_raw` and `pcmu_wav`. They are left out
	 * because raw PCM has no registered MIME type to declare them under, so a
	 * caller could not ask for one through `outputMimeType` anyway.
	 *
	 * @since 1.6.0
	 *
	 * @var array<string, string>
	 */
	private const FORMATS = array(
		'audio/mpeg' => 'mp3',
		'audio/mp3'  => 'mp3',
		'audio/wav'  => 'wav',
		'audio/flac' => 'flac',
		'audio/opus' => 'opus',
	);

	/**
	 * Whether the given model id is one of MiniMax's speech models.
	 *
	 * @since 1.6.0
	 *
	 * @param string $model_id Model id.
	 * @return bool
	 */
	public static function is_speech_model( string $model_id ): bool {
		return isset( self::MODELS[ $model_id ] );
	}

	/**
	 * Converts text to speech.
	 *
	 * @since 1.6.0
	 *
	 * @param array<int, mixed> $prompt The messages holding the text to speak.
	 *
	 * @phpstan-param list<Message> $prompt
	 *
	 * @return GenerativeAiResult The generated result.
	 */
	public function convertTextToSpeechResult( array $prompt ): GenerativeAiResult {
		$params = $this->prepare_convert_text_to_speech_params( $prompt );

		$request = $this->createRequest(
			HttpMethodEnum::POST(),
			't2a_v2',
			array( 'Content-Type' => 'application/json' ),
			$params
		);

		$request  = $this->getRequestAuthentication()->authenticateRequest( $request );
		$response = $this->getHttpTransporter()->send( $request );

		ResponseUtil::throwIfNotSuccessful( $response );
		$this->throw_if_minimax_reported_an_error( $response );

		return $this->parse_response_to_result( $response, $this->resolve_mime_type() );
	}

	/**
	 * Creates a request object for the provider's API.
	 *
	 * @since 1.6.0
	 *
	 * @param HttpMethodEnum                     $method  The HTTP method.
	 * @param string                             $path    The API endpoint path, relative to the base URI.
	 * @param array<string, string|list<string>> $headers The request headers.
	 * @param string|array<string, mixed>|null   $data    The request data.
	 * @return Request The request object.
	 */
	protected function createRequest(
		HttpMethodEnum $method,
		string $path,
		array $headers = array(),
		$data = null
	): Request {
		$headers['MiniMax-Provider'] = 'wordpress-plugin';

		return new Request(
			$method,
			Provider::url( $path ),
			$headers,
			$data,
			$this->getRequestOptions()
		);
	}

	/**
	 * Builds the request body MiniMax expects.
	 *
	 * @since 1.6.0
	 *
	 * @param array<int, mixed> $prompt The messages holding the text to speak.
	 *
	 * @phpstan-param list<Message> $prompt
	 *
	 * @return array<string, mixed> The parameters for the API request.
	 * @throws InvalidArgumentException If the prompt holds no usable text.
	 */
	protected function prepare_convert_text_to_speech_params( array $prompt ): array {
		$config = $this->getConfig();

		$params = array(
			'model'         => $this->metadata()->getId(),
			'text'          => $this->extract_text( $prompt ),
			'stream'        => false,
			'voice_setting' => array(
				'voice_id' => $config->getOutputSpeechVoice() ?? self::DEFAULT_VOICE,
			),
			'audio_setting' => array(
				'format' => self::FORMATS[ $this->resolve_mime_type() ] ?? 'mp3',
			),

			/*
			 * `hex` is MiniMax's own default, but it is sent explicitly because
			 * the choice is the caller's: an inline result is decoded here into
			 * a data URI, a remote one is handed back as the URL MiniMax
			 * returns, which expires.
			 */
			'output_format' => $this->wants_a_url() ? 'url' : 'hex',
		);

		foreach ( $config->getCustomOptions() as $key => $value ) {
			if ( isset( $params[ $key ] ) ) {
				throw new InvalidArgumentException(
					sprintf(
						'The custom option "%s" conflicts with an existing parameter.',
						$key // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					)
				);
			}

			$params[ $key ] = $value;
		}

		if ( ! function_exists( 'apply_filters' ) ) {
			return $params;
		}

		/**
		 * Filters the parameters sent to the MiniMax text-to-speech endpoint.
		 *
		 * The place to reach `voice_setting.speed`, `vol`, `pitch` and
		 * `emotion`, `audio_setting.sample_rate` and `bitrate`,
		 * `language_boost`, `subtitle_enable` and `pronunciation_dict`, none of
		 * which the AI Client's config models.
		 *
		 * @since 1.6.0
		 *
		 * @param array<string, mixed> $params   The request parameters.
		 * @param string               $model_id The model being called.
		 */
		$filtered = apply_filters( 'minimax_convert_text_to_speech_params', $params, $this->metadata()->getId() );

		/*
		 * Checked rather than trusted: a filter returns whatever a third party
		 * gave it, and a callback returning null or a string would otherwise
		 * become the request body.
		 *
		 * @phpstan-ignore function.alreadyNarrowedType
		 */
		if ( ! is_array( $filtered ) ) {
			return $params;
		}

		return $filtered;
	}

	/**
	 * Flattens the prompt into the single string the endpoint takes.
	 *
	 * @since 1.6.0
	 *
	 * @param array<int, mixed> $prompt The messages holding the text to speak.
	 *
	 * @phpstan-param list<Message> $prompt
	 *
	 * @return string
	 * @throws InvalidArgumentException If the prompt holds no text, or too much of it.
	 */
	protected function extract_text( array $prompt ): string {
		$texts = array();

		foreach ( $prompt as $message ) {
			foreach ( $message->getParts() as $part ) {
				/*
				 * The list is typed as messages of parts, so their classes are
				 * not re-checked here. What a part *holds* is not covered by
				 * that: an image or a function call reaching a speech endpoint
				 * is a caller mistake worth naming.
				 */
				if ( ! $part->getType()->isText() ) {
					throw new InvalidArgumentException( 'MiniMax text-to-speech input must contain only text parts.' );
				}

				$text = $part->getText();

				if ( is_string( $text ) && '' !== trim( $text ) ) {
					$texts[] = $text;
				}
			}
		}

		if ( empty( $texts ) ) {
			throw new InvalidArgumentException( 'MiniMax text-to-speech requires at least one non-empty text part.' );
		}

		$text = implode( "\n", $texts );

		/*
		 * Refused here rather than discovered as an API error, so the caller is
		 * told what the limit is instead of being handed MiniMax's
		 * "invalid input parameters".
		 */
		if ( mb_strlen( $text ) > self::MAX_CHARACTERS ) {
			throw new InvalidArgumentException(
				sprintf(
					'MiniMax synchronous text-to-speech accepts at most %1$d characters; %2$d given.',
					self::MAX_CHARACTERS, // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					mb_strlen( $text ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}

		return $text;
	}

	/**
	 * The MIME type the caller asked for, or MiniMax's default.
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 */
	protected function resolve_mime_type(): string {
		$requested = $this->getConfig()->getOutputMimeType();

		if ( is_string( $requested ) && isset( self::FORMATS[ $requested ] ) ) {
			return $requested;
		}

		return 'audio/mpeg';
	}

	/**
	 * Whether the caller wants a URL rather than the audio itself.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	protected function wants_a_url(): bool {
		$file_type = $this->getConfig()->getOutputFileType();

		return null !== $file_type && $file_type->isRemote();
	}

	/**
	 * Parses MiniMax's response into a result.
	 *
	 * @since 1.6.0
	 *
	 * @param Response $response  The response from the API endpoint.
	 * @param string   $mime_type The MIME type the audio was requested in.
	 * @return GenerativeAiResult The parsed result.
	 * @throws ResponseException If the response carries no usable audio.
	 */
	protected function parse_response_to_result( Response $response, string $mime_type ): GenerativeAiResult {
		$data = $response->getData();

		if ( ! is_array( $data ) || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			throw ResponseException::fromMissingData( $this->providerMetadata()->getName(), 'data' ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$audio = $data['data']['audio'] ?? null;

		if ( ! is_string( $audio ) || '' === $audio ) {
			throw ResponseException::fromInvalidData(
				$this->providerMetadata()->getName(), // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				'data.audio',
				'The value must be a non-empty string.'
			);
		}

		$file = new File( $this->to_file_value( $audio, $mime_type ), $mime_type );

		$candidate = new Candidate(
			new Message( MessageRoleEnum::model(), array( new MessagePart( $file ) ) ),
			FinishReasonEnum::stop()
		);

		/*
		 * MiniMax bills speech by character and reports it as `usage_characters`.
		 * That is not a token count and is deliberately not reported as one; the
		 * figure is left in the provider metadata below, where a caller can read
		 * it for what it is.
		 */
		$token_usage = new TokenUsage( 0, 0, 0 );

		$provider_metadata = $data;
		unset( $provider_metadata['data'] );

		return new GenerativeAiResult(
			isset( $data['trace_id'] ) && is_string( $data['trace_id'] ) ? $data['trace_id'] : '',
			array( $candidate ),
			$token_usage,
			$this->providerMetadata(),
			$this->metadata(),
			$provider_metadata
		);
	}

	/**
	 * Turns MiniMax's audio field into something `File` understands.
	 *
	 * With `output_format: url` the field is already a URL. Otherwise it is the
	 * audio hex-encoded — not base64 — so it is decoded and re-encoded as a data
	 * URI. A data URI rather than bare base64 on purpose: `File` tests a plain
	 * base64 string against `file_exists()` before accepting it, and a
	 * multi-megabyte string is not something to hand to the filesystem.
	 *
	 * @since 1.6.0
	 *
	 * @param string $audio     The `data.audio` value.
	 * @param string $mime_type The MIME type the audio was requested in.
	 * @return string
	 * @throws RuntimeException If the hex payload cannot be decoded.
	 */
	protected function to_file_value( string $audio, string $mime_type ): string {
		if ( 0 === strpos( $audio, 'http://' ) || 0 === strpos( $audio, 'https://' ) ) {
			return $audio;
		}

		$binary = @hex2bin( $audio ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( false === $binary ) {
			throw new RuntimeException( 'MiniMax returned audio that is not valid hex.' ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return sprintf( 'data:%1$s;base64,%2$s', $mime_type, base64_encode( $binary ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Refuse a failure that arrived with a 200.
	 *
	 * MiniMax reports API-level errors in a `base_resp` object while still
	 * answering 200, so an HTTP-status check passes and the caller would
	 * receive an empty result instead of an error.
	 *
	 * @since 1.6.0
	 *
	 * @param Response $response The response to check.
	 * @return void
	 * @throws RuntimeException If MiniMax reported an error.
	 */
	protected function throw_if_minimax_reported_an_error( Response $response ): void {
		$data = $response->getData();

		if ( ! is_array( $data ) || ! isset( $data['base_resp'] ) || ! is_array( $data['base_resp'] ) ) {
			return;
		}

		$raw    = $data['base_resp']['status_code'] ?? 0;
		$status = is_numeric( $raw ) ? (int) $raw : 0;

		if ( 0 === $status ) {
			return;
		}

		$message = isset( $data['base_resp']['status_msg'] ) && is_string( $data['base_resp']['status_msg'] )
			? $data['base_resp']['status_msg']
			: 'Unknown error.';

		throw new RuntimeException(
			sprintf( 'MiniMax text-to-speech failed (%1$d): %2$s', $status, $message ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		);
	}
}
