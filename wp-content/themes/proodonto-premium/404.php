<?php
/**
 * Página não encontrada — devolve a pessoa ao que converte: unidades e
 * tratamentos.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="hero hero--list">
	<?php echo proodonto_arc( 'hero' ); // phpcs:ignore ?>
	<div class="wrap hero__in">
		<p class="eyebrow">404</p>
		<h1 class="h1"><?php echo proodonto_rich( __( 'Esta página *não existe mais.*', 'proodonto' ) ); // phpcs:ignore ?></h1>
		<p class="lead"><?php esc_html_e( 'Mas a sua avaliação continua a um clique: escolha a unidade mais perto.', 'proodonto' ); ?></p>
	</div>
</section>
<div class="wrap hero-selector">
	<?php get_template_part( 'template-parts/sections/unit-selector', null, array( 'title' => __( 'Agende na unidade', 'proodonto' ), 'place' => '404', 'class' => 'selector--bar' ) ); ?>
</div>
<section class="section">
	<div class="wrap btn-row">
		<?php echo proodonto_button( array( 'label' => __( 'Ir para o início', 'proodonto' ), 'url' => home_url( '/' ), 'variant' => 'outline' ) ); // phpcs:ignore ?>
		<?php echo proodonto_button( array( 'label' => __( 'Ver os tratamentos', 'proodonto' ), 'url' => proodonto_hub_url(), 'variant' => 'outline' ) ); // phpcs:ignore ?>
	</div>
</section>
<?php
get_footer();
