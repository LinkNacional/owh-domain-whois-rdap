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
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'add_domain_cart_item_data', 10, 3 );
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'update_domain_cart_item_price' );
		
		// Force Add to Cart button for domain products (Block Theme compatibility)
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'render_domain_add_to_cart_form', 30 );
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
