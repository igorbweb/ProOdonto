<?php
/**
 * Dia Pró — landing page do evento (template page-dia-pro.php).
 *
 * Tudo o que a página precisa mora aqui, para não mexer no resto do tema:
 *   1. Conteúdo padrão (o texto aprovado no wireframe de 04/11/2026).
 *   2. Helpers de leitura dos campos (com o padrão quando o campo nunca foi salvo).
 *   3. Grupo de campos ACF "Dia Pró — Landing page", só nesse template.
 *   4. Assets: CSS/JS próprios; o CSS global do site (Tailwind, style.css,
 *      main.css, critical.css) NÃO carrega nessa página — ela não usa o
 *      cabeçalho nem o rodapé do site. O main.js continua (repasse de
 *      utm_*, fbclid e gclid para o link do UpView).
 *   5. noindex + fora do sitemap (oferta com data para acabar), e os botões
 *      flutuantes de WhatsApp (Float Button, Joinchat) desligados — a página
 *      tem barra fixa própria no celular.
 *
 * Regras do projeto respeitadas: CTA só para o UpView (inc/cta.php); sem
 * preço nem condição específica no texto; sem placeholder "[...]" no
 * conteúdo padrão — dado que a clínica ainda não confirmou fica vazio e o
 * bloco não aparece.
 */

defined( 'ABSPATH' ) || exit;

const PROODONTO_DP_TEMPLATE = 'page-dia-pro.php';

/* -------------------------------------------------------------------------
 * 1. Conteúdo padrão
 * ---------------------------------------------------------------------- */

