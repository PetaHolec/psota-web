"""Sloučí řádky '<index v1>\\t<překlad>' z i18n/<lang>.txt (a případné 'k\\t...' z extra_<lang>.txt) do i18n/<lang>.json."""
import json, os, sys
D = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'i18n')
v1 = json.load(open(os.path.join(D, 'keys_v1.json'), encoding='utf-8'))
todo = json.load(open(os.path.join(D, 'todo.json'), encoding='utf-8')) if os.path.exists(os.path.join(D, 'todo.json')) else []
todo2 = json.load(open(os.path.join(D, 'todo2.json'), encoding='utf-8')) if os.path.exists(os.path.join(D, 'todo2.json')) else []
for lang in ('en', 'de', 'pl'):
    out = {}
    jp = os.path.join(D, f'{lang}.json')
    if os.path.exists(jp): out = json.load(open(jp, encoding='utf-8'))
    for fn, byindex in ((f'{lang}.txt', True), (f'x_{lang}.txt', False), (f'y_{lang}.txt', None)):
        p = os.path.join(D, fn)
        if not os.path.exists(p): continue
        for line in open(p, encoding='utf-8').read().split('\n'):
            if '\t' not in line: continue
            a, b = line.split('\t', 1)
            if byindex is True: out[v1[int(a)]] = b
            elif byindex is False: out[todo[int(a)]] = b
            else: out[todo2[int(a)]] = b
    json.dump(out, open(jp, 'w', encoding='utf-8'), ensure_ascii=False, indent=0)
    print(lang, len(out))
