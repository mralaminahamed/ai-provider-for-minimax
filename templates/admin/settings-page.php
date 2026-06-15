<?php
/**
 * Settings page template.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Settings
 *
 * @var string $option_key The settings option key.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'MiniMax Settings', 'alamin-ai-provider-for-minimax' ); ?></h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( $option_key );
		do_settings_sections( 'minimax-settings' );
		submit_button();
		?>
	</form>
</div>
