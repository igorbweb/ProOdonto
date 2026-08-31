<?php
/**
 * Template padrão de arquivo (categoria, tag, data, autor, CPT).
 *
 * Arquivo de categoria (is_category()) ganha um tratamento à parte: hero
 * (eyebrow + H1 + descrição da categoria, se houver) e a grade de cards
 * .blog-card/.blog-grid (assets/css/main.css) — mesmo componente visual do
 * índice do blog (home.php). Tag/data/autor/CPT continuam no cabeçalho e
 * na lista .entry-card simples de sempre (não pedido, não alterado).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main">

	<?php if ( is_category() ) : ?>

		<section class="archive-hero">
			<div class="container">
				<?php proodonto_breadcrumbs(); ?>
				<p class="archive-hero__eyebrow"><?php esc_html_e( 'Categoria', 'proodonto' ); ?></p>
				<h1 class="archive-hero__title"><?php single_cat_title(); ?></h1>
				<?php the_archive_description( '<div class="archive-hero__text">', '</div>' ); ?>
			</div>
		</section>

		<div class="container blog-index">

			<?php if ( ! have_posts() ) : ?>

				<?php get_template_part( 'template-parts/content', 'none' ); ?>

			<?php else : ?>

				<div class="blog-grid">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'template-parts/content', 'category' ); ?>
					<?php endwhile; ?>
				</div>

				<?php proodonto_pagination(); ?>

			<?php endif; ?>

		</div>

	<?php else : ?>

		<div class="container">

			<header class="archive-header">
				<?php proodonto_archive_title(); ?>
				<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
			</header>

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'template-parts/content', get_post_type() ); ?>
				<?php endwhile; ?>

				<?php proodonto_pagination(); ?>

			<?php else : ?>

				<?php get_template_part( 'template-parts/content', 'none' ); ?>

			<?php endif; ?>

		</div>

	<?php endif; ?>

</main>

<?php
get_footer();
