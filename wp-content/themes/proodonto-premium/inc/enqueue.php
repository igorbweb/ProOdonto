<?php
/**
 * CSS, JS, fontes e dicas de carregamento.
 *
 * Orçamento (canvas, nota de LCP): LCP < 2,0 s no 4G e INP < 200 ms.
 *   - Um CSS só (main.css), versão por filemtime — nada de cache velho.
 *   - Um JS só (main.js), defer, sem jQuery, sem Swiper.
 *   - Poppins self-hosted (100/400/700, subset latin), preload de 400 e 700.
 *   - Celular: o LCP é o H1 (topo sem imagem). Tablet/desktop: a foto do
 *     topo ganha <link rel="preload" media="(min-width: 768px)">.
 *   - Pixel da Meta só depois que a página fica ociosa (main.js).
 */

defined( 'ABSPATH' ) || exit;

function proodonto_asset_version( $rel ) {
	$path = PROODONTO_DIR . '/' . ltrim( $rel, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : PROODONTO_VERSION;
}

add_action( 'wp_enqueue_scripts', 'proodonto_enqueue_assets' );

function proodonto_enqueue_assets() {
	wp_enqueue_style( 'proodonto-main', PROODONTO_URI . '/assets/css/main.css', array(), proodonto_asset_version( 'assets/css/main.css' ) );

	wp_enqueue_script(
		'proodonto-main',
		PROODONTO_URI . '/assets/js/main.js',
		array(),
		proodonto_asset_version( 'assets/js/main.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$utm = proodonto_opt( 'repassar_utm' );
	wp_localize_script(
		'proodonto-main',
		'PROODONTO',
		array(
			'passParams' => ( null === $utm || $utm ) ? 1 : 0,
			'utmSource'  => is_singular( 'post' ) ? 'blog' : '',
			'pixel'      => preg_replace( '/\D/', '', (string) proodonto_opt( 'meta_pixel_id' ) ),
			'i18n'       => array(
				'copied' => __( 'Link copiado', 'proodonto' ),
			),
		)
	);

	// Sem blocos do core no front além do que o editor usa no post.
	if ( ! is_singular( array( 'post', 'page' ) ) || is_page_template( array( 'page-home.php', 'page-vendas.php', 'page-servicos.php' ) ) ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}

/**
 * @font-face inline (caminhos absolutos do tema) + preload.
 *
 * Coolvetica e Acre: o tema só aponta para o arquivo se ele existir em
 * assets/fonts/. ATENÇÃO à licença: o zip da Coolvetica recebido em
 * 30/10/2026 vem com a EULA Desktop da Typodermic, que proíbe uso como
 * webfont (seção 4.3) — só coloque o .woff2 aqui depois de comprar a
 * licença web. Até lá vale local(): quem tem a fonte instalada vê, o resto
 * cai na Poppins sem erro.
 */
add_action( 'wp_head', 'proodonto_fonts', 2 );

function proodonto_fonts() {
	$base = PROODONTO_URI . '/assets/fonts/';
	$css  = '';

	foreach ( array( 100, 400, 700 ) as $weight ) {
		$css .= sprintf(
			'@font-face{font-family:"Poppins";src:url("%1$spoppins/poppins-%2$d.woff2") format("woff2");font-weight:%2$d;font-style:normal;font-display:swap;unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}',
			esc_url( $base ),
			$weight
		);
	}

	$licensed = array(
		'Coolvetica' => array( 'coolvetica/coolvetica.woff2', "local('Coolvetica'),local('Coolvetica Rg'),local('Coolvetica-Regular')" ),
		'Acre'       => array( 'acre/acre-medium.woff2', "local('Acre Medium'),local('Acre-Medium'),local('Acre')" ),
	);
	foreach ( $licensed as $family => $def ) {
		$src = $def[1];
		if ( file_exists( PROODONTO_DIR . '/assets/fonts/' . $def[0] ) ) {
			$src .= ',url("' . esc_url( $base . $def[0] ) . '") format("woff2")';
		}
		$css .= '@font-face{font-family:"' . $family . '";src:' . $src . ';font-weight:400;font-style:normal;font-display:swap}';
	}

	foreach ( array( 400, 700 ) as $weight ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $base . 'poppins/poppins-' . $weight . '.woff2' ) );
	}
	if ( file_exists( PROODONTO_DIR . '/assets/fonts/coolvetica/coolvetica.woff2' ) ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $base . 'coolvetica/coolvetica.woff2' ) );
	}

	echo '<style id="proodonto-fonts">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- montado acima com esc_url.
}

/**
 * Preload da foto do topo (só tablet/desktop — no celular o topo não tem
 * imagem e o LCP é o H1).
 */
add_action( 'wp_head', 'proodonto_preload_hero', 3 );

function proodonto_preload_hero() {
	$id    = 0;
	$size  = 'proodonto-hero';
	$min   = '1024px'; // Unidade e tratamento: foto só no desktop.
	$sizes = '560px';

	if ( is_page_template( 'page-home.php' ) ) {
		$min   = '768px';
		$sizes = '100vw';
		$id = (int) proodonto_field( 'home_hero_imagem' );
	} elseif ( is_page_template( 'page-vendas.php' ) ) {
		$id   = (int) proodonto_field( 'uni_hero_foto' );
		$size = 'proodonto-hero-unit';
	} elseif ( is_singular( 'servico' ) ) {
		$id   = (int) proodonto_field( 'srv_hero_foto' );
		$size = 'proodonto-hero-unit';
	}

	if ( ! $id ) {
		return;
	}

	$src    = wp_get_attachment_image_url( $id, $size );
	$srcset = wp_get_attachment_image_srcset( $id, $size );
	if ( ! $src ) {
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%1$s"%2$s media="(min-width: %3$s)" fetchpriority="high">' . "\n",
		esc_url( $src ),
		$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="' . esc_attr( $sizes ) . '"' : '',
		esc_attr( $min )
	);
}

/**
 * Teste A/B do topo das páginas de unidade — decide a variante ANTES da
 * pintura (sem pulo de layout) e guarda num cookie de 30 dias. Funciona
 * com cache de página: o HTML traz as duas versões; o CSS esconde a outra.
 */
add_action( 'wp_head', 'proodonto_ab_head', 1 );

function proodonto_ab_head() {
	if ( ! is_page_template( 'page-vendas.php' ) || 'ab' !== proodonto_field( 'uni_versao' ) ) {
		return;
	}
	?>
	<script>(function(d){try{var m=d.cookie.match(/(?:^|; )po_ab=([ab])/),v=m?m[1]:(Math.random()<.5?'a':'b');if(!m){d.cookie='po_ab='+v+';path=/;max-age=2592000;SameSite=Lax'}d.documentElement.setAttribute('data-ab',v);window.dataLayer=window.dataLayer||[];window.dataLayer.push({event:'ab_view',ab_test:'hero_unidade',ab_variant:v})}catch(e){}})(document);</script>
	<?php
}

/** Estilo do editor de blocos alinhado ao front (fontes + prose). */
add_action(
	'enqueue_block_editor_assets',
	static function () {
		wp_enqueue_script(
			'proodonto-blocks-editor',
			PROODONTO_URI . '/assets/js/blocks-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			proodonto_asset_version( 'assets/js/blocks-editor.js' ),
			true
		);
	}
);
