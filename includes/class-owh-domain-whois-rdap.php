<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://owhgroup.com.br
 * @since      1.0.0
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
/**
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/includes
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class Owh_Domain_Whois_Rdap {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Owh_Domain_Whois_Rdap_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Service Container for dependency injection
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      \OwhDomainWhoisRdap\Services\ServiceContainer    $service_container    The service container.
	 */
	protected $service_container;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'OWH_DOMAIN_WHOIS_RDAP_VERSION' ) ) {
			$this->version = OWH_DOMAIN_WHOIS_RDAP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'owh-domain-whois-rdap';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Owh_Domain_Whois_Rdap_Loader. Orchestrates the hooks of the plugin.
	 * - Owh_Domain_Whois_Rdap_Admin. Defines all hooks for the admin area.
	 * - Owh_Domain_Whois_Rdap_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		$this->loader = new Owh_Domain_Whois_Rdap_Loader();

		// Initialize service container
		$this->service_container = new \OwhDomainWhoisRdap\Services\ServiceContainer();
	}

	
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Owh_Domain_Whois_Rdap_Admin( $this->get_plugin_name(), $this->get_version(), $this->service_container );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_plugin_settings' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_gutenberg_blocks' );
		
		// REST API endpoints
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_rest_routes' );

		// Domain product type hooks
		$this->loader->add_action( 'init', 'Owh_Domain_Product_Registration', 'register_domain_product_type' );
		$this->loader->add_filter( 'product_type_selector', 'Owh_Domain_Product_Registration', 'add_domain_product_type' );
		$this->loader->add_filter( 'woocommerce_product_class', 'Owh_Domain_Product_Registration', 'get_domain_product_class', 10, 2 );
		
		// Domain product admin hooks - Matriz de Preços 3x10
		$this->loader->add_action( 'woocommerce_product_data_tabs', $plugin_admin, 'add_domain_pricing_tab' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'add_domain_pricing_panel' );
		$this->loader->add_action( 'woocommerce_process_product_meta_domain', $plugin_admin, 'save_domain_pricing_matrix_fields' );
		
		// Custom Fields AJAX handlers
		$this->loader->add_action( 'wp_ajax_owh_load_custom_fields', $plugin_admin, 'ajax_load_custom_fields' );
		$this->loader->add_action( 'wp_ajax_owh_save_custom_fields', $plugin_admin, 'ajax_save_custom_fields' );
		
		// Get custom field configs for frontend (both logged in and guests)
		$this->loader->add_action( 'wp_ajax_owh_get_custom_field_configs', $plugin_admin, 'ajax_get_custom_field_configs' );
		$this->loader->add_action( 'wp_ajax_nopriv_owh_get_custom_field_configs', $plugin_admin, 'ajax_get_custom_field_configs' );
		
		// TLD to Product conversion AJAX handler
		$this->loader->add_action( 'wp_ajax_owh_convert_tld_to_product', $plugin_admin, 'ajax_convert_tld_to_product' );
		
		// TLD product status check AJAX handler
		$this->loader->add_action( 'wp_ajax_owh_check_tld_product_status', $plugin_admin, 'ajax_check_tld_product_status' );

		// Display custom fields in admin order view  
		$plugin_public = new Owh_Domain_Whois_Rdap_Public( $this->get_plugin_name(), $this->get_version(), $this->service_container );
		$this->loader->add_action( 'woocommerce_admin_order_data_after_billing_address', $plugin_public, 'display_custom_fields_in_admin_order' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Owh_Domain_Whois_Rdap_Public( $this->get_plugin_name(), $this->get_version(), $this->service_container );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		
		// REST API endpoints
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_rest_routes' );
		
		// Domain product period selection hooks
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'render_domain_period_selector', 18 );
		$this->loader->add_action( 'wp_ajax_get_domain_price_for_period', $plugin_public, 'ajax_get_domain_price_for_period' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_domain_price_for_period', $plugin_public, 'ajax_get_domain_price_for_period' );
		
		// Domain pricing matrix and cart update AJAX handlers
		$this->loader->add_action( 'wp_ajax_owh_get_domain_pricing_matrix', $plugin_public, 'ajax_get_domain_pricing_matrix' );
		$this->loader->add_action( 'wp_ajax_nopriv_owh_get_domain_pricing_matrix', $plugin_public, 'ajax_get_domain_pricing_matrix' );
		$this->loader->add_action( 'wp_ajax_owh_update_domain_period', $plugin_public, 'ajax_update_domain_period' );
		$this->loader->add_action( 'wp_ajax_nopriv_owh_update_domain_period', $plugin_public, 'ajax_update_domain_period' );
		
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'add_domain_cart_item_data', 10, 3 );
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'update_domain_cart_item_price' );
		
		// Filtros para interceptar e corrigir os totais do carrinho
		$this->loader->add_filter( 'woocommerce_cart_get_subtotal', $plugin_public, 'filter_cart_subtotal', 999 );
		$this->loader->add_filter( 'woocommerce_cart_get_total', $plugin_public, 'filter_cart_total', 999 );
		
		// Filtros adicionais para preços individuais dos produtos
		$this->loader->add_filter( 'woocommerce_cart_item_price', $plugin_public, 'filter_cart_item_price', 999, 3 );
		$this->loader->add_filter( 'woocommerce_cart_item_subtotal', $plugin_public, 'filter_cart_item_subtotal', 999, 3 );
		
		// Filtros para WooCommerce Blocks
		$this->loader->add_filter( 'woocommerce_store_api_product_quantity_limit', $plugin_public, 'filter_store_api_price', 999, 3 );
		$this->loader->add_filter( 'woocommerce_blocks_cart_item_data', $plugin_public, 'filter_blocks_cart_item_data', 999, 2 );
		
		// Filtros para forçar preços corretos no carrinho
		$this->loader->add_filter( 'woocommerce_get_cart_contents', $plugin_public, 'filter_cart_contents', 999 );
		$this->loader->add_filter( 'woocommerce_cart_item_price', $plugin_public, 'filter_cart_item_price', 999, 3 );
		$this->loader->add_filter( 'woocommerce_cart_item_subtotal', $plugin_public, 'filter_cart_item_subtotal', 999, 3 );

		// Display domain information in cart and checkout
		$this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_public, 'modify_domain_cart_item_name', 10, 3 );
		$this->loader->add_filter( 'wp_kses_allowed_html', $plugin_public, 'modify_wp_kses_allowed_html', 10, 2 );
		
		// Store API hooks for blocks checkout
		$this->loader->add_action( 'woocommerce_store_api_checkout_update_order_from_request', $plugin_public, 'extend_store_api_item_data', 10, 2 );
		
		// Register Store API extension for blocks
		$this->loader->add_action( 'woocommerce_blocks_loaded', $plugin_public, 'register_store_api_extension' );
		
		// Force Add to Cart button for domain products (Block Theme compatibility)
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'render_domain_add_to_cart_form', 30 );
		
		// Dynamic checkout fields for domain products - use hooks that run after cart is loaded
		$this->loader->add_action( 'woocommerce_checkout_init', $plugin_public, 'register_dynamic_checkout_fields' );
		$this->loader->add_action( 'woocommerce_after_order_notes', $plugin_public, 'display_custom_checkout_fields' );
		$this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'validate_custom_checkout_fields' );
		$this->loader->add_action( 'woocommerce_rest_checkout_process_payment_with_context', $plugin_public, 'save_custom_checkout_fields', 1, 2 );
	
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Owh_Domain_Whois_Rdap_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the service container
	 *
	 * @since     1.0.0
	 * @return    \OwhDomainWhoisRdap\Services\ServiceContainer    The service container.
	 */
	public function get_service_container() {
		return $this->service_container;
	}
}
