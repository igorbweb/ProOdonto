<?php
/**
 * Conteúdo inicial — roda UMA vez por versão, no painel, por quem tem
 * manage_options, com este tema ativo.
 *
 *   - Só grava campo VAZIO: nunca atropela o que alguém editou.
 *   - Só fatos já aprovados (canvas "Proposta 2" + inc/units-map.php do
 *     tema antigo, horários confirmados pela clínica em 10/10/2026, notas
 *     do Google lidas em 04/10). Nada de "[placeholder]": o que depende do
 *     Dr. Alisson ou de foto real fica vazio e a seção não aparece.
 *   - "gratuita"/"sem custo" fora da copy até a revisão do Dr. Alisson.
 *   - Não cria páginas: elas já existem em produção. Se não existirem no
 *     ambiente, a parte daquela página é pulada.
 *
 * Para rodar de novo depois de ajustar este arquivo: suba
 * PROODONTO_SEED_VERSION.
 *
 * Versão 2 (09/11/2026): copy de fallback para o que ainda ficava vazio —
 * conteúdo dos 5 tratamentos (topo, "o que é", para quem, etapas, FAQ),
 * texto da clínica em cada unidade, redes sociais, CTAs dos posts e
 * perguntas extras na Home, no hub e nas unidades. Tudo usa só fatos já
 * aprovados (avaliação com exame de imagem na clínica, plano escrito com
 * valor fechado, só particular, endereços e horários). O texto clínico dos
 * tratamentos é linguagem geral e conservadora — sem prazo, sem promessa
 * de resultado — e continua sujeito à revisão do Dr. Alisson.
 * Ficam vazios de propósito: fotos, avaliações e resultados (só reais),
 * telefone, perfil do Google, CNPJ, "O que pode vir antes" (bloqueado até
 * a revisão) e a resposta rápida de cada post (é escrita post a post).
 *
 * FAQ: além de preencher repeaters vazios, a v2 ACRESCENTA ao fim as
 * perguntas que ainda não existem (compara pelo texto da pergunta). Nada
 * é apagado ou reescrito.
 *
 * Versão 3/4 (09/11/2026): a criação das páginas, dos 5 tratamentos e dos
 * 4 posts de marcação saiu daqui e foi para inc/content-pages.php, que roda
 * SEM depender do ACF (ao ativar o tema e no painel). Este seed só preenche
 * campos, então continua exigindo o ACF PRO ativo; se o ACF for ativado
 * depois, ele roda na próxima tela do painel.
 */

defined( 'ABSPATH' ) || exit;

const PROODONTO_SEED_VERSION = 4;

add_action( 'admin_init', 'proodonto_seed_content', 20 );

function proodonto_seed_content() {
	if ( (int) get_option( 'proodonto_premium_seed' ) >= PROODONTO_SEED_VERSION ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'update_field' ) ) {
		return;
	}

	// Páginas, tratamentos e posts de marcação: garante que existem (sem ACF
	// isso já rodou ao ativar o tema; aqui é só por segurança, idempotente).
	if ( function_exists( 'proodonto_seed_structure' ) ) {
		proodonto_seed_structure( true );
	}

	proodonto_seed_options();
	proodonto_seed_servicos();
	proodonto_seed_home();
	proodonto_seed_units_pages();
	proodonto_seed_hub();

	// v2.
	proodonto_seed_v2_options();
	proodonto_seed_v2_servicos();
	proodonto_seed_v2_home();
	proodonto_seed_v2_units_pages();
	proodonto_seed_v2_hub();
	proodonto_seed_v2_posts();

	// v3/v4: campos ACF dos posts de marcação.
	if ( function_exists( 'proodonto_seed_marcacao_fields' ) ) {
		proodonto_seed_marcacao_fields();
	}

	update_option( 'proodonto_premium_seed', PROODONTO_SEED_VERSION, false );
}

/** Grava só se o campo estiver vazio. */
function proodonto_seed( $name, $value, $post_id ) {
	$current = get_field( $name, $post_id, false );
	if ( null === $current || '' === $current || false === $current || array() === $current || 0 === $current ) {
		update_field( $name, $value, $post_id );
	}
}

/**
 * Acrescenta linhas a um repeater sem tocar nas que já existem. Uma linha
 * entra só se o valor de $key (normalmente a pergunta) ainda não estiver lá.
 */
function proodonto_seed_append( $name, $rows, $post_id, $key = 'pergunta' ) {
	$current = get_field( $name, $post_id );
	$current = is_array( $current ) ? array_values( $current ) : array();

	$norm = static function ( $text ) {
		return mb_strtolower( trim( preg_replace( '/\s+/', ' ', (string) $text ) ) );
	};
	$seen = array();
	foreach ( $current as $row ) {
		$seen[ $norm( $row[ $key ] ?? '' ) ] = true;
	}

	$added = false;
	foreach ( $rows as $row ) {
		$k = $norm( $row[ $key ] ?? '' );
		if ( '' === $k || isset( $seen[ $k ] ) ) {
			continue;
		}
		$current[]  = $row;
		$seen[ $k ] = true;
		$added      = true;
	}
	if ( $added ) {
		update_field( $name, $current, $post_id );
	}
}

/** IDs de posts publicados pelos slugs dados (os que não existem ficam de fora). */
function proodonto_seed_post_ids( $slugs ) {
	$ids = array();
	foreach ( $slugs as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $post && 'publish' === $post->post_status ) {
			$ids[] = (int) $post->ID;
		}
	}
	return $ids;
}

/** "9h–19h" → "das 9h às 19h"; "8h–13h e 14h–19h" → "das 8h às 13h e das 14h às 19h". */
function proodonto_seed_hours_prose( $ranges ) {
	return preg_replace( '/(\d{1,2}h(?:\d{2})?)–(\d{1,2}h(?:\d{2})?)/u', 'das $1 às $2', $ranges );
}

/** ID de um tratamento pelo slug (qualquer status). */
function proodonto_seed_servico_id( $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'servico' );
	return $post ? (int) $post->ID : 0;
}

