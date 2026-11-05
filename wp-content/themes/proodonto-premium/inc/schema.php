<?php
/**
 * Dados estruturados — sempre DENTRO do grafo do Yoast (filtro
 * wpseo_schema_graph), nunca um <script> JSON-LD solto (SEO-06). Sem
 * Yoast ativo, o tema imprime um grafo próprio mínimo.
 *
 * Nada é inventado: cada propriedade só sai se o dado existir no painel.
 * aggregateRating NÃO é publicado (avaliações de terceiros/Google).
 *
 * Outros arquivos podem acrescentar nós pelo filtro de sempre:
 * `proodonto_json_ld_graphs`.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_yoast_active() {
	return defined( 'WPSEO_VERSION' );
}

add_filter(
	'wpseo_schema_graph',
	static function ( $graph ) {
		$extra = apply_filters( 'proodonto_json_ld_graphs', array() );
		return $extra ? array_merge( (array) $graph, $extra ) : $graph;
	},
	20
);

/** Sem Yoast: grafo próprio (Organization/WebSite na home + nós do tema). */
add_action(
	'wp_head',
	static function () {
		if ( proodonto_yoast_active() ) {
			return;
		}
		$graph = array();
		if ( is_front_page() ) {
			$graph[] = array(
				'@type' => 'WebSite',
				'@id'   => home_url( '/#website' ),
				'url'   => home_url( '/' ),
				'name'  => get_bloginfo( 'name' ),
			);
		}
		$graph = apply_filters( 'proodonto_json_ld_graphs', $graph );
		if ( $graph ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
	},
	20
);

/* -----------------------------------------------------------------------
 * Peças
 * -------------------------------------------------------------------- */

function proodonto_schema_org_id() {
	return home_url( '/#organization' );
}

function proodonto_schema_logo_url() {
	$id = (int) proodonto_opt( 'logo' );
	if ( ! $id ) {
		$id = (int) get_theme_mod( 'custom_logo' );
	}
	return $id ? (string) wp_get_attachment_image_url( $id, 'full' ) : '';
}

function proodonto_schema_organization() {
	$org = array(
		'@type' => array( 'Organization', 'MedicalOrganization' ),
		'@id'   => proodonto_schema_org_id(),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);
	$desc = (string) proodonto_opt( 'rodape_texto' );
	if ( $desc ) {
		$org['description'] = $desc;
	}
	$logo = proodonto_schema_logo_url();
	if ( $logo ) {
		$org['logo'] = $logo;
	}
	$same = array_values( array_filter( wp_list_pluck( proodonto_rows( 'redes', 'option' ), 'url' ) ) );
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	$dentists = array();
	foreach ( proodonto_get_units() as $unit ) {
		$dentists[] = array( '@id' => proodonto_schema_dentist_id( $unit ) );
	}
	if ( $dentists ) {
		$org['subOrganization'] = $dentists;
	}
	return $org;
}

function proodonto_schema_dentist_id( $unit ) {
	$url = proodonto_unit_page_url( $unit );
	return $url ? $url . '#dentist' : home_url( '/#dentist-' . $unit['slug'] );
}

function proodonto_schema_dentist( $unit ) {
	if ( ! $unit['street'] ) {
		return null;
	}
	$url     = proodonto_unit_page_url( $unit );
	$dentist = array(
		'@type'              => 'Dentist',
		'@id'                => proodonto_schema_dentist_id( $unit ),
		'name'               => get_bloginfo( 'name' ) . ' ' . $unit['name'],
		'url'                => $url ? $url : home_url( '/#unidades' ),
		'parentOrganization' => array( '@id' => proodonto_schema_org_id() ),
		'address'            => array_filter(
			array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => trim( $unit['street'] . ( $unit['bairro'] ? ' - ' . $unit['bairro'] : '' ) ),
				'addressLocality' => $unit['name'],
				'addressRegion'   => $unit['uf'],
				'postalCode'      => str_replace( "\u{2011}", '-', $unit['cep'] ),
				'addressCountry'  => 'BR',
			)
		),
	);
	if ( $unit['maps_url'] ) {
		$dentist['hasMap'] = $unit['maps_url'];
	}
	if ( $unit['phone'] ) {
		$dentist['telephone'] = $unit['phone'];
	}
	$img = $unit['foto'] ? wp_get_attachment_image_url( (int) $unit['foto'], 'large' ) : proodonto_schema_logo_url();
	if ( $img ) {
		$dentist['image'] = $img;
	}
	$hours = array();
	foreach ( $unit['hours'] as $h ) {
		if ( ! $h['days'] ) {
			continue;
		}
		$map     = array( 'Mo' => 'Monday', 'Tu' => 'Tuesday', 'We' => 'Wednesday', 'Th' => 'Thursday', 'Fr' => 'Friday', 'Sa' => 'Saturday', 'Su' => 'Sunday' );
		$hours[] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array_values( array_intersect_key( $map, array_flip( $h['days'] ) ) ),
			'opens'     => $h['opens'],
			'closes'    => $h['closes'],
		);
	}
	if ( $hours ) {
		$dentist['openingHoursSpecification'] = $hours;
	}
	return $dentist;
}

