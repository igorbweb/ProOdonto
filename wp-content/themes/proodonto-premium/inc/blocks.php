<?php
/**
 * Blocos do editor herdados do tema antigo — mesmos nomes e atributos,
 * para o conteúdo já salvo continuar aparecendo:
 *
 *   proodonto/faq + proodonto/faq-item → acordeão do sistema novo
 *     (<details>, resposta no HTML servido) + FAQPage no grafo do Yoast.
 *   proodonto/cta → cartão clicável do sistema novo.
 *
 * Os demais blocos do tema antigo (hero, testimonials, services, contact)
 * não foram usados em posts no banco local (verificado em 30/10/2026) e
 * não foram portados — ver README.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		$blocks = array(
			'faq'      => 'proodonto_render_block_faq',
			'faq-item' => 'proodonto_render_block_faq_item',
			'cta'      => 'proodonto_render_block_cta',
		);
		foreach ( $blocks as $dir => $callback ) {
			if ( file_exists( PROODONTO_DIR . "/blocks/{$dir}/block.json" ) ) {
				register_block_type( PROODONTO_DIR . "/blocks/{$dir}", array( 'render_callback' => $callback ) );
			}
		}
	}
);

add_filter(
	'block_categories_all',
	static function ( $categories ) {
		array_unshift(
			$categories,
			array(
				'slug'  => 'proodonto',
				'title' => 'PróOdonto',
			)
		);
		return $categories;
	}
);

function proodonto_render_block_faq( $attributes, $content ) {
	if ( ! trim( wp_strip_all_tags( $content ) ) ) {
		return '';
	}
	$heading = isset( $attributes['heading'] ) ? (string) $attributes['heading'] : '';

	return '<section class="faq-block">'
		. ( $heading ? '<h2 class="h2 h2--sm">' . wp_kses_post( $heading ) . '</h2>' : '' )
		. '<div class="card faq">' . $content . '</div></section>'; // phpcs:ignore -- innerBlocks já renderizados.
}

function proodonto_render_block_faq_item( $attributes ) {
	$question = isset( $attributes['question'] ) ? (string) $attributes['question'] : '';
	$answer   = isset( $attributes['answer'] ) ? (string) $attributes['answer'] : '';
	if ( '' === trim( wp_strip_all_tags( $question ) ) ) {
		return '';
	}
	return proodonto_faq_item_html( wp_kses_post( $question ), wpautop( wp_kses_post( $answer ) ) );
}

/** Item de FAQ (usado pelo bloco e pelos repeaters ACF). Recebe HTML já sanitizado. */
function proodonto_faq_item_html( $question_html, $answer_html, $open = false ) {
	return '<details class="faq__item"' . ( $open ? ' open' : '' ) . '><summary class="faq__q"><span>' . $question_html . '</span><span class="faq__ic" aria-hidden="true">' . proodonto_icon( 'plus', 16 ) . '</span></summary><div class="faq__a">' . $answer_html . '</div></details>';
}

function proodonto_render_block_cta( $attributes ) {
	$title = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
	$text  = isset( $attributes['text'] ) ? (string) $attributes['text'] : '';
	$label = isset( $attributes['buttonLabel'] ) ? (string) $attributes['buttonLabel'] : '';
	$url   = isset( $attributes['buttonUrl'] ) ? (string) $attributes['buttonUrl'] : '';

	if ( ! $title && ! $label ) {
		return '';
	}
	if ( '' === $url || proodonto_is_legacy_cta_url( $url ) ) {
		$unit = proodonto_default_unit();
		$url  = $unit ? $unit['whatsapp_url'] : '';
	}
	if ( ! $url ) {
		return '';
	}

	return sprintf(
		'<a class="card tap inline-cta" href="%1$s" data-cta="bloco-cta"><span><span class="inline-cta__t">%2$s</span>%3$s</span><span class="chip chip--solid" aria-hidden="true">%4$s</span></a>',
		esc_url( $url ),
		wp_kses_post( $title ? $title : $label ),
		$text ? '<span class="inline-cta__d">' . wp_kses_post( $text ) . '</span>' : '',
		proodonto_icon( 'arrow', 18 )
	);
}

/**
 * Perguntas do bloco proodonto/faq-item dentro de um conteúdo (schema).
 *
 * @return array<int, array{q:string,a:string}>
 */
function proodonto_block_faq_items( $post ) {
	if ( ! ( $post instanceof WP_Post ) || ! has_block( 'proodonto/faq-item', $post ) ) {
		return array();
	}
	$items = array();
	$walk  = static function ( $blocks ) use ( &$walk, &$items ) {
		foreach ( $blocks as $block ) {
			if ( 'proodonto/faq-item' === $block['blockName'] && ! empty( $block['attrs']['question'] ) ) {
				$items[] = array(
					'q' => $block['attrs']['question'],
					'a' => $block['attrs']['answer'] ?? '',
				);
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
	};
	$walk( parse_blocks( $post->post_content ) );
	return $items;
}
