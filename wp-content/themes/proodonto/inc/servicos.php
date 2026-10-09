<?php
/**
 * Hub de serviços (/servicos/) + uma página por tratamento
 * (/servicos/<tratamento>/), conforme a spec "Hub de serviços e página de
 * tratamento" (2026-10-04) e o canvas de UX/UI "Serviços PróOdonto".
 *
 *   - CPT `servico`, slug de URL `servicos`, SEM arquivo (has_archive
 *     false): o hub /servicos/ é uma PÁGINA com texto próprio
 *     (page-servicos.php), não um arquivo magro de CPT (SEO-08 / HUB-04).
 *     Sem taxonomia de propósito pelo mesmo motivo.
 *   - Sem cidade na URL nem no title (SEO-01): uma página atende as três
 *     unidades; a cidade entra só nos três botões de WhatsApp.
 *   - Sem bloco de preço: retirado das páginas de serviço em 2026-10-04.
 *   - Conteúdo 100% via ACF/editor: nenhum texto de fallback no PHP. Campo
 *     vazio = seção fora do DOM (CONV-07). O hero é a única exceção de
 *     propósito: H1 + botões renderizam SEMPRE (CONV-01/02), com o título
 *     do post como H1 — dado real do WP, não texto inventado.
 *   - FAQ: vive no editor (blocos proodonto/faq + proodonto/faq-item), que
 *     já geram o FAQPage pelo filtro de inc/blocks.php — zero código novo
 *     de schema pro FAQ (SEO-04).
 *   - WhatsApp: links da unidade vêm de proodonto_get_units()
 *     (inc/units-map.php, fonte única); cada serviço pode sobrescrever com
 *     um link do UpView próprio do tratamento (CONV-03/04). Nenhum número
 *     escrito no template.
 *   - Schema: nós entram no grafo do Yoast pelo filtro de sempre
 *     (proodonto_json_ld_graphs) — nunca <script> JSON-LD próprio (SEO-06).
 */

defined( 'ABSPATH' ) || exit;

define( 'PROODONTO_SERVICOS_VERSION', 2 ); // 2 = textos dos cards do hub (2026-10-08).

/* -----------------------------------------------------------------------
 * 1. CPT
 * -------------------------------------------------------------------- */
add_action( 'init', 'proodonto_register_servico_cpt' );

function proodonto_register_servico_cpt() {
	register_post_type(
		'servico',
		array(
			'labels'        => array(
				'name'               => __( 'Serviços', 'proodonto' ),
				'singular_name'      => __( 'Serviço', 'proodonto' ),
				'add_new'            => __( 'Adicionar serviço', 'proodonto' ),
				'add_new_item'       => __( 'Adicionar novo serviço', 'proodonto' ),
				'edit_item'          => __( 'Editar serviço', 'proodonto' ),
				'new_item'           => __( 'Novo serviço', 'proodonto' ),
				'view_item'          => __( 'Ver serviço', 'proodonto' ),
				'search_items'       => __( 'Buscar serviços', 'proodonto' ),
				'not_found'          => __( 'Nenhum serviço encontrado', 'proodonto' ),
				'not_found_in_trash' => __( 'Nenhum serviço na lixeira', 'proodonto' ),
				'menu_name'          => __( 'Serviços', 'proodonto' ),
			),
			'public'        => true,
			'show_in_rest'  => true, // Editor de blocos — o FAQ é montado com proodonto/faq.
			'has_archive'   => false, // O hub é a página /servicos/ (page-servicos.php).
			'hierarchical'  => false,
			'menu_icon'     => 'dashicons-heart',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'revisions' ),
			'rewrite'       => array(
				'slug'       => 'servicos',
				'with_front' => false,
			),
			// Novo serviço já nasce com o bloco de FAQ pronto pra preencher.
			'template'      => array(
				array(
					'proodonto/faq',
					array( 'heading' => '' ),
					array( array( 'proodonto/faq-item' ) ),
				),
			),
		)
	);

	// Regras de reescrita mudam uma vez só (quando o CPT aparece ou muda
	// de versão) — não a cada request.
	if ( (int) get_option( 'proodonto_servicos_rewrite' ) !== PROODONTO_SERVICOS_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'proodonto_servicos_rewrite', PROODONTO_SERVICOS_VERSION );
	}
}

