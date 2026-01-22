(function($) {
    $(document).ready(function() {
        setTimeout(function() {
            console.log('Iniciando transformação das tabelas...');
            transformFormTables();
        }, 500);
        
        function transformFormTables() {
            // Encontra todas as linhas da tabela de configurações
            $('.form-table tbody tr').each(function() {
                const $row = $(this);
                const $th = $row.find('th');
                const $td = $row.find('td');
                
                if ($th.length && $td.length) {
                    transformRow($row, $th, $td);
                }
            });
            
            // Remove espaçamento da tabela
            $('.form-table').css({
                'border-spacing': '0 15px',
                'background': 'transparent'
            });
            
            // Configurar visibilidade e eventos após montar o layout
            setupFieldVisibility();
        }
        
        function transformRow($row, $th, $td) {
            const fieldName = $th.attr('scope') === 'row' ? $th.text().trim() : '';
            
            // Transformar o TH
            transformTh($th, fieldName);
            
            // Transformar o TD
            transformTd($td, fieldName);
        }
        
        function transformTh($th, fieldName) {
            // Aplicar estilos básicos ao TH
            $th.css({
                'font-size': '20px',
                'vertical-align': 'middle'
            });
            
            // Criar estrutura específica para cada campo
            let newContent = '';
            let subtitle = '';
            
            switch(true) {
                case fieldName.includes('Ativar Pesquisa'):
                    newContent = 'Ferramenta de Pesquisa de Domínios';
                    subtitle = 'Ative a ferramenta de pesquisa de domínios (Whois) no seu site.';
                    break;
                case fieldName.includes('Página de Resultados'):
                    newContent = 'Configuração de Resultados';
                    subtitle = 'Configure onde os resultados da pesquisa serão exibidos.';
                    break;
                case fieldName.includes('Tipo de Integração'):
                    newContent = 'Sistema de Integração';
                    subtitle = 'Escolha como integrar com seu sistema de vendas de domínios.';
                    break;
                case fieldName.includes('Custom URL'):
                    newContent = 'URL Personalizada';
                    subtitle = 'Configure um link personalizado para registro de domínios.';
                    break;
                case fieldName.includes('WHMCS'):
                    newContent = 'Integração WHMCS';
                    subtitle = 'Configure a integração com o sistema WHMCS.';
                    break;
                default:
                    newContent = fieldName || 'Configuração';
                    subtitle = 'Clique para executar ações de manutenção e diagnóstico.';
            }
            
            $th.html(`
                <label>${newContent}</label>
                <p style="font-weight: 400;">${subtitle}</p>
            `);
        }
        
        function transformTd($td, fieldName) {
            // Aplicar classe e estilos do WooCommerce
            $td.addClass('forminp forminp-radio').css({
                'display': 'flex',
                'flex-direction': 'column',
                'width': 'auto',
                'padding': '15px 25px',
                'background-color': 'rgb(255, 255, 255)',
                'border': '1px solid rgb(223, 223, 223)',
                'border-radius': '8px',
                'box-sizing': 'border-box',
                'margin-left': '20px'
            });
            
            // Obter conteúdo original
            const originalContent = $td.html();
            
            // Criar header e body
            const header = createHeader(fieldName, originalContent);
            const body = createBody(originalContent);
            
            // Substituir conteúdo
            $td.html(header + body);
        }
        
        function createHeader(fieldName, originalContent) {
            let headerText = '';
            let description = '';
            
            // Extrair descrição do conteúdo original
            const $temp = $('<div>').html(originalContent);
            const originalDesc = $temp.find('.description').text().trim();
            
            switch(true) {
                case fieldName.includes('Ativar Pesquisa'):
                    headerText = 'Ativar Pesquisa de Domínios (RDAP/WHOIS)';
                    description = 'Ofereça aos seus visitantes uma ferramenta para pesquisar a disponibilidade de domínios.';
                    break;
                case fieldName.includes('Página de Resultados'):
                    headerText = 'Página de Resultados da Pesquisa';
                    description = 'Selecione a página onde os resultados serão mostrados.';
                    break;
                case fieldName.includes('Tipo de Integração'):
                    headerText = 'Tipo de Integração';
                    description = 'Escolha entre Custom URL ou WHMCS para integrar com seu sistema.';
                    break;
                case fieldName.includes('Custom URL'):
                    headerText = 'Custom URL';
                    description = 'Configure um URL personalizado para registro de domínios.';
                    break;
                case fieldName.includes('WHMCS'):
                    headerText = 'WHMCS';
                    description = 'Configure a integração com o sistema WHMCS.';
                    break;
                default:
                    headerText = fieldName || 'Configuração';
                    description = originalDesc || 'Configure esta opção.';
            }
            
            return `
                <div class="woo-forminp-header" style="min-height: 44px;">
                    <p class="woo-forminp-header-text" style="font-weight: bold; padding-left: 6px;">${headerText}</p>
                    <span style="color: rgb(52, 59, 69); font-size: 13px; padding-left: 6px; display: block;">${description}</span>
                    <hr style="border-top: 1px solid rgb(221, 221, 221); border-right: none; border-bottom: none; border-left: none; border-image: initial; margin: 8px 0px;">
                </div>
            `;
        }
        
        function createBody(originalContent) {
            const $temp = $('<div>').html(originalContent);
            
            // Verificar se é fieldset com radio buttons
            const $fieldset = $temp.find('fieldset');
            if ($fieldset.length) {
                return createRadioBody($fieldset, $temp);
            }
            
            // Verificar se é select
            const $select = $temp.find('select');
            if ($select.length) {
                return createSelectBody($temp);
            }
            
            // Verificar se é input
            const $input = $temp.find('input[type="url"], input[type="text"]');
            if ($input.length) {
                return createInputBody($temp);
            }
            
            // Padrão para outros tipos
            return createDefaultBody($temp);
        }
        
        function createRadioBody($fieldset, $temp) {
            const $description = $temp.find('.description');
            const descText = $description.text().trim();
            
            // Transformar radio buttons
            const $radios = $fieldset.find('input[type="radio"]');
            let radioHtml = '<fieldset><ul>';
            
            $radios.each(function() {
                const $radio = $(this);
                const $label = $radio.closest('label');
                let labelText = $label.text().trim();
                
                // Personalizar textos dos radio buttons
                if (labelText.includes('Ativar')) {
                    labelText = 'Habilitar';
                } else if (labelText.includes('Desativar')) {
                    labelText = 'Desabilitar';
                }
                
                radioHtml += `
                    <li>
                        <label>
                            <input type="radio" name="${$radio.attr('name')}" value="${$radio.attr('value')}" ${$radio.is(':checked') ? 'checked="checked"' : ''} onchange="toggleFieldsVisibility()"> ${labelText}
                        </label>
                    </li>
                `;
            });
            
            radioHtml += '</ul></fieldset>';
            
            return `
                <div class="woo-forminp-body" style="display: flex; flex-direction: column; justify-content: center; padding: 0px 0px 10px 6px; min-height: 50px;">
                    ${radioHtml}
                    <p class="description" style="color: #646970;">${descText}</p>
                </div>
            `;
        }
        
        function createSelectBody($temp) {
            const $select = $temp.find('select');
            const $description = $temp.find('.description');
            const descText = $description.text().trim();
            
            // Remove a descrição original para evitar duplicação
            $description.remove();
            
            // Remove scripts também para evitar duplicação
            $temp.find('script').remove();
            
            $select.css({
                'width': '100%',
                'max-width': '400px',
                'padding': '8px 12px',
                'border': '1px solid #8C8F94',
                'border-radius': '4px'
            });
            
            // Adicionar onchange se for o select de tipo de integração
            if ($select.attr('id') === 'owh_rdap_integration_type') {
                $select.attr('onchange', 'toggleIntegrationFields()');
            }
            
            return `
                <div class="woo-forminp-body" style="display: flex; flex-direction: column; justify-content: center; padding: 0px 0px 10px 6px; min-height: 50px;">
                    ${$temp.html()}
                    <p class="description" style="color: #646970;">${descText}</p>
                </div>
            `;
        }
        
        function createInputBody($temp) {
            const $input = $temp.find('input[type="url"], input[type="text"]');
            const $description = $temp.find('.description');
            const descText = $description.text().trim();
            
            // Remove a descrição original para evitar duplicação
            $description.remove();
            
            // Remove elementos p extras que não sejam a descrição principal
            $temp.find('p').not('.description').remove();
            
            $input.css({
                'width': '100%',
                'max-width': '400px',
                'padding': '8px 12px',
                'border': '1px solid #8C8F94',
                'border-radius': '4px'
            });
            
            return `
                <div class="woo-forminp-body" style="display: flex; flex-direction: column; justify-content: center; padding: 0px 0px 10px 6px; min-height: 50px;">
                    ${$temp.html()}
                    <p class="description" style="color: #646970;">${descText}</p>
                </div>
            `;
        }
        
        function createDefaultBody($temp) {
            const $description = $temp.find('.description');
            const descText = $description.text().trim();
            
            // Remove a descrição original para evitar duplicação
            $description.remove();
            
            // Remove elementos p extras que não sejam a descrição principal
            $temp.find('p').not('.description').remove();
            
            return `
                <div class="woo-forminp-body" style="display: flex; flex-direction: column; justify-content: center; padding: 0px 0px 10px 6px; min-height: 50px;">
                    ${$temp.html()}
                    <p class="description" style="color: #646970;">${descText}</p>
                </div>
            `;
        }
        
        function setupFieldVisibility() {
            // Tornar as funções globais para uso com onchange
            window.toggleFieldsVisibility = function() {
                // Verificar se a pesquisa está ativada
                const searchEnabled = $('input[name="owh_rdap_enable_search"]:checked').val() === '1';
                
                if (searchEnabled) {
                    // Se ativado, mostrar todas as tabelas e linhas
                    $('.form-table').show();
                    $('.form-table tbody tr').show();
                    
                    // Controlar visibilidade baseada no tipo de integração
                    window.toggleIntegrationFields();
                } else {
                    // Se desativado, esconder todas as configurações exceto a que tem o campo "Ativar Pesquisa"
                    $('.form-table tbody tr').each(function() {
                        const $row = $(this);
                        const $th = $row.find('th');
                        const thText = $th.find('label').text().trim();
                        
                        // Se não contém "Ferramenta de Pesquisa de Domínios", esconder
                        if (!thText.includes('Ferramenta de Pesquisa de Domínios')) {
                            $row.hide();
                        } else {
                            $row.show();
                        }
                    });
                    
                    // Esconder tabelas que não contenham o campo de ativação
                    $('.form-table').each(function() {
                        const $table = $(this);
                        const hasEnableField = $table.find('input[name="owh_rdap_enable_search"]').length > 0;
                        
                        if (!hasEnableField) {
                            $table.hide();
                        } else {
                            $table.show();
                        }
                    });
                }
            };
            
            window.toggleIntegrationFields = function() {
                // Verificar o tipo de integração selecionado
                const integrationType = $('#owh_rdap_integration_type').val();
                
                // Encontrar as linhas específicas por conteúdo do th
                const $customUrlRow = findRowByThContent('URL Personalizada');
                const $whmcsRow = findRowByThContent('Integração WHMCS');
                
                // Encontrar as divs específicas também
                const $customUrlSection = $('#custom_url_section');
                const $whmcsSection = $('#whmcs_url_section');
                
                if (integrationType === 'custom') {
                    // Mostrar Custom URL, esconder WHMCS
                    $customUrlRow.show();
                    $whmcsRow.hide();
                    $customUrlSection.show();
                    $whmcsSection.hide();
                } else if (integrationType === 'whmcs') {
                    // Mostrar WHMCS, esconder Custom URL
                    $whmcsRow.show();
                    $customUrlRow.hide();
                    $whmcsSection.show();
                    $customUrlSection.hide();
                }
            };
            
            function findRowByThContent(content) {
                // Encontrar linha pelo conteúdo do th
                let $targetRow = $();
                $('.form-table tbody tr').each(function() {
                    const $row = $(this);
                    const $th = $row.find('th');
                    const thText = $th.find('label').text().trim();
                    
                    if (thText.includes(content)) {
                        $targetRow = $row;
                        return false; // Break do loop
                    }
                });
                return $targetRow;
            }
            
            // Executar visibilidade inicial
            window.toggleFieldsVisibility();
        }
    });
})(jQuery);
