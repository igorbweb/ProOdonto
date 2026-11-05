<?php
/**
 * Barra fixa do celular (canvas: "aparece quando o seletor sai da tela").
 * Botão principal + atalho redondo (endereços/rota). Some no desktop.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_s = $GLOBALS['proodonto_sticky'] ?? null;
if ( ! $proodonto_s || empty( $proodonto_s['label'] ) || empty( $proodonto_s['url'] ) ) {
	return;
}
?>
<div class="sticky-bar" data-sticky-bar hidden>
	<?php
	echo proodonto_button( // phpcs:ignore
		array(
			'label' => $proodonto_s['label'],
			'url'   => $proodonto_s['url'],
			'icon'  => $proodonto_s['icon'] ?? 'arrow',
			'small' => true,
			'attrs' => $proodonto_s['attrs'] ?? array(),
		)
	);
	?>
	<?php if ( ! empty( $proodonto_s['alt_url'] ) ) : ?>
		<a class="sticky-bar__alt" href="<?php echo esc_url( $proodonto_s['alt_url'] ); ?>" aria-label="<?php echo esc_attr( $proodonto_s['alt_label'] ?? '' ); ?>"<?php echo ! empty( $proodonto_s['alt_attrs'] ) ? proodonto_attrs( $proodonto_s['alt_attrs'] ) : ''; // phpcs:ignore ?>><?php echo proodonto_icon( $proodonto_s['alt_icon'] ?? 'pin', 20 ); // phpcs:ignore ?></a>
	<?php endif; ?>
</div>
