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

// Set default values for custom attributes
$defaults = array(
	'no_result_text' => __( 'Aguardando Pesquisa', 'owh-domain-whois-rdap' ),
	'no_result_description' => __( 'Os resultados da pesquisa de domínios aparecerão aqui.', 'owh-domain-whois-rdap' ),
	'available_title' => __( 'Domínio Disponível', 'owh-domain-whois-rdap' ),
	'available_text' => __( 'Este domínio está disponível para registro!', 'owh-domain-whois-rdap' ),
	'unavailable_title' => __( 'Domínio Indisponível', 'owh-domain-whois-rdap' ),
	'unavailable_text' => __( 'Este domínio já está registrado e não está disponível.', 'owh-domain-whois-rdap' ),
	'buy_button_text' => __( 'Registrar Domínio', 'owh-domain-whois-rdap' ),
	'details_button_text' => __( 'Ver detalhes completos do WHOIS', 'owh-domain-whois-rdap' ),
	'show_icons' => true,
	'search_icon' => '🔍',
	'available_icon' => '✅',
	'unavailable_icon' => '❌'
);

// Merge custom attributes with defaults
if ( isset( $custom_attributes ) && is_array( $custom_attributes ) ) {
	foreach ( $defaults as $key => $default_value ) {
		if ( isset( $custom_attributes[ $key ] ) && $custom_attributes[ $key ] !== '' ) {
			$$key = $custom_attributes[ $key ];
		} else {
			$$key = $default_value;
		}
	}
} else {
	foreach ( $defaults as $key => $default_value ) {
		$$key = $default_value;
	}
}

// Generate custom styles
$container_styles = array();
if ( isset( $custom_attributes['border_width'] ) && $custom_attributes['border_width'] !== '' ) {
	$border_color = isset( $custom_attributes['border_color'] ) ? $custom_attributes['border_color'] : '#ddd';
	$container_styles[] = 'border: ' . intval( $custom_attributes['border_width'] ) . 'px solid ' . $border_color . ';';
}
if ( isset( $custom_attributes['border_radius'] ) && $custom_attributes['border_radius'] !== '' ) {
	$container_styles[] = 'border-radius: ' . intval( $custom_attributes['border_radius'] ) . 'px;';
}
if ( isset( $custom_attributes['background_color'] ) && $custom_attributes['background_color'] !== '' ) {
	$container_styles[] = 'background-color: ' . esc_attr( $custom_attributes['background_color'] ) . ';';
}
if ( isset( $custom_attributes['padding'] ) && $custom_attributes['padding'] !== '' ) {
	$container_styles[] = 'padding: ' . intval( $custom_attributes['padding'] ) . 'px;';
}

$container_style_attr = ! empty( $container_styles ) ? ' style="' . implode( ' ', $container_styles ) . '"' : '';

?>

<?php if ( isset( $custom_attributes['custom_css'] ) && ! empty( $custom_attributes['custom_css'] ) ) : ?>
<style>
	.owh-rdap-results-container { <?php echo esc_html( $custom_attributes['custom_css'] ); ?> }
</style>
<?php endif; ?>

