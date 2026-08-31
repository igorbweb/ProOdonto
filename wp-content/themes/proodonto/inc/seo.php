<?php
/**
 * SEO do tema — funciona em dois modos:
 *
 *   1. SEM Yoast/Rank Math ativo: o tema cuida de tudo sozinho (meta
 *      description, robots, canonical, Open Graph, Twitter Card, JSON-LD
 *      Organization/WebSite/BreadcrumbList + BlogPosting em posts, e
 *      breadcrumbs) — comportamento original deste arquivo.
 *
 *   2. COM Yoast ativo (caso atual do site, plugin `wordpress-seo`
 *      instalado em 2026-08): o Yoast já gera title, meta description,
 *      canonical, robots, Open Graph, Twitter Card, sitemap e o grafo
 *      base (WebPage/WebSite/BreadcrumbList/ImageObject) — então o tema
 *      PARA de imprimir essas mesmas tags (evita duplicação, incluindo
 *      nós JSON-LD com o mesmo @id declarados duas vezes) e passa a:
 *        a) plugar os campos manuais do editor (description/canonical/
 *           noindex, preenchidos no metabox "SEO" abaixo) nos filtros do
 *           próprio Yoast — mesma UI de sempre pro editor, sem tag
 *           duplicada no HTML;
 *        b) acrescentar ao MESMO grafo JSON-LD do Yoast (via filtro
 *           `wpseo_schema_graph`) só os nós exclusivos do tema, que o
 *           Yoast (free) não gera sozinho: Organization enriquecida
 *           (telefone/sameAs/catálogo de tratamentos), Dentist por
 *           unidade, BlogPosting, AboutPage e FAQPage — ver
 *           inc/local-business-schema.php, inc/page-sobre-schema.php e
 *           inc/blocks.php, que continuam usando o mesmo filtro
 *           `proodonto_json_ld_graphs` de sempre.
 *
 * Ver proodonto_yoast_active() e proodonto_bridge_yoast() logo abaixo.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Yoast SEO está instalado e ativo? (define WPSEO_VERSION no boot do
 * plugin). Usado para decidir entre os dois modos descritos acima.
 */
function proodonto_yoast_active() {
	return defined( 'WPSEO_VERSION' );
}

/* -----------------------------------------------------------------------
 * 1. Meta box: Meta description, noindex, canonical override, imagem OG
 * -------------------------------------------------------------------- */
add_action( 'add_meta_boxes', function () {
	foreach ( array( 'post', 'page' ) as $post_type ) {
		add_meta_box(
			'proodonto_seo',
			__( 'SEO', 'proodonto' ),
			'proodonto_render_seo_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}
} );

function proodonto_render_seo_meta_box( $post ) {
	wp_nonce_field( 'proodonto_seo_save', 'proodonto_seo_nonce' );

	$description = get_post_meta( $post->ID, '_proodonto_meta_description', true );
	$noindex     = get_post_meta( $post->ID, '_proodonto_meta_noindex', true );
	$canonical   = get_post_meta( $post->ID, '_proodonto_canonical', true );
	?>
	<p>
		<label for="proodonto_meta_description"><strong><?php esc_html_e( 'Meta description', 'proodonto' ); ?></strong></label><br />
		<textarea id="proodonto_meta_description" name="proodonto_meta_description" rows="3" style="width:100%;" maxlength="160"><?php echo esc_textarea( $description ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Recomendado: até 160 caracteres.', 'proodonto' ); ?></span>
	</p>
	<p>
		<label for="proodonto_canonical"><strong><?php esc_html_e( 'Canonical (opcional)', 'proodonto' ); ?></strong></label><br />
		<input type="url" id="proodonto_canonical" name="proodonto_canonical" style="width:100%;" value="<?php echo esc_attr( $canonical ); ?>" placeholder="https://..." />
	</p>
	<p>
		<label>
			<input type="checkbox" name="proodonto_meta_noindex" value="1" <?php checked( $noindex, '1' ); ?> />
			<?php esc_html_e( 'Não indexar esta página (noindex)', 'proodonto' ); ?>
		</label>
	</p>
	<?php
}

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['proodonto_seo_nonce'] ) || ! wp_verify_nonce( $_POST['proodonto_seo_nonce'], 'proodonto_seo_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['proodonto_meta_description'] ) ) {
		update_post_meta( $post_id, '_proodonto_meta_description', sanitize_textarea_field( wp_unslash( $_POST['proodonto_meta_description'] ) ) );
	}

	if ( isset( $_POST['proodonto_canonical'] ) ) {
		update_post_meta( $post_id, '_proodonto_canonical', esc_url_raw( wp_unslash( $_POST['proodonto_canonical'] ) ) );
	}

	update_post_meta( $post_id, '_proodonto_meta_noindex', isset( $_POST['proodonto_meta_noindex'] ) ? '1' : '' );
} );

