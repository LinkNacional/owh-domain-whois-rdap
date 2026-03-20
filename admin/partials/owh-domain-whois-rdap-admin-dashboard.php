<?php
/**
 * Provide a admin area dashboard view for the plugin
 *
 * @link       https://owh.digital
 * @since      1.2.2
 *
 * @package    Owh_Domain_Whois_Rdap
 * @subpackage Owh_Domain_Whois_Rdap/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logo_url = plugin_dir_url( dirname( __FILE__ ) ) . '../public/images/logo-owh-open-web-hosting.webp';
?>

<div class="owh-dashboard-wrapper">
	<!-- Header Section -->
	<div class="owh-dashboard-header">
		<div class="owh-logo-container">
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="OWH Open Web Hosting" class="owh-logo">
		</div>
		<div class="owh-header-text">
			<p><?php esc_html_e( 'Plugin WordPress moderno para verificação de domínios via protocolo RDAP. Configure shortcodes, blocos Gutenberg e integrações WooCommerce facilmente.', 'owh-domain-whois-rdap' ); ?></p>
		</div>
	</div>

	<!-- Navigation Tabs -->
	<div class="owh-nav-tabs">
		<a href="#home" class="owh-nav-tab owh-nav-tab-active" data-tab="home"><?php esc_html_e( 'Home', 'owh-domain-whois-rdap' ); ?></a>
		<a href="#plugins" class="owh-nav-tab" data-tab="plugins"><?php esc_html_e( 'Plugins úteis', 'owh-domain-whois-rdap' ); ?></a>
		<a href="#changelog" class="owh-nav-tab" data-tab="changelog"><?php esc_html_e( 'Registro de alterações', 'owh-domain-whois-rdap' ); ?></a>
	</div>

	<!-- Main Content -->
	<div class="owh-dashboard-content">
		
		<!-- Home Tab Content -->
		<div id="home-content" class="owh-tab-content owh-tab-active">
			<div class="owh-dashboard-left">
				<div class="owh-config-section">
					<h2><?php esc_html_e( 'Atalhos Configurações', 'owh-domain-whois-rdap' ); ?></h2>
					
					<div class="owh-config-card">
						<div class="owh-config-icon">
							<span class="dashicons dashicons-admin-site-alt3"></span>
						</div>
						<div class="owh-config-content">
							<h3><?php esc_html_e( 'RDAP DOMAINS', 'owh-domain-whois-rdap' ); ?></h3>
							<p><?php esc_html_e( 'Verificador de domínios moderno utilizando protocolo RDAP. Configure a pesquisa de disponibilidade, cache, páginas de resultados e integração com WooCommerce.', 'owh-domain-whois-rdap' ); ?></p>
							<div class="owh-config-actions">
								<a href="<?php echo esc_url( 'https://github.com/LinkNacional/owh-domain-whois-rdap/blob/main/README.md' ); ?>" target="_blank" class="owh-btn owh-btn-secondary">
									<span class="dashicons dashicons-media-document"></span>
									<?php esc_html_e( 'Documentação', 'owh-domain-whois-rdap' ); ?>
								</a>
								<a href="<?php echo admin_url( 'admin.php?page=owh-rdap-settings' ); ?>" class="owh-btn owh-btn-primary">
									<span class="dashicons dashicons-admin-generic"></span>
									<?php esc_html_e( 'Configurar', 'owh-domain-whois-rdap' ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="owh-dashboard-right">
				<!-- Support Sections -->
				<div class="owh-support-sections">
					<div class="owh-support-section">
						<div class="owh-support-icon">
							<span class="dashicons dashicons-book-alt"></span>
						</div>
						<div class="owh-support-content">
							<h3><?php esc_html_e( 'Base de conhecimento', 'owh-domain-whois-rdap' ); ?></h3>
							<p><?php esc_html_e( 'Acesse nossa documentação completa e aprenda dicas e truques sobre o plugin OWH Domain WHOIS RDAP e suas funcionalidades.', 'owh-domain-whois-rdap' ); ?></p>
							<a href="<?php echo esc_url( 'https://github.com/LinkNacional/owh-domain-whois-rdap/blob/main/README.md' ); ?>" target="_blank" class="owh-btn owh-btn-outline"><?php esc_html_e( 'Ver a documentação', 'owh-domain-whois-rdap' ); ?></a>
						</div>
					</div>

					<div class="owh-support-section">
						<div class="owh-support-icon">
							<span class="dashicons dashicons-sos"></span>
						</div>
						<div class="owh-support-content">
							<h3><?php esc_html_e( 'Suporte', 'owh-domain-whois-rdap' ); ?></h3>
							<p><?php esc_html_e( 'Precisa de ajuda? Entre em contato conosco através do nosso sistema de atendimento. Nossa equipe está pronta para ajudá-lo com o plugin.', 'owh-domain-whois-rdap' ); ?></p>
							<a href="<?php echo esc_url( 'https://www.linknacional.com.br/atendimento/' ); ?>" target="_blank" class="owh-btn owh-btn-outline"><?php esc_html_e( 'Enviar chamado', 'owh-domain-whois-rdap' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Plugins Tab Content -->
		<div id="plugins-content" class="owh-tab-content" style="display: none;">
			<div class="owh-dashboard-left">
				<!-- Link Invoice Cards -->
				<div class="owh-plugins-grid">
					<div class="owh-plugin-card">
						<h3><?php esc_html_e( 'Link Invoice Payment for WooCommerce', 'owh-domain-whois-rdap' ); ?></h3>
						<p><?php esc_html_e( 'Poderosa extensão gratuita para WooCommerce que simplifica o faturamento online. Gere faturas únicas e recorrentes com links de pagamento seguros para múltiplas formas de pagamento.', 'owh-domain-whois-rdap' ); ?></p>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/invoice-payment-for-woocommerce/' ); ?>" target="_blank" class="owh-btn owh-btn-outline"><?php esc_html_e( 'Instalar', 'owh-domain-whois-rdap' ); ?></a>
					</div>

					<div class="owh-plugin-card">
						<h3><?php esc_html_e( 'Cielo for WooCommerce', 'owh-domain-whois-rdap' ); ?></h3>
						<p><?php esc_html_e( 'Gateway de pagamento robusto que integra sua loja WooCommerce com a Cielo. Aceite PIX, Google Pay, cartões de crédito e débito com suporte 3DS para transações seguras.', 'owh-domain-whois-rdap' ); ?></p>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/lkn-wc-gateway-cielo/' ); ?>" target="_blank" class="owh-btn owh-btn-outline"><?php esc_html_e( 'Baixar', 'owh-domain-whois-rdap' ); ?></a>
					</div>

					<div class="owh-plugin-card">
						<h3><?php esc_html_e( 'Rede Itaú for WooCommerce', 'owh-domain-whois-rdap' ); ?></h3>
						<p><?php esc_html_e( 'Gateway de pagamento para Rede Itaú integrado ao WooCommerce. Aceite PIX, cartões de crédito e débito com parcelamento, tokenização e autenticação 3DS.', 'owh-domain-whois-rdap' ); ?></p>
						<a href="<?php echo esc_url( 'https://br.wordpress.org/plugins/woo-rede/' ); ?>" target="_blank" class="owh-btn owh-btn-outline"><?php esc_html_e( 'Instalar', 'owh-domain-whois-rdap' ); ?></a>
					</div>
				</div>
			</div>

			<div class="owh-dashboard-right">
				<!-- Support Sections -->
				<div class="owh-support-sections">
					<div class="owh-support-section">
						<div class="owh-support-icon">
							<span class="dashicons dashicons-book-alt"></span>
						</div>
						<div class="owh-support-content">
							<h3><?php esc_html_e( 'Base de conhecimento', 'owh-domain-whois-rdap' ); ?></h3>
							<p><?php esc_html_e( 'Acesse nossa documentação completa e aprenda dicas e truques sobre o plugin OWH Domain WHOIS RDAP e suas funcionalidades.', 'owh-domain-whois-rdap' ); ?></p>
							<a href="<?php echo esc_url( 'https://github.com/LinkNacional/owh-domain-whois-rdap/blob/main/README.md' ); ?>" target="_blank" class="owh-btn owh-btn-outline"><?php esc_html_e( 'Ver a documentação', 'owh-domain-whois-rdap' ); ?></a>
						</div>
					</div>

					<div class="owh-support-section">
						<div class="owh-support-icon">
							<span class="dashicons dashicons-sos"></span>
						</div>
						<div class="owh-support-content">
							<h3><?php esc_html_e( 'Suporte', 'owh-domain-whois-rdap' ); ?></h3>
							<p><?php esc_html_e( 'Precisa de ajuda? Entre em contato conosco através do nosso sistema de atendimento. Nossa equipe está pronta para ajudá-lo com o plugin.', 'owh-domain-whois-rdap' ); ?></p>
							<a href="<?php echo esc_url( 'https://www.linknacional.com.br/atendimento/' ); ?>" target="_blank" class="owh-btn owh-btn-outline"><?php esc_html_e( 'Enviar chamado', 'owh-domain-whois-rdap' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Changelog Tab Content -->
		<div id="changelog-content" class="owh-tab-content" style="display: none;">
			<div class="owh-changelog-wrapper">
				<div class="owh-changelog-content">
					<?php
					// Read and parse the CHANGELOG.md file
					$changelog_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'CHANGELOG.md';
					if ( file_exists( $changelog_path ) ) {
						$changelog_content = file_get_contents( $changelog_path );
						
						// Split content by lines for better processing
						$lines = explode( "\n", $changelog_content );
						$html_output = '';
						$in_list = false;
						
						foreach ( $lines as $line ) {
							$line = trim( $line );
							
							// Skip empty lines
							if ( empty( $line ) ) {
								continue;
							}
							
							// Process headers (versions)
							if ( preg_match( '/^# (.+)$/', $line, $matches ) ) {
								// Close previous list if open
								if ( $in_list ) {
									$html_output .= '</ul>';
									$in_list = false;
								}
								$html_output .= '<h1>' . esc_html( $matches[1] ) . '</h1>';
							}
							// Process list items
							elseif ( preg_match( '/^\* (.+)$/', $line, $matches ) ) {
								// Open list if not already open
								if ( ! $in_list ) {
									$html_output .= '<ul>';
									$in_list = true;
								}
								$html_output .= '<li>' . esc_html( $matches[1] ) . '</li>';
							}
						}
						
						// Close final list if still open
						if ( $in_list ) {
							$html_output .= '</ul>';
						}
						
						echo $html_output;
					} else {
						echo '<p>' . esc_html__( 'CHANGELOG.md não encontrado.', 'owh-domain-whois-rdap' ) . '</p>';
					}
					?>
				</div>
			</div>
		</div>

	</div>
</div>
