#!/usr/bin/env python3
import json, urllib.request, urllib.error

import sys, os
EP = sys.argv[1] if len(sys.argv) > 1 else os.environ.get("MMB_ENDPOINT", "")
TOKEN = sys.argv[2] if len(sys.argv) > 2 else os.environ.get("MMB_TOKEN", "")
if not EP or not TOKEN:
    sys.exit("Uso: python3 demo-teste-mcp.py <ENDPOINT_MCP> <TOKEN>")

elements = []
c = [0]
def nid():
    c[0] += 1
    return "m" + format(c[0], "05d")

def add(name, parent, settings=None, label=None):
    i = nid()
    el = {"id": i, "name": name, "parent": parent, "children": [], "settings": settings or {}}
    if label: el["label"] = label
    elements.append(el)
    if parent != 0:
        for e in elements:
            if e["id"] == parent:
                e["children"].append(i); break
    return i

# Paleta real do site (com id/name pra casar com a paleta do Bricks)
YELLOW = {"hex": "#fedf00", "id": "gufgrq", "name": "Cor #1"}
GOLD   = {"hex": "#f5b700", "id": "utprrl", "name": "Cor #2"}
WHITE  = {"hex": "#ffffff", "id": "wnsipl", "name": "Cor #3"}
PINK   = {"hex": "#f96e9b", "id": "vmukex", "name": "Cor #4"}
BORDER = {"hex": "#e5e5e5", "id": "bxoujl", "name": "Cor #5"}
BLACK  = {"hex": "#000000", "id": "pvloly", "name": "Cor #7"}
MAGENTA= {"hex": "#e12092", "id": "sraaac", "name": "Cor #9"}
LGRAY  = {"hex": "#f0f0f1", "id": "bnbjuf", "name": "Cor #10"}
OFFW   = {"hex": "#fdfcfa", "id": "ovpeex", "name": "Cor #11"}
MUTED  = {"hex": "#555555"}
BTN_THEME = "ixsaug"  # classe global "btn-theme" do site

def section(bg, pt="80px", pb="80px", tag="section"):
    return add("section", 0, {"tag": tag,
        "_padding": {"top": pt, "right": "20px", "bottom": pb, "left": "20px"},
        "_padding:mobile_portrait": {"top": "48px", "bottom": "48px"},
        "_background": {"color": bg}})

def container(sec, gap="22px", align="center"):
    return add("container", sec, {"_alignItems": align, "_rowGap": gap})

def heading(parent, text, tag="h2", color=BLACK, size="2.2rem", weight="700", align="center", maxw=None):
    s = {"text": text, "tag": tag, "_typography": {"font-size": size, "font-weight": weight,
         "color": color, "text-align": align, "line-height": "1.2"}}
    if maxw:
        s["_widthMax"] = maxw; s["_margin"] = {"left": "auto", "right": "auto"}
    return add("heading", parent, s)

def text(parent, body, color=MUTED, size="1.05rem", align="center", weight="400", maxw=None):
    s = {"text": body, "tag": "p", "_typography": {"font-size": size, "color": color,
         "text-align": align, "line-height": "1.6", "font-weight": weight}}
    if maxw:
        s["_widthMax"] = maxw; s["_margin"] = {"left": "auto", "right": "auto"}
    return add("text-basic", parent, s)

def btn_primary(parent, label, url="#"):
    # Reutiliza a classe global btn-theme do site (amarelo + hover dourado)
    return add("button", parent, {"text": label, "tag": "a",
        "link": {"type": "external", "url": url}, "_cssGlobalClasses": [BTN_THEME]})

def btn_outline(parent, label, color=BLACK, url="#"):
    return add("button", parent, {"text": label, "tag": "a", "link": {"type": "external", "url": url},
        "_background": {"color": {"raw": "transparent"}},
        "_typography": {"color": color, "font-weight": "600"},
        "_padding": {"top": "1rem", "right": "1.5rem", "bottom": "1rem", "left": "1.5rem"},
        "_border": {"radius": {"top": "0.5rem", "right": "0.5rem", "bottom": "0.5rem", "left": "0.5rem"},
                    "width": {"top": "1px", "right": "1px", "bottom": "1px", "left": "1px"},
                    "style": "solid", "color": color}})

def btn_solid(parent, label, bg=BLACK, fg=WHITE, url="#"):
    return add("button", parent, {"text": label, "tag": "a", "link": {"type": "external", "url": url},
        "_background": {"color": bg}, "_typography": {"color": fg, "font-weight": "600"},
        "_padding": {"top": "1rem", "right": "1.5rem", "bottom": "1rem", "left": "1.5rem"},
        "_border": {"radius": {"top": "0.5rem", "right": "0.5rem", "bottom": "0.5rem", "left": "0.5rem"}}})

def grid(parent, cols=3, gap="20px", maxw="1080px"):
    return add("block", parent, {"_display": "grid",
        "_gridTemplateColumns": f"repeat({cols}, 1fr)",
        "_gridTemplateColumns:tablet_portrait": "repeat(2, 1fr)",
        "_gridTemplateColumns:mobile_portrait": "1fr",
        "_gridGap": gap, "_widthMax": maxw,
        "_margin": {"top": "36px", "left": "auto", "right": "auto"}})

def card(parent, bg=WHITE, border=BORDER):
    return add("block", parent, {"_padding": {"top": "24px", "right": "22px", "bottom": "24px", "left": "22px"},
        "_background": {"color": bg}, "_rowGap": "8px",
        "_border": {"radius": {"top": "14px", "right": "14px", "bottom": "14px", "left": "14px"},
                    "width": {"top": "1px", "right": "1px", "bottom": "1px", "left": "1px"},
                    "style": "solid", "color": border}})

