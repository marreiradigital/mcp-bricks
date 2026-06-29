<?php
/**
 * Rotina de desinstalacao: remove options e transients do plugin.
 *
 * @package Marreira\MCP_Bricks
 */

// Executado pelo WordPress apenas durante a desinstalacao.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$mmb_options = array(
	'mmb_settings',
	'mmb_version',
	'mmb_token_hash',
	'mmb_token_meta',
);

foreach ( $mmb_options as $mmb_option ) {
	delete_option( $mmb_option );
}

// Limpa transients de rate limit remanescentes.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_mmb_rl_%' OR option_name LIKE '_transient_timeout_mmb_rl_%' OR option_name LIKE '_transient_mmb_flash_token' OR option_name LIKE '_transient_timeout_mmb_flash_token'"
);
