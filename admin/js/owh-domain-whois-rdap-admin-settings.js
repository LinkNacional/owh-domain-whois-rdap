/**
 * Admin Settings JavaScript
 * 
 * Handles functionality for the OWH Domain WHOIS RDAP admin settings page
 *
 * @since      1.0.0
 * @package    Owh_Domain_Whois_Rdap
 * @subpackage Owh_Domain_Whois_Rdap/admin/js
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab Navigation System
        $('.owh-tab').on('click', function(e) {
            e.preventDefault();
            
            var targetTab = $(this).data('tab');
            
            // Remove active class from all tabs and panels
            $('.owh-tab').removeClass('nav-tab-active');
            $('.owh-tab-panel').removeClass('active').hide();
            
            // Add active class to clicked tab
            $(this).addClass('nav-tab-active');
            
            // Show corresponding panel with fade effect
            $('#tab-' + targetTab).addClass('active').fadeIn(300);
        });
        
        // Initialize tabs on page load
        function initializeTabs() {
            // Check if there's a hash in URL
            var hash = window.location.hash.substring(1);
            var targetTab = hash || 'general';
            
            // Activate the correct tab
            $('.owh-tab[data-tab="' + targetTab + '"]').trigger('click');
        }
        
        // Initialize tabs
        initializeTabs();
        
        // Update URL hash when tab changes (optional - for bookmarkable URLs)
        $('.owh-tab').on('click', function() {
            var tabName = $(this).data('tab');
            if (history.pushState) {
                history.pushState(null, null, '#' + tabName);
            } else {
                window.location.hash = '#' + tabName;
            }
        });

        // Remove TLD row (using event delegation for dynamically added elements)
        $(document).on('click', '.remove-tld-row', function(e) {
            e.preventDefault();
            
            // Add confirmation dialog
            if (!confirm('Tem certeza que deseja remover este TLD customizado?')) {
                return;
            }
            
            $(this).closest('.custom-tld-row').remove();
            updateRemoveButtons();
            reindexRows();
        });

        // Update remove button visibility
        function updateRemoveButtons() {
            // Always show remove buttons - allow removing even when there's only 1 TLD
            $('.remove-tld-row').show();
        }

        // Reindex row names after removal
        function reindexRows() {
            $('.custom-tld-row').each(function(index) {
                $(this).find('input[name*="[tld]"]').attr('name', 'custom_tlds[' + index + '][tld]');
                $(this).find('input[name*="[rdap_url]"]').attr('name', 'custom_tlds[' + index + '][rdap_url]');
            });
        }

        // Save Custom TLDs
        $('#save-custom-tlds').on('click', function() {
            var button = $(this);
            var statusDiv = $('#save-custom-tlds-status');
            
            // Check if nonce is available
            if (typeof owh_rdap_admin === 'undefined' || !owh_rdap_admin.nonce) {
                statusDiv.html('<div class="notice notice-error inline"><p>Erro: Token de segurança não disponível. Recarregue a página.</p></div>').show();
                return;
            }
            
            button.prop('disabled', true).text('Salvando...');
            statusDiv.hide();
            
            // Collect all TLD data
            var customTlds = [];
            $('.custom-tld-row').each(function() {
                var tld = $(this).find('input[name*="[tld]"]').val().trim();
                var rdapUrl = $(this).find('input[name*="[rdap_url]"]').val().trim();
                
                
                if (tld && rdapUrl) {
                    customTlds.push({
                        tld: tld,
                        rdap_url: rdapUrl
                    });
                }
            });
            
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_custom_tlds',
                    custom_tlds: customTlds,
                    nonce: owh_rdap_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        statusDiv.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>').show();
                    } else {
                        statusDiv.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>').show();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('[OWH-RDAP] AJAX error:', textStatus, errorThrown, jqXHR.responseText);
                    statusDiv.html('<div class="notice notice-error inline"><p>Erro ao salvar configurações. Resposta: ' + jqXHR.responseText + '</p></div>').show();
                },
                complete: function() {
                    button.prop('disabled', false).text('Salvar TLDs Customizadas');
                }
            });
        });

        // Initialize remove buttons on page load
        updateRemoveButtons();
        
        // Custom TLDs Management - Robust event delegation approach
        var tldRowIndex = $('.custom-tld-row').length;
        
        // Bind add button click handler with namespace to avoid conflicts
        $(document).off('click.owh-tld-manager', '#add-tld-row');
        $(document).on('click.owh-tld-manager', '#add-tld-row', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Always recalculate to ensure accuracy
            var currentIndex = $('.custom-tld-row').length;
            
            var newRowHtml = [
                '<div class="custom-tld-row" style="display: flex; margin-bottom: 5px; align-items: center;">',
                    '<div style="width: 200px; padding: 5px;">',
                        '<input type="text" name="custom_tlds[' + currentIndex + '][tld]" value="" placeholder=".com" />',
                    '</div>',
                    '<div style="width: 300px; padding: 5px;">',
                        '<input type="text" name="custom_tlds[' + currentIndex + '][rdap_url]" value="" placeholder="https://rdap.example.com" />',
                    '</div>',
                    '<div style="width: 100px; padding: 5px;">',
                        '<button type="button" class="button remove-tld-row">Remover</button>',
                    '</div>',
                '</div>'
            ].join('');
            
            $('#custom-tlds-list').append(newRowHtml);
            updateRemoveButtons();
        });
        
    });

})(jQuery);