<div class="owh-rdap-results-container"<?php echo $container_style_attr; ?>>
	<?php if ( $result ) : ?>
		<?php if ( isset( $show_title ) && $show_title ) : ?>
			<div class="owh-rdap-search-header">
				<?php 
				$title_text = isset( $custom_attributes['custom_title'] ) && ! empty( $custom_attributes['custom_title'] ) 
					? $custom_attributes['custom_title'] 
					: __( 'Resultado da pesquisa para: %s', 'owh-domain-whois-rdap' );
				
				if ( strpos( $title_text, '{domain}' ) !== false ) {
					$title_text = str_replace( '{domain}', '<strong>' . esc_html( $result->getDomain() ) . '</strong>', $title_text );
					echo '<h3>' . $title_text . '</h3>';
				} else {
					printf( '<h3>' . $title_text . '</h3>', '<strong>' . esc_html( $result->getDomain() ) . '</strong>' );
				}
				?>
			</div>
		<?php endif; ?>

		<div class="owh-rdap-result-content"><?php else : ?>
			<div class="owh-rdap-result-placeholder">
			<div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px;">
				<?php if ( $show_icons ) : ?>
					<div style="font-size: 48px; margin-bottom: 15px;"><?php echo esc_html( $search_icon ); ?></div>
				<?php endif; ?>
				<h3><?php echo esc_html( $no_result_text ); ?></h3>
				<p><?php echo esc_html( $no_result_description ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $result ) : ?>
		<?php if ( $result->isAvailable() ) : ?>
			<div class="owh-rdap-result-available">
				<?php if ( $show_icons ) : ?>
					<div class="owh-rdap-available-icon"><?php echo esc_html( $available_icon ); ?></div>
				<?php endif; ?>
				<div class="owh-rdap-available-content">
					<h4 style="<?php echo isset( $custom_attributes['available_color'] ) ? 'color: ' . esc_attr( $custom_attributes['available_color'] ) . ';' : ''; ?>"><?php echo esc_html( $available_title ); ?></h4>
					<p><?php echo esc_html( $available_text ); ?></p>
					
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
								   rel="noopener noreferrer"
								   style="<?php echo isset( $custom_attributes['available_color'] ) ? 'background: ' . esc_attr( $custom_attributes['available_color'] ) . ';' : ''; ?>">
									<span class="dashicons dashicons-cart"></span>
									<?php echo esc_html( $buy_button_text ); ?>
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
										class="owh-rdap-search-button result" 
										onclick="document.getElementById('<?php echo esc_js( $form_id ); ?>').submit();"
										style="<?php echo isset( $custom_attributes['available_color'] ) ? 'background: ' . esc_attr( $custom_attributes['available_color'] ) . ';' : ''; ?>">
									<span class="dashicons dashicons-cart"></span>
									<?php echo esc_html( $buy_button_text ); ?>
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
				<?php if ( $show_icons ) : ?>
					<div class="owh-rdap-unavailable-icon"><?php echo esc_html( $unavailable_icon ); ?></div>
				<?php endif; ?>
				<div class="owh-rdap-unavailable-content">
					<h4 style="<?php echo isset( $custom_attributes['unavailable_color'] ) ? 'color: ' . esc_attr( $custom_attributes['unavailable_color'] ) . ';' : ''; ?>"><?php echo esc_html( $unavailable_title ); ?></h4>
					<p><?php echo esc_html( $unavailable_text ); ?></p>
					
					<?php 
					// Check if WHOIS details page is configured
					$whois_details_page_id = get_option( 'owh_rdap_whois_details_page', 0 );
					if ( ! empty( $whois_details_page_id ) && $whois_details_page_id > 0 ) :
						$whois_details_url = get_permalink( $whois_details_page_id );
						if ( $whois_details_url ) :
							$whois_details_url = add_query_arg( 'domain', $result->getDomain(), $whois_details_url );
					?>
					<div class="owh-rdap-whois-details-link">
						<p>
							<a href="<?php echo esc_url( $whois_details_url ); ?>" 
							   class="owh-rdap-details-button"
							   style="<?php echo isset( $custom_attributes['unavailable_color'] ) ? 'background: ' . esc_attr( $custom_attributes['unavailable_color'] ) . ';' : ''; ?>">
								<span class="dashicons dashicons-info"></span>
								<?php echo esc_html( $details_button_text ); ?>
							</a>
						</p>
					</div>
					<?php 
						endif;
					endif; 
					?>
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

.owh-rdap-result-available,
.owh-rdap-result-unavailable {
	display: flex;
	align-items: flex-start;
	gap: 20px;
}

.owh-rdap-available-icon,
.owh-rdap-unavailable-icon {
	font-size: 48px;
	line-height: 1;
}

.owh-rdap-available-content h4,
.owh-rdap-unavailable-content h4 {
	margin: 0 0 10px 0;
	font-size: 20px;
}

.owh-rdap-available-content h4 {
	color: #46b450;
}

.owh-rdap-unavailable-content h4 {
	color: #dc3232;
}

.owh-rdap-available-content p,
.owh-rdap-unavailable-content p {
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
	.owh-rdap-result-unavailable {
		flex-direction: column;
		align-items: center;
		text-align: center;
		gap: 15px;
	}
	
	.owh-rdap-available-icon,
	.owh-rdap-unavailable-icon {
		font-size: 36px;
	}
}
</style>
