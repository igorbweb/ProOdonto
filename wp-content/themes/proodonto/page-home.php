<?php
/**
 * Template Name: Home
 *
 * Template da página inicial. Todo o conteúdo editável (banner, Sobre,
 * Resultados, Tratamentos, Passo a passo, Avaliações, Unidades, Blog e CTA
 * final) vem de custom fields ACF — grupos "Home — *" registrados em
 * inc/acf-fields.php. Na primeira execução do tema em cada ambiente, esse
 * conteúdo já é gravado com a copy/ícones/imagens originais do projeto
 * (ver inc/content-seed.php e inc/page-content-defaults.php) — não é
 * preciso preencher nada manualmente para o visual continuar idêntico.
 *
 * A lista de unidades (endereços, mapa) continua em inc/units-map.php,
 * de propósito: é a mesma fonte usada para gerar o mapa estático e os
 * cards — ver comentário lá.
 *
 * O CSS fica em assets/css/pages/home.css, carregado só nesta página.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_placeholder_img = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1600' height='900'%3E%3Crect width='100%25' height='100%25' fill='%23e2e4e8'/%3E%3C/svg%3E";

// Reaproveitado em várias seções (Antes e Depois, Unidades, CTA final) —
// centralizado aqui pra não duplicar a mesma URL em cada uma. Número vem
// das Opções do Tema (ver proodonto_get_whatsapp() em inc/options-page.php).
$proodonto_whatsapp     = function_exists( 'proodonto_get_whatsapp' ) ? proodonto_get_whatsapp() : '';
$proodonto_whatsapp     = $proodonto_whatsapp ? $proodonto_whatsapp : '5511300000000';
$proodonto_whatsapp_url = 'https://wa.me/' . $proodonto_whatsapp . '?text=' . rawurlencode( 'Olá! Gostaria de agendar uma avaliação na PRÓ-ODONTO.' );

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
                                        <div class="max-w-2xl">
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
	// (grupo "Home — Letreiro de diferenciais"). Ícones em SVG inline
	// (sem dependência de fonte de ícones externa), sanitizados na saída
	// porque agora vêm de um campo editável, não mais de um array PHP fixo.
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

	<?php
	// Seção "Sobre" — custom fields do grupo "Home — Sobre" (galeria,
	// imagem, eyebrow, titulo, texto, estatisticas). Com 1+ fotos na
	// galeria, exibe um carrossel (inicializado em assets/js/pages/home.js,
	// mesmo componente já usado pela galeria "Sobre" da Página de Vendas);
	// sem galeria, cai na imagem única; sem nenhuma das duas, cai no
	// mesmo placeholder usado no resto do tema.
	$proodonto_about_gallery = get_field( 'galeria_sobre' );
	$proodonto_about_image   = get_field( 'imagem' );
	$proodonto_about_stats   = array();
	if ( have_rows( 'estatisticas' ) ) :
		while ( have_rows( 'estatisticas' ) ) : the_row();
			$proodonto_about_stats[] = array(
				'valor'   => get_sub_field( 'valor' ),
				'legenda' => get_sub_field( 'legenda' ),
			);
		endwhile;
	endif;
	?>
	<section class="about">
		<div class="about__grid">

			<div class="about__media">
				<?php if ( $proodonto_about_gallery ) : ?>
					<div class="about-swiper swiper">
						<div class="swiper-wrapper">
							<?php foreach ( $proodonto_about_gallery as $proodonto_about_photo ) : ?>
								<div class="swiper-slide">
									<img
										src="<?php echo esc_url( $proodonto_about_photo['url'] ); ?>"
										alt="<?php echo esc_attr( $proodonto_about_photo['alt'] ?: 'Foto — dentista atendendo com cuidado' ); ?>"
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
						src="<?php echo $proodonto_about_image ? esc_url( $proodonto_about_image['url'] ) : esc_attr( $proodonto_placeholder_img ); ?>"
						alt="<?php echo esc_attr( $proodonto_about_image['alt'] ?? 'Foto — dentista atendendo com cuidado' ); ?>"
						loading="lazy"
					/>
				<?php endif; ?>
			</div>

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
	</section>

	<?php
	// Seção "Antes e Depois" — custom fields do grupo "Home — Resultados"
	// (cabeçalho + repeater "results_itens": nome + foto). Sem "tratamento"
	// no card de propósito: não é conhecido qual procedimento cada paciente
	// fez de verdade, e inventar isso seria afirmar algo factual sobre uma
	// pessoa real e identificável — diferente de um placeholder genérico.
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
											alt="<?php echo esc_attr( sprintf( '%s, paciente PRÓ-ODONTO — antes e depois do tratamento', $proodonto_result['nome'] ) ); ?>"
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
	// Seção "Tratamentos" — custom fields do grupo "Home — Tratamentos"
	// (cabeçalho + repeater "treatments_itens"). Ícones em SVG inline. Card
	// sem link (não há página de tratamento pra apontar ainda) — só ícone,
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
	<section class="treatments" id="tratamentos">
		<div class="treatments__inner">

			<div class="treatments__header">
				<p class="treatments__eyebrow"><?php echo esc_html( get_field( 'treatments_eyebrow' ) ); ?></p>
				<h2 class="treatments__title"><?php echo esc_html( get_field( 'treatments_titulo' ) ); ?></h2>
				<p class="treatments__text"><?php echo esc_html( get_field( 'treatments_texto' ) ); ?></p>
			</div>

			<div class="treatments__grid">
				<?php foreach ( $proodonto_treatments as $proodonto_treatment ) : ?>
					<div class="treatment-card">
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
	</section>

	<?php
	// Seção "Shorts" (YouTube) — custom fields do grupo "Home — Shorts
	// (YouTube)". Miniatura e player são derivados automaticamente do
	// link do YouTube (proodonto_get_youtube_id() em
	// inc/template-functions.php) — não é preciso subir nenhuma imagem,
	// a menos que se queira substituir a capa automática. Linhas sem link
	// válido são ignoradas; sem nenhum vídeo válido, a seção inteira (e o
	// modal) não é exibida.
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
											alt="<?php echo esc_attr( $proodonto_short['capa']['alt'] ?? ( $proodonto_short['titulo'] ?: 'Vídeo PRÓ-ODONTO no YouTube' ) ); ?>"
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
				<div class="results__cta">
                    <a
                        href="<?php echo esc_url( $proodonto_whatsapp_url ); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="cta"
                    >
                        AGENDAR AVALIAÇÃO
                    </a>
                </div>
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
	// Seção "Passo a passo" — custom fields do grupo "Home — Passo a passo"
	// (cabeçalho + repeater "steps_itens"). Ícones em SVG inline.
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
			<div class="results__cta">
                    <a
                        href="<?php echo esc_url( $proodonto_whatsapp_url ); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="cta"
                    >
                        QUERO O MEU SORRISO
                    </a>
                </div>
		</div>
	</section>

	<?php
	// Seção "Unidades" — a lista de unidades em si (endereços, mapa) vem de
	// inc/units-map.php, de propósito: é a mesma fonte usada para gerar o
	// mapa estático (cache em disco) e os cards. Só o cabeçalho vem do
	// custom field (grupo "Home — Unidades (cabeçalho)").
	$proodonto_units       = proodonto_get_units();
	$proodonto_units_map   = function_exists( 'proodonto_get_units_map_url' ) ? proodonto_get_units_map_url() : '';
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
	// Seção "Blog" — carrossel com os ÚLTIMOS POSTS REAIS do blog (post
	// type nativo "post"), não mais uma vitrine de cards fixos. Sem
	// nenhum post publicado ainda, a seção inteira não é exibida — em
	// vez de mostrar cards fictícios sem link real (mesmo princípio já
	// usado na seção "Shorts").
	$proodonto_blog_qtd = (int) get_field( 'blog_quantidade' );
	$proodonto_blog_qtd = $proodonto_blog_qtd > 0 ? $proodonto_blog_qtd : 6;

	$proodonto_blog_posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $proodonto_blog_qtd,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	// "Ver todos os artigos" sem URL própria cadastrada: usa a página de
	// posts do site (Configurações → Leitura) — mesma lógica que o
	// próprio WordPress usa para achar o índice do blog.
	$proodonto_blog_link_url = get_field( 'blog_link_url' );
	if ( ! $proodonto_blog_link_url ) {
		$proodonto_blog_link_url = ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) )
			? get_permalink( get_option( 'page_for_posts' ) )
			: home_url( '/' );
	}
	?>
	<?php if ( $proodonto_blog_posts ) : ?>
		<section class="blog" id="blog">
			<div class="blog__inner">

				<div class="blog__header">
					<div class="blog__heading">
						<p class="blog__eyebrow"><?php echo esc_html( get_field( 'blog_eyebrow' ) ); ?></p>
						<h2 class="blog__title"><?php echo esc_html( get_field( 'blog_titulo' ) ); ?></h2>
					</div>
					<a href="<?php echo esc_url( $proodonto_blog_link_url ); ?>" class="blog__link"><?php echo esc_html( get_field( 'blog_link_label' ) ); ?></a>
				</div>

				<div class="blog-carousel">
					<div class="blog-swiper swiper">
						<div class="swiper-wrapper">
							<?php foreach ( $proodonto_blog_posts as $proodonto_post ) : ?>
								<?php
								$proodonto_post_categories = get_the_category( $proodonto_post->ID );
								$proodonto_post_category   = $proodonto_post_categories ? $proodonto_post_categories[0]->name : __( 'Blog', 'proodonto' );
								$proodonto_post_thumb       = get_the_post_thumbnail( $proodonto_post, 'proodonto-card', array( 'loading' => 'lazy', 'alt' => get_the_title( $proodonto_post ) ) );
								?>
								<div class="swiper-slide">
									<a href="<?php echo esc_url( get_permalink( $proodonto_post ) ); ?>" class="blog-card">
										<div class="blog-card__media">
											<?php if ( $proodonto_post_thumb ) : ?>
												<?php echo $proodonto_post_thumb; ?>
											<?php else : ?>
												<img
													src="<?php echo esc_attr( $proodonto_placeholder_img ); ?>"
													alt="<?php echo esc_attr( get_the_title( $proodonto_post ) ); ?>"
													loading="lazy"
												/>
											<?php endif; ?>
										</div>
										<div class="blog-card__body">
											<p class="blog-card__category"><?php echo esc_html( $proodonto_post_category ); ?></p>
											<h3 class="blog-card__title"><?php echo esc_html( get_the_title( $proodonto_post ) ); ?></h3>
											<p class="blog-card__meta"><?php echo esc_html( sprintf( __( '%d min de leitura', 'proodonto' ), proodonto_reading_time( $proodonto_post->ID ) ) ); ?></p>
										</div>
									</a>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="swiper-pagination"></div>
					</div>

					<button type="button" class="blog-swiper-prev" aria-label="<?php esc_attr_e( 'Ver post anterior', 'proodonto' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
					<button type="button" class="blog-swiper-next" aria-label="<?php esc_attr_e( 'Ver próximo post', 'proodonto' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
					</button>
				</div>

			</div>
		</section>
	<?php endif; ?>

	<?php // CTA final — custom fields do grupo "Home — CTA final". Reaproveita o mesmo link de WhatsApp montado no topo do arquivo. ?>
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