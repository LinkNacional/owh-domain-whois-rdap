<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://owh.digital
 * @since      1.0.0
 *
 * @package    Lknaci_Owh_Domain_Whois_Rdap
 * @subpackage Lknaci_Owh_Domain_Whois_Rdap/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Lknaci_Owh_Domain_Whois_Rdap
 * @subpackage Lknaci_Owh_Domain_Whois_Rdap/admin
 * @author     OWH Group <dev@owhgroup.com.br>
 */
class Lknaci_Owh_Domain_Whois_Rdap_Admin {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lknaci-owh-domain-whois-rdap-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lknaci-owh-domain-whois-rdap-admin.js', array( 'jquery' ), $this->version, false );
		
		// Localize script for AJAX
		wp_localize_script( $this->plugin_name, 'lknaci_owh_rdap_admin_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'lknaci_admin_nonce' )
		) );

		// Carregar script de layout na página de configurações do plugin
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'owh-rdap' ) {
			wp_enqueue_script( 
				$this->plugin_name . '-layout', 
				plugin_dir_url( __FILE__ ) . 'js/lknaci-owh-domain-whois-rdap-admin-layout.js', 
				array( 'jquery' ), 
				$this->version . '-' . time(), // Force refresh
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
			'lknaci-owh-rdap-blocks',
			plugin_dir_url( __FILE__ ) . 'js/lknaci-owh-domain-whois-rdap-blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render' ),
			$this->version
		);

		// Register blocks
		register_block_type( 'owh-rdap/domain-search', array(
			'editor_script' => 'lknaci-owh-rdap-blocks',
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
			'editor_script' => 'lknaci-owh-rdap-blocks',
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
				)
			)
		) );

		register_block_type( 'owh-rdap/whois-details', array(
			'editor_script' => 'lknaci-owh-rdap-blocks',
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
		wp_set_script_translations( 'lknaci-owh-rdap-blocks', 'lknaci-owh-domain-whois-rdap' );
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
			__( 'OWH', 'lknaci-owh-domain-whois-rdap' ),
			__( 'OWH', 'lknaci-owh-domain-whois-rdap' ),
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
		include_once( 'partials/lknaci-owh-domain-whois-rdap-admin-settings.php' );
	}

	/**
	 * Register settings
	 *
	 * @since    1.0.0
	 */
	public function register_plugin_settings() {
		// Register settings
		register_setting( 'owh_rdap_settings', 'owh_rdap_enable_search' );
		register_setting( 'owh_rdap_settings', 'owh_rdap_results_page' );
		register_setting( 'owh_rdap_settings', 'owh_rdap_whois_details_page' );
		register_setting( 'owh_rdap_settings', 'owh_rdap_integration_type' );
		register_setting( 'owh_rdap_settings', 'owh_rdap_custom_url' );
		register_setting( 'owh_rdap_settings', 'owh_rdap_whmcs_url' );
		
		// Main settings section
		add_settings_section(
			'owh_rdap_main_settings',
			__( 'Ferramenta de Pesquisa de Domínios', 'lknaci-owh-domain-whois-rdap' ),
			array( $this, 'main_settings_callback' ),
			'owh_rdap_settings'
		);

		// Enable/Disable search
		add_settings_field(
			'owh_rdap_enable_search',
			__( 'Ativar Pesquisa de Domínios (RDAP/WHOIS)', 'lknaci-owh-domain-whois-rdap' ),
			array( $this, 'enable_search_callback' ),
			'owh_rdap_settings',
			'owh_rdap_main_settings'
		);

		// Results page
		add_settings_field(
			'owh_rdap_results_page',
			__( 'Página de Resultados da Pesquisa', 'lknaci-owh-domain-whois-rdap' ),
			array( $this, 'results_page_callback' ),
			'owh_rdap_settings',
			'owh_rdap_main_settings'
		);

		// WHOIS details page
		add_settings_field(
			'owh_rdap_whois_details_page',
			__( 'Página de Detalhes WHOIS', 'lknaci-owh-domain-whois-rdap' ),
			array( $this, 'whois_details_page_callback' ),
			'owh_rdap_settings',
			'owh_rdap_main_settings'
		);

		// Integration section
		add_settings_section(
			'owh_rdap_integration_settings',
			__( '', 'lknaci-owh-domain-whois-rdap' ),
			array(  ),
			'owh_rdap_settings'
		);

		// Integration type
		add_settings_field(
			'owh_rdap_integration_type',
			__( 'Tipo de Integração', 'lknaci-owh-domain-whois-rdap' ),
			array( $this, 'integration_type_callback' ),
			'owh_rdap_settings',
			'owh_rdap_integration_settings'
		);

		// Custom URL
		add_settings_field(
			'owh_rdap_custom_url',
			__( 'Custom URL', 'lknaci-owh-domain-whois-rdap' ),
			array( $this, 'custom_url_callback' ),
			'owh_rdap_settings',
			'owh_rdap_integration_settings'
		);

		// WHMCS URL
		add_settings_field(
			'owh_rdap_whmcs_url',
			__( 'WHMCS', 'lknaci-owh-domain-whois-rdap' ),
			array( $this, 'whmcs_url_callback' ),
			'owh_rdap_settings',
			'owh_rdap_integration_settings'
		);
	}

	/**
	 * Main settings section callback
	 */
	public function main_settings_callback() {
		echo '<p>' . __( 'Ative a ferramenta de pesquisa de domínios (Whois) no seu site.', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Enable search field callback
	 */
	public function enable_search_callback() {
		$value = get_option( 'owh_rdap_enable_search', false );
		echo '<fieldset>';
		echo '<label><input type="radio" name="owh_rdap_enable_search" value="1"' . checked( 1, $value, false ) . '> ' . __( 'Ativar', 'lknaci-owh-domain-whois-rdap' ) . '</label><br>';
		echo '<label><input type="radio" name="owh_rdap_enable_search" value="0"' . checked( 0, $value, false ) . '> ' . __( 'Desativar', 'lknaci-owh-domain-whois-rdap' ) . '</label>';
		echo '</fieldset>';
		echo '<p class="description">' . __( 'Ao ativar este recurso, você poderá inserir o formulário de pesquisa em qualquer página ou post através de shortcodes e blocos do WordPress.', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Results page field callback
	 */
	public function results_page_callback() {
		$value = get_option( 'owh_rdap_results_page', '' );
		
		wp_dropdown_pages( array(
			'name'             => 'owh_rdap_results_page',
			'selected'         => $value,
			'show_option_none' => __( 'Página Resultado Pesquisa domínios', 'lknaci-owh-domain-whois-rdap' ),
			'option_none_value' => '',
		) );
		
		echo '<p class="description">' . __( 'Escolha a página que mostrará os resultados. Para funcionar, você precisa copiar e colar o shortcode [owh-rdap-whois-results] no conteúdo desta página (no editor de texto ou em um bloco de shortcode).', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * WHOIS details page field callback
	 */
	public function whois_details_page_callback() {
		$value = get_option( 'owh_rdap_whois_details_page', '' );
		
		wp_dropdown_pages( array(
			'name'             => 'owh_rdap_whois_details_page',
			'selected'         => $value,
			'show_option_none' => __( 'Selecione a página de detalhes WHOIS', 'lknaci-owh-domain-whois-rdap' ),
			'option_none_value' => '',
		) );
		
		echo '<p class="description">' . __( 'Escolha a página que mostrará os detalhes WHOIS completos dos domínios registrados. Para funcionar, você precisa copiar e colar o shortcode [owh-rdap-whois-details] no conteúdo desta página.', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
	}

	/**
	 * Integration type field callback
	 */
	public function integration_type_callback() {
		$value = get_option( 'owh_rdap_integration_type', 'custom' );
		echo '<select name="owh_rdap_integration_type" id="owh_rdap_integration_type">';
		echo '<option value="custom"' . selected( 'custom', $value, false ) . '>' . __( 'Custom URL', 'lknaci-owh-domain-whois-rdap' ) . '</option>';
		echo '<option value="whmcs"' . selected( 'whmcs', $value, false ) . '>' . __( 'WHMCS', 'lknaci-owh-domain-whois-rdap' ) . '</option>';
		echo '</select>';
		echo '<p class="description">' . __( 'Selecione o seu sistema de vendas de domínios para criar a integração. Após selecionar e savar siga com as configurações da integração.', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		?>
		<script>
		jQuery(document).ready(function($) {
			function toggleIntegrationFields() {
				var type = $('#owh_rdap_integration_type').val();
				if (type === 'custom') {
					console.log($('#whmcs_url_section').parent().parent())
					$('#custom_url_section').parent().parent().show();
					$('#whmcs_url_section').parent().parent().hide();
					$('#custom_url_section').show();
					$('#whmcs_url_section').hide();
				} else if (type === 'whmcs') {
					$('#custom_url_section').parent().parent().hide();
					$('#whmcs_url_section').parent().parent().show();
					$('#custom_url_section').hide();
					$('#whmcs_url_section').show();
				}
			}
			
			$('#owh_rdap_integration_type').change(toggleIntegrationFields);
			toggleIntegrationFields();
		});
		</script>
		<?php
	}

	/**
	 * Custom URL field callback
	 */
	public function custom_url_callback() {
		$integration_type = get_option( 'owh_rdap_integration_type', 'custom' );
		$value = get_option( 'owh_rdap_custom_url', '' );
		
		$style = $integration_type === 'custom' ? '' : 'style="display: none;"';
		
		echo '<div id="custom_url_section" ' . $style . '>';
		echo '<p>' . __( 'Configurar os parâmetros da URL para seguir com o registro do domínio', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		echo '<p>' . __( 'Configure um URL personalizado para registrar domínios', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		echo '<input type="url" name="owh_rdap_custom_url" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://cliente.linknacional.com.br" />';
		echo '<p class="description">' . __( 'Tags de template disponíveis: {domain}, {sld}, {tld} exemplo: https://who.linknacional.com/whois/?domain={domain}', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		echo '</div>';
	}

	/**
	 * WHMCS URL field callback
	 */
	public function whmcs_url_callback() {
		$integration_type = get_option( 'owh_rdap_integration_type', 'custom' );
		$value = get_option( 'owh_rdap_whmcs_url', '' );
		
		$style = $integration_type === 'whmcs' ? '' : 'style="display: none;"';
		
		echo '<div id="whmcs_url_section" ' . $style . '>';
		echo '<p>' . __( 'Configurar os parâmetros da URL para seguir com o registro do domínio no WHMCS', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		echo '<p>' . __( 'Configure o URL do WHMCS para o registro de domínios.', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		echo '<input type="url" name="owh_rdap_whmcs_url" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://cliente.linknacional.com.br" />';
		echo '<p class="description">' . __( 'Informe o URL de instalação do seu WHMCS', 'lknaci-owh-domain-whois-rdap' ) . '</p>';
		echo '</div>';
	}

}
