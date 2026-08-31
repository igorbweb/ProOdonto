<?php
/**
 * JSON-LD (schema.org) de "negócio local" — o que faz o resultado do site
 * no Google (e em IAs generativas, GEO) ilustrar serviços, telefone,
 * endereço e redes sociais em vez de só nome + link.
 *
 * Usa o mesmo filtro 'proodonto_json_ld_graphs' já usado por inc/seo.php
 * (Organization/WebSite/BreadcrumbList) e inc/page-sobre-schema.php: cada
 * callback acrescenta nós ao mesmo @graph, e nós com o MESMO @id (aqui,
 * sempre home_url('/#organization')) são o mesmo recurso combinado pelos
 * processadores de JSON-LD (Google incluso) — não sobrescrevem o nó base.
 *
 * Só roda na Home (page-home.php), onde essas informações já aparecem
 * visualmente (seção "Tratamentos", seção "Unidades" com endereço real de
 * cada clínica, WhatsApp/telefone do header) — o schema aqui é só a MESMA
 * informação, também em formato lido por máquina:
 *
 *   - Organization (mesmo @id do nó base em inc/seo.php) ganha telefone,
 *     redes sociais (sameAs), logo/imagem, descrição institucional e o
 *     catálogo de tratamentos (hasOfferCatalog), a partir do repeater ACF
 *     "treatments_itens" (grupo "Home — Tratamentos").
 *   - Uma entidade "Dentist" por unidade real (Aracaju, Lagarto, Simão
 *     Dias — fonte única: proodonto_get_units(), a mesma usada pelo mapa
 *     estático e pelos cards da seção "Unidades"), com endereço estruturado
 *     (PostalAddress), link do Google Maps e vínculo (parentOrganization)
 *     com a Organization acima.
 *
 * Nada aqui é inventado: cada campo só é publicado se o dado real já
 * existir (Opções do Tema, ACF, units-map.php). Telefone/WhatsApp ainda
 * não cadastrado, por exemplo, fica de fora — nunca é publicado o número
 * de exemplo usado como fallback visual em outras partes do tema (ver
 * proodonto_get_header_cta_url() em inc/options-page.php), pra não sair
 * dado estruturado errado no ar.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'proodonto_json_ld_graphs', 'proodonto_local_business_json_ld_graphs' );

function proodonto_local_business_json_ld_graphs( $graphs ) {
	if ( ! is_page_template( 'page-home.php' ) ) {
		return $graphs;
	}

	$organization_id = home_url( '/#organization' );

	/* -------------------------------------------------------------
	 * Organization — enriquecimento do nó já criado em inc/seo.php
	 * (mesmo @id, ver comentário no topo do arquivo).
	 * ---------------------------------------------------------- */
	$organization = array(
		'@type' => 'Organization',
		'@id'   => $organization_id,
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$footer_text = function_exists( 'proodonto_get_footer_text' ) ? proodonto_get_footer_text() : '';
	if ( $footer_text ) {
		$organization['description'] = wp_strip_all_tags( $footer_text );
	}

	$logo_url = function_exists( 'proodonto_get_logo_url' ) ? proodonto_get_logo_url() : '';
	if ( $logo_url ) {
		$organization['logo']  = $logo_url;
		$organization['image'] = $logo_url;
	}

	$telephone = proodonto_local_business_telephone();
	if ( $telephone ) {
		$organization['telephone'] = $telephone;
	}

	$same_as = proodonto_get_sameas_urls();
	if ( $same_as ) {
		$organization['sameAs'] = $same_as;
	}

	$catalog = proodonto_get_treatments_offer_catalog( $organization_id );
	if ( $catalog ) {
		$organization['hasOfferCatalog'] = $catalog;
	}

	$graphs[] = $organization;

	/* -------------------------------------------------------------
	 * Dentist — uma entidade por unidade real (endereço, mapa),
	 * vinculada à Organization acima.
	 * ---------------------------------------------------------- */
	foreach ( proodonto_get_units() as $unit ) {
		if ( empty( $unit['name'] ) || empty( $unit['address'] ) ) {
			continue;
		}

		$dentist = array(
			'@type'              => 'Dentist',
			'@id'                => home_url( '/#unidade-' . sanitize_title( $unit['name'] ) ),
			'name'               => get_bloginfo( 'name' ) . ' — ' . $unit['name'],
			'url'                => home_url( '/#unidades' ),
			'address'            => proodonto_parse_br_address( $unit['address'] ),
			'parentOrganization' => array( '@id' => $organization_id ),
		);

		if ( $telephone ) {
			$dentist['telephone'] = $telephone;
		}

		if ( $logo_url ) {
			$dentist['image'] = $logo_url;
		}

		if ( ! empty( $unit['maps_url'] ) ) {
			$dentist['hasMap'] = $unit['maps_url'];
		}

		$graphs[] = $dentist;
	}

	return $graphs;
}

