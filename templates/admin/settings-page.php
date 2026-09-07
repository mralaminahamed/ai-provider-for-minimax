<?php
/**
 * Settings page template.
 *
 * @package MiniMax\MiniMaxAiProvider\Settings
 *
 * @var string $option_key     The settings option key.
 * @var bool                                   $is_connected   Whether a MiniMax API key is configured.
 * @var string                                 $connectors_url URL of the WordPress Connectors settings screen.
 * @var string                                 $test_action    The admin-post action behind the connection test.
 * @var array{status: string, message: string}|null $test_result The last connection test result, if any.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'MiniMax Settings', 'alamin-ai-provider-for-minimax' ); ?></h1>

	<?php if ( $is_connected ) : ?>
		<div class="notice notice-info inline">
			<p>
				<?php echo esc_html__( 'An API key is configured. The defaults below apply to new text generation requests.', 'alamin-ai-provider-for-minimax' ); ?>
			</p>
			<p>
				<?php echo esc_html__( 'A key being present is not the same as a key that works — MiniMax has not been asked until you test it.', 'alamin-ai-provider-for-minimax' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( $test_action ); ?>">
				<?php wp_nonce_field( $test_action ); ?>
				<?php submit_button( __( 'Test connection', 'alamin-ai-provider-for-minimax' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>

		<?php if ( null !== $test_result ) : ?>
			<?php
			$notice_class = 'notice-warning';
			if ( 'valid' === $test_result['status'] ) {
				$notice_class = 'notice-success';
			} elseif ( 'invalid' === $test_result['status'] ) {
				$notice_class = 'notice-error';
			}
			?>
			<div class="notice <?php echo esc_attr( $notice_class ); ?> inline">
				<p><?php echo esc_html( $test_result['message'] ); ?></p>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: %s: URL of the WordPress Connectors settings screen. */
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
