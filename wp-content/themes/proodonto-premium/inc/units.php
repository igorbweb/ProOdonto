<?php
/**
 * Unidades — fonte única (Opções do Tema → aba "Unidades").
 *
 * Cada item normalizado tem as mesmas chaves que o tema antigo usava em
 * inc/units-map.php (name, address, maps_url, whatsapp_url, hours,
 * phone) e mais as que o layout novo pede (slug, bairro, nota, total,
 * foto, página...). Cartões, seletor de unidade, rodapé, FAQ de horário,
 * schema Dentist e destino dos CTAs leem DAQUI — nada de endereço ou
 * link escrito em template.
 *
 * Regra do projeto: todo CTA vai para o UpView da unidade (nunca wa.me,
 * nunca número de exemplo).
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array<int, array> Unidades normalizadas, na ordem do painel.
 */
function proodonto_get_units( $fresh = false ) {
	static $units = null;
	if ( null !== $units && ! $fresh ) {
		return $units;
	}

	$units = array();
	foreach ( proodonto_rows( 'unidades', 'option' ) as $row ) {
		$name = trim( (string) ( $row['nome'] ?? '' ) );
		if ( '' === $name ) {
			continue;
		}

		$hours = array();
		foreach ( (array) ( $row['horarios'] ?? array() ) as $h ) {
			if ( empty( $h['abre'] ) || empty( $h['fecha'] ) ) {
				continue;
			}
			$hours[] = array(
				'label'  => (string) ( $h['rotulo'] ?? '' ),
				'short'  => (string) ( $h['rotulo_curto'] ?? '' ),
				'days'   => array_values( (array) ( $h['dias'] ?? array() ) ),
				'opens'  => substr( (string) $h['abre'], 0, 5 ),
				'closes' => substr( (string) $h['fecha'], 0, 5 ),
			);
		}

		$slug     = sanitize_title( ! empty( $row['slug'] ) ? $row['slug'] : $name );
		$street   = trim( (string) ( $row['endereco'] ?? '' ) );
		$bairro   = trim( (string) ( $row['bairro'] ?? '' ) );
		$uf       = trim( (string) ( $row['uf'] ?? '' ) );
		$cep      = str_replace( '-', "\u{2011}", trim( (string) ( $row['cep'] ?? '' ) ) ); // Hífen inquebrável: o CEP não quebra linha.
		$page_id  = (int) ( is_object( $row['pagina'] ?? null ) ? $row['pagina']->ID : ( $row['pagina'] ?? 0 ) );

		$units[] = array(
			'name'         => $name,
			'slug'         => $slug,
			'bairro'       => $bairro,
			'street'       => $street,
			'uf'           => $uf,
			'cep'          => $cep,
			// Endereço completo no formato do tema antigo (schema/mapas).
			'address'      => str_replace( "\u{2011}", '-', trim( $street . ( $bairro ? ' - ' . $bairro : '' ) . ', ' . $name . ( $uf ? ' - ' . $uf : '' ) . ( $cep ? ', ' . $cep : '' ), ', ' ) ),
			'maps_url'     => (string) ( $row['maps_url'] ?? '' ),
			'whatsapp_url' => (string) ( $row['whatsapp_url'] ?? '' ),
			'google_url'   => (string) ( $row['google_url'] ?? '' ),
			'phone'        => (string) ( $row['telefone'] ?? '' ),
			'hours'        => $hours,
			'nota'         => (string) ( $row['nota'] ?? '' ),
			'total'        => (string) ( $row['total'] ?? '' ),
			'foto'         => $row['foto'] ?? 0,
			'page_id'      => $page_id,
		);
	}

	return $units;
}

/** Choices para os selects de unidade do ACF (slug => nome). */
function proodonto_unit_choices() {
	$choices = array();
	foreach ( proodonto_get_units() as $unit ) {
		$choices[ $unit['slug'] ] = $unit['name'];
	}
	return $choices;
}

function proodonto_get_unit_by_slug( $slug ) {
	foreach ( proodonto_get_units() as $unit ) {
		if ( $unit['slug'] === $slug ) {
			return $unit;
		}
	}
	return null;
}

/**
 * Unidade da página atual: campo "Unidade" da página de unidade; sem
 * campo, o slug da página (/aracaju/, /lagarto/, /simao-dias/).
 */
function proodonto_get_current_unit_slug() {
	if ( ! is_singular( 'page' ) ) {
		return '';
	}
	$id    = get_queried_object_id();
	$field = (string) proodonto_field( 'uni_unidade', $id );
	if ( $field && proodonto_get_unit_by_slug( $field ) ) {
		return $field;
	}
	$slug = (string) get_post_field( 'post_name', $id );
	return proodonto_get_unit_by_slug( $slug ) ? $slug : '';
}

function proodonto_get_current_unit() {
	$slug = proodonto_get_current_unit_slug();
	return $slug ? proodonto_get_unit_by_slug( $slug ) : null;
}

