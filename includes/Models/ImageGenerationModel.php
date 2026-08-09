<?php
/**
 * MiniMax Image Generation Model.
 *
 * @package MiniMax\MiniMaxAiProvider\Models
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Models;

use WordPress\AiClient\Files\Enums\MediaOrientationEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleImageGenerationModel;
use WordPress\AiClient\Results\DTO\GenerativeAiResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image generation for MiniMax.
 *
 * MiniMax's text endpoint is OpenAI-compatible and its image endpoint is not,
 * so this extends the OpenAI-compatible base for the plumbing it does share —
 * request construction, candidate parsing, config handling — and overrides the
 * three places the two APIs genuinely differ:
 *
 * | OpenAI                        | MiniMax                          |
 * |-------------------------------|----------------------------------|
 * | `POST /images/generations`    | `POST /image_generation`         |
 * | `size: "1024x1024"`           | `aspect_ratio: "16:9"`           |
 * | `response_format: b64_json`   | `response_format: base64`        |
 * | `data: [ { b64_json: … } ]`   | `data: { image_base64: [ … ] }`  |
 *
 * The last one is the awkward one: OpenAI returns a list of objects and
 * MiniMax returns an object of lists. Rather than reimplement candidate
 * parsing, the response is reshaped into what the parent already understands.
 *
 * @link https://platform.minimax.io/docs/guides/image-generation
 *
 * @since 1.5.0
 */
class ImageGenerationModel extends AbstractOpenAiCompatibleImageGenerationModel {

	/**
	 * The only model MiniMax currently serves images from.
	 *
	 * @since 1.5.0
	 */
	public const MODEL_ID = 'image-01';

	/**
	 * Creates a request object for the provider's API.
	 *
	 * @since 1.5.0
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
			$path,
			$headers,
			$data,
			$this->getRequestOptions()
		);
	}

	/**
	 * Generates an image.
	 *
	 * Overridden in full because the parent hardcodes `images/generations` as
	 * the path, and MiniMax serves images from `image_generation`.
	 *
	 * @since 1.5.0
	 *
	 * @param array<int, mixed> $prompt The prompt to generate an image for.
	 *
	 * @phpstan-param list<\WordPress\AiClient\Messages\DTO\Message> $prompt
	 *
	 * @return GenerativeAiResult The generated result.
	 */
	public function generateImageResult( array $prompt ): GenerativeAiResult {
		$params = $this->prepareGenerateImageParams( $prompt );

		$request = $this->createRequest(
			HttpMethodEnum::POST(),
			'image_generation',
			array( 'Content-Type' => 'application/json' ),
			$params
		);

		$request  = $this->getRequestAuthentication()->authenticateRequest( $request );
		$response = $this->getHttpTransporter()->send( $request );

		$this->throwIfNotSuccessful( $response );

		return $this->parseResponseToGenerativeAiResult(
			$this->normalize_response( $response ),
			'url' === ( $params['response_format'] ?? '' ) ? 'image/png' : 'image/jpeg'
		);
	}

