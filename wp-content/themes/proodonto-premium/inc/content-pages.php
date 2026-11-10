<?php
/**
 * Páginas que o tema usa + 4 posts de marcação.
 *
 * Roda SEM depender do ACF: ao ativar o tema (after_switch_theme) e na
 * primeira tela do painel de um administrador. Controle próprio de versão
 * (opção `proodonto_premium_pages`). O preenchimento dos campos ACF fica em
 * inc/content-seed.php e só roda com o ACF PRO ativo.
 *
 *   - Nunca duplica: procura pelo slug (qualquer status, inclusive lixeira)
 *     e, nas páginas com template, também pelo template. Se achar, pula.
 *   - No domínio real (proodontosergipe.com.br) tudo nasce como RASCUNHO,
 *     seguindo a regra do projeto de revisar antes de publicar. Em qualquer
 *     outro endereço (Local, staging, WordPress de teste) nasce publicado,
 *     para o layout aparecer. Filtro `proodonto_pages_status` muda isso.
 *   - Política de privacidade nasce sempre como rascunho (texto jurídico
 *     precisa de revisão).
 *   - Os 4 posts de marcação servem para ver o blog, o card, o sumário, o
 *     CTA do meio, as caixas de dica/alerta e o FAQ funcionando. Levam a
 *     meta `_proodonto_marcacao`, ficam em noindex e fora do sitemap mesmo
 *     se alguém publicar. Apague-os quando os posts reais existirem.
 */

defined( 'ABSPATH' ) || exit;

const PROODONTO_PAGES_VERSION = 1;

add_action( 'after_switch_theme', 'proodonto_seed_structure' );
add_action( 'admin_init', 'proodonto_seed_structure', 15 );

/**
 * Cria páginas, tratamentos e posts de marcação que faltarem.
 *
 * @param bool $force Ignora a versão gravada (o seed do ACF chama assim).
 */
function proodonto_seed_structure( $force = false ) {
	if ( ! is_bool( $force ) ) {
		$force = false; // after_switch_theme passa o nome do tema antigo.
	}
	if ( ! $force && (int) get_option( 'proodonto_premium_pages' ) >= PROODONTO_PAGES_VERSION ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! post_type_exists( 'servico' ) && function_exists( 'proodonto_register_servico_cpt' ) ) {
		proodonto_register_servico_cpt(); // after_switch_theme pode rodar antes do init do tema novo.
	}

	proodonto_seed_pages();
	proodonto_seed_page_sobre();
	proodonto_seed_marcacao_posts();

	update_option( 'proodonto_premium_pages', PROODONTO_PAGES_VERSION, false );
	flush_rewrite_rules( false );
}

/* -----------------------------------------------------------------------
 * Marcação nunca indexa
 * -------------------------------------------------------------------- */

/** É um post de marcação? */
function proodonto_is_marcacao( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return $post_id && get_post_meta( $post_id, '_proodonto_marcacao', true );
}

add_filter(
	'wp_robots',
	static function ( $robots ) {
		if ( is_singular( 'post' ) && proodonto_is_marcacao( get_queried_object_id() ) ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'] );
		}
		return $robots;
	}
);

add_filter(
	'wpseo_robots_array',
	static function ( $robots ) {
		if ( is_singular( 'post' ) && proodonto_is_marcacao( get_queried_object_id() ) ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	}
);

add_filter(
	'wpseo_exclude_from_sitemap_by_post_ids',
	static function ( $ids ) {
		$marcacao = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'meta_key'       => '_proodonto_marcacao', // phpcs:ignore WordPress.DB.SlowDBQuery
				'no_found_rows'  => true,
			)
		);
		return array_merge( (array) $ids, $marcacao );
	}
);

/* -----------------------------------------------------------------------
 * Helpers
 * -------------------------------------------------------------------- */

/** Status de nascimento: rascunho no domínio real, publicado no resto. */
function proodonto_pages_status() {
	$host   = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$status = preg_match( '/(^|\.)proodontosergipe\.com\.br$/i', $host ) ? 'draft' : 'publish';
	return apply_filters( 'proodonto_pages_status', $status );
}

