/**
 * Custom Fields Management JavaScript
 * 
 * Handles functionality for custom fields configuration in OWH Domain WHOIS RDAP
 *
 * @since      1.0.0
 * @package    Owh_Domain_Whois_Rdap
 * @subpackage Owh_Domain_Whois_Rdap/admin/js
 */

(function($) {
    'use strict';

    let customFieldsGrid = null;
    let customFieldsData = [];
    let nextFieldId = 1;

    $(document).ready(function() {
        // Check if required variables are available
        if (typeof owhCustomFieldsAjax === 'undefined') {
            console.error('owhCustomFieldsAjax not found. Custom fields functionality may not work.');
            console.log('Available variables:', {
                ajaxurl: typeof ajaxurl,
                window_ajaxurl: typeof window.ajaxurl
            });
            showStatus('Erro: Configuração não encontrada. Recarregue a página.', 'error');
            return;
        }


        // Initialize custom fields grid when custom-fields tab is active
        if ($('#tab-custom-fields').length > 0) {
            initializeCustomFields();
        }

        // Re-initialize when custom-fields tab is clicked
        $(document).on('click', '.owh-tab[data-tab="custom-fields"]', function() {
            setTimeout(initializeCustomFields, 100); // Small delay to ensure tab is active
        });

        // Add new field button
        $(document).on('click', '#add-custom-field', addNewField);
        
        // Save custom fields button
        $(document).on('click', '#save-custom-fields', saveCustomFields);
    });

    /**
     * Initialize custom fields grid
     */
    function initializeCustomFields() {
        if (!$('#tab-custom-fields').hasClass('active')) {
            return;
        }

        // Check if grid container exists
        if (!$('#custom-fields-grid').length) {
            console.error('Grid container not found');
            showStatus('Erro: Container da tabela não encontrado', 'error');
            return;
        }

        loadCustomFields();
    }

    /**
     * Load custom fields from server
     */
    function loadCustomFields() {
        showStatus('Carregando campos...', 'info');

        // Check if ajaxurl is available
        if (typeof ajaxurl === 'undefined') {
            // Try to get from the localized variable
            if (typeof owhCustomFieldsAjax !== 'undefined' && owhCustomFieldsAjax.ajaxurl) {
                window.ajaxurl = owhCustomFieldsAjax.ajaxurl;
            } else {
                // Last resort fallback
                window.ajaxurl = '/wp-admin/admin-ajax.php';
            }
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'owh_load_custom_fields',
                nonce: owhCustomFieldsAjax.nonce
            },
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (response.success) {
                        customFieldsData = response.data || [];
                        
                        // Ensure each field has an ID
                        customFieldsData.forEach(function(field, index) {
                            if (!field.id) {
                                field.id = index + 1;
                            }
                        });
                        
                        // Set next ID
                        if (customFieldsData.length > 0) {
                            nextFieldId = Math.max(...customFieldsData.map(f => f.id || 0)) + 1;
                        }
                        
                        initializeGrid();
                        hideStatus();
                        
                        // Show welcome message if no fields
                        if (customFieldsData.length === 0) {
                            showWelcomeMessage();
                        }
                    } else {
                        showStatus('Nenhum campo configurado ainda. Clique em "Adicionar Campo" para começar.', 'info');
                        // Initialize empty grid anyway
                        customFieldsData = [];
                        initializeGrid();
                        showWelcomeMessage();
                    }
                } catch (e) {
                    console.error('Error parsing response:', e, response);
                    showStatus('Nenhum campo configurado ainda. Clique em "Adicionar Campo" para começar.', 'info');
                    // Initialize empty grid anyway
                    customFieldsData = [];
                    initializeGrid();
                    showWelcomeMessage();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details:', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    readyState: xhr.readyState,
                    statusText: xhr.statusText,
                    url: ajaxurl,
                    action: 'owh_load_custom_fields',
                    nonce: owhCustomFieldsAjax.nonce
                });
                
                // Check if it's actually a successful response but not JSON
                if (xhr.status === 200 && xhr.responseText) {
                    try {
                        // Try to parse the response
                        var response = JSON.parse(xhr.responseText);
                        
                        // Handle as success if we can parse it
                        if (response.success) {
                            customFieldsData = response.data || [];
                            initializeGrid();
                            hideStatus();
                            if (customFieldsData.length === 0) {
                                showWelcomeMessage();
                            }
                            return;
                        }
                    } catch (e) {
                        console.error('Failed to parse response:', e);
                    }
                }
                
                // Try to parse error response
                let errorMessage = 'Nenhum campo configurado ainda. Clique em "Adicionar Campo" para começar.';
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.data || errorMessage;
                    }
                } catch (e) {
                    console.log('Could not parse error response, using default message');
                }
                
                showStatus(errorMessage, 'info');
                // Initialize empty grid anyway
                customFieldsData = [];
                initializeGrid();
                showWelcomeMessage();
            }
        });
    }

    /**
     * Show welcome message when no fields exist
     */
    function showWelcomeMessage() {
        const $container = $('#custom-fields-grid');
        if ($container.length && customFieldsData.length === 0) {
            $container.html(
                '<div class="no-fields-message">' +
                '<h3>👋 Bem-vindo aos Campos Customizados!</h3>' +
                '<p>Ainda não há campos configurados. Clique no botão <strong>"Adicionar Campo"</strong> abaixo para criar seu primeiro campo personalizado.</p>' +
                '<p class="description">Os campos customizados permitem que você colete informações específicas de acordo com cada TLD (extensão de domínio).</p>' +
                '</div>'
            );
        }
    }

    /**
     * Initialize GridJS
     */
    function initializeGrid() {
        if (typeof gridjs === 'undefined' || !gridjs.Grid) {
            console.error('Grid.js not found. Make sure gridjs is loaded.');
            showStatus('Erro: Grid.js não carregado', 'error');
            return;
        }

        const { Grid, h } = gridjs;

        // Destroy existing grid if it exists
        if (customFieldsGrid) {
            customFieldsGrid.destroy();
        }

        // Prepare data for grid
        let gridData = [];
        if (customFieldsData && customFieldsData.length > 0) {
            gridData = customFieldsData.map(function(field) {
                return [field.label || '', field.regex || '', field.error_message || '', '', field.id];
            });
        }

        customFieldsGrid = new Grid({
            columns: [
                {
                    name: 'Label do Campo',
                    id: 'label',
                    sort: true,
                    width: '30%',
                    formatter: function(cell, row) {
                        const fieldId = row.cells[4].data; // ID is in the 5th column now
                        return h('input', {
                            type: 'text',
                            value: cell || '',
                            className: 'regular-text field-label',
                            placeholder: 'Ex: CPF, CNPJ, Passaporte...',
                            'data-field-id': fieldId,
                            onInput: function(e) {
                                updateFieldData(fieldId, 'label', e.target.value);
                            }
                        });
                    }
                },
                {
                    name: 'Regex de Validação (Opcional)',
                    id: 'regex',
                    sort: false,
                    width: '25%',
                    formatter: function(cell, row) {
                        const fieldId = row.cells[4].data; // ID is in the 5th column now
                        return h('input', {
                            type: 'text',
                            value: cell || '',
                            className: 'regular-text field-regex',
                            placeholder: 'Ex: ^[0-9]{11}$ (opcional)',
                            'data-field-id': fieldId,
                            onInput: function(e) {
                                updateFieldData(fieldId, 'regex', e.target.value);
                            }
                        });
                    }
                },
                {
                    name: 'Mensagem de Erro Personalizada',
                    id: 'error_message',
                    sort: false,
                    width: '30%',
                    formatter: function(cell, row) {
                        const fieldId = row.cells[4].data; // ID is in the 5th column now
                        return h('input', {
                            type: 'text',
                            value: cell || '',
                            className: 'regular-text field-error-message',
                            placeholder: 'Ex: Por favor, insira um CPF válido',
                            'data-field-id': fieldId,
                            onInput: function(e) {
                                updateFieldData(fieldId, 'error_message', e.target.value);
                            }
                        });
                    }
                },
                {
                    name: 'Ações',
                    id: 'actions',
                    sort: false,
                    width: '15%',
                    formatter: function(cell, row) {
                        const fieldId = row.cells[4].data; // ID is in the 5th column now
                        return h('div', { className: 'custom-field-actions' }, [
                            h('button', {
                                type: 'button',
                                className: 'button button-small button-secondary test-regex-btn',
                                'data-field-id': fieldId,
                                onClick: function() {
                                    testRegex(fieldId);
                                }
                            }, 'Testar'),
                            h('button', {
                                type: 'button',
                                className: 'button button-small button-link-delete remove-field-btn',
                                'data-field-id': fieldId,
                                onClick: function() {
                                    removeField(fieldId);
                                }
                            }, 'Remover')
                        ]);
                    }
                },
                {
                    name: 'ID',
                    id: 'id',
                    hidden: true
                }
            ],
            data: gridData,
            search: gridData.length > 0 ? true : false,
            sort: true,
            pagination: {
                limit: 10,
                summary: gridData.length > 0
            },
            language: {
                search: {
                    placeholder: 'Buscar campos...'
                },
                pagination: {
                    previous: 'Anterior',
                    next: 'Próximo',
                    to: 'até',
                    of: 'de',
                    results: 'resultados'
                },
                noRecordsFound: 'Nenhum campo configurado. Clique em "Adicionar Campo" para começar.'
            },
            className: {
                table: 'owh-custom-fields-table',
                header: 'owh-grid-header',
                footer: 'owh-grid-footer'
            }
        });

        try {
            customFieldsGrid.render(document.getElementById('custom-fields-grid'));
        } catch (error) {
            console.error('Error rendering grid:', error);
            showStatus('Erro ao renderizar tabela: ' + error.message, 'error');
        }
    }

    /**
     * Add a new field
     */
    function addNewField() {
        const newField = {
            id: nextFieldId++,
            label: '',
            regex: '',
            error_message: ''
        };

        customFieldsData.push(newField);
        
        // Clear welcome message if it exists
        $('#custom-fields-grid .no-fields-message').remove();
        
        initializeGrid();
        showStatus('Campo adicionado. Configure o label, regex e mensagem de erro.', 'success');
    }

    /**
     * Update field data
     */
    function updateFieldData(fieldId, property, value) {
        const field = customFieldsData.find(f => f.id === parseInt(fieldId));
        if (field) {
            field[property] = value;
        }
    }

    /**
     * Remove a field
     */
    function removeField(fieldId) {
        if (confirm('Tem certeza que deseja remover este campo?')) {
            customFieldsData = customFieldsData.filter(f => f.id !== parseInt(fieldId));
            initializeGrid();
            showStatus('Campo removido.', 'success');
        }
    }

    /**
     * Test regex validation
     */
    function testRegex(fieldId) {
        const field = customFieldsData.find(f => f.id === parseInt(fieldId));
        if (!field) {
            alert('Campo não encontrado.');
            return;
        }
        
        if (!field.regex || field.regex.trim() === '') {
            alert('Este campo não tem regex configurada. A regex é opcional - sem ela, qualquer valor será aceito.');
            return;
        }

        const testValue = prompt('Digite um valor para testar a regex "' + field.regex + '":');
        if (testValue !== null) {
            try {
                const regex = new RegExp(field.regex);
                const isValid = regex.test(testValue);
                
                if (isValid) {
                    alert('✓ Válido! O valor "' + testValue + '" passou na validação.');
                } else {
                    alert('✗ Inválido! O valor "' + testValue + '" não passou na validação.');
                }
            } catch (e) {
                alert('Erro na regex: ' + e.message);
            }
        }
    }

    /**
     * Save custom fields
     */
    function saveCustomFields() {
        // Validate fields before saving - only label is required
        const invalidFields = customFieldsData.filter(function(field) {
            return !field.label || field.label.trim() === '';
        });

        if (invalidFields.length > 0) {
            showStatus('Erro: Todos os campos devem ter um label configurado.', 'error');
            return;
        }

        // Test regex patterns that are provided (optional)
        const invalidRegex = customFieldsData.filter(function(field) {
            if (!field.regex || field.regex.trim() === '') {
                return false; // Empty regex is valid (optional)
            }
            
            try {
                new RegExp(field.regex);
                return false;
            } catch (e) {
                return true;
            }
        });

        if (invalidRegex.length > 0) {
            showStatus('Erro: Algumas regex são inválidas. Verifique e tente novamente.', 'error');
            return;
        }

        showStatus('Salvando campos...', 'info');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'owh_save_custom_fields',
                nonce: owhCustomFieldsAjax.nonce,
                fields: JSON.stringify(customFieldsData)
            },
            success: function(response) {
                if (response.success) {
                    showStatus('Campos salvos com sucesso!', 'success');
                } else {
                    showStatus('Erro ao salvar: ' + (response.data || 'Erro desconhecido'), 'error');
                }
            },
            error: function(xhr, status, error) {
                showStatus('Erro de conexão: ' + error, 'error');
            }
        });
    }

    /**
     * Show status message
     */
    function showStatus(message, type) {
        const $statusEl = $('#save-custom-fields-status');
        $statusEl.removeClass('notice-success notice-error notice-info')
                 .addClass('notice notice-' + type)
                 .html('<p>' + message + '</p>')
                 .show();

        if (type === 'success') {
            setTimeout(hideStatus, 3000);
        }
    }

    /**
     * Hide status message
     */
    function hideStatus() {
        $('#save-custom-fields-status').hide();
    }

})(jQuery);
