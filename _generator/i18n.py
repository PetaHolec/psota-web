"""Překlad statických stránek: python3 i18n.py extract | apply
extract: z českých stránek vytáhne všechny překládatelné texty do i18n/keys.json
apply:   přeloží en/de/pl stránky podle i18n/<lang>.json, přepíše odkazy na /<lang>/… a nastaví lang
Texty s vloženým značením mají tvar:  věta [1]tučný text[/1] další věta [br] nový řádek
"""
import os, re, sys, json, html
from html.parser import HTMLParser

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
LANGS = ['en', 'de', 'pl']
I18N = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'i18n')
VOID = {'br', 'img', 'input', 'meta', 'link', 'hr', 'source', 'area', 'base', 'col', 'embed', 'param', 'track', 'wbr', 'path', 'circle', 'rect', 'line', 'use', 'mpath'}
INLINE = {'a', 'strong', 'b', 'em', 'i', 'span', 'small', 'u', 'sup', 'sub', 'time', 'mark', 'abbr', 'code', 'br'}
SKIP = {'script', 'style', 'svg', 'noscript', 'iframe', 'textarea'}
LETTER = re.compile(r'[A-Za-zÀ-ž]{2,}')
ATTRS = ('alt', 'title', 'aria-label', 'placeholder')

class Node:
    __slots__ = ('tag', 'start', 'cs', 'ce', 'end', 'children', 'parent', 'attrs')
    def __init__(self, tag, start, cs, parent, attrs):
        self.tag, self.start, self.cs, self.ce, self.end = tag, start, cs, cs, cs
        self.children, self.parent, self.attrs = [], parent, attrs

class Tree(HTMLParser):
    def __init__(self, src):
        super().__init__(convert_charrefs=False)
        self.src = src
        self.lines = [0]
        for m in re.finditer('\n', src): self.lines.append(m.end())
        self.root = Node('#root', 0, 0, None, {}); self.root.ce = self.root.end = len(src)
        self.cur = self.root
    def off(self): l, c = self.getpos(); return self.lines[l - 1] + c
    def handle_starttag(self, tag, attrs):
        s = self.off(); raw = self.get_starttag_text()
        n = Node(tag, s, s + len(raw), self.cur, dict(attrs)); self.cur.children.append(n)
        if tag in VOID or raw.endswith('/>'): n.end = n.ce = n.cs
        else: self.cur = n
    def handle_startendtag(self, tag, attrs):
        s = self.off(); raw = self.get_starttag_text()
        n = Node(tag, s, s + len(raw), self.cur, dict(attrs)); n.end = n.ce = n.cs; self.cur.children.append(n)
    def handle_endtag(self, tag):
        s = self.off()
        n = self.cur
        while n is not self.root and n.tag != tag: n = n.parent
        if n is self.root: return
        n.ce = s; m = re.compile(r'>').search(self.src, s); n.end = (m.end() if m else s + len(tag) + 3)
        self.cur = n.parent

def norm_ws(t): return re.sub(r'\s+', ' ', t.replace('\xa0', ' ')).strip()

def unit_key(src, node_children, a, b):
    """Return (key, mapping) for source slice [a,b) whose children are inline elements."""
    out, i, idx, maps = [], a, 0, []
    stack = []
    def seg(x, y): return html.unescape(src[x:y])
    for ch in node_children:
        out.append(seg(i, ch.start))
        if ch.tag == 'br': out.append('[br]'); maps.append(('br', src[ch.start:ch.end])); i = ch.end; continue
        idx += 1
        inner = unit_key(src, ch.children, ch.cs, ch.ce)
        out.append(f'[{idx}]{inner[0]}[/{idx}]')
        maps.append((idx, src[ch.start:ch.cs], src[ch.ce:ch.end], inner[1]))
        i = ch.end
    out.append(seg(i, b))
    return ''.join(out), maps

def inline_only(n):
    return all(c.tag in INLINE and inline_only(c) for c in n.children)

