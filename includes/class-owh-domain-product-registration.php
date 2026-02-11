<?php

defined( 'ABSPATH' ) || exit;

/**
 * Domain Product Registration
 * 
 * Handles registration of the Domain product type in WooCommerce
 */
class Owh_Domain_Product_Registration {

    /**
     * Register the domain product type
     */
    public static function register_domain_product_type() {
        // Ensure WooCommerce is loaded
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        // Load the WC_Product_Domain class
        if ( ! class_exists( 'WC_Product_Domain' ) ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-product-domain.php';
        }
    }

    /**
     * Add domain product type to the product type selector
     * 
     * @param array $types Existing product types
     * @return array Modified product types
     */
    public static function add_domain_product_type( $types ) {
        $types['domain'] = __( 'Domain', 'owh-domain-whois-rdap' );
        return $types;
    }

    /**
     * Get the correct product class for domain products
     * 
     * @param string $classname Current class name
     * @param string $product_type Product type
     * @return string Product class name
     */
    public static function get_domain_product_class( $classname, $product_type ) {
        if ( $product_type === 'domain' ) {
            return 'WC_Product_Domain';
        }
        return $classname;
    }
}
