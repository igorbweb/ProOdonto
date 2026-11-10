<?php
/**
 * Template Name: Dia Pró — Landing page
 *
 * Landing page do evento Dia Pró (plantão de implantes e próteses da
 * PróOdonto Aracaju). Página autocontida: não usa o cabeçalho nem o rodapé
 * do site — uma ação só (reservar horário pelo WhatsApp/UpView) e nenhuma
 * saída além dela. wp_head()/wp_footer() continuam, para GTM, Yoast e o
 * main.js (repasse de UTM para o UpView).
 *
 * Conteúdo: grupo ACF "Dia Pró — Landing page" (inc/dia-pro.php). Campo
 * nunca salvo = texto padrão aprovado; campo apagado = bloco some.
 * CSS em assets/css/dia-pro.css (mobile-first), JS em assets/js/dia-pro.js.
 *
 * Os botões abrem o UpView NA MESMA ABA (sem target="_blank"): no navegador
 * do Instagram/Facebook, abrir aba nova atravessando o redirect do UpView
 * é o ponto em que a passagem para o app do WhatsApp mais falha.
 */

defined( 'ABSPATH' ) || exit;

$dp_cta_url   = proodonto_dp_cta_url();
$dp_cta_label = (string) proodonto_dp( 'dp_cta_label' );
$dp_cta_short = (string) proodonto_dp( 'dp_cta_label_curto' );
$dp_vagas     = (string) proodonto_dp( 'dp_vagas' );
$dp_vagas_txt = '' !== $dp_vagas ? sprintf( 1 === (int) $dp_vagas ? 'Resta %d horário' : 'Restam %d horários', (int) $dp_vagas ) : '';
$dp_countdown = proodonto_dp_countdown_state();
$dp_dias      = proodonto_dp_rows( 'dp_dias' );
$dp_img       = PROODONTO_URI . '/assets/images/dia-pro/';

/** Botão principal (link do UpView). */
$dp_button = static function ( $label, $where, $url = '', $variant = 'gold', $icon = true ) use ( $dp_cta_url ) {
	printf(
		'<a class="dp-btn dp-btn--%1$s" href="%2$s" data-dp-cta="%3$s">%4$s<span>%5$s</span></a>',
		esc_attr( $variant ),
		esc_url( $url ? proodonto_dp_cta_url( $url ) : $dp_cta_url ),
		esc_attr( $where ),
		$icon ? proodonto_dp_icon( 'whatsapp', 22 ) : '', // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( $label )
	);
};
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="dp-skip" href="#dp-conteudo">Pular para o conteúdo</a>

