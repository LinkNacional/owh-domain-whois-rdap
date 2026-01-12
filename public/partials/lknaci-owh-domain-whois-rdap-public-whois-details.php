<?php

/**
 * Provide a public-facing view for WHOIS details
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

// Generate dynamic CSS styles
$container_styles = array();

// Apply CSS styling attributes
if ( isset( $border_width ) && isset( $border_color ) ) {
	$container_styles[] = 'border: ' . $border_width . 'px solid ' . $border_color;
}
if ( isset( $border_radius ) ) {
	$container_styles[] = 'border-radius: ' . $border_radius . 'px';
}
if ( isset( $background_color ) ) {
	$container_styles[] = 'background-color: ' . $background_color;
}
if ( isset( $padding ) ) {
	$container_styles[] = 'padding: ' . $padding . 'px';
}

$inline_styles = '';
if ( ! empty( $container_styles ) ) {
	$inline_styles = ' style="' . implode( '; ', $container_styles ) . '"';
}

// Add custom CSS if provided
$custom_css_output = '';
if ( isset( $custom_css ) && ! empty( trim( $custom_css ) ) ) {
	// Clean and validate CSS - remove any malicious content
	$clean_css = strip_tags( $custom_css );
	$clean_css = str_replace( array( '<script', '</script>', 'javascript:' ), '', $clean_css );
	
	// Apply with higher specificity to override plugin styles
	$custom_css_output = '<style>
	.owh-rdap-whois-details-container { ' . esc_attr( $clean_css ) . ' }
	.owh-rdap-whois-details-container * { ' . esc_attr( $clean_css ) . ' }
	.owh-rdap-whois-details-container h1, 
	.owh-rdap-whois-details-container h2, 
	.owh-rdap-whois-details-container h3, 
	.owh-rdap-whois-details-container h4, 
	.owh-rdap-whois-details-container h5, 
	.owh-rdap-whois-details-container h6 { ' . esc_attr( $clean_css ) . ' }
	.owh-rdap-whois-details-container p, 
	.owh-rdap-whois-details-container span, 
	.owh-rdap-whois-details-container div { ' . esc_attr( $clean_css ) . ' }
	</style>';
}

?>

<?php if ( ! empty( $custom_css_output ) ) : ?>
	<?php echo $custom_css_output; ?>
<?php endif; ?>

<div class="owh-rdap-whois-details-container"<?php echo $inline_styles; ?>>
	<?php if ( isset( $show_title ) && $show_title ) : ?>
		<div class="owh-rdap-whois-details-header">
			<h3><?php echo esc_html( isset( $custom_title ) ? $custom_title : __( 'Detalhes WHOIS/RDAP', 'lknaci-owh-domain-whois-rdap' ) ); ?></h3>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $domain ) && $result ) : ?>
		<?php if ( $result->hasError() ) : ?>
			<div class="owh-rdap-result-error">
				<div class="owh-rdap-error-icon">⚠️</div>
				<div class="owh-rdap-error-content">
					<h4><?php echo esc_html( isset( $error_text ) ? $error_text : __( 'Erro na Pesquisa', 'lknaci-owh-domain-whois-rdap' ) ); ?></h4>
					<p><?php echo esc_html( $result->getError() ); ?></p>
				</div>
			</div>
		<?php elseif ( $result->isAvailable() ) : ?>
			<div class="owh-rdap-whois-available">
				<div class="owh-rdap-available-icon">✅</div>
				<div class="owh-rdap-available-content">
					<h4><?php printf( __( 'Domínio %s', 'lknaci-owh-domain-whois-rdap' ), esc_html( $domain ) ); ?></h4>
					<p><?php echo esc_html( isset( $available_text ) ? $available_text : __( 'Este domínio está disponível para registro e não possui informações WHOIS.', 'lknaci-owh-domain-whois-rdap' ) ); ?></p>
				</div>
			</div>
		<?php else : ?>
			<div class="owh-rdap-whois-registered">
				<div class="owh-rdap-domain-header">
					<h4><?php printf( __( 'Detalhes WHOIS para %s', 'lknaci-owh-domain-whois-rdap' ), '<strong>' . esc_html( $domain ) . '</strong>' ); ?></h4>
				</div>

				<?php 
				$rdap_data = $result->getRdapData();
				if ( $rdap_data ) : ?>
					
					<?php if ( isset( $show_events ) && $show_events && isset( $rdap_data['events'] ) && is_array( $rdap_data['events'] ) ) : ?>
					<div class="owh-rdap-domain-events">
						<h5><?php echo esc_html( isset( $events_title ) ? $events_title : __( 'Histórico de Eventos', 'lknaci-owh-domain-whois-rdap' ) ); ?></h5>
						<div class="owh-rdap-events-list">
							<?php 
							// Mapeamento de traduções para eventos RDAP
							$event_translations = array(
								'expiration' => 'Expiração',
								'registration' => 'Registro',
								'last changed' => 'Última Alteração',
								'last update of rdap database' => 'Última Atualização da Base RDAP',
								'reregistration' => 'Renovação',
								'transfer' => 'Transferência',
								'locked' => 'Bloqueado',
								'unlocked' => 'Desbloqueado',
							);
							
							foreach ( $rdap_data['events'] as $event ) : ?>
								<?php if ( isset( $event['eventAction'] ) && isset( $event['eventDate'] ) ) : ?>
								<?php 
									$event_key = strtolower( str_replace( '_', ' ', $event['eventAction'] ) );
									$event_label = isset( $event_translations[$event_key] ) ? $event_translations[$event_key] : ucfirst( str_replace( '_', ' ', $event['eventAction'] ) );
								?>
								<div class="owh-rdap-event-item">
									<h6><?php echo esc_html( $event_label ); ?></h6>
									<div class="owh-rdap-event-info">
										<p><strong>Data:</strong> <?php echo esc_html( date_i18n( 'd/m/Y H:i:s', strtotime( $event['eventDate'] ) ) ); ?></p>
									</div>
								</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( isset( $show_entities ) && $show_entities && isset( $rdap_data['entities'] ) && is_array( $rdap_data['entities'] ) ) : ?>
					<div class="owh-rdap-domain-entities">
						<h5><?php echo esc_html( isset( $entities_title ) ? $entities_title : __( 'Entidades Relacionadas', 'lknaci-owh-domain-whois-rdap' ) ); ?></h5>
						<div class="owh-rdap-entities-list">
							<?php foreach ( $rdap_data['entities'] as $entity ) : ?>
								<?php if ( isset( $entity['roles'] ) && is_array( $entity['roles'] ) ) : ?>
								<div class="owh-rdap-entity-item">
									<h6>
										<?php 
										$roles = array_map( function( $role ) {
											$role_translations = array(
												'registrant' => 'Registrante',
												'administrative' => 'Administrativo',
												'technical' => 'Técnico',
												'billing' => 'Financeiro',
												'registrar' => 'Registrador',
												'reseller' => 'Revendedor',
												'sponsor' => 'Patrocinador',
												'proxy' => 'Proxy',
												'notifications' => 'Notificações',
												'noc' => 'NOC',
												'abuse' => 'Abuso'
											);
											return isset( $role_translations[$role] ) ? $role_translations[$role] : ucfirst( $role );
										}, $entity['roles'] );
										echo esc_html( implode( ', ', $roles ) );
										?>
									</h6>
									
									<?php if ( isset( $entity['vcardArray'] ) && is_array( $entity['vcardArray'] ) ) : ?>
									<div class="owh-rdap-vcard-info">
										<?php 
										// Process vCard data
										foreach ( $entity['vcardArray'] as $vcard_item ) {
											if ( is_array( $vcard_item ) ) {
												foreach ( $vcard_item as $property ) {
													if ( is_array( $property ) && count( $property ) >= 4 ) {
														$property_name = $property[0];
														$property_value = isset( $property[3] ) ? $property[3] : '';
														
														if ( ! empty( $property_value ) && is_string( $property_value ) ) {
															$label_translations = array(
																'fn' => 'Nome',
																'org' => 'Organização',
																'adr' => 'Endereço',
																'tel' => 'Telefone',
																'email' => 'Email',
																'url' => 'Website'
															);
															
															$label = isset( $label_translations[$property_name] ) ? $label_translations[$property_name] : ucfirst( $property_name );
															?>
															<p><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $property_value ); ?></p>
															<?php
														}
													}
												}
											}
										}
										?>
									</div>
									<?php endif; ?>
								</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( isset( $show_nameservers ) && $show_nameservers && isset( $rdap_data['nameservers'] ) && is_array( $rdap_data['nameservers'] ) ) : ?>
					<div class="owh-rdap-nameservers">
						<h5><?php echo esc_html( isset( $nameservers_title ) ? $nameservers_title : __( 'Servidores DNS (Nameservers)', 'lknaci-owh-domain-whois-rdap' ) ); ?></h5>
						<div class="owh-rdap-nameserver-list">
							<ul>
								<?php foreach ( $rdap_data['nameservers'] as $ns ) : ?>
									<?php if ( isset( $ns['ldhName'] ) ) : ?>
									<li><?php echo esc_html( $ns['ldhName'] ); ?></li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( isset( $show_status ) && $show_status && isset( $rdap_data['status'] ) && is_array( $rdap_data['status'] ) ) : ?>
					<div class="owh-rdap-domain-status">
						<h5><?php echo esc_html( isset( $status_title ) ? $status_title : __( 'Status do Domínio', 'lknaci-owh-domain-whois-rdap' ) ); ?></h5>
						<div class="owh-rdap-status-list">
							<ul>
								<?php foreach ( $rdap_data['status'] as $status ) : ?>
									<li><?php echo esc_html( $status ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( isset( $show_dnssec ) && $show_dnssec && isset( $rdap_data['secureDNS'] ) ) : ?>
					<div class="owh-rdap-secure-dns">
						<h5><?php echo esc_html( isset( $dnssec_title ) ? $dnssec_title : __( 'DNSSEC', 'lknaci-owh-domain-whois-rdap' ) ); ?></h5>
						<p>
							<strong><?php _e( 'Status:', 'lknaci-owh-domain-whois-rdap' ); ?></strong>
							<?php 
							$secure_dns_status = isset( $rdap_data['secureDNS']['delegationSigned'] ) && $rdap_data['secureDNS']['delegationSigned'] 
								? __( 'Habilitado', 'lknaci-owh-domain-whois-rdap' )
								: __( 'Desabilitado', 'lknaci-owh-domain-whois-rdap' );
							echo esc_html( $secure_dns_status );
							?>
						</p>
					</div>
					<?php endif; ?>

				<?php else : ?>
					<div class="owh-rdap-no-data">
						<p><?php _e( 'Não foi possível obter informações detalhadas WHOIS/RDAP para este domínio.', 'lknaci-owh-domain-whois-rdap' ); ?></p>
					</div>
				<?php endif; ?>

			</div>
		<?php endif; ?>
	<?php elseif ( ! empty( $domain ) ) : ?>
		<div class="owh-rdap-domain-error">
			<div class="owh-rdap-error-icon">⚠️</div>
			<div class="owh-rdap-error-content">
				<h4><?php echo esc_html( isset( $error_text ) ? $error_text : __( 'Erro na Pesquisa', 'lknaci-owh-domain-whois-rdap' ) ); ?></h4>
				<p><?php _e( 'Não foi possível buscar informações para o domínio informado.', 'lknaci-owh-domain-whois-rdap' ); ?></p>
			</div>
		</div>
	<?php else : ?>
		<div class="owh-rdap-no-domain">
			<div style="text-align: center;">
				<?php if ( isset( $show_icon ) && $show_icon ) : ?>
					<div style="font-size: 48px; margin-bottom: 15px;"><?php echo esc_html( isset( $custom_icon ) ? $custom_icon : '📋' ); ?></div>
				<?php endif; ?>
				<h4><?php echo esc_html( isset( $no_domain_text ) ? $no_domain_text : __( 'Nenhum Domínio Informado', 'lknaci-owh-domain-whois-rdap' ) ); ?></h4>
				<p><?php echo esc_html( isset( $no_domain_description ) ? $no_domain_description : __( 'Para visualizar os detalhes WHOIS, acesse esta página através do link "Ver detalhes completos" nos resultados da pesquisa.', 'lknaci-owh-domain-whois-rdap' ) ); ?></p>
			</div>
		</div>
	<?php endif; ?>
</div>
