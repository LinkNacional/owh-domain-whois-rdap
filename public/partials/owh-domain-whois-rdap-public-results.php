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

<!-- Custom CSS is now handled through wp_add_inline_style in the shortcode method -->

<div class="owh-rdap-results-container"<?php echo esc_attr( $container_style_attr ); ?>>
	<?php if ( $result ) : ?>
		<?php if ( isset( $show_title ) && $show_title ) : ?>
			<div class="owh-rdap-search-header">
				<?php 
				// translators: %s is the domain name being searched
				$title_text = isset( $custom_attributes['custom_title'] ) && ! empty( $custom_attributes['custom_title'] ) 
					? $custom_attributes['custom_title'] 
					: __( 'Resultado da pesquisa para: %s', 'owh-domain-whois-rdap' );
				
				if ( strpos( $title_text, '{domain}' ) !== false ) {
					$title_text = str_replace( '{domain}', '<strong>' . esc_html( $result->getDomain() ) . '</strong>', $title_text );
					echo '<h3>' . wp_kses_post( $title_text ) . '</h3>';
				} else {
					printf( '<h3>' . esc_html( $title_text ) . '</h3>', '<strong>' . esc_html( $result->getDomain() ) . '</strong>' );
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
		<?php if ( $result->hasError() ) : ?>
			<div class="owh-rdap-result-error">
				<?php if ( $show_icons ) : ?>
					<div class="owh-rdap-error-icon">⚠️</div>
				<?php endif; ?>
				<div class="owh-rdap-error-content">
					<h4 style="color: #dc3232;"><?php echo esc_html__( 'Erro na Pesquisa', 'owh-domain-whois-rdap' ); ?></h4>
					<p><?php echo esc_html( $result->getError() ); ?></p>
				</div>
			</div>
		<?php elseif ( $result->isAvailable() ) : ?>
			<div class="owh-rdap-result-available">
				<?php if ( $show_icons ) : ?>
					<div class="owh-rdap-available-icon"><?php echo esc_html( $available_icon ); ?></div>
				<?php endif; ?>
				<div class="owh-rdap-available-content">
					<h4 style="<?php echo isset( $custom_attributes['available_color'] ) ? 'color: ' . esc_attr( $custom_attributes['available_color'] ) . ';' : ''; ?>"><?php echo esc_html( $available_title ); ?></h4>
					<p><?php echo esc_html( $available_text ); ?></p>
					
					<?php 
					// New integration logic
					$integration_type = get_option( 'owh_rdap_integration_type', 'none' );
					
					// Only show buy button if integration type is not 'none'
					if ( $integration_type !== 'none' ) {
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
					} // Close the if ( $integration_type !== 'none' ) block
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

<!-- Inline styles moved to CSS file: public/css/owh-domain-whois-rdap-public.css -->
