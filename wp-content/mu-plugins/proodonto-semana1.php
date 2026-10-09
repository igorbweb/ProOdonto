<?php
/**
 * Plugin Name: PróOdonto — Ajustes da Semana 1 (CTAs no UpView + SEO técnico)
 * Description: Garante que todo botão de contato do site leve ao UpView, tira do índice arquivos que competem com os posts e corrige URLs duplicadas. Independe do tema: sobrevive a deploy/troca de arquivos do tema.
 * Version: 1.0.0
 * Author: Alfama Web
 *
 * POR QUE UM MU-PLUGIN (e não só editar o tema)
 * ---------------------------------------------
 * Os links quebrados vêm de três lugares diferentes, e dois deles não estão
 * no código do tema:
 *   1. fallbacks do tema (`wa.me/5511300000000` quando o WhatsApp das Opções
 *      do Tema está vazio — page-home.php, footer.php, page-sobre.php,
 *      inc/options-page.php, template-parts/content-single.php);
 *   2. conteúdo salvo no banco (blocos/HTML das páginas de Lagarto e Simão
 *      Dias, corpo dos posts com `{{URL_AGENDAMENTO}}`, `wa.me/?text=`);
 *   3. campos ACF (banner da home apontando para `proodonto.com.br/#agregador-links`).
 * Um filtro na saída HTML pega os três de uma vez, sem depender de saber
 * exatamente o que está publicado em produção.
 *
 * O QUE FAZ
 * ---------
 * A) CTAs → UpView (filtro na saída HTML, só no front-end):
 *    Todo <a> cujo href seja WhatsApp direto (wa.me, api.whatsapp.com),
 *    o número de exemplo 5511300000000, o agregador (`#agregador-links`,
 *    inclusive `https://proodonto.com.br/#agregador-links`) ou um
 *    placeholder não resolvido (`{{URL_…}}`) passa a apontar para o UpView:
 *      - o texto do link cita uma cidade        → UpView daquela unidade;
 *      - página de unidade (/aracaju/, /lagarto/, /simao-dias/) → UpView da unidade;
 *      - qualquer outra página (home, sobre, blog, posts) → UpView de Aracaju
 *        + atributo `data-link-aggregator-trigger`. Com o agregador ativo nas
 *        Opções do Tema, o clique abre o seletor de unidade (assets/js/main.js
 *        já escuta esse atributo); sem JS, sem agregador ou em robôs, o link
 *        leva a Aracaju, que é a unidade com mais visitas.
 *    Links já apontando para o UpView não são tocados.
 *    Links internos mortos conhecidos viram texto simples (ver $dead_links).
 *    Cabeçalho de resposta `X-Proodonto-CTA: <n>` = quantos links foram
 *    corrigidos naquela página (útil pra conferir no DevTools → Rede).
 *
 * B) Índice e sitemap (Yoast):
 *    - `noindex, follow` em categorias, tags e arquivo de autor;
 *    - categorias, tags e autores fora do sitemap;
 *    - /timeline/ (página de teste de 2024) fora do sitemap + 301 para a home;
 *    - cache do sitemap desligado (o post-sitemap estava parado em 06/09).
 *
 * C) URLs duplicadas: /aracaju, /lagarto, /simao-dias, /sobre e /blog sem
 *    barra final → 301 para a versão com barra.
 *
 * Para desligar tudo: apagar este arquivo de wp-content/mu-plugins/.
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Configuração
 * ---------------------------------------------------------------------- */

/**
 * Links do UpView por unidade. Usa o mapa do tema (inc/units-map.php) quando
 * existir — mesma fonte de verdade — e cai nos valores fixos se o tema mudar.
 *
 * @return array<string,string> slug => URL do UpView
 */
function proodonto_s1_upview_links() {
	$links = array(
		'aracaju'    => 'https://api.upviewcrm.com/go/proodonto-aracaju/lp-aracaju',
		'lagarto'    => 'https://api.upviewcrm.com/go/proodonto-lagarto/lp-lagarto',
		'simao-dias' => 'https://api.upviewcrm.com/go/proodonto-simao-dias/lp-simao-dias',
	);

	if ( function_exists( 'proodonto_get_units' ) ) {
		foreach ( (array) proodonto_get_units() as $unit ) {
			if ( empty( $unit['name'] ) || empty( $unit['whatsapp_url'] ) ) {
				continue;
			}
			$slug = sanitize_title( $unit['name'] );
			if ( isset( $links[ $slug ] ) && false !== strpos( $unit['whatsapp_url'], 'upviewcrm.com' ) ) {
				$links[ $slug ] = $unit['whatsapp_url'];
			}
		}
	}

	return $links;
}

/** Unidade padrão quando a página não tem cidade (pedido do Igor: Aracaju). */
const PROODONTO_S1_DEFAULT_UNIT = 'aracaju';

/** Slugs de página que são unidade. */
const PROODONTO_S1_UNIT_SLUGS = array( 'aracaju', 'lagarto', 'simao-dias' );

