<?php
/**
 * Opções do Tema (ACF PRO) — página "PróOdonto" no menu do painel.
 *
 * Aqui mora tudo que se repete entre páginas: unidades, números, a
 * "primeira avaliação" (3 passos), resultados (antes e depois), avaliações
 * do Google, fechamento, rodapé e rastreamento. Cada página só guarda o
 * que é dela.
 *
 * Campos registrados por código (acf_add_local_field_group) — o mesmo
 * padrão do tema antigo. O JSON de exportação equivalente fica em
 * acf-export/ (gerado por wp-cli, ver README) para quem preferir importar.
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------------------------------------
 * Construtores curtos — chave única e estável por grupo.
 * -------------------------------------------------------------------- */

/**
 * Campo ACF.
 *
 * @param string $group Prefixo do grupo (vira parte da chave).
 * @param string $name  Nome (meta key).
 * @param string $label Rótulo no painel.
 * @param string $type  Tipo ACF.
 * @param array  $extra Demais configurações.
 */
function proodonto_f( $group, $name, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => 'field_po_' . $group . '_' . $name,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

/** Aba. */
function proodonto_tab( $group, $label ) {
	return array(
		'key'       => 'field_po_' . $group . '_tab_' . sanitize_title( $label ),
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
}

/** Aviso (mensagem) no painel. */
function proodonto_msg( $group, $id, $label, $message ) {
	return array(
		'key'     => 'field_po_' . $group . '_msg_' . $id,
		'label'   => $label,
		'name'    => '',
		'type'    => 'message',
		'message' => $message,
	);
}

/** Instrução padrão dos títulos com destaque. */
function proodonto_title_help() {
	return __( 'Envolva a palavra de destaque com asteriscos: O que você quer *resolver*? Ela sai em teal, sem itálico.', 'proodonto' );
}

/** Select de unidade (choices preenchidos a partir da aba Unidades). */
function proodonto_unit_select( $group, $name, $label, $extra = array() ) {
	return proodonto_f(
		$group,
		$name,
		$label,
		'select',
		array_merge(
			array(
				'choices'         => array(),
				'allow_null'      => 1,
				'ui'              => 0,
				'return_format'   => 'value',
				'proodonto_units' => 1,
			),
			$extra
		)
	);
}

/** Select de ícone. */
function proodonto_icon_select( $group, $name, $label = '', $extra = array() ) {
	return proodonto_f(
		$group,
		$name,
		$label ? $label : __( 'Ícone', 'proodonto' ),
		'select',
		array_merge(
			array(
				'choices'       => proodonto_icon_choices(),
				'allow_null'    => 1,
				'return_format' => 'value',
			),
			$extra
		)
	);
}

/** Repeater de perguntas frequentes (resposta visível no HTML — SEO-04). */
function proodonto_faq_fields( $group, $name ) {
	return proodonto_f(
		$group,
		$name,
		__( 'Perguntas frequentes', 'proodonto' ),
		'repeater',
		array(
			'layout'       => 'block',
			'collapsed'    => 'field_po_' . $group . '_' . $name . '_pergunta',
			'button_label' => __( 'Adicionar pergunta', 'proodonto' ),
			'instructions' => __( 'Saem na página e no schema FAQPage. Resposta curta, que faça sentido lida sozinha. Use {cidade} para o nome da unidade.', 'proodonto' ),
			'sub_fields'   => array(
				proodonto_f( $group . '_' . $name, 'pergunta', __( 'Pergunta', 'proodonto' ) ),
				proodonto_f( $group . '_' . $name, 'resposta', __( 'Resposta', 'proodonto' ), 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
			),
		)
	);
}

/* Unidade: preenche as opções dos selects marcados com 'proodonto_units'. */
add_filter(
	'acf/load_field',
	static function ( $field ) {
		if ( ! empty( $field['proodonto_units'] ) && function_exists( 'proodonto_unit_choices' ) ) {
			$field['choices'] = proodonto_unit_choices();
		}
		return $field;
	}
);

/* -----------------------------------------------------------------------
 * Página de opções + campos
 * -------------------------------------------------------------------- */
add_action( 'acf/init', 'proodonto_register_options' );

function proodonto_register_options() {
	if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'PróOdonto — conteúdo global', 'proodonto' ),
			'menu_title' => __( 'PróOdonto', 'proodonto' ),
			'menu_slug'  => 'proodonto-opcoes',
			'capability' => 'edit_pages',
			'position'   => 3,
			'icon_url'   => 'dashicons-smiley',
			'redirect'   => false,
			'autoload'   => true,
		)
	);

	$g = 'opt';

	$days = array(
		'Mo' => __( 'Seg', 'proodonto' ),
		'Tu' => __( 'Ter', 'proodonto' ),
		'We' => __( 'Qua', 'proodonto' ),
		'Th' => __( 'Qui', 'proodonto' ),
		'Fr' => __( 'Sex', 'proodonto' ),
		'Sa' => __( 'Sáb', 'proodonto' ),
		'Su' => __( 'Dom', 'proodonto' ),
	);

	acf_add_local_field_group(
		array(
			'key'             => 'group_po_opcoes',
			'title'           => __( 'Conteúdo global', 'proodonto' ),
			'style'           => 'seamless',
			'label_placement' => 'top',
			'location'        => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'proodonto-opcoes' ) ) ),
			'fields'          => array(

				/* --- Marca ------------------------------------------- */
				proodonto_tab( $g, __( 'Marca', 'proodonto' ) ),
				proodonto_f( $g, 'logo', __( 'Logo (fundo claro)', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'wrapper' => array( 'width' => 50 ), 'instructions' => __( 'SVG ou PNG transparente. Sem logo aqui, o cabeçalho usa o Logo do Personalizar.', 'proodonto' ) ) ),
				proodonto_f( $g, 'logo_claro', __( 'Logo (fundo marinho, rodapé)', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'nota_resumo', __( 'Nota no Google (resumo)', 'proodonto' ), 'text', array( 'placeholder' => '4,9 a 5,0', 'wrapper' => array( 'width' => 33 ) ) ),
				proodonto_f( $g, 'total_avaliacoes', __( 'Total de avaliações', 'proodonto' ), 'text', array( 'placeholder' => '+1.190', 'wrapper' => array( 'width' => 33 ) ) ),
				proodonto_f( $g, 'google_reviews_url', __( 'Link "Ler todas no Google"', 'proodonto' ), 'url', array( 'wrapper' => array( 'width' => 34 ) ) ),
				proodonto_f( $g, 'header_cta_label', __( 'Botão do cabeçalho', 'proodonto' ), 'text', array( 'placeholder' => 'Agendar avaliação', 'instructions' => __( 'Leva à seção de unidades. Nas páginas de unidade o botão vira "WhatsApp <cidade>" automaticamente.', 'proodonto' ) ) ),

				/* --- Unidades ---------------------------------------- */
				proodonto_tab( $g, __( 'Unidades', 'proodonto' ) ),
				proodonto_f( $g, 'unidades_eyebrow', __( 'Sobretítulo da seção de unidades', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'unidades_titulo', __( 'Título da seção de unidades', 'proodonto' ), 'text', array( 'instructions' => proodonto_title_help(), 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_msg( $g, 'unidades', '', __( 'Fonte única: cartões, seletor do topo, rodapé, schema e todos os botões de WhatsApp leem daqui. Os links de WhatsApp precisam ser do UpView (nunca wa.me).', 'proodonto' ) ),
				proodonto_f(
					$g,
					'unidades',
					__( 'Unidades', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'block',
						'collapsed'    => 'field_po_opt_unidades_nome',
						'button_label' => __( 'Adicionar unidade', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'opt_unidades', 'nome', __( 'Cidade', 'proodonto' ), 'text', array( 'required' => 1, 'wrapper' => array( 'width' => 25 ) ) ),
							proodonto_f( 'opt_unidades', 'slug', __( 'Slug', 'proodonto' ), 'text', array( 'instructions' => __( 'Igual ao endereço da página: aracaju, lagarto, simao-dias.', 'proodonto' ), 'wrapper' => array( 'width' => 25 ) ) ),
							proodonto_f( 'opt_unidades', 'bairro', __( 'Bairro', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
							proodonto_f( 'opt_unidades', 'uf', __( 'UF', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 10 ) ) ),
							proodonto_f( 'opt_unidades', 'cep', __( 'CEP', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 15 ) ) ),
							proodonto_f( 'opt_unidades', 'endereco', __( 'Rua e número', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'opt_unidades', 'telefone', __( 'Telefone', 'proodonto' ), 'text', array( 'instructions' => __( 'Vazio = não aparece.', 'proodonto' ), 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'opt_unidades', 'whatsapp_url', __( 'Link do WhatsApp (UpView)', 'proodonto' ), 'url', array( 'required' => 1, 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'opt_unidades', 'maps_url', __( 'Link "Como chegar" (Google Maps)', 'proodonto' ), 'url', array( 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f( 'opt_unidades', 'nota', __( 'Nota no Google', 'proodonto' ), 'text', array( 'placeholder' => '5,0', 'wrapper' => array( 'width' => 20 ) ) ),
							proodonto_f( 'opt_unidades', 'total', __( 'Nº de avaliações', 'proodonto' ), 'text', array( 'placeholder' => '301', 'wrapper' => array( 'width' => 20 ) ) ),
							proodonto_f( 'opt_unidades', 'google_url', __( 'Perfil no Google', 'proodonto' ), 'url', array( 'wrapper' => array( 'width' => 60 ) ) ),
							proodonto_f( 'opt_unidades', 'foto', __( 'Foto da fachada', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail', 'wrapper' => array( 'width' => 50 ), 'instructions' => __( 'Cartões de unidade e "Outras unidades". Sem foto = bloco marinho.', 'proodonto' ) ) ),
							proodonto_f( 'opt_unidades', 'pagina', __( 'Página da unidade', 'proodonto' ), 'post_object', array( 'post_type' => array( 'page' ), 'return_format' => 'id', 'allow_null' => 1, 'wrapper' => array( 'width' => 50 ) ) ),
							proodonto_f(
								'opt_unidades',
								'horarios',
								__( 'Horários', 'proodonto' ),
								'repeater',
								array(
									'layout'       => 'table',
									'button_label' => __( 'Adicionar faixa', 'proodonto' ),
									'instructions' => __( 'Dia com intervalo de almoço = duas faixas com o mesmo rótulo (a página junta: "Seg a sex 8h–13h e 14h–19h").', 'proodonto' ),
									'sub_fields'   => array(
										proodonto_f( 'opt_unidades_horarios', 'rotulo', __( 'Rótulo', 'proodonto' ), 'text', array( 'placeholder' => 'Segunda a sexta' ) ),
										proodonto_f( 'opt_unidades_horarios', 'rotulo_curto', __( 'Rótulo curto', 'proodonto' ), 'text', array( 'placeholder' => 'Seg a sex' ) ),
										proodonto_f( 'opt_unidades_horarios', 'dias', __( 'Dias (schema)', 'proodonto' ), 'checkbox', array( 'choices' => $days, 'layout' => 'horizontal', 'return_format' => 'value' ) ),
										proodonto_f( 'opt_unidades_horarios', 'abre', __( 'Abre', 'proodonto' ), 'time_picker', array( 'display_format' => 'H:i', 'return_format' => 'H:i' ) ),
										proodonto_f( 'opt_unidades_horarios', 'fecha', __( 'Fecha', 'proodonto' ), 'time_picker', array( 'display_format' => 'H:i', 'return_format' => 'H:i' ) ),
									),
								)
							),
						),
					)
				),

				/* --- Números ----------------------------------------- */
				proodonto_tab( $g, __( 'Números', 'proodonto' ) ),
				proodonto_f(
					$g,
					'numeros',
					__( 'Números da rede', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'table',
						'max'          => 4,
						'button_label' => __( 'Adicionar número', 'proodonto' ),
						'instructions' => __( 'Só fatos aprovados. Aparecem na Home e no hub. No celular saem os 3 primeiros não marcados como "só desktop".', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'opt_numeros', 'valor', __( 'Valor', 'proodonto' ), 'text', array( 'placeholder' => '+22 mil' ) ),
							proodonto_f( 'opt_numeros', 'rotulo', __( 'Rótulo', 'proodonto' ), 'text', array( 'placeholder' => 'atendimentos desde 2019' ) ),
							proodonto_f( 'opt_numeros', 'rotulo_curto', __( 'Rótulo no celular', 'proodonto' ), 'text', array( 'placeholder' => 'atendimentos' ) ),
							proodonto_f( 'opt_numeros', 'so_desktop', __( 'Só desktop', 'proodonto' ), 'true_false', array( 'ui' => 1 ) ),
						),
					)
				),

				/* --- Primeira avaliação ------------------------------ */
				proodonto_tab( $g, __( 'Primeira avaliação', 'proodonto' ) ),
				proodonto_msg( $g, 'passos', '', __( 'Bloco marinho "Uma visita. Tudo resolvido." — Home, páginas de unidade e hub (o hub pode trocar o título).', 'proodonto' ) ),
				proodonto_f( $g, 'passos_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'passos_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => proodonto_title_help(), 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'passos_texto', __( 'Texto (desktop)', 'proodonto' ), 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				proodonto_f( $g, 'passos_botao', __( 'Botão', 'proodonto' ), 'text', array( 'placeholder' => 'Agendar minha avaliação', 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'passos_botao_unidade', __( 'Botão nas páginas de unidade', 'proodonto' ), 'text', array( 'placeholder' => 'Agendar em {cidade}', 'wrapper' => array( 'width' => 50 ), 'instructions' => __( '{cidade} vira o nome da unidade.', 'proodonto' ) ) ),
				proodonto_f(
					$g,
					'passos',
					__( 'Passos', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'table',
						'max'          => 4,
						'button_label' => __( 'Adicionar passo', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'opt_passos_item', 'titulo', __( 'Passo', 'proodonto' ) ),
							proodonto_f( 'opt_passos_item', 'descricao', __( 'Descrição', 'proodonto' ), 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						),
					)
				),

				/* --- Resultados -------------------------------------- */
				proodonto_tab( $g, __( 'Resultados', 'proodonto' ) ),
				proodonto_msg( $g, 'resultados', '', __( 'Antes e depois reais, com autorização por escrito do paciente. Imagem 4:5 (600×750), WebP. As páginas de unidade mostram primeiro os casos daquela unidade.', 'proodonto' ) ),
				proodonto_f( $g, 'resultados_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'resultados_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => proodonto_title_help(), 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f(
					$g,
					'resultados',
					__( 'Casos', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => __( 'Adicionar caso', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'opt_resultados', 'imagem', __( 'Imagem', 'proodonto' ), 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail', 'required' => 1 ) ),
							proodonto_f( 'opt_resultados', 'tratamento', __( 'Tratamento', 'proodonto' ), 'text' ),
							proodonto_unit_select( 'opt_resultados', 'unidade', __( 'Unidade', 'proodonto' ) ),
							proodonto_f( 'opt_resultados', 'alt', __( 'Descrição da imagem (alt)', 'proodonto' ), 'text' ),
						),
					)
				),
				proodonto_f( $g, 'resultados_nota', __( 'Nota legal', 'proodonto' ), 'textarea', array( 'rows' => 2, 'new_lines' => '', 'instructions' => __( 'Autorização + resultados variam + responsável técnico com CRO.', 'proodonto' ) ) ),
				proodonto_f( $g, 'resultados_link_label', __( 'Botão "Ver mais resultados"', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'resultados_link', __( 'Destino do botão', 'proodonto' ), 'url', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => __( 'Vazio = botão não aparece.', 'proodonto' ) ) ),

				/* --- Avaliações -------------------------------------- */
				proodonto_tab( $g, __( 'Avaliações', 'proodonto' ) ),
				proodonto_msg( $g, 'avaliacoes', '', __( 'Só trechos reais do Perfil da Empresa no Google. Não publicamos aggregateRating no schema (avaliações de terceiros).', 'proodonto' ) ),
				proodonto_f( $g, 'avaliacoes_eyebrow', __( 'Sobretítulo', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => __( 'Nas unidades: "Avaliações · {cidade}".', 'proodonto' ) ) ),
				proodonto_f( $g, 'avaliacoes_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => proodonto_title_help(), 'wrapper' => array( 'width' => 50 ) ) ),
				proodonto_f( $g, 'avaliacoes_link_label', __( 'Texto do link para o Google', 'proodonto' ), 'text' ),
				proodonto_f(
					$g,
					'avaliacoes',
					__( 'Avaliações', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'block',
						'collapsed'    => 'field_po_opt_avaliacoes_nome',
						'button_label' => __( 'Adicionar avaliação', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f( 'opt_avaliacoes', 'texto', __( 'Trecho', 'proodonto' ), 'textarea', array( 'rows' => 3, 'new_lines' => '', 'required' => 1 ) ),
							proodonto_f( 'opt_avaliacoes', 'nome', __( 'Nome', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 34 ) ) ),
							proodonto_unit_select( 'opt_avaliacoes', 'unidade', __( 'Unidade', 'proodonto' ), array( 'wrapper' => array( 'width' => 33 ) ) ),
							proodonto_f( 'opt_avaliacoes', 'tratamento', __( 'Tratamento citado', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
						),
					)
				),

				/* --- Fechamento -------------------------------------- */
				proodonto_tab( $g, __( 'Fechamento', 'proodonto' ) ),
				proodonto_msg( $g, 'fechamento', '', __( 'Faixa teal antes do rodapé. Cada página pode trocar o título.', 'proodonto' ) ),
				proodonto_f( $g, 'fechamento_titulo', __( 'Título', 'proodonto' ), 'text', array( 'instructions' => proodonto_title_help() ) ),
				proodonto_f( $g, 'fechamento_texto', __( 'Texto', 'proodonto' ), 'text' ),
				proodonto_f( $g, 'fechamento_botao', __( 'Botão', 'proodonto' ), 'text' ),

				/* --- Rodapé ------------------------------------------ */
				proodonto_tab( $g, __( 'Rodapé', 'proodonto' ) ),
				proodonto_f( $g, 'rodape_texto', __( 'Texto institucional', 'proodonto' ), 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				proodonto_f( $g, 'rodape_botao', __( 'Botão', 'proodonto' ), 'text', array( 'placeholder' => 'Agendar avaliação' ) ),
				proodonto_f(
					$g,
					'redes',
					__( 'Redes sociais', 'proodonto' ),
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => __( 'Adicionar rede', 'proodonto' ),
						'sub_fields'   => array(
							proodonto_f(
								'opt_redes',
								'rede',
								__( 'Rede', 'proodonto' ),
								'select',
								array(
									'choices' => array(
										'instagram' => 'Instagram',
										'facebook'  => 'Facebook',
										'youtube'   => 'YouTube',
										'tiktok'    => 'TikTok',
									),
								)
							),
							proodonto_f( 'opt_redes', 'url', __( 'Link', 'proodonto' ), 'url' ),
						),
					)
				),
				proodonto_f( $g, 'responsavel_nome', __( 'Responsável técnico', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 40 ) ) ),
				proodonto_f( $g, 'responsavel_cargo', __( 'Cargo', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 30 ) ) ),
				proodonto_f( $g, 'responsavel_cro', __( 'CRO', 'proodonto' ), 'text', array( 'wrapper' => array( 'width' => 30 ) ) ),
				proodonto_f( $g, 'rodape_legal', __( 'Linha final (© e CNPJ)', 'proodonto' ), 'text', array( 'instructions' => __( '{ano} vira o ano atual.', 'proodonto' ) ) ),

				/* --- Rastreamento ------------------------------------ */
				proodonto_tab( $g, __( 'Rastreamento', 'proodonto' ) ),
				proodonto_f( $g, 'meta_pixel_id', __( 'ID do Pixel da Meta', 'proodonto' ), 'text', array( 'instructions' => __( 'Só números. Carrega depois que a página fica ociosa (não pesa no LCP/INP). Deixe vazio se o Pixel já entra pelo GTM.', 'proodonto' ) ) ),
				proodonto_f( $g, 'repassar_utm', __( 'Repassar utm_*, fbclid e gclid para o UpView', 'proodonto' ), 'true_false', array( 'ui' => 1, 'default_value' => 1 ) ),
			),
		)
	);
}
