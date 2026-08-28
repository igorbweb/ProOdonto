<?php
/**
 * Template Name: Sobre / Quem Somos
 *
 * Página institucional "Sobre / Quem Somos" — mesmo padrão das páginas
 * Home e Vendas: seções fixas no template, conteúdo 100% em custom fields
 * ACF (grupos "Sobre — *" registrados em inc/acf-fields.php). Na primeira
 * execução do tema em cada ambiente, o conteúdo já nasce preenchido com
 * copy padrão (ver inc/content-seed.php e inc/page-content-defaults.php).
 *
 * Seções, pensadas para reforçar sinais de SEO/GEO/E-E-A-T numa página
 * institucional de saúde (YMYL):
 *   1. Hero — quem somos, em uma frase (H1 único e visível da página).
 *   2. Nossa história — linha do tempo (marcos reais, sem datas inventadas).
 *   3. Missão, visão e valores.
 *   4. Números — estatísticas consistentes com as demais páginas.
 *   5. Corpo clínico — nome, especialidade e CRO de cada profissional
 *      (mesmo princípio de inc/author-credentials.php: mostrar QUEM
 *      atende, não só o nome da clínica). Nasce com profissionais de
 *      EXEMPLO — troque pelos dados reais antes de publicar (ver aviso
 *      no grupo ACF "Sobre — Corpo Clínico / Equipe").
 *   6. Biossegurança — protocolos e cuidados.
 *   7. Unidades — reaproveita inc/units-map.php (mesma fonte de
 *      endereços usada na Home/Vendas — NAP consistente entre páginas).
 *   8. Perguntas frequentes — vira JSON-LD "FAQPage" automaticamente
 *      (ver inc/page-sobre-schema.php).
 *   9. CTA final.
 *
 * O corpo clínico e a FAQ também alimentam JSON-LD (schema.org) extra
 * específico desta página — ver inc/page-sobre-schema.php.
 *
 * Mobile-first: o CSS (assets/css/pages/sobre.css) define a base para
 * telas pequenas e usa min-width (600/900/1200px) para tablet/desktop,
 * mesmo padrão de assets/css/pages/home.css.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_placeholder_img = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1600' height='900'%3E%3Crect width='100%25' height='100%25' fill='%23e2e4e8'/%3E%3C/svg%3E";
$proodonto_avatar_placeholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Crect width='100%25' height='100%25' fill='%23e3f5f6'/%3E%3Ccircle cx='200' cy='160' r='70' fill='%23bfe4e6'/%3E%3Ccircle cx='200' cy='430' r='160' fill='%23bfe4e6'/%3E%3C/svg%3E";

// Número de WhatsApp cadastrado nas Opções do Tema — mesmo padrão de
// page-home.php/page-vendas.php, reaproveitado no hero e no CTA final.
$proodonto_whatsapp     = function_exists( 'proodonto_get_whatsapp' ) ? proodonto_get_whatsapp() : '';
$proodonto_whatsapp     = $proodonto_whatsapp ? $proodonto_whatsapp : '5511300000000';
$proodonto_whatsapp_url = 'https://wa.me/' . $proodonto_whatsapp . '?text=' . rawurlencode( 'Olá! Gostaria de saber mais sobre a PRÓ-ODONTO.' );

get_header();
?>

<main id="primary" class="site-main">

	<?php
	// Seção "Hero" — custom fields do grupo "Sobre — Hero (topo)". Título
	// (hero_titulo) é o único H1 visível desta página.
	$proodonto_hero_imagem = get_field( 'hero_imagem' );
	?>
	<section class="sobre-hero">
		<div class="sobre-hero__inner">

			<?php if ( function_exists( 'proodonto_breadcrumbs' ) ) : ?>
				<?php proodonto_breadcrumbs(); ?>
			<?php endif; ?>

			<div class="sobre-hero__grid">
				<div class="sobre-hero__content">
					<p class="sobre-hero__eyebrow"><?php echo esc_html( get_field( 'hero_eyebrow' ) ); ?></p>
					<h1 class="sobre-hero__title"><?php echo esc_html( get_field( 'hero_titulo' ) ); ?></h1>
					<p class="sobre-hero__text"><?php echo esc_html( get_field( 'hero_texto' ) ); ?></p>
					<a
						href="<?php echo esc_url( $proodonto_whatsapp_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						class="cta cta--whatsapp"
					>
						<?php echo esc_html( get_field( 'hero_cta_label' ) ); ?>
					</a>
				</div>
				<div class="sobre-hero__media">
					<img
						src="<?php echo $proodonto_hero_imagem ? esc_url( $proodonto_hero_imagem['url'] ) : esc_attr( $proodonto_placeholder_img ); ?>"
						alt="<?php echo esc_attr( $proodonto_hero_imagem['alt'] ?? 'Equipe PRÓ-ODONTO' ); ?>"
						loading="eager"
						fetchpriority="high"
					/>
				</div>
			</div>

		</div>
	</section>

	<?php
	// Seção "Nossa História" — custom fields do grupo "Sobre — Nossa
	// História" (cabeçalho + repeater "historia_itens": marco/período,
	// título, texto).
	$proodonto_historia_itens = array();
	if ( have_rows( 'historia_itens' ) ) :
		while ( have_rows( 'historia_itens' ) ) : the_row();
			$proodonto_historia_itens[] = array(
				'ano'    => get_sub_field( 'ano' ),
				'titulo' => get_sub_field( 'titulo' ),
				'texto'  => get_sub_field( 'texto' ),
			);
		endwhile;
	endif;
	?>
	<section class="historia">
		<div class="historia__inner">

			<div class="historia__header">
				<p class="historia__eyebrow"><?php echo esc_html( get_field( 'historia_eyebrow' ) ); ?></p>
				<h2 class="historia__title"><?php echo esc_html( get_field( 'historia_titulo' ) ); ?></h2>
				<p class="historia__text"><?php echo esc_html( get_field( 'historia_texto' ) ); ?></p>
			</div>

			<ol class="timeline">
				<?php foreach ( $proodonto_historia_itens as $proodonto_marco ) : ?>
					<li class="timeline__item">
						<span class="timeline__dot" aria-hidden="true"></span>
						<span class="timeline__period"><?php echo esc_html( $proodonto_marco['ano'] ); ?></span>
						<h3 class="timeline__title"><?php echo esc_html( $proodonto_marco['titulo'] ); ?></h3>
						<p class="timeline__text"><?php echo esc_html( $proodonto_marco['texto'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>

		</div>
	</section>

	<?php
	// Seção "Missão, Visão e Valores" — custom fields do grupo "Sobre —
	// Missão, Visão e Valores".
	$proodonto_valores_itens = array();
	if ( have_rows( 'valores_itens' ) ) :
		while ( have_rows( 'valores_itens' ) ) : the_row();
			$proodonto_valores_itens[] = array(
				'titulo' => get_sub_field( 'titulo' ),
				'texto'  => get_sub_field( 'texto' ),
				'icon'   => get_sub_field( 'icone_svg' ),
			);
		endwhile;
	endif;
	?>
	<section class="valores">
		<div class="valores__inner">

			<div class="valores__header">
				<p class="valores__eyebrow"><?php echo esc_html( get_field( 'valores_eyebrow' ) ); ?></p>
				<h2 class="valores__title"><?php echo esc_html( get_field( 'valores_titulo' ) ); ?></h2>
			</div>

			<div class="valores__mv">
				<div class="valores__mv-card">
					<h3 class="valores__mv-title"><?php echo esc_html( get_field( 'missao_titulo' ) ); ?></h3>
					<p class="valores__mv-text"><?php echo esc_html( get_field( 'missao_texto' ) ); ?></p>
				</div>
				<div class="valores__mv-card">
					<h3 class="valores__mv-title"><?php echo esc_html( get_field( 'visao_titulo' ) ); ?></h3>
					<p class="valores__mv-text"><?php echo esc_html( get_field( 'visao_texto' ) ); ?></p>
				</div>
			</div>

			<div class="valores__grid">
				<?php foreach ( $proodonto_valores_itens as $proodonto_valor ) : ?>
					<div class="value-card">
						<span class="value-card__icon">
							<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<?php echo proodonto_sanitize_svg_fragment( $proodonto_valor['icon'] ); ?>
							</svg>
						</span>
						<h3 class="value-card__title"><?php echo esc_html( $proodonto_valor['titulo'] ); ?></h3>
						<p class="value-card__text"><?php echo esc_html( $proodonto_valor['texto'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
	</section>

	<?php
	// Seção "Números" — custom fields do grupo "Sobre — Números".
	$proodonto_numeros_itens = array();
	if ( have_rows( 'numeros_itens' ) ) :
		while ( have_rows( 'numeros_itens' ) ) : the_row();
			$proodonto_numeros_itens[] = array(
				'valor'   => get_sub_field( 'valor' ),
				'legenda' => get_sub_field( 'legenda' ),
			);
		endwhile;
	endif;
	?>
	<section class="numeros">
		<div class="numeros__inner">

			<div class="numeros__header">
				<p class="numeros__eyebrow"><?php echo esc_html( get_field( 'numeros_eyebrow' ) ); ?></p>
				<h2 class="numeros__title"><?php echo esc_html( get_field( 'numeros_titulo' ) ); ?></h2>
			</div>

			<div class="numeros__grid">
				<?php foreach ( $proodonto_numeros_itens as $proodonto_numero ) : ?>
					<div class="numeros__item">
						<div class="numeros__value"><?php echo esc_html( $proodonto_numero['valor'] ); ?></div>
						<div class="numeros__label"><?php echo esc_html( $proodonto_numero['legenda'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
	</section>

	<?php
	// Seção "Corpo Clínico / Equipe" — custom fields do grupo "Sobre —
	// Corpo Clínico / Equipe". Sinal de E-E-A-T: mostra quem atende, com
	// especialidade e CRO. O JSON-LD correspondente (inc/page-sobre-schema.php)
	// só publica um profissional se o campo CRO estiver preenchido.
	$proodonto_equipe_itens = array();
	if ( have_rows( 'equipe_itens' ) ) :
		while ( have_rows( 'equipe_itens' ) ) : the_row();
			$proodonto_equipe_itens[] = array(
				'foto'  => get_sub_field( 'foto' ),
				'nome'  => get_sub_field( 'nome' ),
				'cargo' => get_sub_field( 'cargo' ),
				'cro'   => get_sub_field( 'cro' ),
				'bio'   => get_sub_field( 'bio' ),
			);
		endwhile;
	endif;
	?>
	<section class="equipe" id="equipe">
		<div class="equipe__inner">

			<div class="equipe__header">
				<p class="equipe__eyebrow"><?php echo esc_html( get_field( 'equipe_eyebrow' ) ); ?></p>
				<h2 class="equipe__title"><?php echo esc_html( get_field( 'equipe_titulo' ) ); ?></h2>
				<p class="equipe__text"><?php echo esc_html( get_field( 'equipe_texto' ) ); ?></p>
			</div>

			<div class="equipe__grid">
				<?php foreach ( $proodonto_equipe_itens as $proodonto_membro ) : ?>
					<div class="team-card">
						<div class="team-card__photo">
							<img
								src="<?php echo $proodonto_membro['foto'] ? esc_url( $proodonto_membro['foto']['url'] ) : esc_attr( $proodonto_avatar_placeholder ); ?>"
								alt="<?php echo esc_attr( $proodonto_membro['foto']['alt'] ?? $proodonto_membro['nome'] ); ?>"
								loading="lazy"
							/>
						</div>
						<h3 class="team-card__name"><?php echo esc_html( $proodonto_membro['nome'] ); ?></h3>
						<p class="team-card__role"><?php echo esc_html( $proodonto_membro['cargo'] ); ?></p>
						<?php if ( $proodonto_membro['cro'] ) : ?>
							<p class="team-card__cro"><?php echo esc_html( $proodonto_membro['cro'] ); ?></p>
						<?php endif; ?>
						<?php if ( $proodonto_membro['bio'] ) : ?>
							<p class="team-card__bio"><?php echo esc_html( $proodonto_membro['bio'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
	</section>

	<?php
	// Seção "Biossegurança" — custom fields do grupo "Sobre — Biossegurança".
	$proodonto_seguranca_itens = array();
	if ( have_rows( 'seguranca_itens' ) ) :
		while ( have_rows( 'seguranca_itens' ) ) : the_row();
			$proodonto_seguranca_itens[] = array(
				'titulo' => get_sub_field( 'titulo' ),
				'texto'  => get_sub_field( 'texto' ),
				'icon'   => get_sub_field( 'icone_svg' ),
			);
		endwhile;
	endif;
	?>
	<section class="seguranca">
		<div class="seguranca__inner">

			<div class="seguranca__header">
				<p class="seguranca__eyebrow"><?php echo esc_html( get_field( 'seguranca_eyebrow' ) ); ?></p>
				<h2 class="seguranca__title"><?php echo esc_html( get_field( 'seguranca_titulo' ) ); ?></h2>
				<p class="seguranca__text"><?php echo esc_html( get_field( 'seguranca_texto' ) ); ?></p>
			</div>

			<div class="seguranca__grid">
				<?php foreach ( $proodonto_seguranca_itens as $proodonto_item ) : ?>
					<div class="safety-card">
						<span class="safety-card__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<?php echo proodonto_sanitize_svg_fragment( $proodonto_item['icon'] ); ?>
							</svg>
						</span>
						<div>
							<h3 class="safety-card__title"><?php echo esc_html( $proodonto_item['titulo'] ); ?></h3>
							<p class="safety-card__text"><?php echo esc_html( $proodonto_item['texto'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
	</section>

	<?php
	// Seção "Unidades" — a lista de unidades em si (endereços, mapa) vem
	// de inc/units-map.php, mesma fonte reaproveitada por Home/Vendas
	// (NAP consistente entre páginas). Só o cabeçalho vem do custom field.
	$proodonto_units     = proodonto_get_units();
	$proodonto_units_map = function_exists( 'proodonto_get_units_map_url' ) ? proodonto_get_units_map_url() : '';
	?>
	<section class="units" id="unidades">
		<div class="units__inner">

			<div class="units__header">
				<p class="units__eyebrow"><?php echo esc_html( get_field( 'units_eyebrow' ) ); ?></p>
				<h2 class="units__title"><?php echo esc_html( get_field( 'units_titulo' ) ); ?></h2>
				<p class="units__text"><?php echo esc_html( get_field( 'units_texto' ) ); ?></p>
			</div>

			<div class="units__grid">

				<div class="units__map">
					<img
						src="<?php echo esc_attr( $proodonto_units_map ? $proodonto_units_map : $proodonto_placeholder_img ); ?>"
						alt="Mapa com os pins das unidades PRÓ-ODONTO em Aracaju, Lagarto e Simão Dias"
						loading="lazy"
					/>
				</div>

				<div class="units__list">
					<?php foreach ( $proodonto_units as $proodonto_index => $proodonto_unit ) : ?>
						<div class="unit-card">
							<div class="unit-card__info">

								<div class="unit-card__heading">
									<span class="unit-card__number" aria-hidden="true"><?php echo (int) ( $proodonto_index + 1 ); ?></span>
									<h3 class="unit-card__name"><?php echo esc_html( $proodonto_unit['name'] ); ?></h3>
								</div>

								<div class="unit-card__detail">
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<path d="M12 21s7-6.6 7-11.5A7 7 0 0 0 5 9.5C5 14.4 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.3"/>
									</svg>
									<?php echo esc_html( $proodonto_unit['address'] ); ?>
								</div>

								<a
									href="<?php echo esc_url( $proodonto_unit['maps_url'] ); ?>"
									target="_blank"
									rel="noopener noreferrer"
									class="unit-card__detail unit-card__detail--link"
								>
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<path d="M9 18l6-6-6-6"/>
									</svg>
									Ver rota no Google Maps
								</a>

							</div>

							<a
								href="<?php echo esc_url( $proodonto_unit['whatsapp_url'] ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								class="unit-card__whatsapp"
								aria-label="<?php echo esc_attr( sprintf( __( 'Falar no WhatsApp com a unidade %s', 'proodonto' ), $proodonto_unit['name'] ) ); ?>"
							>
								<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
									<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .105 5.36.102 11.943c0 2.105.549 4.16 1.595 5.976L0 24l6.335-1.652a11.882 11.882 0 0 0 5.71 1.447h.006c6.585 0 11.941-5.36 11.944-11.943a11.87 11.87 0 0 0-3.475-8.403"/>
								</svg>
							</a>

							<a
								href="<?php echo esc_url( $proodonto_unit['whatsapp_url'] ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								class="unit-card__whatsapp-cta cta"
								aria-label="<?php echo esc_attr( sprintf( __( 'Falar no WhatsApp com a unidade %s', 'proodonto' ), $proodonto_unit['name'] ) ); ?>"
							>
								<?php esc_html_e( 'Falar no WhatsApp', 'proodonto' ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>

			</div>

		</div>
	</section>

	<?php
	// Seção "Perguntas Frequentes" — custom fields do grupo "Sobre —
	// Perguntas Frequentes". <details>/<summary> nativos: acessível e sem
	// JS, e o conteúdo já existe no DOM mesmo fechado (bom para SEO/GEO).
	// Gera JSON-LD "FAQPage" automaticamente — ver inc/page-sobre-schema.php.
	$proodonto_faq_itens = array();
	if ( have_rows( 'faq_itens' ) ) :
		while ( have_rows( 'faq_itens' ) ) : the_row();
			$proodonto_faq_itens[] = array(
				'pergunta' => get_sub_field( 'pergunta' ),
				'resposta' => get_sub_field( 'resposta' ),
			);
		endwhile;
	endif;
	?>
	<section class="faq" id="faq">
		<div class="faq__inner">

			<div class="faq__header">
				<p class="faq__eyebrow"><?php echo esc_html( get_field( 'faq_eyebrow' ) ); ?></p>
				<h2 class="faq__title"><?php echo esc_html( get_field( 'faq_titulo' ) ); ?></h2>
			</div>

			<div class="faq__list">
				<?php foreach ( $proodonto_faq_itens as $proodonto_index => $proodonto_faq ) : ?>
					<details class="faq-item" <?php echo 0 === $proodonto_index ? 'open' : ''; ?>>
						<summary class="faq-item__question">
							<?php echo esc_html( $proodonto_faq['pergunta'] ); ?>
							<span class="faq-item__toggle" aria-hidden="true"></span>
						</summary>
						<p class="faq-item__answer"><?php echo esc_html( $proodonto_faq['resposta'] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>

		</div>
	</section>

	<?php // CTA final — custom fields do grupo "Sobre — CTA final". Reaproveita o WhatsApp montado no topo do arquivo. ?>
	<section class="closing-cta">
		<div class="closing-cta__inner">
			<h2 class="closing-cta__title"><?php echo esc_html( get_field( 'cta_titulo' ) ); ?></h2>
			<p class="closing-cta__text"><?php echo esc_html( get_field( 'cta_texto' ) ); ?></p>
			<a
				href="<?php echo esc_url( $proodonto_whatsapp_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				class="closing-cta__button cta"
			>
				<?php echo esc_html( get_field( 'cta_botao_label' ) ); ?>
			</a>
		</div>
	</section>

</main>

<?php
get_footer();
