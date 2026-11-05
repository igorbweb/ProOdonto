<?php
/**
 * Helpers usados pelos templates.
 *
 * Regra do tema: TODO texto de conteúdo vem do WordPress (ACF, título,
 * editor). O PHP só tem microcopy de interface (aria-label, "WhatsApp",
 * "Rota", "Menu"...) via __(), traduzível. Campo vazio = elemento fora do
 * DOM — nenhum texto inventado vaza para produção.
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------------------------------------
 * Campos
 * -------------------------------------------------------------------- */

/** get_field() seguro (ACF desligado não derruba o site). */
function proodonto_field( $name, $post_id = false ) {
	return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : null;
}

/** Campo das Opções do Tema (página "PróOdonto"). */
function proodonto_opt( $name ) {
	return proodonto_field( $name, 'option' );
}

/** Lista (repeater/relationship) sempre como array. */
function proodonto_rows( $name, $post_id = false ) {
	$rows = proodonto_field( $name, $post_id );
	return is_array( $rows ) ? array_values( $rows ) : array();
}

/* -----------------------------------------------------------------------
 * Texto
 * -------------------------------------------------------------------- */

/**
 * Troca {cidade} e {bairro} pelos dados da unidade — permite um texto só
 * nas Opções ("Agendar em {cidade}") servir às 3 páginas de unidade.
 */
function proodonto_tokens( $text, $unit = null ) {
	$text = (string) $text;
	if ( false === strpos( $text, '{' ) ) {
		return $text;
	}
	$unit = $unit ? $unit : ( function_exists( 'proodonto_get_current_unit' ) ? proodonto_get_current_unit() : null );

	if ( ! $unit ) {
		// Fora de página de unidade, "Avaliações · {cidade}" vira "Avaliações".
		$text = preg_replace( '/\s*[·–-]\s*\{(cidade|bairro)\}|\s+(em|de)\s+\{cidade\}/u', '', $text );
	}

	return strtr(
		$text,
		array(
			'{cidade}' => $unit ? $unit['name'] : '',
			'{bairro}' => $unit ? $unit['bairro'] : '',
		)
	);
}

/**
 * Título com destaque: o editor escreve *palavra* e ela sai em <em>
 * (teal no fundo claro, teal-claro no marinho — sem itálico, regra da
 * marca). Tudo o mais é escapado.
 */
function proodonto_rich( $text, $unit = null ) {
	$text = esc_html( proodonto_tokens( $text, $unit ) );
	$text = preg_replace( '/\*([^*]+)\*/u', '<em>$1</em>', $text );

	return nl2br( $text, false );
}

/** Mesmo texto sem marcação nenhuma (para alt, aria-label, schema). */
function proodonto_plain( $text, $unit = null ) {
	return trim( str_replace( '*', '', proodonto_tokens( (string) $text, $unit ) ) );
}

/* -----------------------------------------------------------------------
 * Componentes
 * -------------------------------------------------------------------- */

/**
 * Os 3 botões do sistema (pílula com seta num círculo):
 * primary (teal cheio) · light (branco, sobre escuro) · outline (contorno).
 * `ghost` = contorno claro sobre fundo escuro (hero do tratamento).
 *
 * @param array $args {
 *   @type string $label   Texto (obrigatório — vazio não imprime nada).
 *   @type string $url     Destino (obrigatório).
 *   @type string $variant primary|light|outline|ghost.
 *   @type bool   $small   Versão 48px.
 *   @type bool   $block   Largura total (celular).
 *   @type string $icon    arrow|whatsapp|route|external.
 *   @type string $class   Classes extras.
 *   @type array  $attrs   Atributos extras (data-cta, aria-label...).
 * }
 */
function proodonto_button( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'label'   => '',
			'url'     => '',
			'variant' => 'primary',
			'small'   => false,
			'block'   => false,
			'icon'    => 'arrow',
			'class'   => '',
			'attrs'   => array(),
		)
	);

	if ( '' === trim( (string) $args['label'] ) || '' === trim( (string) $args['url'] ) ) {
		return '';
	}

	$classes = array( 'btn', 'btn--' . $args['variant'] );
	if ( $args['small'] ) {
		$classes[] = 'btn--sm';
	}
	if ( $args['block'] ) {
		$classes[] = 'btn--block';
	}
	if ( $args['class'] ) {
		$classes[] = $args['class'];
	}

	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s><span class="btn__label">%4$s</span><span class="btn__ic">%5$s</span></a>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $args['url'] ),
		proodonto_attrs( $args['attrs'] ),
		esc_html( proodonto_plain( $args['label'] ) ),
		proodonto_icon( $args['icon'], $args['small'] ? 16 : 18 )
	);
}