function proodonto_dp_defaults() {
	static $d = null;

	if ( null !== $d ) {
		return $d;
	}

	$img = 'assets/images/dia-pro/';

	$d = array(
		// Geral.
		'dp_cta_url'             => '',
		'dp_cta_label'           => 'Reservar meu horário no WhatsApp',
		'dp_cta_label_curto'     => 'Reservar horário',
		'dp_inicio'              => '2026-11-19 08:00:00',
		'dp_fim'                 => '2026-11-20 18:00:00',
		'dp_vagas'               => '',
		'dp_reservados'          => '',
		'dp_total'               => '',
		'dp_reservas_atualizado' => '',
		'dp_noindex'             => 1,

		// Faixa + topo.
		'dp_faixa'               => 'Plantão de implantes e próteses · 19 e 20 de novembro · PróOdonto Aracaju',
		'dp_eyebrow'             => 'Condições jamais vistas',
		'dp_titulo'              => 'A maior oportunidade *do ano.*',
		'dp_subtitulo'           => 'Dois dias de atendimento especial na PróOdonto Aracaju para quem quer voltar a ter dentes fixos. Faça a avaliação de <strong>implante ou prótese</strong> no evento e conheça condições que só valem nesses dias. As vagas por dia são limitadas.',
		'dp_microcopy'           => 'Resposta em minutos · sem compromisso · parcelamento no cartão ou no boleto',
		'dp_foto_topo'           => array( 'file' => $img . 'hero-dentista-coroa.webp', 'sm' => $img . 'hero-dentista-coroa-sm.webp', 'w' => 880, 'h' => 1100, 'alt' => 'Dentista da PróOdonto segurando uma coroa sobre implante, pronta para ser instalada no paciente' ),
		'dp_provas'              => array(
			array( 'destaque' => '+7 anos', 'texto' => 'de PróOdonto em Sergipe' ),
			array( 'destaque' => '3 unidades', 'texto' => 'Aracaju, Lagarto e Simão Dias' ),
		),

		// Datas.
		'dp_datas_titulo'        => 'Só dois dias. *Depois, as condições acabam.*',
		'dp_dias'                => array(
			array( 'dia_semana' => 'Quinta-feira', 'data' => '19 de novembro', 'data_curta' => 'Qui, 19/11', 'horario' => '' ),
			array( 'dia_semana' => 'Sexta-feira · feriado', 'data' => '20 de novembro', 'data_curta' => 'Sex, 20/11 (feriado)', 'horario' => '' ),
		),

		// Casos.
		'dp_casos_eyebrow'       => 'Implantes e próteses',
		'dp_casos_titulo'        => 'Qual destes é o seu caso?',
		'dp_casos_texto'         => 'Escolha o que mais parece com a sua situação e reserve a sua avaliação.',
		'dp_casos_condicao'      => 'Condição especial no atendimento de 19 ou 20/11',
		'dp_casos'               => array(
			array(
				'tag'      => 'Implante dentário',
				'titulo'   => 'Perdi um ou alguns dentes',
				'texto'    => 'O implante substitui a raiz e recebe um dente fixo, sem desgastar os dentes vizinhos.',
				'imagem'   => array( 'file' => $img . 'caso-implante.webp', 'w' => 800, 'h' => 500, 'alt' => 'Cirurgia de implante na PróOdonto, com dentista e auxiliar paramentados' ),
				'link'     => '',
				'botao'    => 'Quero avaliar meu caso',
				'destaque' => 0,
			),
			array(
				'tag'      => 'Prótese fixa sobre implantes (protocolo)',
				'titulo'   => 'Perdi todos os dentes de cima ou de baixo',
				'texto'    => 'Uma arcada inteira fixa, parafusada sobre implantes. Não sai para dormir nem para comer.',
				'imagem'   => array( 'file' => $img . 'caso-protocolo.webp', 'w' => 800, 'h' => 500, 'alt' => 'Prótese protocolo superior, arcada completa, segurada por mão com luva' ),
				'link'     => '',
				'botao'    => 'Quero avaliar meu caso',
				'destaque' => 1,
			),
			array(
				'tag'      => 'Da dentadura para a fixa',
				'titulo'   => 'Uso dentadura e ela não fica firme',
				'texto'    => 'Troque a dentadura móvel por uma prótese fixa sobre implantes e volte a comer de tudo.',
				'imagem'   => array( 'file' => $img . 'caso-dentadura.webp', 'w' => 800, 'h' => 500, 'alt' => 'Sorriso de paciente com prótese fixa superior e inferior' ),
				'link'     => '',
				'botao'    => 'Quero avaliar meu caso',
				'destaque' => 0,
			),
		),
		'dp_ajuda_titulo'        => 'Não sabe qual é o seu caso?',
		'dp_ajuda_texto'         => 'Reserve a avaliação e o dentista indica o tratamento. As condições especiais valem para o que for indicado no dia.',
		'dp_ajuda_botao'         => 'Quero uma avaliação',

		// Resultado real.
		'dp_res_eyebrow'         => 'Resultado real',
		'dp_res_titulo'          => 'Sorriso fixo, feito aqui em Aracaju',
		'dp_res_foto_1'          => array( 'file' => $img . 'resultado-sorriso.webp', 'sm' => $img . 'resultado-sorriso-sm.webp', 'w' => 1000, 'h' => 750, 'alt' => 'Paciente sorrindo com a prótese fixa sobre implantes já instalada' ),
		'dp_res_foto_2'          => array( 'file' => $img . 'resultado-protese.webp', 'w' => 800, 'h' => 600, 'alt' => 'Detalhe da prótese fixa superior e inferior instalada' ),
		'dp_res_video'           => $img . 'bastidores-escolha-cor.mp4',
		'dp_res_video_poster'    => array( 'file' => $img . 'video-poster.webp', 'w' => 720, 'h' => 1280, 'alt' => '' ),
		'dp_res_video_legenda'   => 'Bastidores · escolha da cor',
		'dp_res_aviso'           => 'Paciente da PróOdonto com prótese fixa sobre implantes, imagem publicada com autorização. Cada caso tem planejamento próprio e o resultado varia de pessoa para pessoa.',

		// Passos.
		'dp_passos_titulo'       => 'Como garantir a sua condição em 3 passos',
		'dp_passos'              => array(
			array( 'titulo' => 'Reserve pelo WhatsApp', 'texto' => 'Escolha o dia e o horário. Leva menos de 2 minutos.' ),
			array( 'titulo' => 'Faça a avaliação no dia 19 ou 20', 'texto' => 'Exame com o implantodontista e plano de tratamento na hora.' ),
			array( 'titulo' => 'Conheça as condições do Dia Pró', 'texto' => 'Depois da avaliação, a equipe apresenta as condições especiais do evento. Elas valem só para quem fechar nos dias 19 e 20.' ),
		),
		'dp_passos_foto'         => array( 'file' => $img . 'bastidores-consultorio.webp', 'sm' => $img . 'bastidores-consultorio-sm.webp', 'w' => 800, 'h' => 1000, 'alt' => 'Consultório da PróOdonto Aracaju com escala de cores de dentes em primeiro plano' ),

		// Prova social.
		'dp_prova_eyebrow'       => 'Quem já passou por aqui',
		'dp_prova_titulo'        => 'O que dizem os pacientes',
		'dp_numeros'             => array(
			array( 'valor' => '+7 anos', 'rotulo' => 'cuidando de sorrisos em Sergipe' ),
			array( 'valor' => '3', 'rotulo' => 'unidades: Aracaju, Lagarto e Simão Dias' ),
		),
		'dp_depoimentos'         => array(),

		// Escassez / CTA do meio.
		'dp_escassez_titulo'     => 'Os horários estão sendo preenchidos',
		'dp_escassez_texto'      => 'As vagas de avaliação são limitadas por dia. Garanta a sua antes que o plantão encha.',
		'dp_escassez_botao'      => 'Garantir o meu horário',

		// Pagamento.
		'dp_pag_eyebrow'         => 'Cabe no seu bolso',
		'dp_pag_titulo'          => 'Formas de pagamento do evento',
		'dp_pag_texto'           => 'O valor do tratamento não precisa adiar os seus dentes fixos. Escolha a forma de parcelar que cabe no seu mês: os detalhes você conhece no atendimento.',
		'dp_pagamentos'          => array(
			array( 'icone' => 'cartao', 'titulo' => 'Parcelamento no cartão de crédito', 'texto' => 'Divida o tratamento no cartão e comece já.' ),
			array( 'icone' => 'boleto', 'titulo' => 'Parcelamento no boleto bancário', 'texto' => 'Sem usar o limite do cartão de crédito.' ),
		),

		// Local.
		'dp_local_eyebrow'       => 'Onde acontece',
		'dp_local_titulo'        => 'PróOdonto Aracaju',
		'dp_endereco_1'          => 'Av. Pres. Tancredo Neves, 1028 – Jardins',
		'dp_endereco_2'          => 'Aracaju – SE · CEP 49025-620',
		'dp_estacionamento'      => '',
		'dp_maps_url'            => 'https://maps.app.goo.gl/wvRKAiDYqjFTaJbd9',
		'dp_maps_embed'          => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3917.1554070487023!2d-37.0617939!3d-10.951631!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x71ab30027172715%3A0x12204ec58f950147!2sPr%C3%B3Odonto%20Aracaju!5e0!3m2!1spt-BR!2sbr!4v1793830924503!5m2!1spt-BR!2sbr',

		// FAQ.
		'dp_faq_titulo'          => 'Perguntas frequentes',
		'dp_faq'                 => array(
			array( 'pergunta' => 'Preciso agendar ou posso ir direto?', 'resposta' => 'Quem reserva pelo WhatsApp tem horário garantido no dia escolhido. Sem reserva, o atendimento depende dos horários livres no dia.' ),
			array( 'pergunta' => 'As condições valem depois do dia 20?', 'resposta' => 'Não. As condições especiais valem para quem fechar o tratamento durante o Dia Pró, nos dias 19 e 20 de novembro.' ),
			array( 'pergunta' => 'Quais formas de pagamento são aceitas?', 'resposta' => 'Você pode parcelar no cartão de crédito ou no boleto bancário. As condições de cada forma são apresentadas no atendimento, no dia do evento.' ),
			array( 'pergunta' => 'A PróOdonto aceita convênio?', 'resposta' => 'Não. O atendimento é particular, com parcelamento no cartão de crédito ou no boleto bancário.' ),
			array( 'pergunta' => 'Onde acontece o Dia Pró?', 'resposta' => 'Na PróOdonto Aracaju, Av. Pres. Tancredo Neves, 1028, no bairro Jardins.' ),
		),

		// Fechamento.
		'dp_final_titulo'        => 'Seus dentes fixos começam *em 19 de novembro.*',
		'dp_final_texto'         => 'Reserve o seu horário antes que o plantão encha.',
		'dp_final_micro'         => 'Resposta em minutos · sem compromisso',

		// Rodapé.
		'dp_rodape_empresa'      => 'PróOdonto Aracaju',
		'dp_cnpj'                => '',
		'dp_rt'                  => '',
		'dp_aviso'               => 'Condições especiais apresentadas no atendimento e válidas para tratamentos fechados em 19 e 20/11/2026, mediante avaliação clínica.',
	);

	return $d;
}

