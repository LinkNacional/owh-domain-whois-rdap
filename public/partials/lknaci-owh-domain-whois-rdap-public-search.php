<?php

/**
 * Provide a public-facing view for the domain search form
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

<div class="owh-rdap-search-container">
	<?php if ( isset( $show_title ) && $show_title ) : ?>
		<h3 class="owh-rdap-search-title"><?php _e( 'Pesquisar Domínio', 'lknaci-owh-domain-whois-rdap' ); ?></h3>
	<?php endif; ?>
	
	<form id="owh-rdap-search-form" class="owh-rdap-search-form" method="<?php echo $results_page ? 'get' : 'post'; ?>" <?php echo $results_page ? 'action="' . get_permalink( $results_page ) . '"' : ''; ?>>
		<div class="owh-rdap-search-wrapper">
			<div class="owh-rdap-search-input-wrapper">
				<input 
					type="text" 
					id="owh-rdap-domain-input" 
					name="domain" 
					class="owh-rdap-domain-input" 
					placeholder="<?php echo esc_attr( $placeholder ); ?>" 
					value="<?php echo isset( $_GET['domain'] ) ? esc_attr( sanitize_text_field( $_GET['domain'] ) ) : ''; ?>"
					required 
				/>
				<button type="submit" id="owh-rdap-search-button" class="owh-rdap-search-button">
					<span class="owh-rdap-search-text"><?php echo esc_html( $button_text ); ?></span>
					<span class="owh-rdap-search-loading" style="display: none;">
						<span class="owh-rdap-spinner"></span>
						<?php _e( 'Pesquisando...', 'lknaci-owh-domain-whois-rdap' ); ?>
					</span>
				</button>
			</div>

			<?php if ( $atts['show_examples'] === 'true' ) : ?>
			<div class="owh-rdap-search-examples">
				<small class="owh-rdap-examples-text">
					<?php _e( 'Exemplos:', 'lknaci-owh-domain-whois-rdap' ); ?> 
					<span class="owh-rdap-example-domain" data-domain="exemplo.com">exemplo.com</span>, 
					<span class="owh-rdap-example-domain" data-domain="meusite.com.br">meusite.com.br</span>, 
					<span class="owh-rdap-example-domain" data-domain="minhaempresa.org">minhaempresa.org</span>
				</small>
			</div>
			<?php endif; ?>
		</div>

		<?php if ( ! $results_page ) : ?>
		<input type="hidden" name="action" value="lknaci_check_domain" />
		<input type="hidden" id="owh-rdap-nonce" name="nonce" value="<?php echo wp_create_nonce( 'lknaci_owh_rdap_public_nonce' ); ?>" />
		<?php endif; ?>
	</form>

	<?php if ( ! $results_page ) : ?>
	<div id="owh-rdap-search-results" class="owh-rdap-search-results" style="display: none;"></div>
	<?php endif; ?>
</div>

<style>
.owh-rdap-search-container {
	max-width: 600px;
	margin: 0 auto;
	padding: 20px;
}

.owh-rdap-search-form {
	margin-bottom: 20px;
}

.owh-rdap-search-wrapper {
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	padding: 20px;
}

.owh-rdap-search-input-wrapper {
	display: flex;
	gap: 10px;
	margin-bottom: 15px;
}

.owh-rdap-domain-input {
	flex: 1;
	padding: 12px 16px;
	border: 2px solid #ddd;
	border-radius: 6px;
	font-size: 16px;
	transition: border-color 0.3s ease;
}

.owh-rdap-domain-input:focus {
	outline: none;
	border-color: #0073aa;
	box-shadow: 0 0 0 1px #0073aa;
}

.owh-rdap-search-button {
	padding: 12px 24px;
	background: #0073aa;
	color: white;
	border: none;
	border-radius: 6px;
	font-size: 16px;
	font-weight: 600;
	cursor: pointer;
	transition: background-color 0.3s ease;
	min-width: 120px;
}

.owh-rdap-search-button:hover {
	background: #005a87;
}

.owh-rdap-search-button:disabled {
	background: #ccc;
	cursor: not-allowed;
}

.owh-rdap-search-loading {
	display: flex;
	align-items: center;
	gap: 8px;
}

.owh-rdap-spinner {
	width: 16px;
	height: 16px;
	border: 2px solid rgba(255, 255, 255, 0.3);
	border-top: 2px solid white;
	border-radius: 50%;
	animation: owh-rdap-spin 1s linear infinite;
}

@keyframes owh-rdap-spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

.owh-rdap-search-examples {
	text-align: center;
	margin-top: 15px;
}

.owh-rdap-examples-text {
	color: #666;
	font-size: 14px;
}

.owh-rdap-example-domain {
	color: #0073aa;
	cursor: pointer;
	text-decoration: underline;
}

.owh-rdap-example-domain:hover {
	color: #005a87;
}

.owh-rdap-search-results {
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	padding: 20px;
	margin-top: 20px;
}

.owh-rdap-result-available {
	color: #46b450;
	font-size: 18px;
	font-weight: 600;
	margin-bottom: 15px;
}

.owh-rdap-result-unavailable {
	color: #dc3232;
	font-size: 18px;
	font-weight: 600;
	margin-bottom: 15px;
}

.owh-rdap-result-error {
	color: #dc3232;
	font-size: 16px;
	margin-bottom: 15px;
}

.owh-rdap-buy-button {
	display: inline-block;
	padding: 12px 24px;
	background: #46b450;
	color: white !important;
	text-decoration: none;
	border-radius: 6px;
	font-weight: 600;
	transition: background-color 0.3s ease;
}

.owh-rdap-buy-button:hover {
	background: #3ba943;
	color: white !important;
}

.owh-rdap-buy-button .dashicons {
	margin-right: 5px;
	vertical-align: middle;
}

@media (max-width: 600px) {
	.owh-rdap-search-input-wrapper {
		flex-direction: column;
	}
	
	.owh-rdap-search-button {
		width: 100%;
	}
}
</style>
