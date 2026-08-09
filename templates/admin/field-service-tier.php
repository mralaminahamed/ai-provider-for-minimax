<?php
/**
 * Service tier field template.
 *
 * @package MiniMax\MiniMaxAiProvider\Settings
 *
 * @var string $option_key The settings option key.
 * @var string $value      Current service tier.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<select name="<?php echo esc_attr( $option_key ); ?>[service_tier]" id="minimax_service_tier">
	<option value="standard" <?php selected( $value, 'standard' ); ?>>
		<?php echo esc_html__( 'Standard', 'alamin-ai-provider-for-minimax' ); ?>
	</option>
	<option value="priority" <?php selected( $value, 'priority' ); ?>>
		<?php echo esc_html__( 'Priority — faster, 1.5× the cost', 'alamin-ai-provider-for-minimax' ); ?>
	</option>
</select>
<p class="description">
	<?php
	echo esc_html__(
		'Priority routing costs 1.5 times standard for every request this site makes. Worth it for a visitor waiting on a response; wasteful for a scheduled job nobody is watching.',
		'alamin-ai-provider-for-minimax'
	);
	?>
</p>