/* -------------------------------------------------------------------------
 * 2. Helpers
 * ---------------------------------------------------------------------- */

/**
 * Campos de mídia: vazio = arquivo padrão do tema (as fotos padrão são
 * arquivos do tema, não da Biblioteca de Mídia, então não dá para gravá-las
 * no campo).
 */
const PROODONTO_DP_MEDIA_FIELDS = array( 'dp_foto_topo', 'dp_res_foto_1', 'dp_res_foto_2', 'dp_res_video', 'dp_res_video_poster', 'dp_passos_foto' );

/**
 * Valor de um campo: o salvo no wp-admin (mesmo vazio — apagar o texto
 * esconde o bloco) ou, se o campo nunca foi salvo nesta página, o padrão.
 * Mídia vazia sempre cai no arquivo padrão do tema.
 */
function proodonto_dp( $name ) {
	$defaults = proodonto_dp_defaults();
	$default  = isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';
	$post_id  = get_queried_object_id();

	if ( ! function_exists( 'get_field' ) || ! $post_id || ! metadata_exists( 'post', $post_id, $name ) ) {
		return $default;
	}

	$value = get_field( $name, $post_id );

	if ( null === $value ) {
		return $default;
	}

	if ( in_array( $name, PROODONTO_DP_MEDIA_FIELDS, true ) && empty( $value ) ) {
		return $default;
	}

	return $value;
}

/**
 * Linhas de um repeater (salvo ou padrão), sempre array. Subcampo de imagem
 * vazio herda a foto padrão da linha de mesma posição.
 */
function proodonto_dp_rows( $name ) {
	$rows     = proodonto_dp( $name );
	$rows     = is_array( $rows ) ? array_values( $rows ) : array();
	$defaults = proodonto_dp_defaults();
	$base     = isset( $defaults[ $name ] ) && is_array( $defaults[ $name ] ) ? $defaults[ $name ] : array();

	foreach ( $rows as $i => $row ) {
		if ( is_array( $row ) && array_key_exists( 'imagem', $row ) && empty( $row['imagem'] ) && ! empty( $base[ $i ]['imagem'] ) ) {
			$rows[ $i ]['imagem'] = $base[ $i ]['imagem'];
		}
	}

	return $rows;
}

/**
 * Grava o conteúdo padrão nos campos da página, uma vez (como o
 * inc/content-seed.php faz na Home/Vendas/Sobre). Sem isso, o primeiro
 * "Atualizar" no wp-admin salvaria os repeaters vazios — os campos de
 * texto já aparecem preenchidos pelo default_value do ACF, os repeaters
 * não. Só preenche o que está vazio; mídia fica de fora (cai no arquivo do
 * tema quando vazia).
 */
function proodonto_dp_seed( $post_id ) {
	if ( ! function_exists( 'update_field' ) || get_post_meta( $post_id, '_dp_seeded', true ) ) {
		return;
	}
	if ( PROODONTO_DP_TEMPLATE !== get_page_template_slug( $post_id ) ) {
		return;
	}

	foreach ( proodonto_dp_defaults() as $name => $value ) {
		if ( in_array( $name, PROODONTO_DP_MEDIA_FIELDS, true ) || '' === $value ) {
			continue;
		}

		$current = get_post_meta( $post_id, $name, true );
		if ( '' !== $current && '0' !== $current && null !== $current && metadata_exists( 'post', $post_id, $name ) ) {
			continue;
		}

		if ( is_array( $value ) ) {
			if ( ! $value ) {
				continue;
			}
			$value = array_map(
				static function ( $row ) {
					unset( $row['imagem'] );
					return $row;
				},
				$value
			);
		}

		update_field( 'field_' . $name, $value, $post_id );
	}

	update_post_meta( $post_id, '_dp_seeded', 1 );
}

