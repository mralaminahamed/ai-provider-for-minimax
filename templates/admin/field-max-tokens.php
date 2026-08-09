<?php
/**
 * Max tokens field template.
 *
 * @package MiniMax\MiniMaxAiProvider\Settings
 *
 * @var string $option_key The settings option key.
 * @var int    $value      Current max tokens value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="number" step="1" min="1" max="200000"
	name="<?php echo esc_attr( $option_key ); ?>[max_tokens]"
	id="minimax_max_tokens"
	value="<?php echo esc_attr( (string) $value ); ?>"
	class="small-text" />
<p class="description"><?php echo esc_html__( 'Maximum number of tokens to generate.', 'alamin-ai-provider-for-minimax' ); ?></p>
