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
							<strong><?php esc_html_e( 'Ferramenta de Pesquisa de Domínios', 'owh-domain-whois-rdap' ); ?></strong>
						</label>
						<p class="description">
							<?php esc_html_e( 'Ative a ferramenta de pesquisa de domínios (Whois) no seu site.', 'owh-domain-whois-rdap' ); ?>
						</p>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 20px; border-bottom: 1px solid #CCCCCC; padding-bottom: 15px;">
								<h3 style="font-size: 16px; margin: 0 0 10px 0; color: #3C434A;">
									<?php esc_html_e( 'Ativar Pesquisa de Domínios (RDAP/WHOIS)', 'owh-domain-whois-rdap' ); ?>
								</h3>
								<p style="font-size: 14px; margin: 0 0 15px 0; color: #3C434A;">
									<?php esc_html_e( 'Ofereça aos seus visitantes uma ferramenta para pesquisar a disponibilidade de domínios.', 'owh-domain-whois-rdap' ); ?>
								</p>
							</div>

							<div style="margin-bottom: 15px;">
								<?php 
								$enable_search = get_option( 'owh_domain_whois_rdap_enable_search', false );
								?>
								<label style="display: flex; align-items: center; margin-bottom: 15px;">
									<input 
										type="radio" 
										name="owh_domain_whois_rdap_enable_search" 
										value="1" 
										<?php checked( 1, $enable_search ); ?>
										style="margin-right: 8px;"
									/> 
									<?php esc_html_e( 'Ativar', 'owh-domain-whois-rdap' ); ?>
								</label>
								
								<label style="display: flex; align-items: center; margin-bottom: 15px;">
									<input 
										type="radio" 
										name="owh_domain_whois_rdap_enable_search" 
										value="0" 
										<?php checked( 0, $enable_search ); ?>
										style="margin-right: 8px;"
									/> 
									<?php esc_html_e( 'Desativar', 'owh-domain-whois-rdap' ); ?>
								</label>
								
								<p class="description" style="margin: 15px 0; color: #646970;">
									<?php esc_html_e( 'Ao ativar este recurso, você poderá inserir o formulário de pesquisa em qualquer página ou post através de shortcodes e blocos do WordPress.', 'owh-domain-whois-rdap' ); ?>
								</p>
							</div>
						</fieldset>

						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px; margin-top: 15px;">
							<div style="margin-bottom: 15px;">
								<h3 style="font-size: 16px; margin: 0 0 10px 0; color: #3C434A;">
									<?php esc_html_e( 'Página de Resultados da Pesquisa', 'owh-domain-whois-rdap' ); ?>
								</h3>
								<p style="font-size: 14px; margin: 0 0 15px 0; color: #3C434A;">
									<?php esc_html_e( 'Defina para onde o visitante será levado após pesquisar um domínio.', 'owh-domain-whois-rdap' ); ?>
								</p>
								<hr style="border: 1px solid #CCCCCC; margin: 15px 0;">
							</div>

							<?php 
							$results_page = get_option( 'owh_domain_whois_rdap_results_page', 0 );
							$pages = get_pages();
							?>
							
							<div style="margin-bottom: 20px;">
								<select 
									name="owh_domain_whois_rdap_results_page" 
									style="width: 400px; height: 34px; border: 1px solid #8C8F94; border-radius: 3px; background: #fff; padding: 0 10px;"
								>
									<option value="0"><?php esc_html_e( 'Página Resultado Pesquisa domínios', 'owh-domain-whois-rdap' ); ?></option>
									<?php foreach ( $pages as $page ) : ?>
									<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $results_page, $page->ID ); ?>>
										<?php echo esc_html( $page->post_title ); ?>
									</option>
									<?php endforeach; ?>
								</select>
								
							</div>

							<p class="description" style="color: #646970; margin-top: 20px;">
								<?php esc_html_e( 'Escolha a página que mostrará os resultados. Para funcionar, você precisa copiar e colar o shortcode [owh-rdap-whois-results] no conteúdo desta página (no editor de texto ou em um bloco de shortcode).', 'owh-domain-whois-rdap' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>

				<!-- Visual Settings -->
				<tr>
					<th scope="row">
						<label><strong><?php esc_html_e( 'Configurações Visuais', 'owh-domain-whois-rdap' ); ?></strong></label>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 15px;">
								<label for="owh_domain_whois_rdap_available_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php esc_html_e( 'Texto para Domínio Disponível', 'owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="owh_domain_whois_rdap_available_text"
									name="owh_domain_whois_rdap_available_text" 
									value="<?php echo esc_attr( get_option( 'owh_domain_whois_rdap_available_text', 'Domínio disponível!' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
								/>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="owh_domain_whois_rdap_unavailable_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php esc_html_e( 'Texto para Domínio Indisponível', 'owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="owh_domain_whois_rdap_unavailable_text"
									name="owh_domain_whois_rdap_unavailable_text" 
									value="<?php echo esc_attr( get_option( 'owh_domain_whois_rdap_unavailable_text', 'Domínio não disponível' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
								/>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="owh_domain_whois_rdap_placeholder_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php esc_html_e( 'Placeholder do Campo de Pesquisa', 'owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="owh_domain_whois_rdap_placeholder_text"
									name="owh_domain_whois_rdap_placeholder_text" 
									value="<?php echo esc_attr( get_option( 'owh_domain_whois_rdap_placeholder_text', 'Digite o nome do domínio...' ) ); ?>" 
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
						<label><strong><?php esc_html_e( 'Configurações do Botão de Compra', 'owh-domain-whois-rdap' ); ?></strong></label>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 15px;">
								<label for="owh_domain_whois_rdap_buy_button_text" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php esc_html_e( 'Texto do Botão', 'owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="text" 
									id="owh_domain_whois_rdap_buy_button_text"
									name="owh_domain_whois_rdap_buy_button_text" 
									value="<?php echo esc_attr( get_option( 'owh_domain_whois_rdap_buy_button_text', 'Comprar Domínio' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
								/>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="owh_domain_whois_rdap_buy_button_url" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php esc_html_e( 'URL do Botão de Compra', 'owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="url" 
									id="owh_domain_whois_rdap_buy_button_url"
									name="owh_domain_whois_rdap_buy_button_url" 
									value="<?php echo esc_attr( get_option( 'owh_domain_whois_rdap_buy_button_url', '' ) ); ?>" 
									class="regular-text"
									style="width: 400px;"
									placeholder="https://exemplo.com/comprar-dominio"
								/>
								<p class="description"><?php esc_html_e( 'URL para onde o usuário será redirecionado ao clicar no botão de compra.', 'owh-domain-whois-rdap' ); ?></p>
							</div>

							<div style="margin-bottom: 15px;">
								<?php $buy_button_new_tab = get_option( 'owh_domain_whois_rdap_buy_button_new_tab', true ); ?>
								<label style="display: flex; align-items: center;">
									<input 
										type="checkbox" 
										name="owh_domain_whois_rdap_buy_button_new_tab" 
										value="1" 
										<?php checked( 1, $buy_button_new_tab ); ?>
										style="margin-right: 8px;"
									/> 
									<?php esc_html_e( 'Abrir link em nova aba', 'owh-domain-whois-rdap' ); ?>
								</label>
							</div>
						</fieldset>
					</td>
				</tr>

				<!-- Cache Settings -->
				<tr>
					<th scope="row">
						<label><strong><?php esc_html_e( 'Configurações de Cache', 'owh-domain-whois-rdap' ); ?></strong></label>
					</th>
					<td>
						<fieldset style="background: #fff; border: 1px solid #DFDFDF; border-radius: 4px; padding: 15px;">
							<div style="margin-bottom: 15px;">
								<label for="owh_domain_whois_rdap_available_cache_time" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php esc_html_e( 'Cache para Domínios Disponíveis (segundos)', 'owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="number" 
									id="owh_domain_whois_rdap_available_cache_time"
									name="owh_domain_whois_rdap_available_cache_time" 
									value="<?php echo esc_attr( get_option( 'owh_domain_whois_rdap_available_cache_time', 3600 ) ); ?>" 
									min="0"
									class="small-text"
								/>
								<p class="description"><?php esc_html_e( 'Tempo em segundos para manter domínios disponíveis em cache. Padrão: 3600 (1 hora).', 'owh-domain-whois-rdap' ); ?></p>
							</div>

							<div style="margin-bottom: 15px;">
								<label for="owh_domain_whois_rdap_unavailable_cache_time" style="display: block; margin-bottom: 5px; font-weight: 600;">
									<?php esc_html_e( 'Cache para Domínios Indisponíveis (segundos)', 'owh-domain-whois-rdap' ); ?>
								</label>
								<input 
									type="number" 
									id="owh_domain_whois_rdap_unavailable_cache_time"
									name="owh_domain_whois_rdap_unavailable_cache_time" 
									value="<?php echo esc_attr( get_option( 'owh_domain_whois_rdap_unavailable_cache_time', 86400 ) ); ?>" 
									min="0"
									class="small-text"
								/>
								<p class="description"><?php esc_html_e( 'Tempo em segundos para manter domínios indisponíveis em cache. Padrão: 86400 (24 horas).', 'owh-domain-whois-rdap' ); ?></p>
							</div>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Salvar Configurações', 'owh-domain-whois-rdap' ) ); ?>
	</form>

	<div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
		<h3><?php esc_html_e( 'Shortcodes Disponíveis', 'owh-domain-whois-rdap' ); ?></h3>
		<p>
			<strong>[owh-rdap-whois-search]</strong> - <?php esc_html_e( 'Exibe o formulário de pesquisa de domínios', 'owh-domain-whois-rdap' ); ?><br>
			<strong>[owh-rdap-whois-results]</strong> - <?php esc_html_e( 'Exibe os resultados da pesquisa (use na página de resultados)', 'owh-domain-whois-rdap' ); ?>
		</p>
	</div>

	<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #0073aa; border-radius: 4px;">
		<h4><?php esc_html_e( 'Atualizações RDAP', 'owh-domain-whois-rdap' ); ?></h4>
		<p><?php esc_html_e( 'O plugin mantém uma lista atualizada dos servidores RDAP da IANA. Esta lista é atualizada automaticamente a cada 24 horas.', 'owh-domain-whois-rdap' ); ?></p>
		
		<button type="button" id="update-rdap-servers" class="button button-secondary">
			<?php esc_html_e( 'Atualizar Lista de Servidores RDAP Agora', 'owh-domain-whois-rdap' ); ?>
		</button>
		
		<div id="update-rdap-status" style="margin-top: 10px;"></div>
	</div>
</div>
