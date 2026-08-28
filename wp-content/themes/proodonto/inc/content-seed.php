<?php
/**
 * Semeadura única do conteúdo atual (copy, ícones e imagens) nos custom
 * fields ACF das páginas Home, Página de Vendas e Sobre / Quem Somos.
 *
 * Contexto: os grupos de campos em inc/acf-fields.php cobrem seções que
 * antes eram arrays PHP fixos (Marquee, Sobre, Resultados, Tratamentos,
 * Passo a passo, Avaliações, Unidades, Blog, CTA final, além de todas as
 * seções da página Sobre). A ACF já resolve sozinha os campos "simples"
 * (texto/textarea/url) vazios usando 'default_value' — mas repeaters e
 * campos de imagem NÃO respeitam 'default_value' (limitação da própria
 * ACF: o valor de um repeater é derivado da contagem de post meta already
 * salva, não do default configurado). Por isso, esta rotina grava o
 * conteúdo de inc/page-content-defaults.php como VALOR real
 * (update_field()) na primeira vez que o tema roda em cada ambiente — sem
 * precisar de acesso ao banco de dados nem do wp-admin.
 *
 * Roda uma vez por página (flag '_proodonto_acf_content_seeded' no post),
 * nunca sobrescreve conteúdo já existente/editado, e é idempotente: se a
 * flag já estiver marcada, não faz nada.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'proodonto_seed_home_vendas_content', 20 );

function proodonto_seed_home_vendas_content() {
	if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
		return;
	}

	proodonto_seed_pages_for_template( 'page-home.php', 'proodonto_home_content_defaults', 'proodonto_seed_page_content' );
	proodonto_seed_pages_for_template( 'page-vendas.php', 'proodonto_vendas_content_defaults', 'proodonto_seed_page_content' );
	proodonto_seed_pages_for_template( 'page-sobre.php', 'proodonto_sobre_content_defaults', 'proodonto_seed_sobre_page_content' );

	proodonto_seed_link_aggregator_content();
	proodonto_seed_footer_content();
}

/**
 * Semeadura do repeater "agregador_itens" (Opções do Tema — Agregador de
 * Links de Contato). Diferente das outras seções, este campo vive na
 * página de opções da ACF (armazenamento 'option', não postmeta de uma
 * página) — por isso não passa por proodonto_seed_pages_for_template()
 * (que busca páginas por template) e usa sua própria flag, guardada
 * como wp_option em vez de post meta.
 */
function proodonto_seed_link_aggregator_content() {
	if ( get_option( 'proodonto_agregador_seeded' ) ) {
		return;
	}

	if ( ! get_field( 'agregador_itens', 'option' ) && function_exists( 'proodonto_link_aggregator_defaults' ) ) {
		$itens = proodonto_link_aggregator_defaults();

		if ( $itens ) {
			update_field( 'agregador_itens', $itens, 'option' );
		}
	}

	update_option( 'proodonto_agregador_seeded', 1, false );
}

/**
 * Semeadura dos repeaters "footer_redes_sociais" e "footer_links_colunas"
 * (Opções do Tema — Rodapé). Mesmo raciocínio de proodonto_seed_link_aggregator_content():
 * campo vive na página de opções (armazenamento 'option'), flag própria
 * em vez de post meta. O texto institucional ("footer_texto") NÃO precisa
 * de semeadura — é um campo simples (textarea), a ACF já resolve sozinha
 * com 'default_value' quando vazio.
 */
function proodonto_seed_footer_content() {
	if ( get_option( 'proodonto_footer_seeded' ) ) {
		return;
	}

	if ( ! get_field( 'footer_redes_sociais', 'option' ) && function_exists( 'proodonto_footer_social_defaults' ) ) {
		$itens = proodonto_footer_social_defaults();

		if ( $itens ) {
			update_field( 'footer_redes_sociais', $itens, 'option' );
		}
	}

	if ( ! get_field( 'footer_links_colunas', 'option' ) && function_exists( 'proodonto_footer_link_columns_defaults' ) ) {
		$colunas = proodonto_footer_link_columns_defaults();

		if ( $colunas ) {
			update_field( 'footer_links_colunas', $colunas, 'option' );
		}
	}

	update_option( 'proodonto_footer_seeded', 1, false );
}