/** Faixas de horário no formato do repeater. */
function proodonto_seed_hours( $weekday, $saturday = array( '08:00', '12:00' ) ) {
	$rows = array();
	foreach ( $weekday as $range ) {
		$rows[] = array(
			'rotulo'       => 'Segunda a sexta',
			'rotulo_curto' => 'Seg a sex',
			'dias'         => array( 'Mo', 'Tu', 'We', 'Th', 'Fr' ),
			'abre'         => $range[0],
			'fecha'        => $range[1],
		);
	}
	$rows[] = array(
		'rotulo'       => 'Sábado',
		'rotulo_curto' => 'Sáb',
		'dias'         => array( 'Sa' ),
		'abre'         => $saturday[0],
		'fecha'        => $saturday[1],
	);
	return $rows;
}

/**
 * As 3 unidades (endereço, UpView, Maps, nota, horários). Fonte do seed das
 * Opções e da página Sobre quando o ACF ainda não está ativo.
 *
 * @param callable|null $page_id slug → ID da página (ou '').
 */
function proodonto_seed_units_data( $page_id = null ) {
	$page_id = $page_id ? $page_id : static function () {
		return '';
	};
	return array(
		array(
			'nome'         => 'Aracaju',
			'slug'         => 'aracaju',
			'bairro'       => 'Jardins',
			'uf'           => 'SE',
			'cep'          => '49025-620',
			'endereco'     => 'Av. Pres. Tancredo Neves, 1028',
			'whatsapp_url' => 'https://api.upviewcrm.com/go/proodonto-aracaju/lp-aracaju',
			'maps_url'     => 'https://maps.app.goo.gl/wvRKAiDYqjFTaJbd9',
			'nota'         => '5,0',
			'total'        => '301',
			'pagina'       => $page_id( 'aracaju' ),
			'horarios'     => proodonto_seed_hours( array( array( '09:00', '19:00' ) ) ),
		),
		array(
			'nome'         => 'Lagarto',
			'slug'         => 'lagarto',
			'bairro'       => 'Centro',
			'uf'           => 'SE',
			'cep'          => '49400-000',
			'endereco'     => 'Av. Contorno, 477',
			'whatsapp_url' => 'https://api.upviewcrm.com/go/proodonto-lagarto/lp-lagarto',
			'maps_url'     => 'https://maps.app.goo.gl/ZuvT3qpFaKa67z5P8',
			'nota'         => '4,9',
			'total'        => '419',
			'pagina'       => $page_id( 'lagarto' ),
			'horarios'     => proodonto_seed_hours( array( array( '08:00', '18:00' ) ) ),
		),
		array(
			'nome'         => 'Simão Dias',
			'slug'         => 'simao-dias',
			'bairro'       => 'Centro',
			'uf'           => 'SE',
			'cep'          => '49480-000',
			'endereco'     => 'Av. Construtor João Antônio de Santana, 330',
			'whatsapp_url' => 'https://api.upviewcrm.com/go/proodonto-simao-dias/lp-simao-dias',
			'maps_url'     => 'https://maps.app.goo.gl/JvfkXnriGvccPRaY8',
			'nota'         => '5,0',
			'total'        => '476',
			'pagina'       => $page_id( 'simao-dias' ),
			'horarios'     => proodonto_seed_hours( array( array( '08:00', '13:00' ), array( '14:00', '19:00' ) ) ),
		),
	);
}

function proodonto_seed_options() {
	$o = 'option';

	proodonto_seed( 'nota_resumo', '4,9 a 5,0', $o );
	proodonto_seed( 'unidades_eyebrow', 'Unidades', $o );
	proodonto_seed( 'unidades_titulo', 'Perto de *você*', $o );
	proodonto_seed( 'total_avaliacoes', '+1.190', $o );
	proodonto_seed( 'header_cta_label', 'Agendar avaliação', $o );

	$page_id = static function ( $slug ) {
		$p = get_page_by_path( $slug );
		return $p ? (int) $p->ID : '';
	};

	proodonto_seed( 'unidades', proodonto_seed_units_data( $page_id ), $o );

	proodonto_seed(
		'numeros',
		array(
			array( 'valor' => '+22 mil', 'rotulo' => 'atendimentos desde 2019', 'rotulo_curto' => 'atendimentos', 'so_desktop' => 0 ),
			array( 'valor' => '+7 anos', 'rotulo' => 'de mercado', 'rotulo_curto' => 'de mercado', 'so_desktop' => 0 ),
			array( 'valor' => '13', 'rotulo' => 'profissionais', 'rotulo_curto' => 'profissionais', 'so_desktop' => 0 ),
			array( 'valor' => '3', 'rotulo' => 'unidades em Sergipe', 'rotulo_curto' => 'unidades', 'so_desktop' => 1 ),
		),
		$o
	);

	proodonto_seed( 'passos_eyebrow', 'Primeira avaliação', $o );
	proodonto_seed( 'passos_titulo', 'Uma visita. *Tudo resolvido.*', $o );
	proodonto_seed( 'passos_texto', 'Sem idas e vindas: você conhece o caso, o plano e o valor antes de decidir.', $o );
	proodonto_seed( 'passos_botao', 'Agendar minha avaliação', $o );
	proodonto_seed( 'passos_botao_unidade', 'Agendar em {cidade}', $o );
	proodonto_seed(
		'passos',
		array(
			array( 'titulo' => 'Exame de imagem', 'descricao' => 'Feito na própria clínica, sem encaminhamento.' ),
			array( 'titulo' => 'Avaliação pela equipe', 'descricao' => 'Os profissionais da unidade avaliam o seu caso com calma.' ),
			array( 'titulo' => 'Plano escrito, valor fechado', 'descricao' => 'Você sai com o plano e o valor na mesma visita.' ),
		),
		$o
	);

	proodonto_seed( 'resultados_eyebrow', 'Resultados', $o );
	proodonto_seed( 'resultados_titulo', 'Sorrisos *reais*', $o );
	proodonto_seed( 'resultados_nota', 'Publicadas com autorização por escrito dos pacientes. Resultados variam de pessoa para pessoa. Responsável técnico: Dr. Alisson Matheus Santana dos Santos · CRO-SE 3147.', $o );
	proodonto_seed( 'resultados_link_label', 'Ver mais resultados', $o );

	proodonto_seed( 'avaliacoes_eyebrow', 'Avaliações no Google · {cidade}', $o );
	proodonto_seed( 'avaliacoes_titulo', 'Quem já sorri *conta*', $o );
	proodonto_seed( 'avaliacoes_link_label', 'Ler todas no Google', $o );

	proodonto_seed( 'fechamento_titulo', 'Seu próximo sorriso começa *numa conversa.*', $o );
	proodonto_seed( 'fechamento_texto', 'Escolha a unidade e fale direto com a equipe.', $o );
	proodonto_seed( 'fechamento_botao', 'Escolher unidade', $o );

	proodonto_seed( 'rodape_texto', 'Rede de clínicas odontológicas em Sergipe. Exame de imagem na própria clínica e plano escrito com valor fechado.', $o );
	proodonto_seed( 'rodape_botao', 'Agendar avaliação', $o );
	proodonto_seed( 'responsavel_nome', 'Dr. Alisson Matheus Santana dos Santos', $o );
	proodonto_seed( 'responsavel_cargo', 'Cirurgião-Dentista', $o );
	proodonto_seed( 'responsavel_cro', 'CRO-SE 3147', $o );
	proodonto_seed( 'rodape_legal', '© {ano} PróOdonto', $o );
	proodonto_seed( 'repassar_utm', 1, $o );
}

