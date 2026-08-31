<?php
/**
 * Bootstrap do tema Proodonto.
 *
 * Mantém functions.php enxuto: cada responsabilidade vive em inc/.
 */

defined( 'ABSPATH' ) || exit;

define( 'PROODONTO_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'PROODONTO_DIR', get_template_directory() );
define( 'PROODONTO_URI', get_template_directory_uri() );

$proodonto_includes = array(
	'/inc/setup.php',               // Theme supports, menus, sidebars, image sizes.
	'/inc/performance.php',         // Limpeza de head, defer/async, lazy-load, WebP, heartbeat, etc.
	'/inc/enqueue.php',             // CSS/JS globais + CSS específico por página.
	'/inc/seo.php',                 // Meta description, canonical, Open Graph, Twitter Card, JSON-LD.
	'/inc/author-credentials.php',  // Cargo/especialidade + CRO do autor (user meta), usado na bio e no JSON-LD.
	'/inc/template-functions.php',  // Helpers usados nos templates (paginação, thumbnail, breadcrumbs...).
	'/inc/page-generator.php',      // Gera page-{slug}.php + assets/css/pages/{slug}.css ao publicar página.
	'/inc/blocks.php',              // Blocos nativos (page-builder via Gutenberg): CTA, Hero, Depoimentos, FAQ, Serviços, Contato.
	'/inc/contact-form.php',        // Handler de envio do bloco proodonto/contact (nonce + honeypot + wp_mail).
	'/inc/options-page.php',        // Opções do Tema (ACF Pro): telefone/WhatsApp globais usados no header.
	'/inc/units-map.php',           // Lista real das unidades + mapa estático (Google) com cache permanente.
	'/inc/page-content-defaults.php', // Copy/ícones/nomes de imagem padrão das seções ACF da Home, Vendas e Sobre (usado por acf-fields.php e content-seed.php).
	'/inc/acf-fields.php',          // Todos os grupos de campos (ACF Pro) do tema, via acf_add_local_field_group() — sem depender do banco de dados.
	'/inc/content-seed.php',        // Grava o conteúdo padrão como valor real dos campos ACF, uma vez por página (Home/Vendas/Sobre).
	'/inc/page-sobre-schema.php',   // JSON-LD extra (AboutPage, equipe/Person, FAQPage) só na página Sobre / Quem Somos.
	'/inc/local-business-schema.php', // JSON-LD extra (Organization enriquecida + Dentist por unidade) só na Home — telefone, redes sociais, endereço de cada clínica e catálogo de tratamentos.
);

foreach ( $proodonto_includes as $file ) {
	$path = PROODONTO_DIR . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}