/* -----------------------------------------------------------------------
 * 2. Output das tags no <head> — só roda sozinho se NÃO houver Yoast.
 *    Com Yoast ativo, usamos proodonto_bridge_yoast() em vez disso (ver
 *    comentário no topo do arquivo).
 * -------------------------------------------------------------------- */
if ( proodonto_yoast_active() ) {
	proodonto_bridge_yoast();
} else {
	add_action( 'wp_head', 'proodonto_seo_meta_tags', 1 );
}

/**
 * Modo "Yoast ativo": nada de tags/JSON-LD duplicados. Só plugamos os
 * campos manuais do metabox "SEO" nos filtros do Yoast, e acrescentamos
 * ao grafo JSON-LD que o Yoast já imprime os nós exclusivos do tema
 * (Organization enriquecida, Dentist, BlogPosting, AboutPage, FAQPage —
 * ver inc/local-business-schema.php, inc/page-sobre-schema.php,
 * inc/blocks.php, que alimentam o filtro `proodonto_json_ld_graphs`).
 */
function proodonto_bridge_yoast() {
	// Meta description manual (metabox) vence a do Yoast, se preenchida.
	add_filter( 'wpseo_metadesc', function ( $desc ) {
		if ( ! is_singular() ) {
			return $desc;
		}
		$custom = get_post_meta( get_the_ID(), '_proodonto_meta_description', true );
		return $custom ? $custom : $desc;
	} );

	// Canonical manual (metabox) vence o do Yoast, se preenchido.
	add_filter( 'wpseo_canonical', function ( $canonical ) {
		if ( ! is_singular() ) {
			return $canonical;
		}
		$custom = get_post_meta( get_the_ID(), '_proodonto_canonical', true );
		return $custom ? $custom : $canonical;
	} );

	// Noindex manual (metabox) força noindex mesmo que o Yoast não tenha
	// marcado nada — nunca o contrário (não "reindexamos" nada aqui).
	add_filter( 'wpseo_robots_array', function ( $robots ) {
		if ( is_singular() && '1' === get_post_meta( get_the_ID(), '_proodonto_meta_noindex', true ) ) {
			$robots['index'] = 'noindex';
		}
		return $robots;
	} );

	// Acrescenta os nós exclusivos do tema ao MESMO grafo do Yoast, em vez
	// de imprimir um <script type="application/ld+json"> separado.
	add_filter( 'wpseo_schema_graph', function ( $data ) {
		$extra = apply_filters( 'proodonto_json_ld_graphs', array() );
		foreach ( $extra as $node ) {
			$data[] = $node;
		}
		return $data;
	} );
}

function proodonto_seo_meta_tags() {
	$description = proodonto_get_meta_description();
	$noindex     = is_singular() && '1' === get_post_meta( get_the_ID(), '_proodonto_meta_noindex', true );
	$canonical   = proodonto_get_canonical_url();

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	printf( '<meta name="robots" content="%s" />' . "\n", esc_attr( $noindex ? 'noindex, nofollow' : 'index, follow' ) );

	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );
	}

	// Autoria explícita e datas em ISO 8601 — sinal de frescor/autoridade
	// tanto para buscadores tradicionais quanto para IAs generativas.
	if ( is_singular( 'post' ) ) {
		printf( '<meta name="author" content="%s" />' . "\n", esc_attr( get_the_author() ) );
		printf( '<meta property="article:published_time" content="%s" />' . "\n", esc_attr( get_the_date( 'c' ) ) );
		printf( '<meta property="article:modified_time" content="%s" />' . "\n", esc_attr( get_the_modified_date( 'c' ) ) );

		$proodonto_author_url = get_author_posts_url( (int) get_the_author_meta( 'ID' ) );
		if ( $proodonto_author_url ) {
			printf( '<meta property="article:author" content="%s" />' . "\n", esc_url( $proodonto_author_url ) );
		}

		foreach ( get_the_category() as $proodonto_cat ) {
			printf( '<meta property="article:section" content="%s" />' . "\n", esc_attr( $proodonto_cat->name ) );
		}

		$proodonto_post_tags = get_the_tags();
		if ( $proodonto_post_tags ) {
			foreach ( $proodonto_post_tags as $proodonto_tag ) {
				printf( '<meta property="article:tag" content="%s" />' . "\n", esc_attr( $proodonto_tag->name ) );
			}
		}
	}

	proodonto_open_graph_tags( $description, $canonical );
	proodonto_json_ld();
}

