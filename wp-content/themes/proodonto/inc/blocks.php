<?php
/**
 * Blocos nativos do Editor (Gutenberg como "page builder"):
 *
 *   - proodonto/cta                Chamada para ação
 *   - proodonto/hero                Seção de destaque com imagem
 *   - proodonto/testimonials        Grade de depoimentos (contêiner)
 *   - proodonto/testimonial-item    Um depoimento (filho de testimonials)
 *   - proodonto/faq                 Perguntas frequentes (contêiner)
 *   - proodonto/faq-item             Uma pergunta/resposta (filho de faq)
 *   - proodonto/services             Grade de serviços/especialidades (contêiner)
 *   - proodonto/service-item          Um serviço (filho de services)
 *   - proodonto/contact               Contato/agendamento com formulário nativo
 *
 * Todos são renderizados no servidor (render_callback em PHP) — o editor
 * só cuida da experiência de edição. Isso mantém 100% de controle sobre
 * HTML/CSS e permite carregar o CSS de cada bloco só quando ele aparece
 * na página (ver inc/enqueue.php, via has_block()).
 *
 * Para criar um novo bloco: duplique uma pasta em /blocks, ajuste o
 * block.json e o render.php, registre o slug no array $blocks abaixo e
 * adicione o registerBlockType correspondente em assets/js/blocks-editor.js.
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------------------------------------
 * 1. Registro dos blocos
 * -------------------------------------------------------------------- */
add_action( 'init', 'proodonto_register_blocks' );

function proodonto_register_blocks() {
	$blocks = array(
		'cta',
		'hero',
		'testimonials',
		'testimonial-item',
		'faq',
		'faq-item',
		'services',
		'service-item',
		'contact',
	);

	foreach ( $blocks as $block ) {
		$dir = PROODONTO_DIR . "/blocks/{$block}";

		if ( ! file_exists( "{$dir}/block.json" ) ) {
			continue;
		}

		$render_file = "{$dir}/render.php";
		if ( file_exists( $render_file ) ) {
			require_once $render_file;
		}

		register_block_type(
			$dir,
			array(
				'render_callback' => 'proodonto_render_block_' . str_replace( '-', '_', $block ),
			)
		);
	}
}

/* -----------------------------------------------------------------------
 * 2. Categoria própria no inserter de blocos
 * -------------------------------------------------------------------- */
add_filter( 'block_categories_all', function ( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'proodonto',
			'title' => __( 'Proodonto', 'proodonto' ),
		)
	);

	return $categories;
} );

/* -----------------------------------------------------------------------
 * 3. Assets do editor: JS de registro + CSS para o preview ficar
 *    parecido com o front-end (design tokens + CSS de cada bloco).
 * -------------------------------------------------------------------- */
add_action( 'enqueue_block_editor_assets', function () {
	$fonts_css_rel = 'assets/css/fonts.css';
	$fonts_handle  = null;

	if ( file_exists( PROODONTO_DIR . '/' . $fonts_css_rel ) ) {
		$fonts_handle = 'proodonto-fonts-editor';
		wp_enqueue_style( $fonts_handle, PROODONTO_URI . '/' . $fonts_css_rel, array(), proodonto_asset_version( $fonts_css_rel ) );
	}

	$tailwind_css_rel = 'assets/css/tailwind.css';
	$tailwind_handle  = null;

	if ( file_exists( PROODONTO_DIR . '/' . $tailwind_css_rel ) ) {
		$tailwind_handle = 'proodonto-tailwind-editor';
		wp_enqueue_style( $tailwind_handle, PROODONTO_URI . '/' . $tailwind_css_rel, $fonts_handle ? array( $fonts_handle ) : array(), proodonto_asset_version( $tailwind_css_rel ) );
	}

	wp_enqueue_style(
		'proodonto-style-editor',
		get_stylesheet_uri(),
		$tailwind_handle ? array( $tailwind_handle ) : array(),
		proodonto_asset_version( 'style.css' )
	);

	wp_enqueue_style(
		'proodonto-main-editor',
		PROODONTO_URI . '/assets/css/main.css',
		array( 'proodonto-style-editor' ),
		proodonto_asset_version( 'assets/css/main.css' )
	);

	foreach ( (array) glob( PROODONTO_DIR . '/assets/css/blocks/*.css' ) as $css_path ) {
		$slug = basename( $css_path, '.css' );
		wp_enqueue_style(
			"proodonto-block-{$slug}-editor",
			PROODONTO_URI . "/assets/css/blocks/{$slug}.css",
			array( 'proodonto-main-editor' ),
			proodonto_asset_version( "assets/css/blocks/{$slug}.css" )
		);
	}

	wp_enqueue_script(
		'proodonto-blocks-editor',
		PROODONTO_URI . '/assets/js/blocks-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		proodonto_asset_version( 'assets/js/blocks-editor.js' ),
		true
	);
} );

/* -----------------------------------------------------------------------
 * 4. Bônus SEO: se a página tiver o bloco de FAQ, gera automaticamente o
 *    JSON-LD "FAQPage" (usa o filtro já existente em inc/seo.php).
 * -------------------------------------------------------------------- */
add_filter( 'proodonto_json_ld_graphs', function ( $graphs ) {
	if ( ! is_singular() ) {
		return $graphs;
	}

	$post = get_queried_object();

	if ( ! ( $post instanceof WP_Post ) || ! has_block( 'proodonto/faq-item', $post ) ) {
		return $graphs;
	}

	$items = proodonto_extract_faq_items( parse_blocks( $post->post_content ) );

	if ( ! $items ) {
		return $graphs;
	}

	$graphs[] = array(
		'@type'      => 'FAQPage',
		'mainEntity' => array_map(
			function ( $item ) {
				return array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $item['question'] ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $item['answer'] ),
					),
				);
			},
			$items
		),
	);

	return $graphs;
} );

function proodonto_extract_faq_items( $blocks, &$items = array() ) {
	foreach ( $blocks as $block ) {
		if ( 'proodonto/faq-item' === $block['blockName'] && ! empty( $block['attrs']['question'] ) ) {
			$items[] = array(
				'question' => $block['attrs']['question'],
				'answer'   => isset( $block['attrs']['answer'] ) ? $block['attrs']['answer'] : '',
			);
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			proodonto_extract_faq_items( $block['innerBlocks'], $items );
		}
	}

	return $items;
}
