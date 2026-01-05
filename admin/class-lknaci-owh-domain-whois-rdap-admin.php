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
				'showExamples' => array(
					'type' => 'boolean',
					'default' => true
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
		
		// Use the existing shortcode with parameters
		$shortcode_atts = '';
		$shortcode_atts .= $show_title ? 'show_title="true"' : 'show_title="false"';
		$shortcode_atts .= ' ';
		$shortcode_atts .= $show_examples ? 'show_examples="true"' : 'show_examples="false"';
		
		return do_shortcode( '[owh-rdap-whois-search ' . $shortcode_atts . ']' );
	}

	/**
	 * Render results block
	 *
	 * @since    1.0.0
	 */
	public function render_results_block( $attributes ) {
		$show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
		
		// Use the existing shortcode with parameters
		$shortcode_atts = $show_title ? 'show_title="true"' : 'show_title="false"';
		return do_shortcode( '[owh-rdap-whois-results ' . $shortcode_atts . ']' );
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
