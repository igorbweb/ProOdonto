<?php
/**
 * Template Name: Unidade (vendas)
 *
 * Proposta 2 — página de unidade (P2Vendas 390 / P2VendasDesktop 1440).
 * UM template para /aracaju/, /lagarto/ e /simao-dias/: a unidade vem do
 * campo "Unidade desta página"; o conteúdo exclusivo de cada cidade (texto
 * da clínica, fotos, equipe, perguntas) impede páginas-clone.
 *
 * Teste A/B do topo: A (dor + 1 botão) · B (pergunta + 2 caminhos) ·
 * AB (50/50 por cookie, ver inc/enqueue.php).
 */

defined( 'ABSPATH' ) || exit;

$proodonto_id   = get_queried_object_id();
$proodonto_unit = proodonto_get_current_unit();

if ( ! $proodonto_unit ) {
	// Sem unidade definida: a página continua no ar como página comum.
	get_template_part( 'page' );
	return;
}

$proodonto_versao = (string) proodonto_field( 'uni_versao', $proodonto_id );
$proodonto_versao = in_array( $proodonto_versao, array( 'a', 'b', 'ab' ), true ) ? $proodonto_versao : 'a';
$proodonto_wa     = $proodonto_unit['whatsapp_url'];
$proodonto_city   = $proodonto_unit['name'];
$proodonto_foto   = (int) proodonto_field( 'uni_hero_foto', $proodonto_id );
$proodonto_hours  = proodonto_unit_hours_text( $proodonto_unit );

/** Atributos de CTA com a variante do teste (data-ab). */
$proodonto_cta = static function ( $place, $variant = '' ) use ( $proodonto_unit ) {
	$attrs = proodonto_cta_attrs( $proodonto_unit, $place );
	if ( $variant ) {
		$attrs['data-ab'] = $variant;
	}
	return $attrs;
};

proodonto_set_sticky(
	array(
		'label'     => sprintf( __( 'WhatsApp %s', 'proodonto' ), $proodonto_city ),
		'url'       => $proodonto_wa,
		'icon'      => 'whatsapp',
		'attrs'     => $proodonto_cta( 'barra-fixa' ),
		'alt_url'   => $proodonto_unit['maps_url'],
		'alt_label' => __( 'Rota até a unidade', 'proodonto' ),
		'alt_icon'  => 'route',
		'alt_attrs' => array(
			'rel'       => 'noopener',
			'data-rota' => $proodonto_unit['slug'],
		),
	)
);

/** Cartão "onde fica · horário · nota". */
$proodonto_info = static function ( $class ) use ( $proodonto_unit, $proodonto_hours ) {
	?>
	<div class="unit-info card <?php echo esc_attr( $class ); ?>">
		<?php if ( $proodonto_unit['maps_url'] ) : ?>
			<a class="unit-info__addr tap" href="<?php echo esc_url( $proodonto_unit['maps_url'] ); ?>" rel="noopener" data-rota="<?php echo esc_attr( $proodonto_unit['slug'] ); ?>">
				<?php echo proodonto_icon( 'pin', 24, 1.6 ); // phpcs:ignore ?>
				<span>
					<span class="unit-info__street only-m"><?php echo esc_html( proodonto_unit_address_line( $proodonto_unit, false ) ); ?></span>
					<span class="unit-info__street only-d"><?php echo esc_html( proodonto_unit_address_full( $proodonto_unit ) ); ?></span>
					<span class="unit-info__route"><?php esc_html_e( 'Como chegar', 'proodonto' ); ?></span>
				</span>
				<?php echo proodonto_chip(); // phpcs:ignore ?>
			</a>
		<?php endif; ?>
		<div class="unit-info__row">
			<?php if ( $proodonto_hours ) : ?>
				<div class="unit-info__cell">
					<p class="unit-info__k"><?php esc_html_e( 'Horário', 'proodonto' ); ?></p>
					<p class="unit-info__v"><?php echo esc_html( $proodonto_hours ); ?></p>
				</div>
			<?php endif; ?>
			<?php if ( $proodonto_unit['nota'] ) : ?>
				<a class="unit-info__cell tap" href="#avaliacoes">
					<p class="unit-info__k"><?php esc_html_e( 'Google', 'proodonto' ); ?></p>
					<p class="unit-info__rate">
						<span class="display"><?php echo esc_html( $proodonto_unit['nota'] ); ?></span>
						<span><span class="stars" aria-hidden="true">★★★★★</span><?php if ( $proodonto_unit['total'] ) : ?><br><?php echo esc_html( sprintf( __( '%s avaliações', 'proodonto' ), $proodonto_unit['total'] ) ); ?><?php endif; ?></span>
					</p>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
};

