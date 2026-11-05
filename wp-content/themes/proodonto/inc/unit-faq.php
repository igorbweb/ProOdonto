<?php
/**
 * Perguntas frequentes da Home (rede) e das páginas de unidade
 * (/aracaju/, /lagarto/, /simao-dias/).
 *
 * - Home (page-home.php): FAQ da rede, com horário e endereço das 3 unidades.
 * - Unidades (page-vendas.php): FAQ da unidade, com o horário e o endereço dela.
 * Os dois saem como FAQPage no mesmo grafo do Yoast (filtro
 * proodonto_json_ld_graphs, ver inc/seo.php — nenhum JSON-LD solto).
 *
 * Origem das respostas:
 *   avaliação, pagamento, crianças/idosos, indicação, convênios — texto já
 *   publicado e aprovado no FAQ de /sobre/ (convênio adaptado à cidade);
 *   horário e endereço — montados de inc/units-map.php (mesma fonte do
 *   cartão, do rodapé e do schema Dentist);
 *   medo de dentista — resume a promessa "atendimento para quem tem medo de
 *   dentista" que o site já faz (validar com a clínica).
 * Estilo em assets/css/pages/home.css (.faq--site), que carrega na Home e
 * nas páginas de unidade.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Perguntas comuns à rede e às unidades.
 *
 * @param string $city Cidade da unidade, ou '' para a rede.
 * @return array<int, array{q:string, a:string}>
 */
function proodonto_get_faq_common_items( $city = '' ) {
	return array(
		array(
			'q' => 'A avaliação inicial tem custo?',
			'a' => 'A primeira consulta é de diagnóstico: exame clínico, exame de imagem e plano de tratamento por escrito, sem cobrança e sem compromisso de iniciar o tratamento.',
		),
		array(
			'q' => 'Como funciona o pagamento? Dá para parcelar?',
			'a' => 'Sim. Trabalhamos com cartão e com boleto, o que permite tratar mesmo sem cartão de crédito, e com parcelamento. Quando a entrada compromete o orçamento, ela pode ser dividida e o tratamento programado por etapas. As condições são apresentadas junto com o plano escrito, com o valor total fechado.',
		),
		$city
			? array(
				'q' => sprintf( 'A unidade de %s atende convênios?', $city ),
				'a' => sprintf( 'As condições de convênio variam por unidade. Confirme com a recepção da unidade de %s pelo WhatsApp antes de agendar.', $city ),
			)
			: array(
				'q' => 'A PróOdonto atende convênios odontológicos?',
				'a' => 'As condições de convênio variam por unidade. Confirme com a recepção da unidade em que pretende ser atendido antes de agendar.',
			),
		array(
			'q' => 'Vocês atendem crianças e idosos?',
			'a' => 'Sim. Nossa equipe multidisciplinar atende pacientes de todas as idades, com atenção às necessidades específicas de cada fase da vida.',
		),
		array(
			'q' => 'Preciso de indicação para agendar uma avaliação?',
			'a' => 'Não. Você pode agendar direto pelo WhatsApp ou telefone, sem burocracia.',
		),
		array(
			'q' => 'Tenho medo de dentista. Vocês atendem?',
			'a' => 'Sim. Tudo começa por uma avaliação sem compromisso, em que a equipe explica cada etapa antes de qualquer procedimento, e o tratamento segue no seu ritmo.',
		),
	);
}

/**
 * Horário de uma unidade em uma frase ("Segunda a sexta, das 08:00 às
 * 13:00 e das 14:00 às 19:00; sábado, das 08:00 às 12:00"), ou ''.
 */
function proodonto_get_unit_hours_sentence( $unit ) {
	$lines = array();

	foreach ( ( isset( $unit['hours'] ) ? (array) $unit['hours'] : array() ) as $hours ) {
		if ( empty( $hours['label'] ) || empty( $hours['opens'] ) || empty( $hours['closes'] ) ) {
			continue;
		}
		$lines[ $hours['label'] ][] = sprintf( '%s às %s', $hours['opens'], $hours['closes'] );
	}

	$parts = array();
	foreach ( $lines as $label => $ranges ) {
		// Só a primeira faixa começa com maiúscula ("Segunda a sexta…; sábado…").
		$label   = $parts && function_exists( 'mb_strtolower' ) ? mb_strtolower( $label, 'UTF-8' ) : $label;
		$parts[] = $label . ', das ' . implode( ' e das ', $ranges );
	}

	return implode( '; ', $parts );
}

/**
 * Perguntas e respostas de uma unidade.
 *
 * @param array $unit Item de proodonto_get_units().
 * @return array<int, array{q:string, a:string}>
 */
