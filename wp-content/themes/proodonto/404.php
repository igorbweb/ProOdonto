<?php
/**
 * Template de erro 404.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main">
	<div class="container error-404">

		<h1><?php esc_html_e( 'Página não encontrada', 'proodonto' ); ?></h1>
		<p><?php esc_html_e( 'O conteúdo que você procura não existe ou foi movido.', 'proodonto' ); ?></p>

		<?php get_search_form(); ?>

		<p>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( '&larr; Voltar para a página inicial', 'proodonto' ); ?>
			</a>
		</p>

	</div>
</main>

<?php
get_footer();