/** Caminhos que recebem 301 para a versão com barra final. */
const PROODONTO_S1_SLASH_PATHS = array( '/aracaju', '/lagarto', '/simao-dias', '/sobre', '/blog' );

/** Páginas que saem do ar com 301 para a home. */
const PROODONTO_S1_RETIRED_PAGES = array( 'timeline' );

/* -------------------------------------------------------------------------
 * A) Reescrita dos CTAs
 * ---------------------------------------------------------------------- */

/**
 * O href precisa ser trocado pelo UpView?
 */
function proodonto_s1_is_bad_cta_href( $href ) {
	$h = html_entity_decode( trim( (string) $href ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$h = rawurldecode( $h );

	if ( '' === $h ) {
		return false;
	}

	if ( false !== stripos( $h, 'upviewcrm.com' ) ) {
		return false; // já está certo.
	}

	return (bool) preg_match(
		'~(^|//)(www\.)?wa\.me/'                 // wa.me com ou sem número
		. '|(^|//)(api|web)\.whatsapp\.com/'     // api.whatsapp.com / web.whatsapp.com
		. '|^whatsapp://'                        // esquema do app
		. '|5511300000000'                       // número de exemplo do tema
		. '|#agregador-links$'                   // gatilho do agregador (inclui proodonto.com.br/#agregador-links)
		. '|\{\{\s*URL_[A-Z_]+\s*\}\}~i',          // placeholder não resolvido
		$h
	);
}

/**
 * Cidade citada no texto visível / aria-label / title do link.
 */
function proodonto_s1_city_in_text( $text ) {
	$t = remove_accents( strtolower( wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) ) );

	$found = array();
	if ( false !== strpos( $t, 'aracaju' ) ) {
		$found[] = 'aracaju';
	}
	if ( false !== strpos( $t, 'lagarto' ) ) {
		$found[] = 'lagarto';
	}
	if ( false !== strpos( $t, 'simao dias' ) || false !== strpos( $t, 'simao-dias' ) ) {
		$found[] = 'simao-dias';
	}

	// Só decide quando o link cita UMA cidade (um texto que lista as três não diz qual).
	return 1 === count( $found ) ? $found[0] : '';
}

/**
 * Unidade da página atual, pelo primeiro segmento da URL.
 */
function proodonto_s1_current_unit() {
	static $unit = null;

	if ( null !== $unit ) {
		return $unit;
	}

	$path  = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH );
	$home  = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$path  = '/' . ltrim( substr( $path, strlen( rtrim( $home, '/' ) ) ), '/' );
	$first = strtolower( (string) strtok( trim( $path, '/' ), '/' ) );

	$unit = in_array( $first, PROODONTO_S1_UNIT_SLUGS, true ) ? $first : '';

	return $unit;
}

/**
 * Troca (ou acrescenta) um atributo numa tag de abertura <a ...>.
 */
function proodonto_s1_set_attr( $tag, $name, $value ) {
	$pattern = '~(\s' . preg_quote( $name, '~' ) . '\s*=\s*)("[^"]*"|\'[^\']*\'|[^\s>]+)~i';

	if ( null === $value ) { // atributo booleano: só acrescenta se ainda não existir.
		return preg_match( '~\s' . preg_quote( $name, '~' ) . '(?=[\s=/>])~i', $tag ) ? $tag : preg_replace( '~^<a\b~i', '<a ' . $name, $tag, 1 );
	}

	$quoted = '"' . esc_attr( $value ) . '"';

	if ( preg_match( $pattern, $tag ) ) {
		return preg_replace_callback(
			$pattern,
			static function ( $m ) use ( $quoted ) {
				return $m[1] . $quoted;
			},
			$tag,
			1
		);
	}

	return preg_replace( '~^<a\b~i', '<a ' . $name . '=' . $quoted, $tag, 1 );
}

/**
 * Reescreve os links de um trecho de HTML.
 *
 * @param string $html
 * @param string $page_unit Unidade da página ('' fora das páginas de unidade).
 * @param int    $count     Saída: quantos links foram alterados.
 * @return string
 */
