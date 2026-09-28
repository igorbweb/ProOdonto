/**
 * JS próprio da Página de Vendas (delta sobre home.js — ver
 * inc/enqueue.php: home.js carrega primeiro pelo template compartilhado,
 * este arquivo carrega depois, só nesta página).
 *
 * Barra fixa de CTA do hero (.hero-vendas-barra): aparece no mobile/tablet
 * quando o CTA principal do hero (.hero-vendas .cta) sai da viewport, some
 * quando ele volta. Só é observada abaixo de 900px — igual ao breakpoint
 * em que o hero vira duas colunas e a barra deixa de fazer sentido (ver
 * assets/css/hero-vendas.css).
 */
( function () {
	'use strict';

	var barra = document.querySelector( '.hero-vendas-barra' );
	var cta   = document.querySelector( '.hero-vendas .cta' );

	if ( ! barra || ! cta ) {
		return;
	}

	var mq       = window.matchMedia( '(max-width: 899px)' );
	var observer = null;

	function criarObserver() {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			barra.classList.add( 'is-visivel' );
			return;
		}

		observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				barra.classList.toggle( 'is-visivel', ! entry.isIntersecting );
			} );
		} );

		observer.observe( cta );
	}

	function destruirObserver() {
		if ( observer ) {
			observer.disconnect();
			observer = null;
		}

		barra.classList.remove( 'is-visivel' );
	}

	function avaliar() {
		if ( mq.matches ) {
			if ( ! observer ) {
				criarObserver();
			}
		} else {
			destruirObserver();
		}
	}

	avaliar();
	window.addEventListener( 'resize', avaliar );
} )();
