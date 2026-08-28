<?php
/**
 * Mapa estático (Google Maps Static API) com os pins das unidades reais —
 * desenhado para gastar o mínimo de cota possível:
 *
 *   - A imagem é baixada da API SÓ UMA VEZ (quando os endereços mudam ou
 *     na primeira vez que a chave é configurada) e salva em
 *     wp-content/uploads/proodonto/. Depois disso, todo carregamento da
 *     Home serve esse arquivo local — nenhum request à API do Google.
 *   - Os endereços completos são passados direto como "markers"; a Static
 *     Maps API já geocodifica sozinha, então não existe uma chamada
 *     separada à Geocoding API (seria cota em dobro pra nada).
 *   - O cache é por HASH da lista de endereços, não por tempo: só invalida
 *     se `proodonto_get_units()` mudar de conteúdo.
 *   - Se uma tentativa falhar (chave inválida, sem cota, rede fora), fica
 *     um transient de 1h bloqueando novas tentativas — evita martelar a
 *     API em todo carregamento de página enquanto o problema não é
 *     corrigido.
 *
 * Sem chave configurada (Opções do Tema → Google Maps API Key), a função
 * simplesmente retorna string vazia e o template usa o placeholder normal.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lista única das unidades — fonte de verdade reaproveitada tanto pelo
 * gerador do mapa quanto pelos cards de unidade em page-home.php.
 */
function proodonto_get_units() {
	return array(
		array(
			'name'         => 'Aracaju',
			'address'      => 'Av. Pres. Tancredo Neves, 1028 - Jardins, Aracaju - SE, 49025-620',
			'maps_url'     => 'https://maps.app.goo.gl/wvRKAiDYqjFTaJbd9',
			'whatsapp_url' => 'https://api.upviewcrm.com/go/proodonto-aracaju/lp-aracaju',
		),
		array(
			'name'         => 'Lagarto',
			'address'      => 'Av. Contorno, 477 - Centro, Lagarto - SE, 49400-000',
			'maps_url'     => 'https://maps.app.goo.gl/ZuvT3qpFaKa67z5P8',
			'whatsapp_url' => 'https://api.upviewcrm.com/go/proodonto-lagarto/lp-lagarto',
		),
		array(
			'name'         => 'Simão Dias',
			'address'      => 'Av. Construtor João Antônio de Santana, 330 - Centro, Simão Dias - SE, 49480-000',
			'maps_url'     => 'https://maps.app.goo.gl/JvfkXnriGvccPRaY8',
			'whatsapp_url' => 'https://api.upviewcrm.com/go/proodonto-simao-dias/lp-simao-dias',
		),
	);
}

/**
 * URL (local, já em cache) da imagem do mapa com os pins das unidades.
 * Só toca a API do Google quando realmente precisa gerar/regerar.
 *
 * @return string URL da imagem, ou '' se não houver chave configurada ou
 *                a geração falhar (o chamador deve ter um fallback).
 */
function proodonto_get_units_map_url() {
	$api_key = function_exists( 'get_field' ) ? get_field( 'google_maps_api_key', 'option' ) : '';

	if ( ! $api_key ) {
		return '';
	}

	$units = proodonto_get_units();
	// O hash inclui a "receita" visual (zoom, tamanho...), não só os
	// endereços — assim, qualquer ajuste feito em
	// proodonto_units_map_recipe() invalida o cache sozinho, sem precisar
	// clicar em "Forçar nova geração" toda vez que alguém mexer no código.
	$hash = md5( wp_json_encode( wp_list_pluck( $units, 'address' ) ) . wp_json_encode( proodonto_units_map_recipe() ) );

	$cache      = get_option( 'proodonto_units_map_cache' );
	$upload_dir = wp_upload_dir();

	if ( is_array( $cache ) && ! empty( $cache['hash'] ) && ! empty( $cache['file'] ) && $cache['hash'] === $hash ) {
		$path = $upload_dir['basedir'] . '/proodonto/' . $cache['file'];
		if ( file_exists( $path ) ) {
			return $upload_dir['baseurl'] . '/proodonto/' . $cache['file'];
		}
	}

	// Já falhou recentemente? Não tenta de novo a cada visita — só depois de 1h.
	if ( get_transient( 'proodonto_units_map_failed' ) ) {
		return '';
	}

	$result = proodonto_generate_units_map( $units, $api_key, $hash );

	if ( ! $result ) {
		set_transient( 'proodonto_units_map_failed', 1, HOUR_IN_SECONDS );
	}

	return $result;
}