function proodonto_seed_servicos() {
	$cards = array(
		'protese-dentaria'          => array( 'Repor dentes perdidos com conforto e naturalidade — fixa ou removível.', 'tooth' ),
		'implante-dentario'         => array( 'Dente fixo no lugar do que faltava', 'implant' ),
		'ortodontia'                => array( 'Dentes alinhados e mordida certa', 'ortho' ),
		'protocolo-sobre-implantes' => array( 'Todos os dentes de uma arcada', 'protocol' ),
		'estetica-dental'           => array( 'Cor, forma e harmonia do sorriso', 'aesthetic' ),
	);
	foreach ( $cards as $slug => $card ) {
		$id = proodonto_seed_servico_id( $slug );
		if ( ! $id ) {
			continue;
		}
		proodonto_seed( 'card_para_quem', $card[0], $id );
		proodonto_seed( 'srv_icone', $card[1], $id );
	}

	$protese = proodonto_seed_servico_id( 'protese-dentaria' );
	if ( $protese ) {
		proodonto_seed( 'srv_selo', 'Mais procurado · Repor dentes', $protese );
		proodonto_seed( 'srv_botao', 'Agendar avaliação de prótese', $protese );
		proodonto_seed( 'srv_unidades_titulo', 'Avaliação de prótese *perto de você*', $protese );
		proodonto_seed( 'srv_unidades_texto', 'O WhatsApp abre com a mensagem sobre prótese.', $protese );
	}

	// Rótulos de seção iguais em todos os tratamentos.
	foreach ( proodonto_servicos_query()->posts as $servico ) {
		proodonto_seed( 'srv_botao_etapas', 'Como funciona', $servico->ID );
		proodonto_seed( 'srv_para_quem_eyebrow', 'Para quem é', $servico->ID );
		proodonto_seed( 'srv_para_quem_titulo', 'É para *você* se…', $servico->ID );
		proodonto_seed( 'srv_etapas_eyebrow', 'Etapa por etapa', $servico->ID );
		proodonto_seed( 'srv_etapas_titulo', 'Como funciona *o tratamento*', $servico->ID );
		proodonto_seed( 'srv_etapas_botao', 'Começar pela avaliação', $servico->ID );
		proodonto_seed( 'srv_faq_titulo', 'Perguntas *frequentes*', $servico->ID );
		proodonto_seed(
			'srv_provas',
			array(
				array( 'texto' => 'Exame de imagem na clínica' ),
				array( 'texto' => 'Plano escrito na 1ª visita' ),
				array( 'texto' => '3 unidades em Sergipe' ),
				array( 'texto' => '4,9 a 5,0 no Google' ),
			),
			$servico->ID
		);
		proodonto_seed( 'srv_stat_valor', '+22 mil', $servico->ID );
		proodonto_seed( 'srv_stat_rotulo', 'atendimentos desde 2019', $servico->ID );
	}
}

function proodonto_seed_home() {
	$home = proodonto_page_by_template( 'page-home.php' );
	$id   = $home ? $home->ID : (int) get_option( 'page_on_front' );
	if ( ! $id ) {
		return;
	}

	proodonto_seed( 'home_hero_eyebrow', 'Rede odontológica em Sergipe · desde 2019', $id );
	proodonto_seed( 'home_hero_titulo', 'Dentista em *Aracaju, Lagarto* e *Simão Dias*', $id );
	proodonto_seed( 'home_hero_lead', 'Exame de imagem na própria clínica e plano escrito com valor fechado, na mesma visita.', $id );
	proodonto_seed( 'home_seletor_titulo', 'Agende na unidade mais perto', $id );

	proodonto_seed( 'home_trat_eyebrow', 'Tratamentos', $id );
	proodonto_seed( 'home_trat_titulo', 'O que você quer *resolver*?', $id );
	$destaque = proodonto_seed_servico_id( 'protese-dentaria' );
	if ( $destaque ) {
		proodonto_seed( 'home_trat_destaque', $destaque, $id );
	}
	$lista = array_filter( array_map( 'proodonto_seed_servico_id', array( 'implante-dentario', 'ortodontia', 'protocolo-sobre-implantes', 'estetica-dental' ) ) );
	if ( $lista ) {
		proodonto_seed( 'home_trat_lista', array_values( $lista ), $id );
	}
	proodonto_seed( 'home_trat_botao', 'Ver todos os tratamentos', $id );

	proodonto_seed( 'home_unid_eyebrow', 'Unidades', $id );
	proodonto_seed( 'home_unid_titulo', 'Perto de *você*', $id );

	proodonto_seed( 'home_sobre_titulo', 'Sobre a *PróOdonto*', $id );
	proodonto_seed( 'home_sobre_texto', 'A PróOdonto é uma rede de clínicas odontológicas de Sergipe, com unidades em Aracaju, Lagarto e Simão Dias. Desde 2019, são mais de 22 mil atendimentos feitos por uma equipe de 13 profissionais.', $id );
	$sobre = get_page_by_path( 'sobre' );
	if ( $sobre ) {
		proodonto_seed(
			'home_sobre_link',
			array(
				'title'  => 'Conheça a clínica e a equipe',
				'url'    => get_permalink( $sobre ),
				'target' => '',
			),
			$id
		);
	}

	proodonto_seed( 'home_faq_titulo', 'Perguntas *frequentes*', $id );
	proodonto_seed(
		'home_faq',
		array(
			array(
				'pergunta' => 'Qual o horário das unidades?',
				'resposta' => 'Aracaju: seg a sex 9h–19h. Lagarto: seg a sex 8h–18h. Simão Dias: seg a sex 8h–13h e 14h–19h. As três abrem aos sábados das 8h às 12h.',
			),
			array(
				'pergunta' => 'O que acontece na primeira avaliação?',
				'resposta' => 'Exame de imagem na própria clínica, avaliação pela equipe e plano escrito com valor fechado, na mesma visita.',
			),
			array(
				'pergunta' => 'Quais tratamentos a PróOdonto faz?',
				'resposta' => 'Prótese dentária, implante, protocolo, ortodontia e estética dental — cada um com página própria no catálogo.',
			),
		),
		$id
	);
}

