<?php
/**
 * Listagem de artigos (blog, categoria, busca) — adaptação própria no
 * idioma da Proposta 2 (o canvas não desenha a listagem): topo marinho
 * com arco, filtros por categoria em pílulas e cards de artigo.
 *
 * @var array $args { @type string $title, @type string $lead }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_title = $args['title'] ?? '';
$proodonto_lead  = $args['lead'] ?? '';
$proodonto_cats  = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 8,
	)
);
$proodonto_cur   = is_category() ? get_queried_object_id() : 0;
?>
<section class="hero hero--list">
	<?php echo proodonto_arc( 'hero' ); // phpcs:ignore ?>
	<div class="wrap hero__in">
		<?php proodonto_breadcrumbs( 'crumbs--dark' ); ?>
		<h1 class="h1"><?php echo proodonto_rich( $proodonto_title ); // phpcs:ignore ?></h1>
		<?php if ( $proodonto_lead ) : ?>
			<div class="lead"><?php echo wp_kses_post( $proodonto_lead ); ?></div>
		<?php endif; ?>
		<?php if ( is_search() ) : ?>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>

<section class="section listing">
	<div class="wrap">
		<?php if ( $proodonto_cats && ! is_search() ) : ?>
			<nav class="chips" aria-label="<?php esc_attr_e( 'Categorias', 'proodonto' ); ?>">
				<ul>
					<?php if ( proodonto_blog_url() ) : ?>
						<li><a class="pill<?php echo is_home() ? ' is-current' : ''; ?>" href="<?php echo esc_url( proodonto_blog_url() ); ?>"<?php echo is_home() ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Todos', 'proodonto' ); ?></a></li>
					<?php endif; ?>
					<?php foreach ( $proodonto_cats as $proodonto_c ) : ?>
						<li><a class="pill<?php echo $proodonto_cur === $proodonto_c->term_id ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_category_link( $proodonto_c ) ); ?>"<?php echo $proodonto_cur === $proodonto_c->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $proodonto_c->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="grid-3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/post', null, array( 'id' => get_the_ID(), 'heading' => 'h2' ) );
				endwhile;
				?>
			</div>
			<?php
			$proodonto_pag = paginate_links(
				array(
					'type'      => 'list',
					'mid_size'  => 1,
					'prev_text' => '<span class="sr-only">' . esc_html__( 'Anterior', 'proodonto' ) . '</span>' . proodonto_icon( 'arrow', 16 ),
					'next_text' => '<span class="sr-only">' . esc_html__( 'Próxima', 'proodonto' ) . '</span>' . proodonto_icon( 'arrow', 16 ),
				)
			);
			if ( $proodonto_pag ) {
				echo '<nav class="pager" aria-label="' . esc_attr__( 'Paginação', 'proodonto' ) . '">' . $proodonto_pag . '</nav>'; // phpcs:ignore
			}
			?>
		<?php else : ?>
			<div class="empty card">
				<p class="h2 h2--sm"><?php esc_html_e( 'Nada por aqui ainda.', 'proodonto' ); ?></p>
				<?php echo proodonto_button( array( 'label' => __( 'Voltar ao início', 'proodonto' ), 'url' => home_url( '/' ), 'variant' => 'outline' ) ); // phpcs:ignore ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/sections/closing' ); ?>