function proodonto_s1_rewrite_ctas( $html, $page_unit, &$count = 0 ) {
	$count = 0;

	if ( false === stripos( $html, '<a' ) ) {
		return $html;
	}

	$links      = proodonto_s1_upview_links();
	$dead_links = array(
		'/lente-de-contato-dental-ou-faceta/',
	);

	return preg_replace_callback(
		'~(<a\b[^>]*>)(.*?)(</a>)~is',
		static function ( $m ) use ( $links, $dead_links, $page_unit, &$count ) {
			list( , $open, $inner, $close ) = $m;

			if ( ! preg_match( '~\shref\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))~i', $open, $hm ) ) {
				return $m[0];
			}
			$href = isset( $hm[4] ) && '' !== $hm[4] ? $hm[4] : ( isset( $hm[3] ) && '' !== $hm[3] ? $hm[3] : $hm[2] );

			// Links internos mortos → só o texto.
			foreach ( $dead_links as $dead ) {
				if ( false !== stripos( $href, $dead ) ) {
					$count++;
					return $inner;
				}
			}

			if ( ! proodonto_s1_is_bad_cta_href( $href ) ) {
				return $m[0];
			}

			$label = $inner;
			if ( preg_match( '~\s(aria-label|title)\s*=\s*("([^"]*)"|\'([^\']*)\')~i', $open, $lm ) ) {
				$label .= ' ' . ( isset( $lm[4] ) && '' !== $lm[4] ? $lm[4] : $lm[3] );
			}

			$unit    = proodonto_s1_city_in_text( $label );
			$trigger = false;

			if ( ! $unit ) {
				$unit = $page_unit;
			}
			if ( ! $unit ) {
				$unit    = PROODONTO_S1_DEFAULT_UNIT;
				$trigger = true; // fora das unidades: abre o seletor quando ele existir.
			}

			$open = proodonto_s1_set_attr( $open, 'href', $links[ $unit ] );
			// Itens do próprio seletor não podem reabrir o seletor.
			if ( $trigger && ! preg_match( '~class\s*=\s*["\'][^"\']*link-aggregator-item~i', $open ) ) {
				$open = proodonto_s1_set_attr( $open, 'data-link-aggregator-trigger', null );
			}

			$count++;
			return $open . $inner . $close;
		},
		$html
	);
}

/**
 * Liga o filtro na saída da página inteira (cabeçalho, conteúdo, ACF e rodapé).
 */
add_action(
	'template_redirect',
	static function () {
		if (
			is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_robots() || is_trackback()
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
			|| is_customize_preview()
			|| get_query_var( 'sitemap' ) || get_query_var( 'sitemap_n' ) || get_query_var( 'xsl' )
		) {
			return;
		}

		$page_unit = proodonto_s1_current_unit();

		ob_start(
			static function ( $buffer ) use ( $page_unit ) {
				// Só HTML.
				foreach ( headers_list() as $header ) {
					if ( 0 === stripos( $header, 'content-type:' ) && false === stripos( $header, 'text/html' ) ) {
						return $buffer;
					}
				}
				if ( false === stripos( $buffer, '<html' ) ) {
					return $buffer;
				}

				$count = 0;
				$out   = proodonto_s1_rewrite_ctas( $buffer, $page_unit, $count );

				if ( null === $out ) { // erro de regex: devolve a página intacta.
					return $buffer;
				}
				if ( ! headers_sent() ) {
					header( 'X-Proodonto-CTA: ' . (int) $count );
				}
				return $out;
			}
		);
	},
	PHP_INT_MAX
);

/* -------------------------------------------------------------------------
 * B) Índice e sitemap (Yoast SEO)
 * ---------------------------------------------------------------------- */

// noindex, follow em categorias, tags e autor (competem com os posts).
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

// Fora do sitemap: categorias, tags e autores.
add_filter(
	'wpseo_sitemap_exclude_taxonomy',
	static function ( $exclude, $taxonomy ) {
		return in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ? true : $exclude;
	},
	10,
	2
);
add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );

// Fora do sitemap: páginas aposentadas (/timeline/).
add_filter(
	'wpseo_exclude_from_sitemap_by_post_ids',
	static function ( $ids ) {
		foreach ( PROODONTO_S1_RETIRED_PAGES as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page instanceof WP_Post ) {
				$ids[] = (int) $page->ID;
			}
		}
		return array_values( array_unique( array_map( 'intval', (array) $ids ) ) );
	}
);

// Sitemap sempre gerado na hora (o cache deixou o post-sitemap parado em 06/09).
add_filter( 'wpseo_enable_xml_sitemap_transient_caching', '__return_false' );

/* -------------------------------------------------------------------------
 * C) Redirecionamentos 301
 * ---------------------------------------------------------------------- */

add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || ! in_array( strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET' ), array( 'GET', 'HEAD' ), true ) ) {
			return;
		}

		$uri   = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path  = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
		$home  = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
		$rel   = '/' . ltrim( substr( $path, strlen( $home ) ), '/' );

		// Sem barra final → com barra (mantém a query string: UTM, fbclid...).
		if ( in_array( strtolower( $rel ), PROODONTO_S1_SLASH_PATHS, true ) ) {
			wp_safe_redirect( home_url( $rel . '/' ) . ( $query ? '?' . $query : '' ), 301, 'PróOdonto' );
			exit;
		}

		// Páginas aposentadas → home.
		foreach ( PROODONTO_S1_RETIRED_PAGES as $slug ) {
			if ( in_array( strtolower( trim( $rel, '/' ) ), array( $slug ), true ) ) {
				wp_safe_redirect( home_url( '/' ), 301, 'PróOdonto' );
				exit;
			}
		}
	},
	0
);
