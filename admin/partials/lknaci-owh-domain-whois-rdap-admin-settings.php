<?php
/**
 * Settings page for the plugin
 *
 * @link       https://owh.digital
 * @since      1.0.0
 *
 * @package    Lknaci_Owh_Domain_Whois_Rdap
 * @subpackage Lknaci_Owh_Domain_Whois_Rdap/admin/partials
 */

// Prevent direct access
defined( 'ABSPATH' ) or exit;
?>

<div class="wrap">
    
    <form method="post" action="options.php" id="mainform">
        <?php
        settings_fields('owh_rdap_settings');
        do_settings_sections('owh_rdap_settings');
        submit_button(__('Salvar Configurações', 'lknaci-owh-domain-whois-rdap'));
        ?>
    </form>
    
    <div class="owh-rdap-info" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa;">
        <h3><?php _e('Como usar', 'lknaci-owh-domain-whois-rdap'); ?></h3>
        <p><?php _e('Para exibir o formulário de pesquisa, use o shortcode:', 'lknaci-owh-domain-whois-rdap'); ?></p>
        <code style="padding: 5px; background: #fff; border: 1px solid #ddd;">[owh-rdap-whois-search]</code>
        
        <p style="margin-top: 15px;"><?php _e('Para exibir os resultados da pesquisa, use o shortcode:', 'lknaci-owh-domain-whois-rdap'); ?></p>
        <code style="padding: 5px; background: #fff; border: 1px solid #ddd;">[owh-rdap-whois-results]</code>

        <p style="margin-top: 15px;"><?php _e('Para exibir detalhes WHOIS completos, use o shortcode:', 'lknaci-owh-domain-whois-rdap'); ?></p>
        <code style="padding: 5px; background: #fff; border: 1px solid #ddd;">[owh-rdap-whois-details]</code>
        <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd;">
            <h4><?php _e('Template Variables para Custom URL:', 'lknaci-owh-domain-whois-rdap'); ?></h4>
            <ul style="margin: 0;">
                <li><code>{domain}</code> - <?php _e('Domínio completo (ex: example.com)', 'lknaci-owh-domain-whois-rdap'); ?></li>
                <li><code>{sld}</code> - <?php _e('Nome do domínio (ex: example)', 'lknaci-owh-domain-whois-rdap'); ?></li>
                <li><code>{tld}</code> - <?php _e('Extensão do domínio (ex: com)', 'lknaci-owh-domain-whois-rdap'); ?></li>
            </ul>
        </div>
    </div>
</div>

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
</style>
