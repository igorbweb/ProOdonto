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

		// Fechar o menu mobile também recolhe qualquer acordeão ("Unidades")
		// que tenha ficado aberto — reabrir o menu depois sempre começa do
		// mesmo estado inicial (tudo fechado).
		if ( ! open ) {
			setMobileAccordionOpen( null );
		}
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

	// Acordeão "Unidades" dentro do menu mobile — mesma técnica de colapso
	// suave do painel #mobile-menu (grid-template-rows 0fr → 1fr), só que
	// aninhada. Toque alterna aberto/fechado; só um fica aberto por vez.
	var accordions = menu.querySelectorAll( '.mobile-nav-accordion' );

	function setMobileAccordionOpen( accordionToOpen ) {
		for ( var a = 0; a < accordions.length; a++ ) {
			var accordion = accordions[ a ];
			var open = accordion === accordionToOpen;
			var accordionTrigger = accordion.querySelector( '.mobile-nav-accordion__trigger' );
			var accordionPanel = accordion.querySelector( '.mobile-nav-accordion__panel' );

			if ( accordionPanel ) {
				accordionPanel.classList.toggle( 'grid-rows-[1fr]', open );
				accordionPanel.classList.toggle( 'grid-rows-[0fr]', ! open );
			}

			if ( accordionTrigger ) {
				accordionTrigger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			}
		}
	}

	for ( var m = 0; m < accordions.length; m++ ) {
		( function ( accordion ) {
			var accordionTrigger = accordion.querySelector( '.mobile-nav-accordion__trigger' );

			if ( ! accordionTrigger ) {
				return;
			}

			accordionTrigger.addEventListener( 'click', function () {
				var isCurrentlyOpen = 'true' === accordionTrigger.getAttribute( 'aria-expanded' );
				setMobileAccordionOpen( isCurrentlyOpen ? null : accordion );
			} );
		} )( accordions[ m ] );
	}
} )();

/**
 * Dropdown "Unidades" no menu desktop — abre no hover via CSS puro
 * (:hover/:focus-within, ver .nav-dropdown em assets/css/main.css) e
 * também no clique/toque (classe .is-open), pra cobrir touch e teclado
 * além do mouse. Clicar fora ou apertar Esc fecha.
 */
( function () {
	'use strict';

	var dropdowns = document.querySelectorAll( '.nav-dropdown' );

	if ( ! dropdowns.length ) {
		return;
	}

	function closeAllDropdowns() {
		for ( var d = 0; d < dropdowns.length; d++ ) {
			dropdowns[ d ].classList.remove( 'is-open' );

			var trigger = dropdowns[ d ].querySelector( '.nav-dropdown__trigger' );
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			}
		}
	}

	for ( var i = 0; i < dropdowns.length; i++ ) {
		( function ( dropdown ) {
			var trigger = dropdown.querySelector( '.nav-dropdown__trigger' );

			if ( ! trigger ) {
				return;
			}

			trigger.addEventListener( 'click', function ( event ) {
				var wasOpen = dropdown.classList.contains( 'is-open' );
				event.stopPropagation();
				closeAllDropdowns();
				if ( ! wasOpen ) {
					dropdown.classList.add( 'is-open' );
					trigger.setAttribute( 'aria-expanded', 'true' );
				}
			} );
		} )( dropdowns[ i ] );
	}

	document.addEventListener( 'click', closeAllDropdowns );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key ) {
			closeAllDropdowns();
		}
	} );
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
