<?php
/**
 * Post do blog (P2Post 390 / P2PostDesktop 1440).
 *
 * Busca e IA: "Resposta rápida" logo abaixo do título; trilha visível
 * (BreadcrumbList pelo Yoast); FAQ do editor vira FAQPage.
 * Confiança (YMYL): revisor com CRO no topo e no fim (reviewedBy).
 * Conversão: CTA no meio do texto, cartão fixo na lateral (desktop),
 * faixa final com as 3 unidades; WhatsApp com utm_source=blog.
 * Leitura: corpo 17–18px, ~68 caracteres, barra de progresso, sumário
 * (sanfona no celular, fixo à esquerda no desktop).
 */

defined( 'ABSPATH' ) || exit;

the_post();
$proodonto_id      = get_the_ID();
$proodonto_content = proodonto_post_content( $proodonto_id );
$proodonto_toc     = $proodonto_content['toc'];
$proodonto_rev     = function_exists( 'proodonto_get_post_reviewer' ) ? proodonto_get_post_reviewer( $proodonto_id ) : null;
$proodonto_cats    = get_the_category();
$proodonto_cat     = $proodonto_cats ? $proodonto_cats[0] : null;
$proodonto_quick   = (string) proodonto_field( 'post_resposta_rapida', $proodonto_id );
$proodonto_svc     = (int) proodonto_field( 'post_tratamento', $proodonto_id );
$proodonto_svc     = ( $proodonto_svc && 'publish' === get_post_status( $proodonto_svc ) ) ? $proodonto_svc : 0;
$proodonto_cta_t   = (string) proodonto_field( 'post_cta_titulo', $proodonto_id );
$proodonto_cta_d   = (string) proodonto_field( 'post_cta_texto', $proodonto_id );
$proodonto_units   = proodonto_get_units();

if ( $proodonto_toc ) {
	$proodonto_toc[] = array( 'id' => 'agendar', 'label' => __( 'Agendar avaliação', 'proodonto' ) );
}

proodonto_set_sticky(
	array(
		'label'     => (string) proodonto_opt( 'header_cta_label' ),
		'url'       => '#agendar',
		'attrs'     => array( 'data-cta' => 'barra-fixa-blog' ),
		'alt_url'   => home_url( '/#unidades' ),
		'alt_label' => __( 'Endereços das unidades', 'proodonto' ),
		'alt_icon'  => 'pin',
	)
);

/** Sumário (lista). */
$proodonto_toc_list = static function () use ( $proodonto_toc ) {
	echo '<ol class="toc">';
	foreach ( $proodonto_toc as $item ) {
		printf( '<li><a href="#%1$s" data-toc="%1$s">%2$s</a></li>', esc_attr( $item['id'] ), esc_html( $item['label'] ) );
	}
	echo '</ol>';
};

get_header();
?>
<div class="progress" aria-hidden="true"><span data-progress></span></div>

