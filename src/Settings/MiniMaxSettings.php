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

		$sanitized['temperature'] = max( 0, min( 2, $sanitized['temperature'] ) );
		$sanitized['max_tokens']  = max( 1, min( 200000, $sanitized['max_tokens'] ) );

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
		echo '<p>' . esc_html__( 'Configure default settings for the MiniMax AI provider.', 'alamin-ai-provider-for-minimax' ) . '</p>';
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
		$directory = new MiniMaxModelMetadataDirectory();
		$models    = $directory->listModelMetadata();

		echo '<select name="' . esc_attr( self::OPTION_KEY ) . '[default_model]" id="minimax_default_model">';
		echo '<option value="">' . esc_html__( 'Select a model', 'alamin-ai-provider-for-minimax' ) . '</option>';

		foreach ( $models as $model ) {
			$selected = selected( $settings['default_model'] ?? '', $model->getId(), false );
			echo '<option value="' . esc_attr( $model->getId() ) . '" ' . $selected . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo esc_html( $model->getName() );
			echo '</option>';
		}

		echo '</select>';
		echo '<p class="description">' . esc_html__( 'The default model to use for text generation.', 'alamin-ai-provider-for-minimax' ) . '</p>';
	}

	/**
	 * Render temperature field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function render_temperature_field(): void {
		$settings = self::get_settings();
		$temp_raw = $settings['temperature'] ?? 0.7;
		$value    = is_numeric( $temp_raw ) ? (float) $temp_raw : 0.7;

		echo '<input type="number" step="0.1" min="0" max="2"';
		echo ' name="' . esc_attr( self::OPTION_KEY ) . '[temperature]"';
		echo ' id="minimax_temperature"';
		echo ' value="' . esc_attr( (string) $value ) . '"';
		echo ' class="small-text" />';
		echo '<p class="description">' . esc_html__( 'Controls randomness. Lower values make output more focused. Range: 0-2.', 'alamin-ai-provider-for-minimax' ) . '</p>';
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

		echo '<input type="number" step="1" min="1" max="200000"';
		echo ' name="' . esc_attr( self::OPTION_KEY ) . '[max_tokens]"';
		echo ' id="minimax_max_tokens"';
		echo ' value="' . esc_attr( (string) $value ) . '"';
		echo ' class="small-text" />';
		echo '<p class="description">' . esc_html__( 'Maximum number of tokens to generate.', 'alamin-ai-provider-for-minimax' ) . '</p>';
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

		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'MiniMax Settings', 'alamin-ai-provider-for-minimax' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_KEY );
				do_settings_sections( 'minimax-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
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
			'temperature' => 0.7,
			'max_tokens'  => 4096,
		);

		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			return $defaults;
		}

		return array(
			'default_model' => isset( $saved['default_model'] ) && is_string( $saved['default_model'] )
				? $saved['default_model']
				: '',
			'temperature'   => isset( $saved['temperature'] ) && is_numeric( $saved['temperature'] )
				? (float) $saved['temperature']
				: $defaults['temperature'],
			'max_tokens'    => isset( $saved['max_tokens'] ) && is_int( $saved['max_tokens'] )
				? $saved['max_tokens']
				: $defaults['max_tokens'],
		);
	}
}
