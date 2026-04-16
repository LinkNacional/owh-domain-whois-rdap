<?php

defined( 'ABSPATH' ) || exit;

/**
 * Domain Product Class
 * 
 * Extends WooCommerce Product to create a Domain product type with period selection
 * Following WordPress Coding Standards for includes/ layer
 */
class WC_Product_Domain extends WC_Product {

    /**
     * Get the product type
     * 
     * @return string
     */
    public function get_type() {
        return 'domain';
    }

    /**
     * Get available domain periods (1-10 years)
     * 
     * @return array Available periods
     */
    public function get_available_periods() {
        $periods = array();
        $pricing_matrix = $this->get_pricing_matrix();
        
        for ( $i = 1; $i <= 10; $i++ ) {
            if ( ! empty( $pricing_matrix[ $i ]['register'] ) && $pricing_matrix[ $i ]['register'] > 0 ) {
                /* translators: %d: number of years */
                $periods[ $i ] = sprintf( _n( '%d ano', '%d anos', $i, 'owh-domain-whois-rdap' ), $i );
            }
        }
        
        return $periods;
    }

    /**
     * Check if product has multiple pricing options
     * 
     * @return bool
     */
    public function has_multiple_periods() {
        return count( $this->get_available_periods() ) > 1;
    }

    /**
     * Get price for specific period and action
     * 
     * @param int $period Period in years (1-10)
     * @param string $action Action type (register, renew, transfer)
     * @return float|null Price or null if not available
     */
    public function get_price_for_period( $period = 1, $action = 'register' ) {
        $pricing_matrix = $this->get_pricing_matrix();
        
        if ( isset( $pricing_matrix[ $period ][ $action ] ) && $pricing_matrix[ $period ][ $action ] > 0 ) {
            return floatval( $pricing_matrix[ $period ][ $action ] );
        }
        
        return null;
    }

    /**
     * Get minimum price for display purposes
     * 
     * @return float Minimum registration price
     */
    public function get_min_price() {
        $pricing_matrix = $this->get_pricing_matrix();
        $min_price = null;
        
        for ( $i = 1; $i <= 10; $i++ ) {
            if ( ! empty( $pricing_matrix[ $i ]['register'] ) && $pricing_matrix[ $i ]['register'] > 0 ) {
                $price = floatval( $pricing_matrix[ $i ]['register'] );
                if ( $min_price === null || $price < $min_price ) {
                    $min_price = $price;
                }
            }
        }
        
        return $min_price ?: 0;
    }

    /**
     * Get maximum price for display purposes
     * 
     * @return float Maximum registration price
     */
    public function get_max_price() {
        $pricing_matrix = $this->get_pricing_matrix();
        $max_price = 0;
        
        for ( $i = 1; $i <= 10; $i++ ) {
            if ( ! empty( $pricing_matrix[ $i ]['register'] ) && $pricing_matrix[ $i ]['register'] > 0 ) {
                $price = floatval( $pricing_matrix[ $i ]['register'] );
                if ( $price > $max_price ) {
                    $max_price = $price;
                }
            }
        }
        
        return $max_price;
    }

    /**
     * Get price range text for display
     * 
     * @return string Price range text
     */
    public function get_price_html( $price = '' ) {
        if ( $this->has_multiple_periods() ) {
            $min = $this->get_min_price();
            $max = $this->get_max_price();
            
            if ( $min === $max ) {
                return wc_price( $min );
            } else {
                return sprintf( 
                    /* translators: %1$s: minimum price, %2$s: maximum price */
                    esc_attr__( '%1$s &ndash; %2$s', 'woocommerce' ),
                    wc_price( $min ),
                    wc_price( $max )
                );
            }
        } else {
            return parent::get_price_html( $price );
        }
    }

    /**
     * Check if domain product needs selection form
     * 
     * @return bool
     */
    public function needs_selection_form() {
        return $this->has_multiple_periods();
    }

    /**
     * Get the product price
     * Returns the base price for display purposes (1 year register price)
     * 
     * @param string $context View or edit context
     * @return string
     */
    public function get_price( $context = 'view' ) {
        // Try to get the 1-year registration price from the pricing matrix
        $pricing_matrix = $this->get_meta( '_domain_pricing_matrix', true, $context );
        
        if ( is_array( $pricing_matrix ) && isset( $pricing_matrix[1]['register'] ) ) {
            return $pricing_matrix[1]['register'];
        }
        
        // Fallback to regular price
        return parent::get_price( $context );
    }

