<?php
/**
 * Seletor de unidade = o CTA. Cada linha inteira é um link do WhatsApp
 * da unidade (nada de texto que parece botão e não é — 4% de cliques
 * mortos no Clarity).
 *
 * Celular: cartão branco sobreposto ao topo, linhas empilhadas com a
 * pílula "WhatsApp". Desktop (≥1024): barra horizontal com as 3 cidades
 * em Coolvetica e o círculo-seta.
 *
 * @var array $args {
 *   @type string $title      Título ("Agende na unidade mais perto").
 *   @type int    $servico_id Tratamento (usa o link UpView próprio dele).
 *   @type string $place      Origem do clique (dataLayer).
 *   @type string $class      Classes extras do cartão.
 *   @type bool   $trigger    Marca como gatilho da barra fixa.
 *   @type string $id         id do bloco.
 * }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_units = proodonto_get_units();
if ( ! $proodonto_units ) {
	return;
}

$proodonto_a = wp_parse_args(
	$args ?? array(),
	array(
		'title'      => '',
		'servico_id' => 0,
		'place'      => 'seletor',
		'class'      => '',
		'trigger'    => false,
		'id'         => '',
	)
);
$proodonto_title_id = 'sel-' . wp_unique_id();
?>
<div class="selector card <?php echo esc_attr( $proodonto_a['class'] ); ?>"<?php echo $proodonto_a['id'] ? ' id="' . esc_attr( $proodonto_a['id'] ) . '"' : ''; ?><?php echo $proodonto_a['trigger'] ? ' data-sticky-trigger' : ''; ?>>
	<?php if ( $proodonto_a['title'] ) : ?>
		<h2 class="selector__title" id="<?php echo esc_attr( $proodonto_title_id ); ?>"><?php echo esc_html( proodonto_plain( $proodonto_a['title'] ) ); ?></h2>
	<?php endif; ?>
	<ul class="selector__list"<?php echo $proodonto_a['title'] ? ' aria-labelledby="' . esc_attr( $proodonto_title_id ) . '"' : ''; ?>>
		<?php foreach ( $proodonto_units as $proodonto_u ) : ?>
			<?php
			$proodonto_url = proodonto_unit_cta_url( $proodonto_u, (int) $proodonto_a['servico_id'] );
			if ( ! $proodonto_url ) {
				continue;
			}
			$proodonto_hours = proodonto_unit_hours_first( $proodonto_u );
			?>
			<li>
				<a class="selector__row tap" href="<?php echo esc_url( $proodonto_url ); ?>"<?php echo proodonto_attrs( proodonto_cta_attrs( $proodonto_u, $proodonto_a['place'] ) ); // phpcs:ignore ?>>
					<span class="selector__txt">
						<span class="selector__city"><?php echo esc_html( $proodonto_u['name'] ); ?></span>
						<span class="selector__sub only-m"><?php echo esc_html( implode( ' · ', array_filter( array( $proodonto_u['bairro'], $proodonto_hours ) ) ) ); ?></span>
						<span class="selector__sub only-d"><?php echo esc_html( $proodonto_u['bairro'] ); ?><?php echo $proodonto_u['nota'] ? ' · <span aria-hidden="true">★</span> ' . esc_html( $proodonto_u['nota'] ) : ''; ?></span>
					</span>
					<span class="selector__pill only-m"><?php esc_html_e( 'WhatsApp', 'proodonto' ); ?><span class="selector__pill-ic" aria-hidden="true"><?php echo proodonto_icon( 'arrow', 16 ); // phpcs:ignore ?></span></span>
					<?php echo proodonto_chip( 'only-d' ); // phpcs:ignore ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
