<?php
/**
 * "Perto de você" — cartão por unidade com foto da fachada, nota,
 * endereço e horário em texto (NAP), WhatsApp, rota e link para a página
 * da unidade. Home e hub.
 *
 * @var array $args {
 *   @type string $eyebrow
 *   @type string $title
 *   @type int    $servico_id Usa o link UpView do tratamento.
 *   @type bool   $page_link  Link "Conhecer a unidade".
 *   @type string $id
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
		'eyebrow'    => '',
		'title'      => '',
		'servico_id' => 0,
		'page_link'  => true,
		'id'         => 'unidades',
	)
);
$proodonto_hid = 'unid-' . wp_unique_id();
?>
<section class="section units" id="<?php echo esc_attr( $proodonto_a['id'] ); ?>" aria-labelledby="<?php echo esc_attr( $proodonto_hid ); ?>">
	<div class="wrap">
		<?php proodonto_section_head( $proodonto_a['eyebrow'], $proodonto_a['title'], array( 'id' => $proodonto_hid ) ); ?>
		<div class="units__grid">
			<?php foreach ( $proodonto_units as $proodonto_u ) : ?>
				<?php
				$proodonto_page = $proodonto_a['page_link'] ? proodonto_unit_page_url( $proodonto_u ) : '';
				$proodonto_wa   = proodonto_unit_cta_url( $proodonto_u, (int) $proodonto_a['servico_id'] );
				$proodonto_hrs  = proodonto_unit_hours_text( $proodonto_u );
				?>
				<article class="unit-card card">
					<?php echo proodonto_photo( $proodonto_u['foto'], 'proodonto-card', 'unit-card__photo', array( 'alt' => sprintf( __( 'Fachada da unidade %s', 'proodonto' ), $proodonto_u['name'] ), 'sizes' => '(min-width: 1024px) 380px, 100vw' ) ); // phpcs:ignore ?>
					<div class="unit-card__body">
						<div class="unit-card__head">
							<h3 class="display unit-card__city"><?php echo esc_html( $proodonto_u['name'] ); ?></h3>
							<?php if ( $proodonto_u['nota'] ) : ?>
								<span class="unit-card__rate"><span class="stars" aria-hidden="true">★</span> <?php echo esc_html( trim( $proodonto_u['nota'] . ( $proodonto_u['total'] ? ' · ' . $proodonto_u['total'] : '' ) ) ); ?><span class="sr-only"> <?php esc_html_e( 'avaliações no Google', 'proodonto' ); ?></span></span>
							<?php endif; ?>
						</div>
						<?php if ( $proodonto_u['street'] ) : ?>
							<p class="iline"><?php echo proodonto_icon( 'pin', 18, 1.6 ); // phpcs:ignore ?><span><?php echo esc_html( proodonto_unit_address_line( $proodonto_u ) ); ?></span></p>
						<?php endif; ?>
						<?php if ( $proodonto_hrs ) : ?>
							<p class="iline iline--muted unit-card__hours"><?php echo proodonto_icon( 'clock', 18, 1.6 ); // phpcs:ignore ?><span><?php echo esc_html( $proodonto_hrs ); ?></span></p>
						<?php endif; ?>
						<div class="unit-card__btns">
							<?php
							echo proodonto_button( // phpcs:ignore
								array(
									'label' => __( 'WhatsApp', 'proodonto' ),
									'url'   => $proodonto_wa,
									'small' => true,
									'icon'  => 'whatsapp',
									'attrs' => proodonto_cta_attrs( $proodonto_u, 'cartao-unidade' ),
								)
							);
							echo proodonto_button( // phpcs:ignore
								array(
									'label'   => __( 'Rota', 'proodonto' ),
									'url'     => $proodonto_u['maps_url'],
									'small'   => true,
									'variant' => 'outline',
									'icon'    => 'route',
									'attrs'   => array(
										'rel'       => 'noopener',
										'data-rota' => $proodonto_u['slug'],
									),
								)
							);
							?>
						</div>
						<?php if ( $proodonto_page ) : ?>
							<a class="link" href="<?php echo esc_url( $proodonto_page ); ?>"><?php echo esc_html( sprintf( __( 'Conhecer a unidade %s', 'proodonto' ), $proodonto_u['name'] ) ); ?><?php echo proodonto_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