function proodonto_schema_faq( $items ) {
	$entities = array();
	foreach ( $items as $item ) {
		$q = trim( wp_strip_all_tags( $item['q'] ) );
		$a = trim( wp_strip_all_tags( $item['a'] ) );
		if ( $q && $a ) {
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $q,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $a,
				),
			);
		}
	}
	if ( ! $entities ) {
		return null;
	}
	return array(
		'@type'      => 'FAQPage',
		'@id'        => get_permalink() . '#faq',
		'mainEntity' => $entities,
	);
}

/**
 * Perguntas da página atual (repeater ACF do template; senão, blocos do
 * editor). Mesmo conjunto que aparece na tela.
 *
 * @return array<int, array{q:string,a:string}>
 */
function proodonto_current_faq_items() {
	$field = '';
	if ( is_page_template( 'page-home.php' ) ) {
		$field = 'home_faq';
	} elseif ( is_page_template( 'page-vendas.php' ) ) {
		$field = 'uni_faq';
	} elseif ( is_page_template( 'page-servicos.php' ) ) {
		$field = 'hub_faq';
	} elseif ( is_singular( 'servico' ) ) {
		$field = 'srv_faq';
	}

	$items = array();
	if ( $field ) {
		foreach ( proodonto_rows( $field ) as $row ) {
			if ( ! empty( $row['pergunta'] ) ) {
				$items[] = array(
					'q' => proodonto_tokens( $row['pergunta'] ),
					'a' => proodonto_tokens( $row['resposta'] ?? '' ),
				);
			}
		}
	}
	if ( ! $items && is_singular() ) {
		$items = proodonto_block_faq_items( get_queried_object() );
	}
	return $items;
}

/* -----------------------------------------------------------------------
 * Montagem por página
 * -------------------------------------------------------------------- */
add_filter( 'proodonto_json_ld_graphs', 'proodonto_theme_graph' );

