<?php
/**
 * Verifies that the configured API key actually works.
 *
 * @package MiniMax\MiniMaxAiProvider\Connection
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Connection;

use MiniMax\MiniMaxAiProvider\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asks MiniMax whether the key is good.
 *
 * The settings page and the Connectors screen both reported "connected" as soon
 * as a key was present anywhere, without ever asking MiniMax about it. A key
 * that had been revoked, mistyped, or copied from the wrong account read as
 * configured, and the first sign of trouble was a generation failing somewhere
 * else entirely.
 *
 * `GET /v1/models` is the cheapest question that has a real answer: it requires
 * authentication — an unauthenticated call answers 401 — and it generates
 * nothing, so verifying costs neither tokens nor money.
 *
 * @since 1.6.0
 */
class ConnectionTest {

	/**
	 * The key is good.
	 *
	 * @since 1.6.0
	 */
	public const VALID = 'valid';

	/**
	 * MiniMax rejected the key.
	 *
	 * @since 1.6.0
	 */
	public const INVALID = 'invalid';

	/**
	 * MiniMax could not be asked.
	 *
	 * Distinct from `INVALID` on purpose. A site behind a firewall, or one that
	 * timed out, has learned nothing about its key, and telling the owner their
	 * key is wrong on that evidence would send them to reissue a working one.
	 *
	 * @since 1.6.0
	 */
	public const UNKNOWN = 'unknown';

	/**
	 * How long a result is worth reusing.
	 *
	 * @since 1.6.0
	 */
	public const CACHE_TTL = 300;

	/**
	 * Where the last result is kept.
	 *
	 * @since 1.6.0
	 */
	public const TRANSIENT = 'minimax_connection_test';

	/**
	 * Asks MiniMax whether the configured key works.
	 *
	 * @since 1.6.0
	 *
	 * @return array{status: string, message: string}
	 */
	public static function run(): array {
		$api_key = Settings::get_api_key();

		if ( '' === $api_key ) {
			return self::result(
				self::INVALID,
				__( 'No API key is configured.', 'alamin-ai-provider-for-minimax' )
			);
		}

		$response = wp_remote_get(
			'https://api.minimax.io/v1/models',
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization'    => 'Bearer ' . $api_key,
					'Content-Type'     => 'application/json',
					'MiniMax-Provider' => 'wordpress-plugin',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return self::result(
				self::UNKNOWN,
				sprintf(
					/* translators: %s: error message returned by WordPress. */
					__( 'MiniMax could not be reached: %s', 'alamin-ai-provider-for-minimax' ),
					$response->get_error_message()
				)
			);
		}

		$status = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 === $status ) {
			return self::result(
				self::VALID,
				__( 'Connected. MiniMax accepted the API key.', 'alamin-ai-provider-for-minimax' )
			);
		}

		if ( 401 === $status || 403 === $status ) {
			return self::result(
				self::INVALID,
				sprintf(
					/* translators: %s: error message returned by MiniMax. */
					__( 'MiniMax rejected the API key: %s', 'alamin-ai-provider-for-minimax' ),
					self::describe( $response )
				)
			);
		}

		/*
		 * Anything else — a 5xx, a gateway page, a rate limit — says nothing
		 * about the key.
		 */
		return self::result(
			self::UNKNOWN,
			sprintf(
				/* translators: 1: HTTP status code. 2: error message returned by MiniMax. */
				__( 'MiniMax answered %1$d and the key could not be checked: %2$s', 'alamin-ai-provider-for-minimax' ),
				$status,
				self::describe( $response )
			)
		);
	}

	/**
	 * The last result, if one was taken recently.
	 *
	 * @since 1.6.0
	 *
	 * @return array{status: string, message: string}|null
	 */
	public static function last_result(): ?array {
		$cached = get_transient( self::TRANSIENT );

		if ( ! is_array( $cached ) ) {
			return null;
		}

		$status  = $cached['status'] ?? null;
		$message = $cached['message'] ?? null;

		if ( ! is_string( $status ) || ! is_string( $message ) ) {
			return null;
		}

		return array(
			'status'  => $status,
			'message' => $message,
		);
	}

	/**
	 * Forgets the last result.
	 *
	 * Called when the settings are saved, because the answer may have changed
	 * and a stale "connected" is worse than no answer at all.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public static function forget(): void {
		delete_transient( self::TRANSIENT );
	}

	/**
	 * Stores a result and returns it.
	 *
	 * @since 1.6.0
	 *
	 * @param string $status  One of the class constants.
	 * @param string $message Human-readable explanation.
	 * @return array{status: string, message: string}
	 */
	private static function result( string $status, string $message ): array {
		$result = array(
			'status'  => $status,
			'message' => $message,
		);

		set_transient( self::TRANSIENT, $result, self::CACHE_TTL );

		return $result;
	}

	/**
	 * Pulls MiniMax's own explanation out of an error response.
	 *
	 * @since 1.6.0
	 *
	 * @param array<string, mixed>|\WP_Error $response The HTTP response.
	 * @return string
	 */
	private static function describe( $response ): string {
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_array( $body ) ) {
			$error = $body['error'] ?? null;

			if ( is_array( $error ) ) {
				$message = $error['message'] ?? null;

				if ( is_string( $message ) && '' !== $message ) {
					return $message;
				}
			}
		}

		$message = wp_remote_retrieve_response_message( $response );

		return '' !== $message
			? $message
			: __( 'no explanation given', 'alamin-ai-provider-for-minimax' );
	}
}
