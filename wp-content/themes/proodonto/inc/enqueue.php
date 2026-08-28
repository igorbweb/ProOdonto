<?php
/**
 * Carregamento de CSS/JS.
 *
 * Estratégia:
 *  - assets/css/tailwind.css (utilitários, compilado via `npm run dev`/`build`
 *    a partir de assets/tailwind/input.css) carrega PRIMEIRO — inclui o
 *    preflight do Tailwind, então style.css/main.css carregam depois e podem
 *    sobrescrever o que for preciso.
 *  - style.css (reset + tokens) sempre carrega.
 *  - main.css (layout/componentes globais: header, footer, botões, cards) sempre carrega.
 *  - assets/css/pages/{slug}.css carrega SOMENTE na página correspondente,
 *    gerado automaticamente por inc/page-generator.php a cada nova página.
 *  - assets/js/pages/{slug}.js carrega SOMENTE na página correspondente, se
 *    o arquivo existir (nada gera isso automaticamente — crie à mão quando
 *    a página precisar de JS próprio, ex.: inicializar um carrossel).
 *  - Swiper (self-hosted, assets/vendor/swiper) só carrega nas páginas que
 *    têm um assets/js/pages/{slug}.js — nenhuma outra página paga o custo
 *    da biblioteca.
 *  - assets/css/blocks/{slug}.css carrega SOMENTE quando o bloco
 *    proodonto/{slug} (ver inc/blocks.php) está presente no conteúdo,
 *    detectado via has_block() — não importa em qual página o editor usou
 *    o bloco, o CSS só é servido onde ele realmente aparece.
 *  - JS único (main.js), sem jQuery, carregado com defer.
 *  - Versionamento por filemtime() em vez de PROODONTO_VERSION fixo, para que o
 *    cache do navegador quebre automaticamente a cada alteração do arquivo
 *    durante o desenvolvimento.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'proodonto_enqueue_assets' );

function proodonto_enqueue_assets() {
	// --- Fontes self-hosted (@font-face; ver assets/fonts/ e assets/css/fonts.css) ---
	$fonts_css_rel = 'assets/css/fonts.css';
	$fonts_handle  = null;

	if ( file_exists( PROODONTO_DIR . '/' . $fonts_css_rel ) ) {
		$fonts_handle = 'proodonto-fonts';

		wp_enqueue_style(
			$fonts_handle,
			PROODONTO_URI . '/' . $fonts_css_rel,
			array(),
			proodonto_asset_version( $fonts_css_rel )
		);
	}

	// --- Tailwind (compilado; ver assets/tailwind/input.css + package.json) ---
	$tailwind_css_rel = 'assets/css/tailwind.css';
	$tailwind_handle   = null;

	if ( file_exists( PROODONTO_DIR . '/' . $tailwind_css_rel ) ) {
		$tailwind_handle = 'proodonto-tailwind';

		wp_enqueue_style(
			$tailwind_handle,
			PROODONTO_URI . '/' . $tailwind_css_rel,
			$fonts_handle ? array( $fonts_handle ) : array(),
			proodonto_asset_version( $tailwind_css_rel )
		);
	}

	// --- CSS base (reset + tokens) ---------------------------------------
	wp_enqueue_style(
		'proodonto-style',
		get_stylesheet_uri(),
		$tailwind_handle ? array( $tailwind_handle ) : array(),
		proodonto_asset_version( 'style.css' )
	);

	// --- CSS global de layout/componentes ---------------------------------
	$main_css_rel = 'assets/css/main.css';
	if ( file_exists( PROODONTO_DIR . '/' . $main_css_rel ) ) {
		wp_enqueue_style(
			'proodonto-main',
			PROODONTO_URI . '/' . $main_css_rel,
			array( 'proodonto-style' ),
			proodonto_asset_version( $main_css_rel )
		);
	}

	// --- CSS específico da página (gerado automaticamente) ----------------
	if ( is_page() ) {
		$slug     = get_post_field( 'post_name', get_queried_object_id() );
		$template = get_page_template_slug( get_queried_object_id() );

		// A "Página de Vendas" reaproveita as seções e o carrossel da Home —
		// carrega assets/css/pages/home.css e assets/js/pages/home.js (com
		// Swiper) tanto quando o template page-vendas.php foi selecionado
		// manualmente (Atributos da página) quanto quando a página tem o
		// slug "vendas" (resolvido automaticamente pela Template Hierarchy
		// do próprio WordPress, sem precisar escolher nada). O CSS próprio
		// do slug real (ex.: assets/css/pages/vendas.css) entra depois, só
		// com as diferenças (sem lista de unidades, CTAs extras etc.) — ver
		// comentário no topo de page-vendas.php.
		$is_vendas   = ( 'page-vendas.php' === $template ) || ( 'vendas' === $slug );
		$base_slug   = $is_vendas ? 'home' : $slug;
		$base_handle = null;

		if ( $base_slug ) {
			$page_css_rel = "assets/css/pages/{$base_slug}.css";

			if ( file_exists( PROODONTO_DIR . '/' . $page_css_rel ) ) {
				$base_handle = "proodonto-page-{$base_slug}";

				wp_enqueue_style(
					$base_handle,
					PROODONTO_URI . '/' . $page_css_rel,
					array( 'proodonto-main' ),
					proodonto_asset_version( $page_css_rel )
				);
			}

			// --- JS específico da página (escrito à mão, ex.: carrossel) ---
			$page_js_rel = "assets/js/pages/{$base_slug}.js";

			if ( file_exists( PROODONTO_DIR . '/' . $page_js_rel ) ) {
				$page_js_deps = array();

				// Swiper: só entra em cena se a página realmente tiver JS próprio.
				$swiper_css_rel = 'assets/vendor/swiper/swiper-bundle.min.css';
				$swiper_js_rel  = 'assets/vendor/swiper/swiper-bundle.min.js';

				if ( file_exists( PROODONTO_DIR . '/' . $swiper_css_rel ) ) {
					wp_enqueue_style(
						'swiper',
						PROODONTO_URI . '/' . $swiper_css_rel,
						array(),
						proodonto_asset_version( $swiper_css_rel )
					);
				}

				if ( file_exists( PROODONTO_DIR . '/' . $swiper_js_rel ) ) {
					wp_enqueue_script(
						'swiper',
						PROODONTO_URI . '/' . $swiper_js_rel,
						array(),
						proodonto_asset_version( $swiper_js_rel ),
						array(
							'strategy'  => 'defer',
							'in_footer' => true,
						)
					);
					$page_js_deps[] = 'swiper';
				}

				wp_enqueue_script(
					"proodonto-page-{$base_slug}",
					PROODONTO_URI . '/' . $page_js_rel,
					$page_js_deps,
					proodonto_asset_version( $page_js_rel ),
					array(
						'strategy'  => 'defer',
						'in_footer' => true,
					)
				);
			}
		}

		// CSS próprio (delta), além do que já carregou via $base_slug.
		// Na Página de Vendas isso é SEMPRE "vendas.css", não importa o
		// slug real da página no wp-admin — do contrário, se a página
		// fosse publicada com outro slug (ex.: usando o template "Página
		// de Vendas" mas com slug "promocao-julho"), o tema procuraria
		// por assets/css/pages/promocao-julho.css (inexistente) e o
		// arquivo com as regras de .section-cta nunca carregaria.
		$own_slug = $is_vendas ? 'vendas' : $slug;

		if ( $own_slug && $own_slug !== $base_slug ) {
			$own_css_rel = "assets/css/pages/{$own_slug}.css";

			if ( file_exists( PROODONTO_DIR . '/' . $own_css_rel ) ) {
				wp_enqueue_style(
					"proodonto-page-{$own_slug}",
					PROODONTO_URI . '/' . $own_css_rel,
					array( $base_handle ? $base_handle : 'proodonto-main' ),
					proodonto_asset_version( $own_css_rel )
				);
			}
		}
	}

	// --- CSS dos blocos nativos (Gutenberg), só quando usados na página ----
	if ( is_singular() ) {
		$current_post = get_queried_object();

		foreach ( (array) glob( PROODONTO_DIR . '/assets/css/blocks/*.css' ) as $css_path ) {
			$slug = basename( $css_path, '.css' );

			if ( has_block( "proodonto/{$slug}", $current_post ) ) {
				$block_css_rel = "assets/css/blocks/{$slug}.css";

				wp_enqueue_style(
					"proodonto-block-{$slug}",
					PROODONTO_URI . '/' . $block_css_rel,
					array( 'proodonto-main' ),
					proodonto_asset_version( $block_css_rel )
				);
			}
		}
	}

	// --- JS único, sem dependências, carregado com defer ------------------
	$main_js_rel = 'assets/js/main.js';
	if ( file_exists( PROODONTO_DIR . '/' . $main_js_rel ) ) {
		wp_enqueue_script(
			'proodonto-main',
			PROODONTO_URI . '/' . $main_js_rel,
			array(),
			proodonto_asset_version( $main_js_rel ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

}

/**
 * Retorna filemtime() do arquivo como string de versão, com fallback para a
 * versão do tema caso o arquivo não exista (evita warnings em produção se o
 * deploy não copiar todos os arquivos).
 */
