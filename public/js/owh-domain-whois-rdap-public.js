(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Handle example domain clicks
        $(document).on('click', '.owh-rdap-example-domain', function(e) {
            e.preventDefault();
            var domain = $(this).data('domain');
            $('#owh-rdap-domain-input').val(domain).focus();
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

})(jQuery);
