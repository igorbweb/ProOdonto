<?php
/**
 * Template Name: Página de Vendas
 *
 * Variação da Home usada como página de vendas (tráfego pago/campanhas):
 * mesmas seções e visual, exceto:
 *   - sem seção "Blog" (não faz sentido distrair o visitante da campanha
 *     com conteúdo de blog);
 *   - seção "Unidades" só com o mapa, sem a lista de cards de unidade
 *     (evita fragmentar a decisão em "qual unidade escolher" numa página
 *     pensada para converter direto);
 *   - um CTA centralizado, com chamada em caixa alta, ao final de cada
 *     seção de conteúdo (Sobre, Tratamentos, Passo a passo, Avaliações,
 *     Unidades) — Hero, Resultados e o CTA final já tinham CTA próprio e
 *     permanecem como estão.
 *
 * Todo o conteúdo editável (Hero, Sobre + galeria, Resultados,
 * Tratamentos, Passo a passo, Avaliações, Unidades e CTA final, além da
 * URL de destino dos CTAs) vem de custom fields ACF — grupo único "Página
 * de Vendas" (key group_vendas, acf-json/group_vendas.json), com 1 aba por
 * seção. Os grupos "group_vendas_*" separados em inc/acf-fields.php são
 * histórico da versão anterior à migração pra abas (2026-08-31) e ficam
 * desativados de propósito dentro de um bloco "if (false)" — não edite lá,
 * edite o JSON (ou pelo wp-admin, que reexporta o JSON sozinho). Como o
 * mesmo template pode ser usado em várias páginas (uma por campanha), cada
 * página recebe, na primeira vez que é salva, a copy original do projeto
 * como ponto de partida (ver inc/content-seed.php) — dali em diante, cada
 * campanha pode divergir livremente pelo wp-admin.
 *
 * O CSS específico fica em assets/css/pages/vendas.css e complementa
 * assets/css/pages/home.css — ambos carregam automaticamente (ver
 * inc/enqueue.php) tanto se a página no wp-admin usar este template
 * (Atributos da página → "Página de Vendas") quanto se tiver o slug
 * "vendas", não importa a combinação. O Hero é um componente à parte,
 * com seu próprio CSS (assets/css/hero-vendas.css) e JS
 * (assets/js/pages/vendas.js) — ver preview/hero-vendas.html.
 */

defined('ABSPATH') || exit;

$proodonto_placeholder_img = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1600' height='900'%3E%3Crect width='100%25' height='100%25' fill='%23e2e4e8'/%3E%3C/svg%3E";

// URL de todos os CTAs da página (custom field "cta_url", grupo
// "group_vendas_cta" em inc/acf-fields.php) — permite apontar cada página
// de vendas (campanha) pra um destino diferente (wa.me, link de rastreio,
// etc.) sem mexer em código. Sem valor preenchido, cai no WhatsApp padrão
// do tema. Inclui o CTA principal do hero (ver grupo "Página de Vendas —
// Hero" logo abaixo).
$proodonto_vendas_cta_url = function_exists('get_field') ? get_field('cta_url') : '';
$proodonto_vendas_whatsapp = function_exists('proodonto_get_whatsapp') ? proodonto_get_whatsapp() : '';
$proodonto_vendas_whatsapp = $proodonto_vendas_whatsapp ? $proodonto_vendas_whatsapp : '5511300000000';
$proodonto_whatsapp_url = $proodonto_vendas_cta_url ? $proodonto_vendas_cta_url : 'https://wa.me/' . $proodonto_vendas_whatsapp . '?text=' . rawurlencode('Olá! Gostaria de agendar uma avaliação na ProOdonto.');

// Galeria da seção "Sobre" (custom field "galeria_sobre", grupo
// "group_vendas_about_gallery" em inc/acf-fields.php) — sem nenhuma foto
// cadastrada, cai na imagem de espaço reservado (comportamento igual ao
// resto do tema).
$proodonto_about_gallery = function_exists('get_field') ? get_field('galeria_sobre') : false;

