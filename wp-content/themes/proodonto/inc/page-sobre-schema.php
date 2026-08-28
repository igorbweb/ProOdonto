<?php
/**
 * JSON-LD (schema.org) específico da página "Sobre / Quem Somos"
 * (page-sobre.php) — usa o mesmo filtro 'proodonto_json_ld_graphs' já
 * usado por inc/seo.php (Organization/WebSite/BreadcrumbList) e
 * inc/blocks.php (FAQPage do bloco Gutenberg de FAQ).
 *
 * Acrescenta, só nesta página:
 *   - "AboutPage" (marca a página como a página institucional "sobre" da
 *     Organization) — sinal direto tanto para buscadores quanto para IAs
 *     generativas (GEO) sobre qual página responde "quem é essa empresa".
 *   - Enriquecimento da "Organization" com o corpo clínico, como lista de
 *     Person (jobTitle + identifier/CRO) — MESMO padrão já usado em
 *     inc/author-credentials.php para autores do blog (E-E-A-T).
 *   - "FAQPage", a partir do repeater "faq_itens" (grupo ACF
 *     "Sobre — Perguntas Frequentes").
 *
 * Salvaguarda importante: um profissional só entra no JSON-LD (schema
 * estruturado, lido por máquina) se o campo "CRO" estiver preenchido. Os
 * profissionais de EXEMPLO gravados por inc/content-seed.php nascem com
 * CRO vazio de propósito (ver inc/page-content-defaults.php) — então, por
 * padrão, nenhum profissional fictício é publicado como dado estruturado,
 * mesmo que o cliente esqueça de trocar a foto/texto visual antes de
 * publicar a página. Assim que um CRO real for preenchido, aquele
 * profissional passa a entrar no schema automaticamente.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'proodonto_json_ld_graphs', 'proodonto_sobre_json_ld_graphs' );

function proodonto_sobre_json_ld_graphs( $graphs ) {
	if ( ! is_page_template( 'page-sobre.php' ) || ! function_exists( 'get_field' ) ) {
		return $graphs;
	}

	$permalink = get_permalink();

	$graphs[] = array(
		'@type'      => 'AboutPage',
		'@id'        => $permalink . '#webpage',
		'url'        => $permalink,
		'name'       => get_the_title(),
		'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
		'about'      => array( '@id' => home_url( '/#organization' ) ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	$organization = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$mission = get_field( 'missao_texto' );
	if ( $mission ) {
		$organization['description'] = wp_strip_all_tags( $mission );
	}

	$employees = proodonto_sobre_get_employee_schema();
	if ( $employees ) {
		$organization['employee'] = $employees;
	}

	$graphs[] = $organization;

	$faq_schema = proodonto_sobre_get_faq_schema();
	if ( $faq_schema ) {
		$graphs[] = $faq_schema;
	}

	return $graphs;
}

/**
 * Monta a lista de Person (schema.org) a partir do repeater "equipe_itens"
 * — só profissionais com CRO preenchido (ver aviso no topo do arquivo).
 */
function proodonto_sobre_get_employee_schema() {
	if ( ! have_rows( 'equipe_itens' ) ) {
		return array();
	}

	$people = array();

	while ( have_rows( 'equipe_itens' ) ) {
		the_row();

		$nome = get_sub_field( 'nome' );
		$cro  = get_sub_field( 'cro' );

		if ( ! $nome || ! $cro ) {
			continue;
		}

		$person = array(
			'@type'      => 'Person',
			'name'       => wp_strip_all_tags( $nome ),
			'identifier' => wp_strip_all_tags( $cro ),
		);

		$cargo = get_sub_field( 'cargo' );
		if ( $cargo ) {
			$person['jobTitle'] = wp_strip_all_tags( $cargo );
		}

		$people[] = $person;
	}

	return $people;
}

/**
 * Monta o schema "FAQPage" a partir do repeater "faq_itens" (grupo ACF
 * "Sobre — Perguntas Frequentes"). Mesmo formato usado em inc/blocks.php
 * para o bloco Gutenberg de FAQ, só que lendo de custom fields.
 */
function proodonto_sobre_get_faq_schema() {
	if ( ! have_rows( 'faq_itens' ) ) {
		return null;
	}

	$questions = array();

	while ( have_rows( 'faq_itens' ) ) {
		the_row();

		$pergunta = get_sub_field( 'pergunta' );
		$resposta = get_sub_field( 'resposta' );

		if ( ! $pergunta || ! $resposta ) {
			continue;
		}

		$questions[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $pergunta ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $resposta ),
			),
		);
	}

	if ( ! $questions ) {
		return null;
	}

	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => $questions,
	);
}
