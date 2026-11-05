<?php
/**
 * PróOdonto Premium — bootstrap.
 *
 * Mesmo prefixo de funções (proodonto_) e mesmos nomes de template do tema
 * antigo (page-home.php, page-vendas.php, page-servicos.php,
 * single-servico.php): ao ativar este tema, as páginas que já existem no
 * banco caem no template certo sem precisar reatribuir nada.
 *
 * Os dois temas NÃO podem estar ativos ao mesmo tempo (é o padrão do WP —
 * só um tema ativo), então não há colisão de funções.
 */

defined( 'ABSPATH' ) || exit;

define( 'PROODONTO_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'PROODONTO_DIR', get_template_directory() );
define( 'PROODONTO_URI', get_template_directory_uri() );

$proodonto_includes = array(
	'/inc/setup.php',              // Supports, menus, tamanhos de imagem, estilos de bloco.
	'/inc/icons.php',              // Biblioteca de ícones (SVG inline, traço 1.6–2px, currentColor).
	'/inc/helpers.php',            // Títulos com *destaque*, tokens {cidade}, botões, imagens, unidades.
	'/inc/units.php',              // Fonte única das 3 unidades (Opções do Tema) + horários + links.
	'/inc/acf-options.php',        // Página "PróOdonto" (Opções do Tema) e seus campos.
	'/inc/acf-fields.php',         // Grupos de campos por template (Home, Unidade, Hub, Tratamento, Post).
	'/inc/servicos.php',           // CPT `servico` (/servicos/<tratamento>/), sem arquivo — o hub é uma página.
	'/inc/content-seed.php',       // Grava UMA vez o conteúdo aprovado nas Opções e páginas (idempotente, por versão).
	'/inc/enqueue.php',            // CSS/JS com versão por filemtime, fontes, preload do LCP, Pixel adiado.
	'/inc/performance.php',        // Limpeza de <head>, emojis, embeds, jQuery fora do front.
	'/inc/content-filters.php',    // IDs nos H2 do post (sumário), CTA no meio do texto, estilos alerta/dica.
	'/inc/blocks.php',             // Blocos proodonto/faq, faq-item e cta (compatíveis com o conteúdo antigo).
	'/inc/schema.php',             // JSON-LD dentro do grafo do Yoast (Organization, Dentist, MedicalProcedure, FAQPage...).
	'/inc/author-credentials.php', // Copiado do tema antigo: CRO/cargo do autor + revisor técnico.
	'/inc/indexacao.php',          // Copiado do tema antigo: noindex de arquivos, 301 de barra final e posts mesclados.
);

foreach ( $proodonto_includes as $proodonto_file ) {
	$proodonto_path = PROODONTO_DIR . $proodonto_file;
	if ( file_exists( $proodonto_path ) ) {
		require_once $proodonto_path;
	}
}