/** Atributos HTML a partir de um array (valores escapados). */
function proodonto_attrs( $attrs ) {
	$out = '';
	foreach ( (array) $attrs as $key => $value ) {
		if ( null === $value || false === $value ) {
			continue;
		}
		$out .= ' ' . esc_attr( $key ) . ( true === $value ? '' : '="' . esc_attr( $value ) . '"' );
	}
	return $out;
}

/** Círculo com seta no fim de todo cartão clicável. */
function proodonto_chip( $class = '' ) {
	return '<span class="chip ' . esc_attr( $class ) . '" aria-hidden="true">' . proodonto_icon( 'arrow', 18 ) . '</span>';
}

/**
 * Cabeçalho de seção: eyebrow (Acre, caixa alta) + H2 (Coolvetica).
 * Nada impresso se os dois estiverem vazios.
 */
function proodonto_section_head( $eyebrow, $title, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'id'    => '',
			'tag'   => 'h2',
			'class' => '',
			'size'  => '',
			'unit'  => null,
		)
	);

	if ( ! $eyebrow && ! $title ) {
		return;
	}

	echo '<div class="shead ' . esc_attr( $args['class'] ) . '">';
	if ( $eyebrow ) {
		echo '<p class="eyebrow">' . esc_html( proodonto_plain( $eyebrow, $args['unit'] ) ) . '</p>';
	}
	if ( $title ) {
		printf(
			'<%1$s%2$s class="h2 %3$s">%4$s</%1$s>',
			tag_escape( $args['tag'] ),
			$args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : '',
			esc_attr( $args['size'] ),
			proodonto_rich( $title, $args['unit'] ) // Escapado em proodonto_rich().
		);
	}
	echo '</div>';
}

/**
 * Imagem da biblioteca com srcset/sizes do core.
 *
 * @param int|array $image  ID ou array do ACF.
 * @param string    $size   Tamanho registrado.
 * @param array     $attrs  class, sizes, loading, fetchpriority, alt...
 */
