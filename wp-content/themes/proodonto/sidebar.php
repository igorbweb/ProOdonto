<?php
/**
 * Sidebar opcional. Não é chamada por nenhum template por padrão (o tema é
 * mobile-first e conversion-first: menos colunas competindo pela atenção).
 * Adicione <?php get_sidebar(); ?> onde fizer sentido para o projeto.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside class="widget-area" id="secondary">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
