<?php
/**
 * Indexação: o que entra no índice do Google e no sitemap do Yoast, e
 * redirecionamentos de URLs duplicadas.
 *
 * - Categorias, tags e arquivo de autor: `noindex, follow` e fora do
 *   sitemap (competiam com os próprios posts no Search Console).
 * - Páginas aposentadas (/timeline/, teste de 2024): fora do sitemap e
 *   301 para a home.
 * - Cache do sitemap desligado (o post-sitemap ficou parado em 06/09/2026
 *   sem os posts novos).
 * - Unidades, /sobre e /blog sem barra final: 301 para a versão com barra
 *   (/lagarto sem barra chegou a ter 229 impressões próprias).
 * - Posts mesclados: 301 para o post que os absorveu (PROODONTO_POST_REDIRECTS).
 * - Posts recolhidos para rascunho (tratamento × cidade, canibalizavam a
 *   home e a /aracaju/ — decisão de 31/10/2026): 301 para o caminho de
 *   destino enquanto não houver post PUBLICADO com o slug
 *   (PROODONTO_RETIRED_POSTS). Quando /servicos/<tratamento>/ for ao ar,
 *   trocar o destino aqui.
 */

defined( 'ABSPATH' ) || exit;

/** Páginas que saem do ar com 301 para a home. */
const PROODONTO_RETIRED_PAGES = array( 'timeline' );

/**
 * Posts mesclados → post de destino (301). O redirecionamento só liga
 * quando o post de destino está PUBLICADO, então este arquivo pode ir para
 * produção antes da publicação sem quebrar nada.
 */
const PROODONTO_POST_REDIRECTS = array(
	'como-escolher-implantodontista' => 'como-escolher-clinica-odontologica',
	// verificar-cro-especialista-odontologia saiu desta lista em 31/10/2026:
	// a mescla foi cancelada em 13/10 e o post é o que mais traz clique no blog.
);

/**
 * Posts recolhidos para rascunho → caminho de destino (301). Só redireciona
 * quando NÃO existe post publicado com o slug, então o arquivo pode ir para
 * produção antes ou depois de o post virar rascunho.
 */
const PROODONTO_RETIRED_POSTS = array(
	'protese-dentaria-aracaju' => '/aracaju/', // futuro: /servicos/protese-dentaria/
	'implantodontista-aracaju' => '/aracaju/', // futuro: /servicos/implante-dentario/
);

/** Caminhos que recebem 301 para a versão com barra final. */
const PROODONTO_SLASH_PATHS = array( '/aracaju', '/lagarto', '/simao-dias', '/sobre', '/blog' );

add_filter(
	'wpseo_robots_array',
	static function ( $robots ) {
		if ( is_category() || is_tag() || is_author() ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	},
	99
);

add_filter(
	'wpseo_sitemap_exclude_taxonomy',
	static function ( $exclude, $taxonomy ) {
		return in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ? true : $exclude;
	},
	10,
	2
);

add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );

add_filter(
	'wpseo_exclude_from_sitemap_by_post_ids',
	static function ( $ids ) {
		foreach ( PROODONTO_RETIRED_PAGES as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page instanceof WP_Post ) {
				$ids[] = (int) $page->ID;
			}
		}
		return array_values( array_unique( array_map( 'intval', (array) $ids ) ) );
	}
);

add_filter( 'wpseo_enable_xml_sitemap_transient_caching', '__return_false' );

add_action(
	'template_redirect',
	static function () {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
		if ( is_admin() || ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
			return;
		}

		$uri   = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path  = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
		$home  = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
		$rel   = '/' . ltrim( substr( $path, strlen( $home ) ), '/' );

		if ( in_array( strtolower( $rel ), PROODONTO_SLASH_PATHS, true ) ) {
			wp_safe_redirect( home_url( $rel . '/' ) . ( $query ? '?' . $query : '' ), 301, 'PróOdonto' );
			exit;
		}

		$slug = strtolower( trim( $rel, '/' ) );
		if ( isset( PROODONTO_POST_REDIRECTS[ $slug ] ) ) {
			$target = get_page_by_path( PROODONTO_POST_REDIRECTS[ $slug ], OBJECT, 'post' );
			if ( $target instanceof WP_Post && 'publish' === $target->post_status ) {
				wp_safe_redirect( get_permalink( $target ) . ( $query ? '?' . $query : '' ), 301, 'PróOdonto' );
				exit;
			}
		}

		if ( isset( PROODONTO_RETIRED_POSTS[ $slug ] ) ) {
			$live = get_page_by_path( $slug, OBJECT, 'post' );
			if ( ! ( $live instanceof WP_Post && 'publish' === $live->post_status ) ) {
				wp_safe_redirect( home_url( PROODONTO_RETIRED_POSTS[ $slug ] ) . ( $query ? '?' . $query : '' ), 301, 'PróOdonto' );
				exit;
			}
		}

		if ( in_array( strtolower( trim( $rel, '/' ) ), PROODONTO_RETIRED_PAGES, true ) ) {
			wp_safe_redirect( home_url( '/' ), 301, 'PróOdonto' );
			exit;
		}
	},
	0
);