    /**
     * Get pricing for a specific action and period
     * 
     * @param string $action Action type (register, renew, transfer)
     * @param int $years Number of years
     * @return float|null Price or null if not set
     */
    public function get_domain_price( $action, $years = 1 ) {
        $pricing_matrix = $this->get_meta( '_domain_pricing_matrix', true );
        
        if ( is_array( $pricing_matrix ) && isset( $pricing_matrix[ $years ][ $action ] ) ) {
            return floatval( $pricing_matrix[ $years ][ $action ] );
        }
        
        return null;
    }

    /**
     * Get the complete pricing matrix
     * 
     * @return array Pricing matrix
     */
    public function get_pricing_matrix() {
        $pricing_matrix = $this->get_meta( '_domain_pricing_matrix', true );
        return is_array( $pricing_matrix ) ? $pricing_matrix : array();
    }

    /**
     * Check if this domain product requires Tax ID (CPF/CNPJ)
     * 
     * @return bool
     */
    public function requires_tax_id() {
        return $this->get_meta( '_owh_require_tax_id', true ) === 'yes';
    }

    /**
     * Get the registrar slug for this domain product
     * 
     * @return string
     */
    public function get_registrar_slug() {
        return $this->get_meta( '_owh_registrar_slug', true, 'view' ) ?: 'manual';
    }

    /**
     * Check if product is virtual
     * Domain products are always virtual
     * 
     * @return bool
     */
    public function is_virtual( $context = 'view' ) {
        return true;
    }

    /**
     * Check if product is downloadable
     * Domain products are never downloadable
     * 
     * @return bool
     */
    public function is_downloadable( $context = 'view' ) {
        return false;
    }

    /**
     * Check if product needs shipping
     * Domain products never need shipping
     * 
     * @return bool
     */
    public function needs_shipping() {
        return false;
    }

    /**
     * Check if product is sold individually
     * Domain products are sold individually by default
     * 
     * @return bool
     */
    public function is_sold_individually( $context = 'view' ) {
        return true;
    }

    /**
     * Check if product supports a feature
     * 
     * @param string $feature Feature to check
     * @return bool
     */
    public function supports( $feature ) {
        $features = array(
            'ajax_add_to_cart' => false,
        );

        return isset( $features[ $feature ] ) ? $features[ $feature ] : parent::supports( $feature );
    }

    /**
     * Check if product is purchasable
     * 
     * @return bool
     */
    public function is_purchasable() {
        $purchasable = true;

        // Product must be published
        if ( $this->get_status() !== 'publish' ) {
            $purchasable = false;
        }

        // Must have pricing configured
        if ( empty( $this->get_available_periods() ) ) {
            $purchasable = false;
        }

        return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
    }

    /**
     * Check if product is in stock
     * Domain products are always in stock
     * 
     * @return bool
     */
    public function is_in_stock() {
        return true;
    }

    /**
     * Check if product manages stock
     * Domain products don't manage stock
     * 
     * @return bool
     */
    public function managing_stock() {
        return false;
    }

    /**
     * Get stock status
     * Domain products are always in stock
     * 
     * @param string $context View or edit context
     * @return string
     */
    public function get_stock_status( $context = 'view' ) {
        return 'instock';
    }

    /**
     * Get stock quantity
     * Domain products have unlimited stock
     * 
     * @param string $context View or edit context
     * @return int|null
     */
    public function get_stock_quantity( $context = 'view' ) {
        return null;
    }

    /**
     * Check if product has enough stock for given quantity
     * Domain products always have enough stock
     * 
     * @param int $quantity Quantity to check
     * @return bool
     */
    public function has_enough_stock( $quantity ) {
        return true;
    }

    /**
     * Get formatted name
     * 
     * @return string
     */
    public function get_formatted_name() {
        return sprintf( '%s', $this->get_title() );
    }

    /**
     * Get single add to cart text
     * 
     * @return string
     */
    public function single_add_to_cart_text() {
        return esc_attr__( 'Adicionar ao Carrinho', 'owh-domain-whois-rdap' );
    }

    /**
     * Get add to cart text
     * 
     * @return string
     */
    public function add_to_cart_text() {
        return $this->single_add_to_cart_text();
    }

    /**
     * Get add to cart URL
     * 
     * @return string
     */
    public function add_to_cart_url() {
        return apply_filters( 'woocommerce_product_add_to_cart_url', $this->get_permalink(), $this );
    }
}
