<?php
/**
 * Página de posts (Configurações → Leitura → Página de posts).
 */

defined( 'ABSPATH' ) || exit;

$proodonto_blog_id = (int) get_option( 'page_for_posts' );
get_header();
get_template_part(
	'template-parts/listing',
	null,
	array(
		'title' => $proodonto_blog_id ? get_the_title( $proodonto_blog_id ) : __( 'Blog', 'proodonto' ),
		'lead'  => $proodonto_blog_id ? get_the_excerpt( $proodonto_blog_id ) : '',
	)
);
get_footer();
