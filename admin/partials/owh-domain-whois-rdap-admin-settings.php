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
    <h1><?php esc_html_e('OWH Domain WHOIS RDAP', 'owh-domain-whois-rdap'); ?></h1>
    
    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper owh-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active owh-tab" data-tab="general">
            <?php esc_html_e('Geral', 'owh-domain-whois-rdap'); ?>
        </a>
        <a href="#rdap" class="nav-tab owh-tab" data-tab="rdap">
            <?php esc_html_e('RDAP', 'owh-domain-whois-rdap'); ?>
        </a>
        <a href="#custom-fields" class="nav-tab owh-tab" data-tab="custom-fields">
            <?php esc_html_e('Campos Customizados', 'owh-domain-whois-rdap'); ?>
        </a>
        <?php 
        $integration_type = get_option('owh_rdap_integration_type', '');
        if (!empty($integration_type)): ?>
        <a href="#integration" class="nav-tab owh-tab" data-tab="integration">
            <?php esc_html_e('TLDS', 'owh-domain-whois-rdap'); ?>
        </a>
        <?php endif; ?>
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
        <div id="tab-rdap" class="owh-tab-panel">
            <h2><?php esc_html_e('Configurações RDAP', 'owh-domain-whois-rdap'); ?></h2>
            <p><?php esc_html_e('Gerenciar configurações específicas do protocolo RDAP (Registration Data Access Protocol).', 'owh-domain-whois-rdap'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Ações úteis', 'owh-domain-whois-rdap'); ?></th>
                    <td>
                        <button type="button" id="update-rdap-servers" class="button button-primary">
                            <?php esc_html_e('Atualizar Lista de Extensões (TLDs)', 'owh-domain-whois-rdap'); ?>
                        </button>
                        <div id="update-rdap-status" class="update-rdap-status"></div>
                        
                        <p class="description">
                            <?php esc_html_e('Sincroniza a lista de extensões de domínio (.com, .br, .net) com o registro oficial.', 'owh-domain-whois-rdap'); ?><br>
                            <?php esc_html_e('É importante executar esta ação periodicamente para que a pesquisa inclua novos sufixos.', 'owh-domain-whois-rdap'); ?><br>
                            <?php 
                            $last_update = get_option('owh_domain_whois_rdap_last_update', '2024-10-21');
                            printf(esc_html__('Última atualização: %s.', 'owh-domain-whois-rdap'), esc_html($last_update)); 
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Subdomínios Customizados', 'owh-domain-whois-rdap'); ?></th>
                    <td>
                        <p class="description">
                            <?php esc_html_e('Para disponibilizar subdomínios nas pesquisas de domínios defina os na lista abaixo seguindo rigorosamente a estrutura do JSON.', 'owh-domain-whois-rdap'); ?>
                        </p>
                        
                        <div id="custom-tlds-container" class="custom-tlds-container">
                            <div class="custom-tlds-header">
                                <div><?php esc_html_e('Subdomínio', 'owh-domain-whois-rdap'); ?></div>
                                <div><?php esc_html_e('RDAP URL', 'owh-domain-whois-rdap'); ?></div>
                                <div><?php esc_html_e('Ações', 'owh-domain-whois-rdap'); ?></div>
                            </div>
                            
                            <div id="custom-tlds-list">
                                <?php 
                                $custom_tlds = get_option('owh_domain_whois_rdap_custom_tlds', []);
                                
                                if (empty($custom_tlds)) {
                                    $custom_tlds = [['tld' => '', 'rdap_url' => '']]; // Pelo menos uma linha em branco
                                }
                                foreach ($custom_tlds as $index => $tld_config): ?>
                                    <div class="custom-tld-row">
                                        <div>
                                            <input type="text" 
                                                   name="custom_tlds[<?php echo esc_attr( $index ); ?>][tld]" 
                                                   value="<?php echo esc_attr( $tld_config['tld'] ); ?>" 
                                                   placeholder=".com" />
                                        </div>
                                        <div>
                                            <input type="text" 
                                                   name="custom_tlds[<?php echo esc_attr( $index ); ?>][rdap_url]" 
                                                   value="<?php echo esc_attr( $tld_config['rdap_url'] ); ?>" 
                                                   placeholder="https://rdap.example.com" />
                                        </div>
                                        <div>
                                            <button type="button" class="button remove-tld-row" <?php echo count($custom_tlds) === 1 ? 'style="display:none;"' : ''; ?>>
                                                <?php esc_html_e('Remover', 'owh-domain-whois-rdap'); ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="button" id="add-tld-row" class="button button-secondary add-tld-button">
                                <?php esc_html_e('Adicionar TLD', 'owh-domain-whois-rdap'); ?>
                            </button>
                        </div>
                    </td>
                </tr>
            </table>
            
            <div class="owh-rdap-save-section">
                <input type="hidden" name="action" value="save_custom_tlds" />
                <?php wp_nonce_field('save_custom_tlds_nonce', 'custom_tlds_nonce'); ?>
                <button type="button" id="save-custom-tlds" class="button button-primary">
                    <?php esc_html_e('Salvar TLDs Customizadas', 'owh-domain-whois-rdap'); ?>
                </button>
                <div id="save-custom-tlds-status" class="save-custom-tlds-status"></div>
            </div>
        </div>
        
        <!-- Custom Fields Tab -->
        <div id="tab-custom-fields" class="owh-tab-panel">
            <h2><?php esc_html_e('Campos Customizados para TLDs', 'owh-domain-whois-rdap'); ?></h2>
            <p><?php esc_html_e('Configure campos customizados que serão obrigatórios no checkout para domínios específicos. Cada campo deve ter um label (rótulo). A regex para validação é opcional - sem regex, qualquer valor será aceito. A mensagem de erro personalizada será exibida ANTES da mensagem padrão do sistema quando a validação falhar.', 'owh-domain-whois-rdap'); ?></p>
            
            <div id="custom-fields-container">
                <div id="custom-fields-grid-wrapper">
                    <div id="custom-fields-grid"></div>
                </div>
                
                <div class="custom-fields-actions">
                    <button type="button" id="add-custom-field" class="button button-primary">
                        <?php esc_html_e('Adicionar Campo', 'owh-domain-whois-rdap'); ?>
                    </button>
                    <button type="button" id="save-custom-fields" class="button button-secondary">
                        <?php esc_html_e('Salvar Campos', 'owh-domain-whois-rdap'); ?>
                    </button>
                </div>
                
                <div id="save-custom-fields-status" class="save-status"></div>
            </div>
            
            <div class="custom-fields-help">
                <h3><?php esc_html_e('Como usar os campos customizados:', 'owh-domain-whois-rdap'); ?></h3>
                <ol>
                    <li><?php esc_html_e('Crie campos com labels descritivos (ex: "CPF", "CNPJ", "Passaporte")', 'owh-domain-whois-rdap'); ?></li>
                    <li><?php esc_html_e('Opcionalmente, configure uma regex JavaScript para validação (ex: "^[0-9]{11}$" para CPF)', 'owh-domain-whois-rdap'); ?></li>
                    <li><?php esc_html_e('Configure uma mensagem de erro personalizada que será exibida ANTES da mensagem padrão', 'owh-domain-whois-rdap'); ?></li>
                    <li><?php esc_html_e('Sem regex, qualquer valor será aceito no campo', 'owh-domain-whois-rdap'); ?></li>
                    <li><?php esc_html_e('Nas configurações de produtos, selecione quais campos são obrigatórios para cada TLD', 'owh-domain-whois-rdap'); ?></li>
                    <li><?php esc_html_e('Os campos aparecerão automaticamente no checkout quando necessário', 'owh-domain-whois-rdap'); ?></li>
                </ol>
                
                <div class="regex-examples">
                    <h4><?php esc_html_e('Exemplos de Regex e Mensagens:', 'owh-domain-whois-rdap'); ?></h4>
                    <ul>
                        <li><strong>CPF:</strong> <code>^[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}$</code> | <em>Mensagem: "Por favor, insira um CPF válido no formato 000.000.000-00"</em></li>
                        <li><strong>CNPJ:</strong> <code>^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$</code> | <em>Mensagem: "Por favor, insira um CNPJ válido no formato 00.000.000/0000-00"</em></li>
                        <li><strong>Email:</strong> <code>^[^@\s]+@[^@\s]+\.[^@\s]+$</code> | <em>Mensagem: "Por favor, insira um endereço de email válido"</em></li>
                        <li><strong>Telefone:</strong> <code>^\+55\s?\(?\d{2}\)?\s?9?\d{4}-?\d{4}$</code> | <em>Mensagem: "Por favor, insira um telefone válido no formato +0000000000000"</em></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Integration Tab -->
        <?php 
        $integration_type = get_option('owh_rdap_integration_type', '');
        if (!empty($integration_type)): ?>
        <div id="tab-integration" class="owh-tab-panel">
            <h2><?php esc_html_e('Configuração de TLDs', 'owh-domain-whois-rdap'); ?></h2>
            <p><?php esc_html_e('Configure quais TLDs estão disponíveis para pesquisa.', 'owh-domain-whois-rdap'); ?></p>

            <div id="tlds-grid-container">
                <div id="tlds-grid"></div>
            </div>
            
            <button type="button" id="save-tlds-config" class="button button-primary">
                <?php esc_html_e('Salvar Configurações de TLDs', 'owh-domain-whois-rdap'); ?>
            </button>
            <div id="save-tlds-status" class="save-status"></div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="owh-rdap-info">
        <h3><?php esc_html_e('Como usar', 'owh-domain-whois-rdap'); ?></h3>
        
        <h4><?php esc_html_e('Shortcodes:', 'owh-domain-whois-rdap'); ?></h4>
        <p><?php esc_html_e('Para exibir o formulário de pesquisa, use o shortcode:', 'owh-domain-whois-rdap'); ?></p>
        <code class="owh-shortcode">[owh-rdap-whois-search]</code>
        
        <p class="shortcode-description"><?php esc_html_e('Para exibir os resultados da pesquisa, use o shortcode:', 'owh-domain-whois-rdap'); ?></p>
        <code class="owh-shortcode">[owh-rdap-whois-results]</code>

        <p class="shortcode-description"><?php esc_html_e('Para exibir detalhes WHOIS completos, use o shortcode:', 'owh-domain-whois-rdap'); ?></p>
        <code class="owh-shortcode">[owh-rdap-whois-details]</code>
        
        <h4 class="section-title"><?php esc_html_e('Blocos do Gutenberg:', 'owh-domain-whois-rdap'); ?></h4>
        <p><?php esc_html_e('Você também pode usar os blocos do Gutenberg no editor de posts/páginas. Procure por:', 'owh-domain-whois-rdap'); ?></p>
        <ul class="gutenberg-blocks-list">
            <li><strong><?php esc_html_e('OWH RDAP Search', 'owh-domain-whois-rdap'); ?></strong> - <?php esc_html_e('Formulário de pesquisa de domínio', 'owh-domain-whois-rdap'); ?></li>
            <li><strong><?php esc_html_e('OWH RDAP Results', 'owh-domain-whois-rdap'); ?></strong> - <?php esc_html_e('Resultados da pesquisa de domínio', 'owh-domain-whois-rdap'); ?></li>
            <li><strong><?php esc_html_e('OWH RDAP Details', 'owh-domain-whois-rdap'); ?></strong> - <?php esc_html_e('Detalhes WHOIS completos', 'owh-domain-whois-rdap'); ?></li>
        </ul>
        <div class="template-variables">
            <h4><?php esc_html_e('Template Variables para Custom URL:', 'owh-domain-whois-rdap'); ?></h4>
            <ul>
                <li><code>{domain}</code> - <?php esc_html_e('Domínio completo (ex: example.com)', 'owh-domain-whois-rdap'); ?></li>
                <li><code>{sld}</code> - <?php esc_html_e('Nome do domínio (ex: example)', 'owh-domain-whois-rdap'); ?></li>
                <li><code>{tld}</code> - <?php esc_html_e('Extensão do domínio (ex: com)', 'owh-domain-whois-rdap'); ?></li>
            </ul>
        </div>
    </div>
</div>