// Hero (aba "Hero" do grupo "Página de Vendas", acf-json/group_vendas.json)
// — substitui o antigo banner em carrossel: eyebrow + h1 + texto + imagem +
// depoimento, todos ACF. Sem imagem/depoimento, os respectivos blocos não
// renderizam (ver assets/css/hero-vendas.css e preview/hero-vendas.html).
$proodonto_hero_eyebrow = function_exists('get_field') ? get_field('hero_eyebrow') : '';
$proodonto_hero_titulo = function_exists('get_field') ? get_field('hero_titulo') : '';
$proodonto_hero_texto = function_exists('get_field') ? get_field('hero_texto') : '';
$proodonto_hero_imagem = function_exists('get_field') ? get_field('hero_imagem') : false;
$proodonto_hero_imagem_mob = function_exists('get_field') ? get_field('hero_imagem_mobile') : false;
$proodonto_hero_imagem_mob = $proodonto_hero_imagem_mob ? $proodonto_hero_imagem_mob : $proodonto_hero_imagem;
$proodonto_hero_depo_texto = function_exists('get_field') ? get_field('hero_depoimento_texto') : '';
$proodonto_hero_depo_autor = function_exists('get_field') ? get_field('hero_depoimento_autor') : '';
$proodonto_hero_depo_data = function_exists('get_field') ? get_field('hero_depoimento_data') : '';

// Unidade da landing (se houver) — mesma fonte da seção "Unidades" mais
// abaixo (proodonto_get_current_unit_slug(), pelo slug da própria página).
// Sem unidade reconhecida, o hero perde o CTA secundário "Como chegar" e a
// barra fixa perde o botão de rota — não há endereço único pra apontar
// numa landing de campanha genérica (ver assets/css/hero-vendas.css).
$proodonto_hero_unit_slug = function_exists('proodonto_get_current_unit_slug') ? proodonto_get_current_unit_slug() : '';
$proodonto_hero_unit = $proodonto_hero_unit_slug && function_exists('proodonto_get_unit_by_slug') ? proodonto_get_unit_by_slug($proodonto_hero_unit_slug) : null;

$proodonto_hero_classes = array('hero-vendas');
if (!$proodonto_hero_imagem) {
	$proodonto_hero_classes[] = 'hero-vendas--sem-imagem';
}
if (!$proodonto_hero_depo_texto) {
	$proodonto_hero_classes[] = 'hero-vendas--sem-depoimento';
}
if (!$proodonto_hero_unit) {
	$proodonto_hero_classes[] = 'hero-vendas--sem-unidade';
}

/**
 * Monta o link + texto (em caixa alta) do CTA centralizado que esta
 * página acrescenta ao final de cada seção de conteúdo. Sem ícone e sem
 * a cor de marca do WhatsApp de propósito — aqui é só um reforço de
 * conversão no meio da página, o CTA "com força" fica pro WhatsApp
 * verde do final (closing-cta) e da seção de Resultados.
 */
function proodonto_vendas_section_cta($label, $url)
{
	printf(
		'<div class="section-cta"><a href="%1$s" target="_blank" rel="noopener noreferrer" class="cta">%2$s</a></div>',
		esc_url($url),
		esc_html($label)
	);
}

get_header();
?>

