/**
 * JS único do tema. Vanilla, sem dependências (nem jQuery), carregado com
 * defer (ver inc/enqueue.php) — não bloqueia o parsing do HTML.
 */
( function () {
	'use strict';

	// Menu mobile: anima o painel #mobile-menu (grid-template-rows 0fr → 1fr
	// + opacity, técnica que permite transição suave sem precisar medir
	// altura via JS) e o backdrop atrás dele, que esmaece o resto da página.
	var toggle = document.getElementById( 'menu-toggle' );
	var menu = document.getElementById( 'mobile-menu' );
	var backdrop = document.getElementById( 'mobile-menu-backdrop' );

	if ( ! toggle || ! menu ) {
		return;
	}

	var MENU_CLOSED = [ 'grid-rows-[0fr]', 'opacity-0' ];
	var MENU_OPEN = [ 'grid-rows-[1fr]', 'opacity-100' ];
	var BACKDROP_CLOSED = [ 'opacity-0', 'pointer-events-none' ];
	var BACKDROP_OPEN = [ 'opacity-100', 'pointer-events-auto' ];

	var isOpen = false;

	function setMenuOpen( open ) {
		isOpen = open;

		menu.classList.remove.apply( menu.classList, open ? MENU_CLOSED : MENU_OPEN );
		menu.classList.add.apply( menu.classList, open ? MENU_OPEN : MENU_CLOSED );

		if ( backdrop ) {
			backdrop.classList.remove.apply( backdrop.classList, open ? BACKDROP_CLOSED : BACKDROP_OPEN );
			backdrop.classList.add.apply( backdrop.classList, open ? BACKDROP_OPEN : BACKDROP_CLOSED );
		}

		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	}

	toggle.addEventListener( 'click', function () {
		setMenuOpen( ! isOpen );
	} );

	// Fecha ao clicar em qualquer link do menu, ou no backdrop.
	var links = menu.querySelectorAll( 'a' );
	for ( var i = 0; i < links.length; i++ ) {
		links[ i ].addEventListener( 'click', function () {
			setMenuOpen( false );
		} );
	}

	if ( backdrop ) {
		backdrop.addEventListener( 'click', function () {
			setMenuOpen( false );
		} );
	}
} )();

/**
 * Agregador de Links de Contato — <dialog> nativo, renderizado em
 * footer.php SÓ quando o agregador está ativo e a página não é a de
 * Vendas (aquele template já funciona como landing page de uma unidade
 * específica). A própria ausência do elemento no DOM já desliga todo o
 * comportamento abaixo — nenhuma checagem de página é necessária aqui.
 *
 * Três formas de disparar o modal num clique (em vez de navegar direto
 * pro href original, que fica como fallback: sem JS, o link funciona
 * normalmente):
 *   1. Qualquer link com a classe `cta` (`a.cta`) — automático, é a
 *      classe usada em todos os botões de CTA já existentes no tema.
 *   2. Qualquer elemento com o atributo `data-link-aggregator-trigger`
 *      (ver footer.php) — pra quando dá pra editar o HTML/atributos.
 *   3. Qualquer link cuja URL termine em `LINK_AGGREGATOR_HREF` (ver
 *      constante abaixo) — pra colar em qualquer campo de URL do
 *      wp-admin (bloco "Botões" do Gutenberg, itens de menu, campos ACF
 *      do tipo URL...) sem precisar de classe/atributo nenhum. Mesmo
 *      valor documentado no campo "Ativar agregador de links" das
 *      Opções do Tema (ver inc/acf-fields.php).
 */
( function () {
	'use strict';

	// URL "mágica" que qualquer link pode usar como href pra abrir o
	// agregador — não precisa ser uma URL real (o clique nunca chega a
	// navegar pra ela, é interceptado antes). Cole isto no campo de link
	// de qualquer botão/bloco/menu do wp-admin.
	var LINK_AGGREGATOR_HREF = '#agregador-links';

	var modal = document.getElementById( 'link-aggregator-modal' );

	if ( ! modal || typeof modal.showModal !== 'function' ) {
		return;
	}

	function closeModal() {
		modal.close();
	}

	// Clique no backdrop (fora do card) fecha — o alvo do clique só é o
	// próprio <dialog> quando cai fora de .link-aggregator-modal__inner.
	modal.addEventListener( 'click', function ( event ) {
		if ( event.target === modal ) {
			closeModal();
		}
	} );

	var closeButton = modal.querySelector( '[data-link-aggregator-close]' );
	if ( closeButton ) {
		closeButton.addEventListener( 'click', closeModal );
	}

	// Fecha ao escolher um link (ele abre em nova aba via target="_blank",
	// então fechar aqui só limpa a tela pra quando o visitante voltar).
	var links = modal.querySelectorAll( 'a' );
	for ( var i = 0; i < links.length; i++ ) {
		links[ i ].addEventListener( 'click', closeModal );
	}

	var ctas = document.querySelectorAll( 'a.cta, [data-link-aggregator-trigger], a[href$="' + LINK_AGGREGATOR_HREF + '"]' );
	for ( var j = 0; j < ctas.length; j++ ) {
		ctas[ j ].addEventListener( 'click', function ( event ) {
			event.preventDefault();
			modal.showModal();
		} );
	}
} )();
