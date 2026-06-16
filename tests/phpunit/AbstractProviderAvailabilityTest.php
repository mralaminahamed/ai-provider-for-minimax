<?php
/**
 * Abstract base test for ProviderAvailability implementations.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;

/**
 * Class AbstractProviderAvailabilityTest
 *
 * @since 1.3.2
 */
abstract class AbstractProviderAvailabilityTest extends TestCase {

	/**
	 * @since 1.3.2
	 *
	 * @return string FQCN of the availability class under test.
	 */
	abstract protected function getAvailabilityClass(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Environment variable name, e.g. 'MINIMAX_API_KEY'.
	 */
	abstract protected function getEnvVarName(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string WordPress option key for the connectors API key.
	 */
	abstract protected function getConnectorsOptionKey(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return string Key used inside wp_ai_client_credentials, e.g. 'minimax'.
	 */
	abstract protected function getLegacyCredentialsKey(): string;

	/**
	 * @since 1.3.2
	 *
	 * @return ProviderAvailabilityInterface
	 */
	protected function createAvailability(): ProviderAvailabilityInterface {
		$class = $this->getAvailabilityClass();
		return new $class();
	}

	/**
	 * Set up Brain Monkey before each test.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->justReturn( array() );
	}

	/**
	 * Tear down Brain Monkey and clear env var after each test.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		putenv( $this->getEnvVarName() . '=' );
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test class implements ProviderAvailabilityInterface.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_implements_interface(): void {
		$this->assertInstanceOf( ProviderAvailabilityInterface::class, $this->createAvailability() );
	}

	/**
	 * Test isConfigured returns false when no API key source is present.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_by_default(): void {
		putenv( $this->getEnvVarName() . '=' );

		Functions\when( 'get_option' )->justReturn( '' );

		$this->assertFalse( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns true when ApiKeyRequestAuthentication has a non-empty key.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_true_with_api_key_auth(): void {
		$availability = $this->createAvailability();
		$availability->setRequestAuthentication( new ApiKeyRequestAuthentication( 'sk-test' ) );

		$this->assertTrue( $availability->isConfigured() );
	}

	/**
	 * Test isConfigured returns false when ApiKeyRequestAuthentication has an empty key.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_with_empty_api_key_auth(): void {
		$availability = $this->createAvailability();
		$availability->setRequestAuthentication( new ApiKeyRequestAuthentication( '' ) );

		$this->assertFalse( $availability->isConfigured() );
	}

	/**
	 * Test isConfigured returns true when ApiKeyRequestAuthentication has a whitespace-only key.
	 *
	 * PHP empty() treats non-empty strings (even whitespace) as truthy, so
	 * '   ' passes the !empty() check and isConfigured() returns true.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_with_whitespace_api_key_auth(): void {
		$availability = $this->createAvailability();
		$availability->setRequestAuthentication( new ApiKeyRequestAuthentication( '   ' ) );

		$this->assertTrue( $availability->isConfigured() );
	}

	/**
	 * Test isConfigured returns true when env var is set to a non-empty value.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_true_with_env_key(): void {
		putenv( $this->getEnvVarName() . '=sk-env' );

		$this->assertTrue( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns false when env var is empty.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_with_empty_env_key(): void {
		putenv( $this->getEnvVarName() . '=' );

		Functions\when( 'get_option' )->justReturn( '' );

		$this->assertFalse( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns true when connectors option has a non-empty key.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_true_with_connectors_option(): void {
		putenv( $this->getEnvVarName() . '=' );

		$connectors_key = $this->getConnectorsOptionKey();

		Functions\when( 'get_option' )->alias(
			static function ( string $option, $default = false ) use ( $connectors_key ) {
				if ( $connectors_key === $option ) {
					return 'key123';
				}
				return $default;
			}
		);

		$this->assertTrue( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns false when connectors option is empty.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_with_empty_connectors_option(): void {
		putenv( $this->getEnvVarName() . '=' );

		$connectors_key = $this->getConnectorsOptionKey();

		Functions\when( 'get_option' )->alias(
			static function ( string $option, $default = false ) use ( $connectors_key ) {
				if ( $connectors_key === $option ) {
					return '';
				}
				if ( 'wp_ai_client_credentials' === $option ) {
					return array();
				}
				return $default;
			}
		);

		$this->assertFalse( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns true when legacy wp_ai_client_credentials has a key.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_true_with_legacy_credentials(): void {
		putenv( $this->getEnvVarName() . '=' );

		$connectors_key = $this->getConnectorsOptionKey();
		$legacy_key     = $this->getLegacyCredentialsKey();

		Functions\when( 'get_option' )->alias(
			function ( string $option, $default = false ) use ( $connectors_key, $legacy_key ) {
				if ( $connectors_key === $option ) {
					return '';
				}
				if ( 'wp_ai_client_credentials' === $option ) {
					return array( $legacy_key => array( 'api_key' => 'mykey' ) );
				}
				return $default;
			}
		);

		$this->assertTrue( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns false when legacy credentials have an empty api_key.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_with_empty_legacy_key(): void {
		putenv( $this->getEnvVarName() . '=' );

		$connectors_key = $this->getConnectorsOptionKey();
		$legacy_key     = $this->getLegacyCredentialsKey();

		Functions\when( 'get_option' )->alias(
			function ( string $option, $default = false ) use ( $connectors_key, $legacy_key ) {
				if ( $connectors_key === $option ) {
					return '';
				}
				if ( 'wp_ai_client_credentials' === $option ) {
					return array( $legacy_key => array( 'api_key' => '' ) );
				}
				return $default;
			}
		);

		$this->assertFalse( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns false when wp_ai_client_credentials is not an array.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_with_non_array_legacy_credentials(): void {
		putenv( $this->getEnvVarName() . '=' );

		$connectors_key = $this->getConnectorsOptionKey();

		Functions\when( 'get_option' )->alias(
			static function ( string $option, $default = false ) use ( $connectors_key ) {
				if ( $connectors_key === $option ) {
					return '';
				}
				if ( 'wp_ai_client_credentials' === $option ) {
					return 'not-array';
				}
				return $default;
			}
		);

		$this->assertFalse( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test isConfigured returns false when all option sources return empty values.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_is_configured_false_when_all_options_empty(): void {
		putenv( $this->getEnvVarName() . '=' );

		Functions\when( 'get_option' )->justReturn( '' );

		$this->assertFalse( $this->createAvailability()->isConfigured() );
	}

	/**
	 * Test auth object with empty key returns false even when env var is set.
	 *
	 * Auth object has highest priority; an empty key short-circuits the env var check.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_api_key_auth_takes_priority_over_env(): void {
		$availability = $this->createAvailability();
		$availability->setRequestAuthentication( new ApiKeyRequestAuthentication( '' ) );

		putenv( $this->getEnvVarName() . '=should-not-be-reached' );

		$this->assertFalse( $availability->isConfigured() );
	}

	/**
	 * Test setRequestAuthentication does not throw for a valid auth object.
	 *
	 * @since 1.3.2
	 *
	 * @return void
	 */
	public function test_set_request_authentication_accepts_auth_object(): void {
		$this->expectNotToPerformAssertions();

		$this->createAvailability()->setRequestAuthentication(
			new ApiKeyRequestAuthentication( 'sk-any-value' )
		);
	}
}
