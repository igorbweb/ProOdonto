<?php
/**
 * Exibido quando nenhum post é encontrado (busca vazia, arquivo vazio).
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="no-results">

	<h1><?php esc_html_e( 'Nada encontrado', 'proodonto' ); ?></h1>

	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'Sua busca não retornou nenhum resultado. Tente outros termos.', 'proodonto' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nenhum conteúdo foi publicado ainda.', 'proodonto' ); ?></p>
	<?php endif; ?>

</section>
