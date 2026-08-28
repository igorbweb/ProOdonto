/**
 * Registro dos blocos no Editor. Vanilla JS, sem build step (sem JSX/webpack) —
 * usa wp.element.createElement diretamente, igual ao Editor faz internamente.
 *
 * Todos os blocos são dinâmicos: save() sempre retorna null (ou apenas
 * InnerBlocks.Content nos blocos-contêiner) porque a marcação real é gerada
 * pelo render_callback em PHP (ver render.php de cada bloco). O que é editado
 * aqui são só os atributos.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var RichText = blockEditor.RichText;
	var InspectorControls = blockEditor.InspectorControls;
	var MediaUpload = blockEditor.MediaUpload;
	var InnerBlocks = blockEditor.InnerBlocks;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var Button = components.Button;
	var __ = i18n.__;

	function buttonInspector( attributes, setAttributes ) {
		return el(
			PanelBody,
			{ title: __( 'Botão', 'proodonto' ) },
			el( TextControl, {
				label: __( 'Texto do botão', 'proodonto' ),
				value: attributes.buttonLabel,
				onChange: function ( value ) {
					setAttributes( { buttonLabel: value } );
				},
			} ),
			el( TextControl, {
				label: __( 'Link do botão', 'proodonto' ),
				type: 'url',
				value: attributes.buttonUrl,
				onChange: function ( value ) {
					setAttributes( { buttonUrl: value } );
				},
			} ),
			el( ToggleControl, {
				label: __( 'Abrir em nova aba', 'proodonto' ),
				checked: !! attributes.buttonBlank,
				onChange: function ( value ) {
					setAttributes( { buttonBlank: value } );
				},
			} )
		);
	}

	function mediaInspector( args ) {
		// args: { title, buttonText, attributes, setAttributes, idKey, urlKey, altKey }
		return el(
			PanelBody,
			{ title: args.title },
			el( MediaUpload, {
				allowedTypes: [ 'image' ],
				onSelect: function ( media ) {
					var next = {};
					next[ args.idKey ] = media.id;
					next[ args.urlKey ] = media.url;
					next[ args.altKey ] = media.alt || '';
					args.setAttributes( next );
				},
				render: function ( obj ) {
					return el(
						Button,
						{ onClick: obj.open, variant: 'secondary' },
						args.attributes[ args.urlKey ] ? args.buttonTextChange : args.buttonText
					);
				},
			} )
		);
	}

	/* ---------------------------------------------------------------
	 * CTA
	 * ------------------------------------------------------------ */
	blocks.registerBlockType( 'proodonto/cta', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				Fragment,
				{},
				el( InspectorControls, {}, buttonInspector( a, setAttributes ) ),
				el(
					'section',
					{ className: 'cta' },
					el(
						'div',
						{ className: 'container cta__inner' },
						el( RichText, {
							tagName: 'h2',
							className: 'cta__title',
							placeholder: __( 'Título do CTA…', 'proodonto' ),
							value: a.title,
							onChange: function ( value ) {
								setAttributes( { title: value } );
							},
						} ),
						el( RichText, {
							tagName: 'p',
							className: 'cta__text',
							placeholder: __( 'Texto de apoio…', 'proodonto' ),
							value: a.text,
							onChange: function ( value ) {
								setAttributes( { text: value } );
							},
						} ),
						el(
							'span',
							{ className: 'button' },
							a.buttonLabel || __( 'Texto do botão', 'proodonto' )
						)
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	/* ---------------------------------------------------------------
	 * Hero
	 * ------------------------------------------------------------ */
	blocks.registerBlockType( 'proodonto/hero', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					mediaInspector( {
						title: __( 'Imagem', 'proodonto' ),
						buttonText: __( 'Selecionar imagem', 'proodonto' ),
						buttonTextChange: __( 'Trocar imagem', 'proodonto' ),
						attributes: a,
						setAttributes: setAttributes,
						idKey: 'imageId',
						urlKey: 'imageUrl',
						altKey: 'imageAlt',
					} ),
					buttonInspector( a, setAttributes )
				),
				el(
					'section',
					{ className: 'hero' },
					a.imageUrl
						? el( 'img', { className: 'hero__image', src: a.imageUrl, alt: a.imageAlt } )
						: null,
					el(
						'div',
						{ className: 'container hero__content' },
						el( RichText, {
							tagName: 'h1',
							className: 'hero__title',
							placeholder: __( 'Título…', 'proodonto' ),
							value: a.title,
							onChange: function ( value ) {
								setAttributes( { title: value } );
							},
						} ),
						el( RichText, {
							tagName: 'p',
							className: 'hero__subtitle',
							placeholder: __( 'Subtítulo…', 'proodonto' ),
							value: a.subtitle,
							onChange: function ( value ) {
								setAttributes( { subtitle: value } );
							},
						} ),
						el(
							'span',
							{ className: 'button' },
							a.buttonLabel || __( 'Texto do botão', 'proodonto' )
						)
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	/* ---------------------------------------------------------------
	 * Depoimentos (contêiner + filho)
	 * ------------------------------------------------------------ */
	var TESTIMONIALS_TEMPLATE = [
		[ 'proodonto/testimonial-item', {} ],
		[ 'proodonto/testimonial-item', {} ],
		[ 'proodonto/testimonial-item', {} ],
	];

	blocks.registerBlockType( 'proodonto/testimonials', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'section',
				{ className: 'testimonials' },
				el(
					'div',
					{ className: 'container' },
					el( RichText, {
						tagName: 'h2',
						className: 'testimonials__heading',
						placeholder: __( 'Título da seção…', 'proodonto' ),
						value: a.heading,
						onChange: function ( value ) {
							setAttributes( { heading: value } );
						},
					} ),
					el(
						'div',
						{ className: 'testimonials__grid' },
						el( InnerBlocks, {
							allowedBlocks: [ 'proodonto/testimonial-item' ],
							template: TESTIMONIALS_TEMPLATE,
							templateLock: false,
						} )
					)
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );

	blocks.registerBlockType( 'proodonto/testimonial-item', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'blockquote',
				{ className: 'testimonial' },
				el(
					InspectorControls,
					{},
					mediaInspector( {
						title: __( 'Foto', 'proodonto' ),
						buttonText: __( 'Selecionar foto', 'proodonto' ),
						buttonTextChange: __( 'Trocar foto', 'proodonto' ),
						attributes: a,
						setAttributes: setAttributes,
						idKey: 'avatarId',
						urlKey: 'avatarUrl',
						altKey: 'avatarAlt',
					} )
				),
				a.avatarUrl
					? el( 'img', {
							className: 'testimonial__avatar',
							src: a.avatarUrl,
							alt: a.avatarAlt,
							width: 64,
							height: 64,
					  } )
					: null,
				el( RichText, {
					tagName: 'p',
					className: 'testimonial__quote',
					placeholder: __( 'Depoimento…', 'proodonto' ),
					value: a.quote,
					onChange: function ( value ) {
						setAttributes( { quote: value } );
					},
				} ),
				el( RichText, {
					tagName: 'cite',
					className: 'testimonial__name',
					placeholder: __( 'Nome', 'proodonto' ),
					value: a.name,
					onChange: function ( value ) {
						setAttributes( { name: value } );
					},
				} ),
				el( RichText, {
					tagName: 'span',
					className: 'testimonial__role',
					placeholder: __( 'Cargo / empresa', 'proodonto' ),
					value: a.role,
					onChange: function ( value ) {
						setAttributes( { role: value } );
					},
				} )
			);
		},
		save: function () {
			return null;
		},
	} );

	/* ---------------------------------------------------------------
	 * FAQ (contêiner + filho)
	 * ------------------------------------------------------------ */
	var FAQ_TEMPLATE = [
		[ 'proodonto/faq-item', {} ],
		[ 'proodonto/faq-item', {} ],
	];

	blocks.registerBlockType( 'proodonto/faq', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'section',
				{ className: 'faq' },
				el(
					'div',
					{ className: 'container' },
					el( RichText, {
						tagName: 'h2',
						className: 'faq__heading',
						placeholder: __( 'Perguntas frequentes…', 'proodonto' ),
						value: a.heading,
						onChange: function ( value ) {
							setAttributes( { heading: value } );
						},
					} ),
					el(
						'div',
						{ className: 'faq__list' },
						el( InnerBlocks, {
							allowedBlocks: [ 'proodonto/faq-item' ],
							template: FAQ_TEMPLATE,
							templateLock: false,
						} )
					)
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );

	blocks.registerBlockType( 'proodonto/faq-item', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'div',
				{ className: 'faq-item' },
				el( RichText, {
					tagName: 'p',
					className: 'faq-item__question',
					placeholder: __( 'Pergunta…', 'proodonto' ),
					value: a.question,
					onChange: function ( value ) {
						setAttributes( { question: value } );
					},
				} ),
				el( RichText, {
					tagName: 'div',
					className: 'faq-item__answer',
					multiline: 'p',
					placeholder: __( 'Resposta…', 'proodonto' ),
					value: a.answer,
					onChange: function ( value ) {
						setAttributes( { answer: value } );
					},
				} )
			);
		},
		save: function () {
			return null;
		},
	} );

	/* ---------------------------------------------------------------
	 * Serviços / Especialidades (contêiner + filho)
	 * ------------------------------------------------------------ */
	var SERVICES_TEMPLATE = [
		[ 'proodonto/service-item', {} ],
		[ 'proodonto/service-item', {} ],
		[ 'proodonto/service-item', {} ],
	];

	blocks.registerBlockType( 'proodonto/services', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'section',
				{ className: 'services' },
				el(
					'div',
					{ className: 'container' },
					el( RichText, {
						tagName: 'h2',
						className: 'services__heading',
						placeholder: __( 'Título da seção…', 'proodonto' ),
						value: a.heading,
						onChange: function ( value ) {
							setAttributes( { heading: value } );
						},
					} ),
					el(
						'div',
						{ className: 'services__grid' },
						el( InnerBlocks, {
							allowedBlocks: [ 'proodonto/service-item' ],
							template: SERVICES_TEMPLATE,
							templateLock: false,
						} )
					)
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );

	blocks.registerBlockType( 'proodonto/service-item', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'article',
				{ className: 'service-item' },
				el(
					InspectorControls,
					{},
					mediaInspector( {
						title: __( 'Ícone', 'proodonto' ),
						buttonText: __( 'Selecionar ícone', 'proodonto' ),
						buttonTextChange: __( 'Trocar ícone', 'proodonto' ),
						attributes: a,
						setAttributes: setAttributes,
						idKey: 'iconId',
						urlKey: 'iconUrl',
						altKey: 'iconAlt',
					} ),
					el(
						PanelBody,
						{ title: __( 'Link (opcional)', 'proodonto' ) },
						el( TextControl, {
							label: __( 'Texto do link', 'proodonto' ),
							value: a.linkLabel,
							onChange: function ( value ) {
								setAttributes( { linkLabel: value } );
							},
						} ),
						el( TextControl, {
							label: __( 'URL do link', 'proodonto' ),
							type: 'url',
							value: a.linkUrl,
							onChange: function ( value ) {
								setAttributes( { linkUrl: value } );
							},
						} )
					)
				),
				a.iconUrl
					? el( 'img', { className: 'service-item__icon', src: a.iconUrl, alt: a.iconAlt, width: 64, height: 64 } )
					: null,
				el( RichText, {
					tagName: 'h3',
					className: 'service-item__title',
					placeholder: __( 'Nome do serviço…', 'proodonto' ),
					value: a.title,
					onChange: function ( value ) {
						setAttributes( { title: value } );
					},
				} ),
				el( RichText, {
					tagName: 'p',
					className: 'service-item__description',
					placeholder: __( 'Descrição breve…', 'proodonto' ),
					value: a.description,
					onChange: function ( value ) {
						setAttributes( { description: value } );
					},
				} )
			);
		},
		save: function () {
			return null;
		},
	} );

	/* ---------------------------------------------------------------
	 * Contato / Agendamento
	 * ------------------------------------------------------------ */
	blocks.registerBlockType( 'proodonto/contact', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;

			function field( key, label, type ) {
				return el( TextControl, {
					label: label,
					type: type || 'text',
					value: a[ key ],
					onChange: function ( value ) {
						var next = {};
						next[ key ] = value;
						setAttributes( next );
					},
				} );
			}

			function area( key, label ) {
				return el( TextareaControl, {
					label: label,
					value: a[ key ],
					onChange: function ( value ) {
						var next = {};
						next[ key ] = value;
						setAttributes( next );
					},
				} );
			}

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Informações de contato', 'proodonto' ) },
						field( 'phone', __( 'Telefone', 'proodonto' ), 'tel' ),
						field( 'whatsapp', __( 'WhatsApp (só números, com DDI/DDD)', 'proodonto' ) ),
						field( 'email', __( 'E-mail', 'proodonto' ), 'email' ),
						area( 'address', __( 'Endereço', 'proodonto' ) ),
						area( 'hours', __( 'Horário de funcionamento', 'proodonto' ) ),
						field( 'mapUrl', __( 'URL do mapa incorporado (Google Maps > Compartilhar > Incorporar mapa > copiar o src do iframe)', 'proodonto' ), 'url' )
					),
					el(
						PanelBody,
						{ title: __( 'Formulário', 'proodonto' ) },
						field( 'recipientEmail', __( 'Enviar mensagens para (em branco = e-mail do admin)', 'proodonto' ), 'email' )
					)
				),
				el(
					'section',
					{ className: 'contact' },
					el(
						'div',
						{ className: 'container contact__inner' },
						el(
							'div',
							{ className: 'contact__info' },
							el( RichText, {
								tagName: 'h2',
								className: 'contact__heading',
								placeholder: __( 'Título…', 'proodonto' ),
								value: a.heading,
								onChange: function ( value ) {
									setAttributes( { heading: value } );
								},
							} ),
							el( RichText, {
								tagName: 'p',
								className: 'contact__intro',
								placeholder: __( 'Texto de apoio…', 'proodonto' ),
								value: a.intro,
								onChange: function ( value ) {
									setAttributes( { intro: value } );
								},
							} ),
							el(
								'p',
								{ className: 'contact__editor-note' },
								__( 'Telefone, WhatsApp, e-mail, endereço, horário e mapa: edite no painel lateral →', 'proodonto' )
							)
						),
						el(
							'div',
							{ className: 'contact__form-wrap' },
							el(
								'p',
								{ className: 'contact__editor-note' },
								__( 'Formulário de contato (nome, telefone, e-mail, mensagem) — gerado automaticamente, não editável aqui.', 'proodonto' )
							)
						)
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
