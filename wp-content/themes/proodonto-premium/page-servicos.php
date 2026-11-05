<?php
/**
 * Template Name: Hub de tratamentos
 *
 * Proposta 2 — hub /servicos/ (P2Hub 390 / P2HubDesktop 1440). Abre com
 * uma pergunta ("O que você quer resolver?") em vez de uma lista; os
 * atalhos levam a grupos numerados 01/02/03 de cards com foto.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_id     = get_queried_object_id();
$proodonto_groups = array_values(
	array_filter(
		proodonto_rows( 'hub_grupos', $proodonto_id ),
		static function ( $g ) {
			return ! empty( $g['titulo'] ) || ! empty( $g['atalho'] );
		}
	)
);
$proodonto_title  = (string) proodonto_field( 'hub_titulo', $proodonto_id );
$proodonto_title  = $proodonto_title ? $proodonto_title : get_the_title();

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

/** Cartão de atalhos para os grupos. */
$proodonto_shortcuts = static function ( $class ) use ( $proodonto_groups, $proodonto_id ) {
	if ( ! $proodonto_groups ) {
		return;
	}
	$title = (string) proodonto_field( 'hub_seletor_titulo', $proodonto_id );
	?>
	<div class="shortcuts card <?php echo esc_attr( $class ); ?>" data-sticky-trigger>
		<?php if ( $title ) : ?>
			<h2 class="shortcuts__t"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<ul class="shortcuts__list">
			<?php foreach ( $proodonto_groups as $i => $g ) : ?>
				<?php if ( empty( $g['atalho'] ) ) { continue; } ?>
				<li>
					<a class="shortcuts__a tap" href="#grupo-<?php echo (int) $i + 1; ?>">
						<span class="shortcuts__ic" aria-hidden="true"><?php echo proodonto_icon( ! empty( $g['icone'] ) ? $g['icone'] : 'tooth', 28, 1.6 ); // phpcs:ignore ?></span>
						<span><strong><?php echo esc_html( $g['atalho'] ); ?></strong><?php if ( ! empty( $g['atalho_sub'] ) ) : ?><small><?php echo esc_html( $g['atalho_sub'] ); ?></small><?php endif; ?></span>
						<?php echo proodonto_chip(); // phpcs:ignore ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
};

get_header();
?>

