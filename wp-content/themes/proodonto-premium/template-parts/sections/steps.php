<?php
/**
 * "Primeira avaliação" — bloco marinho com arco e os 3 passos
 * (Opções → Primeira avaliação). Celular: lista vertical; desktop: texto
 * à esquerda e 3 cartões de vidro.
 *
 * @var array $args {
 *   @type string $eyebrow  Troca o sobretítulo global.
 *   @type string $title    Troca o título global.
 *   @type string $button   Troca o texto do botão.
 *   @type string $url      Destino do botão (padrão: unidades).
 *   @type array  $attrs    Atributos do botão.
 *   @type array  $unit     Unidade (tokens {cidade}).
 *   @type string $id       id da seção.
 * }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_steps = array_values(
	array_filter(
		proodonto_rows( 'passos', 'option' ),
		static function ( $r ) {
			return ! empty( $r['titulo'] );
		}
	)
);
if ( ! $proodonto_steps ) {
	return;
}

$proodonto_a = wp_parse_args(
	$args ?? array(),
	array(
		'eyebrow' => '',
		'title'   => '',
		'button'  => '',
		'url'     => proodonto_units_anchor(),
		'attrs'   => array( 'data-cta' => 'passos' ),
		'unit'    => null,
		'id'      => '',
	)
);
$proodonto_eyebrow = $proodonto_a['eyebrow'] ? $proodonto_a['eyebrow'] : proodonto_opt( 'passos_eyebrow' );
$proodonto_title   = $proodonto_a['title'] ? $proodonto_a['title'] : proodonto_opt( 'passos_titulo' );
$proodonto_button  = $proodonto_a['button'] ? $proodonto_a['button'] : proodonto_opt( 'passos_botao' );
$proodonto_text    = (string) proodonto_opt( 'passos_texto' );
$proodonto_hid     = 'passos-' . wp_unique_id();
?>
<section class="band band--navy steps"<?php echo $proodonto_a['id'] ? ' id="' . esc_attr( $proodonto_a['id'] ) . '"' : ''; ?> aria-labelledby="<?php echo esc_attr( $proodonto_hid ); ?>">
	<?php echo proodonto_arc( 'band' ); // phpcs:ignore ?>
	<div class="wrap steps__grid">
		<div class="steps__intro">
			<?php
			proodonto_section_head(
				$proodonto_eyebrow,
				$proodonto_title,
				array(
					'id'   => $proodonto_hid,
					'unit' => $proodonto_a['unit'],
				)
			);
			?>
			<?php if ( $proodonto_text ) : ?>
				<p class="steps__text only-d"><?php echo esc_html( $proodonto_text ); ?></p>
			<?php endif; ?>
			<?php
			echo proodonto_button( // phpcs:ignore
				array(
					'label'   => proodonto_tokens( $proodonto_button, $proodonto_a['unit'] ),
					'url'     => $proodonto_a['url'],
					'variant' => 'light',
					'class'   => 'only-d',
					'attrs'   => $proodonto_a['attrs'],
				)
			);
			?>
		</div>
		<ol class="steps__list">
			<?php foreach ( $proodonto_steps as $proodonto_i => $proodonto_s ) : ?>
				<li class="steps__item glass">
					<span class="steps__n" aria-hidden="true"><?php echo (int) $proodonto_i + 1; ?></span>
					<div>
						<p class="steps__t"><?php echo esc_html( $proodonto_s['titulo'] ); ?></p>
						<?php if ( ! empty( $proodonto_s['descricao'] ) ) : ?>
							<p class="steps__d"><?php echo esc_html( $proodonto_s['descricao'] ); ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
		echo proodonto_button( // phpcs:ignore
			array(
				'label'   => proodonto_tokens( $proodonto_button, $proodonto_a['unit'] ),
				'url'     => $proodonto_a['url'],
				'variant' => 'light',
				'block'   => true,
				'class'   => 'only-m',
				'attrs'   => $proodonto_a['attrs'],
			)
		);
		?>
	</div>
</section>
