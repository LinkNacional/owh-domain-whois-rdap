<?php

/**
 * Provide a public-facing view for domain search results
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://owhgroup.com.br
 * @since      1.0.0
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/public/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="owh-rdap-results-container">
	<?php if ( $result ) : ?>
		<?php if ( isset( $show_title ) && $show_title ) : ?>
			<div class="owh-rdap-search-header">
				<h3><?php printf( __( 'Resultado da pesquisa para: %s', 'lknaci-owh-domain-whois-rdap' ), '<strong>' . esc_html( $result->getDomain() ) . '</strong>' ); ?></h3>
			</div>
		<?php endif; ?>

		<div class="owh-rdap-result-content"><?php else : ?>
			<div class="owh-rdap-result-placeholder">
			<div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px;">
				<div style="font-size: 48px; margin-bottom: 15px;">🔍</div>
				<h3><?php _e( 'Aguardando Pesquisa', 'lknaci-owh-domain-whois-rdap' ); ?></h3>
				<p><?php _e( 'Os resultados da pesquisa de domínios aparecerão aqui.', 'lknaci-owh-domain-whois-rdap' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $result ) : ?>
		<?php if ( $result->hasError() ) : ?>
			<div class="owh-rdap-result-error">
				<div class="owh-rdap-error-icon">⚠️</div>
				<div class="owh-rdap-error-content">
					<h4><?php _e( 'Erro na Pesquisa', 'lknaci-owh-domain-whois-rdap' ); ?></h4>
					<p><?php echo esc_html( $result->getError() ); ?></p>
			</div>
		<?php elseif ( $result->isAvailable() ) : ?>
			<div class="owh-rdap-result-available">
				<div class="owh-rdap-available-icon">✅</div>
				<div class="owh-rdap-available-content">
					<h4><?php echo esc_html( $result->getStatus() ); ?></h4>
					<p><?php _e( 'Este domínio está disponível para registro!', 'lknaci-owh-domain-whois-rdap' ); ?></p>
					
					<?php 
					// New integration logic
					$integration_type = get_option( 'owh_rdap_integration_type', 'custom' );
					$domain_parts = explode( '.', $result->getDomain() );
					$sld = $domain_parts[0];
					$tld = isset( $domain_parts[1] ) ? $domain_parts[1] : '';

					if ( $integration_type === 'custom' ) {
						$custom_url = get_option( 'owh_rdap_custom_url', '' );
						if ( ! empty( $custom_url ) ) {
							$buy_url = str_replace(
								array( '{domain}', '{sld}', '{tld}' ),
								array( $result->getDomain(), $sld, $tld ),
								$custom_url
							);
							?>
							<div class="owh-rdap-buy-section">
								<a href="<?php echo esc_url( $buy_url ); ?>" 
								   class="owh-rdap-buy-button" 
								   target="_blank" 
								   rel="noopener noreferrer">
									<span class="dashicons dashicons-cart"></span>
									<?php _e( 'Registrar Domínio', 'lknaci-owh-domain-whois-rdap' ); ?>
								</a>
							</div>
							<?php
						}
					} elseif ( $integration_type === 'whmcs' ) {
						$whmcs_url = get_option( 'owh_rdap_whmcs_url', '' );
						if ( ! empty( $whmcs_url ) ) {
							$form_id = 'whmcs_' . str_replace( '.', '_', $result->getDomain() );
							?>
							<div class="owh-rdap-buy-section">
								<form style="display:none" method="post" name="<?php echo esc_attr( $form_id ); ?>" id="<?php echo esc_attr( $form_id ); ?>" action="<?php echo esc_url( rtrim( $whmcs_url, '/' ) . '/cart.php?a=add&domain=register' ); ?>" target="_self">
									<input type="hidden" name="domains[]" value="<?php echo esc_attr( $result->getDomain() ); ?>">
									<input type="hidden" name="domainsregperiod[<?php echo esc_attr( $result->getDomain() ); ?>]" value="1">
								</form>
								<button type="button" 
										class="owh-rdap-search-button" 
										onclick="document.getElementById('<?php echo esc_js( $form_id ); ?>').submit();">
									<span class="dashicons dashicons-cart"></span>
									<?php _e( 'Registrar Domínio', 'lknaci-owh-domain-whois-rdap' ); ?>
								</button>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>
		<?php else : ?>
			<div class="owh-rdap-result-unavailable">
				<div class="owh-rdap-unavailable-icon">❌</div>
				<div class="owh-rdap-unavailable-content">
					<h4><?php echo esc_html( $result->getStatus() ); ?></h4>
					<p><?php _e( 'Este domínio já está registrado e não está disponível.', 'lknaci-owh-domain-whois-rdap' ); ?></p>
					
					<?php 
					$rdap_data = $result->getRdapData();
					if ( $rdap_data && isset( $rdap_data['events'] ) ) :
					?>
					<div class="owh-rdap-domain-info">
						<h5><?php _e( 'Informações do Domínio:', 'lknaci-owh-domain-whois-rdap' ); ?></h5>
						<ul class="owh-rdap-domain-details">
							<?php 
							// Mapeamento de traduções para eventos RDAP
							$event_translations = array(
								'expiration' => 'Expiração',
								'registration' => 'Registro',
								'last changed' => 'Última Alteração',
								'last update of rdap database' => 'Última Atualização da Base RDAP',
								'reregistration' => 'Renovação',
								'transfer' => 'Transferência',
								'locked' => 'Bloqueado',
								'unlocked' => 'Desbloqueado',
							);
							
							foreach ( $rdap_data['events'] as $event ) : ?>
								<?php if ( isset( $event['eventAction'] ) && isset( $event['eventDate'] ) ) : ?>
								<?php 
									$event_key = strtolower( str_replace( '_', ' ', $event['eventAction'] ) );
									$event_label = isset( $event_translations[$event_key] ) ? $event_translations[$event_key] : ucfirst( str_replace( '_', ' ', $event['eventAction'] ) );
								?>
								<li>
									<strong><?php echo esc_html( $event_label ); ?>:</strong>
									<?php echo esc_html( date( 'd/m/Y', strtotime( $event['eventDate'] ) ) ); ?>
								</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

<style>
.owh-rdap-results-container {
	max-width: 600px;
	margin: 0 auto;
	padding: 20px;
}

.owh-rdap-search-header {
	text-align: center;
	margin-bottom: 30px;
}

.owh-rdap-search-header h3 {
	color: #333;
	font-size: 24px;
	margin: 0;
}

.owh-rdap-result-content {
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	padding: 30px;
	margin-bottom: 30px;
}

.owh-rdap-result-available,
.owh-rdap-result-unavailable,
.owh-rdap-result-error {
	display: flex;
	align-items: flex-start;
	gap: 20px;
}

.owh-rdap-available-icon,
.owh-rdap-unavailable-icon,
.owh-rdap-error-icon {
	font-size: 48px;
	line-height: 1;
}

.owh-rdap-available-content h4,
.owh-rdap-unavailable-content h4,
.owh-rdap-error-content h4 {
	margin: 0 0 10px 0;
	font-size: 20px;
}

.owh-rdap-available-content h4 {
	color: #46b450;
}

.owh-rdap-unavailable-content h4,
.owh-rdap-error-content h4 {
	color: #dc3232;
}

.owh-rdap-available-content p,
.owh-rdap-unavailable-content p,
.owh-rdap-error-content p {
	margin: 0 0 20px 0;
	color: #666;
	font-size: 16px;
	line-height: 1.5;
}

.owh-rdap-buy-section {
	margin-top: 20px;
}

.owh-rdap-buy-button {
	display: inline-flex;
	align-items: center;
	padding: 12px 24px;
	background: #46b450;
	color: white !important;
	text-decoration: none;
	border-radius: 6px;
	font-weight: 600;
	font-size: 16px;
	transition: background-color 0.3s ease;
	gap: 8px;
	border: none;
	cursor: pointer;
	font-family: inherit;
}

.owh-rdap-buy-button:hover {
	background: #3ba943;
	color: white !important;
	text-decoration: none;
}

.owh-rdap-whmcs-button {
	background: #007cba;
}

.owh-rdap-whmcs-button:hover {
	background: #005177;
}

.owh-rdap-buy-button .dashicons {
	font-size: 18px;
	width: 18px;
	height: 18px;
}

.owh-rdap-domain-info {
	margin-top: 20px;
	padding: 15px;
	background: #f9f9f9;
	border-radius: 6px;
}

.owh-rdap-domain-info h5 {
	margin: 0 0 15px 0;
	color: #333;
	font-size: 16px;
}

.owh-rdap-domain-details {
	margin: 0;
	padding: 0;
	list-style: none;
}

.owh-rdap-domain-details li {
	padding: 5px 0;
	border-bottom: 1px solid #eee;
	color: #666;
}

.owh-rdap-domain-details li:last-child {
	border-bottom: none;
}

.owh-rdap-domain-details strong {
	color: #333;
}

.owh-rdap-search-again {
	text-align: center;
	padding: 20px;
	background: #f9f9f9;
	border-radius: 8px;
}

.owh-rdap-search-again p {
	margin: 0 0 20px 0;
	color: #666;
	font-size: 16px;
}

@media (max-width: 600px) {
	.owh-rdap-result-available,
	.owh-rdap-result-unavailable,
	.owh-rdap-result-error {
		flex-direction: column;
		align-items: center;
		text-align: center;
		gap: 15px;
	}
	
	.owh-rdap-available-icon,
	.owh-rdap-unavailable-icon,
	.owh-rdap-error-icon {
		font-size: 36px;
	}
}
</style>
