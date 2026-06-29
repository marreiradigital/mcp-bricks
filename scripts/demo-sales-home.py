#!/usr/bin/env python3
"""Cria uma homepage de vendas de exemplo via MCP do MarreiraMCP Bricks.

Uso:
    python3 demo-sales-home.py <ENDPOINT_MCP> <TOKEN>
    # ou defina as variaveis de ambiente MMB_ENDPOINT e MMB_TOKEN

Exemplo:
    python3 demo-sales-home.py https://SEU-SITE/wp-json/marreira-mcp/v1/mcp mmb_xxxxx
"""
import json, urllib.request, urllib.error, sys, os

EP = sys.argv[1] if len(sys.argv) > 1 else os.environ.get("MMB_ENDPOINT", "")
TOKEN = sys.argv[2] if len(sys.argv) > 2 else os.environ.get("MMB_TOKEN", "")
if not EP or not TOKEN:
    sys.exit("Uso: python3 demo-sales-home.py <ENDPOINT_MCP> <TOKEN>  (ou MMB_ENDPOINT / MMB_TOKEN)")

elements = []
c = [0]
def nid():
    c[0] += 1
    return "e" + format(c[0], "05d")  # 6 chars [a-z0-9]

def add(name, parent, settings=None, label=None):
    i = nid()
    el = {"id": i, "name": name, "parent": parent, "children": [], "settings": settings or {}}
    if label:
        el["label"] = label
    elements.append(el)
    if parent != 0:
        for e in elements:
            if e["id"] == parent:
                e["children"].append(i)
                break
    return i

# Paleta
DARK = {"hex": "#0b1220"}
PANEL = {"hex": "#131a2b"}
SOFT = {"hex": "#f6f8fb"}
WHITE = {"hex": "#ffffff"}
ACCENT = {"hex": "#3a8bfd"}
INK = {"hex": "#0f172a"}
MUTED = {"hex": "#64748b"}
MUTED_L = {"hex": "#cbd5e1"}

def section(bg, pt="96px", pb="96px", tag="section"):
    return add("section", 0, {"tag": tag, "_padding": {"top": pt, "right": "20px", "bottom": pb, "left": "20px"},
                              "_padding:mobile_portrait": {"top": "56px", "bottom": "56px"},
                              "_background": {"color": bg}})

def container(sec, gap="28px", align="center"):
    return add("container", sec, {"_alignItems": align, "_rowGap": gap})

def heading(parent, text, tag="h2", color=INK, size="2.2rem", weight="700", align="center", maxw=None, mt=None):
    s = {"text": text, "tag": tag, "_typography": {"font-size": size, "font-weight": weight, "color": color, "text-align": align, "line-height": "1.15"}}
    if maxw:
        s["_widthMax"] = maxw
        s["_margin"] = {"left": "auto", "right": "auto"}
    if mt:
        s["_margin"] = {"top": mt, "left": "auto", "right": "auto"}
    return add("heading", parent, s)

def text(parent, body, color=MUTED, size="1.05rem", align="center", weight="400", maxw=None):
    s = {"text": body, "tag": "p", "_typography": {"font-size": size, "color": color, "text-align": align, "line-height": "1.6", "font-weight": weight}}
    if maxw:
        s["_widthMax"] = maxw
        s["_margin"] = {"left": "auto", "right": "auto"}
    return add("text-basic", parent, s)

def button(parent, label, bg, fg, url="#", ghost=False):
    s = {"text": label, "tag": "a", "link": {"type": "external", "url": url},
         "_typography": {"color": fg, "font-weight": "600", "font-size": "1rem"},
         "_padding": {"top": "14px", "right": "26px", "bottom": "14px", "left": "26px"},
         "_border": {"radius": {"top": "10px", "right": "10px", "bottom": "10px", "left": "10px"}}}
    if ghost:
        s["_background"] = {"color": {"raw": "transparent"}}
        s["_border"]["width"] = {"top": "1px", "right": "1px", "bottom": "1px", "left": "1px"}
        s["_border"]["style"] = "solid"
        s["_border"]["color"] = fg
    else:
        s["_background"] = {"color": bg}
    return add("button", parent, s)

def grid(parent, cols=3, gap="22px"):
    return add("block", parent, {"_display": "grid",
                                 "_gridTemplateColumns": f"repeat({cols}, 1fr)",
                                 "_gridTemplateColumns:tablet_portrait": "repeat(2, 1fr)",
                                 "_gridTemplateColumns:mobile_portrait": "1fr",
                                 "_gridGap": gap, "_widthMax": "1080px",
                                 "_margin": {"top": "40px", "left": "auto", "right": "auto"}})

