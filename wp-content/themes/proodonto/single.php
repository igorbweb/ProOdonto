<?php
/**
 * Template de post singular.
 *
 * Depois do conteúdo (template-parts/content-single.php, com a hierarquia
 * de leitura), mostra "posts relacionados" — o objetivo é dar ao leitor
 * um próximo passo óbvio em vez de ele sair da página assim que termina
 * de ler. Sem seção de comentários (removida de propósito).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main">

	<?php while ( have_posts() ) : the_post(); ?>

		<div class="container">
			<?php get_template_part( 'template-parts/content', 'single' ); ?>
		</div>

		<?php
		$proodonto_related = proodonto_get_related_posts();
		if ( $proodonto_related->have_posts() ) :
			?>
			<section class="related-posts">
				<div class="container">
					<h2 class="related-posts__title"><?php esc_html_e( 'Continue lendo', 'proodonto' ); ?></h2>

					<div class="related-posts__grid">
						<?php while ( $proodonto_related->have_posts() ) : $proodonto_related->the_post(); ?>
							<a href="<?php the_permalink(); ?>" class="related-card">
								<?php if ( has_post_thumbnail() ) : ?>
									<div class="related-card__media">
										<?php the_post_thumbnail( 'proodonto-card', array( 'loading' => 'lazy' ) ); ?>
									</div>
								<?php endif; ?>
								<div class="related-card__body">
									<p class="related-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
									<h3 class="related-card__title"><?php the_title(); ?></h3>
								</div>
							</a>
						<?php endwhile; ?>
					</div>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
		?>

	<?php endwhile; ?>

</main>

<?php
get_footer();
