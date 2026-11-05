/**
 * Dia Pró — landing page. Vanilla, sem dependências, carregado com defer.
 *
 * 1. Contagem regressiva (antes → "Começa em", durante → "termina em",
 *    depois → aviso de encerrado). Os números iniciais já vêm do servidor.
 * 2. Barra fixa do celular: aparece quando o botão do topo sai da tela e
 *    some enquanto o fechamento (que tem o mesmo botão) está visível.
 * 3. Clique em botão → evento "dia_pro_cta" no dataLayer (GTM), com a
 *    posição do botão. O repasse de UTM para o UpView é do main.js do tema.
 */
( function () {
	'use strict';

	var doc = document;
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* 1. Contagem ------------------------------------------------------- */

	var cd = doc.querySelector( '[data-dp-countdown]' );

	if ( cd ) {
		var start = Date.parse( cd.getAttribute( 'data-start' ) );
		var end = Date.parse( cd.getAttribute( 'data-end' ) );
		var label = cd.querySelector( '[data-dp-cd-label]' );
		var nums = {
			d: cd.querySelector( '[data-dp-cd="d"]' ),
			h: cd.querySelector( '[data-dp-cd="h"]' ),
			m: cd.querySelector( '[data-dp-cd="m"]' ),
			s: cd.querySelector( '[data-dp-cd="s"]' )
		};
		var phase = '';
		var timer = null;

		var pad = function ( n ) {
			return n < 10 ? '0' + n : String( n );
		};

		var setPhase = function ( next ) {
			if ( next === phase ) {
				return;
			}
			phase = next;
			cd.classList.remove( 'dp-countdown--antes', 'dp-countdown--durante', 'dp-countdown--depois' );
			cd.classList.add( 'dp-countdown--' + next );
			if ( label ) {
				label.textContent = 'antes' === next ? 'Começa em' : ( 'durante' === next ? 'É hoje · termina em' : 'O Dia Pró deste ano terminou' );
			}
		};

		var tick = function () {
			var now = Date.now();
			var target;

			if ( now < start ) {
				setPhase( 'antes' );
				target = start;
			} else if ( now < end ) {
				setPhase( 'durante' );
				target = end;
			} else {
				setPhase( 'depois' );
				if ( timer ) {
					window.clearInterval( timer );
				}
				return;
			}

			var diff = Math.max( 0, Math.floor( ( target - now ) / 1000 ) );
			var values = {
				d: Math.floor( diff / 86400 ),
				h: Math.floor( ( diff % 86400 ) / 3600 ),
				m: Math.floor( ( diff % 3600 ) / 60 ),
				s: diff % 60
			};

			Object.keys( values ).forEach( function ( key ) {
				if ( nums[ key ] ) {
					var text = pad( values[ key ] );
					if ( nums[ key ].textContent !== text ) {
						nums[ key ].textContent = text;
					}
				}
			} );
		};

		if ( ! isNaN( start ) && ! isNaN( end ) ) {
			tick();
			// Com "reduzir movimento", atualiza por minuto em vez de por segundo.
			timer = window.setInterval( tick, reduceMotion ? 60000 : 1000 );

			// Aba em segundo plano: o navegador segura o setInterval; ao voltar, corrige na hora.
			doc.addEventListener( 'visibilitychange', function () {
				if ( ! doc.hidden ) {
					tick();
				}
			} );
		}
	}

	/* 2. Barra fixa do celular --------------------------------------------- */

	var bar = doc.querySelector( '[data-dp-bar]' );
	var heroCta = doc.querySelector( '[data-dp-hero-cta]' );
	var finalSection = doc.querySelector( '[data-dp-final]' );

	if ( bar ) {
		bar.setAttribute( 'data-dp-ready', '' );
		var barLink = bar.querySelector( 'a' );
		var heroVisible = true;
		var finalVisible = false;

		var update = function () {
			var show = ! heroVisible && ! finalVisible;
			bar.classList.toggle( 'is-visible', show );
			bar.setAttribute( 'aria-hidden', show ? 'false' : 'true' );
			if ( barLink ) {
				barLink.setAttribute( 'tabindex', show ? '0' : '-1' );
			}
		};

		if ( 'IntersectionObserver' in window && heroCta ) {
			new IntersectionObserver( function ( entries ) {
				heroVisible = entries[ 0 ].isIntersecting;
				update();
			} ).observe( heroCta );

			if ( finalSection ) {
				new IntersectionObserver(
					function ( entries ) {
						finalVisible = entries[ 0 ].isIntersecting;
						update();
					},
					{ rootMargin: '0px 0px -30% 0px' }
				).observe( finalSection );
			}
		} else {
			heroVisible = false;
			update();
		}
	}

	/* 3. Eventos de clique ---------------------------------------------------- */

	function pushEvent( name, data ) {
		window.dataLayer = window.dataLayer || [];
		var payload = { event: name };
		Object.keys( data || {} ).forEach( function ( key ) {
			payload[ key ] = data[ key ];
		} );
		window.dataLayer.push( payload );
	}

	doc.addEventListener( 'click', function ( event ) {
		var link = event.target && event.target.closest ? event.target.closest( '[data-dp-cta]' ) : null;
		if ( link ) {
			pushEvent( 'dia_pro_cta', {
				dp_position: link.getAttribute( 'data-dp-cta' ),
				dp_label: ( link.textContent || '' ).trim()
			} );
		}
	} );
} )();
