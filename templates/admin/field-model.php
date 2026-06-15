<?php
/**
 * Default model field template.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Settings
 *
 * @var string                                                            $option_key     The settings option key.
 * @var \AlAminAhamed\MiniMaxAiProvider\Metadata\MiniMaxModelMetadata[]  $models         List of model metadata objects.
 * @var string                                                            $selected_model Currently selected model ID.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<select name="<?php echo esc_attr( $option_key ); ?>[default_model]" id="minimax_default_model">
	<option value=""><?php echo esc_html__( 'Select a model', 'alamin-ai-provider-for-minimax' ); ?></option>
	<?php foreach ( $models as $model ) : ?>
		<option value="<?php echo esc_attr( $model->getId() ); ?>" <?php selected( $selected_model, $model->getId() ); ?>>
			<?php echo esc_html( $model->getName() ); ?>
		</option>
	<?php endforeach; ?>
</select>
<p class="description"><?php echo esc_html__( 'The default model to use for text generation.', 'alamin-ai-provider-for-minimax' ); ?></p>