<div class="dp">

	<?php $dp_faixa = (string) proodonto_dp( 'dp_faixa' ); ?>
	<?php if ( $dp_faixa || $dp_vagas_txt ) : ?>
		<div class="dp-strip">
			<?php if ( $dp_faixa ) : ?><span class="dp-strip__main"><?php echo esc_html( $dp_faixa ); ?></span><?php endif; ?>
			<?php if ( $dp_vagas_txt ) : ?><span class="dp-strip__vagas"><?php echo esc_html( $dp_vagas_txt ); ?></span><?php endif; ?>
		</div>
	<?php endif; ?>

	<main id="dp-conteudo">

	<div class="dp-top">
		<img class="dp-deco dp-deco--tl" src="<?php echo esc_url( $dp_img . 'linhas-ouro-tl.webp' ); ?>" width="460" height="400" alt="" aria-hidden="true" decoding="async">
		<img class="dp-deco dp-deco--br" src="<?php echo esc_url( $dp_img . 'linhas-ouro-br.webp' ); ?>" width="300" height="318" alt="" aria-hidden="true" decoding="async" loading="lazy">

		<header class="dp-header dp-wrap">
			<img class="dp-header__logo" src="<?php echo esc_url( $dp_img . 'logo-proodonto.webp' ); ?>" width="400" height="61" alt="PróOdonto">
			<a class="dp-btn dp-btn--outline dp-btn--sm" href="<?php echo esc_url( $dp_cta_url ); ?>" data-dp-cta="cabecalho"><?php echo esc_html( $dp_cta_short ); ?></a>
		</header>

		<section class="dp-hero dp-wrap" aria-labelledby="dp-titulo">
			<div class="dp-hero__copy">
				<?php $dp_eyebrow = (string) proodonto_dp( 'dp_eyebrow' ); ?>
				<?php if ( $dp_eyebrow ) : ?><p class="dp-eyebrow"><?php echo esc_html( $dp_eyebrow ); ?></p><?php endif; ?>

				<?php
				$dp_first_day = $dp_dias ? $dp_dias[0] : array();
				$dp_cidade    = (string) proodonto_dp( 'dp_cidade' );
				$dp_when      = 1 === count( $dp_dias ) ? (string) $dp_first_day['data'] : implode( ' e ', array_map( static function ( $row ) { return preg_replace( '/\D.*$/u', '', (string) $row['data'] ); }, $dp_dias ) ) . ( $dp_dias ? ' ' . preg_replace( '/^\d+\s*/u', '', (string) end( $dp_dias )['data'] ) : '' );
				?>
				<div class="dp-hero__brand">
				<img class="dp-hero__logo" src="<?php echo esc_url( $dp_img . 'logo-dia-pro-sm.webp' ); ?>" srcset="<?php echo esc_attr( $dp_img . 'logo-dia-pro-sm.webp 640w, ' . $dp_img . 'logo-dia-pro.webp 1200w' ); ?>" sizes="(min-width: 900px) 320px, 190px" width="1200" height="497" alt="Dia Pró" fetchpriority="high" decoding="async">

					<?php if ( $dp_cidade || $dp_dias ) : ?>
						<p class="dp-lockup">
							<?php if ( $dp_cidade ) : ?><span class="dp-lockup__city"><?php echo esc_html( $dp_cidade ); ?></span><?php endif; ?>
							<?php if ( $dp_dias ) : ?><span class="dp-lockup__date"><?php echo esc_html( trim( $dp_when ) ); ?></span><?php endif; ?>
							<?php if ( 1 === count( $dp_dias ) && ! empty( $dp_first_day['dia_semana'] ) ) : ?><span class="dp-lockup__week"><?php echo esc_html( $dp_first_day['dia_semana'] . ( ! empty( $dp_first_day['horario'] ) ? ' · ' . $dp_first_day['horario'] : '' ) ); ?></span><?php endif; ?>
						</p>
					<?php endif; ?>
				</div>

				<h1 id="dp-titulo" class="dp-hero__title"><?php echo proodonto_dp_title( proodonto_dp( 'dp_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>

				<?php $dp_sub = (string) proodonto_dp( 'dp_subtitulo' ); ?>
				<?php if ( $dp_sub ) : ?><p class="dp-hero__sub"><?php echo proodonto_dp_rich( $dp_sub ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endif; ?>

				<div class="dp-hero__cta" data-dp-hero-cta>
					<?php $dp_button( $dp_cta_label, 'topo' ); ?>
				</div>

				<?php $dp_micro = (string) proodonto_dp( 'dp_microcopy' ); ?>
				<?php if ( $dp_micro ) : ?><p class="dp-micro"><?php echo esc_html( $dp_micro ); ?></p><?php endif; ?>
			</div>

			<?php if ( $dp_countdown ) : ?>
				<div class="dp-countdown dp-countdown--<?php echo esc_attr( $dp_countdown['phase'] ); ?>" data-dp-countdown data-start="<?php echo esc_attr( $dp_countdown['start'] ); ?>" data-end="<?php echo esc_attr( $dp_countdown['end'] ); ?>">
					<p class="dp-countdown__label" data-dp-cd-label><?php echo esc_html( $dp_countdown['label'] ); ?></p>
					<div class="dp-countdown__units" role="timer" aria-live="off" aria-atomic="true">
						<?php
						foreach ( array(
							'd' => 'dias',
							'h' => 'horas',
							'm' => 'min',
							's' => 'seg',
						) as $dp_unit => $dp_unit_label ) :
							?>
							<div class="dp-countdown__unit">
								<span class="dp-countdown__num" data-dp-cd="<?php echo esc_attr( $dp_unit ); ?>"><?php echo esc_html( str_pad( (string) $dp_countdown[ $dp_unit ], 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="dp-countdown__txt"><?php echo esc_html( $dp_unit_label ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php $dp_hero_img = proodonto_dp_image( proodonto_dp( 'dp_foto_topo' ), '(min-width: 900px) 46vw, 100vw' ); ?>
			<?php if ( $dp_hero_img ) : ?>
				<div class="dp-hero__media">
					<?php proodonto_dp_img_tag( $dp_hero_img, 'dp-hero__photo', array( 'loading' => 'eager' ) ); ?>
				</div>
			<?php endif; ?>

			<?php $dp_provas = proodonto_dp_rows( 'dp_provas' ); ?>
			<?php if ( $dp_provas ) : ?>
				<ul class="dp-hero__proof" role="list">
					<?php foreach ( $dp_provas as $dp_prova ) : ?>
						<li><strong><?php echo esc_html( $dp_prova['destaque'] ); ?></strong> <?php echo esc_html( $dp_prova['texto'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
	</div><!-- /.dp-top -->

	<?php if ( $dp_dias ) : ?>
		<section class="dp-dates<?php echo 1 === count( $dp_dias ) ? ' dp-dates--single' : ''; ?>" aria-labelledby="dp-datas-titulo">
			<div class="dp-wrap dp-dates__inner">
				<div class="dp-dates__copy">
					<?php if ( proodonto_dp( 'dp_datas_eyebrow' ) ) : ?><p class="dp-eyebrow"><?php echo esc_html( proodonto_dp( 'dp_datas_eyebrow' ) ); ?></p><?php endif; ?>
					<h2 id="dp-datas-titulo" class="dp-dates__title"><?php echo proodonto_dp_title( proodonto_dp( 'dp_datas_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php
					$dp_onde = trim( proodonto_dp( 'dp_local_titulo' ) . ( proodonto_dp( 'dp_endereco_1' ) ? ' · ' . proodonto_dp( 'dp_endereco_1' ) : '' ), ' ·' );
					?>
					<?php if ( $dp_onde ) : ?><p class="dp-dates__where"><?php echo proodonto_dp_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $dp_onde ); ?></span></p><?php endif; ?>
				</div>
				<ul class="dp-dates__list" role="list">
					<?php foreach ( $dp_dias as $dp_dia ) : ?>
						<?php $dp_split = proodonto_dp_split_date( $dp_dia['data'] ); ?>
						<li class="dp-day">
							<?php if ( $dp_split['dia'] ) : ?><span class="dp-day__num" aria-hidden="true"><?php echo esc_html( $dp_split['dia'] ); ?></span><?php endif; ?>
							<span class="dp-day__info">
								<span class="dp-day__month"><span class="dp-sr"><?php echo esc_html( $dp_split['dia'] ); ?> de </span><?php echo esc_html( $dp_split['mes'] ); ?></span>
								<?php if ( ! empty( $dp_dia['dia_semana'] ) ) : ?><span class="dp-day__week"><?php echo esc_html( $dp_dia['dia_semana'] ); ?></span><?php endif; ?>
								<?php if ( ! empty( $dp_dia['horario'] ) ) : ?><span class="dp-day__hours"><?php echo esc_html( $dp_dia['horario'] ); ?></span><?php endif; ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php $dp_antes = proodonto_dp_rows( 'dp_antes_depois' ); ?>
	<?php if ( $dp_antes ) : ?>
		<section class="dp-section dp-section--alt dp-before-after" aria-labelledby="dp-res-titulo">
			<div class="dp-wrap">
				<div class="dp-head">
					<?php if ( proodonto_dp( 'dp_res_eyebrow' ) ) : ?><p class="dp-eyebrow"><?php echo esc_html( proodonto_dp( 'dp_res_eyebrow' ) ); ?></p><?php endif; ?>
					<h2 id="dp-res-titulo" class="dp-h2"><?php echo proodonto_dp_title( proodonto_dp( 'dp_res_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				</div>

				<div class="dp-scroller dp-scroller--ba" tabindex="0" aria-label="Fotos de antes e depois — deslize para o lado">
					<?php foreach ( $dp_antes as $dp_ba ) : ?>
						<?php $dp_ba_img = proodonto_dp_image( $dp_ba['imagem'] ?? null, '(min-width: 900px) 360px, 78vw' ); ?>
						<?php if ( ! $dp_ba_img ) { continue; } ?>
						<figure class="dp-media dp-media--ba">
							<?php proodonto_dp_img_tag( $dp_ba_img, 'dp-media__el' ); ?>
							<?php if ( ! empty( $dp_ba['legenda'] ) ) : ?><figcaption class="dp-media__caption"><?php echo esc_html( $dp_ba['legenda'] ); ?></figcaption><?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>

				<?php if ( proodonto_dp( 'dp_res_aviso' ) ) : ?><p class="dp-note"><?php echo esc_html( proodonto_dp( 'dp_res_aviso' ) ); ?></p><?php endif; ?>

				<?php if ( proodonto_dp( 'dp_res_botao' ) ) : ?>
					<div class="dp-before-after__cta"><?php $dp_button( (string) proodonto_dp( 'dp_res_botao' ), 'antes-depois' ); ?></div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php $dp_casos = proodonto_dp_rows( 'dp_casos' ); ?>
	<?php if ( $dp_casos ) : ?>
		<section class="dp-section" aria-labelledby="dp-casos-titulo">
			<div class="dp-wrap">
				<div class="dp-head">
					<?php if ( proodonto_dp( 'dp_casos_eyebrow' ) ) : ?><p class="dp-eyebrow"><?php echo esc_html( proodonto_dp( 'dp_casos_eyebrow' ) ); ?></p><?php endif; ?>
					<h2 id="dp-casos-titulo" class="dp-h2"><?php echo proodonto_dp_title( proodonto_dp( 'dp_casos_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php if ( proodonto_dp( 'dp_casos_texto' ) ) : ?><p class="dp-lead"><?php echo esc_html( proodonto_dp( 'dp_casos_texto' ) ); ?></p><?php endif; ?>
				</div>

				<?php $dp_condicao = (string) proodonto_dp( 'dp_casos_condicao' ); ?>
				<div class="dp-cases">
					<?php foreach ( $dp_casos as $dp_i => $dp_caso ) : ?>
						<article class="dp-case<?php echo ! empty( $dp_caso['destaque'] ) ? ' dp-case--featured' : ''; ?>">
							<?php proodonto_dp_img_tag( proodonto_dp_image( $dp_caso['imagem'] ?? null, '(min-width: 1100px) 360px, (min-width: 700px) 45vw, 100vw' ), 'dp-case__img' ); ?>
							<div class="dp-case__body">
								<?php if ( ! empty( $dp_caso['tag'] ) ) : ?><span class="dp-tag"><?php echo esc_html( $dp_caso['tag'] ); ?></span><?php endif; ?>
								<h3 class="dp-case__title"><?php echo esc_html( $dp_caso['titulo'] ); ?></h3>
								<?php if ( ! empty( $dp_caso['texto'] ) ) : ?><p class="dp-case__text"><?php echo esc_html( $dp_caso['texto'] ); ?></p><?php endif; ?>
								<?php if ( $dp_condicao ) : ?><p class="dp-case__cond"><?php echo proodonto_dp_icon( 'agenda', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $dp_condicao ); ?></span></p><?php endif; ?>
								<?php $dp_button( $dp_caso['botao'] ? $dp_caso['botao'] : $dp_cta_short, 'caso-' . ( $dp_i + 1 ), (string) ( $dp_caso['link'] ?? '' ), 'gold', false ); ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<?php if ( proodonto_dp( 'dp_ajuda_titulo' ) || proodonto_dp( 'dp_ajuda_texto' ) ) : ?>
					<div class="dp-help">
						<p><strong><?php echo esc_html( proodonto_dp( 'dp_ajuda_titulo' ) ); ?></strong> <?php echo esc_html( proodonto_dp( 'dp_ajuda_texto' ) ); ?></p>
						<?php $dp_button( (string) proodonto_dp( 'dp_ajuda_botao' ), 'nao-sei', '', 'outline', false ); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php $dp_passos = proodonto_dp_rows( 'dp_passos' ); ?>
	<?php if ( $dp_passos ) : ?>
		<section class="dp-section" aria-labelledby="dp-passos-titulo">
			<div class="dp-wrap dp-steps-wrap">
				<div class="dp-steps-wrap__copy">
					<h2 id="dp-passos-titulo" class="dp-h2"><?php echo proodonto_dp_title( proodonto_dp( 'dp_passos_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<ol class="dp-steps">
						<?php foreach ( $dp_passos as $dp_i => $dp_passo ) : ?>
							<li class="dp-step">
								<span class="dp-step__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $dp_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<div>
									<h3 class="dp-step__title"><?php echo esc_html( $dp_passo['titulo'] ); ?></h3>
									<?php if ( ! empty( $dp_passo['texto'] ) ) : ?><p class="dp-step__text"><?php echo esc_html( $dp_passo['texto'] ); ?></p><?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
					<?php $dp_button( $dp_cta_label, 'passos' ); ?>
				</div>
				<?php proodonto_dp_img_tag( proodonto_dp_image( proodonto_dp( 'dp_passos_foto' ), '(min-width: 900px) 40vw, 100vw' ), 'dp-steps-wrap__photo' ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$dp_numeros     = proodonto_dp_rows( 'dp_numeros' );
	$dp_depoimentos = array_filter(
		proodonto_dp_rows( 'dp_depoimentos' ),
		static function ( $row ) {
			return ! empty( $row['texto'] );
		}
	);
	?>
	<?php if ( $dp_numeros || $dp_depoimentos ) : ?>
		<section class="dp-section dp-section--alt" aria-labelledby="dp-prova-titulo">
			<div class="dp-wrap">
				<div class="dp-head">
					<?php if ( proodonto_dp( 'dp_prova_eyebrow' ) ) : ?><p class="dp-eyebrow"><?php echo esc_html( proodonto_dp( 'dp_prova_eyebrow' ) ); ?></p><?php endif; ?>
					<h2 id="dp-prova-titulo" class="dp-h2"><?php echo proodonto_dp_title( proodonto_dp( 'dp_prova_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				</div>

				<?php if ( $dp_numeros ) : ?>
					<ul class="dp-stats" role="list">
						<?php foreach ( $dp_numeros as $dp_num ) : ?>
							<li class="dp-stat"><span class="dp-stat__value"><?php echo esc_html( $dp_num['valor'] ); ?></span><span class="dp-stat__label"><?php echo esc_html( $dp_num['rotulo'] ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $dp_depoimentos ) : ?>
					<div class="dp-scroller dp-scroller--quotes" tabindex="0" aria-label="Depoimentos de pacientes — deslize para o lado">
						<?php foreach ( $dp_depoimentos as $dp_dep ) : ?>
							<figure class="dp-quote">
								<span class="dp-quote__stars" role="img" aria-label="5 estrelas"><?php echo str_repeat( proodonto_dp_icon( 'estrela', 18 ), 5 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<blockquote class="dp-quote__text"><?php echo esc_html( $dp_dep['texto'] ); ?></blockquote>
								<figcaption class="dp-quote__who"><?php echo esc_html( trim( $dp_dep['nome'] . ( ! empty( $dp_dep['fonte'] ) ? ' · ' . $dp_dep['fonte'] : '' ), ' ·' ) ); ?></figcaption>
							</figure>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$dp_res_n   = (int) proodonto_dp( 'dp_reservados' );
	$dp_total_n = (int) proodonto_dp( 'dp_total' );
	$dp_pct     = ( $dp_total_n > 0 && $dp_res_n > 0 ) ? min( 100, (int) round( $dp_res_n * 100 / $dp_total_n ) ) : 0;
	$dp_upd     = (string) proodonto_dp( 'dp_reservas_atualizado' );
	?>
	<section class="dp-section dp-section--tight" aria-labelledby="dp-escassez-titulo">
		<div class="dp-wrap">
			<div class="dp-urgency">
				<div class="dp-urgency__copy">
					<h2 id="dp-escassez-titulo" class="dp-h3"><?php echo proodonto_dp_title( proodonto_dp( 'dp_escassez_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php if ( $dp_pct ) : ?>
						<div class="dp-meter" role="meter" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $dp_total_n ); ?>" aria-valuenow="<?php echo esc_attr( $dp_res_n ); ?>" aria-label="Horários reservados">
							<span class="dp-meter__bar" style="width: <?php echo esc_attr( $dp_pct ); ?>%"></span>
						</div>
						<p class="dp-urgency__text"><?php echo esc_html( sprintf( '%1$d de %2$d horários já reservados', $dp_res_n, $dp_total_n ) . ( $dp_upd ? ' · atualizado ' . $dp_upd : '' ) ); ?></p>
					<?php elseif ( $dp_deadline = proodonto_dp_deadline_state() ) : // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition ?>
						<div class="dp-deadline" data-dp-deadline data-from="<?php echo esc_attr( $dp_deadline['from'] ); ?>" data-to="<?php echo esc_attr( $dp_deadline['to'] ); ?>" data-event="<?php echo esc_attr( $dp_deadline['event'] ); ?>" data-end="<?php echo esc_attr( $dp_deadline['end'] ); ?>">
							<div class="dp-meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (int) round( $dp_deadline['pct'] ) ); ?>" aria-label="Prazo até o Dia Pró">
								<span class="dp-meter__bar" data-dp-deadline-bar style="width: <?php echo esc_attr( $dp_deadline['pct'] ); ?>%"></span>
							</div>
							<?php if ( $dp_deadline['label'] ) : ?><p class="dp-deadline__label" data-dp-deadline-label><?php echo esc_html( $dp_deadline['label'] ); ?></p><?php endif; ?>
						</div>
						<p class="dp-urgency__text"><?php echo esc_html( trim( ( $dp_vagas_txt ? $dp_vagas_txt . '. ' : '' ) . proodonto_dp( 'dp_escassez_texto' ) ) ); ?></p>
					<?php else : ?>
						<p class="dp-urgency__text"><?php echo esc_html( trim( ( $dp_vagas_txt ? $dp_vagas_txt . '. ' : '' ) . proodonto_dp( 'dp_escassez_texto' ) ) ); ?></p>
					<?php endif; ?>
				</div>
				<?php $dp_button( (string) proodonto_dp( 'dp_escassez_botao' ), 'escassez' ); ?>
			</div>
		</div>
	</section>

	<?php $dp_pags = proodonto_dp_rows( 'dp_pagamentos' ); ?>
	<?php if ( $dp_pags ) : ?>
		<section class="dp-section dp-section--alt" aria-labelledby="dp-pag-titulo">
			<div class="dp-wrap dp-split">
				<div class="dp-head dp-split__copy">
					<?php if ( proodonto_dp( 'dp_pag_eyebrow' ) ) : ?><p class="dp-eyebrow"><?php echo esc_html( proodonto_dp( 'dp_pag_eyebrow' ) ); ?></p><?php endif; ?>
					<h2 id="dp-pag-titulo" class="dp-h2"><?php echo proodonto_dp_title( proodonto_dp( 'dp_pag_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<?php if ( proodonto_dp( 'dp_pag_texto' ) ) : ?><p class="dp-lead"><?php echo esc_html( proodonto_dp( 'dp_pag_texto' ) ); ?></p><?php endif; ?>
				</div>
				<ul class="dp-pay" role="list">
					<?php foreach ( $dp_pags as $dp_pag ) : ?>
						<li class="dp-pay__item">
							<?php echo proodonto_dp_icon( $dp_pag['icone'] ? $dp_pag['icone'] : 'cartao', 40 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<strong class="dp-pay__title"><?php echo esc_html( $dp_pag['titulo'] ); ?></strong>
							<?php if ( ! empty( $dp_pag['texto'] ) ) : ?><span class="dp-pay__text"><?php echo esc_html( $dp_pag['texto'] ); ?></span><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$dp_maps_url   = (string) proodonto_dp( 'dp_maps_url' );
	$dp_maps_embed = proodonto_dp_map_embed_url();
	$dp_end_1      = (string) proodonto_dp( 'dp_endereco_1' );
	$dp_end_2      = (string) proodonto_dp( 'dp_endereco_2' );
	$dp_estac      = (string) proodonto_dp( 'dp_estacionamento' );
	$dp_horarios   = array_filter(
		$dp_dias,
		static function ( $row ) {
			return ! empty( $row['horario'] );
		}
	);
	?>
	<section class="dp-section" aria-labelledby="dp-local-titulo">
		<div class="dp-wrap dp-split dp-split--map">
			<div class="dp-local">
				<?php if ( proodonto_dp( 'dp_local_eyebrow' ) ) : ?><p class="dp-eyebrow"><?php echo esc_html( proodonto_dp( 'dp_local_eyebrow' ) ); ?></p><?php endif; ?>
				<h2 id="dp-local-titulo" class="dp-h2"><?php echo esc_html( proodonto_dp( 'dp_local_titulo' ) ); ?></h2>
				<address class="dp-local__address">
					<?php if ( $dp_end_1 ) : ?><span><?php echo esc_html( $dp_end_1 ); ?></span><?php endif; ?>
					<?php if ( $dp_end_2 ) : ?><span><?php echo esc_html( $dp_end_2 ); ?></span><?php endif; ?>
					<?php if ( $dp_estac ) : ?><span><?php echo esc_html( $dp_estac ); ?></span><?php endif; ?>
				</address>
				<?php if ( $dp_horarios ) : ?>
					<dl class="dp-local__hours">
						<?php foreach ( $dp_horarios as $dp_dia ) : ?>
							<div><dt><?php echo esc_html( $dp_dia['data_curta'] ? $dp_dia['data_curta'] : $dp_dia['data'] ); ?></dt><dd><?php echo esc_html( $dp_dia['horario'] ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
				<div class="dp-local__actions">
					<?php $dp_button( $dp_cta_short, 'local', '', 'gold', false ); ?>
					<?php if ( $dp_maps_url ) : ?>
						<a class="dp-btn dp-btn--ghost" href="<?php echo esc_url( $dp_maps_url ); ?>" rel="noopener" data-dp-cta="como-chegar">Como chegar</a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $dp_maps_embed ) : ?>
				<div class="dp-map">
					<iframe
						src="<?php echo esc_url( $dp_maps_embed ); ?>"
						title="<?php echo esc_attr( 'Mapa: ' . proodonto_dp( 'dp_local_titulo' ) ); ?>"
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"
						allowfullscreen
					></iframe>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$dp_faq = array_filter(
		proodonto_dp_rows( 'dp_faq' ),
		static function ( $row ) {
			return ! empty( $row['pergunta'] ) && ! empty( $row['resposta'] );
		}
	);
	?>
	<?php if ( $dp_faq ) : ?>
		<section class="dp-section dp-section--alt" aria-labelledby="dp-faq-titulo">
			<div class="dp-wrap dp-wrap--narrow">
				<h2 id="dp-faq-titulo" class="dp-h2"><?php echo esc_html( proodonto_dp( 'dp_faq_titulo' ) ); ?></h2>
				<div class="dp-faq">
					<?php foreach ( array_values( $dp_faq ) as $dp_i => $dp_item ) : ?>
						<details class="dp-faq__item"<?php echo 0 === $dp_i ? ' open' : ''; ?>>
							<summary class="dp-faq__q"><span><?php echo esc_html( $dp_item['pergunta'] ); ?></span><?php echo proodonto_dp_icon( 'mais', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
							<p class="dp-faq__a"><?php echo esc_html( $dp_item['resposta'] ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="dp-final" aria-labelledby="dp-final-titulo" data-dp-final>
		<img class="dp-deco dp-deco--final" src="<?php echo esc_url( $dp_img . 'linhas-ouro-br.webp' ); ?>" width="300" height="318" alt="" aria-hidden="true" loading="lazy" decoding="async">
		<div class="dp-wrap dp-wrap--narrow dp-final__inner">
			<img class="dp-final__logo" src="<?php echo esc_url( $dp_img . 'logo-dia-pro-sm.webp' ); ?>" width="640" height="265" alt="Dia Pró" loading="lazy" decoding="async">
			<h2 id="dp-final-titulo" class="dp-h2 dp-final__title"><?php echo proodonto_dp_title( proodonto_dp( 'dp_final_titulo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php $dp_final_txt = trim( ( $dp_vagas_txt ? $dp_vagas_txt . '. ' : '' ) . proodonto_dp( 'dp_final_texto' ) ); ?>
			<?php if ( $dp_final_txt ) : ?><p class="dp-lead"><?php echo esc_html( $dp_final_txt ); ?></p><?php endif; ?>
			<?php $dp_button( $dp_cta_label, 'fechamento' ); ?>
			<?php if ( proodonto_dp( 'dp_final_micro' ) ) : ?><p class="dp-micro"><?php echo esc_html( proodonto_dp( 'dp_final_micro' ) ); ?></p><?php endif; ?>
		</div>
	</section>

	</main>

	<footer class="dp-footer">
		<img src="<?php echo esc_url( $dp_img . 'icone-proodonto.webp' ); ?>" width="176" height="156" alt="" aria-hidden="true" loading="lazy" decoding="async" class="dp-footer__icon">
		<?php
		$dp_empresa = trim( proodonto_dp( 'dp_rodape_empresa' ) . ( proodonto_dp( 'dp_cnpj' ) ? ' · CNPJ ' . proodonto_dp( 'dp_cnpj' ) : '' ) );
		?>
		<?php if ( $dp_empresa ) : ?><p><?php echo esc_html( $dp_empresa ); ?></p><?php endif; ?>
		<?php if ( proodonto_dp( 'dp_rt' ) ) : ?><p>Responsável técnico: <?php echo esc_html( proodonto_dp( 'dp_rt' ) ); ?></p><?php endif; ?>
		<?php if ( proodonto_dp( 'dp_aviso' ) ) : ?><p><?php echo esc_html( proodonto_dp( 'dp_aviso' ) ); ?></p><?php endif; ?>
	</footer>

</div><!-- /.dp -->

<div class="dp-bar" data-dp-bar aria-hidden="true">
	<div class="dp-bar__info">
		<?php
		$dp_bar_days = array_filter(
			array_map(
				static function ( $row ) {
					return preg_replace( '/\D.*$/u', '', (string) $row['data'] );
				},
				$dp_dias
			)
		);
		?>
		<?php $dp_bar_when = ( 1 === count( $dp_dias ) && ! empty( $dp_dias[0]['data_curta'] ) ) ? $dp_dias[0]['data_curta'] : ( $dp_bar_days ? implode( ' e ', $dp_bar_days ) . '/11' : '' ); ?>
		<strong>Dia Pró<?php echo $dp_bar_when ? ' · ' . esc_html( $dp_bar_when ) : ''; ?></strong>
		<span><?php echo esc_html( $dp_vagas_txt ? $dp_vagas_txt : 'Vagas limitadas' ); ?></span>
	</div>
	<a class="dp-btn dp-btn--gold dp-btn--bar" href="<?php echo esc_url( $dp_cta_url ); ?>" data-dp-cta="barra-fixa" tabindex="-1"><?php echo proodonto_dp_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $dp_cta_short ); ?></span></a>
</div>

<?php wp_footer(); ?>
</body>
</html>