def row(parent):
    return add("block", parent, {"_display": "flex", "_direction": "row", "_justifyContent": "center",
        "_alignItems": "center", "_columnGap": "14px", "_rowGap": "12px", "_flexWrap": "wrap",
        "_direction:mobile_portrait": "column"})

# -------- HERO --------
sec = section(OFFW, "96px", "88px")
con = container(sec, gap="20px")
text(con, "PÁGINA DE EXEMPLO • GERADA VIA MCP", color=MAGENTA, size="0.85rem", weight="700")
heading(con, "Esta página foi criada pela IA, no padrão do seu site.", tag="h1",
        color=BLACK, size="clamp(2rem, 5.5vw, 3.4rem)", maxw="900px")
text(con, "Layout, cores e botões seguem o design system do Segui Mores VIP — montado de ponta a ponta pelo MarreiraMCP Bricks, sem sair do editor nativo.",
     color=MUTED, size="1.15rem", maxw="680px")
r = row(con)
btn_primary(r, "Começar agora", url="#cta")
btn_outline(r, "Saiba mais", url="#recursos")

# -------- STATS --------
sec = section(WHITE, "56px", "56px")
con = container(sec)
g = grid(con, 4, maxw="980px")
for num, lbl, col in [("100%", "Nativo do Bricks", BLACK), ("24", "Ferramentas MCP", MAGENTA),
                      ("0", "Linhas no código", BLACK), ("∞", "Páginas possíveis", MAGENTA)]:
    cd = add("block", g, {"_alignItems": "center", "_rowGap": "4px"})
    heading(cd, num, tag="div", color=col, size="2.6rem", align="center")
    text(cd, lbl, color=MUTED, size="0.95rem", align="center")

# -------- RECURSOS --------
sec = section(LGRAY)
con = container(sec)
heading(con, "Por que usar o MarreiraMCP Bricks", color=BLACK, size="2.2rem")
text(con, "A IA cria e edita no formato real do Bricks — tudo continua editável no builder.", color=MUTED, maxw="620px")
g = grid(con, 3)
feats = [
    ("⚡", "Round-trip nativo", "Páginas criadas pela IA abrem e editam normalmente no editor visual do Bricks."),
    ("🎨", "Segue seu design", "Reaproveita paleta, classes globais e theme styles do seu próprio site."),
    ("🛡️", "Seguro por padrão", "Token, HTTPS, rate limit e bloqueio anti-código. Endpoints fora do /wp-json público."),
]
for emoji, title, body in feats:
    cd = card(g, WHITE)
    heading(cd, emoji, tag="div", color=PINK, size="2.2rem", align="left")
    heading(cd, title, tag="h3", color=BLACK, size="1.25rem", align="left")
    text(cd, body, color=MUTED, align="left", size="1rem")

# -------- DESTAQUE (amarelo) --------
sec = section(YELLOW, "64px", "64px")
con = container(sec, gap="16px")
heading(con, "Tudo isso sem tirar você do controle.", color=BLACK, size="2rem", maxw="760px")
text(con, "Você revisa, ajusta e publica. A IA só acelera o trabalho pesado.", color=BLACK, size="1.1rem", maxw="600px")
r = row(con)
btn_solid(r, "Ver no editor Bricks", bg=BLACK, fg=WHITE, url="#")

# -------- FAQ --------
sec = section(WHITE)
con = container(sec)
heading(con, "Perguntas frequentes", color=BLACK, size="2.2rem")
stack = add("block", con, {"_display": "flex", "_direction": "column", "_rowGap": "12px",
    "_widthMax": "760px", "_margin": {"top": "32px", "left": "auto", "right": "auto"}})
faqs = [
    ("Essa página é editável no Bricks?", "Sim. Tudo foi gravado no formato nativo; é só abrir no editor visual."),
    ("A IA pode quebrar meu site?", "Não. Ela respeita suas permissões e não executa código arbitrário."),
    ("Preciso configurar algo?", "Só gerar um token no painel do plugin e apontar seu cliente MCP."),
]
for q, a in faqs:
    cd = card(stack, OFFW)
    heading(cd, q, tag="h3", color=BLACK, size="1.12rem", align="left")
    text(cd, a, color=MUTED, align="left", size="1rem")

# -------- CTA FINAL (rosa) --------
sec = section(PINK, "72px", "72px", tag="section")
con = container(sec, gap="16px")
heading(con, "Pronto para criar páginas com IA?", color=WHITE, size="2.2rem", maxw="700px")
text(con, "Gere um token no painel e comece agora mesmo.", color=WHITE, size="1.1rem", maxw="560px")
r = row(con)
btn_solid(r, "Começar agora", bg=WHITE, fg=BLACK, url="#")

# -------- ENVIO --------
payload = {"jsonrpc": "2.0", "id": 20, "method": "tools/call",
    "params": {"name": "create_bricks_page", "arguments": {
        "title": "Teste MCP", "post_type": "page", "status": "publish",
        "slug": "teste-mcp", "elements": elements}}}

print("Elementos:", len(elements))
req = urllib.request.Request(EP, data=json.dumps(payload).encode("utf-8"),
    headers={"Content-Type": "application/json", "Accept": "application/json",
             "Authorization": "Bearer " + TOKEN, "User-Agent": "MarreiraMCP-Test/1.0"})
try:
    with urllib.request.urlopen(req, timeout=60) as r:
        print("HTTP", r.status); print(r.read().decode("utf-8"))
except urllib.error.HTTPError as e:
    print("HTTPError", e.code); print(e.read().decode("utf-8"))
