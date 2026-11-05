/**
 * Editor: blocos herdados do tema antigo (CTA e Perguntas Frequentes).
 * Extraído de proodonto/assets/js/blocks-editor.js — mesmos nomes e
 * atributos; o HTML do front vem do render_callback (inc/blocks.php).
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

} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
