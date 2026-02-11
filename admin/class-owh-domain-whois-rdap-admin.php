<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://owh.digital
 * @since      1.0.0
 *
 * @package    Owh_Domain_Whois_Rdap
 * @subpackage Owh_Domain_Whois_Rdap/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Owh_Domain_Whois_Rdap
 * @subpackage Owh_Domain_Whois_Rdap/admin
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class Owh_Domain_Whois_Rdap_Admin {

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
	 * Service container
	 *
	 * @var object
	 */
	private $service_container;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $service_container ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->service_container = $service_container;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/owh-domain-whois-rdap-admin.css', array(), $this->version, 'all' );

		// Carregar CSS específico das configurações na página de configurações do plugin
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) ) {
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			if ( $page === 'owh-rdap' ) {
				wp_enqueue_style( 
					$this->plugin_name . '-settings', 
					plugin_dir_url( __FILE__ ) . 'css/owh-domain-whois-rdap-admin-settings.css', 
					array( $this->plugin_name ), // Dependente do CSS principal
					$this->version, 
					'all' 
				);
				
				// Grid.js CSS
				wp_enqueue_style(
					'gridjs-theme',
					plugin_dir_url( dirname( __FILE__ ) ) . 'node_modules/gridjs/dist/theme/mermaid.min.css',
					array(),
					$this->version,
					'all'
				);
				
				// TLDs Grid CSS
				wp_enqueue_style(
					$this->plugin_name . '-tlds-grid',
					plugin_dir_url( __FILE__ ) . 'css/owh-domain-whois-rdap-tlds-grid.css',
					array(),
					$this->version,
					'all'
				);
			}
		}

		// Domain Product Admin CSS - for product edit pages
		if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
			global $post_type;
			if ( $post_type === 'product' ) {
				wp_enqueue_style(
					$this->plugin_name . '-domain-product-admin',
					plugin_dir_url( __FILE__ ) . 'css/owh-domain-product-admin.css',
					array(),
					$this->version,
					'all'
				);
				
				wp_enqueue_style(
					$this->plugin_name . '-pricing-matrix',
					plugin_dir_url( __FILE__ ) . 'css/owh-domain-pricing-matrix.css',
					array(),
					$this->version,
					'all'
				);
			}
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'wp-api' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/owh-domain-whois-rdap-admin.js', array( 'jquery', 'wp-api' ), $this->version, false );
		
		// Domain Pricing Matrix JS - for product edit pages
		global $pagenow;
		if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
			global $post_type;
			if ( $post_type === 'product' ) {
				wp_enqueue_script(
					$this->plugin_name . '-pricing-matrix',
					plugin_dir_url( __FILE__ ) . 'js/owh-domain-pricing-matrix.js',
					array( 'jquery' ),
					$this->version,
					true
				);
				
				// Domain product type handler
				wp_enqueue_script(
					$this->plugin_name . '-product-type',
					plugin_dir_url( __FILE__ ) . 'js/owh-domain-product-type.js',
					array( 'jquery' ),
					$this->version,
					true
				);
			}
		}
		
		// Localize script for admin strings and API settings
		wp_localize_script( $this->plugin_name, 'owh_rdap_admin', array(
			'rest_url' => rest_url( 'owh-rdap/v1/' ),
			'nonce' => wp_create_nonce( 'owh_rdap_admin_nonce' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'strings' => array(
				'updating' => __( 'Atualizando...', 'owh-domain-whois-rdap' ),
				'updated' => __( 'Atualizado com sucesso!', 'owh-domain-whois-rdap' ),
				'error' => __( 'Erro ao atualizar', 'owh-domain-whois-rdap' )
			)
		) );

		// Carregar scripts específicos na página de configurações do plugin
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'owh-rdap' ) {
			// Script de layout
			wp_enqueue_script( 
				$this->plugin_name . '-layout', 
				plugin_dir_url( __FILE__ ) . 'js/owh-domain-whois-rdap-admin-layout.js', 
				array( 'jquery', $this->plugin_name ), // Dependente do script principal
				$this->version, 
				true 
			);
			
			// Script das configurações
			wp_enqueue_script( 
				$this->plugin_name . '-settings', 
				plugin_dir_url( __FILE__ ) . 'js/owh-domain-whois-rdap-admin-settings.js', 
				array( 'jquery', $this->plugin_name ), // Dependente do script principal
				$this->version, 
				true 
			);
			
			// Grid.js library
			wp_enqueue_script(
				'gridjs',
				plugin_dir_url( dirname( __FILE__ ) ) . 'node_modules/gridjs/dist/gridjs.umd.js',
				array(),
				$this->version,
				true
			);
			
			// TLDs Grid script
			wp_enqueue_script(
				$this->plugin_name . '-tlds-grid',
				plugin_dir_url( __FILE__ ) . 'js/owh-domain-whois-rdap-tlds-grid.js',
				array( 'jquery', 'gridjs', $this->plugin_name ),
				$this->version,
				true
			);
		}

	}

	/**
	 * Register Gutenberg blocks
	 *
	 * @since    1.0.0
	 */
	public function register_gutenberg_blocks() {
		// Check if Gutenberg is available
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Enqueue block scripts
		wp_enqueue_script(
			'owh-rdap-blocks',
			plugin_dir_url( __FILE__ ) . 'js/owh-domain-whois-rdap-blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render' ),
			$this->version
		);

		// Register blocks
		register_block_type( 'owh-rdap/domain-search', array(
			'editor_script' => 'owh-rdap-blocks',
			'render_callback' => array( $this, 'render_search_block' ),
			'attributes' => array(
				'showTitle' => array(
					'type' => 'boolean',
					'default' => true
				),
				'customTitle' => array(
					'type' => 'string',
					'default' => 'Pesquisar Domínio'
				),
				'showExamples' => array(
					'type' => 'boolean',
					'default' => true
				),
				// Textos personalizáveis
				'placeholderText' => array(
					'type' => 'string',
					'default' => 'Digite o nome do domínio...'
				),
				'searchButtonText' => array(
					'type' => 'string',
					'default' => 'Pesquisar'
				),
				'loadingText' => array(
					'type' => 'string',
					'default' => 'Pesquisando...'
				),
				'examplesText' => array(
					'type' => 'string',
					'default' => 'Exemplos:'
				),
				'example1' => array(
					'type' => 'string',
					'default' => 'exemplo.com'
				),
				'example2' => array(
					'type' => 'string',
					'default' => 'meusite.com.br'
				),
				'example3' => array(
					'type' => 'string',
					'default' => 'minhaempresa.org'
				),
				// Visual customizations
				'customCSS' => array(
					'type' => 'string',
					'default' => ''
				),
				'borderWidth' => array(
					'type' => 'number',
					'default' => 0
				),
				'borderColor' => array(
					'type' => 'string',
					'default' => '#ddd'
				),
				'borderRadius' => array(
					'type' => 'number',
					'default' => 8
				),
				'backgroundColor' => array(
					'type' => 'string',
					'default' => '#ffffff'
				),
				'padding' => array(
					'type' => 'number',
					'default' => 20
				),
				// Colors
				'primaryColor' => array(
					'type' => 'string',
					'default' => '#0073aa'
				),
				'buttonHoverColor' => array(
					'type' => 'string',
					'default' => '#005a87'
				),
				'inputBorderColor' => array(
					'type' => 'string',
					'default' => '#ddd'
				),
				'inputFocusColor' => array(
					'type' => 'string',
					'default' => '#0073aa'
				),
				// Layout options
				'buttonLayout' => array(
					'type' => 'string',
					'default' => 'external'
				)
			)
		) );

		register_block_type( 'owh-rdap/domain-results', array(
			'editor_script' => 'owh-rdap-blocks',
			'render_callback' => array( $this, 'render_results_block' ),
			'attributes' => array(
				'showTitle' => array(
					'type' => 'boolean',
					'default' => true
				),
				'customTitle' => array(
					'type' => 'string',
					'default' => 'Resultado da pesquisa para: {domain}'
				),
				// Textos personalizáveis
				'noResultText' => array(
					'type' => 'string',
					'default' => 'Aguardando Pesquisa'
				),
				'noResultDescription' => array(
					'type' => 'string',
					'default' => 'Os resultados da pesquisa de domínios aparecerão aqui.'
				),
				'availableTitle' => array(
					'type' => 'string',
					'default' => 'Domínio Disponível'
				),
				'availableText' => array(
					'type' => 'string',
					'default' => 'Este domínio está disponível para registro!'
				),
				'unavailableTitle' => array(
					'type' => 'string',
					'default' => 'Domínio Indisponível'
				),
				'unavailableText' => array(
					'type' => 'string',
					'default' => 'Este domínio já está registrado e não está disponível.'
				),
				'buyButtonText' => array(
					'type' => 'string',
					'default' => 'Registrar Domínio'
				),
				'detailsButtonText' => array(
					'type' => 'string',
					'default' => 'Ver detalhes completos do WHOIS'
				),
				// Ícones personalizáveis
				'showIcons' => array(
					'type' => 'boolean',
					'default' => true
				),
				'searchIcon' => array(
					'type' => 'string',
					'default' => '🔍'
				),
				'availableIcon' => array(
					'type' => 'string',
					'default' => '✅'
				),
				'unavailableIcon' => array(
					'type' => 'string',
					'default' => '❌'
				),
				'disabledIcon' => array(
					'type' => 'string',
					'default' => '⚠️'
				),
				// Textos para domínio desabilitado
				'disabledTitle' => array(
					'type' => 'string',
					'default' => 'Erro na Pesquisa'
				),
				'disabledText' => array(
					'type' => 'string',
					'default' => 'A tld "{tld}" está desabilitada.'
				),
				// Preview mode
				'previewMode' => array(
					'type' => 'string',
					'default' => 'no-result'
				),
				// Visual customizations
				'customCSS' => array(
					'type' => 'string',
					'default' => ''
				),
				'borderWidth' => array(
					'type' => 'number',
					'default' => 0
				),
				'borderColor' => array(
					'type' => 'string',
					'default' => '#ddd'
				),
				'borderRadius' => array(
					'type' => 'number',
					'default' => 8
				),
				'backgroundColor' => array(
					'type' => 'string',
					'default' => '#ffffff'
				),
				'padding' => array(
					'type' => 'number',
					'default' => 20
				),
				// Colors
				'availableColor' => array(
					'type' => 'string',
					'default' => '#46b450'
				),
				'unavailableColor' => array(
					'type' => 'string',
					'default' => '#dc3232'
				),
				'disabledColor' => array(
					'type' => 'string',
					'default' => '#dc3232'
				)
			)
		) );

		register_block_type( 'owh-rdap/whois-details', array(
			'editor_script' => 'owh-rdap-blocks',
			'render_callback' => array( $this, 'render_whois_details_block' ),
			'attributes' => array(
				'showTitle' => array(
					'type' => 'boolean',
					'default' => true
				),
				'customTitle' => array(
					'type' => 'string',
					'default' => 'Detalhes WHOIS/RDAP'
				),
				'showEvents' => array(
					'type' => 'boolean',
					'default' => true
				),
				'eventsTitle' => array(
					'type' => 'string',
					'default' => 'Histórico de Eventos'
				),
				'showEntities' => array(
					'type' => 'boolean',
					'default' => true
				),
				'entitiesTitle' => array(
					'type' => 'string',
					'default' => 'Entidades Relacionadas'
				),
				'showNameservers' => array(
					'type' => 'boolean',
					'default' => true
				),
				'nameserversTitle' => array(
					'type' => 'string',
					'default' => 'Servidores DNS (Nameservers)'
				),
				'showStatus' => array(
					'type' => 'boolean',
					'default' => true
				),
				'statusTitle' => array(
					'type' => 'string',
					'default' => 'Status do Domínio'
				),
				'showDnssec' => array(
					'type' => 'boolean',
					'default' => true
				),
				'dnssecTitle' => array(
					'type' => 'string',
					'default' => 'DNSSEC'
				),
				'noDomainText' => array(
					'type' => 'string',
					'default' => 'Nenhum Domínio Informado'
				),
				'noDomainDescription' => array(
					'type' => 'string',
					'default' => 'Para visualizar os detalhes WHOIS, acesse esta página através do link "Ver detalhes completos" nos resultados da pesquisa.'
				),
				'availableText' => array(
					'type' => 'string',
					'default' => 'Este domínio está disponível para registro e não possui informações WHOIS.'
				),
				'errorText' => array(
					'type' => 'string',
					'default' => 'Erro na Pesquisa'
				),
				'previewMode' => array(
					'type' => 'string',
					'default' => 'no-domain'
				)
			)
		) );

		// Set up translations for JavaScript
		wp_set_script_translations( 'owh-rdap-blocks', 'owh-domain-whois-rdap' );
	}

	/**
	 * Render search block
	 *
	 * @since    1.0.0
	 */
	public function render_search_block( $attributes ) {
		$show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
		$show_examples = isset( $attributes['showExamples'] ) ? $attributes['showExamples'] : true;
		
		// Build shortcode attributes from block attributes
		$shortcode_atts = array();
		$shortcode_atts[] = $show_title ? 'show_title="true"' : 'show_title="false"';
		$shortcode_atts[] = $show_examples ? 'show_examples="true"' : 'show_examples="false"';
		
		// Add custom texts
		if ( isset( $attributes['customTitle'] ) && ! empty( $attributes['customTitle'] ) ) {
			$shortcode_atts[] = 'custom_title="' . esc_attr( $attributes['customTitle'] ) . '"';
		}
		if ( isset( $attributes['placeholderText'] ) && ! empty( $attributes['placeholderText'] ) ) {
			$shortcode_atts[] = 'placeholder_text="' . esc_attr( $attributes['placeholderText'] ) . '"';
		}
		if ( isset( $attributes['searchButtonText'] ) && ! empty( $attributes['searchButtonText'] ) ) {
			$shortcode_atts[] = 'search_button_text="' . esc_attr( $attributes['searchButtonText'] ) . '"';
		}
		if ( isset( $attributes['examplesText'] ) && ! empty( $attributes['examplesText'] ) ) {
			$shortcode_atts[] = 'examples_text="' . esc_attr( $attributes['examplesText'] ) . '"';
		}
		if ( isset( $attributes['example1'] ) && ! empty( $attributes['example1'] ) ) {
			$shortcode_atts[] = 'example1="' . esc_attr( $attributes['example1'] ) . '"';
		}
		if ( isset( $attributes['example2'] ) && ! empty( $attributes['example2'] ) ) {
			$shortcode_atts[] = 'example2="' . esc_attr( $attributes['example2'] ) . '"';
		}
		if ( isset( $attributes['example3'] ) && ! empty( $attributes['example3'] ) ) {
			$shortcode_atts[] = 'example3="' . esc_attr( $attributes['example3'] ) . '"';
		}
		
		// Add visual customizations
		if ( isset( $attributes['customCSS'] ) && ! empty( $attributes['customCSS'] ) ) {
			$shortcode_atts[] = 'custom_css="' . esc_attr( $attributes['customCSS'] ) . '"';
		}
		if ( isset( $attributes['borderWidth'] ) ) {
			$shortcode_atts[] = 'border_width="' . esc_attr( $attributes['borderWidth'] ) . '"';
		}
		if ( isset( $attributes['borderColor'] ) && ! empty( $attributes['borderColor'] ) ) {
			$shortcode_atts[] = 'border_color="' . esc_attr( $attributes['borderColor'] ) . '"';
		}
		if ( isset( $attributes['borderRadius'] ) ) {
			$shortcode_atts[] = 'border_radius="' . esc_attr( $attributes['borderRadius'] ) . '"';
		}
		if ( isset( $attributes['backgroundColor'] ) && ! empty( $attributes['backgroundColor'] ) ) {
			$shortcode_atts[] = 'background_color="' . esc_attr( $attributes['backgroundColor'] ) . '"';
		}
		if ( isset( $attributes['padding'] ) ) {
			$shortcode_atts[] = 'padding="' . esc_attr( $attributes['padding'] ) . '"';
		}
		
		// Add color customizations
		if ( isset( $attributes['primaryColor'] ) && ! empty( $attributes['primaryColor'] ) ) {
			$shortcode_atts[] = 'primary_color="' . esc_attr( $attributes['primaryColor'] ) . '"';
		}
		if ( isset( $attributes['buttonHoverColor'] ) && ! empty( $attributes['buttonHoverColor'] ) ) {
			$shortcode_atts[] = 'button_hover_color="' . esc_attr( $attributes['buttonHoverColor'] ) . '"';
		}
		if ( isset( $attributes['inputBorderColor'] ) && ! empty( $attributes['inputBorderColor'] ) ) {
			$shortcode_atts[] = 'input_border_color="' . esc_attr( $attributes['inputBorderColor'] ) . '"';
		}
		if ( isset( $attributes['inputFocusColor'] ) && ! empty( $attributes['inputFocusColor'] ) ) {
			$shortcode_atts[] = 'input_focus_color="' . esc_attr( $attributes['inputFocusColor'] ) . '"';
		}
		
		// Add layout options
		if ( isset( $attributes['buttonLayout'] ) && ! empty( $attributes['buttonLayout'] ) ) {
			$shortcode_atts[] = 'button_layout="' . esc_attr( $attributes['buttonLayout'] ) . '"';
		}
		
		return do_shortcode( '[owh-rdap-whois-search ' . implode( ' ', $shortcode_atts ) . ']' );
	}

	/**
	 * Render results block
	 *
	 * @since    1.0.0
	 */
	public function render_results_block( $attributes ) {
		$show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
		
		// Build shortcode attributes from block attributes
		$shortcode_atts = array();
		$shortcode_atts[] = $show_title ? 'show_title="true"' : 'show_title="false"';
		
		// Add custom texts
		if ( isset( $attributes['customTitle'] ) && ! empty( $attributes['customTitle'] ) ) {
			$shortcode_atts[] = 'custom_title="' . esc_attr( $attributes['customTitle'] ) . '"';
		}
		if ( isset( $attributes['noResultText'] ) && ! empty( $attributes['noResultText'] ) ) {
			$shortcode_atts[] = 'no_result_text="' . esc_attr( $attributes['noResultText'] ) . '"';
		}
		if ( isset( $attributes['noResultDescription'] ) && ! empty( $attributes['noResultDescription'] ) ) {
			$shortcode_atts[] = 'no_result_description="' . esc_attr( $attributes['noResultDescription'] ) . '"';
		}
		if ( isset( $attributes['availableTitle'] ) && ! empty( $attributes['availableTitle'] ) ) {
			$shortcode_atts[] = 'available_title="' . esc_attr( $attributes['availableTitle'] ) . '"';
		}
		if ( isset( $attributes['availableText'] ) && ! empty( $attributes['availableText'] ) ) {
			$shortcode_atts[] = 'available_text="' . esc_attr( $attributes['availableText'] ) . '"';
		}
		if ( isset( $attributes['unavailableTitle'] ) && ! empty( $attributes['unavailableTitle'] ) ) {
			$shortcode_atts[] = 'unavailable_title="' . esc_attr( $attributes['unavailableTitle'] ) . '"';
		}
		if ( isset( $attributes['unavailableText'] ) && ! empty( $attributes['unavailableText'] ) ) {
			$shortcode_atts[] = 'unavailable_text="' . esc_attr( $attributes['unavailableText'] ) . '"';
		}
		if ( isset( $attributes['buyButtonText'] ) && ! empty( $attributes['buyButtonText'] ) ) {
			$shortcode_atts[] = 'buy_button_text="' . esc_attr( $attributes['buyButtonText'] ) . '"';
		}
		if ( isset( $attributes['detailsButtonText'] ) && ! empty( $attributes['detailsButtonText'] ) ) {
			$shortcode_atts[] = 'details_button_text="' . esc_attr( $attributes['detailsButtonText'] ) . '"';
		}
		
		// Add icon settings
		if ( isset( $attributes['showIcons'] ) ) {
			$shortcode_atts[] = 'show_icons="' . ( $attributes['showIcons'] ? 'true' : 'false' ) . '"';
		}
		if ( isset( $attributes['searchIcon'] ) && ! empty( $attributes['searchIcon'] ) ) {
			$shortcode_atts[] = 'search_icon="' . esc_attr( $attributes['searchIcon'] ) . '"';
		}
		if ( isset( $attributes['availableIcon'] ) && ! empty( $attributes['availableIcon'] ) ) {
			$shortcode_atts[] = 'available_icon="' . esc_attr( $attributes['availableIcon'] ) . '"';
		}
		if ( isset( $attributes['unavailableIcon'] ) && ! empty( $attributes['unavailableIcon'] ) ) {
			$shortcode_atts[] = 'unavailable_icon="' . esc_attr( $attributes['unavailableIcon'] ) . '"';
		}
		if ( isset( $attributes['disabledIcon'] ) && ! empty( $attributes['disabledIcon'] ) ) {
			$shortcode_atts[] = 'disabled_icon="' . esc_attr( $attributes['disabledIcon'] ) . '"';
		}
		
		// Add disabled texts
		if ( isset( $attributes['disabledTitle'] ) && ! empty( $attributes['disabledTitle'] ) ) {
			$shortcode_atts[] = 'disabled_title="' . esc_attr( $attributes['disabledTitle'] ) . '"';
		}
		if ( isset( $attributes['disabledText'] ) && ! empty( $attributes['disabledText'] ) ) {
			$shortcode_atts[] = 'disabled_text="' . esc_attr( $attributes['disabledText'] ) . '"';
		}
		
		// Add visual customizations
		if ( isset( $attributes['customCSS'] ) && ! empty( $attributes['customCSS'] ) ) {
			$shortcode_atts[] = 'custom_css="' . esc_attr( $attributes['customCSS'] ) . '"';
		}
		if ( isset( $attributes['borderWidth'] ) && $attributes['borderWidth'] !== '' ) {
			$shortcode_atts[] = 'border_width="' . esc_attr( $attributes['borderWidth'] ) . '"';
		}
		if ( isset( $attributes['borderColor'] ) && ! empty( $attributes['borderColor'] ) ) {
			$shortcode_atts[] = 'border_color="' . esc_attr( $attributes['borderColor'] ) . '"';
		}
		if ( isset( $attributes['borderRadius'] ) && $attributes['borderRadius'] !== '' ) {
			$shortcode_atts[] = 'border_radius="' . esc_attr( $attributes['borderRadius'] ) . '"';
		}
		if ( isset( $attributes['backgroundColor'] ) && ! empty( $attributes['backgroundColor'] ) ) {
			$shortcode_atts[] = 'background_color="' . esc_attr( $attributes['backgroundColor'] ) . '"';
		}
		if ( isset( $attributes['padding'] ) && $attributes['padding'] !== '' ) {
			$shortcode_atts[] = 'padding="' . esc_attr( $attributes['padding'] ) . '"';
		}
		
		// Add color customizations
		if ( isset( $attributes['availableColor'] ) && ! empty( $attributes['availableColor'] ) ) {
			$shortcode_atts[] = 'available_color="' . esc_attr( $attributes['availableColor'] ) . '"';
		}
		if ( isset( $attributes['unavailableColor'] ) && ! empty( $attributes['unavailableColor'] ) ) {
			$shortcode_atts[] = 'unavailable_color="' . esc_attr( $attributes['unavailableColor'] ) . '"';
		}
		if ( isset( $attributes['disabledColor'] ) && ! empty( $attributes['disabledColor'] ) ) {
			$shortcode_atts[] = 'disabled_color="' . esc_attr( $attributes['disabledColor'] ) . '"';
		}
		
		return do_shortcode( '[owh-rdap-whois-results ' . implode( ' ', $shortcode_atts ) . ']' );
	}

	/**
	 * Render WHOIS details block
	 *
	 * @since    1.0.0
	 */
	public function render_whois_details_block( $attributes ) {
		$show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
		
		// Build shortcode attributes from block attributes
		$shortcode_atts = array();
		$shortcode_atts[] = $show_title ? 'show_title="true"' : 'show_title="false"';
		
		// Add custom texts and titles
		if ( isset( $attributes['customTitle'] ) ) {
			$shortcode_atts[] = 'custom_title="' . esc_attr( $attributes['customTitle'] ) . '"';
		}
		
		// Add section visibility
		if ( isset( $attributes['showEvents'] ) ) {
			$shortcode_atts[] = 'show_events="' . ( $attributes['showEvents'] ? 'true' : 'false' ) . '"';
		}
		if ( isset( $attributes['showEntities'] ) ) {
			$shortcode_atts[] = 'show_entities="' . ( $attributes['showEntities'] ? 'true' : 'false' ) . '"';
		}
		if ( isset( $attributes['showNameservers'] ) ) {
			$shortcode_atts[] = 'show_nameservers="' . ( $attributes['showNameservers'] ? 'true' : 'false' ) . '"';
		}
		if ( isset( $attributes['showStatus'] ) ) {
			$shortcode_atts[] = 'show_status="' . ( $attributes['showStatus'] ? 'true' : 'false' ) . '"';
		}
		if ( isset( $attributes['showDnssec'] ) ) {
			$shortcode_atts[] = 'show_dnssec="' . ( $attributes['showDnssec'] ? 'true' : 'false' ) . '"';
		}
		
		// Add section titles
		if ( isset( $attributes['eventsTitle'] ) ) {
			$shortcode_atts[] = 'events_title="' . esc_attr( $attributes['eventsTitle'] ) . '"';
		}
		if ( isset( $attributes['entitiesTitle'] ) ) {
			$shortcode_atts[] = 'entities_title="' . esc_attr( $attributes['entitiesTitle'] ) . '"';
		}
		if ( isset( $attributes['nameserversTitle'] ) ) {
			$shortcode_atts[] = 'nameservers_title="' . esc_attr( $attributes['nameserversTitle'] ) . '"';
		}
		if ( isset( $attributes['statusTitle'] ) ) {
			$shortcode_atts[] = 'status_title="' . esc_attr( $attributes['statusTitle'] ) . '"';
		}
		if ( isset( $attributes['dnssecTitle'] ) ) {
			$shortcode_atts[] = 'dnssec_title="' . esc_attr( $attributes['dnssecTitle'] ) . '"';
		}
		
		// Add custom texts
		if ( isset( $attributes['noDomainText'] ) ) {
			$shortcode_atts[] = 'no_domain_text="' . esc_attr( $attributes['noDomainText'] ) . '"';
		}
		if ( isset( $attributes['noDomainDescription'] ) ) {
			$shortcode_atts[] = 'no_domain_description="' . esc_attr( $attributes['noDomainDescription'] ) . '"';
		}
		if ( isset( $attributes['availableText'] ) ) {
			$shortcode_atts[] = 'available_text="' . esc_attr( $attributes['availableText'] ) . '"';
		}
		if ( isset( $attributes['errorText'] ) ) {
			$shortcode_atts[] = 'error_text="' . esc_attr( $attributes['errorText'] ) . '"';
		}
		
		// Add CSS styling attributes
		if ( isset( $attributes['borderWidth'] ) ) {
			$shortcode_atts[] = 'border_width="' . intval( $attributes['borderWidth'] ) . '"';
		}
		if ( isset( $attributes['borderColor'] ) ) {
			$shortcode_atts[] = 'border_color="' . esc_attr( $attributes['borderColor'] ) . '"';
		}
		if ( isset( $attributes['borderRadius'] ) ) {
			$shortcode_atts[] = 'border_radius="' . intval( $attributes['borderRadius'] ) . '"';
		}
		if ( isset( $attributes['backgroundColor'] ) ) {
			$shortcode_atts[] = 'background_color="' . esc_attr( $attributes['backgroundColor'] ) . '"';
		}
		if ( isset( $attributes['padding'] ) ) {
			$shortcode_atts[] = 'padding="' . intval( $attributes['padding'] ) . '"';
		}
		if ( isset( $attributes['customCSS'] ) && ! empty( trim( $attributes['customCSS'] ) ) ) {
			$shortcode_atts[] = 'custom_css="' . esc_attr( $attributes['customCSS'] ) . '"';
		}
		if ( isset( $attributes['showIcon'] ) ) {
			$shortcode_atts[] = 'show_icon="' . ( $attributes['showIcon'] ? 'true' : 'false' ) . '"';
		}
		if ( isset( $attributes['customIcon'] ) ) {
			$shortcode_atts[] = 'custom_icon="' . esc_attr( $attributes['customIcon'] ) . '"';
		}
		
		$shortcode_string = '[owh-rdap-whois-details ' . implode( ' ', $shortcode_atts ) . ']';
		return do_shortcode( $shortcode_string );
	}

	/**
	 * Add plugin admin menu
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		// Add main OWH menu
		add_menu_page(
			__( 'OWH', 'owh-domain-whois-rdap' ),
			__( 'OWH', 'owh-domain-whois-rdap' ),
			'manage_options',
			'owh-rdap',
			array( $this, 'display_plugin_setup_page' ),
			'dashicons-admin-site-alt3',
			30
		);
	}

	/**
	 * Display plugin admin page
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_setup_page() {
		include_once( 'partials/owh-domain-whois-rdap-admin-settings.php' );
	}

	/**
	 * Register settings
	 *
	 * @since    1.0.0
	 */
	public function register_plugin_settings() {
		// Register settings with sanitization
		register_setting( 'owh_rdap_settings', 'owh_rdap_enable_search', array(
			'sanitize_callback' => 'absint'
		) );
		register_setting( 'owh_rdap_settings', 'owh_rdap_results_page', array(
			'sanitize_callback' => 'absint'
		) );
		register_setting( 'owh_rdap_settings', 'owh_rdap_whois_details_page', array(
			'sanitize_callback' => 'absint'
		) );
		register_setting( 'owh_rdap_settings', 'owh_rdap_integration_type', array(
			'sanitize_callback' => 'sanitize_key'
		) );
		register_setting( 'owh_rdap_settings', 'owh_rdap_custom_url', array(
			'sanitize_callback' => 'esc_url_raw'
		) );
		register_setting( 'owh_rdap_settings', 'owh_rdap_whmcs_url', array(
			'sanitize_callback' => 'esc_url_raw'
		) );
		
		// Main settings section
		add_settings_section(
			'owh_rdap_main_settings',
			__( 'Ferramenta de Pesquisa de Domínios', 'owh-domain-whois-rdap' ),
			array( $this, 'main_settings_callback' ),
			'owh_rdap_settings'
		);

		// Enable/Disable search
		add_settings_field(
			'owh_rdap_enable_search',
			__( 'Ativar Pesquisa de Domínios (RDAP/WHOIS)', 'owh-domain-whois-rdap' ),
			array( $this, 'enable_search_callback' ),
			'owh_rdap_settings',
			'owh_rdap_main_settings'
		);

		// Results page
		add_settings_field(
			'owh_rdap_results_page',
			__( 'Página de Resultados da Pesquisa', 'owh-domain-whois-rdap' ),
			array( $this, 'results_page_callback' ),
			'owh_rdap_settings',
			'owh_rdap_main_settings'
		);

		// WHOIS details page
		add_settings_field(
			'owh_rdap_whois_details_page',
			__( 'Página de Detalhes WHOIS', 'owh-domain-whois-rdap' ),
			array( $this, 'whois_details_page_callback' ),
			'owh_rdap_settings',
			'owh_rdap_main_settings'
		);

		// Integration section
		add_settings_section(
			'owh_rdap_integration_settings',
			__( 'Configurações de Integração', 'owh-domain-whois-rdap' ),
			array( $this, 'integration_settings_callback' ),
			'owh_rdap_settings'
		);

		// Integration type
		add_settings_field(
			'owh_rdap_integration_type',
			__( 'Tipo de Integração', 'owh-domain-whois-rdap' ),
			array( $this, 'integration_type_callback' ),
			'owh_rdap_settings',
			'owh_rdap_integration_settings'
		);

		// Custom URL
		add_settings_field(
			'owh_rdap_custom_url',
			__( 'Custom URL', 'owh-domain-whois-rdap' ),
			array( $this, 'custom_url_callback' ),
			'owh_rdap_settings',
			'owh_rdap_integration_settings'
		);

		// WHMCS URL
		add_settings_field(
			'owh_rdap_whmcs_url',
			__( 'WHMCS', 'owh-domain-whois-rdap' ),
			array( $this, 'whmcs_url_callback' ),
			'owh_rdap_settings',
			'owh_rdap_integration_settings'
		);
	}

	/**
	 * Main settings section callback
	 */
	public function main_settings_callback() {
		echo '<p>' . esc_html__( 'Ative a ferramenta de pesquisa de domínios (Whois) no seu site.', 'owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Integration settings callback
	 */
	public function integration_settings_callback() {
		echo '<p>' . esc_html__( 'Configure como os domínios disponíveis serão direcionados para compra.', 'owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Enable search field callback
	 */
	public function enable_search_callback() {
		$value = get_option( 'owh_rdap_enable_search', false );
		// Convert to integer to ensure proper comparison
		$value = (int) $value;
		echo '<fieldset>';
		echo '<label><input type="radio" name="owh_rdap_enable_search" value="1"' . checked( 1, $value, false ) . '> ' . esc_html__( 'Ativar', 'owh-domain-whois-rdap' ) . '</label><br>';
		echo '<label><input type="radio" name="owh_rdap_enable_search" value="0"' . checked( 0, $value, false ) . '> ' . esc_html__( 'Desativar', 'owh-domain-whois-rdap' ) . '</label>';
		echo '</fieldset>';
		echo '<p class="description">' . esc_html__( 'Ao ativar este recurso, você poderá inserir o formulário de pesquisa em qualquer página ou post através de shortcodes e blocos do WordPress.', 'owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Results page field callback
	 */
	public function results_page_callback() {
		$value = get_option( 'owh_rdap_results_page', '' );
		
		wp_dropdown_pages( array(
			'name'             => 'owh_rdap_results_page',
			'selected'         => $value,
			'show_option_none' => __( 'Página Resultado Pesquisa domínios', 'owh-domain-whois-rdap' ),
			'option_none_value' => '',
		) );
		
		echo '<p class="description">' . esc_html__( 'Escolha a página que mostrará os resultados. Para funcionar, você precisa copiar e colar o shortcode [owh-rdap-whois-results] no conteúdo desta página (no editor de texto ou em um bloco de shortcode).', 'owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * WHOIS details page field callback
	 */
	public function whois_details_page_callback() {
		$value = get_option( 'owh_rdap_whois_details_page', '' );
		
		wp_dropdown_pages( array(
			'name'             => 'owh_rdap_whois_details_page',
			'selected'         => $value,
			'show_option_none' => __( 'Selecione a página de detalhes WHOIS', 'owh-domain-whois-rdap' ),
			'option_none_value' => '',
		) );
		
		echo '<p class="description">' . esc_html__( 'Escolha a página que mostrará os detalhes WHOIS completos dos domínios registrados. Para funcionar, você precisa copiar e colar o shortcode [owh-rdap-whois-details] no conteúdo desta página.', 'owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Integration type field callback
	 */
	public function integration_type_callback() {
		$value = get_option( 'owh_rdap_integration_type', 'none' );
		echo '<select name="owh_rdap_integration_type" id="owh_rdap_integration_type">';
		echo '<option value="none"' . selected( 'none', $value, false ) . '>' . esc_html__( 'Nenhum', 'owh-domain-whois-rdap' ) . '</option>';
		echo '<option value="custom"' . selected( 'custom', $value, false ) . '>' . esc_html__( 'Custom URL', 'owh-domain-whois-rdap' ) . '</option>';
		echo '<option value="whmcs"' . selected( 'whmcs', $value, false ) . '>' . esc_html__( 'WHMCS', 'owh-domain-whois-rdap' ) . '</option>';
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Selecione o seu sistema de vendas de domínios para criar a integração. Selecione "Nenhum" se não desejar exibir botões de compra.', 'owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Custom URL field callback
	 */
	public function custom_url_callback() {
		$integration_type = get_option( 'owh_rdap_integration_type', 'none' );
		$value = get_option( 'owh_rdap_custom_url', '' );
		
		$style = ($integration_type === 'custom') ? '' : 'style="display: none;"';
		
		echo '<div id="custom_url_section" ' . esc_attr( $style ) . '>';
		echo '<p>' . esc_html__( 'Configurar os parâmetros da URL para seguir com o registro do domínio', 'owh-domain-whois-rdap' ) . '</p>';
		echo '<p>' . esc_html__( 'Configure um URL personalizado para registrar domínios', 'owh-domain-whois-rdap' ) . '</p>';
		echo '<input type="url" name="owh_rdap_custom_url" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://cliente.linknacional.com.br" />';
		echo '<p class="description">' . esc_html__( 'Tags de template disponíveis: {domain}, {sld}, {tld} exemplo: https://who.linknacional.com/whois/?domain={domain}', 'owh-domain-whois-rdap' ) . '</p>';
		echo '</div>';
	}

	/**
	 * WHMCS URL field callback
	 */
	public function whmcs_url_callback() {
		$integration_type = get_option( 'owh_rdap_integration_type', 'none' );
		$value = get_option( 'owh_rdap_whmcs_url', '' );
		
		$style = ($integration_type === 'whmcs') ? '' : 'style="display: none;"';
		
		echo '<div id="whmcs_url_section" ' . esc_attr( $style ) . '>';
		echo '<p>' . esc_html__( 'Configurar os parâmetros da URL para seguir com o registro do domínio no WHMCS', 'owh-domain-whois-rdap' ) . '</p>';
		echo '<p>' . esc_html__( 'Configure o URL do WHMCS para o registro de domínios.', 'owh-domain-whois-rdap' ) . '</p>';
		echo '<input type="url" name="owh_rdap_whmcs_url" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://cliente.linknacional.com.br" />';
		echo '<p class="description">' . esc_html__( 'Informe o URL de instalação do seu WHMCS', 'owh-domain-whois-rdap' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Register REST API routes
	 *
	 * @since    1.0.0
	 */
	public function register_rest_routes() {
		register_rest_route( 'owh-rdap/v1', '/update-servers', array(
			'methods' => 'POST',
			'callback' => array( $this, 'rest_update_rdap_servers' ),
			'permission_callback' => array( $this, 'rest_permissions_check' ),
		) );

		register_rest_route( 'owh-rdap/v1', '/server-status', array(
			'methods' => 'GET',
			'callback' => array( $this, 'rest_get_server_status' ),
			'permission_callback' => array( $this, 'rest_permissions_check' ),
		) );

		register_rest_route( 'owh-rdap/v1', '/custom-tlds', array(
			'methods' => 'POST',
			'callback' => array( $this, 'rest_save_custom_tlds' ),
			'permission_callback' => array( $this, 'rest_permissions_check' ),
		) );

		register_rest_route( 'owh-rdap/v1', '/tlds-config', array(
			'methods' => 'GET',
			'callback' => array( $this, 'rest_get_tlds_config' ),
			'permission_callback' => array( $this, 'rest_permissions_check' ),
		) );

		register_rest_route( 'owh-rdap/v1', '/tlds-config', array(
			'methods' => 'POST',
			'callback' => array( $this, 'rest_save_tlds_config' ),
			'permission_callback' => array( $this, 'rest_permissions_check' ),
		) );
	}

	/**
	 * REST API permission check
	 *
	 * @since    1.0.0
	 */
	public function rest_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * REST API handler for updating RDAP servers
	 *
	 * @since    1.0.0
	 */
	public function rest_update_rdap_servers( $request ) {
		try {
			// Get the BootstrapFileHandler service from container
			$bootstrap_handler = $this->service_container->get( 'BootstrapFileHandler' );
			
			// Update DNS data with detailed error information
			$result = $bootstrap_handler->updateDnsDataWithDetails();
			
			if ( $result['success'] ) {
				return new \WP_REST_Response( array(
					'success' => true,
					'message' => $result['message']
				), 200 );
			} else {
				return new \WP_Error( 'update_failed', $result['message'], array( 'status' => 500 ) );
			}
		} catch ( Exception $e ) {
			return new \WP_Error( 'server_error', __( 'Erro interno: ', 'owh-domain-whois-rdap' ) . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * REST API handler for getting server status
	 *
	 * @since    1.0.0
	 */
	public function rest_get_server_status( $request ) {
		try {
			// Get the BootstrapFileHandler service from container
			$bootstrap_handler = $this->service_container->get( 'BootstrapFileHandler' );
			
			// Get update info
			$info = $bootstrap_handler->getLastUpdateInfo();
			
			return new \WP_REST_Response( array(
				'success' => true,
				'data' => $info
			), 200 );
		} catch ( Exception $e ) {
			return new \WP_Error( 'server_error', __( 'Erro interno: ', 'owh-domain-whois-rdap' ) . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * REST API handler for saving custom TLDs
	 *
	 * @since    1.0.0
	 */
	public function rest_save_custom_tlds( $request ) {
		try {
			// Get the custom TLDs data from request
			$params = $request->get_json_params();
			$custom_tlds = isset( $params['custom_tlds'] ) ? $params['custom_tlds'] : array();
			
			// Simple validation and processing
			$processed_tlds = array();
			
			foreach ( $custom_tlds as $tld_data ) {
				if ( is_array( $tld_data ) && 
					 isset( $tld_data['tld'] ) && 
					 isset( $tld_data['rdap_url'] ) ) {
					
					$tld = trim( sanitize_text_field( $tld_data['tld'] ) );
					$rdap_url = trim( sanitize_url( $tld_data['rdap_url'] ) );
					
					if ( ! empty( $tld ) && ! empty( $rdap_url ) ) {
						// Ensure TLD starts with dot
						if ( substr( $tld, 0, 1 ) !== '.' ) {
							$tld = '.' . $tld;
						}
						
						$processed_tlds[] = array(
							'tld' => $tld,
							'rdap_url' => $rdap_url
						);
					}
				}
			}
			
			// Save using WordPress function
			$option_name = 'owh_domain_whois_rdap_custom_tlds';
			$save_result = update_option( $option_name, $processed_tlds );
			
			return new \WP_REST_Response( array(
				'success' => true,
				'message' => sprintf( 'TLDs customizadas salvas com sucesso! %d TLD(s) configurada(s).', count( $processed_tlds ) )
			), 200 );

		} catch ( Exception $e ) {
			return new \WP_Error( 'save_error', 'Erro interno: ' . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * REST API handler for getting TLDs configuration
	 *
	 * @since    1.0.0
	 */
	public function rest_get_tlds_config( $request ) {
		try {
			// Get DNS data from local file
			$dns_file = plugin_dir_path( dirname( __FILE__ ) ) . 'data/dns.json';
			if ( ! file_exists( $dns_file ) ) {
				return new \WP_Error( 'file_not_found', 'Arquivo DNS.json não encontrado', array( 'status' => 404 ) );
			}

			$dns_data = json_decode( file_get_contents( $dns_file ), true );
			if ( ! $dns_data || ! isset( $dns_data['services'] ) ) {
				return new \WP_Error( 'invalid_data', 'Dados DNS inválidos', array( 'status' => 500 ) );
			}

			// Get saved TLDs configuration
			$saved_config = get_option( 'owh_domain_whois_rdap_tlds_config', array() );
			
			// Process TLDs from DNS data
			$tlds_data = array();
			
			foreach ( $dns_data['services'] as $service ) {
				if ( isset( $service[0] ) && isset( $service[1] ) && is_array( $service[0] ) ) {
					foreach ( $service[0] as $tld ) {
						$tld = '.' . $tld; // Ensure TLD starts with dot
						
						$providers = is_array( $service[1] ) ? $service[1] : array();
						
						// Get saved configuration for this TLD (only stores disabled TLDs)
						$tld_config = isset( $saved_config[ $tld ] ) ? $saved_config[ $tld ] : array();
						
						$tlds_data[] = array(
							'tld' => $tld,
							'providers' => $providers,
							'selectedProvider' => isset( $tld_config['provider'] ) ? $tld_config['provider'] : ( ! empty( $providers ) ? $providers[0] : '' ),
							'enabled' => isset( $tld_config['enabled'] ) ? $tld_config['enabled'] : true // Default: enabled
						);
					}
				}
			}

			// Add custom TLDs
			$custom_tlds = get_option( 'owh_domain_whois_rdap_custom_tlds', array() );
			if ( is_array( $custom_tlds ) ) {
				foreach ( $custom_tlds as $custom_tld ) {
					if ( ! empty( $custom_tld['tld'] ) && ! empty( $custom_tld['rdap_url'] ) ) {
						$tld = $custom_tld['tld'];
						
						// Ensure TLD starts with dot
						if ( strpos( $tld, '.' ) !== 0 ) {
							$tld = '.' . $tld;
						}
						
						// Check if this TLD already exists in official list
						$tld_exists = false;
						foreach ( $tlds_data as $existing_tld ) {
							if ( $existing_tld['tld'] === $tld ) {
								$tld_exists = true;
								break;
							}
						}
						
						// Only add if it doesn't exist in official list
						if ( ! $tld_exists ) {
							// Get saved configuration for this custom TLD
							$tld_config = isset( $saved_config[ $tld ] ) ? $saved_config[ $tld ] : array();
							
							$tlds_data[] = array(
								'tld' => $tld,
								'providers' => array( $custom_tld['rdap_url'] ),
								'selectedProvider' => $custom_tld['rdap_url'],
								'enabled' => isset( $tld_config['enabled'] ) ? $tld_config['enabled'] : true // Default: enabled
							);
						}
					}
				}
			}
			
			// Sort by TLD
			usort( $tlds_data, function( $a, $b ) {
				return strcmp( $a['tld'], $b['tld'] );
			} );

			return new \WP_REST_Response( array(
				'success' => true,
				'data' => $tlds_data
			), 200 );

		} catch ( Exception $e ) {
			return new \WP_Error( 'load_error', 'Erro ao carregar configurações: ' . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * REST API handler for saving TLDs configuration
	 *
	 * @since    1.0.0
	 */
	public function rest_save_tlds_config( $request ) {
		try {
			$params = $request->get_json_params();
			$config_data = isset( $params['config'] ) ? $params['config'] : array();
			
			if ( ! is_array( $config_data ) ) {
				return new \WP_Error( 'invalid_data', 'Dados de configuração inválidos', array( 'status' => 400 ) );
			}
			
			// Process and validate configuration - only save disabled TLDs
			$processed_config = array();
			
			foreach ( $config_data as $item ) {
				if ( isset( $item['tld'] ) && isset( $item['provider'] ) && isset( $item['enabled'] ) ) {
					$tld = sanitize_text_field( $item['tld'] );
					$provider = sanitize_text_field( $item['provider'] );
					$enabled = (bool) $item['enabled'];
					
					// Only save if TLD is DISABLED (optimization: don't save enabled ones)
					if ( ! $enabled ) {
						$processed_config[ $tld ] = array(
							'provider' => $provider,
							'enabled' => false
						);
					}
				}
			}
			
			// Save configuration (only disabled TLDs)
			update_option( 'owh_domain_whois_rdap_tlds_config', $processed_config );
			
			return new \WP_REST_Response( array(
				'success' => true,
				'message' => 'Configurações salvas com sucesso!'
			), 200 );

		} catch ( Exception $e ) {
			return new \WP_Error( 'save_error', 'Erro ao salvar configurações: ' . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * Add domain pricing tab to product data tabs
	 * 
	 * @since 1.0.0
	 * @param array $tabs Existing tabs
	 * @return array Modified tabs
	 */
	public function add_domain_pricing_tab( $tabs ) {
		// Aba 1: Configuração de Domínio
		$tabs['domain_config'] = array(
			'label'    => __( 'Configuração de Domínio', 'owh-domain-whois-rdap' ),
			'target'   => 'domain_config_options',
			'class'    => array( 'show_if_domain' ),
			'priority' => 80
		);
		
		// Aba 2: Valores do Domínio 
		$tabs['domain_pricing'] = array(
			'label'    => __( 'Valores do Domínio', 'owh-domain-whois-rdap' ),
			'target'   => 'domain_pricing_options', 
			'class'    => array( 'show_if_domain' ),
			'priority' => 81
		);
		
		// Aba 3: Documentos
		$tabs['domain_documents'] = array(
			'label'    => __( 'Documentos', 'owh-domain-whois-rdap' ),
			'target'   => 'domain_documents_options',
			'class'    => array( 'show_if_domain' ),
			'priority' => 82
		);
		
		return $tabs;
	}

	/**
	 * Add domain pricing panel to product data panels
	 * 
	 * @since 1.0.0
	 */
	public function add_domain_pricing_panel() {
		// Aba 1: Configuração de Domínio
		$this->render_domain_config_panel();
		
		// Aba 2: Valores do Domínio
		$this->render_domain_pricing_panel();
		
		// Aba 3: Documentos
		$this->render_domain_documents_panel();
	}

	/**
	 * Render Domain Configuration Panel (TLD e Registradora)
	 * 
	 * @since 1.0.0
	 */
	private function render_domain_config_panel() {
		global $post;
		?>
		<div id="domain_config_options" class="panel woocommerce_options_panel">
			<div class="options_group">
				<h3><?php esc_html_e( 'Configuração do Domínio', 'owh-domain-whois-rdap' ); ?></h3>
				<p><?php esc_html_e( 'Configure as informações básicas do tipo de domínio.', 'owh-domain-whois-rdap' ); ?></p>
				
				<?php
				// Get available TLDs from dns.json
				$tld_options = $this->get_available_tlds();
				
				// TLD Field - Select dropdown
				woocommerce_wp_select( array(
					'id'            => '_domain_tld',
					'label'         => __( 'TLD (Extensão)', 'owh-domain-whois-rdap' ),
					'description'   => __( 'Selecione a extensão de domínio disponível.', 'owh-domain-whois-rdap' ),
					'desc_tip'      => true,
					'value'         => get_post_meta( $post->ID, '_domain_tld', true ),
					'options'       => $tld_options
				) );

				// Registradora Field
				woocommerce_wp_select( array(
					'id'            => '_domain_registrar',
					'label'         => __( 'Registradora', 'owh-domain-whois-rdap' ),
					'description'   => __( 'Selecione a registradora responsável por este tipo de domínio.', 'owh-domain-whois-rdap' ),
					'desc_tip'      => true,
					'value'         => get_post_meta( $post->ID, '_domain_registrar', true ),
					'options'       => array(
						''                => __( 'Selecione...', 'owh-domain-whois-rdap' ),
						'manual'     => 'Manual',
					)
				) );

				// Custom Registrar Field (appears when "Outros" is selected)
				woocommerce_wp_text_input( array(
					'id'            => '_domain_registrar_custom',
					'label'         => __( 'Registradora Personalizada', 'owh-domain-whois-rdap' ),
					'description'   => __( 'Digite o nome da registradora (apenas quando "Outros" for selecionado).', 'owh-domain-whois-rdap' ),
					'desc_tip'      => true,
					'value'         => get_post_meta( $post->ID, '_domain_registrar_custom', true )
				) );
				?>
				
				<script>
				jQuery(document).ready(function($) {
					function toggleCustomRegistrar() {
						if ($('#_domain_registrar').val() === 'outros') {
							$('#_domain_registrar_custom').closest('p').show();
						} else {
							$('#_domain_registrar_custom').closest('p').hide();
						}
					}
					
					$('#_domain_registrar').on('change', toggleCustomRegistrar);
					toggleCustomRegistrar(); // Initial check
				});
				</script>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Domain Pricing Panel (Tabela de Valores)
	 * 
	 * @since 1.0.0
	 */
	private function render_domain_pricing_panel() {
		global $post;
		?>
		<div id="domain_pricing_options" class="panel woocommerce_options_panel">
			<div class="options_group">
				<h3><?php esc_html_e( 'Valores do Domínio - Matriz 3x10', 'owh-domain-whois-rdap' ); ?></h3>
				<p><?php esc_html_e( 'Configure os preços para registro, renovação e transferência por período de 1 a 10 anos.', 'owh-domain-whois-rdap' ); ?></p>

				<?php $this->render_pricing_matrix_table(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Domain Documents Panel (Opção de documentos obrigatórios)
	 * 
	 * @since 1.0.0
	 */
	private function render_domain_documents_panel() {
		global $post;
		?>
		<div id="domain_documents_options" class="panel woocommerce_options_panel">
			<div class="options_group">
				<h3><?php esc_html_e( 'Documentos Obrigatórios', 'owh-domain-whois-rdap' ); ?></h3>
				<p><?php esc_html_e( 'Configure quais documentos são obrigatórios para este tipo de domínio.', 'owh-domain-whois-rdap' ); ?></p>
				
				<?php
				// Require CPF/CNPJ checkbox
				woocommerce_wp_checkbox( array(
					'id'            => '_domain_require_tax_id',
					'label'         => __( 'Exigir CPF/CNPJ', 'owh-domain-whois-rdap' ),
					'description'   => __( 'Marque esta opção se este tipo de domínio exigir documento (CPF/CNPJ) do titular.', 'owh-domain-whois-rdap' ),
					'desc_tip'      => true,
					'value'         => get_post_meta( $post->ID, '_domain_require_tax_id', true )
				) );

				// Require RG checkbox
				woocommerce_wp_checkbox( array(
					'id'            => '_domain_require_rg',
					'label'         => __( 'Exigir RG', 'owh-domain-whois-rdap' ),
					'description'   => __( 'Marque esta opção se este tipo de domínio exigir RG do titular.', 'owh-domain-whois-rdap' ),
					'desc_tip'      => true,
					'value'         => get_post_meta( $post->ID, '_domain_require_rg', true )
				) );

				// Require Birth Certificate checkbox
				woocommerce_wp_checkbox( array(
					'id'            => '_domain_require_birth_certificate',
					'label'         => __( 'Exigir Certidão de Nascimento', 'owh-domain-whois-rdap' ),
					'description'   => __( 'Marque esta opção se este tipo de domínio exigir certidão de nascimento.', 'owh-domain-whois-rdap' ),
					'desc_tip'      => true,
					'value'         => get_post_meta( $post->ID, '_domain_require_birth_certificate', true )
				) );

				// Require Company Registration checkbox
				woocommerce_wp_checkbox( array(
					'id'            => '_domain_require_company_registration',
					'label'         => __( 'Exigir Contrato Social/CNPJ', 'owh-domain-whois-rdap' ),
					'description'   => __( 'Marque esta opção se este tipo de domínio exigir contrato social ou comprovante de CNPJ.', 'owh-domain-whois-rdap' ),
					'desc_tip'      => true,
					'value'         => get_post_meta( $post->ID, '_domain_require_company_registration', true )
				) );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render pricing matrix table for domain products
	 * Matriz de Preços 3x10 (3 Actions x 10 Years)
	 * 
	 * @since 1.0.0
	 */
	private function render_pricing_matrix_table() {
		global $post;
		
		echo '<h3>' . __( 'Configuração de Preços - Matriz 3x10', 'owh-domain-whois-rdap' ) . '</h3>';
		echo '<p>' . __( 'Configure os preços para registro, renovação e transferência por período de 1 a 10 anos.', 'owh-domain-whois-rdap' ) . '</p>';

		// Get current pricing matrix
		$pricing_matrix = get_post_meta( $post->ID, '_domain_pricing_matrix', true );
		if ( ! $pricing_matrix ) {
			$pricing_matrix = array();
		}

		// Actions
		$actions = array(
			'register' => __( 'Registro', 'owh-domain-whois-rdap' ),
			'renew'    => __( 'Renovação', 'owh-domain-whois-rdap' ),
			'transfer' => __( 'Transferência', 'owh-domain-whois-rdap' )
		);

		echo '<table class="domain-pricing-matrix-table" style="width: 100%; border-collapse: collapse;">';
		echo '<thead>';
		echo '<tr>';
		echo '<th style="border: 1px solid #ddd; padding: 8px;">' . __( 'Ação / Anos', 'owh-domain-whois-rdap' ) . '</th>';
		
		// Header with years 1-10
		for ( $year = 1; $year <= 10; $year++ ) {
			echo '<th style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . sprintf( __( '%d ano(s)', 'owh-domain-whois-rdap' ), $year ) . '</th>';
		}
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		// Rows for each action
		foreach ( $actions as $action_key => $action_label ) {
			echo '<tr>';
			echo '<td style="border: 1px solid #ddd; padding: 8px; font-weight: bold; background-color: #f9f9f9;">' . esc_html( $action_label ) . '</td>';
			
			// Columns for each year (1-10)
			for ( $year = 1; $year <= 10; $year++ ) {
				$field_name = "_domain_pricing_matrix[{$year}][{$action_key}]";
				$current_value = isset( $pricing_matrix[ $year ][ $action_key ] ) ? $pricing_matrix[ $year ][ $action_key ] : '';
				
				echo '<td style="border: 1px solid #ddd; padding: 4px;">';
				echo '<input type="text" name="' . esc_attr( $field_name ) . '" ';
				echo 'value="' . esc_attr( $current_value ) . '" ';
				echo 'placeholder="0.00" ';
				echo 'style="width: 100%; text-align: center;" ';
				echo 'pattern="[0-9]+(\.[0-9]{1,2})?" ';
				echo 'title="' . __( 'Digite o preço no formato: 00.00', 'owh-domain-whois-rdap' ) . '" />';
				echo '</td>';
			}
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Save domain pricing matrix fields
	 * Hook: woocommerce_process_product_meta_domain
	 * 
	 * @since 1.0.0
	 * @param int $post_id Product ID
	 */
	public function save_domain_pricing_matrix_fields( $post_id ) {
		// Security check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// === ABA 1: CONFIGURAÇÃO DE DOMÍNIO ===
		
		// Save TLD
		if ( isset( $_POST['_domain_tld'] ) ) {
			$tld = sanitize_text_field( $_POST['_domain_tld'] );
			update_post_meta( $post_id, '_domain_tld', $tld );
		}

		// Save Registrar
		if ( isset( $_POST['_domain_registrar'] ) ) {
			$registrar = sanitize_text_field( $_POST['_domain_registrar'] );
			update_post_meta( $post_id, '_domain_registrar', $registrar );
		}

		// Save Custom Registrar
		if ( isset( $_POST['_domain_registrar_custom'] ) ) {
			$registrar_custom = sanitize_text_field( $_POST['_domain_registrar_custom'] );
			update_post_meta( $post_id, '_domain_registrar_custom', $registrar_custom );
		}

		// === ABA 2: VALORES DO DOMÍNIO ===
		
		// Save pricing matrix
		if ( isset( $_POST['_domain_pricing_matrix'] ) ) {
			$pricing_matrix = array();
			
			// Sanitize and validate pricing data
			foreach ( $_POST['_domain_pricing_matrix'] as $year => $actions ) {
				$year = intval( $year );
				if ( $year >= 1 && $year <= 10 ) {
					foreach ( $actions as $action => $price ) {
						if ( in_array( $action, array( 'register', 'renew', 'transfer' ) ) ) {
							$price = sanitize_text_field( $price );
							// Validate price format
							if ( $price !== '' && is_numeric( $price ) && floatval( $price ) >= 0 ) {
								$pricing_matrix[ $year ][ $action ] = number_format( floatval( $price ), 2, '.', '' );
							}
						}
					}
				}
			}
			
			update_post_meta( $post_id, '_domain_pricing_matrix', $pricing_matrix );
		}

		// === ABA 3: DOCUMENTOS OBRIGATÓRIOS ===
		
		// Save Tax ID requirement
		$require_tax_id = isset( $_POST['_domain_require_tax_id'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_domain_require_tax_id', $require_tax_id );

		// Save RG requirement
		$require_rg = isset( $_POST['_domain_require_rg'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_domain_require_rg', $require_rg );

		// Save Birth Certificate requirement
		$require_birth_certificate = isset( $_POST['_domain_require_birth_certificate'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_domain_require_birth_certificate', $require_birth_certificate );

		// Save Company Registration requirement
		$require_company_registration = isset( $_POST['_domain_require_company_registration'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_domain_require_company_registration', $require_company_registration );

		// === AUTOMAÇÃO E COMPATIBILIDADE ===

		// AUTOMAÇÃO LKN INVOICE PAYMENT
		// Força este produto a ser uma assinatura anual recorrente
		update_post_meta( $post_id, '_lkn-wcip-subscription-product', 'yes' ); 
		update_post_meta( $post_id, 'lkn_wcip_subscription_interval_number', '1' );
		update_post_meta( $post_id, 'lkn_wcip_subscription_interval_type', 'year' );
		update_post_meta( $post_id, 'lkn_wcip_subscription_limit', '0' ); // 0 = Infinito

		// Set base price from 1 year register price (for display purposes)
		if ( isset( $pricing_matrix[1]['register'] ) && ! empty( $pricing_matrix[1]['register'] ) ) {
			update_post_meta( $post_id, '_regular_price', $pricing_matrix[1]['register'] );
			update_post_meta( $post_id, '_price', $pricing_matrix[1]['register'] );
		}
	}

	/**
	 * Domain Configuration Tab Content
	 */
	public function domain_config_tab_content() {
		global $post;
		
		$tld = get_post_meta( $post->ID, '_domain_tld', true );
		$registrar = get_post_meta( $post->ID, '_domain_registrar', true );
		
		// Get available TLDs from DNS data
		$dns_file = plugin_dir_path( dirname( __FILE__ ) ) . 'data/dns.json';
		$tlds = array();
		
		if ( file_exists( $dns_file ) ) {
			$dns_data = json_decode( file_get_contents( $dns_file ), true );
			if ( isset( $dns_data['services'] ) ) {
				foreach ( $dns_data['services'] as $service ) {
					if ( isset( $service[0] ) && is_array( $service[0] ) ) {
						foreach ( $service[0] as $tld_entry ) {
							if ( is_string( $tld_entry ) ) {
								$tlds[] = $tld_entry;
							}
						}
					}
				}
			}
		}
		
		// Remove duplicates and sort
		$tlds = array_unique( $tlds );
		sort( $tlds );
		
		?>
		<div id="domain_config_product_data" class="panel woocommerce_options_panel">
			<div class="options_group">
				<p class="form-field">
					<label for="domain_tld"><?php esc_html_e( 'TLD (Extensão)', 'owh-domain-whois-rdap' ); ?></label>
					<select name="domain_tld" id="domain_tld" class="select short">
						<option value=""><?php esc_html_e( 'Selecione uma extensão', 'owh-domain-whois-rdap' ); ?></option>
						<?php foreach ( $tlds as $tld_option ) : ?>
							<option value="<?php echo esc_attr( $tld_option ); ?>" <?php selected( $tld, $tld_option ); ?>>
								.<?php echo esc_html( ltrim( $tld_option, '.' ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="description"><?php esc_html_e( 'Selecione a extensão de domínio (TLD) para este produto.', 'owh-domain-whois-rdap' ); ?></span>
				</p>

				<p class="form-field">
					<label for="domain_registrar"><?php esc_html_e( 'Registradora', 'owh-domain-whois-rdap' ); ?></label>
					<select name="domain_registrar" id="domain_registrar" class="select short">
						<option value="manual" <?php selected( $registrar, 'manual' ); ?>>
							<?php esc_html_e( 'Manual', 'owh-domain-whois-rdap' ); ?>
						</option>
					</select>
					<span class="description"><?php esc_html_e( 'Por enquanto apenas registro manual está disponível.', 'owh-domain-whois-rdap' ); ?></span>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Domain Pricing Tab Content
	 */
	public function domain_pricing_tab_content() {
		global $post;
		
		$pricing_matrix = get_post_meta( $post->ID, '_domain_pricing_matrix', true );
		if ( ! is_array( $pricing_matrix ) ) {
			$pricing_matrix = array();
		}
		
		?>
		<div id="domain_pricing_product_data" class="panel woocommerce_options_panel">
			<div class="options_group">
				<h3><?php esc_html_e( 'Tabela de Preços por Período', 'owh-domain-whois-rdap' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Configure os preços para registro, renovação e transferência por período (anos).', 'owh-domain-whois-rdap' ); ?></p>
				
				<table class="widefat domain-pricing-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Período', 'owh-domain-whois-rdap' ); ?></th>
							<th><?php esc_html_e( 'Registro (R$)', 'owh-domain-whois-rdap' ); ?></th>
							<th><?php esc_html_e( 'Renovação (R$)', 'owh-domain-whois-rdap' ); ?></th>
							<th><?php esc_html_e( 'Transferência (R$)', 'owh-domain-whois-rdap' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php for ( $i = 1; $i <= 10; $i++ ) : ?>
							<tr>
								<td><strong><?php echo $i; ?> <?php echo $i === 1 ? 'ano' : 'anos'; ?></strong></td>
								<td>
									<input type="number" 
										   name="domain_pricing[<?php echo $i; ?>][register]" 
										   value="<?php echo esc_attr( $pricing_matrix[$i]['register'] ?? '' ); ?>"
										   step="0.01" 
										   min="0" 
										   class="small-text" />
								</td>
								<td>
									<input type="number" 
										   name="domain_pricing[<?php echo $i; ?>][renew]" 
										   value="<?php echo esc_attr( $pricing_matrix[$i]['renew'] ?? '' ); ?>"
										   step="0.01" 
										   min="0" 
										   class="small-text" />
								</td>
								<td>
									<input type="number" 
										   name="domain_pricing[<?php echo $i; ?>][transfer]" 
										   value="<?php echo esc_attr( $pricing_matrix[$i]['transfer'] ?? '' ); ?>"
										   step="0.01" 
										   min="0" 
										   class="small-text" />
								</td>
							</tr>
						<?php endfor; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Domain Documents Tab Content
	 */
	public function domain_documents_tab_content() {
		global $post;
		
		$required_documents = get_post_meta( $post->ID, '_domain_required_documents', true );
		if ( ! is_array( $required_documents ) ) {
			$required_documents = array();
		}
		
		?>
		<div id="domain_documents_product_data" class="panel woocommerce_options_panel">
			<div class="options_group">
				<h3><?php esc_html_e( 'Documentos Obrigatórios', 'owh-domain-whois-rdap' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Configure quais documentos são obrigatórios para este tipo de domínio.', 'owh-domain-whois-rdap' ); ?></p>
				
				<div class="domain-documents-list">
					<?php
					$available_documents = array(
						'cpf' => 'CPF',
						'cnpj' => 'CNPJ',
						'rg' => 'RG',
						'passport' => 'Passaporte',
						'company_registration' => 'Registro da Empresa',
						'trademark' => 'Marca Registrada',
						'authorization' => 'Autorização Legal',
						'identity_proof' => 'Comprovante de Identidade',
						'address_proof' => 'Comprovante de Endereço'
					);
					
					foreach ( $available_documents as $doc_key => $doc_label ) :
					?>
						<p class="form-field">
							<label>
								<input type="checkbox" 
									   name="domain_required_documents[]" 
									   value="<?php echo esc_attr( $doc_key ); ?>"
									   <?php checked( in_array( $doc_key, $required_documents ) ); ?> />
								<?php echo esc_html( $doc_label ); ?>
							</label>
						</p>
					<?php endforeach; ?>
				</div>
				
				<p class="form-field">
					<label for="domain_documents_note"><?php esc_html_e( 'Observações sobre Documentos', 'owh-domain-whois-rdap' ); ?></label>
					<textarea name="domain_documents_note" 
							  id="domain_documents_note" 
							  rows="3" 
							  class="large-text"
							  placeholder="<?php esc_attr_e( 'Instruções adicionais sobre os documentos necessários...', 'owh-domain-whois-rdap' ); ?>"><?php echo esc_textarea( get_post_meta( $post->ID, '_domain_documents_note', true ) ); ?></textarea>
					<span class="description"><?php esc_html_e( 'Instruções adicionais que serão mostradas ao cliente sobre os documentos.', 'owh-domain-whois-rdap' ); ?></span>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save domain product custom fields
	 */
	public function save_domain_product_fields( $post_id ) {
		$product = wc_get_product( $post_id );
		if ( ! $product || $product->get_type() !== 'domain' ) {
			return;
		}

		// Save domain configuration
		if ( isset( $_POST['domain_tld'] ) ) {
			update_post_meta( $post_id, '_domain_tld', sanitize_text_field( $_POST['domain_tld'] ) );
		}
		
		if ( isset( $_POST['domain_registrar'] ) ) {
			update_post_meta( $post_id, '_domain_registrar', sanitize_text_field( $_POST['domain_registrar'] ) );
		}

		// Save pricing matrix
		if ( isset( $_POST['domain_pricing'] ) && is_array( $_POST['domain_pricing'] ) ) {
			$pricing_matrix = array();
			foreach ( $_POST['domain_pricing'] as $period => $prices ) {
				$period = intval( $period );
				if ( $period >= 1 && $period <= 10 ) {
					$pricing_matrix[$period] = array(
						'register' => floatval( $prices['register'] ?? 0 ),
						'renew' => floatval( $prices['renew'] ?? 0 ),
						'transfer' => floatval( $prices['transfer'] ?? 0 )
					);
				}
			}
			update_post_meta( $post_id, '_domain_pricing_matrix', $pricing_matrix );
		}

		// Save required documents
		$required_documents = array();
		if ( isset( $_POST['domain_required_documents'] ) && is_array( $_POST['domain_required_documents'] ) ) {
			foreach ( $_POST['domain_required_documents'] as $doc ) {
				$required_documents[] = sanitize_text_field( $doc );
			}
		}
		update_post_meta( $post_id, '_domain_required_documents', $required_documents );
		
		if ( isset( $_POST['domain_documents_note'] ) ) {
			update_post_meta( $post_id, '_domain_documents_note', sanitize_textarea_field( $_POST['domain_documents_note'] ) );
		}
	}

	/**
	 * Get available TLDs from dns.json file
	 * 
	 * @since 1.0.0
	 * @return array Array of TLD options for select field
	 */
	private function get_available_tlds() {
		$tld_options = array( '' => __( 'Selecione uma extensão...', 'owh-domain-whois-rdap' ) );
		
		// Path to dns.json file
		$dns_file = plugin_dir_path( dirname( __FILE__ ) ) . 'data/dns.json';
		
		if ( ! file_exists( $dns_file ) ) {
			return $tld_options;
		}
		
		// Read and decode JSON
		$dns_content = file_get_contents( $dns_file );
		$dns_data = json_decode( $dns_content, true );
		
		if ( ! $dns_data || ! isset( $dns_data['services'] ) ) {
			return $tld_options;
		}
		
		$tlds = array();
		
		// Extract TLDs from services array
		foreach ( $dns_data['services'] as $service ) {
			if ( isset( $service[0] ) && is_array( $service[0] ) ) {
				foreach ( $service[0] as $tld ) {
					if ( is_string( $tld ) && ! empty( $tld ) ) {
						// Ensure TLD has leading dot
						$formatted_tld = ( strpos( $tld, '.' ) === 0 ) ? $tld : '.' . $tld;
						$tlds[ $formatted_tld ] = $formatted_tld;
					}
				}
			}
		}
		
		// Sort TLDs alphabetically
		ksort( $tlds );
		
		// Merge with default option
		$tld_options = array_merge( $tld_options, $tlds );
		
		return $tld_options;
	}
}
