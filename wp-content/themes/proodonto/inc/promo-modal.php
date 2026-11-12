<?php
/**
 * Modal promocional — uma imagem (versão desktop + versão mobile) que
 * leva a uma URL de destino ao ser clicada. Tudo editável em
 * Opções do Tema → "Modal promocional" (grupo ACF registrado abaixo).
 *
 * Comportamento:
 *  - Abre sozinho X segundos depois de a página carregar (campo "Atraso").
 *  - Aparece no máximo 1x por dia por visitante (cookie de 24h, gravado
 *    quando o modal abre). Trocar a imagem ou a URL gera um cookie novo,
 *    então uma campanha nova volta a aparecer para quem já viu a anterior.
 *  - Páginas: todo o site (com lista de exceções), só a home, ou só as
 *    páginas escolhidas.
 *  - As imagens só são baixadas quando o modal vai mesmo abrir (src em
 *    data-*), e o modal só abre depois que a imagem carregou — não pesa
 *    no carregamento da página nem abre vazio.
 *  - Para testar sem esperar o cookie vencer: acrescente ?promo_modal=1
 *    à URL de qualquer página onde o modal está habilitado.
 *  - Envia ao GTM (dataLayer) os eventos promo_modal_view e
 *    promo_modal_click.
 *
 * Módulo independente: registra os próprios campos, CSS e JS, e imprime
 * a marcação via wp_footer (funciona inclusive em templates que não usam
 * footer.php, como a LP do Dia Pró). O CSS não depende dos tokens de
 * style.css, porque algumas páginas (Dia Pró) tiram esse arquivo.
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * 1. Campos (Opções do Tema)
 * ---------------------------------------------------------------------- */

add_action( 'acf/init', 'proodonto_promo_modal_register_fields' );

function proodonto_promo_modal_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$when_active = array(
		array(
			array(
				'field'    => 'field_promo_modal_ativo',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'         => 'group_theme_options_promo_modal',
			'title'       => 'Opções do Tema — Modal promocional',
			'fields'      => array(
				array(
					'key'           => 'field_promo_modal_ativo',
					'label'         => 'Ativar modal promocional',
					'name'          => 'promo_modal_ativo',
					'type'          => 'true_false',
					'instructions'  => 'Abre uma imagem em destaque alguns segundos depois de a página carregar, no máximo 1x por dia para cada visitante. Para testar: acrescente ?promo_modal=1 ao endereço da página.',
					'ui'            => 1,
					'default_value' => 0,
				),
				array(
					'key'               => 'field_promo_modal_imagem_desktop',
					'label'             => 'Imagem — desktop',
					'name'              => 'promo_modal_imagem_desktop',
					'type'              => 'image',
					'instructions'      => 'Usada em telas a partir de 768px. Sugestão: 1080 × 1080 ou 1200 × 800 px, JPG/WebP com até ~200 KB.',
					'required'          => 1,
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'library'           => 'all',
					'mime_types'        => 'jpg,jpeg,png,webp,gif',
					'wrapper'           => array( 'width' => '50' ),
					'conditional_logic' => $when_active,
				),
				array(
					'key'               => 'field_promo_modal_imagem_mobile',
					'label'             => 'Imagem — mobile',
					'name'              => 'promo_modal_imagem_mobile',
					'type'              => 'image',
					'instructions'      => 'Usada em telas até 767px. Sugestão: 1080 × 1350 px (4:5). Se ficar vazia, o celular usa a imagem desktop.',
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'library'           => 'all',
					'mime_types'        => 'jpg,jpeg,png,webp,gif',
					'wrapper'           => array( 'width' => '50' ),
					'conditional_logic' => $when_active,
				),
				array(
					'key'               => 'field_promo_modal_url',
					'label'             => 'URL de destino',
					'name'              => 'promo_modal_url',
					'type'              => 'url',
					'instructions'      => 'Para onde o clique na imagem leva. Se ficar vazia, a imagem aparece sem link.',
					'placeholder'       => 'https://',
					'wrapper'           => array( 'width' => '50' ),
					'conditional_logic' => $when_active,
				),
				array(
					'key'               => 'field_promo_modal_nova_aba',
					'label'             => 'Abrir em nova aba',
					'name'              => 'promo_modal_nova_aba',
					'type'              => 'true_false',
					'ui'                => 1,
					'default_value'     => 0,
					'wrapper'           => array( 'width' => '25' ),
					'conditional_logic' => $when_active,
				),
				array(
					'key'               => 'field_promo_modal_atraso',
					'label'             => 'Atraso (segundos)',
					'name'              => 'promo_modal_atraso',
					'type'              => 'number',
					'instructions'      => 'Tempo depois do carregamento da página.',
					'default_value'     => 3,
					'min'               => 0,
					'max'               => 60,
					'step'              => 1,
					'wrapper'           => array( 'width' => '25' ),
					'conditional_logic' => $when_active,
				),
				array(
					'key'               => 'field_promo_modal_alt',
					'label'             => 'Texto alternativo',
					'name'              => 'promo_modal_alt',
					'type'              => 'text',
					'instructions'      => 'Descreve a imagem para leitores de tela (ex.: "Dia Pró: avaliação com condições especiais em 22/11"). Se vazio, usa o texto alternativo cadastrado na própria imagem.',
					'conditional_logic' => $when_active,
				),
				array(
					'key'               => 'field_promo_modal_paginas',
					'label'             => 'Onde exibir',
					'name'              => 'promo_modal_paginas',
					'type'              => 'radio',
					'choices'           => array(
						'todas'        => 'Todo o site',
						'home'         => 'Só a página inicial',
						'selecionadas' => 'Só nas páginas escolhidas',
					),
					'default_value'     => 'todas',
					'layout'            => 'horizontal',
					'conditional_logic' => $when_active,
				),
				array(
					'key'               => 'field_promo_modal_paginas_incluir',
					'label'             => 'Páginas onde exibir',
					'name'              => 'promo_modal_paginas_incluir',
					'type'              => 'post_object',
					'post_type'         => array( 'page', 'post', 'servico' ),
					'multiple'          => 1,
					'return_format'     => 'id',
					'ui'                => 1,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_promo_modal_ativo',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'field'    => 'field_promo_modal_paginas',
								'operator' => '==',
								'value'    => 'selecionadas',
							),
						),
					),
				),
				array(
					'key'               => 'field_promo_modal_paginas_excluir',
					'label'             => 'Não exibir nestas páginas',
					'name'              => 'promo_modal_paginas_excluir',
					'type'              => 'post_object',
					'instructions'      => 'Opcional. Ex.: landing pages de campanha, onde o modal competiria com a própria oferta.',
					'post_type'         => array( 'page', 'post', 'servico' ),
					'multiple'          => 1,
					'allow_null'        => 1,
					'return_format'     => 'id',
					'ui'                => 1,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_promo_modal_ativo',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'field'    => 'field_promo_modal_paginas',
								'operator' => '==',
								'value'    => 'todas',
							),
						),
					),
				),
			),
			'location'    => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'proodonto-options',
					),
				),
			),
			'menu_order'  => 3,
			'description' => 'Imagem promocional (desktop + mobile) com link, exibida em modal no máximo 1x por dia.',
		)
	);
}