<main id="primary" class="site-main">
	<section class="<?php echo esc_attr(implode(' ', $proodonto_hero_classes)); ?>">
		<div class="hero-vendas__inner">
			<div class="hero-vendas__conteudo">
				<p class="hero-vendas__eyebrow">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
						stroke-linejoin="round" aria-hidden="true">
						<path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11Z" />
						<circle cx="12" cy="10" r="2.5" />
					</svg>
					<?php echo esc_html($proodonto_hero_eyebrow); ?>
				</p>
				<h1 class="hero-vendas__titulo"><?php echo esc_html($proodonto_hero_titulo); ?></h1>
				<p class="hero-vendas__texto"><?php echo wp_kses_post($proodonto_hero_texto); ?></p>
				<div class="hero-vendas__acoes">
					<a class="cta" href="<?php echo esc_url($proodonto_whatsapp_url); ?>" target="_blank"
						rel="noopener">AGENDE AGORA</a>
					<?php if ($proodonto_hero_unit): ?>
						<a class="hero-vendas__cta-secundario"
							href="<?php echo esc_url($proodonto_hero_unit['maps_url']); ?>" target="_blank"
							rel="noopener">Como chegar</a>
					<?php endif; ?>
				</div>
				<ul class="hero-vendas__chips">
					<li>5,0 no Google</li>
					<li>+25 mil sorrisos</li>
					<li>13 profissionais</li>
					<li>Parcelado</li>
				</ul>
				<hr class="hero-vendas__divisor">
				<ul class="hero-vendas__prova">
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path
								d="M12 2l2.9 6.3 6.9.6-5.2 4.6 1.6 6.8-6.2-3.7-6.2 3.7 1.6-6.8-5.2-4.6 6.9-.6L12 2Z" />
						</svg>
						<span><strong>5,0</strong> de nota no Google</span>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M20 6 9 17l-5-5" />
						</svg>
						<span><strong>+25 mil</strong> sorrisos transformados</span>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M17 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1" />
							<circle cx="10" cy="7" r="3.5" />
							<path d="M21 20v-1a4 4 0 0 0-3-3.87" />
							<path d="M16.5 3.63A4 4 0 0 1 16.5 11" />
						</svg>
						<span><strong>13 profissionais</strong>, todas as áreas</span>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<rect x="2.5" y="5.5" width="19" height="14" rx="2" />
							<path d="M2.5 10h19" />
						</svg>
						<span>Pagamento <strong>parcelado</strong></span>
					</li>
				</ul>
			</div>
			<?php if ($proodonto_hero_imagem): ?>
				<div class="hero-vendas__midia">
					<picture>
						<source media="(min-width: 900px)"
							srcset="<?php echo esc_attr(wp_get_attachment_image_srcset($proodonto_hero_imagem['ID'])); ?>">
						<img class="hero-vendas__imagem" src="<?php echo esc_url($proodonto_hero_imagem_mob['url']); ?>"
							srcset="<?php echo esc_attr(wp_get_attachment_image_srcset($proodonto_hero_imagem_mob['ID'])); ?>"
							sizes="100vw" width="<?php echo esc_attr($proodonto_hero_imagem['width']); ?>"
							height="<?php echo esc_attr($proodonto_hero_imagem['height']); ?>"
							alt="<?php echo esc_attr($proodonto_hero_imagem['alt']); ?>" loading="eager"
							fetchpriority="high">
					</picture>
					<?php if ($proodonto_hero_depo_texto): ?>
						<figure class="hero-vendas__depoimento">
							<div class="hero-vendas__estrelas">
								<?php for ($proodonto_hero_estrela = 0; $proodonto_hero_estrela < 5; $proodonto_hero_estrela++): ?>
									<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
										<path
											d="M12 2l2.9 6.3 6.9.6-5.2 4.6 1.6 6.8-6.2-3.7-6.2 3.7 1.6-6.8-5.2-4.6 6.9-.6L12 2Z" />
									</svg>
								<?php endfor; ?>
								<span class="hero-vendas__no-google">no Google</span>
							</div>
							<blockquote><?php echo esc_html($proodonto_hero_depo_texto); ?></blockquote>
							<figcaption>
								<?php echo esc_html($proodonto_hero_depo_autor); ?>
								<?php if ($proodonto_hero_depo_data): ?> ·
									<?php echo esc_html($proodonto_hero_depo_data); ?>		<?php endif; ?>
							</figcaption>
						</figure>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<div class="hero-vendas-barra">
		<?php if ($proodonto_hero_unit): ?>
			<a class="hero-vendas-barra__rota" href="<?php echo esc_url($proodonto_hero_unit['maps_url']); ?>"
				target="_blank" rel="noopener" aria-label="Como chegar">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
					stroke-linejoin="round" aria-hidden="true">
					<path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11Z" />
					<circle cx="12" cy="10" r="2.5" />
				</svg>
			</a>
		<?php endif; ?>
		<a class="hero-vendas-barra__cta" href="<?php echo esc_url($proodonto_whatsapp_url); ?>" target="_blank"
			rel="noopener">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
				stroke-linejoin="round" aria-hidden="true">
				<path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.55L4 20l1.02-4.42A8.5 8.5 0 1 1 21 11.5Z" />
				<path d="M8.5 10.5c.3 2.4 2.6 4.7 5 5" />
			</svg>
			AGENDAR AVALIAÇÃO
		</a>
	</div>

	<?php
	// Letreiro (marquee) de diferenciais — custom field "marquee_itens"
	// (grupo "Página de Vendas — Letreiro de diferenciais").
	$proodonto_marquee_items = array();
	if (have_rows('marquee_itens')):
		while (have_rows('marquee_itens')):
			the_row();
			$proodonto_marquee_items[] = array(
				'label' => get_sub_field('label'),
				'icon' => get_sub_field('icone_svg'),
			);
		endwhile;
	endif;
	?>
	<section class="marquee">
		<div class="marquee__track">
			<?php for ($proodonto_pass = 0; $proodonto_pass < 2; $proodonto_pass++): ?>
				<?php foreach ($proodonto_marquee_items as $proodonto_item): ?>
					<div class="marquee__item">
						<svg class="marquee__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
							stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<?php echo proodonto_sanitize_svg_fragment($proodonto_item['icon']); ?>
						</svg>
						<span><?php echo esc_html($proodonto_item['label']); ?></span>
					</div>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</section>

	<section class="reviews" id="avaliacoes">
		<div class="reviews__inner">

			<div class="reviews__header">
				<p class="reviews__eyebrow">
					<?php echo esc_html(get_field('reviews_eyebrow')); ?>
				</p>
				<h2 class="reviews__title">
					<?php echo esc_html(get_field('reviews_titulo')); ?>
				</h2>
				<p class="reviews__text">
					<?php echo esc_html(get_field('reviews_texto')); ?>
				</p>
			</div>

			<div class="reviews__widget">
				<?php echo do_shortcode('[trustindex no-registration=google]'); ?>
			</div>

			<?php proodonto_vendas_section_cta(get_field('reviews_cta_label'), $proodonto_whatsapp_url); ?>
		</div>
	</section>

		<?php
	// Seção "Tratamentos" — custom fields do grupo "Página de Vendas —
	// Tratamentos" (cabeçalho + repeater "treatments_itens"). Card sem
	// link (não há página de tratamento pra apontar ainda) — só ícone,
	// título e texto.
	$proodonto_treatments = array();
	if (have_rows('treatments_itens')):
		while (have_rows('treatments_itens')):
			the_row();
			$proodonto_treatments[] = array(
				'title' => get_sub_field('titulo'),
				'text' => get_sub_field('texto'),
				'icon' => get_sub_field('icone_svg'),
			);
		endwhile;
	endif;
	?>
	<section class="treatments">
		<div class="treatments__inner">

			<div class="treatments__header">
				<p class="treatments__eyebrow"><?php echo esc_html(get_field('treatments_eyebrow')); ?></p>
				<h2 class="treatments__title"><?php echo esc_html(get_field('treatments_titulo')); ?></h2>
				<p class="treatments__text"><?php echo esc_html(get_field('treatments_texto')); ?></p>
			</div>

			<?php // No mobile (<600px) isto vira um swiper (ver assets/js/pages/home.js) — 1.2 por vista, autoplay, loop, sem botões/paginação, pra reduzir a rolagem. A partir de 600px o JS destrói o swiper e assets/css/pages/vendas.css restaura a grade normal. ?>
			<div class="treatments-swiper swiper">
				<div class="treatments__grid swiper-wrapper">
					<?php foreach ($proodonto_treatments as $proodonto_treatment): ?>
						<div class="treatment-card swiper-slide">
							<span class="treatment-card__icon">
								<svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor"
									stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<?php echo proodonto_sanitize_svg_fragment($proodonto_treatment['icon']); ?>
								</svg>
							</span>
							<h3 class="treatment-card__title"><?php echo esc_html($proodonto_treatment['title']); ?></h3>
							<p class="treatment-card__text"><?php echo esc_html($proodonto_treatment['text']); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php proodonto_vendas_section_cta(get_field('treatments_cta_label'), $proodonto_whatsapp_url); ?>
		</div>
	</section>

		<?php
	// Seção "Antes e Depois" — custom fields do grupo "Página de Vendas —
	// Resultados" (cabeçalho + repeater "results_itens": nome + foto). Sem
	// "tratamento" no card de propósito — ver comentário original.
	//
	// Legenda de credencial (nome + CRO) hardcoded de propósito: é o mesmo
	// responsável técnico já exibido no rodapé (footer.php), exigido nas
	// normas de publicidade do CFO para fotos de resultado de tratamento.
	$proodonto_results_credencial_nome = 'Dr. Alisson M. Santana dos Santos';
	$proodonto_results_credencial_cro = 'CRO-SE 3147';

	$proodonto_results = array();
	if (have_rows('results_itens')):
		while (have_rows('results_itens')):
			the_row();
			$proodonto_results[] = array(
				'nome' => get_sub_field('nome'),
				'foto' => get_sub_field('foto'),
			);
		endwhile;
	endif;
	?>
	<section class="results">
		<div class="results__inner">

			<div class="results__header">
				<p class="results__eyebrow"><?php echo esc_html(get_field('results_eyebrow')); ?></p>
				<h2 class="results__title"><?php echo esc_html(get_field('results_titulo')); ?></h2>
				<p class="results__text"><?php echo esc_html(get_field('results_texto')); ?></p>
			</div>

			<div class="results-carousel">
				<div class="results-swiper swiper">
					<div class="swiper-wrapper">
						<?php foreach ($proodonto_results as $proodonto_result): ?>
							<div class="swiper-slide">
								<div class="result-card">
									<div class="result-card__photo">
										<?php if ($proodonto_result['foto']): ?>
										<a class="glightbox" href="<?php echo esc_url($proodonto_result['foto']['url']); ?>" data-gallery="results-gallery" data-title="<?php echo esc_attr($proodonto_results_credencial_nome . ' — ' . $proodonto_results_credencial_cro); ?>">
											<img src="<?php echo esc_url($proodonto_result['foto']['url']); ?>"
												alt="<?php echo esc_attr(sprintf('%s, paciente ProOdonto — antes e depois do tratamento', $proodonto_result['nome'])); ?>"
												loading="lazy" />
										</a>
										<?php else: ?>
										<img src="<?php echo esc_attr($proodonto_placeholder_img); ?>"
											alt="<?php echo esc_attr(sprintf('%s, paciente ProOdonto — antes e depois do tratamento', $proodonto_result['nome'])); ?>"
											loading="lazy" />
										<?php endif; ?>
										<p class="result-card__credential">
											<span
												class="result-card__credential-nome"><?php echo esc_html($proodonto_results_credencial_nome); ?></span>
											<span
												class="result-card__credential-cro"><?php echo esc_html($proodonto_results_credencial_cro); ?></span>
										</p>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="swiper-pagination"></div>
				</div>

				<button type="button" class="results-swiper-prev"
					aria-label="<?php esc_attr_e('Ver resultado anterior', 'proodonto'); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M15 18l-6-6 6-6" />
					</svg>
				</button>
				<button type="button" class="results-swiper-next"
					aria-label="<?php esc_attr_e('Ver próximo resultado', 'proodonto'); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M9 18l6-6-6-6" />
					</svg>
				</button>
			</div>

			<div class="results__cta">
				<a href="<?php echo esc_url($proodonto_whatsapp_url); ?>" target="_blank" rel="noopener noreferrer"
					class="cta">
					<?php echo esc_html(get_field('results_cta_label')); ?>
				</a>
			</div>

		</div>
	</section>

		<?php
	// Seção "Passo a passo" — custom fields do grupo "Página de Vendas —
	// Passo a passo" (cabeçalho + repeater "steps_itens").
	$proodonto_steps = array();
	if (have_rows('steps_itens')):
		while (have_rows('steps_itens')):
			the_row();
			$proodonto_steps[] = array(
				'label' => get_sub_field('label'),
				'text' => get_sub_field('texto'),
				'icon' => get_sub_field('icone_svg'),
				'success' => get_sub_field('sucesso'),
			);
		endwhile;
	endif;
	?>
	<section class="steps">
		<div class="steps__inner">

			<div class="steps__header">
				<p class="steps__eyebrow"><?php echo esc_html(get_field('steps_eyebrow')); ?></p>
				<h2 class="steps__title"><?php echo esc_html(get_field('steps_titulo')); ?></h2>
			</div>

			<div class="steps__grid">
				<?php foreach ($proodonto_steps as $proodonto_index => $proodonto_step): ?>
					<div class="step<?php echo !empty($proodonto_step['success']) ? ' step--success' : ''; ?>">
						<span class="step__badge">
							<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
								stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<?php echo proodonto_sanitize_svg_fragment($proodonto_step['icon']); ?>
							</svg>
						</span>
						<p class="step__label"><?php echo esc_html(sprintf('ETAPA %02d', $proodonto_index + 1)); ?></p>
						<h3 class="step__title"><?php echo esc_html($proodonto_step['label']); ?></h3>
						<p class="step__text"><?php echo esc_html($proodonto_step['text']); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<?php proodonto_vendas_section_cta(get_field('steps_cta_label'), $proodonto_whatsapp_url); ?>
		</div>
	</section>

	<section class="about">
		<div class="about__inner">
			<div class="about__grid">

				<div class="about__media">
					<?php if ($proodonto_about_gallery): ?>
						<div class="about-swiper swiper">
							<div class="swiper-wrapper">
								<?php foreach ($proodonto_about_gallery as $proodonto_about_image): ?>
									<div class="swiper-slide">
										<a class="glightbox" href="<?php echo esc_url($proodonto_about_image['url']); ?>" data-gallery="about-gallery">
											<img src="<?php echo esc_url($proodonto_about_image['url']); ?>"
												alt="<?php echo esc_attr($proodonto_about_image['alt'] ?: 'Foto — dentista atendendo com cuidado'); ?>"
												loading="lazy" />
										</a>
									</div>
								<?php endforeach; ?>
							</div>
							<?php if (count($proodonto_about_gallery) > 1): ?>
								<div class="swiper-pagination"></div>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<img src="<?php echo esc_attr($proodonto_placeholder_img); ?>"
							alt="Foto — dentista atendendo com cuidado" loading="lazy" />
					<?php endif; ?>
				</div>

				<?php
				$proodonto_about_stats = array();
				if (have_rows('estatisticas')):
					while (have_rows('estatisticas')):
						the_row();
						$proodonto_about_stats[] = array(
							'valor' => get_sub_field('valor'),
							'legenda' => get_sub_field('legenda'),
						);
					endwhile;
				endif;
				?>
				<div class="about__content">
					<p class="about__eyebrow"><?php echo esc_html(get_field('eyebrow')); ?></p>
					<h2 class="about__title"><?php echo nl2br(esc_html(get_field('titulo'))); ?></h2>
					<p class="about__text"><?php echo esc_html(get_field('texto')); ?></p>
					<div class="about__stats">
						<?php foreach ($proodonto_about_stats as $proodonto_stat): ?>
							<div class="about__stat">
								<div class="about__stat-value"><?php echo esc_html($proodonto_stat['valor']); ?></div>
								<div class="about__stat-label"><?php echo esc_html($proodonto_stat['legenda']); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

			</div>

			<?php proodonto_vendas_section_cta(get_field('about_cta_label'), $proodonto_whatsapp_url); ?>
		</div>
	</section>

	<?php
	// Seção "Shorts" (YouTube) — custom fields do grupo "Página de Vendas
	// — Shorts (YouTube)", mesma seção/carrossel da Home (CSS/JS
	// reaproveitados de assets/css/pages/home.css e assets/js/pages/home.js
	// — ver inc/enqueue.php). Miniatura e player são derivados
	// automaticamente do link do YouTube (proodonto_get_youtube_id() em
	// inc/template-functions.php). Linhas sem link válido são ignoradas;
	// sem nenhum vídeo válido, a seção inteira (e o modal) não é exibida.
	$proodonto_shorts_itens = array();
	if (have_rows('shorts_itens')):
		while (have_rows('shorts_itens')):
			the_row();
			$proodonto_short_id = proodonto_get_youtube_id(get_sub_field('url'));

			if (!$proodonto_short_id) {
				continue;
			}

			$proodonto_shorts_itens[] = array(
				'id' => $proodonto_short_id,
				'titulo' => get_sub_field('titulo'),
				'capa' => get_sub_field('capa_personalizada'),
			);
		endwhile;
	endif;
	?>
	<?php if ($proodonto_shorts_itens): ?>
		<section class="shorts">
			<div class="shorts__inner">

				<div class="shorts__header">
					<p class="shorts__eyebrow"><?php echo esc_html(get_field('shorts_eyebrow')); ?></p>
					<h2 class="shorts__title"><?php echo esc_html(get_field('shorts_titulo')); ?></h2>
					<p class="shorts__text"><?php echo esc_html(get_field('shorts_texto')); ?></p>
				</div>

				<div class="shorts-carousel">
					<div class="shorts-swiper swiper">
						<div class="swiper-wrapper">
							<?php foreach ($proodonto_shorts_itens as $proodonto_short): ?>
								<div class="swiper-slide">
									<button type="button" class="short-card"
										data-youtube-id="<?php echo esc_attr($proodonto_short['id']); ?>"
										aria-haspopup="dialog"
										aria-label="<?php echo esc_attr($proodonto_short['titulo'] ? sprintf(__('Assistir vídeo: %s', 'proodonto'), $proodonto_short['titulo']) : __('Assistir vídeo', 'proodonto')); ?>">
										<img src="<?php echo $proodonto_short['capa'] ? esc_url($proodonto_short['capa']['url']) : esc_url(proodonto_get_youtube_thumbnail_url($proodonto_short['id'])); ?>"
											alt="<?php echo esc_attr($proodonto_short['capa']['alt'] ?? ($proodonto_short['titulo'] ?: 'Vídeo ProOdonto no YouTube')); ?>"
											loading="lazy" />
										<span class="short-card__play" aria-hidden="true">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
												aria-hidden="true">
												<path d="M8 5v14l11-7Z" />
											</svg>
										</span>
										<?php if ($proodonto_short['titulo']): ?>
											<span
												class="short-card__caption"><?php echo esc_html($proodonto_short['titulo']); ?></span>
										<?php endif; ?>
									</button>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="swiper-pagination"></div>
					</div>

					<button type="button" class="shorts-swiper-prev"
						aria-label="<?php esc_attr_e('Ver vídeo anterior', 'proodonto'); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M15 18l-6-6 6-6" />
						</svg>
					</button>
					<button type="button" class="shorts-swiper-next"
						aria-label="<?php esc_attr_e('Ver próximo vídeo', 'proodonto'); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M9 18l6-6-6-6" />
						</svg>
					</button>
				</div>

				<?php proodonto_vendas_section_cta(get_field('shorts_cta_label'), $proodonto_whatsapp_url); ?>
			</div>
		</section>

		<?php // Modal de vídeo — <dialog> nativo, compartilhado por todos os cards acima. O iframe só é criado no clique (ver assets/js/pages/home.js) e destruído ao fechar, pra garantir que o player realmente pare. ?>
		<dialog class="video-modal" id="video-modal" aria-label="<?php esc_attr_e('Player de vídeo', 'proodonto'); ?>">
			<div class="video-modal__inner">
				<button type="button" class="video-modal__close" data-video-modal-close
					aria-label="<?php esc_attr_e('Fechar vídeo', 'proodonto'); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
						stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M6 6l12 12M18 6 6 18" />
					</svg>
				</button>
				<div class="video-modal__player" data-video-modal-player></div>
			</div>
		</dialog>
	<?php endif; ?>

	<?php
	// Seção "Unidades" — só o mapa, sem a lista de cards (ver comentário
	// no topo do arquivo). Quando esta página é a landing de uma unidade
	// específica (slug aracaju/lagarto/simao-dias — ver
	// proodonto_get_current_unit_slug() em inc/units-map.php), mostra o
	// mapa individual daquela unidade (um pin só, bem mais "chegado") em
	// vez do mapa combinado de sempre; outras páginas de campanha (sem
	// slug de unidade) continuam com o combinado, como antes. Ambos vêm
	// da Static Maps API e ficam cacheados em disco — ver inc/units-map.php.
	// Só o cabeçalho vem do custom field (grupo "Página de Vendas —
	// Unidades (cabeçalho)").
	$proodonto_current_unit_slug = function_exists('proodonto_get_current_unit_slug') ? proodonto_get_current_unit_slug() : '';

	if ($proodonto_current_unit_slug) {
		$proodonto_units_map = function_exists('proodonto_get_unit_map_url') ? proodonto_get_unit_map_url($proodonto_current_unit_slug) : '';
		$proodonto_current_unit = function_exists('proodonto_get_unit_by_slug') ? proodonto_get_unit_by_slug($proodonto_current_unit_slug) : null;
		$proodonto_units_map_alt = sprintf('Mapa com o pin da unidade ProOdonto em %s', $proodonto_current_unit ? $proodonto_current_unit['name'] : get_the_title());
	} else {
		$proodonto_units_map = '';
		$proodonto_units_map_alt = 'Mapa com os pins das unidades ProOdonto em Aracaju, Lagarto e Simão Dias';
	}

	// Sem mapa individual (unidade sem slug reconhecido, ou geração
	// falhou), cai no combinado — nunca fica sem mapa nenhum à toa.
	if (!$proodonto_units_map) {
		$proodonto_units_map = function_exists('proodonto_get_units_map_url') ? proodonto_get_units_map_url() : '';
	}
	?>
	<section class="units" id="unidades">
		<div class="units__inner">

			<div class="units__header">
				<p class="units__eyebrow"><?php echo esc_html(get_field('units_eyebrow')); ?></p>
				<h2 class="units__title"><?php echo esc_html(get_field('units_titulo')); ?></h2>
				<p class="units__text"><?php echo esc_html(get_field('units_texto')); ?></p>
			</div>

			<div class="units__grid">

				<div class="units__map">
					<img src="<?php echo esc_attr($proodonto_units_map ? $proodonto_units_map : $proodonto_placeholder_img); ?>"
						alt="<?php echo esc_attr($proodonto_units_map_alt); ?>" loading="lazy" />
				</div>

			</div>

			<?php proodonto_vendas_section_cta(get_field('units_cta_label'), $proodonto_whatsapp_url); ?>
		</div>
	</section>

	<?php // CTA final — custom fields do grupo "Página de Vendas — CTA final". Reaproveita o mesmo link de WhatsApp montado acima. ?>
	<section class="closing-cta">
		<div class="closing-cta__inner">
			<h2 class="closing-cta__title"><?php echo esc_html(get_field('closing_titulo')); ?></h2>
			<p class="closing-cta__text"><?php echo esc_html(get_field('closing_texto')); ?></p>
			<a href="<?php echo esc_url($proodonto_whatsapp_url); ?>" target="_blank" rel="noopener noreferrer"
				class="closing-cta__button cta">

				<?php echo esc_html(get_field('closing_botao_label')); ?>
			</a>
		</div>
	</section>
</main>

<?php
get_footer();