def units(src, root):
    """Yield (start, end, key, maps) for every translatable region."""
    res = []
    def gaps(n):
        i = n.cs
        for c in n.children:
            yield i, c.start
            i = c.end
        yield i, n.ce
    def walk(n):
        if n.tag in SKIP or (n.attrs.get('lang') == 'cs' and n.tag != 'html'): return
        has_direct = any(LETTER.search(html.unescape(re.sub(r'<!--.*?-->', '', src[a:b], flags=re.S))) for a, b in gaps(n))
        if n.tag != '#root' and ((n.children and inline_only(n) and has_direct) or not n.children):
            raw = src[n.cs:n.ce]
            txt = re.sub(r'<!--.*?-->', '', raw, flags=re.S)
            if LETTER.search(html.unescape(re.sub(r'<[^>]+>', '', txt))) and n.tag not in ('html', 'head', 'body'):
                key, maps = unit_key(src, n.children, n.cs, n.ce)
                res.append((n.cs, n.ce, key, maps))
            return
        for a, b in gaps(n):
            raw = src[a:b]
            if n.tag in ('html', 'head', 'body', '#root') and not n.tag == 'title': pass
            t = re.sub(r'<!--.*?-->', '', raw, flags=re.S)
            if LETTER.search(html.unescape(t)) and '<' not in t:
                res.append((a, b, html.unescape(t), []))
        for c in n.children: walk(c)
    walk(root)
    return res

def key_norm(k): return norm_ws(k)

DATE_SUF = re.compile(r'^(.*?)(\s*\(\d{2}\.\d{2}\.\d{4}\))$')
def pieces(k):
    """Split page titles ('A – B | PSOTA Group') and strip '(dd.mm.yyyy)' suffixes → list of translatable pieces (+ separators)."""
    if k.endswith('PSOTA Group') and ' | ' in k:
        out = []
        for p in re.split(r'( \| | – )', k): out.append(p)
        res = []
        for p in out:
            res += pieces(p) if p not in (' | ', ' – ') else [p]
        return res
    m = DATE_SUF.match(k)
    if m: return [m.group(1), m.group(2)]
    return [k]

def attr_hits(src):
    """(start, end, value) for translatable attribute values (alt, aria-label, title, placeholder, meta content)."""
    hits = []
    for m in re.finditer(r'<(?!script|style|svg)[a-zA-Z][^<>]*>', src):
        tag = m.group(0)
        for am in re.finditer(r'\s(' + '|'.join(ATTRS) + r')="([^"]*)"', tag):
            if LETTER.search(am.group(2)): hits.append((m.start() + am.start(2), m.start() + am.end(2), html.unescape(am.group(2)), 'attr'))
        if tag.startswith('<meta'):
            nm = re.search(r'(?:name|property)="([^"]+)"', tag)
            cm = re.search(r'content="([^"]*)"', tag)
            if nm and cm and nm.group(1) in ('description', 'og:title', 'og:description', 'og:site_name') and LETTER.search(cm.group(1)):
                hits.append((m.start() + cm.start(1), m.start() + cm.end(1), html.unescape(cm.group(1)), 'desc' if nm.group(1) in ('description', 'og:description') else 'attr'))
    return hits

def page_files(langdir=None):
    base = os.path.join(ROOT, langdir) if langdir else ROOT
    for dp, dn, fn in os.walk(base):
        rel = os.path.relpath(dp, ROOT)
        top = rel.split(os.sep)[0]
        if not langdir and top in ('_generator', 'assets', 'en', 'de', 'pl'): dn[:] = []; continue
        for f in fn:
            if f.endswith('.html') and f != '404.html': yield os.path.join(dp, f)

def split_title(t):
    parts = re.split(r'( \| | – )', t)
    return parts

def extract():
    keys = {}
    for f in page_files('en'):
        src = open(f, encoding='utf-8').read()
        tree = Tree(src); tree.feed(src)
        for a, b, key, maps in units(src, tree.root):
            k = key_norm(key)
            if k.startswith('<?') or not LETTER.search(re.sub(r'\[/?\w+\]', '', k)): continue
            for pc in pieces(k):
                if LETTER.search(pc) and pc not in (' | ', ' – '): keys[pc] = keys.get(pc, 0) + 1
        for a, b, v, kind in attr_hits(src):
            if kind == 'desc' and (v.rstrip().endswith('…') or len(v) < 60 or not f.endswith('index.html')): pass
            if v.rstrip().endswith('…'): continue
            for part in split_title(norm_ws(v)):
                if LETTER.search(part) and part not in (' | ', ' – '): keys[part] = keys.get(part, 0) + 1
    WD = r'(Pondělí|Úterý|Středa|Čtvrtek|Pátek|Sobota|Neděle)'
    for k in list(keys):
        if k.endswith('…') or re.fullmatch(r'[\d.\s–\-]+' + WD + r'?\s*(ZAVŘENO)?', k): del keys[k]
    keys.update({'ZAVŘENO': 1, 'Pondělí': 1, 'Úterý': 1, 'Středa': 1, 'Čtvrtek': 1, 'Pátek': 1, 'Sobota': 1, 'Neděle': 1})
    os.makedirs(I18N, exist_ok=True)
    # title tags are handled as units already (<title> has no element children)
    json.dump(sorted(keys), open(os.path.join(I18N, 'keys.json'), 'w'), ensure_ascii=False, indent=0)
    print(len(keys), 'unikátních textů,', sum(len(k) for k in keys), 'znaků')

