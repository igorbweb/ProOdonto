<?php
/**
 * Render server-side do bloco proodonto/testimonial-item.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_testimonial_item( $attributes ) {
	$quote     = isset( $attributes['quote'] ) ? $attributes['quote'] : '';
	$name      = isset( $attributes['name'] ) ? $attributes['name'] : '';
	$role      = isset( $attributes['role'] ) ? $attributes['role'] : '';
	$avatar_id = ! empty( $attributes['avatarId'] ) ? (int) $attributes['avatarId'] : 0;
	$avatar_url = isset( $attributes['avatarUrl'] ) ? $attributes['avatarUrl'] : '';
	$avatar_alt = isset( $attributes['avatarAlt'] ) ? $attributes['avatarAlt'] : '';

	if ( ! $quote && ! $name ) {
		return '';
	}

	ob_start();
	?>
	<blockquote class="testimonial">

		<?php if ( $avatar_id ) : ?>
			<?php echo wp_get_attachment_image( $avatar_id, array( 64, 64 ), false, array( 'class' => 'testimonial__avatar', 'loading' => 'lazy', 'alt' => $avatar_alt ) ); ?>
		<?php elseif ( $avatar_url ) : ?>
			<img class="testimonial__avatar" src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $avatar_alt ); ?>" width="64" height="64" loading="lazy" />
		<?php endif; ?>

		<?php if ( $quote ) : ?>
			<p class="testimonial__quote">&ldquo;<?php echo wp_kses_post( $quote ); ?>&rdquo;</p>
		<?php endif; ?>

		<footer class="testimonial__footer">
			<?php if ( $name ) : ?>
				<cite class="testimonial__name"><?php echo wp_kses_post( $name ); ?></cite>
			<?php endif; ?>
			<?php if ( $role ) : ?>
				<span class="testimonial__role"><?php echo wp_kses_post( $role ); ?></span>
			<?php endif; ?>
		</footer>

	</blockquote>
	<?php
	return ob_get_clean();
}
