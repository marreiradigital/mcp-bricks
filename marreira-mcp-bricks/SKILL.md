# SKILL — MarreiraMCP Bricks

Guia de uso do servidor MCP do plugin **MarreiraMCP Bricks** para um agente de
IA criar e editar páginas do **Bricks Builder**. Este documento descreve a
**superfície pública** (conexão, rotas, ferramentas e o formato de dados) — não
a implementação interna.

> Sempre que o comportamento do plugin mudar, **este arquivo deve ser
> atualizado** junto (rotas/ferramentas/limites). Veja o CHANGELOG para o que
> mudou em cada versão.

---

## 1. Conexão

| Item | Valor |
|---|---|
| **Endpoint** | `https://SEU-SITE/wp-json/marreira-mcp/v1/mcp` |
| **Método** | `POST` (JSON-RPC 2.0). `GET` retorna 405. |
| **Transporte** | Streamable HTTP (uma resposta JSON por POST) |
| **Auth** | Header `Authorization: Bearer <token>` |
| **TLS** | HTTPS obrigatório (por padrão) |
| **Descoberta** | A rota é **oculta** do índice público de `/wp-json/` |

O token é gerado em **Configurações → MarreiraMCP Bricks** no WP Admin. As
permissões efetivas são as do **usuário de serviço** configurado.

### Cabeçalhos recomendados

```http
POST /wp-json/marreira-mcp/v1/mcp HTTP/1.1
Content-Type: application/json
Accept: application/json
Authorization: Bearer mmb_xxxxxxxx...
Origin: https://SEU-SITE
```

---

## 2. Handshake MCP

### 2.1 `initialize`

```json
{ "jsonrpc": "2.0", "id": 1, "method": "initialize",
  "params": { "protocolVersion": "2025-03-26", "capabilities": {}, "clientInfo": { "name": "meu-agente", "version": "1.0" } } }
```

Resposta inclui `serverInfo` (`MarreiraMCP Bricks`) e `capabilities.tools`.

### 2.2 `tools/list`

```json
{ "jsonrpc": "2.0", "id": 2, "method": "tools/list", "params": {} }
```

Retorna o catálogo (nome, descrição e `inputSchema` de cada ferramenta).

### 2.3 `tools/call`

```json
{ "jsonrpc": "2.0", "id": 3, "method": "tools/call",
  "params": { "name": "create_bricks_page", "arguments": { "title": "Landing", "status": "draft", "elements": [ /* ... */ ] } } }
```

O resultado vem em `result.content[0].text` (JSON) e `result.isError`.

Também há `ping` (mantém vivo) e a notificação `notifications/initialized`.

---

## 3. Modelo de dados do Bricks (essencial)

Os elementos formam uma **árvore plana**. Cada elemento:

```json
{
  "id": "abc123",          // 6 caracteres [a-z0-9], único na página (gerado pelo plugin)
  "name": "heading",       // tipo do elemento
  "parent": "sec001",      // id do pai, ou 0 na raiz
  "children": ["btn001"],  // ids dos filhos
  "label": "Título",       // opcional
  "settings": { /* ... */ }
}
```

### Regras de ouro

- **Cores são objeto:** `{ "hex": "#0f172a" }`, `{ "rgb": "rgba(0,0,0,.5)" }` ou
  `{ "raw": "var(--brand)" }` — nunca string.
- **Tipografia em kebab-case:** `_typography: { "font-size": "2rem", "font-weight": "700", "color": { "hex": "#fff" } }`.
- **Espaçamento:** `_padding`/`_margin` como `{ "top": "...", "right": "...", "bottom": "...", "left": "..." }`.
- **Responsivo por sufixo:** `_padding:tablet_portrait`, `_typography:mobile_portrait`, e pseudo `_background:hover`.
  Breakpoints padrão: `tablet_portrait`, `mobile_landscape`, `mobile_portrait`.
- **Classes globais:** referenciadas por **id** em `settings._cssGlobalClasses: ["idDaClasse"]` — a classe precisa **existir antes** (use `upsert_global_class`).
- **IDs:** você pode omitir/duplicar IDs ao enviar subárvores; o plugin
  **regenera** IDs ao inserir/duplicar para evitar colisão.

### Nomes de elementos comuns

`section`, `container`, `block`, `div`, `heading`, `text-basic`, `text`,
`button`, `image`, `icon`, `video`, `divider`, `list`, `icon-box`, `nav-nested`,
`accordion-nested`, `tabs-nested`, `slider-nested`, `posts`, `form`, entre
outros.

### Exemplo mínimo (Seção → Container → Heading + Botão)

```json
[
  { "id": "sec001", "name": "section", "parent": 0, "children": ["con001"],
    "settings": { "tag": "section", "_padding": { "top": "80px", "bottom": "80px" }, "_background": { "color": { "hex": "#0f172a" } } } },
  { "id": "con001", "name": "container", "parent": "sec001", "children": ["hd001","bt001"],
    "settings": { "_alignItems": "center", "_rowGap": "24px" } },
  { "id": "hd001", "name": "heading", "parent": "con001", "children": [],
    "settings": { "text": "Bem-vindo", "tag": "h1", "_typography": { "font-size": "3rem", "color": { "hex": "#ffffff" }, "text-align": "center" } } },
  { "id": "bt001", "name": "button", "parent": "con001", "children": [],
    "settings": { "text": "Começar", "tag": "a", "link": { "type": "external", "url": "#" }, "_background": { "color": { "hex": "#3a8bfd" } } } }
]
```

