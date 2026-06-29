<?php
/**
 * Plugin Name:       MarreiraMCP Bricks
 * Plugin URI:        https://marreiradigital.com.br/marreira-mcp-bricks
 * Description:        Servidor MCP (Model Context Protocol) para criar e editar paginas e templates do Bricks Builder de forma nativa via IA, com seguranca por token e endpoints ocultos do indice publico do REST.
 * Version:           0.4.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Paulo Marreira
 * Author URI:        https://marreiradigital.com.br
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       marreira-mcp-bricks
 * Domain Path:       /languages
 *
 * @package Marreira\MCP_Bricks
 */

// Bloqueia acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Constantes do plugin.
// ---------------------------------------------------------------------------
define( 'MMB_VERSION', '0.4.0' );
define( 'MMB_PLUGIN_FILE', __FILE__ );
define( 'MMB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MMB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MMB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Namespace e rota do endpoint MCP (rota oculta do indice publico).
define( 'MMB_REST_NAMESPACE', 'marreira-mcp/v1' );
define( 'MMB_REST_ROUTE', '/mcp' );

// Prefixo das options no banco.
define( 'MMB_OPTION_PREFIX', 'mmb_' );

// Versao do protocolo MCP suportada.
define( 'MMB_MCP_PROTOCOL_VERSION', '2025-03-26' );

// ---------------------------------------------------------------------------
// Autoloader simples (PSR-ish) mapeando o namespace para /includes.
// Marreira\MCP_Bricks\Auth\Token_Manager => includes/auth/class-token-manager.php
// ---------------------------------------------------------------------------
spl_autoload_register(
	static function ( $class ) {
		$prefix = 'Marreira\\MCP_Bricks\\';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$parts    = explode( '\\', $relative );
		$class_nm = array_pop( $parts );

		// Sub-namespaces viram diretorios em minusculo.
		$sub_path = '';
		if ( ! empty( $parts ) ) {
			$sub_path = strtolower( implode( '/', $parts ) ) . '/';
		}

		// Foo_Bar => class-foo-bar.php
		$file_name = 'class-' . strtolower( str_replace( '_', '-', $class_nm ) ) . '.php';
		$file      = MMB_PLUGIN_DIR . 'includes/' . $sub_path . $file_name;

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

// ---------------------------------------------------------------------------
// Hooks de ciclo de vida.
// ---------------------------------------------------------------------------
register_activation_hook( __FILE__, array( '\Marreira\MCP_Bricks\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\Marreira\MCP_Bricks\Activator', 'deactivate' ) );

// ---------------------------------------------------------------------------
// Bootstrap.
// ---------------------------------------------------------------------------
add_action(
	'plugins_loaded',
	static function () {
		\Marreira\MCP_Bricks\Plugin::instance()->boot();
	}
);
