<?php
/**
 * Tests for ConnectionTest.
 *
 * @package MiniMax\Tests\Connection
 */

declare(strict_types=1);

namespace MiniMax\Tests\Connection;

use Brain\Monkey;
use Brain\Monkey\Functions;
use MiniMax\MiniMaxAiProvider\Connection\ConnectionTest;
use PHPUnit\Framework\TestCase;
use WP_Error;

/**
 * A key being present is not the same as a key that works.
 *
 * @since 1.6.0
 */
class ConnectionTestTest extends TestCase {

	/**
	 * The last value handed to set_transient().
	 *
	 * @var mixed
	 */
	private $stored;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->stored = null;

		Functions\when( 'get_option' )->justReturn( '' );
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'delete_transient' )->justReturn( true );
		Functions\when( 'set_transient' )->alias(
			function ( $key, $value ) {
				$this->stored = $value;
				return true;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Stubs a configured key.
	 *
	 * @return void
	 */
	private function with_a_key(): void {
		Functions\when( 'get_option' )->alias(
			static function ( $name ) {
				return 'connectors_ai_minimax_api_key' === $name ? 'a-key' : '';
			}
		);
	}

	/**
	 * Stubs a key and an HTTP answer, then runs the test.
	 *
	 * @param int    $status HTTP status code to answer with.
	 * @param string $body   Response body.
	 * @return array{status: string, message: string}
	 */
	private function run_with( int $status, string $body = '' ): array {
		$this->with_a_key();

		Functions\when( 'wp_remote_get' )->justReturn( array( 'body' => $body ) );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( $status );
		Functions\when( 'wp_remote_retrieve_response_message' )->justReturn( '' );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( $body );

		return ConnectionTest::run();
	}

	/**
	 * A 200 from the models endpoint means the key works.
	 *
	 * @return void
	 */
	public function test_a_working_key_reads_as_valid(): void {
		$this->assertSame( ConnectionTest::VALID, $this->run_with( 200, '{"data":[]}' )['status'] );
	}

	/**
	 * A 401 means MiniMax rejected the key, and says so in MiniMax's words.
	 *
	 * @return void
	 */
	public function test_a_rejected_key_reads_as_invalid(): void {
		$result = $this->run_with(
			401,
			'{"error":{"message":"login fail: Please carry the API secret key"}}'
		);

		$this->assertSame( ConnectionTest::INVALID, $result['status'] );
		$this->assertStringContainsString( 'login fail', $result['message'] );
	}

	/**
	 * A network failure is not evidence about the key.
	 *
	 * Reporting "invalid" here would send someone to reissue a key that works,
	 * because their site is behind a firewall.
	 *
	 * @return void
	 */
	public function test_an_unreachable_api_is_unknown_not_invalid(): void {
		$this->with_a_key();

		Functions\when( 'wp_remote_get' )->justReturn( new WP_Error( 'http_request_failed', 'Connection timed out' ) );
		Functions\when( 'is_wp_error' )->justReturn( true );

		$result = ConnectionTest::run();

		$this->assertSame( ConnectionTest::UNKNOWN, $result['status'] );
		$this->assertStringContainsString( 'Connection timed out', $result['message'] );
	}

	/**
	 * A 500 says nothing about the key either.
	 *
	 * @return void
	 */
	public function test_a_server_error_is_unknown_not_invalid(): void {
		$this->assertSame( ConnectionTest::UNKNOWN, $this->run_with( 500, '' )['status'] );
	}

	/**
	 * With no key at all there is nothing to ask MiniMax about.
	 *
	 * @return void
	 */
	public function test_no_key_reads_as_invalid_without_a_request(): void {
		Functions\expect( 'wp_remote_get' )->never();

		$this->assertSame( ConnectionTest::INVALID, ConnectionTest::run()['status'] );
	}

	/**
	 * The verdict is cached, so the settings page does not re-ask on every load.
	 *
	 * @return void
	 */
	public function test_the_verdict_is_stored(): void {
		$this->run_with( 200, '{"data":[]}' );

		$this->assertIsArray( $this->stored );
		$this->assertSame( ConnectionTest::VALID, $this->stored['status'] );
	}

	/**
	 * A stored verdict in the wrong shape is ignored rather than rendered.
	 *
	 * @return void
	 */
	public function test_a_malformed_cached_verdict_is_ignored(): void {
		Functions\when( 'get_transient' )->justReturn( array( 'status' => 123 ) );

		$this->assertNull( ConnectionTest::last_result() );
	}
}