function proodonto_get_meta_description() {
	if ( is_singular() ) {
		$custom = get_post_meta( get_the_ID(), '_proodonto_meta_description', true );
		if ( $custom ) {
			return $custom;
		}

		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_trim_words( wp_strip_all_tags( $excerpt ), 30, '…' );
		}
	}

	if ( is_front_page() || is_home() ) {
		return get_bloginfo( 'description' );
	}

	return '';
}

function proodonto_get_canonical_url() {
	if ( is_singular() ) {
		$custom = get_post_meta( get_the_ID(), '_proodonto_canonical', true );
		if ( $custom ) {
			return $custom;
		}
		return get_permalink();
	}

	if ( is_home() || is_front_page() ) {
		return home_url( '/' );
	}

	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

function proodonto_open_graph_tags( $description, $canonical ) {
	$title = wp_get_document_title();
	$image = '';

	if ( is_singular() && has_post_thumbnail() ) {
		$image_data = wp_get_attachment_image_src( get_post_thumbnail_id(), 'proodonto-hero' );
		$image      = $image_data ? $image_data[0] : '';
	}

	// Sem featured image (caso da Home e da Página de Vendas, que usam banner
	// via ACF em vez do featured image nativo) ou em páginas não-singulares
	// (arquivos, blog index): cai no logo do site em vez de sair sem
	// og:image — compartilhamentos no WhatsApp/redes sempre saem com imagem.
	if ( ! $image ) {
		$image = proodonto_get_logo_url();
	}

	$tags = array(
		'og:site_name' => get_bloginfo( 'name' ),
		'og:type'      => is_singular( 'post' ) ? 'article' : 'website',
		'og:title'     => $title,
		'og:url'       => $canonical,
	);

	if ( $description ) {
		$tags['og:description'] = $description;
	}
	if ( $image ) {
		$tags['og:image'] = $image;
	}

	foreach ( $tags as $property => $content ) {
		if ( '' === $content ) {
			continue;
		}
		printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), esc_attr( $content ) );
	}

	// Twitter Card reaproveita os mesmos dados do Open Graph.
	printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( $image ? 'summary_large_image' : 'summary' ) );
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	if ( $image ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );
	}
}

/* -----------------------------------------------------------------------
 * 3. JSON-LD (Organization + WebSite na home, BreadcrumbList nas demais)
 * -------------------------------------------------------------------- */
function proodonto_json_ld() {
	$graphs = array();

	if ( is_front_page() ) {
		$graphs[] = array(
			'@type' => 'Organization',
			'@id'   => home_url( '/#organization' ),
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		);

		$graphs[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	} else {
		$crumbs = proodonto_get_breadcrumb_items();

		if ( count( $crumbs ) > 1 ) {
			$items = array();
			foreach ( $crumbs as $i => $crumb ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $crumb['label'],
					'item'     => $crumb['url'],
				);
			}

			$graphs[] = array(
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			);
		}
	}

	$graphs = apply_filters( 'proodonto_json_ld_graphs', $graphs );

	if ( ! $graphs ) {
		return;
	}

	$schema = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graphs,
	);

	printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( $schema ) );
}

/* -----------------------------------------------------------------------
 * 4b. JSON-LD "BlogPosting" nos posts do blog — o dado estruturado mais
 *     relevante pra SEO/GEO num post: título, datas, autor, editora,
 *     imagem, seção, tags, tempo de leitura e nº de palavras, tudo
 *     explícito em vez de depender do crawler adivinhar pelo HTML.
 * -------------------------------------------------------------------- */
