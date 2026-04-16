/**
 * Domain Pricing Matrix JavaScript
 * Enhanced functionality for the pricing matrix interface
 * 
 * @package    Owh_Domain_Whois_Rdap
 * @subpackage Owh_Domain_Whois_Rdap/admin/js
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Initialize domain pricing matrix functionality
        initDomainPricingMatrix();

        /**
         * Initialize the pricing matrix
         */
        function initDomainPricingMatrix() {
            // Show/hide pricing matrix based on product type
            toggleDomainPricingMatrix();
            
            // Bind events
            $('#product-type').on('change', toggleDomainPricingMatrix);
            
            // Enhanced input validation
            $('.domain-pricing-matrix-table input[type="text"]').on('input', validatePriceInput);
            $('.domain-pricing-matrix-table input[type="text"]').on('blur', formatPriceInput);
            
            // Bulk fill functionality
            addBulkFillFunctionality();
        }

        /**
         * Show/hide the domain pricing matrix
         */
        function toggleDomainPricingMatrix() {
            var productType = $('#product-type').val();
            var $matrixSection = $('.domain_pricing_matrix');
            
            if (productType === 'domain') {
                $matrixSection.slideDown(300);
                // Hide standard pricing fields
                $('._regular_price_field, ._sale_price_field, .pricing').hide();
            } else {
                $matrixSection.slideUp(300);
                // Show standard pricing fields
                $('._regular_price_field, ._sale_price_field, .pricing').show();
            }
        }

        /**
         * Validate price input in real-time
         */
        function validatePriceInput() {
            var $input = $(this);
            var value = $input.val();
            
            // Remove invalid characters
            value = value.replace(/[^0-9.,]/g, '');
            
            // Convert comma to dot
            value = value.replace(',', '.');
            
            // Ensure only one decimal point
            var parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limit decimal places to 2
            if (parts[1] && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }
            
            $input.val(value);
            
            // Visual feedback
            if (value === '' || /^\d+(\.\d{1,2})?$/.test(value)) {
                $input.removeClass('price-invalid').addClass('price-valid');
            } else {
                $input.removeClass('price-valid').addClass('price-invalid');
            }
        }

        /**
         * Format price input on blur
         */
        function formatPriceInput() {
            var $input = $(this);
            var value = parseFloat($input.val());
            
            if (!isNaN(value)) {
                $input.val(value.toFixed(2));
                $input.addClass('price-valid').removeClass('price-invalid');
            }
        }

        /**
         * Add bulk fill functionality
         */
        function addBulkFillFunctionality() {
            var $matrixSection = $('.domain_pricing_matrix');
            
            if ($matrixSection.length > 0) {
                var $bulkFillSection = $('<div class="bulk-fill-section" style="margin-top: 15px; padding: 15px; background: #f0f8ff; border: 1px solid #c3d9ff; border-radius: 4px;"></div>');
                $bulkFillSection.append('<h4 style="margin-top: 0;">Preenchimento em Lote</h4>');
                
                // Bulk fill by action
                var $actionFill = $('<div style="margin-bottom: 10px;"></div>');
                $actionFill.append('<label>Preencher ação: </label>');
                
                var $actionSelect = $('<select class="bulk-action-select" style="margin: 0 10px;">');
                $actionSelect.append('<option value="">Selecione uma ação...</option>');
                $actionSelect.append('<option value="register">Registro</option>');
                $actionSelect.append('<option value="renew">Renovação</option>');
                $actionSelect.append('<option value="transfer">Transferência</option>');
                
                var $actionPrice = $('<input type="text" class="bulk-action-price" placeholder="0.00" style="width: 80px; margin: 0 10px;">');
                var $actionBtn = $('<button type="button" class="button bulk-action-btn">Aplicar</button>');
                
                $actionFill.append($actionSelect).append(' com preço: ').append($actionPrice).append(' ').append($actionBtn);
                $bulkFillSection.append($actionFill);

                // Progressive pricing
                var $progressiveFill = $('<div></div>');
                $progressiveFill.append('<label>Preço progressivo: Base: </label>');
                
                var $basePrice = $('<input type="text" class="progressive-base" placeholder="10.00" style="width: 80px; margin: 0 5px;">');
                var $increment = $('<input type="text" class="progressive-increment" placeholder="5.00" style="width: 80px; margin: 0 5px;">');
                var $progressiveBtn = $('<button type="button" class="button progressive-btn">Aplicar Progressive</button>');
                
                $progressiveFill.append($basePrice).append(' Incremento: ').append($increment).append(' ').append($progressiveBtn);
                $bulkFillSection.append($progressiveFill);

                $matrixSection.append($bulkFillSection);

                // Event handlers
                $('.bulk-action-btn').on('click', function() {
                    var action = $('.bulk-action-select').val();
                    var price = $('.bulk-action-price').val();
                    
                    if (action && price) {
                        $('input[name*="[' + action + ']"]').val(price).trigger('blur');
                        showNotice('Preços aplicados para ' + action + '!', 'success');
                    }
                });

                $('.progressive-btn').on('click', function() {
                    var basePrice = parseFloat($('.progressive-base').val()) || 0;
                    var increment = parseFloat($('.progressive-increment').val()) || 0;
                    
                    if (basePrice > 0) {
                        for (var year = 1; year <= 10; year++) {
                            var price = basePrice + (increment * (year - 1));
                            $('input[name*="[' + year + '][register]"]').val(price.toFixed(2)).trigger('blur');
                        }
                        showNotice('Preços progressivos aplicados!', 'success');
                    }
                });
            }
        }

        /**
         * Show notice message
         */
        function showNotice(message, type) {
            type = type || 'info';
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible" style="margin: 10px 0;"><p>' + message + '</p></div>');
            
            $('.domain_pricing_matrix').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });

})(jQuery);