/**
 * Parâmetros visuais do mapa — numa função à parte pra ficarem fáceis de
 * ajustar (e pra qualquer mudança aqui invalidar o cache sozinha, ver
 * proodonto_get_units_map_url()).
 *
 * `zoom` é fixo de propósito, em vez de deixar o Google auto-ajustar pelos
 * `markers`: o auto-fit encaixa só os PONTOS geográficos das unidades,
 * sem reservar espaço pro desenho do pin em si (que se estende bem acima
 * do ponto) — resultado: pins perto da borda saem cortados. Um zoom fixo,
 * um pouco mais "aberto" que o auto-fit, resolve dando margem de sobra.
 * Se ainda cortar (aumente a margem) ou as unidades ficarem pequenas
 * demais / muito espaçadas (diminua a margem), ajuste este número —
 * quanto menor, mais afastado/com mais margem; quanto maior, mais perto.
 */
function proodonto_units_map_recipe() {
	return array(
		'size'    => '640x400',
		'scale'   => '2',
		'maptype' => 'roadmap',
		'zoom'    => 9,
	);
}

/**
 * Faz o único request à Static Maps API, salva a imagem localmente e
 * atualiza o cache. Só é chamado de dentro de proodonto_get_units_map_url()
 * quando o cache está ausente/desatualizado.
 */
function proodonto_generate_units_map( $units, $api_key, $hash ) {
	$query        = proodonto_units_map_recipe();
	$query['key'] = $api_key;

	$query_string = '';
	foreach ( $query as $param => $value ) {
		$query_string .= $param . '=' . rawurlencode( $value ) . '&';
	}

	foreach ( $units as $index => $unit ) {
		$query_string .= 'markers=' . rawurlencode(
			sprintf( 'color:0x049da5|label:%d|%s', $index + 1, $unit['address'] )
		) . '&';
	}

	$url = 'https://maps.googleapis.com/maps/api/staticmap?' . rtrim( $query_string, '&' );

	$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return '';
	}

	$body         = wp_remote_retrieve_body( $response );
	$content_type = (string) wp_remote_retrieve_header( $response, 'content-type' );

	if ( ! $body || 0 !== strpos( $content_type, 'image' ) ) {
		return '';
	}

	$upload_dir = wp_upload_dir();
	$target_dir = $upload_dir['basedir'] . '/proodonto';

	if ( ! file_exists( $target_dir ) ) {
		wp_mkdir_p( $target_dir );
	}

	$filename = 'units-map-' . substr( $hash, 0, 12 ) . '.png';

	if ( false === file_put_contents( $target_dir . '/' . $filename, $body ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return '';
	}

	// Remove o arquivo do hash anterior, se existir, pra não acumular lixo.
	$previous = get_option( 'proodonto_units_map_cache' );
	if ( is_array( $previous ) && ! empty( $previous['file'] ) && $previous['file'] !== $filename ) {
		$old_path = $target_dir . '/' . $previous['file'];
		if ( file_exists( $old_path ) ) {
			wp_delete_file( $old_path );
		}
	}

	update_option(
		'proodonto_units_map_cache',
		array(
			'hash' => $hash,
			'file' => $filename,
		),
		false // Não precisa autoload — só é lido no render da Home.
	);

	delete_transient( 'proodonto_units_map_failed' );

	return $upload_dir['baseurl'] . '/proodonto/' . $filename;
}

/**
 * Botão "Forçar nova geração" logo abaixo do campo da API key nas Opções
 * do Tema — útil pra confirmar que a chave funciona ou pra regerar depois
 * de trocar um endereço sem esperar o carregamento normal da Home.
 */
add_action( 'acf/render_field/name=google_maps_api_key', function () {
	$cache = get_option( 'proodonto_units_map_cache' );
	$url   = wp_nonce_url( admin_url( 'admin-post.php?action=proodonto_regenerate_units_map' ), 'proodonto_regenerate_units_map' );

	echo '<p class="description">';
	if ( is_array( $cache ) && ! empty( $cache['file'] ) ) {
		esc_html_e( 'Mapa já gerado e em cache — não consome mais cota da API até os endereços mudarem.', 'proodonto' );
	} else {
		esc_html_e( 'Mapa ainda não gerado. Será criado no próximo carregamento da Home, se a chave acima for válida.', 'proodonto' );
	}
	echo ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Forçar nova geração', 'proodonto' ) . '</a></p>';
} );

add_action( 'admin_post_proodonto_regenerate_units_map', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'proodonto' ) );
	}

	check_admin_referer( 'proodonto_regenerate_units_map' );

	delete_option( 'proodonto_units_map_cache' );
	delete_transient( 'proodonto_units_map_failed' );

	$referer = wp_get_referer();
	wp_safe_redirect( $referer ? $referer : admin_url() );
	exit;
} );
