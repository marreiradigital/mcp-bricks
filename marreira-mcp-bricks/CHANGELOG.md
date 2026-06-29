# Changelog

Todas as mudanças relevantes deste projeto são documentadas aqui.

O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/)
e o projeto adota [Versionamento Semântico](https://semver.org/lang/pt-BR/).

A cada modificação, a versão é incrementada e sincronizada em três lugares:
o header `Version:` do arquivo principal, o `Stable tag:` do `readme.txt` e
uma nova entrada neste arquivo (espelhada na seção `== Changelog ==` do
`readme.txt`).

## [0.1.0] - 2026-06-29

### Adicionado
- **Menu próprio de topo** (com ícone SVG) no admin, em vez de subitem de
  Configurações.
- **Painel 100% AJAX** (sem recarregar a página), com abas **Painel**,
  **Conexão**, **Segurança** e **Ferramentas**.
- **Dashboard enriquecido**: métricas (páginas Bricks, templates, classes
  globais, ferramentas MCP), status do ambiente (Bricks, modo de CSS, anti-RCE,
  protocolo) e **autoteste interno** de conexão.
- **Catálogo de ferramentas MCP** com busca e destaque dos argumentos
  obrigatórios — lido da mesma fonte única do servidor MCP.
- Gerar/rotacionar/revogar **token** e salvar configurações (HTTPS, anti-RCE,
  rate limit, usuário de serviço) **via AJAX**, com toasts de feedback.

### Alterado
- `MCP_Server` expõe `build_registry()` estático como **fonte única** do
  catálogo de tools, reutilizado pelo painel (evita duplicar a lista).
- Camada de admin reescrita: removido o antigo `Settings_Page` (form POST com
  reload) em favor de `Admin` (SPA AJAX).

### Motivação
- O Paulo pediu menu próprio, painel mais rico, design revisado e tudo via AJAX.

## [0.0.1] - 2026-06-29

### Adicionado
- Versão inicial do plugin **MarreiraMCP Bricks**.
- Servidor MCP (Model Context Protocol) sobre uma rota REST oculta
  (`marreira-mcp/v1/mcp`), falando JSON-RPC 2.0 (`initialize`, `tools/list`,
  `tools/call`, `ping`). A rota é removida do índice público de `/wp-json/`
  via `show_in_index => false` e filtros de `rest_index`/`rest_namespace_index`.
- Autenticação por token Bearer (apenas o hash SHA-256 é armazenado, em option
  não-autoload), com HTTPS obrigatório, rate limiting por token e adoção de um
  usuário de serviço para as checagens de capability.
- Camada única de acesso ao Bricks (`Bricks_Gateway`) com leitura/escrita
  round-trip-safe do formato nativo (`_bricks_page_content_2`, header/footer,
  page settings, templates `bricks_template`).
- Validação e manipulação da árvore plana de elementos (`Element_Tree`):
  integridade `parent`/`children`, IDs de 6 caracteres, inserção, remoção,
  movimentação e duplicação.
- Guard anti-RCE (`Code_Guard`): recusa elementos/configurações que executam
  código (Code, SVG com código, `{echo:}`, `{do_action:}`, scripts em page
  settings).
- Regeneração de CSS sensível ao modo do Bricks (inline x external files).
- Gestão de estilos globais: classes globais (upsert/delete), leitura de paleta
  de cores, theme styles e fontes.
- Conjunto de tools MCP da v0.0.1: páginas, templates, elementos, estilos
  globais e utilitários (`get_capabilities`, `validate_tree`, `regenerate_css`).
- Tela de administração com visual inspirado no builder do Bricks (tema escuro)
  para gerar/rotacionar/revogar token, definir o usuário de serviço, ver a URL
  do endpoint e ajustar as proteções.

[0.1.0]: https://marreiradigital.com.br/marreira-mcp-bricks
[0.0.1]: https://marreiradigital.com.br/marreira-mcp-bricks
