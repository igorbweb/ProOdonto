<?php
/**
 * Avaliações reais do Google (Opções → Avaliações).
 *
 * Variantes:
 *   home   — 1 depoimento + cartão com a nota de cada unidade + link.
 *   unit   — até 3 depoimentos da unidade (grade no desktop, carrossel
 *            com scroll-snap no celular) + link.
 *   single — 1 depoimento grande (hub e tratamento).
 *
 * @var array $args {
 *   @type string $variant  home|unit|single.
 *   @type array  $unit     Unidade (variant unit).
 *   @type bool   $head     Imprime eyebrow + título.
 *   @type array  $items    Avaliações próprias (tratamento) — senão, as globais.
 *   @type string $link     Link "Ler todas" (padrão: Opções).
 *   @type bool   $wrap     Envolve em <section>.
 *   @type string $id       id.
 * }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_a = wp_parse_args(
	$args ?? array(),
	array(
		'variant' => 'home',
		'unit'    => null,
		'head'    => true,
		'items'   => null,
		'link'    => '',
		'wrap'    => true,
		'id'      => 'avaliacoes',
	)
);

$proodonto_unit  = $proodonto_a['unit'];
$proodonto_items = is_array( $proodonto_a['items'] ) && $proodonto_a['items'] ? $proodonto_a['items'] : proodonto_reviews_for( $proodonto_unit ? $proodonto_unit['slug'] : '' );
$proodonto_units = proodonto_get_units();
$proodonto_link  = $proodonto_a['link'] ? $proodonto_a['link'] : ( ( $proodonto_unit && $proodonto_unit['google_url'] ) ? $proodonto_unit['google_url'] : (string) proodonto_opt( 'google_reviews_url' ) );
$proodonto_llbl  = (string) proodonto_opt( 'avaliacoes_link_label' );

$proodonto_has_notes = 'home' === $proodonto_a['variant'] && array_filter( wp_list_pluck( $proodonto_units, 'nota' ) );
if ( ! $proodonto_items && ! $proodonto_has_notes ) {
	return;
}

$proodonto_hid = 'aval-' . wp_unique_id();

/** Um cartão de depoimento. */
$proodonto_quote = static function ( $r, $big = false ) {
	$unit_name = proodonto_unit_name( $r['unidade'] ?? '' );
	$unit_name = $unit_name ? $unit_name : ( $r['unidade'] ?? '' ); // Tratamento guarda a unidade em texto.
	?>
	<figure class="quote card<?php echo $big ? ' quote--big' : ''; ?>">
		<p class="quote__mark" aria-hidden="true">“</p>
		<blockquote><p><?php echo esc_html( $r['texto'] ); ?></p></blockquote>
		<figcaption class="quote__by">
			<?php if ( ! empty( $r['nome'] ) ) : ?>
				<span class="avatar avatar--sm" aria-hidden="true"><?php echo esc_html( proodonto_initials( $r['nome'] ) ); ?></span>
			<?php endif; ?>
			<span>
				<?php if ( ! empty( $r['nome'] ) ) : ?>
					<strong><?php echo esc_html( $r['nome'] ); ?></strong><br>
				<?php endif; ?>
				<?php echo $unit_name ? esc_html( sprintf( __( 'Unidade %s', 'proodonto' ), $unit_name ) ) . ' · ' : ''; ?><span class="stars" aria-label="<?php esc_attr_e( '5 estrelas', 'proodonto' ); ?>">★★★★★</span>
			</span>
		</figcaption>
	</figure>
	<?php
};
?>
<?php if ( $proodonto_a['wrap'] ) : ?>
<section class="section reviews reviews--<?php echo esc_attr( $proodonto_a['variant'] ); ?>"<?php echo $proodonto_a['id'] ? ' id="' . esc_attr( $proodonto_a['id'] ) . '"' : ''; ?> aria-labelledby="<?php echo esc_attr( $proodonto_hid ); ?>"><div class="wrap">
<?php else : ?>
<div class="reviews reviews--<?php echo esc_attr( $proodonto_a['variant'] ); ?>"<?php echo $proodonto_a['id'] ? ' id="' . esc_attr( $proodonto_a['id'] ) . '"' : ''; ?>>
<?php endif; ?>

	<?php if ( $proodonto_a['head'] ) : ?>
		<div class="reviews__head">
			<?php
			proodonto_section_head(
				proodonto_opt( 'avaliacoes_eyebrow' ),
				proodonto_opt( 'avaliacoes_titulo' ),
				array(
					'id'   => $proodonto_hid,
					'unit' => $proodonto_unit,
				)
			);
			if ( 'unit' === $proodonto_a['variant'] ) {
				echo proodonto_button( // phpcs:ignore
					array(
						'label'   => $proodonto_llbl,
						'url'     => $proodonto_link,
						'variant' => 'outline',
						'icon'    => 'external',
						'class'   => 'only-d',
						'attrs'   => array( 'rel' => 'noopener' ),
					)
				);
			}
			?>
		</div>
	<?php endif; ?>

	<?php if ( 'unit' === $proodonto_a['variant'] ) : ?>
		<div class="reviews__track" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Avaliações — role para o lado', 'proodonto' ); ?>">
			<?php foreach ( array_slice( $proodonto_items, 0, 3 ) as $proodonto_r ) { $proodonto_quote( $proodonto_r ); } ?>
		</div>
	<?php elseif ( 'single' === $proodonto_a['variant'] ) : ?>
		<?php $proodonto_quote( $proodonto_items[0], true ); ?>
	<?php else : ?>
		<div class="reviews__stack">
			<?php if ( $proodonto_items ) { $proodonto_quote( $proodonto_items[ wp_rand( 0, count( $proodonto_items ) - 1 ) ] ); } ?>
			<?php if ( $proodonto_has_notes ) : ?>
				<ul class="notes card">
					<?php foreach ( $proodonto_units as $proodonto_u ) : ?>
						<?php if ( ! $proodonto_u['nota'] ) { continue; } ?>
						<li>
							<p class="notes__n display"><?php echo esc_html( $proodonto_u['nota'] ); ?></p>
							<p class="notes__c"><?php echo esc_html( $proodonto_u['name'] ); ?></p>
							<?php if ( $proodonto_u['total'] ) : ?>
								<p class="notes__t"><?php echo esc_html( sprintf( __( '%s avaliações', 'proodonto' ), $proodonto_u['total'] ) ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( 'single' !== $proodonto_a['variant'] && $proodonto_llbl && $proodonto_link ) : ?>
		<a class="link<?php echo 'unit' === $proodonto_a['variant'] ? ' only-m' : ''; ?>" href="<?php echo esc_url( $proodonto_link ); ?>" rel="noopener"><?php echo esc_html( $proodonto_llbl ); ?><?php echo proodonto_icon( 'external', 16 ); // phpcs:ignore ?></a>
	<?php endif; ?>

<?php if ( $proodonto_a['wrap'] ) : ?>
</div></section>
<?php else : ?>
</div>
<?php endif; ?>
