<?php
/**
 * Theme supports, menus, sidebars e tamanhos de imagem.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'proodonto_setup' );

function proodonto_setup() {
	// Título automático via <title>, gerenciado pelo core (mais leve que SEO plugins para isso).
	add_theme_support( 'title-tag' );

	// Imagens.
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// Logo customizado (evita depender de imagem hardcoded no header.php).
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// HTML5 semântico para markup mais limpo e leve.
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Feeds automáticos no <head> não são necessários na maioria dos sites institucionais;
	// mantenha comentado se o projeto não usa RSS.
	// remove_theme_support( 'automatic-feed-links' );

	// Largura de conteúdo padrão (usada por oEmbeds e wp_get_attachment_image).
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1200;
	}

	// Menus.
	register_nav_menus(
		array(
			'primary' => __( 'Menu Principal', 'proodonto' ),
			'footer'  => __( 'Menu do Rodapé', 'proodonto' ),
		)
	);

	// Tamanhos de imagem enxutos, pensados para carregar só o necessário por breakpoint.
	add_image_size( 'proodonto-card', 480, 320, true );
	add_image_size( 'proodonto-hero', 1600, 900, true );
}

add_action( 'widgets_init', 'proodonto_widgets_init' );

function proodonto_widgets_init() {
	// Não usada por nenhum template por padrão (tema mobile-first / conversion-first).
	// Registrada para que sidebar.php funcione se o projeto precisar dela.
	register_sidebar(
		array(
			'name'          => __( 'Barra Lateral (opcional)', 'proodonto' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Não usada por padrão — adicione get_sidebar() a um template para ativá-la.', 'proodonto' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Rodapé', 'proodonto' ),
			'id'            => 'footer-1',
			'description'   => __( 'Widgets exibidos no rodapé do site.', 'proodonto' ),
			'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
