<?php
/**
 * Frequency penalty field template.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Settings
 *
 * @var string $option_key The settings option key.
 * @var float  $value      Current frequency_penalty value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="number" step="0.1" min="-2" max="2"
	name="<?php echo esc_attr( $option_key ); ?>[frequency_penalty]"
	id="minimax_frequency_penalty"
	value="<?php echo esc_attr( (string) $value ); ?>"
	class="small-text" />
<p class="description"><?php echo esc_html__( 'Penalizes tokens based on their frequency in the output so far. Range: -2 to 2.', 'alamin-ai-provider-for-minimax' ); ?></p>