	/**
	 * Builds the request body MiniMax expects.
	 *
	 * @since 1.5.0
	 *
	 * @param array<int, mixed> $prompt The prompt to generate an image for.
	 *
	 * @phpstan-param list<\WordPress\AiClient\Messages\DTO\Message> $prompt
	 *
	 * @return array<string, mixed> The parameters for the API request.
	 */
	protected function prepareGenerateImageParams( array $prompt ): array {
		$params = parent::prepareGenerateImageParams( $prompt );

		/*
		 * MiniMax sizes an image by ratio, not by pixels. The parent will have
		 * produced an OpenAI `size` string; it is dropped and replaced.
		 */
		unset( $params['size'], $params['output_format'] );

		$config      = $this->getConfig();
		$orientation = $config->getOutputMediaOrientation();
		$ratio       = $config->getOutputMediaAspectRatio();

		if ( null !== $orientation || null !== $ratio ) {
			$params['aspect_ratio'] = $this->prepare_aspect_ratio( $orientation, $ratio );
		}

		/*
		 * The parent writes OpenAI's `b64_json`, which MiniMax rejects. Both
		 * APIs spell the URL variant the same way, so only the inline case
		 * needs translating.
		 */
		if ( ! isset( $params['response_format'] ) || 'url' !== $params['response_format'] ) {
			$params['response_format'] = 'base64';
		}

		/**
		 * Filters the parameters sent to the MiniMax image generation endpoint.
		 *
		 * The place to reach `seed`, `prompt_optimizer` and
		 * `subject_reference`, none of which the AI Client's config models.
		 *
		 * @since 1.5.0
		 *
		 * @param array<string, mixed> $params   The request parameters.
		 * @param string               $model_id The model being called.
		 */
		$filtered = apply_filters( 'minimax_generate_image_params', $params, $this->metadata()->getId() );

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
	 * Chooses an aspect ratio.
	 *
	 * An explicit ratio wins; otherwise an orientation is mapped to MiniMax's
	 * nearest supported ratio. Mirrors `WithAspectRatioTrait` in the official
	 * Google provider, which solves the same problem.
	 *
	 * @since 1.5.0
	 *
	 * @param MediaOrientationEnum|null $orientation Desired orientation.
	 * @param string|null               $ratio       Desired aspect ratio.
	 * @return string
	 */
	protected function prepare_aspect_ratio( ?MediaOrientationEnum $orientation, ?string $ratio ): string {
		if ( null !== $ratio ) {
			return $ratio;
		}

		if ( null !== $orientation ) {
			if ( $orientation->isLandscape() ) {
				return '16:9';
			}

			if ( $orientation->isPortrait() ) {
				return '9:16';
			}
		}

		return '1:1';
	}

	/**
	 * Reshape MiniMax's response into the one the parent parses.
	 *
	 * MiniMax answers `{"data": {"image_base64": ["…", "…"]}}` — an object of
	 * lists. The parent expects OpenAI's `{"data": [{"b64_json": "…"}]}` — a
	 * list of objects. Everything else about the two responses is close enough
	 * that reshaping is cheaper, and less likely to rot, than a second copy of
	 * the candidate parser.
	 *
	 * A response already in the expected shape is returned untouched, so this
	 * is safe if MiniMax ever converges on OpenAI's format.
	 *
	 * @since 1.5.0
	 *
	 * @param Response $response The raw response.
	 * @return Response A response the parent can parse.
	 */
	protected function normalize_response( Response $response ): Response {
		$data = $response->getData();

		if ( ! is_array( $data ) || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return $response;
		}

		// Already a list of objects — nothing to do.
		if ( array_is_list( $data['data'] ) ) {
			return $response;
		}

		$candidates = array();

		$shapes = array(
			'image_base64' => 'b64_json',
			'image_urls'   => 'url',
		);

		foreach ( $shapes as $source => $target ) {
			if ( ! isset( $data['data'][ $source ] ) || ! is_array( $data['data'][ $source ] ) ) {
				continue;
			}

			foreach ( $data['data'][ $source ] as $value ) {
				if ( is_string( $value ) && '' !== $value ) {
					$candidates[] = array( $target => $value );
				}
			}
		}

		$data['data'] = $candidates;

		return new Response(
			$response->getStatusCode(),
			$response->getHeaders(),
			(string) wp_json_encode( $data )
		);
	}

	/**
	 * Refuse a failure that arrived with a 200.
	 *
	 * MiniMax reports API-level errors in a `base_resp` object while still
	 * answering 200, so the parent's HTTP-status check passes and the caller
	 * would receive an empty result instead of an error.
	 *
	 * @since 1.5.0
	 *
	 * @param Response $response The response to check.
	 * @return void
	 *
	 * @throws \WordPress\AiClient\Common\Exception\RuntimeException If MiniMax reported an error.
	 */
	protected function throwIfNotSuccessful( Response $response ): void {
		parent::throwIfNotSuccessful( $response );

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

		throw new \WordPress\AiClient\Common\Exception\RuntimeException(
			sprintf( 'MiniMax image generation failed (%1$d): %2$s', $status, $message ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		);
	}
}