/* -----------------------------------------------------------------------
 * 2. Campos (ACF Pro), no mesmo padrão de inc/acf-fields.php
 *    (acf_add_local_field_group, sem depender do banco).
 * -------------------------------------------------------------------- */
add_action( 'acf/init', 'proodonto_register_servicos_fields' );

function proodonto_register_servicos_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/* --- Página de tratamento (CPT servico) ------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_servico',
			'title'                 => 'Serviço — conteúdo da página',
			'position'              => 'acf_after_title',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'fields'                => array(
				array(
					'key'   => 'field_servico_tab_hero',
					'label' => 'Topo (hero)',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_servico_hero_titulo',
					'label'        => 'H1',
					'name'         => 'hero_titulo',
					'type'         => 'text',
					'instructions' => 'Vazio = usa o título do serviço. Sugestão de padrão: "<Tratamento> em Sergipe, com avaliação gratuita". Nunca o nome de uma cidade só.',
				),
				array(
					'key'          => 'field_servico_hero_lead',
					'label'        => 'Lead (uma frase)',
					'name'         => 'hero_lead',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => 'Uma frase. Precisa ser lida em 10 segundos, sem rolar a página.',
				),
				array(
					'key'          => 'field_servico_card_para_quem',
					'label'        => 'Card do hub — para quem é',
					'name'         => 'card_para_quem',
					'type'         => 'text',
					'maxlength'    => 60,
					'instructions' => 'Uma linha curta exibida no card deste tratamento em /servicos/ (ex.: "Dentes tortos ou mordida desalinhada").',
				),

				array(
					'key'   => 'field_servico_tab_resposta',
					'label' => 'O que é',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_servico_resposta_direta',
					'label'        => 'O que é — resposta direta',
					'name'         => 'resposta_direta',
					'type'         => 'textarea',
					'rows'         => 4,
					'instructions' => '2 a 3 frases, 40 a 60 palavras, começando pelo nome do tratamento. Tem que fazer sentido lido fora da página: é o trecho que o Google e as IAs recortam. Também vira a descrição do schema.',
				),

				array(
					'key'   => 'field_servico_tab_para_quem',
					'label' => 'Para quem é',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_servico_para_quem_itens',
					'label'        => 'Para quem é',
					'name'         => 'para_quem_itens',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Adicionar item',
					'sub_fields'   => array(
						array(
							'key'   => 'field_servico_para_quem_texto',
							'label' => 'Item',
							'name'  => 'texto',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'          => 'field_servico_pre_requisitos',
					'label'        => 'O que pode vir antes',
					'name'         => 'pre_requisitos',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => 'Bloqueado até revisão do Dr. Alisson (enxerto, gengiva, extração...).',
				),

				array(
					'key'   => 'field_servico_tab_etapas',
					'label' => 'Etapas',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_servico_etapas',
					'label'        => 'Como funciona, etapa por etapa',
					'name'         => 'etapas',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar etapa',
					'instructions' => 'Etapas reais revisadas pelo Dr. Alisson. Também entram no schema (howPerformed).',
					'sub_fields'   => array(
						array(
							'key'   => 'field_servico_etapa_titulo',
							'label' => 'Etapa',
							'name'  => 'titulo',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_servico_etapa_descricao',
							'label' => 'Descrição',
							'name'  => 'descricao',
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'key'          => 'field_servico_etapa_duracao',
							'label'        => 'Tempo',
							'name'         => 'duracao',
							'type'         => 'text',
							'instructions' => 'Ex.: "Na mesma visita".',
						),
					),
				),

				array(
					'key'   => 'field_servico_tab_avaliacoes',
					'label' => 'Avaliações',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_servico_avaliacoes',
					'label'        => 'Avaliações reais',
					'name'         => 'avaliacoes',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar avaliação',
					'instructions' => 'Só trechos reais do Perfil da Empresa no Google, de preferência que citem este tratamento. Vazio = a seção não aparece.',
					'max'          => 3,
					'sub_fields'   => array(
						array(
							'key'   => 'field_servico_avaliacao_texto',
							'label' => 'Trecho',
							'name'  => 'texto',
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'key'     => 'field_servico_avaliacao_nome',
							'label'   => 'Nome',
							'name'    => 'nome',
							'type'    => 'text',
							'wrapper' => array( 'width' => '50' ),
						),
						array(
							'key'     => 'field_servico_avaliacao_unidade',
							'label'   => 'Unidade',
							'name'    => 'unidade',
							'type'    => 'text',
							'wrapper' => array( 'width' => '50' ),
						),
					),
				),
				array(
					'key'          => 'field_servico_avaliacoes_link',
					'label'        => 'Link "Ver todas no Google"',
					'name'         => 'avaliacoes_link',
					'type'         => 'url',
				),

				array(
					'key'   => 'field_servico_tab_whatsapp',
					'label' => 'WhatsApp',
					'type'  => 'tab',
				),
				array(
					'key'     => 'field_servico_cta_message',
					'label'   => 'Como funciona',
					'name'    => '',
					'type'    => 'message',
					'message' => 'Cada campo abaixo aceita um link do UpView próprio DESTE tratamento (mensagem pré-preenchida falando do tratamento — CONV-04). Vazio = usa o link padrão da unidade (inc/units-map.php). Nunca cole wa.me com número de exemplo.',
				),
				array(
					'key'     => 'field_servico_cta_url_aracaju',
					'label'   => 'Link — Aracaju',
					'name'    => 'cta_url_aracaju',
					'type'    => 'url',
					'wrapper' => array( 'width' => '33' ),
				),
				array(
					'key'     => 'field_servico_cta_url_lagarto',
					'label'   => 'Link — Lagarto',
					'name'    => 'cta_url_lagarto',
					'type'    => 'url',
					'wrapper' => array( 'width' => '33' ),
				),
				array(
					'key'     => 'field_servico_cta_url_simao_dias',
					'label'   => 'Link — Simão Dias',
					'name'    => 'cta_url_simao_dias',
					'type'    => 'url',
					'wrapper' => array( 'width' => '34' ),
				),

				array(
					'key'   => 'field_servico_tab_relacionados',
					'label' => 'Leia também',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_servico_posts_relacionados',
					'label'         => 'Posts do blog relacionados',
					'name'          => 'posts_relacionados',
					'type'          => 'relationship',
					'post_type'     => array( 'post' ),
					'filters'       => array( 'search' ),
					'return_format' => 'id',
					'max'           => 3,
					'instructions'  => 'Pelo menos um (SEO-07). E o post deve linkar de volta para esta página.',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'servico',
					),
				),
			),
		)
	);

	/* --- Hub /servicos/ (page-servicos.php) -------------------------- */
	acf_add_local_field_group(
		array(
			'key'      => 'group_servicos_hub',
			'title'    => 'Hub de serviços',
			'position' => 'acf_after_title',
			'fields'   => array(
				array(
					'key'          => 'field_servicos_hub_titulo',
					'label'        => 'H1',
					'name'         => 'hub_titulo',
					'type'         => 'text',
					'instructions' => 'Vazio = usa o título da página. Ex.: "Tratamentos odontológicos".',
				),
				array(
					'key'          => 'field_servicos_hub_lead',
					'label'        => 'Lead (uma frase)',
					'name'         => 'hub_lead',
					'type'         => 'textarea',
					'rows'         => 2,
				),
				array(
					'key'     => 'field_servicos_hub_message',
					'label'   => 'Texto próprio do hub',
					'name'    => '',
					'type'    => 'message',
					'message' => 'O texto próprio do hub (120–180 palavras, "como escolher o tratamento") vai no editor desta página, abaixo. Os cards vêm sozinhos dos Serviços publicados, na ordem do campo "Ordem".',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-servicos.php',
					),
				),
			),
		)
	);

	/* --- Global, reaproveitado pelo hub e por toda página de serviço -- */
	acf_add_local_field_group(
		array(
			'key'      => 'group_servicos_global',
			'title'    => 'Opções do Tema — Páginas de serviço',
			'fields'   => array(
				array(
					'key'          => 'field_servicos_prova',
					'label'        => 'Prova social (texto, não chip)',
					'name'         => 'servicos_prova',
					'type'         => 'repeater',
					'layout'       => 'table',
					'max'          => 4,
					'button_label' => 'Adicionar número',
					'instructions' => 'Só fatos aprovados. Aparece no topo do hub e de cada serviço.',
					'sub_fields'   => array(
						array(
							'key'   => 'field_servicos_prova_valor',
							'label' => 'Valor',
							'name'  => 'valor',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_servicos_prova_rotulo',
							'label' => 'Rótulo',
							'name'  => 'rotulo',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'   => 'field_servicos_oferta_titulo',
					'label' => 'Oferta — título',
					'name'  => 'servicos_oferta_titulo',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_servicos_oferta_texto',
					'label' => 'Oferta — texto',
					'name'  => 'servicos_oferta_texto',
					'type'  => 'textarea',
					'rows'  => 2,
				),
				array(
					'key'          => 'field_servicos_oferta_itens',
					'label'        => 'Oferta — o que inclui',
					'name'         => 'servicos_oferta_itens',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Adicionar item',
					'sub_fields'   => array(
						array(
							'key'   => 'field_servicos_oferta_item_texto',
							'label' => 'Item',
							'name'  => 'texto',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'   => 'field_servicos_oferta_nota',
					'label' => 'Oferta — nota final',
					'name'  => 'servicos_oferta_nota',
					'type'  => 'text',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'proodonto-options',
					),
				),
			),
		)
	);
}

/* -----------------------------------------------------------------------
 * 3. Helpers de template
 * -------------------------------------------------------------------- */

/**
 * Página do hub (/servicos/), só se publicada — nunca link morto.
 *
 * @return WP_Post|null
 */
function proodonto_servicos_hub_page() {
	static $page = false;

	if ( false === $page ) {
		$found = get_page_by_path( 'servicos' );
		$page  = ( $found instanceof WP_Post && 'publish' === $found->post_status ) ? $found : null;
	}

	return $page;
}

/**
 * As três unidades com o link de WhatsApp certo pra página atual: o link
 * do UpView próprio do serviço (campo cta_url_<slug>), senão o link da
 * unidade (inc/units-map.php). Os dois lados são dado real — nenhum
 * número ou URL escrito aqui.
 *
 * @return array<int, array{name:string,slug:string,address:string,maps_url:string,whatsapp_url:string,page_url:string}>
 */
function proodonto_servicos_units( $post_id = null ) {
	if ( ! function_exists( 'proodonto_get_units' ) ) {
		return array();
	}

	$units = array();

	foreach ( proodonto_get_units() as $unit ) {
		$slug     = sanitize_title( $unit['name'] );
		$override = ( $post_id && function_exists( 'get_field' ) ) ? (string) get_field( 'cta_url_' . str_replace( '-', '_', $slug ), $post_id ) : '';
		$whatsapp = $override ? $override : ( isset( $unit['whatsapp_url'] ) ? $unit['whatsapp_url'] : '' );

		$units[] = array(
			'name'         => $unit['name'],
			'slug'         => $slug,
			'address'      => isset( $unit['address'] ) ? $unit['address'] : '',
			'maps_url'     => isset( $unit['maps_url'] ) ? $unit['maps_url'] : '',
			'whatsapp_url' => $whatsapp,
			'page_url'     => function_exists( 'proodonto_get_unit_page_url' ) ? proodonto_get_unit_page_url( $unit ) : '',
		);
	}

	return $units;
}

/**
 * Telefone global (Opções do Tema) como link tel:, ou '' se não houver.
 * É o caminho alternativo quando o WhatsApp não abre no navegador do
 * Instagram/Facebook (metade do tráfego).
 */
function proodonto_servicos_tel_url() {
	$phone  = function_exists( 'proodonto_get_phone' ) ? proodonto_get_phone() : '';
	$digits = preg_replace( '/\D+/', '', (string) $phone );

	return $digits ? 'tel:+' . ( 0 === strpos( $digits, '55' ) ? $digits : '55' . $digits ) : '';
}

/**
 * Prova social (Opções do Tema → Páginas de serviço).
 *
 * @return array<int, array{valor:string,rotulo:string}>
 */
function proodonto_servicos_proof_items() {
	$rows = function_exists( 'get_field' ) ? get_field( 'servicos_prova', 'option' ) : array();
	$out  = array();

	foreach ( (array) $rows as $row ) {
		if ( ! empty( $row['valor'] ) ) {
			$out[] = array(
				'valor'  => (string) $row['valor'],
				'rotulo' => isset( $row['rotulo'] ) ? (string) $row['rotulo'] : '',
			);
		}
	}

	return $out;
}

/**
 * Itens da trilha visível: Início › Serviços › <Tratamento>.
 */
function proodonto_servicos_breadcrumb_items() {
	$items = array(
		array( 'label' => __( 'Início', 'proodonto' ), 'url' => home_url( '/' ) ),
	);

	$hub = proodonto_servicos_hub_page();

	if ( is_singular( 'servico' ) ) {
		if ( $hub ) {
			$items[] = array( 'label' => get_the_title( $hub ), 'url' => get_permalink( $hub ) );
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} else {
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	}

	return $items;
}

function proodonto_servicos_breadcrumbs( $class = '' ) {
	$items = proodonto_servicos_breadcrumb_items();
	$last  = count( $items ) - 1;

	echo '<nav class="svc-breadcrumbs ' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Você está aqui:', 'proodonto' ) . '"><ol>';
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

/**
 * Ícones de traço (SVG inline, herdam currentColor).
 */
function proodonto_servicos_icon( $name, $size = 18 ) {
	$paths = array(
		'whatsapp' => '<path d="M3.5 20.5l1.4-4.1A8.5 8.5 0 1 1 8.1 19.4z"/><path d="M9 10c.4 2.3 2.4 4.4 5 5"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'down'     => '<path d="M12 5v14M6 13l6 6 6-6"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="svc-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Link de WhatsApp de uma unidade. Sem target="_blank" e sem noreferrer
 * de propósito: o navegador embutido do Instagram/Facebook perde a ponte
 * com o app em janela nova (PERF-03), e o noreferrer apaga a origem que o
 * UpView usa pra atribuir o lead (CONV-06). Os parâmetros de campanha da
 * URL atual são repassados por assets/js/servicos.js.
 */
function proodonto_servicos_cta_link( $unit, $label, $class, $servico = '' ) {
	if ( empty( $unit['whatsapp_url'] ) ) {
		return '';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s" rel="noopener" data-svc-cta data-unidade="%3$s" data-servico="%4$s" aria-label="%5$s">%6$s<span>%7$s</span></a>',
		esc_attr( $class ),
		esc_url( $unit['whatsapp_url'] ),
		esc_attr( $unit['name'] ),
		esc_attr( $servico ),
		/* translators: %s: nome da unidade */
		esc_attr( sprintf( __( 'WhatsApp da unidade %s', 'proodonto' ), $unit['name'] ) ),
		proodonto_servicos_icon( 'whatsapp', 18 ),
		esc_html( $label )
	);
}

/**
 * Bloco "As 3 unidades" — endereço visível, WhatsApp, ligar e rota
 * (HUB-05 / CONV-09). Mesmo componente no hub e no serviço.
 *
 * @param array  $units   proodonto_servicos_units().
 * @param string $heading Título da seção.
 * @param string $intro   Linha de apoio (opcional).
 * @param string $servico Nome do serviço (evento do Pixel), '' no hub.
 */
function proodonto_servicos_render_units( $units, $heading, $intro = '', $servico = '' ) {
	$units = array_filter(
		$units,
		function ( $unit ) {
			return ! empty( $unit['whatsapp_url'] ) || ! empty( $unit['address'] );
		}
	);

	if ( ! $units ) {
		return;
	}

	$tel = proodonto_servicos_tel_url();
	?>
	<section id="unidades" class="svc-units" aria-labelledby="svc-units-title">
		<div class="svc-container">
			<h2 id="svc-units-title" class="svc-units__title"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( $intro ) : ?>
				<p class="svc-units__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>

			<div class="svc-units__grid">
				<?php foreach ( $units as $unit ) : ?>
					<article class="svc-unit">
						<div class="svc-unit__head">
							<h3 class="svc-unit__name"><?php echo esc_html( $unit['name'] ); ?></h3>
							<?php if ( $unit['page_url'] ) : ?>
								<a class="svc-unit__page" href="<?php echo esc_url( $unit['page_url'] ); ?>"><?php esc_html_e( 'Página da unidade', 'proodonto' ); ?></a>
							<?php endif; ?>
						</div>

						<?php if ( $unit['address'] ) : ?>
							<p class="svc-unit__address"><?php echo proodonto_servicos_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG fixo do tema. ?><span><?php echo esc_html( $unit['address'] ); ?></span></p>
						<?php endif; ?>

						<?php
						echo proodonto_servicos_cta_link( // phpcs:ignore WordPress.Security.EscapeOutput -- escapado dentro do helper.
							$unit,
							/* translators: %s: nome da unidade */
							sprintf( __( 'WhatsApp %s', 'proodonto' ), $unit['name'] ),
							'svc-btn svc-btn--primary svc-btn--block',
							$servico
						);
						?>

						<?php if ( $tel || $unit['maps_url'] ) : ?>
							<div class="svc-unit__secondary">
								<?php if ( $tel ) : ?>
									<a class="svc-btn svc-btn--ghost" href="<?php echo esc_url( $tel ); ?>"><?php echo proodonto_servicos_icon( 'phone', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Ligar', 'proodonto' ); ?></span></a>
								<?php endif; ?>
								<?php if ( $unit['maps_url'] ) : ?>
									<a class="svc-btn svc-btn--ghost" href="<?php echo esc_url( $unit['maps_url'] ); ?>" rel="noopener"><?php echo proodonto_servicos_icon( 'pin', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Ver rota', 'proodonto' ); ?></span></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Barra fixa de WhatsApp — só até 768px (CSS). assets/js/servicos.js a
 * esconde enquanto os botões do hero ou o bloco de unidades estão na tela.
 */
function proodonto_servicos_render_sticky_bar( $units, $servico = '' ) {
	$units = array_filter(
		$units,
		function ( $unit ) {
			return ! empty( $unit['whatsapp_url'] );
		}
	);

	if ( ! $units ) {
		return;
	}
	?>
	<div class="svc-sticky" data-svc-sticky role="region" aria-label="<?php esc_attr_e( 'Agendar pelo WhatsApp', 'proodonto' ); ?>">
		<p class="svc-sticky__label"><?php esc_html_e( 'Avaliação gratuita pelo WhatsApp', 'proodonto' ); ?></p>
		<div class="svc-sticky__grid">
			<?php
			foreach ( $units as $unit ) {
				echo proodonto_servicos_cta_link( $unit, $unit['name'], 'svc-btn svc-btn--primary svc-btn--compact', $servico ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado dentro do helper.
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Prova social em texto (CONV-08: nada com cara de botão).
 */
function proodonto_servicos_render_proof( $class ) {
	$items = proodonto_servicos_proof_items();

	if ( ! $items ) {
		return;
	}

	echo '<dl class="' . esc_attr( $class ) . '">';
	foreach ( $items as $item ) {
		printf(
			'<div><dt>%s</dt><dd>%s</dd></div>',
			esc_html( $item['rotulo'] ),
			esc_html( $item['valor'] )
		);
	}
	echo '</dl>';
}

/* -----------------------------------------------------------------------
 * 4. CSS/JS só nas páginas de serviço
 * -------------------------------------------------------------------- */
function proodonto_is_servicos_context() {
	return is_singular( 'servico' ) || is_page_template( 'page-servicos.php' ) || is_page( 'servicos' );
}

add_action( 'wp_enqueue_scripts', 'proodonto_enqueue_servicos_assets', 20 );

function proodonto_enqueue_servicos_assets() {
	if ( ! proodonto_is_servicos_context() ) {
		return;
	}

	$css_rel = 'assets/css/servicos.css';
	if ( file_exists( PROODONTO_DIR . '/' . $css_rel ) ) {
		wp_enqueue_style(
			'proodonto-servicos',
			PROODONTO_URI . '/' . $css_rel,
			array( 'proodonto-main' ),
			proodonto_asset_version( $css_rel )
		);
	}

	$js_rel = 'assets/js/servicos.js';
	if ( file_exists( PROODONTO_DIR . '/' . $js_rel ) ) {
		wp_enqueue_script(
			'proodonto-servicos',
			PROODONTO_URI . '/' . $js_rel,
			array(),
			proodonto_asset_version( $js_rel ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}

/* -----------------------------------------------------------------------
 * 5. Schema — no grafo do Yoast, pelo filtro de sempre (SEO-06)
 * -------------------------------------------------------------------- */
add_filter( 'proodonto_json_ld_graphs', 'proodonto_servicos_json_ld_graphs' );

function proodonto_servicos_json_ld_graphs( $graphs ) {
	if ( is_singular( 'servico' ) ) {
		$post_id   = get_queried_object_id();
		$permalink = get_permalink( $post_id );

		$procedure = array(
			'@type'              => 'MedicalProcedure',
			'@id'                => $permalink . '#procedimento',
			'name'               => wp_strip_all_tags( get_the_title( $post_id ) ),
			'url'                => $permalink,
			'relevantSpecialty'  => 'https://schema.org/Dentistry',
		);

		$resposta = function_exists( 'get_field' ) ? (string) get_field( 'resposta_direta', $post_id ) : '';
		if ( $resposta ) {
			$procedure['description'] = wp_strip_all_tags( $resposta );
		}

		$preparo = function_exists( 'get_field' ) ? (string) get_field( 'pre_requisitos', $post_id ) : '';
		if ( $preparo ) {
			$procedure['preparation'] = wp_strip_all_tags( $preparo );
		}

		$etapas = function_exists( 'get_field' ) ? get_field( 'etapas', $post_id ) : array();
		$passos = array();
		foreach ( (array) $etapas as $etapa ) {
			if ( empty( $etapa['titulo'] ) ) {
				continue;
			}
			$passos[] = trim( wp_strip_all_tags( $etapa['titulo'] . ( ! empty( $etapa['descricao'] ) ? ': ' . $etapa['descricao'] : '' ) ) );
		}
		if ( $passos ) {
			$procedure['howPerformed'] = implode( ' ', array_map(
				function ( $passo, $i ) {
					return ( $i + 1 ) . '. ' . rtrim( $passo, '.' ) . '.';
				},
				$passos,
				array_keys( $passos )
			) );
		}

		$graphs[] = $procedure;

		// Amarra ao WebPage do Yoast (mesmo @id = URL), mesmo padrão das
		// páginas de unidade em inc/local-business-schema.php.
		$graphs[] = array(
			'@type'      => 'WebPage',
			'@id'        => $permalink,
			'about'      => array( '@id' => $procedure['@id'] ),
			'mainEntity' => array( '@id' => $procedure['@id'] ),
			'publisher'  => array( '@id' => home_url( '/#organization' ) ),
		);

		return $graphs;
	}

	if ( is_page() && ( is_page_template( 'page-servicos.php' ) || is_page( 'servicos' ) ) ) {
		$permalink = get_permalink( get_queried_object_id() );
		$elements  = array();

		foreach ( proodonto_servicos_query()->posts as $i => $servico ) {
			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => get_permalink( $servico ),
				'name'     => wp_strip_all_tags( get_the_title( $servico ) ),
			);
		}

		if ( ! $elements ) {
			return $graphs;
		}

		$graphs[] = array(
			'@type'           => 'ItemList',
			'@id'             => $permalink . '#servicos',
			'name'            => wp_strip_all_tags( get_the_title() ),
			'itemListElement' => $elements,
		);

		$graphs[] = array(
			'@type'      => 'WebPage',
			'@id'        => $permalink,
			'mainEntity' => array( '@id' => $permalink . '#servicos' ),
		);
	}

	return $graphs;
}

/**
 * Serviços publicados, na ordem do campo "Ordem" (menu_order).
 */
function proodonto_servicos_query() {
	static $query = null;

	if ( null === $query ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'servico',
				'post_status'    => 'publish',
				'posts_per_page' => 30,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'no_found_rows'  => true,
			)
		);
	}

	return $query;
}

/**
 * Trilha do Yoast (visível e BreadcrumbList do schema): insere o hub
 * entre "Início" e o serviço — sem isso o Yoast pula direto, porque o
 * CPT não tem arquivo (SEO-05).
 */
add_filter( 'wpseo_breadcrumb_links', function ( $links ) {
	if ( ! is_singular( 'servico' ) ) {
		return $links;
	}

	$hub = proodonto_servicos_hub_page();
	if ( ! $hub || count( $links ) < 2 ) {
		return $links;
	}

	array_splice(
		$links,
		count( $links ) - 1,
		0,
		array(
			array(
				'url'  => get_permalink( $hub ),
				'text' => get_the_title( $hub ),
				'id'   => $hub->ID,
			),
		)
	);

	return $links;
} );

/* -----------------------------------------------------------------------
 * 6. Bootstrap — roda uma vez por versão, só no admin.
 *    Cria o hub e os tratamentos como RASCUNHO (nada vai ao ar sozinho
 *    num deploy) e grava os fatos já aprovados nas Opções do Tema.
 * -------------------------------------------------------------------- */
add_action( 'admin_init', 'proodonto_servicos_bootstrap' );

function proodonto_servicos_bootstrap() {
	if ( (int) get_option( 'proodonto_servicos_bootstrap' ) >= PROODONTO_SERVICOS_VERSION ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'update_field' ) ) {
		return;
	}

	// Hub /servicos/ — rascunho, com o template já atribuído (é o que faz
	// os campos "Hub de serviços" aparecerem no editor).
	$hub = get_page_by_path( 'servicos', OBJECT, 'page' );
	if ( ! $hub ) {
		$hub_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'post_title'  => 'Serviços',
				'post_name'   => 'servicos',
			)
		);
		if ( $hub_id && ! is_wp_error( $hub_id ) ) {
			update_post_meta( $hub_id, '_wp_page_template', 'page-servicos.php' );
			update_field( 'hub_titulo', 'Tratamentos odontológicos', $hub_id );
		}
	} elseif ( ! get_page_template_slug( $hub->ID ) ) {
		update_post_meta( $hub->ID, '_wp_page_template', 'page-servicos.php' );
	}

	// Tratamentos da arquitetura fechada em 17/09/2026, na ordem de
	// publicação (demanda medida no GSC). Só título + slug: o conteúdo
	// clínico depende da revisão do Dr. Alisson.
	$servicos = array(
		'ortodontia'                => 'Ortodontia',
		'protocolo-sobre-implantes' => 'Protocolo sobre implantes',
		'protese-dentaria'          => 'Prótese dentária',
		'implante-dentario'         => 'Implante dentário',
		'estetica-dental'           => 'Estética dental',
	);

	$order = 0;
	foreach ( $servicos as $slug => $title ) {
		$order++;
		if ( get_page_by_path( $slug, OBJECT, 'servico' ) ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'   => 'servico',
				'post_status' => 'draft',
				'post_title'  => $title,
				'post_name'   => $slug,
				'menu_order'  => $order,
			)
		);
	}

	// Fatos aprovados (spec, seção 08) — só grava se ainda estiver vazio,
	// pra nunca atropelar uma edição feita no painel.
	if ( ! get_field( 'servicos_prova', 'option' ) ) {
		update_field(
			'servicos_prova',
			array(
				array( 'valor' => '+22 mil', 'rotulo' => 'atendimentos' ),
				array( 'valor' => '+7 anos', 'rotulo' => 'de mercado' ),
				array( 'valor' => '13', 'rotulo' => 'profissionais' ),
				array( 'valor' => '5,0', 'rotulo' => 'no Google' ),
			),
			'option'
		);
	}

	if ( ! get_field( 'servicos_oferta_titulo', 'option' ) ) {
		update_field( 'servicos_oferta_titulo', 'Não sabe por onde começar?', 'option' );
		update_field( 'servicos_oferta_texto', 'Comece pela avaliação gratuita. Em uma visita, você recebe:', 'option' );
		update_field(
			'servicos_oferta_itens',
			array(
				array( 'texto' => 'Exame de imagem feito na própria clínica' ),
				array( 'texto' => 'Avaliação pela equipe' ),
				array( 'texto' => 'Plano escrito com valor fechado' ),
			),
			'option'
		);
		update_field( 'servicos_oferta_nota', 'Sem custo e sem compromisso.', 'option' );
	}

	// v2 — linha "para quem é" de cada card do hub. Descrição de público,
	// sem claim clínico, prazo ou preço. Só grava em campo vazio.
	$cards = array(
		'ortodontia'                => 'Dentes tortos ou mordida desalinhada',
		'protocolo-sobre-implantes' => 'Repor todos os dentes de uma arcada',
		'protese-dentaria'          => 'Repor dentes perdidos',
		'implante-dentario'         => 'Repor um ou mais dentes, de forma fixa',
		'estetica-dental'           => 'Cor, forma e harmonia do sorriso',
	);

	foreach ( $cards as $slug => $texto ) {
		$servico = get_page_by_path( $slug, OBJECT, 'servico' );
		if ( $servico && ! get_field( 'card_para_quem', $servico->ID ) ) {
			update_field( 'card_para_quem', $texto, $servico->ID );
		}
	}

	update_option( 'proodonto_servicos_bootstrap', PROODONTO_SERVICOS_VERSION );
}