/**
 * Aplica a semeadura em toda página (published ou draft) que usa o
 * template informado e ainda não foi semeada.
 */
function proodonto_seed_pages_for_template( $template, $defaults_callback, $seed_callback ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => '_wp_page_template',
					'value' => $template,
				),
			),
		)
	);

	if ( ! $pages ) {
		return;
	}

	$defaults = call_user_func( $defaults_callback );

	foreach ( $pages as $page ) {
		if ( get_post_meta( $page->ID, '_proodonto_acf_content_seeded', true ) ) {
			continue;
		}

		call_user_func( $seed_callback, $page->ID, $defaults );

		update_post_meta( $page->ID, '_proodonto_acf_content_seeded', current_time( 'mysql' ) );
	}
}

/**
 * Grava, um a um, os valores padrão em cada grupo/campo ACF da página —
 * só quando o campo ainda está vazio (proteção extra além da flag
 * '_proodonto_acf_content_seeded', para o caso de a página já ter algum
 * conteúdo salvo manualmente antes desta rotina existir).
 */
function proodonto_seed_page_content( $post_id, $defaults ) {
	// Marquee.
	if ( ! empty( $defaults['marquee'] ) && ! get_field( 'marquee_itens', $post_id ) ) {
		$rows = array();
		foreach ( $defaults['marquee'] as $item ) {
			$rows[] = array(
				'label'     => $item['label'],
				'icone_svg' => $item['icon'],
			);
		}
		update_field( 'marquee_itens', $rows, $post_id );
	}

	// Sobre. Campos SEM prefixo de propósito (eyebrow/titulo/texto) — únicos
	// campos com esses nomes no grupo "Sobre" de cada página; todas as
	// outras seções abaixo usam nomes prefixados (results_eyebrow,
	// treatments_titulo, etc.) para não colidir no post meta, já que ficam
	// todas na MESMA página/post.
	if ( ! empty( $defaults['about'] ) ) {
		$about = $defaults['about'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'eyebrow'         => $about['eyebrow'],
				'titulo'          => $about['titulo'],
				'texto'           => $about['texto'],
				'about_cta_label' => isset( $about['cta_label'] ) ? $about['cta_label'] : null,
			)
		);

		if ( ! empty( $about['estatisticas'] ) && ! get_field( 'estatisticas', $post_id ) ) {
			update_field( 'estatisticas', $about['estatisticas'], $post_id );
		}
	}

	// Resultados (antes e depois) — inclui sideload das fotos reais para a Biblioteca de Mídia.
	if ( ! empty( $defaults['results'] ) ) {
		$results = $defaults['results'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'results_eyebrow'   => $results['eyebrow'],
				'results_titulo'    => $results['titulo'],
				'results_texto'     => $results['texto'],
				'results_cta_label' => $results['cta_label'],
			)
		);

		if ( ! empty( $results['itens'] ) && ! get_field( 'results_itens', $post_id ) ) {
			$rows = array();
			foreach ( $results['itens'] as $item ) {
				$attachment_id = proodonto_import_theme_image( $item['arquivo'], $item['nome'] . ' — paciente ProOdonto, antes e depois do tratamento' );
				$rows[]        = array(
					'nome' => $item['nome'],
					'foto' => $attachment_id ? $attachment_id : '',
				);
			}
			update_field( 'results_itens', $rows, $post_id );
		}
	}

	// Tratamentos.
	if ( ! empty( $defaults['treatments'] ) ) {
		$treatments = $defaults['treatments'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'treatments_eyebrow'   => $treatments['eyebrow'],
				'treatments_titulo'    => $treatments['titulo'],
				'treatments_texto'     => $treatments['texto'],
				'treatments_cta_label' => isset( $treatments['cta_label'] ) ? $treatments['cta_label'] : null,
			)
		);

		if ( ! empty( $treatments['itens'] ) && ! get_field( 'treatments_itens', $post_id ) ) {
			$rows = array();
			foreach ( $treatments['itens'] as $item ) {
				$rows[] = array(
					'titulo'    => $item['titulo'],
					'texto'     => $item['texto'],
					'icone_svg' => $item['icon'],
				);
			}
			update_field( 'treatments_itens', $rows, $post_id );
		}
	}

	// Shorts (YouTube) — só o cabeçalho (e o CTA de reforço, na Página de
	// Vendas). Sem vídeos reais ainda cadastrados, "shorts_itens" fica
	// vazio de propósito (ver aviso no grupo ACF "Home/Página de Vendas —
	// Shorts (YouTube)") — os templates já não exibem a seção enquanto
	// não houver nenhum link válido.
	if ( ! empty( $defaults['shorts'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'shorts_eyebrow'   => $defaults['shorts']['eyebrow'],
				'shorts_titulo'    => $defaults['shorts']['titulo'],
				'shorts_texto'     => $defaults['shorts']['texto'],
				'shorts_cta_label' => isset( $defaults['shorts']['cta_label'] ) ? $defaults['shorts']['cta_label'] : null,
			)
		);
	}

	// Passo a passo.
	if ( ! empty( $defaults['steps'] ) ) {
		$steps = $defaults['steps'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'steps_eyebrow'   => $steps['eyebrow'],
				'steps_titulo'    => $steps['titulo'],
				'steps_cta_label' => isset( $steps['cta_label'] ) ? $steps['cta_label'] : null,
			)
		);

		if ( ! empty( $steps['itens'] ) && ! get_field( 'steps_itens', $post_id ) ) {
			$rows = array();
			foreach ( $steps['itens'] as $item ) {
				$rows[] = array(
					'label'     => $item['label'],
					'texto'     => $item['texto'],
					'icone_svg' => $item['icon'],
					'sucesso'   => ! empty( $item['sucesso'] ),
				);
			}
			update_field( 'steps_itens', $rows, $post_id );
		}
	}

	// Avaliações.
	if ( ! empty( $defaults['reviews'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'reviews_eyebrow'   => $defaults['reviews']['eyebrow'],
				'reviews_titulo'    => $defaults['reviews']['titulo'],
				'reviews_texto'     => $defaults['reviews']['texto'],
				'reviews_cta_label' => isset( $defaults['reviews']['cta_label'] ) ? $defaults['reviews']['cta_label'] : null,
			)
		);
	}

	// Unidades (cabeçalho).
	if ( ! empty( $defaults['units'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'units_eyebrow'   => $defaults['units']['eyebrow'],
				'units_titulo'    => $defaults['units']['titulo'],
				'units_texto'     => $defaults['units']['texto'],
				'units_cta_label' => isset( $defaults['units']['cta_label'] ) ? $defaults['units']['cta_label'] : null,
			)
		);
	}

	// Blog (só existe na Home) — só o cabeçalho; os posts em si são os
	// últimos posts reais do blog, buscados em page-home.php.
	if ( ! empty( $defaults['blog'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'blog_eyebrow'    => $defaults['blog']['eyebrow'],
				'blog_titulo'     => $defaults['blog']['titulo'],
				'blog_link_label' => $defaults['blog']['link_label'],
			)
		);
	}

	// CTA final.
	if ( ! empty( $defaults['closing_cta'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'closing_titulo'      => $defaults['closing_cta']['titulo'],
				'closing_texto'       => $defaults['closing_cta']['texto'],
				'closing_botao_label' => $defaults['closing_cta']['botao_label'],
			)
		);
	}
}

