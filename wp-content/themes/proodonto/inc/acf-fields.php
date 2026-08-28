<?php
/**
 * Todos os grupos de campos personalizados (ACF Pro) do tema, registrados
 * inteiramente em código via acf_add_local_field_group() — em vez de
 * arquivos acf-json/ (que ainda dependem de alguém criar/editar o grupo
 * pelo painel do WordPress pelo menos uma vez, gravando um registro no
 * banco daquele ambiente específico antes da ACF exportar o .json) ou de
 * qualquer criação manual direto no banco.
 *
 * Com os campos vindos só deste arquivo, eles já existem prontos pra uso
 * em QUALQUER ambiente onde o tema seja instalado — produção, staging,
 * um clone local novo — sem nenhum passo manual no wp-admin. Os grupos
 * registrados aqui não ficam editáveis pelo painel "Grupos de campos" da
 * ACF (aparecem como somente leitura, com origem "PHP"); pra mudar algo,
 * edite este arquivo.
 *
 * Isto substitui os arquivos que existiam em acf-json/ (removidos): a
 * pasta acf-json/ é reservada para exports feitos automaticamente pela
 * ACF caso alguém edite um grupo local pelo painel (ela grava lá, não no
 * banco) — algo que não deve acontecer com os grupos abaixo, já que eles
 * não são editáveis pelo painel.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'proodonto_register_acf_fields' );

function proodonto_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/* ---------------------------------------------------------------
	 * Opções do Tema — dados globais (telefone/WhatsApp no header, chave
	 * da Google Maps Static API usada no mapa de unidades da Home).
	 * A página de opções em si é registrada em inc/options-page.php.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_theme_options',
			'title'                 => 'Opções do Tema',
			'fields'                => array(
				array(
					'key'           => 'field_theme_options_telefone',
					'label'         => 'Telefone',
					'name'          => 'telefone',
					'type'          => 'text',
					'instructions'  => 'Exibido no header (link tel:). Formate como quer que apareça, ex.: (11) 4000-0000.',
					'wrapper'       => array( 'width' => '50' ),
					'placeholder'   => '(11) 4000-0000',
				),
				array(
					'key'           => 'field_theme_options_whatsapp',
					'label'         => 'WhatsApp',
					'name'          => 'whatsapp',
					'type'          => 'text',
					'instructions'  => 'Só números, com DDI e DDD (ex.: 5511999999999). Usado para montar o link wa.me/.',
					'wrapper'       => array( 'width' => '50' ),
					'placeholder'   => '5511999999999',
				),
				array(
					'key'           => 'field_theme_options_google_maps_api_key',
					'label'         => 'Google Maps API Key',
					'name'          => 'google_maps_api_key',
					'type'          => 'text',
					'instructions'  => 'Precisa ter a "Maps Static API" habilitada no Google Cloud Console. Usada só para gerar o mapa das unidades (ver seção Unidades da Home) — o resultado fica em cache permanente (wp-content/uploads/proodonto/), então a API só é chamada de novo se os endereços mudarem, não a cada acesso ao site.',
					'placeholder'   => 'AIza...',
				),
				array(
					'key'           => 'field_theme_options_header_cta_label',
					'label'         => 'CTA do header — Texto do botão',
					'name'          => 'header_cta_label',
					'type'          => 'text',
					'instructions'  => 'Texto do único botão de destaque exibido no header, em todas as páginas.',
					'wrapper'       => array( 'width' => '50' ),
					'default_value' => 'Agendar avaliação',
					'placeholder'   => 'Agendar avaliação',
				),
				array(
					'key'           => 'field_theme_options_header_cta_url',
					'label'         => 'CTA do header — URL',
					'name'          => 'header_cta_url',
					'type'          => 'url',
					'instructions'  => 'Link do botão do header. Pode ser wa.me/, link de rastreio (ex.: api.upviewcrm.com) ou qualquer outra URL. Se vazio, usa o WhatsApp cadastrado no campo acima.',
					'wrapper'       => array( 'width' => '50' ),
					'placeholder'   => 'https://wa.me/5511999999999?text=...',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'proodonto-options',
					),
				),
			),
			'description'           => 'Dados globais do site: telefone/WhatsApp (usados no rodapé e nas páginas) e o botão de CTA exibido no header.',
		)
	);

	/* ---------------------------------------------------------------
	 * Opções do Tema — Agregador de Links de Contato. Modal que abre ao
	 * clicar em QUALQUER CTA do site (ver assets/js/main.js), com um
	 * link por unidade — pra quem visita uma página institucional
	 * (Home, Sobre) poder escolher a unidade mais próxima antes de
	 * falar com a equipe, em vez de cair direto num único WhatsApp.
	 *
	 * NÃO aparece na Página de Vendas: aquele template já funciona como
	 * landing page de UMA unidade específica por campanha (custom field
	 * "cta_url", grupo "Página de Vendas — CTA") — abrir um seletor de
	 * unidades ali competiria com a própria campanha. Ver a exclusão em
	 * footer.php (proodonto_is_vendas_page()).
	 *
	 * Os itens nascem preenchidos com as mesmas unidades/links reais já
	 * usados em inc/units-map.php (ver inc/content-seed.php) — dali em
	 * diante, este repeater vira a fonte independente (pode divergir de
	 * propósito: aqui é "pra quem eu falo no WhatsApp", não
	 * necessariamente a lista completa de endereços físicos).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_theme_options_agregador',
			'title'                 => 'Opções do Tema — Agregador de Links de Contato',
			'fields'                => array(
				array(
					'key'           => 'field_agregador_ativo',
					'label'         => 'Ativar agregador de links',
					'name'          => 'agregador_ativo',
					'type'          => 'true_false',
					'instructions'  => 'Com o agregador ativo, clicar em qualquer botão de CTA do site (exceto na Página de Vendas) abre este modal em vez de ir direto para um único link.',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'key'               => 'field_agregador_titulo',
					'label'             => 'Título do modal',
					'name'              => 'agregador_titulo',
					'type'              => 'text',
					'default_value'     => 'Fale com a unidade mais próxima',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_agregador_ativo',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
				array(
					'key'               => 'field_agregador_texto',
					'label'             => 'Texto do modal',
					'name'              => 'agregador_texto',
					'type'              => 'textarea',
					'rows'              => 2,
					'default_value'     => 'Escolha abaixo a unidade mais próxima de você para falar direto com a nossa equipe pelo WhatsApp.',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_agregador_ativo',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
				array(
					'key'               => 'field_agregador_itens',
					'label'             => 'Links do modal',
					'name'              => 'agregador_itens',
					'type'              => 'repeater',
					'instructions'      => 'Um link por unidade (ou qualquer outro contato que queira oferecer). Linhas sem link ficam de fora do modal.',
					'layout'            => 'table',
					'button_label'      => 'Adicionar link',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_agregador_ativo',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'sub_fields'        => array(
						array(
							'key'         => 'field_agregador_item_label',
							'label'       => 'Nome',
							'name'        => 'label',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '30' ),
							'placeholder' => 'Unidade Aracaju',
						),
						array(
							'key'         => 'field_agregador_item_descricao',
							'label'       => 'Descrição (opcional)',
							'name'        => 'descricao',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '30' ),
							'placeholder' => 'Av. Pres. Tancredo Neves, 1028',
						),
						array(
							'key'         => 'field_agregador_item_url',
							'label'       => 'Link',
							'name'        => 'url',
							'type'        => 'url',
							'wrapper'     => array( 'width' => '40' ),
							'placeholder' => 'https://wa.me/5511999999999?text=...',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'proodonto-options',
					),
				),
			),
			'menu_order'            => 1,
			'description'           => 'Modal com links de contato por unidade, disparado por qualquer CTA do site (exceto na Página de Vendas).',
		)
	);

	/* ---------------------------------------------------------------
	 * Opções do Tema — Rodapé. Texto institucional, redes sociais e as
	 * colunas de links exibidas no footer (ver footer.php). As colunas
	 * "Unidades" e "Contato" continuam gerado automaticamente ali a partir
	 * de inc/units-map.php e do WhatsApp cadastrado acima — de propósito,
	 * pra não duplicar/desatualizar a mesma informação em dois lugares;
	 * este repeater cobre só as colunas "livres" (ex.: Tratamentos).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_theme_options_footer',
			'title'                 => 'Opções do Tema — Rodapé',
			'fields'                => array(
				array(
					'key'          => 'field_footer_texto',
					'label'        => 'Texto institucional',
					'name'         => 'footer_texto',
					'type'         => 'textarea',
					'instructions' => 'Texto curto exibido abaixo do logo, no rodapé de todas as páginas.',
					'rows'         => 3,
					'default_value' => 'Rede de clínicas odontológicas especializada em implantes e próteses, com atendimento acolhedor há mais de 20 anos.',
				),
				array(
					'key'          => 'field_footer_redes_sociais',
					'label'        => 'Redes sociais',
					'name'         => 'footer_redes_sociais',
					'type'         => 'repeater',
					'instructions' => 'Ícones exibidos abaixo do texto institucional, no rodapé.',
					'layout'       => 'table',
					'button_label' => 'Adicionar rede social',
					'sub_fields'   => array(
						array(
							'key'         => 'field_footer_rede_label',
							'label'       => 'Nome',
							'name'        => 'label',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '20' ),
							'placeholder' => 'Instagram',
						),
						array(
							'key'          => 'field_footer_rede_icone',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt;, viewBox 0 0 24 24.',
							'rows'         => 2,
							'wrapper'      => array( 'width' => '35' ),
						),
						array(
							'key'         => 'field_footer_rede_url',
							'label'       => 'Link',
							'name'        => 'url',
							'type'        => 'url',
							'instructions' => 'Ignorado se "Usar WhatsApp do tema" estiver marcado.',
							'wrapper'     => array( 'width' => '30' ),
							'placeholder' => 'https://instagram.com/...',
						),
						array(
							'key'           => 'field_footer_rede_usar_whatsapp',
							'label'         => 'Usar WhatsApp do tema',
							'name'          => 'usar_whatsapp_padrao',
							'type'          => 'true_false',
							'instructions'  => 'Aponta para o WhatsApp cadastrado nas Opções do Tema, em vez do link acima.',
							'ui'            => 1,
							'wrapper'       => array( 'width' => '15' ),
						),
					),
				),
				array(
					'key'          => 'field_footer_links_colunas',
					'label'        => 'Colunas de links',
					'name'         => 'footer_links_colunas',
					'type'         => 'repeater',
					'instructions' => 'Colunas de links exibidas no rodapé, antes das colunas fixas "Unidades" e "Contato" (essas duas continuam automáticas).',
					'layout'       => 'block',
					'button_label' => 'Adicionar coluna',
					'collapsed'    => 'field_footer_coluna_titulo',
					'sub_fields'   => array(
						array(
							'key'   => 'field_footer_coluna_titulo',
							'label' => 'Título da coluna',
							'name'  => 'heading',
							'type'  => 'text',
							'placeholder' => 'Tratamentos',
						),
						array(
							'key'          => 'field_footer_coluna_links',
							'label'        => 'Links',
							'name'         => 'links',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => 'Adicionar link',
							'sub_fields'   => array(
								array(
									'key'         => 'field_footer_coluna_link_label',
									'label'       => 'Texto',
									'name'        => 'label',
									'type'        => 'text',
									'wrapper'     => array( 'width' => '50' ),
									'placeholder' => 'Implantes',
								),
								array(
									'key'         => 'field_footer_coluna_link_url',
									'label'       => 'Link',
									'name'        => 'url',
									'type'        => 'url',
									'wrapper'     => array( 'width' => '50' ),
									'placeholder' => 'https://...',
								),
							),
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'proodonto-options',
					),
				),
			),
			'menu_order'            => 2,
			'description'           => 'Texto institucional, redes sociais e colunas de links do rodapé (colunas "Unidades" e "Contato" continuam automáticas).',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Banner (Hero). Repeater de slides do carrossel principal.
	 * Localização por TEMPLATE (não por ID de página): funciona em
	 * qualquer ambiente, mesmo que a página Home tenha um ID diferente.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_home_banner',
			'title'                 => 'Home — Banner (Hero)',
			'fields'                => array(
				array(
					'key'           => 'field_home_banner',
					'label'         => 'Banner',
					'name'          => 'banner',
					'type'          => 'repeater',
					'instructions'  => 'Slides do banner principal (hero) da Home.',
					'layout'        => 'block',
					'button_label'  => 'Adicionar slide',
					'collapsed'     => 'field_home_banner_texto',
					'sub_fields'    => array(
						array(
							'key'          => 'field_home_banner_desktop',
							'label'        => 'Imagem (desktop)',
							'name'         => 'desktop',
							'type'         => 'image',
							'instructions' => 'Usada em telas a partir de 1024px.',
							'required'     => 1,
							'wrapper'      => array( 'width' => '50' ),
							'return_format' => 'array',
							'preview_size' => 'medium',
						),
						array(
							'key'          => 'field_home_banner_mobile',
							'label'        => 'Imagem (mobile)',
							'name'         => 'mobile',
							'type'         => 'image',
							'instructions' => 'Usada em telas até 1023px.',
							'wrapper'      => array( 'width' => '50' ),
							'return_format' => 'array',
							'preview_size' => 'medium',
						),
						array(
							'key'          => 'field_home_banner_texto',
							'label'        => 'Texto do slide',
							'name'         => 'texto',
							'type'         => 'wysiwyg',
							'instructions' => 'Conteúdo exibido sobre o banner (título/chamada do slide).',
							'tabs'         => 'all',
							'toolbar'      => 'basic',
							'media_upload' => 0,
						),
						array(
							'key'          => 'field_home_banner_link_externo',
							'label'        => 'Tipo de link',
							'name'         => 'link_externo',
							'type'         => 'select',
							'instructions' => 'Define se o slide abre um link externo, um vídeo/iframe, ou nenhum link.',
							'choices'      => array(
								'nenhum'  => 'Nenhum',
								'externo' => 'Link externo',
								'iframe'  => 'Vídeo / iframe embutido',
							),
							'default_value' => 'nenhum',
							'return_format' => 'value',
						),
						array(
							'key'               => 'field_home_banner_url',
							'label'             => 'URL do link externo',
							'name'              => 'url',
							'type'              => 'url',
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_home_banner_link_externo',
										'operator' => '==',
										'value'    => 'externo',
									),
								),
							),
							'placeholder'       => 'https://...',
						),
						array(
							'key'               => 'field_home_banner_iframe',
							'label'             => 'URL do vídeo / iframe',
							'name'              => 'iframe',
							'type'              => 'url',
							'instructions'      => 'URL aberta dentro do fancybox (ex.: link do YouTube/Vimeo).',
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_home_banner_link_externo',
										'operator' => '==',
										'value'    => 'iframe',
									),
								),
							),
							'placeholder'       => 'https://...',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'description'           => 'Campos do banner (hero) da página Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Sobre. Usado por page-home.php (seção "about"). O conteúdo
	 * atual (copy + estatísticas) é gravado como valor real destes campos
	 * na primeira execução do tema em cada ambiente — ver inc/content-seed.php.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_home_about',
			'title'                 => 'Home — Sobre',
			'fields'                => array(
				array(
					'key'           => 'field_home_about_imagem',
					'label'         => 'Imagem',
					'name'          => 'imagem',
					'type'          => 'image',
					'instructions'  => 'Foto usada na seção "Sobre" (ex.: dentista atendendo) quando a galeria abaixo estiver vazia.',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'           => 'field_home_about_galeria',
					'label'         => 'Galeria de fotos',
					'name'          => 'galeria_sobre',
					'type'          => 'gallery',
					'instructions'  => 'Com 1 ou mais fotos aqui, a seção "Sobre" exibe um carrossel em vez da imagem única acima. Vazia, usa o campo "Imagem" (ou o espaço reservado padrão, se nenhum dos dois estiver definido).',
					'return_format' => 'array',
					'insert'        => 'append',
					'preview_size'  => 'medium',
				),
				array(
					'key'          => 'field_home_about_eyebrow',
					'label'        => 'Texto acima do título',
					'name'         => 'eyebrow',
					'type'         => 'text',
					'default_value' => 'Sobre a PRÓ-ODONTO',
				),
				array(
					'key'           => 'field_home_about_titulo',
					'label'         => 'Título',
					'name'          => 'titulo',
					'type'          => 'textarea',
					'instructions'  => 'Use uma quebra de linha onde quiser que o título quebre visualmente.',
					'placeholder'   => "Cuidado de verdade,\ndo primeiro sorriso ao último",
					'default_value' => "Cuidado de verdade,\ndo primeiro sorriso ao último",
					'rows'          => 3,
				),
				array(
					'key'           => 'field_home_about_texto',
					'label'         => 'Texto',
					'name'          => 'texto',
					'type'          => 'textarea',
					'rows'          => 4,
					'default_value' => 'Há mais de 20 anos ajudamos famílias a recuperar a saúde e a confiança do sorriso. Cada tratamento começa por ouvir você — e segue com transparência, tecnologia e o carinho que você merece.',
				),
				array(
					'key'          => 'field_home_about_estatisticas',
					'label'        => 'Estatísticas',
					'name'         => 'estatisticas',
					'type'         => 'repeater',
					'instructions' => 'Ex.: "+15 mil" / "sorrisos transformados".',
					'layout'       => 'table',
					'button_label' => 'Adicionar estatística',
					'sub_fields'   => array(
						array(
							'key'         => 'field_home_about_stat_valor',
							'label'       => 'Valor',
							'name'        => 'valor',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '30' ),
							'placeholder' => '+15 mil',
						),
						array(
							'key'         => 'field_home_about_stat_legenda',
							'label'       => 'Legenda',
							'name'        => 'legenda',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '70' ),
							'placeholder' => 'sorrisos transformados',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order'            => 2,
			'description'           => 'Campos da seção "Sobre" da página Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Letreiro de diferenciais (marquee). Repeater de itens com
	 * label + ícone (fragmento SVG, ver proodonto_sanitize_svg_fragment()
	 * em inc/template-functions.php).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_marquee',
			'title'      => 'Home — Letreiro de diferenciais',
			'fields'     => array(
				array(
					'key'          => 'field_home_marquee_itens',
					'label'        => 'Itens',
					'name'         => 'marquee_itens',
					'type'         => 'repeater',
					'instructions' => 'Diferenciais exibidos em loop no letreiro (marquee) abaixo do banner.',
					'layout'       => 'table',
					'button_label' => 'Adicionar item',
					'sub_fields'   => array(
						array(
							'key'         => 'field_home_marquee_label',
							'label'       => 'Texto',
							'name'        => 'label',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '60' ),
							'placeholder' => 'Atendimento humanizado',
						),
						array(
							'key'          => 'field_home_marquee_icone',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt; (ex.: &lt;path d="..."/&gt;), sem a tag &lt;svg&gt; em volta. ViewBox fixo em 0 0 24 24.',
							'wrapper'      => array( 'width' => '40' ),
							'rows'         => 2,
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 1,
			'description' => 'Itens do letreiro de diferenciais da Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Resultados (Antes e Depois). Cabeçalho da seção + repeater
	 * de pacientes (nome + foto). As fotos são importadas para a
	 * Biblioteca de Mídia automaticamente na primeira execução do tema
	 * — ver inc/content-seed.php.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_results',
			'title'      => 'Home — Resultados (Antes e Depois)',
			'fields'     => array(
				array(
					'key'           => 'field_home_results_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'results_eyebrow',
					'type'          => 'text',
					'default_value' => 'Resultados reais',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_results_titulo',
					'label'         => 'Título',
					'name'          => 'results_titulo',
					'type'          => 'text',
					'default_value' => 'Sorrisos transformados',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_results_cta_label',
					'label'         => 'Texto do botão (CTA)',
					'name'          => 'results_cta_label',
					'type'          => 'text',
					'instructions'  => 'O link usa sempre o WhatsApp padrão do tema (Opções do Tema).',
					'default_value' => 'Quero um sorriso assim',
					'wrapper'       => array( 'width' => '34' ),
				),
				array(
					'key'           => 'field_home_results_texto',
					'label'         => 'Texto',
					'name'          => 'results_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Veja alguns dos resultados que conquistamos com nossos pacientes.',
				),
				array(
					'key'          => 'field_home_results_itens',
					'label'        => 'Pacientes',
					'name'         => 'results_itens',
					'type'         => 'repeater',
					'instructions' => 'Foto real do paciente (composição única antes/depois). Sem informação de tratamento de propósito — ver comentário original em page-home.php.',
					'layout'       => 'table',
					'button_label' => 'Adicionar paciente',
					'sub_fields'   => array(
						array(
							'key'         => 'field_home_results_nome',
							'label'       => 'Nome',
							'name'        => 'nome',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '30' ),
						),
						array(
							'key'           => 'field_home_results_foto',
							'label'         => 'Foto',
							'name'          => 'foto',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
							'wrapper'       => array( 'width' => '70' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 3,
			'description' => 'Cabeçalho e pacientes da seção "Resultados" (antes e depois) da Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Tratamentos. Cabeçalho + repeater de cards (título, texto,
	 * ícone SVG e link).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_treatments',
			'title'      => 'Home — Tratamentos',
			'fields'     => array(
				array(
					'key'           => 'field_home_treatments_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'treatments_eyebrow',
					'type'          => 'text',
					'default_value' => 'Tratamentos',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_home_treatments_titulo',
					'label'         => 'Título',
					'name'          => 'treatments_titulo',
					'type'          => 'text',
					'default_value' => 'Como podemos ajudar você',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_home_treatments_texto',
					'label'         => 'Texto',
					'name'          => 'treatments_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Todas as especialidades em um só lugar, com profissionais dedicados a cada tipo de cuidado.',
				),
				array(
					'key'          => 'field_home_treatments_itens',
					'label'        => 'Tratamentos',
					'name'         => 'treatments_itens',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar tratamento',
					'sub_fields'   => array(
						array(
							'key'     => 'field_home_treatments_titulo_item',
							'label'   => 'Título',
							'name'    => 'titulo',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_home_treatments_texto_item',
							'label'   => 'Texto',
							'name'    => 'texto',
							'type'    => 'textarea',
							'rows'    => 2,
							'wrapper' => array( 'width' => '45' ),
						),
						array(
							'key'          => 'field_home_treatments_icone_item',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt;, viewBox 0 0 24 24.',
							'rows'         => 2,
							'wrapper'      => array( 'width' => '25' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 4,
			'description' => 'Cabeçalho e cards da seção "Tratamentos" da Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Shorts (YouTube). Carrossel de vídeos curtos, entre
	 * "Tratamentos" e "Passo a passo". Miniatura e player são derivados
	 * automaticamente do link do YouTube (proodonto_get_youtube_id() /
	 * proodonto_get_youtube_thumbnail_url(), em inc/template-functions.php)
	 * — não depende de nenhuma chave de API.
	 *
	 * Sem vídeos reais da PRÓ-ODONTO ainda cadastrados, o repeater nasce
	 * vazio de propósito (nenhum link de exemplo é inventado — um vídeo
	 * de terceiro atribuído à clínica seria pior que não ter seção
	 * nenhuma). page-home.php já trata isso: sem nenhum item com link
	 * válido, a seção inteira não é exibida.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_shorts',
			'title'      => 'Home — Shorts (YouTube)',
			'fields'     => array(
				array(
					'key'     => 'field_home_shorts_aviso',
					'label'   => '',
					'name'    => 'shorts_aviso',
					'type'    => 'message',
					'message' => 'Adicione o link de cada vídeo do YouTube abaixo (Shorts, "watch?v=" ou "youtu.be" — qualquer um funciona). A miniatura e o player são gerados automaticamente a partir do link, sem precisar enviar nenhuma imagem. <strong>Sem nenhum vídeo cadastrado, esta seção não aparece no site.</strong>',
				),
				array(
					'key'           => 'field_home_shorts_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'shorts_eyebrow',
					'type'          => 'text',
					'default_value' => 'Vídeos',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_shorts_titulo',
					'label'         => 'Título',
					'name'          => 'shorts_titulo',
					'type'          => 'text',
					'default_value' => 'Acompanhe nos nossos Shorts',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_shorts_texto',
					'label'         => 'Texto',
					'name'          => 'shorts_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Bastidores, dicas rápidas e resultados reais — direto do nosso canal no YouTube.',
					'wrapper'       => array( 'width' => '34' ),
				),
				array(
					'key'          => 'field_home_shorts_itens',
					'label'        => 'Vídeos',
					'name'         => 'shorts_itens',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar vídeo',
					'sub_fields'   => array(
						array(
							'key'          => 'field_home_shorts_url',
							'label'        => 'Link do YouTube',
							'name'         => 'url',
							'type'         => 'url',
							'instructions' => 'Aceita link de Shorts, vídeo normal (watch?v=) ou youtu.be — o ID é extraído automaticamente.',
							'placeholder'  => 'https://www.youtube.com/shorts/XXXXXXXXXXX',
						),
						array(
							'key'          => 'field_home_shorts_titulo_item',
							'label'        => 'Título / legenda (opcional)',
							'name'         => 'titulo',
							'type'         => 'text',
							'instructions' => 'Exibido como legenda sobre a miniatura, se preenchido.',
						),
						array(
							'key'           => 'field_home_shorts_capa',
							'label'         => 'Capa personalizada (opcional)',
							'name'          => 'capa_personalizada',
							'type'          => 'image',
							'instructions'  => 'Sem capa própria, usamos a miniatura automática do vídeo no YouTube.',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 5,
			'description' => 'Carrossel de vídeos (YouTube Shorts) entre "Tratamentos" e "Passo a passo".',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Passo a passo. Cabeçalho + repeater de etapas (label, texto,
	 * ícone SVG e marcação da etapa final/"sucesso").
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_steps',
			'title'      => 'Home — Passo a passo',
			'fields'     => array(
				array(
					'key'           => 'field_home_steps_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'steps_eyebrow',
					'type'          => 'text',
					'default_value' => 'Passo a passo',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_home_steps_titulo',
					'label'         => 'Título',
					'name'          => 'steps_titulo',
					'type'          => 'text',
					'default_value' => 'Como é o seu tratamento',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'          => 'field_home_steps_itens',
					'label'        => 'Etapas',
					'name'         => 'steps_itens',
					'type'         => 'repeater',
					'instructions' => 'A numeração "ETAPA 01, 02..." é automática, na ordem das linhas abaixo.',
					'layout'       => 'block',
					'button_label' => 'Adicionar etapa',
					'sub_fields'   => array(
						array(
							'key'     => 'field_home_steps_label_item',
							'label'   => 'Nome da etapa',
							'name'    => 'label',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_home_steps_texto_item',
							'label'   => 'Texto',
							'name'    => 'texto',
							'type'    => 'textarea',
							'rows'    => 2,
							'wrapper' => array( 'width' => '45' ),
						),
						array(
							'key'          => 'field_home_steps_icone_item',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt;, viewBox 0 0 24 24.',
							'rows'         => 2,
							'wrapper'      => array( 'width' => '15' ),
						),
						array(
							'key'           => 'field_home_steps_sucesso_item',
							'label'         => 'Etapa final (estilo de destaque)',
							'name'          => 'sucesso',
							'type'          => 'true_false',
							'ui'            => 1,
							'default_value' => 0,
							'wrapper'       => array( 'width' => '10' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 6,
			'description' => 'Cabeçalho e etapas da seção "Passo a passo" da Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Avaliações. Só o cabeçalho: o widget de avaliações em si
	 * vem do shortcode [trustindex] (plugin), não é conteúdo do tema.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_reviews',
			'title'      => 'Home — Avaliações',
			'fields'     => array(
				array(
					'key'           => 'field_home_reviews_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'reviews_eyebrow',
					'type'          => 'text',
					'default_value' => 'Avaliações',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_reviews_titulo',
					'label'         => 'Título',
					'name'          => 'reviews_titulo',
					'type'          => 'text',
					'default_value' => 'O que dizem sobre nós',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_reviews_texto',
					'label'         => 'Texto',
					'name'          => 'reviews_texto',
					'type'          => 'text',
					'default_value' => 'Depoimentos reais de quem já passou pela PRÓ-ODONTO.',
					'wrapper'       => array( 'width' => '34' ),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 7,
			'description' => 'Cabeçalho da seção "Avaliações" da Home (o widget de avaliações em si vem do plugin, via shortcode).',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Unidades (cabeçalho). A lista de unidades em si continua
	 * em inc/units-map.php, de propósito: é a mesma fonte usada para
	 * gerar o mapa estático (cache em disco) e os cards — duplicar isso
	 * aqui como ACF criaria duas fontes de verdade para o mesmo dado.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_units',
			'title'      => 'Home — Unidades (cabeçalho)',
			'fields'     => array(
				array(
					'key'   => 'field_home_units_aviso',
					'label' => '',
					'name'  => 'aviso_unidades',
					'type'  => 'message',
					'message' => 'A lista de unidades (endereços, links do Maps/WhatsApp) é editada em código, em <code>inc/units-map.php</code> — é a mesma fonte usada para gerar o mapa estático da seção. Aqui você edita só os textos de cabeçalho abaixo.',
				),
				array(
					'key'           => 'field_home_units_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'units_eyebrow',
					'type'          => 'text',
					'default_value' => 'Unidades',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_units_titulo',
					'label'         => 'Título',
					'name'          => 'units_titulo',
					'type'          => 'text',
					'default_value' => 'Uma PRÓ-ODONTO perto de você',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_home_units_texto',
					'label'         => 'Texto',
					'name'          => 'units_texto',
					'type'          => 'text',
					'default_value' => 'Escolha a unidade mais próxima e fale direto com a nossa equipe.',
					'wrapper'       => array( 'width' => '34' ),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 8,
			'description' => 'Cabeçalho da seção "Unidades" da Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — Blog. Cabeçalho apenas — os posts em si agora são os
	 * ÚLTIMOS POSTS REAIS do blog (post type nativo "post"), buscados
	 * direto em page-home.php via get_posts() e exibidos em carrossel.
	 * Substituiu a "vitrine" fixa (repeater de cards inventados) que
	 * existia antes: sem nenhum post publicado ainda, a seção inteira
	 * não é exibida (ver page-home.php) — em vez de mostrar cards
	 * fictícios sem link real.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_blog',
			'title'      => 'Home — Blog',
			'fields'     => array(
				array(
					'key'           => 'field_home_blog_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'blog_eyebrow',
					'type'          => 'text',
					'default_value' => 'Blog',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_home_blog_titulo',
					'label'         => 'Título',
					'name'          => 'blog_titulo',
					'type'          => 'text',
					'default_value' => 'Dicas para o seu sorriso',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_home_blog_quantidade',
					'label'         => 'Quantidade de posts',
					'name'          => 'blog_quantidade',
					'type'          => 'number',
					'instructions'  => 'Quantos dos últimos posts publicados exibir no carrossel.',
					'default_value' => 6,
					'min'           => 3,
					'max'           => 12,
					'step'          => 1,
					'wrapper'       => array( 'width' => '15' ),
				),
				array(
					'key'           => 'field_home_blog_link_label',
					'label'         => 'Texto do link "ver todos"',
					'name'          => 'blog_link_label',
					'type'          => 'text',
					'default_value' => 'Ver todos os artigos →',
					'wrapper'       => array( 'width' => '17.5' ),
				),
				array(
					'key'          => 'field_home_blog_link_url',
					'label'        => 'URL do link "ver todos"',
					'name'         => 'blog_link_url',
					'type'         => 'url',
					'instructions' => 'Em branco, usa automaticamente a página de posts do site (Configurações → Leitura).',
					'wrapper'      => array( 'width' => '17.5' ),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 9,
			'description' => 'Cabeçalho e posts em destaque da seção "Blog" da Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Home — CTA final.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_home_closing_cta',
			'title'      => 'Home — CTA final',
			'fields'     => array(
				array(
					'key'           => 'field_home_closing_titulo',
					'label'         => 'Título',
					'name'          => 'closing_titulo',
					'type'          => 'text',
					'default_value' => 'Vamos cuidar do seu sorriso?',
				),
				array(
					'key'           => 'field_home_closing_texto',
					'label'         => 'Texto',
					'name'          => 'closing_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Fale agora com a nossa equipe pelo WhatsApp e agende sua avaliação — é rápido, gratuito e sem compromisso.',
				),
				array(
					'key'           => 'field_home_closing_botao_label',
					'label'         => 'Texto do botão',
					'name'          => 'closing_botao_label',
					'type'          => 'text',
					'instructions'  => 'O link usa sempre o WhatsApp padrão do tema (Opções do Tema).',
					'default_value' => 'Chamar no WhatsApp',
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-home.php',
					),
				),
			),
			'menu_order' => 9,
			'description' => 'Título, texto e botão do CTA final da Home.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — URL dos CTAs. Ver page-vendas.php.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_vendas_cta',
			'title'                 => 'Página de Vendas — CTA',
			'fields'                => array(
				array(
					'key'          => 'field_vendas_cta_url',
					'label'        => 'URL dos CTAs da página',
					'name'         => 'cta_url',
					'type'         => 'url',
					'instructions' => 'Usada em todos os botões de CTA da página (Resultados, Sobre, Tratamentos, Passo a passo, Avaliações, Unidades e o CTA final) — pode ser um link wa.me/, um link de rastreio (ex.: api.upviewcrm.com) ou qualquer outra URL de destino da campanha. O banner (hero) tem seus próprios links, definidos slide a slide, e não é afetado por este campo. Se deixado em branco, usa o WhatsApp padrão do tema.',
					'placeholder'  => 'https://wa.me/5511999999999?text=...',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order'            => 0,
			'description'           => 'URL de destino dos CTAs em qualquer página que use o template "Página de Vendas".',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Letreiro de diferenciais (marquee). Mesma
	 * estrutura do grupo homônimo da Home (group_home_marquee), copy
	 * própria da página de vendas.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_marquee',
			'title'      => 'Página de Vendas — Letreiro de diferenciais',
			'fields'     => array(
				array(
					'key'          => 'field_vendas_marquee_itens',
					'label'        => 'Itens',
					'name'         => 'marquee_itens',
					'type'         => 'repeater',
					'instructions' => 'Diferenciais exibidos em loop no letreiro (marquee) abaixo do banner.',
					'layout'       => 'table',
					'button_label' => 'Adicionar item',
					'sub_fields'   => array(
						array(
							'key'         => 'field_vendas_marquee_label',
							'label'       => 'Texto',
							'name'        => 'label',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '60' ),
							'placeholder' => 'Atendimento humanizado',
						),
						array(
							'key'          => 'field_vendas_marquee_icone',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt; (ex.: &lt;path d="..."/&gt;), sem a tag &lt;svg&gt; em volta. ViewBox fixo em 0 0 24 24.',
							'wrapper'      => array( 'width' => '40' ),
							'rows'         => 2,
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 1,
			'description' => 'Itens do letreiro de diferenciais da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Sobre (texto). A galeria de fotos continua no
	 * grupo separado group_vendas_about_gallery (abaixo).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_about',
			'title'      => 'Página de Vendas — Sobre (texto)',
			'fields'     => array(
				array(
					'key'           => 'field_vendas_about_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'eyebrow',
					'type'          => 'text',
					'default_value' => 'Sobre a ProOdonto',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_vendas_about_cta_label',
					'label'         => 'Texto do botão (CTA da seção)',
					'name'          => 'about_cta_label',
					'type'          => 'text',
					'instructions'  => 'O link usa a "URL dos CTAs da página" definida no grupo "Página de Vendas — CTA".',
					'default_value' => 'AGENDAR AVALIAÇÃO GRATUITA',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_vendas_about_titulo',
					'label'         => 'Título',
					'name'          => 'titulo',
					'type'          => 'textarea',
					'instructions'  => 'Use uma quebra de linha onde quiser que o título quebre visualmente.',
					'rows'          => 3,
					'default_value' => "Devolvemos muito mais que dentes.\nDevolvemos qualidade de vida.",
				),
				array(
					'key'           => 'field_vendas_about_texto',
					'label'         => 'Texto',
					'name'          => 'texto',
					'type'          => 'textarea',
					'rows'          => 4,
					'default_value' => 'Na ProOdonto, cada tratamento começa ouvindo a sua história — e só termina quando você volta a sorrir, comer e viver com confiança. Somos referência em odontologia em Sergipe porque cuidamos de pessoas, não apenas de procedimentos.',
				),
				array(
					'key'          => 'field_vendas_about_estatisticas',
					'label'        => 'Estatísticas',
					'name'         => 'estatisticas',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Adicionar estatística',
					'sub_fields'   => array(
						array(
							'key'         => 'field_vendas_about_stat_valor',
							'label'       => 'Valor',
							'name'        => 'valor',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '30' ),
							'placeholder' => '+15 mil',
						),
						array(
							'key'         => 'field_vendas_about_stat_legenda',
							'label'       => 'Legenda',
							'name'        => 'legenda',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '70' ),
							'placeholder' => 'sorrisos transformados',
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 2,
			'description' => 'Copy e estatísticas da seção "Sobre" da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Galeria "Sobre". Ver page-vendas.php.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_vendas_about_gallery',
			'title'                 => 'Página de Vendas — Galeria "Sobre"',
			'fields'                => array(
				array(
					'key'          => 'field_vendas_about_galeria',
					'label'        => 'Galeria de fotos',
					'name'         => 'galeria_sobre',
					'type'         => 'gallery',
					'instructions' => 'Fotos exibidas em carrossel na seção "Sobre". Sem nenhuma foto cadastrada, a seção usa uma imagem de espaço reservado.',
					'return_format' => 'array',
					'insert'       => 'append',
					'preview_size' => 'medium',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order'            => 3,
			'description'           => 'Fotos da seção "Sobre" na Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Resultados (Antes e Depois).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_results',
			'title'      => 'Página de Vendas — Resultados (Antes e Depois)',
			'fields'     => array(
				array(
					'key'           => 'field_vendas_results_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'results_eyebrow',
					'type'          => 'text',
					'default_value' => 'Resultados reais',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_results_titulo',
					'label'         => 'Título',
					'name'          => 'results_titulo',
					'type'          => 'text',
					'default_value' => 'Sorrisos transformados',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_results_cta_label',
					'label'         => 'Texto do botão (CTA)',
					'name'          => 'results_cta_label',
					'type'          => 'text',
					'instructions'  => 'O link usa a "URL dos CTAs da página" definida no grupo "Página de Vendas — CTA".',
					'default_value' => 'QUERO MEU SORRISO',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_results_texto',
					'label'         => 'Texto',
					'name'          => 'results_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Pacientes reais da ProOdonto que recuperaram a saúde bucal e a confiança para sorrir. O próximo resultado pode ser o seu.',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'          => 'field_vendas_results_itens',
					'label'        => 'Pacientes',
					'name'         => 'results_itens',
					'type'         => 'repeater',
					'instructions' => 'Foto real do paciente (composição única antes/depois). Sem informação de tratamento de propósito — ver comentário original em page-vendas.php.',
					'layout'       => 'table',
					'button_label' => 'Adicionar paciente',
					'sub_fields'   => array(
						array(
							'key'     => 'field_vendas_results_nome',
							'label'   => 'Nome',
							'name'    => 'nome',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'           => 'field_vendas_results_foto',
							'label'         => 'Foto',
							'name'          => 'foto',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
							'wrapper'       => array( 'width' => '70' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 4,
			'description' => 'Cabeçalho e pacientes da seção "Resultados" (antes e depois) da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Tratamentos.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_treatments',
			'title'      => 'Página de Vendas — Tratamentos',
			'fields'     => array(
				array(
					'key'           => 'field_vendas_treatments_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'treatments_eyebrow',
					'type'          => 'text',
					'default_value' => 'Tratamentos',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_vendas_treatments_titulo',
					'label'         => 'Título',
					'name'          => 'treatments_titulo',
					'type'          => 'text',
					'default_value' => 'Encontre o tratamento certo para o seu sorriso',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_vendas_treatments_cta_label',
					'label'         => 'Texto do botão (CTA da seção)',
					'name'          => 'treatments_cta_label',
					'type'          => 'text',
					'default_value' => 'QUERO MEU TRATAMENTO',
					'wrapper'       => array( 'width' => '34' ),
				),
				array(
					'key'           => 'field_vendas_treatments_texto',
					'label'         => 'Texto',
					'name'          => 'treatments_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Reunimos todas as especialidades em um só lugar, com profissionais dedicados a cuidar de cada etapa — do diagnóstico ao resultado final.',
				),
				array(
					'key'          => 'field_vendas_treatments_itens',
					'label'        => 'Tratamentos',
					'name'         => 'treatments_itens',
					'type'         => 'repeater',
					'instructions' => 'No mobile (<600px) estes cards viram um carrossel automático (ver assets/js/pages/home.js) — não é preciso configurar nada aqui pra isso.',
					'layout'       => 'block',
					'button_label' => 'Adicionar tratamento',
					'sub_fields'   => array(
						array(
							'key'     => 'field_vendas_treatments_titulo_item',
							'label'   => 'Título',
							'name'    => 'titulo',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_vendas_treatments_texto_item',
							'label'   => 'Texto',
							'name'    => 'texto',
							'type'    => 'textarea',
							'rows'    => 2,
							'wrapper' => array( 'width' => '45' ),
						),
						array(
							'key'          => 'field_vendas_treatments_icone_item',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt;, viewBox 0 0 24 24.',
							'rows'         => 2,
							'wrapper'      => array( 'width' => '25' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 5,
			'description' => 'Cabeçalho e cards da seção "Tratamentos" da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Shorts (YouTube). Mesma seção/carrossel da Home
	 * (group_home_shorts) — reaproveita o MESMO CSS/JS (assets/css/pages/
	 * home.css e assets/js/pages/home.js já carregam nesta página, ver
	 * inc/enqueue.php: a Página de Vendas reusa os assets da Home). Só
	 * este grupo de campos e a marcação em page-vendas.php são próprios,
	 * incluindo o CTA de reforço padrão desta página (proodonto_vendas_section_cta()).
	 *
	 * Sem vídeos reais ainda cadastrados, o repeater nasce vazio de
	 * propósito — mesmo motivo do grupo homônimo da Home.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_shorts',
			'title'      => 'Página de Vendas — Shorts (YouTube)',
			'fields'     => array(
				array(
					'key'     => 'field_vendas_shorts_aviso',
					'label'   => '',
					'name'    => 'shorts_aviso',
					'type'    => 'message',
					'message' => 'Adicione o link de cada vídeo do YouTube abaixo (Shorts, "watch?v=" ou "youtu.be" — qualquer um funciona). A miniatura e o player são gerados automaticamente a partir do link, sem precisar enviar nenhuma imagem. <strong>Sem nenhum vídeo cadastrado, esta seção não aparece no site.</strong>',
				),
				array(
					'key'           => 'field_vendas_shorts_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'shorts_eyebrow',
					'type'          => 'text',
					'default_value' => 'Vídeos',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_shorts_titulo',
					'label'         => 'Título',
					'name'          => 'shorts_titulo',
					'type'          => 'text',
					'default_value' => 'Veja de perto o nosso atendimento',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_shorts_cta_label',
					'label'         => 'Texto do botão (CTA da seção)',
					'name'          => 'shorts_cta_label',
					'type'          => 'text',
					'instructions'  => 'O link usa a "URL dos CTAs da página" definida no grupo "Página de Vendas — CTA".',
					'default_value' => 'QUERO AGENDAR AGORA',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_shorts_texto',
					'label'         => 'Texto',
					'name'          => 'shorts_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Bastidores, depoimentos e dicas rápidas — direto do nosso canal no YouTube.',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'          => 'field_vendas_shorts_itens',
					'label'        => 'Vídeos',
					'name'         => 'shorts_itens',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar vídeo',
					'sub_fields'   => array(
						array(
							'key'          => 'field_vendas_shorts_url',
							'label'        => 'Link do YouTube',
							'name'         => 'url',
							'type'         => 'url',
							'instructions' => 'Aceita link de Shorts, vídeo normal (watch?v=) ou youtu.be — o ID é extraído automaticamente.',
							'placeholder'  => 'https://www.youtube.com/shorts/XXXXXXXXXXX',
						),
						array(
							'key'          => 'field_vendas_shorts_titulo_item',
							'label'        => 'Título / legenda (opcional)',
							'name'         => 'titulo',
							'type'         => 'text',
							'instructions' => 'Exibido como legenda sobre a miniatura, se preenchido.',
						),
						array(
							'key'           => 'field_vendas_shorts_capa',
							'label'         => 'Capa personalizada (opcional)',
							'name'          => 'capa_personalizada',
							'type'          => 'image',
							'instructions'  => 'Sem capa própria, usamos a miniatura automática do vídeo no YouTube.',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 6,
			'description' => 'Carrossel de vídeos (YouTube Shorts) entre "Tratamentos" e "Passo a passo" da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Passo a passo.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_steps',
			'title'      => 'Página de Vendas — Passo a passo',
			'fields'     => array(
				array(
					'key'           => 'field_vendas_steps_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'steps_eyebrow',
					'type'          => 'text',
					'default_value' => 'Passo a passo',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_vendas_steps_titulo',
					'label'         => 'Título',
					'name'          => 'steps_titulo',
					'type'          => 'text',
					'default_value' => 'O caminho para você voltar a sorrir',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_vendas_steps_cta_label',
					'label'         => 'Texto do botão (CTA da seção)',
					'name'          => 'steps_cta_label',
					'type'          => 'text',
					'default_value' => 'QUERO COMEÇAR AGORA',
					'wrapper'       => array( 'width' => '34' ),
				),
				array(
					'key'          => 'field_vendas_steps_itens',
					'label'        => 'Etapas',
					'name'         => 'steps_itens',
					'type'         => 'repeater',
					'instructions' => 'A numeração "ETAPA 01, 02..." é automática, na ordem das linhas abaixo.',
					'layout'       => 'block',
					'button_label' => 'Adicionar etapa',
					'sub_fields'   => array(
						array(
							'key'     => 'field_vendas_steps_label_item',
							'label'   => 'Nome da etapa',
							'name'    => 'label',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_vendas_steps_texto_item',
							'label'   => 'Texto',
							'name'    => 'texto',
							'type'    => 'textarea',
							'rows'    => 2,
							'wrapper' => array( 'width' => '45' ),
						),
						array(
							'key'          => 'field_vendas_steps_icone_item',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt;, viewBox 0 0 24 24.',
							'rows'         => 2,
							'wrapper'      => array( 'width' => '15' ),
						),
						array(
							'key'           => 'field_vendas_steps_sucesso_item',
							'label'         => 'Etapa final (estilo de destaque)',
							'name'          => 'sucesso',
							'type'          => 'true_false',
							'ui'            => 1,
							'default_value' => 0,
							'wrapper'       => array( 'width' => '10' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 7,
			'description' => 'Cabeçalho e etapas da seção "Passo a passo" da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Avaliações. Só o cabeçalho: o widget vem do
	 * shortcode [trustindex] (plugin), não é conteúdo do tema.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_reviews',
			'title'      => 'Página de Vendas — Avaliações',
			'fields'     => array(
				array(
					'key'           => 'field_vendas_reviews_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'reviews_eyebrow',
					'type'          => 'text',
					'default_value' => 'Avaliações',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_reviews_titulo',
					'label'         => 'Título',
					'name'          => 'reviews_titulo',
					'type'          => 'text',
					'default_value' => 'Quem já passou pela ProOdonto recomenda',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_reviews_cta_label',
					'label'         => 'Texto do botão (CTA da seção)',
					'name'          => 'reviews_cta_label',
					'type'          => 'text',
					'default_value' => 'QUERO ESSE RESULTADO',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_reviews_texto',
					'label'         => 'Texto',
					'name'          => 'reviews_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Depoimentos reais de pacientes que recuperaram a saúde e a autoestima do sorriso com a gente.',
					'wrapper'       => array( 'width' => '25' ),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 8,
			'description' => 'Cabeçalho da seção "Avaliações" da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — Unidades (cabeçalho). A lista de unidades em
	 * si continua em inc/units-map.php — ver comentário no grupo
	 * homônimo da Home (group_home_units).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_units',
			'title'      => 'Página de Vendas — Unidades (cabeçalho)',
			'fields'     => array(
				array(
					'key'     => 'field_vendas_units_aviso',
					'label'   => '',
					'name'    => 'aviso_unidades',
					'type'    => 'message',
					'message' => 'A lista de unidades (endereços, links do Maps/WhatsApp) é editada em código, em <code>inc/units-map.php</code>. Nesta página de vendas, a seção "Unidades" mostra só o mapa (sem os cards) — ver page-vendas.php.',
				),
				array(
					'key'           => 'field_vendas_units_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'units_eyebrow',
					'type'          => 'text',
					'default_value' => 'Unidades',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_units_titulo',
					'label'         => 'Título',
					'name'          => 'units_titulo',
					'type'          => 'text',
					'default_value' => 'Uma ProOdonto perto de você',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_units_cta_label',
					'label'         => 'Texto do botão (CTA da seção)',
					'name'          => 'units_cta_label',
					'type'          => 'text',
					'default_value' => 'FALAR COM UNIDADE',
					'wrapper'       => array( 'width' => '25' ),
				),
				array(
					'key'           => 'field_vendas_units_texto',
					'label'         => 'Texto',
					'name'          => 'units_texto',
					'type'          => 'text',
					'default_value' => 'Escolha a unidade mais próxima e fale direto com a nossa equipe para agendar sua avaliação gratuita.',
					'wrapper'       => array( 'width' => '25' ),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 9,
			'description' => 'Cabeçalho da seção "Unidades" da Página de Vendas.',
		)
	);

	/* ---------------------------------------------------------------
	 * Página de Vendas — CTA final.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_vendas_closing_cta',
			'title'      => 'Página de Vendas — CTA final',
			'fields'     => array(
				array(
					'key'           => 'field_vendas_closing_titulo',
					'label'         => 'Título',
					'name'          => 'closing_titulo',
					'type'          => 'text',
					'default_value' => 'Pronto para voltar a sorrir com confiança?',
				),
				array(
					'key'           => 'field_vendas_closing_texto',
					'label'         => 'Texto',
					'name'          => 'closing_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Fale agora com a nossa equipe pelo WhatsApp e agende sua avaliação gratuita — é rápido, sem burocracia e sem compromisso.',
				),
				array(
					'key'           => 'field_vendas_closing_botao_label',
					'label'         => 'Texto do botão',
					'name'          => 'closing_botao_label',
					'type'          => 'text',
					'instructions'  => 'O link usa a "URL dos CTAs da página" definida no grupo "Página de Vendas — CTA".',
					'default_value' => 'CHAMAR NO WHATSAPP',
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-vendas.php',
					),
				),
			),
			'menu_order' => 10,
			'description' => 'Título, texto e botão do CTA final da Página de Vendas.',
		)
	);

	/* =================================================================
	 * Página "Sobre / Quem Somos" (page-sobre.php) — 9 grupos, um por
	 * seção, mesmo padrão das páginas Home/Vendas. Nomes de campo
	 * prefixados por seção (hero_*, historia_*, valores_*...) mesmo
	 * sendo um template novo, para já nascer sem risco de colisão no
	 * post meta caso outra seção reaproveite um nome genérico no futuro.
	 * ================================================================ */

	/* ---------------------------------------------------------------
	 * Sobre — Hero (topo da página, sem carrossel).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_hero',
			'title'      => 'Sobre — Hero (topo)',
			'fields'     => array(
				array(
					'key'           => 'field_sobre_hero_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'hero_eyebrow',
					'type'          => 'text',
					'default_value' => 'Quem somos',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_hero_cta_label',
					'label'         => 'Texto do botão (CTA)',
					'name'          => 'hero_cta_label',
					'type'          => 'text',
					'instructions'  => 'O link usa sempre o WhatsApp padrão do tema (Opções do Tema).',
					'default_value' => 'Agendar avaliação gratuita',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_hero_titulo',
					'label'         => 'Título (H1 da página)',
					'name'          => 'hero_titulo',
					'type'          => 'textarea',
					'instructions'  => 'Este é o único H1 visível da página — capriche.',
					'rows'          => 2,
					'default_value' => 'Cuidado odontológico com propósito, há anos ao lado de Sergipe',
				),
				array(
					'key'           => 'field_sobre_hero_texto',
					'label'         => 'Texto',
					'name'          => 'hero_texto',
					'type'          => 'textarea',
					'rows'          => 3,
					'default_value' => 'Somos a PRÓ-ODONTO: uma equipe de cirurgiões-dentistas e especialistas dedicados a devolver saúde bucal, autoestima e qualidade de vida para cada paciente que passa pelas nossas unidades.',
				),
				array(
					'key'           => 'field_sobre_hero_imagem',
					'label'         => 'Imagem',
					'name'          => 'hero_imagem',
					'type'          => 'image',
					'instructions'  => 'Foto da equipe, recepção ou fachada de uma unidade. Sem imagem cadastrada, usa um espaço reservado.',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 0,
			'description' => 'Hero (topo) da página Sobre / Quem Somos.',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — Nossa História (linha do tempo).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_historia',
			'title'      => 'Sobre — Nossa História',
			'fields'     => array(
				array(
					'key'           => 'field_sobre_historia_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'historia_eyebrow',
					'type'          => 'text',
					'default_value' => 'Nossa história',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_sobre_historia_titulo',
					'label'         => 'Título',
					'name'          => 'historia_titulo',
					'type'          => 'text',
					'default_value' => 'Uma trajetória construída sorriso a sorriso',
					'wrapper'       => array( 'width' => '67' ),
				),
				array(
					'key'           => 'field_sobre_historia_texto',
					'label'         => 'Texto',
					'name'          => 'historia_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Começamos com o compromisso de oferecer uma odontologia acessível, humana e tecnicamente atualizada — e crescemos junto com a confiança de cada paciente e cada indicação.',
				),
				array(
					'key'          => 'field_sobre_historia_itens',
					'label'        => 'Marcos da história',
					'name'         => 'historia_itens',
					'type'         => 'repeater',
					'instructions' => 'Use marcos reais (evite datas/fatos inventados). Ex.: "Origem", "Expansão", "Hoje".',
					'layout'       => 'block',
					'button_label' => 'Adicionar marco',
					'sub_fields'   => array(
						array(
							'key'         => 'field_sobre_historia_ano',
							'label'       => 'Marco / período',
							'name'        => 'ano',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '20' ),
							'placeholder' => 'Origem',
						),
						array(
							'key'     => 'field_sobre_historia_titulo_item',
							'label'   => 'Título',
							'name'    => 'titulo',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_sobre_historia_texto_item',
							'label'   => 'Texto',
							'name'    => 'texto',
							'type'    => 'textarea',
							'rows'    => 2,
							'wrapper' => array( 'width' => '50' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 1,
			'description' => 'Linha do tempo da seção "Nossa História" da página Sobre.',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — Missão, Visão e Valores.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_valores',
			'title'      => 'Sobre — Missão, Visão e Valores',
			'fields'     => array(
				array(
					'key'           => 'field_sobre_valores_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'valores_eyebrow',
					'type'          => 'text',
					'default_value' => 'Missão, visão e valores',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_valores_titulo',
					'label'         => 'Título',
					'name'          => 'valores_titulo',
					'type'          => 'text',
					'default_value' => 'O que nos guia todos os dias',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_missao_titulo',
					'label'         => 'Missão — título',
					'name'          => 'missao_titulo',
					'type'          => 'text',
					'default_value' => 'Missão',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_visao_titulo',
					'label'         => 'Visão — título',
					'name'          => 'visao_titulo',
					'type'          => 'text',
					'default_value' => 'Visão',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_missao_texto',
					'label'         => 'Missão — texto',
					'name'          => 'missao_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Oferecer odontologia de qualidade, acessível e humanizada, devolvendo saúde bucal e autoestima para cada paciente.',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_visao_texto',
					'label'         => 'Visão — texto',
					'name'          => 'visao_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Ser a referência em cuidado odontológico em Sergipe, reconhecida pela excelência clínica e pelo acolhimento em cada atendimento.',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'          => 'field_sobre_valores_itens',
					'label'        => 'Valores',
					'name'         => 'valores_itens',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar valor',
					'sub_fields'   => array(
						array(
							'key'     => 'field_sobre_valores_titulo_item',
							'label'   => 'Título',
							'name'    => 'titulo',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_sobre_valores_texto_item',
							'label'   => 'Texto',
							'name'    => 'texto',
							'type'    => 'textarea',
							'rows'    => 2,
							'wrapper' => array( 'width' => '45' ),
						),
						array(
							'key'          => 'field_sobre_valores_icone_item',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt;, viewBox 0 0 24 24.',
							'rows'         => 2,
							'wrapper'      => array( 'width' => '25' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 2,
			'description' => 'Missão, visão e cards de valores da página Sobre.',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — Números (estatísticas). Mesmos números já usados na Home,
	 * mantidos consistentes entre páginas de propósito.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_numeros',
			'title'      => 'Sobre — Números',
			'fields'     => array(
				array(
					'key'           => 'field_sobre_numeros_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'numeros_eyebrow',
					'type'          => 'text',
					'default_value' => 'PRÓ-ODONTO em números',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_numeros_titulo',
					'label'         => 'Título',
					'name'          => 'numeros_titulo',
					'type'          => 'text',
					'default_value' => 'Resultados que contam nossa história',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'          => 'field_sobre_numeros_itens',
					'label'        => 'Estatísticas',
					'name'         => 'numeros_itens',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Adicionar estatística',
					'sub_fields'   => array(
						array(
							'key'         => 'field_sobre_numeros_valor',
							'label'       => 'Valor',
							'name'        => 'valor',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '30' ),
							'placeholder' => '+15 mil',
						),
						array(
							'key'         => 'field_sobre_numeros_legenda',
							'label'       => 'Legenda',
							'name'        => 'legenda',
							'type'        => 'text',
							'wrapper'     => array( 'width' => '70' ),
							'placeholder' => 'sorrisos transformados',
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 3,
			'description' => 'Estatísticas da página Sobre.',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — Corpo Clínico / Equipe. Sinal de E-E-A-T essencial numa
	 * página institucional de saúde (YMYL): mostra QUEM atende, com
	 * especialidade e CRO — não só o nome da clínica. Vem com
	 * profissionais de EXEMPLO (sem nome/CRO reais, ver aviso no campo
	 * "message") — troque pelos dados reais antes de publicar.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_equipe',
			'title'      => 'Sobre — Corpo Clínico / Equipe',
			'fields'     => array(
				array(
					'key'     => 'field_sobre_equipe_aviso',
					'label'   => '',
					'name'    => 'equipe_aviso',
					'type'    => 'message',
					'message' => '<strong>Antes de publicar:</strong> substitua os profissionais de exemplo abaixo pelos dados reais da sua equipe (nome, especialidade, número de CRO e foto). A credencial (CRO) exibida precisa corresponder ao profissional real.',
				),
				array(
					'key'           => 'field_sobre_equipe_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'equipe_eyebrow',
					'type'          => 'text',
					'default_value' => 'Corpo clínico',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_sobre_equipe_titulo',
					'label'         => 'Título',
					'name'          => 'equipe_titulo',
					'type'          => 'text',
					'default_value' => 'Profissionais que cuidam do seu sorriso',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_sobre_equipe_texto',
					'label'         => 'Texto',
					'name'          => 'equipe_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Nossa equipe reúne cirurgiões-dentistas especialistas em diferentes áreas, todos registrados no Conselho Regional de Odontologia (CRO).',
					'wrapper'       => array( 'width' => '34' ),
				),
				array(
					'key'          => 'field_sobre_equipe_itens',
					'label'        => 'Profissionais',
					'name'         => 'equipe_itens',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar profissional',
					'sub_fields'   => array(
						array(
							'key'           => 'field_sobre_equipe_foto',
							'label'         => 'Foto',
							'name'          => 'foto',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
							'wrapper'       => array( 'width' => '20' ),
						),
						array(
							'key'     => 'field_sobre_equipe_nome',
							'label'   => 'Nome',
							'name'    => 'nome',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'         => 'field_sobre_equipe_cargo',
							'label'       => 'Cargo / especialidade',
							'name'        => 'cargo',
							'type'        => 'text',
							'placeholder' => 'Cirurgiã(o)-Dentista — Implantodontia',
							'wrapper'     => array( 'width' => '30' ),
						),
						array(
							'key'         => 'field_sobre_equipe_cro',
							'label'       => 'CRO',
							'name'        => 'cro',
							'type'        => 'text',
							'placeholder' => 'CRO-SE 12345',
							'wrapper'     => array( 'width' => '20' ),
						),
						array(
							'key'   => 'field_sobre_equipe_bio',
							'label' => 'Mini-bio',
							'name'  => 'bio',
							'type'  => 'textarea',
							'rows'  => 2,
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 4,
			'description' => 'Profissionais exibidos na seção "Corpo clínico" da página Sobre — também alimenta o JSON-LD (schema.org) da página, ver inc/page-sobre-schema.php.',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — Compromisso com Biossegurança.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_seguranca',
			'title'      => 'Sobre — Biossegurança',
			'fields'     => array(
				array(
					'key'           => 'field_sobre_seguranca_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'seguranca_eyebrow',
					'type'          => 'text',
					'default_value' => 'Segurança em primeiro lugar',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_sobre_seguranca_titulo',
					'label'         => 'Título',
					'name'          => 'seguranca_titulo',
					'type'          => 'text',
					'default_value' => 'Protocolos que protegem você',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_sobre_seguranca_texto',
					'label'         => 'Texto',
					'name'          => 'seguranca_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Seguimos boas práticas de biossegurança e investimos em equipamentos atualizados em todas as unidades.',
					'wrapper'       => array( 'width' => '34' ),
				),
				array(
					'key'          => 'field_sobre_seguranca_itens',
					'label'        => 'Itens',
					'name'         => 'seguranca_itens',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar item',
					'sub_fields'   => array(
						array(
							'key'     => 'field_sobre_seguranca_titulo_item',
							'label'   => 'Título',
							'name'    => 'titulo',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_sobre_seguranca_texto_item',
							'label'   => 'Texto',
							'name'    => 'texto',
							'type'    => 'textarea',
							'rows'    => 2,
							'wrapper' => array( 'width' => '45' ),
						),
						array(
							'key'          => 'field_sobre_seguranca_icone_item',
							'label'        => 'Ícone (SVG)',
							'name'         => 'icone_svg',
							'type'         => 'textarea',
							'instructions' => 'Conteúdo interno do &lt;svg&gt;, viewBox 0 0 24 24.',
							'rows'         => 2,
							'wrapper'      => array( 'width' => '25' ),
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 5,
			'description' => 'Protocolos de biossegurança exibidos na página Sobre.',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — Unidades (cabeçalho). A lista de unidades em si continua
	 * em inc/units-map.php — mesmo padrão dos grupos homônimos da
	 * Home/Vendas (group_home_units / group_vendas_units).
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_units',
			'title'      => 'Sobre — Unidades (cabeçalho)',
			'fields'     => array(
				array(
					'key'     => 'field_sobre_units_aviso',
					'label'   => '',
					'name'    => 'units_aviso',
					'type'    => 'message',
					'message' => 'A lista de unidades (endereços, links do Maps/WhatsApp) é editada em código, em <code>inc/units-map.php</code>. Aqui você edita só os textos de cabeçalho abaixo.',
				),
				array(
					'key'           => 'field_sobre_units_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'units_eyebrow',
					'type'          => 'text',
					'default_value' => 'Onde estamos',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_sobre_units_titulo',
					'label'         => 'Título',
					'name'          => 'units_titulo',
					'type'          => 'text',
					'default_value' => 'Três unidades para ficar perto de você',
					'wrapper'       => array( 'width' => '33' ),
				),
				array(
					'key'           => 'field_sobre_units_texto',
					'label'         => 'Texto',
					'name'          => 'units_texto',
					'type'          => 'text',
					'default_value' => 'Aracaju, Lagarto e Simão Dias — escolha a unidade mais próxima e venha conhecer a nossa equipe.',
					'wrapper'       => array( 'width' => '34' ),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 6,
			'description' => 'Cabeçalho da seção "Unidades" da página Sobre.',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — FAQ institucional. Alimenta o JSON-LD "FAQPage" — ver
	 * inc/page-sobre-schema.php. Formato de pergunta/resposta ajuda
	 * tanto buscadores tradicionais (rich snippet) quanto IAs
	 * generativas (GEO) a citar respostas diretas sobre a clínica.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_faq',
			'title'      => 'Sobre — Perguntas Frequentes',
			'fields'     => array(
				array(
					'key'           => 'field_sobre_faq_eyebrow',
					'label'         => 'Texto acima do título',
					'name'          => 'faq_eyebrow',
					'type'          => 'text',
					'default_value' => 'Perguntas frequentes',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_sobre_faq_titulo',
					'label'         => 'Título',
					'name'          => 'faq_titulo',
					'type'          => 'text',
					'default_value' => 'Ainda tem dúvidas sobre a PRÓ-ODONTO?',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'          => 'field_sobre_faq_itens',
					'label'        => 'Perguntas',
					'name'         => 'faq_itens',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Adicionar pergunta',
					'sub_fields'   => array(
						array(
							'key'   => 'field_sobre_faq_pergunta',
							'label' => 'Pergunta',
							'name'  => 'pergunta',
							'type'  => 'text',
						),
						array(
							'key'  => 'field_sobre_faq_resposta',
							'label' => 'Resposta',
							'name'  => 'resposta',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 7,
			'description' => 'Perguntas frequentes da página Sobre (gera JSON-LD FAQPage automaticamente).',
		)
	);

	/* ---------------------------------------------------------------
	 * Sobre — CTA final.
	 * ------------------------------------------------------------- */
	acf_add_local_field_group(
		array(
			'key'        => 'group_sobre_cta',
			'title'      => 'Sobre — CTA final',
			'fields'     => array(
				array(
					'key'           => 'field_sobre_cta_titulo',
					'label'         => 'Título',
					'name'          => 'cta_titulo',
					'type'          => 'text',
					'default_value' => 'Vamos cuidar do seu sorriso também?',
				),
				array(
					'key'           => 'field_sobre_cta_texto',
					'label'         => 'Texto',
					'name'          => 'cta_texto',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Fale agora com a nossa equipe pelo WhatsApp e agende sua avaliação gratuita — é rápido, sem burocracia e sem compromisso.',
				),
				array(
					'key'           => 'field_sobre_cta_botao_label',
					'label'         => 'Texto do botão',
					'name'          => 'cta_botao_label',
					'type'          => 'text',
					'instructions'  => 'O link usa sempre o WhatsApp padrão do tema (Opções do Tema).',
					'default_value' => 'Chamar no WhatsApp',
				),
			),
			'location'   => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-sobre.php',
					),
				),
			),
			'menu_order' => 8,
			'description' => 'Título, texto e botão do CTA final da página Sobre.',
		)
	);
}
