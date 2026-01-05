<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://owhgroup.com.br
 * @since      1.0.0
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors(); ?>

	<form method="post" action="options.php" id="mainform">
		<?php 
		settings_fields( $this->plugin_name );
		do_settings_sections( $this->plugin_name );
		?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="enable_search">
							<strong><?php _e( 'Ferramenta de Pesquisa de Domínios', 'lknaci-owh-domain-whois-rdap' ); ?></strong>
						</label>
						<p class="description">
							<?php _e( 'Ative a ferramenta de pesquisa de domínios (Whois) no seu site.', 'lknaci-owh-domain-whois-rdap' ); ?>
						</p>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 20px; border-bottom: 1px solid #CCCCCC; padding-bottom: 15px;">
								<h3 style="font-size: 16px; margin: 0 0 10px 0; color: #3C434A;">
									<?php _e( 'Ativar Pesquisa de Domínios (RDAP/WHOIS)', 'lknaci-owh-domain-whois-rdap' ); ?>
								</h3>
								<p style="font-size: 14px; margin: 0 0 15px 0; color: #3C434A;">
									<?php _e( 'Ofereça aos seus visitantes uma ferramenta para pesquisar a disponibilidade de domínios.', 'lknaci-owh-domain-whois-rdap' ); ?>
								</p>
							</div>

							<div style="margin-bottom: 15px;">
								<?php 
								$enable_search = get_option( 'lknaci_owh_domain_whois_rdap_enable_search', false );
								?>
								<label style="display: flex; align-items: center; margin-bottom: 15px;">
									<input 
										type="radio" 
										name="lknaci_owh_domain_whois_rdap_enable_search" 
										value="1" 
										<?php checked( 1, $enable_search ); ?>
										style="margin-right: 8px;"
									/> 
									<?php _e( 'Ativar', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								
								<label style="display: flex; align-items: center; margin-bottom: 15px;">
									<input 
										type="radio" 
										name="lknaci_owh_domain_whois_rdap_enable_search" 
										value="0" 
										<?php checked( 0, $enable_search ); ?>
										style="margin-right: 8px;"
									/> 
									<?php _e( 'Desativar', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								
								<p class="description" style="margin: 15px 0; color: #646970;">
									<?php _e( 'Ao ativar este recurso, você poderá inserir o formulário de pesquisa em qualquer página ou post através de shortcodes e blocos do WordPress.', 'lknaci-owh-domain-whois-rdap' ); ?>
								</p>
							</div>
						</fieldset>

						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px; margin-top: 15px;">
							<div style="margin-bottom: 15px;">
								<h3 style="font-size: 16px; margin: 0 0 10px 0; color: #3C434A;">
									<?php _e( 'Página de Resultados da Pesquisa', 'lknaci-owh-domain-whois-rdap' ); ?>
								</h3>
								<p style="font-size: 14px; margin: 0 0 15px 0; color: #3C434A;">
									<?php _e( 'Defina para onde o visitante será levado após pesquisar um domínio.', 'lknaci-owh-domain-whois-rdap' ); ?>
								</p>
								<hr style="border: 1px solid #CCCCCC; margin: 15px 0;">
							</div>

							<?php 
							$results_page = get_option( 'lknaci_owh_domain_whois_rdap_results_page', 0 );
							$pages = get_pages();
							?>
							
							<div style="margin-bottom: 20px;">
								<select 
									name="lknaci_owh_domain_whois_rdap_results_page" 
									style="width: 400px; height: 34px; border: 1px solid #8C8F94; border-radius: 3px; background: #fff; padding: 0 10px;"
								>
									<option value="0"><?php _e( 'Página Resultado Pesquisa domínios', 'lknaci-owh-domain-whois-rdap' ); ?></option>
									<?php foreach ( $pages as $page ) : ?>
									<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $results_page, $page->ID ); ?>>
										<?php echo esc_html( $page->post_title ); ?>
									</option>
									<?php endforeach; ?>
								</select>
								
							</div>

							<p class="description" style="color: #646970; margin-top: 20px;">
								<?php _e( 'Escolha a página que mostrará os resultados. Para funcionar, você precisa copiar e colar o shortcode [owh-rdap-whois-results] no conteúdo desta página (no editor de texto ou em um bloco de shortcode).', 'lknaci-owh-domain-whois-rdap' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>

				<!-- Visual Settings -->
				<tr>
					<th scope="row">
						<label><strong><?php _e( 'Configurações Visuais', 'lknaci-owh-domain-whois-rdap' ); ?></strong></label>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 15px;">
								<label for="lknaci_owh_domain_whois_rdap_available_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php _e( 'Texto para Domínio Disponível', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="lknaci_owh_domain_whois_rdap_available_text"
									name="lknaci_owh_domain_whois_rdap_available_text" 
									value="<?php echo esc_attr( get_option( 'lknaci_owh_domain_whois_rdap_available_text', 'Domínio disponível!' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
								/>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="lknaci_owh_domain_whois_rdap_unavailable_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php _e( 'Texto para Domínio Indisponível', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="lknaci_owh_domain_whois_rdap_unavailable_text"
									name="lknaci_owh_domain_whois_rdap_unavailable_text" 
									value="<?php echo esc_attr( get_option( 'lknaci_owh_domain_whois_rdap_unavailable_text', 'Domínio não disponível' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
								/>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="lknaci_owh_domain_whois_rdap_placeholder_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php _e( 'Placeholder do Campo de Pesquisa', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="lknaci_owh_domain_whois_rdap_placeholder_text"
									name="lknaci_owh_domain_whois_rdap_placeholder_text" 
									value="<?php echo esc_attr( get_option( 'lknaci_owh_domain_whois_rdap_placeholder_text', 'Digite o nome do domínio...' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
								/>
							</div>
						</fieldset>
					</td>
				</tr>

				<!-- Buy Button Settings -->
				<tr>
					<th scope="row">
						<label><strong><?php _e( 'Configurações do Botão de Compra', 'lknaci-owh-domain-whois-rdap' ); ?></strong></label>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 15px;">
								<label for="lknaci_owh_domain_whois_rdap_buy_button_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php _e( 'Texto do Botão', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="lknaci_owh_domain_whois_rdap_buy_button_text"
									name="lknaci_owh_domain_whois_rdap_buy_button_text" 
									value="<?php echo esc_attr( get_option( 'lknaci_owh_domain_whois_rdap_buy_button_text', 'Comprar Domínio' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
								/>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="lknaci_owh_domain_whois_rdap_buy_button_url" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php _e( 'URL do Botão de Compra', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="url" 
									id="lknaci_owh_domain_whois_rdap_buy_button_url"
									name="lknaci_owh_domain_whois_rdap_buy_button_url" 
									value="<?php echo esc_attr( get_option( 'lknaci_owh_domain_whois_rdap_buy_button_url', '' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
									placeholder="https://exemplo.com/comprar-dominio"
								/>
								<p class="description"><?php _e( 'URL para onde o usuário será redirecionado ao clicar no botão de compra.', 'lknaci-owh-domain-whois-rdap' ); ?></p>
							</div>

							<div style="margin-bottom: 15px;">
								<?php $buy_button_new_tab = get_option( 'lknaci_owh_domain_whois_rdap_buy_button_new_tab', true ); ?>
								<label style="display: flex; align-items: center;">
									<input 
										type="checkbox" 
										name="lknaci_owh_domain_whois_rdap_buy_button_new_tab" 
										value="1" 
										<?php checked( 1, $buy_button_new_tab ); ?>
										style="margin-right: 8px;"
									/> 
									<?php _e( 'Abrir link em nova aba', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
							</div>
						</fieldset>
					</td>
				</tr>

				<!-- Cache Settings -->
				<tr>
					<th scope="row">
						<label><strong><?php _e( 'Configurações de Cache', 'lknaci-owh-domain-whois-rdap' ); ?></strong></label>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 15px;">
								<label for="lknaci_owh_domain_whois_rdap_available_cache_time" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php _e( 'Cache para Domínios Disponíveis (segundos)', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="number" 
									id="lknaci_owh_domain_whois_rdap_available_cache_time"
									name="lknaci_owh_domain_whois_rdap_available_cache_time" 
									value="<?php echo esc_attr( get_option( 'lknaci_owh_domain_whois_rdap_available_cache_time', 3600 ) ); ?>" 
									min="0"
									class="small-text"
								/>
								<p class="description"><?php _e( 'Tempo em segundos para manter domínios disponíveis em cache. Padrão: 3600 (1 hora).', 'lknaci-owh-domain-whois-rdap' ); ?></p>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="lknaci_owh_domain_whois_rdap_unavailable_cache_time" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php _e( 'Cache para Domínios Indisponíveis (segundos)', 'lknaci-owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="number" 
									id="lknaci_owh_domain_whois_rdap_unavailable_cache_time"
									name="lknaci_owh_domain_whois_rdap_unavailable_cache_time" 
									value="<?php echo esc_attr( get_option( 'lknaci_owh_domain_whois_rdap_unavailable_cache_time', 86400 ) ); ?>" 
									min="0"
									class="small-text"
								/>
								<p class="description"><?php _e( 'Tempo em segundos para manter domínios indisponíveis em cache. Padrão: 86400 (24 horas).', 'lknaci-owh-domain-whois-rdap' ); ?></p>
							</div>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Salvar Configurações', 'lknaci-owh-domain-whois-rdap' ) ); ?>
	</form>

	<div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
		<h3><?php _e( 'Shortcodes Disponíveis', 'lknaci-owh-domain-whois-rdap' ); ?></h3>
		<p>
			<strong>[owh-rdap-whois-search]</strong> - <?php _e( 'Exibe o formulário de pesquisa de domínios', 'lknaci-owh-domain-whois-rdap' ); ?><br>
			<strong>[owh-rdap-whois-results]</strong> - <?php _e( 'Exibe os resultados da pesquisa (use na página de resultados)', 'lknaci-owh-domain-whois-rdap' ); ?>
		</p>
	</div>

	<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #0073aa; border-radius: 4px;">
		<h4><?php _e( 'Atualizações RDAP', 'lknaci-owh-domain-whois-rdap' ); ?></h4>
		<p><?php _e( 'O plugin mantém uma lista atualizada dos servidores RDAP da IANA. Esta lista é atualizada automaticamente a cada 24 horas.', 'lknaci-owh-domain-whois-rdap' ); ?></p>
		
		<button type="button" id="update-rdap-servers" class="button button-secondary">
			<?php _e( 'Atualizar Lista de Servidores RDAP Agora', 'lknaci-owh-domain-whois-rdap' ); ?>
		</button>
		
		<div id="update-rdap-status" style="margin-top: 10px;"></div>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#update-rdap-servers').on('click', function() {
		var button = $(this);
		var status = $('#update-rdap-status');
		
		button.prop('disabled', true).text('<?php _e( 'Atualizando...', 'lknaci-owh-domain-whois-rdap' ); ?>');
		status.html('<span style="color: #0073aa;"><?php _e( 'Atualizando lista de servidores RDAP...', 'lknaci-owh-domain-whois-rdap' ); ?></span>');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'lknaci_update_rdap_servers',
				nonce: '<?php echo wp_create_nonce( 'lknaci_owh_rdap_admin_nonce' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					status.html('<span style="color: #46b450;"><?php _e( 'Lista de servidores RDAP atualizada com sucesso!', 'lknaci-owh-domain-whois-rdap' ); ?></span>');
				} else {
					status.html('<span style="color: #dc3232;"><?php _e( 'Erro ao atualizar lista: ', 'lknaci-owh-domain-whois-rdap' ); ?>' + (response.data || 'Erro desconhecido') + '</span>');
				}
			},
			error: function() {
				status.html('<span style="color: #dc3232;"><?php _e( 'Erro de conexão ao atualizar lista.', 'lknaci-owh-domain-whois-rdap' ); ?></span>');
			},
			complete: function() {
				button.prop('disabled', false).text('<?php _e( 'Atualizar Lista de Servidores RDAP Agora', 'lknaci-owh-domain-whois-rdap' ); ?>');
			}
		});
	});
});
</script>
