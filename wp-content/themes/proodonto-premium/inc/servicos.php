<?php
/**
 * Tratamentos: CPT `servico` em /servicos/<tratamento>/.
 *
 * Igual ao tema antigo (mesmo nome de CPT e mesmo slug, para nenhuma URL
 * mudar): sem arquivo — o hub /servicos/ é uma PÁGINA com texto próprio
 * (page-servicos.php) — e sem cidade na URL nem no title (SEO-01).
 */

defined( 'ABSPATH' ) || exit;

const PROODONTO_SERVICOS_REWRITE = 3;

add_action( 'init', 'proodonto_register_servico_cpt' );

function proodonto_register_servico_cpt() {
	register_post_type(
		'servico',
		array(
			'labels'        => array(
				'name'               => __( 'Tratamentos', 'proodonto' ),
				'singular_name'      => __( 'Tratamento', 'proodonto' ),
				'add_new'            => __( 'Adicionar tratamento', 'proodonto' ),
				'add_new_item'       => __( 'Adicionar novo tratamento', 'proodonto' ),
				'edit_item'          => __( 'Editar tratamento', 'proodonto' ),
				'new_item'           => __( 'Novo tratamento', 'proodonto' ),
				'view_item'          => __( 'Ver tratamento', 'proodonto' ),
				'search_items'       => __( 'Buscar tratamentos', 'proodonto' ),
				'not_found'          => __( 'Nenhum tratamento encontrado', 'proodonto' ),
				'not_found_in_trash' => __( 'Nenhum tratamento na lixeira', 'proodonto' ),
				'menu_name'          => __( 'Tratamentos', 'proodonto' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => false,
			'hierarchical'  => false,
			'menu_icon'     => 'dashicons-heart',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			'rewrite'       => array(
				'slug'       => 'servicos',
				'with_front' => false,
			),
		)
	);

	if ( (int) get_option( 'proodonto_servicos_rewrite' ) !== PROODONTO_SERVICOS_REWRITE ) {
		flush_rewrite_rules( false );
		update_option( 'proodonto_servicos_rewrite', PROODONTO_SERVICOS_REWRITE );
	}
}

/** Tratamentos publicados, na ordem do campo "Ordem". */
function proodonto_servicos_query() {
	static $query = null;
	if ( null === $query ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'servico',
				'post_status'    => 'publish',
				'posts_per_page' => 30,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'no_found_rows'  => true,
			)
		);
	}
	return $query;
}

/**
 * Só IDs publicados, na ordem dada (relationship do ACF pode trazer
 * rascunho — rascunho nunca vira card no ar).
 */
function proodonto_published_ids( $ids ) {
	return array_values(
		array_filter(
			array_map( 'intval', (array) $ids ),
			static function ( $id ) {
				return $id && 'publish' === get_post_status( $id );
			}
		)
	);
}

/**
 * Dados de card de um tratamento.
 *
 * @return array{id:int,title:string,url:string,desc:string,icon:string,thumb:int}|null
 */
function proodonto_servico_card( $id ) {
	$id = (int) $id;
	if ( ! $id || 'publish' !== get_post_status( $id ) ) {
		return null;
	}

	$desc = (string) proodonto_field( 'card_para_quem', $id );
	if ( '' === $desc && has_excerpt( $id ) ) {
		$desc = get_the_excerpt( $id );
	}

	return array(
		'id'    => $id,
		'title' => get_the_title( $id ),
		'url'   => get_permalink( $id ),
		'desc'  => $desc,
		'icon'  => (string) proodonto_field( 'srv_icone', $id ),
		'thumb' => (int) get_post_thumbnail_id( $id ),
	);
}

/**
 * Trilha do Yoast (visível e BreadcrumbList): insere o hub entre "Início"
 * e o tratamento — o CPT não tem arquivo, então o Yoast pularia (SEO-05).
 */
add_filter(
	'wpseo_breadcrumb_links',
	static function ( $links ) {
		if ( ! is_singular( 'servico' ) || count( $links ) < 2 ) {
			return $links;
		}
		$hub = proodonto_page_by_template( 'page-servicos.php' );
		if ( ! $hub ) {
			return $links;
		}
		array_splice(
			$links,
			count( $links ) - 1,
			0,
			array(
				array(
					'url'  => get_permalink( $hub ),
					'text' => get_the_title( $hub ),
					'id'   => $hub->ID,
				),
			)
		);
		return $links;
	}
);
