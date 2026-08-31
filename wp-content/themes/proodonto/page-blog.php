<?php
/**
 * Template gerado automaticamente para a página "Blog" (slug: blog).
 * Criado em 2026-08-12 18:14 pelo gerador automático do tema Proodonto.
 *
 * Este arquivo NÃO é sobrescrito em publicações futuras — edite à vontade.
 * O CSS correspondente fica em assets/css/pages/blog.css e é carregado
 * automaticamente apenas nesta página (ver inc/enqueue.php).
 *
 * NOTA (2026-08-28): "Blog" é a Página de posts do site (Configurações →
 * Leitura → "Página de posts"), então o WordPress nunca chega a usar este
 * arquivo — para essa rota, is_home() tem prioridade sobre page-{slug}.php
 * na Template Hierarchy, e o WordPress usa home.php em vez deste template.
 * O índice do blog (hero, categorias, destaque, grade, paginação) fica em
 * home.php; este arquivo só entraria em cena se "Página de posts" fosse
 * desmarcada nas Configurações de Leitura.
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
