<?php
/**
 * Perguntas frequentes — acordeão nativo (<details>), resposta no HTML
 * servido (SEO-04). O mesmo conjunto vira FAQPage em inc/schema.php.
 *
 * @var array $args {
 *   @type array  $items  [ ['pergunta' => '', 'resposta' => ''], ... ].
 *   @type string $title  Título (com *destaque*).
 *   @type string $layout split (título à esquerda no desktop) | stack.
 *   @type bool   $open   Primeira pergunta aberta.
 *   @type string $html   HTML pronto (FAQ do editor) no lugar dos itens.
 *   @type string $aside  HTML extra na coluna do título (ex.: "Sobre").
 *   @type array  $unit   Unidade (tokens).
 *   @type string $id
 *   @type string $class
 * }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_a = wp_parse_args(
	$args ?? array(),
	array(
		'items'  => array(),
		'title'  => '',
		'layout' => 'split',
		'open'   => false,
		'html'   => '',
		'aside'  => '',
		'unit'   => null,
		'id'     => 'perguntas',
		'class'  => '',
	)
);

$proodonto_items = array_values(
	array_filter(
		(array) $proodonto_a['items'],
		static function ( $r ) {
			return ! empty( $r['pergunta'] ) && ! empty( $r['resposta'] );
		}
	)
);
if ( ! $proodonto_items && ! $proodonto_a['html'] && ! $proodonto_a['aside'] ) {
	return;
}
$proodonto_hid = 'faq-' . wp_unique_id();
?>
<section class="section faq-sec faq-sec--<?php echo esc_attr( $proodonto_a['layout'] ); ?> <?php echo esc_attr( $proodonto_a['class'] ); ?>" id="<?php echo esc_attr( $proodonto_a['id'] ); ?>"<?php echo $proodonto_a['title'] ? ' aria-labelledby="' . esc_attr( $proodonto_hid ) . '"' : ''; ?>>
	<div class="wrap faq-sec__grid">
		<div class="faq-sec__side">
			<?php if ( $proodonto_a['aside'] ) : ?>
				<?php echo $proodonto_a['aside']; // phpcs:ignore -- montado pelo template com escapes. ?>
			<?php elseif ( $proodonto_a['title'] ) : ?>
				<h2 class="h2" id="<?php echo esc_attr( $proodonto_hid ); ?>"><?php echo proodonto_rich( $proodonto_a['title'], $proodonto_a['unit'] ); // phpcs:ignore ?></h2>
			<?php endif; ?>
		</div>
		<div class="faq-sec__list">
			<?php if ( $proodonto_a['aside'] && $proodonto_a['title'] ) : ?>
				<h2 class="h2 h2--sm only-m" id="<?php echo esc_attr( $proodonto_hid ); ?>"><?php echo proodonto_rich( $proodonto_a['title'], $proodonto_a['unit'] ); // phpcs:ignore ?></h2>
			<?php endif; ?>
			<?php if ( $proodonto_items ) : ?>
				<div class="card faq">
					<?php foreach ( $proodonto_items as $proodonto_i => $proodonto_r ) : ?>
						<?php
						echo proodonto_faq_item_html( // phpcs:ignore
							esc_html( proodonto_tokens( $proodonto_r['pergunta'], $proodonto_a['unit'] ) ),
							'<p>' . esc_html( proodonto_tokens( $proodonto_r['resposta'], $proodonto_a['unit'] ) ) . '</p>',
							$proodonto_a['open'] && 0 === $proodonto_i
						);
						?>
					<?php endforeach; ?>
				</div>
			<?php elseif ( $proodonto_a['html'] ) : ?>
				<div class="faq-sec__html"><?php echo $proodonto_a['html']; // phpcs:ignore -- the_content. ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>
