<?php
/**
 * Render server-side do bloco proodonto/service-item.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_service_item( $attributes ) {
	$title       = isset( $attributes['title'] ) ? $attributes['title'] : '';
	$description = isset( $attributes['description'] ) ? $attributes['description'] : '';
	$link_label  = isset( $attributes['linkLabel'] ) ? $attributes['linkLabel'] : '';
	$link_url    = isset( $attributes['linkUrl'] ) ? $attributes['linkUrl'] : '';
	$icon_id     = ! empty( $attributes['iconId'] ) ? (int) $attributes['iconId'] : 0;
	$icon_url    = isset( $attributes['iconUrl'] ) ? $attributes['iconUrl'] : '';
	$icon_alt    = isset( $attributes['iconAlt'] ) ? $attributes['iconAlt'] : '';

	if ( ! $title ) {
		return '';
	}

	ob_start();
	?>
	<article class="service-item">

		<?php if ( $icon_id ) : ?>
			<?php echo wp_get_attachment_image( $icon_id, array( 64, 64 ), false, array( 'class' => 'service-item__icon', 'loading' => 'lazy', 'alt' => $icon_alt ) ); ?>
		<?php elseif ( $icon_url ) : ?>
			<img class="service-item__icon" src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $icon_alt ); ?>" width="64" height="64" loading="lazy" />
		<?php endif; ?>

		<h3 class="service-item__title"><?php echo wp_kses_post( $title ); ?></h3>

		<?php if ( $description ) : ?>
			<p class="service-item__description"><?php echo wp_kses_post( $description ); ?></p>
		<?php endif; ?>

		<?php if ( $link_label && $link_url ) : ?>
			<a class="service-item__link" href="<?php echo esc_url( $link_url ); ?>">
				<?php echo esc_html( $link_label ); ?>
			</a>
		<?php endif; ?>

	</article>
	<?php
	return ob_get_clean();
}
