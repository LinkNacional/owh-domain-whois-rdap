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
class Owh_Domain_Whois_Rdap_Public {

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
			plugin_dir_url( __FILE__ ) . 'css/owh-domain-whois-rdap-public.css', 
			array(), 
			$this->version, 
			'all' 
		);

		// Enqueue domain product periods CSS on single product page
		if ( is_product() ) {
			global $product;
			if ( $product && $product->get_type() === 'domain' ) {
				wp_enqueue_style( 
					$this->plugin_name . '-domain-periods', 
					plugin_dir_url( __FILE__ ) . 'css/owh-domain-product-periods.css', 
					array( $this->plugin_name ), 
					$this->version, 
					'all' 
				);
			}
		}
	}

	public function add_inline_styles( $custom_css ) {
		if ( ! empty( $custom_css ) ) {
			wp_add_inline_style( $this->plugin_name, $custom_css );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Always enqueue main script - needed for checkout interception
		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/owh-domain-whois-rdap-public.js', 
			array( 'jquery' ), 
			$this->version, 
			false 
		);

		// Enqueue domain product periods script on single product page
		if ( is_product() ) {
			global $product;
			if ( $product && $product->get_type() === 'domain' ) {
				wp_enqueue_script( 
					'owh-domain-periods', 
					plugin_dir_url( __FILE__ ) . 'js/owh-domain-product-periods.js', 
					array( 'jquery' ), 
					$this->version, 
					true 
				);
				
				wp_localize_script( 'owh-domain-periods', 'owh_domain_ajax', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'owh_domain_ajax' )
				) );
			}
		}

		// Localize script with plugin settings
		$results_page_id = get_option( 'owh_domain_whois_rdap_results_page', '' );

		wp_localize_script( $this->plugin_name, 'owhRdapPublic', array(
			'hasResultsPage' => !empty($results_page_id),
			'rest_url' => rest_url( 'owh-rdap/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
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
		add_shortcode( 'owh-rdap-whois-search', array( $this, 'search_shortcode' ) );
		add_shortcode( 'owh-rdap-whois-results', array( $this, 'results_shortcode' ) );
		add_shortcode( 'owh-rdap-whois-details', array( $this, 'whois_details_shortcode' ) );
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
			'custom_css' => '',
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
			'custom_css' => ! empty( $atts['custom_css'] ) ? $atts['custom_css'] : '',
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
		
		if ( ! empty( $custom_attributes['custom_css'] ) ) {
			$inline_css .= '.owh-rdap-search-container { ' . esc_html( $custom_attributes['custom_css'] ) . ' }' . "\n";
		}

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
			wp_add_inline_style( $this->plugin_name, $inline_css );
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
			'disabled_title' => '',
			'disabled_text' => '',
			'buy_button_text' => '',
			'details_button_text' => '',
			'show_icons' => 'true',
			'search_icon' => '',
			'available_icon' => '',
			'unavailable_icon' => '',
			'disabled_icon' => '',
			'custom_css' => '',
			'border_width' => '',
			'border_color' => '',
			'border_radius' => '',
			'background_color' => '',
			'padding' => '',
			'available_color' => '',
			'unavailable_color' => '',
			'disabled_color' => ''
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
			'disabled_title' => ! empty( $atts['disabled_title'] ) ? $atts['disabled_title'] : '',
			'disabled_text' => ! empty( $atts['disabled_text'] ) ? $atts['disabled_text'] : '',
			'buy_button_text' => ! empty( $atts['buy_button_text'] ) ? $atts['buy_button_text'] : '',
			'details_button_text' => ! empty( $atts['details_button_text'] ) ? $atts['details_button_text'] : '',
			'show_icons' => $atts['show_icons'] === 'true',
			'search_icon' => ! empty( $atts['search_icon'] ) ? $atts['search_icon'] : '',
			'available_icon' => ! empty( $atts['available_icon'] ) ? $atts['available_icon'] : '',
			'unavailable_icon' => ! empty( $atts['unavailable_icon'] ) ? $atts['unavailable_icon'] : '',
			'disabled_icon' => ! empty( $atts['disabled_icon'] ) ? $atts['disabled_icon'] : '',
			'custom_css' => ! empty( $atts['custom_css'] ) ? $atts['custom_css'] : '',
			'border_width' => ! empty( $atts['border_width'] ) ? intval( $atts['border_width'] ) : '',
			'border_color' => ! empty( $atts['border_color'] ) ? $atts['border_color'] : '',
			'border_radius' => ! empty( $atts['border_radius'] ) ? intval( $atts['border_radius'] ) : '',
			'background_color' => ! empty( $atts['background_color'] ) ? $atts['background_color'] : '',
			'padding' => ! empty( $atts['padding'] ) ? intval( $atts['padding'] ) : '',
			'available_color' => ! empty( $atts['available_color'] ) ? $atts['available_color'] : '',
			'unavailable_color' => ! empty( $atts['unavailable_color'] ) ? $atts['unavailable_color'] : '',
			'disabled_color' => ! empty( $atts['disabled_color'] ) ? $atts['disabled_color'] : ''
		);

		// Ensure styles are enqueued
		$this->enqueue_styles();

		// Build and add inline styles for results
		$inline_css = '';
		
		if ( ! empty( $custom_attributes['custom_css'] ) ) {
			$inline_css .= '.owh-rdap-results-container { ' . esc_html( $custom_attributes['custom_css'] ) . ' }' . "\n";
		}

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
			'custom_css' => '',
			'show_icon' => 'true',
			'custom_icon' => '📋'
		), $atts, 'owh-rdap-whois-details' );

		// Check if search is enabled
		if ( ! get_option( 'owh_rdap_enable_search', false ) ) {
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
		$custom_css = $atts['custom_css'];
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
		
		if ( ! empty( $custom_css ) ) {
			$clean_css = wp_strip_all_tags( $custom_css );
			$clean_css = str_replace( array( '<script', '</script>', 'javascript:' ), '', $clean_css );
			
			$inline_css .= '.owh-rdap-whois-details-container { ' . esc_attr( $clean_css ) . ' }' . "\n";
		}

		// Add inline styles through WordPress
		if ( ! empty( $inline_css ) ) {
			wp_add_inline_style( $this->plugin_name, $inline_css );
		}

		// Start output buffering
		ob_start();

		// Include the WHOIS details template
		include plugin_dir_path( __FILE__ ) . 'partials/owh-domain-whois-rdap-public-whois-details.php';

		return ob_get_clean();
	}

	/**
	 * Register REST API routes
	 *
	 * @since    1.0.0
	 */
	public function register_rest_routes() {
		register_rest_route( 'owh-rdap/v1', '/check-domain', array(
			'methods' => 'POST',
			'callback' => array( $this, 'rest_check_domain' ),
			'permission_callback' => '__return_true', // Public endpoint
			'args' => array(
				'domain' => array(
					'required' => true,
					'type' => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );
	}

	/**
	 * REST API permission check for public endpoints
	 *
	 * @since    1.0.0
	 */
	public function rest_public_permissions_check() {
		return true; // Public endpoints
	}

	/**
	 * REST API handler for checking domain availability
	 *
	 * @since    1.0.0
	 */
	public function rest_check_domain( $request ) {
		$domain = $request->get_param( 'domain' );

		if ( empty( $domain ) ) {
			return new \WP_Error( 'missing_domain', __( 'Domínio não informado.', 'owh-domain-whois-rdap' ), array( 'status' => 400 ) );
		}

		try {
			$settings_manager = $this->service_container->get( 'SettingsManager' );

			// Check if search is enabled
			if ( ! $settings_manager->isSearchEnabled() ) {
				return new \WP_Error( 'search_disabled', __( 'A pesquisa de domínios está desabilitada.', 'owh-domain-whois-rdap' ), array( 'status' => 403 ) );
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
				$integration_type = get_option( 'owh_rdap_integration_type', 'none' );
				
				// Only add integration if type is not 'none'
				if ( $integration_type !== 'none' ) {
					$domain_parts = explode( '.', $result->getDomain() );
					$sld = $domain_parts[0];
					$tld = isset( $domain_parts[1] ) ? '.' . $domain_parts[1] : '';

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
								'buy_url' => $buy_url
							);
						}
					} elseif ( $integration_type === 'woocommerce' && ! empty( $tld ) ) {
						// Check if there's a product for this TLD
						$product_data = $this->get_product_data_by_tld( $tld );
						
						if ( $product_data ) {
							$response_data['integration'] = array(
								'type' => 'woocommerce',
								'product_id' => $product_data['id'],
								'product_name' => $product_data['name'],
								'product_price' => $product_data['price'],
								'add_to_cart_url' => $product_data['add_to_cart_url'],
								'tld' => $tld
							);
						}
					}
				}
			}

			return new \WP_REST_Response( array(
				'success' => true,
				'data' => $response_data
			), 200 );

		} catch ( Exception $e ) {
			return new \WP_Error( 'check_error', __( 'Erro ao verificar domínio: ', 'owh-domain-whois-rdap' ) . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * Render domain period selector on single product page
	 * 
	 * @since 1.0.0
	 */
	public function render_domain_period_selector() {
		global $product;
		
		// Only show for domain products
		if ( ! $product || $product->get_type() !== 'domain' ) {
			return;
		}
		
		// Check if product has available periods (single or multiple)
		if ( ! method_exists( $product, 'get_available_periods' ) ) {
			return;
		}
		
		$available_periods = $product->get_available_periods();
		if ( empty( $available_periods ) ) {
			return;
		}
		
		// Include the template
		include plugin_dir_path( __FILE__ ) . 'partials/owh-domain-product-periods.php';
	}

	/**
	 * AJAX handler for getting domain price for specific period
	 * 
	 * @since 1.0.0
	 */
	public function ajax_get_domain_price_for_period() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'owh_domain_ajax' ) ) {
			wp_send_json_error( 'Invalid nonce' );
			return;
		}

		$product_id = intval( $_POST['product_id'] );
		$period = intval( $_POST['period'] );

		if ( ! $product_id || ! $period ) {
			wp_send_json_error( 'Invalid parameters' );
		}

		$product = wc_get_product( $product_id );

		if ( ! $product || $product->get_type() !== 'domain' ) {
			wp_send_json_error( 'Invalid product' );
		}

		$price = $product->get_price_for_period( $period, 'register' );

		if ( $price === null ) {
			wp_send_json_error( 'Price not available for this period' );
		}

		wp_send_json_success( array(
			'price' => $price,
			'price_html' => wc_price( $price ),
			'period' => $period
		) );
	}

	/**
	 * Add domain-specific data to cart item
	 * 
	 * @since 1.0.0
	 * @param array $cart_item_data Cart item data
	 * @param int $product_id Product ID
	 * @param int $variation_id Variation ID
	 * @return array Modified cart item data
	 */
	public function add_domain_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		$product = wc_get_product( $product_id );
		
		if ( ! $product || $product->get_type() !== 'domain' ) {
			return $cart_item_data;
		}

		// Get domain period from form
		if ( isset( $_POST['domain_period'] ) ) {
			$period = intval( $_POST['domain_period'] );
			
			if ( $period >= 1 && $period <= 10 ) {
				$cart_item_data['domain_period'] = $period;
				
				// Get the price for this period
				$price = $product->get_price_for_period( $period, 'register' );
				if ( $price !== null ) {
					$cart_item_data['domain_price'] = $price;
				}
			}
		}

		// Get domain name if provided
		if ( isset( $_POST['domain_name'] ) && ! empty( $_POST['domain_name'] ) ) {
			$cart_item_data['domain_name'] = sanitize_text_field( $_POST['domain_name'] );
		}

		// Get domain action (register/renew/transfer)
		if ( isset( $_POST['domain_action'] ) ) {
			$cart_item_data['domain_action'] = sanitize_text_field( $_POST['domain_action'] );
		}

		return $cart_item_data;
	}

	/**
	 * Update cart item price based on selected period
	 * 
	 * @since 1.0.0
	 * @param WC_Cart $cart Cart object
	 */
	public function update_domain_cart_item_price( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			
			if ( $product->get_type() !== 'domain' ) {
				continue;
			}

			// Check if we have domain-specific pricing
			if ( isset( $cart_item['domain_price'] ) && $cart_item['domain_price'] > 0 ) {
				$product->set_price( $cart_item['domain_price'] );
			}
		}
	}

	/**
	 * Render Add to Cart form for domain products
	 * Ensures compatibility with Block Themes
	 * 
	 * @since 1.0.0
	 */
	public function render_domain_add_to_cart_form() {
		global $product;
		
		// Only show for domain products
		if ( ! $product || $product->get_type() !== 'domain' ) {
			return;
		}
		
		// Don't show if not purchasable
		if ( ! method_exists( $product, 'is_purchasable' ) || ! $product->is_purchasable() ) {
			return;
		}
		
		// Render Add to Cart form
		echo '<div class="domain-add-to-cart-section">';
		
		echo '<form class="cart" method="post" enctype="multipart/form-data">';
		echo '<input type="hidden" name="add-to-cart" value="' . $product->get_id() . '" />';
		echo '<input type="hidden" name="quantity" value="1" />';
		
		// Add custom domain fields if they exist in the session/form
		if ( isset( $_POST['domain_period'] ) ) {
			echo '<input type="hidden" name="domain_period" value="' . intval( $_POST['domain_period'] ) . '" />';
		}
		
		echo '<button type="submit" name="add-to-cart" value="' . $product->get_id() . '" class="single_add_to_cart_button button alt wp-element-button">';
		echo 'Adicionar ao carrinho';
		echo '</button>';
		
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Get product ID for a specific TLD
	 *
	 * @param string $tld The TLD to search for (including the dot, e.g. '.com')
	 * @return int|false Product ID if found, false otherwise
	 */
	public function get_product_id_by_tld( $tld ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		$existing_products = get_posts( array(
			'post_type' => 'product',
			'meta_query' => array(
				array(
					'key' => '_domain_tld',
					'value' => $tld,
					'compare' => '='
				)
			),
			'post_status' => 'publish',
			'numberposts' => 1,
			'fields' => 'ids'
		) );

		return ! empty( $existing_products ) ? $existing_products[0] : false;
	}

	/**
	 * Get product data for a specific TLD
	 *
	 * @param string $tld The TLD to search for
	 * @return array|false Product data if found, false otherwise
	 */
	public function get_product_data_by_tld( $tld ) {
		$product_id = $this->get_product_id_by_tld( $tld );
		
		if ( ! $product_id ) {
			return false;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;
		}

		return array(
			'id' => $product_id,
			'name' => $product->get_name(),
			'price' => $product->get_price(),
			'permalink' => get_permalink( $product_id ),
			'add_to_cart_url' => wc_get_cart_url() . '?add-to-cart=' . $product_id
		);
	}

	/**
	 * Register dynamic checkout fields for domain products
	 * 
	 * @since 1.0.0
	 */
	public function register_dynamic_checkout_fields() {
		// Only run on checkout pages
		if ( ! is_checkout() ) {
			return;
		}
		if (! function_exists('woocommerce_register_additional_checkout_field')) {
			return;
		}
		
		// Try modern WooCommerce method first (WC 8.8+) if available
		$required_fields = $this->get_required_custom_fields_for_cart();
		
		if ( empty( $required_fields ) ) {
			return;
		}
		
		$custom_fields = get_option( 'owh_domain_whois_rdap_custom_fields', array() );
		
		// Only use modern method if function exists
		foreach ( $required_fields as $field_id ) {
			$field_config = null;
			
			// Find field configuration
			foreach ( $custom_fields as $field ) {
				if ( intval( $field['id'] ) === intval( $field_id ) ) {
					$field_config = $field;
					break;
				}
			}
			
			if ( ! $field_config ) {
				continue;
			}


			woocommerce_register_additional_checkout_field( array(
				'id'       => 'owh-domain-whois-rdap/custom-field-' . $field_id,
				'label'    => $field_config['label'],
				'location' => 'order',
				'type'     => 'text',
				'required' => true,
				'attributes' => array(
					'pattern' => ! empty( $field_config['regex'] ) ? $field_config['regex'] : '',
				),
			));
		}
		// For older WC versions, the hooks are already registered in the main file
	}

	/**
	 * Display custom checkout field (fallback method)
	 * 
	 * @since 1.0.0
	 */
	public function display_custom_checkout_fields( $checkout ) {
		$required_fields = $this->get_required_custom_fields_for_cart();
		
		if ( empty( $required_fields ) ) {
			return;
		}

		$custom_fields = get_option( 'owh_domain_whois_rdap_custom_fields', array() );
		
		echo '<div id="owh_domain_custom_fields"><h3>' . esc_html__( 'Informações Adicionais para Domínios', 'owh-domain-whois-rdap' ) . '</h3>';
		
		foreach ( $required_fields as $field_id ) {
			$field_config = null;
			
			foreach ( $custom_fields as $field ) {
				if ( intval( $field['id'] ) === intval( $field_id ) ) {
					$field_config = $field;
					break;
				}
			}
			
			if ( ! $field_config ) {
				continue;
			}

			$field_name = 'owh_domain_custom_field_' . $field_id;
			$field_value = $checkout->get_value( $field_name );
			
			woocommerce_form_field( $field_name, array(
				'type'        => 'text',
				'class'       => array( 'form-row-wide' ),
				'label'       => $field_config['label'],
				'placeholder' => $field_config['label'],
				'required'    => true,
				'custom_attributes' => array(
					'pattern' => ! empty( $field_config['regex'] ) ? $field_config['regex'] : '',
					'data-field-id' => $field_id
				)
			), $field_value );
		}
		
		echo '</div>';
	}

	/**
	 * Validate custom checkout fields
	 * 
	 * @since 1.0.0
	 */
	public function validate_custom_checkout_fields() {
		$required_fields = $this->get_required_custom_fields_for_cart();
		
		if ( empty( $required_fields ) ) {
			return;
		}

		$custom_fields = get_option( 'owh_domain_whois_rdap_custom_fields', array() );
		
		foreach ( $required_fields as $field_id ) {
			$field_config = null;
			
			foreach ( $custom_fields as $field ) {
				if ( intval( $field['id'] ) === intval( $field_id ) ) {
					$field_config = $field;
					break;
				}
			}
			
			if ( ! $field_config ) {
				continue;
			}

			$field_name = 'owh_domain_custom_field_' . $field_id;
			$field_value = sanitize_text_field( $_POST[ $field_name ] ?? '' );
			
			// Check if field is required and empty
			if ( empty( $field_value ) ) {
				wc_add_notice( sprintf( 
					__( 'O campo "%s" é obrigatório.', 'owh-domain-whois-rdap' ), 
					$field_config['label'] 
				), 'error' );
				continue;
			}
			
			// Validate against regex if provided
			if ( ! empty( $field_config['regex'] ) ) {
				if ( ! preg_match( '/' . $field_config['regex'] . '/', $field_value ) ) {
					wc_add_notice( sprintf( 
						__( 'O campo "%s" não atende aos critérios necessários.', 'owh-domain-whois-rdap' ), 
						$field_config['label'] 
					), 'error' );
				}
			}
		}
	}

	/**
	 * Save custom checkout fields to order meta
	 * 
	 * Este método salva os campos personalizados do checkout no meta da ordem.
	 * Os dados chegam através do $context->payment_data no formato:
	 * Array(
	 *   "owh-domain-whois-rdap-custom-field-1" => "624.655.700-72",
	 *   "owh-domain-whois-rdap-custom-field-2" => "23.375.581/0001-41"
	 * )
	 * 
	 * E são salvos como uma única meta da ordem com a chave "_owh_domain_custom_fields" 
	 * contendo um array estruturado:
	 * Array(
	 *   "1" => Array(
	 *     "field_id" => "1",
	 *     "value" => "624.655.700-72", 
	 *     "original_key" => "owh-domain-whois-rdap-custom-field-1"
	 *   ),
	 *   "2" => Array(
	 *     "field_id" => "2",
	 *     "value" => "23.375.581/0001-41",
	 *     "original_key" => "owh-domain-whois-rdap-custom-field-2" 
	 *   )
	 * )
	 * 
	 * @since 1.0.0
	 * @param \WC_Order_Context $context Payment context object
	 * @param \WC_Result $result Payment result object
	 */
	public function save_custom_checkout_fields( $context, $result ) {
		// Check if we have payment data and order
		$order = $context->order;
		$payment_data = $context->payment_data;

		foreach ( $payment_data as $key => $value ) {
			// Check if the key matches our custom field pattern
			if ( strpos( $key, 'owh-domain-whois-rdap-custom-field-' ) === 0 ) {
				// Extract the field ID from the key
				$field_id = str_replace( 'owh-domain-whois-rdap-custom-field-', '', $key );
				
				// Sanitize the value
				$sanitized_value = sanitize_text_field( $value );
				
				// Adicionar ao array de campos personalizados
				$custom_fields_data[ $field_id ] = array(
					'field_id' => $field_id,
					'value' => $sanitized_value,
					'original_key' => $key
				);
			}
		}

		// Salvar todos os campos em uma única meta se houver dados
		if ( ! empty( $custom_fields_data ) ) {
			$order->update_meta_data( '_owh_domain_custom_fields', $custom_fields_data );
		}

		// Save the order to persist the meta data
		$order->save();
	}

	/**
	 * Get required custom fields for domain products in cart
	 * 
	 * @since 1.0.0
	 * @return array Array of field IDs that are required
	 */
	private function get_required_custom_fields_for_cart() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array();
		}

		$required_fields = array();
		
		// Loop through cart items
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];
			$product = wc_get_product( $product_id );
			
			// Check if it's a domain product
			if ( $product && $product->get_type() === 'domain' ) {
				$product_required_fields = get_post_meta( $product_id, '_domain_required_custom_fields', true );
				
				if ( is_array( $product_required_fields ) ) {
					$required_fields = array_merge( $required_fields, $product_required_fields );
				}
			}
		}

		// Remove duplicates and return
		return array_unique( $required_fields );
	}

	/**
	 * Display custom checkout fields in admin order view
	 * 
	 * Exibe os campos personalizados na página de visualização do pedido no painel administrativo.
	 * Mostra no formato: (Label do Campo) (Valor do Campo)
	 * 
	 * @since 1.0.0
	 * @param WC_Order $order Order object
	 */
	public function display_custom_fields_in_admin_order( $order ) {
		// Get custom fields from order meta
		$custom_fields = $this->get_order_custom_fields( $order );
		
		// Always enqueue the CSS for consistency, even if no fields
		$this->enqueue_admin_custom_fields_styles();
		
		// Get field configurations to show proper labels
		$field_configs = get_option( 'owh_domain_whois_rdap_custom_fields', array() );
		
		// Create a map of field_id => field_config for easier lookup
		$field_map = array();
		foreach ( $field_configs as $field_config ) {
			if ( isset( $field_config['id'] ) ) {
				$field_map[ $field_config['id'] ] = $field_config;
			}
		}

		// Prepare template variables
		$template_vars = array(
			'custom_fields' => $custom_fields,
			'field_map' => $field_map,
			'section_title' => __( 'Informações Adicionais do Domínio', 'owh-domain-whois-rdap' )
		);

		// Load template
		$this->load_admin_template( 'owh-domain-custom-fields-admin-order', $template_vars );
	}

	/**
	 * Enqueue admin styles for custom fields display
	 * 
	 * @since 1.0.0
	 */
	private function enqueue_admin_custom_fields_styles() {
		wp_enqueue_style( 
			$this->plugin_name . '-admin-custom-fields', 
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/owh-domain-custom-fields-admin.css', 
			array(), 
			$this->version, 
			'all' 
		);
	}

	/**
	 * Load admin template with variables
	 * 
	 * @since 1.0.0
	 * @param string $template_name Template file name (without .php extension)
	 * @param array $vars Variables to extract for template use
	 */
	private function load_admin_template( $template_name, $vars = array() ) {
		// Extract variables for template use
		extract( $vars );
		
		// Build template path
		$template_path = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/' . $template_name . '.php';
		
		// Load template if exists
		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			// Fallback error message
			echo '<div class="notice notice-error"><p>' . 
				sprintf( __( 'Template não encontrado: %s', 'owh-domain-whois-rdap' ), $template_name ) . 
				'</p></div>';
		}
	}

	/**
	 * Get custom checkout fields from order meta
	 * 
	 * Método helper para recuperar os campos personalizados salvos no meta da ordem.
	 * 
	 * @since 1.0.0
	 * @param WC_Order|int $order Order object or order ID
	 * @return array Array of custom fields data or empty array if none found
	 */
	public function get_order_custom_fields( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order ) {
			return array();
		}

		$custom_fields = $order->get_meta( '_owh_domain_custom_fields', true );
		
		return is_array( $custom_fields ) ? $custom_fields : array();
	}

	/**
	 * Get specific custom field value from order
	 * 
	 * @since 1.0.0
	 * @param WC_Order|int $order Order object or order ID
	 * @param string|int $field_id Field ID to retrieve
	 * @return string|null Field value or null if not found
	 */
	public function get_order_custom_field_value( $order, $field_id ) {
		$custom_fields = $this->get_order_custom_fields( $order );
		
		if ( isset( $custom_fields[ $field_id ] ) && isset( $custom_fields[ $field_id ]['value'] ) ) {
			return $custom_fields[ $field_id ]['value'];
		}
		
		return null;
	}
}