get_header();
?>

<div class="unit-top<?php echo 'ab' === $proodonto_versao ? ' unit-top--ab' : ''; ?>" id="onde">
	<section class="hero hero--unit" data-sticky-trigger>
		<?php echo proodonto_arc( 'hero' ); // phpcs:ignore ?>
		<div class="wrap hero__in hero__split">
			<div class="hero__copy">
				<h1 class="hero__seo"><span class="dot" aria-hidden="true"></span><?php echo esc_html( proodonto_tokens( (string) proodonto_field( 'uni_hero_h1', $proodonto_id ), $proodonto_unit ) ); ?></h1>

				<?php if ( 'b' !== $proodonto_versao && proodonto_field( 'uni_hero_titulo_a', $proodonto_id ) ) : ?>
					<p class="h1 hero__pain ab-a"><?php echo proodonto_rich( proodonto_field( 'uni_hero_titulo_a', $proodonto_id ), $proodonto_unit ); // phpcs:ignore ?></p>
				<?php endif; ?>
				<?php if ( 'a' !== $proodonto_versao && proodonto_field( 'uni_hero_titulo_b', $proodonto_id ) ) : ?>
					<p class="h1 hero__pain ab-b"><?php echo proodonto_rich( proodonto_field( 'uni_hero_titulo_b', $proodonto_id ), $proodonto_unit ); // phpcs:ignore ?></p>
				<?php endif; ?>

				<?php
				$proodonto_lead = trim( proodonto_field( 'uni_hero_lead', $proodonto_id ) . ' ' . proodonto_field( 'uni_hero_extra', $proodonto_id ) );
				if ( $proodonto_lead ) :
					?>
					<p class="lead"><?php echo esc_html( proodonto_tokens( $proodonto_lead, $proodonto_unit ) ); ?></p>
				<?php endif; ?>

				<?php
				if ( 'b' !== $proodonto_versao ) {
					echo proodonto_button( // phpcs:ignore
						array(
							'label' => (string) proodonto_field( 'uni_hero_botao_a', $proodonto_id ),
							'url'   => $proodonto_wa,
							'icon'  => 'whatsapp',
							'class' => 'btn--lg btn--fluid-m ab-a',
							'attrs' => $proodonto_cta( 'topo-unidade', 'ab' === $proodonto_versao ? 'a' : '' ),
						)
					);
				}
				if ( 'a' !== $proodonto_versao ) :
					$proodonto_paths = array_values(
						array_filter(
							proodonto_rows( 'uni_hero_caminhos', $proodonto_id ),
							static function ( $r ) {
								return ! empty( $r['titulo'] );
							}
						)
					);
					if ( $proodonto_paths ) :
						?>
						<ul class="paths ab-b">
							<?php foreach ( $proodonto_paths as $proodonto_i => $proodonto_p ) : ?>
								<?php $proodonto_link = ! empty( $proodonto_p['link'] ) && ! proodonto_is_legacy_cta_url( $proodonto_p['link'] ) ? $proodonto_p['link'] : $proodonto_wa; ?>
								<li>
									<a class="paths__a tap" href="<?php echo esc_url( $proodonto_link ); ?>"<?php echo proodonto_attrs( $proodonto_cta( 'topo-caminho-' . ( $proodonto_i + 1 ), 'ab' === $proodonto_versao ? 'b' : '' ) ); // phpcs:ignore ?>>
										<span><strong><?php echo esc_html( $proodonto_p['titulo'] ); ?></strong><?php if ( ! empty( $proodonto_p['subtitulo'] ) ) : ?><small><?php echo esc_html( $proodonto_p['subtitulo'] ); ?></small><?php endif; ?></span>
										<?php echo proodonto_chip(); // phpcs:ignore ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php
					endif;
				endif;
				?>
			</div>

			<div class="hero__media only-d">
				<span class="photo hero__photo<?php echo $proodonto_foto ? ' photo--has-img' : ''; ?>"><?php echo proodonto_wide_image( $proodonto_foto, 'proodonto-hero-unit', array( 'class' => 'photo__img', 'alt' => sprintf( __( 'Unidade %s', 'proodonto' ), $proodonto_city ), 'sizes' => '560px', 'fetchpriority' => 'high' ), '1023px' ); // phpcs:ignore ?></span>
				<?php $proodonto_info( 'unit-info--float' ); ?>
			</div>
		</div>
	</section>
	<div class="wrap hero-selector only-m">
		<?php $proodonto_info( 'unit-info--overlap' ); ?>
	</div>
