<?php
/**
 * Render server-side do bloco proodonto/contact.
 *
 * O formulário é 100% nativo (sem plugin): submete via admin-post.php para
 * inc/contact-form.php, que valida nonce + honeypot e envia com wp_mail().
 * Sem JS/AJAX — o retorno é um redirect com ?proodonto_contact=success|error,
 * lido aqui para mostrar o aviso.
 */

defined( 'ABSPATH' ) || exit;

function proodonto_render_block_contact( $attributes ) {
	$heading   = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
	$intro     = isset( $attributes['intro'] ) ? $attributes['intro'] : '';
	$phone     = isset( $attributes['phone'] ) ? $attributes['phone'] : '';
	$whatsapp  = isset( $attributes['whatsapp'] ) ? preg_replace( '/\D+/', '', (string) $attributes['whatsapp'] ) : '';
	$email     = isset( $attributes['email'] ) ? $attributes['email'] : '';
	$address   = isset( $attributes['address'] ) ? $attributes['address'] : '';
	$hours     = isset( $attributes['hours'] ) ? $attributes['hours'] : '';
	$map_url   = isset( $attributes['mapUrl'] ) ? $attributes['mapUrl'] : '';
	$recipient = isset( $attributes['recipientEmail'] ) ? $attributes['recipientEmail'] : '';

	$status = isset( $_GET['proodonto_contact'] ) ? sanitize_key( wp_unslash( $_GET['proodonto_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- só controla qual aviso exibir, não executa ação.

	ob_start();
	?>
	<section class="contact">
		<div class="container contact__inner">

			<div class="contact__info">
				<?php if ( $heading ) : ?>
					<h2 class="contact__heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $intro ) : ?>
					<p class="contact__intro"><?php echo wp_kses_post( $intro ); ?></p>
				<?php endif; ?>

				<ul class="contact__details">
					<?php if ( $phone ) : ?>
						<li><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $whatsapp ) : ?>
						<li><a href="https://wa.me/<?php echo esc_attr( $whatsapp ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp', 'proodonto' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $address ) : ?>
						<li><?php echo nl2br( esc_html( $address ) ); ?></li>
					<?php endif; ?>
					<?php if ( $hours ) : ?>
						<li><?php echo nl2br( esc_html( $hours ) ); ?></li>
					<?php endif; ?>
				</ul>

				<?php if ( $map_url ) : ?>
					<div class="contact__map">
						<iframe
							src="<?php echo esc_url( $map_url ); ?>"
							loading="lazy"
							referrerpolicy="no-referrer-when-downgrade"
							title="<?php esc_attr_e( 'Mapa de localização', 'proodonto' ); ?>"
						></iframe>
					</div>
				<?php endif; ?>
			</div>

			<div class="contact__form-wrap">

				<?php if ( 'success' === $status ) : ?>
					<p class="contact__notice contact__notice--success" role="status">
						<?php esc_html_e( 'Mensagem enviada com sucesso! Em breve entraremos em contato.', 'proodonto' ); ?>
					</p>
				<?php elseif ( 'error' === $status ) : ?>
					<p class="contact__notice contact__notice--error" role="alert">
						<?php esc_html_e( 'Não foi possível enviar sua mensagem. Tente novamente ou use o telefone/WhatsApp acima.', 'proodonto' ); ?>
					</p>
				<?php endif; ?>

				<form class="contact__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'proodonto_contact', 'proodonto_contact_nonce' ); ?>
					<input type="hidden" name="action" value="proodonto_contact" />
					<?php if ( $recipient ) : ?>
						<input type="hidden" name="proodonto_contact_to" value="<?php echo esc_attr( $recipient ); ?>" />
					<?php endif; ?>

					<span class="contact__honeypot" aria-hidden="true">
						<label for="proodonto_contact_website"><?php esc_html_e( 'Deixe este campo em branco', 'proodonto' ); ?></label>
						<input type="text" id="proodonto_contact_website" name="proodonto_contact_website" tabindex="-1" autocomplete="off" />
					</span>

					<p class="contact__field">
						<label for="proodonto_contact_name"><?php esc_html_e( 'Nome', 'proodonto' ); ?></label>
						<input type="text" id="proodonto_contact_name" name="proodonto_contact_name" required />
					</p>

					<p class="contact__field">
						<label for="proodonto_contact_phone"><?php esc_html_e( 'Telefone', 'proodonto' ); ?></label>
						<input type="tel" id="proodonto_contact_phone" name="proodonto_contact_phone" />
					</p>

					<p class="contact__field">
						<label for="proodonto_contact_email"><?php esc_html_e( 'E-mail', 'proodonto' ); ?></label>
						<input type="email" id="proodonto_contact_email" name="proodonto_contact_email" />
					</p>

					<p class="contact__field">
						<label for="proodonto_contact_message"><?php esc_html_e( 'Mensagem', 'proodonto' ); ?></label>
						<textarea id="proodonto_contact_message" name="proodonto_contact_message" rows="4"></textarea>
					</p>

					<button type="submit" class="button contact__submit"><?php esc_html_e( 'Enviar', 'proodonto' ); ?></button>
				</form>
			</div>

		</div>
	</section>
	<?php
	return ob_get_clean();
}