function proodonto_seed_units_pages() {
	foreach ( proodonto_get_units( true ) as $unit ) {
		$page = $unit['page_id'] ? get_post( $unit['page_id'] ) : get_page_by_path( $unit['slug'] );
		if ( ! $page ) {
			continue;
		}
		$id = $page->ID;

		if ( ! get_page_template_slug( $id ) ) {
			update_post_meta( $id, '_wp_page_template', 'page-vendas.php' );
		}

		proodonto_seed( 'uni_unidade', $unit['slug'], $id );
		proodonto_seed( 'uni_versao', 'a', $id );
		proodonto_seed( 'uni_hero_h1', 'Dentista em {cidade} · {bairro}', $id );
		proodonto_seed( 'uni_hero_titulo_a', 'Você ainda cobre a boca *na hora de sorrir?*', $id );
		proodonto_seed( 'uni_hero_botao_a', 'Agendar avaliação pelo WhatsApp', $id );
		proodonto_seed( 'uni_hero_titulo_b', 'O que mais incomoda *no seu sorriso hoje?*', $id );
		proodonto_seed(
			'uni_hero_caminhos',
			array(
				array( 'titulo' => 'Faltam dentes', 'subtitulo' => 'Implante ou prótese', 'link' => '' ),
				array( 'titulo' => 'Dentes tortos', 'subtitulo' => 'Ortodontia', 'link' => '' ),
			),
			$id
		);
		proodonto_seed( 'uni_hero_lead', 'Exame de imagem aqui na clínica, avaliação pela equipe e plano escrito com valor fechado, na mesma visita.', $id );
		if ( 'aracaju' !== $unit['slug'] ) {
			proodonto_seed( 'uni_hero_extra', 'Sem precisar viajar até Aracaju.', $id );
		}
		proodonto_seed( 'uni_trat_eyebrow', 'Tratamentos em {cidade}', $id );
		proodonto_seed( 'uni_trat_titulo', 'O que você quer *resolver*?', $id );
		proodonto_seed( 'uni_clinica_eyebrow', 'A clínica em {cidade}', $id );
		proodonto_seed( 'uni_clinica_titulo', 'Feita para você *se sentir em casa*', $id );
		proodonto_seed( 'uni_equipe_titulo', 'Equipe em {cidade}', $id );
		proodonto_seed( 'uni_faq_titulo', 'Dúvidas sobre *a unidade*', $id );

		$weekday  = '';
		$saturday = '';
		foreach ( proodonto_unit_hours_lines( $unit, false ) as $line ) {
			if ( 0 === stripos( $line, 'Sábado' ) ) {
				$saturday = trim( substr( $line, strlen( 'Sábado' ) ) );
			} else {
				$weekday = $line;
			}
		}
		$faq = array();
		if ( $saturday ) {
			$faq[] = array(
				'pergunta' => 'Abre aos sábados?',
				'resposta' => 'Sim, das ' . str_replace( '–', ' às ', $saturday ) . '.' . ( $weekday ? ' Durante a semana: ' . mb_strtolower( $weekday ) . '.' : '' ),
			);
		}
		$faq[] = array(
			'pergunta' => 'Preciso levar algum exame?',
			'resposta' => 'Não. O exame de imagem é feito na própria clínica, na avaliação.',
		);
		proodonto_seed( 'uni_faq', $faq, $id );

		proodonto_seed( 'uni_outras_titulo', 'Outras *unidades*', $id );
		proodonto_seed( 'uni_fechamento_titulo', 'Pronto para *voltar a sorrir?*', $id );
		proodonto_seed( 'uni_fechamento_texto', 'Fale com a equipe de {cidade} pelo WhatsApp.', $id );
		proodonto_seed( 'uni_fechamento_botao', 'Falar com {cidade}', $id );
	}
}