</div>

<?php
get_template_part(
	'template-parts/sections/reviews',
	null,
	array(
		'variant' => 'unit',
		'unit'    => $proodonto_unit,
	)
);

$proodonto_trats = proodonto_published_ids( proodonto_field( 'uni_trat_lista', $proodonto_id ) );
if ( ! $proodonto_trats ) {
	$proodonto_trats = wp_list_pluck( proodonto_servicos_query()->posts, 'ID' );
}
if ( $proodonto_trats ) :
	?>
	<section class="section treatments treatments--unit" id="tratamentos" aria-labelledby="h-trat">
		<div class="wrap">
			<?php
			proodonto_section_head(
				proodonto_field( 'uni_trat_eyebrow', $proodonto_id ),
				proodonto_field( 'uni_trat_titulo', $proodonto_id ),
				array(
					'id'   => 'h-trat',
					'unit' => $proodonto_unit,
				)
			);
			?>
			<ul class="svc-list card">
				<?php foreach ( array_slice( $proodonto_trats, 0, 5 ) as $proodonto_sid ) : ?>
					<li><?php get_template_part( 'template-parts/cards/servico', null, array( 'id' => $proodonto_sid, 'variant' => 'row' ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part(
	'template-parts/sections/steps',
	null,
	array(
		'button' => (string) proodonto_opt( 'passos_botao_unidade' ),
		'url'    => $proodonto_wa,
		'attrs'  => $proodonto_cta( 'passos' ),
		'unit'   => $proodonto_unit,
	)
);
?>

<div class="clinic-block">
	<?php
	$proodonto_fotos  = array_slice( (array) proodonto_field( 'uni_clinica_fotos', $proodonto_id ), 0, 3 );
	$proodonto_team   = array_values(
		array_filter(
			proodonto_rows( 'uni_equipe', $proodonto_id ),
			static function ( $r ) {
				return ! empty( $r['nome'] );
			}
		)
	);
	$proodonto_ctitle = proodonto_field( 'uni_clinica_titulo', $proodonto_id );
	$proodonto_ctext  = proodonto_field( 'uni_clinica_texto', $proodonto_id );
	if ( $proodonto_ctitle || $proodonto_ctext || $proodonto_team || $proodonto_fotos ) :
		?>
		<section class="section clinic" id="clinica" aria-labelledby="h-clin">
			<div class="wrap clinic__grid">
				<div class="clinic__copy">
					<?php
					proodonto_section_head(
						proodonto_field( 'uni_clinica_eyebrow', $proodonto_id ),
						$proodonto_ctitle,
						array(
							'id'   => 'h-clin',
							'unit' => $proodonto_unit,
						)
					);
					?>
					<?php if ( $proodonto_ctext ) : ?>
						<div class="clinic__text"><?php echo wpautop( wp_kses_post( proodonto_tokens( $proodonto_ctext, $proodonto_unit ) ) ); // phpcs:ignore ?></div>
					<?php endif; ?>
					<?php if ( $proodonto_team ) : ?>
						<div class="team card">
							<?php if ( proodonto_field( 'uni_equipe_titulo', $proodonto_id ) ) : ?>
								<p class="team__t"><?php echo esc_html( proodonto_tokens( proodonto_field( 'uni_equipe_titulo', $proodonto_id ), $proodonto_unit ) ); ?></p>
							<?php endif; ?>
							<ul class="team__list">
								<?php foreach ( $proodonto_team as $proodonto_m ) : ?>
									<li>
										<?php if ( ! empty( $proodonto_m['foto'] ) ) : ?>
											<?php echo proodonto_image( (int) $proodonto_m['foto'], 'proodonto-thumb', array( 'class' => 'avatar', 'alt' => '' ) ); // phpcs:ignore ?>
										<?php else : ?>
											<span class="avatar" aria-hidden="true"><?php echo esc_html( proodonto_initials( $proodonto_m['nome'] ) ); ?></span>
										<?php endif; ?>
										<span>
											<strong><?php echo esc_html( $proodonto_m['nome'] ); ?></strong>
											<small><?php echo esc_html( implode( ' · ', array_filter( array( $proodonto_m['especialidade'] ?? '', $proodonto_m['cro'] ?? '' ) ) ) ); ?></small>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $proodonto_fotos ) : ?>
					<div class="mosaic mosaic--<?php echo count( $proodonto_fotos ); ?>">
						<?php foreach ( $proodonto_fotos as $proodonto_i => $proodonto_f ) : ?>
							<?php echo proodonto_photo( $proodonto_f, 0 === $proodonto_i ? 'proodonto-portrait' : 'proodonto-card', 'mosaic__item', array( 'sizes' => '(min-width: 1024px) 340px, 50vw' ) ); // phpcs:ignore ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/sections/results',
		null,
		array(
			'unit'       => $proodonto_unit['slug'],
			'max'        => 4,
			'mobile_max' => 2,
			'head_tag'   => 'h3',
			'button'     => false,
			'id'         => 'resultados',
		)
	);
	?>
