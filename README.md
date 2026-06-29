<div align="center">

# 🧱 MarreiraMCP Bricks

**Servidor MCP para criar e editar páginas do Bricks Builder com IA — de forma nativa, segura e reversível.**

[![Versão](https://img.shields.io/badge/versão-0.4.0-3a8bfd.svg)](marreira-mcp-bricks/CHANGELOG.md)
[![WordPress](https://img.shields.io/badge/WordPress-6.4%2B-21759b.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://www.php.net/)
[![Bricks](https://img.shields.io/badge/Bricks%20Builder-requerido-ff5a52.svg)](https://bricksbuilder.io/)
[![Licença](https://img.shields.io/badge/licença-GPL--2.0%2B-green.svg)](#-licença)
[![MCP](https://img.shields.io/badge/protocolo-MCP%20JSON--RPC%202.0-7b5cff.svg)](https://modelcontextprotocol.io/)

</div>

---

## ✨ O que é

**MarreiraMCP Bricks** é um plugin WordPress que liga um agente de IA (como
Claude, Cursor e outros clientes MCP) diretamente ao **Bricks Builder**. Em vez
de você arrastar elementos manualmente, a IA monta e ajusta as páginas pra você
— escrevendo **no formato nativo do Bricks**, o mesmo que o editor visual usa.

O plugin expõe um **servidor MCP (Model Context Protocol)** por uma rota REST
**oculta** e protegida por **token**, com um catálogo de ferramentas (tools)
para páginas, templates, elementos e estilos globais.

> 🔁 **Compatibilidade bidirecional (round-trip):** páginas geradas pela IA
> abrem e editam normalmente no editor Bricks — e páginas feitas no editor
> podem ser refinadas pela IA sem corromper nada.

---

## 🎯 Por que usar

| | |
|---|---|
| 🤖 **IA nativa no Bricks** | A IA cria seções, containers, headings, botões, imagens — no formato real do Bricks, não em HTML "colado". |
| 🔁 **Sem lock-in, sem corrupção** | Tudo continua editável no builder visual. A IA respeita o que já existe. |
| 🛡️ **Seguro por padrão** | Token + HTTPS + rate limit + bloqueio anti-RCE. Endpoints fora da descoberta pública de `/wp-json/`. |
| 🎨 **Estilos globais** | Cria e reaproveita classes globais, lê paletas de cor, theme styles e fontes. |
| 🧩 **Templates** | Header, footer, seções e templates de conteúdo, com condições de exibição. |
| ⚡ **CSS sempre em dia** | Detecta o modo de CSS do Bricks e regenera quando necessário. |
| 📖 **Skill embutida** | Cada site instala uma URL pública com a documentação em Markdown, pronta para a IA ler. |
| 🖥️ **Painel próprio** | Menu de topo com painel 100% AJAX (estilo Bricks): token, segurança, métricas e catálogo de ferramentas. |

---

## 📦 Requisitos

- WordPress **6.4+**
- PHP **7.4+**
- Tema/plugin **Bricks Builder** ativo
- **HTTPS** no site (exigido por padrão)
- Um **cliente MCP** (ex.: Claude Desktop, Cursor) para consumir o servidor

---

## 🚀 Instalação rápida

1. Copie a pasta `marreira-mcp-bricks/` para `wp-content/plugins/` (ou instale o
   `.zip` pelo painel do WordPress).
2. Ative em **Plugins**.
3. Vá em **Configurações → MarreiraMCP Bricks**.
4. Escolha um **usuário de serviço** (Editor ou Administrador) — as permissões
   dele definem o que a IA pode fazer.
5. Clique em **Gerar token** e copie-o (ele aparece **uma única vez**).
6. Configure seu cliente MCP com a URL do endpoint e o cabeçalho:

   ```http
   Authorization: Bearer <seu-token>
   ```

A tela de administração tem visual inspirado no builder do Bricks (tema
escuro), com botões de copiar, toggles de segurança e o status do token.

---

## 🔌 Como funciona (visão geral)

```
┌──────────────┐   JSON-RPC 2.0 / HTTPS    ┌─────────────────────────┐
│  Cliente IA  │ ───── Bearer token ─────▶ │  WordPress + este plugin │
│ (MCP client) │                           │   (rota REST oculta)     │
└──────────────┘ ◀──── resultado JSON ──── └────────────┬────────────┘
                                                         │ formato nativo
                                                         ▼
                                              ┌─────────────────────┐
                                              │   Bricks Builder     │
                                              │ (páginas/templates)  │
                                              └─────────────────────┘
```

- O cliente faz o handshake MCP (`initialize`), lista as ferramentas
  (`tools/list`) e as executa (`tools/call`).
- Cada chamada passa pela camada de segurança antes de tocar no Bricks.
- As alterações refletem no front e continuam editáveis no builder.

> 📖 Para o detalhamento das rotas, ferramentas e do que é (ou não) possível,
> consulte **[SKILL.md](SKILL.md)**.

---

## 📖 Skill para a IA (URL pública por instalação)

O `SKILL.md` vai **embutido no plugin** e é servido numa **URL pública**
(somente leitura, sem token) em qualquer site onde o plugin esteja instalado:

```
GET https://SEU-SITE/wp-json/marreira-mcp/v1/skill
```

Ela devolve a documentação em **Markdown** — basta copiar a URL (há um botão de
copiar no painel, aba **Conexão**) e mandar para a IA/IDE ler como referência do
que o plugin permite. Útil quando a skill ainda não está baixada no cliente.

---

## 🧰 Capacidades (resumo)

- **Páginas:** listar, ler, criar, atualizar, ajustar SEO/visibilidade, excluir.
- **Templates:** listar, criar (header/footer/seção/conteúdo), atualizar,
  definir condições de exibição.
- **Elementos:** inserir, atualizar estilos/settings, mover, duplicar, excluir —
  com validação de integridade da árvore.
- **Estilos globais:** classes globais (criar/atualizar/excluir), leitura de
  paleta de cores, theme styles e fontes.
- **Utilitários:** inspecionar o ambiente, validar uma árvore (dry-run),
  regenerar o CSS e **descobrir os widgets + o schema de settings de cada um**
  (`list_elements` / `get_element_schema`).

---

## 🔒 Segurança

Pensado para **não abrir brechas** no seu WordPress:

- ✅ **Endpoints ocultos** do índice público de `/wp-json/`.
- ✅ **Token** armazenado apenas como **hash** (nunca em texto puro).
- ✅ **HTTPS obrigatório** e **rate limiting** por token.
- ✅ **Permissões reais**: cada operação respeita as capabilities do usuário de
  serviço — nada de acesso irrestrito.
- ✅ **Anti-RCE**: a IA **não** consegue injetar PHP/JS executável (elemento
  Code, SVG com código, tags `{echo:}`/`{do_action:}` ou scripts em page
  settings são recusados).
- ✅ **Rotação e revogação** de token a um clique.

Encontrou algo? Veja **[Política de Segurança](#-segurança)** e reporte de forma
responsável (não abra issue pública com detalhes sensíveis).

---

## 🗺️ Roadmap

Já entregue:

- **0.1.0** — Menu próprio + painel 100% AJAX (estilo Bricks).
- **0.2.0** — `SKILL.md` embutido + URL pública da skill por instalação.
- **0.3.0** — Elemento de código com assinatura manual (a IA cria, o humano
  assina no Bricks).
- **0.4.0** — Introspecção de widgets: `list_elements` + `get_element_schema`
  (descobre o schema de settings de qualquer elemento do site).

Planejado para versões futuras (sujeito a ajustes):

- **0.3.x** — Escrita de paleta de cores e theme styles; upload/gestão de fontes.
- **0.4.x** — Componentes Bricks (criar/instanciar) e variáveis globais.
- **0.5.x** — Custom Post Types e taxonomias sob demanda para a IA.
- **0.6.x** — Tabelas personalizadas e campos customizáveis refletindo nas
  páginas.
- **0.7.x** — Funções de tema seguras e dynamic data tags personalizadas.
- **1.0.0** — Estabilização, cobertura de testes e publicação no diretório
  WordPress.org.

> O versionamento segue [SemVer](https://semver.org/lang/pt-BR/). Cada mudança é
> registrada em **[CHANGELOG.md](marreira-mcp-bricks/CHANGELOG.md)**.

---

## 🧾 Versionamento & Changelog

- Versão atual: **0.0.1**.
- Toda alteração incrementa a versão e é documentada no
  [CHANGELOG](marreira-mcp-bricks/CHANGELOG.md).
- O `Stable tag` do `readme.txt`, o header do plugin e o changelog ficam sempre
  sincronizados.

---

## 🤝 Contribuição

Sugestões, issues e PRs são bem-vindos. Antes de contribuir:

1. Abra uma issue descrevendo a ideia/bug.
2. Siga os padrões de código do projeto (prefixos `mmb_`, sanitização/escape,
   pt-BR acentuado na interface).
3. Atualize o **CHANGELOG** e o **SKILL.md** quando o comportamento mudar.

---

## 📄 Licença

**Software livre e gratuito.** Você pode **usar, copiar, modificar e distribuir**
este plugin à vontade — inclusive em projetos comerciais — **desde que mantenha
os devidos créditos** ao autor original (**Paulo Marreira / Marreira Digital**).

Formalmente licenciado sob **GPL-2.0-or-later** (exigência do diretório
WordPress.org), que já garante essas liberdades e a preservação dos avisos de
autoria/licença ao redistribuir. Mantenha o cabeçalho do plugin e este crédito.

Resumo: faça o que quiser com o código, só **dê o crédito**. 🙌

---

<div align="center">

Feito com 🧱 por **[Paulo Marreira](https://marreiradigital.com.br)** · MarreiraDigital

</div>
