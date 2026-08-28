<?php
/**
 * Conteúdo padrão (copy, ícones e nomes de imagem) das seções que hoje
 * viram custom fields ACF nas páginas Home e Página de Vendas.
 *
 * Fonte de verdade usada por inc/content-seed.php para gravar esse
 * conteúdo como VALOR real dos campos (inclusive repeaters e imagens, que
 * não respeitam 'default_value' da ACF — ver o próprio content-seed.php)
 * na primeira vez que o tema roda em cada ambiente. Os campos "simples"
 * (texto/textarea/url) em inc/acf-fields.php também têm essa mesma copy
 * definida em 'default_value' (a ACF já resolve esses sozinha quando
 * vazios) — os dois arquivos são mantidos com o mesmo conteúdo de
 * propósito, não por acoplamento entre eles.
 *
 * Trocar a copy aqui não muda nada em produção depois que a semeadura já
 * rodou uma vez (nesse ponto o conteúdo real já mora no banco, editável
 * pela ACF) — isto é só o "estado inicial de fábrica".
 */

defined( 'ABSPATH' ) || exit;

function proodonto_home_content_defaults() {
	return array(
		'marquee' => array(
			array(
				'label' => 'Atendimento humanizado',
				'icon'  => '<path d="M12 20.25c-.3 0-.6-.1-.8-.3C6.5 16.4 3 13.3 3 9.5 3 6.9 5.1 5 7.5 5c1.5 0 2.9.7 3.8 1.9.1.1.2.1.3 0C12.6 5.7 14 5 15.5 5 17.9 5 20 6.9 20 9.5c0 3.8-3.5 6.9-8.2 10.45-.2.2-.5.3-.8.3Z"/>',
			),
			array(
				'label' => 'Parcelamos em até 12x',
				'icon'  => '<rect x="2.5" y="5.5" width="19" height="13" rx="2"/><line x1="2.5" y1="10" x2="21.5" y2="10"/>',
			),
			array(
				'label' => 'Especialistas em cada área',
				'icon'  => '<path d="M8.5 6.5V5a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v1.5"/><rect x="2.5" y="6.5" width="19" height="13" rx="2"/><path d="M12 10v6M9 13h6"/>',
			),
			array(
				'label' => 'Horários flexíveis',
				'icon'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
			),
			array(
				'label' => 'Atendimento sem dor',
				'icon'  => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/><path d="M9 12l2 2 4-4"/>',
			),
			array(
				'label' => '5.0 ★ no Google',
				'icon'  => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.3-4.1 5.9-.9L12 3.5Z"/>',
			),
			array(
				'label' => '4 unidades perto de você',
				'icon'  => '<path d="M12 21s7-6.6 7-11.5A7 7 0 0 0 5 9.5C5 14.4 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.3"/>',
			),
		),

		'about' => array(
			'eyebrow'      => 'Sobre a PRÓ-ODONTO',
			'titulo'       => "Cuidado de verdade,\ndo primeiro sorriso ao último",
			'texto'        => 'Há mais de 20 anos ajudamos famílias a recuperar a saúde e a confiança do sorriso. Cada tratamento começa por ouvir você — e segue com transparência, tecnologia e o carinho que você merece.',
			'estatisticas' => array(
				array( 'valor' => '+22 mil', 'legenda' => 'sorrisos transformados' ),
				array( 'valor' => '+7', 'legenda' => 'anos de mercado' ),
				array( 'valor' => '+13', 'legenda' => 'profissionais dedicados' ),
				array( 'valor' => '5.0★', 'legenda' => 'avaliação no Google' ),
			),
		),

		'results' => array(
			'eyebrow'   => 'Resultados reais',
			'titulo'    => 'Sorrisos transformados',
			'texto'     => 'Veja alguns dos resultados que conquistamos com nossos pacientes.',
			'cta_label' => 'Quero um sorriso assim',
			'itens'     => array(
				array( 'nome' => 'Manoel', 'arquivo' => 'assets/images/resultado-manoel.jpg' ),
				array( 'nome' => 'Cesar', 'arquivo' => 'assets/images/resultado-cesar.jpg' ),
				array( 'nome' => 'Dona Valdinete', 'arquivo' => 'assets/images/resultado-valdinete.jpg' ),
				array( 'nome' => 'Eduardo', 'arquivo' => 'assets/images/resultado-eduardo.jpg' ),
				array( 'nome' => 'Joa Ramailho', 'arquivo' => 'assets/images/resultado-joa-ramailho.jpg' ),
				array( 'nome' => 'Josefa Marlene', 'arquivo' => 'assets/images/resultado-josefa-marlene.jpg' ),
			),
		),

		'treatments' => array(
			'eyebrow' => 'Tratamentos',
			'titulo'  => 'Como podemos ajudar você',
			'texto'   => 'Todas as especialidades em um só lugar, com profissionais dedicados a cada tipo de cuidado.',
			'itens'   => array(
				array(
					'titulo' => 'Implantes',
					'texto'  => 'Dentes fixos e definitivos que devolvem a mastigação e a autoestima.',
					'icon'   => '<path d="M12 3c-1.8 0-2.8.9-3.5.9S7.1 3 5.8 3C4 3 3 4.6 3 6.8c0 2.5.7 4.3 1.3 6C5 14.8 5.3 17 5.8 19c.3 1.3 1 2 1.8 2 1.1 0 1.2-2.4 1.6-4.1.3-1.3.6-2.4 1.3-2.4s1 1.1 1.3 2.4c.4 1.7.5 4.1 1.6 4.1.8 0 1.5-.7 1.8-2 .5-2 .8-4.2 1.5-6.2.6-1.7 1.3-3.5 1.3-6C20 4.6 19 3 17.2 3c-1.3 0-1.9.9-2.7.9S13.8 3 12 3Z"/>',
				),
				array(
					'titulo' => 'Próteses',
					'texto'  => 'Próteses fixas e removíveis com encaixe natural e visual harmônico.',
					'icon'   => '<circle cx="12" cy="12" r="9"/><path d="M8.5 10.5h.01M15.5 10.5h.01"/><path d="M8 14.5c1 1.3 2.4 2 4 2s3-.7 4-2"/>',
				),
				array(
					'titulo' => 'Ortodontia',
					'texto'  => 'Aparelhos e alinhadores para deixar seus dentes no lugar certo.',
					'icon'   => '<path d="M12 3v18"/><rect x="4" y="9" width="5" height="6" rx="1"/><rect x="15" y="9" width="5" height="6" rx="1"/>',
				),
				array(
					'titulo' => 'Estética',
					'texto'  => 'Clareamento e facetas para um sorriso mais bonito e natural.',
					'icon'   => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="M19 15l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7.7-2Z"/>',
				),
			),
		),

		// Sem 'itens' de propósito: não há vídeos reais da PRÓ-ODONTO no
		// YouTube ainda — ver aviso no grupo ACF "Home — Shorts (YouTube)"
		// e comentário em inc/content-seed.php. Só o cabeçalho é semeado.
		'shorts' => array(
			'eyebrow' => 'Vídeos',
			'titulo'  => 'Acompanhe nos nossos Shorts',
			'texto'   => 'Bastidores, dicas rápidas e resultados reais — direto do nosso canal no YouTube.',
		),

		'steps' => array(
			'eyebrow' => 'Passo a passo',
			'titulo'  => 'Como é o seu tratamento',
			'itens'   => array(
				array(
					'label' => 'Agendamento',
					'texto' => 'Marque o melhor horário pelo WhatsApp ou telefone, sem burocracia.',
					'icon'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
				),
				array(
					'label' => 'Exames',
					'texto' => 'Radiografias e imagens completas para enxergar tudo antes de decidir.',
					'icon'  => '<path d="M4 8V5a1 1 0 0 1 1-1h3M20 8V5a1 1 0 0 0-1-1h-3M4 16v3a1 1 0 0 0 1 1h3M20 16v3a1 1 0 0 1-1 1h-3"/><path d="M9 12h6"/>',
				),
				array(
					'label' => 'Avaliação',
					'texto' => 'Exame completo e conversa sobre suas expectativas — gratuito.',
					'icon'  => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.35-4.35"/>',
				),
				array(
					'label' => 'Plano de tratamento',
					'texto' => 'Você recebe o plano com etapas, prazos e valores transparentes.',
					'icon'  => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 3.5h6a1 1 0 0 1 1 1V6H8V4.5a1 1 0 0 1 1-1Z"/><path d="M8.5 11h7M8.5 14.5h7M8.5 18h4"/>',
				),
				array(
					'label' => 'Procedimento',
					'texto' => 'Cuidado com conforto e segurança em cada sessão do tratamento.',
					'icon'  => '<rect x="3" y="7" width="18" height="12" rx="2"/><path d="M12 11v6M9 14h6"/><path d="M9 7V5.5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2V7"/>',
				),
				array(
					'label'   => 'Sorriso transformado',
					'texto'   => 'Acompanhamento e manutenção para o seu sorriso durar muito.',
					'icon'    => '<circle cx="12" cy="12" r="9"/><path d="M8.5 10.5h.01M15.5 10.5h.01"/><path d="M8 14.5c1 1.3 2.4 2 4 2s3-.7 4-2"/>',
					'sucesso' => true,
				),
			),
		),

		'reviews' => array(
			'eyebrow' => 'Avaliações',
			'titulo'  => 'O que dizem sobre nós',
			'texto'   => 'Depoimentos reais de quem já passou pela PRÓ-ODONTO.',
		),

		'units' => array(
			'eyebrow' => 'Unidades',
			'titulo'  => 'Uma PRÓ-ODONTO perto de você',
			'texto'   => 'Escolha a unidade mais próxima e fale direto com a nossa equipe.',
		),

		// Sem 'itens' de propósito: os posts agora são os últimos posts
		// REAIS do blog (buscados em page-home.php via get_posts()), não
		// mais uma vitrine de cards inventados. Só o cabeçalho é semeado.
		'blog' => array(
			'eyebrow'    => 'Blog',
			'titulo'     => 'Dicas para o seu sorriso',
			'link_label' => 'Ver todos os artigos →',
		),

		'closing_cta' => array(
			'titulo'       => 'Vamos cuidar do seu sorriso?',
			'texto'        => 'Fale agora com a nossa equipe pelo WhatsApp e agende sua avaliação — é rápido, gratuito e sem compromisso.',
			'botao_label'  => 'Chamar no WhatsApp',
		),
	);
}