function proodonto_theme_graph( $graph ) {
	$is_home = is_front_page() || is_page_template( 'page-home.php' );
	$unit    = is_page_template( 'page-vendas.php' ) ? proodonto_get_current_unit() : null;

	if ( $is_home || $unit || is_singular( 'servico' ) || is_page_template( 'page-servicos.php' ) ) {
		$graph[] = proodonto_schema_organization();
	}

	if ( $is_home ) {
		foreach ( proodonto_get_units() as $u ) {
			$node = proodonto_schema_dentist( $u );
			if ( $node ) {
				$graph[] = $node;
			}
		}
	}

	if ( $unit ) {
		$node = proodonto_schema_dentist( $unit );
		if ( $node ) {
			$graph[] = $node;
			$graph[] = array(
				'@type'      => 'WebPage',
				'@id'        => get_permalink(),
				'about'      => array( '@id' => $node['@id'] ),
				'mainEntity' => array( '@id' => $node['@id'] ),
			);
		}
	}

	if ( is_singular( 'servico' ) ) {
		$post_id   = get_queried_object_id();
		$permalink = get_permalink( $post_id );
		$procedure = array(
			'@type'             => 'MedicalProcedure',
			'@id'               => $permalink . '#procedimento',
			'name'              => wp_strip_all_tags( get_the_title( $post_id ) ),
			'url'               => $permalink,
			'relevantSpecialty' => 'https://schema.org/Dentistry',
			'provider'          => array( '@id' => proodonto_schema_org_id() ),
		);
		$resposta = (string) proodonto_field( 'resposta_direta', $post_id );
		if ( $resposta ) {
			$procedure['description'] = wp_strip_all_tags( $resposta );
		}
		$preparo = (string) proodonto_field( 'pre_requisitos', $post_id );
		if ( $preparo ) {
			$procedure['preparation'] = wp_strip_all_tags( $preparo );
		}
		$passos = array();
		foreach ( proodonto_rows( 'etapas', $post_id ) as $i => $etapa ) {
			if ( ! empty( $etapa['titulo'] ) ) {
				$passos[] = ( $i + 1 ) . '. ' . rtrim( wp_strip_all_tags( $etapa['titulo'] . ( ! empty( $etapa['descricao'] ) ? ': ' . $etapa['descricao'] : '' ) ), '.' ) . '.';
			}
		}
		if ( $passos ) {
			$procedure['howPerformed'] = implode( ' ', $passos );
		}
		$graph[] = $procedure;
		$graph[] = array(
			'@type'      => 'WebPage',
			'@id'        => $permalink,
			'about'      => array( '@id' => $procedure['@id'] ),
			'mainEntity' => array( '@id' => $procedure['@id'] ),
		);
	}

	if ( is_page_template( 'page-servicos.php' ) ) {
		$elements = array();
		$position = 0;
		foreach ( proodonto_hub_servico_ids() as $id ) {
			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => ++$position,
				'url'      => get_permalink( $id ),
				'name'     => wp_strip_all_tags( get_the_title( $id ) ),
			);
		}
		if ( $elements ) {
			$graph[] = array(
				'@type'           => 'ItemList',
				'@id'             => get_permalink() . '#tratamentos',
				'name'            => wp_strip_all_tags( get_the_title() ),
				'itemListElement' => $elements,
			);
		}
	}

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$page    = array(
			'@type'        => 'WebPage',
			'@id'          => get_permalink( $post_id ),
			'lastReviewed' => get_the_modified_date( 'Y-m-d', $post_id ),
		);
		if ( function_exists( 'proodonto_get_reviewer_schema' ) ) {
			$page['reviewedBy'] = proodonto_get_reviewer_schema( $post_id );
		}
		$graph[] = $page;

		if ( ! proodonto_yoast_active() ) {
			$graph[] = array(
				'@type'         => 'BlogPosting',
				'@id'           => get_permalink( $post_id ) . '#article',
				'headline'      => wp_strip_all_tags( get_the_title( $post_id ) ),
				'datePublished' => get_the_date( 'c', $post_id ),
				'dateModified'  => get_the_modified_date( 'c', $post_id ),
				'author'        => function_exists( 'proodonto_get_author_schema' ) ? proodonto_get_author_schema( (int) get_post_field( 'post_author', $post_id ) ) : null,
				'publisher'     => array( '@id' => proodonto_schema_org_id() ),
			);
		}
	}

	if ( is_singular() ) {
		$faq = proodonto_schema_faq( proodonto_current_faq_items() );
		if ( $faq ) {
			$graph[] = $faq;
		}
	}

	return $graph;
}

/** IDs dos tratamentos do hub, na ordem dos grupos (sem repetir). */
function proodonto_hub_servico_ids( $post_id = null ) {
	$ids = array();
	foreach ( proodonto_rows( 'hub_grupos', $post_id ) as $grupo ) {
		$ids = array_merge( $ids, array( (int) ( $grupo['destaque'] ?? 0 ) ), (array) ( $grupo['servicos'] ?? array() ) );
	}
	$ids = array_values( array_unique( proodonto_published_ids( $ids ) ) );
	if ( ! $ids ) {
		$ids = wp_list_pluck( proodonto_servicos_query()->posts, 'ID' );
	}
	return $ids;
}
