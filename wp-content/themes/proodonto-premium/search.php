<?php
/**
 * Resultados da busca.
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part(
	'template-parts/listing',
	null,
	array(
		/* translators: %s: termo buscado */
		'title' => sprintf( __( 'Busca: *%s*', 'proodonto' ), get_search_query() ),
	)
);
get_footer();
