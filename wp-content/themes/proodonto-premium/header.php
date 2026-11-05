<?php
/**
 * Cabeçalho: logo, menu em pílula (desktop), botão de agendar e menu do
 * celular (<dialog>, com Esc e foco presos pelo navegador).
 *
 * Nas páginas de unidade o menu vira "Nesta página" (âncoras) e o botão
 * vira "WhatsApp <cidade>".
 */

defined( 'ABSPATH' ) || exit;

$proodonto_unit    = is_page_template( 'page-vendas.php' ) ? proodonto_get_current_unit() : null;
$proodonto_nav     = $proodonto_unit ? proodonto_unit_nav_items() : proodonto_menu_items( 'primary' );
$proodonto_nav     = $proodonto_nav ? $proodonto_nav : proodonto_default_nav_items();
$proodonto_nav_lbl = $proodonto_unit ? __( 'Nesta página', 'proodonto' ) : __( 'Menu principal', 'proodonto' );

if ( $proodonto_unit ) {
	$proodonto_cta = array(
		'label' => sprintf( /* translators: %s: cidade */ __( 'WhatsApp %s', 'proodonto' ), $proodonto_unit['name'] ),
		'url'   => $proodonto_unit['whatsapp_url'],
		'icon'  => 'whatsapp',
		'attrs' => proodonto_cta_attrs( $proodonto_unit, 'cabecalho' ),
	);
} else {
	$proodonto_cta = array(
		'label' => (string) proodonto_opt( 'header_cta_label' ),
		'url'   => proodonto_units_anchor(),
		'icon'  => 'arrow',
		'attrs' => array( 'data-cta' => 'cabecalho' ),
	);
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#F7F4EE">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'proodonto' ); ?></a>

<header class="site-header" id="topo">
	<div class="wrap site-header__in">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s — início', 'proodonto' ), get_bloginfo( 'name' ) ) ); ?>"><?php echo proodonto_logo( 'dark' ); // phpcs:ignore -- wp_get_attachment_image. ?></a>

		<nav class="pillnav" aria-label="<?php echo esc_attr( $proodonto_nav_lbl ); ?>">
			<ul><?php proodonto_nav_links( $proodonto_nav ); ?></ul>
		</nav>

		<?php
		echo proodonto_button( // phpcs:ignore -- escapado no helper.
			array_merge(
				$proodonto_cta,
				array(
					'small' => true,
					'class' => 'site-header__cta',
				)
			)
		);
		?>

		<button type="button" class="menu-btn" aria-controls="po-menu" aria-expanded="false" data-menu-open>
			<?php echo proodonto_icon( 'menu', 20 ); // phpcs:ignore ?>
			<span><?php esc_html_e( 'Menu', 'proodonto' ); ?></span>
		</button>
	</div>
</header>

<dialog class="drawer" id="po-menu" aria-label="<?php esc_attr_e( 'Menu', 'proodonto' ); ?>">
	<div class="drawer__in">
		<div class="drawer__top">
			<span class="logo"><?php echo proodonto_logo( 'dark' ); // phpcs:ignore ?></span>
			<button type="button" class="menu-btn" data-menu-close>
				<?php echo proodonto_icon( 'close', 20 ); // phpcs:ignore ?>
				<span><?php esc_html_e( 'Fechar', 'proodonto' ); ?></span>
			</button>
		</div>
		<nav aria-label="<?php echo esc_attr( $proodonto_nav_lbl ); ?>">
			<ul class="drawer__nav"><?php proodonto_nav_links( $proodonto_nav, 'drawer__a' ); ?></ul>
		</nav>
		<?php $proodonto_units_all = proodonto_get_units(); ?>
		<?php if ( $proodonto_units_all ) : ?>
			<p class="eyebrow drawer__eyebrow"><?php esc_html_e( 'Agendar pelo WhatsApp', 'proodonto' ); ?></p>
			<ul class="drawer__units">
				<?php foreach ( $proodonto_units_all as $proodonto_u ) : ?>
					<li>
						<a class="tap drawer__unit" href="<?php echo esc_url( $proodonto_u['whatsapp_url'] ); ?>"<?php echo proodonto_attrs( proodonto_cta_attrs( $proodonto_u, 'menu' ) ); // phpcs:ignore ?>>
							<span><strong><?php echo esc_html( $proodonto_u['name'] ); ?></strong><small><?php echo esc_html( $proodonto_u['bairro'] ); ?></small></span>
							<span class="chip chip--solid" aria-hidden="true"><?php echo proodonto_icon( 'whatsapp', 16 ); // phpcs:ignore ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</dialog>

<main id="conteudo" class="site-main">