/* -------------------------------------------------------------------------
 * 2. Dados + regra de exibição
 * ---------------------------------------------------------------------- */

/**
 * Dados do modal já resolvidos, ou null se ele não deve aparecer na
 * página atual. Calculado uma vez por requisição.
 *
 * @return array|null
 */
function proodonto_promo_modal_data() {
	static $data = false;

	if ( false !== $data ) {
		return $data;
	}

	$data = null;

	if ( is_admin() || ! function_exists( 'get_field' ) || ! get_field( 'promo_modal_ativo', 'option' ) ) {
		return $data;
	}

	$desktop_id = (int) get_field( 'promo_modal_imagem_desktop', 'option' );
	$mobile_id  = (int) get_field( 'promo_modal_imagem_mobile', 'option' );

	$desktop = $desktop_id ? wp_get_attachment_image_src( $desktop_id, 'full' ) : false;

	if ( ! $desktop ) {
		return $data;
	}

	$mobile = $mobile_id ? wp_get_attachment_image_src( $mobile_id, 'full' ) : false;

	if ( ! proodonto_promo_modal_matches_page() ) {
		return $data;
	}

	$url = trim( (string) get_field( 'promo_modal_url', 'option' ) );
	$alt = trim( (string) get_field( 'promo_modal_alt', 'option' ) );

	if ( '' === $alt ) {
		$alt = trim( (string) get_post_meta( $desktop_id, '_wp_attachment_image_alt', true ) );
	}

	$delay = get_field( 'promo_modal_atraso', 'option' );
	$delay = ( '' === $delay || null === $delay ) ? 3 : max( 0, min( 60, (int) $delay ) );

	$data = array(
		'desktop' => array(
			'src'    => $desktop[0],
			'width'  => (int) $desktop[1],
			'height' => (int) $desktop[2],
		),
		'mobile'  => $mobile ? array(
			'src'    => $mobile[0],
			'width'  => (int) $mobile[1],
			'height' => (int) $mobile[2],
		) : null,
		'url'     => $url,
		'new_tab' => (bool) get_field( 'promo_modal_nova_aba', 'option' ),
		'alt'     => $alt,
		'delay'   => $delay,
		// Muda quando a campanha muda → quem viu a anterior vê a nova.
		'id'      => substr( md5( $desktop_id . '|' . $mobile_id . '|' . $url ), 0, 8 ),
	);

	return $data;
}

