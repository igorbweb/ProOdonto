/**
 * JS específico da página Home — mas também reaproveitado pela Página de
 * Vendas (ver inc/enqueue.php), então cada bloco abaixo procura seu
 * próprio elemento e não faz nada se ele não existir naquela página.
 *
 * Inicializa o carrossel da galeria "Sobre", o carrossel de "Antes e
 * Depois", o carrossel de "Shorts" (YouTube) com o modal de vídeo, o
 * carrossel dos últimos posts do "Blog" e, só na Página de Vendas, o
 * carrossel de "Tratamentos" no mobile. O carrossel do hero
 * (.swiper.hero) já existe no HTML mas não é inicializado por este
 * arquivo de propósito, para não mexer no hero sem pedido explícito.
 */
( function () {
	'use strict';

	/*
	 * Modal de vídeo (seção "Shorts") — <dialog> nativo, sem relação com o
	 * Swiper (fica ANTES do guard de Swiper abaixo de propósito, pra
	 * continuar funcionando mesmo se a lib não carregar por algum motivo).
	 * O iframe só é criado no clique — nunca no carregamento da página,
	 * pra não pagar o custo de vários players do YouTube de uma vez — e é
	 * destruído ao fechar (removido do DOM), pra garantir que o vídeo
	 * realmente pare: só ocultar o modal não pausa o player.
	 */
	var videoModal = document.getElementById( 'video-modal' );

	if ( videoModal && typeof videoModal.showModal === 'function' ) {
		var videoModalPlayer = videoModal.querySelector( '[data-video-modal-player]' );

		var closeVideoModal = function () {
			videoModal.close();
		};

		videoModal.addEventListener( 'close', function () {
			videoModalPlayer.innerHTML = '';
		} );

		// Clique no backdrop (fora do card) fecha — o alvo do clique só é
		// o próprio <dialog> quando cai fora de .video-modal__inner.
		videoModal.addEventListener( 'click', function ( event ) {
			if ( event.target === videoModal ) {
				closeVideoModal();
			}
		} );

		var videoModalCloseBtn = videoModal.querySelector( '[data-video-modal-close]' );

		if ( videoModalCloseBtn ) {
			videoModalCloseBtn.addEventListener( 'click', closeVideoModal );
		}

		document.querySelectorAll( '.short-card' ).forEach( function ( card ) {
			card.addEventListener( 'click', function () {
				var videoId = card.getAttribute( 'data-youtube-id' );

				if ( ! videoId ) {
					return;
				}

				var iframe = document.createElement( 'iframe' );
				iframe.src = 'https://www.youtube-nocookie.com/embed/' + videoId + '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
				iframe.title = 'YouTube video player';
				iframe.setAttribute( 'allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' );
				iframe.setAttribute( 'allowfullscreen', '' );
				iframe.setAttribute( 'frameborder', '0' );

				videoModalPlayer.innerHTML = '';
				videoModalPlayer.appendChild( iframe );

				videoModal.showModal();
			} );
		} );
	}

	if ( typeof window.Swiper === 'undefined' ) {
		return;
	}

	var aboutEl = document.querySelector( '.about-swiper' );

	if ( aboutEl ) {
		var aboutSlideCount = aboutEl.querySelectorAll( '.swiper-slide' ).length;

		// eslint-disable-next-line no-new
		new window.Swiper( aboutEl, {
			loop: aboutSlideCount > 1,
			autoplay: aboutSlideCount > 1 ? {
				delay: 4000,
				disableOnInteraction: false,
			} : false,
			pagination: {
				el: aboutEl.querySelector( '.swiper-pagination' ),
				clickable: true,
			},
		} );
	}

	var resultsEl = document.querySelector( '.results-swiper' );

	if ( resultsEl ) {
		// eslint-disable-next-line no-new
		new window.Swiper( resultsEl, {
			slidesPerView: 1.15,
			spaceBetween: 20,
			keyboard: {
				enabled: true,
			},
			pagination: {
				el: resultsEl.querySelector( '.swiper-pagination' ),
				clickable: true,
			},
			navigation: {
				nextEl: '.results-swiper-next',
				prevEl: '.results-swiper-prev',
			},
			breakpoints: {
				640: {
					slidesPerView: 2.1,
					spaceBetween: 20,
				},
				1024: {
					slidesPerView: 3,
					spaceBetween: 24,
				},
			},
		} );
	}

	var shortsEl = document.querySelector( '.shorts-swiper' );

	if ( shortsEl ) {
		// eslint-disable-next-line no-new
		new window.Swiper( shortsEl, {
			slidesPerView: 2.3,
			spaceBetween: 14,
			keyboard: {
				enabled: true,
			},
			pagination: {
				el: shortsEl.querySelector( '.swiper-pagination' ),
				clickable: true,
			},
			navigation: {
				nextEl: '.shorts-swiper-next',
				prevEl: '.shorts-swiper-prev',
			},
			breakpoints: {
				640: {
					slidesPerView: 3.2,
					spaceBetween: 18,
				},
				1024: {
					slidesPerView: 5,
					spaceBetween: 20,
				},
			},
		} );
	}

	var blogEl = document.querySelector( '.blog-swiper' );

	if ( blogEl ) {
		// eslint-disable-next-line no-new
		new window.Swiper( blogEl, {
			slidesPerView: 1.1,
			spaceBetween: 20,
			keyboard: {
				enabled: true,
			},
			pagination: {
				el: blogEl.querySelector( '.swiper-pagination' ),
				clickable: true,
			},
			navigation: {
				nextEl: '.blog-swiper-next',
				prevEl: '.blog-swiper-prev',
			},
			breakpoints: {
				640: {
					slidesPerView: 2.1,
					spaceBetween: 20,
				},
				1024: {
					slidesPerView: 3,
					spaceBetween: 24,
				},
			},
		} );
	}

	/*
	 * Tratamentos (só na Página de Vendas — page-vendas.php) — swiper só
	 * no mobile, pra reduzir a rolagem da página; a partir de 600px
	 * (mesmo breakpoint tablet do resto do tema) o carrossel é destruído
	 * e assets/css/pages/vendas.css restaura a grade normal de cards.
	 * new Swiper()/destroy() em vez de breakpoints com "enabled", pra não
	 * depender de um comportamento do Swiper mais difícil de garantir —
	 * destroy(true, true) limpa os estilos inline que o Swiper aplica,
	 * devolvendo o elemento pro CSS puro controlar o layout em grade.
	 */
	var treatmentsEl = document.querySelector( '.treatments-swiper' );

	if ( treatmentsEl ) {
		var treatmentsSwiper = null;
		var treatmentsMq = window.matchMedia( '(max-width: 599px)' );

		var syncTreatmentsSwiper = function () {
			if ( treatmentsMq.matches && ! treatmentsSwiper ) {
				treatmentsSwiper = new window.Swiper( treatmentsEl, {
					slidesPerView: 1.2,
					loop: true,
					autoplay: {
						delay: 3000,
						disableOnInteraction: false,
					},
				} );
			} else if ( ! treatmentsMq.matches && treatmentsSwiper ) {
				treatmentsSwiper.destroy( true, true );
				treatmentsSwiper = null;
			}
		};

		syncTreatmentsSwiper();
		treatmentsMq.addEventListener( 'change', syncTreatmentsSwiper );
	}
} )();
