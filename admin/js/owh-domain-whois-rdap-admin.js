(function($) {
    'use strict';

    $(document).ready(function() {
        // Check if localized script is available
        if (typeof owh_rdap_admin === 'undefined') {
            console.error('owh_rdap_admin object not found! Script localization may have failed.');
            return;
        }
        
        // Tab Navigation System
        $('.owh-tab').on('click', function(e) {
            e.preventDefault();
            
            var targetTab = $(this).data('tab');
            
            // Remove active class from all tabs and panels
            $('.owh-tab').removeClass('nav-tab-active');
            $('.owh-tab-panel').removeClass('active').hide();
            
            // Add active class to clicked tab
            $(this).addClass('nav-tab-active');
            
            // Show corresponding panel with fade effect
            $('#tab-' + targetTab).addClass('active').fadeIn(300);
        });
        
        // Initialize tabs on page load
        function initializeTabs() {
            // Check if there's a hash in URL
            var hash = window.location.hash.substring(1);
            var targetTab = hash || 'general';
            
            // Activate the correct tab
            $('.owh-tab[data-tab="' + targetTab + '"]').trigger('click');
        }
        
        // Initialize tabs
        initializeTabs();
        
        // Load RDAP server status on page load
        loadRdapServerStatus();
        
        // Update URL hash when tab changes (optional - for bookmarkable URLs)
        $('.owh-tab').on('click', function() {
            var tabName = $(this).data('tab');
            if (history.pushState) {
                history.pushState(null, null, '#' + tabName);
            } else {
                window.location.hash = '#' + tabName;
            }
            
            // Load status when RDAP tab is activated
            if (tabName === 'rdap') {
                setTimeout(function() {
                    loadRdapServerStatus();
                }, 100);
            }
        });
        
        // Admin update RDAP servers button (using event delegation)
        $(document).on('click', '#update-rdap-servers', function() {
            var button = $(this);
            var status = $('#update-rdap-status');
            var originalText = button.text();
            
            // Check if localized object is available
            if (typeof owh_rdap_admin === 'undefined') {
                console.error('owh_rdap_admin not available - using fallback');
                status.show().html('<span style="color: #dc3232;">Erro: Configuração JavaScript não encontrada.</span>');
                return;
            }
            
            
            button.prop('disabled', true).text(owh_rdap_admin.strings.updating);
            status.removeClass('success error').addClass('loading').show()
                .html('<span style="color: #0073aa;">' + owh_rdap_admin.strings.updating + '</span>');
            
            $.ajax({
                url: owh_rdap_admin.rest_url + 'update-servers',
                type: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', owh_rdap_admin.nonce);
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    if (response.success) {
                        status.removeClass('loading error').addClass('success')
                            .html('<span style="color: #46b450;">' + response.message + '</span>');
                        
                        // Update the last update date in the description
                        var currentDate = new Date();
                        var formattedDate = currentDate.toLocaleDateString('pt-BR') + ' ' + currentDate.toLocaleTimeString('pt-BR');
                        var descriptionElement = $('.description').last();
                        var descriptionText = descriptionElement.html();
                        
                        // Replace the last update date in the description
                        var updatedText = descriptionText.replace(/Última atualização: [^.]+\./, 'Última atualização: ' + formattedDate + '.');
                        descriptionElement.html(updatedText);
                        
                        // Reload server status after successful update
                        setTimeout(function() {
                            loadRdapServerStatus();
                        }, 1000);
                    } else {
                        status.removeClass('loading success').addClass('error')
                            .html('<span style="color: #dc3232;">' + (response.message || owh_rdap_admin.strings.error) + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = '';
                    
                    if (status === 'timeout') {
                        errorMessage = 'Timeout: A atualização demorou mais que 30 segundos. Tente novamente.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        // Try to parse error from responseText
                        try {
                            var errorData = JSON.parse(xhr.responseText);
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            } else {
                                errorMessage = 'Erro na comunicação com o servidor: ' + xhr.responseText.substring(0, 100);
                            }
                        } catch (e) {
                            errorMessage = 'Erro na comunicação com o servidor (Status: ' + xhr.status + ')';
                        }
                    } else {
                        errorMessage = 'Erro desconhecido na atualização. Status: ' + xhr.status;
                    }
                    
                    $('#update-rdap-status').removeClass('loading success').addClass('error')
                        .html('<span style="color: #dc3232;">' + errorMessage + '</span>');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                    
                    // Hide status after 5 seconds
                    setTimeout(function() {
                        status.fadeOut();
                    }, 5000);
                }
            });
        });

        // Settings form validation
        $('form').on('submit', function(e) {
            var form = $(this);
            var hasError = false;

            // Validate buy button URL if provided
            var buyButtonUrl = $('#owh_domain_whois_rdap_buy_button_url');
            if (buyButtonUrl.length && buyButtonUrl.val()) {
                var urlPattern = /^https?:\/\/.+/;
                if (!urlPattern.test(buyButtonUrl.val())) {
                    buyButtonUrl.css('border-color', '#dc3232');
                    alert('Por favor, insira uma URL válida para o botão de compra (deve começar com http:// ou https://).');
                    hasError = true;
                } else {
                    buyButtonUrl.css('border-color', '');
                }
            }

            // Validate cache times
            $('.cache-time-input').each(function() {
                var input = $(this);
                var value = parseInt(input.val());
                if (isNaN(value) || value < 0) {
                    input.css('border-color', '#dc3232');
                    alert('Os tempos de cache devem ser números positivos.');
                    hasError = true;
                } else {
                    input.css('border-color', '');
                }
            });

            if (hasError) {
                e.preventDefault();
                return false;
            }
        });

        // Real-time URL validation
        $('#owh_domain_whois_rdap_buy_button_url').on('input', function() {
            var input = $(this);
            var value = input.val();
            
            if (value === '') {
                input.css('border-color', '');
                return;
            }
            
            var urlPattern = /^https?:\/\/.+/;
            if (urlPattern.test(value)) {
                input.css('border-color', '#46b450');
            } else {
                input.css('border-color', '#dc3232');
            }
        });

        // Template variable helpers
        $('.template-var').on('click', function() {
            var variable = $(this).data('var');
            var targetInput = $(this).data('target');
            
            if (targetInput && variable) {
                var input = $('#' + targetInput);
                var currentValue = input.val();
                var cursorPos = input[0].selectionStart;
                var newValue = currentValue.substring(0, cursorPos) + variable + currentValue.substring(cursorPos);
                
                input.val(newValue);
                input.focus();
                
                // Set cursor position after the inserted variable
                var newCursorPos = cursorPos + variable.length;
                input[0].setSelectionRange(newCursorPos, newCursorPos);
            }
        });
    });

    // Function to load RDAP server status
    function loadRdapServerStatus() {
        // Only load if the status elements exist (RDAP tab)
        if (!$('#file-status').length) {
            return;
        }
        
        // Check if localized object is available
        if (typeof owh_rdap_admin === 'undefined') {
            console.error('owh_rdap_admin not available in loadRdapServerStatus');
            $('#file-status').text('Erro: Configuração JavaScript não encontrada');
            return;
        }
        
        $('#file-status').text('Carregando...');
        $('#last-update').text('Verificando...');
        $('#file-size').text('-');
        
        $.ajax({
            url: owh_rdap_admin.rest_url + 'server-status',
            type: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', owh_rdap_admin.nonce);
            },
            success: function(response) {
                if (response.success && response.data) {
                    var data = response.data;
                    
                    // File status
                    if (data.has_file) {
                        var statusText = data.from_bundled ? 
                            'Arquivo incluído no plugin' : 
                            'Arquivo atualizado via download';
                        $('#file-status').html('<span style="color: #46b450;">✓ ' + statusText + '</span>');
                    } else {
                        $('#file-status').html('<span style="color: #dc3232;">✗ Arquivo não encontrado</span>');
                    }
                    
                    // Last update
                    if (data.last_modified) {
                        var date = new Date(data.last_modified * 1000);
                        $('#last-update').text(date.toLocaleString());
                    } else {
                        $('#last-update').text('Desconhecido');
                    }
                    
                    // File size
                    if (data.file_size > 0) {
                        var size = (data.file_size / 1024).toFixed(2) + ' KB';
                        $('#file-size').text(size);
                    } else {
                        $('#file-size').text('-');
                    }
                } else {
                    $('#file-status').html('<span style="color: #dc3232;">Erro ao carregar status</span>');
                }
            },
            error: function() {
                $('#file-status').html('<span style="color: #dc3232;">Erro ao carregar status</span>');
                $('#last-update').text('Erro');
                $('#file-size').text('-');
            }
        });
    }
    
    // RDAP Server Update Handler
    $(document).on('click', '#update-rdap-servers', function() {
        var button = $(this);
        var status = $('#update-rdap-status');
        
        // Get localized strings
        var strings = owh_rdap_admin.strings || {};
        var updatingText = strings.updating || 'Atualizando...';
        var originalText = button.text();
        
        button.prop('disabled', true).text(updatingText);
        status.html('<span style="color: #0073aa;">Atualizando lista de servidores RDAP...</span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'owh_update_rdap_servers',
                nonce: owh_rdap_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    status.html('<span style="color: #46b450;">Lista de servidores RDAP atualizada com sucesso!</span>');
                    // Reload server status after successful update
                    setTimeout(function() {
                        loadRdapServerStatus();
                    }, 1000);
                } else {
                    status.html('<span style="color: #dc3232;">Erro ao atualizar lista: ' + (response.data || 'Erro desconhecido') + '</span>');
                }
            },
            error: function() {
                status.html('<span style="color: #dc3232;">Erro de conexão ao atualizar lista.</span>');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
})(jQuery);