/**
 * A página atual está no escopo escolhido em "Onde exibir"?
 */
function proodonto_promo_modal_matches_page() {
	$mode       = (string) get_field( 'promo_modal_paginas', 'option' );
	$current_id = is_singular() ? (int) get_queried_object_id() : 0;

	// Página de posts (Blog) também tem ID próprio.
	if ( ! $current_id && is_home() && ! is_front_page() ) {
		$current_id = (int) get_option( 'page_for_posts' );
	}

	if ( 'home' === $mode ) {
		return is_front_page();
	}

	if ( 'selecionadas' === $mode ) {
		$ids = array_map( 'intval', (array) get_field( 'promo_modal_paginas_incluir', 'option' ) );
		return $current_id && in_array( $current_id, $ids, true );
	}

	// "todas" (padrão), menos as exceções.
	$exclude = array_map( 'intval', array_filter( (array) get_field( 'promo_modal_paginas_excluir', 'option' ) ) );

	return ! ( $current_id && in_array( $current_id, $exclude, true ) );
}

/* -------------------------------------------------------------------------
 * 3. Assets — só nas páginas onde o modal pode aparecer
 * ---------------------------------------------------------------------- */

// Prioridade 110: depois do proodonto_dp_enqueue (100), que limpa o CSS
// do site na LP do Dia Pró.
add_action( 'wp_enqueue_scripts', 'proodonto_promo_modal_enqueue', 110 );

function proodonto_promo_modal_enqueue() {
	if ( ! proodonto_promo_modal_data() ) {
		return;
	}

	$css = 'assets/css/promo-modal.css';
	$js  = 'assets/js/promo-modal.js';

	wp_enqueue_style( 'proodonto-promo-modal', PROODONTO_URI . '/' . $css, array(), proodonto_asset_version( $css ) );

	wp_enqueue_script(
		'proodonto-promo-modal',
		PROODONTO_URI . '/' . $js,
		array(),
		proodonto_asset_version( $js ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}

/* -------------------------------------------------------------------------
 * 4. Marcação
 * ---------------------------------------------------------------------- */

add_action( 'wp_footer', 'proodonto_promo_modal_render', 5 );

function proodonto_promo_modal_render() {
	$data = proodonto_promo_modal_data();

	if ( ! $data ) {
		return;
	}

	$desktop = $data['desktop'];
	$mobile  = $data['mobile'];
	$label   = $data['alt'] ? $data['alt'] : __( 'Promoção', 'proodonto' );
	?>
	<dialog
		class="promo-modal"
		id="promo-modal"
		aria-label="<?php echo esc_attr( $label ); ?>"
		data-promo-id="<?php echo esc_attr( $data['id'] ); ?>"
		data-promo-delay="<?php echo esc_attr( $data['delay'] ); ?>"
	>
		<button type="button" class="promo-modal__close" data-promo-close aria-label="<?php esc_attr_e( 'Fechar', 'proodonto' ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
		</button>

		<?php if ( $data['url'] ) : ?>
			<a
				class="promo-modal__link"
				href="<?php echo esc_url( $data['url'] ); ?>"
				data-promo-link
				<?php echo $data['new_tab'] ? 'target="_blank" rel="noopener"' : ''; ?>
			>
		<?php else : ?>
			<div class="promo-modal__link">
		<?php endif; ?>

			<picture>
				<?php if ( $mobile ) : ?>
					<source
						media="(max-width: 767px)"
						data-srcset="<?php echo esc_url( $mobile['src'] ); ?>"
						width="<?php echo esc_attr( $mobile['width'] ); ?>"
						height="<?php echo esc_attr( $mobile['height'] ); ?>"
					/>
				<?php endif; ?>
				<img
					class="promo-modal__img"
					data-src="<?php echo esc_url( $desktop['src'] ); ?>"
					width="<?php echo esc_attr( $desktop['width'] ); ?>"
					height="<?php echo esc_attr( $desktop['height'] ); ?>"
					alt="<?php echo esc_attr( $data['alt'] ); ?>"
					decoding="async"
				/>
			</picture>

		<?php echo $data['url'] ? '</a>' : '</div>'; ?>
	</dialog>
	<?php
}
