/**
 * Páginas de serviço (hub /servicos/ e /servicos/<tratamento>/).
 * Vanilla, sem dependências, carregado com defer — ver inc/servicos.php.
 *
 *  1. Repassa os parâmetros de campanha da URL atual (utm_*, fbclid,
 *     gclid) para os links de WhatsApp [data-svc-cta]. Sem isso o UpView
 *     não gera o código de rastreio e o lead cai em "orgânico" (CONV-06).
 *  2. Dispara um evento por clique de CTA, com a unidade e o serviço,
 *     para o Pixel da Meta (se fbq existir) e para o dataLayer (PERF-04).
 *     Não abre janela nem segura a navegação: o link segue normal —
 *     window.open quebra no navegador do Instagram/Facebook (PERF-03).
 *  3. Esconde a barra fixa enquanto os botões do hero ou o bloco de
 *     unidades estão visíveis, para não duplicar CTA na mesma tela.
 */
( function () {
	'use strict';

	var PASS_THROUGH = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_id', 'fbclid', 'gclid' ];
	var links = document.querySelectorAll( 'a[data-svc-cta]' );

	/* 1. Parâmetros de campanha ------------------------------------- */
	var current;
	try {
		current = new URLSearchParams( window.location.search );
	} catch ( e ) {
		current = null;
	}

	if ( current ) {
		var params = [];
		PASS_THROUGH.forEach( function ( key ) {
			var value = current.get( key );
			if ( value ) {
				params.push( [ key, value ] );
			}
		} );

		if ( params.length ) {
			Array.prototype.forEach.call( links, function ( link ) {
				try {
					var url = new URL( link.href, window.location.href );
					params.forEach( function ( pair ) {
						if ( ! url.searchParams.has( pair[ 0 ] ) ) {
							url.searchParams.set( pair[ 0 ], pair[ 1 ] );
						}
					} );
					link.href = url.toString();
				} catch ( e ) {
					// URL inválida no campo: deixa o link como está.
				}
			} );
		}
	}

	/* 2. Evento de clique -------------------------------------------- */
	Array.prototype.forEach.call( links, function ( link ) {
		link.addEventListener( 'click', function () {
			var data = {
				unidade: link.getAttribute( 'data-unidade' ) || '',
				servico: link.getAttribute( 'data-servico' ) || ''
			};

			try {
				if ( typeof window.fbq === 'function' ) {
					window.fbq( 'trackCustom', 'CliqueWhatsApp', data );
				}
				window.dataLayer = window.dataLayer || [];
				window.dataLayer.push( {
					event: 'clique_whatsapp',
					unidade: data.unidade,
					servico: data.servico
				} );
			} catch ( e ) {
				// Rastreio nunca pode impedir o clique.
			}
		} );
	} );

	/* 3. Barra fixa --------------------------------------------------- */
	var sticky = document.querySelector( '[data-svc-sticky]' );
	if ( ! sticky || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	var watched = [];
	var heroCta = document.querySelector( '[data-svc-hero-cta]' );
	var units = document.getElementById( 'unidades' );
	if ( heroCta ) {
		watched.push( heroCta );
	}
	if ( units ) {
		watched.push( units );
	}
	if ( ! watched.length ) {
		return;
	}

	var visible = new Map();
	var observer = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			visible.set( entry.target, entry.isIntersecting );
		} );

		var anyVisible = false;
		visible.forEach( function ( isVisible ) {
			anyVisible = anyVisible || isVisible;
		} );

		sticky.classList.toggle( 'is-hidden', anyVisible );
	} );

	watched.forEach( function ( el ) {
		observer.observe( el );
	} );
} )();
