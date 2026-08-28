<?php
/**
 * Credenciais profissionais do autor (cargo/especialidade + CRO), guardadas
 * como user meta nativo — sem depender de CPT ou ACF para isso.
 *
 * Alimenta três pontos, todos lendo o mesmo dado:
 *   - Campos extras no perfil do usuário (wp-admin/profile.php).
 *   - Bio do autor em template-parts/content-single.php.
 *   - author.jobTitle / author.identifier no JSON-LD BlogPosting (inc/seo.php).
 *
 * Sinal de E-E-A-T exigido pela pauta de conteúdo YMYL (odontologia):
 * página de saúde precisa expor quem escreveu/revisou e seu registro
 * profissional, não só um nome.
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------------------------------------
 * 1. Campos no perfil do usuário
 * -------------------------------------------------------------------- */
add_action( 'show_user_profile', 'proodonto_render_author_credentials_fields' );
add_action( 'edit_user_profile', 'proodonto_render_author_credentials_fields' );

function proodonto_render_author_credentials_fields( $user ) {
	$job_title = get_user_meta( $user->ID, 'proodonto_job_title', true );
	$cro       = get_user_meta( $user->ID, 'proodonto_cro', true );
	?>
	<h2><?php esc_html_e( 'Credenciais profissionais (blog)', 'proodonto' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="proodonto_job_title"><?php esc_html_e( 'Cargo / especialidade', 'proodonto' ); ?></label></th>
			<td>
				<input type="text" name="proodonto_job_title" id="proodonto_job_title" value="<?php echo esc_attr( $job_title ); ?>" class="regular-text" placeholder="Cirurgião-dentista especialista em Implantodontia" />
			</td>
		</tr>
		<tr>
			<th><label for="proodonto_cro"><?php esc_html_e( 'CRO', 'proodonto' ); ?></label></th>
			<td>
				<input type="text" name="proodonto_cro" id="proodonto_cro" value="<?php echo esc_attr( $cro ); ?>" class="regular-text" placeholder="CRO-SE 12345" />
				<p class="description"><?php esc_html_e( 'Exibido na bio dos posts do blog e no schema.org (JSON-LD) como credencial do autor.', 'proodonto' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'personal_options_update', 'proodonto_save_author_credentials_fields' );
add_action( 'edit_user_profile_update', 'proodonto_save_author_credentials_fields' );

function proodonto_save_author_credentials_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	if ( isset( $_POST['proodonto_job_title'] ) ) {
		update_user_meta( $user_id, 'proodonto_job_title', sanitize_text_field( wp_unslash( $_POST['proodonto_job_title'] ) ) );
	}

	if ( isset( $_POST['proodonto_cro'] ) ) {
		update_user_meta( $user_id, 'proodonto_cro', sanitize_text_field( wp_unslash( $_POST['proodonto_cro'] ) ) );
	}
}

/* -----------------------------------------------------------------------
 * 2. Helper de leitura, usado no template e no JSON-LD
 * -------------------------------------------------------------------- */
function proodonto_get_author_credentials( $user_id ) {
	return array(
		'job_title' => get_user_meta( $user_id, 'proodonto_job_title', true ),
		'cro'       => get_user_meta( $user_id, 'proodonto_cro', true ),
	);
}

/**
 * Monta o objeto Person do JSON-LD (BlogPosting.author), incluindo
 * jobTitle/identifier (CRO) quando o autor tiver essas credenciais
 * preenchidas no perfil.
 */
function proodonto_get_author_schema( $user_id ) {
	$person = array(
		'@type' => 'Person',
		'name'  => get_the_author_meta( 'display_name', $user_id ),
		'url'   => get_author_posts_url( $user_id ),
	);

	$credentials = proodonto_get_author_credentials( $user_id );

	if ( $credentials['job_title'] ) {
		$person['jobTitle'] = $credentials['job_title'];
	}

	if ( $credentials['cro'] ) {
		$person['identifier'] = $credentials['cro'];
	}

	return $person;
}
