<?php
/**
 * Render server-side do bloco proodonto/faq.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_faq( $attributes, $content ) {
	$heading = isset( $attributes['heading'] ) ? $attributes['heading'] : '';

	if ( ! trim( wp_strip_all_tags( $content ) ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="faq">
		<div class="container">

			<?php if ( $heading ) : ?>
				<h2 class="faq__heading"><?php echo wp_kses_post( $heading ); ?></h2>
			<?php endif; ?>

			<div class="faq__list">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- innerBlocks já renderizados/escapados individualmente. ?>
			</div>

		</div>
	</section>
	<?php
	return ob_get_clean();
}