/**
 * Telefone/WhatsApp cadastrado nas Opções do Tema, formatado como
 * schema.org espera (com DDI). Retorna '' se nada estiver cadastrado —
 * o chamador decide se omite o campo (nunca cai no número de exemplo
 * usado como fallback visual em outras partes do tema).
 */
function proodonto_local_business_telephone() {
	$whatsapp = function_exists( 'proodonto_get_whatsapp' ) ? proodonto_get_whatsapp() : '';
	if ( $whatsapp ) {
		return '+' . $whatsapp;
	}

	$telefone = function_exists( 'proodonto_get_phone' ) ? trim( (string) proodonto_get_phone() ) : '';
	return $telefone;
}

/**
 * Redes sociais reais (Instagram, Facebook, etc.) cadastradas no repeater
 * "footer_redes_sociais" (Opções do Tema → Rodapé). Linhas marcadas "Usar
 * WhatsApp do tema" ficam de fora de propósito: sameAs é para outros
 * perfis/páginas da MESMA entidade (redes sociais, Wikipédia...), não
 * para um link de conversa/agendamento.
 *
 * @return array<int, string>
 */
function proodonto_get_sameas_urls() {
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'footer_redes_sociais', 'option' ) ) {
		return array();
	}

	$urls = array();

	while ( have_rows( 'footer_redes_sociais', 'option' ) ) {
		the_row();

		if ( get_sub_field( 'usar_whatsapp_padrao' ) ) {
			continue;
		}

		$url = get_sub_field( 'url' );
		if ( $url ) {
			$urls[] = esc_url_raw( $url );
		}
	}

	return array_values( array_unique( array_filter( $urls ) ) );
}

/**
 * Catálogo de tratamentos (schema.org OfferCatalog/Service), a partir do
 * repeater ACF "treatments_itens" da Home (grupo "Home — Tratamentos") —
 * a mesma fonte usada pelos cards da seção "Tratamentos" em page-home.php.
 * Sem link por item de propósito (não há página própria de tratamento
 * ainda — ver comentário em page-home.php), só nome + descrição.
 *
 * @return array|null
 */
function proodonto_get_treatments_offer_catalog( $organization_id ) {
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'treatments_itens' ) ) {
		return null;
	}

	$items = array();

	while ( have_rows( 'treatments_itens' ) ) {
		the_row();

		$titulo = get_sub_field( 'titulo' );
		if ( ! $titulo ) {
			continue;
		}

		$service = array(
			'@type'    => 'Service',
			'name'     => wp_strip_all_tags( $titulo ),
			'provider' => array( '@id' => $organization_id ),
		);

		$texto = get_sub_field( 'texto' );
		if ( $texto ) {
			$service['description'] = wp_strip_all_tags( $texto );
		}

		$items[] = array(
			'@type'       => 'Offer',
			'itemOffered' => $service,
		);
	}

	if ( ! $items ) {
		return null;
	}

	return array(
		'@type'           => 'OfferCatalog',
		'name'            => 'Tratamentos',
		'itemListElement' => $items,
	);
}

/**
 * Quebra um endereço no formato usado em proodonto_get_units() —
 * "Rua Fulano, 123 - Bairro, Cidade - UF, 00000-000" — em PostalAddress
 * (schema.org). O formato é fixo (mesmas 3 unidades cadastradas em
 * inc/units-map.php), mas, se algum dia fugir do padrão (vírgulas a
 * mais/a menos), cai num fallback seguro: endereço inteiro em
 * "streetAddress", sem quebrar cidade/UF/CEP.
 *
 * @return array
 */
function proodonto_parse_br_address( $address ) {
	$parts = array_map( 'trim', explode( ',', $address ) );

	// Formato esperado: [Rua, "Número - Bairro", "Cidade - UF", CEP].
	if ( 4 === count( $parts ) && preg_match( '/^(.+?)\s*-\s*([A-Z]{2})$/u', $parts[2], $city_state ) ) {
		return array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $parts[0] . ', ' . $parts[1],
			'addressLocality' => trim( $city_state[1] ),
			'addressRegion'   => trim( $city_state[2] ),
			'postalCode'      => $parts[3],
			'addressCountry'  => 'BR',
		);
	}

	return array(
		'@type'          => 'PostalAddress',
		'streetAddress'  => $address,
		'addressCountry' => 'BR',
	);
}
