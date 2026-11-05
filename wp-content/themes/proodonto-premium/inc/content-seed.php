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
 */

defined( 'ABSPATH' ) || exit;

const PROODONTO_SEED_VERSION = 1;

add_action( 'admin_init', 'proodonto_seed_content', 20 );

function proodonto_seed_content() {
	if ( (int) get_option( 'proodonto_premium_seed' ) >= PROODONTO_SEED_VERSION ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'update_field' ) ) {
		return;
	}

	proodonto_seed_options();
	proodonto_seed_servicos();
	proodonto_seed_home();
	proodonto_seed_units_pages();
	proodonto_seed_hub();

	update_option( 'proodonto_premium_seed', PROODONTO_SEED_VERSION, false );
}

/** Grava só se o campo estiver vazio. */
function proodonto_seed( $name, $value, $post_id ) {
	$current = get_field( $name, $post_id, false );
	if ( null === $current || '' === $current || false === $current || array() === $current || 0 === $current ) {
		update_field( $name, $value, $post_id );
	}
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

	proodonto_seed(
		'unidades',
		array(
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
		),
		$o
	);

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
