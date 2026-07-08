<?php
/**
 * Bootstrap dos testes de integração.
 *
 * Regra de Ouro: WooCommerce (vizinho do Local WP) é carregado
 * ANTES do nosso plugin no hook muplugins_loaded.
 *
 * @package OWH_Domain_WHOIS_RDAP
 */

// Composer autoloader
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Aponta para o wp-tests-config.php na raiz do plugin
define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );

/*
 * Pré-carrega functions.php do wp-phpunit para que `tests_add_filter`
 * esteja disponível ANTES do WordPress inicializar.
 *
 * Isso permite registrar callbacks em muplugins_loaded antes do bootstrap
 */
require_once dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/includes/functions.php';

/**
 * Carrega o WooCommerce vizinho do Local WP no hook muplugins_loaded.
 *
 * A estrutura de diretórios esperada no Local WP:
 *   wp-content/plugins/
 *     owh-domain-whois-rdap/   ← este plugin (PROJECT ROOT)
 *     woocommerce/             ← plugin WooCommerce vizinho
 *
 * dirname(__DIR__, 2) a partir de tests/bootstrap.php:
 *   nível 1 → owh-domain-whois-rdap/
 *   nível 2 → plugins/
 *
 * NÃO instalar WooCommerce via Composer (wpackagist) —
 * isso geraria pastas ocultas e causaria "Cannot declare class".
 */
tests_add_filter( 'muplugins_loaded', function () {
	$wc_main_file = dirname( __DIR__, 2 ) . '/woocommerce/woocommerce.php';

	if ( file_exists( $wc_main_file ) ) {
		require $wc_main_file;
	}
} );

/*
 * Inicializa o WordPress e a infraestrutura de testes (PHPUnit).
 */
require_once dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';

/**
 * Carrega nosso plugin DEPOIS que WooCommerce já está carregado.
 */
tests_add_filter( 'plugins_loaded', function () {
	require dirname( __DIR__ ) . '/owh-domain-whois-rdap.php';
} );