<article <?php post_class( 'article' ); ?>>
	<header class="wrap article__head">
		<div class="article__intro">
			<?php proodonto_breadcrumbs(); ?>
			<?php if ( $proodonto_cat ) : ?>
				<a class="pill" href="<?php echo esc_url( get_category_link( $proodonto_cat ) ); ?>"><?php echo esc_html( $proodonto_cat->name ); ?></a>
			<?php endif; ?>
			<h1 class="h1 article__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="article__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="byline">
				<?php if ( $proodonto_rev && ! empty( $proodonto_rev['name'] ) ) : ?>
					<span class="avatar" aria-hidden="true"><?php echo esc_html( proodonto_initials( $proodonto_rev['name'] ) ); ?></span>
				<?php endif; ?>
				<span>
					<?php if ( $proodonto_rev && ! empty( $proodonto_rev['name'] ) ) : ?>
						<strong><?php echo esc_html( sprintf( __( 'Revisão clínica: %1$s · %2$s', 'proodonto' ), $proodonto_rev['name'], $proodonto_rev['cro'] ) ); ?></strong>
					<?php endif; ?>
					<?php
					printf(
						/* translators: 1: data, 2: minutos */
						esc_html__( 'Atualizado em %1$s · %2$d min de leitura', 'proodonto' ),
						'<time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>', // phpcs:ignore
						(int) proodonto_reading_time( $proodonto_id )
					);
					?>
				</span>
			</div>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
			<?php echo proodonto_photo( get_post_thumbnail_id(), 'large', 'article__img', array( 'sizes' => '(min-width: 1024px) 520px, 100vw', 'fetchpriority' => 'high', 'alt' => get_the_title() ) ); // phpcs:ignore ?>
		<?php endif; ?>
	</header>

	<?php if ( $proodonto_toc ) : ?>
		<details class="card toc-m only-m">
			<summary><?php esc_html_e( 'Neste artigo', 'proodonto' ); ?><?php echo proodonto_icon( 'chevron', 18 ); // phpcs:ignore ?></summary>
			<?php $proodonto_toc_list(); ?>
		</details>
	<?php endif; ?>

	<div class="wrap article__grid">
		<aside class="article__side" aria-label="<?php esc_attr_e( 'Navegação do artigo', 'proodonto' ); ?>">
			<div class="article__sticky">
				<?php if ( $proodonto_toc ) : ?>
					<nav class="only-d" aria-label="<?php esc_attr_e( 'Neste artigo', 'proodonto' ); ?>">
						<p class="eyebrow"><?php esc_html_e( 'Neste artigo', 'proodonto' ); ?></p>
						<?php $proodonto_toc_list(); ?>
					</nav>
				<?php endif; ?>
				<div class="share only-d">
					<button type="button" class="share__b" data-copy-link aria-label="<?php esc_attr_e( 'Copiar link do artigo', 'proodonto' ); ?>"><?php echo proodonto_icon( 'link', 18 ); // phpcs:ignore ?></button>
					<a class="share__b" href="<?php echo esc_url( 'https://api.whatsapp.com/send?text=' . rawurlencode( get_the_title() . ' ' . get_permalink() ) ); ?>" rel="noopener" aria-label="<?php esc_attr_e( 'Compartilhar no WhatsApp', 'proodonto' ); ?>" data-share="whatsapp"><?php echo proodonto_icon( 'whatsapp', 18 ); // phpcs:ignore ?></a>
					<span class="share__msg" role="status" aria-live="polite"></span>
				</div>

				<?php if ( $proodonto_cta_t && $proodonto_units ) : ?>
					<div class="side-cta card only-d">
						<p class="side-cta__t display"><?php echo esc_html( $proodonto_cta_t ); ?></p>
						<?php if ( $proodonto_cta_d ) : ?>
							<p class="side-cta__d"><?php echo esc_html( $proodonto_cta_d ); ?></p>
						<?php endif; ?>
						<ul class="side-cta__list">
							<?php foreach ( $proodonto_units as $proodonto_u ) : ?>
								<li>
									<a class="side-cta__a tap" href="<?php echo esc_url( proodonto_unit_cta_url( $proodonto_u, $proodonto_svc ) ); ?>"<?php echo proodonto_attrs( proodonto_cta_attrs( $proodonto_u, 'blog-lateral' ) ); // phpcs:ignore ?>>
										<span><strong><?php echo esc_html( $proodonto_u['name'] ); ?></strong><small><?php echo esc_html( $proodonto_u['bairro'] ); ?></small></span>
										<?php echo proodonto_chip( 'chip--solid chip--sm' ); // phpcs:ignore ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( $proodonto_svc ) : ?>
					<?php $proodonto_c = proodonto_servico_card( $proodonto_svc ); ?>
					<a class="related-svc card tap only-d" href="<?php echo esc_url( $proodonto_c['url'] ); ?>">
						<?php echo proodonto_arc( 'card' ); // phpcs:ignore ?>
						<span>
							<span class="eyebrow"><?php esc_html_e( 'Tratamento relacionado', 'proodonto' ); ?></span>
							<span class="related-svc__t display"><?php echo esc_html( $proodonto_c['title'] ); ?></span>
							<?php if ( $proodonto_c['desc'] ) : ?>
								<span class="related-svc__d"><?php echo esc_html( $proodonto_c['desc'] ); ?></span>
							<?php endif; ?>
						</span>
						<?php echo proodonto_chip( 'chip--white' ); // phpcs:ignore ?>
					</a>
				<?php endif; ?>
			</div>
		</aside>

		<div class="article__main">
			<?php if ( $proodonto_quick ) : ?>
				<aside class="quick card" aria-labelledby="rr" data-sticky-trigger>
					<p class="eyebrow" id="rr"><?php esc_html_e( 'Resposta rápida', 'proodonto' ); ?></p>
					<p><?php echo proodonto_bold( $proodonto_quick ); // phpcs:ignore ?></p>
				</aside>
			<?php endif; ?>

			<div class="prose" data-prose>
				<?php echo $proodonto_content['html']; // phpcs:ignore -- the_content. ?>
			</div>

			<?php if ( $proodonto_svc ) : ?>
				<?php $proodonto_c = proodonto_servico_card( $proodonto_svc ); ?>
				<a class="related-svc card tap only-m" href="<?php echo esc_url( $proodonto_c['url'] ); ?>">
					<?php echo proodonto_arc( 'card' ); // phpcs:ignore ?>
					<span>
						<span class="eyebrow"><?php esc_html_e( 'Tratamento relacionado', 'proodonto' ); ?></span>
						<span class="related-svc__t display"><?php echo esc_html( $proodonto_c['title'] ); ?></span>
						<?php if ( $proodonto_c['desc'] ) : ?>
							<span class="related-svc__d"><?php echo esc_html( $proodonto_c['desc'] ); ?></span>
						<?php endif; ?>
					</span>
					<?php echo proodonto_chip( 'chip--white' ); // phpcs:ignore ?>
				</a>
			<?php endif; ?>

			<?php if ( $proodonto_rev && ! empty( $proodonto_rev['name'] ) ) : ?>
				<div class="reviewer card">
					<span class="avatar avatar--lg" aria-hidden="true"><?php echo esc_html( proodonto_initials( $proodonto_rev['name'] ) ); ?></span>
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Revisão clínica', 'proodonto' ); ?></p>
						<p class="reviewer__n">
							<?php if ( ! empty( $proodonto_rev['url'] ) ) : ?>
								<a href="<?php echo esc_url( $proodonto_rev['url'] ); ?>"><?php echo esc_html( $proodonto_rev['name'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $proodonto_rev['name'] ); ?>
							<?php endif; ?>
						</p>
						<p class="reviewer__r"><?php echo esc_html( implode( ' · ', array_filter( array( $proodonto_rev['job_title'], $proodonto_rev['cro'] ) ) ) ); ?></p>
						<p class="reviewer__d"><?php esc_html_e( 'Este conteúdo é informativo e não substitui a avaliação presencial.', 'proodonto' ); ?></p>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</article>

<section class="band band--navy post-final" id="agendar" aria-labelledby="h-agendar">
	<?php echo proodonto_arc( 'band' ); // phpcs:ignore ?>
	<div class="wrap">
		<?php
		$proodonto_ft = (string) proodonto_field( 'post_final_titulo', $proodonto_id );
		$proodonto_ft = $proodonto_ft ? $proodonto_ft : (string) proodonto_opt( 'fechamento_titulo' );
		$proodonto_fd = (string) proodonto_field( 'post_final_texto', $proodonto_id );
		?>
		<h2 class="h2 h2--lg" id="h-agendar"><?php echo proodonto_rich( $proodonto_ft ); // phpcs:ignore ?></h2>
		<?php if ( $proodonto_fd ) : ?>
			<p class="units-band__text"><?php echo esc_html( $proodonto_fd ); ?></p>
		<?php endif; ?>
		<?php
		get_template_part(
			'template-parts/sections/unit-selector',
			null,
			array(
				'title'      => __( 'Agende na unidade', 'proodonto' ),
				'servico_id' => $proodonto_svc,
				'place'      => 'blog-final',
				'class'      => 'selector--bar',
			)
		);
		?>
	</div>
</section>

<?php $proodonto_related = proodonto_related_posts( $proodonto_id, 3 ); ?>
<?php if ( $proodonto_related ) : ?>
	<section class="section more" aria-labelledby="h-rel">
		<div class="wrap">
			<div class="section__top">
				<h2 class="h2" id="h-rel"><?php echo proodonto_rich( __( 'Continue *lendo*', 'proodonto' ) ); // phpcs:ignore ?></h2>
				<?php
				echo proodonto_button( // phpcs:ignore
					array(
						'label'   => __( 'Ver todos os artigos', 'proodonto' ),
						'url'     => proodonto_blog_url(),
						'variant' => 'outline',
						'class'   => 'only-d',
					)
				);
				?>
			</div>
			<div class="grid-3">
				<?php foreach ( $proodonto_related as $proodonto_p ) : ?>
					<?php get_template_part( 'template-parts/cards/post', null, array( 'id' => $proodonto_p->ID ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
