<?php
/**
 * Página de tratamento — /servicos/<tratamento>/ (P2Servico 390 /
 * P2ServicoDesktop 1440).
 *
 * Topo marinho (ou teal, campo "Cor do topo") com selo, H1, lead, 2
 * botões e provas ✓; seletor das 3 unidades sobreposto (WhatsApp com a
 * mensagem do tratamento) · o que é (resposta direta, revisor) · para
 * quem é · etapas em linha do tempo · resultados + depoimento ·
 * perguntas · unidades · veja também.
 *
 * Conteúdo clínico depende da revisão do Dr. Alisson: campo vazio =
 * seção fora do ar. H1 e botões renderizam SEMPRE (CONV-01/02).
 */

defined( 'ABSPATH' ) || exit;

the_post();
$proodonto_id    = get_the_ID();
$proodonto_title = (string) proodonto_field( 'hero_titulo', $proodonto_id );
$proodonto_title = $proodonto_title ? $proodonto_title : get_the_title();
$proodonto_teal  = 'teal' === proodonto_field( 'srv_hero_cor', $proodonto_id );
$proodonto_btn   = (string) proodonto_field( 'srv_botao', $proodonto_id );
$proodonto_btn   = $proodonto_btn ? $proodonto_btn : (string) proodonto_opt( 'header_cta_label' );

proodonto_set_sticky(
	array(
		'label'     => $proodonto_btn,
		'url'       => '#unidades',
		'attrs'     => array( 'data-cta' => 'barra-fixa' ),
		'alt_url'   => '#unidades',
		'alt_label' => __( 'Endereços das unidades', 'proodonto' ),
		'alt_icon'  => 'pin',
	)
);

get_header();
?>

<section class="hero hero--svc<?php echo $proodonto_teal ? ' hero--teal' : ''; ?>">
	<?php echo proodonto_arc( 'hero' ); // phpcs:ignore ?>
	<div class="wrap hero__in hero__split">
		<div class="hero__copy">
			<?php proodonto_breadcrumbs( 'crumbs--dark' ); ?>
			<?php if ( proodonto_field( 'srv_selo', $proodonto_id ) ) : ?>
				<p class="seal glass"><span class="seal__ic" aria-hidden="true"><?php echo proodonto_icon( proodonto_field( 'srv_icone', $proodonto_id ) ? proodonto_field( 'srv_icone', $proodonto_id ) : 'tooth', 20, 1.6 ); // phpcs:ignore ?></span><?php echo esc_html( proodonto_field( 'srv_selo', $proodonto_id ) ); ?></p>
			<?php endif; ?>
			<h1 class="h1 h1--xxl"><?php echo proodonto_rich( $proodonto_title ); // phpcs:ignore ?></h1>
			<?php if ( proodonto_field( 'hero_lead', $proodonto_id ) ) : ?>
				<p class="lead"><?php echo esc_html( proodonto_field( 'hero_lead', $proodonto_id ) ); ?></p>
			<?php endif; ?>
			<div class="hero__btns" data-sticky-trigger>
				<?php
				echo proodonto_button( // phpcs:ignore
					array(
						'label'   => $proodonto_btn,
						'url'     => '#unidades',
						'variant' => 'light',
						'class'   => 'btn--fluid-m',
						'attrs'   => array( 'data-cta' => 'topo-tratamento' ),
					)
				);
				$proodonto_has_steps = (bool) array_filter( wp_list_pluck( proodonto_rows( 'etapas', $proodonto_id ), 'titulo' ) );
				if ( $proodonto_has_steps ) {
					echo proodonto_button( // phpcs:ignore
						array(
							'label'   => (string) proodonto_field( 'srv_botao_etapas', $proodonto_id ),
							'url'     => '#como-funciona',
							'variant' => 'ghost',
							'class'   => 'only-d',
						)
					);
				}
				?>
			</div>
			<?php $proodonto_proofs = array_filter( wp_list_pluck( proodonto_rows( 'srv_provas', $proodonto_id ), 'texto' ) ); ?>
			<?php if ( $proodonto_proofs ) : ?>
				<ul class="proofs">
					<?php foreach ( $proodonto_proofs as $proodonto_p ) : ?>
						<li><?php echo proodonto_icon( 'check', 18 ); // phpcs:ignore ?><?php echo esc_html( $proodonto_p ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="hero__media only-d">
			<?php $proodonto_hf = (int) proodonto_field( 'srv_hero_foto', $proodonto_id ); ?>
			<span class="photo hero__photo hero__photo--tall<?php echo $proodonto_hf ? ' photo--has-img' : ''; ?>"><?php echo proodonto_wide_image( $proodonto_hf, 'proodonto-hero-unit', array( 'class' => 'photo__img', 'alt' => '', 'sizes' => '560px', 'fetchpriority' => 'high' ), '1023px' ); // phpcs:ignore ?></span>
			<?php if ( proodonto_field( 'srv_stat_valor', $proodonto_id ) ) : ?>
				<p class="stat card"><span class="display"><?php echo esc_html( proodonto_field( 'srv_stat_valor', $proodonto_id ) ); ?></span><span><?php echo esc_html( proodonto_field( 'srv_stat_rotulo', $proodonto_id ) ); ?></span></p>
			<?php endif; ?>
		</div>
	</div>
