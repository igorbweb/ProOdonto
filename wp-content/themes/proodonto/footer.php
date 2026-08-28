<?php
/**
 * Footer — mobile-first, global (todas as páginas).
 *
 * Texto institucional, redes sociais e as colunas "livres" de links (ex.:
 * Tratamentos) vêm das Opções do Tema — grupo ACF "Opções do Tema —
 * Rodapé" (ver inc/acf-fields.php e os helpers em inc/options-page.php).
 * As colunas "Unidades" e "Contato" continuam montadas aqui mesmo, a
 * partir das mesmas fontes de sempre (inc/units-map.php e o WhatsApp das
 * Opções do Tema) — de propósito, pra não duplicar/desatualizar a mesma
 * informação em dois lugares. CSS em assets/css/main.css — diferente do
 * CSS específico da Home (assets/css/pages/home.css), porque este footer
 * aparece em toda página do site.
 *
 * O logo usa o Custom Logo nativo do WP (Personalizar → Identidade do
 * site) se estiver configurado, com fallback pro nome do site — em vez
 * do blob: URL do protótipo original, que não existe fora daquele
 * ambiente.
 */

defined( 'ABSPATH' ) || exit;

// WhatsApp padrão do tema — vem das Opções do Tema (mesmo campo usado no
// header, ver proodonto_get_whatsapp() em inc/options-page.php). Na Página
// de Vendas, vira o custom field "cta_url" daquela página específica (ver
// page-vendas.php e proodonto_is_vendas_page() em inc/template-functions.php),
// pra cada campanha poder ter seu próprio destino também no footer. Sem
// valor preenchido em nenhum dos dois, cai no placeholder do tema.
$proodonto_footer_whatsapp     = function_exists( 'proodonto_get_whatsapp' ) ? proodonto_get_whatsapp() : '';
$proodonto_footer_whatsapp     = $proodonto_footer_whatsapp ? $proodonto_footer_whatsapp : '5511300000000';
$proodonto_footer_whatsapp_url = 'https://wa.me/' . $proodonto_footer_whatsapp . '?text=' . rawurlencode( 'Olá! Gostaria de agendar uma avaliação na PRÓ-ODONTO.' );

if ( function_exists( 'proodonto_is_vendas_page' ) && proodonto_is_vendas_page() && function_exists( 'get_field' ) ) {
	$proodonto_footer_vendas_cta_url = get_field( 'cta_url' );

	if ( $proodonto_footer_vendas_cta_url ) {
		$proodonto_footer_whatsapp_url = $proodonto_footer_vendas_cta_url;
	}
}

// Unidades reais — mesma fonte usada na seção "Unidades" da Home (ver
// inc/units-map.php), pra não duplicar/desatualizar em dois lugares.
$proodonto_footer_units_links = array();
foreach ( ( function_exists( 'proodonto_get_units' ) ? proodonto_get_units() : array() ) as $proodonto_footer_unit ) {
	$proodonto_footer_units_links[] = array(
		'label' => $proodonto_footer_unit['name'],
		'url'   => $proodonto_footer_unit['maps_url'],
	);
}

// Texto institucional, redes sociais e colunas "livres" (ex.: Tratamentos)
// vêm das Opções do Tema (grupo "Opções do Tema — Rodapé", ver
// inc/acf-fields.php e os helpers em inc/options-page.php). "Unidades" e
// "Contato" continuam montadas aqui mesmo, com as mesmas fontes de sempre
// (inc/units-map.php e o WhatsApp cadastrado nas Opções do Tema).
$proodonto_footer_text = function_exists( 'proodonto_get_footer_text' ) ? proodonto_get_footer_text() : '';

$proodonto_footer_columns   = function_exists( 'proodonto_get_footer_link_columns' ) ? proodonto_get_footer_link_columns() : array();
$proodonto_footer_columns[] = array(
	'heading' => 'Unidades',
	'links'   => $proodonto_footer_units_links,
);
$proodonto_footer_columns[] = array(
	'heading' => 'Contato',
	'links'   => array(
		array(
			'label'              => 'WhatsApp',
			'url'                => $proodonto_footer_whatsapp_url,
			'trigger_aggregator' => true,
		),
	),
);

$proodonto_footer_social = function_exists( 'proodonto_get_footer_social_items' ) ? proodonto_get_footer_social_items() : array();

/*
 * Agregador de Links de Contato (Opções do Tema) — modal com um link por
 * unidade, disparado por QUALQUER CTA do site (ver assets/js/main.js:
 * o clique em qualquer `a.cta` abre este <dialog> em vez de navegar
 * direto, só quando ele existe no DOM da página).
 *
 * De propósito, NÃO aparece na Página de Vendas: aquele template já é
 * usado como landing page de UMA unidade específica por campanha
 * (custom field "cta_url") — abrir um seletor de unidades ali competiria
 * com a própria campanha. Ver grupo ACF "Agregador de Links de Contato"
 * em inc/acf-fields.php.
 */