<section class="hero hero--hub">
	<?php echo proodonto_arc( 'hero' ); // phpcs:ignore ?>
	<div class="wrap hero__in hero__split">
		<div class="hero__copy">
			<?php proodonto_breadcrumbs( 'crumbs--dark' ); ?>
			<?php if ( proodonto_field( 'hub_eyebrow', $proodonto_id ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( proodonto_field( 'hub_eyebrow', $proodonto_id ) ); ?></p>
			<?php endif; ?>
			<h1 class="h1 h1--xl"><?php echo proodonto_rich( $proodonto_title ); // phpcs:ignore ?></h1>
			<?php if ( proodonto_field( 'hub_lead', $proodonto_id ) ) : ?>
				<p class="lead"><?php echo esc_html( proodonto_field( 'hub_lead', $proodonto_id ) ); ?></p>
			<?php endif; ?>
			<?php echo proodonto_rating_pill( 'rating-pill--dark' ); // phpcs:ignore ?>
		</div>
		<?php $proodonto_shortcuts( 'only-d' ); ?>
	</div>
</section>
<div class="wrap hero-selector only-m">
	<?php $proodonto_shortcuts( 'shortcuts--overlap' ); ?>
</div>

<?php get_template_part( 'template-parts/sections/numbers', null, array( 'class' => 'numbers--lg' ) ); ?>

<?php if ( $proodonto_groups ) : ?>
	<div class="wrap groups">
		<?php foreach ( $proodonto_groups as $proodonto_i => $proodonto_g ) : ?>
			<?php
			$proodonto_feat  = (int) ( $proodonto_g['destaque'] ?? 0 );
			$proodonto_feat  = ( $proodonto_feat && 'publish' === get_post_status( $proodonto_feat ) ) ? $proodonto_feat : 0;
			$proodonto_items = array_values( array_diff( proodonto_published_ids( $proodonto_g['servicos'] ?? array() ), array( $proodonto_feat ) ) );
			if ( ! $proodonto_feat && ! $proodonto_items ) {
				continue;
			}
			$proodonto_wide = $proodonto_feat || count( $proodonto_items ) > 1;
			$proodonto_gid  = 'grupo-' . ( $proodonto_i + 1 );
			$proodonto_sel  = $proodonto_feat ? (string) proodonto_field( 'srv_selo', $proodonto_feat ) : '';
			$proodonto_sel  = $proodonto_sel ? trim( explode( '·', $proodonto_sel )[0] ) : '';
			?>
			<section class="group<?php echo $proodonto_wide ? ' group--wide' : ''; ?>" id="<?php echo esc_attr( $proodonto_gid ); ?>" aria-labelledby="<?php echo esc_attr( $proodonto_gid ); ?>-t">
				<div class="group__head">
					<div class="group__title">
						<span class="group__n" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $proodonto_i + 1 ) ); ?></span>
						<div>
							<h2 class="h2" id="<?php echo esc_attr( $proodonto_gid ); ?>-t"><?php echo proodonto_rich( ! empty( $proodonto_g['titulo'] ) ? $proodonto_g['titulo'] : $proodonto_g['atalho'] ); // phpcs:ignore ?></h2>
							<?php if ( ! empty( $proodonto_g['subtitulo'] ) ) : ?>
								<p class="group__sub"><?php echo esc_html( $proodonto_g['subtitulo'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<?php
					echo proodonto_button( // phpcs:ignore
						array(
							'label'   => (string) ( $proodonto_g['botao'] ?? '' ),
							'url'     => '#unidades',
							'variant' => 'outline',
							'class'   => 'only-d',
							'attrs'   => array( 'data-cta' => 'hub-grupo' ),
						)
					);
					?>
				</div>
				<div class="group__grid<?php echo $proodonto_feat ? ' group__grid--feat' : ''; ?> group__grid--n<?php echo count( $proodonto_items ); ?>">
					<?php
					if ( $proodonto_feat ) {
						get_template_part(
							'template-parts/cards/servico',
							null,
							array(
								'id'      => $proodonto_feat,
								'variant' => 'feature-photo',
								'badge'   => $proodonto_sel,
							)
						);
					}
					foreach ( $proodonto_items as $proodonto_sid ) {
						get_template_part( 'template-parts/cards/servico', null, array( 'id' => $proodonto_sid, 'variant' => 'photo' ) );
					}
					?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<?php // Sem grupos cadastrados: todos os tratamentos publicados. ?>
	<div class="wrap groups">
		<section class="group group--wide">
			<div class="group__grid group__grid--n3">
				<?php foreach ( proodonto_servicos_query()->posts as $proodonto_s ) : ?>
					<?php get_template_part( 'template-parts/cards/servico', null, array( 'id' => $proodonto_s->ID, 'variant' => 'photo' ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
<?php endif; ?>

<?php
// Texto próprio do hub (editor) — "como escolher o tratamento".
$proodonto_content = trim( (string) get_post_field( 'post_content', $proodonto_id ) );
if ( $proodonto_content ) :
	?>
	<section class="section">
		<div class="wrap wrap--text prose">
			<?php echo apply_filters( 'the_content', $proodonto_content ); // phpcs:ignore ?>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part(
	'template-parts/sections/steps',
	null,
	array(
		'eyebrow' => proodonto_field( 'hub_passos_eyebrow', $proodonto_id ),
		'title'   => proodonto_field( 'hub_passos_titulo', $proodonto_id ),
		'id'      => 'avaliacao',
	)
);
?>

<section class="section proof proof--side" id="resultados">
	<div class="wrap proof__grid">
		<?php
		get_template_part(
			'template-parts/sections/results',
			null,
			array(
				'wrap'    => false,
				'caption' => 'overlay',
				'button'  => false,
			)
		);
		get_template_part(
			'template-parts/sections/reviews',
			null,
			array(
				'variant' => 'single',
				'wrap'    => false,
				'head'    => false,
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
		'eyebrow'   => proodonto_opt( 'unidades_eyebrow' ),
		'title'     => proodonto_opt( 'unidades_titulo' ),
		'page_link' => true,
	)
);

get_template_part(
	'template-parts/sections/faq',
	null,
	array(
		'items' => proodonto_rows( 'hub_faq', $proodonto_id ),
		'title' => proodonto_field( 'hub_faq_titulo', $proodonto_id ),
		'open'  => true,
	)
);

get_template_part( 'template-parts/sections/closing' );

get_footer();
