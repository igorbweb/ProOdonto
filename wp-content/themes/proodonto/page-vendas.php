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
 * Todo o conteúdo editável (banner, Sobre + galeria, Resultados,
 * Tratamentos, Passo a passo, Avaliações, Unidades e CTA final, além da
 * URL de destino dos CTAs) vem de custom fields ACF — grupos "Página de
 * Vendas — *" registrados em inc/acf-fields.php. Como o mesmo template
 * pode ser usado em várias páginas (uma por campanha), cada página recebe,
 * na primeira vez que é salva, a copy original do projeto como ponto de
 * partida (ver inc/content-seed.php) — dali em diante, cada campanha pode
 * divergir livremente pelo wp-admin.
 *
 * O CSS específico fica em assets/css/pages/vendas.css e complementa
 * assets/css/pages/home.css — ambos carregam automaticamente (ver
 * inc/enqueue.php) tanto se a página no wp-admin usar este template
 * (Atributos da página → "Página de Vendas") quanto se tiver o slug
 * "vendas", não importa a combinação.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_placeholder_img = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1600' height='900'%3E%3Crect width='100%25' height='100%25' fill='%23e2e4e8'/%3E%3C/svg%3E";

// URL de todos os CTAs da página (custom field "cta_url", grupo
// "group_vendas_cta" em inc/acf-fields.php) — permite apontar cada página
// de vendas (campanha) pra um destino diferente (wa.me, link de rastreio,
// etc.) sem mexer em código. Sem valor preenchido, cai no WhatsApp padrão
// do tema. O banner (hero) não usa isso: seus links são definidos slide a
// slide.
$proodonto_vendas_cta_url = function_exists( 'get_field' ) ? get_field( 'cta_url' ) : '';
$proodonto_vendas_whatsapp = function_exists( 'proodonto_get_whatsapp' ) ? proodonto_get_whatsapp() : '';
$proodonto_vendas_whatsapp = $proodonto_vendas_whatsapp ? $proodonto_vendas_whatsapp : '5511300000000';
$proodonto_whatsapp_url   = $proodonto_vendas_cta_url ? $proodonto_vendas_cta_url : 'https://wa.me/' . $proodonto_vendas_whatsapp . '?text=' . rawurlencode( 'Olá! Gostaria de agendar uma avaliação na ProOdonto.' );

// Galeria da seção "Sobre" (custom field "galeria_sobre", grupo
// "group_vendas_about_gallery" em inc/acf-fields.php) — sem nenhuma foto
// cadastrada, cai na imagem de espaço reservado (comportamento igual ao
// resto do tema).
$proodonto_about_gallery = function_exists( 'get_field' ) ? get_field( 'galeria_sobre' ) : false;

/**
 * Monta o link + texto (em caixa alta) do CTA centralizado que esta
 * página acrescenta ao final de cada seção de conteúdo. Sem ícone e sem
 * a cor de marca do WhatsApp de propósito — aqui é só um reforço de
 * conversão no meio da página, o CTA "com força" fica pro WhatsApp
 * verde do final (closing-cta) e da seção de Resultados.
 */
function proodonto_vendas_section_cta( $label, $url ) {
	printf(
		'<div class="section-cta"><a href="%1$s" target="_blank" rel="noopener noreferrer" class="cta">%2$s</a></div>',
		esc_url( $url ),
		esc_html( $label )
	);
}

get_header();
?>

