<?php
/**
 * Gerador automático de template + CSS por página.
 *
 * Toda vez que uma página é PUBLICADA pela primeira vez no wp-admin, este
 * script cria:
 *
 *   - page-{slug}.php                    (na raiz do tema)
 *   - assets/css/pages/{slug}.css         (mobile-first, carregado só nessa página)
 *
 * Isso funciona sem nenhuma configuração adicional porque page-{slug}.php
 * já faz parte da Template Hierarchy nativa do WordPress: se o arquivo
 * existir, o WP o usa automaticamente para aquela página — não é preciso
 * cabeçalho "Template Name" nem limpar cache de templates.
 *
 * Arquivos já existentes NUNCA são sobrescritos automaticamente: uma vez
 * gerado, o arquivo é seu para editar livremente. Para forçar a
 * regeneração, apague o arquivo manualmente (ou use o botão "Recriar
 * arquivos" na caixa "Proodonto — Arquivos da página" no editor).
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------------------------------------
 * 1. Gatilhos
 * -------------------------------------------------------------------- */

// Gatilho principal: primeira vez que a página é publicada.
add_action( 'transition_post_status', 'proodonto_on_publish_generate_assets', 10, 3 );

function proodonto_on_publish_generate_assets( $new_status, $old_status, $post ) {
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}
	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}

	proodonto_scaffold_page_assets( $post );
}

// Fallback: cobre páginas que já estavam publicadas antes de o tema existir,
// ou que foram importadas/duplicadas sem passar pelo fluxo normal de publicação.
add_action( 'save_post_page', 'proodonto_backfill_page_assets', 20, 2 );

function proodonto_backfill_page_assets( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( 'publish' !== $post->post_status ) {
		return;
	}
	if ( get_post_meta( $post_id, '_proodonto_scaffolded', true ) ) {
		return;
	}

	proodonto_scaffold_page_assets( $post );
}

/* -----------------------------------------------------------------------
 * 2. Geração dos arquivos
 * -------------------------------------------------------------------- */

function proodonto_scaffold_page_assets( $post, $force = false ) {
	$slug = $post->post_name ? $post->post_name : sanitize_title( $post->post_title );

	if ( ! $slug ) {
		return array();
	}

	$template_rel  = "page-{$slug}.php";
	$template_path = PROODONTO_DIR . '/' . $template_rel;
	$css_rel       = "assets/css/pages/{$slug}.css";
	$css_path      = PROODONTO_DIR . '/' . $css_rel;

	$created = array();

	if ( ( $force || ! file_exists( $template_path ) ) && wp_is_writable( dirname( $template_path ) ) ) {
		$content = apply_filters( 'proodonto_page_template_boilerplate', proodonto_build_page_template( $post, $slug ), $post, $slug );
		if ( false !== file_put_contents( $template_path, $content ) ) {
			$created[] = $template_rel;
		}
	}

	if ( ( $force || ! file_exists( $css_path ) ) && wp_is_writable( dirname( $css_path ) ) ) {
		$content = apply_filters( 'proodonto_page_css_boilerplate', proodonto_build_page_css( $post, $slug ), $post, $slug );
		if ( false !== file_put_contents( $css_path, $content ) ) {
			$created[] = $css_rel;
		}
	}

	update_post_meta( $post->ID, '_proodonto_scaffolded', 1 );
	update_post_meta( $post->ID, '_proodonto_scaffolded_slug', $slug );

	if ( $created ) {
		proodonto_queue_admin_notice(
			sprintf(
				/* translators: 1: título da página, 2: lista de arquivos criados */
				__( 'Proodonto: arquivos criados para "%1$s" — %2$s', 'proodonto' ),
				$post->post_title,
				implode( ', ', $created )
			)
		);
	}

	return $created;
}

