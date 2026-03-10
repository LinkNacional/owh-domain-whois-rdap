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

        // Enter key handling for better UX
        $('#owh-rdap-domain-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $('#owh-rdap-search-form').submit();
            }
        });

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