/** Post existente pelo slug, em qualquer status. */
function proodonto_pages_find( $slug, $type = 'page' ) {
	$found = get_posts(
		array(
			'name'           => $slug,
			'post_type'      => $type,
			'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
			'posts_per_page' => 1,
			'no_found_rows'  => true,
		)
	);
	return $found ? $found[0] : null;
}

/** Página que já usa o template (qualquer status). */
function proodonto_pages_find_by_template( $template ) {
	$found = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $template,           // phpcs:ignore WordPress.DB.SlowDBQuery
			'no_found_rows'  => true,
		)
	);
	return $found ? $found[0] : null;
}

/** Cria (se não existir) e devolve o ID. */
function proodonto_pages_ensure( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'post_type'    => 'page',
			'post_status'  => proodonto_pages_status(),
			'post_content' => '',
			'post_excerpt' => '',
			'menu_order'   => 0,
			'template'     => '',
			'meta'         => array(),
		)
	);

	$existing = proodonto_pages_find( $args['post_name'], $args['post_type'] );
	if ( ! $existing && $args['template'] ) {
		$existing = proodonto_pages_find_by_template( $args['template'] );
	}
	if ( $existing ) {
		return (int) $existing->ID;
	}

	$id = wp_insert_post(
		array(
			'post_type'      => $args['post_type'],
			'post_status'    => $args['post_status'],
			'post_title'     => $args['post_title'],
			'post_name'      => $args['post_name'],
			'post_content'   => $args['post_content'],
			'post_excerpt'   => $args['post_excerpt'],
			'menu_order'     => $args['menu_order'],
			'post_author'    => get_current_user_id(),
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'meta_input'     => array_merge(
				$args['template'] ? array( '_wp_page_template' => $args['template'] ) : array(),
				$args['meta']
			),
		),
		true
	);

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/** Bloco serializado. */
function proodonto_block( $name, $attrs = array(), $inner = '' ) {
	$json = $attrs ? ' ' . serialize_block_attributes( $attrs ) : '';
	if ( '' === $inner ) {
		return "<!-- wp:{$name}{$json} /-->\n\n";
	}
	return "<!-- wp:{$name}{$json} -->\n{$inner}\n<!-- /wp:{$name} -->\n\n";
}

function proodonto_block_p( $html ) {
	return proodonto_block( 'paragraph', array(), '<p>' . $html . '</p>' );
}

function proodonto_block_h2( $text ) {
	return proodonto_block( 'heading', array(), '<h2 class="wp-block-heading">' . esc_html( $text ) . '</h2>' );
}

function proodonto_block_list( $items ) {
	$inner = '<ul class="wp-block-list">';
	foreach ( $items as $item ) {
		$inner .= "\n" . proodonto_block( 'list-item', array(), '<li>' . $item . '</li>' );
	}
	return proodonto_block( 'list', array(), $inner . '</ul>' );
}

/** Caixa "dica" ou "alerta" (estilos do Grupo registrados em inc/setup.php). */
function proodonto_block_box( $style, $html ) {
	return proodonto_block(
		'group',
		array(
			'className' => 'is-style-' . $style,
			'layout'    => array( 'type' => 'constrained' ),
		),
		'<div class="wp-block-group is-style-' . $style . '">' . "\n" . proodonto_block_p( $html ) . '</div>'
	);
}

/** Bloco de FAQ do tema (vira FAQPage no schema). */
function proodonto_block_faq( $items, $heading = 'Perguntas frequentes' ) {
	$inner = '';
	foreach ( $items as $item ) {
		$inner .= proodonto_block(
			'proodonto/faq-item',
			array(
				'question' => $item[0],
				'answer'   => $item[1],
			)
		);
	}
	return proodonto_block( 'proodonto/faq', array( 'heading' => $heading ), trim( $inner ) );
}

