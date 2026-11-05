<?php
/**
 * Theme supports, menus, tamanhos de imagem e estilos de bloco.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'proodonto_setup' );

function proodonto_setup() {
	load_theme_textdomain( 'proodonto', PROODONTO_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 392,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 720;
	}

	register_nav_menus(
		array(
			'primary'           => __( 'Menu principal (cabeçalho)', 'proodonto' ),
			'footer_tratamentos' => __( 'Rodapé — Tratamentos', 'proodonto' ),
			'footer_clinica'    => __( 'Rodapé — A clínica', 'proodonto' ),
		)
	);

	/*
	 * Tamanhos pensados para o que o layout realmente pede (2x para telas
	 * retina já incluído). O hero full-bleed da Home usa `proodonto-hero`
	 * (1600 de largura → WebP ≤ 120 KB na meta da performance).
	 */
	add_image_size( 'proodonto-hero', 1600, 900, true );
	add_image_size( 'proodonto-hero-unit', 1000, 1000, true );
	add_image_size( 'proodonto-card', 760, 480, true );
	add_image_size( 'proodonto-portrait', 600, 750, true );
	add_image_size( 'proodonto-thumb', 160, 160, true );
}

/**
 * Caixas de alerta/dica do post (P2Post): estilos do bloco Grupo, para o
 * editor marcar sem escrever HTML.
 */
add_action(
	'init',
	static function () {
		if ( ! function_exists( 'register_block_style' ) ) {
			return;
		}
		register_block_style(
			'core/group',
			array(
				'name'  => 'alerta',
				'label' => __( 'Alerta (laranja)', 'proodonto' ),
			)
		);
		register_block_style(
			'core/group',
			array(
				'name'  => 'dica',
				'label' => __( 'Dica (água)', 'proodonto' ),
			)
		);
	}
);
