<?php
/**
 * Página de tratamento — /servicos/<tratamento>/ (CPT `servico`).
 *
 * A ordem dos blocos é a ordem em que o paciente decide (spec, seção 03):
 * hero → o que é → para quem é → etapas → avaliações → FAQ → unidades.
 * Sem bloco de preço (retirado em 2026-10-04).
 *
 * Regras que este template garante (ver inc/servicos.php):
 *   - H1 e os 3 botões de WhatsApp sempre acima da dobra, sem depender de
 *     foto nem de campo opcional (CONV-01/02).
 *   - Todo bloco opcional só existe no DOM se tiver dado (CONV-07).
 *   - FAQ = conteúdo do editor (blocos proodonto/faq), HTML servido com a
 *     resposta visível — o FAQPage sai sozinho por inc/blocks.php.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$proodonto_post_id  = get_the_ID();
	$proodonto_title    = get_the_title();
	$proodonto_units    = proodonto_servicos_units( $proodonto_post_id );
	$proodonto_has_acf  = function_exists( 'get_field' );
	$proodonto_h1       = $proodonto_has_acf ? (string) get_field( 'hero_titulo' ) : '';
	$proodonto_lead     = $proodonto_has_acf ? (string) get_field( 'hero_lead' ) : '';
	$proodonto_resposta = $proodonto_has_acf ? (string) get_field( 'resposta_direta' ) : '';
	$proodonto_para     = $proodonto_has_acf ? array_filter( (array) get_field( 'para_quem_itens' ), function ( $row ) {
		return ! empty( $row['texto'] );
	} ) : array();
	$proodonto_antes    = $proodonto_has_acf ? (string) get_field( 'pre_requisitos' ) : '';
	$proodonto_etapas   = $proodonto_has_acf ? array_filter( (array) get_field( 'etapas' ), function ( $row ) {
		return ! empty( $row['titulo'] );
	} ) : array();
	$proodonto_reviews  = $proodonto_has_acf ? array_filter( (array) get_field( 'avaliacoes' ), function ( $row ) {
		return ! empty( $row['texto'] );
	} ) : array();
	$proodonto_rev_link = $proodonto_has_acf ? (string) get_field( 'avaliacoes_link' ) : '';
	$proodonto_related  = $proodonto_has_acf ? array_filter( (array) get_field( 'posts_relacionados' ) ) : array();
	$proodonto_hub      = proodonto_servicos_hub_page();
	$proodonto_tel      = proodonto_servicos_tel_url();
	$proodonto_content  = trim( (string) get_the_content() );
	$proodonto_cta_units = array_filter( $proodonto_units, function ( $unit ) {
		return ! empty( $unit['whatsapp_url'] );
	} );
	?>

	<main id="primary" class="site-main svc-page svc-page--servico">

		<!-- 02 Hero: fundo sólido, sem foto — renderiza mesmo com todos os campos vazios. -->
		<section class="svc-hero" aria-labelledby="svc-h1">
			<div class="svc-container">
				<?php proodonto_servicos_breadcrumbs( 'svc-breadcrumbs--on-dark' ); ?>

				<?php if ( $proodonto_units ) : ?>
					<p class="svc-hero__eyebrow"><?php echo esc_html( implode( ' · ', wp_list_pluck( $proodonto_units, 'name' ) ) ); ?></p>
				<?php endif; ?>

				<h1 id="svc-h1" class="svc-hero__title"><?php echo esc_html( $proodonto_h1 ? $proodonto_h1 : $proodonto_title ); ?></h1>

				<?php if ( $proodonto_lead ) : ?>
					<p class="svc-hero__lead"><?php echo esc_html( $proodonto_lead ); ?></p>
				<?php endif; ?>

				<?php if ( $proodonto_cta_units ) : ?>
					<p id="svc-hero-cta-label" class="svc-hero__cta-label"><?php esc_html_e( 'Agende pelo WhatsApp na unidade mais perto:', 'proodonto' ); ?></p>
					<div class="svc-hero__ctas" role="group" aria-labelledby="svc-hero-cta-label" data-svc-hero-cta>
						<?php
						foreach ( $proodonto_cta_units as $proodonto_unit ) {
							echo proodonto_servicos_cta_link( $proodonto_unit, $proodonto_unit['name'], 'svc-btn svc-btn--unit', $proodonto_title ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado dentro do helper.
						}
						?>
					</div>
					<p class="svc-hero__fallback">
						<?php esc_html_e( 'O WhatsApp não abriu?', 'proodonto' ); ?>
						<?php if ( $proodonto_tel ) : ?>
							<a href="<?php echo esc_url( $proodonto_tel ); ?>"><?php esc_html_e( 'Ligue para a clínica', 'proodonto' ); ?></a>
						<?php else : ?>
							<a href="#unidades"><?php esc_html_e( 'Veja os endereços', 'proodonto' ); ?></a>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php proodonto_servicos_render_proof( 'svc-proof svc-proof--hero' ); ?>
			</div>
		</section>

		<?php if ( $proodonto_resposta ) : ?>
			<!-- 03 O que é: resposta direta (SEO-03). -->
			<section class="svc-section" aria-labelledby="svc-o-que-e">
				<div class="svc-container svc-container--text">
					<?php /* translators: %s: nome do tratamento */ ?>
					<h2 id="svc-o-que-e" class="svc-h2"><?php echo esc_html( sprintf( __( 'O que é %s', 'proodonto' ), mb_strtolower( $proodonto_title ) ) ); ?></h2>
					<p class="svc-answer"><?php echo esc_html( $proodonto_resposta ); ?></p>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $proodonto_para || $proodonto_antes ) : ?>
			<!-- 04 Para quem é · o que vem antes. -->
			<section class="svc-section svc-section--tight-top" aria-labelledby="svc-para-quem">
				<div class="svc-container svc-container--text">
					<h2 id="svc-para-quem" class="svc-h2"><?php esc_html_e( 'Para quem é', 'proodonto' ); ?></h2>
					<?php if ( $proodonto_para ) : ?>
						<ul class="svc-checklist">
							<?php foreach ( $proodonto_para as $proodonto_item ) : ?>
								<li><?php echo proodonto_servicos_icon( 'check', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $proodonto_item['texto'] ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $proodonto_antes ) : ?>
						<div class="svc-note">
							<p class="svc-note__title"><?php esc_html_e( 'O que pode vir antes', 'proodonto' ); ?></p>
							<p class="svc-note__text"><?php echo esc_html( $proodonto_antes ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $proodonto_etapas ) : ?>
			<!-- 06 Etapas: lista numerada estática (PERF-02). -->
			<section class="svc-section" aria-labelledby="svc-etapas">
				<div class="svc-container svc-container--text">
					<h2 id="svc-etapas" class="svc-h2"><?php esc_html_e( 'Como funciona, etapa por etapa', 'proodonto' ); ?></h2>
					<ol class="svc-steps">
						<?php $proodonto_n = 0; ?>
						<?php foreach ( $proodonto_etapas as $proodonto_etapa ) : ?>
							<?php $proodonto_n++; ?>
							<li class="svc-step">
								<span class="svc-step__n" aria-hidden="true"><?php echo esc_html( str_pad( (string) $proodonto_n, 2, '0', STR_PAD_LEFT ) ); ?></span>
								<div class="svc-step__body">
									<h3 class="svc-step__title"><?php echo esc_html( $proodonto_etapa['titulo'] ); ?></h3>
									<?php if ( ! empty( $proodonto_etapa['descricao'] ) ) : ?>
										<p class="svc-step__text"><?php echo esc_html( $proodonto_etapa['descricao'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $proodonto_etapa['duracao'] ) ) : ?>
										<p class="svc-step__time"><?php echo esc_html( $proodonto_etapa['duracao'] ); ?></p>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $proodonto_reviews ) : ?>
			<!-- 07 Avaliações reais — sem dado, a seção não existe (CONV-07). -->
			<section class="svc-section svc-section--alt" aria-labelledby="svc-avaliacoes">
				<div class="svc-container svc-container--text">
					<h2 id="svc-avaliacoes" class="svc-h2"><?php esc_html_e( 'Quem já tratou aqui', 'proodonto' ); ?></h2>
					<div class="svc-reviews">
						<?php foreach ( $proodonto_reviews as $proodonto_review ) : ?>
							<figure class="svc-review">
								<blockquote class="svc-review__text"><?php echo esc_html( $proodonto_review['texto'] ); ?></blockquote>
								<?php if ( ! empty( $proodonto_review['nome'] ) || ! empty( $proodonto_review['unidade'] ) ) : ?>
									<figcaption class="svc-review__author">
										<?php echo esc_html( implode( ' · ', array_filter( array( $proodonto_review['nome'], __( 'Google', 'proodonto' ), $proodonto_review['unidade'] ) ) ) ); ?>
									</figcaption>
								<?php endif; ?>
							</figure>
						<?php endforeach; ?>
					</div>
					<?php if ( $proodonto_rev_link ) : ?>
						<a class="svc-link-arrow" href="<?php echo esc_url( $proodonto_rev_link ); ?>" rel="noopener"><?php esc_html_e( 'Ver todas no Google', 'proodonto' ); ?><?php echo proodonto_servicos_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $proodonto_content ) : ?>
			<!-- 08 FAQ (blocos proodonto/faq do editor) + qualquer conteúdo extra. -->
			<div class="svc-content">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<?php
		// 09 CTA por unidade.
		proodonto_servicos_render_units(
			$proodonto_units,
			__( 'Agende sua avaliação gratuita', 'proodonto' ),
			/* translators: %s: nome do tratamento */
			sprintf( __( 'Escolha a unidade. O WhatsApp abre com a conversa sobre %s.', 'proodonto' ), mb_strtolower( $proodonto_title ) ),
			$proodonto_title
		);
		?>

		<?php if ( $proodonto_related || $proodonto_hub ) : ?>
			<!-- Link interno nos dois sentidos (SEO-07). -->
			<nav class="svc-related" aria-label="<?php esc_attr_e( 'Conteúdo relacionado', 'proodonto' ); ?>">
				<div class="svc-container svc-container--text">
					<p class="svc-related__label"><?php esc_html_e( 'Continue lendo', 'proodonto' ); ?></p>
					<?php foreach ( $proodonto_related as $proodonto_related_id ) : ?>
						<?php if ( 'publish' !== get_post_status( $proodonto_related_id ) ) { continue; } ?>
						<a class="svc-related__link" href="<?php echo esc_url( get_permalink( $proodonto_related_id ) ); ?>"><span><?php echo esc_html( get_the_title( $proodonto_related_id ) ); ?></span><?php echo proodonto_servicos_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endforeach; ?>
					<?php if ( $proodonto_hub ) : ?>
						<a class="svc-related__link" href="<?php echo esc_url( get_permalink( $proodonto_hub ) ); ?>"><span><?php esc_html_e( 'Ver todos os tratamentos', 'proodonto' ); ?></span><?php echo proodonto_servicos_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endif; ?>
				</div>
			</nav>
		<?php endif; ?>

		<?php proodonto_servicos_render_sticky_bar( $proodonto_units, $proodonto_title ); ?>
	</main>

	<?php
endwhile;

get_footer();