def rebuild(key_tr, maps):
    """Substitute [n]..[/n] and [br] tokens with the original tags."""
    byid = {m[0]: m for m in maps}
    def sub(txt, maps_):
        bid = {m[0]: m for m in maps_}
        def tok(m):
            n = m.group(1)
            if n == 'br': return bid.get('br', ('br', '<br>'))[1] if False else '<br>'
            if n not in [str(i) for i in bid if i != 'br']: return ''
            return None
        out = []
        pos = 0
        for m in re.finditer(r'\[(br)\]|\[(\d+)\](.*?)\[/\2\]', txt, flags=re.S):
            out.append(esc(txt[pos:m.start()])); pos = m.end()
            if m.group(1): out.append('<br>'); continue
            i = int(m.group(2)); ent = bid.get(i)
            if not ent: out.append(esc(m.group(3))); continue
            out.append(ent[1] + sub(m.group(3), ent[3]) + ent[2])
        out.append(esc(txt[pos:]))
        return ''.join(out)
    return sub(key_tr, maps)

def esc(t): return t.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')
def esca(t): return t.replace('&', '&amp;').replace('"', '&quot;').replace('<', '&lt;').replace('>', '&gt;')


# ---------------------------------------------------------------- rule-based translations
LBL = {
    'Počet míst': {'en': 'Seats', 'de': 'Sitzplätze', 'pl': 'Liczba miejsc'},
    'Motor': {'en': 'Engine', 'de': 'Motor', 'pl': 'Silnik'},
    'Koncepce': {'en': 'Layout', 'de': 'Konzept', 'pl': 'Układ'},
    'Rozměry vnější': {'en': 'External dimensions', 'de': 'Außenmaße', 'pl': 'Wymiary zewnętrzne'},
    'Nákladní prostor': {'en': 'Cargo space', 'de': 'Laderaum', 'pl': 'Przestrzeń ładunkowa'},
    'Celková hmotnost': {'en': 'Gross weight', 'de': 'Gesamtgewicht', 'pl': 'Masa całkowita'},
    'Převodovka': {'en': 'Transmission', 'de': 'Getriebe', 'pl': 'Skrzynia biegów'},
    'Výbava': {'en': 'Equipment', 'de': 'Ausstattung', 'pl': 'Wyposażenie'},
}
VAL = [  # longest first
    ('zadní pohon', {'en': 'rear-wheel drive', 'de': 'Hinterradantrieb', 'pl': 'napęd na tylną oś'}),
    ('přední pohon', {'en': 'front-wheel drive', 'de': 'Frontantrieb', 'pl': 'napęd na przednią oś'}),
    ('JEDNOMONTÁŽ', {'en': 'single tyres', 'de': 'Einzelbereifung', 'pl': 'pojedyncze ogumienie'}),
    ('dvoumontáž', {'en': 'dual tyres', 'de': 'Zwillingsbereifung', 'pl': 'ogumienie bliźniacze'}),
    ('manuální', {'en': 'manual', 'de': 'manuell', 'pl': 'manualna'}),
    ('šířka mezi blatníky', {'en': 'width between wheel arches', 'de': 'Breite zwischen den Radkästen', 'pl': 'szerokość między nadkolami'}),
    ('výška bočnic', {'en': 'side height', 'de': 'Bordwandhöhe', 'pl': 'wysokość burt'}),
    ('délka', {'en': 'length', 'de': 'Länge', 'pl': 'długość'}),
    ('šířka', {'en': 'width', 'de': 'Breite', 'pl': 'szerokość'}),
    ('výška', {'en': 'height', 'de': 'Höhe', 'pl': 'wysokość'}),
    ('ccm', {'en': 'cc', 'de': 'ccm', 'pl': 'cm³'}),
]
META = {'Počet přepravovaných osob': {'en': 'People carried', 'de': 'Beförderte Personen', 'pl': 'Liczba osób'},
        'Dojezd na rovině': {'en': 'Range on the flat (km)', 'de': 'Reichweite in der Ebene (km)', 'pl': 'Zasięg na płaskim (km)'},
        'Maximální rychlost': {'en': 'Max. speed (km/h)', 'de': 'Höchstgeschw. (km/h)', 'pl': 'Prędk. maks. (km/h)'},
        'Maximální zatížení': {'en': 'Max. load (kg)', 'de': 'Max. Zuladung (kg)', 'pl': 'Maks. obciążenie (kg)'}}
