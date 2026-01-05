<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://owhgroup.com.br
 * @since      1.0.0
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/public
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class Lknaci_Owh_Domain_Whois_Rdap_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Service Container
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      \OwhDomainWhoisRdap\Services\ServiceContainer    $service_container    Service container.
	 */
	private $service_container;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of the plugin.
	 * @param    string    $version    The version of this plugin.
	 * @param    \OwhDomainWhoisRdap\Services\ServiceContainer    $service_container    Service container.
	 */
	public function __construct( $plugin_name, $version, $service_container ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->service_container = $service_container;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'css/lknaci-owh-domain-whois-rdap-public.css', 
			array(), 
			$this->version, 
			'all' 
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/lknaci-owh-domain-whois-rdap-public.js', 
			array( 'jquery' ), 
			$this->version, 
			false 
		);

		// Localize script for AJAX
		wp_localize_script( $this->plugin_name, 'lknaci_owh_rdap_public', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'lknaci_owh_rdap_public_nonce' ),
			'strings' => array(
				'searching' => __( 'Pesquisando domínio...', 'lknaci-owh-domain-whois-rdap' ),
				'error' => __( 'Erro ao pesquisar domínio. Tente novamente.', 'lknaci-owh-domain-whois-rdap' ),
				'invalid_domain' => __( 'Por favor, digite um domínio válido.', 'lknaci-owh-domain-whois-rdap' ),
			)
		));
	}

	/**
	 * Register shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'owh-rdap-whois-search', array( $this, 'search_shortcode' ) );
		add_shortcode( 'owh-rdap-whois-results', array( $this, 'results_shortcode' ) );
	}

	/**
	 * Search form shortcode
	 *
	 * @since    1.0.0
	 * @param    array    $atts    Shortcode attributes.
	 * @return   string            The shortcode output.
	 */
	public function search_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'placeholder' => '',
			'button_text' => '',
			'show_examples' => 'true',
			'show_title' => 'true',
		), $atts );

		$settings_manager = $this->service_container->get( 'SettingsManager' );

		// Check if search is enabled
		if ( ! $settings_manager->isSearchEnabled() ) {
			return '<p>' . __( 'A pesquisa de domínios está desabilitada.', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		}

		$placeholder = ! empty( $atts['placeholder'] ) ? $atts['placeholder'] : $settings_manager->getPlaceholderText();
		$button_text = ! empty( $atts['button_text'] ) ? $atts['button_text'] : __( 'Pesquisar', 'lknaci-owh-domain-whois-rdap' );
		$show_title = $atts['show_title'] === 'true';
		$results_page = $settings_manager->getResultsPageId();

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/lknaci-owh-domain-whois-rdap-public-search.php';
		return ob_get_clean();
	}

	/**
	 * Results shortcode
	 *
	 * @since    1.0.0
	 * @param    array    $atts    Shortcode attributes.
	 * @return   string            The shortcode output.
	 */
	public function results_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'show_title' => 'true',
		), $atts );

		$settings_manager = $this->service_container->get( 'SettingsManager' );

		// Check if search is enabled
		if ( ! $settings_manager->isSearchEnabled() ) {
			return '<p>' . __( 'A pesquisa de domínios está desabilitada.', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		}

		$show_title = $atts['show_title'] === 'true';

		// Get domain from URL parameter
		$domain = isset( $_GET['domain'] ) ? sanitize_text_field( $_GET['domain'] ) : '';

		// Perform the search only if we have a domain
		$result = null;
		if ( ! empty( $domain ) ) {
			$availability_service = $this->service_container->get( 'AvailabilityService' );
			$result = $availability_service->checkAvailability( $domain );
		}

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/lknaci-owh-domain-whois-rdap-public-results.php';
		return ob_get_clean();
	}

	/**
	 * AJAX handler for domain check
	 *
	 * @since    1.0.0
	 */
	public function ajax_check_domain() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'lknaci_owh_rdap_public_nonce' ) ) {
			wp_die( 'Security check failed', 'Security Check', array( 'response' => 403 ) );
		}

		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( $_POST['domain'] ) : '';

		if ( empty( $domain ) ) {
			wp_send_json_error( __( 'Domínio não informado.', 'lknaci-owh-domain-whois-rdap' ) );
			return;
		}

		try {
			$settings_manager = $this->service_container->get( 'SettingsManager' );

			// Check if search is enabled
			if ( ! $settings_manager->isSearchEnabled() ) {
				wp_send_json_error( __( 'A pesquisa de domínios está desabilitada.', 'lknaci-owh-domain-whois-rdap' ) );
				return;
			}

			$availability_service = $this->service_container->get( 'AvailabilityService' );
			$result = $availability_service->checkAvailability( $domain );

			$response_data = array(
				'domain' => $result->getDomain(),
				'is_available' => $result->isAvailable(),
				'status' => $result->getStatus(),
				'has_error' => $result->hasError(),
				'error' => $result->getError(),
			);

			// Add integration info if domain is available
			if ( $result->isAvailable() ) {
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

						$response_data['integration'] = array(
							'type' => 'custom',
							'url' => $buy_url,
							'text' => __( 'Registrar Domínio', 'lknaci-owh-domain-whois-rdap' ),
						);
					}
				} elseif ( $integration_type === 'whmcs' ) {
					$whmcs_url = get_option( 'owh_rdap_whmcs_url', '' );
					if ( ! empty( $whmcs_url ) ) {
						$response_data['integration'] = array(
							'type' => 'whmcs',
							'url' => $whmcs_url,
							'domain' => $result->getDomain(),
							'form_html' => sprintf(
								'<form style="display:none" method="post" name="whmcs_%s" id="whmcs_%s" action="%s/cart.php?a=add&domain=register" target="_self">
								<input type="hidden" name="domains[]" value="%s">
								<input type="hidden" name="domainsregperiod[%s]" value="1">
								</form>',
								str_replace( '.', '_', $result->getDomain() ),
								str_replace( '.', '_', $result->getDomain() ),
								rtrim( $whmcs_url, '/' ),
								$result->getDomain(),
								$result->getDomain()
							),
							'text' => __( 'Registrar Domínio', 'lknaci-owh-domain-whois-rdap' ),
						);
					}
				}
			}

			if ( $result->hasError() ) {
				wp_send_json_error( $response_data );
			} else {
				wp_send_json_success( $response_data );
			}

		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Erro interno do servidor.', 'lknaci-owh-domain-whois-rdap' ) );
		}
	}

	/**
	 * AJAX handler for domain search (required by main class)
	 */
	public function handle_domain_search() {
		// Redirect to the existing check domain handler
		return $this->ajax_check_domain();
	}
}
