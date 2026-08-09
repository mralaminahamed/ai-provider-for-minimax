<?php
/**
 * MiniMax Text Generation Model.
 *
 * @package MiniMax\MiniMaxAiProvider\Models
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Models;

use MiniMax\MiniMaxAiProvider\Settings\Settings;
use WordPress\AiClient\Messages\DTO\Message;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text generation model for MiniMax.
 *
 * @since 1.0.0
 */
class TextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel {

	/**
	 * Applies the site's saved defaults and MiniMax's own parameters.
	 *
	 * The settings screen has stored a temperature, a token limit and a top_p
	 * since 1.0.0, and nothing has ever read them: no code outside
	 * `Settings` itself touched `minimax_settings`. Saving the form
	 * changed a row in `wp_options` and nothing else, so every generation ran
	 * on whatever the calling plugin happened to pass.
	 *
	 * A caller's own value always wins — this only fills what was left unset,
	 * which is what a default means.
	 *
	 * @since 1.5.0
	 *
	 * @param Message[] $prompt The prompt to generate text for.
	 *
	 * @phpstan-param list<Message> $prompt
	 * @return array<string, mixed> The parameters for the API request.
	 */
	protected function prepareGenerateTextParams( array $prompt ): array {
		$params = parent::prepareGenerateTextParams( $prompt );

		if ( ! function_exists( 'get_option' ) ) {
			return $params;
		}

		$settings = Settings::get_settings();

		foreach ( array( 'temperature', 'max_tokens', 'top_p' ) as $key ) {
			if ( ! isset( $params[ $key ] ) && isset( $settings[ $key ] ) ) {
				$params[ $key ] = $settings[ $key ];
			}
		}

		/*
		 * `thinking` and `service_tier` are MiniMax's own, so the SDK's
		 * OpenAI-shaped config has nowhere to carry them and they can only be
		 * added here.
		 *
		 * `thinking` is only sent when it is being turned off: M3 reasons by
		 * default and the M2.x models cannot be stopped from reasoning, so
		 * sending `adaptive` states the default at best and is refused at
		 * worst. `disabled` is the only value that changes anything.
		 */
		if ( isset( $settings['thinking'] ) && 'disabled' === $settings['thinking'] ) {
			$params['thinking'] = array( 'type' => 'disabled' );
		}

		if ( isset( $settings['service_tier'] ) && 'priority' === $settings['service_tier'] ) {
			$params['service_tier'] = 'priority';
		}

		/**
		 * Filters the parameters sent to the MiniMax chat completions endpoint.
		 *
		 * The last word before the request is built, for anything this plugin
		 * does not model.
		 *
		 * @since 1.5.0
		 *
		 * @param array<string, mixed> $params   The request parameters.
		 * @param array<string, mixed> $settings The saved plugin settings.
		 */
		$filtered = apply_filters( 'minimax_generate_text_params', $params, $settings );

		/*
		 * Checked rather than trusted: a filter returns whatever a third party
		 * gave it, and a callback returning null or a string would otherwise
		 * become the request body.
		 *
		 * PHPStan reads the filter's own `@param` as its return contract and so
		 * calls this redundant. It is redundant only for callbacks that honour
		 * the contract, which is not something this code can assume.
		 *
		 * @phpstan-ignore function.alreadyNarrowedType
		 */
		if ( ! is_array( $filtered ) ) {
			return $params;
		}

		return $filtered;
	}

	/**
	 * Creates a request object for the provider's API.
	 *
	 * @since 1.0.0
	 *
	 * @param HttpMethodEnum                     $method  The HTTP method.
	 * @param string                             $path    The API endpoint path, relative to the base URI.
	 * @param array<string, string|list<string>> $headers The request headers.
	 * @param string|array<string, mixed>|null   $data    The request data.
	 * @return Request The request object.
	 */
	protected function createRequest( HttpMethodEnum $method, string $path, array $headers = array(), $data = null ): Request {
		$headers['MiniMax-Provider'] = 'wordpress-plugin';

		return new Request(
			$method,
			$path,
			$headers,
			$data,
			$this->getRequestOptions()
		);
	}
}