ROLES = [  # contact-block roles (cs → per language); longest first
    ('personalista a správce vozového parku', {'en': 'HR manager and fleet manager', 'de': 'Personalleiter und Fuhrparkverwalter', 'pl': 'specjalista ds. kadr i zarządca taboru'}),
    ('vedoucí dispečer MKD', {'en': 'head of international dispatch', 'de': 'Leiter der Disposition Internationaler Güterverkehr', 'pl': 'kierownik dyspozytorni transportu międzynarodowego'}),
    ('jednatel společnosti', {'en': 'managing director', 'de': 'Geschäftsführer', 'pl': 'dyrektor zarządzający'}),
    ('zástupce vedoucího', {'en': 'deputy manager', 'de': 'stellvertretender Leiter', 'pl': 'zastępca kierownika'}),
    ('vedoucí restaurace', {'en': 'restaurant manager', 'de': 'Restaurantleiterin', 'pl': 'kierowniczka restauracji'}),
    ('vedoucí pobočky', {'en': 'branch manager', 'de': 'Standortleiter', 'pl': 'kierownik oddziału'}),
    ('učitel autoškoly', {'en': 'driving school instructor', 'de': 'Fahrlehrer', 'pl': 'nauczyciel w szkole jazdy'}),
    ('vedoucí oboru', {'en': 'head of the field', 'de': 'Bereichsleiter', 'pl': 'kierownik kierunku'}),
    ('operátorka', {'en': 'receptionist', 'de': 'Empfangsmitarbeiterin', 'pl': 'recepcjonistka'}),
    ('personalista', {'en': 'HR manager', 'de': 'Personalleiter', 'pl': 'specjalista ds. kadr'}),
    ('technik', {'en': 'technician', 'de': 'Techniker', 'pl': 'diagnosta'}),
    ('vedoucí', {'en': 'manager', 'de': 'Leiter', 'pl': 'kierownik'}),
]
LABELS = {'tel:': {'en': 'phone:', 'de': 'Tel.:', 'pl': 'tel.:'}, 'GSM:': {'en': 'mobile:', 'de': 'Mobil:', 'pl': 'tel. kom.:'},
          'fax:': {'en': 'fax:', 'de': 'Fax:', 'pl': 'faks:'}, 'e-mail:': {'en': 'e-mail:', 'de': 'E-Mail:', 'pl': 'e-mail:'},
          'mob:': {'en': 'mobile:', 'de': 'Mobil:', 'pl': 'tel. kom.:'}, 'mobil:': {'en': 'mobile:', 'de': 'Mobil:', 'pl': 'tel. kom.:'}}
CURR = {'en': lambda n: f'CZK {n:,}', 'de': lambda n: f'{n:,}'.replace(',', '.') + ' CZK', 'pl': lambda n: f'{n:,}'.replace(',', '\u00a0') + ' CZK'}


def _loc_price(lang, txt):
    nums = [int(re.sub(r'\D', '', p)) for p in txt.split('/')]
    if lang == 'en': return 'CZK ' + ' / '.join(f'{n:,}' for n in nums)
    sep = '.' if lang == 'de' else '\u00a0'
    return ' / '.join(f'{n:,}'.replace(',', sep) for n in nums) + ' CZK'


def rule_tr(lang, k):
    """Translate regular, data-like strings without needing one dictionary entry per value."""
    m = re.fullmatch(r'(od )?(\d[\d ]*(?: / \d[\d ]*)?) Kč', k)
    if m:
        p = _loc_price(lang, m.group(2))
        return {'en': 'from ', 'de': 'ab ', 'pl': 'od '}[lang] + p if m.group(1) else p
    m = re.fullmatch(r'(.+?): (.*)', k)
    if m and m.group(1) in LBL:
        v = m.group(2)
        for cz, tr in VAL: v = v.replace(cz, tr[lang])
        return f'{LBL[m.group(1)][lang]}: {v}'
    m = re.fullmatch(r'\[1\]([\d\-]+)\[/1\](' + '|'.join(map(re.escape, META)) + ')', k)
    if m: return f'[1]{m.group(1)}[/1]{META[m.group(2)][lang]}'
    m = re.fullmatch(r'fotografie (\d+)', k)
    if m: return {'en': 'photo', 'de': 'Foto', 'pl': 'zdjęcie'}[lang] + ' ' + m.group(1)
    m = re.fullmatch(r'ŘP (\w+)', k)
    if m: return {'en': 'Licence ', 'de': 'FS ', 'pl': 'PJ '}[lang] + m.group(1)
    if re.fullmatch(r'(tel|fax|GSM|mob|mobil): [\d +]+', k):
        a, b = k.split(': ', 1); return f'{LABELS[a + ":"][lang]} {b}'
    if re.search(r'\[br\]', k) and re.search(r'\[1\][^\[]+\[/1\]\[br\]', k) and re.search(r'e-mail:|tel:|GSM:|fax:', k):
        out = k
        for cz, tr in ROLES:
            out = re.sub(r'(\[br\])' + re.escape(cz) + r'(?=\[br\])', lambda mm: mm.group(1) + tr[lang], out)
        for cz, tr in LABELS.items(): out = out.replace(cz, tr[lang])
        return out
    return None

