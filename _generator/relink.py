"""Převede odkazy začínající '/' na relativní, aby web fungoval i z disku (file://) a z libovolné podsložky."""
import os, re, sys
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SKIP = {'_generator'}
def target(val, prefix):
    m = re.match(r'^([^?#]*)([?#].*)?$', val); path, rest = m.group(1), m.group(2) or ''
    p = path.lstrip('/')
    if re.search(r'\.[A-Za-z0-9]{2,5}$', p):           # soubor (css, js, jpg, pdf, xml…)
        return prefix + p + rest
    p = p.rstrip('/')
    return prefix + (p + '/index.html' if p else 'index.html') + rest
for dp, dn, fn in os.walk(ROOT):
    dn[:] = [d for d in dn if d not in SKIP and d != 'assets']
    for f in fn:
        if not f.endswith('.html') or f == '404.html': continue
        full = os.path.join(dp, f)
        rel = os.path.relpath(full, ROOT)
        depth = rel.count(os.sep)
        prefix = '../' * depth if depth else './'
        s = open(full, encoding='utf-8').read()
        sub = lambda m: f'{m.group(1)}="{target(m.group(2), prefix)}"'
        s = re.sub(r'\b(href|src)="(/(?!/)[^"]*)"', sub, s)
        s = re.sub(r"url\('(/(?!/)[^']*)'\)", lambda m: f"url('{target(m.group(1), prefix)}')", s)
        open(full, 'w', encoding='utf-8').write(s)
print('relinked')
