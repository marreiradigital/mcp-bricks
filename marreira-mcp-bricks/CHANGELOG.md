# Changelog

Todas as mudanças relevantes deste projeto são documentadas aqui.

O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/)
e o projeto adota [Versionamento Semântico](https://semver.org/lang/pt-BR/).

A cada modificação, a versão é incrementada e sincronizada em três lugares:
o header `Version:` do arquivo principal, o `Stable tag:` do `readme.txt` e
uma nova entrada neste arquivo (espelhada na seção `== Changelog ==` do
`readme.txt`).

## [0.5.0] - 2026-06-29

### Alterado
- **Painel administrativo redesenhado** com visual high-tech / "cara de IA":
  tema escuro, cards de vidro (glass), gradientes índigo→violeta, glow/neon,
  fundo dot-grid com brilhos radiais animados (respeita `prefers-reduced-motion`).
- **Largura 100% do viewport** — removido o limite de largura que deixava o
  conteúdo "preso" e alinhado à esquerda; grids agora preenchem a tela inteira
  (auto-fit).
- **Contraste cuidado (WCAG AA)**: paleta de texto/fundo escolhida para
  legibilidade (texto claro ~8–16:1 sobre os fundos; accents neon só onde têm
  contraste suficiente).
- Mantém o painel **100% AJAX** (sem reloads) — apenas a camada visual mudou.

## [0.4.0] - 2026-06-29

### Adicionado
- **Introspecção de widgets.** Duas novas tools MCP:
  - `list_elements` — lista todos os elementos/widgets registrados no site
    (nome, label, categoria, nestable), incluindo Bricks Pro e elementos de
    terceiros.
  - `get_element_schema` — retorna o **schema de settings** de um widget
    (tipos de controle, defaults, opções, grupos), lido direto do
    `set_controls()` da classe do elemento.
- Assim a IA descobre exatamente quais settings um `form`, `slider-nested`,
  `accordion-nested`, `posts`, etc. aceitam, **sem precisar montar no editor
  antes**.

### Implementação
- Novo `Element_Inspector` (`includes/bricks/class-element-inspector.php`):
  lê `\Bricks\Elements::$elements`, instancia cada elemento e chama apenas
  `set_control_groups()` + `set_controls()` (nunca `load()`/`init()`), tudo em
  try/catch — somente leitura, sem efeitos colaterais de render.

### Motivação
- O Paulo perguntou se dava para descobrir o schema de settings de cada widget.
  Dá — o Bricks expõe os controles na própria classe do elemento.

## [0.3.0] - 2026-06-29

### Adicionado
- **Elemento de código com assinatura manual.** Com o toggle **"Bloquear
  elementos de código"** desligado, a IA pode **criar** o elemento `code` — mas
  ele entra **sem assinatura**, e o Bricks não executa código não-assinado. As
  tools (`create_bricks_page`, `update_bricks_page`, `insert_element`,
  `create_template`, `update_template`) retornam um **aviso** instruindo o
  usuário a abrir no editor do Bricks e clicar **"Sign code"** para executar.

### Segurança
- Qualquer `signature` enviada pela IA é **sempre descartada** pelo Sanitizer —
  só um humano autenticado pode assinar código no Bricks.
- Injeção de script/HTML executável, tags `{echo:`/`{do_action:` e scripts em
  page settings continuam **sempre bloqueados** (não há etapa de assinatura que
  os proteja), independentemente do toggle.

### Documentação
- `SKILL.md` (raiz + cópia empacotada) documenta o fluxo de código + assinatura.
- `README.md`: seção de licença reescrita (uso/cópia/modificação/distribuição
  livres, mantendo os créditos ao autor).

### Motivação
- O Paulo pediu para permitir inserir código com o fluxo de assinatura manual do
  Bricks (a IA cria, o humano assina), mantendo a proteção anti-RCE.

## [0.2.0] - 2026-06-29

### Adicionado
- **SKILL.md empacotado no plugin** e exposto numa **rota pública**
  `GET /wp-json/marreira-mcp/v1/skill` que serve a documentação em **Markdown**
  (somente leitura, sem token). Assim qualquer site com o plugin instalado tem
  uma URL fixa, fácil de copiar e mandar para a IA/IDE ler — útil quando a
  skill ainda não está baixada no cliente.
- Painel: card **"Skill para a IA"** na aba Conexão, com a URL pública copiável.
- **SKILL.md — seção "Boas práticas de design"**: o agente deve seguir o guia
  de estilo do site (Theme Styles) em vez de cravar `font-size`; reaproveitar a
  paleta e as classes globais; preservar elementos funcionais ao editar páginas
  existentes (ex.: login); e dar um `label` legível a cada elemento para a
  árvore ficar organizada no editor do Bricks.

### Motivação
- O Paulo pediu que a skill fosse distribuída pelo próprio plugin (URL pública
  por instalação), e a correção de tipografia/design surgiu ao gerar páginas
  reais que não seguiam o design system do site.

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

[0.5.0]: https://marreiradigital.com.br/marreira-mcp-bricks
[0.4.0]: https://marreiradigital.com.br/marreira-mcp-bricks
[0.3.0]: https://marreiradigital.com.br/marreira-mcp-bricks
[0.2.0]: https://marreiradigital.com.br/marreira-mcp-bricks
[0.1.0]: https://marreiradigital.com.br/marreira-mcp-bricks
[0.0.1]: https://marreiradigital.com.br/marreira-mcp-bricks
