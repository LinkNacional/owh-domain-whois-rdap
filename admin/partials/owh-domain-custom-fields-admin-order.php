<?php
/**
 * Admin template for displaying custom domain fields in order view
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    OWH_Domain_WHOIS_RDAP
 * @subpackage OWH_Domain_WHOIS_RDAP/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Available variables:
 * @var array $custom_fields - Array of custom field data
 * @var array $field_map - Map of field configurations
 * @var string $section_title - Title for the section
 */
?>

<div class="owh-domain-custom-fields-section">
    <h3><?php echo esc_html( $section_title ); ?></h3>
    
    <?php if ( ! empty( $custom_fields ) ) : ?>
        <table class="widefat fixed striped">
            <tbody>
                <?php foreach ( $custom_fields as $field_id => $field_data ) : ?>
                    <?php
                    // Get the label from field configuration
                    $label = __( 'Campo Personalizado', 'owh-domain-whois-rdap' ); // Default label
                    if ( isset( $field_map[ $field_id ] ) && ! empty( $field_map[ $field_id ]['label'] ) ) {
                        $label = $field_map[ $field_id ]['label'];
                    }
                    
                    $value = isset( $field_data['value'] ) ? $field_data['value'] : '';
                    ?>
                    
                    <?php if ( ! empty( $value ) ) : ?>
                        <tr>
                            <td class="field-label"><?php echo esc_html( $label ); ?></td>
                            <td class="field-value"><?php echo esc_html( $value ); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="no-fields-message">
            <?php esc_html_e( 'Nenhum campo personalizado encontrado para este pedido.', 'owh-domain-whois-rdap' ); ?>
        </div>
    <?php endif; ?>
</div>