function proodonto_seed_hub() {
	$hub = proodonto_page_by_template( 'page-servicos.php' );
	if ( ! $hub ) {
		$hub = get_page_by_path( 'servicos' );
	}
	if ( ! $hub ) {
		return;
	}
	$id = $hub->ID;

	proodonto_seed( 'hub_eyebrow', 'Tratamentos odontológicos', $id );
	proodonto_seed( 'hub_titulo', 'Cuidado completo para *o seu sorriso*', $id );
	proodonto_seed( 'hub_lead', 'Prótese, implante, protocolo, ortodontia e estética em Aracaju, Lagarto e Simão Dias.', $id );
	proodonto_seed( 'hub_seletor_titulo', 'O que você quer resolver?', $id );

	$s = 'proodonto_seed_servico_id';
	proodonto_seed(
		'hub_grupos',
		array(
			array(
				'atalho'     => 'Repor dentes',
				'atalho_sub' => 'Prótese · implante · protocolo',
				'icone'      => 'tooth',
				'titulo'     => 'Repor *dentes*',
				'subtitulo'  => 'Faltam um, vários ou todos os dentes.',
				'destaque'   => $s( 'protese-dentaria' ),
				'servicos'   => array_values( array_filter( array( $s( 'implante-dentario' ), $s( 'protocolo-sobre-implantes' ) ) ) ),
				'botao'      => 'Agendar avaliação',
			),
			array(
				'atalho'     => 'Alinhar os dentes',
				'atalho_sub' => 'Ortodontia',
				'icone'      => 'ortho',
				'titulo'     => 'Alinhar *os dentes*',
				'subtitulo'  => 'Dentes tortos, separados ou mordida errada.',
				'destaque'   => '',
				'servicos'   => array_values( array_filter( array( $s( 'ortodontia' ) ) ) ),
				'botao'      => '',
			),
			array(
				'atalho'     => 'Sorriso mais bonito',
				'atalho_sub' => 'Estética dental',
				'icone'      => 'aesthetic',
				'titulo'     => 'Deixar o sorriso *mais bonito*',
				'subtitulo'  => 'Cor, forma e harmonia.',
				'destaque'   => '',
				'servicos'   => array_values( array_filter( array( $s( 'estetica-dental' ) ) ) ),
				'botao'      => '',
			),
		),
		$id
	);

	proodonto_seed( 'hub_passos_eyebrow', 'Não sabe por onde começar?', $id );
	proodonto_seed( 'hub_passos_titulo', 'Comece pela *avaliação.*', $id );
	proodonto_seed( 'hub_faq_titulo', 'Perguntas *frequentes*', $id );
	proodonto_seed(
		'hub_faq',
		array(
			array(
				'pergunta' => 'Qual tratamento é o certo para mim?',
				'resposta' => 'Quem define é a avaliação: exame de imagem na própria clínica, avaliação pela equipe e plano escrito com valor fechado, na mesma visita.',
			),
			array(
				'pergunta' => 'Onde ficam as unidades?',
				'resposta' => 'Aracaju (Jardins), Lagarto (Centro) e Simão Dias (Centro). Endereço, horário e rota de cada uma estão nesta página.',
			),
		),
		$id
	);
}

/* -----------------------------------------------------------------------
 * Versão 2 — copy de fallback
 * -------------------------------------------------------------------- */

/** Resposta padrão sobre convênio (a PróOdonto só atende particular). */
function proodonto_seed_faq_convenio() {
	return array(
		'pergunta' => 'A PróOdonto atende convênio?',
		'resposta' => 'Não. O atendimento é só particular. Na primeira avaliação você recebe o plano escrito com o valor fechado e decide com calma, antes de começar.',
	);
}

function proodonto_seed_v2_options() {
	$o = 'option';

	// Perfis oficiais (os mesmos links já usados no site antigo).
	proodonto_seed(
		'redes',
		array(
			array( 'rede' => 'instagram', 'url' => 'https://www.instagram.com/clinicaproodonto/' ),
			array( 'rede' => 'facebook', 'url' => 'https://www.facebook.com/proodontosd/' ),
			array( 'rede' => 'youtube', 'url' => 'https://www.youtube.com/@clinicaproodonto' ),
		),
		$o
	);
}

/**
 * Conteúdo de cada tratamento. Chave = slug do CPT `servico`.
 * Etapas 1 e 2 são iguais em todos (é a primeira avaliação, já aprovada).
 */
