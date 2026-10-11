<?php
/**
 * Destino dos CTAs — todo botão de contato do site vai para o UpView.
 *
 * Regra do projeto (10/10/2026): nenhum CTA aponta para wa.me,
 * api.whatsapp.com, para o número de exemplo do tema (5511300000000) ou
 * para o domínio antigo proodonto.com.br. Nas páginas de unidade, o
 * destino é o UpView daquela unidade; no resto do site (home, sobre,
 * blog, posts), o UpView de Aracaju — com o atributo
 * `data-link-aggregator-trigger`, para o clique abrir o seletor de
 * unidade quando o agregador estiver ativo (assets/js/main.js).
 *
 * Fonte única dos links: proodonto_get_units() em inc/units-map.php.
 */

defined( 'ABSPATH' ) || exit;

/** Unidade usada quando a página não tem cidade (a de mais visitas). */
const PROODONTO_DEFAULT_CTA_UNIT = 'aracaju';

/**
 * Link do UpView de uma unidade, pelo slug (aracaju, lagarto, simao-dias).
 */
function proodonto_get_unit_cta_url( $slug ) {
	if ( function_exists( 'proodonto_get_unit_by_slug' ) ) {
		$unit = proodonto_get_unit_by_slug( $slug );
		if ( $unit && ! empty( $unit['whatsapp_url'] ) ) {
			return $unit['whatsapp_url'];
		}
	}
	return '';
}

/**
 * Link padrão dos CTAs fora das páginas de unidade.
 */
function proodonto_get_default_cta_url() {
	return proodonto_get_unit_cta_url( PROODONTO_DEFAULT_CTA_UNIT );
}

/**
 * URL antiga que não pode mais ser destino de CTA?
 * (WhatsApp direto, número de exemplo, agregador por URL, placeholder
 * de template não resolvido.)
 */
function proodonto_is_legacy_cta_url( $url ) {
	$u = rawurldecode( html_entity_decode( trim( (string) $url ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

	if ( '' === $u || false !== stripos( $u, 'upviewcrm.com' ) ) {
		return false;
	}

	return (bool) preg_match(
		'~(^|//)(www\.)?wa\.me/|(^|//)(api|web)\.whatsapp\.com/|^whatsapp://|5511300000000|#agregador-links$|\{\{\s*URL_[A-Z_]+\s*\}\}~i',
		$u
	);
}

/**
 * Resolve o destino de um CTA: mantém uma URL válida cadastrada no
 * wp-admin; troca vazia ou antiga pelo UpView da unidade (ou o padrão).
 *
 * @param string $url  URL cadastrada (ACF, Opções do Tema...) — pode ser vazia.
 * @param string $slug Unidade da página, se houver.
 */
function proodonto_resolve_cta_url( $url = '', $slug = '' ) {
	$url = (string) $url;

	if ( '' !== trim( $url ) && ! proodonto_is_legacy_cta_url( $url ) ) {
		return $url;
	}

	$unit_url = $slug ? proodonto_get_unit_cta_url( $slug ) : '';

	return $unit_url ? $unit_url : proodonto_get_default_cta_url();
}

/**
 * Conteúdo de posts/páginas salvo no banco (Gutenberg) ainda pode ter
 * botões antigos — ex.: `{{URL_AGENDAMENTO}}` no post de aparelho. Este
 * filtro corrige só o HTML do conteúdo (the_content), nunca a página
 * inteira. O certo continua sendo editar o post; o filtro evita link
 * quebrado enquanto isso não acontece.
 */
add_filter(
	'the_content',
	static function ( $content ) {
		if ( is_admin() || false === stripos( $content, '<a' ) ) {
			return $content;
		}

		$dead_links = array( '/lente-de-contato-dental-ou-faceta/' );
		$slug       = function_exists( 'proodonto_get_current_unit_slug' ) ? proodonto_get_current_unit_slug() : '';

		$fixed = preg_replace_callback(
			'~(<a\b[^>]*>)(.*?)(</a>)~is',
			static function ( $m ) use ( $dead_links, $slug ) {
				if ( ! preg_match( '~\shref\s*=\s*(["\'])(.*?)\1~i', $m[1], $h ) ) {
					return $m[0];
				}

				foreach ( $dead_links as $dead ) {
					if ( false !== stripos( $h[2], $dead ) ) {
						return $m[2]; // link morto → só o texto.
					}
				}

				if ( ! proodonto_is_legacy_cta_url( $h[2] ) ) {
					return $m[0];
				}

				$open = str_replace( $h[0], ' href="' . esc_url( proodonto_resolve_cta_url( '', $slug ) ) . '"', $m[1] );
				if ( ! $slug && false === stripos( $open, 'data-link-aggregator-trigger' ) ) {
					$open = preg_replace( '~^<a\b~i', '<a data-link-aggregator-trigger', $open, 1 );
				}

				return $open . $m[2] . $m[3];
			},
			$content
		);

		return is_string( $fixed ) ? $fixed : $content;
	},
	30
);
