<?php
/**
 * Página comum (Sobre, Privacidade...) — topo marinho com arco + texto do
 * editor em coluna de leitura. Adaptação própria no idioma da Proposta 2.
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="hero hero--list">
		<?php echo proodonto_arc( 'hero' ); // phpcs:ignore ?>
		<div class="wrap hero__in">
			<?php proodonto_breadcrumbs( 'crumbs--dark' ); ?>
			<h1 class="h1"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<section class="section">
		<div class="wrap wrap--text prose">
			<?php the_content(); ?>
		</div>
	</section>
	<?php
endwhile;
get_template_part( 'template-parts/sections/closing' );
get_footer();