def card(parent, bg=SOFT, border="#e6ebf2"):
    return add("block", parent, {"_padding": {"top": "26px", "right": "24px", "bottom": "26px", "left": "24px"},
                                 "_background": {"color": bg}, "_rowGap": "10px",
                                 "_border": {"radius": {"top": "14px", "right": "14px", "bottom": "14px", "left": "14px"},
                                             "width": {"top": "1px", "right": "1px", "bottom": "1px", "left": "1px"},
                                             "style": "solid", "color": {"hex": border}}})

def row(parent, gap="14px"):
    return add("block", parent, {"_display": "flex", "_direction": "row", "_justifyContent": "center",
                                 "_alignItems": "center", "_columnGap": gap, "_flexWrap": "wrap",
                                 "_direction:mobile_portrait": "column"})

# ---------------- HERO ----------------
sec = section(DARK, "120px", "120px")
con = container(sec, gap="22px")
text(con, "AGÊNCIA DESTAK • MARKETING DE PERFORMANCE", color=ACCENT, size="0.85rem", weight="700")
heading(con, "Sua marca em destaque. Suas vendas nas alturas.", tag="h1", color=WHITE, size="clamp(2.4rem, 6vw, 4rem)", maxw="900px")
text(con, "Tráfego, criativo e estratégia num só lugar. A Destak transforma cliques em clientes com método, dados e velocidade.", color=MUTED_L, size="1.2rem", maxw="680px")
r = row(con)
button(r, "Quero vender mais", ACCENT, WHITE, url="#contato")
button(r, "Falar com especialista", DARK, WHITE, url="#contato", ghost=True)

# ---------------- SOCIAL PROOF ----------------
sec = section(SOFT, "44px", "44px")
con = container(sec)
text(con, "Mais de 500 negócios já aceleraram resultados com a Destak", color=MUTED, size="1rem", weight="600")

# ---------------- BENEFITS ----------------
sec = section(WHITE)
con = container(sec)
heading(con, "Por que escolher a Destak", color=INK, size="2.4rem")
text(con, "Tudo o que o seu marketing precisa para vender mais, sem complicação.", color=MUTED, maxw="600px")
g = grid(con, 3)
benefits = [
    ("🚀", "Mais conversões", "Funis e criativos testados para transformar visitantes em compradores."),
    ("🎯", "Tráfego qualificado", "Campanhas segmentadas que falam com quem realmente compra."),
    ("📈", "Resultado mensurável", "Relatórios claros: você acompanha cada real investido virar receita."),
]
for emoji, title, body in benefits:
    cd = card(g, SOFT)
    heading(cd, emoji, tag="div", color=INK, size="2.4rem", align="left")
    heading(cd, title, tag="h3", color=INK, size="1.3rem", align="left")
    text(cd, body, color=MUTED, align="left", size="1rem")

# ---------------- STEPS ----------------
sec = section(SOFT)
con = container(sec)
heading(con, "Como funciona", color=INK, size="2.4rem")
text(con, "Em 3 passos simples você coloca a sua máquina de vendas para rodar.", color=MUTED, maxw="600px")
g = grid(con, 3)
steps = [
    ("1", "Diagnóstico", "Entendemos seu negócio, suas metas e seu público em uma call estratégica."),
    ("2", "Estratégia & criativos", "Montamos campanhas, páginas e criativos sob medida para converter."),
    ("3", "Escala com dados", "Otimizamos diariamente e escalamos o que dá resultado de verdade."),
]
for num, title, body in steps:
    cd = card(g, WHITE)
    heading(cd, num, tag="div", color=ACCENT, size="2.6rem", align="left")
    heading(cd, title, tag="h3", color=INK, size="1.25rem", align="left")
    text(cd, body, color=MUTED, align="left", size="1rem")

# ---------------- TESTIMONIALS ----------------
sec = section(WHITE)
con = container(sec)
heading(con, "O que dizem nossos clientes", color=INK, size="2.4rem")
g = grid(con, 2)
quotes = [
    ("“Em 3 meses dobramos o faturamento da loja. A Destak virou parte do time.”", "— Marina S., e-commerce de moda"),
    ("“Saímos do achismo. Hoje cada campanha tem meta, número e retorno claro.”", "— Rafael T., curso online"),
]
for q, who in quotes:
    cd = card(g, SOFT)
    text(cd, q, color=INK, align="left", size="1.15rem", weight="500")
    text(cd, who, color=MUTED, align="left", size="0.95rem", weight="600")