function proodonto_seed_v2_servicos_data() {
	$avaliacao = array(
		'titulo'    => 'Avaliação e exame de imagem',
		'descricao' => 'O exame é feito na própria clínica, sem encaminhamento, e a equipe avalia o seu caso com calma.',
		'duracao'   => 'Na primeira visita',
	);
	$plano = array(
		'titulo'    => 'Plano escrito, valor fechado',
		'descricao' => 'Você sai sabendo o que será feito, em que ordem e quanto custa, antes de decidir.',
		'duracao'   => 'Na primeira visita',
	);
	$preco = static function ( $nome ) {
		return array(
			'pergunta' => 'Quanto custa ' . $nome . '?',
			'resposta' => 'Depende do caso. O valor sai fechado no plano escrito, entregue na primeira avaliação, antes de você decidir qualquer coisa.',
		);
	};

	return array(
		'protese-dentaria'          => array(
			'hero_titulo'     => 'Prótese dentária para *voltar a sorrir*',
			'hero_lead'       => 'Fixa ou removível, indicada depois do exame de imagem feito na própria clínica.',
			'resposta_direta' => 'Prótese dentária é a peça que substitui um ou mais dentes perdidos e devolve a mastigação, a fala e a estética do sorriso. Pode ser fixa, como coroas, pontes e próteses sobre implantes, ou removível, como a parcial e a dentadura. O tipo indicado depende do exame de imagem e da avaliação de cada caso.',
			'para_quem'       => array(
				array( 'texto' => 'Perdeu um ou mais dentes', 'icone' => 'tooth' ),
				array( 'texto' => 'Usa uma prótese que machuca, solta ou incomoda', 'icone' => 'adjust' ),
				array( 'texto' => 'Evita sorrir ou deixa de comer certos alimentos', 'icone' => 'aesthetic' ),
				array( 'texto' => 'Tem um dente muito destruído para restaurar', 'icone' => 'info' ),
			),
			'etapas'          => array(
				$avaliacao,
				$plano,
				array(
					'titulo'    => 'Moldagem e provas',
					'descricao' => 'A prótese é feita sob medida e provada na boca até ficar confortável na mordida e natural no sorriso.',
					'duracao'   => 'Conforme o plano',
				),
				array(
					'titulo'    => 'Entrega e ajustes',
					'descricao' => 'Instalação, orientação de cuidados e retorno para ajustar o que for preciso.',
					'duracao'   => 'Após a entrega',
				),
			),
			'faq'             => array(
				array(
					'pergunta' => 'Qual a diferença entre prótese fixa e removível?',
					'resposta' => 'A fixa fica presa nos dentes ou em implantes e só o dentista remove. A removível você tira para limpar e dormir. A avaliação mostra qual serve para o seu caso.',
				),
				array(
					'pergunta' => 'Quanto tempo leva para ficar pronta?',
					'resposta' => 'Varia com o tipo de prótese e o número de provas. O prazo previsto vem no plano escrito, entregue na primeira avaliação.',
				),
				$preco( 'uma prótese dentária' ),
				proodonto_seed_faq_convenio(),
			),
			'unidades_titulo' => '',
			'unidades_texto'  => '',
			'posts'           => array( 'dentadura-ou-protocolo-sergipe' ),
		),
		'implante-dentario'         => array(
			'hero_titulo'     => 'Implante dentário para ter *dente fixo* de novo',
			'hero_lead'       => 'Planejado a partir do exame de imagem feito na própria clínica, com plano escrito e valor fechado na mesma visita.',
			'selo'            => 'Dente fixo',
			'botao'           => 'Agendar avaliação de implante',
			'resposta_direta' => 'Implante dentário é um pino de titânio colocado no osso da maxila ou da mandíbula para fazer o papel da raiz de um dente perdido. Depois que o osso se une ao implante, ele recebe uma coroa fixa, que mastiga e aparece como um dente natural. A indicação depende do exame de imagem e da quantidade de osso.',
			'para_quem'       => array(
				array( 'texto' => 'Perdeu um dente e quer uma solução fixa', 'icone' => 'implant' ),
				array( 'texto' => 'Não quer desgastar os dentes vizinhos para fazer uma ponte', 'icone' => 'tooth' ),
				array( 'texto' => 'Usa prótese removível e quer mais firmeza', 'icone' => 'adjust' ),
				array( 'texto' => 'Quer mastigar sem medo de a prótese soltar', 'icone' => 'check' ),
			),
			'etapas'          => array(
				array(
					'titulo'    => 'Avaliação e exame de imagem',
					'descricao' => 'O exame, feito na própria clínica, mostra a altura e a espessura do osso — é o que define se e onde o implante pode entrar.',
					'duracao'   => 'Na primeira visita',
				),
				$plano,
				array(
					'titulo'    => 'Instalação do implante',
					'descricao' => 'Procedimento com anestesia local para posicionar o implante no osso.',
					'duracao'   => 'Conforme o plano',
				),
				array(
					'titulo'    => 'Cicatrização',
					'descricao' => 'O osso se une ao implante. O tempo varia de pessoa para pessoa, e a equipe acompanha nos retornos.',
					'duracao'   => 'Varia por caso',
				),
				array(
					'titulo'    => 'Coroa sobre o implante',
					'descricao' => 'A coroa é feita sob medida, na cor dos seus dentes, e fixada sobre o implante.',
					'duracao'   => 'Ao final',
				),
			),
			'faq'             => array(
				array(
					'pergunta' => 'Todo mundo pode fazer implante?',
					'resposta' => 'Quem define é a avaliação. O exame de imagem mostra se há osso suficiente e se é preciso algum preparo antes, como um enxerto.',
				),
				array(
					'pergunta' => 'Colocar implante dói?',
					'resposta' => 'A instalação é feita com anestesia local. Nos dias seguintes pode haver incômodo, controlado com as orientações e a medicação indicadas pelo dentista.',
				),
				array(
					'pergunta' => 'Quanto tempo leva o tratamento?',
					'resposta' => 'Depende do caso, porque o osso precisa de um tempo para se unir ao implante. O prazo previsto vem no plano escrito da primeira avaliação.',
				),
				array(
					'pergunta' => 'Qual a diferença entre implante e prótese?',
					'resposta' => 'O implante substitui a raiz; a prótese é a parte que aparece. Muitas vezes os dois andam juntos: o implante segura uma coroa ou uma prótese fixa.',
				),
				$preco( 'um implante dentário' ),
			),
			'unidades_titulo' => 'Avaliação de implante *perto de você*',
			'unidades_texto'  => 'Escolha a unidade e fale com a equipe pelo WhatsApp.',
			'posts'           => array( 'enxerto-osseo-implante-dentario', 'implantodontista-aracaju' ),
		),
		'protocolo-sobre-implantes' => array(
			'hero_titulo'     => 'Protocolo: *dentes fixos* no lugar da dentadura',
			'hero_lead'       => 'Para quem perdeu todos ou quase todos os dentes de cima ou de baixo e quer trocar a dentadura por uma prótese fixa.',
			'selo'            => 'Arcada completa',
			'botao'           => 'Agendar avaliação de protocolo',
			'resposta_direta' => 'Protocolo sobre implantes é uma prótese fixa que repõe todos os dentes de uma arcada, presa sobre implantes instalados no osso. Diferente da dentadura, não sai da boca nem se apoia na gengiva, o que dá mais firmeza para mastigar e falar. O número de implantes e o tipo de prótese dependem do exame de imagem.',
			'para_quem'       => array(
				array( 'texto' => 'Perdeu todos ou quase todos os dentes de uma arcada', 'icone' => 'protocol' ),
				array( 'texto' => 'Usa dentadura que solta, machuca ou atrapalha a fala', 'icone' => 'adjust' ),
				array( 'texto' => 'Quer voltar a mastigar com firmeza', 'icone' => 'check' ),
				array( 'texto' => 'Prefere uma prótese que não precisa tirar para dormir', 'icone' => 'clock' ),
			),
			'etapas'          => array(
				$avaliacao,
				$plano,
				array(
					'titulo'    => 'Instalação dos implantes',
					'descricao' => 'Os implantes que vão segurar a prótese são instalados com anestesia local.',
					'duracao'   => 'Conforme o plano',
				),
				array(
					'titulo'    => 'Prótese fixa',
					'descricao' => 'A prótese é feita sob medida e fixada sobre os implantes. Se haverá uma provisória antes da definitiva, o plano já mostra.',
					'duracao'   => 'Conforme o plano',
				),
				array(
					'titulo'    => 'Retornos',
					'descricao' => 'Ajuste da mordida e orientação de limpeza para a prótese durar.',
					'duracao'   => 'Após a entrega',
				),
			),
			'faq'             => array(
				array(
					'pergunta' => 'Qual a diferença entre protocolo e dentadura?',
					'resposta' => 'A dentadura se apoia na gengiva e sai da boca. O protocolo fica preso em implantes: não sai, não aperta a gengiva e dá mais firmeza para mastigar.',
				),
				array(
					'pergunta' => 'Quantos implantes o protocolo precisa?',
					'resposta' => 'Depende do osso e da arcada. O número é definido no planejamento, a partir do exame de imagem feito na própria clínica.',
				),
				array(
					'pergunta' => 'Quem usa dentadura há anos pode fazer protocolo?',
					'resposta' => 'Em muitos casos, sim. A avaliação mostra se há osso suficiente ou se é preciso um preparo antes.',
				),
				$preco( 'um protocolo' ),
			),
			'unidades_titulo' => 'Avaliação de protocolo *perto de você*',
			'unidades_texto'  => 'Escolha a unidade e fale com a equipe pelo WhatsApp.',
			'posts'           => array( 'dentadura-ou-protocolo-sergipe' ),
		),
		'ortodontia'                => array(
			'hero_titulo'     => 'Ortodontia para *alinhar* dentes e mordida',
			'hero_lead'       => 'O aparelho certo para o seu caso, indicado depois do exame de imagem, com plano escrito e valor fechado.',
			'selo'            => 'Alinhar os dentes',
			'botao'           => 'Agendar avaliação de ortodontia',
			'resposta_direta' => 'Ortodontia é a especialidade que corrige a posição dos dentes e o encaixe da mordida com aparelhos fixos ou removíveis. Além da estética, dentes alinhados ficam mais fáceis de limpar e a mordida trabalha melhor. O tipo de aparelho e o tempo de uso dependem da avaliação de cada caso.',
			'para_quem'       => array(
				array( 'texto' => 'Tem dentes tortos, encavalados ou separados', 'icone' => 'ortho' ),
				array( 'texto' => 'Sente que a mordida não encaixa', 'icone' => 'adjust' ),
				array( 'texto' => 'Já usou aparelho e os dentes voltaram a mexer', 'icone' => 'clock' ),
				array( 'texto' => 'Quer um sorriso mais harmônico, em qualquer idade', 'icone' => 'aesthetic' ),
			),
			'etapas'          => array(
				$avaliacao,
				$plano,
				array(
					'titulo'    => 'Instalação do aparelho',
					'descricao' => 'O aparelho indicado é instalado e você recebe as orientações de uso e de limpeza.',
					'duracao'   => 'Conforme o plano',
				),
				array(
					'titulo'    => 'Manutenções',
					'descricao' => 'Retornos periódicos para ajustar o aparelho e acompanhar a movimentação dos dentes.',
					'duracao'   => 'Durante o tratamento',
				),
				array(
					'titulo'    => 'Contenção',
					'descricao' => 'Ao tirar o aparelho, a contenção ajuda a manter os dentes no lugar.',
					'duracao'   => 'Ao final',
				),
			),
			'faq'             => array(
				array(
					'pergunta' => 'Adulto pode usar aparelho?',
					'resposta' => 'Pode. Com dentes e gengiva saudáveis, a ortodontia funciona em adultos — a avaliação confirma.',
				),
				array(
					'pergunta' => 'Quanto tempo dura o tratamento?',
					'resposta' => 'Depende do caso e do tipo de aparelho. O tempo estimado vem no plano escrito da primeira avaliação.',
				),
				array(
					'pergunta' => 'Aparelho dói?',
					'resposta' => 'Nos primeiros dias e depois das manutenções é comum sentir os dentes sensíveis. O incômodo costuma passar em poucos dias.',
				),
				$preco( 'o aparelho' ),
			),
			'unidades_titulo' => 'Avaliação de ortodontia *perto de você*',
			'unidades_texto'  => 'Escolha a unidade e fale com a equipe pelo WhatsApp.',
			'posts'           => array( 'aparelho-ortodontico-sergipe' ),
		),
		'estetica-dental'           => array(
			'hero_titulo'     => 'Estética dental para um sorriso *mais bonito*',
			'hero_lead'       => 'Clareamento, lentes de contato dental e resinas planejados depois da avaliação, com plano escrito e valor fechado.',
			'selo'            => 'Sorriso mais bonito',
			'botao'           => 'Agendar avaliação estética',
			'resposta_direta' => 'Estética dental é o conjunto de tratamentos que melhora a cor, a forma e a proporção dos dentes, como clareamento, lentes de contato dental e restaurações em resina. O objetivo é um sorriso harmônico com o rosto, sem abrir mão da saúde dos dentes. O que é indicado depende da avaliação de cada caso.',
			'para_quem'       => array(
				array( 'texto' => 'Acha os dentes amarelados ou manchados', 'icone' => 'spark' ),
				array( 'texto' => 'Tem dentes pequenos, desgastados ou de formato irregular', 'icone' => 'aesthetic' ),
				array( 'texto' => 'Tem restaurações antigas que mudaram de cor', 'icone' => 'adjust' ),
				array( 'texto' => 'Quer sorrir em fotos sem pensar nos dentes', 'icone' => 'check' ),
			),
			'etapas'          => array(
				array(
					'titulo'    => 'Avaliação do sorriso',
					'descricao' => 'A equipe avalia dentes, gengiva e o desenho do sorriso para ver o que cada procedimento pode resolver.',
					'duracao'   => 'Na primeira visita',
				),
				$plano,
				array(
					'titulo'    => 'Tratamento',
					'descricao' => 'Clareamento, lentes ou resinas, na ordem definida no plano.',
					'duracao'   => 'Conforme o plano',
				),
				array(
					'titulo'    => 'Acompanhamento',
					'descricao' => 'Retorno para conferir o resultado e orientar os cuidados que mantêm a cor e a forma.',
					'duracao'   => 'Após o tratamento',
				),
			),
			'faq'             => array(
				array(
					'pergunta' => 'Clareamento estraga os dentes?',
					'resposta' => 'Feito com acompanhamento do dentista, o clareamento não estraga o esmalte. Pode haver sensibilidade passageira durante o tratamento.',
				),
				array(
					'pergunta' => 'Lente de contato dental desgasta o dente?',
					'resposta' => 'Depende do caso: às vezes não há desgaste, às vezes é preciso um preparo mínimo. A avaliação define o que é possível no seu sorriso.',
				),
				array(
					'pergunta' => 'Dá para fazer mais de um procedimento?',
					'resposta' => 'Sim. Quando o sorriso pede mais de um, o plano escrito reúne tudo, na ordem certa e com valor fechado.',
				),
				$preco( 'a estética dental' ),
			),
			'unidades_titulo' => 'Avaliação estética *perto de você*',
			'unidades_texto'  => 'Escolha a unidade e fale com a equipe pelo WhatsApp.',
			'posts'           => array( 'lente-de-contato-dental-aracaju' ),
		),
	);
}