> Também é aceito o **formato clipboard** do Bricks: `{ "content": [ ... ] }`.

---

## 4. Catálogo de ferramentas

> `area` aceita `content` (padrão), `header` ou `footer`. Capability indica a
> permissão mínima exigida do usuário de serviço.

### 4.1 Páginas

| Tool | Argumentos | Faz | Capability |
|---|---|---|---|
| `list_pages` | `post_type?`, `status?`, `limit?` | Lista posts/páginas Bricks | `edit_pages` |
| `get_page` | `post_id`, `area?` | Retorna a árvore + page settings | `edit_pages` |
| `create_bricks_page` | `title`, `post_type?`, `status?`, `slug?`, `elements?` | Cria página Bricks | `publish_pages` |
| `update_bricks_page` | `post_id`, `area?`, `elements` | Substitui a árvore (round-trip-safe) | `edit_post` |
| `set_page_settings` | `post_id`, `settings` | SEO/visibilidade (scripts recusados) | `edit_pages` |
| `delete_page` | `post_id`, `force?` | Lixeira (ou exclusão definitiva) | `delete_post` |

### 4.2 Templates

| Tool | Argumentos | Faz | Capability |
|---|---|---|---|
| `list_templates` | `type?` | Lista templates Bricks | `edit_pages` |
| `create_template` | `title`, `type`, `status?`, `elements?` | Cria template (`header`,`footer`,`content`,`section`,…) | `edit_theme_options` |
| `update_template` | `template_id`, `elements` | Substitui a árvore do template | `edit_theme_options` |
| `set_template_conditions` | `template_id`, `conditions` | Define onde o template aparece | `edit_theme_options` |

Exemplo de `conditions`: `[{ "main": "any" }]` ou
`[{ "main": "postType", "postType": ["page"] }]`.

### 4.3 Elementos (edição fina)

| Tool | Argumentos | Faz | Capability |
|---|---|---|---|
| `insert_element` | `post_id`, `area?`, `parent_id?`, `position?`, `element` **ou** `elements` | Insere um nó ou subárvore (gera IDs) | `edit_pages` |
| `update_element_settings` | `post_id`, `area?`, `element_id`, `settings`, `replace?` | Merge (ou substitui) settings de 1 elemento | `edit_pages` |
| `move_element` | `post_id`, `area?`, `element_id`, `new_parent`, `position?` | Move para outro pai/posição | `edit_pages` |
| `delete_element` | `post_id`, `area?`, `element_id` | Remove o elemento e a subárvore | `edit_pages` |
| `duplicate_element` | `post_id`, `area?`, `element_id` | Duplica como irmão (novos IDs) | `edit_pages` |

### 4.4 Estilos globais

| Tool | Argumentos | Faz | Capability |
|---|---|---|---|
| `list_global_classes` | — | Lista classes globais | `edit_pages` |
| `upsert_global_class` | `name`, `id?`, `settings?`, `category?` | Cria/atualiza classe global | `edit_theme_options` |
| `delete_global_class` | `id` | Remove classe global | `edit_theme_options` |
| `list_color_palette` | — | Lê a paleta de cores | `edit_pages` |
| `get_theme_styles` | — | Lê os theme styles | `edit_pages` |
| `list_fonts` | — | Lista fontes customizadas | `edit_pages` |

### 4.5 Utilitários

| Tool | Argumentos | Faz | Capability |
|---|---|---|---|
| `get_capabilities` | — | Ambiente: versão do Bricks, modo de CSS, breakpoints, flags | — |
| `validate_tree` | `elements` | Valida integridade/IDs/anti-RCE (dry-run, não grava) | — |
| `regenerate_css` | `post_id?` | Força regeneração de CSS (modo External Files) | `edit_theme_options` |

---

## 5. O que é possível ✅ / não é possível ❌

**Possível:**
- Criar/editar páginas e templates Bricks no formato nativo, com round-trip.
- Montar layouts completos (section/container/elementos), aplicar estilos,
  responsividade e classes globais.
- Inserir/mover/duplicar/excluir elementos individualmente.
- Ler e criar classes globais; ler paleta, theme styles e fontes.
- Validar uma árvore antes de gravar.

**Código — pode criar, mas o usuário assina (importante):**
- O elemento `code` é controlado pelo toggle **"Bloquear elementos de código"**
  no painel:
  - **Ligado (padrão):** qualquer elemento de código é **recusado (403)**.
  - **Desligado:** a IA **pode criar** o elemento `code`, **mas ele entra SEM
    assinatura**. O Bricks **não executa código não-assinado** — aparece só um
    placeholder. Para rodar, o **usuário precisa abrir a página no editor do
    Bricks e clicar em "Sign code"** (Código → Assinar).
