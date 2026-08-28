<?php
/**
 * Página de Opções do Tema (ACF Pro) — dados globais usados em vários
 * lugares (ex.: telefone/WhatsApp no header), que não pertencem a
 * nenhuma página específica.
 *
 * Os campos em si ficam em inc/acf-fields.php (grupo "group_theme_options",
 * registrado via acf_add_local_field_group()). Se ACF Pro não estiver
 * ativo, este arquivo não faz nada — o restante do tema já lê esses
 * campos com fallback seguro.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Opções do Tema', 'proodonto' ),
			'menu_title' => __( 'Opções do Tema', 'proodonto' ),
			'menu_slug'  => 'proodonto-options',
			'capability' => 'manage_options',
			'redirect'   => false,
			'icon_url'   => 'dashicons-admin-generic',
			'position'   => 80,
		)
	);
} );

/**
 * Helper: telefone formatado para exibição (ex.: no header).
 */
function proodonto_get_phone() {
	return function_exists( 'get_field' ) ? (string) get_field( 'telefone', 'option' ) : '';
}

/**
 * Helper: WhatsApp só com dígitos, pronto para montar o link wa.me/.
 */
function proodonto_get_whatsapp() {
	$raw = function_exists( 'get_field' ) ? (string) get_field( 'whatsapp', 'option' ) : '';
	return preg_replace( '/\D+/', '', $raw );
}

/**
 * Helper: texto do único botão de CTA do header.
 */
function proodonto_get_header_cta_label() {
	$label = function_exists( 'get_field' ) ? (string) get_field( 'header_cta_label', 'option' ) : '';
	return $label ? $label : 'Agendar avaliação';
}

/**
 * Helper: URL do único botão de CTA do header. Sem valor próprio
 * cadastrado, cai no WhatsApp padrão do tema (mesmo campo usado no
 * rodapé e nas páginas).
 */
function proodonto_get_header_cta_url() {
	$url = function_exists( 'get_field' ) ? (string) get_field( 'header_cta_url', 'option' ) : '';
	if ( $url ) {
		return $url;
	}

	$whatsapp = proodonto_get_whatsapp();
	$whatsapp = $whatsapp ? $whatsapp : '5511300000000';
	return 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode( 'Olá! Gostaria de agendar uma avaliação.' );
}

/* -----------------------------------------------------------------------
 * Rodapé — texto institucional, redes sociais e colunas de links (ver
 * grupo ACF "group_theme_options_footer" em inc/acf-fields.php e o
 * consumo em footer.php). As colunas "Unidades" e "Contato" continuam
 * montadas diretamente em footer.php (fonte: inc/units-map.php e o
 * WhatsApp cadastrado acima) — não fazem parte deste repeater.
 * -------------------------------------------------------------------- */

/**
 * Helper: texto institucional exibido abaixo do logo, no rodapé.
 */
function proodonto_get_footer_text() {
	$text = function_exists( 'get_field' ) ? (string) get_field( 'footer_texto', 'option' ) : '';
	return $text ? $text : 'Rede de clínicas odontológicas especializada em implantes e próteses, com atendimento acolhedor há mais de 20 anos.';
}

/**
 * Helper: ícones de redes sociais do rodapé — label, ícone (SVG cru,
 * ainda sem sanitizar — quem ecoa decide, ver proodonto_sanitize_svg_fragment())
 * e link. Linhas com "Usar WhatsApp do tema" marcado ignoram o campo
 * "url" e apontam para o WhatsApp cadastrado nas Opções do Tema.
 *
 * @return array<int, array{label: string, icon: string, url: string}>
 */
function proodonto_get_footer_social_items() {
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'footer_redes_sociais', 'option' ) ) {
		return array();
	}

	$items = array();

	while ( have_rows( 'footer_redes_sociais', 'option' ) ) {
		the_row();

		$url = get_sub_field( 'url' );

		if ( get_sub_field( 'usar_whatsapp_padrao' ) ) {
			$whatsapp = proodonto_get_whatsapp();
			$whatsapp = $whatsapp ? $whatsapp : '5511300000000';
			$url      = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode( 'Olá! Gostaria de agendar uma avaliação na PRÓ-ODONTO.' );
		}

		$items[] = array(
			'label' => get_sub_field( 'label' ),
			'icon'  => get_sub_field( 'icone_svg' ),
			'url'   => $url ? $url : '#',
		);
	}

	return $items;
}

/**
 * Helper: colunas de links "livres" do rodapé (ex.: Tratamentos) — vêm
 * ANTES das colunas fixas "Unidades" e "Contato" montadas em footer.php.
 *
 * @return array<int, array{heading: string, links: array<int, array{label: string, url: string}>}>
 */
function proodonto_get_footer_link_columns() {
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'footer_links_colunas', 'option' ) ) {
		return array();
	}

	$columns = array();

	while ( have_rows( 'footer_links_colunas', 'option' ) ) {
		the_row();

		$links = array();

		if ( have_rows( 'links' ) ) {
			while ( have_rows( 'links' ) ) {
				the_row();

				$links[] = array(
					'label' => get_sub_field( 'label' ),
					'url'   => get_sub_field( 'url' ),
				);
			}
		}

		$columns[] = array(
			'heading' => get_sub_field( 'heading' ),
			'links'   => $links,
		);
	}

	return $columns;
}

/* -----------------------------------------------------------------------
 * Agregador de Links de Contato — modal disparado por qualquer CTA do
 * site (ver assets/js/main.js), exceto na Página de Vendas (ver
 * proodonto_is_vendas_page() em inc/template-functions.php, usado em
 * footer.php pra decidir se o modal é renderizado naquela página).
 * -------------------------------------------------------------------- */

/**
 * Helper: agregador está ativo nas Opções do Tema? Sem ACF, considera
 * desativado (nada pra abrir).
 */
function proodonto_link_aggregator_is_enabled() {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	return (bool) get_field( 'agregador_ativo', 'option' );
}

/**
 * Helper: título do modal do agregador.
 */
function proodonto_get_link_aggregator_title() {
	$title = function_exists( 'get_field' ) ? (string) get_field( 'agregador_titulo', 'option' ) : '';
	return $title ? $title : 'Fale com a unidade mais próxima';
}

/**
 * Helper: texto de apoio do modal do agregador.
 */
function proodonto_get_link_aggregator_text() {
	return function_exists( 'get_field' ) ? (string) get_field( 'agregador_texto', 'option' ) : '';
}

/**
 * Helper: itens (label/descrição/url) do modal do agregador — só os que
 * têm nome E link preenchidos.
 *
 * @return array<int, array{label: string, descricao: string, url: string}>
 */
function proodonto_get_link_aggregator_items() {
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'agregador_itens', 'option' ) ) {
		return array();
	}

	$items = array();

	while ( have_rows( 'agregador_itens', 'option' ) ) {
		the_row();

		$label = get_sub_field( 'label' );
		$url   = get_sub_field( 'url' );

		if ( ! $label || ! $url ) {
			continue;
		}

		$items[] = array(
			'label'     => $label,
			'descricao' => get_sub_field( 'descricao' ),
			'url'       => $url,
		);
	}

	return $items;
}
