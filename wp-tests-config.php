<?php
/**
 * Configuração do banco de dados de teste para PHPUnit.
 *
 * Banco local_tests criado via Adminer no Local WP,
 * evitando erros de socket do terminal (isolamento Linux).
 *
 * @package OWH_Domain_WHOIS_RDAP
 */

/* ---- Database ---- */
define( 'DB_NAME', 'local_tests' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

/* ---- WordPress Test Suite ---- */
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'OWH Domain WHOIS RDAP Tests' );
define( 'WP_PHP_BINARY', 'php' );

/* ---- Absolute path to WordPress core ---- */
define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress/' );

/* ---- Table prefix para testes ---- */
$table_prefix = 'wptests_';

/* ---- Debug nos testes ---- */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Evita requests externos nos testes */
define( 'WP_HTTP_BLOCK_EXTERNAL', false );
define( 'WP_TESTS_SKIP_INSTALL', false );
