<?php
/**
 * Card de post no arquivo de categoria (archive.php, is_category()).
 * Mesmo componente visual de .blog-card (assets/css/main.css) usado no
 * índice do blog — sem o selo de categoria, já redundante aqui (todo post
 * listado é da mesma categoria anunciada no hero da página).
 */

defined( 'ABSPATH' ) || exit;

$proodonto_thumb = get_the_post_thumbnail(
	get_the_ID(),
	'proodonto-card',
	array(
		'loading' => 'lazy',
		'alt'     => get_the_title(),
	)
);
?>
<a href="<?php the_permalink(); ?>" class="blog-card">
	<?php if ( $proodonto_thumb ) : ?>
		<div class="blog-card__media">
			<?php echo $proodonto_thumb; ?>
		</div>
	<?php endif; ?>
	<div class="blog-card__body">
		<h2 class="blog-card__title"><?php the_title(); ?></h2>
		<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14, '…' ) ); ?></p>
		<p class="blog-card__meta">
			<?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( sprintf( __( '%d min de leitura', 'proodonto' ), proodonto_reading_time() ) ); ?>
		</p>
	</div>
</a>
