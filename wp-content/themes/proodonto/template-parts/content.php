<?php
/**
 * Card de post usado em index.php / archive.php.
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="entry-card__thumb" tabindex="-1">
			<?php the_post_thumbnail( 'proodonto-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="entry-card__body">
		<h2 class="entry-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="entry-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</div>

		<div class="entry-card__excerpt">
			<?php the_excerpt(); ?>
		</div>
	</div>

</article>
