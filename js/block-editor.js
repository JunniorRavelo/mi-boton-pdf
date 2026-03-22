( function ( blocks, element, components, blockEditor, i18n, serverSideRender ) {
    'use strict';

    var el = element.createElement;
    var Fragment = element.Fragment;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps = blockEditor.useBlockProps;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var RangeControl = components.RangeControl;
    var ServerSideRender =
        serverSideRender && serverSideRender.default ? serverSideRender.default : serverSideRender;

    var __ = i18n.__;

    var buttons = mbpdfBlock && mbpdfBlock.buttons ? mbpdfBlock.buttons : [];

    var options = [
        { label: __( 'Selecciona un botón PDF…', 'mi-boton-pdf' ), value: '0' },
    ].concat(
        buttons.map( function ( b ) {
            return { label: b.label + ' (ID ' + b.id + ')', value: String( b.id ) };
        } )
    );

    registerBlockType( 'mi-boton-pdf/boton', {
        apiVersion: 2,
        title: __( 'Botón PDF', 'mi-boton-pdf' ),
        description: __( 'Muestra un enlace a un PDF creado en Botones PDF.', 'mi-boton-pdf' ),
        icon: 'media-document',
        category: 'widgets',
        attributes: {
            buttonId: { type: 'integer', default: 0 },
            text: { type: 'string', default: '' },
            target: { type: 'string', default: '_blank' },
            download: { type: 'string', default: 'auto' },
            size: { type: 'integer', default: 48 },
            className: { type: 'string', default: '' },
        },
        supports: {
            html: false,
            align: true,
            className: true,
        },
        edit: function ( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var buttonId = attributes.buttonId || 0;
            var blockProps = useBlockProps( {
                className: 'mbpdf-block-editor-preview',
            } );

            return el(
                Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: __( 'Botón PDF', 'mi-boton-pdf' ), initialOpen: true },
                        el( SelectControl, {
                            label: __( 'Botón', 'mi-boton-pdf' ),
                            value: String( buttonId ),
                            options: options,
                            onChange: function ( v ) {
                                setAttributes( { buttonId: parseInt( v, 10 ) || 0 } );
                            },
                        } ),
                        el( TextControl, {
                            label: __( 'Texto (opcional)', 'mi-boton-pdf' ),
                            help: __( 'Si está vacío, se usa el título del botón.', 'mi-boton-pdf' ),
                            value: attributes.text,
                            onChange: function ( v ) {
                                setAttributes( { text: v } );
                            },
                        } ),
                        el( SelectControl, {
                            label: __( 'Abrir enlace en', 'mi-boton-pdf' ),
                            value: attributes.target || '_blank',
                            options: [
                                { label: __( 'Nueva pestaña', 'mi-boton-pdf' ), value: '_blank' },
                                { label: __( 'Misma pestaña', 'mi-boton-pdf' ), value: '_self' },
                            ],
                            onChange: function ( v ) {
                                setAttributes( { target: v } );
                            },
                        } ),
                        el( SelectControl, {
                            label: __( 'Atributo download', 'mi-boton-pdf' ),
                            value: attributes.download || 'auto',
                            options: [
                                { label: __( 'Automático (mismo sitio)', 'mi-boton-pdf' ), value: 'auto' },
                                { label: __( 'Forzar descarga', 'mi-boton-pdf' ), value: 'yes' },
                                { label: __( 'No', 'mi-boton-pdf' ), value: 'no' },
                            ],
                            onChange: function ( v ) {
                                setAttributes( { download: v } );
                            },
                        } ),
                        el( RangeControl, {
                            label: __( 'Tamaño del icono (px)', 'mi-boton-pdf' ),
                            value: attributes.size || 48,
                            onChange: function ( v ) {
                                setAttributes( { size: v } );
                            },
                            min: 16,
                            max: 128,
                            step: 4,
                        } )
                    )
                ),
                el(
                    'div',
                    blockProps,
                    buttonId
                        ? ServerSideRender
                            ? el( ServerSideRender, {
                                block: 'mi-boton-pdf/boton',
                                attributes: attributes,
                            } )
                            : el(
                                'p',
                                { className: 'mbpdf-block-placeholder' },
                                __( 'Vista previa no disponible. Actualiza WordPress o guarda la entrada y mírala en el sitio.', 'mi-boton-pdf' )
                            )
                        : el(
                            'p',
                            { className: 'mbpdf-block-placeholder' },
                            __( 'Elige un botón PDF en el panel lateral.', 'mi-boton-pdf' )
                        )
                )
            );
        },
        save: function () {
            return null;
        },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor,
    window.wp.i18n,
    window.wp.serverSideRender
);
