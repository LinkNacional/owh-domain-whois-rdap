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
            const $form = $selector.closest('.domain-product-form-container').find('form.cart');
            
            console.log('handlePeriodChange', { selectedPeriod, formFound: $form.length }); // Debug
            
            this.updatePrice($form, selectedPeriod);
            this.updateButtonText($form, selectedPeriod);
        }

        handleDomainChange(event) {
            const $input = $(event.target);
            const $form = $input.closest('form');
            
            // Validate domain name format
            this.validateDomainName($input.val());
        }

        updatePrice($form, period) {
            const $priceDisplay = $('.domain-period-price');
            const $container = $('.domain-product-form-container');
            const productId = $container.data('product-id') || $form.find('input[name="add-to-cart"]').val();
            
            console.log('updatePrice called', { productId, period }); // Debug
            
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
                    console.log('AJAX success:', response); // Debug
                    if (response.success && response.data.price) {
                        $priceDisplay.removeClass('loading').html(response.data.price_html);
                        
                        // Update hidden field for cart
                        $form.find('input[name="domain_period"]').val(period);
                        $form.find('input[name="domain_price"]').val(response.data.price);
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

        updateButtonText($form, period) {
            const $button = $form.find('.single_add_to_cart_button');
            const periodText = period === 1 ? '1 ano' : period + ' anos';
            
            $button.text(`Adicionar ao Carrinho - ${periodText}`);
        }

        updateInitialPrice() {
            // Set initial price for products with period selector
            $('.domain-period-selector').each((index, element) => {
                const $selector = $(element);
                if ($selector.val()) {
                    const $form = $selector.closest('form');
                    this.updatePrice($form, parseInt($selector.val()));
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
