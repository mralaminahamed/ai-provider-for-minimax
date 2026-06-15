<?php
/**
 * Top P field template.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Settings
 *
 * @var string $option_key The settings option key.
 * @var float  $value      Current top_p value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="number" step="0.01" min="0" max="1"
	name="<?php echo esc_attr( $option_key ); ?>[top_p]"
	id="minimax_top_p"
	value="<?php echo esc_attr( (string) $value ); ?>"
	class="small-text" />
<p class="description"><?php echo esc_html__( 'Nucleus sampling threshold. 1.0 disables top-p sampling. Range: 0-1.', 'alamin-ai-provider-for-minimax' ); ?></p>
