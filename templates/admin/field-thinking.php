<?php
/**
 * Thinking mode field template.
 *
 * @package MiniMax\MiniMaxAiProvider\Settings
 *
 * @var string $option_key The settings option key.
 * @var string $value      Current thinking mode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<select name="<?php echo esc_attr( $option_key ); ?>[thinking]" id="minimax_thinking">
	<option value="adaptive" <?php selected( $value, 'adaptive' ); ?>>
		<?php echo esc_html__( 'Adaptive — let the model decide', 'alamin-ai-provider-for-minimax' ); ?>
	</option>
	<option value="disabled" <?php selected( $value, 'disabled' ); ?>>
		<?php echo esc_html__( 'Disabled — answer without reasoning first', 'alamin-ai-provider-for-minimax' ); ?>
	</option>
</select>
<p class="description">
	<?php
	echo esc_html__(
		'Reasoning before answering. Disabling it is faster and cheaper, and weaker on anything multi-step. Only MiniMax-M3 can be switched off; the M2 series always reasons and ignores this.',
		'alamin-ai-provider-for-minimax'
	);
	?>
</p>