function proodonto_vendas_content_defaults() {
	return array(
		'marquee' => array(
			array(
				'label' => 'Atendimento humanizado',
				'icon'  => '<path d="M12 20.25c-.3 0-.6-.1-.8-.3C6.5 16.4 3 13.3 3 9.5 3 6.9 5.1 5 7.5 5c1.5 0 2.9.7 3.8 1.9.1.1.2.1.3 0C12.6 5.7 14 5 15.5 5 17.9 5 20 6.9 20 9.5c0 3.8-3.5 6.9-8.2 10.45-.2.2-.5.3-.8.3Z"/>',
			),
			array(
				'label' => 'Pagamento parcelado',
				'icon'  => '<rect x="2.5" y="5.5" width="19" height="13" rx="2"/><line x1="2.5" y1="10" x2="21.5" y2="10"/>',
			),
			array(
				'label' => 'Especialistas em cada área',
				'icon'  => '<path d="M8.5 6.5V5a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v1.5"/><rect x="2.5" y="6.5" width="19" height="13" rx="2"/><path d="M12 10v6M9 13h6"/>',
			),
			array(
				'label' => 'Horários flexíveis',
				'icon'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
			),
			array(
				'label' => 'Atendimento sem dor',
				'icon'  => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/><path d="M9 12l2 2 4-4"/>',
			),
			array(
				'label' => '5.0 ★ no Google',
				'icon'  => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.3-4.1 5.9-.9L12 3.5Z"/>',
			),
			array(
				'label' => '3 unidades perto de você',
				'icon'  => '<path d="M12 21s7-6.6 7-11.5A7 7 0 0 0 5 9.5C5 14.4 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.3"/>',
			),
		),

		'about' => array(
			'eyebrow'      => 'Sobre a ProOdonto',
			'titulo'       => "Devolvemos muito mais que dentes.\nDevolvemos qualidade de vida.",
			'texto'        => 'Na ProOdonto, cada tratamento começa ouvindo a sua história — e só termina quando você volta a sorrir, comer e viver com confiança. Somos referência em odontologia em Sergipe porque cuidamos de pessoas, não apenas de procedimentos.',
			'cta_label'    => 'AGENDAR AVALIAÇÃO GRATUITA',
			'estatisticas' => array(
				array( 'valor' => '+22 mil', 'legenda' => 'sorrisos transformados' ),
				array( 'valor' => '+7', 'legenda' => 'anos de mercado' ),
				array( 'valor' => '+13', 'legenda' => 'profissionais dedicados' ),
				array( 'valor' => '5.0★', 'legenda' => 'avaliação no Google' ),
			),
		),

		'results' => array(
			'eyebrow'   => 'Resultados reais',
			'titulo'    => 'Sorrisos transformados',
			'texto'     => 'Pacientes reais da ProOdonto que recuperaram a saúde bucal e a confiança para sorrir. O próximo resultado pode ser o seu.',
			'cta_label' => 'QUERO MEU SORRISO',
			'itens'     => array(
				array( 'nome' => 'Manoel', 'arquivo' => 'assets/images/resultado-manoel.jpg' ),
				array( 'nome' => 'Cesar', 'arquivo' => 'assets/images/resultado-cesar.jpg' ),
				array( 'nome' => 'Dona Valdinete', 'arquivo' => 'assets/images/resultado-valdinete.jpg' ),
				array( 'nome' => 'Eduardo', 'arquivo' => 'assets/images/resultado-eduardo.jpg' ),
				array( 'nome' => 'Joa Ramailho', 'arquivo' => 'assets/images/resultado-joa-ramailho.jpg' ),
				array( 'nome' => 'Josefa Marlene', 'arquivo' => 'assets/images/resultado-josefa-marlene.jpg' ),
			),
		),

		'treatments' => array(
			'eyebrow'   => 'Tratamentos',
			'titulo'    => 'Encontre o tratamento certo para o seu sorriso',
			'texto'     => 'Reunimos todas as especialidades em um só lugar, com profissionais dedicados a cuidar de cada etapa — do diagnóstico ao resultado final.',
			'cta_label' => 'QUERO MEU TRATAMENTO',
			'itens'     => array(
				array(
					'titulo' => 'Implantes',
					'texto'  => 'Dentes fixos e definitivos, sem o desconforto de próteses móveis, para voltar a mastigar, falar e sorrir com naturalidade.',
					'icon'   => '<path d="M12 3c-1.8 0-2.8.9-3.5.9S7.1 3 5.8 3C4 3 3 4.6 3 6.8c0 2.5.7 4.3 1.3 6C5 14.8 5.3 17 5.8 19c.3 1.3 1 2 1.8 2 1.1 0 1.2-2.4 1.6-4.1.3-1.3.6-2.4 1.3-2.4s1 1.1 1.3 2.4c.4 1.7.5 4.1 1.6 4.1.8 0 1.5-.7 1.8-2 .5-2 .8-4.2 1.5-6.2.6-1.7 1.3-3.5 1.3-6C20 4.6 19 3 17.2 3c-1.3 0-1.9.9-2.7.9S13.8 3 12 3Z"/>',
				),
				array(
					'titulo' => 'Próteses',
					'texto'  => 'Próteses fixas e removíveis com encaixe perfeito e aparência natural, feitas sob medida para o seu sorriso.',
					'icon'   => '<circle cx="12" cy="12" r="9"/><path d="M8.5 10.5h.01M15.5 10.5h.01"/><path d="M8 14.5c1 1.3 2.4 2 4 2s3-.7 4-2"/>',
				),
				array(
					'titulo' => 'Ortodontia',
					'texto'  => 'Aparelhos e alinhadores para corrigir o alinhamento dos dentes com conforto, em qualquer fase da vida.',
					'icon'   => '<path d="M12 3v18"/><rect x="4" y="9" width="5" height="6" rx="1"/><rect x="15" y="9" width="5" height="6" rx="1"/>',
				),
				array(
					'titulo' => 'Estética',
					'texto'  => 'Clareamento, facetas e harmonização para um sorriso mais bonito, natural e cheio de autoestima.',
					'icon'   => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="M19 15l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7.7-2Z"/>',
				),
			),
		),

		// Sem 'itens' de propósito: não há vídeos reais da ProOdonto no
		// YouTube ainda — ver aviso no grupo ACF "Página de Vendas —
		// Shorts (YouTube)" e comentário em inc/content-seed.php. Só o
		// cabeçalho é semeado.
		'shorts' => array(
			'eyebrow'   => 'Vídeos',
			'titulo'    => 'Veja de perto o nosso atendimento',
			'texto'     => 'Bastidores, depoimentos e dicas rápidas — direto do nosso canal no YouTube.',
			'cta_label' => 'QUERO AGENDAR AGORA',
		),

		'steps' => array(
			'eyebrow'   => 'Passo a passo',
			'titulo'    => 'O caminho para você voltar a sorrir',
			'cta_label' => 'QUERO COMEÇAR AGORA',
			'itens'     => array(
				array(
					'label' => 'Agendamento',
					'texto' => 'Marque o melhor horário pelo WhatsApp ou telefone — sem burocracia e sem enrolação.',
					'icon'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
				),
				array(
					'label' => 'Exames',
					'texto' => 'Radiografias e imagens completas para entender exatamente o que o seu sorriso precisa.',
					'icon'  => '<path d="M4 8V5a1 1 0 0 1 1-1h3M20 8V5a1 1 0 0 0-1-1h-3M4 16v3a1 1 0 0 0 1 1h3M20 16v3a1 1 0 0 1-1 1h-3"/><path d="M9 12h6"/>',
				),
				array(
					'label' => 'Avaliação',
					'texto' => 'Exame completo e uma conversa franca sobre suas expectativas — sem custo e sem compromisso.',
					'icon'  => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.35-4.35"/>',
				),
				array(
					'label' => 'Plano de tratamento',
					'texto' => 'Você recebe um plano claro, com etapas, prazos e valores transparentes — sem letras pequenas.',
					'icon'  => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 3.5h6a1 1 0 0 1 1 1V6H8V4.5a1 1 0 0 1 1-1Z"/><path d="M8.5 11h7M8.5 14.5h7M8.5 18h4"/>',
				),
				array(
					'label' => 'Procedimento',
					'texto' => 'Cada sessão é feita com conforto, segurança e tecnologia, pensada para o seu bem-estar.',
					'icon'  => '<rect x="3" y="7" width="18" height="12" rx="2"/><path d="M12 11v6M9 14h6"/><path d="M9 7V5.5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2V7"/>',
				),
				array(
					'label'   => 'Sorriso transformado',
					'texto'   => 'Acompanhamento contínuo para garantir que o seu novo sorriso dure por muitos anos.',
					'icon'    => '<circle cx="12" cy="12" r="9"/><path d="M8.5 10.5h.01M15.5 10.5h.01"/><path d="M8 14.5c1 1.3 2.4 2 4 2s3-.7 4-2"/>',
					'sucesso' => true,
				),
			),
		),

		'reviews' => array(
			'eyebrow'   => 'Avaliações',
			'titulo'    => 'Quem já passou pela ProOdonto recomenda',
			'texto'     => 'Depoimentos reais de pacientes que recuperaram a saúde e a autoestima do sorriso com a gente.',
			'cta_label' => 'QUERO ESSE RESULTADO',
		),

		'units' => array(
			'eyebrow'   => 'Unidades',
			'titulo'    => 'Uma ProOdonto perto de você',
			'texto'     => 'Escolha a unidade mais próxima e fale direto com a nossa equipe para agendar sua avaliação gratuita.',
			'cta_label' => 'FALAR COM UNIDADE',
		),

		'closing_cta' => array(
			'titulo'      => 'Pronto para voltar a sorrir com confiança?',
			'texto'       => 'Fale agora com a nossa equipe pelo WhatsApp e agende sua avaliação gratuita — é rápido, sem burocracia e sem compromisso.',
			'botao_label' => 'CHAMAR NO WHATSAPP',
		),
	);
}