/** Outras unidades (para "Outras unidades"). */
function proodonto_get_other_units( $slug ) {
	return array_values(
		array_filter(
			proodonto_get_units(),
			static function ( $u ) use ( $slug ) {
				return $u['slug'] !== $slug;
			}
		)
	);
}

/** "09:00" → "9h", "08:30" → "8h30". */
function proodonto_hour( $time ) {
	$parts = explode( ':', (string) $time );
	$h     = (int) ( $parts[0] ?? 0 );
	$m     = (int) ( $parts[1] ?? 0 );
	return $h . 'h' . ( $m ? sprintf( '%02d', $m ) : '' );
}

/**
 * Horário em linhas, juntando faixas do mesmo rótulo:
 * ["Seg a sex 8h–13h e 14h–19h", "Sáb 8h–12h"].
 *
 * @param bool $short Usa o rótulo curto ("Seg a sex") em vez do longo.
 */
function proodonto_unit_hours_lines( $unit, $short = true ) {
	$groups = array();
	foreach ( (array) ( $unit['hours'] ?? array() ) as $h ) {
		$label = ( $short && $h['short'] ) ? $h['short'] : ( $h['label'] ? $h['label'] : $h['short'] );
		$groups[ $label ][] = proodonto_hour( $h['opens'] ) . '–' . proodonto_hour( $h['closes'] );
	}

	$lines = array();
	foreach ( $groups as $label => $ranges ) {
		$lines[] = trim( $label . ' ' . implode( ' e ', $ranges ) );
	}
	return $lines;
}

/** "Seg a sex 9h–19h · Sáb 8h–12h". */
function proodonto_unit_hours_text( $unit, $sep = ' · ' ) {
	return implode( $sep, proodonto_unit_hours_lines( $unit ) );
}

/** Só a primeira linha (dias úteis) — seletor do celular. */
function proodonto_unit_hours_first( $unit ) {
	$lines = proodonto_unit_hours_lines( $unit );
	return $lines ? $lines[0] : '';
}

/** "Av. Pres. Tancredo Neves, 1028 · Jardins · 49025-620". */
function proodonto_unit_address_line( $unit, $with_cep = true ) {
	return implode( ' · ', array_filter( array( $unit['street'], $unit['bairro'], $with_cep ? $unit['cep'] : '' ) ) );
}

/** Endereço com cidade/UF: "… · Jardins · Aracaju – SE · 49025-620". */
function proodonto_unit_address_full( $unit ) {
	$city = $unit['name'] . ( $unit['uf'] ? ' – ' . $unit['uf'] : '' );
	return implode( ' · ', array_filter( array( $unit['street'], $unit['bairro'], $city, $unit['cep'] ) ) );
}

/** Página da unidade (campo "Página" ou slug igual), se publicada. */
function proodonto_unit_page_url( $unit ) {
	$page = $unit['page_id'] ? get_post( $unit['page_id'] ) : get_page_by_path( $unit['slug'] );
	return ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : '';
}

/**
 * Destino do WhatsApp (UpView) de uma unidade. Num tratamento, o link
 * próprio daquele tratamento (campo cta_url_<slug>) tem prioridade — a
 * mensagem já chega falando do tratamento (CONV-04).
 */
function proodonto_unit_cta_url( $unit, $servico_id = 0 ) {
	if ( $servico_id ) {
		$own = (string) proodonto_field( 'cta_url_' . str_replace( '-', '_', $unit['slug'] ), $servico_id );
		if ( $own && ! proodonto_is_legacy_cta_url( $own ) ) {
			return $own;
		}
	}
	return $unit['whatsapp_url'];
}

/** Unidade padrão dos CTAs fora das páginas de unidade (a primeira do painel). */
function proodonto_default_unit() {
	$units = proodonto_get_units();
	return $units ? $units[0] : null;
}

/**
 * URL antiga que nunca pode ser destino de CTA (wa.me, api.whatsapp,
 * número de exemplo, placeholder de template).
 */
function proodonto_is_legacy_cta_url( $url ) {
	$u = rawurldecode( html_entity_decode( trim( (string) $url ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	if ( '' === $u || false !== stripos( $u, 'upviewcrm.com' ) ) {
		return false;
	}
	return (bool) preg_match( '~(^|//)(www\.)?wa\.me/|(^|//)(api|web)\.whatsapp\.com/|^whatsapp://|5511300000000|\{\{\s*URL_[A-Z_]+\s*\}\}~i', $u );
}

/**
 * Atributos de rastreamento de um CTA de WhatsApp. O JS (main.js) repassa
 * utm_*, fbclid e gclid da URL atual para o link no clique e dispara
 * `cta_click` no dataLayer (GTM) + `Contact` no Pixel, se houver.
 *
 * @param array  $unit  Unidade.
 * @param string $place Onde está o botão (hero, barra, unidades...).
 */
function proodonto_cta_attrs( $unit, $place ) {
	return array(
		'data-cta'      => $place,
		'data-cta-unit' => $unit ? $unit['slug'] : '',
	);
}
