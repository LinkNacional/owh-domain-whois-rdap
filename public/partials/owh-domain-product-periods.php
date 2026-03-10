<?php
/**
 * Domain Product Period Selection Form
 * Template for displaying period selection on single domain product page
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure this is a domain product
if ( ! $product || $product->get_type() !== 'domain' ) {
    return;
}

$available_periods = $product->get_available_periods();

if ( empty( $available_periods ) ) {
    return;
}
?>

<div class="domain-product-form-container" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
    
    <?php if ( $product->has_multiple_periods() ) : ?>
        
        <div class="domain-period-selection">
            <label for="domain_period_selector" class="domain-period-label">
                Período de Registro:
            </label>
            
            <select name="domain_period" id="domain_period_selector" class="domain-period-selector" required>
                <option value="">Selecione o período...</option>
                <?php foreach ( $available_periods as $period => $label ) : ?>
                    <?php 
                    $price = $product->get_price_for_period( $period, 'register' );
                    $selected = $period === 1 ? 'selected' : ''; // Default to 1 year
                    ?>
                    <option value="<?php echo esc_attr( $period ); ?>" <?php echo $selected; ?>>
                        <?php echo esc_html( $label ); ?>
                        <?php if ( $price ) : ?>
                            - <?php echo wc_price( $price ); ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="domain-period-price-display">
            <span class="domain-period-price">
                <?php 
                // Show initial price for selected period (1 year default)
                $initial_price = $product->get_price_for_period( 1, 'register' );
                if ( $initial_price ) {
                    echo wc_price( $initial_price );
                }
                ?>
            </span>
        </div>
        
        
    <?php else : ?>
        
        <!-- Single period available -->
        <?php 
        $single_period = array_keys( $available_periods )[0];
        $single_price = $product->get_price_for_period( $single_period, 'register' );
        ?>
        <input type="hidden" name="domain_period" value="<?php echo esc_attr( $single_period ); ?>" />
        <input type="hidden" name="domain_price" value="<?php echo esc_attr( $single_price ); ?>" />
        
        <div class="domain-single-period">
            <span class="period-text">
                <?php echo esc_html( $available_periods[ $single_period ] ); ?>
            </span>
            <span class="period-price">
                <?php echo wc_price( $single_price ); ?>
            </span>
        </div>
        
    <?php endif; ?>
    
    <!-- Additional domain-specific fields -->
    <div class="domain-additional-fields">
        <!-- Domain name input (if needed for custom search) -->
        <input type="text" name="domain_name" class="domain-name-input" placeholder="Digite o domínio..." style="display: none;" />
        
        <!-- Hidden fields for cart processing -->
        <input type="hidden" name="domain_action" value="register" />
        <input type="hidden" name="is_domain_product" value="1" />
        <input type="hidden" name="domain_price" id="hidden_domain_price" value="" />
    </div>
    
</div>

<style>
.domain-product-form-container {
    margin: 20px 0;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}

.domain-period-selection {
    margin-bottom: 15px;
}

.domain-period-label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.domain-period-selector {
    width: 100%;
    max-width: 300px;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
    font-size: 14px;
}

.domain-period-price-display {
    margin-top: 10px;
    padding: 10px;
    background: white;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
}

.domain-period-price {
    font-size: 18px;
    font-weight: bold;
    color: #2c5aa0;
}

.domain-period-price.loading::after {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ccc;
    border-top: 2px solid #2c5aa0;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 5px;
}

.domain-single-period {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
}

.period-text {
    font-weight: bold;
    color: #333;
}

.period-price {
    font-size: 18px;
    font-weight: bold;
    color: #2c5aa0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .domain-period-selector {
        max-width: 100%;
    }
    
    .domain-single-period {
        flex-direction: column;
        text-align: center;
    }
    
    .period-price {
        margin-top: 8px;
    }
}
</style>