/**
 * Conteúdo padrão da página "Sobre / Quem Somos" (page-sobre.php).
 *
 * Diferente das seções "Resultados" (Home/Vendas), que usam fotos reais de
 * pacientes já existentes nos assets do tema, esta página NÃO inclui nomes,
 * fotos ou números de CRO reais de profissionais — inventar isso seria
 * apresentar uma credencial regulatória (CRO) falsa vinculada a uma pessoa
 * fictícia, associada a uma clínica real. A seção "Corpo clínico" abaixo
 * usa por isso placeholders claramente identificados como exemplo, com
 * instruções (campo tipo "message") pedindo a substituição pelos dados
 * reais antes da publicação. Números, cidades das unidades e estatísticas
 * usados aqui já existem em outras páginas do site (Home) ou em
 * inc/units-map.php — mantidos consistentes entre páginas, não inventados
 * de novo aqui.
 */
function proodonto_sobre_content_defaults() {
	return array(
		'hero' => array(
			'eyebrow'   => 'Quem somos',
			'titulo'    => 'Cuidado odontológico com propósito, há anos ao lado de Sergipe',
			'texto'     => 'Somos a PRÓ-ODONTO: uma equipe de cirurgiões-dentistas e especialistas dedicados a devolver saúde bucal, autoestima e qualidade de vida para cada paciente que passa pelas nossas unidades.',
			'cta_label' => 'Agendar avaliação gratuita',
		),

		'historia' => array(
			'eyebrow' => 'Nossa história',
			'titulo'  => 'Uma trajetória construída sorriso a sorriso',
			'texto'   => 'Começamos com o compromisso de oferecer uma odontologia acessível, humana e tecnicamente atualizada — e crescemos junto com a confiança de cada paciente e cada indicação.',
			'itens'   => array(
				array(
					'ano'    => 'Origem',
					'titulo' => 'Os primeiros atendimentos',
					'texto'  => 'Abrimos as portas com uma missão simples: tratar cada paciente com atenção e transparência, dos exames à conclusão do tratamento.',
				),
				array(
					'ano'    => 'Expansão',
					'titulo' => 'Chegamos a Lagarto',
					'texto'  => 'A confiança dos pacientes de Aracaju nos levou a abrir uma nova unidade em Lagarto, ampliando o acesso a tratamentos completos.',
				),
				array(
					'ano'    => 'Expansão',
					'titulo' => 'Chegamos a Simão Dias',
					'texto'  => 'Seguimos crescendo com a mesma essência: perto das pessoas, com equipe própria e estrutura completa em cada unidade.',
				),
				array(
					'ano'    => 'Hoje',
					'titulo' => '+22 mil sorrisos transformados',
					'texto'  => 'Hoje somos referência em odontologia em Sergipe, com unidades em Aracaju, Lagarto e Simão Dias e uma equipe multidisciplinar pronta para cuidar de você.',
				),
			),
		),

		'valores' => array(
			'eyebrow'      => 'Missão, visão e valores',
			'titulo'       => 'O que nos guia todos os dias',
			'missao_titulo' => 'Missão',
			'missao_texto'  => 'Oferecer odontologia de qualidade, acessível e humanizada, devolvendo saúde bucal e autoestima para cada paciente.',
			'visao_titulo'  => 'Visão',
			'visao_texto'   => 'Ser a referência em cuidado odontológico em Sergipe, reconhecida pela excelência clínica e pelo acolhimento em cada atendimento.',
			'itens'         => array(
				array(
					'titulo' => 'Ética e transparência',
					'texto'  => 'Falamos a verdade sobre diagnóstico, prazos e valores — sem letras pequenas.',
					'icon'   => '<path d="M8 12.5l2.5 2.5L16 9"/><circle cx="12" cy="12" r="9"/>',
				),
				array(
					'titulo' => 'Cuidado humanizado',
					'texto'  => 'Cada tratamento começa por ouvir você e entender suas necessidades reais.',
					'icon'   => '<path d="M12 20.25c-.3 0-.6-.1-.8-.3C6.5 16.4 3 13.3 3 9.5 3 6.9 5.1 5 7.5 5c1.5 0 2.9.7 3.8 1.9.1.1.2.1.3 0C12.6 5.7 14 5 15.5 5 17.9 5 20 6.9 20 9.5c0 3.8-3.5 6.9-8.2 10.45-.2.2-.5.3-.8.3Z"/>',
				),
				array(
					'titulo' => 'Atualização constante',
					'texto'  => 'Investimos em formação contínua e tecnologia para oferecer o melhor tratamento.',
					'icon'   => '<path d="M12 4 3 8l9 4 9-4-9-4Z"/><path d="M7 10.5V16c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5v-5.5"/>',
				),
				array(
					'titulo' => 'Acesso para todos',
					'texto'  => 'Parcelamos em até 12x para que o tratamento ideal esteja ao alcance de mais pessoas.',
					'icon'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="2"/><line x1="2.5" y1="10" x2="21.5" y2="10"/>',
				),
			),
		),

		'numeros' => array(
			'eyebrow' => 'PRÓ-ODONTO em números',
			'titulo'  => 'Resultados que contam nossa história',
			'itens'   => array(
				array( 'valor' => '+22 mil', 'legenda' => 'sorrisos transformados' ),
				array( 'valor' => '+7', 'legenda' => 'anos de mercado' ),
				array( 'valor' => '+13', 'legenda' => 'profissionais dedicados' ),
				array( 'valor' => '3', 'legenda' => 'unidades em Sergipe' ),
				array( 'valor' => '5.0★', 'legenda' => 'avaliação no Google' ),
			),
		),

		'equipe' => array(
			'eyebrow' => 'Corpo clínico',
			'titulo'  => 'Profissionais que cuidam do seu sorriso',
			'texto'   => 'Nossa equipe reúne cirurgiões-dentistas especialistas em diferentes áreas, todos registrados no Conselho Regional de Odontologia (CRO).',
			'aviso'   => 'Substitua os profissionais de exemplo abaixo pelos dados reais da sua equipe (nome, especialidade, número de CRO e foto) antes de publicar a página — a credencial (CRO) exibida precisa corresponder ao profissional real.',
			'itens'   => array(
				array(
					'nome'  => 'Nome do(a) profissional',
					'cargo' => 'Cirurgiã(o)-Dentista — Implantodontia',
					'cro'   => '',
					'bio'   => 'Especialista em implantes e reabilitação oral, com foco em devolver função e estética ao sorriso.',
				),
				array(
					'nome'  => 'Nome do(a) profissional',
					'cargo' => 'Cirurgiã(o)-Dentista — Ortodontia',
					'cro'   => '',
					'bio'   => 'Cuida do alinhamento dos dentes com aparelhos e alinhadores para todas as idades.',
				),
				array(
					'nome'  => 'Nome do(a) profissional',
					'cargo' => 'Cirurgiã(o)-Dentista — Odontologia Estética',
					'cro'   => '',
					'bio'   => 'Especialista em clareamento, facetas e harmonização do sorriso.',
				),
			),
		),

		'seguranca' => array(
			'eyebrow' => 'Segurança em primeiro lugar',
			'titulo'  => 'Protocolos que protegem você',
			'texto'   => 'Seguimos boas práticas de biossegurança e investimos em equipamentos atualizados em todas as unidades.',
			'itens'   => array(
				array(
					'titulo' => 'Esterilização rigorosa',
					'texto'  => 'Materiais e instrumentais seguem protocolo completo de esterilização antes de cada atendimento.',
					'icon'   => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/><path d="M9 12l2 2 4-4"/>',
				),
				array(
					'titulo' => 'Materiais descartáveis',
					'texto'  => 'Itens de uso único são descartados após cada paciente, sem exceção.',
					'icon'   => '<path d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12.5A2 2 0 0 0 10 21h4a2 2 0 0 0 2-1.5L17 7"/>',
				),
				array(
					'titulo' => 'Equipamentos atualizados',
					'texto'  => 'Investimos em tecnologia de diagnóstico e tratamento para mais precisão e conforto.',
					'icon'   => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
				),
				array(
					'titulo' => 'Equipe capacitada',
					'texto'  => 'Nossos profissionais participam de atualização constante nas suas especialidades.',
					'icon'   => '<path d="M8.5 6.5V5a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v1.5"/><rect x="2.5" y="6.5" width="19" height="13" rx="2"/><path d="M12 10v6M9 13h6"/>',
				),
			),
		),

		'units' => array(
			'eyebrow' => 'Onde estamos',
			'titulo'  => 'Três unidades para ficar perto de você',
			'texto'   => 'Aracaju, Lagarto e Simão Dias — escolha a unidade mais próxima e venha conhecer a nossa equipe.',
		),

		'faq' => array(
			'eyebrow' => 'Perguntas frequentes',
			'titulo'  => 'Ainda tem dúvidas sobre a PRÓ-ODONTO?',
			'itens'   => array(
				array(
					'pergunta' => 'A avaliação inicial tem custo?',
					'resposta' => 'Não. A primeira avaliação é gratuita e serve para entendermos o seu caso e montarmos um plano de tratamento transparente.',
				),
				array(
					'pergunta' => 'Vocês atendem crianças e idosos?',
					'resposta' => 'Sim. Nossa equipe multidisciplinar atende pacientes de todas as idades, com atenção às necessidades específicas de cada fase da vida.',
				),
				array(
					'pergunta' => 'É possível parcelar o tratamento?',
					'resposta' => 'Sim, parcelamos em até 12x, para que o tratamento ideal fique ao seu alcance.',
				),
				array(
					'pergunta' => 'Preciso de indicação para agendar uma avaliação?',
					'resposta' => 'Não. Você pode agendar direto pelo WhatsApp ou telefone, sem burocracia.',
				),
				array(
					'pergunta' => 'A PRÓ-ODONTO atende convênios odontológicos?',
					'resposta' => 'Consulte nossa equipe pelo WhatsApp para confirmar os convênios aceitos na sua unidade.',
				),
				array(
					'pergunta' => 'Quais são as unidades da PRÓ-ODONTO?',
					'resposta' => 'Temos unidades em Aracaju, Lagarto e Simão Dias — todas com estrutura completa e equipe própria.',
				),
			),
		),

		'cta' => array(
			'titulo'      => 'Vamos cuidar do seu sorriso também?',
			'texto'       => 'Fale agora com a nossa equipe pelo WhatsApp e agende sua avaliação gratuita — é rápido, sem burocracia e sem compromisso.',
			'botao_label' => 'Chamar no WhatsApp',
		),
	);
}

