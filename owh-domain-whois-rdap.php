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
 * Version:           1.2.0
 * Author:            OWH Group
 * Author URI:        https://owhgroup.com.br
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       owh-domain-whois-rdap
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
define( 'OWH_DOMAIN_WHOIS_RDAP_VERSION', '1.2.0' );

/**
 * Plugin path and URL
 */
define( 'OWH_DOMAIN_WHOIS_RDAP_PATH', plugin_dir_path( __FILE__ ) );
define( 'OWH_DOMAIN_WHOIS_RDAP_URL', plugin_dir_url( __FILE__ ) );

/**
 * Composer Autoloader
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-owh-domain-whois-rdap-activator.php
 */
function owh_activate_domain_whois_rdap() {
	Owh_Domain_Whois_Rdap_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-owh-domain-whois-rdap-deactivator.php
 */
function owh_deactivate_domain_whois_rdap() {
	Owh_Domain_Whois_Rdap_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'owh_activate_domain_whois_rdap' );
register_deactivation_hook( __FILE__, 'owh_deactivate_domain_whois_rdap' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function owh_run_domain_whois_rdap() {
	$plugin = new Owh_Domain_Whois_Rdap();
	$plugin->run();
}
owh_run_domain_whois_rdap();