function proodonto_asset_version( $relative_path ) {
	$path = PROODONTO_DIR . '/' . ltrim( $relative_path, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : PROODONTO_VERSION;
}

/**
 * Injeta assets/css/critical.css inline no <head>, antes de qualquer CSS
 * enfileirado, eliminando render-blocking na primeira pintura. Mantenha
 * esse arquivo pequeno — só o necessário para o cabeçalho e o hero.
 */
add_action( 'wp_head', 'proodonto_inline_critical_css', 0 );

function proodonto_inline_critical_css() {
	$path = PROODONTO_DIR . '/assets/css/critical.css';

	if ( ! file_exists( $path ) ) {
		return;
	}

	$css = file_get_contents( $path );

	if ( $css ) {
		echo '<style id="proodonto-critical-css">' . $css . '</style>' . "\n";
	}
}

/**
 * Preload do peso 400 do Poppins (o mais usado, no texto do corpo) para
 * evitar FOIT — o navegador começa a buscar a fonte antes mesmo de parsear
 * o CSS que a referencia.
 */
add_action( 'wp_head', 'proodonto_preload_fonts', 1 );

function proodonto_preload_fonts() {
	$font_rel = 'assets/fonts/poppins/poppins-400.woff2';

	if ( ! file_exists( PROODONTO_DIR . '/' . $font_rel ) ) {
		return;
	}

	printf(
		'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin="anonymous" />' . "\n",
		esc_url( PROODONTO_URI . '/' . $font_rel )
	);
}

/**
 * Preload da imagem de destaque quando ela funciona como LCP (home e páginas
 * singulares). Reduz o tempo até o maior elemento visível carregar.
 */
add_action( 'wp_head', 'proodonto_preload_lcp_image', 1 );

function proodonto_preload_lcp_image() {
	if ( ! is_singular() || ! has_post_thumbnail() ) {
		return;
	}

	$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'proodonto-hero' );

	if ( ! $image ) {
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high" />' . "\n",
		esc_url( $image[0] )
	);
}