function proodonto_build_page_template( $post, $slug ) {
	$date = current_time( 'Y-m-d H:i' );

	return <<<PHP
<?php
/**
 * Template gerado automaticamente para a página "{$post->post_title}" (slug: {$slug}).
 * Criado em {$date} pelo gerador automático do tema Proodonto.
 *
 * Este arquivo NÃO é sobrescrito em publicações futuras — edite à vontade.
 * O CSS correspondente fica em assets/css/pages/{$slug}.css e é carregado
 * automaticamente apenas nesta página (ver inc/enqueue.php).
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'page' ); ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();

PHP;
}

function proodonto_build_page_css( $post, $slug ) {
	$date = current_time( 'Y-m-d H:i' );

	return <<<CSS
/*
 * CSS da página "{$post->post_title}" (slug: {$slug}).
 * Gerado automaticamente em {$date} — mobile-first, edite à vontade.
 * Carregado apenas nesta página via inc/enqueue.php.
 */

.page-{$slug} {
}

/* Tablet */
@media (min-width: 600px) {
	.page-{$slug} {
	}
}

/* Desktop */
@media (min-width: 900px) {
	.page-{$slug} {
	}
}

/* Desktop grande */
@media (min-width: 1200px) {
	.page-{$slug} {
	}
}

CSS;
}

/* -----------------------------------------------------------------------
 * 3. Aviso no admin confirmando os arquivos criados
 * -------------------------------------------------------------------- */

function proodonto_queue_admin_notice( $message ) {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$notices   = get_transient( "proodonto_notices_{$user_id}" );
	$notices   = is_array( $notices ) ? $notices : array();
	$notices[] = $message;

	set_transient( "proodonto_notices_{$user_id}", $notices, MINUTE_IN_SECONDS * 5 );
}

add_action( 'admin_notices', function () {
	$user_id = get_current_user_id();
	$notices = get_transient( "proodonto_notices_{$user_id}" );

	if ( ! $notices ) {
		return;
	}

	delete_transient( "proodonto_notices_{$user_id}" );

	foreach ( (array) $notices as $notice ) {
		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $notice ) );
	}
} );

/* -----------------------------------------------------------------------
 * 4. Caixa "Proodonto — Arquivos da página" no editor, com botão para
 *    recriar arquivos apagados manualmente (nunca sobrescreve os existentes
 *    a menos que o usuário confirme explicitamente via "forçar").
 * -------------------------------------------------------------------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'proodonto_page_files',
		__( 'Proodonto — Arquivos da página', 'proodonto' ),
		'proodonto_render_page_files_meta_box',
		'page',
		'side',
		'low'
	);
} );

function proodonto_render_page_files_meta_box( $post ) {
	$slug = $post->post_name ? $post->post_name : sanitize_title( $post->post_title );

	if ( ! $slug || 'auto-draft' === $post->post_status ) {
		esc_html_e( 'Salve/publique a página para gerar os arquivos.', 'proodonto' );
		return;
	}

	$template_rel = "page-{$slug}.php";
	$css_rel      = "assets/css/pages/{$slug}.css";
	$template_ok  = file_exists( PROODONTO_DIR . '/' . $template_rel );
	$css_ok       = file_exists( PROODONTO_DIR . '/' . $css_rel );

	wp_nonce_field( 'proodonto_regenerate_' . $post->ID, 'proodonto_regenerate_nonce' );
	?>
	<p>
		<?php echo $template_ok ? '✅' : '⬜'; ?> <code><?php echo esc_html( $template_rel ); ?></code><br />
		<?php echo $css_ok ? '✅' : '⬜'; ?> <code><?php echo esc_html( $css_rel ); ?></code>
	</p>
	<?php if ( ! $template_ok || ! $css_ok ) : ?>
		<button type="submit" class="button" name="proodonto_regenerate" value="1">
			<?php esc_html_e( 'Criar arquivos que faltam', 'proodonto' ); ?>
		</button>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'Ambos os arquivos existem. Apague um deles manualmente para poder recriá-lo aqui.', 'proodonto' ); ?></p>
	<?php endif; ?>
	<?php
}

add_action( 'save_post_page', function ( $post_id, $post ) {
	if ( empty( $_POST['proodonto_regenerate'] ) ) {
		return;
	}
	if ( ! isset( $_POST['proodonto_regenerate_nonce'] ) || ! wp_verify_nonce( $_POST['proodonto_regenerate_nonce'], 'proodonto_regenerate_' . $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	proodonto_scaffold_page_assets( $post );
}, 20, 2 );
