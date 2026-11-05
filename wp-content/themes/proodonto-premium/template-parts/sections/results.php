<?php
/**
 * Resultados (antes e depois) — Opções → Resultados. Cada foto abre o
 * visualizador (<dialog>); sem JS, o link abre a imagem grande.
 *
 * @var array $args {
 *   @type string $unit       Slug: casos desta unidade primeiro.
 *   @type int    $max        Máximo no desktop (3 ou 4).
 *   @type int    $mobile_max Máximo no celular (2).
 *   @type string $caption    below|overlay.
 *   @type bool   $head       Imprime eyebrow + título.
 *   @type string $head_tag   h2|h3.
 *   @type bool   $button     Botão "Ver mais resultados".
 *   @type bool   $wrap       Envolve em <section> com container.
 *   @type string $id         id da seção.
 * }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_a = wp_parse_args(
	$args ?? array(),
	array(
		'unit'       => '',
		'max'        => 3,
		'mobile_max' => 2,
		'caption'    => 'below',
		'head'       => true,
		'head_tag'   => 'h2',
		'button'     => true,
		'wrap'       => true,
		'id'         => '',
	)
);

$proodonto_rows = array_slice( proodonto_results_for( $proodonto_a['unit'] ), 0, (int) $proodonto_a['max'] );
if ( ! $proodonto_rows ) {
	return;
}
$proodonto_hid  = 'res-' . wp_unique_id();
$proodonto_note = (string) proodonto_opt( 'resultados_nota' );
?>
<?php if ( $proodonto_a['wrap'] ) : ?>
<section class="section results"<?php echo $proodonto_a['id'] ? ' id="' . esc_attr( $proodonto_a['id'] ) . '"' : ''; ?> aria-labelledby="<?php echo esc_attr( $proodonto_hid ); ?>"><div class="wrap">
<?php else : ?>
<div class="results"<?php echo $proodonto_a['id'] ? ' id="' . esc_attr( $proodonto_a['id'] ) . '"' : ''; ?>>
<?php endif; ?>

	<?php
	if ( $proodonto_a['head'] ) {
		proodonto_section_head(
			proodonto_opt( 'resultados_eyebrow' ),
			proodonto_opt( 'resultados_titulo' ),
			array(
				'id'   => $proodonto_hid,
				'tag'  => $proodonto_a['head_tag'],
				'size' => 'h3' === $proodonto_a['head_tag'] ? 'h2--sm' : '',
			)
		);
	}
	?>
	<ul class="results__grid results__grid--<?php echo (int) $proodonto_a['max']; ?>" data-mobile-max="<?php echo (int) $proodonto_a['mobile_max']; ?>">
		<?php foreach ( $proodonto_rows as $proodonto_i => $proodonto_r ) : ?>
			<?php
			$proodonto_full = wp_get_attachment_image_url( (int) $proodonto_r['imagem'], 'large' );
			$proodonto_cap  = implode( ' · ', array_filter( array( $proodonto_r['tratamento'] ?? '', proodonto_unit_name( $proodonto_r['unidade'] ?? '' ) ) ) );
			$proodonto_alt  = ! empty( $proodonto_r['alt'] ) ? $proodonto_r['alt'] : __( 'Paciente sorrindo, com fotos dos dentes antes e depois do tratamento', 'proodonto' );
			?>
			<li class="results__item<?php echo $proodonto_i >= (int) $proodonto_a['mobile_max'] ? ' only-d' : ''; ?>">
				<a class="results__link card tap" href="<?php echo esc_url( $proodonto_full ); ?>" data-lightbox data-caption="<?php echo esc_attr( $proodonto_cap ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Ampliar resultado %d', 'proodonto' ), $proodonto_i + 1 ) ); ?>">
					<?php echo proodonto_image( (int) $proodonto_r['imagem'], 'proodonto-portrait', array( 'class' => 'results__img', 'alt' => $proodonto_alt, 'sizes' => '(min-width: 1024px) 320px, 45vw' ) ); // phpcs:ignore ?>
					<?php if ( 'overlay' === $proodonto_a['caption'] && $proodonto_cap ) : ?>
						<span class="results__tag"><?php echo esc_html( $proodonto_cap ); ?></span>
					<?php endif; ?>
				</a>
				<?php if ( 'below' === $proodonto_a['caption'] && $proodonto_cap ) : ?>
					<p class="results__cap">
						<?php if ( ! empty( $proodonto_r['tratamento'] ) ) : ?>
							<strong><?php echo esc_html( $proodonto_r['tratamento'] ); ?></strong><?php echo proodonto_unit_name( $proodonto_r['unidade'] ?? '' ) ? ' · ' . esc_html( proodonto_unit_name( $proodonto_r['unidade'] ) ) : ''; ?>
						<?php else : ?>
							<?php echo esc_html( $proodonto_cap ); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if ( $proodonto_note ) : ?>
		<p class="legal-note"><?php echo esc_html( $proodonto_note ); ?></p>
	<?php endif; ?>
	<?php
	if ( $proodonto_a['button'] ) {
		echo proodonto_button( // phpcs:ignore
			array(
				'label'   => (string) proodonto_opt( 'resultados_link_label' ),
				'url'     => (string) proodonto_opt( 'resultados_link' ),
				'variant' => 'outline',
				'class'   => 'btn--fluid-m',
			)
		);
	}
	?>

<?php if ( $proodonto_a['wrap'] ) : ?>
</div></section>
<?php else : ?>
</div>
<?php endif; ?>
