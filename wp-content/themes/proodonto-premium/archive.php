<?php
/**
 * Categoria, tag, autor e datas.
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part(
	'template-parts/listing',
	null,
	array(
		'title' => wp_strip_all_tags( get_the_archive_title() ),
		'lead'  => get_the_archive_description(),
	)
);
get_footer();