/**
 * Itens padrão do Agregador de Links de Contato (Opções do Tema) — um
 * link por unidade, a partir da MESMA lista real usada em
 * inc/units-map.php (não duplicada/inventada aqui). Usado só uma vez,
 * por inc/content-seed.php, pra semear o repeater "agregador_itens";
 * dali em diante o repeater é editado independentemente pelo wp-admin.
 */
function proodonto_link_aggregator_defaults() {
	$units = function_exists( 'proodonto_get_units' ) ? proodonto_get_units() : array();

	return array_map(
		function ( $unit ) {
			return array(
				'label'     => $unit['name'],
				'descricao' => $unit['address'],
				'url'       => $unit['whatsapp_url'],
			);
		},
		$units
	);
}

/**
 * Ícones de redes sociais padrão do rodapé (Opções do Tema). Usado só uma
 * vez, por inc/content-seed.php, pra semear o repeater "footer_redes_sociais";
 * dali em diante o repeater é editado independentemente pelo wp-admin.
 */
function proodonto_footer_social_defaults() {
	return array(
		array(
			'label'                => 'Instagram',
			'icone_svg'            => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor" stroke="none"/>',
			'url'                  => '#',
			'usar_whatsapp_padrao' => 0,
		),
		array(
			'label'                => 'Facebook',
			'icone_svg'            => '<circle cx="12" cy="12" r="9"/><path d="M13.5 21v-6.5h2l.3-2.5h-2.3v-1.6c0-.7.2-1.2 1.2-1.2h1.3V6.9c-.2 0-1-.1-1.9-.1-1.9 0-3.1 1.1-3.1 3.2v1.9H9v2.5h1.9V21"/>',
			'url'                  => '#',
			'usar_whatsapp_padrao' => 0,
		),
		array(
			'label'                => 'WhatsApp',
			'icone_svg'            => '<path fill="currentColor" stroke="none" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .105 5.36.102 11.943c0 2.105.549 4.16 1.595 5.976L0 24l6.335-1.652a11.882 11.882 0 0 0 5.71 1.447h.006c6.585 0 11.941-5.36 11.944-11.943a11.87 11.87 0 0 0-3.475-8.403"/>',
			'url'                  => '',
			'usar_whatsapp_padrao' => 1,
		),
	);
}

/**
 * Colunas de links "livres" padrão do rodapé (Opções do Tema) — hoje só a
 * coluna "Tratamentos" (as colunas "Unidades" e "Contato" são automáticas,
 * montadas direto em footer.php). Usado só uma vez, por
 * inc/content-seed.php, pra semear o repeater "footer_links_colunas".
 */
function proodonto_footer_link_columns_defaults() {
	return array(
		array(
			'heading' => 'Tratamentos',
			'links'   => array(
				array(
					'label' => 'Implantes',
					'url'   => '#',
				),
				array(
					'label' => 'Próteses',
					'url'   => '#',
				),
				array(
					'label' => 'Ortodontia',
					'url'   => '#',
				),
				array(
					'label' => 'Estética',
					'url'   => '#',
				),
			),
		),
	);
}
