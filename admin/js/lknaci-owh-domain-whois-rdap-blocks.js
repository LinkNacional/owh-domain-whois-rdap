/**
 * WordPress dependencies
 */
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;
const { ServerSideRender } = wp.serverSideRender || wp.components;

/**
 * Block: RDAP Domain Search
 */
registerBlockType('owh-rdap/domain-search', {
    title: __('RDAP - Pesquisa de Domínios', 'lknaci-owh-domain-whois-rdap'),
    description: __('Formulário de pesquisa de disponibilidade de domínios via RDAP/WHOIS', 'lknaci-owh-domain-whois-rdap'),
    icon: 'search',
    category: 'widgets',
    keywords: [
        __('domain', 'lknaci-owh-domain-whois-rdap'),
        __('whois', 'lknaci-owh-domain-whois-rdap'),
        __('rdap', 'lknaci-owh-domain-whois-rdap'),
        __('search', 'lknaci-owh-domain-whois-rdap')
    ],
    attributes: {
        showTitle: {
            type: 'boolean',
            default: true
        },
        showExamples: {
            type: 'boolean',
            default: true
        }
    },
    supports: {
        html: false,
        customClassName: false
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;
        const { showTitle, showExamples } = attributes;

        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                InspectorControls,
                null,
                wp.element.createElement(
                    PanelBody,
                    {
                        title: __('Configurações do Bloco', 'lknaci-owh-domain-whois-rdap'),
                        initialOpen: true
                    },
                    wp.element.createElement(ToggleControl, {
                        label: __('Exibir título', 'lknaci-owh-domain-whois-rdap'),
                        checked: showTitle,
                        onChange: function(value) {
                            setAttributes({ showTitle: value });
                        }
                    }),
                    wp.element.createElement(ToggleControl, {
                        label: __('Exibir exemplos', 'lknaci-owh-domain-whois-rdap'),
                        checked: showExamples,
                        onChange: function(value) {
                            setAttributes({ showExamples: value });
                        }
                    })
                )
            ),
            wp.element.createElement(ServerSideRender, {
                block: 'owh-rdap/domain-search',
                attributes: attributes
            })
        );
    },
    save: function() {
        return null; // Server-side rendering
    }
});

/**
 * Block: RDAP Domain Results
 */
registerBlockType('owh-rdap/domain-results', {
    title: __('RDAP - Resultados de Domínios', 'lknaci-owh-domain-whois-rdap'),
    description: __('Exibe os resultados da pesquisa de domínios via RDAP/WHOIS', 'lknaci-owh-domain-whois-rdap'),
    icon: 'list-view',
    category: 'widgets',
    keywords: [
        __('domain', 'lknaci-owh-domain-whois-rdap'),
        __('whois', 'lknaci-owh-domain-whois-rdap'),
        __('rdap', 'lknaci-owh-domain-whois-rdap'),
        __('results', 'lknaci-owh-domain-whois-rdap')
    ],
    attributes: {
        showTitle: {
            type: 'boolean',
            default: true
        }
    },
    supports: {
        html: false,
        customClassName: false
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;
        const { showTitle } = attributes;

        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                InspectorControls,
                null,
                wp.element.createElement(
                    PanelBody,
                    {
                        title: __('Configurações do Bloco', 'lknaci-owh-domain-whois-rdap'),
                        initialOpen: true
                    },
                    wp.element.createElement(ToggleControl, {
                        label: __('Exibir título', 'lknaci-owh-domain-whois-rdap'),
                        checked: showTitle,
                        onChange: function(value) {
                            setAttributes({ showTitle: value });
                        }
                    })
                )
            ),
            wp.element.createElement(ServerSideRender, {
                block: 'owh-rdap/domain-results',
                attributes: attributes
            })
        );
    },
    save: function() {
        return null; // Server-side rendering
    }
});
