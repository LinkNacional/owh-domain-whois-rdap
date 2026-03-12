/**
 * Domain name modification for WooCommerce Blocks checkout
 * 
 * @package OWH_Domain_WHOIS_RDAP
 * @since   1.0.0
 */

( function() {
    'use strict';

    // Wait for WooCommerce Blocks checkout to be available
    document.addEventListener( 'DOMContentLoaded', function() {
        if ( typeof window.wc === 'undefined' || typeof window.wc.blocksCheckout === 'undefined' ) {
            return;
        }

        const { registerCheckoutFilters } = window.wc.blocksCheckout;

        /**
         * Modify item name in blocks checkout and cart
         * 
         * @param {string} defaultValue - Original product name
         * @param {Object} extensions - Extensions data
         * @param {Object} args - Arguments containing cartItem and context
         * @return {string} Modified product name with select element
         */
        const changeItemName = ( defaultValue, extensions, args ) => {
            const { cartItem, context } = args;

            // Only modify in cart and checkout summary
            if ( context === 'cart' || context === 'summary' ) {
                
                if ( cartItem.extensions && 
                     cartItem.extensions.owh_domain_data && 
                     cartItem.extensions.owh_domain_data.product_type === 'domain' ) {
                    
                    const domainData = cartItem.extensions.owh_domain_data;
                    
                    // Tentar obter o nome do domínio de várias fontes
                    let domainName = domainData.domain_name || cartItem.name || defaultValue;
                    
                    if ( domainName && domainName.trim() !== '' ) {
                        // Get product ID for matrix lookup
                        const productId = cartItem.id;
                        
                        // Update domainData with the found domain name
                        const updatedDomainData = { ...domainData, domain_name: domainName };
                        
                        // Fetch pricing matrix from product meta
                        fetchPricingMatrix(productId, cartItem, updatedDomainData);
                        
                        // For now, return a loading state while we fetch the matrix
                        const currentPeriod = domainData.domain_period || 1;
                        const selectId = 'domain-period-select-' + cartItem.key;
                        
                        return `
                            <select 
                                id="${selectId}" 
                                class="domain-period-selector loading" 
                                data-cart-key="${cartItem.key}"
                                data-domain="${domainName}"
                                data-product-id="${productId}"
                                style="width: 100%; max-width: 300px; padding: 5px; border: 1px solid #ddd; border-radius: 4px;"
                                onchange="updateDomainPeriod(this)"
                            >
                                <option>Carregando opções de período...</option>
                            </select>
                        `;
                    }
                }
            }

            return defaultValue;
        };

        /**
         * Fetch pricing matrix from WordPress backend
         */
        async function fetchPricingMatrix(productId, cartItem, domainData) {
            try {
                const response = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'owh_get_domain_pricing_matrix',
                        'product_id': productId,
                        'nonce': window.owh_domain_nonce || ''
                    })
                });

                const data = await response.json();
                
                if (data.success && data.data && data.data.matrix) {
                    updateSelectWithMatrix(cartItem, domainData, data.data.matrix);
                } else {
                    showNoPricingMessage(cartItem, domainData, data.message || 'Matriz não configurada');
                }
            } catch (error) {
                console.error('Error fetching pricing matrix:', error);
                showNoPricingMessage(cartItem, domainData, 'Erro ao carregar preços');
            }
        }

        /**
         * Update select element with real pricing matrix
         */
        function updateSelectWithMatrix(cartItem, domainData, pricingMatrix) {
            const selectId = 'domain-period-select-' + cartItem.key;
            const selectElement = document.getElementById(selectId);
            
            if (!selectElement) {
                return;
            }

            const currentPeriod = domainData.domain_period || 1;
            let selectOptions = '';

            // Build options based on pricing matrix
            for (const [period, data] of Object.entries(pricingMatrix)) {

                const registerPrice = data.register;

                if (registerPrice && parseFloat(registerPrice) > 0) {

                    const periodNumber = parseInt(period);
                    const periodText = periodNumber === 1 ? '1 ano' : periodNumber + ' anos';
                    const selected = periodNumber === currentPeriod ? ' selected' : '';
                    const priceFormatted = 'R$ ' + parseFloat(registerPrice).toFixed(2).replace('.', ',');

                    const optionHtml = `<option value="${periodNumber}"${selected}>${domainData.domain_name} por ${periodText} (${priceFormatted})</option>`;
                    selectOptions += optionHtml;

                }
            }

            if (selectOptions === '') {
                console.warn('No valid options found in matrix');
                showNoPricingMessage(cartItem, domainData, 'Nenhum preço válido encontrado na matriz');
                return;
            }

            // Update select with new options
            selectElement.innerHTML = selectOptions;
            selectElement.classList.remove('loading');
        }

        /**
         * Show message when no pricing matrix is configured
         */
        function showNoPricingMessage(cartItem, domainData, message) {
            const selectId = 'domain-period-select-' + cartItem.key;
            const selectElement = document.getElementById(selectId);
            
            if (!selectElement) {
                console.error('Select element not found for no pricing message!');
                return;
            }

            // Show message indicating no pricing is configured
            const displayMessage = message || 'Configurar matriz de preços no produto';
            selectElement.innerHTML = `<option disabled>❌ ${displayMessage}</option>`;
            selectElement.classList.remove('loading');
            selectElement.disabled = true;
            selectElement.style.borderColor = '#dc3545';
            selectElement.style.color = '#dc3545';
            selectElement.style.backgroundColor = '#fff5f5';
        }

        // Register the filter for checkout blocks
        try {
            registerCheckoutFilters( 'owh-domain-whois-rdap', {
                itemName: changeItemName,
            });
        } catch (error) {
            console.warn('Erro ao registrar filtros do checkout por blocos:', error);
        }

        // Add global function to handle select changes
        window.updateDomainPeriod = function(selectElement) {
            const cartKey = selectElement.dataset.cartKey;
            const domainName = selectElement.dataset.domain;
            const productId = selectElement.dataset.productId;
            const newPeriod = parseInt(selectElement.value);
            
            // Show loading state
            selectElement.style.borderColor = '#007cba';
            selectElement.disabled = true;
            
            // Make AJAX call to update cart item with new period
            updateCartItemPeriod(cartKey, newPeriod, productId).then(response => {
                if (response.success) {
                    selectElement.style.borderColor = '#28a745';
                    
                    // Trigger cart refresh in WooCommerce Blocks
                    if (window.wp && window.wp.data) {
                        const { dispatch } = window.wp.data;
                        if (dispatch('wc/store/cart')) {
                            dispatch('wc/store/cart').invalidateResolutionForStoreSelector('getCartData');
                        }
                    }
                } else {
                    console.error('Erro ao atualizar carrinho:', response.message);
                    selectElement.style.borderColor = '#dc3545';
                }
            }).catch(error => {
                console.error('Erro AJAX:', error);
                selectElement.style.borderColor = '#dc3545';
            }).finally(() => {
                selectElement.disabled = false;
                selectElement.style.borderColor = '#ddd';
            });
        };

        /**
         * Update cart item period via AJAX
         */
        async function updateCartItemPeriod(cartKey, newPeriod, productId) {
            try {
                const response = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'owh_update_domain_period',
                        'cart_key': cartKey,
                        'new_period': newPeriod,
                        'product_id': productId,
                        'nonce': window.owh_domain_nonce || ''
                    })
                });

                return await response.json();
            } catch (error) {
                throw new Error('Network error: ' + error.message);
            }
        }

        // Add CSS styles for the select
        const selectStyles = `
            .domain-period-selector {
                font-size: 14px;
                font-family: inherit;
                background: white;
                cursor: pointer;
            }
            .domain-period-selector:focus {
                outline: 2px solid #007cba;
                border-color: #007cba;
            }
            .domain-period-selector:hover {
                border-color: #999;
            }
        `;
        
        const styleSheet = document.createElement('style');
        styleSheet.textContent = selectStyles;
        document.head.appendChild(styleSheet);

    });

    /**
     * Observer for custom error messages in WooCommerce Blocks
     * Replaces default validation messages with custom configured messages
     */
    function initCustomErrorMessageObserver() {
        // Store custom error messages
        let customErrorMessages = {};
        
        // Fetch custom field configurations
        function fetchCustomFieldConfigs() {
            // Try REST API first, then fallback to AJAX
            const restUrl = window.wpApiSettings?.root || '/wp-json/';
            const apiUrl = restUrl + 'owh-rdap/v1/custom-field-configs';
            
            fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    // Build error message map
                    data.data.forEach(function(field) {
                        if (field.id && field.error_message && field.error_message.trim() !== '') {
                            customErrorMessages['validate-error-order_owh-domain-whois-rdap/custom-field-' + field.id] = field.error_message;
                        }
                    });
                } else {
                    console.log('REST API response format unexpected:', data);
                }
            })
            .catch(error => {
                console.log('REST API failed, trying AJAX fallback:', error);
                
                // Fallback to AJAX if available
                if (window.owh_ajax_url && window.owh_domain_nonce) {
                    const formData = new FormData();
                    formData.append('action', 'owh_get_custom_field_configs');
                    formData.append('nonce', window.owh_domain_nonce);
                    
                    fetch(window.owh_ajax_url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            data.data.forEach(function(field) {
                                if (field.id && field.error_message && field.error_message.trim() !== '') {
                                    customErrorMessages['validate-error-order_owh-domain-whois-rdap/custom-field-' + field.id] = field.error_message;
                                }
                            });
                        }
                    })
                    .catch(ajaxError => {
                        console.log('Both REST API and AJAX failed:', ajaxError);
                    });
                } else {
                    console.log('No AJAX configuration available for fallback');
                }
            });
        }
        
        // Create mutation observer to watch for validation errors
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if this is a validation error div
                        if (node.classList && node.classList.contains('wc-block-components-validation-error')) {
                            replaceErrorMessage(node);
                        }
                        
                        // Also check child elements
                        const errorDivs = node.querySelectorAll && node.querySelectorAll('.wc-block-components-validation-error');
                        if (errorDivs && errorDivs.length > 0) {
                            errorDivs.forEach(replaceErrorMessage);
                        }
                    }
                });
            });
        });
        
        // Function to replace error message
        function replaceErrorMessage(errorDiv) {
            const errorP = errorDiv.querySelector('p[id^="validate-error-order_owh-domain-whois-rdap/custom-field-"]');
            if (errorP) {
                const fieldId = errorP.id;
                
                if (customErrorMessages[fieldId]) {
                    const span = errorP.querySelector('span');
                    if (span) {
                        const currentText = span.textContent.trim();
                        
                        // Check if the message has already been modified (to avoid duplicating)
                        if (!currentText.startsWith(customErrorMessages[fieldId])) {
                            // List of patterns that should be prefixed with custom messages
                            const replaceablePatterns = [
                                'Please match the requested format.',
                                'This field is invalid.',
                                'The field format is invalid.',
                                'Invalid format.',
                                /critérios?\s+necessários?/i,
                                /não\s+atende/i,
                                /format?\s+(required|invalid)/i,
                                /required?\s+format/i,
                                /invalid\s+input/i,
                                /formato\s+(inválido|incorreto)/i
                            ];
                            
                            const shouldModify = replaceablePatterns.some(pattern => {
                                if (pattern instanceof RegExp) {
                                    return pattern.test(currentText);
                                }
                                return currentText === pattern || currentText.includes(pattern);
                            });
                            
                            if (shouldModify) {
                                // Prepend custom message to the existing message
                                const newMessage = customErrorMessages[fieldId] + ' ' + currentText;
                                span.textContent = newMessage;
                            } else {
                                console.log('Error message doesn\'t match replacement patterns:', currentText);
                            }
                        } else {
                            console.log('Message already contains custom prefix, skipping');
                        }
                    }
                } else {
                    console.log('No custom message found for field ID:', fieldId);
                }
            }
        }
        
        // Start observing when DOM is ready
        function startObserving() {
            fetchCustomFieldConfigs();
            
            // Start observing the document for changes
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Also check existing error messages
            setTimeout(() => {
                const existingErrors = document.querySelectorAll('.wc-block-components-validation-error');
                existingErrors.forEach(replaceErrorMessage);
            }, 100);
        }
        
        // Initialize when checkout is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startObserving);
        } else {
            startObserving();
        }
        
        // Re-fetch configs when checkout updates (in case of AJAX)
        document.addEventListener('updated_checkout', function() {
            setTimeout(fetchCustomFieldConfigs, 500);
        });
        
        // Also listen for form validation events
        document.addEventListener('change', function(e) {
            if (e.target && e.target.name && e.target.name.includes('owh_domain_custom_field_')) {
                setTimeout(() => {
                    const errorDivs = document.querySelectorAll('.wc-block-components-validation-error');
                    errorDivs.forEach(replaceErrorMessage);
                }, 100);
            }
        });
    }
    
    // Initialize the observer
    initCustomErrorMessageObserver();

})();