<?php
/**
 * Números da rede (Opções → Números). Celular: 3 colunas centradas;
 * desktop: até 4, com filete entre eles.
 *
 * @var array $args { @type string $class }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_rows = array_values(
	array_filter(
		proodonto_rows( 'numeros', 'option' ),
		static function ( $r ) {
			return ! empty( $r['valor'] );
		}
	)
);
if ( ! $proodonto_rows ) {
	return;
}
$proodonto_class = $args['class'] ?? '';
?>
<section class="numbers <?php echo esc_attr( $proodonto_class ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'A %s em números', 'proodonto' ), get_bloginfo( 'name' ) ) ); ?>">
	<dl class="wrap numbers__list">
		<?php foreach ( $proodonto_rows as $proodonto_r ) : ?>
			<div class="numbers__item<?php echo ! empty( $proodonto_r['so_desktop'] ) ? ' only-d' : ''; ?>">
				<dt>
					<span class="only-d"><?php echo esc_html( $proodonto_r['rotulo'] ); ?></span>
					<span class="only-m"><?php echo esc_html( $proodonto_r['rotulo_curto'] ? $proodonto_r['rotulo_curto'] : $proodonto_r['rotulo'] ); ?></span>
				</dt>
				<dd class="display"><?php echo esc_html( $proodonto_r['valor'] ); ?></dd>
			</div>
		<?php endforeach; ?>
	</dl>
</section>
