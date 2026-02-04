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
                    width: '150px',
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
                }
            ],
            data: tldsData.map(item => [
                item.tld,
                item.selectedProvider || item.providers[0] || '',
                item.enabled || false
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
