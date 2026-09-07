<?php
/**
 * MiniMax Settings.
 *
 * @package MiniMax\MiniMaxAiProvider\Settings
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Settings;

use MiniMax\MiniMaxAiProvider\Connection\ConnectionTest;
use MiniMax\MiniMaxAiProvider\Metadata\ModelMetadataDirectory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Option key for settings.
	 *
	 * @since 1.0.0
	 */
	public const OPTION_KEY = 'minimax_settings';

	/**
	 * The `admin-post.php` action behind the connection test.
	 *
	 * @since 1.6.0
	 */
	public const TEST_ACTION = 'minimax_test_connection';

	/**
	 * Default model used when none has been chosen.
	 *
	 * Matches the flagship entry in ModelMetadataDirectory's built-in
	 * list, so it is always a valid selection even before the API is reachable.
	 *
	 * @since 1.3.2
	 */
	public const DEFAULT_MODEL = 'MiniMax-M3';

	/**
	 * Initialize settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'add_settings_page' ) );
		add_action( 'admin_init', array( self::class, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MINIMAX_PLUGIN_FILE ), array( self::class, 'add_action_links' ) );

		add_action( 'admin_post_' . self::TEST_ACTION, array( self::class, 'handle_test_connection' ) );

		/*
		 * A cached verdict outlives the key it was about. Both places a key can
		 * be stored are watched, so changing one drops the old answer rather
		 * than leaving a stale "connected" on the screen.
		 */
		add_action( 'update_option_wp_ai_client_credentials', array( ConnectionTest::class, 'forget' ) );
		add_action( 'update_option_connectors_ai_minimax_api_key', array( ConnectionTest::class, 'forget' ) );
	}

	/**
	 * Runs the connection test and returns to the settings page.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public static function handle_test_connection(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to test this connection.', 'alamin-ai-provider-for-minimax' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::TEST_ACTION );

		ConnectionTest::run();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'           => 'minimax-settings',
					'minimax-tested' => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int|string, string> $links Existing action links.
	 * @return array<int|string, string>
	 */
	public static function add_action_links( array $links ): array {
		$settings_link   = '<a href="' . esc_url( admin_url( 'options-general.php?page=minimax-settings' ) ) . '">' . esc_html__( 'Settings', 'alamin-ai-provider-for-minimax' ) . '</a>';
		$connectors_link = '<a href="' . esc_url( admin_url( 'options-connectors.php' ) ) . '">' . esc_html__( 'Connectors', 'alamin-ai-provider-for-minimax' ) . '</a>';
		array_unshift( $links, $settings_link, $connectors_link );
		return $links;
	}

	/**
	 * Add settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function add_settings_page(): void {
		add_options_page(
			__( 'MiniMax Settings', 'alamin-ai-provider-for-minimax' ),
			__( 'MiniMax', 'alamin-ai-provider-for-minimax' ),
			'manage_options',
			'minimax-settings',
			array( self::class, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			self::OPTION_KEY,
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( self::class, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'minimax_general',
			__( 'General Settings', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_general_section' ),
			'minimax-settings'
		);

		add_settings_field(
			'default_model',
			__( 'Default Model', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_model_field' ),
			'minimax-settings',
			'minimax_general'
		);

		add_settings_field(
			'temperature',
			__( 'Temperature', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_temperature_field' ),
			'minimax-settings',
			'minimax_general'
		);

		add_settings_field(
			'max_tokens',
			__( 'Max Tokens', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_max_tokens_field' ),
			'minimax-settings',
			'minimax_general'
		);

		add_settings_field(
			'top_p',
			__( 'Top P', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_top_p_field' ),
			'minimax-settings',
			'minimax_general'
		);

		add_settings_field(
			'thinking',
			__( 'Thinking', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_thinking_field' ),
			'minimax-settings',
			'minimax_general'
		);

		add_settings_field(
			'service_tier',
			__( 'Service Tier', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_service_tier_field' ),
			'minimax-settings',
			'minimax_general'
		);

		/*
		 * No presence or frequency penalty field.
		 *
		 * MiniMax documents both as ignored on the OpenAI-compatible endpoint,
		 * so the fields offered a setting that could not affect anything. A
		 * control that does nothing is worse than a missing one: it invites the
		 * user to tune it and then blame the model for not responding.
		 *
		 * @link https://platform.minimax.io/docs/api-reference/text-openai-api
		 */
	}

	/**
	 * Sanitize settings.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input Settings input.
	 * @return array<string, mixed>
	 */
	public static function sanitize_settings( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$sanitized = array();

		$default_model_raw          = $input['default_model'] ?? '';
		$default_model              = sanitize_text_field( is_string( $default_model_raw ) ? $default_model_raw : '' );
		$sanitized['default_model'] = '' !== $default_model ? $default_model : self::DEFAULT_MODEL;

		$temperature_raw          = $input['temperature'] ?? 0.7;
		$sanitized['temperature'] = is_numeric( $temperature_raw ) ? (float) $temperature_raw : 0.7;

		$max_tokens_raw          = $input['max_tokens'] ?? 4096;
		$sanitized['max_tokens'] = is_numeric( $max_tokens_raw ) ? (int) $max_tokens_raw : 4096;

		$top_p_raw          = $input['top_p'] ?? 1.0;
		$sanitized['top_p'] = is_numeric( $top_p_raw ) ? (float) $top_p_raw : 1.0;

		$thinking_raw          = $input['thinking'] ?? 'adaptive';
		$thinking              = is_string( $thinking_raw ) ? $thinking_raw : 'adaptive';
		$sanitized['thinking'] = in_array( $thinking, array( 'adaptive', 'disabled' ), true ) ? $thinking : 'adaptive';

		$service_tier_raw          = $input['service_tier'] ?? 'standard';
		$service_tier              = is_string( $service_tier_raw ) ? $service_tier_raw : 'standard';
		$sanitized['service_tier'] = in_array( $service_tier, array( 'standard', 'priority' ), true ) ? $service_tier : 'standard';

		/*
		 * Ranges are MiniMax's own, not OpenAI's: temperature is [0, 2] and
		 * top_p is [0, 1]. The upper bound on max_tokens covers M3's
		 * 1,000,000-token context; the M2.x models top out at 204,800 and the
		 * API is left to reject anything beyond what the chosen model allows.
		 */
		$sanitized['temperature'] = max( 0, min( 2, $sanitized['temperature'] ) );
		$sanitized['max_tokens']  = max( 1, min( 1000000, $sanitized['max_tokens'] ) );
		$sanitized['top_p']       = max( 0, min( 1, $sanitized['top_p'] ) );

		return $sanitized;
	}

	/**
	 * Render general section description.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function render_general_section(): void {
		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/section-general.php';
	}

	/**
	 * Render model field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function render_model_field(): void {
		$settings  = self::get_settings();
		$directory = new ModelMetadataDirectory();

		/*
		 * The field is labelled "default model for text generation", so it
		 * lists text models. It listed everything, which already offered
		 * `image-01` as a text model and would now offer eight speech models
		 * too.
		 */
		$models = array_values(
			array_filter(
				$directory->listModelMetadata(),
				static function ( $model ): bool {
					foreach ( $model->getSupportedCapabilities() as $capability ) {
						if ( $capability->isTextGeneration() ) {
							return true;
						}
					}

					return false;
				}
			)
		);

		$selected_model = $settings['default_model'] ?? '';
		$option_key     = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-model.php';
	}

	/**
	 * Render temperature field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function render_temperature_field(): void {
		$settings   = self::get_settings();
		$temp_raw   = $settings['temperature'] ?? 0.7;
		$value      = is_numeric( $temp_raw ) ? (float) $temp_raw : 0.7;
		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-temperature.php';
	}

	/**
	 * Render max tokens field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function render_max_tokens_field(): void {
		$settings   = self::get_settings();
		$tokens_raw = $settings['max_tokens'] ?? 4096;
		$value      = is_int( $tokens_raw ) ? $tokens_raw : 4096;
		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-max-tokens.php';
	}

	/**
	 * Render top p field.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public static function render_top_p_field(): void {
		$settings   = self::get_settings();
		$raw        = $settings['top_p'] ?? 1.0;
		$value      = is_numeric( $raw ) ? (float) $raw : 1.0;
		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-top-p.php';
	}

	/**
	 * Render thinking field.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public static function render_thinking_field(): void {
		$settings   = self::get_settings();
		$raw        = $settings['thinking'] ?? 'adaptive';
		$value      = is_string( $raw ) ? $raw : 'adaptive';
		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-thinking.php';
	}

	/**
	 * Render service tier field.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public static function render_service_tier_field(): void {
		$settings   = self::get_settings();
		$raw        = $settings['service_tier'] ?? 'standard';
		$value      = is_string( $raw ) ? $raw : 'standard';
		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-service-tier.php';
	}

	/**
	 * Render settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$option_key     = self::OPTION_KEY;
		$is_connected   = self::has_api_key();
		$connectors_url = admin_url( 'options-connectors.php' );
		$test_action    = self::TEST_ACTION;
		$test_result    = ConnectionTest::last_result();

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/settings-page.php';
	}

	/**
	 * Whether a MiniMax API key is configured.
	 *
	 * Checks the same sources as the AI Client credential filter: the
	 * MINIMAX_API_KEY environment variable, the WordPress Connectors page option
	 * (WP 7.0+), and the legacy nested wp_ai_client_credentials option.
	 *
	 * @since 1.3.2
	 *
	 * @return bool True when an API key is present in any supported location.
	 */
	public static function has_api_key(): bool {
		return '' !== self::get_api_key();
	}

	/**
	 * The configured MiniMax API key, or an empty string.
	 *
	 * Checks the same sources as the AI Client credential filter, in the order
	 * a site owner would expect them to win: an environment variable set by the
	 * server, then the WordPress Connectors page option (WP 7.0+), then the
	 * legacy nested `wp_ai_client_credentials` option.
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 */
	public static function get_api_key(): string {
		$env_key = getenv( 'MINIMAX_API_KEY' );
		if ( is_string( $env_key ) && '' !== $env_key ) {
			return $env_key;
		}

		if ( ! function_exists( 'get_option' ) ) {
			return '';
		}

		$connectors_key = get_option( 'connectors_ai_minimax_api_key', '' );
		if ( is_string( $connectors_key ) && '' !== $connectors_key ) {
			return $connectors_key;
		}

		$option      = get_option( 'wp_ai_client_credentials', array() );
		$credentials = is_array( $option ) ? ( $option['minimax'] ?? array() ) : array();
		$key         = is_array( $credentials ) ? ( $credentials['api_key'] ?? '' ) : '';

		return is_string( $key ) ? $key : '';
	}

	/**
	 * Get settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$defaults = array(
			'default_model' => self::DEFAULT_MODEL,
			'temperature'   => 0.7,
			'max_tokens'    => 4096,
			'top_p'         => 1.0,
			'thinking'      => 'adaptive',
			'service_tier'  => 'standard',
		);

		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			return $defaults;
		}

		/*
		 * `presence_penalty` and `frequency_penalty` are deliberately not read
		 * back, even where an older version of this plugin saved them. MiniMax
		 * ignores both, so returning them would put values into the settings
		 * array that nothing may act on. The stored keys are harmless and are
		 * left in place rather than migrated away.
		 */
		return array(
			'default_model' => isset( $saved['default_model'] ) && is_string( $saved['default_model'] ) && '' !== $saved['default_model']
				? $saved['default_model']
				: self::DEFAULT_MODEL,
			'temperature'   => isset( $saved['temperature'] ) && is_numeric( $saved['temperature'] )
				? (float) $saved['temperature']
				: $defaults['temperature'],
			'max_tokens'    => isset( $saved['max_tokens'] ) && is_int( $saved['max_tokens'] )
				? $saved['max_tokens']
				: $defaults['max_tokens'],
			'top_p'         => isset( $saved['top_p'] ) && is_numeric( $saved['top_p'] )
				? (float) $saved['top_p']
				: 1.0,
			'thinking'      => isset( $saved['thinking'] ) && in_array( $saved['thinking'], array( 'adaptive', 'disabled' ), true )
				? $saved['thinking']
				: $defaults['thinking'],
			'service_tier'  => isset( $saved['service_tier'] ) && in_array( $saved['service_tier'], array( 'standard', 'priority' ), true )
				? $saved['service_tier']
				: $defaults['service_tier'],
		);
	}
}
