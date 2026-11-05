<?php
/**
 * Campos por template (ACF PRO, registrados por código).
 *
 * Prefixos dos nomes: home_*, uni_*, hub_*, srv_*, post_* — evitam pegar
 * por engano um valor antigo com o mesmo nome deixado pelo tema anterior
 * no banco. Exceção de propósito: os campos do Tratamento e do Hub que JÁ
 * existiam (hero_titulo, etapas, avaliacoes, hub_titulo...) mantêm o
 * MESMO nome e a MESMA chave do tema antigo — assim o conteúdo já escrito
 * (inclusive repeaters) continua aparecendo.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'proodonto_register_page_fields' );

function proodonto_register_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$title_help = proodonto_title_help();

	/* ===================================================================
	 * HOME — page-home.php
	 * ================================================================ */
	$g = 'home';
	acf_add_local_field_group(
		array(
			'key'            => 'group_po_home',
			'title'          => __( 'Home', 'proodonto' ),
			'position'       => 'acf_after_title',
			'hide_on_screen' => array( 'the_content' ),
			'location'       => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-home.php' ) ) ),
			'fields'         => array(
				proodonto_tab( $g, __( 'Topo', 'proodonto' ) ),
				proodonto_msg( $g, 'topo', '', __( 'No celular o topo é só texto (o H1 é o LCP). A foto entra a partir do tablet. Unidades, nota e números vêm de PróOdonto → conteúdo global.', 'proodonto' ) ),
				proodonto_f( $g, 'home_hero_eyebrow', __( 'Sobretítulo', 'proodonto' ) ),
				proodonto_f( $g, 'home_hero_titulo', __( 'H1', 'proodonto' ), 'text', array( 'instructions' => $title_help, 'required' => 1 ) ),
				proodonto_f( $g, 'home_hero_lead', __( 'Lead', 'proodonto' ), 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				proodonto_f( $g, 'home_hero_imagem', __( 'Foto full-bleed (tablet/desktop)', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => __( 'Equipe ou recepção. 1600×900, WebP/AVIF ≤ 120 KB. Ganha preload e fica fora do lazy-load.', 'proodonto' ) ) ),
				proodonto_f( $g, 'home_seletor_titulo', __( 'Título do seletor de unidade', 'proodonto' ), 'text', array( 'placeholder' => 'Agende na unidade mais perto' ) ),

				proodonto_tab( $g, __( 'Tratamentos', 'proodonto' ) ),
				proodonto_f( $g, 'home_trat_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'home_trat_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => $title_help, 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'home_trat_destaque', __( 'Tratamento em destaque (card marinho)', 'proodonto' ), 'post_object', array( 'post_type' => array( 'servico' ), 'return_format' => 'id', 'allow_null' => 1 ) ),
				proodonto_f( $g, 'home_trat_lista', __( 'Demais cards', 'proodonto' ), 'relationship', array( 'post_type' => array( 'servico' ), 'return_format' => 'id', 'max' => 4, 'filters' => array( 'search' ), 'instructions' => __( 'Até 4. Ícone e resumo vêm de cada Tratamento.', 'proodonto' ) ) ),
				proodonto_f( $g, 'home_trat_botao', __( 'Botão para o hub', 'proodonto' ), 'text', array( 'placeholder' => 'Ver todos os tratamentos' ) ),

				proodonto_tab( $g, __( 'Unidades', 'proodonto' ) ),
				proodonto_f( $g, 'home_unid_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'home_unid_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => $title_help, 'wrapper' => array( 'width' => 50 ) ) ),

				proodonto_tab( $g, __( 'Sobre', 'proodonto' ) ),
				proodonto_f( $g, 'home_sobre_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_f( $g, 'home_sobre_texto', __( 'Texto', 'proodonto' ), 'textarea', array( 'rows' => 4, 'new_lines' => 'wpautop' ) ),
				proodonto_f( $g, 'home_sobre_link', __( 'Botão', 'proodonto' ), 'link', array( 'return_format' => 'array' ) ),

				proodonto_tab( $g, __( 'Perguntas', 'proodonto' ) ),
				proodonto_f( $g, 'home_faq_titulo', __( 'Título (celular)', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_faq_fields( $g, 'home_faq' ),

				proodonto_tab( $g, __( 'Fechamento', 'proodonto' ) ),
				proodonto_f( $g, 'home_fechamento_titulo', __( 'Título (vazio = o global)', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
			),
		)
	);

	/* ===================================================================
	 * UNIDADE — page-vendas.php (/aracaju/, /lagarto/, /simao-dias/)
	 * ================================================================ */
	$g = 'uni';
	acf_add_local_field_group(
		array(
			'key'            => 'group_po_unidade',
			'title'          => __( 'Página de unidade', 'proodonto' ),
			'position'       => 'acf_after_title',
			'hide_on_screen' => array( 'the_content' ),
			'location'       => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-vendas.php' ) ) ),
			'fields'         => array(
				proodonto_tab( $g, __( 'Unidade e teste', 'proodonto' ) ),
				proodonto_unit_select( $g, 'uni_unidade', __( 'Unidade desta página', 'proodonto' ), array( 'instructions' => __( 'Endereço, horário, nota, WhatsApp e avaliações saem da unidade escolhida.', 'proodonto' ) ) ),
				proodonto_f(
					$g,
					'uni_versao',
					__( 'Versão do topo', 'proodonto' ),
					'button_group',
					array(
						'choices'       => array(
							'a'  => __( 'A · dor + 1 botão', 'proodonto' ),
							'b'  => __( 'B · pergunta + 2 caminhos', 'proodonto' ),
							'ab' => __( 'Teste A/B 50/50', 'proodonto' ),
						),
						'default_value' => 'a',
						'instructions'  => __( 'No teste, cada visitante vê sempre a mesma versão (cookie de 30 dias) e o GTM recebe o evento ab_view com a variante. Os cliques no WhatsApp levam data-ab.', 'proodonto' ),
					)
				),

				proodonto_tab( $g, __( 'Topo', 'proodonto' ) ),
				proodonto_f( $g, 'uni_hero_h1', __( 'H1 (SEO, pequeno)', 'proodonto' ), 'text', array( 'placeholder' => 'Dentista em {cidade} · {bairro}', 'required' => 1, 'instructions' => __( 'Com cidade e bairro. A frase de dor abaixo é o título visual.', 'proodonto' ) ) ),
				proodonto_f( $g, 'uni_hero_titulo_a', __( 'Versão A — frase de dor', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_f( $g, 'uni_hero_botao_a', __( 'Versão A — botão', 'proodonto' ), 'text', array( 'placeholder' => 'Agendar avaliação pelo WhatsApp' ) ),
				proodonto_f( $g, 'uni_hero_titulo_b', __( 'Versão B — pergunta', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_f(
					$g,
					'uni_hero_caminhos',
					__( 'Versão B — caminhos', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'table',
						'max'          => 3,
						'button_label' => __( 'Adicionar caminho', 'proodonto' ),
						'instructions' => __( 'Cada caminho pode ter um link do UpView com mensagem própria. Vazio = WhatsApp da unidade.', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'uni_caminhos', 'titulo', __( 'Título', 'proodonto' ), 'text', array( 'placeholder' => 'Faltam dentes' ) ),
							proodonto_f( 'uni_caminhos', 'subtitulo', __( 'Subtítulo', 'proodonto' ), 'text', array( 'placeholder' => 'Implante ou prótese' ) ),
							proodonto_f( 'uni_caminhos', 'link', __( 'Link (UpView)', 'proodonto' ), 'url' ),
						),
					)
				),
				proodonto_f( $g, 'uni_hero_lead', __( 'Lead', 'proodonto' ), 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				proodonto_f( $g, 'uni_hero_extra', __( 'Frase extra', 'proodonto' ), 'text', array( 'placeholder' => 'Sem precisar viajar até Aracaju.', 'instructions' => __( 'Só em Lagarto e Simão Dias.', 'proodonto' ) ) ),
				proodonto_f( $g, 'uni_hero_foto', __( 'Foto da unidade (desktop)', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => __( '1000×1000, WebP ≤ 70 KB. Ganha preload.', 'proodonto' ) ) ),

				proodonto_tab( $g, __( 'Tratamentos', 'proodonto' ) ),
				proodonto_f( $g, 'uni_trat_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'placeholder' => 'Tratamentos em {cidade}', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'uni_trat_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => $title_help, 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'uni_trat_lista', __( 'Tratamentos', 'proodonto' ), 'relationship', array( 'post_type' => array( 'servico' ), 'return_format' => 'id', 'max' => 5, 'instructions' => __( 'Vazio = todos os tratamentos publicados, na ordem do painel. Os links levam a /servicos/<x>/ (sem cidade na URL).', 'proodonto' ) ) ),

				proodonto_tab( $g, __( 'A clínica', 'proodonto' ) ),
				proodonto_msg( $g, 'clinica', '', __( 'Conteúdo exclusivo desta cidade — é o que impede as 3 páginas de virarem a mesma página trocando o nome.', 'proodonto' ) ),
				proodonto_f( $g, 'uni_clinica_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'placeholder' => 'A clínica em {cidade}', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'uni_clinica_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => $title_help, 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'uni_clinica_texto', __( 'Texto', 'proodonto' ), 'textarea', array( 'rows' => 4, 'new_lines' => 'wpautop', 'instructions' => __( '2–3 frases só desta unidade: desde quando atende, estrutura, ponto de referência, estacionamento.', 'proodonto' ) ) ),
				proodonto_f( $g, 'uni_clinica_fotos', __( 'Fotos (recepção, consultório, fachada)', 'proodonto' ), 'gallery', array( 'return_format' => 'id', 'max' => 3, 'preview_size' => 'thumbnail' ) ),
				proodonto_f( $g, 'uni_equipe_titulo', __( 'Título da equipe', 'proodonto' ), 'text', array( 'placeholder' => 'Equipe em {cidade}' ) ),
				proodonto_f(
					$g,
					'uni_equipe',
					__( 'Equipe', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => __( 'Adicionar profissional', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'uni_equipe', 'nome', __( 'Nome', 'proodonto' ) ),
							proodonto_f( 'uni_equipe', 'especialidade', __( 'Especialidade', 'proodonto' ) ),
							proodonto_f( 'uni_equipe', 'cro', __( 'CRO', 'proodonto' ), 'text', array( 'placeholder' => 'CRO-SE 0000' ) ),
							proodonto_f( 'uni_equipe', 'foto', __( 'Foto', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail' ) ),
						),
					)
				),

				proodonto_tab( $g, __( 'Perguntas', 'proodonto' ) ),
				proodonto_f( $g, 'uni_faq_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_faq_fields( $g, 'uni_faq' ),

				proodonto_tab( $g, __( 'Final', 'proodonto' ) ),
				proodonto_f( $g, 'uni_outras_titulo', __( 'Título "Outras unidades"', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_f( $g, 'uni_fechamento_titulo', __( 'Fechamento — título', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_f( $g, 'uni_fechamento_texto', __( 'Fechamento — texto (celular)', 'proodonto' ), 'text', array( 'placeholder' => 'Fale com a equipe de {cidade} pelo WhatsApp.' ) ),
				proodonto_f( $g, 'uni_fechamento_botao', __( 'Fechamento — botão', 'proodonto' ), 'text', array( 'placeholder' => 'Falar com {cidade}' ) ),
			),
		)
	);

	/* ===================================================================
	 * HUB — page-servicos.php (/servicos/)
	 * ================================================================ */
	$g = 'hub';
	acf_add_local_field_group(
		array(
			'key'      => 'group_po_hub',
			'title'    => __( 'Hub de tratamentos', 'proodonto' ),
			'position' => 'acf_after_title',
			'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-servicos.php' ) ) ),
			'fields'   => array(
				proodonto_tab( $g, __( 'Topo', 'proodonto' ) ),
				proodonto_f( $g, 'hub_eyebrow', __( 'Sobretítulo', 'proodonto' ) ),
				// Mesma chave/nome do tema antigo.
				array(
					'key'          => 'field_servicos_hub_titulo',
					'label'        => __( 'H1', 'proodonto' ),
					'name'         => 'hub_titulo',
					'type'         => 'text',
					'instructions' => $title_help . ' ' . __( 'Vazio = título da página.', 'proodonto' ),
				),
				array(
					'key'       => 'field_servicos_hub_lead',
					'label'     => __( 'Lead', 'proodonto' ),
					'name'      => 'hub_lead',
					'type'      => 'textarea',
					'rows'      => 2,
					'new_lines' => '',
				),
				proodonto_f( $g, 'hub_seletor_titulo', __( 'Título do cartão de atalhos', 'proodonto' ), 'text', array( 'placeholder' => 'O que você quer resolver?' ) ),

				proodonto_tab( $g, __( 'Grupos', 'proodonto' ) ),
				proodonto_msg( $g, 'grupos', '', __( 'Cada grupo vira uma seção numerada (01, 02, 03) e um atalho no cartão do topo. O primeiro tratamento do grupo marcado como destaque ganha o card marinho grande.', 'proodonto' ) ),
				proodonto_f(
					$g,
					'hub_grupos',
					__( 'Grupos', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'block',
						'collapsed'    => 'field_po_hub_grupos_atalho',
						'button_label' => __( 'Adicionar grupo', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'hub_grupos', 'atalho', __( 'Atalho (cartão do topo)', 'proodonto' ), 'text', array( 'placeholder' => 'Repor dentes', 'wrapper' => array( 'width' => 34 ) ) ),
							proodonto_f( 'hub_grupos', 'atalho_sub', __( 'Linha de apoio do atalho', 'proodonto' ), 'text', array( 'placeholder' => 'Prótese · implante · protocolo', 'wrapper' => array( 'width' => 33 ) ) ),
							proodonto_icon_select( 'hub_grupos', 'icone', '', array( 'wrapper' => array( 'width' => 33 ) ) ),
							proodonto_f( 'hub_grupos', 'titulo', __( 'Título da seção', 'proodonto' ), 'text', array( 'instructions' => $title_help, 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'hub_grupos', 'subtitulo', __( 'Subtítulo', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'hub_grupos', 'destaque', __( 'Card em destaque', 'proodonto' ), 'post_object', array( 'post_type' => array( 'servico' ), 'return_format' => 'id', 'allow_null' => 1, 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'hub_grupos', 'servicos', __( 'Demais tratamentos', 'proodonto' ), 'relationship', array( 'post_type' => array( 'servico' ), 'return_format' => 'id', 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'hub_grupos', 'botao', __( 'Botão ao lado do título', 'proodonto' ), 'text', array( 'instructions' => __( 'Ex.: "Agendar avaliação" (leva às unidades). Vazio = sem botão.', 'proodonto' ) ) ),
						),
					)
				),

				proodonto_tab( $g, __( 'Avaliação e FAQ', 'proodonto' ) ),
				proodonto_f( $g, 'hub_passos_eyebrow', __( '"Primeira avaliação" — sobretítulo', 'proodonto' ), 'text', array( 'placeholder' => 'Não sabe por onde começar?', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'hub_passos_titulo', __( '"Primeira avaliação" — título', 'proodonto' ), 'text', array( 'placeholder' => 'Comece pela *avaliação.*', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'hub_faq_titulo', __( 'Título das perguntas', 'proodonto' ), 'text', array( 'instructions' => $title_help ) ),
				proodonto_faq_fields( $g, 'hub_faq' ),
				proodonto_msg( $g, 'editor', __( 'Texto do editor', 'proodonto' ), __( 'O editor abaixo continua valendo: o texto próprio do hub ("como escolher o tratamento", 120–180 palavras) aparece depois dos grupos.', 'proodonto' ) ),
			),
		)
	);

	/* ===================================================================
	 * TRATAMENTO — CPT servico (single-servico.php)
	 * Chaves "field_servico_*" = as MESMAS do tema antigo.
	 * ================================================================ */
	$g = 'srv';
	acf_add_local_field_group(
		array(
			'key'                   => 'group_servico',
			'title'                 => __( 'Tratamento — conteúdo da página', 'proodonto' ),
			'position'              => 'acf_after_title',
			'instruction_placement' => 'label',
			'location'              => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'servico' ) ) ),
			'fields'                => array(
				proodonto_tab( $g, __( 'Topo', 'proodonto' ) ),
				array(
					'key'          => 'field_servico_hero_titulo',
					'label'        => __( 'H1', 'proodonto' ),
					'name'         => 'hero_titulo',
					'type'         => 'text',
					'instructions' => $title_help . ' ' . __( 'Vazio = título do tratamento. Nunca o nome de uma cidade só.', 'proodonto' ),
				),
				array(
					'key'          => 'field_servico_hero_lead',
					'label'        => __( 'Lead', 'proodonto' ),
					'name'         => 'hero_lead',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => __( 'Uma frase, lida em 10 segundos.', 'proodonto' ),
				),
				proodonto_f( $g, 'srv_selo', __( 'Selo', 'proodonto' ), 'text', array( 'placeholder' => 'Mais procurado · Repor dentes', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f(
					$g,
					'srv_hero_cor',
					__( 'Cor do topo', 'proodonto' ),
					'button_group',
					array(
						'choices'       => array(
							'marinho' => __( 'Marinho', 'proodonto' ),
							'teal'    => __( 'Teal', 'proodonto' ),
						),
						'default_value' => 'marinho',
						'wrapper'       => array( 'width' => 50 ),
					)
				),
				proodonto_f( $g, 'srv_botao', __( 'Botão principal', 'proodonto' ), 'text', array( 'placeholder' => 'Agendar avaliação de prótese', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'srv_botao_etapas', __( 'Botão secundário (vai às etapas)', 'proodonto' ), 'text', array( 'placeholder' => 'Como funciona', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f(
					$g,
					'srv_provas',
					__( 'Provas com ✓', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'table',
						'max'          => 4,
						'button_label' => __( 'Adicionar prova', 'proodonto' ),
						'sub_fields'   => array( proodonto_f( 'srv_provas', 'texto', __( 'Texto', 'proodonto' ) ) ),
					)
				),
				proodonto_f( $g, 'srv_hero_foto', __( 'Foto do topo', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => __( 'Paciente sorrindo ou consultório. WebP ≤ 70 KB. Sem foto = bloco marinho.', 'proodonto' ), 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'srv_stat_valor', __( 'Número sobre a foto', 'proodonto' ), 'text', array( 'placeholder' => '+22 mil', 'wrapper' => array( 'width' => 25 ) ) ),
				proodonto_f( $g, 'srv_stat_rotulo', __( 'Rótulo do número', 'proodonto' ), 'text', array( 'placeholder' => 'atendimentos desde 2019', 'wrapper' => array( 'width' => 25 ) ) ),

				proodonto_tab( $g, __( 'Card', 'proodonto' ) ),
				array(
					'key'          => 'field_servico_card_para_quem',
					'label'        => __( 'Resumo no card', 'proodonto' ),
					'name'         => 'card_para_quem',
					'type'         => 'text',
					'maxlength'    => 80,
					'instructions' => __( 'Uma linha nos cards da Home, unidades e hub (ex.: "Dentes alinhados e mordida certa").', 'proodonto' ),
				),
				proodonto_icon_select( $g, 'srv_icone' ),
				proodonto_msg( $g, 'card_foto', '', __( 'A foto do card é a Imagem destacada do tratamento.', 'proodonto' ) ),

				proodonto_tab( $g, __( 'O que é', 'proodonto' ) ),
				array(
					'key'          => 'field_servico_resposta_direta',
					'label'        => __( 'O que é — resposta direta', 'proodonto' ),
					'name'         => 'resposta_direta',
					'type'         => 'textarea',
					'rows'         => 4,
					'instructions' => __( '40–60 palavras começando pelo nome do tratamento ("Prótese dentária é…"). É o trecho que Google e IAs recortam; vira a descrição do schema. Revisão do Dr. Alisson.', 'proodonto' ),
				),
				proodonto_f( $g, 'srv_oque_foto', __( 'Foto ao lado', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'medium' ) ),

				proodonto_tab( $g, __( 'Para quem é', 'proodonto' ) ),
				proodonto_f( $g, 'srv_para_quem_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'placeholder' => 'Para quem é', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'srv_para_quem_titulo', __( 'Título', 'proodonto' ), 'text', array( 'placeholder' => 'É para *você* se…', 'wrapper' => array( 'width' => 50 ) ) ),
				array(
					'key'          => 'field_servico_para_quem_itens',
					'label'        => __( 'Itens', 'proodonto' ),
					'name'         => 'para_quem_itens',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Adicionar item', 'proodonto' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_servico_para_quem_texto',
							'label' => __( 'Item', 'proodonto' ),
							'name'  => 'texto',
							'type'  => 'text',
						),
						proodonto_icon_select( 'srv_para_quem', 'icone' ),
					),
				),
				array(
					'key'          => 'field_servico_pre_requisitos',
					'label'        => __( 'O que pode vir antes', 'proodonto' ),
					'name'         => 'pre_requisitos',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => __( 'Bloqueado até revisão do Dr. Alisson.', 'proodonto' ),
				),

				proodonto_tab( $g, __( 'Etapas', 'proodonto' ) ),
				proodonto_f( $g, 'srv_etapas_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'placeholder' => 'Etapa por etapa', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'srv_etapas_titulo', __( 'Título', 'proodonto' ), 'text', array( 'placeholder' => 'Como funciona *o tratamento*', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'srv_etapas_botao', __( 'Botão', 'proodonto' ), 'text', array( 'placeholder' => 'Começar pela avaliação' ) ),
				array(
					'key'          => 'field_servico_etapas',
					'label'        => __( 'Etapas', 'proodonto' ),
					'name'         => 'etapas',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => __( 'Adicionar etapa', 'proodonto' ),
					'instructions' => __( 'Revisadas pelo Dr. Alisson. Também entram no schema (howPerformed).', 'proodonto' ),
					'sub_fields'   => array(
						array( 'key' => 'field_servico_etapa_titulo', 'label' => __( 'Etapa', 'proodonto' ), 'name' => 'titulo', 'type' => 'text' ),
						array( 'key' => 'field_servico_etapa_descricao', 'label' => __( 'Descrição', 'proodonto' ), 'name' => 'descricao', 'type' => 'textarea', 'rows' => 2 ),
						array( 'key' => 'field_servico_etapa_duracao', 'label' => __( 'Tempo', 'proodonto' ), 'name' => 'duracao', 'type' => 'text', 'instructions' => __( 'Ex.: "Na mesma visita".', 'proodonto' ) ),
					),
				),

				proodonto_tab( $g, __( 'Avaliações', 'proodonto' ) ),
				array(
					'key'          => 'field_servico_avaliacoes',
					'label'        => __( 'Avaliação em destaque', 'proodonto' ),
					'name'         => 'avaliacoes',
					'type'         => 'repeater',
					'layout'       => 'block',
					'max'          => 3,
					'button_label' => __( 'Adicionar avaliação', 'proodonto' ),
					'instructions' => __( 'Trechos reais que citem este tratamento. Vazio = a primeira avaliação global.', 'proodonto' ),
					'sub_fields'   => array(
						array( 'key' => 'field_servico_avaliacao_texto', 'label' => __( 'Trecho', 'proodonto' ), 'name' => 'texto', 'type' => 'textarea', 'rows' => 3 ),
						array( 'key' => 'field_servico_avaliacao_nome', 'label' => __( 'Nome', 'proodonto' ), 'name' => 'nome', 'type' => 'text', 'wrapper' => array( 'width' => '50' ) ),
						array( 'key' => 'field_servico_avaliacao_unidade', 'label' => __( 'Unidade', 'proodonto' ), 'name' => 'unidade', 'type' => 'text', 'wrapper' => array( 'width' => '50' ) ),
					),
				),
				array(
					'key'   => 'field_servico_avaliacoes_link',
					'label' => __( 'Link "Ver todas no Google"', 'proodonto' ),
					'name'  => 'avaliacoes_link',
					'type'  => 'url',
				),

				proodonto_tab( $g, __( 'Unidades e WhatsApp', 'proodonto' ) ),
				proodonto_f( $g, 'srv_unidades_titulo', __( 'Título do bloco de unidades', 'proodonto' ), 'text', array( 'placeholder' => 'Avaliação de prótese *perto de você*' ) ),
				proodonto_f( $g, 'srv_unidades_texto', __( 'Texto', 'proodonto' ), 'text', array( 'placeholder' => 'O WhatsApp abre com a mensagem sobre prótese.' ) ),
				proodonto_msg( $g, 'cta', '', __( 'Links do UpView próprios deste tratamento (mensagem pré-preenchida). Vazio = link padrão da unidade. Nunca wa.me.', 'proodonto' ) ),
				array( 'key' => 'field_servico_cta_url_aracaju', 'label' => 'Aracaju', 'name' => 'cta_url_aracaju', 'type' => 'url', 'wrapper' => array( 'width' => '33' ) ),
				array( 'key' => 'field_servico_cta_url_lagarto', 'label' => 'Lagarto', 'name' => 'cta_url_lagarto', 'type' => 'url', 'wrapper' => array( 'width' => '33' ) ),
				array( 'key' => 'field_servico_cta_url_simao_dias', 'label' => 'Simão Dias', 'name' => 'cta_url_simao_dias', 'type' => 'url', 'wrapper' => array( 'width' => '34' ) ),

				proodonto_tab( $g, __( 'Perguntas', 'proodonto' ) ),
				proodonto_f( $g, 'srv_faq_titulo', __( 'Título', 'proodonto' ), 'text', array( 'placeholder' => 'Perguntas *frequentes*' ) ),
				proodonto_faq_fields( $g, 'srv_faq' ),
				proodonto_msg( $g, 'faq_editor', '', __( 'Se este tratamento já tem FAQ no editor (bloco "Perguntas Frequentes"), ele continua valendo e aparece neste lugar.', 'proodonto' ) ),

				proodonto_tab( $g, __( 'Veja também', 'proodonto' ) ),
				array(
					'key'           => 'field_servico_posts_relacionados',
					'label'         => __( 'Posts do blog relacionados', 'proodonto' ),
					'name'          => 'posts_relacionados',
					'type'          => 'relationship',
					'post_type'     => array( 'post' ),
					'filters'       => array( 'search' ),
					'return_format' => 'id',
					'max'           => 3,
					'instructions'  => __( 'Pelo menos um (SEO-07) — e o post deve linkar de volta.', 'proodonto' ),
				),
				proodonto_f( $g, 'srv_relacionados', __( 'Outros tratamentos', 'proodonto' ), 'relationship', array( 'post_type' => array( 'servico' ), 'return_format' => 'id', 'max' => 3, 'instructions' => __( 'Vazio = os próximos tratamentos da lista.', 'proodonto' ) ) ),
			),
		)
	);

	/* ===================================================================
	 * POST — single.php
	 * ================================================================ */
	$g = 'post';
	acf_add_local_field_group(
		array(
			'key'      => 'group_po_post',
			'title'    => __( 'Post — conversão e busca', 'proodonto' ),
			'position' => 'side',
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
			'fields'   => array(
				proodonto_f( $g, 'post_resposta_rapida', __( 'Resposta rápida', 'proodonto' ), 'textarea', array( 'rows' => 5, 'new_lines' => '', 'instructions' => __( '2–3 frases que respondem à busca, logo abaixo do título. **trecho** vira negrito.', 'proodonto' ) ) ),
				proodonto_f( $g, 'post_tratamento', __( 'Tratamento relacionado', 'proodonto' ), 'post_object', array( 'post_type' => array( 'servico' ), 'return_format' => 'id', 'allow_null' => 1 ) ),
				proodonto_f( $g, 'post_cta_titulo', __( 'CTA — título', 'proodonto' ), 'text', array( 'placeholder' => 'Sua prótese quebrou?' ) ),
				proodonto_f( $g, 'post_cta_texto', __( 'CTA — texto', 'proodonto' ), 'text', array( 'placeholder' => 'A equipe avalia se compensa consertar ou refazer.' ) ),
				proodonto_f( $g, 'post_cta_posicao', __( 'CTA no meio do texto antes do H2 nº', 'proodonto' ), 'number', array( 'default_value' => 3, 'min' => 0, 'instructions' => __( '0 = sem CTA no meio.', 'proodonto' ) ) ),
				proodonto_f( $g, 'post_final_titulo', __( 'Faixa final — título', 'proodonto' ), 'text', array( 'placeholder' => 'Prótese quebrou? *A gente avalia.*' ) ),
				proodonto_f( $g, 'post_final_texto', __( 'Faixa final — texto', 'proodonto' ), 'text' ),
			),
		)
	);
}
