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
                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                <span style="font-weight: 500; color: #333;">${domainName} por</span>
                                <select 
                                    id="${selectId}" 
                                    class="domain-period-selector loading" 
                                    data-cart-key="${cartItem.key}"
                                    data-domain="${domainName}"
                                    data-product-id="${productId}"
                                    style="width: fit-content; padding: 5px; border: 1px solid #ddd; border-radius: 4px;"
                                    onchange="updateDomainPeriod(this)"
                                >
                                    <option>Carregando opções de período...</option>
                                </select>
                            </div>
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

                    const optionHtml = `<option value="${periodNumber}"${selected}>${periodText} (${priceFormatted})</option>`;
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
                    // Build error message map - store by field.id for flexible matching
                    data.data.forEach(function(field) {
                        if (field.id && field.error_message && field.error_message.trim() !== '') {
                            // Store just the field ID and error message for flexible matching
                            customErrorMessages[field.id] = field.error_message;
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
                                    // Store just the field ID and error message for flexible matching
                                    customErrorMessages[field.id] = field.error_message;
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
            // Look for any error paragraph that contains our custom field pattern
            const errorP = errorDiv.querySelector('p[id*="owh-domain-whois-rdap/custom-field-"]');
            if (errorP) {
                const fullFieldId = errorP.id;
                let fieldId = null;
                
                const matches = fullFieldId.match(/custom-field-(?:[^-]+-)?(\d+)$/);
                if (matches && matches[1]) {
                    fieldId = matches[1];
                }
                
                if (fieldId && customErrorMessages[fieldId]) {
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
                            }
                        }
                    }
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
    
    /**
     * Group domain fields by domain name and add section headers
     */
    function initDomainFieldGrouping() {
        function groupDomainFields() {
            const orderFieldset = document.querySelector('#order-fields');
            if (!orderFieldset) return;

            const addressForm = orderFieldset.querySelector('.wc-block-components-address-form');
            if (!addressForm) return;

            // Find all custom domain fields
            const domainFields = addressForm.querySelectorAll('[class*="owh-domain-whois-rdap-custom-field-"]');
            if (domainFields.length === 0) return;

            // Group fields by domain (hash)
            const domainGroups = {};
            
            domainFields.forEach(field => {
                // Extract domain name and hash from field classes
                const classList = Array.from(field.classList);
                const customFieldClass = classList.find(cls => cls.includes('owh-domain-whois-rdap-custom-field-'));
                
                if (customFieldClass) {
                    // Extract hash from class name
                    const match = customFieldClass.match(/owh-domain-whois-rdap-custom-field-([^-]+)/);
                    if (match) {
                        const hash = match[1];
                        
                        // Get domain name from label
                        const label = field.querySelector('label');
                        let domainName = '';
                        
                        if (label) {
                            const labelText = label.textContent;
                            // Extract domain from parentheses like "CPF (example.com)"
                            const domainMatch = labelText.match(/\(([^)]+)\)$/);
                            if (domainMatch) {
                                domainName = domainMatch[1];
                                
                                // Remove parentheses from the label text for cleaner look
                                const cleanLabelText = labelText.replace(/\s*\([^)]+\)$/, '');
                                label.textContent = cleanLabelText;
                            }
                        }
                        
                        if (domainName) {
                            if (!domainGroups[hash]) {
                                domainGroups[hash] = {
                                    domainName: domainName,
                                    fields: []
                                };
                            }
                            domainGroups[hash].fields.push(field);
                        }
                    }
                }
            });

            // Only proceed if we have multiple domains or want to add headers
            if (Object.keys(domainGroups).length === 0) return;

            // Change the main title
            const mainTitle = orderFieldset.querySelector('.wc-block-components-checkout-step__title');
            if (mainTitle && Object.keys(domainGroups).length === 1) {
                const firstDomain = Object.values(domainGroups)[0];
                mainTitle.textContent = `Informações para registro do domínio ${firstDomain.domainName}`;
            } else if (mainTitle && Object.keys(domainGroups).length > 1) {
                mainTitle.textContent = 'Informações para registro dos domínios';
            }

            // Remove legend if exists (we'll replace with custom headers)
            const legend = orderFieldset.querySelector('legend');
            if (legend) {
                legend.style.display = 'none';
            }

            // Create grouped sections
            let hasCreatedSections = false;
            
            Object.values(domainGroups).forEach((group, index) => {
                if (group.fields.length === 0) return;

                // Create section header for multiple domains
                if (Object.keys(domainGroups).length > 1) {
                    const sectionHeader = document.createElement('div');
                    sectionHeader.className = 'wc-block-components-checkout-step__heading-container owh-domain-section-header';
                    sectionHeader.innerHTML = `
                        <div class="wc-block-components-checkout-step__heading">
                            <h3 class="wc-block-components-title wc-block-components-checkout-step__title" style="font-size: 1.1em; margin: 20px 0 15px 0; color: #333;">
                                Informações para registro do domínio ${group.domainName}
                            </h3>
                        </div>
                    `;

                    // Insert header before the first field of this group
                    const firstField = group.fields[0];
                    firstField.parentNode.insertBefore(sectionHeader, firstField);
                    hasCreatedSections = true;
                }
            });

            // Add some CSS to improve visual separation
            if (hasCreatedSections) {
                addDomainGroupingStyles();
            }
        }

        function addDomainGroupingStyles() {
            const styleId = 'owh-domain-grouping-styles';
            if (document.getElementById(styleId)) return; // Already added

            const styles = `
                .owh-domain-section-header {
                    border-top: 2px solid #e0e0e0;
                    padding-top: 15px;
                    margin-top: 25px;
                }
                
                .owh-domain-section-header:first-child {
                    border-top: none;
                    margin-top: 10px;
                    padding-top: 0;
                }
                
                .owh-domain-section-header h3 {
                    color: #333 !important;
                    font-weight: 600 !important;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 8px;
                    margin-bottom: 15px !important;
                }
                
                /* Add subtle background to domain sections */
                .wc-block-components-address-form {
                    position: relative;
                }
                
                /* Style for improved readability */
                .wc-block-components-text-input[class*="owh-domain-whois-rdap-custom-field-"] {
                    margin-bottom: 15px;
                }
                
                /* Add spacing between domain groups */
                .owh-domain-section-header + .wc-block-components-text-input {
                    margin-top: 10px;
                }
            `;
            
            const styleSheet = document.createElement('style');
            styleSheet.id = styleId;
            styleSheet.textContent = styles;
            document.head.appendChild(styleSheet);
        }

        // Observer to watch for DOM changes and regroup fields
        const fieldGroupingObserver = new MutationObserver(function(mutations) {
            let shouldRegroup = false;
            
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if new domain fields were added
                        if (node.classList && Array.from(node.classList).some(cls => cls.includes('owh-domain-whois-rdap-custom-field-'))) {
                            shouldRegroup = true;
                        }
                        
                        // Also check child elements
                        const domainFields = node.querySelectorAll && node.querySelectorAll('[class*="owh-domain-whois-rdap-custom-field-"]');
                        if (domainFields && domainFields.length > 0) {
                            shouldRegroup = true;
                        }
                    }
                });
            });
            
            if (shouldRegroup) {
                // Debounce the regrouping to avoid excessive calls
                clearTimeout(window.owh_regroup_timeout);
                window.owh_regroup_timeout = setTimeout(groupDomainFields, 300);
            }
        });

        function startFieldGrouping() {
            // Initial grouping
            setTimeout(groupDomainFields, 500);
            
            // Start observing for changes
            const orderFields = document.querySelector('#order-fields');
            if (orderFields) {
                fieldGroupingObserver.observe(orderFields, {
                    childList: true,
                    subtree: true
                });
            }
            
            // Also regroup when checkout updates
            document.addEventListener('updated_checkout', function() {
                setTimeout(groupDomainFields, 300);
            });
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startFieldGrouping);
        } else {
            startFieldGrouping();
        }
    }
    
    // Initialize domain field grouping
    initDomainFieldGrouping();

})();