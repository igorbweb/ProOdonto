<?php
/**
 * Render server-side do bloco proodonto/hero.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_hero( $attributes ) {
	$title        = isset( $attributes['title'] ) ? $attributes['title'] : '';
	$subtitle     = isset( $attributes['subtitle'] ) ? $attributes['subtitle'] : '';
	$button_label = isset( $attributes['buttonLabel'] ) ? $attributes['buttonLabel'] : '';
	$button_url   = isset( $attributes['buttonUrl'] ) ? $attributes['buttonUrl'] : '';
	$button_blank = ! empty( $attributes['buttonBlank'] );
	$image_id     = ! empty( $attributes['imageId'] ) ? (int) $attributes['imageId'] : 0;
	$image_url    = isset( $attributes['imageUrl'] ) ? $attributes['imageUrl'] : '';
	$image_alt    = isset( $attributes['imageAlt'] ) ? $attributes['imageAlt'] : '';

	if ( ! $title && ! $image_url ) {
		return '';
	}

	ob_start();
	?>
	<section class="hero">

		<?php if ( $image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'proodonto-hero',
				false,
				array(
					'class'         => 'hero__image',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'alt'           => $image_alt,
				)
			);
			?>
		<?php elseif ( $image_url ) : ?>
			<img
				class="hero__image"
				src="<?php echo esc_url( $image_url ); ?>"
				alt="<?php echo esc_attr( $image_alt ); ?>"
				loading="eager"
				fetchpriority="high"
			/>
		<?php endif; ?>

		<div class="container hero__content">

			<?php if ( $title ) : ?>
				<h1 class="hero__title"><?php echo wp_kses_post( $title ); ?></h1>
			<?php endif; ?>

			<?php if ( $subtitle ) : ?>
				<p class="hero__subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
			<?php endif; ?>

			<?php if ( $button_label && $button_url ) : ?>
				<a
					class="hero__button button"
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
