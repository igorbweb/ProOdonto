<?php
/**
 * Card de resultado de busca — mais enxuto que o card de blog padrão.
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card entry-card--search' ); ?>>

	<h2 class="entry-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<div class="entry-card__meta">
		<span><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
	</div>

	<div class="entry-card__excerpt">
		<?php the_excerpt(); ?>
	</div>

</article>
