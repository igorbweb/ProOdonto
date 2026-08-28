<?php
/**
 * Otimizações de performance: limpeza do <head>, desativação de recursos
 * não usados, lazy-load, WebP, heartbeat e afins.
 *
 * Cada bloco é independente — comente o que não fizer sentido para o projeto.
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------------------------------------
 * 1. Emojis nativos do WordPress (script + estilos inline pesados)
 * -------------------------------------------------------------------- */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
} );

add_filter( 'tiny_mce_plugins', function ( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : $plugins;
} );

/* -----------------------------------------------------------------------
 * 2. Limpeza de tags irrelevantes no <head>
 * -------------------------------------------------------------------- */
add_action( 'init', function () {
	remove_action( 'wp_head', 'rsd_link' );                        // EditURI / RSD.
	remove_action( 'wp_head', 'wlwmanifest_link' );                 // Windows Live Writer.
	remove_action( 'wp_head', 'wp_generator' );                     // Versão do WP exposta.
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );             // Shortlink.
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );  // rel=prev/next (não usado fora de posts paginados).
	remove_action( 'wp_head', 'rest_output_link_wp_head' );         // Link da REST API no head (a API continua ativa).
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
} );

// Remove a versão do WP também das queries de assets (?ver=6.x expõe a versão).
add_filter( 'the_generator', '__return_empty_string' );

/* -----------------------------------------------------------------------
 * 3. Desativa oEmbeds e o JS de embeds do WP (wp-embed.min.js)
 * -------------------------------------------------------------------- */
add_action( 'init', function () {
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
	wp_deregister_script( 'wp-embed' );
} );

/* -----------------------------------------------------------------------
 * 4. XML-RPC e pingbacks (superfície de ataque + carga desnecessária)
 * -------------------------------------------------------------------- */
add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

add_action( 'pre_ping', function ( &$links ) {
	foreach ( $links as $key => $link ) {
		if ( 0 === strpos( $link, home_url() ) ) {
			unset( $links[ $key ] );
		}
	}
} );

/* -----------------------------------------------------------------------
 * 5. Heartbeat API: desliga no front, reduz frequência no admin
 * -------------------------------------------------------------------- */
add_action( 'init', function () {
	if ( ! is_admin() ) {
		wp_deregister_script( 'heartbeat' );
	}
} );

add_filter( 'heartbeat_settings', function ( $settings ) {
	$settings['interval'] = 60; // segundos, só afeta o admin.
	return $settings;
} );

/* -----------------------------------------------------------------------
 * 6. jQuery Migrate: remove se o projeto não depende de plugins antigos
 * -------------------------------------------------------------------- */
add_action( 'wp_default_scripts', function ( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$deps = $scripts->registered['jquery']->deps;
		$scripts->registered['jquery']->deps = array_diff( $deps, array( 'jquery-migrate' ) );
	}
} );

/* -----------------------------------------------------------------------
 * 7. Defer/async em scripts do tema (mantém plugins de fora intocados)
 * -------------------------------------------------------------------- */
add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
	$defer_handles = apply_filters( 'proodonto_defer_scripts', array( 'proodonto-main' ) );

	if ( in_array( $handle, $defer_handles, true ) && false === strpos( $tag, 'defer' ) ) {
		$tag = str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}, 10, 3 );

/* -----------------------------------------------------------------------
 * 8. Lazy-load + fetchpriority na imagem de destaque (ajuda o LCP)
 * -------------------------------------------------------------------- */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
	// Página inicial: a imagem de destaque tende a ser o LCP, então evitamos lazy-load nela.
	if ( is_front_page() && doing_filter( 'get_the_post_thumbnail' ) ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
	}

	return $attr;
}, 10, 3 );

/* -----------------------------------------------------------------------
 * 9. Suporte a upload/geração de WebP nas subsizes do Media Library
 * -------------------------------------------------------------------- */
add_filter( 'image_editor_output_format', function ( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	return $formats;
} );

add_filter( 'upload_mimes', function ( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
} );

/* -----------------------------------------------------------------------
 * 10. Resource hints (preconnect/dns-prefetch) — personalize por projeto
 * -------------------------------------------------------------------- */
add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
	$hosts = apply_filters( 'proodonto_preconnect_hosts', array() );

	if ( 'preconnect' === $relation_type ) {
		foreach ( $hosts as $host ) {
			$urls[] = array(
				'href'        => $host,
				'crossorigin' => true,
			);
		}
	}

	return $urls;
}, 10, 2 );

/* -----------------------------------------------------------------------
 * 11. Limpeza de assets de bloco não usados no front (blocos de galeria,
 *     estilos globais duplicados) — desative com cuidado se usar o Editor
 *     de Blocos extensivamente para layout.
 * -------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_admin() ) {
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 20 );

/* -----------------------------------------------------------------------
 * 12. Dashicons apenas para usuários logados (não é usado no front público)
 * -------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_admin_bar_showing() ) {
		wp_deregister_style( 'dashicons' );
	}
} );

/* -----------------------------------------------------------------------
 * 13. Desativa auto-atualização de revisões em excesso via limite (definir
 *     WP_POST_REVISIONS no wp-config.php é a forma recomendada; deixamos
 *     um valor padrão aqui apenas como rede de segurança).
 * -------------------------------------------------------------------- */
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 5 );
}