</div>

<?php
$proodonto_others = proodonto_get_other_units( $proodonto_unit['slug'] );
$proodonto_faq    = array_values(
	array_filter(
		proodonto_rows( 'uni_faq', $proodonto_id ),
		static function ( $r ) {
			return ! empty( $r['pergunta'] ) && ! empty( $r['resposta'] );
		}
	)
);
if ( $proodonto_faq || $proodonto_others ) :
	?>
	<section class="section faq-outras" id="outras">
		<div class="wrap faq-outras__grid">
			<?php if ( $proodonto_faq ) : ?>
				<div id="perguntas">
					<h2 class="h2 h2--md"><?php echo proodonto_rich( (string) proodonto_field( 'uni_faq_titulo', $proodonto_id ), $proodonto_unit ); // phpcs:ignore ?></h2>
					<div class="card faq">
						<?php foreach ( $proodonto_faq as $proodonto_r ) : ?>
							<?php echo proodonto_faq_item_html( esc_html( proodonto_tokens( $proodonto_r['pergunta'], $proodonto_unit ) ), '<p>' . esc_html( proodonto_tokens( $proodonto_r['resposta'], $proodonto_unit ) ) . '</p>' ); // phpcs:ignore ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
			<?php if ( $proodonto_others ) : ?>
				<div>
					<h2 class="h2 h2--md"><?php echo proodonto_rich( (string) proodonto_field( 'uni_outras_titulo', $proodonto_id ), $proodonto_unit ); // phpcs:ignore ?></h2>
					<ul class="others">
						<?php foreach ( $proodonto_others as $proodonto_o ) : ?>
							<?php $proodonto_ourl = proodonto_unit_page_url( $proodonto_o ); ?>
							<?php if ( ! $proodonto_ourl ) { continue; } ?>
							<li>
								<a class="other card tap" href="<?php echo esc_url( $proodonto_ourl ); ?>">
									<?php echo proodonto_photo( $proodonto_o['foto'], 'proodonto-thumb', 'other__photo', array( 'alt' => '' ) ); // phpcs:ignore ?>
									<span class="other__txt">
										<span class="display other__city"><?php echo esc_html( $proodonto_o['name'] ); ?></span>
										<span class="other__sub"><?php echo esc_html( implode( ' · ', array_filter( array( $proodonto_o['bairro'], $proodonto_o['nota'] ? '★ ' . $proodonto_o['nota'] : '', $proodonto_o['total'] ? sprintf( __( '%s avaliações', 'proodonto' ), $proodonto_o['total'] ) : '' ) ) ) ); ?></span>
									</span>
									<?php echo proodonto_chip(); // phpcs:ignore ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part(
	'template-parts/sections/closing',
	null,
	array(
		'title'  => proodonto_field( 'uni_fechamento_titulo', $proodonto_id ),
		'text'   => proodonto_field( 'uni_fechamento_texto', $proodonto_id ),
		'button' => proodonto_field( 'uni_fechamento_botao', $proodonto_id ),
		'url'    => $proodonto_wa,
		'attrs'  => $proodonto_cta( 'fechamento' ),
		'unit'   => $proodonto_unit,
	)
);

get_footer();