function proodonto_get_unit_faq( $unit ) {
	if ( empty( $unit['name'] ) ) {
		return array();
	}

	$city  = $unit['name'];
	$items = proodonto_get_faq_common_items( $city );

	$hours = proodonto_get_unit_hours_sentence( $unit );
	if ( $hours ) {
		$items[] = array(
			'q' => sprintf( 'Qual o horário de atendimento da PróOdonto em %s?', $city ),
			'a' => $hours . '.',
		);
	}

	if ( ! empty( $unit['address'] ) ) {
		$items[] = array(
			'q' => sprintf( 'Onde fica a PróOdonto em %s?', $city ),
			'a' => sprintf( 'Na %s. Use o botão “Ver rota no Google Maps” nesta página para abrir o caminho até a clínica.', $unit['address'] ),
		);
	}

	return $items;
}

/**
 * Perguntas e respostas da rede (Home): as comuns + horário e endereço
 * das três unidades.
 *
 * @return array<int, array{q:string, a:string}>
 */
function proodonto_get_network_faq() {
	$units = function_exists( 'proodonto_get_units' ) ? proodonto_get_units() : array();
	$items = proodonto_get_faq_common_items();

	$hours = array();
	$where = array();

	foreach ( $units as $unit ) {
		$sentence = proodonto_get_unit_hours_sentence( $unit );
		if ( $sentence ) {
			$hours[] = $unit['name'] . ': ' . ( function_exists( 'mb_strtolower' ) ? mb_strtolower( mb_substr( $sentence, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $sentence, 1, null, 'UTF-8' ) : $sentence ) . '.';
		}
		if ( ! empty( $unit['address'] ) ) {
			$where[] = $unit['name'] . ': ' . $unit['address'] . '.';
		}
	}

	if ( $hours ) {
		$items[] = array(
			'q' => 'Qual o horário de atendimento das unidades?',
			'a' => implode( ' ', $hours ),
		);
	}

	if ( $where ) {
		$items[] = array(
			'q' => 'Onde ficam as clínicas da PróOdonto?',
			'a' => 'São três unidades em Sergipe. ' . implode( ' ', $where ),
		);
	}

	return $items;
}

/**
 * Unidade da página atual (item de proodonto_get_units()) ou null.
 */
function proodonto_get_current_unit() {
	$slug = function_exists( 'proodonto_get_current_unit_slug' ) ? proodonto_get_current_unit_slug() : '';
	return ( $slug && function_exists( 'proodonto_get_unit_by_slug' ) ) ? proodonto_get_unit_by_slug( $slug ) : null;
}

/**
 * Imprime a seção de perguntas frequentes (mesmo markup na Home e nas
 * unidades; visual em home.css, .faq--site).
 *
 * @param string $title Título da seção.
 * @param array  $items Saída de proodonto_get_unit_faq() / proodonto_get_network_faq().
 */
function proodonto_render_faq_section( $title, $items ) {
	if ( ! $items ) {
		return;
	}
	?>
	<section class="faq faq--site" id="perguntas-frequentes">
		<div class="faq__inner">
			<div class="faq__header">
				<p class="faq__eyebrow">Perguntas frequentes</p>
				<h2 class="faq__title"><?php echo esc_html( $title ); ?></h2>
			</div>
			<div class="faq__list">
				<?php foreach ( $items as $index => $item ) : ?>
					<details class="faq-item" <?php echo 0 === $index ? 'open' : ''; ?>>
						<summary class="faq-item__question">
							<?php echo esc_html( $item['q'] ); ?>
							<span class="faq-item__toggle" aria-hidden="true"></span>
						</summary>
						<p class="faq-item__answer"><?php echo esc_html( $item['a'] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * FAQPage no grafo do Yoast: FAQ da rede na Home, FAQ da unidade nas
 * páginas de unidade.
 */
add_filter(
	'proodonto_json_ld_graphs',
	static function ( $graphs ) {
		if ( ! is_page() ) {
			return $graphs;
		}

		if ( is_page_template( 'page-home.php' ) ) {
			$faq = proodonto_get_network_faq();
		} else {
			$unit = proodonto_get_current_unit();
			$faq  = $unit ? proodonto_get_unit_faq( $unit ) : array();
		}

		if ( ! $faq ) {
			return $graphs;
		}

		$url       = get_permalink();
		$questions = array();

		foreach ( $faq as $qa ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $qa['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $qa['a'],
				),
			);
		}

		$graphs[] = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#perguntas-frequentes',
			'isPartOf'   => array( '@id' => $url ),
			'mainEntity' => $questions,
		);

		return $graphs;
	}
);
