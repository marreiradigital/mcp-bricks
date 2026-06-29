<?php
/**
 * Code_Guard: recusa elementos/settings que executam codigo (anti-RCE).
 *
 * @package Marreira\MCP_Bricks
 */

namespace Marreira\MCP_Bricks\Bricks;

use Marreira\MCP_Bricks\Auth\Rest_Guard;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Varre arvores de elementos e page settings em busca de vetores de execucao
 * de codigo arbitrario. Quando o bloqueio esta ativo (padrao), qualquer
 * ocorrencia faz a operacao falhar com erro 403.
 *
 * Vetores cobertos:
 *  - Elemento "code" (PHP/JS/HTML executavel) e "svg" com codigo embutido.
 *  - Settings com codigo: executeCode, code, javascriptCode, phpCode, signature.
 *  - Page settings com scripts (customScripts*) ou customCss perigoso.
 *  - Tags dinamicas perigosas: {echo:...} e {do_action:...} em strings.
 */
class Code_Guard {

	/**
	 * Nomes de elementos proibidos quando o bloqueio esta ativo.
	 *
	 * @var string[]
	 */
	const FORBIDDEN_ELEMENTS = array( 'code' );

	/**
	 * Chaves de settings que indicam codigo executavel.
	 *
	 * @var string[]
	 */
	const FORBIDDEN_SETTING_KEYS = array(
		'executeCode',
		'code',
		'javascriptCode',
		'phpCode',
		'signature',
	);

	/**
	 * Chaves de page settings proibidas (injecao de script).
	 *
	 * @var string[]
	 */
	const FORBIDDEN_PAGE_SETTING_KEYS = array(
		'customScriptsHeader',
		'customScriptsBodyHeader',
		'customScriptsBodyFooter',
	);

	/**
	 * Indica se o bloqueio de codigo esta ativo nas configuracoes.
	 *
	 * @return bool
	 */
	public static function is_blocking() {
		$settings = Rest_Guard::settings();
		return ! empty( $settings['block_code'] );
	}

	/**
	 * Inspeciona uma arvore de elementos. Retorna WP_Error se houver violacao.
	 *
	 * @param array $elements Arvore plana de elementos.
	 * @return true|WP_Error
	 */
	public static function inspect_elements( $elements ) {
		if ( ! self::is_blocking() ) {
			return true;
		}

		if ( ! is_array( $elements ) ) {
			return true;
		}

		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$name = isset( $element['name'] ) ? (string) $element['name'] : '';
			if ( in_array( $name, self::FORBIDDEN_ELEMENTS, true ) ) {
				return self::violation(
					sprintf(
						/* translators: %s: element name */
						__( 'Elemento que executa codigo recusado: "%s". A IA nao pode criar/editar codigo executavel.', 'marreira-mcp-bricks' ),
						$name
					)
				);
			}

			// SVG com codigo embutido (campo "code" dentro do svg).
			if ( 'svg' === $name && isset( $element['settings']['code'] ) ) {
				return self::violation(
					__( 'Elemento SVG com codigo embutido recusado.', 'marreira-mcp-bricks' )
				);
			}

			if ( isset( $element['settings'] ) && is_array( $element['settings'] ) ) {
				$check = self::inspect_settings( $element['settings'] );
				if ( is_wp_error( $check ) ) {
					return $check;
				}
			}
		}

		return true;
	}

	/**
	 * Inspeciona um array de settings (recursivamente) por chaves/strings perigosas.
	 *
	 * @param array $settings Settings.
	 * @return true|WP_Error
	 */
	public static function inspect_settings( $settings ) {
		foreach ( $settings as $key => $value ) {
			if ( in_array( $key, self::FORBIDDEN_SETTING_KEYS, true ) ) {
				return self::violation(
					sprintf(
						/* translators: %s: setting key */
						__( 'Setting de codigo recusada: "%s".', 'marreira-mcp-bricks' ),
						$key
					)
				);
			}

			if ( is_string( $value ) ) {
				$check = self::inspect_string( $value );
				if ( is_wp_error( $check ) ) {
					return $check;
				}
			} elseif ( is_array( $value ) ) {
				$check = self::inspect_settings( $value );
				if ( is_wp_error( $check ) ) {
					return $check;
				}
			}
		}

		return true;
	}

	/**
	 * Inspeciona page settings.
	 *
	 * @param array $page_settings Page settings.
	 * @return true|WP_Error
	 */
	public static function inspect_page_settings( $page_settings ) {
		if ( ! self::is_blocking() || ! is_array( $page_settings ) ) {
			return true;
		}

		foreach ( self::FORBIDDEN_PAGE_SETTING_KEYS as $key ) {
			if ( ! empty( $page_settings[ $key ] ) ) {
				return self::violation(
					sprintf(
						/* translators: %s: page setting key */
						__( 'Injecao de script em page settings recusada: "%s".', 'marreira-mcp-bricks' ),
						$key
					)
				);
			}
		}

		// customCss com tags de codigo/expressao perigosa.
		if ( ! empty( $page_settings['customCss'] ) && is_string( $page_settings['customCss'] ) ) {
			if ( preg_match( '/<\s*script|javascript:|expression\s*\(/i', $page_settings['customCss'] ) ) {
				return self::violation(
					__( 'CSS personalizado com conteudo perigoso recusado.', 'marreira-mcp-bricks' )
				);
			}
		}

		return true;
	}

	/**
	 * Inspeciona uma string por tags dinamicas perigosas.
	 *
	 * @param string $value String.
	 * @return true|WP_Error
	 */
	private static function inspect_string( $value ) {
		if ( preg_match( '/\{\s*(echo|do_action)\s*:/i', $value ) ) {
			return self::violation(
				__( 'Tag dinamica que executa codigo recusada ({echo:} ou {do_action:}).', 'marreira-mcp-bricks' )
			);
		}
		if ( preg_match( '/<\s*script\b/i', $value ) ) {
			return self::violation(
				__( 'Tag <script> em conteudo recusada.', 'marreira-mcp-bricks' )
			);
		}
		return true;
	}

	/**
	 * Constroi o WP_Error de violacao.
	 *
	 * @param string $message Mensagem.
	 * @return WP_Error
	 */
	private static function violation( $message ) {
		return new WP_Error( 'mmb_code_blocked', $message, array( 'status' => 403 ) );
	}
}