function proodonto_seed_v2_servicos() {
	foreach ( proodonto_seed_v2_servicos_data() as $slug => $d ) {
		$id = proodonto_seed_servico_id( $slug );
		if ( ! $id ) {
			continue;
		}

		proodonto_seed( 'hero_titulo', $d['hero_titulo'], $id );
		proodonto_seed( 'hero_lead', $d['hero_lead'], $id );
		if ( ! empty( $d['selo'] ) ) {
			proodonto_seed( 'srv_selo', $d['selo'], $id );
		}
		if ( ! empty( $d['botao'] ) ) {
			proodonto_seed( 'srv_botao', $d['botao'], $id );
		}
		proodonto_seed( 'resposta_direta', $d['resposta_direta'], $id );
		proodonto_seed( 'para_quem_itens', $d['para_quem'], $id );
		proodonto_seed( 'etapas', $d['etapas'], $id );
		proodonto_seed_append( 'srv_faq', $d['faq'], $id );
		if ( $d['unidades_titulo'] ) {
			proodonto_seed( 'srv_unidades_titulo', $d['unidades_titulo'], $id );
		}
		if ( $d['unidades_texto'] ) {
			proodonto_seed( 'srv_unidades_texto', $d['unidades_texto'], $id );
		}

		$posts = proodonto_seed_post_ids( $d['posts'] );
		if ( $posts ) {
			proodonto_seed( 'posts_relacionados', $posts, $id );
		}
	}
}

