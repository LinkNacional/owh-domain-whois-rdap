<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://owhgroup.com.br
 * @since             1.0.0
 * @package           OWH_Domain_WHOIS_RDAP
 *
 * @wordpress-plugin
 * Plugin Name:       OWH Domain WHOIS RDAP
 * Plugin URI:        https://github.com/owhgroup/owh-domain-whois-rdap
 * Description:       Verificação de disponibilidade de domínios via protocolo RDAP.
 * Version:           1.0.0
 * Author:            OWH Group
 * Author URI:        https://owhgroup.com.br
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lknaci-owh-domain-whois-rdap
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LKNACI_OWH_DOMAIN_WHOIS_RDAP_VERSION', '1.0.0' );

/**
 * Plugin path and URL
 */
define( 'LKNACI_OWH_DOMAIN_WHOIS_RDAP_PATH', plugin_dir_path( __FILE__ ) );
define( 'LKNACI_OWH_DOMAIN_WHOIS_RDAP_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lknaci-owh-domain-whois-rdap-activator.php
 */
function lknaci_activate_owh_domain_whois_rdap() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lknaci-owh-domain-whois-rdap-activator.php';
	Lknaci_Owh_Domain_Whois_Rdap_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lknaci-owh-domain-whois-rdap-deactivator.php
 */
function lknaci_deactivate_owh_domain_whois_rdap() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lknaci-owh-domain-whois-rdap-deactivator.php';
	Lknaci_Owh_Domain_Whois_Rdap_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'lknaci_activate_owh_domain_whois_rdap' );
register_deactivation_hook( __FILE__, 'lknaci_deactivate_owh_domain_whois_rdap' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-lknaci-owh-domain-whois-rdap.php';

/**
 * Autoloader para as classes PSR-4 (src/)
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'OwhDomainWhoisRdap\\';
	$base_dir = __DIR__ . '/src/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function lknaci_run_owh_domain_whois_rdap() {
	$plugin = new Lknaci_Owh_Domain_Whois_Rdap();
	$plugin->run();
}
lknaci_run_owh_domain_whois_rdap();
