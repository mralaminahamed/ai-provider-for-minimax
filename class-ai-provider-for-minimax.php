<?php
/**
 * Main Plugin Class
 *
 * @package    MiniMax
 * @author     Al Amin Ahamed <me@alaminahamed.com>
 * @copyright  2026 Al Amin Ahamed
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

use MiniMax\MiniMaxAiProvider\Provider\Provider;
use MiniMax\MiniMaxAiProvider\Settings\Settings;
use WordPress\AiClient\AiClient;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class for AI Provider for MiniMax.
 *
 * The work is done by the classes under `includes/`; this exists to own the
 * wiring — which hooks fire, in what order, and what each one reaches for —
 * so that a reader can see the plugin's whole surface in one file.
 *
 * @since 1.5.0
 */
class AI_Provider_For_MiniMax {

	/**
	 * Singleton instance.
	 *
	 * @since 1.5.0
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Plugin version.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public string $version = MINIMAX_VERSION;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.5.0
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Register the plugin's hooks.
	 *
	 * Both `init` callbacks run at priority 5, ahead of the default 10, because
	 * anything that wants to generate text on `init` needs the provider to be
	 * in the registry before it asks.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_provider' ), 5 );
		add_action( 'init', array( $this, 'init_settings' ), 5 );

		add_filter( 'wpai_has_ai_credentials', array( $this, 'declare_credentials' ) );
		add_filter( 'wpai_pre_has_valid_credentials_check', array( $this, 'declare_valid_credentials' ) );
	}

	/**
	 * Register the MiniMax provider with the AI Client.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_provider(): void {
		if ( ! class_exists( AiClient::class ) ) {
			return;
		}

		$registry = AiClient::defaultRegistry();

		if ( $registry->hasProvider( Provider::class ) ) {
			return;
		}

		$registry->registerProvider( Provider::class );
	}

	/**
	 * Initialise the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_settings(): void {
		Settings::init();
	}

	/**
	 * Declare credential availability to the AI plugin.
	 *
	 * The AI plugin's `has_ai_credentials()` only checks connectors that store
	 * an API key as a flat option. MiniMax stores its key under the nested
	 * `wp_ai_client_credentials` option or the WP 7.0 Connectors page option,
	 * so without this filter a configured site is reported as unconfigured.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $has_credentials Current credential status.
	 * @return bool
	 */
	public function declare_credentials( bool $has_credentials ): bool {
		if ( $has_credentials ) {
			return true;
		}

		return Settings::has_api_key();
	}

	/**
	 * Short-circuit the validity check when a MiniMax key is configured.
	 *
	 * Returns `null` rather than `false` when no key is found: `null` means
	 * "no opinion, run the default check", and answering `false` here would
	 * veto every other provider's credentials too.
	 *
	 * @since 1.2.0
	 *
	 * @param bool|null $valid Current validity status; null means "use default check".
	 * @return bool|null
	 */
	public function declare_valid_credentials( $valid ) {
		if ( true === $valid ) {
			return true;
		}

		return $this->declare_credentials( false ) ? true : null;
	}
}
