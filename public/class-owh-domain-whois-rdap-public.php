<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://linknacional.com.br
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
 * @author     Link Nacional <dev@linknacional.com.br>
 */
class Owhdwhoisrdap_Domain_Whois_Rdap_Public {

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
	 * @var      \OwhdwhoisrdapDomainWhoisRdap\Services\ServiceContainer    $service_container    Service container.
	 */
	private $service_container;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of the plugin.
	 * @param    string    $version    The version of this plugin.
	 * @param    \OwhdwhoisrdapDomainWhoisRdap\Services\ServiceContainer    $service_container    Service container.
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
			plugin_dir_url( __FILE__ ) . 'css/owh-domain-whois-rdap-public.css', 
			array(), 
			$this->version, 
			'all' 
		);
		
		// Enqueue shortcodes specific styles
		wp_enqueue_style( 
			$this->plugin_name . '-shortcodes', 
			plugin_dir_url( __FILE__ ) . 'css/owh-domain-whois-rdap-shortcodes.css', 
			array( $this->plugin_name ), 
			$this->version, 
			'all' 
		);
	}

	// add_inline_styles method removed for security compliance

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/owh-domain-whois-rdap-public.js', 
			array( 'jquery' ), 
			$this->version, 
			false 
		);

		// Localize script with plugin settings
		$results_page_id = get_option( 'owhdwhoisrdap_domain_whois_rdap_results_page', '' );

		wp_localize_script( $this->plugin_name, 'owhRdapPublic', array(
			'hasResultsPage' => !empty($results_page_id),
			'strings' => array(
				'configRequired' => __( 'Para realizar pesquisas, é necessário configurar uma "Página de Resultados" nas configurações do plugin. Entre em contato com o administrador.', 'owh-domain-whois-rdap' ),
				'configTitle' => __( 'Configuração Necessária', 'owh-domain-whois-rdap' )
			)
		) );
	}

	/**
	 * Register shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'owhdwhoisrdap-rdap-whois-search', array( $this, 'search_shortcode' ) );
		add_shortcode( 'owhdwhoisrdap-rdap-whois-results', array( $this, 'results_shortcode' ) );
		add_shortcode( 'owhdwhoisrdap-rdap-whois-details', array( $this, 'whois_details_shortcode' ) );
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
			'show_title' => 'true',
			'custom_title' => '',
			'placeholder_text' => '',
			'search_button_text' => '',
			'show_examples' => 'true',
			'examples_text' => '',
			'example1' => '',
			'example2' => '',
			'example3' => '',
			'border_width' => '',
			'border_color' => '',
			'border_radius' => '',
			'background_color' => '',
			'padding' => '',
			'primary_color' => '',
			'button_hover_color' => '',
			'input_border_color' => '',
			'input_focus_color' => '',
			'button_layout' => 'external'
		), $atts );

		$settings_manager = $this->service_container->get( 'SettingsManager' );

		// Check if search is enabled
		if ( ! $settings_manager->isSearchEnabled() ) {
			return '<p>' . __( 'A pesquisa de domínios está desabilitada.', 'owh-domain-whois-rdap' ) . '</p>';
		}

		$show_title = $atts['show_title'] === 'true';
		$show_examples = $atts['show_examples'] === 'true';
		$results_page = $settings_manager->getResultsPageId();

		// Pass custom attributes to the template
		$custom_attributes = array(
			'custom_title' => ! empty( $atts['custom_title'] ) ? $atts['custom_title'] : '',
			'placeholder_text' => ! empty( $atts['placeholder_text'] ) ? $atts['placeholder_text'] : '',
			'search_button_text' => ! empty( $atts['search_button_text'] ) ? $atts['search_button_text'] : '',
			'examples_text' => ! empty( $atts['examples_text'] ) ? $atts['examples_text'] : '',
			'example1' => ! empty( $atts['example1'] ) ? $atts['example1'] : '',
			'example2' => ! empty( $atts['example2'] ) ? $atts['example2'] : '',
			'example3' => ! empty( $atts['example3'] ) ? $atts['example3'] : '',
			'border_width' => isset( $atts['border_width'] ) ? $atts['border_width'] : '',
			'border_color' => ! empty( $atts['border_color'] ) ? $atts['border_color'] : '',
			'border_radius' => isset( $atts['border_radius'] ) ? $atts['border_radius'] : '',
			'background_color' => ! empty( $atts['background_color'] ) ? $atts['background_color'] : '',
			'padding' => isset( $atts['padding'] ) ? $atts['padding'] : '',
			'primary_color' => ! empty( $atts['primary_color'] ) ? $atts['primary_color'] : '',
			'button_hover_color' => ! empty( $atts['button_hover_color'] ) ? $atts['button_hover_color'] : '',
			'input_border_color' => ! empty( $atts['input_border_color'] ) ? $atts['input_border_color'] : '',
			'input_focus_color' => ! empty( $atts['input_focus_color'] ) ? $atts['input_focus_color'] : '',
			'button_layout' => ! empty( $atts['button_layout'] ) ? $atts['button_layout'] : 'external'
		);

		// Ensure styles are enqueued
		$this->enqueue_styles();

		// Build and add inline styles
		$inline_css = '';
		

		// Add dynamic styles from attributes
		$dynamic_css_parts = array();
		
		if ( ! empty( $atts['primary_color'] ) ) {
			$dynamic_css_parts[] = '.owh-rdap-search-container .owh-rdap-search-button { background-color: ' . esc_attr( $atts['primary_color'] ) . '; border-color: ' . esc_attr( $atts['primary_color'] ) . '; }';
		}
		
		if ( ! empty( $atts['button_hover_color'] ) ) {
			$dynamic_css_parts[] = '.owh-rdap-search-container .owh-rdap-search-button:hover { background-color: ' . esc_attr( $atts['button_hover_color'] ) . '; }';
		}

		if ( ! empty( $dynamic_css_parts ) ) {
			$inline_css .= implode( "\n", $dynamic_css_parts );
		}

		// Add inline styles through WordPress
		if ( ! empty( $inline_css ) ) {
			wp_add_inline_style( $this->plugin_name . '-shortcodes', $inline_css );
		}

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/owh-domain-whois-rdap-public-search.php';
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
			'custom_title' => '',
			'no_result_text' => '',
			'no_result_description' => '',
			'available_title' => '',
			'available_text' => '',
			'unavailable_title' => '',
			'unavailable_text' => '',
			'buy_button_text' => '',
			'details_button_text' => '',
			'show_icons' => 'true',
			'search_icon' => '',
			'available_icon' => '',
			'unavailable_icon' => '',
			// '' removed for security compliance
			'border_width' => '',
			'border_color' => '',
			'border_radius' => '',
			'background_color' => '',
			'padding' => '',
			'available_color' => '',
			'unavailable_color' => ''
		), $atts );

		$settings_manager = $this->service_container->get( 'SettingsManager' );

		// Check if search is enabled
		if ( ! $settings_manager->isSearchEnabled() ) {
			return '<p>' . __( 'A pesquisa de domínios está desabilitada.', 'owh-domain-whois-rdap' ) . '</p>';
		}

		$show_title = $atts['show_title'] === 'true';

		// Get domain from URL parameter
		$domain = isset( $_GET['domain'] ) ? sanitize_text_field( wp_unslash( $_GET['domain'] ) ) : '';

		// Perform the search only if we have a domain
		$result = null;
		if ( ! empty( $domain ) ) {
			$availability_service = $this->service_container->get( 'AvailabilityService' );
			$result = $availability_service->checkAvailability( $domain );
		}

		// Pass custom attributes to the template
		$custom_attributes = array(
			'custom_title' => ! empty( $atts['custom_title'] ) ? $atts['custom_title'] : '',
			'no_result_text' => ! empty( $atts['no_result_text'] ) ? $atts['no_result_text'] : '',
			'no_result_description' => ! empty( $atts['no_result_description'] ) ? $atts['no_result_description'] : '',
			'available_title' => ! empty( $atts['available_title'] ) ? $atts['available_title'] : '',
			'available_text' => ! empty( $atts['available_text'] ) ? $atts['available_text'] : '',
			'unavailable_title' => ! empty( $atts['unavailable_title'] ) ? $atts['unavailable_title'] : '',
			'unavailable_text' => ! empty( $atts['unavailable_text'] ) ? $atts['unavailable_text'] : '',
			'buy_button_text' => ! empty( $atts['buy_button_text'] ) ? $atts['buy_button_text'] : '',
			'details_button_text' => ! empty( $atts['details_button_text'] ) ? $atts['details_button_text'] : '',
			'show_icons' => $atts['show_icons'] === 'true',
			'search_icon' => ! empty( $atts['search_icon'] ) ? $atts['search_icon'] : '',
			'available_icon' => ! empty( $atts['available_icon'] ) ? $atts['available_icon'] : '',
			'unavailable_icon' => ! empty( $atts['unavailable_icon'] ) ? $atts['unavailable_icon'] : '',
			// '' removed for security compliance
			'border_width' => ! empty( $atts['border_width'] ) ? intval( $atts['border_width'] ) : '',
			'border_color' => ! empty( $atts['border_color'] ) ? $atts['border_color'] : '',
			'border_radius' => ! empty( $atts['border_radius'] ) ? intval( $atts['border_radius'] ) : '',
			'background_color' => ! empty( $atts['background_color'] ) ? $atts['background_color'] : '',
			'padding' => ! empty( $atts['padding'] ) ? intval( $atts['padding'] ) : '',
			'available_color' => ! empty( $atts['available_color'] ) ? $atts['available_color'] : '',
			'unavailable_color' => ! empty( $atts['unavailable_color'] ) ? $atts['unavailable_color'] : ''
		);

		// Ensure styles are enqueued
		$this->enqueue_styles();

		// Build and add inline styles for results
		$inline_css = '';
		
		// Add inline styles through WordPress
		if ( ! empty( $inline_css ) ) {
			wp_add_inline_style( $this->plugin_name, $inline_css );
		}

		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/owh-domain-whois-rdap-public-results.php';
		return ob_get_clean();
	}

	/**
	 * AJAX handler for domain check
	 *
	 * @since    1.0.0
	 */
	public function ajax_check_domain() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'owhdwhoisrdap_rdap_public_nonce' ) ) {
			wp_send_json_error( array( 
				'message' => __( 'Verificação de segurança falhou.', 'owh-domain-whois-rdap' )
			) );
			return;
		}

		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

		// Validate domain format
		if ( empty( $domain ) ) {
			wp_send_json_error( __( 'Domínio não informado.', 'owh-domain-whois-rdap' ) );
			return;
		}

		// Basic domain validation
		$domain = strtolower( trim( $domain ) );
		if ( ! preg_match( '/^[a-zA-Z0-9][a-zA-Z0-9\-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/', $domain ) ) {
			wp_send_json_error( __( 'Formato de domínio inválido.', 'owh-domain-whois-rdap' ) );
			return;
		}

		try {
			$settings_manager = $this->service_container->get( 'SettingsManager' );

			// Check if search is enabled
			if ( ! $settings_manager->isSearchEnabled() ) {
				wp_send_json_error( __( 'A pesquisa de domínios está desabilitada.', 'owh-domain-whois-rdap' ) );
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
				$integration_type = get_option( 'owhdwhoisrdap_rdap_integration_type', 'none' );
				
				// Only add integration if type is not 'none'
				if ( $integration_type !== 'none' ) {
					$domain_parts = explode( '.', $result->getDomain() );
					$sld = $domain_parts[0];
					$tld = isset( $domain_parts[1] ) ? $domain_parts[1] : '';

					if ( $integration_type === 'custom' ) {
					$custom_url = get_option( 'owhdwhoisrdap_rdap_custom_url', '' );
					if ( ! empty( $custom_url ) ) {
						$buy_url = str_replace(
							array( '{domain}', '{sld}', '{tld}' ),
							array( $result->getDomain(), $sld, $tld ),
							$custom_url
						);

						$response_data['integration'] = array(
							'type' => 'custom',
							'url' => $buy_url,
							'text' => __( 'Registrar Domínio', 'owh-domain-whois-rdap' ),
						);
					}
				} elseif ( $integration_type === 'whmcs' ) {
					$whmcs_url = get_option( 'owhdwhoisrdap_rdap_whmcs_url', '' );
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
							'text' => __( 'Registrar Domínio', 'owh-domain-whois-rdap' ),
						);
					}
				}
				} // Close the if ( $integration_type !== 'none' ) block
			}

			if ( $result->hasError() ) {
				wp_send_json_error( $response_data );
			} else {
				wp_send_json_success( $response_data );
			}

		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Erro interno do servidor.', 'owh-domain-whois-rdap' ) );
		}
	}

	/**
	 * AJAX handler for domain search (required by main class)
	 */
	public function handle_domain_search() {
		// Redirect to the existing check domain handler
		return $this->ajax_check_domain();
	}

	/**
	 * WHOIS details shortcode
	 *
	 * @since    1.0.0
	 * @param array $atts Shortcode attributes
	 * @return string Shortcode output
	 */
	public function whois_details_shortcode( $atts ) {
		// Parse attributes
		$atts = shortcode_atts( array(
			'show_title' => 'true',
			'custom_title' => 'Detalhes WHOIS/RDAP',
			'show_events' => 'true',
			'events_title' => 'Histórico de Eventos',
			'show_entities' => 'true',
			'entities_title' => 'Entidades Relacionadas',
			'show_nameservers' => 'true',
			'nameservers_title' => 'Servidores DNS (Nameservers)',
			'show_status' => 'true',
			'status_title' => 'Status do Domínio',
			'show_dnssec' => 'true',
			'dnssec_title' => 'DNSSEC',
			'no_domain_text' => 'Nenhum Domínio Informado',
			'no_domain_description' => 'Para visualizar os detalhes WHOIS, acesse esta página através do link "Ver detalhes completos" nos resultados da pesquisa.',
			'available_text' => 'Este domínio está disponível para registro e não possui informações WHOIS.',
			'error_text' => 'Erro na Pesquisa',
			// CSS styling attributes
			'border_width' => '1',
			'border_color' => '#ddd',
			'border_radius' => '4',
			'background_color' => '#ffffff',
			'padding' => '20',
			'show_icon' => 'true',
			'custom_icon' => '📋'
		), $atts, 'owhdwhoisrdap-rdap-whois-details' );

		// Check if search is enabled
		if ( ! get_option( 'owhdwhoisrdap_rdap_enable_search', false ) ) {
			return '<p>' . __( 'A pesquisa de domínios está desabilitada.', 'owh-domain-whois-rdap' ) . '</p>';
		}

		// Get domain from URL parameter
		$domain = isset( $_GET['domain'] ) ? sanitize_text_field( wp_unslash( $_GET['domain'] ) ) : '';
		
		// Convert string booleans to actual booleans
		$show_title = filter_var( $atts['show_title'], FILTER_VALIDATE_BOOLEAN );
		$show_events = filter_var( $atts['show_events'], FILTER_VALIDATE_BOOLEAN );
		$show_entities = filter_var( $atts['show_entities'], FILTER_VALIDATE_BOOLEAN );
		$show_nameservers = filter_var( $atts['show_nameservers'], FILTER_VALIDATE_BOOLEAN );
		$show_status = filter_var( $atts['show_status'], FILTER_VALIDATE_BOOLEAN );
		$show_dnssec = filter_var( $atts['show_dnssec'], FILTER_VALIDATE_BOOLEAN );
		$show_icon = filter_var( $atts['show_icon'], FILTER_VALIDATE_BOOLEAN );
		
		// Pass all custom texts to template
		$custom_title = $atts['custom_title'];
		$events_title = $atts['events_title'];
		$entities_title = $atts['entities_title'];
		$nameservers_title = $atts['nameservers_title'];
		$status_title = $atts['status_title'];
		$dnssec_title = $atts['dnssec_title'];
		$no_domain_text = $atts['no_domain_text'];
		$no_domain_description = $atts['no_domain_description'];
		$available_text = $atts['available_text'];
		$error_text = $atts['error_text'];
		
		// CSS styling variables
		$border_width = intval( $atts['border_width'] );
		$border_color = $atts['border_color'];
		$border_radius = intval( $atts['border_radius'] );
		$background_color = $atts['background_color'];
		$padding = intval( $atts['padding'] );
		$custom_icon = $atts['custom_icon'];
		
		$result = null;

		// If domain is provided, fetch WHOIS data
		if ( ! empty( $domain ) ) {
			try {
				$availability_service = $this->service_container->get( 'AvailabilityService' );
				$result = $availability_service->checkAvailability( $domain );
			} catch ( Exception $e ) {
				// Handle error silently or return error message
				$result = null;
			}
		}

		// Ensure styles are enqueued
		$this->enqueue_styles();

		// Build and add inline styles for whois details
		$inline_css = '';
		
		// Add inline styles through WordPress
		if ( ! empty( $inline_css ) ) {
			wp_add_inline_style( $this->plugin_name . '-shortcodes', $inline_css );
		}

		// Start output buffering
		ob_start();

		// Include the WHOIS details template
		include plugin_dir_path( __FILE__ ) . 'partials/owh-domain-whois-rdap-public-whois-details.php';

		return ob_get_clean();
	}
}