- A IA **nunca** assina: qualquer `signature` enviada é **descartada** pelo
  plugin. Só um humano autenticado no Bricks pode assinar.
- ⚠️ Sempre que criar/editar uma página com elemento de código, **avise o
  usuário** que ele precisa assinar no editor para o código executar (o próprio
  resultado da tool já retorna esse aviso).

**Nunca é possível (não há etapa de assinatura que proteja):**
- ❌ Injeção de script/HTML executável em elementos comuns, tags `{echo:` /
  `{do_action:` e scripts em page settings — **sempre recusado (403)**, mesmo
  com o toggle desligado.
- ❌ Operar sem token válido ou sem HTTPS.
- ❌ Ultrapassar as permissões do usuário de serviço (sem escalonar funções).
- ❌ (por ora) **escrever** paleta de cores/theme styles, criar fontes,
  componentes, CPTs ou tabelas — veja o Roadmap no README.

---

## 6. Erros

- **JSON-RPC**: `-32700` (parse), `-32601` (método), `-32602` (tool/args).
- **Auth/guard** (HTTP): `401` (sem token), `403` (token inválido / HTTPS /
  anti-RCE), `429` (rate limit).
- **Tool**: erros de validação voltam com `result.isError = true` e a mensagem
  em `result.content[0].text`.

---

## 7. Fluxo recomendado para a IA

1. `get_capabilities` → confirmar Bricks ativo, modo de CSS e breakpoints.
2. (Opcional) `upsert_global_class` → criar as classes que serão reutilizadas.
3. `validate_tree` → checar a árvore antes de gravar.
4. `create_bricks_page` / `update_bricks_page` → publicar o layout.
5. Ajustes finos com `update_element_settings`, `insert_element`, etc.
6. Em modo External Files, `regenerate_css` se necessário.

---

## 8. Boas práticas de design (seguir o guia de estilo do site)

O objetivo é que a página gerada pareça **nativa do site**. Para isso, **siga
o design system do site (Theme Styles do Bricks), não invente o seu**.

### Tipografia — NÃO fixe tamanho de fonte
- **Não** defina `font-size`, `font-weight` nem `line-height` em headings e
  textos. Use apenas a **tag semântica** correta: `h1` no título principal,
  `h2` nas seções, `h3` em cards/itens, `p` no corpo. O Bricks aplica a fonte
  e a escala dos **Theme Styles** automaticamente.
- Precisa de um número/destaque grande (ex.: estatística)? Use uma tag de
  heading (`h2`/`h3`) em vez de cravar `font-size` — assim entra na escala do
  tema.
- Só sobrescreva `color`/`text-align` quando necessário (ex.: texto branco
  sobre faixa colorida). Em fundo claro, deixe a cor padrão do tema.
- **Por quê:** cravar `font-size` atropela o guia de estilo, a página fica
  "fora do site" e não acompanha mudanças globais de tipografia.

### Reaproveite os tokens do site (leia antes de criar)
- `list_color_palette` → use as cores reais; passe o objeto completo
  `{ "hex", "id", "name" }` para casar com a cor **nomeada** da paleta.
- `list_global_classes` → reutilize classes existentes (ex.: a classe de botão
  do site) via `settings._cssGlobalClasses: ["<id>"]` em vez de reestilizar do
  zero.
- `get_theme_styles` → confira fontes, largura do container e paddings padrão.
- Use `section` + `container` nativos: o container já herda a largura do tema.

### Quando precisar vencer um Theme Style
- Com parcimônia, defina a propriedade **no próprio elemento** (maior
  especificidade). Ex.: um botão cuja cor de marca é sobreposta pelo theme
  style — setar `_background` no elemento força a cor correta, mantendo a
  classe global para padding/raio/hover.

### Editando páginas existentes (ex.: login, checkout)
- **Leia primeiro** com `get_page` e **preserve os elementos funcionais**
  (formulário de login, shortcodes, query loops) — reaproveite esses nós com
  os mesmos `settings`; **não os recrie do zero**. Enriqueça o layout **ao
  redor** deles.
- Páginas que contêm um elemento `code` **não podem ser regravadas** enquanto
  o bloqueio anti-RCE estiver ligado (o guard recusa qualquer árvore com
  `code`). Edite-as no editor visual do Bricks ou ajuste a flag conscientemente.

### Árvore organizada e página em tela cheia
- **Dê um `label` legível a cada elemento** (`{"id","name","label","settings"}`).
  É o nome exibido no painel de estrutura do Bricks — sem ele, a árvore vira uma
  lista de "section/container/block" genéricos. Ex.: `"Seção · Hero"`, `"Card ·
  Preços"`, `"Botão primário"`.
- Para uma landing/documentação em **tela cheia**, desative header e footer da
  página com `set_page_settings`: `{ "headerDisabled": true, "footerDisabled":
  true }`.
- Evite literais perigosos no conteúdo: textos contendo `{echo:` ou
  `{do_action:` (mesmo em exemplos/documentação) são recusados pelo guard
  anti-RCE. Escreva-os sem as chaves ao documentar.

---

_Documento de referência da skill — mantenha sincronizado com o código a cada
versão._