add_filter( 'proodonto_json_ld_graphs', function ( $graphs ) {
	if ( ! is_singular( 'post' ) ) {
		return $graphs;
	}

	$post_id     = get_the_ID();
	$permalink   = get_permalink( $post_id );
	$author_id   = (int) get_post_field( 'post_author', $post_id );
	$description = proodonto_get_meta_description();

	$article = array(
		'@type'            => 'BlogPosting',
		'@id'              => $permalink . '#article',
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => $permalink,
		),
		'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
		'datePublished'    => get_the_date( 'c', $post_id ),
		'dateModified'     => get_the_modified_date( 'c', $post_id ),
		'inLanguage'       => get_bloginfo( 'language' ),
		'author'           => proodonto_get_author_schema( $author_id ),
		'publisher'        => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
		'wordCount'        => str_word_count( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) ) ),
		'timeRequired'     => 'PT' . proodonto_reading_time( $post_id ) . 'M',
	);

	if ( $description ) {
		$article['description'] = $description;
	}

	$proodonto_logo_url = proodonto_get_logo_url();
	if ( $proodonto_logo_url ) {
		$article['publisher']['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $proodonto_logo_url,
		);
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$proodonto_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'proodonto-hero' );
		if ( $proodonto_image ) {
			$article['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $proodonto_image[0],
				'width'  => $proodonto_image[1],
				'height' => $proodonto_image[2],
			);
		}
	}

	$proodonto_categories = get_the_category( $post_id );
	if ( $proodonto_categories ) {
		$article['articleSection'] = wp_list_pluck( $proodonto_categories, 'name' );
	}

	$proodonto_tags = get_the_tags( $post_id );
	if ( $proodonto_tags ) {
		$article['keywords'] = implode( ', ', wp_list_pluck( $proodonto_tags, 'name' ) );
	}

	$graphs[] = $article;

	return $graphs;
} );

/**
 * URL do logo (Custom Logo → Site Icon → vazio), usado como publisher.logo
 * no schema BlogPosting. Retorna string vazia se nada estiver configurado,
 * para o chamador decidir se omite o campo (schema.org não aceita "").
 */
function proodonto_get_logo_url() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$src = wp_get_attachment_image_src( $custom_logo_id, 'full' );
		if ( $src ) {
			return $src[0];
		}
	}

	$site_icon_id = get_option( 'site_icon' );
	if ( $site_icon_id ) {
		$src = wp_get_attachment_image_src( $site_icon_id, 'full' );
		if ( $src ) {
			return $src[0];
		}
	}

	return '';
}

/* -----------------------------------------------------------------------
 * 4. Breadcrumbs (usado no JSON-LD e disponível como template tag)
 * -------------------------------------------------------------------- */
function proodonto_get_breadcrumb_items() {
	$items = array(
		array( 'label' => __( 'Início', 'proodonto' ), 'url' => home_url( '/' ) ),
	);

	if ( is_singular( 'page' ) ) {
		$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
		foreach ( $ancestors as $ancestor_id ) {
			$items[] = array( 'label' => get_the_title( $ancestor_id ), 'url' => get_permalink( $ancestor_id ) );
		}
		$items[] = array( 'label' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_singular( 'post' ) ) {
		if ( get_option( 'page_for_posts' ) ) {
			$items[] = array( 'label' => get_the_title( get_option( 'page_for_posts' ) ), 'url' => get_permalink( get_option( 'page_for_posts' ) ) );
		}
		$categories = get_the_category();
		if ( $categories ) {
			$items[] = array( 'label' => $categories[0]->name, 'url' => get_category_link( $categories[0] ) );
		}
		$items[] = array( 'label' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
		$items[] = array( 'label' => get_the_title( get_option( 'page_for_posts' ) ), 'url' => '' );
	} elseif ( is_category() ) {
		$items[] = array( 'label' => single_cat_title( '', false ), 'url' => '' );
	} elseif ( is_search() ) {
		$items[] = array( 'label' => sprintf( __( 'Resultados para: %s', 'proodonto' ), get_search_query() ), 'url' => '' );
	} elseif ( is_404() ) {
		$items[] = array( 'label' => __( 'Página não encontrada', 'proodonto' ), 'url' => '' );
	}

	return $items;
}

/**
 * Template tag: <?php proodonto_breadcrumbs(); ?>
 */
function proodonto_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items = proodonto_get_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Você está aqui:', 'proodonto' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		echo '<li>';
		if ( $item['url'] && $i !== $last ) {
			printf( '<a href="%s">%s</a>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
		} else {
			printf( '<span aria-current="page">%s</span>', esc_html( $item['label'] ) );
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}
