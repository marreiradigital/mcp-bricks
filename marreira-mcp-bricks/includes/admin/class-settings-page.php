<?php
/**
 * Settings_Page: tela de administracao do plugin (visual estilo Bricks Builder).
 *
 * @package Marreira\MCP_Bricks
 */

namespace Marreira\MCP_Bricks\Admin;

use Marreira\MCP_Bricks\Activator;
use Marreira\MCP_Bricks\Auth\Token_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagina minima e segura (manage_options) com aparencia inspirada no builder
 * do Bricks (tema escuro), para gerenciar token e configuracoes.
 */
class Settings_Page {

	const MENU_SLUG    = 'marreira-mcp-bricks';
	const NONCE_ACTION = 'mmb_settings';
	const FLASH_TOKEN  = 'mmb_flash_token';

	/**
	 * Registra os hooks de admin.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_mmb_save_settings', array( $this, 'handle_save' ) );
		add_action( 'admin_post_mmb_generate_token', array( $this, 'handle_generate_token' ) );
		add_action( 'admin_post_mmb_revoke_token', array( $this, 'handle_revoke_token' ) );
	}

	/**
	 * Adiciona o item de menu sob Configuracoes.
	 *
	 * @return void
	 */
	public function add_menu() {
		$hook = add_options_page(
			__( 'MarreiraMCP Bricks', 'marreira-mcp-bricks' ),
			__( 'MarreiraMCP Bricks', 'marreira-mcp-bricks' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render' )
		);
		$this->page_hook = $hook;
	}

	/**
	 * Hook da nossa pagina (para carregar assets so nela).
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Carrega CSS/JS apenas na nossa pagina.
	 *
	 * @param string $hook Hook atual.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( $hook !== $this->page_hook ) {
			return;
		}
		wp_enqueue_style(
			'mmb-admin',
			MMB_PLUGIN_URL . 'assets/admin.css',
			array(),
			MMB_VERSION
		);
		wp_enqueue_script(
			'mmb-admin',
			MMB_PLUGIN_URL . 'assets/admin.js',
			array(),
			MMB_VERSION,
			true
		);
	}

	/**
	 * URL completa do endpoint MCP.
	 *
	 * @return string
	 */
	private function endpoint_url() {
		return esc_url_raw( rest_url( MMB_REST_NAMESPACE . MMB_REST_ROUTE ) );
	}

	/**
	 * Renderiza a pagina.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Acesso negado.', 'marreira-mcp-bricks' ) );
		}

		$settings  = wp_parse_args( get_option( Activator::SETTINGS_OPTION, array() ), Activator::default_settings() );
		$meta      = Token_Manager::meta();
		$has_token = Token_Manager::has_token();

		$flash_token = get_transient( self::FLASH_TOKEN );
		if ( $flash_token ) {
			delete_transient( self::FLASH_TOKEN );
		}
		?>
		<div class="mmb-builder">

			<header class="mmb-topbar">
				<div class="mmb-logo" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
				<div>
					<h1 class="mmb-title"><?php esc_html_e( 'MarreiraMCP Bricks', 'marreira-mcp-bricks' ); ?></h1>
					<p class="mmb-subtitle"><?php esc_html_e( 'Servidor MCP para criar e editar páginas Bricks via IA', 'marreira-mcp-bricks' ); ?></p>
				</div>
				<?php if ( $has_token ) : ?>
					<span class="mmb-badge is-on"><?php esc_html_e( '● Token ativo', 'marreira-mcp-bricks' ); ?></span>
				<?php else : ?>
					<span class="mmb-badge is-off"><?php esc_html_e( '○ Sem token', 'marreira-mcp-bricks' ); ?></span>
				<?php endif; ?>
			</header>

			<?php if ( $flash_token ) : ?>
				<div class="mmb-flash">
					<h2><?php esc_html_e( 'Token gerado — copie agora!', 'marreira-mcp-bricks' ); ?></h2>
					<p class="mmb-hint"><?php esc_html_e( 'Este token será exibido apenas uma vez. Guarde-o em local seguro; o WordPress armazena somente o hash.', 'marreira-mcp-bricks' ); ?></p>
					<div class="mmb-copy-row">
						<code class="mmb-code is-token" id="mmb-token-value"><?php echo esc_html( $flash_token ); ?></code>
						<button type="button" class="mmb-btn mmb-copy" data-target="#mmb-token-value" data-done="<?php esc_attr_e( 'Copiado!', 'marreira-mcp-bricks' ); ?>"><?php esc_html_e( 'Copiar', 'marreira-mcp-bricks' ); ?></button>
					</div>
				</div>
			<?php endif; ?>

			<div class="mmb-grid">

				<!-- Conexao MCP -->
				<section class="mmb-card mmb-span-2">
					<h2><?php esc_html_e( 'Conexão MCP', 'marreira-mcp-bricks' ); ?></h2>
					<p class="mmb-hint"><?php esc_html_e( 'Aponte seu cliente MCP (Claude, Cursor, etc.) para a URL abaixo, enviando o token no cabeçalho de autenticação. Os endpoints ficam ocultos do índice público do REST.', 'marreira-mcp-bricks' ); ?></p>

					<div class="mmb-field">
						<label><?php esc_html_e( 'URL do endpoint (POST, JSON-RPC 2.0)', 'marreira-mcp-bricks' ); ?></label>
						<div class="mmb-copy-row">
							<code class="mmb-code" id="mmb-endpoint"><?php echo esc_html( $this->endpoint_url() ); ?></code>
							<button type="button" class="mmb-btn mmb-copy" data-target="#mmb-endpoint" data-done="<?php esc_attr_e( 'Copiado!', 'marreira-mcp-bricks' ); ?>"><?php esc_html_e( 'Copiar', 'marreira-mcp-bricks' ); ?></button>
						</div>
					</div>

					<div class="mmb-field">
						<label><?php esc_html_e( 'Cabeçalho de autenticação', 'marreira-mcp-bricks' ); ?></label>
						<code class="mmb-code">Authorization: Bearer &lt;<?php esc_html_e( 'seu-token', 'marreira-mcp-bricks' ); ?>&gt;</code>
					</div>

					<p class="mmb-status-line">
						<?php
						if ( $has_token && ! empty( $meta['last_used'] ) ) {
							echo esc_html(
								sprintf(
									/* translators: %s: data/hora */
									__( 'Último uso do token: %s', 'marreira-mcp-bricks' ),
									wp_date( 'd/m/Y H:i', (int) $meta['last_used'] )
								)
							);
						} elseif ( $has_token ) {
							esc_html_e( 'Token configurado e ainda não utilizado.', 'marreira-mcp-bricks' );
						} else {
							esc_html_e( 'Nenhum token configurado ainda. Gere um na seção ao lado.', 'marreira-mcp-bricks' );
						}
						?>
					</p>
				</section>

				<!-- Token -->
				<section class="mmb-card">
					<h2><?php esc_html_e( 'Token de acesso', 'marreira-mcp-bricks' ); ?></h2>
					<p class="mmb-hint"><?php esc_html_e( 'O token autentica o agente de IA. É guardado apenas como hash; gere um novo a qualquer momento para rotacionar.', 'marreira-mcp-bricks' ); ?></p>

					<div class="mmb-actions">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( self::NONCE_ACTION ); ?>
							<input type="hidden" name="action" value="mmb_generate_token" />
							<button type="submit" class="mmb-btn mmb-btn-primary">
								<?php echo $has_token ? esc_html__( 'Rotacionar token', 'marreira-mcp-bricks' ) : esc_html__( 'Gerar token', 'marreira-mcp-bricks' ); ?>
							</button>
						</form>
						<?php if ( $has_token ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Revogar o token? Integrações existentes deixarão de funcionar.', 'marreira-mcp-bricks' ) ); ?>');">
								<?php wp_nonce_field( self::NONCE_ACTION ); ?>
								<input type="hidden" name="action" value="mmb_revoke_token" />
								<button type="submit" class="mmb-btn mmb-btn-danger"><?php esc_html_e( 'Revogar', 'marreira-mcp-bricks' ); ?></button>
							</form>
						<?php endif; ?>
					</div>
				</section>

				<!-- Seguranca -->
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="mmb-card">
					<h2><?php esc_html_e( 'Segurança', 'marreira-mcp-bricks' ); ?></h2>
					<p class="mmb-hint"><?php esc_html_e( 'Proteções aplicadas a cada requisição ao endpoint.', 'marreira-mcp-bricks' ); ?></p>
					<?php wp_nonce_field( self::NONCE_ACTION ); ?>
					<input type="hidden" name="action" value="mmb_save_settings" />

					<div class="mmb-field">
						<label class="mmb-toggle">
							<input type="checkbox" name="https_only" value="1" <?php checked( ! empty( $settings['https_only'] ) ); ?> />
							<span class="mmb-switch" aria-hidden="true"></span>
							<span>
								<span class="mmb-toggle-label"><?php esc_html_e( 'Exigir HTTPS', 'marreira-mcp-bricks' ); ?></span>
								<span class="mmb-toggle-sub"><?php esc_html_e( 'Recusa chamadas sem TLS. Recomendado.', 'marreira-mcp-bricks' ); ?></span>
							</span>
						</label>
					</div>

					<div class="mmb-field">
						<label class="mmb-toggle">
							<input type="checkbox" name="block_code" value="1" <?php checked( ! empty( $settings['block_code'] ) ); ?> />
							<span class="mmb-switch" aria-hidden="true"></span>
							<span>
								<span class="mmb-toggle-label"><?php esc_html_e( 'Bloquear execução de código (anti-RCE)', 'marreira-mcp-bricks' ); ?></span>
								<span class="mmb-toggle-sub"><?php esc_html_e( 'A IA não pode criar elementos Code/SVG com código nem scripts. Fortemente recomendado.', 'marreira-mcp-bricks' ); ?></span>
							</span>
						</label>
					</div>

					<div class="mmb-field">
						<label for="mmb_rate_limit"><?php esc_html_e( 'Limite de requisições', 'marreira-mcp-bricks' ); ?></label>
						<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
							<input type="number" min="0" id="mmb_rate_limit" name="rate_limit" value="<?php echo esc_attr( (int) $settings['rate_limit'] ); ?>" />
							<span class="mmb-toggle-sub"><?php esc_html_e( 'por', 'marreira-mcp-bricks' ); ?></span>
							<input type="number" min="1" id="mmb_rate_window" name="rate_window" value="<?php echo esc_attr( (int) $settings['rate_window'] ); ?>" />
							<span class="mmb-toggle-sub"><?php esc_html_e( 'segundos', 'marreira-mcp-bricks' ); ?></span>
						</div>
						<p class="description"><?php esc_html_e( 'Use 0 no limite para desativar o rate limiting.', 'marreira-mcp-bricks' ); ?></p>
					</div>

					<div class="mmb-actions">
						<button type="submit" class="mmb-btn mmb-btn-primary"><?php esc_html_e( 'Salvar segurança', 'marreira-mcp-bricks' ); ?></button>
					</div>
				</form>

				<!-- Usuario de servico -->
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="mmb-card">
					<h2><?php esc_html_e( 'Usuário de serviço', 'marreira-mcp-bricks' ); ?></h2>
					<p class="mmb-hint"><?php esc_html_e( 'As permissões deste usuário definem exatamente o que a IA pode fazer. Recomendado: um usuário dedicado com função Editor ou Administrador.', 'marreira-mcp-bricks' ); ?></p>
					<?php wp_nonce_field( self::NONCE_ACTION ); ?>
					<input type="hidden" name="action" value="mmb_save_settings" />
					<input type="hidden" name="https_only" value="<?php echo ! empty( $settings['https_only'] ) ? '1' : '0'; ?>" />
					<input type="hidden" name="block_code" value="<?php echo ! empty( $settings['block_code'] ) ? '1' : '0'; ?>" />
					<input type="hidden" name="rate_limit" value="<?php echo esc_attr( (int) $settings['rate_limit'] ); ?>" />
					<input type="hidden" name="rate_window" value="<?php echo esc_attr( (int) $settings['rate_window'] ); ?>" />

					<div class="mmb-field">
						<label for="mmb_service_user"><?php esc_html_e( 'Selecione o usuário', 'marreira-mcp-bricks' ); ?></label>
						<?php
						wp_dropdown_users(
							array(
								'name'              => 'service_user_id',
								'id'                => 'mmb_service_user',
								'selected'          => (int) $settings['service_user_id'],
								'show_option_none'  => __( '— selecione —', 'marreira-mcp-bricks' ),
								'option_none_value' => 0,
								'capability'        => array( 'edit_pages' ),
							)
						);
						?>
						<p class="description"><?php esc_html_e( 'Sem um usuário de serviço válido, as operações de escrita serão negadas.', 'marreira-mcp-bricks' ); ?></p>
					</div>

					<div class="mmb-actions">
						<button type="submit" class="mmb-btn mmb-btn-primary"><?php esc_html_e( 'Salvar usuário', 'marreira-mcp-bricks' ); ?></button>
					</div>
				</form>

			</div>
		</div>
		<?php
	}

	/**
	 * Valida nonce + capability nas acoes.
	 *
	 * @return void
	 */
	private function guard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Acesso negado.', 'marreira-mcp-bricks' ) );
		}
		check_admin_referer( self::NONCE_ACTION );
	}

	/**
	 * Redireciona de volta para a pagina com uma mensagem.
	 *
	 * @param string $notice Codigo da notice.
	 * @return void
	 */
	private function redirect_back( $notice ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => self::MENU_SLUG,
					'mmb_notice' => $notice,
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Handler: salvar configuracoes.
	 *
	 * @return void
	 */
	public function handle_save() {
		$this->guard();

		$settings = wp_parse_args( get_option( Activator::SETTINGS_OPTION, array() ), Activator::default_settings() );

		$settings['service_user_id'] = isset( $_POST['service_user_id'] ) ? absint( wp_unslash( $_POST['service_user_id'] ) ) : 0;
		$settings['https_only']      = ! empty( $_POST['https_only'] );
		$settings['block_code']      = ! empty( $_POST['block_code'] );
		$settings['rate_limit']      = isset( $_POST['rate_limit'] ) ? max( 0, absint( wp_unslash( $_POST['rate_limit'] ) ) ) : 60;
		$settings['rate_window']     = isset( $_POST['rate_window'] ) ? max( 1, absint( wp_unslash( $_POST['rate_window'] ) ) ) : 60;

		update_option( Activator::SETTINGS_OPTION, $settings, false );
		$this->redirect_back( 'saved' );
	}

	/**
	 * Handler: gerar/rotacionar token.
	 *
	 * @return void
	 */
	public function handle_generate_token() {
		$this->guard();
		$token = Token_Manager::generate();
		set_transient( self::FLASH_TOKEN, $token, 60 );
		$this->redirect_back( 'token' );
	}

	/**
	 * Handler: revogar token.
	 *
	 * @return void
	 */
	public function handle_revoke_token() {
		$this->guard();
		Token_Manager::revoke();
		$this->redirect_back( 'revoked' );
	}
}