function proodonto_seed_v2_home() {
	$home = proodonto_page_by_template( 'page-home.php' );
	$id   = $home ? $home->ID : (int) get_option( 'page_on_front' );
	if ( ! $id ) {
		return;
	}

	proodonto_seed_append(
		'home_faq',
		array(
			array(
				'pergunta' => 'Como agendar a avaliação?',
				'resposta' => 'Escolha a unidade mais perto e toque no botão de WhatsApp: você fala direto com a equipe daquela cidade e combina o melhor horário.',
			),
			array(
				'pergunta' => 'Preciso levar algum exame?',
				'resposta' => 'Não. O exame de imagem é feito na própria clínica, durante a avaliação.',
			),
			proodonto_seed_faq_convenio(),
		),
		$id
	);
}

function proodonto_seed_v2_units_pages() {
	foreach ( proodonto_get_units( true ) as $unit ) {
		$page = $unit['page_id'] ? get_post( $unit['page_id'] ) : get_page_by_path( $unit['slug'] );
		if ( ! $page ) {
			continue;
		}
		$id = $page->ID;

		// "Segunda a sexta das 9h às 19h e sábado das 8h às 12h".
		$hours = array();
		foreach ( proodonto_unit_hours_lines( $unit, false ) as $line ) {
			$hours[] = preg_replace( '/^sábado /u', 'aos sábados ', proodonto_seed_hours_prose( mb_strtolower( $line ) ) );
		}
		$hours_text = implode( ', e ', $hours );
		$street     = $unit['street'];
		$bairro     = $unit['bairro'];

		$texto = sprintf( 'A unidade de %s fica na %s%s.', $unit['name'], $street, $bairro ? ( 'Centro' === $bairro ? ', no Centro' : ', no bairro ' . $bairro ) : '' );
		if ( $hours_text ) {
			$texto .= ' Atende de ' . $hours_text . '.';
		}
		$texto .= ' O exame de imagem é feito aqui mesmo, na avaliação, e você sai com o plano escrito e o valor fechado na mesma visita.';
		if ( 'aracaju' !== $unit['slug'] ) {
			$texto .= ' Sem precisar viajar até Aracaju.';
		}
		proodonto_seed( 'uni_clinica_texto', $texto, $id );

		$faq = array();
		if ( $street ) {
			$faq[] = array(
				'pergunta' => 'Onde fica a unidade de ' . $unit['name'] . '?',
				'resposta' => 'Na ' . $street . ( $bairro ? ', ' . $bairro : '' ) . ', ' . $unit['name'] . ( $unit['uf'] ? ' – ' . $unit['uf'] : '' ) . '. O botão "Rota" abre o caminho no Google Maps.',
			);
		}
		$faq[] = array(
			'pergunta' => 'Como agendar em ' . $unit['name'] . '?',
			'resposta' => 'Pelo botão de WhatsApp desta página: você fala direto com a equipe de ' . $unit['name'] . ' e combina o melhor horário.',
		);
		$faq[] = proodonto_seed_faq_convenio();
		proodonto_seed_append( 'uni_faq', $faq, $id );
	}
}

function proodonto_seed_v2_hub() {
	$hub = proodonto_page_by_template( 'page-servicos.php' );
	if ( ! $hub ) {
		$hub = get_page_by_path( 'servicos' );
	}
	if ( ! $hub ) {
		return;
	}

	proodonto_seed_append(
		'hub_faq',
		array(
			array(
				'pergunta' => 'Quanto custa o tratamento?',
				'resposta' => 'Depende do caso. O valor sai fechado no plano escrito, entregue na primeira avaliação, antes de você decidir.',
			),
			array(
				'pergunta' => 'Dá para fazer mais de um tratamento?',
				'resposta' => 'Sim. Quando o caso pede mais de um, o plano escrito reúne tudo, na ordem certa e com valor fechado.',
			),
			proodonto_seed_faq_convenio(),
		),
		$hub->ID
	);
}

/** CTA lateral e faixa final dos posts (o título da faixa vazio usa o fechamento global). */
function proodonto_seed_v2_posts() {
	$ids = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => array( 'publish', 'future', 'draft', 'pending' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	foreach ( $ids as $id ) {
		proodonto_seed( 'post_cta_titulo', 'Quer saber o que o seu caso precisa?', $id );
		proodonto_seed( 'post_cta_texto', 'Exame de imagem na clínica e plano escrito com valor fechado, na mesma visita.', $id );
		proodonto_seed( 'post_final_texto', 'Escolha a unidade mais perto e fale direto com a equipe pelo WhatsApp.', $id );
	}
}
