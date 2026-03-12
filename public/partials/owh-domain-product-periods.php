<?php
/**
 * Domain Product Search Form
 * Template for displaying domain search form on single domain product page
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure this is a domain product
if ( ! $product || $product->get_type() !== 'domain' ) {
    return;
}

// Render the domain search shortcode
echo do_shortcode( '[owh-rdap-whois-search show_title="true" custom_title="Pesquisar Disponibilidade de Domínio" show_examples="true"]' );
?>
