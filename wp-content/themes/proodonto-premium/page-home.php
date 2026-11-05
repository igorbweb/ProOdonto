<?php
/**
 * Template Name: Home
 *
 * Proposta 2 — Home (P2Home 390 / P2HomeDesktop 1440, hero opção A).
 * Ordem: topo + seletor de unidade · números · tratamentos · primeira
 * avaliação · resultados + avaliações · unidades · sobre + perguntas ·
 * fechamento.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_id   = get_queried_object_id();
$proodonto_img  = (int) proodonto_field( 'home_hero_imagem', $proodonto_id );
$proodonto_hub  = proodonto_hub_url();

proodonto_set_sticky(
	array(
		'label'     => (string) proodonto_opt( 'header_cta_label' ),
		'url'       => '#unidades',
		'attrs'     => array( 'data-cta' => 'barra-fixa' ),
		'alt_url'   => '#unidades',
		'alt_label' => __( 'Endereços das unidades', 'proodonto' ),
		'alt_icon'  => 'pin',
	)
);

get_header();
?>

<section class="hero hero--home<?php echo $proodonto_img ? ' hero--has-img' : ''; ?>" id="inicio">
	<?php if ( $proodonto_img ) : ?>
		<div class="hero__bg" aria-hidden="true">
			<?php echo proodonto_wide_image( $proodonto_img, 'proodonto-hero', array( 'class' => 'hero__img', 'alt' => '', 'sizes' => '100vw', 'fetchpriority' => 'high' ) ); // phpcs:ignore ?>
		</div>
	<?php endif; ?>
	<?php echo proodonto_arc( 'hero' ); // phpcs:ignore ?>
	<div class="wrap hero__in">
		<?php if ( proodonto_field( 'home_hero_eyebrow', $proodonto_id ) ) : ?>
			<p class="eyebrow"><?php echo esc_html( proodonto_field( 'home_hero_eyebrow', $proodonto_id ) ); ?></p>
		<?php endif; ?>
		<h1 class="h1"><?php echo proodonto_rich( proodonto_field( 'home_hero_titulo', $proodonto_id ) ? proodonto_field( 'home_hero_titulo', $proodonto_id ) : get_the_title() ); // phpcs:ignore ?></h1>
		<?php if ( proodonto_field( 'home_hero_lead', $proodonto_id ) ) : ?>
			<p class="lead"><?php echo esc_html( proodonto_field( 'home_hero_lead', $proodonto_id ) ); ?></p>
		<?php endif; ?>
		<?php echo proodonto_rating_pill( 'rating-pill--dark' ); // phpcs:ignore ?>
	</div>
</section>

<div class="wrap hero-selector">
	<?php
	get_template_part(
		'template-parts/sections/unit-selector',
		null,
		array(
			'title'   => (string) proodonto_field( 'home_seletor_titulo', $proodonto_id ),
			'place'   => 'topo-home',
			'trigger' => true,
			'class'   => 'selector--bar',
		)
	);
	?>
</div>

<?php get_template_part( 'template-parts/sections/numbers' ); ?>

<?php
$proodonto_destaque = (int) proodonto_field( 'home_trat_destaque', $proodonto_id );
$proodonto_lista    = proodonto_published_ids( proodonto_field( 'home_trat_lista', $proodonto_id ) );
if ( $proodonto_destaque || $proodonto_lista ) :
	$proodonto_badge = $proodonto_destaque ? (string) proodonto_field( 'srv_selo', $proodonto_destaque ) : '';
	$proodonto_badge = $proodonto_badge ? trim( explode( '·', $proodonto_badge )[0] ) : '';
	?>
	<section class="section treatments" id="tratamentos" aria-labelledby="h-trat">
		<div class="wrap">
			<div class="section__top">
				<?php proodonto_section_head( proodonto_field( 'home_trat_eyebrow', $proodonto_id ), proodonto_field( 'home_trat_titulo', $proodonto_id ), array( 'id' => 'h-trat' ) ); ?>
				<?php
				echo proodonto_button( // phpcs:ignore
					array(
						'label'   => (string) proodonto_field( 'home_trat_botao', $proodonto_id ),
						'url'     => $proodonto_hub,
						'variant' => 'outline',
						'class'   => 'only-d',
					)
				);
				?>
			</div>
			<div class="treatments__grid">
				<?php
				if ( $proodonto_destaque ) {
					get_template_part(
						'template-parts/cards/servico',
						null,
						array(
							'id'      => $proodonto_destaque,
							'variant' => 'feature',
							'badge'   => $proodonto_badge,
							'class'   => 'treatments__feature',
						)
					);
				}
				foreach ( $proodonto_lista as $proodonto_sid ) {
					get_template_part( 'template-parts/cards/servico', null, array( 'id' => $proodonto_sid ) );
				}
				?>
			</div>
			<?php
			echo proodonto_button( // phpcs:ignore
				array(
					'label'   => (string) proodonto_field( 'home_trat_botao', $proodonto_id ),
					'url'     => $proodonto_hub,
					'variant' => 'outline',
					'block'   => true,
					'class'   => 'only-m section__btn',
				)
			);
			?>
		</div>
	</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/sections/steps' ); ?>

<section class="section proof" id="resultados">
	<div class="wrap proof__grid">
		<?php
		get_template_part(
			'template-parts/sections/results',
			null,
			array(
				'wrap'       => false,
				'max'        => 3,
				'mobile_max' => 2,
				'class'      => 'proof__results',
			)
		);
		get_template_part(
			'template-parts/sections/reviews',
			null,
			array(
				'variant' => 'home',
				'wrap'    => false,
				'id'      => 'avaliacoes',
			)
		);
		?>
	</div>
</section>

<?php
get_template_part(
	'template-parts/sections/units',
	null,
	array(
		'eyebrow' => proodonto_field( 'home_unid_eyebrow', $proodonto_id ) ? proodonto_field( 'home_unid_eyebrow', $proodonto_id ) : proodonto_opt( 'unidades_eyebrow' ),
		'title'   => proodonto_field( 'home_unid_titulo', $proodonto_id ) ? proodonto_field( 'home_unid_titulo', $proodonto_id ) : proodonto_opt( 'unidades_titulo' ),
	)
);

// Sobre (coluna do título no desktop) + perguntas.
$proodonto_sobre_t = (string) proodonto_field( 'home_sobre_titulo', $proodonto_id );
$proodonto_sobre   = '';
if ( $proodonto_sobre_t ) {
	$proodonto_link  = proodonto_field( 'home_sobre_link', $proodonto_id );
	$proodonto_sobre = '<div class="about only-d" id="sobre"><h2 class="h2 h2--md">' . proodonto_rich( $proodonto_sobre_t ) . '</h2>'
		. wpautop( wp_kses_post( (string) proodonto_field( 'home_sobre_texto', $proodonto_id ) ) )
		. ( is_array( $proodonto_link ) && ! empty( $proodonto_link['url'] ) ? proodonto_button(
			array(
				'label'   => $proodonto_link['title'],
				'url'     => $proodonto_link['url'],
				'variant' => 'outline',
			)
		) : '' )
		. '</div>';
}
get_template_part(
	'template-parts/sections/faq',
	null,
	array(
		'items' => proodonto_rows( 'home_faq', $proodonto_id ),
		'title' => proodonto_field( 'home_faq_titulo', $proodonto_id ),
		'aside' => $proodonto_sobre,
		'class' => 'faq-sec--home',
	)
);

get_template_part( 'template-parts/sections/closing', null, array( 'title' => proodonto_field( 'home_fechamento_titulo', $proodonto_id ) ) );

get_footer();