// Ao abrir a página no editor e depois do primeiro salvamento com o template.
add_action(
	'load-post.php',
	static function () {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $post_id && 'page' === get_post_type( $post_id ) ) {
			proodonto_dp_seed( $post_id );
		}
	}
);

add_action(
	'acf/save_post',
	static function ( $post_id ) {
		if ( is_numeric( $post_id ) && 'page' === get_post_type( (int) $post_id ) ) {
			proodonto_dp_seed( (int) $post_id );
		}
	},
	20
);

/**
 * Texto curto com *destaque* em dourado. Escapa tudo e só depois troca
 * *trecho* pelo span.
 */
function proodonto_dp_title( $text ) {
	$html = esc_html( (string) $text );
	return preg_replace( '/\*(.+?)\*/u', '<span class="dp-gold">$1</span>', $html );
}

/** Texto com HTML básico (negrito, itálico, quebra, link). */
function proodonto_dp_rich( $text ) {
	return wp_kses(
		(string) $text,
		array(
			'strong' => array(),
			'b'      => array(),
			'em'     => array(),
			'br'     => array(),
			'a'      => array( 'href' => array() ),
		)
	);
}

/**
 * Imagem normalizada: aceita o array do ACF (return_format = array) ou o
 * padrão do tema ('file', 'sm', 'w', 'h', 'alt'). Retorna src, srcset,
 * width, height e alt — ou null.
 */
function proodonto_dp_image( $value, $sizes_attr = '100vw' ) {
	if ( is_array( $value ) && ! empty( $value['ID'] ) ) {
		$id     = (int) $value['ID'];
		$full   = wp_get_attachment_image_src( $id, 'full' );
		$srcset = wp_get_attachment_image_srcset( $id, 'full' );

		if ( ! $full ) {
			return null;
		}

		return array(
			'src'    => $full[0],
			'srcset' => $srcset ? $srcset : '',
			'sizes'  => $sizes_attr,
			'w'      => (int) $full[1],
			'h'      => (int) $full[2],
			'alt'    => isset( $value['alt'] ) ? (string) $value['alt'] : '',
		);
	}

	if ( is_array( $value ) && ! empty( $value['file'] ) ) {
		$src    = PROODONTO_URI . '/' . $value['file'];
		$srcset = '';

		if ( ! empty( $value['sm'] ) && file_exists( PROODONTO_DIR . '/' . $value['sm'] ) ) {
			$sm_size = wp_getimagesize( PROODONTO_DIR . '/' . $value['sm'] );
			$sm_w    = $sm_size ? (int) $sm_size[0] : 480;
			$srcset  = PROODONTO_URI . '/' . $value['sm'] . ' ' . $sm_w . 'w, ' . $src . ' ' . (int) $value['w'] . 'w';
		}

		return array(
			'src'    => $src,
			'srcset' => $srcset,
			'sizes'  => $sizes_attr,
			'w'      => (int) $value['w'],
			'h'      => (int) $value['h'],
			'alt'    => (string) $value['alt'],
		);
	}

	return null;
}

/** Imprime <img> de uma imagem normalizada. */
function proodonto_dp_img_tag( $img, $class = '', $attrs = array() ) {
	if ( ! $img ) {
		return;
	}

	$attrs = array_merge(
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		$attrs
	);

	printf(
		'<img src="%1$s"%2$s width="%3$d" height="%4$d" alt="%5$s" class="%6$s"%7$s>',
		esc_url( $img['src'] ),
		$img['srcset'] ? ' srcset="' . esc_attr( $img['srcset'] ) . '" sizes="' . esc_attr( $img['sizes'] ) . '"' : '',
		(int) $img['w'],
		(int) $img['h'],
		esc_attr( $img['alt'] ),
		esc_attr( $class ),
		implode(
			'',
			array_map(
				static function ( $k, $v ) {
					return ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
				},
				array_keys( $attrs ),
				$attrs
			)
		)
	);
}

/** URL de vídeo: campo de arquivo do ACF (array/URL) ou caminho do tema. */
function proodonto_dp_video_url( $value ) {
	if ( is_array( $value ) && ! empty( $value['url'] ) ) {
		return $value['url'];
	}
	if ( is_string( $value ) && '' !== $value ) {
		return preg_match( '~^https?://~', $value ) ? $value : PROODONTO_URI . '/' . ltrim( $value, '/' );
	}
	return '';
}

/** Link dos CTAs: o do campo (se válido) ou o UpView de Aracaju. */
function proodonto_dp_cta_url( $override = '' ) {
	$url = '' !== trim( (string) $override ) ? $override : (string) proodonto_dp( 'dp_cta_url' );

	if ( function_exists( 'proodonto_resolve_cta_url' ) ) {
		return proodonto_resolve_cta_url( $url, 'aracaju' );
	}

	return $url ? $url : '#';
}

/** Momento do evento (DateTimeImmutable no fuso do site) ou null. */
function proodonto_dp_time( $name ) {
	$raw = (string) proodonto_dp( $name );
	if ( '' === $raw ) {
		return null;
	}
	try {
		return new DateTimeImmutable( $raw, wp_timezone() );
	} catch ( Exception $e ) {
		return null;
	}
}

