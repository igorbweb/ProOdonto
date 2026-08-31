<?php
/**
 * Template do índice do blog.
 *
 * "Blog" está configurado em Configurações → Leitura → "Página de posts",
 * então esta rota é is_home() (não is_page()) — a Template Hierarchy do
 * WordPress usa home.php nessa situação, com prioridade sobre
 * page-blog.php (que fica inerte enquanto essa configuração existir; ver
 * nota em page-blog.php). A Page "Blog" em si só empresta o título/slug
 * para essa configuração — seu conteúdo (the_content) nunca é exibido, daí
 * o hero usar esse conteúdo diretamente via get_post_field(), sem passar
 * pelo loop principal.
 *
 * Hero (breadcrumbs + eyebrow + H1 + texto editável da Page "Blog" + chips
 * de categoria) seguido do post mais recente em destaque (só na 1ª página)
 * e uma grade com os demais — paginação nativa via proodonto_pagination()
 * (query principal, mesma função usada em archive.php/search.php).
 *
 * O CSS correspondente fica em assets/css/pages/blog.css e é carregado
 * automaticamente apenas nesta rota (ver inc/enqueue.php).
 */

defined( 'ABSPATH' ) || exit;

$proodonto_placeholder_img = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1600' height='900'%3E%3Crect width='100%25' height='100%25' fill='%23e2e4e8'/%3E%3C/svg%3E";

$proodonto_blog_page_id = (int) get_option( 'page_for_posts' );
$proodonto_categories   = get_categories( array( 'hide_empty' => true ) );

get_header();
?>

<main id="primary" class="site-main">

	<section class="blog-hero">
		<div class="container">

			<?php proodonto_breadcrumbs(); ?>

			<p class="blog-hero__eyebrow"><?php esc_html_e( 'Blog', 'proodonto' ); ?></p>
			<h1 class="blog-hero__title">
				<?php echo esc_html( $proodonto_blog_page_id ? get_the_title( $proodonto_blog_page_id ) : __( 'Blog', 'proodonto' ) ); ?>
			</h1>

			<?php
			$proodonto_hero_text = $proodonto_blog_page_id ? get_post_field( 'post_content', $proodonto_blog_page_id ) : '';
			if ( $proodonto_hero_text ) :
				?>
				<div class="blog-hero__text"><?php echo apply_filters( 'the_content', $proodonto_hero_text ); ?></div>
			<?php endif; ?>

			<?php if ( $proodonto_categories ) : ?>
				<nav class="blog-cats" aria-label="<?php esc_attr_e( 'Categorias do blog', 'proodonto' ); ?>">
					<?php foreach ( $proodonto_categories as $proodonto_cat ) : ?>
						<a href="<?php echo esc_url( get_category_link( $proodonto_cat->term_id ) ); ?>" class="blog-cats__pill">
							<?php echo esc_html( $proodonto_cat->name ); ?>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

		</div>
	</section>

	<div class="container blog-index">

		<?php if ( ! have_posts() ) : ?>

			<p class="blog-empty"><?php esc_html_e( 'Em breve, novos artigos por aqui. Volte mais tarde!', 'proodonto' ); ?></p>

		<?php else : ?>

			<?php
			global $wp_query;
			$proodonto_grid_open = false;
			?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php
				$proodonto_is_featured = ( 0 === $wp_query->current_post && ! is_paged() );
				$proodonto_cats        = get_the_category();
				$proodonto_cat_name    = $proodonto_cats ? $proodonto_cats[0]->name : __( 'Blog', 'proodonto' );
				$proodonto_thumb       = get_the_post_thumbnail(
					get_the_ID(),
					'proodonto-card',
					array(
						'loading'       => $proodonto_is_featured ? 'eager' : 'lazy',
						'fetchpriority' => $proodonto_is_featured ? 'high' : 'low',
						'alt'           => get_the_title(),
					)
				);
				?>

				<?php if ( $proodonto_is_featured ) : ?>

					<a href="<?php the_permalink(); ?>" class="blog-featured">
						<div class="blog-featured__media">
							<?php if ( $proodonto_thumb ) : ?>
								<?php echo $proodonto_thumb; ?>
							<?php else : ?>
								<img src="<?php echo esc_attr( $proodonto_placeholder_img ); ?>" alt="<?php the_title_attribute(); ?>" loading="eager" />
							<?php endif; ?>
						</div>
						<div class="blog-featured__body">
							<p class="blog-featured__category"><?php echo esc_html( $proodonto_cat_name ); ?></p>
							<h2 class="blog-featured__title"><?php the_title(); ?></h2>
							<p class="blog-featured__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40, '…' ) ); ?></p>
							<p class="blog-featured__meta">
								<?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( sprintf( __( '%d min de leitura', 'proodonto' ), proodonto_reading_time() ) ); ?>
							</p>
						</div>
					</a>

				<?php else : ?>

					<?php if ( ! $proodonto_grid_open ) : ?>
						<div class="blog-grid">
						<?php $proodonto_grid_open = true; ?>
					<?php endif; ?>

					<a href="<?php the_permalink(); ?>" class="blog-card">
						<div class="blog-card__media">
							<?php if ( $proodonto_thumb ) : ?>
								<?php echo $proodonto_thumb; ?>
							<?php else : ?>
								<img src="<?php echo esc_attr( $proodonto_placeholder_img ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
							<?php endif; ?>
						</div>
						<div class="blog-card__body">
							<p class="blog-card__category"><?php echo esc_html( $proodonto_cat_name ); ?></p>
							<h3 class="blog-card__title"><?php the_title(); ?></h3>
							<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14, '…' ) ); ?></p>
							<p class="blog-card__meta">
								<?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( sprintf( __( '%d min de leitura', 'proodonto' ), proodonto_reading_time() ) ); ?>
							</p>
						</div>
					</a>

				<?php endif; ?>

			<?php endwhile; ?>

			<?php if ( $proodonto_grid_open ) : ?>
				</div><!-- .blog-grid -->
			<?php endif; ?>

			<?php proodonto_pagination(); ?>

		<?php endif; ?>

	</div>

</main>

<?php
get_footer();
