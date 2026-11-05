<?php
/**
 * Limpeza do <head> e do front — menos bytes antes do LCP.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		// Emojis (script + CSS em toda página).
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );

		// Links que ninguém usa.
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}
);

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'the_generator', '__return_empty_string' );

/** wp-embed não é usado. */
add_action(
	'wp_footer',
	static function () {
		wp_dequeue_script( 'wp-embed' );
	}
);

/** Heartbeat mais espaçado no painel. */
add_filter(
	'heartbeat_settings',
	static function ( $settings ) {
		$settings['interval'] = 60;
		return $settings;
	}
);

/** WebP como saída padrão dos tamanhos gerados (WP 5.8+ com GD/Imagick). */
add_filter(
	'image_editor_output_format',
	static function ( $formats ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
		return $formats;
	}
);

/** Imagens de conteúdo: lazy + async por padrão (o LCP sai com fetchpriority). */
add_filter(
	'wp_get_attachment_image_attributes',
	static function ( $attr ) {
		if ( empty( $attr['decoding'] ) ) {
			$attr['decoding'] = 'async';
		}
		return $attr;
	}
);
