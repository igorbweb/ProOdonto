<?php
/**
 * Conteúdo do editor: sumário do post, CTA no meio do texto e correção
 * de links antigos de WhatsApp.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processa o conteúdo do post para o template single.php:
 *   - dá id estável a cada H2 (âncora do sumário "Neste artigo");
 *   - insere o cartão de CTA antes do H2 nº N (campo do post);
 *   - devolve o sumário.
 *
 * @return array{html:string, toc:array<int, array{id:string,label:string}>}
 */
function proodonto_post_content( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$html    = apply_filters( 'the_content', get_the_content( null, false, $post_id ) );
	$html    = str_replace( ']]>', ']]&gt;', $html );
	$toc     = array();
	$used    = array();
	$count   = 0;

	$cta_at   = (int) proodonto_field( 'post_cta_posicao', $post_id );
	$cta_html = $cta_at > 0 ? proodonto_post_inline_cta( $post_id ) : '';

	$html = preg_replace_callback(
		'~<h2(\s[^>]*)?>(.*?)</h2>~is',
		static function ( $m ) use ( &$toc, &$used, &$count, $cta_at, $cta_html ) {
			$count++;
			$attrs = isset( $m[1] ) ? $m[1] : '';
			$label = trim( wp_strip_all_tags( $m[2] ) );

			if ( preg_match( '~\sid=(["\'])(.*?)\1~i', $attrs, $idm ) ) {
				$id = $idm[2];
			} else {
				$base = sanitize_title( $label );
				$base = $base ? $base : 'secao-' . $count;
				$id   = $base;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;

			if ( '' !== $label ) {
				$toc[] = array(
					'id'    => $id,
					'label' => $label,
				);
			}

			$before = ( $cta_html && $count === $cta_at ) ? $cta_html : '';

			return $before . '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		$html
	);

	return array(
		'html' => $html,
		'toc'  => $toc,
	);
}

/** Cartão "Sua prótese quebrou?" que entra no meio do texto. */
function proodonto_post_inline_cta( $post_id ) {
	$title = (string) proodonto_field( 'post_cta_titulo', $post_id );
	$text  = (string) proodonto_field( 'post_cta_texto', $post_id );
	$unit  = proodonto_default_unit();
	if ( ! $title || ! $unit ) {
		return '';
	}

	return sprintf(
		'<a class="card tap inline-cta" href="%1$s"%2$s><span><span class="inline-cta__t">%3$s</span>%4$s</span><span class="chip chip--solid" aria-hidden="true">%5$s</span></a>',
		'#agendar', // Faixa final do post, com o seletor das 3 unidades.
		proodonto_attrs( array( 'data-cta' => 'post-meio' ) ),
		esc_html( $title ),
		$text ? '<span class="inline-cta__d">' . esc_html( $text ) . '</span>' : '',
		proodonto_icon( 'arrow', 18 )
	);
}

/** "**trecho**" → <strong> (resposta rápida). */
function proodonto_bold( $text ) {
	$text = esc_html( (string) $text );
	return preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $text );
}

/**
 * Links antigos dentro do conteúdo (wa.me, número de exemplo,
 * {{URL_AGENDAMENTO}}) viram o UpView da unidade padrão — o certo é editar
 * o post, o filtro só evita link quebrado enquanto isso.
 */
add_filter(
	'the_content',
	static function ( $content ) {
		if ( is_admin() || false === stripos( $content, '<a' ) ) {
			return $content;
		}
		$unit = proodonto_default_unit();
		if ( ! $unit || ! $unit['whatsapp_url'] ) {
			return $content;
		}

		$fixed = preg_replace_callback(
			'~<a\b([^>]*)\shref\s*=\s*(["\'])(.*?)\2~is',
			static function ( $m ) use ( $unit ) {
				if ( ! proodonto_is_legacy_cta_url( $m[3] ) ) {
					return $m[0];
				}
				return '<a' . $m[1] . ' data-cta="conteudo" data-cta-unit="' . esc_attr( $unit['slug'] ) . '" href="' . esc_url( $unit['whatsapp_url'] ) . '"';
			},
			$content
		);

		return is_string( $fixed ) ? $fixed : $content;
	},
	30
);