/**
 * Estado da contagem: antes, durante ou depois do evento, com o alvo e os
 * números iniciais (renderizados no servidor — sem pulo de layout e sem
 * depender de JS para mostrar algo certo).
 */
function proodonto_dp_countdown_state() {
	$start = proodonto_dp_time( 'dp_inicio' );
	$end   = proodonto_dp_time( 'dp_fim' );

	if ( ! $start || ! $end ) {
		return null;
	}

	$now = new DateTimeImmutable( 'now', wp_timezone() );

	if ( $now < $start ) {
		$phase  = 'antes';
		$target = $start;
		$label  = 'Começa em';
	} elseif ( $now < $end ) {
		$phase  = 'durante';
		$target = $end;
		$label  = 'É hoje · termina em';
	} else {
		$phase  = 'depois';
		$target = $end;
		$label  = 'O Dia Pró deste ano terminou';
	}

	$diff = max( 0, $target->getTimestamp() - $now->getTimestamp() );

	return array(
		'phase'  => $phase,
		'label'  => $label,
		'start'  => $start->format( DATE_ATOM ),
		'end'    => $end->format( DATE_ATOM ),
		'd'      => (int) floor( $diff / 86400 ),
		'h'      => (int) floor( ( $diff % 86400 ) / 3600 ),
		'm'      => (int) floor( ( $diff % 3600 ) / 60 ),
		's'      => (int) ( $diff % 60 ),
	);
}

/** É a landing page do Dia Pró? */
function proodonto_is_dia_pro_page() {
	return is_page() && is_page_template( PROODONTO_DP_TEMPLATE );
}

