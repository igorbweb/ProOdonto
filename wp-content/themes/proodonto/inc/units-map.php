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
 * Uma unidade de proodonto_get_units(), pelo mesmo slug das páginas
 * /aracaju/, /lagarto/, /simao-dias/ (sanitize_title() do nome) — usado
 * pelo mapa individual (proodonto_get_unit_map_url()) e por quem mais
 * precisar dos dados (nome, endereço) de uma unidade a partir do slug da
 * página atual, sem duplicar o laço de busca em cada lugar.
 *
 * @return array|null Item de proodonto_get_units(), ou null se o slug não
 *                     corresponder a nenhuma unidade.
 */
function proodonto_get_unit_by_slug( $slug ) {
	foreach ( proodonto_get_units() as $unit ) {
		if ( sanitize_title( $unit['name'] ) === $slug ) {
			return $unit;
		}
	}

	return null;
}

/**
 * Mapa estático INDIVIDUAL de uma unidade (um pin só, zoom bem mais
 * "chegado" que o mapa combinado de proodonto_get_units_map_url(), que
 * precisa enquadrar os 3 pins espalhados pelo estado). Usado na Página de
 * Vendas (page-vendas.php) quando ela está sendo usada como landing page
 * de uma unidade específica — ver proodonto_get_unit_map_url_for_page().
 *
 * Mesmo princípio de cache em disco do mapa combinado (mesmo arquivo
 * `inc/units-map.php`, mesma pasta wp-content/uploads/proodonto/, mesmo
 * cache por HASH do endereço + "receita" visual, mesmo transient de 1h
 * bloqueando novas tentativas se uma geração falhar) — só a chave da
 * unidade entra no hash/nome do arquivo, pra cada unidade ter o seu.
 *
 * @param string $slug Slug da unidade (aracaju, lagarto, simao-dias —
 *                      mesmo slug das respectivas páginas).
 * @return string URL da imagem, ou '' se a unidade não existir, não
 *                houver chave configurada, ou a geração falhar.
 */
function proodonto_get_unit_map_url( $slug ) {
	$unit = proodonto_get_unit_by_slug( $slug );

	if ( ! $unit ) {
		return '';
	}

	$api_key = function_exists( 'get_field' ) ? get_field( 'google_maps_api_key', 'option' ) : '';

	if ( ! $api_key ) {
		return '';
	}

	$hash = md5( $unit['address'] . wp_json_encode( proodonto_unit_map_recipe() ) );

	$all_cache  = get_option( 'proodonto_unit_maps_cache' );
	$all_cache  = is_array( $all_cache ) ? $all_cache : array();
	$cache      = isset( $all_cache[ $slug ] ) ? $all_cache[ $slug ] : null;
	$upload_dir = wp_upload_dir();

	if ( is_array( $cache ) && ! empty( $cache['hash'] ) && ! empty( $cache['file'] ) && $cache['hash'] === $hash ) {
		$path = $upload_dir['basedir'] . '/proodonto/' . $cache['file'];
		if ( file_exists( $path ) ) {
			return $upload_dir['baseurl'] . '/proodonto/' . $cache['file'];
		}
	}

	// Já falhou recentemente? Não tenta de novo a cada visita — só depois de 1h.
	if ( get_transient( 'proodonto_unit_map_failed_' . $slug ) ) {
		return '';
	}

	$result = proodonto_generate_unit_map( $unit, $slug, $api_key, $hash );

	if ( ! $result ) {
		set_transient( 'proodonto_unit_map_failed_' . $slug, 1, HOUR_IN_SECONDS );
	}

	return $result;
}

/**
 * Slug da unidade correspondente à página atual (aracaju, lagarto,
 * simao-dias), ou '' se a página atual não for nenhuma delas — usado pela
 * Página de Vendas pra decidir entre o mapa individual da unidade e o
 * mapa combinado de sempre (ver page-vendas.php).
 */
function proodonto_get_current_unit_slug() {
	$unit_slugs = array( 'aracaju', 'lagarto', 'simao-dias' );
	$slug       = get_post_field( 'post_name', get_queried_object_id() );

	return in_array( $slug, $unit_slugs, true ) ? $slug : '';
}

/**
 * Parâmetros visuais do mapa individual — zoom bem mais "chegado" que
 * proodonto_units_map_recipe() (aquele precisa enquadrar 3 pins
 * espalhados pelo estado; este é um pin só, no endereço exato).
 */
function proodonto_unit_map_recipe() {
	return array(
		'size'    => '640x400',
		'scale'   => '2',
		'maptype' => 'roadmap',
		'zoom'    => 15,
	);
}

/**
 * Faz o request à Static Maps API pra UMA unidade, salva a imagem em
 * wp-content/uploads/proodonto/ e atualiza o cache dela dentro da opção
 * `proodonto_unit_maps_cache` (array indexado por slug — um cache por
 * unidade, mesma opção pras 3). Só é chamado de dentro de
 * proodonto_get_unit_map_url() quando o cache está ausente/desatualizado.
 */
function proodonto_generate_unit_map( $unit, $slug, $api_key, $hash ) {
	$query        = proodonto_unit_map_recipe();
	$query['key'] = $api_key;

	$query_string = '';
	foreach ( $query as $param => $value ) {
		$query_string .= $param . '=' . rawurlencode( $value ) . '&';
	}

	$query_string .= 'markers=' . rawurlencode( 'color:0x049da5|' . $unit['address'] ) . '&';

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

	$filename = 'unit-map-' . $slug . '-' . substr( $hash, 0, 12 ) . '.png';

	if ( false === file_put_contents( $target_dir . '/' . $filename, $body ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return '';
	}

	// Remove o arquivo do hash anterior dessa unidade, se existir, pra não acumular lixo.
	$all_cache = get_option( 'proodonto_unit_maps_cache' );
	$all_cache = is_array( $all_cache ) ? $all_cache : array();
	$previous  = isset( $all_cache[ $slug ] ) ? $all_cache[ $slug ] : null;

	if ( is_array( $previous ) && ! empty( $previous['file'] ) && $previous['file'] !== $filename ) {
		$old_path = $target_dir . '/' . $previous['file'];
		if ( file_exists( $old_path ) ) {
			wp_delete_file( $old_path );
		}
	}

	$all_cache[ $slug ] = array(
		'hash' => $hash,
		'file' => $filename,
	);

	update_option( 'proodonto_unit_maps_cache', $all_cache, false ); // Não precisa autoload.

	delete_transient( 'proodonto_unit_map_failed_' . $slug );

	return $upload_dir['baseurl'] . '/proodonto/' . $filename;
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