ASSET = re.compile(r'\.[A-Za-z0-9]{2,5}($|[?#])')
def localize_links(s, lang):
    def fix_tag(m):
        tag = m.group(0)
        if 'data-nolang' in tag: return tag
        def fix(am):
            v = am.group(2)
            if not v.startswith('/') or v.startswith('//') or v.startswith(f'/{lang}/') or v == f'/{lang}': return am.group(0)
            if v.startswith('/assets/') or ASSET.search(v.split('#')[0]): return am.group(0)
            return f'{am.group(1)}="/{lang}{v if v != "/" else "/"}"'
        return re.sub(r'\b(href|action)="([^"]*)"', fix, tag)
    return re.sub(r'<a\b[^>]*>', fix_tag, s)

def apply():
    for lang in LANGS:
        d = json.load(open(os.path.join(I18N, f'{lang}.json'), encoding='utf-8'))
        miss = {}
        for f in page_files(lang):
            src = open(f, encoding='utf-8').read()
            tree = Tree(src); tree.feed(src)
            edits = []
            for a, b, key, maps in units(src, tree.root):
                k = key_norm(key)
                if k.startswith('<?'): continue
                tr = d.get(k)
                if tr is None:
                    pcs = pieces(k)
                    if len(pcs) > 1 and any(p in d for p in pcs): tr = ''.join(d.get(p, p) for p in pcs)
                if tr is None: tr = rule_tr(lang, k)
                if tr is None and re.fullmatch(r'[\d.\s–\-]+(Pondělí|Úterý|Středa|Čtvrtek|Pátek|Sobota|Neděle)?\s*(ZAVŘENO)?', k):
                    tr = k
                    for w in ('Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'): tr = tr.replace(w, d.get(w, w))
                    tr = tr.replace('ZAVŘENO', d.get('ZAVŘENO', 'ZAVŘENO'))
                if tr is None:
                    if LETTER.search(re.sub(r'\[/?\w+\]', '', k)): miss[k] = miss.get(k, 0) + 1
                    continue
                raw = src[a:b]
                lead = re.match(r'\s*', raw).group(0); trail = re.search(r'\s*$', raw).group(0)
                edits.append((a, b, lead + rebuild(tr, maps) + trail))
            for a, b, v, kind in attr_hits(src):
                n = norm_ws(v)
                if kind == 'desc' and n not in d and '__desc__' in d: edits.append((a, b, esca(d['__desc__']))); continue
                if n.endswith('…') and '__desc__' in d: edits.append((a, b, esca(d['__desc__']))); continue
                if n in d: edits.append((a, b, esca(d[n])))
                else:
                    parts = pieces(n)
                    if len(parts) > 1 and any((p in d) for p in parts):
                        edits.append((a, b, esca(''.join(d.get(p, p) for p in parts))))
            edits.sort(key=lambda e: e[0], reverse=True)
            last = None
            for a, b, t in edits:
                if last is not None and b > last: continue   # overlapping, skip
                src = src[:a] + t + src[b:]; last = a
            src = localize_links(src, lang)
            src = src.replace('<html lang="' + lang + '">', '<html lang="' + lang + '">')
            open(f, 'w', encoding='utf-8').write(src)
        if miss:
            json.dump(sorted(miss), open(os.path.join(I18N, f'missing_{lang}.json'), 'w'), ensure_ascii=False, indent=0)
        print(lang, 'hotovo; nepřeloženo:', len(miss))

if __name__ == '__main__':
    {'extract': extract, 'apply': apply}[sys.argv[1]]()