</section>

<div class="wrap hero-selector hero-selector--svc">
	<?php
	get_template_part(
		'template-parts/sections/unit-selector',
		null,
		array(
			'title'      => __( 'Agende na unidade', 'proodonto' ),
			'servico_id' => $proodonto_id,
			'place'      => 'topo-tratamento',
			'class'      => 'selector--bar',
		)
	);
	?>
</div>

<?php
// O que é.
$proodonto_answer = (string) proodonto_field( 'resposta_direta', $proodonto_id );
if ( $proodonto_answer ) :
	$proodonto_rev = function_exists( 'proodonto_get_post_reviewer' ) ? proodonto_get_post_reviewer( $proodonto_id ) : null;
	?>
	<section class="section what" aria-labelledby="h-oque">
		<div class="wrap what__grid">
			<?php echo proodonto_photo( proodonto_field( 'srv_oque_foto', $proodonto_id ), 'proodonto-portrait', 'what__photo', array( 'sizes' => '(min-width: 1024px) 520px, 100vw' ) ); // phpcs:ignore ?>
			<div>
				<h2 class="eyebrow" id="h-oque"><?php esc_html_e( 'O que é', 'proodonto' ); ?></h2>
				<p class="what__answer display"><?php echo esc_html( $proodonto_answer ); ?></p>
				<?php if ( $proodonto_rev && ! empty( $proodonto_rev['name'] ) ) : ?>
					<p class="byline">
						<span class="avatar avatar--sm" aria-hidden="true"><?php echo esc_html( proodonto_initials( $proodonto_rev['name'] ) ); ?></span>
						<span><strong><?php echo esc_html( sprintf( __( 'Revisão clínica: %s', 'proodonto' ), $proodonto_rev['name'] ) ); ?></strong><?php echo esc_html( implode( ' · ', array_filter( array( $proodonto_rev['job_title'], $proodonto_rev['cro'] ) ) ) ); ?></span>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
