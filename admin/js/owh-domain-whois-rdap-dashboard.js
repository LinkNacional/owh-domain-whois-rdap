/**
 * OWH Domain WHOIS RDAP Dashboard JavaScript
 * 
 * @since 1.2.2
 * @package owh-domain-whois-rdap
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Tab switching functionality
        $('.owh-nav-tab').on('click', function(e) {
            e.preventDefault();
            
            var targetTab = $(this).data('tab');
            
            // Remove active class from all tabs
            $('.owh-nav-tab').removeClass('owh-nav-tab-active');
            
            // Add active class to clicked tab
            $(this).addClass('owh-nav-tab-active');
            
            // Hide all tab contents
            $('.owh-tab-content').hide().removeClass('owh-tab-active');
            
            // Show target tab content
            $('#' + targetTab + '-content').show().addClass('owh-tab-active');
        });
        
        // Initialize first tab as active
        $('.owh-nav-tab:first').addClass('owh-nav-tab-active');
        $('#home-content').show().addClass('owh-tab-active');
        
    });

})(jQuery);