/** Categoria pelo nome; cria só fora de produção. */
function proodonto_pages_category( $name ) {
	$term = get_term_by( 'name', $name, 'category' );
	if ( $term ) {
		return (int) $term->term_id;
	}
	if ( 'publish' !== proodonto_pages_status() ) {
		return 0;
	}
	$new = wp_insert_term( $name, 'category' );
	return is_wp_error( $new ) ? 0 : (int) $new['term_id'];
}

/* -----------------------------------------------------------------------
 * Páginas
 * -------------------------------------------------------------------- */

function proodonto_seed_pages() {
	$status = proodonto_pages_status();

	// Home (só se ainda não houver página inicial definida).
	$home = (int) get_option( 'page_on_front' ) ? 0 : proodonto_pages_ensure(
		array(
			'post_title' => 'Início',
			'post_name'  => 'inicio',
			'template'   => 'page-home.php',
		)
	);
	if ( $home && 'publish' === get_post_status( $home ) && ! (int) get_option( 'page_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
	}

	// Unidades.
	$unidades = array(
		'aracaju'    => 'Aracaju',
		'lagarto'    => 'Lagarto',
		'simao-dias' => 'Simão Dias',
	);
	$order    = 10;
	foreach ( $unidades as $slug => $nome ) {
		proodonto_pages_ensure(
			array(
				'post_title'   => $nome,
				'post_name'    => $slug,
				'template'     => '', // As três usam o mesmo template: procurar só pelo slug.
				'meta'         => array( '_wp_page_template' => 'page-vendas.php' ),
				'menu_order'   => $order++,
				'post_excerpt' => 'Dentista em ' . $nome . ': avaliação com exame de imagem na própria clínica e plano escrito com valor fechado.',
			)
		);
	}

	// Hub de tratamentos.
	proodonto_pages_ensure(
		array(
			'post_title'   => 'Tratamentos',
			'post_name'    => 'servicos',
			'template'     => 'page-servicos.php',
			'menu_order'   => 20,
			'post_content' => proodonto_block_p( 'A PróOdonto faz prótese dentária, implante, protocolo sobre implantes, ortodontia e estética dental nas unidades de Aracaju, Lagarto e Simão Dias. Todo tratamento começa pela mesma avaliação: exame de imagem na própria clínica, avaliação pela equipe e plano escrito com valor fechado, na mesma visita.' ),
		)
	);

	// Sobre: proodonto_seed_page_sobre(), depois das unidades nas Opções.

	// Blog (página de posts).
	$blog = (int) get_option( 'page_for_posts' );
	if ( ! $blog ) {
		$blog = proodonto_pages_ensure(
			array(
				'post_title'   => 'Blog',
				'post_name'    => 'blog',
				'menu_order'   => 40,
				'post_excerpt' => 'Dúvidas de quem procura dentista em Sergipe, respondidas pela equipe da PróOdonto.',
			)
		);
		if ( $blog && 'publish' === get_post_status( $blog ) ) {
			update_option( 'page_for_posts', $blog );
		}
	}

	// Política de privacidade — sempre rascunho.
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( ! $privacy || ! get_post( $privacy ) || 'trash' === get_post_status( $privacy ) ) {
		$privacy = proodonto_pages_ensure(
			array(
				'post_title'   => 'Política de privacidade',
				'post_name'    => 'politica-de-privacidade',
				'post_status'  => 'draft',
				'menu_order'   => 50,
				'post_content' => proodonto_pages_privacy_content(),
			)
		);
		if ( $privacy ) {
			update_option( 'wp_page_for_privacy_policy', $privacy );
		}
	}

	// Tratamentos (CPT servico) — na ordem dos cards da Home.
	$tratamentos = array(
		'protese-dentaria'          => 'Prótese dentária',
		'implante-dentario'         => 'Implante dentário',
		'protocolo-sobre-implantes' => 'Protocolo sobre implantes',
		'ortodontia'                => 'Ortodontia',
		'estetica-dental'           => 'Estética dental',
	);
	$order       = 1;
	foreach ( $tratamentos as $slug => $titulo ) {
		proodonto_pages_ensure(
			array(
				'post_type'  => 'servico',
				'post_title' => $titulo,
				'post_name'  => $slug,
				'menu_order' => $order++,
				'post_status' => $status,
			)
		);
	}
}

/** Sobre — roda depois de proodonto_seed_options(), para listar as unidades. */
function proodonto_seed_page_sobre() {
	if ( proodonto_pages_find( 'sobre' ) ) {
		return;
	}
	proodonto_pages_ensure(
		array(
			'post_title'   => 'Sobre a PróOdonto',
			'post_name'    => 'sobre',
			'menu_order'   => 30,
			'post_excerpt' => 'Rede de clínicas odontológicas em Aracaju, Lagarto e Simão Dias, desde 2019.',
			'post_content' => proodonto_pages_sobre_content(),
		)
	);
}

function proodonto_pages_sobre_content() {
	$html  = proodonto_block_h2( 'Quem somos' );
	$html .= proodonto_block_p( 'A PróOdonto é uma rede de clínicas odontológicas de Sergipe, com unidades em Aracaju, Lagarto e Simão Dias. Atendemos desde 2019 e já são mais de 22 mil atendimentos, feitos por uma equipe de 13 profissionais.' );

	$html .= proodonto_block_h2( 'Como funciona a primeira avaliação' );
	$html .= proodonto_block_p( 'Na primeira visita, o exame de imagem é feito na própria clínica, sem encaminhamento. A equipe avalia o seu caso com calma e você sai com o plano escrito e o valor fechado, antes de decidir qualquer coisa.' );
	$html .= proodonto_block_p( 'O atendimento é particular: a PróOdonto não trabalha com convênio.' );

	$html .= proodonto_block_h2( 'O que tratamos' );
	$html .= proodonto_block_list(
		array(
			'<strong>Prótese dentária</strong> — repor dentes perdidos, com prótese fixa ou removível.',
			'<strong>Implante dentário</strong> — dente fixo no lugar do que faltava.',
			'<strong>Protocolo sobre implantes</strong> — todos os dentes de uma arcada, fixos.',
			'<strong>Ortodontia</strong> — dentes alinhados e mordida certa.',
			'<strong>Estética dental</strong> — cor, forma e harmonia do sorriso.',
		)
	);

	$units = function_exists( 'proodonto_get_units' ) ? proodonto_get_units( true ) : array();
	if ( ! $units && function_exists( 'proodonto_seed_units_data' ) ) {
		// ACF ainda não ativo: mesmos dados que o seed grava nas Opções.
		foreach ( proodonto_seed_units_data() as $row ) {
			$units[] = array(
				'name'   => $row['nome'],
				'street' => $row['endereco'],
				'bairro' => $row['bairro'],
				'cep'    => $row['cep'],
				'hours'  => array_map(
					static function ( $h ) {
						return array(
							'label'  => $h['rotulo'],
							'short'  => $h['rotulo_curto'],
							'opens'  => $h['abre'],
							'closes' => $h['fecha'],
						);
					},
					$row['horarios']
				),
			);
		}
	}
	if ( $units ) {
		$items = array();
		foreach ( $units as $unit ) {
			$items[] = '<strong>' . esc_html( $unit['name'] ) . '</strong> — ' . esc_html( proodonto_unit_address_line( $unit, false ) ) . '. ' . esc_html( proodonto_unit_hours_text( $unit ) ) . '.';
		}
		$html .= proodonto_block_h2( 'Onde estamos' );
		$html .= proodonto_block_list( $items );
	}

	$html .= proodonto_block_h2( 'Responsável técnico' );
	$html .= proodonto_block_p( 'Dr. Alisson Matheus Santana dos Santos, cirurgião-dentista, CRO-SE 3147.' );

	return $html;
}

function proodonto_pages_privacy_content() {
	$html  = proodonto_block_box( 'alerta', 'Rascunho criado pelo tema. Revisar com o jurídico antes de publicar.' );
	$html .= proodonto_block_h2( 'Quem cuida dos seus dados' );
	$html .= proodonto_block_p( 'Este site é mantido pela PróOdonto, rede de clínicas odontológicas com unidades em Aracaju, Lagarto e Simão Dias, em Sergipe. Esta página explica quais dados o site usa e para quê, conforme a Lei Geral de Proteção de Dados (Lei 13.709/2018).' );
	$html .= proodonto_block_h2( 'Quais dados o site usa' );
	$html .= proodonto_block_list(
		array(
			'<strong>Dados de navegação</strong> — páginas visitadas, tempo na página, cliques e dispositivo, coletados por ferramentas de medição.',
			'<strong>Origem da visita</strong> — parâmetros de campanha (como utm_source) repassados ao WhatsApp quando você toca em um botão de agendamento.',
			'<strong>Conversas pelo WhatsApp</strong> — o site não tem formulário. Os dados que você envia na conversa com a unidade são usados só para o agendamento e o atendimento.',
		)
	);
	$html .= proodonto_block_h2( 'Cookies' );
	$html .= proodonto_block_p( 'Usamos cookies de medição (Google Tag Manager, Google Analytics, Microsoft Clarity e, quando ativo, o Pixel da Meta) e um cookie próprio, de 30 dias, que guarda qual versão da página de unidade você viu. Você pode apagar ou bloquear cookies nas configurações do navegador.' );
	$html .= proodonto_block_h2( 'Seus direitos' );
	$html .= proodonto_block_p( 'Você pode pedir acesso, correção ou exclusão dos seus dados a qualquer momento, pelo WhatsApp de qualquer unidade.' );
	return $html;
}

/* -----------------------------------------------------------------------
 * 4 posts de marcação
 * -------------------------------------------------------------------- */

function proodonto_seed_marcacao_posts() {
	foreach ( proodonto_marcacao_posts_data() as $p ) {
		if ( proodonto_pages_find( $p['slug'], 'post' ) ) {
			continue;
		}

		$content = '';
		foreach ( $p['secoes'] as $secao ) {
			$content .= proodonto_block_h2( $secao['h2'] );
			foreach ( $secao['p'] as $par ) {
				$content .= proodonto_block_p( $par );
			}
			if ( ! empty( $secao['lista'] ) ) {
				$content .= proodonto_block_list( $secao['lista'] );
			}
			if ( ! empty( $secao['dica'] ) ) {
				$content .= proodonto_block_box( 'dica', $secao['dica'] );
			}
			if ( ! empty( $secao['alerta'] ) ) {
				$content .= proodonto_block_box( 'alerta', $secao['alerta'] );
			}
		}
		$content .= proodonto_block_faq( $p['faq'] );

		$cat = proodonto_pages_category( $p['categoria'] );
		$id  = wp_insert_post(
			array(
				'post_type'      => 'post',
				'post_status'    => proodonto_pages_status(),
				'post_title'     => $p['titulo'],
				'post_name'      => $p['slug'],
				'post_excerpt'   => $p['resumo'],
				'post_content'   => $content,
				'post_author'    => get_current_user_id(),
				'post_category'  => $cat ? array( $cat ) : array(),
				'comment_status' => 'closed',
				'meta_input'     => array(
					'_proodonto_marcacao'                => 1,
					'_yoast_wpseo_meta-robots-noindex'   => 1,
					'_yoast_wpseo_metadesc'              => $p['resumo'],
				),
			),
			true
		);
		// Campos ACF (resposta rápida, tratamento, CTA): proodonto_seed_marcacao_fields().
	}
}

/** Campos ACF dos posts de marcação — chamado pelo seed, só com ACF ativo. */
function proodonto_seed_marcacao_fields() {
	foreach ( proodonto_marcacao_posts_data() as $p ) {
		$post = proodonto_pages_find( $p['slug'], 'post' );
		if ( ! $post || ! get_post_meta( $post->ID, '_proodonto_marcacao', true ) ) {
			continue;
		}
		proodonto_seed( 'post_resposta_rapida', $p['resposta'], $post->ID );
		proodonto_seed( 'post_cta_posicao', 3, $post->ID );
		$svc = $p['tratamento'] ? proodonto_seed_servico_id( $p['tratamento'] ) : 0;
		if ( $svc ) {
			proodonto_seed( 'post_tratamento', $svc, $post->ID );
		}
	}
}

function proodonto_marcacao_posts_data() {
	return array(
		array(
			'titulo'     => 'Como cuidar da prótese dentária no dia a dia',
			'slug'       => 'como-cuidar-da-protese-dentaria',
			'categoria'  => 'Próteses e Implantes',
			'tratamento' => 'protese-dentaria',
			'resumo'     => 'Limpeza, guarda e revisões: o que faz a prótese dentária durar e continuar confortável.',
			'resposta'   => 'Para a prótese durar, **limpe todos os dias**, guarde do jeito certo e volte ao dentista nas revisões. A removível sai da boca para a limpeza; a fixa é limpa junto com os dentes, com escova e fio próprios.',
			'secoes'     => array(
				array(
					'h2' => 'Por que a limpeza da prótese importa',
					'p'  => array(
						'Restos de comida e placa se acumulam na prótese do mesmo jeito que nos dentes. Sem limpeza, aparecem mau hálito, manchas e irritação na gengiva.',
						'Quando a gengiva ou os dentes que ficaram adoecem, a prótese pode perder o encaixe e começar a machucar.',
					),
				),
				array(
					'h2'   => 'Prótese removível: como limpar',
					'p'    => array(
						'Tire a prótese depois das refeições e enxágue em água corrente. Uma vez por dia, escove com uma escova macia própria e o produto indicado pelo dentista.',
						'Faça a limpeza sobre a pia com um pouco de água ou sobre uma toalha: se a prótese escorregar, a queda não quebra a peça.',
					),
					'dica' => '<strong>Dica:</strong> evite água quente e produtos abrasivos. Eles podem deformar ou riscar a prótese.',
				),
				array(
					'h2' => 'Prótese fixa: como limpar',
					'p'  => array(
						'A prótese fixa é escovada junto com os dentes. Por baixo da ponte ou da prótese sobre implante, use passa-fio ou escova interdental.',
						'Na revisão, a equipe mostra a técnica no seu caso.',
					),
				),
				array(
					'h2' => 'Posso dormir com a prótese?',
					'p'  => array(
						'Depende do tipo de prótese. Na removível, a orientação mais comum é tirar à noite para a gengiva descansar, guardando a peça limpa num recipiente com água ou na solução indicada. Siga sempre o que o seu dentista orientar.',
					),
				),
				array(
					'h2'     => 'Quando procurar o dentista',
					'p'      => array(
						'Mesmo sem dor, as revisões ajudam a pegar cedo um desgaste ou uma mudança no encaixe.',
					),
					'alerta' => '<strong>Atenção:</strong> prótese que machuca, solta, quebrou ou mudou de encaixe precisa de avaliação. Não tente ajustar em casa.',
				),
			),
			'faq'        => array(
				array( 'Posso usar pasta de dente na prótese removível?', 'A pasta comum pode riscar a prótese com o tempo. Prefira o produto indicado pelo dentista e uma escova macia própria.' ),
				array( 'De quanto em quanto tempo troca a prótese?', 'Depende do tipo e do uso. Nas revisões, o dentista avalia se a prótese continua adaptada ou se precisa de ajuste ou troca.' ),
			),
		),
		array(
			'titulo'     => 'Implante dentário: quanto tempo dura e como fazer durar mais',
			'slug'       => 'quanto-tempo-dura-implante-dentario',
			'categoria'  => 'Próteses e Implantes',
			'tratamento' => 'implante-dentario',
			'resumo'     => 'O que faz um implante dentário durar, o que encurta a vida dele e os sinais de que algo não vai bem.',
			'resposta'   => 'Bem cuidado, o implante dentário **pode durar muitos anos**. O que mais pesa é a higiene, as revisões e hábitos como o cigarro. A coroa sobre o implante pode precisar de troca com o tempo, mesmo com o implante firme.',
			'secoes'     => array(
				array(
					'h2' => 'Implante e coroa são partes diferentes',
					'p'  => array(
						'O implante é o pino que fica no osso, no lugar da raiz. A coroa é a parte que aparece e mastiga.',
						'Por isso a pergunta "quanto dura" tem duas respostas: o implante pode durar muitos anos, e a coroa, que sofre o desgaste da mastigação, pode precisar de troca antes.',
					),
				),
				array(
					'h2'    => 'O que ajuda o implante a durar',
					'p'     => array( 'Os cuidados são parecidos com os dos dentes naturais, com atenção especial à gengiva ao redor do implante.' ),
					'lista' => array(
						'Escovar depois das refeições.',
						'Limpar ao redor do implante com fio próprio ou escova interdental.',
						'Ir às revisões, mesmo sem dor.',
						'Evitar o cigarro.',
					),
					'dica'  => '<strong>Dica:</strong> a escova interdental alcança a área entre o implante e a gengiva, onde a escova comum não chega.',
				),
				array(
					'h2' => 'O que pode encurtar a vida do implante',
					'p'  => array(
						'A inflamação da gengiva e do osso ao redor do implante (peri-implantite) é a causa mais comum de problema. Cigarro, falta de limpeza e ranger os dentes sem proteção também pesam.',
					),
				),
				array(
					'h2'     => 'Sinais de que algo não vai bem',
					'p'      => array( 'Quanto antes o problema é visto, mais simples costuma ser resolver.' ),
					'alerta' => '<strong>Atenção:</strong> sangramento, inchaço, dor ou sensação de que o dente está mexendo são motivo para procurar o dentista logo.',
				),
				array(
					'h2' => 'Antes do implante: a avaliação',
					'p'  => array(
						'Na PróOdonto, a avaliação começa com o exame de imagem feito na própria clínica. Ele mostra se há osso suficiente e se é preciso algum preparo antes. Você sai com o plano escrito e o valor fechado na mesma visita.',
					),
				),
			),
			'faq'        => array(
				array( 'O corpo pode rejeitar o implante?', 'Não é uma rejeição como num transplante. O que pode acontecer é o osso não se unir ao implante ou surgir inflamação ao redor — por isso a avaliação e o acompanhamento contam.' ),
				array( 'Quem fuma pode fazer implante?', 'Quem define é a avaliação. O cigarro atrapalha a cicatrização e aumenta o risco de problemas, e o dentista orienta caso a caso.' ),
			),
		),
		array(
			'titulo'     => 'Primeiras semanas de aparelho ortodôntico: o que muda na rotina',
			'slug'       => 'primeiras-semanas-de-aparelho-ortodontico',
			'categoria'  => 'Ortodontia',
			'tratamento' => 'ortodontia',
			'resumo'     => 'Sensibilidade, alimentação e limpeza: como passar bem pelas primeiras semanas de aparelho.',
			'resposta'   => 'Nas primeiras semanas de aparelho é comum sentir os dentes sensíveis e estranhar a fala e a mastigação. **O incômodo costuma passar em poucos dias.** Ajuste a alimentação, capriche na limpeza e siga as orientações da equipe.',
			'secoes'     => array(
				array(
					'h2' => 'Sensibilidade nos primeiros dias',
					'p'  => array(
						'O aparelho começa a mover os dentes logo após a instalação, e é normal senti-los sensíveis ao mastigar. A sensação volta, mais leve, depois das manutenções.',
					),
				),
				array(
					'h2'    => 'O que comer no começo',
					'p'     => array( 'Nos primeiros dias, prefira alimentos macios. Depois, o cuidado é com o que pode soltar ou entortar o aparelho.' ),
					'lista' => array(
						'Evite alimentos duros, como torrão de açúcar, pé de moleque e gelo.',
						'Evite alimentos grudentos, como bala de goma e caramelo.',
						'Corte frutas e pães firmes em pedaços pequenos.',
					),
					'dica'  => '<strong>Dica:</strong> se um fio ou bráquete machucar a bochecha, a cera ortodôntica alivia até a próxima consulta.',
				),
				array(
					'h2' => 'Limpeza com aparelho',
					'p'  => array(
						'Com aparelho fixo, a comida fica presa com mais facilidade. Escove depois das refeições e use passa-fio ou escova interdental entre os bráquetes.',
					),
				),
				array(
					'h2'     => 'Se alguma peça soltar',
					'p'      => array( 'Bráquete solto ou fio fora do lugar acontecem, principalmente no começo.' ),
					'alerta' => '<strong>Atenção:</strong> avise a clínica e não tente consertar em casa.',
				),
				array(
					'h2' => 'As manutenções',
					'p'  => array(
						'Nos retornos periódicos, a equipe ajusta o aparelho e acompanha a movimentação dos dentes. Faltar às manutenções costuma alongar o tratamento.',
					),
				),
			),
			'faq'        => array(
				array( 'Quanto tempo leva para acostumar com o aparelho?', 'Para a maioria das pessoas, de poucos dias a algumas semanas. A fala e a mastigação se adaptam rápido.' ),
				array( 'Posso comer de tudo com aparelho?', 'Quase tudo, com cuidado. Alimentos duros e grudentos podem soltar o aparelho e devem ser evitados ou cortados em pedaços pequenos.' ),
			),
		),
		array(
			'titulo'     => 'Sensibilidade nos dentes: causas comuns e quando procurar o dentista',
			'slug'       => 'sensibilidade-nos-dentes',
			'categoria'  => 'Saúde bucal',
			'tratamento' => '',
			'resumo'     => 'Por que os dentes doem com frio, calor ou doce, o que ajuda em casa e quando é hora de avaliar.',
			'resposta'   => 'Sensibilidade é a dor rápida que aparece com frio, calor ou doce. As causas mais comuns são **gengiva retraída, desgaste do esmalte e cárie**. Quando a sensibilidade dura ou piora, é hora de avaliar.',
			'secoes'     => array(
				array(
					'h2' => 'O que é a sensibilidade',
					'p'  => array(
						'É uma dor curta e aguda que aparece com estímulos como água gelada, café quente, doce ou até o ar frio. Ela acontece quando a parte mais interna do dente fica menos protegida.',
					),
				),
				array(
					'h2'    => 'Causas mais comuns',
					'p'     => array( 'A sensibilidade é um sintoma, não um diagnóstico. Entre as causas mais frequentes estão:' ),
					'lista' => array(
						'Gengiva retraída, que deixa a raiz do dente exposta.',
						'Escovação com força ou escova dura.',
						'Desgaste do esmalte por ranger os dentes ou por alimentos ácidos.',
						'Cárie ou restauração com infiltração.',
						'Clareamento, com sensibilidade passageira.',
					),
					'dica'  => '<strong>Dica:</strong> escova macia e movimentos leves protegem a gengiva e o esmalte.',
				),
				array(
					'h2' => 'O que ajuda em casa',
					'p'  => array(
						'Creme dental para dentes sensíveis pode aliviar o sintoma. Evite escovar com força e reduza alimentos e bebidas muito ácidos.',
					),
				),
				array(
					'h2'     => 'Quando procurar o dentista',
					'p'      => array( 'Se a sensibilidade dura mais de algumas semanas ou piora, vale avaliar a causa.' ),
					'alerta' => '<strong>Atenção:</strong> dor que dura, acorda você à noite ou vem com inchaço não é só sensibilidade. Procure atendimento.',
				),
				array(
					'h2' => 'Na avaliação',
					'p'  => array(
						'Na PróOdonto, o exame de imagem é feito na própria clínica, na primeira visita. Ele ajuda a encontrar a causa, e você sai com o plano escrito e o valor fechado.',
					),
				),
			),
			'faq'        => array(
				array( 'Creme dental para sensibilidade resolve?', 'Pode aliviar, mas não trata a causa. Se a sensibilidade continua, a avaliação mostra de onde ela vem.' ),
				array( 'Clareamento deixa os dentes sensíveis?', 'Pode haver sensibilidade passageira durante o tratamento. O acompanhamento do dentista ajuda a controlar.' ),
			),
		),
	);
}
