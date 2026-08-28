<?php
/**
 * Template gerado automaticamente para a página "Blog" (slug: blog).
 * Criado em 2026-08-12 18:14 pelo gerador automático do tema Proodonto.
 *
 * Este arquivo NÃO é sobrescrito em publicações futuras — edite à vontade.
 * O CSS correspondente fica em assets/css/pages/blog.css e é carregado
 * automaticamente apenas nesta página (ver inc/enqueue.php).
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'page' ); ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
