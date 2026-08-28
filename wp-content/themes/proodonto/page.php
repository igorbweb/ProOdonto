<?php
/**
 * Template padrão de página.
 *
 * Só é usado quando NÃO existe page-{slug}.php para a página atual — o
 * gerador automático (inc/page-generator.php) cria um arquivo dedicado
 * para cada página nova, então este arquivo serve principalmente como
 * fallback e referência de estrutura.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'page' ); ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
