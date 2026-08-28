<?php
/**
 * Header — sticky no topo (com blur), com container, logo à esquerda,
 * menu ao centro (desktop) / botão hambúrguer com painel próprio no
 * mobile, um único botão de CTA à direita.
 *
 * Estilizado com Tailwind (ver assets/tailwind/input.css). Sticky (não
 * mais fixed) — o <body> não precisa de padding-top compensando a
 * altura do header, porque sticky já reserva seu próprio espaço no
 * fluxo normal do documento.
 *
 * O CTA (texto + URL) é cadastrável nas Opções do Tema — ver
 * proodonto_get_header_cta_label()/proodonto_get_header_cta_url() em
 * inc/options-page.php. Sem URL própria cadastrada, cai no WhatsApp
 * padrão do tema. Nav hardcoded por enquanto — sem menu dinâmico ainda.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_header_cta_label = function_exists( 'proodonto_get_header_cta_label' ) ? proodonto_get_header_cta_label() : 'Agendar avaliação';
$proodonto_header_cta_url   = function_exists( 'proodonto_get_header_cta_url' ) ? proodonto_get_header_cta_url() : '#';
$proodonto_nav_links  = array(
	array(
		'label' => 'Tratamentos',
		'url'   => '#',
	),
	array(
		'label' => 'Unidades',
		'url'   => '#unidades',
	),
	array(
		'label' => 'Sobre nós',
		'url'   => home_url( '/sobre/' ),
	),
	array(
		'label' => 'Blog',
		'url'   => '#blog',
	),
);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a
	href="#primary"
	class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-100 focus:rounded-proodonto focus:bg-bg focus:px-3 focus:py-2"
>
	<?php esc_html_e( 'Pular para o conteúdo', 'proodonto' ); ?>
</a>

<header id="masthead" class="site-header sticky top-0 z-50 border-b border-[#e6eaf1] bg-white/92 backdrop-blur-[10px]">
	<div class="container mx-auto flex items-center justify-between gap-4 px-4 py-3">

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding flex shrink-0 items-center gap-2" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img
					src="<?php echo esc_url( PROODONTO_URI . '/assets/images/logo3dhorizontal.png' ); ?>"
					alt="<?php bloginfo( 'name' ); ?>"
					width="597"
					height="92"
				/>
			<?php endif; ?>
		</a>

		<nav
			id="site-navigation"
			class="hidden items-center gap-7 text-[15px] font-medium text-[#3a4a5e] md:flex"
			aria-label="<?php esc_attr_e( 'Menu principal', 'proodonto' ); ?>"
		>
			<?php foreach ( $proodonto_nav_links as $proodonto_link ) : ?>
				<a href="<?php echo esc_url( $proodonto_link['url'] ); ?>" class="hover:text-cta-dark"><?php echo esc_html( $proodonto_link['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="flex shrink-0 items-center gap-3">

			<a
				href="<?php echo esc_url( $proodonto_header_cta_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				class="cta inline-flex items-center gap-2 whitespace-nowrap rounded-full px-4 py-2.5 text-[15px] font-semibold text-white hover:opacity-90 hover:no-underline"
			>
				<?php echo esc_html( $proodonto_header_cta_label ); ?>
			</a>

			<button
				type="button"
				id="menu-toggle"
				class="flex flex-col gap-1.5 p-2 md:hidden"
				aria-controls="mobile-menu"
				aria-expanded="false"
			>
				<span class="sr-only"><?php esc_html_e( 'Abrir menu', 'proodonto' ); ?></span>
				<span class="block h-0.5 w-6 bg-text"></span>
				<span class="block h-0.5 w-6 bg-text"></span>
				<span class="block h-0.5 w-6 bg-text"></span>
			</button>

		</div>

	</div>

	<div
		id="mobile-menu"
		class="grid grid-rows-[0fr] opacity-0 transition-all duration-300 ease-out md:hidden"
	>
		<!--
			Estrutura em 3 níveis, de propósito: o item de grid (este div,
			min-h-0 + overflow-hidden, SEM padding/borda própria) é o que
			realmente colapsa pra 0 — padding/borda nunca encolhem sozinhos
			via grid-template-rows, então ficam só no filho de dentro, que
			é cortado pelo overflow-hidden daqui quando fechado.
		-->
		<div class="min-h-0 overflow-hidden">
			<div class="border-t border-[#e6eaf1] bg-white px-4 py-4">
				<nav class="flex flex-col gap-1" aria-label="<?php esc_attr_e( 'Menu principal (mobile)', 'proodonto' ); ?>">
					<?php foreach ( $proodonto_nav_links as $proodonto_link ) : ?>
						<a href="<?php echo esc_url( $proodonto_link['url'] ); ?>" class="rounded-proodonto px-2 py-2.5 text-[15px] font-medium text-[#3a4a5e] hover:bg-bg-alt"><?php echo esc_html( $proodonto_link['label'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
		</div>
	</div>
</header>

<div
	id="mobile-menu-backdrop"
	class="fixed inset-0 z-40 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-300 ease-out md:hidden"
	aria-hidden="true"
></div>