/**
 * Domain Product Periods Selection JavaScript
 * Handles period selection and price updates for domain products
 */

(function($) {
    'use strict';

    class DomainProductPeriods {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.updateInitialPrice();
        }

        bindEvents() {
            // Listen for period selection changes
            $(document).on('change', '.domain-period-selector', this.handlePeriodChange.bind(this));
            
            // Listen for domain name changes (if applicable)
            $(document).on('change input', '.domain-name-input', this.handleDomainChange.bind(this));
            
            // Form submission handling
            $(document).on('submit', '.cart form', this.handleFormSubmit.bind(this));
        }

        handlePeriodChange(event) {
            const $selector = $(event.target);
            const selectedPeriod = parseInt($selector.val());
            const $container = $selector.closest('.domain-product-form-container');
            
            
            this.updatePrice($container, selectedPeriod);
            this.updateAllForms(selectedPeriod);
        }

        handleDomainChange(event) {
            const $input = $(event.target);
            const $form = $input.closest('form');
            
            // Validate domain name format
            this.validateDomainName($input.val());
        }

        updatePrice($container, period) {
            const $priceDisplay = $container.find('.domain-period-price');
            const productId = $container.data('product-id');
            
            
            if (!productId || !period) {
                console.log('Missing productId or period');
                return;
            }

            // Show loading state
            $priceDisplay.addClass('loading').text('Carregando...');

            // AJAX request to get price for selected period
            $.ajax({
                url: owh_domain_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_domain_price_for_period',
                    product_id: productId,
                    period: period,
                    nonce: owh_domain_ajax.nonce
                },
                success: (response) => {
                    if (response.success && response.data.price) {
                        $priceDisplay.removeClass('loading').html(response.data.price_html);
                        
                        // Update all forms with new price data
                        this.updateAllForms(period, response.data.price);
                    } else {
                        $priceDisplay.removeClass('loading').text('Preço não disponível');
                    }
                },
                error: (xhr, status, error) => {
                    console.log('AJAX error:', { xhr, status, error }); // Debug
                    $priceDisplay.removeClass('loading').text('Erro ao carregar preço');
                }
            });
        }
        
        updateAllForms(period, price = null) {
            // Update all WooCommerce cart forms
            $('form.cart').each((index, form) => {
                this.updateFormFields($(form), period, price);
            });
            
            // Update custom domain add-to-cart forms
            $('.domain-add-to-cart-section form').each((index, form) => {
                this.updateFormFields($(form), period, price);
            });
            
            // Update global hidden fields
            $('#hidden_domain_price').val(price || '');
        }
        
        updateFormFields($form, period, price) {
            // Remove existing fields to avoid duplicates
            $form.find('input[name="domain_period"]').remove();
            $form.find('input[name="domain_price"]').remove();
            
            // Add new hidden fields
            $form.append(`<input type="hidden" name="domain_period" value="${period}" />`);
            if (price) {
                $form.append(`<input type="hidden" name="domain_price" value="${price}" />`);
            }
            
        }

        updateInitialPrice() {
            // Set initial price for products with period selector
            $('.domain-period-selector').each((index, element) => {
                const $selector = $(element);
                const selectedValue = $selector.val() || $selector.find('option:selected').val();
                
                if (selectedValue) {
                    const $container = $selector.closest('.domain-product-form-container');
                    this.updatePrice($container, parseInt(selectedValue));
                }
            });
        }

        validateDomainName(domainName) {
            // Basic domain validation
            const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.[a-zA-Z]{2,}$/;
            return domainRegex.test(domainName);
        }

        handleFormSubmit(event) {
            const $form = $(event.target);
            
            // Check if this is a domain product form
            if (!$form.find('.domain-period-selector').length) {
                return true;
            }

            const domainName = $form.find('.domain-name-input').val();
            const selectedPeriod = $form.find('.domain-period-selector').val();

            // Basic validation
            if (domainName && !this.validateDomainName(domainName)) {
                alert('Por favor, insira um nome de domínio válido.');
                event.preventDefault();
                return false;
            }

            if (!selectedPeriod) {
                alert('Por favor, selecione o período de registro.');
                event.preventDefault();
                return false;
            }

            return true;
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        new DomainProductPeriods();
    });

})(jQuery);