# ---------------- PRICING ----------------
sec = section(DARK, "100px", "100px")
con = container(sec)
heading(con, "Planos que cabem no seu momento", color=WHITE, size="2.4rem")
text(con, "Sem fidelidade engessada. Escolha o ritmo do seu crescimento.", color=MUTED_L, maxw="600px")
g = grid(con, 3)
plans = [
    ("Start", "R$ 1.497", "/mês", ["1 plataforma de anúncios", "Gestão de campanhas", "Relatório mensal", "Suporte por e-mail"], False),
    ("Pro", "R$ 2.997", "/mês", ["Até 3 plataformas", "Criativos inclusos", "Relatório semanal", "Suporte prioritário"], True),
    ("Scale", "Sob consulta", "", ["Estratégia full-funnel", "Squad dedicado", "BI e dashboards", "Reuniões quinzenais"], False),
]
for name, price, per, feats, highlight in plans:
    cd = card(g, PANEL, border="#22304d")
    if highlight:
        cd_el = next(e for e in elements if e["id"] == cd)
        cd_el["settings"]["_border"]["color"] = ACCENT
        cd_el["settings"]["_border"]["width"] = {"top": "2px", "right": "2px", "bottom": "2px", "left": "2px"}
    heading(cd, name, tag="h3", color=WHITE, size="1.4rem", align="left")
    heading(cd, price + ("  " + per if per else ""), tag="div", color=ACCENT, size="2rem", align="left")
    for f in feats:
        text(cd, "✓ " + f, color=MUTED_L, align="left", size="1rem")
    button(cd, "Assinar agora" if name != "Scale" else "Falar com vendas", ACCENT, WHITE, url="#contato")

# ---------------- FAQ ----------------
sec = section(SOFT)
con = container(sec)
heading(con, "Perguntas frequentes", color=INK, size="2.4rem")
stack = add("block", con, {"_display": "flex", "_direction": "column", "_rowGap": "14px", "_widthMax": "760px",
                           "_margin": {"top": "36px", "left": "auto", "right": "auto"}})
faqs = [
    ("Preciso ter site pronto?", "Não. A gente cria páginas de alta conversão para suas campanhas se precisar."),
    ("Em quanto tempo vejo resultado?", "Os primeiros dados aparecem na 1ª semana; otimização real a partir do 1º mês."),
    ("Tem fidelidade?", "Trabalhamos com ciclos mensais. Você fica porque dá resultado, não por contrato."),
    ("Qual investimento mínimo em mídia?", "Recomendamos a partir de R$ 50/dia para ter dados suficientes de otimização."),
]
for q, a in faqs:
    cd = card(stack, WHITE)
    heading(cd, q, tag="h3", color=INK, size="1.15rem", align="left")
    text(cd, a, color=MUTED, align="left", size="1rem")

# ---------------- FINAL CTA ----------------
sec = section(ACCENT, "92px", "92px")
sec_el = next(e for e in elements if e["id"] == sec)
con = container(sec, gap="20px")
heading(con, "Pronto para vender mais?", tag="h2", color=WHITE, size="2.6rem", maxw="720px")
text(con, "Agende um diagnóstico gratuito e descubra o potencial de vendas da sua marca.", color=WHITE, size="1.15rem", maxw="600px")
r = row(con)
button(r, "Começar agora", WHITE, ACCENT, url="#contato")

# ---------------- FOOTER ----------------
sec = section(DARK, "40px", "40px", tag="footer")
con = container(sec)
text(con, "© 2026 Destak • Marreira Digital. Todos os direitos reservados.", color=MUTED_L, size="0.9rem")

# ---------------- ENVIAR ----------------
payload = {
    "jsonrpc": "2.0", "id": 10, "method": "tools/call",
    "params": {"name": "create_bricks_page", "arguments": {
        "title": "Home — Destak (Vendas)",
        "post_type": "page",
        "status": "publish",
        "slug": "home-destak-vendas",
        "elements": elements,
    }}
}

print(f"Total de elementos: {len(elements)}")
req = urllib.request.Request(EP, data=json.dumps(payload).encode("utf-8"),
                            headers={"Content-Type": "application/json",
                                     "Accept": "application/json, text/event-stream",
                                     "Authorization": "Bearer " + TOKEN})
try:
    with urllib.request.urlopen(req, timeout=60) as resp:
        out = resp.read().decode("utf-8")
        print("HTTP", resp.status)
        print(out)
except urllib.error.HTTPError as e:
    print("HTTPError", e.code)
    print(e.read().decode("utf-8"))
