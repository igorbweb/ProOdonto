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

// "Unidades" vira um dropdown com as 3 páginas de unidade (aracaju,
// lagarto, simao-dias — ver proodonto_get_unit_nav_pages() em
// inc/template-functions.php) em vez de um link direto pra
// section#unidades. Sem nenhuma unidade publicada ainda, cai de volta pro
// link antigo (fallback defensivo — não deixa o item do menu quebrado).
$proodonto_unit_pages = function_exists( 'proodonto_get_unit_nav_pages' ) ? proodonto_get_unit_nav_pages() : array();

$proodonto_nav_links = array(
	array(
		'label' => 'Tratamentos',
		'url'   => home_url( '/#tratamentos' ),
	),
	array(
		'label'    => 'Unidades',
		'url'      => home_url( '/#unidades' ),
		'dropdown' => $proodonto_unit_pages,
	),
	array(
		'label' => 'Sobre nós',
		'url'   => home_url( '/sobre/' ),
	),
	array(
		'label' => 'Blog',
		'url'   => home_url( '/blog/' ),
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
				<?php if ( ! empty( $proodonto_link['dropdown'] ) ) : ?>
					<div class="nav-dropdown">
						<button type="button" class="nav-dropdown__trigger hover:text-cta-dark" aria-haspopup="true" aria-expanded="false">
							<?php echo esc_html( $proodonto_link['label'] ); ?>
							<svg class="nav-dropdown__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
						</button>
						<div class="nav-dropdown__panel">
							<div class="nav-dropdown__panel-inner" role="menu" aria-label="<?php echo esc_attr( $proodonto_link['label'] ); ?>">
								<?php foreach ( $proodonto_link['dropdown'] as $proodonto_unit ) : ?>
									<a href="<?php echo esc_url( $proodonto_unit['url'] ); ?>" class="nav-dropdown__item" role="menuitem"><?php echo esc_html( $proodonto_unit['label'] ); ?></a>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( $proodonto_link['url'] ); ?>" class="hover:text-cta-dark"><?php echo esc_html( $proodonto_link['label'] ); ?></a>
				<?php endif; ?>
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
						<?php if ( ! empty( $proodonto_link['dropdown'] ) ) : ?>
							<div class="mobile-nav-accordion">
								<button
									type="button"
									class="mobile-nav-accordion__trigger flex w-full items-center justify-between rounded-proodonto px-2 py-2.5 text-[15px] font-medium text-[#3a4a5e] hover:bg-bg-alt"
									aria-expanded="false"
								>
									<?php echo esc_html( $proodonto_link['label'] ); ?>
									<svg class="mobile-nav-accordion__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
								</button>
								<div class="mobile-nav-accordion__panel grid grid-rows-[0fr] transition-all duration-300 ease-out">
									<div class="min-h-0 overflow-hidden">
										<div class="flex flex-col gap-1 py-1 pl-4">
											<?php foreach ( $proodonto_link['dropdown'] as $proodonto_unit ) : ?>
												<a href="<?php echo esc_url( $proodonto_unit['url'] ); ?>" class="rounded-proodonto px-2 py-2 text-[14px] text-[#3a4a5e] hover:bg-bg-alt"><?php echo esc_html( $proodonto_unit['label'] ); ?></a>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>
						<?php else : ?>
							<a href="<?php echo esc_url( $proodonto_link['url'] ); ?>" class="rounded-proodonto px-2 py-2.5 text-[15px] font-medium text-[#3a4a5e] hover:bg-bg-alt"><?php echo esc_html( $proodonto_link['label'] ); ?></a>
						<?php endif; ?>
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