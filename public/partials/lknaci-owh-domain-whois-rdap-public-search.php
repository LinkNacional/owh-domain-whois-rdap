<?php

/**
 * Provide a public-facing view for the domain search form
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://owhgroup.com.br
 * @since      1.0.0
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/public/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set default values for custom attributes
$defaults = array(
	'custom_title' => __( 'Pesquisar Domínio', 'lknaci-owh-domain-whois-rdap' ),
	'placeholder_text' => __( 'Digite o nome do domínio...', 'lknaci-owh-domain-whois-rdap' ),
	'search_button_text' => __( 'Pesquisar', 'lknaci-owh-domain-whois-rdap' ),
	'examples_text' => __( 'Exemplos:', 'lknaci-owh-domain-whois-rdap' ),
	'example1' => 'exemplo.com',
	'example2' => 'meusite.com.br',
	'example3' => 'minhaempresa.org'
);

// Merge custom attributes with defaults
if ( isset( $custom_attributes ) && is_array( $custom_attributes ) ) {
	foreach ( $defaults as $key => $default_value ) {
		if ( isset( $custom_attributes[ $key ] ) && $custom_attributes[ $key ] !== '' ) {
			$$key = $custom_attributes[ $key ];
		} else {
			$$key = $default_value;
		}
	}
} else {
	foreach ( $defaults as $key => $default_value ) {
		$$key = $default_value;
	}
}

// Generate custom styles
$container_styles = array();
if ( isset( $custom_attributes['border_width'] ) && is_numeric( $custom_attributes['border_width'] ) ) {
	$border_color = isset( $custom_attributes['border_color'] ) ? $custom_attributes['border_color'] : '#ddd';
	$container_styles[] = 'border: ' . intval( $custom_attributes['border_width'] ) . 'px solid ' . $border_color . ';';
}
if ( isset( $custom_attributes['border_radius'] ) && is_numeric( $custom_attributes['border_radius'] ) ) {
	$container_styles[] = 'border-radius: ' . intval( $custom_attributes['border_radius'] ) . 'px;';
}
if ( isset( $custom_attributes['background_color'] ) && $custom_attributes['background_color'] !== '' ) {
	$container_styles[] = 'background-color: ' . esc_attr( $custom_attributes['background_color'] ) . ';';
}
if ( isset( $custom_attributes['padding'] ) && is_numeric( $custom_attributes['padding'] ) ) {
	$container_styles[] = 'padding: ' . intval( $custom_attributes['padding'] ) . 'px;';
}

$container_style_attr = ! empty( $container_styles ) ? ' style="' . implode( ' ', $container_styles ) . '"' : '';

// Generate dynamic CSS for colors and layout
$dynamic_css = '';
$button_layout = isset( $custom_attributes['button_layout'] ) ? $custom_attributes['button_layout'] : 'external';

if ( isset( $custom_attributes ) ) {
	$primary_color = ! empty( $custom_attributes['primary_color'] ) ? $custom_attributes['primary_color'] : '#0073aa';
	$button_hover_color = ! empty( $custom_attributes['button_hover_color'] ) ? $custom_attributes['button_hover_color'] : '#005a87';
	$input_border_color = ! empty( $custom_attributes['input_border_color'] ) ? $custom_attributes['input_border_color'] : '#ddd';
	$input_focus_color = ! empty( $custom_attributes['input_focus_color'] ) ? $custom_attributes['input_focus_color'] : '#0073aa';
	
	$dynamic_css = "
		.owh-rdap-search-button { 
			background: {$primary_color} !important; 
		}
		.owh-rdap-search-button:hover { 
			background: {$button_hover_color} !important; 
		}
		.owh-rdap-domain-input { 
			border-color: {$input_border_color} !important; 
		}
		.owh-rdap-domain-input:focus { 
			border-color: {$input_focus_color} !important; 
			box-shadow: 0 0 0 1px {$input_focus_color} !important;
		}
		.owh-rdap-example-domain { 
			color: {$primary_color} !important; 
		}
		.owh-rdap-example-domain:hover { 
			color: {$button_hover_color} !important; 
		}
	";
	
	// Add layout-specific CSS
	if ( $button_layout === 'internal' ) {
		$dynamic_css .= "
			.owh-rdap-search-input-wrapper {
				position: relative !important;
				display: block !important;
			}
			.owh-rdap-domain-input {
				padding-right: 130px !important;
				width: 100% !important;
				box-sizing: border-box !important;
			}
			.owh-rdap-search-button {
				position: absolute !important;
				right: 6px !important;
				top: 6px !important;
				width: 110px !important;
				bottom: 6px !important;
				transform: none !important;
				padding: 0 12px !important;
				font-size: 14px !important;
				border-radius: 4px !important;
				min-width: 110px !important;
				height: auto !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
			}
		";
	} else {
		$dynamic_css .= "
			.owh-rdap-search-input-wrapper {
				display: flex !important;
				gap: 12px !important;
				align-items: center !important;
			}
			.owh-rdap-domain-input {
				flex: 1 !important;
			}
			.owh-rdap-search-button {
				flex-shrink: 0 !important;
				min-width: 120px !important;
			}
		";
	}
}

?>

<?php if ( isset( $custom_attributes['custom_css'] ) && ! empty( $custom_attributes['custom_css'] ) ) : ?>
<style>
	.owh-rdap-search-container { <?php echo esc_html( $custom_attributes['custom_css'] ); ?> }
</style>
<?php endif; ?>

<?php if ( ! empty( $dynamic_css ) ) : ?>
<style>
	<?php echo $dynamic_css; ?>
</style>
<?php endif; ?>

<div class="owh-rdap-search-container"<?php echo $container_style_attr; ?>>
	<?php if ( isset( $show_title ) && $show_title ) : ?>
		<h3 class="owh-rdap-search-title"><?php echo esc_html( $custom_title ); ?></h3>
	<?php endif; ?>
	
	<form id="owh-rdap-search-form" class="owh-rdap-search-form" method="<?php echo $results_page ? 'get' : 'post'; ?>" <?php echo $results_page ? 'action="' . get_permalink( $results_page ) . '"' : ''; ?>>
		<div class="owh-rdap-search-wrapper">
			<div class="owh-rdap-search-input-wrapper">
				<input 
					type="text" 
					id="owh-rdap-domain-input" 
					name="domain" 
					class="owh-rdap-domain-input" 
					placeholder="<?php echo esc_attr( $placeholder_text ); ?>" 
					value="<?php echo isset( $_GET['domain'] ) ? esc_attr( sanitize_text_field( $_GET['domain'] ) ) : ''; ?>"
					required 
				/>
				<button type="submit" id="owh-rdap-search-button" class="owh-rdap-search-button">
					<span class="owh-rdap-search-text"><?php echo esc_html( $search_button_text ); ?></span>
					<span class="owh-rdap-search-loading" style="display: none;">
						<span class="owh-rdap-spinner"></span>
					</span>
				</button>
			</div>

			<?php if ( isset( $show_examples ) && $show_examples ) : ?>
			<div class="owh-rdap-search-examples">
				<small class="owh-rdap-examples-text">
					<?php echo esc_html( $examples_text ); ?> 
					<span class="owh-rdap-example-domain" data-domain="<?php echo esc_attr( $example1 ); ?>"><?php echo esc_html( $example1 ); ?></span>, 
					<span class="owh-rdap-example-domain" data-domain="<?php echo esc_attr( $example2 ); ?>"><?php echo esc_html( $example2 ); ?></span>, 
					<span class="owh-rdap-example-domain" data-domain="<?php echo esc_attr( $example3 ); ?>"><?php echo esc_html( $example3 ); ?></span>
				</small>
			</div>
			<?php endif; ?>
		</div>

		<?php if ( ! $results_page ) : ?>
		<?php wp_nonce_field( 'lknaci_owh_rdap_public_nonce', 'lknaci_owh_rdap_public_nonce' ); ?>
		<?php endif; ?>
	</form>

</div>

	<?php if ( ! $results_page ) : ?>
	<div id="owh-rdap-search-results" class="owh-rdap-search-results" style="display: none;"></div>
	<?php endif; ?>

<style>
.owh-rdap-search-container {
	max-width: 600px;
	margin: 0 auto;
	padding: 20px;
}

.owh-rdap-search-wrapper {
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	padding: 20px;
}

.owh-rdap-search-input-wrapper {
	display: flex;
	gap: 10px;
	margin-bottom: 15px;
}

.owh-rdap-domain-input {
	flex: 1;
	padding: 12px 16px;
	border: 2px solid #ddd;
	border-radius: 6px;
	font-size: 16px;
	transition: border-color 0.3s ease;
}

.owh-rdap-domain-input:focus {
	outline: none;
	border-color: #0073aa;
	box-shadow: 0 0 0 1px #0073aa;
}

.owh-rdap-search-button {
	padding: 12px 24px;
	background: #0073aa;
	color: white;
	border: none;
	border-radius: 6px;
	font-size: 16px;
	font-weight: 600;
	cursor: pointer;
	transition: background-color 0.3s ease;
	min-width: 120px;
}

.owh-rdap-search-button:hover {
	background: #005a87;
}

.owh-rdap-search-button:disabled {
	background: #ccc;
	cursor: not-allowed;
}

.owh-rdap-search-loading {
	display: flex;
	align-items: center;
	gap: 8px;
}

.owh-rdap-spinner {
	width: 16px;
	height: 16px;
	border: 2px solid rgba(255, 255, 255, 0.3);
	border-top: 2px solid white;
	border-radius: 50%;
	animation: owh-rdap-spin 1s linear infinite;
}

@keyframes owh-rdap-spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

.owh-rdap-search-examples {
	text-align: center;
	margin-top: 15px;
}

.owh-rdap-examples-text {
	color: #666;
	font-size: 14px;
}

.owh-rdap-example-domain {
	color: #0073aa;
	cursor: pointer;
	text-decoration: underline;
}

.owh-rdap-example-domain:hover {
	color: #005a87;
}

.owh-rdap-search-results {
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	padding: 20px;
	margin-top: 20px;
}

.owh-rdap-result-available {
	color: #46b450;
	font-size: 18px;
	font-weight: 600;
	margin-bottom: 15px;
}

.owh-rdap-result-unavailable {
	color: #dc3232;
	font-size: 18px;
	font-weight: 600;
	margin-bottom: 15px;
}

.owh-rdap-result-error {
	color: #dc3232;
	font-size: 16px;
	margin-bottom: 15px;
}

.owh-rdap-buy-button {
	display: inline-block;
	padding: 12px 24px;
	background: #46b450;
	color: white !important;
	text-decoration: none;
	border-radius: 6px;
	font-weight: 600;
	transition: background-color 0.3s ease;
}

.owh-rdap-buy-button:hover {
	background: #3ba943;
	color: white !important;
}

.owh-rdap-buy-button .dashicons {
	margin-right: 5px;
	vertical-align: middle;
}

@media (max-width: 600px) {
	.owh-rdap-search-input-wrapper {
		flex-direction: column;
	}
	
	.owh-rdap-search-button {
		width: 100%;
	}
}
</style>