function proodonto_image( $image, $size = 'large', $attrs = array() ) {
	$id = is_array( $image ) ? (int) ( $image['ID'] ?? $image['id'] ?? 0 ) : (int) $image;
	if ( ! $id ) {
		return '';
	}

	$attrs = wp_parse_args( $attrs, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	if ( isset( $attrs['fetchpriority'] ) && 'high' === $attrs['fetchpriority'] ) {
		$attrs['loading'] = 'eager';
		// Fora do lazy-load de plugins (EWWW, LiteSpeed...): é o LCP.
		$attrs['data-skip-lazy'] = '1';
		$attrs['class']          = trim( ( $attrs['class'] ?? '' ) . ' skip-lazy no-lazy' );
	}

	return wp_get_attachment_image( $id, $size, false, $attrs );
}

/**
 * Bloco de foto: a imagem, ou — sem imagem — o bloco marinho listrado do
 * canvas (decorativo, sem texto), para o layout não desmontar enquanto a
 * sessão de fotos não acontece.
 */
function proodonto_photo( $image, $size = 'proodonto-card', $class = '', $attrs = array() ) {
	$img = proodonto_image( $image, $size, wp_parse_args( $attrs, array( 'class' => 'photo__img' ) ) );

	return '<span class="photo ' . esc_attr( $class ) . ( $img ? ' photo--has-img' : '' ) . '"' . ( $img ? '' : ' aria-hidden="true"' ) . '>' . $img . '</span>';
}

/** Selo "★★★★★ 4,9 a 5,0 · +1.190 avaliações no Google" (Opções). */
function proodonto_rating_pill( $class = '', $long = true ) {
	$nota  = proodonto_opt( 'nota_resumo' );
	$total = proodonto_opt( 'total_avaliacoes' );
	if ( ! $nota && ! $total ) {
		return '';
	}

	$label = $total ? ( $long ? sprintf( /* translators: %s: total */ __( '%s avaliações no Google', 'proodonto' ), $total ) : sprintf( __( '%s no Google', 'proodonto' ), $total ) ) : '';

	return sprintf(
		'<p class="rating-pill %1$s"><span class="stars" aria-hidden="true">★★★★★</span><span>%2$s%3$s%4$s</span></p>',
		esc_attr( $class ),
		$nota ? '<strong>' . esc_html( $nota ) . '</strong>' : '',
		( $nota && $label ) ? ' · ' : '',
		esc_html( $label )
	);
}

/* -----------------------------------------------------------------------
 * Navegação e páginas especiais
 * -------------------------------------------------------------------- */

/** Página que usa um template (cache por request). */
function proodonto_page_by_template( $template ) {
	static $cache = array();
	if ( array_key_exists( $template, $cache ) ) {
		return $cache[ $template ];
	}

	$pages              = get_pages(
		array(
			'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $template,           // phpcs:ignore WordPress.DB.SlowDBQuery
			'post_status' => 'publish',
			'number'      => 1,
		)
	);
	$cache[ $template ] = $pages ? $pages[0] : null;

	return $cache[ $template ];
}

/** URL do hub /servicos/. */
function proodonto_hub_url() {
	$hub = proodonto_page_by_template( 'page-servicos.php' );
	if ( ! $hub ) {
		$hub = get_page_by_path( 'servicos' );
	}
	return ( $hub && 'publish' === $hub->post_status ) ? get_permalink( $hub ) : '';
}

/** URL da página do blog (Configurações → Leitura). */
function proodonto_blog_url() {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? get_permalink( $id ) : '';
}

/**
 * Âncora das unidades: na Home/Hub/Tratamento é a seção da própria
 * página; nas demais, a seção da Home.
 */
function proodonto_units_anchor() {
	if ( is_page_template( array( 'page-home.php', 'page-servicos.php' ) ) || is_singular( 'servico' ) ) {
		return '#unidades';
	}
	return home_url( '/#unidades' );
}

/**
 * Trilha visível (Início / … / atual). O BreadcrumbList do schema fica
 * com o Yoast (ver inc/schema.php — o hub é inserido lá também).
 *
 * @return array<int, array{label:string, url:string}>
 */
function proodonto_breadcrumb_items() {
	$items = array( array( 'label' => __( 'Início', 'proodonto' ), 'url' => home_url( '/' ) ) );

	if ( is_singular( 'servico' ) ) {
		$hub = proodonto_page_by_template( 'page-servicos.php' );
		if ( $hub ) {
			$items[] = array( 'label' => get_the_title( $hub ), 'url' => get_permalink( $hub ) );
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_singular( 'post' ) ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog ) {
			$items[] = array( 'label' => get_the_title( $blog ), 'url' => get_permalink( $blog ) );
		}
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = array( 'label' => $cats[0]->name, 'url' => get_category_link( $cats[0] ) );
		}
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = array( 'label' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) );
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_home() || is_archive() || is_search() ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog && ! is_home() ) {
			$items[] = array( 'label' => get_the_title( $blog ), 'url' => get_permalink( $blog ) );
		}
		if ( is_home() && $blog ) {
			$items[] = array( 'label' => get_the_title( $blog ), 'url' => '' );
		} elseif ( is_category() || is_tag() ) {
			$items[] = array( 'label' => single_term_title( '', false ), 'url' => '' );
		} elseif ( is_search() ) {
			$items[] = array( 'label' => sprintf( __( 'Busca: %s', 'proodonto' ), get_search_query() ), 'url' => '' );
		}
	}

	return $items;
}

function proodonto_breadcrumbs( $class = '' ) {
	$items = proodonto_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}

	echo '<nav class="crumbs ' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Você está em', 'proodonto' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i > 0 ) {
			echo '<li aria-hidden="true" class="crumbs__sep">/</li>';
		}
		if ( $item['url'] && $i !== $last ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
		} elseif ( $item['url'] ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
		} else {
			printf( '<li aria-current="page">%s</li>', esc_html( $item['label'] ) );
		}
	}
	echo '</ol></nav>';
}

/* -----------------------------------------------------------------------
 * Blog
 * -------------------------------------------------------------------- */