/**
 * Semeadura da página "Sobre / Quem Somos" — mesma lógica de
 * proodonto_seed_page_content() (Home/Vendas), adaptada às seções
 * próprias desta página (hero, história, valores, números, equipe,
 * biossegurança, unidades, FAQ, CTA final). Nenhuma foto real de
 * profissional é importada aqui: a seção "Equipe" nasce com placeholders
 * — ver aviso no próprio grupo ACF e em inc/page-content-defaults.php.
 */
function proodonto_seed_sobre_page_content( $post_id, $defaults ) {
	// Hero.
	if ( ! empty( $defaults['hero'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'hero_eyebrow'   => $defaults['hero']['eyebrow'],
				'hero_titulo'    => $defaults['hero']['titulo'],
				'hero_texto'     => $defaults['hero']['texto'],
				'hero_cta_label' => $defaults['hero']['cta_label'],
			)
		);
	}

	// Nossa História.
	if ( ! empty( $defaults['historia'] ) ) {
		$historia = $defaults['historia'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'historia_eyebrow' => $historia['eyebrow'],
				'historia_titulo'  => $historia['titulo'],
				'historia_texto'   => $historia['texto'],
			)
		);

		if ( ! empty( $historia['itens'] ) && ! get_field( 'historia_itens', $post_id ) ) {
			update_field( 'historia_itens', $historia['itens'], $post_id );
		}
	}

	// Missão, Visão e Valores.
	if ( ! empty( $defaults['valores'] ) ) {
		$valores = $defaults['valores'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'valores_eyebrow' => $valores['eyebrow'],
				'valores_titulo'  => $valores['titulo'],
				'missao_titulo'   => $valores['missao_titulo'],
				'missao_texto'    => $valores['missao_texto'],
				'visao_titulo'    => $valores['visao_titulo'],
				'visao_texto'     => $valores['visao_texto'],
			)
		);

		if ( ! empty( $valores['itens'] ) && ! get_field( 'valores_itens', $post_id ) ) {
			$rows = array();
			foreach ( $valores['itens'] as $item ) {
				$rows[] = array(
					'titulo'    => $item['titulo'],
					'texto'     => $item['texto'],
					'icone_svg' => $item['icon'],
				);
			}
			update_field( 'valores_itens', $rows, $post_id );
		}
	}

	// Números.
	if ( ! empty( $defaults['numeros'] ) ) {
		$numeros = $defaults['numeros'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'numeros_eyebrow' => $numeros['eyebrow'],
				'numeros_titulo'  => $numeros['titulo'],
			)
		);

		if ( ! empty( $numeros['itens'] ) && ! get_field( 'numeros_itens', $post_id ) ) {
			update_field( 'numeros_itens', $numeros['itens'], $post_id );
		}
	}

	// Corpo clínico / Equipe — sem foto (placeholder), sem CRO (em branco de propósito).
	if ( ! empty( $defaults['equipe'] ) ) {
		$equipe = $defaults['equipe'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'equipe_eyebrow' => $equipe['eyebrow'],
				'equipe_titulo'  => $equipe['titulo'],
				'equipe_texto'   => $equipe['texto'],
			)
		);

		if ( ! empty( $equipe['itens'] ) && ! get_field( 'equipe_itens', $post_id ) ) {
			$rows = array();
			foreach ( $equipe['itens'] as $item ) {
				$rows[] = array(
					'foto'  => '',
					'nome'  => $item['nome'],
					'cargo' => $item['cargo'],
					'cro'   => $item['cro'],
					'bio'   => $item['bio'],
				);
			}
			update_field( 'equipe_itens', $rows, $post_id );
		}
	}

	// Biossegurança.
	if ( ! empty( $defaults['seguranca'] ) ) {
		$seguranca = $defaults['seguranca'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'seguranca_eyebrow' => $seguranca['eyebrow'],
				'seguranca_titulo'  => $seguranca['titulo'],
				'seguranca_texto'   => $seguranca['texto'],
			)
		);

		if ( ! empty( $seguranca['itens'] ) && ! get_field( 'seguranca_itens', $post_id ) ) {
			$rows = array();
			foreach ( $seguranca['itens'] as $item ) {
				$rows[] = array(
					'titulo'    => $item['titulo'],
					'texto'     => $item['texto'],
					'icone_svg' => $item['icon'],
				);
			}
			update_field( 'seguranca_itens', $rows, $post_id );
		}
	}

	// Unidades (cabeçalho).
	if ( ! empty( $defaults['units'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'units_eyebrow' => $defaults['units']['eyebrow'],
				'units_titulo'  => $defaults['units']['titulo'],
				'units_texto'   => $defaults['units']['texto'],
			)
		);
	}

	// FAQ institucional.
	if ( ! empty( $defaults['faq'] ) ) {
		$faq = $defaults['faq'];

		proodonto_seed_simple_fields(
			$post_id,
			array(
				'faq_eyebrow' => $faq['eyebrow'],
				'faq_titulo'  => $faq['titulo'],
			)
		);

		if ( ! empty( $faq['itens'] ) && ! get_field( 'faq_itens', $post_id ) ) {
			update_field( 'faq_itens', $faq['itens'], $post_id );
		}
	}

	// CTA final.
	if ( ! empty( $defaults['cta'] ) ) {
		proodonto_seed_simple_fields(
			$post_id,
			array(
				'cta_titulo'      => $defaults['cta']['titulo'],
				'cta_texto'       => $defaults['cta']['texto'],
				'cta_botao_label' => $defaults['cta']['botao_label'],
			)
		);
	}
}

