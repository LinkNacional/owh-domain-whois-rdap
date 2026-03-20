/**
 * WordPress dependencies
 */
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { 
    PanelBody, 
    ToggleControl, 
    TextControl, 
    SelectControl, 
    TextareaControl, 
    RangeControl, 
    ColorPicker, 
    TabPanel 
} = wp.components;
const { ServerSideRender } = wp.serverSideRender || wp.components;

/**
 * Block: RDAP Domain Search
 */
registerBlockType('owh-domain-whois-rdap/domain-search', {
    title: __('RDAP - Pesquisa de Domínios', 'owh-domain-whois-rdap'),
    description: __('Formulário de pesquisa de disponibilidade de domínios via RDAP/WHOIS', 'owh-domain-whois-rdap'),
    icon: 'search',
    category: 'widgets',
    keywords: [
        __('domain', 'owh-domain-whois-rdap'),
        __('whois', 'owh-domain-whois-rdap'),
        __('rdap', 'owh-domain-whois-rdap'),
        __('search', 'owh-domain-whois-rdap')
    ],
    attributes: {
        showTitle: {
            type: 'boolean',
            default: true
        },
        customTitle: {
            type: 'string',
            default: 'Pesquisar Domínio'
        },
        showExamples: {
            type: 'boolean',
            default: true
        },
        // Textos personalizáveis
        placeholderText: {
            type: 'string',
            default: 'Digite o nome do domínio...'
        },
        searchButtonText: {
            type: 'string',
            default: 'Pesquisar'
        },
        loadingText: {
            type: 'string',
            default: 'Pesquisando...'
        },
        examplesText: {
            type: 'string',
            default: 'Exemplos:'
        },
        example1: {
            type: 'string',
            default: 'exemplo.com'
        },
        example2: {
            type: 'string',
            default: 'meusite.com.br'
        },
        example3: {
            type: 'string',
            default: 'minhaempresa.org'
        },
        borderWidth: {
            type: 'number',
            default: 0
        },
        borderColor: {
            type: 'string',
            default: '#ddd'
        },
        borderRadius: {
            type: 'number',
            default: 8
        },
        backgroundColor: {
            type: 'string',
            default: '#ffffff'
        },
        padding: {
            type: 'number',
            default: 20
        },
        // Colors
        primaryColor: {
            type: 'string',
            default: '#0073aa'
        },
        buttonHoverColor: {
            type: 'string',
            default: '#005a87'
        },
        inputBorderColor: {
            type: 'string',
            default: '#ddd'
        },
        inputFocusColor: {
            type: 'string',
            default: '#0073aa'
        },
        // Layout options
        buttonLayout: {
            type: 'string',
            default: 'external' // 'external' ou 'internal'
        }
    },
    supports: {
        html: false,
        customClassName: false
    },
    edit: ({ attributes, setAttributes }) => {
        const { 
            showTitle, customTitle, showExamples, placeholderText, searchButtonText, 
            loadingText, examplesText, example1, example2, example3, 
            borderWidth, borderColor, borderRadius, backgroundColor, padding,
            primaryColor, buttonHoverColor, inputBorderColor, inputFocusColor, buttonLayout
        } = attributes;

        // Preview component
        const PreviewComponent = () => {
            // Gerar CSS dinâmico baseado nos controles visuais
            const dynamicStyle = {
                border: `${borderWidth}px solid ${borderColor}`,
                borderRadius: `${borderRadius}px`,
                backgroundColor: backgroundColor,
                padding: `${padding}px`,
                maxWidth: '600px',
                margin: '0 auto'
            };

            const containerStyle = {
                ...dynamicStyle,
                boxShadow: '0 2px 10px rgba(0, 0, 0, 0.1)'
            };

            const wrapperStyle = {
                background: '#fff',
                borderRadius: '8px',
                boxShadow: '0 2px 10px rgba(0, 0, 0, 0.1)',
                padding: '20px'
            };

            const inputWrapperStyle = {
                display: 'flex',
                gap: buttonLayout === 'external' ? '10px' : '0',
                marginBottom: '15px',
                position: 'relative'
            };

            const inputStyle = {
                flex: '1',
                padding: buttonLayout === 'external' ? '12px 16px' : '12px 120px 12px 16px',
                border: `2px solid ${inputBorderColor}`,
                borderRadius: '6px',
                fontSize: '16px',
                transition: 'border-color 0.3s ease',
                width: '100%'
            };

            const buttonStyle = {
                padding: '12px 24px',
                background: primaryColor,
                color: 'white',
                border: 'none',
                borderRadius: '6px',
                fontSize: '16px',
                fontWeight: '600',
                cursor: 'pointer',
                transition: 'background-color 0.3s ease',
                minWidth: '120px',
                ...(buttonLayout === 'internal' ? {
                    position: 'absolute',
                    transform: 'translateY(-50%)',
                    padding: '8px 16px',
                    fontSize: '14px',
                    borderRadius: '4px',
                    minWidth: '100px',
                    right: '7px',
                    top: '50%',
                    height: '48px',
                } : {})
            };

            const examplesStyle = {
                textAlign: 'center',
                marginTop: '15px'
            };

            const examplesTextStyle = {
                color: '#666',
                fontSize: '14px'
            };

            const exampleDomainStyle = {
                color: primaryColor,
                cursor: 'pointer',
                textDecoration: 'underline'
            };

            return (
                <div>
                    {combinedCSS && (
                        <style>
                            {`.owh-rdap-search-container { ${combinedCSS} }`}
                        </style>
                    )}
                    <div 
                        className="owh-rdap-search-container"
                        style={containerStyle}
                    >
                        {showTitle && (
                            <h3 style={{ textAlign: 'center', marginBottom: '20px' }}>
                                {customTitle}
                            </h3>
                        )}
                        
                        <div style={wrapperStyle}>
                            <div style={inputWrapperStyle}>
                                <input 
                                    type="text"
                                    style={inputStyle}
                                    placeholder={placeholderText}
                                    readOnly
                                />
                                <button style={buttonStyle}>
                                    {searchButtonText}
                                </button>
                            </div>

                            {showExamples && (
                                <div style={examplesStyle}>
                                    <small style={examplesTextStyle}>
                                        {examplesText} {' '}
                                        <span style={exampleDomainStyle}>{example1}</span>, {' '}
                                        <span style={exampleDomainStyle}>{example2}</span>, {' '}
                                        <span style={exampleDomainStyle}>{example3}</span>
                                    </small>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            );
        };

        return (
            <>
                <InspectorControls>
                    <TabPanel
                        className="owh-rdap-block-tabs"
                        activeClass="is-active"
                        tabs={[
                            {
                                name: 'titulo',
                                title: '📝 Título',
                                className: 'tab-titulo',
                            },
                            {
                                name: 'input',
                                title: '📝 Campo de Input',
                                className: 'tab-input',
                            },
                            {
                                name: 'botao',
                                title: '🔘 Botão',
                                className: 'tab-botao',
                            },
                            {
                                name: 'descricao',
                                title: '📄 Descrição/Exemplos',
                                className: 'tab-descricao',
                            },
                            {
                                name: 'visual',
                                title: '🎨 Visual Geral',
                                className: 'tab-visual',
                            }
                        ]}
                    >
                        {(tab) => {
                            // Tab: Título
                            if (tab.name === 'titulo') {
                                return (
                                    <PanelBody
                                        title={__('Configurações do Título', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <ToggleControl
                                            label={__('Exibir título', 'owh-domain-whois-rdap')}
                                            checked={showTitle}
                                            onChange={(value) => setAttributes({ showTitle: value })}
                                            help={__('Controla se o título do formulário será exibido', 'owh-domain-whois-rdap')}
                                        />
                                        {showTitle && (
                                            <TextControl
                                                label={__('Título do formulário', 'owh-domain-whois-rdap')}
                                                value={customTitle}
                                                onChange={(value) => setAttributes({ customTitle: value })}
                                                placeholder={__('Pesquisar Domínio', 'owh-domain-whois-rdap')}
                                            />
                                        )}
                                    </PanelBody>
                                );
                            }

                            // Tab: Campo de Input
                            if (tab.name === 'input') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Configurações do Campo', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <TextControl
                                                label={__('Placeholder do campo', 'owh-domain-whois-rdap')}
                                                value={placeholderText}
                                                onChange={(value) => setAttributes({ placeholderText: value })}
                                                help={__('Texto de dica exibido dentro do campo de entrada', 'owh-domain-whois-rdap')}
                                            />
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Cores do Campo', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor da Borda do Campo', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={inputBorderColor}
                                                    onChangeComplete={(color) => setAttributes({ inputBorderColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor de Foco do Campo', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={inputFocusColor}
                                                    onChangeComplete={(color) => setAttributes({ inputFocusColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                        </PanelBody>
                                    </div>
                                );
                            }

                            // Tab: Botão
                            if (tab.name === 'botao') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Configurações do Botão', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <TextControl
                                                label={__('Texto do botão', 'owh-domain-whois-rdap')}
                                                value={searchButtonText}
                                                onChange={(value) => setAttributes({ searchButtonText: value })}
                                            />
                                            <SelectControl
                                                label={__('Layout do Botão', 'owh-domain-whois-rdap')}
                                                value={buttonLayout}
                                                options={[
                                                    { label: __('Externo (ao lado do campo)', 'owh-domain-whois-rdap'), value: 'external' },
                                                    { label: __('Interno (dentro do campo)', 'owh-domain-whois-rdap'), value: 'internal' }
                                                ]}
                                                onChange={(value) => setAttributes({ buttonLayout: value })}
                                                help={__('Controla a posição do botão em relação ao campo de pesquisa', 'owh-domain-whois-rdap')}
                                            />
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Cores do Botão', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor Primária (Botão)', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={primaryColor}
                                                    onChangeComplete={(color) => setAttributes({ primaryColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor Hover do Botão', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={buttonHoverColor}
                                                    onChangeComplete={(color) => setAttributes({ buttonHoverColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                        </PanelBody>
                                    </div>
                                );
                            }

                            // Tab: Descrição/Exemplos
                            if (tab.name === 'descricao') {
                                return (
                                    <PanelBody
                                        title={__('Configurações dos Exemplos', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <ToggleControl
                                            label={__('Exibir exemplos', 'owh-domain-whois-rdap')}
                                            checked={showExamples}
                                            onChange={(value) => setAttributes({ showExamples: value })}
                                            help={__('Mostra exemplos de domínios abaixo do formulário', 'owh-domain-whois-rdap')}
                                        />
                                        {showExamples && (
                                            <>
                                                <TextControl
                                                        label={__('Texto dos exemplos', 'owh-domain-whois-rdap')}
                                                        value={examplesText}
                                                        onChange={(value) => setAttributes({ examplesText: value })}
                                                    />
                                                    <TextControl
                                                        label={__('Exemplo 1', 'owh-domain-whois-rdap')}
                                                        value={example1}
                                                        onChange={(value) => setAttributes({ example1: value })}
                                                    />
                                                    <TextControl
                                                        label={__('Exemplo 2', 'owh-domain-whois-rdap')}
                                                        value={example2}
                                                        onChange={(value) => setAttributes({ example2: value })}
                                                    />
                                                    <TextControl
                                                        label={__('Exemplo 3', 'owh-domain-whois-rdap')}
                                                        value={example3}
                                                        onChange={(value) => setAttributes({ example3: value })}
                                                />
                                            </>
                                        )}
                                    </PanelBody>
                                );
                            }

                            // Tab: Visual Geral
                            if (tab.name === 'visual') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Configurações de Borda', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <RangeControl
                                                label={__('Espessura da Borda (px)', 'owh-domain-whois-rdap')}
                                                value={borderWidth}
                                                onChange={(value) => setAttributes({ borderWidth: value })}
                                                min={0}
                                                max={10}
                                                step={1}
                                            />
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor da Borda', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={borderColor}
                                                    onChangeComplete={(color) => setAttributes({ borderColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <RangeControl
                                                label={__('Arredondamento (px)', 'owh-domain-whois-rdap')}
                                                value={borderRadius}
                                                onChange={(value) => setAttributes({ borderRadius: value })}
                                                min={0}
                                                max={50}
                                                step={1}
                                            />
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Layout e Cores Gerais', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor de Fundo', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={backgroundColor}
                                                    onChangeComplete={(color) => setAttributes({ backgroundColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <RangeControl
                                                label={__('Espaçamento Interno (px)', 'owh-domain-whois-rdap')}
                                                value={padding}
                                                onChange={(value) => setAttributes({ padding: value })}
                                                min={0}
                                                max={60}
                                                step={5}
                                            />
                                        </PanelBody>
                                    </div>
                                );
                            }

                            return null;
                        }}
                    </TabPanel>
                </InspectorControls>
                <PreviewComponent />
            </>
        );
    },
    save: () => null // Server-side rendering
});

/**
 * Block: RDAP Domain Results
 */
registerBlockType('owh-domain-whois-rdap/domain-results', {
    title: __('RDAP - Resultados de Domínios', 'owh-domain-whois-rdap'),
    description: __('Exibe os resultados da pesquisa de domínios via RDAP/WHOIS', 'owh-domain-whois-rdap'),
    icon: 'list-view',
    category: 'widgets',
    keywords: [
        __('domain', 'owh-domain-whois-rdap'),
        __('whois', 'owh-domain-whois-rdap'),
        __('rdap', 'owh-domain-whois-rdap'),
        __('results', 'owh-domain-whois-rdap')
    ],
    attributes: {
        showTitle: {
            type: 'boolean',
            default: true
        },
        customTitle: {
            type: 'string',
            default: 'Resultado da pesquisa para: {domain}'
        },
        // Textos personalizáveis
        noResultText: {
            type: 'string',
            default: 'Aguardando Pesquisa'
        },
        noResultDescription: {
            type: 'string',
            default: 'Os resultados da pesquisa de domínios aparecerão aqui.'
        },
        availableTitle: {
            type: 'string',
            default: 'Domínio Disponível'
        },
        availableText: {
            type: 'string',
            default: 'Este domínio está disponível para registro!'
        },
        unavailableTitle: {
            type: 'string',
            default: 'Domínio Indisponível'
        },
        unavailableText: {
            type: 'string',
            default: 'Este domínio já está registrado e não está disponível.'
        },
        buyButtonText: {
            type: 'string',
            default: 'Registrar Domínio'
        },
        detailsButtonText: {
            type: 'string',
            default: 'Ver detalhes completos do WHOIS'
        },
        // Ícones personalizáveis
        showIcons: {
            type: 'boolean',
            default: true
        },
        searchIcon: {
            type: 'string',
            default: '🔍'
        },
        availableIcon: {
            type: 'string',
            default: '✅'
        },
        unavailableIcon: {
            type: 'string',
            default: '❌'
        },
        // Preview mode
        previewMode: {
            type: 'string',
            default: 'no-result' // 'no-result', 'available', 'unavailable'
        },
        borderWidth: {
            type: 'number',
            default: 0
        },
        borderColor: {
            type: 'string',
            default: '#ddd'
        },
        borderRadius: {
            type: 'number',
            default: 8
        },
        backgroundColor: {
            type: 'string',
            default: '#ffffff'
        },
        padding: {
            type: 'number',
            default: 20
        },
        // Colors
        availableColor: {
            type: 'string',
            default: '#46b450'
        },
        unavailableColor: {
            type: 'string',
            default: '#dc3232'
        },
        // Layout options
        buttonLayout: {
            type: 'string',
            default: 'external' // 'external' ou 'internal'
        },
        // Watermark
        showWatermark: {
            type: 'boolean',
            default: false
        }
    },
    supports: {
        html: false,
        customClassName: false
    },
    edit: ({ attributes, setAttributes }) => {
        const { 
            showTitle, customTitle, noResultText, noResultDescription,
            availableTitle, availableText, unavailableTitle, unavailableText,
            buyButtonText, detailsButtonText,
            showIcons, searchIcon, availableIcon, unavailableIcon,
            previewMode, borderWidth, borderColor, borderRadius,
            backgroundColor, padding, availableColor, unavailableColor, showWatermark, buttonLayout
        } = attributes;

        // Preview component
        const PreviewComponent = () => {
            // Gerar CSS dinâmico baseado nos controles visuais
            const dynamicStyle = {
                border: `${borderWidth}px solid ${borderColor}`,
                borderRadius: `${borderRadius}px`,
                backgroundColor: backgroundColor,
                padding: `${padding}px`,
                maxWidth: '600px',
                margin: '0 auto'
            };

            const containerStyle = {
                ...dynamicStyle,
                boxShadow: '0 2px 10px rgba(0, 0, 0, 0.1)'
            };

            const resultStyle = {
                display: 'flex',
                alignItems: 'flex-start',
                gap: '20px',
                marginBottom: '20px'
            };

            const iconStyle = {
                fontSize: '48px',
                lineHeight: '1'
            };

            const contentStyle = {
                flex: '1'
            };

            const titleStyle = {
                margin: '0 0 10px 0',
                fontSize: '20px'
            };

            const textStyle = {
                margin: '0 0 20px 0',
                color: '#666',
                fontSize: '16px',
                lineHeight: '1.5'
            };

            const buttonStyle = {
                display: 'inline-flex',
                alignItems: 'center',
                padding: '12px 24px',
                color: 'white',
                textDecoration: 'none',
                borderRadius: '6px',
                fontWeight: '600',
                fontSize: '16px',
                gap: '8px',
                border: 'none',
                cursor: 'pointer'
            };

            if (previewMode === 'no-result') {
                return (
                    <div>
                        {combinedCSS && (
                            <style>
                                {`.owh-rdap-results-container { ${combinedCSS} }`}
                            </style>
                        )}
                        <div 
                            className="owh-rdap-results-container"
                            style={containerStyle}
                        >
                            <div className="owh-rdap-result-content">
                                <div style={{ textAlign: 'center', padding: '40px', background: '#f9f9f9', borderRadius: '8px' }}>
                                    {showIcons && (
                                        <div style={{ fontSize: '48px', marginBottom: '15px' }}>
                                            {searchIcon}
                                        </div>
                                    )}
                                    <h3 style={{ margin: '0 0 15px 0' }}>{noResultText}</h3>
                                    <p style={{ margin: 0, color: '#666' }}>{noResultDescription}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            }

            let icon, title, text, titleColor, buttonBg;
            const domain = 'exemplo.com';
            
            if (previewMode === 'available') {
                icon = availableIcon;
                title = availableTitle;
                text = availableText;
                titleColor = availableColor;
                buttonBg = availableColor;
            } else if (previewMode === 'unavailable') {
                icon = unavailableIcon;
                title = unavailableTitle;
                text = unavailableText;
                titleColor = unavailableColor;
                buttonBg = unavailableColor;
            }

            return (
                <div>
                    {combinedCSS && (
                        <style>
                            {`.owh-rdap-results-container { ${combinedCSS} }`}
                        </style>
                    )}
                    <div 
                        className="owh-rdap-results-container"
                        style={containerStyle}
                    >
                        {showTitle && (
                            <div style={{ textAlign: 'center', marginBottom: '30px' }}>
                                <h3 style={{ color: '#333', fontSize: '24px', margin: 0 }}>
                                    {customTitle.replace('{domain}', domain)}
                                </h3>
                            </div>
                        )}
                        <div className="owh-rdap-result-content">
                            <div 
                                className={previewMode === 'available' ? 'owh-rdap-result-available' : 'owh-rdap-result-unavailable'}
                                style={resultStyle}
                            >
                                {showIcons && (
                                    <div 
                                        className={previewMode === 'available' ? 'owh-rdap-available-icon' : 'owh-rdap-unavailable-icon'}
                                        style={iconStyle}
                                    >
                                        {icon}
                                    </div>
                                )}
                                <div 
                                    className={previewMode === 'available' ? 'owh-rdap-available-content' : 'owh-rdap-unavailable-content'}
                                    style={contentStyle}
                                >
                                    <h4 style={{ ...titleStyle, color: titleColor }}>
                                        {title}
                                    </h4>
                                    <p style={textStyle}>
                                        {text}
                                    </p>
                                    {previewMode === 'available' && (
                                        <div style={{ marginTop: '20px' }}>
                                            <button style={{ ...buttonStyle, background: buttonBg }}>
                                                <span className="dashicons dashicons-cart"></span>
                                                {buyButtonText}
                                            </button>
                                        </div>
                                    )}
                                    {previewMode === 'unavailable' && (
                                        <div style={{ marginTop: '20px' }}>
                                            <button style={{ ...buttonStyle, background: buttonBg }}>
                                                <span className="dashicons dashicons-info"></span>
                                                {detailsButtonText}
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        };

        return (
            <>
                <InspectorControls>
                    <TabPanel
                        className="owh-rdap-block-tabs"
                        activeClass="is-active"
                        tabs={[
                            {
                                name: 'titulo',
                                title: '📝 Título',
                                className: 'tab-titulo',
                            },
                            {
                                name: 'botao',
                                title: '🔘 Botões',
                                className: 'tab-botao',
                            },
                            {
                                name: 'icones',
                                title: '🎭 Ícones',
                                className: 'tab-icones',
                            },
                            {
                                name: 'descricoes',
                                title: '📄 Descrições/Textos',
                                className: 'tab-descricoes',
                            },
                            {
                                name: 'visual',
                                title: '🎨 Visual Geral',
                                className: 'tab-visual',
                            },
                            {
                                name: 'preview',
                                title: '👁️ Preview',
                                className: 'tab-preview',
                            }
                        ]}
                    >
                        {(tab) => {
                            // Tab: Título
                            if (tab.name === 'titulo') {
                                return (
                                    <PanelBody
                                        title={__('Configurações do Título', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <ToggleControl
                                            label={__('Exibir título principal', 'owh-domain-whois-rdap')}
                                            checked={showTitle}
                                            onChange={(value) => setAttributes({ showTitle: value })}
                                            help={__('Controla se o título principal será exibido', 'owh-domain-whois-rdap')}
                                        />
                                        {showTitle && (
                                            <TextControl
                                                label={__('Título principal', 'owh-domain-whois-rdap')}
                                                value={customTitle}
                                                onChange={(value) => setAttributes({ customTitle: value })}
                                                placeholder={__('Resultado da pesquisa para: {domain}', 'owh-domain-whois-rdap')}
                                                help={__('Use {domain} para inserir dinamicamente o nome do domínio', 'owh-domain-whois-rdap')}
                                            />
                                        )}
                                    </PanelBody>
                                );
                            }

                            // Tab: Botões
                            if (tab.name === 'botao') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Botão de Compra (Domínio Disponível)', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <TextControl
                                                label={__('Texto do Botão de Compra', 'owh-domain-whois-rdap')}
                                                value={buyButtonText}
                                                onChange={(value) => setAttributes({ buyButtonText: value })}
                                            />
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor do Botão - Disponível', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={availableColor}
                                                    onChangeComplete={(color) => setAttributes({ availableColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Botão de Detalhes (Domínio Indisponível)', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <TextControl
                                                label={__('Texto do Botão de Detalhes', 'owh-domain-whois-rdap')}
                                                value={detailsButtonText}
                                                onChange={(value) => setAttributes({ detailsButtonText: value })}
                                            />
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor do Botão - Indisponível', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={unavailableColor}
                                                    onChangeComplete={(color) => setAttributes({ unavailableColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                        </PanelBody>
                                    </div>
                                );
                            }

                            // Tab: Ícones
                            if (tab.name === 'icones') {
                                return (
                                    <PanelBody
                                        title={__('Configurações de Ícones', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <ToggleControl
                                            label={__('Mostrar Ícones', 'owh-domain-whois-rdap')}
                                            checked={showIcons}
                                            onChange={(value) => setAttributes({ showIcons: value })}
                                            help={__('Exibe ou oculta os ícones nos resultados.', 'owh-domain-whois-rdap')}
                                        />
                                        {showIcons && (
                                            <div>
                                                <TextControl
                                                    label={__('Ícone de Pesquisa', 'owh-domain-whois-rdap')}
                                                    value={searchIcon}
                                                    onChange={(value) => setAttributes({ searchIcon: value })}
                                                    placeholder="🔍"
                                                />
                                                <TextControl
                                                    label={__('Ícone Disponível', 'owh-domain-whois-rdap')}
                                                    value={availableIcon}
                                                    onChange={(value) => setAttributes({ availableIcon: value })}
                                                    placeholder="✅"
                                                />
                                                <TextControl
                                                    label={__('Ícone Indisponível', 'owh-domain-whois-rdap')}
                                                    value={unavailableIcon}
                                                    onChange={(value) => setAttributes({ unavailableIcon: value })}
                                                    placeholder="❌"
                                                />
                                            </div>
                                        )}
                                    </PanelBody>
                                );
                            }

                            // Tab: Descrições/Textos
                            if (tab.name === 'descricoes') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Aguardando Pesquisa', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <TextControl
                                                label={__('Título "Sem Resultado"', 'owh-domain-whois-rdap')}
                                                value={noResultText}
                                                onChange={(value) => setAttributes({ noResultText: value })}
                                            />
                                            <TextareaControl
                                                label={__('Descrição "Sem Resultado"', 'owh-domain-whois-rdap')}
                                                value={noResultDescription}
                                                onChange={(value) => setAttributes({ noResultDescription: value })}
                                                rows={2}
                                            />
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Domínio Disponível', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <TextControl
                                                label={__('Título "Disponível"', 'owh-domain-whois-rdap')}
                                                value={availableTitle}
                                                onChange={(value) => setAttributes({ availableTitle: value })}
                                            />
                                            <TextareaControl
                                                label={__('Texto "Disponível"', 'owh-domain-whois-rdap')}
                                                value={availableText}
                                                onChange={(value) => setAttributes({ availableText: value })}
                                                rows={2}
                                            />
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Domínio Indisponível', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <TextControl
                                                label={__('Título "Indisponível"', 'owh-domain-whois-rdap')}
                                                value={unavailableTitle}
                                                onChange={(value) => setAttributes({ unavailableTitle: value })}
                                            />
                                            <TextareaControl
                                                label={__('Texto "Indisponível"', 'owh-domain-whois-rdap')}
                                                value={unavailableText}
                                                onChange={(value) => setAttributes({ unavailableText: value })}
                                                rows={2}
                                            />
                                        </PanelBody>
                                    </div>
                                );
                            }

                            // Tab: Visual Geral
                            if (tab.name === 'visual') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Configurações de Borda', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <RangeControl
                                                label={__('Espessura da Borda (px)', 'owh-domain-whois-rdap')}
                                                value={borderWidth}
                                                onChange={(value) => setAttributes({ borderWidth: value })}
                                                min={0}
                                                max={10}
                                                step={1}
                                            />
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor da Borda', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={borderColor}
                                                    onChangeComplete={(color) => setAttributes({ borderColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <RangeControl
                                                label={__('Arredondamento (px)', 'owh-domain-whois-rdap')}
                                                value={borderRadius}
                                                onChange={(value) => setAttributes({ borderRadius: value })}
                                                min={0}
                                                max={50}
                                                step={1}
                                            />
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Layout e Cores Gerais', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <SelectControl
                                                label={__('Layout do Botão', 'owh-domain-whois-rdap')}
                                                value={buttonLayout}
                                                options={[
                                                    { label: __('Externo (ao lado do campo)', 'owh-domain-whois-rdap'), value: 'external' },
                                                    { label: __('Interno (dentro do campo)', 'owh-domain-whois-rdap'), value: 'internal' }
                                                ]}
                                                onChange={(value) => setAttributes({ buttonLayout: value })}
                                                help={__('Controla a posição do botão em relação ao campo de pesquisa', 'owh-domain-whois-rdap')}
                                            />
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor de Fundo', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={backgroundColor}
                                                    onChangeComplete={(color) => setAttributes({ backgroundColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <RangeControl
                                                label={__('Espaçamento Interno (px)', 'owh-domain-whois-rdap')}
                                                value={padding}
                                                onChange={(value) => setAttributes({ padding: value })}
                                                min={0}
                                                max={60}
                                                step={5}
                                            />
                                        </PanelBody>
                                    </div>
                                );
                            }

                            // Tab: Preview
                            if (tab.name === 'preview') {
                                return (
                                    <PanelBody
                                        title={__('Configurações de Preview', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <SelectControl
                                            label={__('Modo de Preview', 'owh-domain-whois-rdap')}
                                            value={previewMode}
                                            options={[
                                                { label: __('Sem Resultado', 'owh-domain-whois-rdap'), value: 'no-result' },
                                                { label: __('Domínio Disponível', 'owh-domain-whois-rdap'), value: 'available' },
                                                { label: __('Domínio Indisponível', 'owh-domain-whois-rdap'), value: 'unavailable' }
                                            ]}
                                            onChange={(value) => setAttributes({ previewMode: value })}
                                            help={__('Escolha como visualizar o bloco no editor', 'owh-domain-whois-rdap')}
                                        />
                                    </PanelBody>
                                );
                            }

                            return null;
                        }}
                    </TabPanel>
                </InspectorControls>
                <PreviewComponent />
            </>
        );
    },
    save: () => null // Server-side rendering
});

/**
 * Block: RDAP WHOIS Details
 */
registerBlockType('owh-domain-whois-rdap/whois-details', {
    title: __('RDAP - Detalhes WHOIS', 'owh-domain-whois-rdap'),
    description: __('Exibe informações detalhadas WHOIS/RDAP de um domínio', 'owh-domain-whois-rdap'),
    icon: 'info',
    category: 'widgets',
    keywords: [
        __('domain', 'owh-domain-whois-rdap'),
        __('whois', 'owh-domain-whois-rdap'),
        __('rdap', 'owh-domain-whois-rdap'),
        __('details', 'owh-domain-whois-rdap')
    ],
    attributes: {
        showTitle: {
            type: 'boolean',
            default: true
        },
        customTitle: {
            type: 'string',
            default: 'Detalhes WHOIS/RDAP'
        },
        showEvents: {
            type: 'boolean',
            default: true
        },
        eventsTitle: {
            type: 'string',
            default: 'Histórico de Eventos'
        },
        showEntities: {
            type: 'boolean',
            default: true
        },
        entitiesTitle: {
            type: 'string',
            default: 'Entidades Relacionadas'
        },
        showNameservers: {
            type: 'boolean',
            default: true
        },
        nameserversTitle: {
            type: 'string',
            default: 'Servidores DNS (Nameservers)'
        },
        showStatus: {
            type: 'boolean',
            default: true
        },
        statusTitle: {
            type: 'string',
            default: 'Status do Domínio'
        },
        showDnssec: {
            type: 'boolean',
            default: true
        },
        dnssecTitle: {
            type: 'string',
            default: 'DNSSEC'
        },
        noDomainText: {
            type: 'string',
            default: 'Nenhum Domínio Informado'
        },
        noDomainDescription: {
            type: 'string',
            default: 'Para visualizar os detalhes WHOIS, acesse esta página através do link "Ver detalhes completos" nos resultados da pesquisa.'
        },
        availableText: {
            type: 'string',
            default: 'Este domínio está disponível para registro e não possui informações WHOIS.'
        },
        errorText: {
            type: 'string',
            default: 'Erro na Pesquisa'
        },
        previewMode: {
            type: 'string',
            default: 'no-domain' // 'no-domain' or 'with-domain'
        },
        showIcon: {
            type: 'boolean',
            default: true
        },
        customIcon: {
            type: 'string',
            default: '📋'
        },
        borderWidth: {
            type: 'number',
            default: 1
        },
        borderColor: {
            type: 'string',
            default: '#ddd'
        },
        borderRadius: {
            type: 'number',
            default: 4
        },
        backgroundColor: {
            type: 'string',
            default: '#ffffff'
        },
        padding: {
            type: 'number',
            default: 20
        },
        // Layout options  
        buttonLayout: {
            type: 'string',
            default: 'external' // 'external' ou 'internal'
        }
    },
    supports: {
        html: false,
        customClassName: false
    },
    edit: ({ attributes, setAttributes }) => {
        const { 
            showTitle, customTitle, showEvents, eventsTitle, showEntities, entitiesTitle,
            showNameservers, nameserversTitle, showStatus, statusTitle, showDnssec, dnssecTitle,
            noDomainText, noDomainDescription, availableText, errorText, previewMode,
            showIcon, customIcon, borderWidth, borderColor, borderRadius, 
            backgroundColor, padding, buttonLayout
        } = attributes;

        // Preview component
        const PreviewComponent = () => {
            // Gerar CSS dinâmico baseado nos controles visuais
            const dynamicStyle = {
                border: `${borderWidth}px solid ${borderColor}`,
                borderRadius: `${borderRadius}px`,
                backgroundColor: backgroundColor,
                padding: `${padding}px`,
                textAlign: previewMode === 'no-domain' ? 'center' : 'left'
            };

            if (previewMode === 'no-domain') {
                return (
                    <div>
                        {combinedCSS && (
                            <style>
                                {`.owh-rdap-whois-details-container { ${combinedCSS} }`}
                            </style>
                        )}
                        <div 
                            className="owh-rdap-whois-details-container"
                            style={dynamicStyle}
                        >
                            {showTitle && <h3>{customTitle}</h3>}
                            {showIcon && (
                                <div style={{ fontSize: '48px', margin: '20px 0' }}>
                                    {customIcon}
                                </div>
                            )}
                            <h4>{noDomainText}</h4>
                            <p>{noDomainDescription}</p>
                        </div>
                    </div>
                );
            } else {
                return (
                    <div>
                        {combinedCSS && (
                            <style>
                                {`.owh-rdap-whois-details-container { ${combinedCSS} }`}
                            </style>
                        )}
                        <div 
                            className="owh-rdap-whois-details-container"
                            style={dynamicStyle}
                        >   
                            <div style={{ marginBottom: '20px', textAlign: 'center' }}>
                                {showTitle && <h3>{customTitle}</h3>}
                                <h4>Detalhes WHOIS para <strong>exemplo.com</strong></h4>
                            </div>
                            {showEvents && (
                                <div style={{ marginBottom: '20px', padding: '15px', background: '#f9f9f9', borderLeft: '4px solid #0073aa' }}>
                                    <h5>{eventsTitle}</h5>
                                    <p style={{ margin: 0, fontSize: '14px' }}>Registro: 15/01/2020, Expiração: 15/01/2025</p>
                                </div>
                            )}
                            {showEntities && (
                                <div style={{ marginBottom: '20px', padding: '15px', background: '#f9f9f9', borderLeft: '4px solid #0073aa' }}>
                                    <h5>{entitiesTitle}</h5>
                                    <p style={{ margin: 0, fontSize: '14px' }}>Registrante, Administrativo, Técnico</p>
                                </div>
                            )}
                            {showNameservers && (
                                <div style={{ marginBottom: '20px', padding: '15px', background: '#f9f9f9', borderLeft: '4px solid #0073aa' }}>
                                    <h5>{nameserversTitle}</h5>
                                    <p style={{ margin: 0, fontSize: '14px' }}>ns1.example.com, ns2.example.com</p>
                                </div>
                            )}
                            {showStatus && (
                                <div style={{ marginBottom: '20px', padding: '15px', background: '#f9f9f9', borderLeft: '4px solid #0073aa' }}>
                                    <h5>{statusTitle}</h5>
                                    <p style={{ margin: 0, fontSize: '14px' }}>clientTransferProhibited</p>
                                </div>
                            )}
                            {showDnssec && (
                                <div style={{ padding: '15px', background: '#f9f9f9', borderLeft: '4px solid #0073aa' }}>
                                    <h5>{dnssecTitle}</h5>
                                    <p style={{ margin: 0, fontSize: '14px' }}>Status: Habilitado</p>
                                </div>
                            )}
                        </div>
                    </div>
                );
            }
        };

        return (
            <>
                <InspectorControls>
                    <TabPanel
                        className="owh-rdap-block-tabs"
                        activeClass="is-active"
                        tabs={[
                            {
                                name: 'titulo',
                                title: '📝 Título',
                                className: 'tab-titulo',
                            },
                            {
                                name: 'icone',
                                title: '🔧 Ícone',
                                className: 'tab-icone',
                            },
                            {
                                name: 'secoes',
                                title: '📋 Seções',
                                className: 'tab-secoes',
                            },
                            {
                                name: 'descricoes',
                                title: '📄 Descrições',
                                className: 'tab-descricoes',
                            },
                            {
                                name: 'visual',
                                title: '🎨 Visual Geral',
                                className: 'tab-visual',
                            },
                            {
                                name: 'preview',
                                title: '👁️ Preview',
                                className: 'tab-preview',
                            }
                        ]}
                    >
                        {(tab) => {
                            // Tab: Título
                            if (tab.name === 'titulo') {
                                return (
                                    <PanelBody
                                        title={__('Configurações do Título', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <ToggleControl
                                            label={__('Exibir título principal', 'owh-domain-whois-rdap')}
                                            checked={showTitle}
                                            onChange={(value) => setAttributes({ showTitle: value })}
                                            help={__('Controla se o título principal será exibido', 'owh-domain-whois-rdap')}
                                        />
                                        {showTitle && (
                                            <TextControl
                                                label={__('Título principal', 'owh-domain-whois-rdap')}
                                                value={customTitle}
                                                onChange={(value) => setAttributes({ customTitle: value })}
                                                placeholder={__('Detalhes WHOIS/RDAP', 'owh-domain-whois-rdap')}
                                            />
                                        )}
                                    </PanelBody>
                                );
                            }

                            // Tab: Ícone
                            if (tab.name === 'icone') {
                                return (
                                    <PanelBody
                                        title={__('Configurações de Ícone', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <ToggleControl
                                            label={__('Mostrar Ícone', 'owh-domain-whois-rdap')}
                                            checked={showIcon}
                                            onChange={(value) => setAttributes({ showIcon: value })}
                                            help={__('Exibe ou oculta o ícone quando nenhum domínio foi especificado.', 'owh-domain-whois-rdap')}
                                        />
                                        {showIcon && (
                                            <TextControl
                                                label={__('Ícone Personalizado', 'owh-domain-whois-rdap')}
                                                value={customIcon}
                                                onChange={(value) => setAttributes({ customIcon: value })}
                                                placeholder="📋"
                                                help={__('Insira um emoji ou texto que será usado como ícone.', 'owh-domain-whois-rdap')}
                                            />
                                        )}
                                        <div style={{ 
                                            marginTop: '20px',
                                            padding: '15px',
                                            background: '#f0f0f0',
                                            borderRadius: '4px',
                                            border: '1px solid #ddd'
                                        }}>
                                            <h4 style={{ marginTop: 0, fontSize: '13px' }}>
                                                {__('Preview do Ícone:', 'owh-domain-whois-rdap')}
                                            </h4>
                                            {showIcon ? (
                                                <div style={{ fontSize: '32px', textAlign: 'center' }}>
                                                    {customIcon}
                                                </div>
                                            ) : (
                                                <p style={{ margin: 0, fontStyle: 'italic' }}>
                                                    {__('Ícone desabilitado', 'owh-domain-whois-rdap')}
                                                </p>
                                            )}
                                        </div>
                                    </PanelBody>
                                );
                            }

                            // Tab: Seções de Informações
                            if (tab.name === 'secoes') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Histórico de Eventos', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <ToggleControl
                                                label={__('Mostrar Histórico de Eventos', 'owh-domain-whois-rdap')}
                                                checked={showEvents}
                                                onChange={(value) => setAttributes({ showEvents: value })}
                                            />
                                            {showEvents && (
                                                <TextControl
                                                    label={__('Título da seção de eventos', 'owh-domain-whois-rdap')}
                                                    value={eventsTitle}
                                                    onChange={(value) => setAttributes({ eventsTitle: value })}
                                                />
                                            )}
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Entidades Relacionadas', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <ToggleControl
                                                label={__('Mostrar Entidades Relacionadas', 'owh-domain-whois-rdap')}
                                                checked={showEntities}
                                                onChange={(value) => setAttributes({ showEntities: value })}
                                            />
                                            {showEntities && (
                                                <TextControl
                                                    label={__('Título da seção de entidades', 'owh-domain-whois-rdap')}
                                                    value={entitiesTitle}
                                                    onChange={(value) => setAttributes({ entitiesTitle: value })}
                                                />
                                            )}
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Servidores DNS', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <ToggleControl
                                                label={__('Mostrar Servidores DNS', 'owh-domain-whois-rdap')}
                                                checked={showNameservers}
                                                onChange={(value) => setAttributes({ showNameservers: value })}
                                            />
                                            {showNameservers && (
                                                <TextControl
                                                    label={__('Título da seção de nameservers', 'owh-domain-whois-rdap')}
                                                    value={nameserversTitle}
                                                    onChange={(value) => setAttributes({ nameserversTitle: value })}
                                                />
                                            )}
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Status e DNSSEC', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <ToggleControl
                                                label={__('Mostrar Status do Domínio', 'owh-domain-whois-rdap')}
                                                checked={showStatus}
                                                onChange={(value) => setAttributes({ showStatus: value })}
                                            />
                                            {showStatus && (
                                                <TextControl
                                                    label={__('Título da seção de status', 'owh-domain-whois-rdap')}
                                                    value={statusTitle}
                                                    onChange={(value) => setAttributes({ statusTitle: value })}
                                                />
                                            )}
                                            <ToggleControl
                                                label={__('Mostrar Informações DNSSEC', 'owh-domain-whois-rdap')}
                                                checked={showDnssec}
                                                onChange={(value) => setAttributes({ showDnssec: value })}
                                            />
                                            {showDnssec && (
                                                <TextControl
                                                    label={__('Título da seção DNSSEC', 'owh-domain-whois-rdap')}
                                                    value={dnssecTitle}
                                                    onChange={(value) => setAttributes({ dnssecTitle: value })}
                                                />
                                            )}
                                        </PanelBody>
                                    </div>
                                );
                            }

                            // Tab: Descrições
                            if (tab.name === 'descricoes') {
                                return (
                                    <PanelBody
                                        title={__('Mensagens Personalizadas', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <TextControl
                                            label={__('Texto "Nenhum Domínio"', 'owh-domain-whois-rdap')}
                                            value={noDomainText}
                                            onChange={(value) => setAttributes({ noDomainText: value })}
                                            help={__('Título exibido quando nenhum domínio for especificado', 'owh-domain-whois-rdap')}
                                        />
                                        <TextareaControl
                                            label={__('Descrição "Nenhum Domínio"', 'owh-domain-whois-rdap')}
                                            value={noDomainDescription}
                                            onChange={(value) => setAttributes({ noDomainDescription: value })}
                                            rows={3}
                                            help={__('Descrição explicativa exibida quando nenhum domínio for especificado', 'owh-domain-whois-rdap')}
                                        />
                                        <TextControl
                                            label={__('Texto "Domínio Disponível"', 'owh-domain-whois-rdap')}
                                            value={availableText}
                                            onChange={(value) => setAttributes({ availableText: value })}
                                            help={__('Texto exibido quando o domínio estiver disponível', 'owh-domain-whois-rdap')}
                                        />
                                    </PanelBody>
                                );
                            }

                            // Tab: Visual Geral
                            if (tab.name === 'visual') {
                                return (
                                    <div>
                                        <PanelBody
                                            title={__('Configurações de Borda', 'owh-domain-whois-rdap')}
                                            initialOpen={true}
                                        >
                                            <RangeControl
                                                label={__('Espessura da Borda (px)', 'owh-domain-whois-rdap')}
                                                value={borderWidth}
                                                onChange={(value) => setAttributes({ borderWidth: value })}
                                                min={0}
                                                max={10}
                                                step={1}
                                            />
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor da Borda', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={borderColor}
                                                    onChangeComplete={(color) => setAttributes({ borderColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <RangeControl
                                                label={__('Arredondamento (px)', 'owh-domain-whois-rdap')}
                                                value={borderRadius}
                                                onChange={(value) => setAttributes({ borderRadius: value })}
                                                min={0}
                                                max={50}
                                                step={1}
                                            />
                                        </PanelBody>
                                        
                                        <PanelBody
                                            title={__('Layout e Cores Gerais', 'owh-domain-whois-rdap')}
                                            initialOpen={false}
                                        >
                                            <SelectControl
                                                label={__('Layout do Botão', 'owh-domain-whois-rdap')}
                                                value={buttonLayout}
                                                options={[
                                                    { label: __('Externo (ao lado do campo)', 'owh-domain-whois-rdap'), value: 'external' },
                                                    { label: __('Interno (dentro do campo)', 'owh-domain-whois-rdap'), value: 'internal' }
                                                ]}
                                                onChange={(value) => setAttributes({ buttonLayout: value })}
                                                help={__('Controla a posição do botão em relação ao campo de pesquisa', 'owh-domain-whois-rdap')}
                                            />
                                            <div style={{ marginBottom: '20px' }}>
                                                <label style={{ 
                                                    display: 'block', 
                                                    marginBottom: '8px', 
                                                    fontSize: '11px', 
                                                    fontWeight: '500', 
                                                    textTransform: 'uppercase' 
                                                }}>
                                                    {__('Cor de Fundo', 'owh-domain-whois-rdap')}
                                                </label>
                                                <ColorPicker
                                                    color={backgroundColor}
                                                    onChangeComplete={(color) => setAttributes({ backgroundColor: color.hex })}
                                                    disableAlpha={true}
                                                />
                                            </div>
                                            <RangeControl
                                                label={__('Espaçamento Interno (px)', 'owh-domain-whois-rdap')}
                                                value={padding}
                                                onChange={(value) => setAttributes({ padding: value })}
                                                min={0}
                                                max={60}
                                                step={5}
                                            />
                                        </PanelBody>
                                    </div>
                                );
                            }

                            // Tab: Preview
                            if (tab.name === 'preview') {
                                return (
                                    <PanelBody
                                        title={__('Configurações de Preview', 'owh-domain-whois-rdap')}
                                        initialOpen={true}
                                    >
                                        <SelectControl
                                            label={__('Modo de Preview', 'owh-domain-whois-rdap')}
                                            value={previewMode}
                                            options={[
                                                { label: __('Sem Domínio', 'owh-domain-whois-rdap'), value: 'no-domain' },
                                                { label: __('Com Domínio (Exemplo)', 'owh-domain-whois-rdap'), value: 'with-domain' }
                                            ]}
                                            onChange={(value) => setAttributes({ previewMode: value })}
                                            help={__('Escolha como visualizar o bloco no editor', 'owh-domain-whois-rdap')}
                                        />
                                    </PanelBody>
                                );
                            }

                            return null;
                        }}
                    </TabPanel>
                </InspectorControls>
                <PreviewComponent />
            </>
        );
    },
    save: () => null // Server-side rendering
});
