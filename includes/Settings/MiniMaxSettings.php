<?php
/**
 * MiniMax Settings.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Settings
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Settings;

use AlAminAhamed\MiniMaxAiProvider\Metadata\MiniMaxModelMetadataDirectory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MiniMaxSettings
 *
 * @since 1.0.0
 */
class MiniMaxSettings {

	/**
	 * Option key for settings.
	 *
	 * @since 1.0.0
	 */
	public const OPTION_KEY = 'minimax_settings';

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
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=minimax-settings' ) ) . '">' . esc_html__( 'Settings', 'alamin-ai-provider-for-minimax' ) . '</a>';
		array_unshift( $links, $settings_link );
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
			'presence_penalty',
			__( 'Presence Penalty', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_presence_penalty_field' ),
			'minimax-settings',
			'minimax_general'
		);

		add_settings_field(
			'frequency_penalty',
			__( 'Frequency Penalty', 'alamin-ai-provider-for-minimax' ),
			array( self::class, 'render_frequency_penalty_field' ),
			'minimax-settings',
			'minimax_general'
		);
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

		$default_model              = $input['default_model'] ?? '';
		$sanitized['default_model'] = sanitize_text_field( is_string( $default_model ) ? $default_model : '' );

		$temperature_raw          = $input['temperature'] ?? 0.7;
		$sanitized['temperature'] = is_numeric( $temperature_raw ) ? (float) $temperature_raw : 0.7;

		$max_tokens_raw          = $input['max_tokens'] ?? 4096;
		$sanitized['max_tokens'] = is_numeric( $max_tokens_raw ) ? (int) $max_tokens_raw : 4096;

		$top_p_raw          = $input['top_p'] ?? 1.0;
		$sanitized['top_p'] = is_numeric( $top_p_raw ) ? (float) $top_p_raw : 1.0;

		$presence_penalty_raw          = $input['presence_penalty'] ?? 0.0;
		$sanitized['presence_penalty'] = is_numeric( $presence_penalty_raw ) ? (float) $presence_penalty_raw : 0.0;

		$frequency_penalty_raw          = $input['frequency_penalty'] ?? 0.0;
		$sanitized['frequency_penalty'] = is_numeric( $frequency_penalty_raw ) ? (float) $frequency_penalty_raw : 0.0;

		$sanitized['temperature']       = max( 0, min( 2, $sanitized['temperature'] ) );
		$sanitized['max_tokens']        = max( 1, min( 200000, $sanitized['max_tokens'] ) );
		$sanitized['top_p']             = max( 0, min( 1, $sanitized['top_p'] ) );
		$sanitized['presence_penalty']  = max( -2, min( 2, $sanitized['presence_penalty'] ) );
		$sanitized['frequency_penalty'] = max( -2, min( 2, $sanitized['frequency_penalty'] ) );

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
		$settings       = self::get_settings();
		$directory      = new MiniMaxModelMetadataDirectory();
		$models         = $directory->listModelMetadata();
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
	 * Render presence penalty field.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public static function render_presence_penalty_field(): void {
		$settings   = self::get_settings();
		$raw        = $settings['presence_penalty'] ?? 0.0;
		$value      = is_numeric( $raw ) ? (float) $raw : 0.0;
		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-presence-penalty.php';
	}

	/**
	 * Render frequency penalty field.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public static function render_frequency_penalty_field(): void {
		$settings   = self::get_settings();
		$raw        = $settings['frequency_penalty'] ?? 0.0;
		$value      = is_numeric( $raw ) ? (float) $raw : 0.0;
		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/field-frequency-penalty.php';
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

		$option_key = self::OPTION_KEY;

		require dirname( MINIMAX_PLUGIN_FILE ) . '/templates/admin/settings-page.php';
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
			'default_model'     => '',
			'temperature'       => 0.7,
			'max_tokens'        => 4096,
			'top_p'             => 1.0,
			'presence_penalty'  => 0.0,
			'frequency_penalty' => 0.0,
		);

		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			return $defaults;
		}

		return array(
			'default_model'     => isset( $saved['default_model'] ) && is_string( $saved['default_model'] )
				? $saved['default_model']
				: '',
			'temperature'       => isset( $saved['temperature'] ) && is_numeric( $saved['temperature'] )
				? (float) $saved['temperature']
				: $defaults['temperature'],
			'max_tokens'        => isset( $saved['max_tokens'] ) && is_int( $saved['max_tokens'] )
				? $saved['max_tokens']
				: $defaults['max_tokens'],
			'top_p'             => isset( $saved['top_p'] ) && is_numeric( $saved['top_p'] )
				? (float) $saved['top_p']
				: 1.0,
			'presence_penalty'  => isset( $saved['presence_penalty'] ) && is_numeric( $saved['presence_penalty'] )
				? (float) $saved['presence_penalty']
				: 0.0,
			'frequency_penalty' => isset( $saved['frequency_penalty'] ) && is_numeric( $saved['frequency_penalty'] )
				? (float) $saved['frequency_penalty']
				: 0.0,
		);
	}
}
