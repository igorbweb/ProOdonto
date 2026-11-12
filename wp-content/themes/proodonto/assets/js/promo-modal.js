/**
 * Modal promocional — ver inc/promo-modal.php.
 *
 * Fluxo: confere o cookie (1x por dia) → aguarda o atraso configurado →
 * só então pede a imagem (desktop ou mobile, via <picture>) → abre o
 * <dialog> quando ela termina de carregar. Se a imagem falhar, não abre.
 * ?promo_modal=1 na URL ignora o cookie (para testar).
 */
( function () {
	'use strict';

	var modal = document.getElementById( 'promo-modal' );

	if ( ! modal || typeof modal.showModal !== 'function' ) {
		return;
	}

	var promoId = modal.getAttribute( 'data-promo-id' ) || 'x';
	var delay = parseInt( modal.getAttribute( 'data-promo-delay' ), 10 );
	var cookieName = 'proodonto_promo_' + promoId;
	var forced = /[?&]promo_modal=1(&|$)/.test( window.location.search );

	delay = isNaN( delay ) ? 3 : delay;

	function seenToday() {
		return document.cookie.split( '; ' ).some( function ( pair ) {
			return 0 === pair.indexOf( cookieName + '=' );
		} );
	}

	function markSeen() {
		document.cookie = cookieName + '=1; max-age=86400; path=/; SameSite=Lax';
	}

	function track( event ) {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( { event: event, promo_id: promoId } );
	}

	if ( ! forced && seenToday() ) {
		return;
	}

	var img = modal.querySelector( '.promo-modal__img' );
	var source = modal.querySelector( 'source[data-srcset]' );
	var opened = false;

	function open() {
		if ( opened || modal.open ) {
			return;
		}
		// Não abre por cima de outro modal (ex.: agregador de links).
		if ( document.querySelector( 'dialog[open]' ) ) {
			window.setTimeout( open, 1500 );
			return;
		}
		opened = true;
		modal.showModal();
		markSeen();
		track( 'promo_modal_view' );
	}

	function loadAndOpen() {
		if ( ! img ) {
			return;
		}
		img.addEventListener( 'load', open, { once: true } );
		if ( source ) {
			source.setAttribute( 'srcset', source.getAttribute( 'data-srcset' ) );
		}
		img.setAttribute( 'src', img.getAttribute( 'data-src' ) );
		if ( img.complete && img.naturalWidth ) {
			open();
		}
	}

	function start() {
		window.setTimeout( loadAndOpen, delay * 1000 );
	}

	if ( 'complete' === document.readyState ) {
		start();
	} else {
		window.addEventListener( 'load', start, { once: true } );
	}

	function close() {
		modal.close();
	}

	// Clique fora da imagem (no backdrop) fecha.
	modal.addEventListener( 'click', function ( event ) {
		if ( event.target === modal ) {
			close();
		}
	} );

	var closeButton = modal.querySelector( '[data-promo-close]' );
	if ( closeButton ) {
		closeButton.addEventListener( 'click', close );
	}

	var link = modal.querySelector( '[data-promo-link]' );
	if ( link ) {
		link.addEventListener( 'click', function () {
			track( 'promo_modal_click' );
			if ( '_blank' === link.getAttribute( 'target' ) ) {
				close();
			}
		} );
	}
} )();
