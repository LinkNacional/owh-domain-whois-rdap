/**
 * TLDs Grid Configuration using Grid.js
 *
 * @package    Owh_Domain_Whois_Rdap
 * @since      1.0.0
 */

(function($) {
    'use strict';

    let tldsGrid;
    let tldsData = [];

    /**
     * Initialize the TLDs grid
     */
    function initTldsGrid() {
        // Check if grid container exists
        const gridContainer = document.getElementById('tlds-grid');
        if (!gridContainer) {
            return;
        }

        // Load TLDs data and initialize grid
        loadTldsData()
            .then(function(data) {
                tldsData = data;
                createGrid();
            })
            .catch(function(error) {
                console.error('Error loading TLDs data:', error);
                showStatus('Erro ao carregar dados de TLDs: ' + error.message, 'error');
            });
    }

    /**
     * Load TLDs data from DNS JSON and saved configuration
     */
    function loadTldsData() {
        return new Promise(function(resolve, reject) {
            fetch('/wp-json/owh-rdap/v1/tlds-config', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': owh_rdap_admin.rest_nonce
                }
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    resolve(data.data);
                } else {
                    reject(new Error(data.message || 'Erro ao carregar configurações'));
                }
            })
            .catch(function(error) {
                reject(error);
            });
        });
    }

    /**
     * Create the Grid.js instance
     */
    function createGrid() {
        // Check if Grid.js is available
        if (typeof gridjs === 'undefined' || !gridjs.Grid) {
            console.error('Grid.js not found. Make sure gridjs is loaded.');
            showStatus('Erro: Grid.js não carregado', 'error');
            return;
        }

        const { Grid, h } = gridjs;

        tldsGrid = new Grid({
            columns: [
                {
                    name: 'TLD',
                    id: 'tld',
                    sort: true,
                    width: '200px'
                },
                {
                    name: 'Provedor RDAP',
                    id: 'provider',
                    sort: true,
                    formatter: function(cell, row) {
                        const tld = row.cells[0].data;
                        const providers = tldsData.find(item => item.tld === tld)?.providers || [];
                        
                        if (providers.length === 0) {
                            return 'Nenhum provedor disponível';
                        }
                        
                        if (providers.length === 1) {
                            return providers[0];
                        }
                        
                        return h('select', {
                            className: 'provider-select',
                            'data-tld': tld,
                            onchange: function(e) {
                                updateTldProvider(tld, e.target.value);
                            }
                        }, providers.map(provider => 
                            h('option', {
                                value: provider,
                                selected: cell === provider
                            }, provider)
                        ));
                    }
                },
                {
                    name: 'Permitir Pesquisa',
                    id: 'enabled',
                    sort: true,
                    width: '120px',
                    formatter: function(cell, row) {
                        const tld = row.cells[0].data;
                        return h('input', {
                            type: 'checkbox',
                            className: 'tld-enabled-checkbox',
                            'data-tld': tld,
                            checked: cell,
                            onchange: function(e) {
                                updateTldEnabled(tld, e.target.checked);
                            }
                        });
                    }
                },
                {
                    name: 'Produto',
                    id: 'product',
                    sort: false,
                    width: '180px',
                    formatter: function(cell, row) {
                        const tld = row.cells[0].data;
                        const sanitizedTld = tld.replace('.', '');
                        return gridjs.html(`
                            <div class="product-actions" data-tld="${tld}">
                                <button type="button" 
                                        class="button button-small button-primary convert-to-product-btn" 
                                        data-tld="${tld}">
                                    Converter para Produto
                                </button>
                                <div id="product-status-${sanitizedTld}" class="product-status"></div>
                            </div>
                        `);
                    }
                }
            ],
            data: tldsData.map(item => [
                item.tld,
                item.selectedProvider || item.providers[0] || '',
                item.enabled || false,
                '' // Placeholder for product actions
            ]),
            search: {
                enabled: true,
                selector: (cell, rowIndex, cellIndex) => {
                    // Only search in the first column (TLD column, cellIndex = 0)
                    return cellIndex === 0 ? cell : '';
                }
            },
            pagination: {
                limit: 20,
                summary: true
            },
            sort: true,
            resizable: true,
            className: {
                table: 'owh-tlds-table',
                th: 'owh-tlds-th',
                td: 'owh-tlds-td'
            },
            language: {
                search: {
                    placeholder: 'Buscar TLD...'
                },
                pagination: {
                    previous: 'Anterior',
                    next: 'Próximo',
                    showing: 'Mostrando',
                    results: function() {
                        return 'resultados';
                    },
                    of: 'de',
                    to: 'até'
                },
                sort: {
                    sortAsc: 'Ordenar A-Z',
                    sortDesc: 'Ordenar Z-A'
                }
            }
        });

        tldsGrid.render(document.getElementById('tlds-grid'));
        
        // Add event handler for convert buttons after grid is rendered
        $(document).off('click', '.convert-to-product-btn').on('click', '.convert-to-product-btn', function(e) {
            e.preventDefault();
            const tld = $(this).data('tld');
            convertTldToProduct(tld);
        });
        
        // Load product status after grid is rendered
        loadProductStatus();
    }

    /**
     * Load product status for all TLDs
     */
    function loadProductStatus() {
        const allTlds = tldsData.map(item => item.tld);
        
        jQuery.post(owh_admin_ajax.ajax_url, {
            action: 'owh_check_tld_product_status',
            nonce: owh_admin_ajax.nonce,
            tlds: allTlds
        })
        .done(function(response) {
            if (response.success && response.data) {
                updateProductButtons(response.data);
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error loading product status:', error);
        });
    }

    /**
     * Update product buttons based on status
     */
    function updateProductButtons(productStatus) {
        Object.keys(productStatus).forEach(function(tld) {
            const sanitizedTld = tld.replace('.', '');
            const button = $(`.convert-to-product-btn[data-tld="${tld}"]`);
            const statusDiv = $(`#product-status-${sanitizedTld}`);
            
            if (productStatus[tld].exists) {
                // Product exists - show "Product Created" state
                button.text('Produto Criado')
                      .removeClass('button-primary')
                      .addClass('button-secondary')
                      .prop('disabled', true);
                      
                statusDiv.html(`<small><a href="${productStatus[tld].edit_url}" target="_blank">Editar produto</a></small>`);
            } else {
                // Product doesn't exist - show "Convert" button
                button.text('Converter para Produto')
                      .removeClass('button-secondary')
                      .addClass('button-primary')
                      .prop('disabled', false);
                      
                statusDiv.empty();
            }
        });
    }

    /**
     * Update TLD provider selection
     */
    function updateTldProvider(tld, provider) {
        const tldIndex = tldsData.findIndex(item => item.tld === tld);
        if (tldIndex !== -1) {
            tldsData[tldIndex].selectedProvider = provider;
        }
    }

    /**
     * Update TLD enabled status
     */
    function updateTldEnabled(tld, enabled) {
        const tldIndex = tldsData.findIndex(item => item.tld === tld);
        if (tldIndex !== -1) {
            tldsData[tldIndex].enabled = enabled;
        }
    }

    /**
     * Save TLDs configuration - only sends disabled TLDs to optimize database
     */
    function saveTldsConfig() {
        // Only include disabled TLDs to optimize database storage
        const configData = tldsData
            .filter(item => !item.enabled) // Only disabled TLDs
            .map(item => ({
                tld: item.tld,
                provider: item.selectedProvider || item.providers[0] || '',
                enabled: false
            }));

        showStatus('Salvando configurações...', 'info');

        fetch('/wp-json/owh-rdap/v1/tlds-config', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': owh_rdap_admin.rest_nonce
            },
            body: JSON.stringify({
                config: configData
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                showStatus('Configurações salvas com sucesso!', 'success');
            } else {
                showStatus('Erro ao salvar: ' + (data.message || 'Erro desconhecido'), 'error');
            }
        })
        .catch(function(error) {
            showStatus('Erro na requisição: ' + error.message, 'error');
        });
    }

    /**
     * Show status message
     */
    function showStatus(message, type) {
        const statusDiv = $('#save-tlds-status');
        statusDiv.removeClass('success error info')
                 .addClass(type)
                 .text(message)
                 .show();

        if (type === 'success' || type === 'error') {
            setTimeout(function() {
                statusDiv.fadeOut();
            }, 3000);
        }
    }

    /**
     * Convert TLD to WooCommerce Product
     */
    function convertTldToProduct(tld) {
        const button = $(`.convert-to-product-btn[data-tld="${tld}"]`);
        const sanitizedTld = tld.replace('.', '');
        const statusDiv = $(`#product-status-${sanitizedTld}`);
        
        // Check if button is already disabled (product already exists or conversion in progress)
        if (button.prop('disabled')) {
            return;
        }
        
        // Store original state
        const originalText = button.text();
        const originalClass = button.hasClass('button-primary') ? 'button-primary' : 'button-secondary';
        
        // Disable button and show loading state
        button.prop('disabled', true)
              .text('Criando...')
              .removeClass('button-primary button-secondary')
              .addClass('button-secondary');
        
        statusDiv.html('<small>Criando produto...</small>');
        
        // Make AJAX request to create product
        jQuery.post(owh_admin_ajax.ajax_url, {
            action: 'owh_convert_tld_to_product',
            nonce: owh_admin_ajax.nonce,
            tld: tld
        })
        .done(function(response) {
            if (response.success) {
                // Success - product created
                showStatus(`Produto criado com sucesso para ${tld}!`, 'success');
                button.text('Produto Criado')
                      .removeClass('button-primary')
                      .addClass('button-secondary');
                
                statusDiv.html(`<small><a href="${response.data.edit_url}" target="_blank">Editar produto</a></small>`);
                
                // Keep button disabled permanently
                
            } else {
                // Handle different types of errors
                if (response.data && response.data.product_exists) {
                    // Product already exists
                    showStatus(`Produto já existe para ${tld}`, 'info');
                    button.text('Produto Criado')
                          .removeClass('button-primary')
                          .addClass('button-secondary');
                    
                    statusDiv.html(`<small><a href="${response.data.edit_url}" target="_blank">Editar produto</a></small>`);
                    
                    // Keep button disabled
                } else {
                    // Other errors - restore button state
                    showStatus(`Erro ao criar produto para ${tld}: ` + (response.data || 'Erro desconhecido'), 'error');
                    button.prop('disabled', false)
                          .text(originalText)
                          .removeClass('button-primary button-secondary')
                          .addClass(originalClass);
                    
                    statusDiv.empty();
                }
            }
        })
        .fail(function(xhr, status, error) {
            // Network/server error - restore button state
            showStatus(`Erro na requisição: ${error}`, 'error');
            button.prop('disabled', false)
                  .text(originalText)
                  .removeClass('button-primary button-secondary')
                  .addClass(originalClass);
            
            statusDiv.empty();
        });
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Check if we're on the integration tab
        if ($('#tab-integration').length > 0) {
            // Initialize grid when integration tab is shown
            initTldsGrid()

            // Save button click handler
            $(document).on('click', '#save-tlds-config', saveTldsConfig);
        }
    });

})(jQuery);
