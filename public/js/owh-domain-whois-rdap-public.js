(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Handle example domain clicks
        $(document).on('click', '.owh-rdap-example-domain', function(e) {
            e.preventDefault();
            var domain = $(this).data('domain');
            $('#owh-rdap-domain-input').val(domain).focus();
        });

        // Handle domain period selection changes in cart/checkout
        $(document).on('change', '.owh-domain-period-selector', function(e) {
            e.preventDefault();
            
            var $select = $(this);
            var cartKey = $select.data('cart-key');
            var productId = $select.data('product-id');
            var newPeriod = $select.val();
            var $loading = $select.siblings('.owh-domain-loading');
            var $wrapper = $select.closest('.owh-domain-period-wrapper');
            
            // Show loading
            $loading.show();
            $select.prop('disabled', true);
            
            // Make AJAX request to update period
            $.ajax({
                url: owhRdapPublic.ajax_url,
                type: 'POST',
                data: {
                    action: 'owh_update_domain_period',
                    nonce: owhRdapPublic.nonce,
                    cart_key: cartKey,
                    product_id: productId,
                    new_period: newPeriod
                },
                success: function(response) {
                    if (response.success) {
                        // Update the displayed price in the select option
                        var $selectedOption = $select.find('option:selected');
                        
                        // Reload checkout fragments to update totals
                        if (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.is_checkout) {
                            $('body').trigger('update_checkout');
                        } else if (typeof wc_cart_params !== 'undefined') {
                            // For cart page
                            $('[name="update_cart"]').trigger('click');
                        } else {
                            // Fallback: reload page
                            location.reload();
                        }
                    } else {
                        alert('Erro ao atualizar período: ' + (response.data || 'Erro desconhecido'));
                        // Reset select to previous value
                        $select.val($select.data('previous-value'));
                    }
                },
                error: function() {
                    alert('Erro de conexão ao atualizar período');
                    // Reset select to previous value
                    $select.val($select.data('previous-value'));
                },
                complete: function() {
                    // Hide loading and re-enable select
                    $loading.hide();
                    $select.prop('disabled', false);
                }
            });
        });

        // Store previous value when select gets focus
        $(document).on('focus', '.owh-domain-period-selector', function() {
            $(this).data('previous-value', $(this).val());
        });

        // Real-time domain validation
        $('#owh-rdap-domain-input').on('input', function() {
            var input = $(this);
            var domain = input.val().trim();
            var submitButton = $('#owh-rdap-search-button');
            
            if (domain === '') {
                input.css('border-color', '');
                submitButton.prop('disabled', false);
                return;
            }
            
            if (isValidDomainFormat(domain)) {
                input.css('border-color', '#46b450');
                submitButton.prop('disabled', false);
            } else {
                input.css('border-color', '#dc3232');
                submitButton.prop('disabled', false); // Don't disable, let server validation handle it
            }
        });

        function isValidDomainFormat(domain) {
            // Basic domain validation regex
            var domainRegex = /^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/;
            
            // Check basic format
            if (!domainRegex.test(domain)) {
                return false;
            }
            
            // Check if it has at least one dot
            if (domain.indexOf('.') === -1) {
                return false;
            }
            
            // Check length
            if (domain.length > 253) {
                return false;
            }
            
            return true;
        }

        // Auto-focus on domain input when page loads
        $('#owh-rdap-domain-input').focus();

        // Handle page unload to show loading for redirected searches
        $(window).on('beforeunload', function() {
            var form = $('#owh-rdap-search-form');
            var loadingOverlay = $('#owh-rdap-loading-overlay');
            
            if (form.length > 0 && form.attr('action') && form.attr('action') !== '') {
                // If form has an action URL, show loading during page transition
                if (loadingOverlay.length > 0) {
                    loadingOverlay.show();
                }
            }
        });

        // Hide loading on page load (in case it was left visible)
        $(window).on('load', function() {
            var loadingOverlay = $('#owh-rdap-loading-overlay');
            if (loadingOverlay.length > 0) {
                loadingOverlay.hide();
            }
        });

        // Enter key handling for better UX
        $('#owh-rdap-domain-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $('#owh-rdap-search-form').submit();
            }
        });

        // Handle form submission with loading overlay
        $(document).on('submit', '#owh-rdap-search-form', function(e) {
            var form = $(this);
            var domainInput = $('#owh-rdap-domain-input');
            var domain = domainInput.val().trim();
            var searchButton = $('#owh-rdap-search-button');
            var searchText = searchButton.find('.owh-rdap-search-text');
            var searchLoading = searchButton.find('.owh-rdap-search-loading');
            var loadingOverlay = $('#owh-rdap-loading-overlay');
            
            // Basic validation
            if (domain === '') {
                e.preventDefault();
                domainInput.focus();
                return false;
            }
            
            // Show loading states
            showSearchLoading(searchButton, searchText, searchLoading, loadingOverlay);
            
            // If this is a same-page search (no results page configured), handle with AJAX
            if (!form.attr('action') || form.attr('action') === '') {
                e.preventDefault();
                handleAjaxSearch(domain, searchButton, searchText, searchLoading, loadingOverlay);
                return false;
            }
            
            // For normal form submission to results page, show loading until page changes
            // The loading will be hidden when the new page loads
        });

        function showSearchLoading(searchButton, searchText, searchLoading, loadingOverlay) {
            // Button loading state with smooth transition
            searchText.addClass('hidden');
            searchLoading.addClass('active').show();
            searchButton.prop('disabled', true);
            
            // Add searching class to input
            $('#owh-rdap-domain-input').addClass('searching').prop('readonly', true);
            
            // Show overlay if it exists
            if (loadingOverlay.length > 0) {
                loadingOverlay.fadeIn(300);
            }
        }

        function hideSearchLoading(searchButton, searchText, searchLoading, loadingOverlay) {
            // Button normal state with smooth transition
            searchLoading.removeClass('active');
            setTimeout(function() {
                searchLoading.hide();
                searchText.removeClass('hidden');
            }, 300);
            searchButton.prop('disabled', false);
            
            // Remove searching class from input
            $('#owh-rdap-domain-input').removeClass('searching').prop('readonly', false);
            
            // Hide overlay if it exists
            if (loadingOverlay.length > 0) {
                loadingOverlay.fadeOut(300);
            }
        }

        function handleAjaxSearch(domain, searchButton, searchText, searchLoading, loadingOverlay) {
            var resultsContainer = $('#owh-rdap-search-results');
            
            // Show results container if hidden
            if (resultsContainer.length > 0 && resultsContainer.is(':hidden')) {
                resultsContainer.show();
            }
            
            // Setup dynamic loading messages
            var loadingMessages = [
                'Verificando disponibilidade...',
                'Consultando registros RDAP...',
                'Analisando status do domínio...',
                'Finalizando consulta...'
            ];
            var currentMessageIndex = 0;
            var messageInterval;
            
            // Update loading message if overlay exists
            if (loadingOverlay.length > 0) {
                var loadingMessage = loadingOverlay.find('.owh-rdap-loading-message');
                if (loadingMessage.length > 0) {
                    messageInterval = setInterval(function() {
                        currentMessageIndex = (currentMessageIndex + 1) % loadingMessages.length;
                        loadingMessage.fadeOut(200, function() {
                            $(this).text(loadingMessages[currentMessageIndex]).fadeIn(200);
                        });
                    }, 2000);
                }
            }
            
            // Make AJAX request (this would need to be implemented in the PHP side)
            $.ajax({
                url: owhRdapPublic.ajax_url || ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'owh_rdap_search_domain',
                    domain: domain,
                    nonce: owhRdapPublic.nonce || ''
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    if (response.success && resultsContainer.length > 0) {
                        resultsContainer.html(response.data.html || '<p>Resultado da pesquisa para: ' + domain + '</p>');
                    } else {
                        if (resultsContainer.length > 0) {
                            resultsContainer.html('<div class="owh-rdap-result-error">Erro ao processar a pesquisa. Tente novamente.</div>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    if (resultsContainer.length > 0) {
                        var errorMessage = 'Erro de conexão. ';
                        if (status === 'timeout') {
                            errorMessage += 'A pesquisa está demorando mais que o esperado. Tente novamente.';
                        } else {
                            errorMessage += 'Verifique sua conexão e tente novamente.';
                        }
                        resultsContainer.html('<div class="owh-rdap-result-error">' + errorMessage + '</div>');
                    }
                },
                complete: function() {
                    // Clear message interval
                    if (messageInterval) {
                        clearInterval(messageInterval);
                    }
                    hideSearchLoading(searchButton, searchText, searchLoading, loadingOverlay);
                }
            });
        }

    });

    // Initialize checkout field interception when page loads
    $(window).load(function () {
        const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
        if (placeOrderButton) {
            // Intercepta o fetch para /wc/store/v1/checkout
            const originalFetch = window.fetch;
            
            window.fetch = async (input, init) => {
                if (typeof input === 'string' && input.includes('/wc/store/v1/checkout')) {
                    // Clona o payload existente
                    const body = JSON.parse(init.body);
                    
                    // Busca por campos com prefixo owh-domain-whois-rdap/custom-field-
                    document.querySelectorAll('[id^="order-owh-domain-whois-rdap-custom-field-"]').forEach(function(field) {
                        if (field.value) {
                            // Converte o id do campo para o nome correto
                            const fieldName = field.id.replace('order-', '');
                            
                            // Adiciona cada campo individualmente ao payment_data
                            body.payment_data.push({
                                'key': fieldName,
                                'value': field.value
                            });
                        }
                    });

                    // Recria o init com o payload modificado
                    init.body = JSON.stringify(body);
                }
                return originalFetch(input, init);
            };
        }

        // Fallback para checkout clássico (legacy form)
        const legacyForm = document.querySelector('.checkout.woocommerce-checkout');
        if (legacyForm) {
            let originalXHROpen = XMLHttpRequest.prototype.open;
            let originalXHRSend = XMLHttpRequest.prototype.send;
          
            XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
                this._requestURL = url; // Armazena a URL da requisição
                originalXHROpen.apply(this, arguments);
            };
          
            XMLHttpRequest.prototype.send = function (body) {
                if (this._requestURL && this._requestURL.includes('?wc-ajax=checkout')) {
                    let xhr = this; // Armazena referência ao objeto XMLHttpRequest
          
                    // Adiciona os campos customizados ao corpo da requisição
                    let newBody = new URLSearchParams(body);
                    
                    // Coleta todos os campos customizados de domínio
                    document.querySelectorAll('[name^="owh-domain-whois-rdap/custom-field-"], [name^="owh_domain_custom_field_"]').forEach(function(field) {
                        if (field.value) {
                            newBody.append(field.name, field.value);
                        }
                    });
                    
                    body = newBody.toString();
                    originalXHRSend.call(xhr, body);
                } else {
                    originalXHRSend.apply(this, arguments);
                }
            };
        }
    });

})(jQuery);
