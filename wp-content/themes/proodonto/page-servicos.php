<?php
/**
 * Template Name: Hub de serviços
 *
 * Hub /servicos/ — catálogo de tratamentos. Não converte, distribui
 * (spec, seção 04): abre com o catálogo em fundo creme, o primeiro card
 * já acima da dobra (HUB-01), e manda a pessoa pra página certa.
 *
 * Funciona tanto pelo slug "servicos" (Template Hierarchy) quanto pelo
 * template escolhido em Atributos da página — o "Template Name" acima é
 * o que faz os campos ACF "Hub de serviços" aparecerem no editor.
 *
 * Cards: Serviços publicados (CPT `servico`), na ordem do campo "Ordem".
 * Texto próprio do hub (HUB-04): o conteúdo do editor desta página.
 * Sem preço nos cards (retirado em 2026-10-04).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$proodonto_has_acf   = function_exists( 'get_field' );
	$proodonto_h1        = $proodonto_has_acf ? (string) get_field( 'hub_titulo' ) : '';
	$proodonto_lead      = $proodonto_has_acf ? (string) get_field( 'hub_lead' ) : '';
	$proodonto_servicos  = proodonto_servicos_query()->posts;
	$proodonto_units     = proodonto_servicos_units();
	$proodonto_content   = trim( (string) get_the_content() );
	$proodonto_offer_ttl = $proodonto_has_acf ? (string) get_field( 'servicos_oferta_titulo', 'option' ) : '';
	$proodonto_offer_txt = $proodonto_has_acf ? (string) get_field( 'servicos_oferta_texto', 'option' ) : '';
	$proodonto_offer_nt  = $proodonto_has_acf ? (string) get_field( 'servicos_oferta_nota', 'option' ) : '';
	$proodonto_offer_its = $proodonto_has_acf ? array_filter( (array) get_field( 'servicos_oferta_itens', 'option' ), function ( $row ) {
		return ! empty( $row['texto'] );
	} ) : array();
	?>

	<main id="primary" class="site-main svc-page svc-page--hub">

		<!-- Hero creme: abre com o catálogo, sem foto grande. -->
		<section class="svc-hub-hero" aria-labelledby="svc-h1" data-svc-hero-cta>
			<div class="svc-container">
				<?php proodonto_servicos_breadcrumbs(); ?>
				<h1 id="svc-h1" class="svc-hub-hero__title"><?php echo esc_html( $proodonto_h1 ? $proodonto_h1 : get_the_title() ); ?></h1>
				<?php if ( $proodonto_lead ) : ?>
					<p class="svc-hub-hero__lead"><?php echo esc_html( $proodonto_lead ); ?></p>
				<?php endif; ?>
				<?php proodonto_servicos_render_proof( 'svc-proof svc-proof--inline' ); ?>
			</div>
		</section>

		<?php if ( $proodonto_servicos ) : ?>
			<!-- Grade de tratamentos — 2 colunas no celular, nunca 1 (HUB-02). ItemList no schema. -->
			<section class="svc-catalog" aria-label="<?php esc_attr_e( 'Tratamentos', 'proodonto' ); ?>">
				<div class="svc-container">
					<ul class="svc-cards">
						<?php foreach ( $proodonto_servicos as $proodonto_servico ) : ?>
							<?php $proodonto_para = $proodonto_has_acf ? (string) get_field( 'card_para_quem', $proodonto_servico->ID ) : ''; ?>
							<li>
								<a class="svc-card" href="<?php echo esc_url( get_permalink( $proodonto_servico ) ); ?>">
									<span class="svc-card__title"><?php echo esc_html( get_the_title( $proodonto_servico ) ); ?></span>
									<?php if ( $proodonto_para ) : ?>
										<span class="svc-card__text"><?php echo esc_html( $proodonto_para ); ?></span>
									<?php endif; ?>
									<span class="svc-card__more"><?php esc_html_e( 'Ver tratamento', 'proodonto' ); ?><?php echo proodonto_servicos_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $proodonto_offer_ttl && $proodonto_units ) : ?>
			<!-- "Não sabe por onde começar" — a oferta real da casa (HUB-03). -->
			<section class="svc-offer-wrap">
				<div class="svc-container">
					<div class="svc-offer">
						<h2 class="svc-offer__title"><?php echo esc_html( $proodonto_offer_ttl ); ?></h2>
						<?php if ( $proodonto_offer_txt ) : ?>
							<p class="svc-offer__text"><?php echo esc_html( $proodonto_offer_txt ); ?></p>
						<?php endif; ?>
						<?php if ( $proodonto_offer_its ) : ?>
							<ul class="svc-checklist svc-checklist--on-dark">
								<?php foreach ( $proodonto_offer_its as $proodonto_item ) : ?>
									<li><?php echo proodonto_servicos_icon( 'check', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $proodonto_item['texto'] ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<a class="svc-btn svc-btn--light svc-btn--block" href="#unidades"><span><?php esc_html_e( 'Escolher unidade', 'proodonto' ); ?></span><?php echo proodonto_servicos_icon( 'down', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
						<?php if ( $proodonto_offer_nt ) : ?>
							<p class="svc-offer__note"><?php echo esc_html( $proodonto_offer_nt ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $proodonto_content ) : ?>
			<!-- Texto próprio do hub (HUB-04). -->
			<div class="svc-content svc-content--hub">
				<div class="svc-container svc-container--text entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php
		proodonto_servicos_render_units(
			$proodonto_units,
			/* translators: %d: número de unidades */
			sprintf( _n( 'A unidade', 'As %d unidades', count( $proodonto_units ), 'proodonto' ), count( $proodonto_units ) ),
			__( 'Todos os tratamentos em todas as unidades.', 'proodonto' )
		);

		proodonto_servicos_render_sticky_bar( $proodonto_units );
		?>
	</main>

	<?php
endwhile;

get_footer();
