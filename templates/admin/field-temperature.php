<?php
/**
 * Temperature field template.
 *
 * @package MiniMax\MiniMaxAiProvider\Settings
 *
 * @var string $option_key The settings option key.
 * @var float  $value      Current temperature value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="number" step="0.1" min="0" max="2"
	name="<?php echo esc_attr( $option_key ); ?>[temperature]"
	id="minimax_temperature"
	value="<?php echo esc_attr( (string) $value ); ?>"
	class="small-text" />
<p class="description"><?php echo esc_html__( 'Controls randomness. Lower values make output more focused. Range: 0-2.', 'alamin-ai-provider-for-minimax' ); ?></p>
