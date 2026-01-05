(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Admin update RDAP servers button
        $('#update-rdap-servers').on('click', function() {
            var button = $(this);
            var status = $('#update-rdap-status');
            var originalText = button.text();
            
            button.prop('disabled', true).text(lknaci_owh_rdap_admin.strings.updating);
            status.removeClass('success error').addClass('loading').show()
                .html('<span style="color: #0073aa;">' + lknaci_owh_rdap_admin.strings.updating + '</span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'lknaci_update_rdap_servers',
                    nonce: lknaci_owh_rdap_admin.nonce
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    if (response.success) {
                        status.removeClass('loading error').addClass('success')
                            .html('<span style="color: #46b450;">' + lknaci_owh_rdap_admin.strings.updated + '</span>');
                    } else {
                        status.removeClass('loading success').addClass('error')
                            .html('<span style="color: #dc3232;">' + lknaci_owh_rdap_admin.strings.error + 
                                  (response.data ? ': ' + response.data : '') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = lknaci_owh_rdap_admin.strings.error;
                    if (status === 'timeout') {
                        errorMessage += ': Timeout na requisição.';
                    } else {
                        errorMessage += ': ' + error;
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
            var buyButtonUrl = $('#lknaci_owh_domain_whois_rdap_buy_button_url');
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
        $('#lknaci_owh_domain_whois_rdap_buy_button_url').on('input', function() {
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

        // Cache time validation
        $('.cache-time-input').on('input', function() {
            var input = $(this);
            var value = parseInt(input.val());
            
            if (isNaN(value) || value < 0) {
                input.css('border-color', '#dc3232');
            } else {
                input.css('border-color', '#46b450');
            }
        });

        // Toggle results page options based on search enable/disable
        $('input[name="lknaci_owh_domain_whois_rdap_enable_search"]').on('change', function() {
            var isEnabled = $(this).val() === '1' && $(this).is(':checked');
            var resultsPageRow = $('#lknaci_owh_domain_whois_rdap_results_page').closest('tr');
            
            if (isEnabled) {
                resultsPageRow.fadeIn();
            } else {
                resultsPageRow.fadeOut();
            }
        });

        // Initial state
        var searchEnabled = $('input[name="lknaci_owh_domain_whois_rdap_enable_search"]:checked').val();
        if (searchEnabled !== '1') {
            $('#lknaci_owh_domain_whois_rdap_results_page').closest('tr').hide();
        }
    });

})(jQuery);
