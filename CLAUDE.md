# MarreiraMCP Bricks — regras do projeto

Plugin WordPress que expõe um servidor **MCP (Model Context Protocol)** para
criar e editar páginas/templates do **Bricks Builder** de forma nativa via IA.
Destino: diretório oficial **WordPress.org**.

Código do plugin em [`marreira-mcp-bricks/`](marreira-mcp-bricks/).

## 1. Versionamento automático (OBRIGATÓRIO)

O plugin começou em **0.0.1**. **A cada modificação lógica** do código do
plugin, incremente a versão e registre no changelog — não espere o usuário
pedir.

- **patch** (`0.0.x`) para correções e ajustes pequenos.
- **minor** (`0.x.0`) para novas features/tools.
- **major** (`x.0.0`) só para quebras de compatibilidade.

A versão precisa ficar **sincronizada nos três lugares**, sempre juntos no
mesmo commit:

1. Header `Version:` em [`marreira-mcp-bricks/marreira-mcp-bricks.php`](marreira-mcp-bricks/marreira-mcp-bricks.php)
   **e** a constante `MMB_VERSION` logo abaixo.
2. `Stable tag:` em [`marreira-mcp-bricks/readme.txt`](marreira-mcp-bricks/readme.txt).
3. Nova entrada em [`marreira-mcp-bricks/CHANGELOG.md`](marreira-mcp-bricks/CHANGELOG.md)
   **e** na seção `== Changelog ==` do `readme.txt`.

A entrada do changelog descreve o **porquê** da mudança (não só o "o quê"),
no formato Keep a Changelog (Adicionado/Alterado/Corrigido/Removido).

## 2. Compatibilidade round-trip com o Bricks (inquebrável)

Toda escrita no Bricks é **read-modify-write**: ler o estado atual, alterar só
o necessário e regravar preservando IDs e campos desconhecidos. Nunca
sobrescrever a árvore cega. Páginas criadas pela IA têm que abrir no editor
Bricks e vice-versa. Centralize qualquer acesso ao Bricks no `Bricks_Gateway`
e a manipulação de árvore no `Element_Tree` — não duplicar leitura/escrita de
postmeta/options em outros lugares.

## 3. Segurança não-negociável

- Endpoints **fora** do índice público de `/wp-json/` (`show_in_index => false`
  + filtros de índice).
- Escrita só com token válido + capability do usuário de serviço. Nunca
  `permission_callback => __return_true` em escrita.
- `Code_Guard` recusa execução de código (anti-RCE). Não criar caminhos que
  contornem isso.
- Token apenas como hash, em option não-autoload.

## 4. Padrões de código (WordPress.org)

- Prefixo `mmb_` / `MMB_` em options, hooks e constantes; namespace
  `Marreira\MCP_Bricks`.
- Sanitizar toda entrada; escapar toda saída no admin (`esc_html`, `esc_attr`,
  `esc_url`). Texto de UI em **pt-BR com acentuação correta**.
- Sem execução de código arbitrário, sem `eval`, sem assets minificados sem
  fonte. Licença GPL.
- Validar antes de commitar: `php -l` nos arquivos tocados (e Plugin Check
  quando possível).

## 5. Commits

Seguir a disciplina de commits incrementais (um commit por preocupação,
header em pt-BR sem acentos, corpo explicando o porquê). O bump de versão +
changelog entra **no mesmo commit** da mudança que o motivou.
