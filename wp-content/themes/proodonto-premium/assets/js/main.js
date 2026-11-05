/**
 * PróOdonto Premium — interações.
 *
 * Sem jQuery, sem bibliotecas. Tudo passivo/adiado para não pesar no INP
 * (meta < 200 ms): nenhum trabalho no carregamento além de observers.
 *
 *  1. Menu do celular (<dialog>).
 *  2. Barra fixa: aparece quando o seletor/topo sai da tela, some no rodapé.
 *  3. Post: barra de progresso + item ativo do sumário + copiar link.
 *  4. Visualizador de resultados (<dialog>).
 *  5. CTAs: repassa utm_*, fbclid e gclid ao UpView, utm_source=blog nos
 *     posts, e registra o clique (dataLayer do GTM + Pixel, se houver).
 *  6. Pixel da Meta carregado só quando a página fica ociosa.
 */
( function () {
	'use strict';

	var CFG = window.PROODONTO || {};
	var doc = document;
	var html = doc.documentElement;

	function $( sel, ctx ) { return ( ctx || doc ).querySelector( sel ); }
	function $$( sel, ctx ) { return Array.prototype.slice.call( ( ctx || doc ).querySelectorAll( sel ) ); }
	function visible( el ) { return !! ( el && ( el.offsetWidth || el.offsetHeight || el.getClientRects().length ) ); }

	/* 1. Menu ---------------------------------------------------------- */
	var drawer = $( '#po-menu' );
	var openBtn = $( '[data-menu-open]' );

	if ( drawer && openBtn && typeof drawer.showModal === 'function' ) {
		var closeDrawer = function () {
			if ( drawer.open ) { drawer.close(); }
		};
		openBtn.addEventListener( 'click', function () {
			drawer.showModal();
			openBtn.setAttribute( 'aria-expanded', 'true' );
		} );
		drawer.addEventListener( 'close', function () {
			openBtn.setAttribute( 'aria-expanded', 'false' );
			openBtn.focus();
		} );
		$$( '[data-menu-close]', drawer ).forEach( function ( b ) { b.addEventListener( 'click', closeDrawer ); } );
		// Clique fora do conteúdo (no backdrop) fecha.
		drawer.addEventListener( 'click', function ( e ) {
			if ( e.target === drawer ) { closeDrawer(); }
		} );
		// Âncora da mesma página: fecha e deixa rolar.
		$$( 'a[href*="#"]', drawer ).forEach( function ( a ) {
			a.addEventListener( 'click', function () {
				if ( a.pathname === location.pathname ) { closeDrawer(); }
			} );
		} );
	} else if ( openBtn ) {
		// Navegador sem <dialog>: o botão leva ao rodapé (links e unidades).
		openBtn.addEventListener( 'click', function () { location.hash = 'rodape'; } );
	}

	/* 2. Barra fixa ---------------------------------------------------- */
	var bar = $( '[data-sticky-bar]' );
	if ( bar && 'IntersectionObserver' in window ) {
		var trigger = $$( '[data-sticky-trigger]' ).filter( visible )[ 0 ];
		var footer = $( '#rodape' );
		var state = { past: false, footer: false };

		var render = function () {
			var show = state.past && ! state.footer;
			if ( show === ! bar.hidden ) { return; }
			bar.hidden = ! show;
		};

		if ( trigger ) {
			new IntersectionObserver( function ( entries ) {
				var e = entries[ 0 ];
				// Passou do gatilho = ele saiu por cima da tela.
				state.past = ! e.isIntersecting && e.boundingClientRect.top < 0;
				render();
			} ).observe( trigger );
		} else {
			var onScroll = function () {
				state.past = window.scrollY > 600;
				render();
			};
			window.addEventListener( 'scroll', onScroll, { passive: true } );
		}

		if ( footer ) {
			new IntersectionObserver( function ( entries ) {
				state.footer = entries[ 0 ].isIntersecting;
				render();
			} ).observe( footer );
		}
	}

	/* 3. Post: progresso, sumário, copiar link ------------------------- */
	var progress = $( '[data-progress]' );
	var prose = $( '[data-prose]' );
	if ( progress && prose ) {
		var ticking = false;
		var update = function () {
			var r = prose.getBoundingClientRect();
			var total = r.height - window.innerHeight * 0.6;
			var p = total > 0 ? Math.min( 1, Math.max( 0, -r.top / total ) ) : 1;
			progress.style.transform = 'scaleX(' + p.toFixed( 4 ) + ')';
			ticking = false;
		};
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) { ticking = true; window.requestAnimationFrame( update ); }
		}, { passive: true } );
		update();
	}

	var tocLinks = $$( '[data-toc]' );
	if ( tocLinks.length && 'IntersectionObserver' in window ) {
		var targets = [];
		tocLinks.forEach( function ( a ) {
			var t = doc.getElementById( a.getAttribute( 'data-toc' ) );
			if ( t && targets.indexOf( t ) === -1 ) { targets.push( t ); }
		} );
		var setActive = function ( id ) {
			tocLinks.forEach( function ( a ) {
				var on = a.getAttribute( 'data-toc' ) === id;
				a.classList.toggle( 'is-active', on );
				if ( on ) { a.setAttribute( 'aria-current', 'location' ); } else { a.removeAttribute( 'aria-current' ); }
			} );
		};
		var spy = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) { setActive( e.target.id ); }
			} );
		}, { rootMargin: '0px 0px -70% 0px' } );
		targets.forEach( function ( t ) { spy.observe( t ); } );
		var tocM = $( '.toc-m' );
		if ( tocM ) {
			$$( 'a', tocM ).forEach( function ( a ) {
				a.addEventListener( 'click', function () { tocM.open = false; } );
			} );
		}
	}

	$$( '[data-copy-link]' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			var msg = b.parentNode.querySelector( '.share__msg' );
			var done = function () {
				if ( msg ) {
					msg.textContent = ( CFG.i18n && CFG.i18n.copied ) || 'Link copiado';
					setTimeout( function () { msg.textContent = ''; }, 2500 );
				}
			};
			if ( navigator.clipboard ) {
				navigator.clipboard.writeText( location.href.split( '#' )[ 0 ] ).then( done, function () {} );
			}
		} );
	} );

	/* 4. Visualizador de resultados ------------------------------------ */
	var box = $( '#po-lightbox' );
	if ( box && typeof box.showModal === 'function' ) {
		var boxImg = $( '.lightbox__img', box );
		var boxCap = $( '.lightbox__cap', box );
		doc.addEventListener( 'click', function ( e ) {
			var a = e.target.closest && e.target.closest( '[data-lightbox]' );
			if ( ! a ) { return; }
			e.preventDefault();
			var img = a.querySelector( 'img' );
			boxImg.src = a.getAttribute( 'href' );
			boxImg.alt = img ? img.alt : '';
			boxCap.textContent = a.getAttribute( 'data-caption' ) || '';
			box.showModal();
		} );
		box.addEventListener( 'click', function ( e ) {
			if ( e.target === box || ( e.target.closest && e.target.closest( '[data-lightbox-close]' ) ) ) { box.close(); }
		} );
	}

	/* 5. CTAs: parâmetros de campanha + registro do clique ------------- */
	var PASS = /^(utm_[a-z]+|fbclid|gclid|gbraid|wbraid)$/i;
	var current = new URLSearchParams( location.search );

	function withParams( href ) {
		var url;
		try { url = new URL( href, location.href ); } catch ( err ) { return href; }
		if ( ! /upviewcrm\.com$/i.test( url.hostname ) ) { return href; }
		if ( CFG.passParams ) {
			current.forEach( function ( value, key ) {
				if ( PASS.test( key ) && ! url.searchParams.has( key ) ) { url.searchParams.set( key, value ); }
			} );
		}
		if ( CFG.utmSource && ! url.searchParams.has( 'utm_source' ) ) {
			url.searchParams.set( 'utm_source', CFG.utmSource );
		}
		return url.toString();
	}

	function track( a ) {
		var data = {
			event: 'cta_click',
			cta_place: a.getAttribute( 'data-cta' ) || '',
			cta_unit: a.getAttribute( 'data-cta-unit' ) || '',
			ab_variant: a.getAttribute( 'data-ab' ) || html.getAttribute( 'data-ab' ) || '',
			link_url: a.href
		};
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( data );
		if ( typeof window.fbq === 'function' && /upviewcrm\.com/i.test( a.href ) ) {
			window.fbq( 'track', 'Contact', { content_name: data.cta_place, content_category: data.cta_unit } );
		}
	}

	// pointerdown troca o href antes da navegação (inclusive clique do meio);
	// click registra. Nada de target=_blank: o UpView abre na mesma aba.
	doc.addEventListener( 'pointerdown', function ( e ) {
		var a = e.target.closest && e.target.closest( 'a[href]' );
		if ( a && /upviewcrm\.com/i.test( a.href ) ) { a.href = withParams( a.href ); }
	}, { passive: true } );

	doc.addEventListener( 'click', function ( e ) {
		var a = e.target.closest && e.target.closest( 'a[href]' );
		if ( ! a ) { return; }
		if ( /upviewcrm\.com/i.test( a.href ) ) {
			a.href = withParams( a.href );
			track( a );
		} else if ( a.hasAttribute( 'data-cta' ) ) {
			track( a );
		} else if ( a.hasAttribute( 'data-rota' ) ) {
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push( { event: 'rota_click', cta_unit: a.getAttribute( 'data-rota' ) } );
		}
	} );

	/* 6. Pixel da Meta, adiado ----------------------------------------- */
	if ( CFG.pixel && typeof window.fbq !== 'function' ) {
		var loadPixel = function () {
			/* eslint-disable */
			!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,doc,'script','https://connect.facebook.net/en_US/fbevents.js');
			/* eslint-enable */
			window.fbq( 'init', CFG.pixel );
			window.fbq( 'track', 'PageView' );
		};
		var idle = function () {
			if ( 'requestIdleCallback' in window ) { window.requestIdleCallback( loadPixel, { timeout: 4000 } ); } else { setTimeout( loadPixel, 2500 ); }
		};
		if ( doc.readyState === 'complete' ) { idle(); } else { window.addEventListener( 'load', idle ); }
	}
}() );
