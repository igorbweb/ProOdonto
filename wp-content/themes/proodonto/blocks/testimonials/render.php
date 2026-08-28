<?php
/**
 * Render server-side do bloco proodonto/testimonials.
 *
 * $content já vem com os blocos filhos (proodonto/testimonial-item)
 * renderizados pelo próprio WordPress — só precisamos envolver.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_testimonials( $attributes, $content ) {
	$heading = isset( $attributes['heading'] ) ? $attributes['heading'] : '';

	if ( ! trim( wp_strip_all_tags( $content ) ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="testimonials">
		<div class="container">

			<?php if ( $heading ) : ?>
				<h2 class="testimonials__heading"><?php echo wp_kses_post( $heading ); ?></h2>
			<?php endif; ?>

			<div class="testimonials__grid">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- innerBlocks já renderizados/escapados individualmente. ?>
			</div>

		</div>
	</section>
	<?php
	return ob_get_clean();
}