// Para quem é.
$proodonto_for = array_values(
	array_filter(
		proodonto_rows( 'para_quem_itens', $proodonto_id ),
		static function ( $r ) {
			return ! empty( $r['texto'] );
		}
	)
);
$proodonto_pre = (string) proodonto_field( 'pre_requisitos', $proodonto_id );
if ( $proodonto_for || $proodonto_pre ) :
	$proodonto_def_icons = array( 'spark', 'adjust', 'info' );
	?>
	<section class="section forwho" aria-labelledby="h-pq">
		<div class="wrap">
			<?php proodonto_section_head( proodonto_field( 'srv_para_quem_eyebrow', $proodonto_id ), proodonto_field( 'srv_para_quem_titulo', $proodonto_id ), array( 'id' => 'h-pq', 'size' => 'h2--lg' ) ); ?>
			<?php if ( $proodonto_for ) : ?>
				<ul class="forwho__grid">
					<?php foreach ( $proodonto_for as $proodonto_i => $proodonto_r ) : ?>
						<li class="card forwho__item">
							<span class="svc__ic svc__ic--sm" aria-hidden="true"><?php echo proodonto_icon( ! empty( $proodonto_r['icone'] ) ? $proodonto_r['icone'] : $proodonto_def_icons[ $proodonto_i % 3 ], 24, 1.6 ); // phpcs:ignore ?></span>
							<span><?php echo esc_html( $proodonto_r['texto'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( $proodonto_pre ) : ?>
				<aside class="callout callout--warn">
					<?php echo proodonto_icon( 'alert', 24, 1.8 ); // phpcs:ignore ?>
					<div><p class="callout__t"><?php esc_html_e( 'O que pode vir antes', 'proodonto' ); ?></p><p><?php echo esc_html( $proodonto_pre ); ?></p></div>
				</aside>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php
// Etapas.
$proodonto_steps = array_values(
	array_filter(
		proodonto_rows( 'etapas', $proodonto_id ),
		static function ( $r ) {
			return ! empty( $r['titulo'] );
		}
	)
);
if ( $proodonto_steps ) :
	?>
	<section class="band band--navy timeline-sec" id="como-funciona" aria-labelledby="h-et">
		<?php echo proodonto_arc( 'band' ); // phpcs:ignore ?>
		<div class="wrap">
			<div class="section__top">
				<?php proodonto_section_head( proodonto_field( 'srv_etapas_eyebrow', $proodonto_id ), proodonto_field( 'srv_etapas_titulo', $proodonto_id ), array( 'id' => 'h-et', 'size' => 'h2--lg' ) ); ?>
				<?php
				echo proodonto_button( // phpcs:ignore
					array(
						'label'   => (string) proodonto_field( 'srv_etapas_botao', $proodonto_id ),
						'url'     => '#unidades',
						'variant' => 'light',
						'class'   => 'only-d',
						'attrs'   => array( 'data-cta' => 'etapas' ),
					)
				);
				?>
			</div>
			<ol class="timeline timeline--n<?php echo count( $proodonto_steps ); ?>">
				<?php foreach ( $proodonto_steps as $proodonto_i => $proodonto_s ) : ?>
					<li class="timeline__item">
						<span class="timeline__n" aria-hidden="true"><?php echo (int) $proodonto_i + 1; ?></span>
						<div class="timeline__card glass">
							<p class="timeline__t"><?php echo esc_html( $proodonto_s['titulo'] ); ?></p>
							<?php if ( ! empty( $proodonto_s['descricao'] ) ) : ?>
								<p class="timeline__d"><?php echo esc_html( $proodonto_s['descricao'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $proodonto_s['duracao'] ) ) : ?>
								<span class="timeline__tag"><?php echo esc_html( $proodonto_s['duracao'] ); ?></span>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php
			echo proodonto_button( // phpcs:ignore
				array(
					'label'   => (string) proodonto_field( 'srv_etapas_botao', $proodonto_id ),
					'url'     => '#unidades',
					'variant' => 'light',
					'block'   => true,
					'class'   => 'only-m',
					'attrs'   => array( 'data-cta' => 'etapas' ),
				)
			);
			?>
		</div>
	</section>
<?php endif; ?>

<?php
$proodonto_own_reviews = array_values(
	array_filter(
		proodonto_rows( 'avaliacoes', $proodonto_id ),
		static function ( $r ) {
			return ! empty( $r['texto'] );
		}
	)
);
?>
<section class="section proof proof--side" id="resultados">
	<div class="wrap proof__grid">
		<?php
		get_template_part( 'template-parts/sections/results', null, array( 'wrap' => false, 'caption' => 'overlay', 'button' => false ) );
		get_template_part(
			'template-parts/sections/reviews',
			null,
			array(
				'variant' => 'single',
				'wrap'    => false,
				'head'    => false,
				'items'   => $proodonto_own_reviews,
				'id'      => 'avaliacoes',
			)
		);
		?>
	</div>
</section>

<?php
// Perguntas: repeater; sem repeater, o FAQ do editor (blocos do tema antigo).
$proodonto_faq_items = proodonto_rows( 'srv_faq', $proodonto_id );
$proodonto_editor    = '';
if ( ! array_filter( wp_list_pluck( $proodonto_faq_items, 'pergunta' ) ) && trim( get_the_content() ) ) {
	$proodonto_editor = apply_filters( 'the_content', get_the_content() );
}
get_template_part(
	'template-parts/sections/faq',
	null,
	array(
		'items' => $proodonto_faq_items,
		'html'  => $proodonto_editor,
		'title' => proodonto_field( 'srv_faq_titulo', $proodonto_id ),
		'open'  => true,
	)
);
?>

<section class="band band--navy units-band" id="unidades" aria-labelledby="h-unid">
	<?php echo proodonto_arc( 'band' ); // phpcs:ignore ?>
	<div class="wrap">
		<?php if ( proodonto_field( 'srv_unidades_titulo', $proodonto_id ) ) : ?>
			<h2 class="h2 h2--lg" id="h-unid"><?php echo proodonto_rich( proodonto_field( 'srv_unidades_titulo', $proodonto_id ) ); // phpcs:ignore ?></h2>
		<?php else : ?>
			<h2 class="sr-only" id="h-unid"><?php esc_html_e( 'Unidades', 'proodonto' ); ?></h2>
		<?php endif; ?>
		<?php if ( proodonto_field( 'srv_unidades_texto', $proodonto_id ) ) : ?>
			<p class="units-band__text"><?php echo esc_html( proodonto_field( 'srv_unidades_texto', $proodonto_id ) ); ?></p>
		<?php endif; ?>
		<?php
		get_template_part(
			'template-parts/sections/unit-selector',
			null,
			array(
				'title'      => __( 'Agende na unidade', 'proodonto' ),
				'servico_id' => $proodonto_id,
				'place'      => 'unidades-tratamento',
				'class'      => 'selector--bar',
			)
		);
		?>
	</div>
</section>

<?php
// Veja também: posts relacionados + outros tratamentos.
$proodonto_posts = proodonto_published_ids( proodonto_field( 'posts_relacionados', $proodonto_id ) );
$proodonto_svcs  = proodonto_published_ids( proodonto_field( 'srv_relacionados', $proodonto_id ) );
if ( ! $proodonto_svcs ) {
	$proodonto_all = wp_list_pluck( proodonto_servicos_query()->posts, 'ID' );
	$proodonto_pos = array_search( $proodonto_id, $proodonto_all, true );
	$proodonto_all = false === $proodonto_pos ? $proodonto_all : array_merge( array_slice( $proodonto_all, $proodonto_pos + 1 ), array_slice( $proodonto_all, 0, $proodonto_pos ) );
	$proodonto_svcs = array_values( array_diff( $proodonto_all, array( $proodonto_id ) ) );
}
$proodonto_more = array_slice( array_merge( array_map( static fn( $i ) => array( 'post', $i ), $proodonto_posts ), array_map( static fn( $i ) => array( 'servico', $i ), $proodonto_svcs ) ), 0, 3 );
if ( $proodonto_more ) :
	?>
	<section class="section more" aria-labelledby="h-more">
		<div class="wrap">
			<div class="section__top">
				<h2 class="h2" id="h-more"><?php echo proodonto_rich( __( 'Veja *também*', 'proodonto' ) ); // phpcs:ignore ?></h2>
				<?php
				echo proodonto_button( // phpcs:ignore
					array(
						'label'   => __( 'Todos os tratamentos', 'proodonto' ),
						'url'     => proodonto_hub_url(),
						'variant' => 'outline',
						'class'   => 'only-d',
					)
				);
				?>
			</div>
			<div class="grid-3">
				<?php
				foreach ( $proodonto_more as $proodonto_m ) {
					if ( 'post' === $proodonto_m[0] ) {
						get_template_part( 'template-parts/cards/post', null, array( 'id' => $proodonto_m[1], 'meta' => sprintf( __( 'Blog · %d min', 'proodonto' ), proodonto_reading_time( $proodonto_m[1] ) ) ) );
					} else {
						get_template_part( 'template-parts/cards/servico', null, array( 'id' => $proodonto_m[1], 'variant' => 'photo' ) );
					}
				}
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
