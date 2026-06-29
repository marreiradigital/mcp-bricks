=== MarreiraMCP Bricks ===
Contributors: paulomarreira
Tags: bricks, mcp, ai, page builder, rest api
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Servidor MCP para criar e editar paginas e templates do Bricks Builder de forma nativa via IA, com autenticacao por token e endpoints ocultos do indice publico.

== Description ==

MarreiraMCP Bricks expoe um servidor **MCP (Model Context Protocol)** dentro do WordPress para que um agente de IA (Claude, Cursor, etc.) possa criar, ler, editar e excluir paginas e templates feitos no **Bricks Builder**, gerando exatamente o mesmo formato de dados que o editor visual usa.

Isso garante **compatibilidade bidirecional**: paginas criadas pela IA abrem e editam normalmente no editor Bricks, e paginas criadas no editor Bricks podem ser editadas pela IA sem corromper nada.

= Principais recursos =

* Endpoint MCP (JSON-RPC 2.0) sobre uma rota REST **oculta** do indice publico de `/wp-json/`.
* Autenticacao por **token Bearer** (apenas o hash e armazenado), HTTPS obrigatorio e rate limiting.
* Operacoes de pagina: listar, ler, criar, atualizar, excluir e ajustar page settings.
* Operacoes de template: listar, criar, atualizar e definir condicoes.
* Edicao fina de elementos: inserir, atualizar settings, mover, excluir e duplicar.
* Estilos globais: classes globais (criar/atualizar/excluir), paleta de cores, theme styles e fontes.
* Utilitarios: capacidades do ambiente, validacao de arvore (dry-run) e regeneracao de CSS.
* **Seguranca em primeiro lugar**: recusa de elementos que executam codigo (anti-RCE), checagem de capabilities e usuario de servico configuravel.

= Requisitos =

* Tema/plugin **Bricks Builder** ativo.
* WordPress 6.4+ e PHP 7.4+.
* HTTPS no site (recomendado e exigido por padrao).

== Installation ==

1. Envie a pasta `marreira-mcp-bricks` para `/wp-content/plugins/` (ou instale o .zip pelo painel).
2. Ative o plugin em **Plugins**.
3. Acesse **Configuracoes > MarreiraMCP Bricks**.
4. Defina um **usuario de servico** (Editor ou Administrador) e gere um **token**.
5. Configure seu cliente MCP com a URL do endpoint e o cabecalho `Authorization: Bearer <token>`.

== Frequently Asked Questions ==

= O endpoint aparece na listagem publica de /wp-json/? =

Nao. A rota e registrada com `show_in_index => false` e o namespace e removido do indice via filtros, ficando fora da descoberta publica.

= A IA pode executar codigo PHP no meu site? =

Nao. Por padrao, o plugin recusa qualquer elemento ou configuracao que execute codigo (elemento Code, SVG com codigo, tags `{echo:}`/`{do_action:}` e scripts em page settings).

= Preciso regenerar o CSS apos a IA editar uma pagina? =

No modo de CSS inline (padrao do Bricks) nao e necessario. No modo External Files, o plugin tenta regenerar automaticamente e expoe a tool `regenerate_css`.

== Changelog ==

= 0.1.0 =
* Menu proprio de topo (com icone) em vez de subitem de Configuracoes.
* Painel administrativo 100% AJAX (sem reloads): abas Painel, Conexao, Seguranca e Ferramentas.
* Dashboard enriquecido: metricas (paginas, templates, classes globais, tools), status do ambiente e autoteste interno.
* Catalogo de ferramentas MCP com busca e indicacao de argumentos obrigatorios.
* Geracao/rotacao/revogacao de token e configuracoes salvas via AJAX.
* Refatoracao: registro de tools como fonte unica reutilizada pelo painel.

= 0.0.1 =
* Versao inicial: servidor MCP sobre rota REST oculta (JSON-RPC 2.0).
* Autenticacao por token Bearer (hash), HTTPS obrigatorio e rate limiting.
* CRUD de paginas e templates Bricks com round-trip-safe.
* Edicao fina de elementos (inserir, atualizar, mover, excluir, duplicar).
* Guard anti-RCE recusando execucao de codigo.
* Estilos globais: classes globais, paleta, theme styles e fontes.
* Tela de admin com visual inspirado no builder do Bricks.

== Upgrade Notice ==

= 0.0.1 =
Versao inicial.
