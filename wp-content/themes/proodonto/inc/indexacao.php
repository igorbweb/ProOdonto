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
 */

defined( 'ABSPATH' ) || exit;

/** Páginas que saem do ar com 301 para a home. */
const PROODONTO_RETIRED_PAGES = array( 'timeline' );

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

		if ( in_array( strtolower( trim( $rel, '/' ) ), PROODONTO_RETIRED_PAGES, true ) ) {
			wp_safe_redirect( home_url( '/' ), 301, 'PróOdonto' );
			exit;
		}
	},
	0
);