/**
 * Grava, um a um, campos "simples" (texto/textarea/url) só se ainda
 * estiverem vazios.
 */
function proodonto_seed_simple_fields( $post_id, $fields ) {
	foreach ( $fields as $name => $value ) {
		if ( null === $value || '' === $value ) {
			continue;
		}
		if ( get_field( $name, $post_id ) ) {
			continue;
		}
		update_field( $name, $value, $post_id );
	}
}

/**
 * Importa uma imagem já presente nos assets do tema para a Biblioteca de
 * Mídia (uma única vez — resultado fica em cache por caminho relativo em
 * 'proodonto_imported_theme_images'), e devolve o ID do attachment.
 *
 * Necessário porque os campos ACF do tipo "imagem" armazenam um ID de
 * attachment da Biblioteca de Mídia — não uma URL de arquivo do tema —
 * e as fotos de "Resultados" hoje são arquivos estáticos em
 * assets/images/, nunca foram enviadas via wp-admin.
 *
 * @param string $relative_path Caminho relativo ao diretório do tema (ex.: 'assets/images/resultado-manoel.jpg').
 * @param string $description   Usado como título/alt do attachment.
 * @return int ID do attachment, ou 0 se o arquivo não existir ou a importação falhar.
 */
function proodonto_import_theme_image( $relative_path, $description ) {
	$cache = get_option( 'proodonto_imported_theme_images', array() );
	$cache = is_array( $cache ) ? $cache : array();

	if ( ! empty( $cache[ $relative_path ] ) && get_post( $cache[ $relative_path ] ) ) {
		return (int) $cache[ $relative_path ];
	}

	$source_path = PROODONTO_DIR . '/' . $relative_path;

	if ( ! file_exists( $source_path ) ) {
		return 0;
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	if ( ! function_exists( 'wp_read_image_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}
	if ( ! function_exists( 'wp_crop_image' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$filename = wp_unique_filename( wp_upload_dir()['path'], basename( $relative_path ) );
	$contents = file_get_contents( $source_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents

	if ( false === $contents ) {
		return 0;
	}

	$uploaded = wp_upload_bits( $filename, null, $contents );

	if ( ! empty( $uploaded['error'] ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( $uploaded['file'], null );

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $description,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$uploaded['file']
	);

	if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $description );

	$metadata = wp_generate_attachment_metadata( $attachment_id, $uploaded['file'] );
	wp_update_attachment_metadata( $attachment_id, $metadata );

	$cache[ $relative_path ] = $attachment_id;
	update_option( 'proodonto_imported_theme_images', $cache, false );

	return (int) $attachment_id;
}
