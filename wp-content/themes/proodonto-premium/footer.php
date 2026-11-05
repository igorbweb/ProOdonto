<?php
/**
 * Rodapé (componente único, igual em todas as páginas): endereço + horário
 * + WhatsApp + rota das 3 unidades, links dos tratamentos, responsável
 * técnico com CRO. Depois: barra fixa do celular e o visualizador de
 * resultados.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_units = proodonto_get_units();

// Tratamentos: menu do painel ou, sem menu, o hub + tratamentos publicados.
$proodonto_trat = proodonto_menu_items( 'footer_tratamentos' );
if ( ! $proodonto_trat ) {
	$proodonto_hub = proodonto_hub_url();
	if ( $proodonto_hub ) {
		$proodonto_trat[] = array( 'label' => __( 'Todos os tratamentos', 'proodonto' ), 'url' => $proodonto_hub, 'current' => false, 'strong' => true );
	}
	foreach ( proodonto_servicos_query()->posts as $proodonto_s ) {
		$proodonto_trat[] = array( 'label' => get_the_title( $proodonto_s ), 'url' => get_permalink( $proodonto_s ), 'current' => false );
	}
}

// A clínica: menu do painel ou, sem menu, páginas que existem.
$proodonto_inst = proodonto_menu_items( 'footer_clinica' );
if ( ! $proodonto_inst ) {
	$proodonto_sobre = get_page_by_path( 'sobre' );
	if ( $proodonto_sobre && 'publish' === $proodonto_sobre->post_status ) {
		$proodonto_inst[] = array( 'label' => get_the_title( $proodonto_sobre ), 'url' => get_permalink( $proodonto_sobre ), 'current' => false );
	}
	if ( proodonto_blog_url() ) {
		$proodonto_inst[] = array( 'label' => __( 'Blog', 'proodonto' ), 'url' => proodonto_blog_url(), 'current' => false );
	}
	if ( get_privacy_policy_url() ) {
		$proodonto_inst[] = array( 'label' => __( 'Política de privacidade', 'proodonto' ), 'url' => get_privacy_policy_url(), 'current' => false );
	}
}

$proodonto_resp = array_filter( array( proodonto_opt( 'responsavel_nome' ), trim( proodonto_opt( 'responsavel_cargo' ) . ' · ' . proodonto_opt( 'responsavel_cro' ), ' ·' ) ) );
$proodonto_text = (string) proodonto_opt( 'rodape_texto' );
$proodonto_legal = str_replace( '{ano}', wp_date( 'Y' ), (string) proodonto_opt( 'rodape_legal' ) );
?>
</main>

<footer class="site-footer" id="rodape">
	<?php echo proodonto_arc( 'band' ); // phpcs:ignore ?>
	<div class="wrap site-footer__grid">
		<div class="site-footer__brand">
			<a class="logo logo--light" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo proodonto_logo( 'light' ); // phpcs:ignore ?></a>
			<?php if ( $proodonto_text ) : ?>
				<p class="site-footer__text"><?php echo esc_html( $proodonto_text ); ?></p>
			<?php endif; ?>
			<?php
			echo proodonto_button( // phpcs:ignore
				array(
					'label' => (string) proodonto_opt( 'rodape_botao' ),
					'url'   => proodonto_units_anchor(),
					'small' => true,
					'attrs' => array( 'data-cta' => 'rodape' ),
				)
			);
			?>
			<?php $proodonto_redes = proodonto_rows( 'redes', 'option' ); ?>
			<?php if ( $proodonto_redes ) : ?>
				<ul class="social">
					<?php foreach ( $proodonto_redes as $proodonto_r ) : ?>
						<?php if ( empty( $proodonto_r['url'] ) || empty( $proodonto_r['rede'] ) ) { continue; } ?>
						<li><a href="<?php echo esc_url( $proodonto_r['url'] ); ?>" rel="noopener" aria-label="<?php echo esc_attr( sprintf( __( '%1$s da %2$s', 'proodonto' ), ucfirst( $proodonto_r['rede'] ), get_bloginfo( 'name' ) ) ); ?>"><?php echo proodonto_icon( $proodonto_r['rede'], 20 ); // phpcs:ignore ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="site-footer__main">
			<?php if ( $proodonto_units ) : ?>
				<section class="site-footer__units" aria-labelledby="ft-unid">
					<h2 id="ft-unid" class="eyebrow"><?php esc_html_e( 'Unidades', 'proodonto' ); ?></h2>
					<div class="site-footer__units-grid">
						<?php foreach ( $proodonto_units as $proodonto_u ) : ?>
							<?php $proodonto_page = proodonto_unit_page_url( $proodonto_u ); ?>
							<div class="ft-unit">
								<h3><?php echo esc_html( $proodonto_u['name'] ); ?></h3>
								<address>
									<?php echo esc_html( implode( ' – ', array_filter( array( $proodonto_u['street'], $proodonto_u['bairro'] ) ) ) ); ?><br>
									<?php echo esc_html( trim( $proodonto_u['name'] . ( $proodonto_u['uf'] ? ' – ' . $proodonto_u['uf'] : '' ) . ( $proodonto_u['cep'] ? ', ' . $proodonto_u['cep'] : '' ) ) ); ?>
								</address>
								<?php $proodonto_lines = proodonto_unit_hours_lines( $proodonto_u ); ?>
								<?php if ( $proodonto_lines ) : ?>
									<p class="ft-unit__hours"><?php echo implode( '<br>', array_map( 'esc_html', $proodonto_lines ) ); // phpcs:ignore ?></p>
								<?php endif; ?>
								<?php if ( $proodonto_u['phone'] ) : ?>
									<p class="ft-unit__hours"><a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $proodonto_u['phone'] ) ); ?>"><?php echo esc_html( $proodonto_u['phone'] ); ?></a></p>
								<?php endif; ?>
								<p class="ft-unit__links">
									<?php if ( $proodonto_u['whatsapp_url'] ) : ?>
										<a href="<?php echo esc_url( $proodonto_u['whatsapp_url'] ); ?>"<?php echo proodonto_attrs( proodonto_cta_attrs( $proodonto_u, 'rodape' ) ); // phpcs:ignore ?>><?php esc_html_e( 'WhatsApp', 'proodonto' ); ?></a>
									<?php endif; ?>
									<?php if ( $proodonto_u['maps_url'] ) : ?>
										<a href="<?php echo esc_url( $proodonto_u['maps_url'] ); ?>" rel="noopener" data-rota="<?php echo esc_attr( $proodonto_u['slug'] ); ?>"><?php esc_html_e( 'Como chegar', 'proodonto' ); ?></a>
									<?php endif; ?>
									<?php if ( $proodonto_page ) : ?>
										<a href="<?php echo esc_url( $proodonto_page ); ?>"><?php esc_html_e( 'Página da unidade', 'proodonto' ); ?></a>
									<?php endif; ?>
								</p>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<div class="site-footer__cols">
				<?php if ( $proodonto_trat ) : ?>
					<nav aria-labelledby="ft-trat">
						<h2 id="ft-trat" class="eyebrow"><?php esc_html_e( 'Tratamentos', 'proodonto' ); ?></h2>
						<ul class="ft-links">
							<?php foreach ( $proodonto_trat as $proodonto_i ) : ?>
								<li><a href="<?php echo esc_url( $proodonto_i['url'] ); ?>"<?php echo ! empty( $proodonto_i['strong'] ) ? ' class="is-strong"' : ''; ?>><?php echo esc_html( $proodonto_i['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
				<?php if ( $proodonto_inst ) : ?>
					<nav aria-labelledby="ft-inst">
						<h2 id="ft-inst" class="eyebrow"><?php esc_html_e( 'A clínica', 'proodonto' ); ?></h2>
						<ul class="ft-links">
							<?php foreach ( $proodonto_inst as $proodonto_i ) : ?>
								<li><a href="<?php echo esc_url( $proodonto_i['url'] ); ?>"><?php echo esc_html( $proodonto_i['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
				<?php if ( $proodonto_resp ) : ?>
					<div>
						<h2 class="eyebrow"><?php esc_html_e( 'Responsável técnico', 'proodonto' ); ?></h2>
						<p class="ft-resp"><?php echo implode( '<br>', array_map( 'esc_html', $proodonto_resp ) ); // phpcs:ignore ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php if ( $proodonto_legal ) : ?>
		<div class="site-footer__legal"><p class="wrap"><?php echo esc_html( $proodonto_legal ); ?></p></div>
	<?php endif; ?>
</footer>

<?php get_template_part( 'template-parts/sticky-bar' ); ?>

<dialog class="lightbox" id="po-lightbox" aria-label="<?php esc_attr_e( 'Resultado ampliado', 'proodonto' ); ?>">
	<button type="button" class="lightbox__close" data-lightbox-close aria-label="<?php esc_attr_e( 'Fechar', 'proodonto' ); ?>"><?php echo proodonto_icon( 'close', 22 ); // phpcs:ignore ?></button>
	<img class="lightbox__img" alt="" decoding="async">
	<p class="lightbox__cap"></p>
</dialog>

<?php wp_footer(); ?>
</body>
</html>
