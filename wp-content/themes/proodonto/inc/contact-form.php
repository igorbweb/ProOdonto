<?php
/**
 * Processa o envio do formulário do bloco proodonto/contact.
 *
 * Sem plugin, sem AJAX: submit normal de formulário via admin-post.php,
 * validação por nonce + honeypot, envio por wp_mail(), e redirect de volta
 * para a página com ?proodonto_contact=success|error (lido em
 * blocks/contact/render.php para exibir o aviso).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_proodonto_contact', 'proodonto_handle_contact_submission' );
add_action( 'admin_post_proodonto_contact', 'proodonto_handle_contact_submission' );

function proodonto_handle_contact_submission() {
	$redirect = wp_get_referer() ? remove_query_arg( 'proodonto_contact', wp_get_referer() ) : home_url( '/' );

	if ( ! isset( $_POST['proodonto_contact_nonce'] ) || ! wp_verify_nonce( $_POST['proodonto_contact_nonce'], 'proodonto_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'proodonto_contact', 'error', $redirect ) );
		exit;
	}

	// Honeypot: campo invisível que só um bot preencheria. Finge sucesso
	// sem realmente enviar e-mail, para não ensinar o bot a se adaptar.
	if ( ! empty( $_POST['proodonto_contact_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'proodonto_contact', 'success', $redirect ) );
		exit;
	}

	$name    = isset( $_POST['proodonto_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['proodonto_contact_name'] ) ) : '';
	$phone   = isset( $_POST['proodonto_contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['proodonto_contact_phone'] ) ) : '';
	$email   = isset( $_POST['proodonto_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['proodonto_contact_email'] ) ) : '';
	$message = isset( $_POST['proodonto_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['proodonto_contact_message'] ) ) : '';
	$to      = isset( $_POST['proodonto_contact_to'] ) ? sanitize_email( wp_unslash( $_POST['proodonto_contact_to'] ) ) : '';

	if ( ! $name || ( ! $phone && ! $email ) ) {
		wp_safe_redirect( add_query_arg( 'proodonto_contact', 'error', $redirect ) );
		exit;
	}

	if ( ! $to ) {
		$to = get_option( 'admin_email' );
	}

	$subject = sprintf(
		/* translators: %s: nome de quem enviou o formulário */
		__( 'Novo contato pelo site: %s', 'proodonto' ),
		$name
	);

	$body = sprintf(
		"Nome: %s\nTelefone: %s\nE-mail: %s\n\nMensagem:\n%s",
		$name,
		$phone ? $phone : '-',
		$email ? $email : '-',
		$message ? $message : '-'
	);

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( $email ) {
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
	}

	$sent = wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'proodonto_contact', $sent ? 'success' : 'error', $redirect ) );
	exit;
}