/** Ícones (traço) usados na página. */
function proodonto_dp_icon( $name, $size = 24 ) {
	$paths = array(
		'whatsapp' => '<path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.5z"/>',
		'cartao'   => '<rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M2.5 9.5h19M6 15h4"/>',
		'boleto'   => '<path d="M3 5v14M6 5v14M9 5v14M13 5v14M15 5v14M18 5v14M21 5v14"/>',
		'pix'      => '<path d="M12 3l9 9-9 9-9-9z"/><path d="M8 12h8"/>',
		'pin'      => '<path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.6"/>',
		'mais'     => '<path d="M12 5v14M5 12h14"/>',
		'estrela'  => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"/>',
	);

	if ( empty( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="dp-icon dp-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

/* -------------------------------------------------------------------------
 * 3. Campos ACF
 * ---------------------------------------------------------------------- */

add_action( 'acf/init', 'proodonto_dp_register_fields' );

function proodonto_dp_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$d = proodonto_dp_defaults();

	$f = static function ( $name, $label, $type, $extra = array() ) use ( $d ) {
		$field = array(
			'key'   => 'field_' . $name,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		);
		if ( isset( $d[ $name ] ) && ! is_array( $d[ $name ] ) && in_array( $type, array( 'text', 'textarea', 'url', 'number', 'true_false', 'date_time_picker' ), true ) ) {
			$field['default_value'] = $d[ $name ];
		}
		return array_merge( $field, $extra );
	};

	$tab = static function ( $key, $label ) {
		return array(
			'key'       => 'field_dp_tab_' . $key,
			'label'     => $label,
			'name'      => '',
			'type'      => 'tab',
			'placement' => 'top',
		);
	};

	$sub = static function ( $parent, $name, $label, $type, $extra = array() ) {
		return array_merge(
			array(
				'key'   => 'field_' . $parent . '__' . $name, // "__": não colide com campos de topo (dp_casos_titulo × dp_casos → titulo).
				'name'  => $name,
				'label' => $label,
				'type'  => $type,
			),
			$extra
		);
	};

	$image = array(
		'return_format' => 'array',
		'preview_size'  => 'medium',
		'library'       => 'all',
		'mime_types'    => 'jpg,jpeg,png,webp',
		'instructions'  => 'Vazio = foto padrão do tema. Use WebP ou JPG de até 200 KB.',
	);

	$fields = array(
		$tab( 'geral', 'Geral' ),
		$f( 'dp_cta_url', 'Link dos botões (UpView)', 'url', array( 'instructions' => 'Vazio = UpView de Aracaju. Só links do UpView: WhatsApp direto é trocado automaticamente.' ) ),
		$f( 'dp_cta_label', 'Texto do botão principal', 'text' ),
		$f( 'dp_cta_label_curto', 'Texto curto (cabeçalho e barra do celular)', 'text' ),
		$f( 'dp_inicio', 'Início do evento', 'date_time_picker', array( 'display_format' => 'd/m/Y H:i', 'return_format' => 'Y-m-d H:i:s', 'first_day' => 0, 'instructions' => 'Usado na contagem regressiva.' ) ),
		$f( 'dp_fim', 'Fim do evento', 'date_time_picker', array( 'display_format' => 'd/m/Y H:i', 'return_format' => 'Y-m-d H:i:s', 'first_day' => 0 ) ),
		$f( 'dp_vagas', 'Horários restantes', 'number', array( 'min' => 0, 'instructions' => 'Mostra "Restam N horários" na faixa, na barra do celular e no fechamento. Vazio = não mostra. Só preencha com o número real.' ) ),
		$f( 'dp_reservados', 'Horários já reservados', 'number', array( 'min' => 0, 'wrapper' => array( 'width' => 33 ), 'instructions' => 'Com "Total" preenchido, aparece a barra de ocupação.' ) ),
		$f( 'dp_total', 'Total de horários', 'number', array( 'min' => 0, 'wrapper' => array( 'width' => 33 ) ) ),
		$f( 'dp_reservas_atualizado', 'Atualizado em', 'text', array( 'wrapper' => array( 'width' => 34 ), 'placeholder' => 'hoje, 14h' ) ),
		$f( 'dp_noindex', 'Fora do Google (noindex)', 'true_false', array( 'ui' => 1, 'instructions' => 'Recomendado: oferta com data para acabar não deve competir com /aracaju/ na busca.' ) ),

		$tab( 'topo', 'Topo' ),
		$f( 'dp_faixa', 'Faixa dourada do topo', 'text' ),
		$f( 'dp_eyebrow', 'Chamada acima da logo', 'text' ),
		$f( 'dp_titulo', 'Título (H1)', 'text', array( 'instructions' => 'Entre *asteriscos* fica dourado.' ) ),
		$f( 'dp_subtitulo', 'Subtítulo', 'textarea', array( 'rows' => 3, 'new_lines' => '', 'instructions' => 'Aceita <strong>negrito</strong>.' ) ),
		$f( 'dp_microcopy', 'Linha abaixo do botão', 'text' ),
		$f( 'dp_foto_topo', 'Foto do topo', 'image', $image ),
		$f(
			'dp_provas',
			'Provas abaixo do botão',
			'repeater',
			array(
				'layout'       => 'table',
				'max'          => 3,
				'button_label' => 'Adicionar prova',
				'instructions' => 'Ex.: "4,9 no Google" + "120 avaliações". Só números reais.',
				'sub_fields'   => array(
					$sub( 'dp_provas', 'destaque', 'Destaque', 'text' ),
					$sub( 'dp_provas', 'texto', 'Complemento', 'text' ),
				),
			)
		),

		$tab( 'datas', 'Datas' ),
		$f( 'dp_datas_titulo', 'Frase da faixa de datas', 'text', array( 'instructions' => 'Entre *asteriscos* fica dourado.' ) ),
		$f(
			'dp_dias',
			'Dias do evento',
			'repeater',
			array(
				'layout'       => 'table',
				'max'          => 4,
				'button_label' => 'Adicionar dia',
				'sub_fields'   => array(
					$sub( 'dp_dias', 'dia_semana', 'Dia da semana', 'text' ),
					$sub( 'dp_dias', 'data', 'Data por extenso', 'text' ),
					$sub( 'dp_dias', 'data_curta', 'Data curta (bloco Onde acontece)', 'text' ),
					$sub( 'dp_dias', 'horario', 'Horário de atendimento', 'text', array( 'placeholder' => '08h às 20h' ) ),
				),
			)
		),

		$tab( 'casos', 'Tratamentos' ),
		$f( 'dp_casos_eyebrow', 'Chamada', 'text' ),
		$f( 'dp_casos_titulo', 'Título', 'text' ),
		$f( 'dp_casos_texto', 'Texto', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
		$f( 'dp_casos_condicao', 'Frase da condição (em todos os cards)', 'text' ),
		$f(
			'dp_casos',
			'Cards',
			'repeater',
			array(
				'layout'       => 'block',
				'max'          => 4,
				'button_label' => 'Adicionar card',
				'sub_fields'   => array(
					$sub( 'dp_casos', 'tag', 'Etiqueta (tratamento)', 'text' ),
					$sub( 'dp_casos', 'titulo', 'Título (situação do paciente)', 'text' ),
					$sub( 'dp_casos', 'texto', 'Texto', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
					$sub( 'dp_casos', 'imagem', 'Foto (16:10)', 'image', $image ),
					$sub( 'dp_casos', 'link', 'Link próprio do UpView', 'url', array( 'instructions' => 'Opcional: link do UpView com mensagem desse tratamento. Vazio = link geral.' ) ),
					$sub( 'dp_casos', 'botao', 'Texto do botão', 'text' ),
					$sub( 'dp_casos', 'destaque', 'Card em destaque', 'true_false', array( 'ui' => 1 ) ),
				),
			)
		),
		$f( 'dp_ajuda_titulo', '"Não sabe qual é o seu caso?" — título', 'text' ),
		$f( 'dp_ajuda_texto', '"Não sabe qual é o seu caso?" — texto', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
		$f( 'dp_ajuda_botao', '"Não sabe qual é o seu caso?" — botão', 'text' ),

		$tab( 'resultado', 'Resultado real' ),
		$f( 'dp_res_eyebrow', 'Chamada', 'text' ),
		$f( 'dp_res_titulo', 'Título', 'text' ),
		$f( 'dp_res_foto_1', 'Foto 1', 'image', $image ),
		$f( 'dp_res_foto_2', 'Foto 2', 'image', $image ),
		$f( 'dp_res_video', 'Vídeo (MP4 vertical)', 'file', array( 'return_format' => 'array', 'mime_types' => 'mp4', 'instructions' => 'Vazio = vídeo padrão do tema. Até 3 MB, sem som.' ) ),
		$f( 'dp_res_video_poster', 'Capa do vídeo', 'image', $image ),
		$f( 'dp_res_video_legenda', 'Legenda do vídeo', 'text' ),
		$f( 'dp_res_aviso', 'Aviso abaixo das fotos', 'textarea', array( 'rows' => 2, 'new_lines' => '', 'instructions' => 'Mantenha a menção à autorização de uso de imagem (CFO).' ) ),

		$tab( 'passos', 'Passos' ),
		$f( 'dp_passos_titulo', 'Título', 'text' ),
		$f(
			'dp_passos',
			'Passos',
			'repeater',
			array(
				'layout'       => 'block',
				'max'          => 4,
				'button_label' => 'Adicionar passo',
				'sub_fields'   => array(
					$sub( 'dp_passos', 'titulo', 'Título', 'text' ),
					$sub( 'dp_passos', 'texto', 'Texto', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				),
			)
		),
		$f( 'dp_passos_foto', 'Foto ao lado dos passos', 'image', $image ),

		$tab( 'prova', 'Pacientes' ),
		$f( 'dp_prova_eyebrow', 'Chamada', 'text' ),
		$f( 'dp_prova_titulo', 'Título', 'text' ),
		$f(
			'dp_numeros',
			'Números',
			'repeater',
			array(
				'layout'       => 'table',
				'max'          => 4,
				'button_label' => 'Adicionar número',
				'instructions' => 'Só números reais e comprováveis.',
				'sub_fields'   => array(
					$sub( 'dp_numeros', 'valor', 'Número', 'text' ),
					$sub( 'dp_numeros', 'rotulo', 'Rótulo', 'text' ),
				),
			)
		),
		$f(
			'dp_depoimentos',
			'Depoimentos',
			'repeater',
			array(
				'layout'       => 'block',
				'max'          => 6,
				'button_label' => 'Adicionar depoimento',
				'instructions' => 'Copie avaliações reais do Google (de preferência citando implante ou prótese). Vazio = bloco não aparece.',
				'sub_fields'   => array(
					$sub( 'dp_depoimentos', 'texto', 'Texto', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
					$sub( 'dp_depoimentos', 'nome', 'Nome', 'text' ),
					$sub( 'dp_depoimentos', 'fonte', 'Fonte', 'text', array( 'default_value' => 'Google' ) ),
				),
			)
		),

		$tab( 'escassez', 'Reserva' ),
		$f( 'dp_escassez_titulo', 'Título', 'text' ),
		$f( 'dp_escassez_texto', 'Texto (quando não há barra de ocupação)', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
		$f( 'dp_escassez_botao', 'Botão', 'text' ),

		$tab( 'pagamento', 'Pagamento' ),
		$f( 'dp_pag_eyebrow', 'Chamada', 'text' ),
		$f( 'dp_pag_titulo', 'Título', 'text' ),
		$f( 'dp_pag_texto', 'Texto', 'textarea', array( 'rows' => 2, 'new_lines' => '', 'instructions' => 'Sem preço nem número de parcelas: as condições são apresentadas no atendimento.' ) ),
		$f(
			'dp_pagamentos',
			'Formas de pagamento',
			'repeater',
			array(
				'layout'       => 'table',
				'max'          => 4,
				'button_label' => 'Adicionar forma',
				'sub_fields'   => array(
					$sub(
						'dp_pagamentos',
						'icone',
						'Ícone',
						'select',
						array(
							'choices'       => array(
								'cartao' => 'Cartão',
								'boleto' => 'Boleto',
								'pix'    => 'Pix',
							),
							'default_value' => 'cartao',
						)
					),
					$sub( 'dp_pagamentos', 'titulo', 'Título', 'text' ),
					$sub( 'dp_pagamentos', 'texto', 'Texto', 'text' ),
				),
			)
		),

		$tab( 'local', 'Local' ),
		$f( 'dp_local_eyebrow', 'Chamada', 'text' ),
		$f( 'dp_local_titulo', 'Título', 'text' ),
		$f( 'dp_endereco_1', 'Endereço — linha 1', 'text' ),
		$f( 'dp_endereco_2', 'Endereço — linha 2', 'text' ),
		$f( 'dp_estacionamento', 'Estacionamento', 'text', array( 'instructions' => 'Vazio = não aparece.' ) ),
		$f( 'dp_maps_url', 'Link "Como chegar"', 'url' ),
		$f( 'dp_maps_embed', 'URL do mapa incorporado (src do iframe)', 'url', array( 'instructions' => 'O mapa só carrega quando a pessoa toca nele (página mais leve no celular).' ) ),

		$tab( 'faq', 'Perguntas' ),
		$f( 'dp_faq_titulo', 'Título', 'text' ),
		$f(
			'dp_faq',
			'Perguntas e respostas',
			'repeater',
			array(
				'layout'       => 'block',
				'max'          => 12,
				'button_label' => 'Adicionar pergunta',
				'instructions' => 'Respostas clínicas (dor, prazo, osso, dentadura) só depois da revisão do responsável técnico.',
				'sub_fields'   => array(
					$sub( 'dp_faq', 'pergunta', 'Pergunta', 'text' ),
					$sub( 'dp_faq', 'resposta', 'Resposta', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
				),
			)
		),

		$tab( 'final', 'Fechamento e rodapé' ),
		$f( 'dp_final_titulo', 'Título do fechamento', 'text', array( 'instructions' => 'Entre *asteriscos* fica dourado.' ) ),
		$f( 'dp_final_texto', 'Texto do fechamento', 'text', array( 'instructions' => 'Com "Horários restantes" preenchido, "Restam N horários." entra antes deste texto.' ) ),
		$f( 'dp_final_micro', 'Linha abaixo do botão', 'text' ),
		$f( 'dp_rodape_empresa', 'Nome no rodapé', 'text' ),
		$f( 'dp_cnpj', 'CNPJ', 'text' ),
		$f( 'dp_rt', 'Responsável técnico', 'text', array( 'placeholder' => 'Dr(a). Nome — CRO-SE 0000', 'instructions' => 'Obrigatório em anúncio odontológico (CFO).' ) ),
		$f( 'dp_aviso', 'Aviso legal', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_dia_pro',
			'title'                 => 'Dia Pró — Landing page',
			'fields'                => $fields,
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => PROODONTO_DP_TEMPLATE,
					),
				),
			),
			'position'              => 'acf_after_title',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => array( 'the_content', 'excerpt', 'discussion', 'comments', 'featured_image' ),
			'active'                => true,
		)
	);
}

/* -------------------------------------------------------------------------
 * 4. Assets
 * ---------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', 'proodonto_dp_enqueue', 100 );

function proodonto_dp_enqueue() {
	if ( ! proodonto_is_dia_pro_page() ) {
		return;
	}

	// O CSS do site é desenhado para o cabeçalho/rodapé claros do site —
	// nada disso aparece aqui. Sai da página: menos bytes no celular.
	// Idem para o CSS de blocos do WordPress (a página não tem conteúdo do editor).
	foreach ( array( 'proodonto-tailwind', 'proodonto-style', 'proodonto-main', 'swiper', 'glightbox', 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $handle ) {
		wp_dequeue_style( $handle );
	}

	// CSS de página gerado por slug (inc/page-generator.php), se existir.
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	if ( $slug ) {
		wp_dequeue_style( 'proodonto-page-' . $slug );
	}

	$css = 'assets/css/dia-pro.css';
	$js  = 'assets/js/dia-pro.js';

	wp_enqueue_style( 'proodonto-dia-pro', PROODONTO_URI . '/' . $css, array(), proodonto_asset_version( $css ) );

	wp_enqueue_script(
		'proodonto-dia-pro',
		PROODONTO_URI . '/' . $js,
		array(),
		proodonto_asset_version( $js ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}

/**
 * Head da página: sem critical.css do site (é do cabeçalho claro) e sem o
 * preload da imagem destacada; entram o preload da logo do Dia Pró (LCP no
 * celular) e o peso 700 do Poppins (títulos e botões).
 */
add_action(
	'template_redirect',
	static function () {
		if ( ! proodonto_is_dia_pro_page() ) {
			return;
		}

		remove_action( 'wp_head', 'proodonto_inline_critical_css', 0 );
		remove_action( 'wp_head', 'proodonto_preload_lcp_image', 1 );

		add_action(
			'wp_head',
			static function () {
				$logo_sm = PROODONTO_URI . '/assets/images/dia-pro/logo-dia-pro-sm.webp';
				$logo    = PROODONTO_URI . '/assets/images/dia-pro/logo-dia-pro.webp';
				printf(
					'<link rel="preload" as="image" href="%1$s" imagesrcset="%1$s 640w, %2$s 1200w" imagesizes="(min-width: 900px) 440px, 260px" fetchpriority="high">' . "\n",
					esc_url( $logo_sm ),
					esc_url( $logo )
				);
				printf(
					'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin="anonymous">' . "\n",
					esc_url( PROODONTO_URI . '/assets/fonts/poppins/poppins-700.woff2' )
				);
				echo '<meta name="theme-color" content="#0a0a0a">' . "\n";
			},
			1
		);

		// Botões flutuantes de WhatsApp: a página tem barra fixa própria.
		add_filter( 'joinchat_show', '__return_false', 99 );
	}
);

/* -------------------------------------------------------------------------
 * 5. Indexação
 * ---------------------------------------------------------------------- */

function proodonto_dp_is_noindex( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();

	if ( ! function_exists( 'get_field' ) || ! metadata_exists( 'post', $post_id, 'dp_noindex' ) ) {
		return true;
	}

	return (bool) get_field( 'dp_noindex', $post_id );
}

add_filter(
	'wpseo_robots_array',
	static function ( $robots ) {
		if ( proodonto_is_dia_pro_page() && proodonto_dp_is_noindex() ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	},
	100
);

// Sem Yoast: o tema (inc/seo.php) lê o noindex do metabox "_proodonto_meta_noindex".
add_filter(
	'get_post_metadata',
	static function ( $value, $object_id, $meta_key ) {
		if ( '_proodonto_meta_noindex' !== $meta_key || is_admin() ) {
			return $value;
		}
		if ( PROODONTO_DP_TEMPLATE === get_post_meta( $object_id, '_wp_page_template', true ) && proodonto_dp_is_noindex( $object_id ) ) {
			return array( '1' );
		}
		return $value;
	},
	10,
	3
);

// Sem Yoast: o próprio WordPress também imprime o robots.
add_filter(
	'wp_robots',
	static function ( $robots ) {
		if ( ! defined( 'WPSEO_VERSION' ) && proodonto_is_dia_pro_page() && proodonto_dp_is_noindex() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

add_filter(
	'wpseo_exclude_from_sitemap_by_post_ids',
	static function ( $ids ) {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => 20,
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => PROODONTO_DP_TEMPLATE, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		foreach ( $pages as $id ) {
			if ( proodonto_dp_is_noindex( $id ) ) {
				$ids[] = (int) $id;
			}
		}
		return $ids;
	}
);

/** Classe no <body> para o CSS esconder botões flutuantes de plugins. */
add_filter(
	'body_class',
	static function ( $classes ) {
		if ( proodonto_is_dia_pro_page() ) {
			$classes[] = 'dp-page';
		}
		return $classes;
	}
);
