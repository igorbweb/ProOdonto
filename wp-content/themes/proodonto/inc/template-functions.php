<?php
/**
 * Helpers usados nos templates.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Trunca o excerpt automático em menos palavras (mobile-first: cards menores).
 */
add_filter( 'excerpt_length', function () {
	return 20;
} );

add_filter( 'excerpt_more', function () {
	return '…';
} );

/**
 * True quando a página atual usa o template "Página de Vendas"
 * (page-vendas.php) — detecta tanto quem escolheu o template pelos
 * Atributos da página quanto quem só deu o slug "vendas" à página (ver
 * inc/enqueue.php, mesma lógica). Usado pelo footer pra decidir se usa o
 * custom field "cta_url" da página em vez do WhatsApp padrão do tema.
 */
function proodonto_is_vendas_page( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();

	if ( ! $post_id ) {
		return false;
	}

	return 'page-vendas.php' === get_page_template_slug( $post_id )
		|| 'vendas' === get_post_field( 'post_name', $post_id );
}

/**
 * Páginas das 3 unidades, puxadas manualmente pelo slug — usado pelo
 * dropdown "Unidades" do header (ver header.php). Só entram as que
 * realmente existem e estão publicadas, pra nunca gerar link morto no
 * menu se alguma página ainda não tiver sido criada.
 *
 * O rótulo usa sempre o nome curto da cidade, não o título da página
 * (ex.: "Implantes e reabilitação oral em Aracaju" — pensado pra
 * SEO/aba do navegador, longo demais pra um item de menu).
 */
function proodonto_get_unit_nav_pages() {
	$cities = array(
		'aracaju'    => __( 'Aracaju', 'proodonto' ),
		'lagarto'    => __( 'Lagarto', 'proodonto' ),
		'simao-dias' => __( 'Simão Dias', 'proodonto' ),
	);

	$pages = array();

	foreach ( $cities as $slug => $label ) {
		$page = get_page_by_path( $slug );

		if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
			continue;
		}

		$pages[] = array(
			'label' => $label,
			'url'   => get_permalink( $page ),
		);
	}

	return $pages;
}

/**
 * Paginação simples, reaproveitada em archive.php / search.php / index.php.
 */
function proodonto_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( '&larr; Anterior', 'proodonto' ),
			'next_text' => __( 'Próxima &rarr;', 'proodonto' ),
			'type'      => 'list',
		)
	);
}

/**
 * Adiciona uma classe de contexto no <body> por slug de página, útil para
 * overrides pontuais de CSS sem precisar de um arquivo por página.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( $slug ) {
			$classes[] = 'page-' . $slug;
		}
	}

	return $classes;
} );

/**
 * Tempo estimado de leitura (~200 palavras/minuto), usado em single.php.
 */
function proodonto_reading_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );

	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Envolve toda <table> do conteúdo com um wrapper de rolagem horizontal —
 * as tabelas comparativas da pauta de SEO/GEO (ex.: cirurgia guiada x
 * convencional) têm várias colunas e não podem depender de layout largo
 * fixo sem quebrar em telas estreitas.
 */
add_filter( 'the_content', function ( $content ) {
	if ( false === strpos( $content, '<table' ) ) {
		return $content;
	}

	return preg_replace( '/(<table\b.*?<\/table>)/is', '<div class="table-scroll">$1</div>', $content );
}, 20 );

/**
 * Posts relacionados: mesma categoria do post atual, mais recentes primeiro,
 * excluindo o post atual. Usado em single.php para incentivar o leitor a
 * continuar navegando pelo blog em vez de sair da página.
 */
function proodonto_get_related_posts( $post_id = null, $number = 3 ) {
	$post_id    = $post_id ? $post_id : get_the_ID();
	$categories = wp_get_post_categories( $post_id );

	$args = array(
		'post__not_in'        => array( $post_id ),
		'posts_per_page'      => $number,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $categories ) {
		$args['category__in'] = $categories;
	}

	return new WP_Query( $args );
}

/**
 * Título de página amigável para 404 e busca (usado nos templates).
 */
function proodonto_archive_title() {
	if ( is_search() ) {
		printf( '<h1>%s</h1>', esc_html( sprintf( __( 'Resultados da busca por: %s', 'proodonto' ), get_search_query() ) ) );
	} elseif ( is_404() ) {
		echo '<h1>' . esc_html__( 'Página não encontrada', 'proodonto' ) . '</h1>';
	} else {
		the_archive_title( '<h1>', '</h1>' );
	}
}

/**
 * Sanitiza um fragmento de ícone SVG (ex.: campos ACF "Ícone (SVG)" das
 * seções Marquee/Tratamentos/Passo a passo) antes de ecoar dentro de um
 * <svg>. Esses campos passaram a ser editáveis por quem tem acesso ao
 * wp-admin — antes eram só arrays PHP fixos no código — então, mesmo sendo
 * conteúdo de confiança relativa (mesmo nível de um Editor no editor de
 * blocos), passam por um allowlist de formas SVG básicas em vez de ir para
 * a tela sem nenhum filtro.
 */
function proodonto_sanitize_svg_fragment( $svg ) {
	// fill/stroke por elemento: a maioria dos ícones herda tudo do <svg> pai
	// (fill="none" stroke="currentColor"), mas os ícones de redes sociais do
	// rodapé precisam de formas com fill sólido (ex.: o "balão" do WhatsApp),
	// daí o override por elemento.
	$color_attrs = array( 'fill' => true, 'stroke' => true );

	$allowed = array(
		'path'     => array( 'd' => true ) + $color_attrs,
		'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true ) + $color_attrs,
		'ellipse'  => array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ) + $color_attrs,
		'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true ) + $color_attrs,
		'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ) + $color_attrs,
		'polygon'  => array( 'points' => true ) + $color_attrs,
		'polyline' => array( 'points' => true ) + $color_attrs,
		'g'        => $color_attrs,
	);

	return wp_kses( (string) $svg, $allowed );
}

/**
 * Extrai o ID de um vídeo do YouTube a partir de qualquer formato comum de
 * link (Shorts, watch?v=, youtu.be, embed) — usado pela seção "Shorts" da
 * Home (grupo ACF "Home — Shorts (YouTube)") pra gerar a miniatura e o
 * player sem precisar de nenhuma chave de API.
 *
 * @return string ID do vídeo, ou '' se a URL não bater com nenhum formato conhecido.
 */
function proodonto_get_youtube_id( $url ) {
	if ( ! $url ) {
		return '';
	}

	$patterns = array(
		'~youtube(?:-nocookie)?\.com/shorts/([A-Za-z0-9_-]{6,})~i',
		'~youtube(?:-nocookie)?\.com/embed/([A-Za-z0-9_-]{6,})~i',
		'~youtube\.com/watch\?v=([A-Za-z0-9_-]{6,})~i',
		'~youtu\.be/([A-Za-z0-9_-]{6,})~i',
	);

	foreach ( $patterns as $pattern ) {
		if ( preg_match( $pattern, $url, $matches ) ) {
			return $matches[1];
		}
	}

	return '';
}

/**
 * Miniatura pública do YouTube para um ID de vídeo (sem chave de API —
 * mesmo endereço usado pelo próprio YouTube). "hqdefault" é o único
 * tamanho garantido a existir para todo vídeo enviado (diferente de
 * "maxresdefault", que pode não existir e devolver 404).
 */
function proodonto_get_youtube_thumbnail_url( $video_id ) {
	return $video_id ? sprintf( 'https://i.ytimg.com/vi/%s/hqdefault.jpg', $video_id ) : '';
}
