<?php
/**
 * Settings page for the plugin
 *
 * @link       https://owh.digital
 * @since      1.0.0
 *
 * @package    Owh_Domain_Whois_Rdap
 * @subpackage Owh_Domain_Whois_Rdap/admin/partials
 */

// Prevent direct access
defined( 'ABSPATH' ) or exit;
?>

<div class="wrap">
    <h1><?php _e('OWH Domain WHOIS RDAP', 'owh-domain-whois-rdap'); ?></h1>
    
    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper owh-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active owh-tab" data-tab="general">
            <?php _e('Geral', 'owh-domain-whois-rdap'); ?>
        </a>
        <a href="#rdap" class="nav-tab owh-tab" data-tab="rdap">
            <?php _e('RDAP', 'owh-domain-whois-rdap'); ?>
        </a>
    </nav>
    
    <!-- Tab Contents -->
    <div class="owh-tab-content">
        <!-- General Tab -->
        <div id="tab-general" class="owh-tab-panel active">
            <form method="post" action="options.php" id="mainform">
                <?php
                settings_fields('owh_rdap_settings');
                do_settings_sections('owh_rdap_settings');
                submit_button(__('Salvar Configurações', 'owh-domain-whois-rdap'));
                ?>
            </form>
        </div>
        
        <!-- RDAP Tab -->
        <div id="tab-rdap" class="owh-tab-panel" style="display: none;">
            <h2><?php _e('Configurações RDAP', 'owh-domain-whois-rdap'); ?></h2>
            <p><?php _e('Gerenciar configurações específicas do protocolo RDAP (Registration Data Access Protocol).', 'owh-domain-whois-rdap'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Ações úteis', 'owh-domain-whois-rdap'); ?></th>
                    <td>
                        <div id="rdap-status-info" style="margin-bottom: 15px;">
                            <p><strong><?php _e('Arquivo atual:', 'owh-domain-whois-rdap'); ?></strong> <span id="file-status"><?php _e('Carregando...', 'owh-domain-whois-rdap'); ?></span></p>
                            <p><strong><?php _e('Última atualização:', 'owh-domain-whois-rdap'); ?></strong> <span id="last-update"><?php _e('Verificando...', 'owh-domain-whois-rdap'); ?></span></p>
                            <p><strong><?php _e('Tamanho do arquivo:', 'owh-domain-whois-rdap'); ?></strong> <span id="file-size">-</span></p>
                        </div>
                        
                        <button type="button" id="update-rdap-servers" class="button button-secondary">
                            <?php _e('Atualizar Lista de Servidores RDAP', 'owh-domain-whois-rdap'); ?>
                        </button>
                        <div id="update-rdap-status" style="margin-top: 10px; display: none;"></div>
                        
                        <p class="description">
                            <?php _e('O plugin já inclui uma lista de servidores RDAP. Use este botão para baixar a versão mais recente da IANA.', 'owh-domain-whois-rdap'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="owh-rdap-info" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa;">
        <h3><?php _e('Como usar', 'owh-domain-whois-rdap'); ?></h3>
        
        <h4><?php _e('Shortcodes:', 'owh-domain-whois-rdap'); ?></h4>
        <p><?php _e('Para exibir o formulário de pesquisa, use o shortcode:', 'owh-domain-whois-rdap'); ?></p>
        <code style="padding: 5px; background: #fff; border: 1px solid #ddd;">[owh-rdap-whois-search]</code>
        
        <p style="margin-top: 15px;"><?php _e('Para exibir os resultados da pesquisa, use o shortcode:', 'owh-domain-whois-rdap'); ?></p>
        <code style="padding: 5px; background: #fff; border: 1px solid #ddd;">[owh-rdap-whois-results]</code>

        <p style="margin-top: 15px;"><?php _e('Para exibir detalhes WHOIS completos, use o shortcode:', 'owh-domain-whois-rdap'); ?></p>
        <code style="padding: 5px; background: #fff; border: 1px solid #ddd;">[owh-rdap-whois-details]</code>
        
        <h4 style="margin-top: 25px;"><?php _e('Blocos do Gutenberg:', 'owh-domain-whois-rdap'); ?></h4>
        <p><?php _e('Você também pode usar os blocos do Gutenberg no editor de posts/páginas. Procure por:', 'owh-domain-whois-rdap'); ?></p>
        <ul style="margin: 10px 0 0 20px;">
            <li><strong><?php _e('OWH RDAP Search', 'owh-domain-whois-rdap'); ?></strong> - <?php _e('Formulário de pesquisa de domínio', 'owh-domain-whois-rdap'); ?></li>
            <li><strong><?php _e('OWH RDAP Results', 'owh-domain-whois-rdap'); ?></strong> - <?php _e('Resultados da pesquisa de domínio', 'owh-domain-whois-rdap'); ?></li>
            <li><strong><?php _e('OWH RDAP Details', 'owh-domain-whois-rdap'); ?></strong> - <?php _e('Detalhes WHOIS completos', 'owh-domain-whois-rdap'); ?></li>
        </ul>
        <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd;">
            <h4><?php _e('Template Variables para Custom URL:', 'owh-domain-whois-rdap'); ?></h4>
            <ul style="margin: 0;">
                <li><code>{domain}</code> - <?php _e('Domínio completo (ex: example.com)', 'owh-domain-whois-rdap'); ?></li>
                <li><code>{sld}</code> - <?php _e('Nome do domínio (ex: example)', 'owh-domain-whois-rdap'); ?></li>
                <li><code>{tld}</code> - <?php _e('Extensão do domínio (ex: com)', 'owh-domain-whois-rdap'); ?></li>
            </ul>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
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
});
</script>

<style>
.form-table th {
    width: 200px;
}

.owh-rdap-info h3 {
    margin-top: 0;
    color: #0073aa;
}

.owh-rdap-info code {
    font-size: 14px;
}

/* Tab Styles */
.owh-tab-wrapper {
    margin-bottom: 0;
    border-bottom: 1px solid #ccc;
}

.owh-tab {
    position: relative;
    text-decoration: none;
    background: transparent;
    border: 1px solid transparent;
    border-bottom: 0;
    margin-bottom: -1px;
    padding: 10px 15px;
    color: #0073aa;
    transition: all 0.3s ease;
}

.owh-tab:hover {
    color: #005a87;
    background-color: #f9f9f9;
}

.owh-tab.nav-tab-active {
    background-color: #fff;
    border-color: #ccc;
    border-bottom-color: #fff;
    color: #000;
}

.owh-tab-content {
    background: #fff;
    border: 1px solid #ccc;
    border-top: 0;
    padding: 20px;
    min-height: 300px;
}

.owh-tab-panel {
    display: none;
}

.owh-tab-panel.active {
    display: block;
}

.owh-tab-panel h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
</style>