$proodonto_link_aggregator_items = array();

if (
	function_exists( 'proodonto_link_aggregator_is_enabled' ) && proodonto_link_aggregator_is_enabled()
	&& ! ( function_exists( 'proodonto_is_vendas_page' ) && proodonto_is_vendas_page() )
) {
	$proodonto_link_aggregator_items = function_exists( 'proodonto_get_link_aggregator_items' ) ? proodonto_get_link_aggregator_items() : array();
}
?>
	<footer class="site-footer" id="colophon">
		<div class="container">

			<div class="site-footer__top">

				<div class="site-footer__brand">
					<div class="site-footer__logo">
						<?php if ( has_custom_logo() ) : ?>
							<?php the_custom_logo(); ?>
						<?php else : ?>
							<img
								src="<?php echo esc_url( PROODONTO_URI . '/assets/images/logo-white.png' ); ?>"
								alt="<?php bloginfo( 'name' ); ?>"
								width="364"
								height="148"
								loading="lazy"
								class="site-footer__logo-img"
							/>
						<?php endif; ?>
					</div>

					<p class="site-footer__text"><?php echo esc_html( $proodonto_footer_text ); ?></p>

					<div class="site-footer__social">
						<?php foreach ( $proodonto_footer_social as $proodonto_social ) : ?>
							<a
								href="<?php echo esc_url( $proodonto_social['url'] ); ?>"
								class="site-footer__social-link"
								aria-label="<?php echo esc_attr( $proodonto_social['label'] ); ?>"
							>
								<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<?php echo proodonto_sanitize_svg_fragment( $proodonto_social['icon'] ); ?>
								</svg>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<?php foreach ( $proodonto_footer_columns as $proodonto_column ) : ?>
					<div class="site-footer__column">
						<p class="site-footer__heading"><?php echo esc_html( $proodonto_column['heading'] ); ?></p>
						<div class="site-footer__links">
							<?php foreach ( $proodonto_column['links'] as $proodonto_link ) : ?>
								<?php if ( ! empty( $proodonto_link['url'] ) && '#' !== $proodonto_link['url'] ) : ?>
									<a
										href="<?php echo esc_url( $proodonto_link['url'] ); ?>"
										<?php echo ! empty( $proodonto_link['trigger_aggregator'] ) ? 'data-link-aggregator-trigger' : ''; ?>
									><?php echo esc_html( $proodonto_link['label'] ); ?></a>
								<?php else : ?>
									<span class="site-footer__links-item--static"><?php echo esc_html( $proodonto_link['label'] ); ?></span>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>

			</div>

			<div class="site-footer__bottom">
				<span>
					&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
					<?php esc_html_e( 'Todos os direitos reservados.', 'proodonto' ); ?>
				</span>
				<span>CRO responsável · <a href="#">Política de privacidade</a></span>
			</div>

		</div>
	</footer>

	<?php if ( $proodonto_link_aggregator_items ) : ?>
		<dialog
			class="link-aggregator-modal"
			id="link-aggregator-modal"
			aria-label="<?php echo esc_attr( proodonto_get_link_aggregator_title() ); ?>"
		>
			<div class="link-aggregator-modal__inner">
				<button
					type="button"
					class="link-aggregator-modal__close"
					data-link-aggregator-close
					aria-label="<?php esc_attr_e( 'Fechar', 'proodonto' ); ?>"
				>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
				</button>

				<h2 class="link-aggregator-modal__title"><?php echo esc_html( proodonto_get_link_aggregator_title() ); ?></h2>

				<?php $proodonto_link_aggregator_text = proodonto_get_link_aggregator_text(); ?>
				<?php if ( $proodonto_link_aggregator_text ) : ?>
					<p class="link-aggregator-modal__text"><?php echo esc_html( $proodonto_link_aggregator_text ); ?></p>
				<?php endif; ?>

				<div class="link-aggregator-modal__list">
					<?php foreach ( $proodonto_link_aggregator_items as $proodonto_link_item ) : ?>
						<a
							href="<?php echo esc_url( $proodonto_link_item['url'] ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							class="link-aggregator-item"
						>
							<span class="link-aggregator-item__text">
								<span class="link-aggregator-item__label"><?php echo esc_html( $proodonto_link_item['label'] ); ?></span>
								<?php if ( $proodonto_link_item['descricao'] ) : ?>
									<span class="link-aggregator-item__desc"><?php echo esc_html( $proodonto_link_item['descricao'] ); ?></span>
								<?php endif; ?>
							</span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="link-aggregator-item__arrow"><path d="M9 18l6-6-6-6"/></svg>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</dialog>
	<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
