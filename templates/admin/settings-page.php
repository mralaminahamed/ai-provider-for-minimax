<?php
/**
 * Settings page template.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Settings
 *
 * @var string $option_key     The settings option key.
 * @var bool   $is_connected   Whether a MiniMax API key is configured.
 * @var string $connectors_url URL of the WordPress Connectors settings screen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'MiniMax Settings', 'alamin-ai-provider-for-minimax' ); ?></h1>

	<?php if ( $is_connected ) : ?>
		<div class="notice notice-success inline">
			<p><?php echo esc_html__( 'MiniMax is connected — an API key is configured. The defaults below apply to new text generation requests.', 'alamin-ai-provider-for-minimax' ); ?></p>
		</div>
	<?php else : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				printf(
					/* translators: %s: URL of the WordPress Connectors settings screen. */
					wp_kses(
						__( 'No MiniMax API key found. Add your key on the <a href="%s">Connectors screen</a> to activate this provider.', 'alamin-ai-provider-for-minimax' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( $connectors_url )
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<form method="post" action="options.php">
		<?php
		settings_fields( $option_key );
		do_settings_sections( 'minimax-settings' );
		submit_button();
		?>
	</form>
</div>
