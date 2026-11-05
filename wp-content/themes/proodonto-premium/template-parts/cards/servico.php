<?php
/**
 * Card de tratamento — todo card clicável termina no círculo-seta, sobe
 * 2px no hover e afunda no toque.
 *
 * Variantes:
 *   feature       — marinho com arco e selo (Home: Prótese).
 *   icon          — ícone em quadrado água + nome + resumo (Home).
 *   row           — linha com ícone (lista da unidade no celular; cartão alto no desktop).
 *   photo         — foto + ícone sobreposto (hub, "Veja também").
 *   feature-photo — marinho com foto grande (hub, destaque do grupo).
 *
 * @var array $args { @type int $id, @type string $variant, @type string $badge, @type string $class, @type string $photo_h }
 */

defined( 'ABSPATH' ) || exit;

$proodonto_c = proodonto_servico_card( $args['id'] ?? 0 );
if ( ! $proodonto_c ) {
	return;
}

$proodonto_v     = $args['variant'] ?? 'icon';
$proodonto_badge = $args['badge'] ?? '';
$proodonto_cls   = $args['class'] ?? '';
$proodonto_icon  = $proodonto_c['icon'] ? $proodonto_c['icon'] : 'tooth';

switch ( $proodonto_v ) {
	case 'feature':
		?>
		<a class="svc svc--feature card tap <?php echo esc_attr( $proodonto_cls ); ?>" href="<?php echo esc_url( $proodonto_c['url'] ); ?>">
			<?php echo proodonto_arc( 'card' ); // phpcs:ignore ?>
			<?php if ( $proodonto_badge ) : ?>
				<span class="badge"><?php echo esc_html( $proodonto_badge ); ?></span>
			<?php endif; ?>
			<span class="svc__spacer" aria-hidden="true"></span>
			<span class="svc__title display"><?php echo esc_html( $proodonto_c['title'] ); ?></span>
			<span class="svc__foot">
				<?php if ( $proodonto_c['desc'] ) : ?>
					<span class="svc__desc"><?php echo esc_html( $proodonto_c['desc'] ); ?></span>
				<?php endif; ?>
				<?php echo proodonto_chip( 'chip--white chip--lg' ); // phpcs:ignore ?>
			</span>
		</a>
		<?php
		break;

	case 'row':
		?>
		<a class="svc svc--row tap <?php echo esc_attr( $proodonto_cls ); ?>" href="<?php echo esc_url( $proodonto_c['url'] ); ?>">
			<span class="svc__ic" aria-hidden="true"><?php echo proodonto_icon( $proodonto_icon, 24, 1.6 ); // phpcs:ignore ?></span>
			<span class="svc__spacer" aria-hidden="true"></span>
			<span class="svc__txt">
				<span class="svc__title"><?php echo esc_html( $proodonto_c['title'] ); ?></span>
				<?php if ( $proodonto_c['desc'] ) : ?>
					<span class="svc__desc"><?php echo esc_html( $proodonto_c['desc'] ); ?></span>
				<?php endif; ?>
			</span>
			<?php echo proodonto_chip(); // phpcs:ignore ?>
		</a>
		<?php
		break;

	case 'photo':
	case 'feature-photo':
		$proodonto_feature = 'feature-photo' === $proodonto_v;
		?>
		<a class="svc svc--photo<?php echo $proodonto_feature ? ' svc--feature-photo' : ''; ?> card tap <?php echo esc_attr( $proodonto_cls ); ?>" href="<?php echo esc_url( $proodonto_c['url'] ); ?>">
			<span class="svc__media">
				<?php echo proodonto_photo( $proodonto_c['thumb'], 'proodonto-card', 'svc__photo', array( 'alt' => '', 'sizes' => $proodonto_feature ? '(min-width: 1024px) 640px, 100vw' : '(min-width: 1024px) 420px, 100vw' ) ); // phpcs:ignore ?>
				<?php if ( $proodonto_badge ) : ?>
					<span class="badge badge--on-photo"><?php echo esc_html( $proodonto_badge ); ?></span>
				<?php endif; ?>
				<?php if ( ! $proodonto_feature ) : ?>
					<span class="svc__ic svc__ic--float" aria-hidden="true"><?php echo proodonto_icon( $proodonto_icon, 28, 1.6 ); // phpcs:ignore ?></span>
				<?php endif; ?>
			</span>
			<span class="svc__body">
				<?php if ( $proodonto_feature ) { echo proodonto_arc( 'card' ); } // phpcs:ignore ?>
				<span class="svc__title display"><?php echo esc_html( $proodonto_c['title'] ); ?></span>
				<span class="svc__foot">
					<?php if ( $proodonto_c['desc'] ) : ?>
						<span class="svc__desc"><?php echo esc_html( $proodonto_c['desc'] ); ?></span>
					<?php endif; ?>
					<?php echo proodonto_chip( $proodonto_feature ? 'chip--white chip--lg' : '' ); // phpcs:ignore ?>
				</span>
			</span>
		</a>
		<?php
		break;

	default: // icon
		?>
		<a class="svc svc--icon card tap <?php echo esc_attr( $proodonto_cls ); ?>" href="<?php echo esc_url( $proodonto_c['url'] ); ?>">
			<span class="svc__ic" aria-hidden="true"><?php echo proodonto_icon( $proodonto_icon, 26, 1.6 ); // phpcs:ignore ?></span>
			<span class="svc__spacer" aria-hidden="true"></span>
			<span class="svc__title"><?php echo esc_html( $proodonto_c['title'] ); ?></span>
			<span class="svc__foot">
				<?php if ( $proodonto_c['desc'] ) : ?>
					<span class="svc__desc"><?php echo esc_html( $proodonto_c['desc'] ); ?></span>
				<?php endif; ?>
				<?php echo proodonto_chip(); // phpcs:ignore ?>
			</span>
		</a>
		<?php
}