<main id="primary" class="site-main">
	<?php // H1 único da página — visualmente oculto (o hero é 100% visual/carrossel via ACF), mas garante exatamente um H1 semântico, com o título real da página no wp-admin. ?>
	<h1 class="sr-only"><?php echo esc_html( get_the_title() ); ?></h1>

	<section class="hero mb-0">
        <div class="swiper hero">
            <div class="swiper-wrapper items-stretch">
                <?php
                if (have_rows('banner')):
                    $index = 0;
                    while (have_rows('banner')): the_row();
                        $desktop_img = get_sub_field('desktop');
                        $mobile_img  = get_sub_field('mobile');

                        $desktop_id = $desktop_img['ID'] ?? null;
                        $mobile_id  = $mobile_img['ID'] ?? null;

                        $texto   = get_sub_field('texto') ?? '';
                        $link_externo = get_sub_field('link_externo');

                        $is_first = ($index === 0);
                ?>
                        <div class="swiper-slide h-auto!">
                            <a
                                <?php
                                $href = '';
                                $target = '_self';
                                $fancybox = '';
                                $play = '';
                                switch ($link_externo) {
                                    case 'externo':
                                        $href = get_sub_field('url');
                                        $target = '_blank';
                                        $play = '';
                                        break;
                                    case 'iframe':
                                        $href = get_sub_field('iframe');
                                        $target = '_self';
                                        $fancybox = 'data-fancybox="banner" data-type="iframe" data-width="80%" data-height="80%"';
                                        $play = '
                                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-8 h-8 lg:w-24 lg:h-24 hover:opacity-70 transition-opacity duration-300 ease-in-out">
                                                <img
                                                    src="' . IMG_URI . 'play.svg"
                                                    alt=""
                                                    width="140"
                                                    height="140"
                                                    class="w-full h-full"
                                                    loading="lazy"
                                                    decoding="async">
                                            </div>';
                                        break;
                                }
                                ?>
                                href="<?= $href ?? ''; ?>"
                                target="<?= $target ?? ''; ?>"
                                <?= $fancybox ?? ''; ?>
                                class="relative w-full h-full"
                                aria-label="<?= esc_attr($texto ?: 'Banner'); ?>">
                                <?= $play ?? ''; ?>
                                <picture>
                                    <?php if ($mobile_id) : ?>
                                        <source
                                            media="(max-width: 1023px)"
                                            srcset="<?= wp_get_attachment_image_srcset($mobile_id); ?>"
                                            sizes="100vw">
                                    <?php endif; ?>

                                    <?php if ($desktop_id) : ?>
                                        <source
                                            media="(min-width: 1024px)"
                                            srcset="<?= wp_get_attachment_image_srcset($desktop_id); ?>"
                                            sizes="100vw">
                                    <?php endif; ?>

                                    <img
                                        src="<?= esc_url($desktop_img['url'] ?? $mobile_img['url']); ?>"
                                        srcset="<?= $desktop_id ? wp_get_attachment_image_srcset($desktop_id) : ''; ?>"
                                        sizes="100vw"
                                        alt="Banner Stanza"
                                        loading="<?= $is_first ? 'eager' : 'lazy'; ?>"
                                        fetchpriority="<?= $is_first ? 'high' : 'auto'; ?>">
                                </picture>
                            </a>
                            <div class="gradient"></div>
                            <div class="hero-content">
                                <div class="container h-full">
                                    <div class="h-full align-items-center content-center">
                                        <div class="col-lg-6">
                                            <div class="slide-text">
                                                <?=
                                                get_sub_field('texto')
                                                ?>
                                            </div>
                                            <a href="<?php echo esc_url( $proodonto_whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="cta mt-10">AGENDE AGORA</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                        $index++;
                    endwhile;
                endif;
                ?>
            </div>
            <div class="swiper-pagination bottom-10! lg:bottom-20!"></div>
        </div>
        <div class="swiper-prev-custom hidden lg:block"></div>
        <div class="swiper-next-custom hidden lg:block"></div>
    </section>

	<?php
	// Letreiro (marquee) de diferenciais — custom field "marquee_itens"
	// (grupo "Página de Vendas — Letreiro de diferenciais").
	$proodonto_marquee_items = array();
	if ( have_rows( 'marquee_itens' ) ) :
		while ( have_rows( 'marquee_itens' ) ) : the_row();
			$proodonto_marquee_items[] = array(
				'label' => get_sub_field( 'label' ),
				'icon'  => get_sub_field( 'icone_svg' ),
			);
		endwhile;
	endif;
	?>
	<section class="marquee">
		<div class="marquee__track">
			<?php for ( $proodonto_pass = 0; $proodonto_pass < 2; $proodonto_pass++ ) : ?>
				<?php foreach ( $proodonto_marquee_items as $proodonto_item ) : ?>
					<div class="marquee__item">
						<svg
							class="marquee__icon"
							width="22" height="22" viewBox="0 0 24 24"
							fill="none" stroke="currentColor" stroke-width="1.8"
							stroke-linecap="round" stroke-linejoin="round"
							aria-hidden="true"
						>
							<?php echo proodonto_sanitize_svg_fragment( $proodonto_item['icon'] ); ?>
						</svg>
						<span><?php echo esc_html( $proodonto_item['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</section>

	<section class="about">
		<div class="about__grid">

			<div class="about__media">
				<?php if ( $proodonto_about_gallery ) : ?>
					<div class="about-swiper swiper">
						<div class="swiper-wrapper">
							<?php foreach ( $proodonto_about_gallery as $proodonto_about_image ) : ?>
								<div class="swiper-slide">
									<img
										src="<?php echo esc_url( $proodonto_about_image['url'] ); ?>"
										alt="<?php echo esc_attr( $proodonto_about_image['alt'] ?: 'Foto — dentista atendendo com cuidado' ); ?>"
										loading="lazy"
									/>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if ( count( $proodonto_about_gallery ) > 1 ) : ?>
							<div class="swiper-pagination"></div>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<img
						src="<?php echo esc_attr( $proodonto_placeholder_img ); ?>"
						alt="Foto — dentista atendendo com cuidado"
						loading="lazy"
					/>
				<?php endif; ?>
			</div>

			<?php
			$proodonto_about_stats = array();
			if ( have_rows( 'estatisticas' ) ) :
				while ( have_rows( 'estatisticas' ) ) : the_row();
					$proodonto_about_stats[] = array(
						'valor'   => get_sub_field( 'valor' ),
						'legenda' => get_sub_field( 'legenda' ),
					);
				endwhile;
			endif;
			?>
			<div class="about__content">
				<p class="about__eyebrow"><?php echo esc_html( get_field( 'eyebrow' ) ); ?></p>
				<h2 class="about__title"><?php echo nl2br( esc_html( get_field( 'titulo' ) ) ); ?></h2>
				<p class="about__text"><?php echo esc_html( get_field( 'texto' ) ); ?></p>
				<div class="about__stats">
					<?php foreach ( $proodonto_about_stats as $proodonto_stat ) : ?>
						<div class="about__stat">
							<div class="about__stat-value"><?php echo esc_html( $proodonto_stat['valor'] ); ?></div>
							<div class="about__stat-label"><?php echo esc_html( $proodonto_stat['legenda'] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
		<?php proodonto_vendas_section_cta( get_field( 'about_cta_label' ), $proodonto_whatsapp_url ); ?>
	</section>

	<?php
	// Seção "Antes e Depois" — custom fields do grupo "Página de Vendas —
	// Resultados" (cabeçalho + repeater "results_itens": nome + foto). Sem
	// "tratamento" no card de propósito — ver comentário original.
	$proodonto_results = array();
	if ( have_rows( 'results_itens' ) ) :
		while ( have_rows( 'results_itens' ) ) : the_row();
			$proodonto_results[] = array(
				'nome' => get_sub_field( 'nome' ),
				'foto' => get_sub_field( 'foto' ),
			);
		endwhile;
	endif;
	?>
	<section class="results">
		<div class="results__inner">

			<div class="results__header">
				<p class="results__eyebrow"><?php echo esc_html( get_field( 'results_eyebrow' ) ); ?></p>
				<h2 class="results__title"><?php echo esc_html( get_field( 'results_titulo' ) ); ?></h2>
				<p class="results__text"><?php echo esc_html( get_field( 'results_texto' ) ); ?></p>
			</div>

			<div class="results-carousel">
				<div class="results-swiper swiper">
					<div class="swiper-wrapper">
						<?php foreach ( $proodonto_results as $proodonto_result ) : ?>
							<div class="swiper-slide">
								<div class="result-card">
									<div class="result-card__photo">
										<img
											src="<?php echo $proodonto_result['foto'] ? esc_url( $proodonto_result['foto']['url'] ) : esc_attr( $proodonto_placeholder_img ); ?>"
											alt="<?php echo esc_attr( sprintf( '%s, paciente ProOdonto — antes e depois do tratamento', $proodonto_result['nome'] ) ); ?>"
											loading="lazy"
										/>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="swiper-pagination"></div>
				</div>

				<button type="button" class="results-swiper-prev" aria-label="<?php esc_attr_e( 'Ver resultado anterior', 'proodonto' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
				</button>
				<button type="button" class="results-swiper-next" aria-label="<?php esc_attr_e( 'Ver próximo resultado', 'proodonto' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
				</button>
			</div>

			<div class="results__cta">
				<a
					href="<?php echo esc_url( $proodonto_whatsapp_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					class="cta"
				>
					<?php echo esc_html( get_field( 'results_cta_label' ) ); ?>
				</a>
			</div>

		</div>
	</section>

	<?php
	// Seção "Tratamentos" — custom fields do grupo "Página de Vendas —
	// Tratamentos" (cabeçalho + repeater "treatments_itens"). Card sem
	// link (não há página de tratamento pra apontar ainda) — só ícone,
	// título e texto.
	$proodonto_treatments = array();
	if ( have_rows( 'treatments_itens' ) ) :
		while ( have_rows( 'treatments_itens' ) ) : the_row();
			$proodonto_treatments[] = array(
				'title' => get_sub_field( 'titulo' ),
				'text'  => get_sub_field( 'texto' ),
				'icon'  => get_sub_field( 'icone_svg' ),
			);
		endwhile;
	endif;
	?>
	<section class="treatments">
		<div class="treatments__inner">

			<div class="treatments__header">
				<p class="treatments__eyebrow"><?php echo esc_html( get_field( 'treatments_eyebrow' ) ); ?></p>
				<h2 class="treatments__title"><?php echo esc_html( get_field( 'treatments_titulo' ) ); ?></h2>
				<p class="treatments__text"><?php echo esc_html( get_field( 'treatments_texto' ) ); ?></p>
			</div>

			<?php // No mobile (<600px) isto vira um swiper (ver assets/js/pages/home.js) — 1.2 por vista, autoplay, loop, sem botões/paginação, pra reduzir a rolagem. A partir de 600px o JS destrói o swiper e assets/css/pages/vendas.css restaura a grade normal. ?>
			<div class="treatments-swiper swiper">
				<div class="treatments__grid swiper-wrapper">
					<?php foreach ( $proodonto_treatments as $proodonto_treatment ) : ?>
						<div class="treatment-card swiper-slide">
							<span class="treatment-card__icon">
								<svg
									width="27" height="27" viewBox="0 0 24 24"
									fill="none" stroke="currentColor" stroke-width="1.6"
									stroke-linecap="round" stroke-linejoin="round"
									aria-hidden="true"
								>
									<?php echo proodonto_sanitize_svg_fragment( $proodonto_treatment['icon'] ); ?>
								</svg>
							</span>
							<h3 class="treatment-card__title"><?php echo esc_html( $proodonto_treatment['title'] ); ?></h3>
							<p class="treatment-card__text"><?php echo esc_html( $proodonto_treatment['text'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php proodonto_vendas_section_cta( get_field( 'treatments_cta_label' ), $proodonto_whatsapp_url ); ?>
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
	if ( have_rows( 'shorts_itens' ) ) :
		while ( have_rows( 'shorts_itens' ) ) : the_row();
			$proodonto_short_id = proodonto_get_youtube_id( get_sub_field( 'url' ) );

			if ( ! $proodonto_short_id ) {
				continue;
			}

			$proodonto_shorts_itens[] = array(
				'id'     => $proodonto_short_id,
				'titulo' => get_sub_field( 'titulo' ),
				'capa'   => get_sub_field( 'capa_personalizada' ),
			);
		endwhile;
	endif;
	?>
	<?php if ( $proodonto_shorts_itens ) : ?>
		<section class="shorts">
			<div class="shorts__inner">

				<div class="shorts__header">
					<p class="shorts__eyebrow"><?php echo esc_html( get_field( 'shorts_eyebrow' ) ); ?></p>
					<h2 class="shorts__title"><?php echo esc_html( get_field( 'shorts_titulo' ) ); ?></h2>
					<p class="shorts__text"><?php echo esc_html( get_field( 'shorts_texto' ) ); ?></p>
				</div>

				<div class="shorts-carousel">
					<div class="shorts-swiper swiper">
						<div class="swiper-wrapper">
							<?php foreach ( $proodonto_shorts_itens as $proodonto_short ) : ?>
								<div class="swiper-slide">
									<button
										type="button"
										class="short-card"
										data-youtube-id="<?php echo esc_attr( $proodonto_short['id'] ); ?>"
										aria-haspopup="dialog"
										aria-label="<?php echo esc_attr( $proodonto_short['titulo'] ? sprintf( __( 'Assistir vídeo: %s', 'proodonto' ), $proodonto_short['titulo'] ) : __( 'Assistir vídeo', 'proodonto' ) ); ?>"
									>
										<img
											src="<?php echo $proodonto_short['capa'] ? esc_url( $proodonto_short['capa']['url'] ) : esc_url( proodonto_get_youtube_thumbnail_url( $proodonto_short['id'] ) ); ?>"
											alt="<?php echo esc_attr( $proodonto_short['capa']['alt'] ?? ( $proodonto_short['titulo'] ?: 'Vídeo ProOdonto no YouTube' ) ); ?>"
											loading="lazy"
										/>
										<span class="short-card__play" aria-hidden="true">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7Z"/></svg>
										</span>
										<?php if ( $proodonto_short['titulo'] ) : ?>
											<span class="short-card__caption"><?php echo esc_html( $proodonto_short['titulo'] ); ?></span>
										<?php endif; ?>
									</button>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="swiper-pagination"></div>
					</div>

					<button type="button" class="shorts-swiper-prev" aria-label="<?php esc_attr_e( 'Ver vídeo anterior', 'proodonto' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
					<button type="button" class="shorts-swiper-next" aria-label="<?php esc_attr_e( 'Ver próximo vídeo', 'proodonto' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
					</button>
				</div>

				<?php proodonto_vendas_section_cta( get_field( 'shorts_cta_label' ), $proodonto_whatsapp_url ); ?>
			</div>
		</section>

		<?php // Modal de vídeo — <dialog> nativo, compartilhado por todos os cards acima. O iframe só é criado no clique (ver assets/js/pages/home.js) e destruído ao fechar, pra garantir que o player realmente pare. ?>
		<dialog class="video-modal" id="video-modal" aria-label="<?php esc_attr_e( 'Player de vídeo', 'proodonto' ); ?>">
			<div class="video-modal__inner">
				<button type="button" class="video-modal__close" data-video-modal-close aria-label="<?php esc_attr_e( 'Fechar vídeo', 'proodonto' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
				</button>
				<div class="video-modal__player" data-video-modal-player></div>
			</div>
		</dialog>
	<?php endif; ?>

	<?php
	// Seção "Passo a passo" — custom fields do grupo "Página de Vendas —
	// Passo a passo" (cabeçalho + repeater "steps_itens").
	$proodonto_steps = array();
	if ( have_rows( 'steps_itens' ) ) :
		while ( have_rows( 'steps_itens' ) ) : the_row();
			$proodonto_steps[] = array(
				'label'   => get_sub_field( 'label' ),
				'text'    => get_sub_field( 'texto' ),
				'icon'    => get_sub_field( 'icone_svg' ),
				'success' => get_sub_field( 'sucesso' ),
			);
		endwhile;
	endif;
	?>
	<section class="steps">
		<div class="steps__inner">

			<div class="steps__header">
				<p class="steps__eyebrow"><?php echo esc_html( get_field( 'steps_eyebrow' ) ); ?></p>
				<h2 class="steps__title"><?php echo esc_html( get_field( 'steps_titulo' ) ); ?></h2>
			</div>

			<div class="steps__grid">
				<?php foreach ( $proodonto_steps as $proodonto_index => $proodonto_step ) : ?>
					<div class="step<?php echo ! empty( $proodonto_step['success'] ) ? ' step--success' : ''; ?>">
						<span class="step__badge">
							<svg
								width="30" height="30" viewBox="0 0 24 24"
								fill="none" stroke="currentColor" stroke-width="1.6"
								stroke-linecap="round" stroke-linejoin="round"
								aria-hidden="true"
							>
								<?php echo proodonto_sanitize_svg_fragment( $proodonto_step['icon'] ); ?>
							</svg>
						</span>
						<p class="step__label"><?php echo esc_html( sprintf( 'ETAPA %02d', $proodonto_index + 1 ) ); ?></p>
						<h3 class="step__title"><?php echo esc_html( $proodonto_step['label'] ); ?></h3>
						<p class="step__text"><?php echo esc_html( $proodonto_step['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<?php proodonto_vendas_section_cta( get_field( 'steps_cta_label' ), $proodonto_whatsapp_url ); ?>
		</div>
	</section>

	<section class="reviews" id="avaliacoes">
		<div class="reviews__inner">

			<div class="reviews__header">
				<p class="reviews__eyebrow"><?php echo esc_html( get_field( 'reviews_eyebrow' ) ); ?></p>
				<h2 class="reviews__title"><?php echo esc_html( get_field( 'reviews_titulo' ) ); ?></h2>
				<p class="reviews__text"><?php echo esc_html( get_field( 'reviews_texto' ) ); ?></p>
			</div>

			<div class="reviews__widget">
				<?php echo do_shortcode( '[trustindex no-registration=google]' ); ?>
			</div>

			<?php proodonto_vendas_section_cta( get_field( 'reviews_cta_label' ), $proodonto_whatsapp_url ); ?>
		</div>
	</section>

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
	$proodonto_current_unit_slug = function_exists( 'proodonto_get_current_unit_slug' ) ? proodonto_get_current_unit_slug() : '';

	if ( $proodonto_current_unit_slug ) {
		$proodonto_units_map     = function_exists( 'proodonto_get_unit_map_url' ) ? proodonto_get_unit_map_url( $proodonto_current_unit_slug ) : '';
		$proodonto_current_unit  = function_exists( 'proodonto_get_unit_by_slug' ) ? proodonto_get_unit_by_slug( $proodonto_current_unit_slug ) : null;
		$proodonto_units_map_alt = sprintf( 'Mapa com o pin da unidade ProOdonto em %s', $proodonto_current_unit ? $proodonto_current_unit['name'] : get_the_title() );
	} else {
		$proodonto_units_map     = '';
		$proodonto_units_map_alt = 'Mapa com os pins das unidades ProOdonto em Aracaju, Lagarto e Simão Dias';
	}

	// Sem mapa individual (unidade sem slug reconhecido, ou geração
	// falhou), cai no combinado — nunca fica sem mapa nenhum à toa.
	if ( ! $proodonto_units_map ) {
		$proodonto_units_map = function_exists( 'proodonto_get_units_map_url' ) ? proodonto_get_units_map_url() : '';
	}
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
						alt="<?php echo esc_attr( $proodonto_units_map_alt ); ?>"
						loading="lazy"
					/>
				</div>

			</div>

			<?php proodonto_vendas_section_cta( get_field( 'units_cta_label' ), $proodonto_whatsapp_url ); ?>
		</div>
	</section>

	<?php // CTA final — custom fields do grupo "Página de Vendas — CTA final". Reaproveita o mesmo link de WhatsApp montado acima. ?>
	<section class="closing-cta">
		<div class="closing-cta__inner">
			<h2 class="closing-cta__title"><?php echo esc_html( get_field( 'closing_titulo' ) ); ?></h2>
			<p class="closing-cta__text"><?php echo esc_html( get_field( 'closing_texto' ) ); ?></p>
			<a
				href="<?php echo esc_url( $proodonto_whatsapp_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				class="closing-cta__button cta"
			>

				<?php echo esc_html( get_field( 'closing_botao_label' ) ); ?>
			</a>
		</div>
	</section>
</main>

<?php
get_footer();
