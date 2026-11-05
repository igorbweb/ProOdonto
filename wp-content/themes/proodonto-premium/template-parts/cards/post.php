<?php
/**
 * Card de artigo (blog, "Continue lendo", "Veja também").
 *
 * @var array $args { @type int $id, @type string $meta (texto da linha de cima), @type string $heading (h2|h3) }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_id = (int) ( $args['id'] ?? get_the_ID() );
if ( ! $proodonto_id ) {
	return;
}
$proodonto_cats = get_the_category( $proodonto_id );
$proodonto_meta = $args['meta'] ?? implode(
	' · ',
	array_filter(
		array(
			$proodonto_cats ? $proodonto_cats[0]->name : '',
			sprintf( __( '%d min', 'proodonto' ), proodonto_reading_time( $proodonto_id ) ),
		)
	)
);
$proodonto_tag = tag_escape( $args['heading'] ?? 'h3' );
?>
<article class="post-card">
	<a class="post-card__a card tap" href="<?php echo esc_url( get_permalink( $proodonto_id ) ); ?>">
		<?php echo proodonto_photo( get_post_thumbnail_id( $proodonto_id ), 'proodonto-card', 'post-card__photo', array( 'alt' => '', 'sizes' => '(min-width: 1024px) 400px, 100vw' ) ); // phpcs:ignore ?>
		<span class="post-card__body">
			<?php if ( $proodonto_meta ) : ?>
				<span class="post-card__meta"><?php echo esc_html( $proodonto_meta ); ?></span>
			<?php endif; ?>
			<<?php echo $proodonto_tag; // phpcs:ignore ?> class="post-card__t display"><?php echo esc_html( get_the_title( $proodonto_id ) ); ?></<?php echo $proodonto_tag; // phpcs:ignore ?>>
			<span class="link link--sm" aria-hidden="true"><?php esc_html_e( 'Ler artigo', 'proodonto' ); ?><?php echo proodonto_icon( 'arrow', 16 ); // phpcs:ignore ?></span>
		</span>
	</a>
</article>