/** Minutos de leitura (200 palavras/min). */
function proodonto_reading_time( $post_id = null ) {
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ? $post_id : get_the_ID() ) ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/** Posts da mesma categoria (fallback: mais recentes). */
function proodonto_related_posts( $post_id = null, $number = 3 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$args    = array(
		'post_type'           => 'post',
		'posts_per_page'      => $number,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$cats = wp_get_post_categories( $post_id );
	if ( $cats ) {
		$args['category__in'] = $cats;
	}
	$query = new WP_Query( $args );

	if ( $query->post_count < $number && $cats ) {
		unset( $args['category__in'] );
		$args['post__not_in'] = array_merge( array( $post_id ), wp_list_pluck( $query->posts, 'ID' ) );
		$args['posts_per_page'] = $number - $query->post_count;
		$extra = new WP_Query( $args );
		$query->posts = array_merge( $query->posts, $extra->posts );
	}

	return $query->posts;
}

/** Iniciais para o avatar redondo ("Alisson Matheus" → "AM"). */
function proodonto_initials( $name ) {
	$name  = preg_replace( '/^(dr\.?|dra\.?)\s+/iu', '', trim( (string) $name ) );
	$parts = preg_split( '/\s+/u', $name );
	$first = $parts ? mb_substr( $parts[0], 0, 1 ) : '';
	$last  = count( $parts ) > 1 ? mb_substr( $parts[1], 0, 1 ) : '';
	return mb_strtoupper( $first . $last );
}

/* -----------------------------------------------------------------------
 * Logo e menu
 * -------------------------------------------------------------------- */

/**
 * Logo: Opções (claro/escuro) → Logo do Personalizar → nome do site.
 *
 * @param string $variant dark (cabeçalho, fundo claro) | light (rodapé).
 */
function proodonto_logo( $variant = 'dark' ) {
	$id = (int) proodonto_opt( 'light' === $variant ? 'logo_claro' : 'logo' );
	if ( ! $id && 'light' !== $variant ) {
		$id = (int) get_theme_mod( 'custom_logo' );
	}
	if ( $id ) {
		return wp_get_attachment_image(
			$id,
			'medium',
			false,
			array(
				'class'    => 'logo__img',
				'alt'      => get_bloginfo( 'name' ),
				'loading'  => 'eager',
				'decoding' => 'async',
			)
		);
	}
	return '<span class="logo__text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
}

/**
 * Menu principal sem menu cadastrado: os destinos que o canvas mostra,
 * montados com dados do WP (páginas que existem de verdade).
 *
 * @return array<int, array{label:string,url:string,current:bool}>
 */
function proodonto_default_nav_items() {
	$items = array();
	$home  = home_url( '/' );
	$items[] = array( 'label' => __( 'Unidades', 'proodonto' ), 'url' => $home . '#unidades', 'current' => false );
	$hub     = proodonto_hub_url();
	if ( $hub ) {
		$items[] = array( 'label' => __( 'Tratamentos', 'proodonto' ), 'url' => $hub, 'current' => is_singular( 'servico' ) || is_page_template( 'page-servicos.php' ) );
	}
	$items[] = array( 'label' => __( 'Resultados', 'proodonto' ), 'url' => $home . '#resultados', 'current' => false );
	$items[] = array( 'label' => __( 'Avaliações', 'proodonto' ), 'url' => $home . '#avaliacoes', 'current' => false );
	$sobre   = get_page_by_path( 'sobre' );
	if ( $sobre && 'publish' === $sobre->post_status ) {
		$items[] = array( 'label' => __( 'Sobre', 'proodonto' ), 'url' => get_permalink( $sobre ), 'current' => is_page( $sobre->ID ) );
	}
	$blog = proodonto_blog_url();
	if ( $blog ) {
		$items[] = array( 'label' => __( 'Blog', 'proodonto' ), 'url' => $blog, 'current' => is_home() || is_singular( 'post' ) || is_category() );
	}
	return $items;
}

/** Âncoras do cabeçalho nas páginas de unidade ("Nesta página"). */
function proodonto_unit_nav_items() {
	return array(
		array( 'label' => __( 'Como chegar', 'proodonto' ), 'url' => '#onde', 'current' => true ),
		array( 'label' => __( 'Tratamentos', 'proodonto' ), 'url' => '#tratamentos', 'current' => false ),
		array( 'label' => __( 'Avaliações', 'proodonto' ), 'url' => '#avaliacoes', 'current' => false ),
		array( 'label' => __( 'A clínica', 'proodonto' ), 'url' => '#clinica', 'current' => false ),
		array( 'label' => __( 'Outras unidades', 'proodonto' ), 'url' => '#outras', 'current' => false ),
	);
}

/** Imprime itens de menu (lista de links) — usado no cabeçalho e no menu do celular. */
function proodonto_nav_links( $items, $class = 'pillnav__a' ) {
	foreach ( $items as $item ) {
		printf(
			'<li><a class="%1$s%2$s" href="%3$s"%4$s>%5$s</a></li>',
			esc_attr( $class ),
			$item['current'] ? ' is-current' : '',
			esc_url( $item['url'] ),
			$item['current'] && 0 !== strpos( $item['url'], '#' ) ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
}

/**
 * Menu do painel (se houver) como lista de itens simples — 1 nível, que é
 * o que o desenho comporta.
 */
function proodonto_menu_items( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$menu_items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $menu_items ) {
		return array();
	}
	_wp_menu_item_classes_by_context( $menu_items );
	$items = array();
	foreach ( $menu_items as $mi ) {
		if ( (int) $mi->menu_item_parent ) {
			continue;
		}
		$classes = (array) $mi->classes;
		$items[] = array(
			'label'   => $mi->title,
			'url'     => $mi->url,
			'current' => in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) || in_array( 'current_page_parent', $classes, true ),
		);
	}
	return $items;
}

/* -----------------------------------------------------------------------
 * Barra fixa do celular
 * -------------------------------------------------------------------- */

/**
 * Define a barra fixa da página (impressa no footer.php). Aparece no
 * celular só depois que o elemento [data-sticky-trigger] sai da tela.
 *
 * @param array $args label, url, icon, attrs, alt_url, alt_label, alt_icon.
 */
function proodonto_set_sticky( $args ) {
	$GLOBALS['proodonto_sticky'] = $args;
}

/** Faixa de unidades do resultado: prioriza os casos da unidade. */
function proodonto_results_for( $unit_slug = '' ) {
	$rows = array_values(
		array_filter(
			proodonto_rows( 'resultados', 'option' ),
			static function ( $r ) {
				return ! empty( $r['imagem'] );
			}
		)
	);
	if ( $unit_slug ) {
		usort(
			$rows,
			static function ( $a, $b ) use ( $unit_slug ) {
				return (int) ( ( $b['unidade'] ?? '' ) === $unit_slug ) - (int) ( ( $a['unidade'] ?? '' ) === $unit_slug );
			}
		);
	}
	return $rows;
}

/** Avaliações globais; com unidade, só as dela (sem avaliações dela = todas). */
function proodonto_reviews_for( $unit_slug = '' ) {
	$rows = array_values(
		array_filter(
			proodonto_rows( 'avaliacoes', 'option' ),
			static function ( $r ) {
				return ! empty( $r['texto'] );
			}
		)
	);
	if ( $unit_slug ) {
		$own = array_values(
			array_filter(
				$rows,
				static function ( $r ) use ( $unit_slug ) {
					return ( $r['unidade'] ?? '' ) === $unit_slug;
				}
			)
		);
		return $own ? $own : $rows;
	}
	return $rows;
}

/** Nome da unidade a partir do slug salvo numa avaliação/resultado. */
function proodonto_unit_name( $slug ) {
	$u = $slug ? proodonto_get_unit_by_slug( $slug ) : null;
	return $u ? $u['name'] : '';
}

/**
 * Imagem só para telas largas: abaixo do corte, o <picture> entrega um
 * GIF de 1px — o celular não baixa a foto (no celular o LCP é o H1).
 *
 * @param string $max_hidden Largura máxima em que a foto NÃO aparece.
 */
function proodonto_wide_image( $id, $size, $attrs = array(), $max_hidden = '767px' ) {
	$img = proodonto_image( $id, $size, $attrs );
	if ( ! $img ) {
		return '';
	}
	return '<picture><source media="(max-width: ' . esc_attr( $max_hidden ) . ')" srcset="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">' . $img . '</picture>';
}
