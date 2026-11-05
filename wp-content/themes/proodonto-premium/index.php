<?php
/**
 * Fallback obrigatório do WordPress — mesma listagem do blog.
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/listing', null, array( 'title' => wp_strip_all_tags( get_the_archive_title() ) ) );
get_footer();
