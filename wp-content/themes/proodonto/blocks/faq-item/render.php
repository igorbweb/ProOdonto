<?php
/**
 * Render server-side do bloco proodonto/faq-item.
 *
 * Usa <details>/<summary> nativos do HTML: accordion funcional sem
 * nenhuma linha de JavaScript.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_faq_item( $attributes ) {
	$question = isset( $attributes['question'] ) ? $attributes['question'] : '';
	$answer   = isset( $attributes['answer'] ) ? $attributes['answer'] : '';

	if ( ! $question ) {
		return '';
	}

	ob_start();
	?>
	<details class="faq-item">
		<summary class="faq-item__question"><?php echo wp_kses_post( $question ); ?></summary>
		<div class="faq-item__answer"><?php echo wp_kses_post( $answer ); ?></div>
	</details>
	<?php
	return ob_get_clean();
}
