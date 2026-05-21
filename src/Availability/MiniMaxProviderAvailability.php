<?php
/**
 * MiniMax Provider Availability.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Availability
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Availability;

use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\Http\Contracts\RequestAuthenticationInterface;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Availability check for the MiniMax provider.
 *
 * Returns configured only when a non-empty API key is present via
 * environment variable, the WP 7.0 Connectors page option, or the
 * legacy wp_ai_client_credentials option. This prevents the connector
 * showing as "Connected" on the Connectors page before any key is set.
 *
 * @since 1.2.0
 */
class MiniMaxProviderAvailability implements ProviderAvailabilityInterface {

	/**
	 * Request authentication instance set by the registry.
	 *
	 * @since 1.2.0
	 *
	 * @var RequestAuthenticationInterface|null
	 */
	private ?RequestAuthenticationInterface $request_authentication = null;

	/**
	 * Set the request authentication instance.
	 *
	 * @since 1.2.0
	 *
	 * @param RequestAuthenticationInterface $request_authentication Authentication instance.
	 * @return void
	 */
	public function setRequestAuthentication( RequestAuthenticationInterface $request_authentication ): void {
		$this->request_authentication = $request_authentication;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.2.0
	 */
	public function isConfigured(): bool {
		// Key passed by the registry (highest priority).
		if ( $this->request_authentication instanceof ApiKeyRequestAuthentication ) {
			return ! empty( $this->request_authentication->getApiKey() );
		}

		// Environment variable.
		$env_key = getenv( 'MINIMAX_API_KEY' );
		if ( ! empty( $env_key ) ) {
			return true;
		}

		if ( ! function_exists( 'get_option' ) ) {
			return false;
		}

		// WP 7.0+ Connectors page option.
		$connectors_key = get_option( 'connectors_ai_minimax_api_key', '' );
		if ( ! empty( $connectors_key ) ) {
			return true;
		}

		// Legacy wp_ai_client_credentials option.
		$option      = get_option( 'wp_ai_client_credentials', array() );
		$credentials = is_array( $option ) ? ( $option['minimax'] ?? array() ) : array();
		$key         = is_array( $credentials ) ? ( $credentials['api_key'] ?? '' ) : '';

		return ! empty( $key );
	}
}
