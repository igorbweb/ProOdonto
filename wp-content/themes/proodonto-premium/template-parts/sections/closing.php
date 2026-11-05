<?php
/**
 * Fechamento teal com arco branco (Opções → Fechamento; cada página pode
 * trocar título, texto e botão).
 *
 * @var array $args { title, text, button, url, attrs, unit }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_a = wp_parse_args(
	$args ?? array(),
	array(
		'title'  => '',
		'text'   => '',
		'button' => '',
		'url'    => proodonto_units_anchor(),
		'attrs'  => array( 'data-cta' => 'fechamento' ),
		'unit'   => null,
	)
);

$proodonto_title  = $proodonto_a['title'] ? $proodonto_a['title'] : proodonto_opt( 'fechamento_titulo' );
$proodonto_text   = $proodonto_a['text'] ? $proodonto_a['text'] : proodonto_opt( 'fechamento_texto' );
$proodonto_button = $proodonto_a['button'] ? $proodonto_a['button'] : proodonto_opt( 'fechamento_botao' );

if ( ! $proodonto_title ) {
	return;
}
?>
<section class="closing-sec">
	<div class="closing">
		<?php echo proodonto_arc( 'closing' ); // phpcs:ignore ?>
		<div class="closing__txt">
			<h2 class="h2 closing__t"><?php echo proodonto_rich( $proodonto_title, $proodonto_a['unit'] ); // phpcs:ignore ?></h2>
			<?php if ( $proodonto_text ) : ?>
				<p class="closing__d"><?php echo esc_html( proodonto_tokens( $proodonto_text, $proodonto_a['unit'] ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		echo proodonto_button( // phpcs:ignore
			array(
				'label'   => proodonto_tokens( $proodonto_button, $proodonto_a['unit'] ),
				'url'     => $proodonto_a['url'],
				'variant' => 'light',
				'class'   => 'closing__btn btn--fluid-m',
				'attrs'   => $proodonto_a['attrs'],
			)
		);
		?>
	</div>
</section>
