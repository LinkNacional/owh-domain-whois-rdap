(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Handle search form submission
        $('#owh-rdap-search-form').on('submit', function(e) {
            var form = $(this);
            var method = form.attr('method');
            
            // If form method is POST, handle via AJAX
            if (method === 'post') {
                e.preventDefault();
                handleDomainSearch(form);
            }
            // If method is GET, let form submit normally to results page
        });

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

        function handleDomainSearch(form) {
            var domain = $('#owh-rdap-domain-input').val().trim();
            var submitButton = $('#owh-rdap-search-button');
            var searchText = submitButton.find('.owh-rdap-search-text');
            var loadingText = submitButton.find('.owh-rdap-search-loading');
            var resultsDiv = $('#owh-rdap-search-results');
            
            if (!domain) {
                showError('Por favor, digite um domínio.');
                return;
            }

            if (!isValidDomainFormat(domain)) {
                showError(lknaci_owh_rdap_public.strings.invalid_domain);
                return;
            }

            // Update button state
            submitButton.prop('disabled', true);
            searchText.hide();
            loadingText.show();

            // Clear previous results
            resultsDiv.hide().html('');

            $.ajax({
                url: lknaci_owh_rdap_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'lknaci_check_domain',
                    domain: domain,
                    nonce: lknaci_owh_rdap_public.nonce
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    if (response.success) {
                        showResult(response.data);
                    } else {
                        showError(response.data || lknaci_owh_rdap_public.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = lknaci_owh_rdap_public.strings.error;
                    
                    if (status === 'timeout') {
                        errorMessage = 'Timeout na pesquisa. O servidor pode estar sobrecarregado. Tente novamente.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Acesso negado. Verifique suas permissões.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Erro interno do servidor. Tente novamente mais tarde.';
                    }
                    
                    showError(errorMessage);
                },
                complete: function() {
                    // Reset button state
                    submitButton.prop('disabled', false);
                    searchText.show();
                    loadingText.hide();
                }
            });
        }

        function showResult(data) {
            var resultsDiv = $('#owh-rdap-search-results');
            var html = '';

            if (data.has_error) {
                html = '<div class="owh-rdap-result-error">' +
                       '<div class="owh-rdap-error-icon">⚠️</div>' +
                       '<div class="owh-rdap-error-content">' +
                       '<h4>Erro na Pesquisa</h4>' +
                       '<p>' + escapeHtml(data.error || 'Erro desconhecido') + '</p>' +
                       '</div>' +
                       '</div>';
            } else if (data.is_available) {
                html = '<div class="owh-rdap-result-available">' +
                       '<div class="owh-rdap-available-icon">✅</div>' +
                       '<div class="owh-rdap-available-content">' +
                       '<h4>' + escapeHtml(data.status) + '</h4>' +
                       '<p>Este domínio está disponível para registro!</p>';

                // New integration logic
                if (data.integration) {
                    if (data.integration.type === 'custom') {
                        html += '<div class="owh-rdap-buy-section">' +
                                '<a href="' + escapeHtml(data.integration.url) + '" ' +
                                'class="owh-rdap-buy-button" target="_blank" rel="noopener noreferrer">' +
                                '<span class="dashicons dashicons-cart"></span>' +
                                escapeHtml(data.integration.text) +
                                '</a></div>';
                    } else if (data.integration.type === 'whmcs') {
                        var formId = 'whmcs_' + data.domain.replace(/\./g, '_');
                        html += '<div class="owh-rdap-buy-section">' +
                                data.integration.form_html +
                                '<button type="button" class="owh-rdap-search-button" ' +
                                'onclick="document.getElementById(\'' + formId + '\').submit();">' +
                                '<span class="dashicons dashicons-cart"></span>' +
                                escapeHtml(data.integration.text) +
                                '</button></div>';
                    }
                }

                html += '</div></div>';
            } else {
                html = '<div class="owh-rdap-result-unavailable">' +
                       '<div class="owh-rdap-unavailable-icon">❌</div>' +
                       '<div class="owh-rdap-unavailable-content">' +
                       '<h4>' + escapeHtml(data.status) + '</h4>' +
                       '<p>Este domínio já está registrado e não está disponível.</p>' +
                       '</div>' +
                       '</div>';
            }

            resultsDiv.html(html).fadeIn();
        }

        function showError(message) {
            var resultsDiv = $('#owh-rdap-search-results');
            var html = '<div class="owh-rdap-result-error">' + escapeHtml(message) + '</div>';
            resultsDiv.html(html).fadeIn();
        }

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

        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') {
                return '';
            }
            
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Auto-focus on domain input when page loads
        $('#owh-rdap-domain-input').focus();

        // Enter key handling for better UX
        $('#owh-rdap-domain-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $('#owh-rdap-search-form').submit();
            }
        });

        // Clear results when input is cleared
        $('#owh-rdap-domain-input').on('input', function() {
            var domain = $(this).val().trim();
            if (domain === '') {
                $('#owh-rdap-search-results').fadeOut();
            }
        });

        // Smooth scroll to results
        $(document).on('DOMNodeInserted', '#owh-rdap-search-results', function() {
            if ($(this).is(':visible')) {
                $('html, body').animate({
                    scrollTop: $(this).offset().top - 20
                }, 500);
            }
        });

    });

})(jQuery);
