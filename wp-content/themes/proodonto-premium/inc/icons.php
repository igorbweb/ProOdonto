<?php
/**
 * Ícones do tema — os mesmos traços do canvas (Proposta 2). SVG inline,
 * `stroke="currentColor"`, sempre aria-hidden (o texto ao lado ou o
 * aria-label do link é que nomeia a ação).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Corpo (paths) de cada ícone, viewBox 0 0 24 24.
 */
function proodonto_icon_paths() {
	return array(
		'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'external'  => '<path d="M7 17L17 7M9 7h8v8"/>',
		'menu'      => '<path d="M4 8h16M4 16h16"/>',
		'close'     => '<path d="M6 6l12 12M18 6L6 18"/>',
		'pin'       => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'whatsapp'  => '<path d="M3.5 20.5l1.4-4.1A8.5 8.5 0 1 1 8.1 19.4z"/>',
		'route'     => '<path d="M3 11l18-8-8 18-2-8z"/>',
		'plus'      => '<path d="M12 5v14M5 12h14"/>',
		'minus'     => '<path d="M5 12h14"/>',
		'chevron'   => '<path d="M6 9l6 6 6-6"/>',
		'alert'     => '<path d="M12 9v4M12 17h.01M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
		'bulb'      => '<path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/>',
		'link'      => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
		'check'     => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'spark'     => '<path d="M12 3v4M12 17v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M3 12h4M17 12h4"/>',
		'adjust'    => '<path d="M4 12h16M7 8l-3 4 3 4M17 8l3 4-3 4"/>',
		'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/>',
		'tooth'     => '<path d="M7 3c-2.2 0-4 1.8-4 4.3 0 2.5 1.2 4 1.7 6.2.5 2.3.8 7.5 2.8 7.5 1.6 0 1.7-3.6 2.5-5.4.4-.9 1.6-.9 2 0 .8 1.8.9 5.4 2.5 5.4 2 0 2.3-5.2 2.8-7.5.5-2.2 1.7-3.7 1.7-6.2C21 4.8 19.2 3 17 3c-1.9 0-2.9 1-5 1S8.9 3 7 3z"/>',
		'implant'   => '<path d="M7 3h10l-1.5 5h-7zM9 11h6M9.5 14h5M10 17h4M11 20h2"/>',
		'ortho'     => '<path d="M5 8h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2zM8 8v8M12 8v8M16 8v8M3 12h18"/>',
		'protocol'  => '<path d="M3 15c0-5 4-9 9-9s9 4 9 9M3 15h18M7 15v3M11 15v3M13 15v3M17 15v3"/>',
		'aesthetic' => '<path d="M11 3l1.8 5.2L18 10l-5.2 1.8L11 17l-1.8-5.2L4 10l5.2-1.8z"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
		'facebook'  => '<path d="M15 3h-2.5A3.5 3.5 0 0 0 9 6.5V10H6v4h3v7h4v-7h3l1-4h-4V7a1 1 0 0 1 1-1h2z"/>',
		'youtube'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10 9.5v5l4.5-2.5z"/>',
		'tiktok'    => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5M14 3c.5 2.8 2.4 4.5 5 4.6"/>',
	);
}

/**
 * Ícones que o editor pode escolher (tratamentos, "para quem é", atalhos).
 */
function proodonto_icon_choices() {
	return array(
		'tooth'     => __( 'Dente (prótese)', 'proodonto' ),
		'implant'   => __( 'Implante', 'proodonto' ),
		'protocol'  => __( 'Arcada (protocolo)', 'proodonto' ),
		'ortho'     => __( 'Aparelho (ortodontia)', 'proodonto' ),
		'aesthetic' => __( 'Brilho (estética)', 'proodonto' ),
		'spark'     => __( 'Raios', 'proodonto' ),
		'adjust'    => __( 'Ajuste', 'proodonto' ),
		'info'      => __( 'Informação', 'proodonto' ),
		'check'     => __( 'Confirmado', 'proodonto' ),
		'clock'     => __( 'Relógio', 'proodonto' ),
		'pin'       => __( 'Local', 'proodonto' ),
	);
}

/**
 * SVG pronto.
 *
 * @param string $name   Chave de proodonto_icon_paths().
 * @param int    $size   Largura/altura em px.
 * @param float  $stroke Espessura do traço (2 em setas/ações, 1.6 em ícones de conteúdo).
 */
function proodonto_icon( $name, $size = 18, $stroke = 2 ) {
	$paths = proodonto_icon_paths();
	if ( empty( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="i i-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%3$s" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		(int) $size,
		esc_attr( (string) $stroke ),
		$paths[ $name ] // Conteúdo estático deste arquivo.
	);
}

/**
 * Arco fino em teal — a "curva do sorriso", assinatura dos blocos escuros.
 *
 * @param string $variant hero|card|band|closing — muda só proporção/posição (CSS).
 */
function proodonto_arc( $variant = 'hero' ) {
	$double = in_array( $variant, array( 'hero', 'band' ), true );
	$svg    = '<svg class="arc arc--' . esc_attr( $variant ) . '" viewBox="0 0 560 300" fill="none" aria-hidden="true" focusable="false" preserveAspectRatio="xMidYMid meet">';
	$svg   .= '<path d="M10 40 C 120 300, 440 300, 550 40" stroke="currentColor" stroke-opacity=".35" stroke-width="1.5" vector-effect="non-scaling-stroke"/>';
	if ( $double ) {
		$svg .= '<path d="M60 60 C 150 260, 410 260, 500 60" stroke="currentColor" stroke-opacity=".18" stroke-width="1.5" vector-effect="non-scaling-stroke"/>';
	}
	$svg .= '</svg>';

	return $svg;
}
