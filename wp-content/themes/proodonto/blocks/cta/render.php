<?php
/**
 * Render server-side do bloco proodonto/cta.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_cta( $attributes ) {
	$title       = isset( $attributes['title'] ) ? $attributes['title'] : '';
	$text        = isset( $attributes['text'] ) ? $attributes['text'] : '';
	$button_label = isset( $attributes['buttonLabel'] ) ? $attributes['buttonLabel'] : '';
	$button_url  = isset( $attributes['buttonUrl'] ) ? $attributes['buttonUrl'] : '';
	$button_blank = ! empty( $attributes['buttonBlank'] );

	if ( ! $title && ! $button_label ) {
		return '';
	}

	ob_start();
	?>
	<section class="cta">
		<div class="container cta__inner">

			<?php if ( $title ) : ?>
				<h2 class="cta__title"><?php echo wp_kses_post( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( $text ) : ?>
				<p class="cta__text"><?php echo wp_kses_post( $text ); ?></p>
			<?php endif; ?>

			<?php if ( $button_label && $button_url ) : ?>
				<a
					class="cta__button button"
					href="<?php echo esc_url( $button_url ); ?>"
					<?php echo $button_blank ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
				>
					<?php echo esc_html( $button_label ); ?>
				</a>
			<?php endif; ?>

		</div>
	</section>
	<?php
	return ob_get_clean();
}
