<?php
/**
 * Formulário de busca.
 */

defined( 'ABSPATH' ) || exit;
$proodonto_sid = 'busca-' . wp_unique_id();
?>
<form role="search" method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr-only" for="<?php echo esc_attr( $proodonto_sid ); ?>"><?php esc_html_e( 'Buscar no blog', 'proodonto' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $proodonto_sid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Buscar no blog', 'proodonto' ); ?>">
	<button type="submit" class="btn btn--light btn--sm"><span class="btn__label"><?php esc_html_e( 'Buscar', 'proodonto' ); ?></span><span class="btn__ic"><?php echo proodonto_icon( 'arrow', 16 ); // phpcs:ignore ?></span></button>
</form>
