<?php

/**
 * Fired during plugin activation
 *
 * @link       https://owhgroup.com.br
 * @since      1.0.0
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/includes
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class Lknaci_Owh_Domain_Whois_Rdap_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Set default options
		$default_options = array(
			'enable_search' => false,
			'results_page' => 0,
			'available_text' => 'Domínio disponível!',
			'unavailable_text' => 'Domínio não disponível',
			'placeholder_text' => 'Digite o nome do domínio...',
			'loading_image' => '',
			'buy_button_text' => 'Comprar Domínio',
			'buy_button_icon' => 'dashicons-cart',
			'buy_button_url' => '',
			'buy_button_new_tab' => true,
			'available_cache_time' => 3600,
			'unavailable_cache_time' => 86400,
		);

		foreach ( $default_options as $option_name => $default_value ) {
			$full_option_name = 'lknaci_owh_domain_whois_rdap_' . $option_name;
			if ( get_option( $full_option_name ) === false ) {
				add_option( $full_option_name, $default_value );
			}
		}

		// Create uploads directory for storing dns.json
		$upload_dir = wp_upload_dir();
		$plugin_upload_dir = $upload_dir['basedir'] . '/lknaci-owh-domain-whois-rdap';
		
		if ( ! file_exists( $plugin_upload_dir ) ) {
			wp_mkdir_p( $plugin_upload_dir );
		}
	}
}
