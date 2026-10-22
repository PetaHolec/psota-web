"""Builds the pages for the sections that used to link to external websites
(Servisy a mytí, Osobní doprava, Elektroauta) from the scraped source HTML and merges them into data/pages.json.

Usage:  python3 tools/build_ext_data.py <folder with ppl/, zds/, birdie/ source html>
"""
import html as H
import json
import os
import re
import sys

SRC = sys.argv[1] if len(sys.argv) > 1 else '.'
HERE = os.path.dirname(os.path.abspath(__file__))
PAGES = os.path.join(HERE, '..', 'data', 'pages.json')
sys.path.insert(0, SRC)
from txt import txt  # noqa: E402  (helper from the scraping folder)

pages = json.load(open(PAGES, encoding='utf-8'))
for k in [k for k in pages if k.startswith(('servisy__', 'osobni-doprava__')) or k in ('servisy', 'osobni-doprava')]:
    del pages[k]


def esc(s): return H.escape(s, quote=False)
def key(path): return path.strip('/').replace('/', '__')


NUM = r'(?:\d{1,3}(?: \d{3})+|\d+)'
PRICE_RE = re.compile(r'(?<![\d])(' + NUM + r')(?:,-|,–|-)?(?:\s*/\s*(' + NUM + r')(?:,-|,–|-)?)?\s*$')


def money(n): return f'{int(re.sub(r"[ ]", "", n)):,}'.replace(',', '\u00a0')


def split_price(line):
    line = line.replace('\xa0', ' ').strip()
    m = PRICE_RE.search(line)
    if not m or m.start() == 0: return None
    label = line[:m.start()].strip(' -–')
    if not re.search(r'[A-Za-zÀ-ž]', label): return None
    nums = [money(x) for x in m.groups() if x]
    return label, ' / '.join(nums) + '\u00a0Kč'


def price_rows(lines):
    rows = []
    for l in lines:
        r = split_price(l)
        if r: rows.append(r)
    return rows


def table(rows, head=None):
    out = '<table>'
    if head: out += '<thead><tr>' + ''.join(f'<th>{esc(h)}</th>' for h in head) + '</tr></thead>'
    out += '<tbody>' + ''.join('<tr>' + ''.join(f'<td>{c if c.startswith("<") else esc(c)}</td>' for c in r) + '</tr>' for r in rows) + '</tbody></table>'
    return out


def ul(items): return '<ul>' + ''.join(f'<li>{esc(i)}</li>' for i in items) + '</ul>'
def p_(t): return f'<p>{esc(t)}</p>'
def h3(t): return f'<h3>{esc(t)}</h3>'
def h2(t): return f'<h2>{esc(t)}</h2>'


def lines_of(path, start=None, end=None):
    ti, im, b = txt(os.path.join(SRC, path))
    if start:
        i = b.find(start)
        b = b[i + len(start):] if i >= 0 else b
    if end:
        b = b.split(end)[0]
    return ti, im, [l.strip() for l in b.split('\n') if l.strip()]


def page(path, title, body='', **kw):
    body = body.replace('/assets/images/', '/images/')
    d = {'slug': key(path), 'title': title, 'crumbs': [], 'body': body, 'gallery': kw.pop('gallery', []), 'info': kw.pop('info', {})}
    d.update(kw)
    pages[key(path)] = d
    return d


def gal(prefix, names): return [f'/images/ext/{prefix}/{n}' for n in names]


# ============================================================ SERVISY (P.P.L. AUTOCENTRUM)
PPL_HOURS = [['Pondělí', '07:00 - 18:00'], ['Úterý', '07:00 - 18:00'], ['Středa', '07:00 - 18:00'], ['Čtvrtek', '07:00 - 18:00'], ['Pátek', '07:00 - 18:00'], ['Sobota', '08:00 - 12:00']]
PPL_CONTACT = ('<p>Dobšická 3697/6, 669 02 Znojmo<br />tel: <a href="tel:+420515210551">+420 515 210 551</a><br />'
               'servis: <a href="mailto:wabco.servis@psota-znojmo.cz">wabco.servis@psota-znojmo.cz</a><br />'
               'pneu: <a href="mailto:pneu.servis@psota-znojmo.cz">pneu.servis@psota-znojmo.cz</a><br />'
               'mytí: <a href="mailto:myci.centrum@psota-znojmo.cz">myci.centrum@psota-znojmo.cz</a><br />IČ: 26241935 | DIČ: CZ26241935</p>')
PPL_INFO = {'logo': '/images/logo/logo_ppl.png', 'name': 'P.P.L. AUTOCENTRUM s.r.o.', 'contact': PPL_CONTACT, 'hours_title': 'Provozní doba', 'hours': PPL_HOURS}
PPL_IMG = 'ext/ppl/images/'
TOOL = 'media/com_twojtoolbox/'


def ppl_gallery(name):
    t = open(os.path.join(SRC, 'ppl', name + '.html'), encoding='utf-8').read()
    got = re.findall(r'href="https://pplautocentrum\.cz/media/com_twojtoolbox/([^"]+)"', t)
    return gal('ppl/media/com_twojtoolbox', list(dict.fromkeys(got)))


def ppl_page(path, name, title, body, hero, **kw):
    gl = kw.pop('gallery', None)
    return page(path, title, body, gallery=ppl_gallery(name) if gl is None else gl, info=kw.pop('info', PPL_INFO), hero=PPL_IMG + hero, **kw)


# ---- autoservis hub
ppl_page('servisy/autoservis', 'autoservis', 'Autoservis',
         p_('Provádíme servis osobních, užitkových, nákladních vozidel a autobusů všech značek a to v rozsahu:') +
         ul(['měření emisí diesel', 'příprava a provedení STK', 'diagnostika: TEXA, JALTEST, EASY Iveco, BREM BIE, PT BOX SOR, WABCO, KNORR-BREMSE, HALDEX',
             'seřízení geometrie laserovým paprskem FASEP', 'čištění a výměna DPF a SCR filtrů', 'opravy nezávislého topení značky Eberspaecher, Webasto',
             'autorizované opravy náprav a brzd BPW, SAF', 'opravy elektrické instalace u nákladních vozidel a autobusů',
             'opravy vozidel po nehodách – smlouvy s pojišťovnami: ČP, Generali, ČPP', 'výměny čelních skel u nákladních vozidel a autobusů']) +
         h3('Q-SERVICE') + p_('Mezinárodní síť profesionálních a nezávislých autoservisů, jichž jsme členem. I po dobu záruky vašeho nového automobilu můžete vyřídit opravu, údržbu nebo servis svého vozidla v nezávislém autoservisu a nepřicházíte tím o záruku u výrobce vozidla.') +
         h3('PICK-UP SERVICE') + p_('Pro ještě pohodlnější vyřízení servisních úkonů na vašem vozidle nabízíme možnost vyzvednutí vozu přímo u vás a jeho následné přistavení zpět na místo.'),
         'slide-servis-autocentrum-znojmo.jpg',
         cards=[{'href': '/servisy/autoservis/osobni-vozidla', 'title': 'Servis osobních vozidel', 'desc': 'Mechanické a elektrikářské práce, diagnostika.', 'img': 'ext/ppl/media/com_twojtoolbox/P1016239_copy1.jpg', 'icon': 'car'},
                {'href': '/servisy/autoservis/dodavkova-vozidla', 'title': 'Servis dodávkových vozidel', 'desc': 'Autorizovaný servis řady IVECO Daily.', 'img': 'ext/ppl/media/com_twojtoolbox/P1016243_copy1.jpg', 'icon': 'truck'},
                {'href': '/servisy/autoservis/nakladni-vozidla', 'title': 'Servis nákladních vozidel', 'desc': 'Dílna pro až 10 nákladních aut najednou.', 'img': 'ext/ppl/media/com_twojtoolbox/P1016255.JPG', 'icon': 'truck'},
                {'href': '/servisy/autoservis/overeni-tachografu', 'title': 'Ověření tachografů', 'desc': 'Analogové, digitální i inteligentní tachografy.', 'img': 'ext/ppl/media/com_twojtoolbox/P1026425.JPG', 'icon': 'clock'},
                {'href': '/servisy/autoservis/plneni-klimatizaci', 'title': 'Plnění klimatizací', 'desc': 'Opravy, čištění a plnění klimatizací.', 'img': 'ext/ppl/media/com_twojtoolbox/DSC_0100.JPG', 'icon': 'wrench'},
                {'href': '/servisy/autoservis/odtahova-a-asistencni-sluzba', 'title': 'Odtahová a asistenční služba', 'desc': 'Dodávky, nákladní auta, autobusy.', 'img': 'ext/ppl/media/com_twojtoolbox/odtahova_sluzba_01.JPG', 'icon': 'truck'},
                {'href': '/servisy/autoservis/objednani-na-servis', 'title': 'On-line objednání na servis', 'desc': 'Rezervujte si termín v pohodlí domova.', 'img': 'ext/ppl/media/com_twojtoolbox/P1016268-2.jpg', 'icon': 'check'}])


def service_price_page(slug, name, title, intro, tablename, notes, hero='slide-servis-autocentrum-znojmo.jpg', extra=''):
    _, _, lines = lines_of(f'ppl/{name}.html', start=f'Ceník {tablename}', end='Provozní doba')
    rows = [(a, b) for a, b in price_rows([l for l in lines if not l.startswith('Ceny jsou')])]
    return ppl_page(f'servisy/autoservis/{slug}', name, title, intro + h3(f'Ceník {tablename}') + table(rows, ['Služba', 'Cena']) + p_(notes) + extra, hero)


service_price_page('osobni-vozidla', 'autoservis__osobni-vozidla', 'Servis osobních vozidel',
                   p_('Naše dílny jsou nadměrné a dokážeme v nich opravovat až 10 náklaďáků najednou, vždy se ale najde místo i pro pár osobních automobilů. Přivezte k nám do servisu svůj osobní automobil a my se o něj profesionálně postaráme.'),
                   'servisu osobních vozidel', 'Ceny jsou v Kč bez DPH / vč. DPH.')
service_price_page('dodavkova-vozidla', 'autoservis__dodavkova-vozidla', 'Servis dodávkových vozidel',
                   p_('S dodávkami různých značek a typů k nám již řadu let jezdí živnostníci, firmy a veřejnosprávní organizace. Naše dlouholeté zkušenosti v oblasti servisu užitkových vozidel vyústily v autorizaci řady IVECO Daily.'),
                   'servisu dodávkových vozidel', 'Ceny jsou v Kč bez DPH.')
service_price_page('nakladni-vozidla', 'autoservis__nakladni-vozidla', 'Servis nákladních vozidel',
                   p_('Nadčasová technologie a prostorné uspořádání dílny zabezpečí opravu až 10 náklaďáků najednou. Víme, kolik stojí prostoj auta, proto se zaměřujeme na rychlost a preciznost prováděných prací.') +
                   p_('Máte vozidlo značky IVECO? Pak jste v těch nejlepších rukách! Jsme autorizovaným servisem IVECO.'),
                   'servisu nákladních vozidel', 'Ceny jsou v Kč bez DPH.')
service_price_page('overeni-tachografu', 'autoservis__overeni-tachografu', 'Ověření tachografů',
                   p_('Bez ověření tachografu se dnes neobejde žádné nákladní auto. Svěřte tuto práci zkušeným profesionálům a budete mít jistotu, že máte vše v pořádku. Provádíme servis, opravu a ověření tachografů starých analogových i inteligentních, nové generace.'),
                   'servisu tachografů', 'Ceny jsou v Kč a bez DPH.')
ppl_page('servisy/autoservis/plneni-klimatizaci', 'autoservis__plneni-klimatizaci', 'Plnění klimatizací',
         p_('V letních parnech nejvíce oceníte správně doplněnou a fungující klimatizaci. Provádíme opravy, čištění a plnění klimatizací ve všech kategoriích a značkách vozů. Pokud si necháte u nás udělat i další služby, máte vše pod jednou střechou.'),
         'slide-servis-autocentrum-znojmo.jpg')
ppl_page('servisy/autoservis/odtahova-a-asistencni-sluzba', 'autoservis__odtahova-a-asistencni-sluzba', 'Odtahová a asistenční služba',
         p_('Zajišťujeme odtahovou a asistenční službu pro dodávky, nákladní auta a autobusy.'), 'slide-servis-autocentrum-znojmo.jpg')
ppl_page('servisy/autoservis/objednani-na-servis', 'autoservis__objednani-na-servis', 'On-line objednání na servis',
         p_('Využijte možnost objednání servisu přes náš on-line rezervační systém. Pokud řešíte plánovanou výměnu olejů, filtrů, nebo potřebujete jakýkoliv servisní zásah, který není nutné řešit okamžitě, rezervujte si termín, který vám bude nejvíce vyhovovat pro přistavení vozidla do servisu.') +
         '<h3>Rezervace v&nbsp;3 krocích</h3><ol><li>Vyberte službu: přezutí kol (na vlastní disky), vyvážení kol, oprava defektu, doplnění klimatizace, kontrola úniku klimatizace, geometrie, seřízení světel.</li><li>Zvolte datum a čas.</li><li>Zadejte údaje o vozidle a potvrďte rezervaci.</li></ol>'
         '<p class="notice">Rezervační kalendář zatím není připojen. V rámci návrhu webu zde bude vložen systém pro on-line rezervace. Do té doby nás kontaktujte na tel. <a href="tel:+420515210551">+420 515 210 551</a>.</p>',
         'slide-servis-autocentrum-znojmo.jpg')

# ---- pneuservis
_, _, pl = lines_of('ppl/pneuservis.html', start='Ceník služeb pneuservisu', end='Ceny jsou v Kč')
ppl_page('servisy/pneuservis', 'pneuservis', 'Pneuservis',
         p_('Správné obutí vozu vám zabezpečí bezpečnou a tichou jízdu. Přezouváme vozidla osobní, dodávková, nákladní i autobusy. Doporučujeme kvalitní pneumatiky značek HANKOOK, MICHELIN, GOODYEAR, BRIDGESTONE, DUNLOP nebo BARUM, které si můžete zakoupit v naší firemní prodejně.') +
         p_('Pro rezervaci termínu pneuservisu můžete využít náš rezervační systém.') + h3('Ceník služeb pneuservisu') + table(price_rows(pl), ['Služba', 'Cena']) + p_('Ceny jsou v Kč a bez DPH.') +
         h3('Mobilní pneuservis dodávek a nákladních vozidel') + p_('S naším mobilním pneuservisem přijedeme za vámi a provedeme přezutí na vašem voze ve vaší garáži, nebo na parkovišti. Přezouvejte pohodlně, bez nutnosti návštěvy pneuservisu.'),
         'slide-pneuservis-autocentrum-znojmo.jpg')

# ---- mytí
_, _, ml = lines_of('ppl/myti-nakladnich-vozidel.html', start='Ceník služeb mycího centra:', end='Provozní doba')
body = (p_('V našem mycím centru se postaráme jak o exteriér, tak i interiér. Přijeďte se vším, co má kola, a my vám to umyjeme, a to ručně, nebo automaticky. Chcete, aby se vaše auto lesklo a mělo na nějaký čas ošetřený lak, potom si nechte nanést vosk. Co vše vám můžeme nabídnout, se dozvíte v ceníku níže.') +
        p_('Na dvou linkách myjeme ručně i automaticky: motorky, osobní, nákladní, návěsy, traktory i autobusy.'))
cur = None
buf = []


def flush():
    global buf
    if buf:
        body_parts.append(table(price_rows(buf), ['Služba', 'Cena']))
    buf = []


body_parts = []
for l in ml:
    if re.match(r'^[A-ZÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ ]{5,}$', l) and not split_price(l):
        flush(); body_parts.append(h2(l.capitalize()))
    elif split_price(l):
        buf.append(l)
    elif l.startswith('Ceny jsou'):
        flush(); body_parts.append(p_(l))
    elif len(l) > 70:
        flush(); body_parts.append(p_(l))
    else:
        flush(); body_parts.append(h3(l))
flush()
mytí_info = dict(PPL_INFO, hours_title='Provozní doba mycího centra', hours=[[d, '06:00 - 22:00'] for d in ['Pondělí', 'Úterý', 'Středa', 'Čtvrtek']] + [['Pátek', '06:00 - 24:00'], ['Sobota', '06:00 - 22:00'], ['Neděle', '06:00 - 22:00']])
ppl_page('servisy/myti-vozidel', 'myti-nakladnich-vozidel', 'Mytí vozidel – Truck myčka', body + ''.join(body_parts), 'slide-myti-vozidel-autocentrum-znojmo.jpg', info=mytí_info)

# ---- náhradní díly
ppl_page('servisy/prodej-nahradnich-dilu', 'prodej-nahradnich-dilu', 'Prodej náhradních dílů',
         p_('Neshánějte náhradní díly a příslušenství k autům jinde, využijte naší prodejny. Provádějte servis a nákup náhradních dílů pod jednou střechou, ušetříte čas i peníze.') +
         p_('U nás dostanete náhradní díly značek IVECO, MAN, Volvo, Renault a další. Co nemáme, objednáme.') +
         h3('Hydraulické hadice') + p_('Pro uživatele hydraulických hadic a komponentů zajistíme na počkání opravu nebo výrobu nového kusu.'),
         'slide-prodej-nahradnich-dilu-autocentrum-znojmo.jpg')

# ---- IVECO
IVECO_BODY_SERVICE = (p_('Víme, že stát se autorizovaným servisem není jednoduché a udržet si tuto pozici a dělat práci profesionálně dá hodně úsilí. My si tuto pozici držíme a děláme vše pro to, aby všichni uživatelé vozů IVECO v nás měli silného a spolehlivého partnera.') +
                      p_('Zaručujeme, že se o váš vůz postarají důkladně proškolení technici, kteří dodrží pracovní postup navržený IVECO. Technici jsou také od IVECO pravidelně školeni a přezkušováni. Váš vůz umíme přesně diagnostikovat pomocí moderních přístrojů. Pro opravy používáme nářadí a přípravky požadované výrobcem, což umožňuje provést práci rychleji, kvalitněji a tedy i levněji. Díky dodržování předepsaných pracovních postupů se eliminuje zdlouhavé hledání řešení a díky tomu dosahujeme rychlé opravy vašeho vozu.') +
                      h3('Servis IVECO, IVECO BUS a autobusy SOR') + ul(['záruční servis', 'pozáruční servis', 'pravidelné servisní údržby a kontroly', 'diagnostické zařízení JALTEST, IVECO', 'sociální zázemí včetně internetu a občerstvení', 'karosářské i lakýrnické práce']))
IVECO_CONTACT = (h3('Kontakt na obchod') + '<p>V případě jakéhokoliv dotazu nás můžete kontaktovat telefonicky na těchto číslech: <a href="tel:+420515210551">+420 515 210 551</a>, <a href="tel:+420608108478">+420 608 108 478</a></p>')
ppl_page('servisy/iveco', 'iveco', 'IVECO',
         p_('Značka IVECO se pro nás a naše zákazníky servisu stala symbolem kvality a spolehlivosti. Proto jsme se rozhodli šířit dobré povědomí této renovované značky ve Znojmě a okolí.') + p_('Jsme autorizovaným prodejcem a servisem IVECO.'),
         'slide-iveco-autocentrum-znojmo.jpg',
         cards=[{'href': '/servisy/iveco/prodej-uzitkovych-vozidel', 'title': 'Prodej užitkových vozidel', 'desc': 'IVECO Daily – dodávky pro každý úkol.', 'img': 'ext/ppl/media/com_twojtoolbox/iveco-daily.jpg', 'icon': 'truck'},
                {'href': '/servisy/iveco/prodej-nakladnich-automobilu', 'title': 'Prodej nákladních automobilů', 'desc': 'Eurocargo, S-WAY, Stralis, Stralis NP.', 'img': 'ext/ppl/media/com_twojtoolbox/iveco-stralis.jpg', 'icon': 'truck'},
                {'href': '/servisy/iveco/autorizovany-servis', 'title': 'Autorizovaný servis', 'desc': 'Záruční i pozáruční servis IVECO.', 'img': 'ext/ppl/media/com_twojtoolbox/P1016240.JPG', 'icon': 'wrench'}])
ppl_page('servisy/iveco/prodej-uzitkovych-vozidel', 'iveco__prodej-uzitkovych-vozidel', 'Prodej užitkových vozidel IVECO',
         p_('Naše dlouholeté a velmi dobré zkušenosti s dodávkami značky IVECO Daily, jak v oblasti využití, tak i v počtu neplánovaných servisních zásahů, nás ubezpečily o tom, že právě tato značka je jedničkou na trhu v kategorii užitkových vozů, proto jsme se stali autorizovaným servisem a následně i prodejcem vozů IVECO.') +
         p_('IVECO se zabývá výrobou lehkých, středních a těžkých užitkových vozidel. V kategorii užitkových vozidel slaví velký úspěch model DAILY s rámovou konstrukcí a objemem nákladového prostoru až 20 m³, který nabízí nejvyšší užitečné zatížení ve své třídě – více než 4 000 kg. Právě pro svoji spolehlivost, variabilitu provedení a užitné vlastnosti patří k nejrozšířenějším značkám, které brázdí silnice po celém světě.') +
         h3('Nekonečné příležitosti – dodávka DAILY pro ambice bez hranic') +
         p_('DAILY je nejuniverzálnějším vozidlem ve své třídě. Široká nabídka modelů a možností karoserie otevírá nepřeberné množství příležitostí: bez ohledu na váš úkol – ať už se jedná o rozvoz s mnoha zastávkami, například doručování zásilek nebo kurýrní službu, nebo přepravu těžších nákladů, například stěhování nábytku – najdete a přizpůsobíte si DAILY tak, aby dokonale vyhovovala vašim provozním a obchodním požadavkům.') + IVECO_CONTACT,
         'slide-iveco-autocentrum-znojmo.jpg')
ppl_page('servisy/iveco/prodej-nakladnich-automobilu', 'iveco__prodej-nakladnich-automobilu', 'Prodej nákladních automobilů IVECO',
         p_('V segmentu nákladních vozidel se zaměřujeme hlavně na řidiči oblíbenou střední řadu 6,5 – 18 tun IVECO EUROCARGO, kde dodáváme širokou škálu nástaveb od skříně, sklopky až po speciální. Konfiguraci auta nastavíme přesně podle vašich požadavků a potřeb.') +
         p_('V naší nabídce ale nechybí ani řada s těžkou tonáží 16 a více tun: IVECO S-WAY, IVECO STRALIS, IVECO STRALIS NP.') +
         p_('Vyberte si to IVECO, které potřebujete pro své podnikání, a my vám jej přivezeme před váš práh.') + IVECO_CONTACT,
         'slide-iveco-autocentrum-znojmo.jpg')
ppl_page('servisy/iveco/autorizovany-servis', 'iveco__autorizovany-servis', 'Autorizovaný servis IVECO', IVECO_BODY_SERVICE, 'slide-iveco-autocentrum-znojmo.jpg')

# ---- půjčovna dodávek
t = open(os.path.join(SRC, 'ppl', 'pujcovna-dodavek.html'), encoding='utf-8').read()
ti, im, b = txt(os.path.join(SRC, 'ppl', 'pujcovna-dodavek.html'))
seg = b[b.find('Nabídka vozů'):b.find('Ceník a podmínky')]
blocks = re.split(r'\n (?=[^\n]+\n \d[\d ]* Kč\n [^\n]+\n ŘP \w+\n)', seg)
vehicles = []
for blk in blocks[1:]:
    m = re.match(r'([^\n]+)\n (\d[\d ]*) Kč\n ([^\n]+)\n ŘP (\w+)', blk.strip())
    if not m: continue
    name = m.group(1).strip()
    desc = re.search(r'✍️ ([^\n]+)', blk)
    specs = re.findall(r'\n?((?:👥|⚙️|🔧|📏|📦|✨|🔄|⚖️|🌟) [^\n]+)', blk)
    pos = t.find(name)
    img = re.search(r'/obrazky/(veh-\d+/[^"\'?\s]+\.(?:jpe?g|png))', t[pos:pos + 6000], re.I) if pos >= 0 else None
    vehicles.append({'name': name, 'price': m.group(2).replace(' ', ''), 'plate': m.group(3), 'rp': m.group(4), 'desc': desc.group(1) if desc else '', 'specs': [re.sub(r'^\S+ ', '', s) for s in specs if not s.startswith('📦 ')], 'img': 'ext/ppl/obrazky/' + img.group(1) if img else None})
fleet = '<div class="fleet">'
for v in vehicles:
    fleet += ('<article class="vcard">' + (f'<a href="/assets/images/{v["img"]}" data-lightbox="fleet"><img src="/assets/images/{v["img"]}" alt="{esc(v["name"])}" loading="lazy"></a>' if v['img'] else '') +
              f'<div class="vcard__body"><h3>{esc(v["name"])}</h3><p class="vcard__price"><b>od {int(v["price"]):,} Kč</b> <span>/ den vč. DPH</span></p>'.replace(',', ' ') +
              f'<p class="vcard__tags"><span>ŘP {esc(v["rp"])}</span><span>{esc(v["plate"])}</span></p><p>{esc(v["desc"])}</p>' +
              ul([s for s in v['specs'] if s and not s.startswith('Výbava')][:6]) + '</div></article>')
fleet += '</div>'
ppl_page('servisy/pujcovna-dodavek', 'pujcovna-dodavek', 'Půjčovna dodávek',
         p_('Dodávky i 8místný minibus. Rezervujte on-line kdykoliv – klidně o půlnoci. Bez registrace a bez čekání na telefon. V ceně je 500 km na den, kauce 5 000 Kč, nad limit 3,50 Kč/km. Půjčujeme vozidla značek Iveco, Mercedes, DAF a Ford.') +
         '<div class="facts"><div class="fact"><b>500 km</b><span>denní limit v ceně</span></div><div class="fact"><b>5 000 Kč</b><span>kauce</span></div><div class="fact"><b>3,50 Kč</b><span>za km nad limit</span></div><div class="fact"><b>24/7</b><span>rezervace on-line</span></div></div>' +
         h2('Nabídka vozů') + fleet +
         h2('Ceník a podmínky') + ul(['Všechny uvedené ceny jsou včetně DPH 21 %.', 'Limit: 500 km/den, nadlimit 3,50 Kč/km.', 'Kauce: 5 000 Kč (dle typu vozidla).', 'Rezervace: +420 602 748 560, +420 515 210 551 nebo on-line.']) +
         h3('Vyzvednutí a vrácení') + table([['Vyzvednutí', 'všední dny od 07:59, sobota od 08:15'], ['Vrácení', 'následující den do 07:00 (bez dalších poplatků), sobota do 08:30']]) +
         h3('Co je k tomu potřeba') + ul(['Platné řidičské oprávnění pro zvolené vozidlo.', 'Složení kauce dle typu vozidla.', 'Vozidlo vrátit ve stavu převzetí, s plnou nádrží a čisté.']) +
         '<p class="notice">Rezervační kalendář a AI poradce pro výběr vozidla provozuje samostatný systém půjčovny. V tomto návrhu je odkaz na něj: <a href="https://dodavkyznojmo.cz" target="_blank" rel="noopener">dodavkyznojmo.cz</a>.</p>',
         'slide-autocentrum-znojmo-autopujcovna-nakladnich-vozidel.jpg', info=dict(PPL_INFO, contact=PPL_CONTACT.replace('wabco.servis@psota-znojmo.cz', 'info@dodavkyznojmo.cz').replace('mailto:wabco.servis@psota-znojmo.cz', 'mailto:info@dodavkyznojmo.cz')), gallery=[])
pages['servisy__pujcovna-dodavek']['hero'] = PPL_IMG + 'slide-autocentrum-znojmo-autopujcovna-nakladnich-vozidel.jpg'

# ---- aktuality servisů
art = []
for n in ['aktuality__43-pujcovna-dodavek-a-nakladnich-vozidel', 'aktuality__42-pujcovna-dodavek-znojmo', 'aktuality__39-iveco-daily-jerab-na-kontejneru-samostatny-kontejner', 'aktuality__38-ze-znojma-az-do-teplic']:
    ti, im, b = txt(os.path.join(SRC, 'ppl', n + '.html'))
    i = b.find('AKTUALITY\n')
    ls = [l.strip() for l in b[i + 10:].split('Hlavní nabídka')[0].split('\n') if l.strip()]
    art.append((ls[0], ls[1:]))
dates = {'43': '30. 5. 2022', '42': '30. 5. 2022', '39': '10. 12. 2021', '38': '19. 11. 2021'}
body = ''
for (title, ls), d in zip(art, dates.values()):
    body += f'<h3>{esc(title)}</h3><p class="muted small">{d}</p>'
    if len(ls) > 2 and ls[0].startswith('NABÍDKA'):
        body += p_('Nabídka vozů:') + ul(ls[1:])
    else:
        body += ''.join(p_(x) for x in ls)
ppl_page('servisy/aktuality', 'aktuality__42-pujcovna-dodavek-znojmo', 'Aktuality ze společnosti', body, 'slide-autocentrum-znojmo-autoservis-servis-nakladnich-aut.jpg')
pages['servisy__aktuality']['gallery'] = []

# ---- kontakty (náš tým)
def person(name, role, tel=None, gsm=None, mail=None, photo=None):
    return {'name': name, 'role': role, 'tel': [f'tel: {tel}'] if tel else [] + ([f'GSM: {gsm}'] if gsm else []), 'email': mail, 'photo': photo, 'other': []}


def P(name, role, tel='', gsm='', mail=None, photo=None):
    tl = []
    if tel: tl.append(f'tel: {tel}')
    if gsm: tl.append(f'GSM: {gsm}')
    return {'name': name, 'role': role, 'tel': tl, 'email': mail, 'photo': photo, 'other': []}


Z = '/images/ext/ppl/images/zamestnanci/'
ppl_page('servisy/kontakty', 'nas-tym__kontakty', 'Náš tým', '', 'slide-nas-tym-autocentrum-znojmo.jpg',
         people=[{'title': 'Vedení společnosti', 'people': [P('Pavel Psota', 'jednatel společnosti', '+420 515 210 545', '+420 602 582 204', None, Z + 'psota_pavel_120.jpg')]},
                 {'title': 'Prodej', 'people': [P('Rostislav Ištvánek', 'obchodní manažer', '+420 515 210 551', '+420 608 108 478', None, Z + 'istvanek_rostislav_120.jpg')]},
                 {'title': 'Příjem zakázek a prodej náhradních dílů', 'people': [P('Jan Kopečný', 'přijímací technik', '+420 515 210 551', '+420 602 748 560', 'jan.kopecny@psota-znojmo.cz', Z + 'kopecny_jan_120.jpg'),
                                                                                  P('Pavel Psota ml.', 'přijímací technik', '+420 515 210 551', '+420 602 332 988', 'pavel.psota.ml@psota-znojmo.cz', Z + 'psota_pavel_ml_120.jpg'),
                                                                                  P('Michal Worbis', 'vedoucí pneuservisu', '+420 515 210 551', '', 'pneu.servis@psota-znojmo.cz', Z + 'worbis_michal_120.jpg')]},
                 {'title': 'Mycí centrum', 'people': [P('Mycí centrum', 'vedoucí mycího centra', '', '+420 602 542 528', 'myci.centrum@psota-znojmo.cz', None)]},
                 {'title': 'Ekonomický úsek', 'people': [P('Lenka Vítková', 'účetní P.P.L. AUTOCENTRUM', '+420 515 210 540', '+420 602 513 243', 'lenka.vitkova@psota-znojmo.cz', Z + 'vitkova_lenka_120_v2.jpg')]}])
pages['servisy__kontakty']['gallery'] = []

# ============================================================ ELEKTROAUTA (BirdieCar)
BC = 'ext/birdie/'
BIRDIE_INFO = {'logo': '/images/ext/birdie/images/logo.png', 'name': 'P.P.L. AUTOCENTRUM s.r.o.',
               'contact': '<p>Dobšická 3697/6, 669 02 Znojmo<br />tel: <a href="tel:+420515210551">+420 515 210 551</a><br />e-mail: <a href="mailto:info@birdiecar-elektroauta.cz">info@birdiecar-elektroauta.cz</a><br />IČ: 26241935 | DIČ: CZ26241935</p>',
               'hours_title': 'Provozní doba', 'hours': [['Pondělí', '07:00 - 20:00'], ['Úterý', '07:00 - 20:00'], ['Středa', '07:00 - 20:00'], ['Čtvrtek', '07:00 - 20:00'], ['Pátek', '07:00 - 20:00'], ['Sobota', '08:00 - 14:00']]}
CATS = [('preprava-osob', 'Přeprava osob', 'user'), ('nakladni', 'Nákladní', 'truck'), ('autobusy', 'Autobusy', 'bus'), ('terenni', 'Terénní', 'car'), ('specialni', 'Speciální', 'wrench')]
cat_models = {}
cat_text = {}
for slug, name, ic in CATS:
    ti, im, b = txt(os.path.join(SRC, 'birdie', f'modely__{slug}.html'))
    i = b.find(f'{name} \n', b.find('Titulní stránka'))
    ls = [l.strip() for l in b[b.find('Titulní stránka'):].split('\n') if l.strip()]
    # description = lines between the second occurrence of the title and "Seřadit dle"
    try:
        idxs = [k for k, l in enumerate(ls) if l == name]
        s = idxs[1] if len(idxs) > 1 else idxs[0]
        e = ls.index('Seřadit dle')
        cat_text[slug] = [l for l in ls[s + 1:e] if not l.startswith('[[')]
    except Exception:
        cat_text[slug] = []
    cat_models[slug] = []
files = sorted(f for f in os.listdir(os.path.join(SRC, 'birdie')) if re.match(r'modely__[a-z-]+__\d+-[^_]+\.html$', f))
for f in files:
    cat, mslug = re.match(r'modely__([a-z-]+)__(.+)\.html', f).groups()
    ti, im, b = txt(os.path.join(SRC, 'birdie', f))
    raw = b[b.find('Titulní stránka'):]
    lines = raw.split('\n')
    name = [l.strip() for l in lines if l.strip()][4] if False else None
    t = open(os.path.join(SRC, 'birdie', f), encoding='utf-8').read()
    mm = re.search(r'<h1[^>]*>\s*(.*?)\s*</h1>', t, re.S)
    name = ti.split(' - ', 1)[1].strip() if ' - ' in ti else mslug
    i = raw.find('Parametry')
    j = raw.find('Přílohy') if 'Přílohy' in raw else raw.find('P.P.L. AUTOCENTRUM')
    pre = [l.strip() for l in raw[:i].split('\n') if l.strip()]
    desc = pre[-1] if pre else ''
    groups, cur, curg = {}, None, None
    for l in raw[i:j].split('\n'):
        if not l.strip(): continue
        if l.strip() in ('Parametry', 'Motor', 'Rozměry') and not l.endswith(' '):
            curg = l.strip(); groups[curg] = []; cur = None; continue
        if curg is None: continue
        if l.endswith(' ') and cur is None: cur = l.strip()
        elif cur is not None: groups[curg].append((cur, l.strip())); cur = None
        else: cur = l.strip()
    base = mslug.split('-', 1)[1]
    imgs = sorted(set(re.findall(r'djcatalog2/images/item/0/([^"\s]+?)_[ft]\.jpg', t)))
    imgs = [x for x in imgs if not x.endswith('_l')]
    main = [x for x in imgs if '.' not in x]
    extras = [x for x in imgs if '.' in x]
    cat_models[cat].append({'slug': mslug, 'name': name, 'desc': desc, 'groups': groups, 'main': main[0] if main else None, 'extras': extras})
    body = p_(desc)
    for g, rows in groups.items():
        body += h3({'Parametry': 'Parametry', 'Motor': 'Motor a pohon', 'Rozměry': 'Rozměry'}[g]) + table(rows)
    body += '<p class="muted small">Technický list ve formátu PDF zašleme na vyžádání.</p>'
    gal_files = [f'ext/birdie/media/djcatalog2/images/item/0/{x}_f.jpg' for x in ([main[0]] if main else []) + extras]
    page(f'servisy/elektroauta/modely/{cat}/{mslug}', name, body, gallery=['/images/' + g for g in gal_files], info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-02-novy.jpg')

for slug, name, ic in CATS:
    cards = []
    for m in cat_models[slug]:
        g = m['groups'].get('Parametry', [])
        meta = [[k.split(' (')[0], v] for k, v in g if k.startswith(('Počet', 'Dojezd', 'Maximální rychlost', 'Maximální zatížení'))]
        cards.append({'href': f'/servisy/elektroauta/modely/{slug}/{m["slug"]}', 'title': m['name'], 'desc': m['desc'], 'meta': meta,
                      'img': f'ext/birdie/media/djcatalog2/images/item/0/{m["main"]}_l.jpg' if m['main'] else None, 'icon': ic})
    page(f'servisy/elektroauta/modely/{slug}', name, ''.join(p_(x) for x in cat_text[slug]), cards=cards, info=BIRDIE_INFO,
         hero=BC + f'media/djcatalog2/images/category/0/{slug}_l.jpg')

page('servisy/elektroauta/modely', 'Modely', p_('V nabídce máme 28 různých modelů elektrických vozidel BirdieCar: osobní, nákladní, pro přepravu osob, terénní nebo například se sklopnou korbou. Vyberte si kategorii.'),
     cards=[{'href': f'/servisy/elektroauta/modely/{s}', 'title': n, 'desc': (cat_text[s][0] if cat_text[s] else ''), 'img': f'ext/birdie/media/djcatalog2/images/category/0/{s}_l.jpg', 'icon': ic} for s, n, ic in CATS],
     info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-03-novy.jpg')

SHORT = {'preprava-osob': 'Elektroauta pro 2 až 29 osob: osobní vozy, vyhlídkové vozy i autobusy.', 'nakladni': 'Levná a čistá přeprava nákladu od 350 do 2 000 kg.', 'autobusy': 'Vyhlídkové autobusy pro 14 až 29 osob s nulovými emisemi.', 'terenni': 'Terénní vozidla pro těžký terén, lesy a chráněné oblasti.', 'specialni': 'Sklopky, elektrický traktor a další speciální vozidla.'}
page('servisy/elektroauta', 'Elektroauta BirdieCar',
     p_('Používání elektromobilů je již nejlepším dostupným řešením v určitých oblastech mobility. Naše společnost se zaměřuje na tuto oblast a zabývá se především dovozem, montáží a prodejem nových elektrických osobních a lehkých užitkových vozidel i průmyslových elektrických vozidel. Díky našemu domácímu centru a naší jedinečné prodejní síti poskytujeme našim zákazníkům celou řadu služeb.') +
     '<div class="facts"><div class="fact"><b>28</b><span>různých modelů</span></div><div class="fact"><b>100 km</b><span>maximální dojezd</span></div><div class="fact"><b>2 000 kg</b><span>nosnost</span></div><div class="fact"><b>45 km/h</b><span>rychlost</span></div></div>' +
     h2('Proč elektroauta BirdieCar?') +
     '<div class="why">' + ''.join(f'<div class="why__item"><h3>{esc(t)}</h3><p>{esc(d)}</p></div>' for t, d in [
         ('Nabíjení', 'BirdieCar lze bez problému nabíjet z jakékoliv zásuvky na 230 V a to zapojením do palubní nabíječky, případně nabíječkou automobilů.'),
         ('Provozní náklady', 'Náklady na „palivo“ se pohybují kolem 25 Kč na 100 km, což je nesrovnatelné s vozidly se spalovacími motory.'),
         ('Běžný provoz', 'Stejně jako na automobily, potřebujete řidičský průkaz skupiny B a technickou kontrolu každé 4 roky.'),
         ('Obrovský výkon', 'Osobní doprava od 2 do 29 osob, nákladní doprava do 2 000 kg nákladu a dojezd na jedno nabití až 100 km.'),
         ('Žádné znečištění', 'Naše elektromobily produkují nulové emise, takže je možné je použít v chráněných centrech měst nebo v interiéru.'),
         ('Pořizovací náklady', 'Díky přímému zastoupení výrobce vám garantujeme nejnižší cenu na českém trhu. Kontaktujte nás pro individuální cenovou nabídku.')]) + '</div>' +
     h2('Kategorie modelů'),
     cards=[{'href': f'/servisy/elektroauta/modely/{s}', 'title': n, 'desc': SHORT[s], 'img': f'ext/birdie/media/djcatalog2/images/category/0/{s}_l.jpg', 'icon': ic} for s, n, ic in CATS],
     info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-01-novy.jpg')

page('servisy/elektroauta/o-nas', 'O značce BirdieCar',
     p_('Birdiecar-elektroauta je značka elektrických automobilů, které slouží pro přepravu osob, jak na silnici, tak i v terénu. Umí vozit i náklad, a to v menším rozsahu, např. cestovní kufr, velmi ladně si ale dokážou poradit i s větší tonáží, kdy na korbu malého náklaďáku můžete naložit např. 2 tuny sypkého materiálu, pokud se rozhodnete pro variantu se sklopnou korbou. Jsou variabilní a nabízejí přesně ty varianty vozů, které budou vyhovovat právě vašim potřebám.') +
     p_('P.P.L. AUTOCENTRUM s.r.o., Dobšická 6, 669 02 Znojmo je výhradním importérem Birdiecar-elektroauta do České republiky a zároveň je nedílnou součástí významného a silného partnera v oblasti dopravy, servisu a služeb pro motoristy společnosti PSOTA HOLDING.') +
     h3('Showroom a zkušební jízdy') + p_('V areálu PSOTA HOLDING Dobšická 6, 669 02 Znojmo pro vás máme otevřený showroom, kde si můžete Birdiecar-elektroauta nejen prohlédnout, ale i vyzkoušet, jak se v nich sedí a jezdí. Jízda s Birdiecar-elektroauta je skutečným požitkem a proškolený personál se těší na vaši návštěvu.') +
     h3('Servis') + p_('Birdiecar-elektroauta se vyznačují velmi malou potřebou servisu, ale ani tato vozidla se po čase užívání bez něho neobejdou. Naše společnost zajišťuje záruční i pozáruční servis vozů Birdiecar-elektroauta a to ve svých nadčasově vybavených servisních dílnách. Pod jednou střechou se o vás postaráme od koupě tohoto tichého společníka a budeme vám kdykoliv k dispozici, po celou dobu jeho levných služeb.') +
     h3('Doprava až k vám') + p_('Vámi vybrané a vyzkoušené Birdiecar-elektroauto převezeme i do toho nejvzdálenějšího koutu Česka. Vzhledem k malým rozměrům a nízké hmotnosti většiny typů si snadno převezete Birdiecar-elektroauto za pomoci vlastní lehké dopravy.'),
     info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-04-novy.jpg')
page('servisy/elektroauta/servis', 'Servis elektroaut',
     p_('Jako výhradní distributoři a prodejci značky Birdiecar-elektroauta jsme si plně vědomi, že pro každého zákazníka, který si koupí jakékoliv auto, je velmi důležité zázemí prodejce a hlavně pak servis. Samozřejmostí je to, že poskytujeme záruční i pozáruční servis, který provádějí naši proškolení technici.') +
     p_('Birdiecar-elektroauta se vyznačují velmi malou potřebou servisu, ale ani tato vozidla se po čase užívání bez něho neobejdou, proto pro nás prodejem vztah se zákazníkem nekončí, ale naopak začíná. Servis zajišťujeme ve specializovaných a nadčasově vybavených pracovištích, které provozujeme v areálu PSOTA HOLDING na adrese Dobšická 6, 669 02 Znojmo.') +
     h3('Máte to k nám daleko, nebo nemůžete přijet s vaším vozidlem do servisu?') + p_('I na to jsme připraveni! Naši technici přijedou na místo, kde Birdiecar elektroauto garážujete, nebo parkujete, a o vše se profesionálně postarají.') +
     p_('Zakoupili jste si, anebo plánujete zakoupení u některého z našich prodejců? Pak počítejte se stejným profesionálním přístupem a zajištěním servisních služeb.'),
     info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-05-novy.jpg')
page('servisy/elektroauta/reference', 'Reference',
     p_('Birdiecar-elektroauta již jezdí na několika místech v České republice a postupně přibývají další. Spokojené ohlasy dostáváme od všech uživatelů.') +
     p_('Například v Domově pro seniory v Jevišovicích najezdil Birdiecar po areálu zařízení 15 000 km za dva roky provozu, a to bez jediného servisního zásahu. Uživatelé si pochvalují jednoduchost obsluhy, nízké provozní náklady a bezúdržbový provoz.'),
     gallery=[f'/images/ext/birdie/images/igallery/resized/1-100/birdiecar-elektroauto-reference-jevisovice-00{i}-{i + 4}-1600-1000-80.jpg' for i in (1, 2, 3, 4)], info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-06-novy.jpg')
page('servisy/elektroauta/prodejci', 'Dealerská síť', p_('Dealerskou síť elektroaut BirdieCar připravujeme. Do té doby je prodej a servis zajišťován přímo v areálu PSOTA HOLDING ve Znojmě.'), info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-01-novy.jpg')
page('servisy/elektroauta/cenntro', 'Elektroauta Cenntro', p_('Jsme autorizovaným prodejcem elektrických užitkových vozidel značky Cenntro. Informace o modelech a cenách pro vás připravujeme – kontaktujte prosím náš obchod.') + p_('Obchodní manažer: Rostislav Ištvánek, tel. +420 515 210 551, GSM +420 608 108 478.'), info=PPL_INFO, hero=BC + 'images/birdievar-elektroauta-banner-02-novy.jpg')
art = [('BC L7e-CU', 'Novinkou ve značce Birdiecar elektroauta je model BC L7e-CU, který svým provedením zaujme hned na první pohled. Díky různým možnostem provedení nástavby lze toto vozidlo použít ke spoustě účelů, např. rozvoz obědů, rozvoz pizzy, pracovní stroj pro zaměstnance obecních úřadů, ale i jako druhý rodinný vůz na nákupy. Fantazii se meze nekladou.'),
       ('Osobní auto až pro 6 osob', 'Naše elektrické osobní vozidlo BC M6 + 2 může pohodlně vozit 6 + 2 osoby. Jeho maximální rychlost je 25–45 km/h. Jeho dojezd je 70–100 km.'),
       ('Elektrická terénní dodávka', 'Naše elektrická terénní dodávka BC 2 + CARGO XL CC je terénní ďábel. Vhodné pro 2 osoby a 450 kg. Vydrží i ty nejnáročnější podmínky, není počasí, které by ho chytilo. Má maximální rychlost 40 km/h, dojezd 100 km a maximální nosnost 450 kg.'),
       ('Autobus pro 23 osob', 'BC MB23 je elektrický vyhlídkový autobus pro 23 osob. Na jedno nabití dokáže ujet až 100 km.'),
       ('Nákladní auto model BC-5', 'BC 5 + Cargo může přepravovat 5 osob. Má maximální nosnost 1120 kg, dojezd 100 km a maximální rychlost 40 km/h.')]
page('servisy/elektroauta/aktuality', 'Aktuality BirdieCar', ''.join(h3(t) + p_(d) for t, d in art), info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-03-novy.jpg')
page('servisy/elektroauta/kontakty', 'Kontakty – elektroauta', '', info=BIRDIE_INFO, hero=BC + 'images/birdievar-elektroauta-banner-04-novy.jpg',
     people=[{'title': 'Prodej', 'people': [P('Rostislav Ištvánek', 'obchodní manažer', '+420 515 210 551', '+420 608 108 478', None, Z + 'istvanek_rostislav_120.jpg')]},
             {'title': 'Servis', 'people': [P('Pavel Psota ml.', 'přijímací technik', '+420 515 210 551', '+420 602 332 988', 'pavel.psota.ml@psota-znojmo.cz', Z + 'psota_pavel_ml_120.jpg')]}])

# ---- servisy root
page('servisy', 'Servisy a mytí',
     p_('Jsme autorizovaným prodejcem a servisem vozů IVECO, elektrovozidel BirdieCar a Cenntro. Postaráme se o servis a pneuservis osobních, dodávkových, nákladních vozidel a autobusů, včetně oprav tachografů a doplnění klimatizací. Provozujeme vlastní mycí centrum, prodejnu náhradních dílů IVECO, Renault, Volvo, MAN a půjčovnu užitkových vozidel značek Iveco a Mercedes.') +
     p_('Točíme se kolem aut… Všechno na jednom místě v areálu AUTOCENTRUM ve Znojmě.'),
     info=PPL_INFO, hero=PPL_IMG + 'slide-autocentrum-znojmo-autoservis-servis-nakladnich-aut.jpg',
     cards=[{'href': '/servisy/autoservis', 'title': 'Autoservis', 'desc': 'Opravy osobních i nákladních vozidel všech značek, autorizovaný servis IVECO.', 'img': 'ext/ppl/media/com_twojtoolbox/P1016255.JPG', 'icon': 'wrench'},
            {'href': '/servisy/pneuservis', 'title': 'Pneuservis', 'desc': 'Osobní auta, nákladní auta, autobusy, traktory. HANKOOK, MICHELIN, GOODYEAR, BARUM.', 'img': 'ext/ppl/media/com_twojtoolbox/P1016268-2.jpg', 'icon': 'car'},
            {'href': '/servisy/myti-vozidel', 'title': 'Mycí centrum', 'desc': 'Na dvou linkách myjeme ručně i automaticky – od motorky po autobus.', 'img': 'ext/ppl/media/com_twojtoolbox/DSC_4959.jpg', 'icon': 'truck'},
            {'href': '/servisy/iveco', 'title': 'Prodej a servis IVECO', 'desc': 'Autorizovaný prodej a servis vozů značky Iveco a Iveco Bus.', 'img': 'ext/ppl/media/com_twojtoolbox/iveco-daily.jpg', 'icon': 'truck'},
            {'href': '/servisy/elektroauta', 'title': 'Elektroauta', 'desc': 'Výhradní dovozce BirdieCar, prodejce Cenntro. Showroom v areálu.', 'img': 'ext/birdie/images/birdiecar-kategorie-preprava-osob.jpg', 'icon': 'bolt'},
            {'href': '/servisy/prodej-nahradnich-dilu', 'title': 'Prodej náhradních dílů', 'desc': 'Renault, Volvo, Iveco a MAN – ostatní značky na objednání.', 'img': 'ext/ppl/media/com_twojtoolbox/sklad-nahradnich-dilu.jpg', 'icon': 'building'},
            {'href': '/servisy/pujcovna-dodavek', 'title': 'Půjčovna dodávek', 'desc': 'Iveco Daily, Eurocargo, minibus pro 8 osob. Dostupnost a rezervace on-line.', 'img': 'ext/ppl/obrazky/veh-21/1-iveco.jpg', 'icon': 'truck'},
            {'href': '/servisy/autoservis/overeni-tachografu', 'title': 'Ostatní služby', 'desc': 'Tachografy, doplnění klimatizací, oprava hydraulických hadic.', 'img': 'ext/ppl/media/com_twojtoolbox/P1026425.JPG', 'icon': 'check'}])

# ============================================================ OSOBNÍ DOPRAVA (ZDS Psota)
ZDS_INFO = {'logo': '/images/ext/ppl/images/logo_zds.png', 'name': 'Znojemská dopravní společnost – PSOTA, s.r.o.',
            'contact': '<p>Hakenova 3640/21, 669 02 Znojmo<br />IČO: 63496283 | DIČ: CZ63496283<br />e-mail: <a href="mailto:obchodni@zds-psota.cz">obchodni@zds-psota.cz</a><br />ztráty a nálezy: <a href="tel:+420602546381">+420 602 546 381</a></p>'}
ZH = 'ext/zds/wp-content/uploads/2024/05/head.jpg'


def zds_lines(name, start=None):
    _, _, ls = lines_of(f'zds/{name}.html', start='/kontakty/]] Kontakty', end=' Znojemská dopravní společnost – PSOTA, s.r.o.\n Hakenova')
    return ls


# history
ti, im, b = txt(os.path.join(SRC, 'zds', 'index.html'))
b = b[b.find('Historie společnosti') + 20:].split('[[https://www.zds-psota.cz/wp-content/uploads/2026/06/Prohlaseni')[0]
hist = {}
cur = None
for l in [x.strip() for x in b.split('\n') if x.strip()]:
    if re.fullmatch(r'(19|20)\d\d', l): cur = l; hist[cur] = []
    elif cur: hist[cur].append(l)
hbody = '<p>' + 'Kořeny společnosti sahají do roku 1990. Z jednoho autobusu Škoda ŠD-11 vyrostl významný regionální dopravce.</p>' + '<br />'.join(f'<strong>{y}</strong> {esc(" ".join(v))}' for y, v in hist.items())
page('osobni-doprava/historie-spolecnosti', 'Historie společnosti', hbody, info=ZDS_INFO, hero=ZH)

# regional lines
rl = zds_lines('autobusova-doprava__autobusova-doprava-pravidelna__regionalni')
sections = []
secname = None
for l in rl:
    if l.startswith('Oblast'):
        secname = l; sections.append((l, [])); continue
    m = re.match(r'Linka (\d+) : \[\[([^\]]+)\]\] (\d+) (.+)$', l)
    if m and sections: sections[-1][1].append((m.group(3), m.group(4), m.group(2)))
body = p_('Zajišťujeme regionální linky v rámci Integrovaného dopravního systému Jihomoravského kraje (IDS JMK). Jízdní řády jednotlivých linek najdete ve formátu PDF na stránkách IDS JMK.')
for sec, rows in sections:
    body += h3(sec) + table([[n, name, f'<a href="{u}" target="_blank" rel="noopener">Jízdní řád (PDF)</a>'] for n, name, u in rows], ['Linka', 'Trasa', 'Jízdní řád'])
page('osobni-doprava/linky/regionalni', 'Regionální linky IDS JMK', body, info=ZDS_INFO, hero=ZH)

# long-distance Praha
page('osobni-doprava/linky/znojmo-jihlava-praha', 'Znojmo – Jihlava – Praha',
     h3('Linka 830800: Znojmo – Jihlava – Praha') +
     '<figure class="figure"><a href="/assets/images/ext/zds/wp-content/uploads/2024/12/JR_Praha_2025.jpg" data-lightbox="jr"><img src="/assets/images/ext/zds/wp-content/uploads/2024/12/JR_Praha_2025.jpg" alt="Jízdní řád Znojmo – Jihlava – Praha" loading="lazy"></a></figure>' +
     '<p><a href="http://portal.idos.cz/Down.aspx?f=pdf/L830800_251214_412161.pdf" target="_blank" rel="noopener">Jízdní řád CIS (PDF)</a> · Zavazadlo: 20 Kč/ks</p>' +
     h3('Bezplatná přeprava') + ul(['1 dítě do dovršení 6. roku v doprovodu osoby starší 10 let', 'průvodce držitele průkazu ZTP/P nebo vodicí pes držitele průkazu ZTP/P',
                                    'zavazadla menší než 20 × 30 × 50 cm, nebo tvaru válce do délky 150 cm a průměru 10 cm, nebo tvaru desky do 80 × 100 cm, nebo o hmotnosti do 25 kg',
                                    'dětské kočárky a invalidní vozíky pro držitele průkazu ZTP a ZTP/P (pouze do zavazadlového prostoru)', 'tašky na kolečkách, které s sebou berou do vozidla ZTP a ZTP/P']) +
     h3('Zlevněné jízdné 25 % základní ceny') + ul(['držitelé průkazu ZTP a ZTP/P – nutno prokázat platným průkazem']) +
     h3('Zlevněné jízdné 50 % základní ceny') + ul(['cestující od 6 do 15 let – není nutno prokazovat', 'cestující od 15 do 18 let – nutno prokázat věk úředně vydaným průkazem (např. občanský průkaz, cestovní pas, žákovský průkaz, ISIC)',
                                                   'cestující od 18 do 26 let – nutno prokázat nárok žákovským průkazem nebo studentským průkazem ISIC', 'cestující starší 65 let – nutno prokázat věk úředně vydaným průkazem', 'osoba invalidní ve třetím stupni – potvrzení pro slevu na jízdném vydané ČSSZ a platný osobní doklad s fotografií']) +
     p_('Platnost od 1. 1. 2024.') +
     '<p><a href="https://www.zds-psota.cz/wp-content/uploads/2025/08/2024_Cenik-komercni-Praha-C.Budejovice.pdf" target="_blank" rel="noopener">Kilometrický ceník (PDF)</a> · <a href="https://www.zds-psota.cz/wp-content/uploads/2024/08/SPP_komercni.pdf" target="_blank" rel="noopener">Smluvní přepravní podmínky (PDF)</a></p>' +
     h3('Zakoupení jízdenky s místenkou on-line') + '<p><a href="http://www.idos.cz" target="_blank" rel="noopener">idos.cz</a> · <a href="http://www.amsbus.cz" target="_blank" rel="noopener">amsbus.cz</a></p>',
     info=ZDS_INFO, hero=ZH, gallery=[])
page('osobni-doprava/linky/znojmo-ceske-budejovice', 'Znojmo – Jindřichův Hradec – České Budějovice',
     h3('Linka 830860: Znojmo – Jindřichův Hradec – České Budějovice') + '<p class="notice notice--red">Linka byla zrušena.</p>', info=ZDS_INFO, hero=ZH)

# train
t = open(os.path.join(SRC, 'zds', 'autobusova-doprava__autobusova-doprava-pravidelna__turisticky-vlacek.html'), encoding='utf-8').read()
rows = []
for r in re.findall(r'<tr[^>]*>(.*?)</tr>', t, re.S):
    cells = [H.unescape(re.sub(r'<[^>]+>', '', c)).strip().replace('–', '–') for c in re.findall(r'<t[dh][^>]*>(.*?)</t[dh]>', r, re.S)]
    if len(cells) >= 6 and any(re.match(r'\d+:\d\d', c) for c in cells): rows.append(cells)
tt = []
for c in rows:
    if len(c) == 8: tt.append([c[0], c[1], c[2]] + c[3:])
    elif len(c) == 6: tt.append(['', '', c[0]] + c[1:])
page('osobni-doprava/linky/turisticky-vlacek', 'Turistický vláček',
     p_('Turistický vláček jezdí ve Znojmě od 1. května do 30. září denně včetně nedělí a svátků. Ve dnech konání Znojemského historického vinobraní bude linka provozována podle upraveného jízdního řádu.') +
     h3('Jízdní řád turistického vláčku') + table(tt, ['č.', 'Zastávka', '', '1. jízda', '2. jízda', '3. jízda', '4. jízda', '5. jízda']) +
     h3('Ceník jízdného (platný od 1. 5. 2026)') + table([['obyčejné jízdné', '120 Kč'], ['snížené jízdné – děti od 3 do 15 let, studenti, držitelé průkazu ZTP nebo ZTP/P', '90 Kč'], ['rodinné jízdné 1+1 (1 osoba nad 15 let + 1 osoba do 15 let)', '160 Kč'], ['rodinné jízdné 2+2 (2 osoby nad 15 let + 2 osoby do 15 let)', '270 Kč'],
                                                                      ['průvodce držitele průkazu ZTP/P, děti do 3 let, dětské kočárky', 'zdarma']], ['Jízdné', 'Cena']) +
     h3('Mapa trasy') + '<figure class="figure"><a href="/assets/images/ext/zds/wp-content/uploads/2024/05/mapa_vlacek-1.jpg" data-lightbox="vl"><img src="/assets/images/ext/zds/wp-content/uploads/2024/05/mapa_vlacek-1.jpg" alt="Mapa trasy turistického vláčku" loading="lazy"></a></figure>' +
     '<p><a href="https://www.zds-psota.cz/wp-content/uploads/2026/05/Jizdni-rad-2026.pdf" target="_blank" rel="noopener">Jízdní řád a ceník (PDF)</a> · <a href="https://www.zds-psota.cz/wp-content/uploads/2024/08/Vlacek_SPP_2024.pdf" target="_blank" rel="noopener">Smluvní přepravní podmínky (PDF)</a> · <a href="https://www.zds-psota.cz/wp-content/uploads/2024/10/Vlacek-objednavka.pdf" target="_blank" rel="noopener">Objednávka vláčku (PDF)</a></p>',
     info=ZDS_INFO, hero=ZH)
page('osobni-doprava/linky/mad-uherske-hradiste', 'MAD Uherské Hradiště',
     p_('Městskou autobusovou dopravu v Uherském Hradišti provozujeme pod značkou Poulička. Provozovna: Průmyslová 712, 686 01 Uherské Hradiště.') +
     '<p><a href="https://idzk.info/poulicka/" target="_blank" rel="noopener">Oficiální web www.poulicka.cz</a> · <a href="https://www.idzk.cz/mhd-uh" target="_blank" rel="noopener">IDZK – Uherské Hradiště</a></p>',
     info=ZDS_INFO, hero=ZH,
     people=[{'title': 'Dispečink MAD Uherské Hradiště', 'people': [P('Zdeněk Handl', 'vedoucí střediska a dispečer MAD Uherské Hradiště', '', '725 345 123', 'dispecink-uh@zds-psota.cz'), P('Bc. Martina Šrámková', 'dispečerka MAD Uherské Hradiště', '', '725 345 113', 'dispecink-uh2@zds-psota.cz')]}])
page('osobni-doprava/linky/zajezdova-doprava', 'Zájezdová doprava',
     p_('Autobusy pro zájezdy, svatby, školní výlety i firemní akce. Objednávku přepravy zašlete na obchodní oddělení.') +
     '<p><a href="https://www.zds-psota.cz/wp-content/uploads/2024/05/Objednavka-prepravy.pdf" target="_blank" rel="noopener">Objednávka přepravy (PDF)</a> · <a href="https://www.zds-psota.cz/wp-content/uploads/2024/05/1-Objednavka-prepravy.doc" target="_blank" rel="noopener">Objednávka přepravy (Word)</a></p>' +
     '<p><a href="/osobni-doprava/prepravni-rad">Přepravní řád – smluvní přepravní podmínky</a></p>', info=ZDS_INFO, hero=ZH)
page('osobni-doprava/linky/ztraty-a-nalezy', 'Ztráty a nálezy',
     p_('Něco jste zapomněli v autobuse nebo ve vláčku? Napište nám nebo zavolejte na číslo ztrát a nálezů: +420 602 546 381.') +
     '<h3>Formulář ztrát a nálezů</h3><ul><li>datum ztráty</li><li>na jaké lince to bylo (104, 105, 108, 164, 446, 504, 815–818, 820, 821, 825, 830, 831, 521–588, 830800 Praha, turistický vláček)</li><li>v kolik hodin, odkud – kam</li><li>popis autobusu (evidenční číslo nebo jméno řidiče)</li><li>popis ztraceného předmětu, telefonní kontakt, e-mail, fotografie</li></ul>'
     '<p class="notice">Online formulář bude v hotovém webu napojen na e-mail obchodního oddělení (<a href="mailto:obchodni@zds-psota.cz">obchodni@zds-psota.cz</a>).</p>', info=ZDS_INFO, hero=ZH)
page('osobni-doprava/linky', 'Linky a doprava', p_('Regionální linky, dálkové spoje, turistický vláček, městská doprava v Uherském Hradišti a zájezdová doprava.'),
     info=ZDS_INFO, hero=ZH,
     cards=[{'href': '/osobni-doprava/linky/regionalni', 'title': 'Regionální linky IDS JMK', 'desc': 'Znojemsko, Mikulovsko a Břeclavsko.', 'img': None, 'icon': 'bus'},
            {'href': '/osobni-doprava/linky/znojmo-jihlava-praha', 'title': 'Znojmo – Jihlava – Praha', 'desc': 'Dálková linka 830800.', 'img': None, 'icon': 'bus'},
            {'href': '/osobni-doprava/linky/turisticky-vlacek', 'title': 'Turistický vláček', 'desc': 'Denně od 1. května do 30. září.', 'img': None, 'icon': 'bus'},
            {'href': '/osobni-doprava/linky/mad-uherske-hradiste', 'title': 'MAD Uherské Hradiště', 'desc': 'Městská doprava Poulička.', 'img': None, 'icon': 'bus'},
            {'href': '/osobni-doprava/linky/zajezdova-doprava', 'title': 'Zájezdová doprava', 'desc': 'Autobusy na zájezdy a akce.', 'img': None, 'icon': 'bus'},
            {'href': '/osobni-doprava/linky/ztraty-a-nalezy', 'title': 'Ztráty a nálezy', 'desc': 'Zapomněli jste něco v autobuse?', 'img': None, 'icon': 'bell'}])

# services
page('osobni-doprava/sluzby/cng-stanice', 'CNG stanice',
     p_('Samoobslužný provoz pouze na čipové karty (Innogy, E.ON a Pražská plynárenská).') +
     table([['Adresa', 'Areál Autocentrum Psota, Dobšická 3697/6, Znojmo'], ['Otevírací doba', 'NONSTOP'], ['Koncovka NGV1', '1×'], ['Koncovka NGV2', '1×'], ['Platební metoda', 'CNG karta'], ['Aktuální cena CNG', '44,50 Kč']]) +
     h3('Vyřízení CNG karty Innogy') + '<ul><li>Fyzická osoba: <a href="https://cng.innogy.cz/objednavka-fyzicka-osoba" target="_blank" rel="noopener">cng.innogy.cz/objednavka-fyzicka-osoba</a></li><li>Živnostník: <a href="https://cng.innogy.cz/objednavka-zivnostnik" target="_blank" rel="noopener">cng.innogy.cz/objednavka-zivnostnik</a></li><li>Firma: <a href="https://cng.innogy.cz/objednavka-firma" target="_blank" rel="noopener">cng.innogy.cz/objednavka-firma</a></li></ul>',
     info=ZDS_INFO, hero=ZH)
page('osobni-doprava/sluzby/cerpaci-stanice', 'Čerpací stanice', '<p class="notice notice--red">Provoz čerpací stanice byl ukončen (28. 5. 2026). K dispozici zůstává veřejná CNG stanice.</p>' + '<p><a href="/osobni-doprava/sluzby/cng-stanice">CNG stanice</a></p>', info=ZDS_INFO, hero=ZH)
penz = sorted(f for f in os.listdir('/Users/petrholecek/Desktop/práce/psota/assets/images/ext/zds/wp-content/uploads/2024/09') if f.endswith('.jpg'))
page('osobni-doprava/sluzby/pronajem', 'Pronájem – Penzion Renata',
     p_('Příjemné ubytování ve Znojmě ve třech dvoulůžkových pokojích a jednom čtyřlůžkovém apartmánu.') + h3('Každý pokoj má') + ul(['klimatizaci', 'vlastní sociální zařízení', 'k dispozici kuchyňku a lednici', 'možnost úschovny kol']) +
     p_('Penzion se nachází v jižní části města Znojma, 150 m od zastávky MHD. Vinné sklepy jsou cca 1,5 km od objektu, Kaufland cca 10 minut pěšky, restaurace 150 m.') +
     table([['Adresa objektu', 'Nová Oblekovická 281/26, Znojmo'], ['Telefon', '+420 724 313 911, +420 607 949 573'], ['E-mail', 'pechaz@email.cz, rechaz@seznam.cz']]),
     info=ZDS_INFO, hero=ZH, gallery=gal('zds/wp-content/uploads/2024/09', penz))
page('osobni-doprava/sluzby', 'Služby', p_('Čerpací a plnicí stanice, pronájem ubytování a další služby Znojemské dopravní společnosti.'), info=ZDS_INFO, hero=ZH,
     cards=[{'href': '/osobni-doprava/sluzby/cng-stanice', 'title': 'CNG stanice', 'desc': 'Veřejná plnicí stanice nonstop.', 'img': None, 'icon': 'bolt'},
            {'href': '/osobni-doprava/sluzby/cerpaci-stanice', 'title': 'Čerpací stanice', 'desc': 'Provoz ukončen.', 'img': None, 'icon': 'truck'},
            {'href': '/osobni-doprava/sluzby/pronajem', 'title': 'Pronájem – Penzion Renata', 'desc': 'Ubytování ve Znojmě.', 'img': 'ext/zds/wp-content/uploads/2024/09/Penzion_00.jpg', 'icon': 'building'}])
page('osobni-doprava/ceny-pohonnych-hmot', 'Ceny pohonných hmot',
     table([['Natural 95 – E10', 'provoz ukončen 28. 5. 2026'], ['Diesel – B7', 'provoz ukončen 28. 5. 2026'], ['AdBlue', 'provoz ukončen 28. 5. 2026'], ['CNG', '44,50 Kč']], ['Palivo', 'Cena']), info=ZDS_INFO, hero=ZH)
page('osobni-doprava/kariera', 'Kariéra',
     h3('Řidič turistického vláčku') + p_('Řidič turistického vláčku ve Znojmě jezdící pravidelnou linku.') + p_('Požadujeme: řidičské oprávnění sk. D+E, osvědčení o profesní způsobilosti.') + p_('Nabízíme: příspěvek na stravování, volné jízdné v rámci Jihomoravského kraje.') + p_('Životopis posílejte na: obchodni@zds-psota.cz') +
     h3('Řidiči a řidičky do Uherského Hradiště') + p_('Hledáme řidiče a řidičky městské autobusové dopravy v Uherském Hradišti.') +
     h3('Autoelektrikář') + p_('Diagnostika a opravy autobusů, převážně značek SOR, IVECO a jiné. Požadujeme vzdělání v oboru. Nabízíme plat od 35 000 Kč + příplatek za odbornost, příspěvek na stravování a volné jízdné v rámci Jihomoravského kraje. Adresa pracoviště: Hakenova 3640/21, Znojmo. Životopis posílejte na zds-servis@seznam.cz.') +
     h3('Karosář') + p_('Karosářské opravy, výměny skel, opravy autobusů, převážně značek SOR, IVECO a jiné. Požadujeme vzdělání v oboru. Nabízíme plat od 35 000 Kč + příplatek za odbornost a stejné benefity.'), info=ZDS_INFO, hero=ZH)


def long_text(name):
    ls = zds_lines(name)
    return ''.join(f'<p>{esc(l)}</p>' for l in ls[1:] if not l.startswith('[['))


page('osobni-doprava/prepravni-rad', 'Přepravní řád – smluvní přepravní podmínky',
     '<div lang="cs" class="cs-only"><p class="muted small">Smluvní přepravní podmínky jsou právně závazné pouze v českém znění.</p>' + long_text('prepravni-rad-smluvni-prepravni-podminky') + '</div>', info=ZDS_INFO, hero=ZH)
page('osobni-doprava/prepravni-rad-turisticky-vlacek', 'Přepravní řád turistického vláčku',
     '<div lang="cs" class="cs-only"><p class="muted small">Přepravní řád je právně závazný pouze v českém znění.</p>' + long_text('prepravni-rad') + '</div>', info=ZDS_INFO, hero=ZH)
page('osobni-doprava/kontakty', 'Kontakty', '', info=ZDS_INFO, hero=ZH,
     people=[{'title': 'Ředitel společnosti', 'people': [P('Ing. Petr Chadim', 'ředitel', '', '724 313 911', 'reditel@zds-psota.cz')]},
             {'title': 'Obchodní oddělení a dispečink', 'people': [P('Radka Jahodová', 'vedoucí obchodu a marketingu, objednávky osobní přepravy a vyhlídkového vláčku', '', '602 538 227', 'obchodni@zds-psota.cz'),
                                                                   P('Miroslav Langhammer', 'vedoucí dispečer, dispečer osobní přepravy Znojemsko', '', '725 150 599', 'dispecink@zds-psota.cz'),
                                                                   P('Ivan Hudec', 'dispečer osobní přepravy Znojemsko', '', '724 368 831', 'dispecink-znojmo@zds-psota.cz'),
                                                                   P('Blanka Prosecká', 'vedoucí dispečer, dispečerka osobní přepravy Mikulovsko, Břeclavsko', '', '725 345 122', 'dispecink-mikulov@zds-psota.cz')]},
             {'title': 'MAD Uherské Hradiště', 'people': [P('Zdeněk Handl', 'vedoucí střediska a dispečer', '', '725 345 123', 'dispecink-uh@zds-psota.cz'), P('Bc. Martina Šrámková', 'dispečerka', '', '725 345 113', 'dispecink-uh2@zds-psota.cz')]},
             {'title': 'Ekonomický úsek', 'people': [P('Ing. Martina Madejová, DiS.', 'ekonomka společnosti', '', '725 345 101', 'ekonom@zds-psota.cz'), P('Ing. Vladimír Vrba', 'ekonom dopravy, tvorba jízdních řádů', '', '725 345 100', 'ekonom-dopravy@zds-psota.cz'),
                                                         P('Lukáš Madeja', 'referent dopravy, škodní události', '', '602 348 843', 'referent-dopravy@zds-psota.cz'), P('Dana Lavingrová', 'ekonomický referent', '', '725 345 129', 'referent-eko@zds-psota.cz'),
                                                         P('Oldřiška Ševčíková', 'pokladna, fakturace', '', '602 546 303', 'pokladna@zds-psota.cz')]},
             {'title': 'Servis autobusů a označníky', 'people': [P('Antonín Novotný', 'vedoucí dílen (Hakenova 3640/21, Po–Pá 7:00–15:30)', '', '728 088 222', 'servis@zds-psota.cz'), P('Aleš Czehovský', 'hlášení poškozených označníků a nečitelných jízdních řádů (Po–Pá 7:00–15:00)', '', '724 260 723', 'sklad@zds-psota.cz'), P('Milan Kovář', 'hlášení poškozených označníků a nečitelných jízdních řádů', '', '602 324 701', 'sklad@zds-psota.cz')]},
             {'title': 'Autoškola a školicí středisko ZDS Psota', 'people': [P('Mgr. Renata Chadimová', 'vedoucí autoškoly ZDS Psota', '', '607 949 573', 'rechaz@email.cz'), P('Ing. Petr Chadim', 'vedoucí akreditovaného školicího střediska', '', '724 313 911', 'reditel@zds-psota.cz')]}])

page('osobni-doprava', 'Osobní doprava',
     p_('Znojemská dopravní společnost – PSOTA, s.r.o. je autobusová společnost zajišťující regionální linky v rámci Integrovaného dopravního systému Jihomoravského kraje (IDS JMK), provozovatel dálkových linek, zájezdů a turistických vláčků ve Znojmě. Autobusy cestují za našimi zákazníky od roku 1990.') +
     '<div class="facts"><div class="fact"><b>80</b><span>autobusů</span></div><div class="fact"><b>1990</b><span>založení společnosti</span></div><div class="fact"><b>IDS JMK</b><span>regionální linky</span></div><div class="fact"><b>CNG</b><span>veřejná plnicí stanice</span></div></div>',
     info=ZDS_INFO, hero=ZH,
     cards=[{'href': '/osobni-doprava/linky', 'title': 'Linky a doprava', 'desc': 'Regionální linky, Praha, turistický vláček, MAD Uherské Hradiště.', 'img': None, 'icon': 'bus'},
            {'href': '/osobni-doprava/linky/zajezdova-doprava', 'title': 'Zájezdová doprava', 'desc': 'Autobusy pro zájezdy a akce.', 'img': None, 'icon': 'bus'},
            {'href': '/osobni-doprava/sluzby', 'title': 'Služby', 'desc': 'CNG stanice, penzion a další.', 'img': None, 'icon': 'bolt'},
            {'href': '/osobni-doprava/historie-spolecnosti', 'title': 'Historie společnosti', 'desc': 'Od jednoho autobusu k regionálnímu dopravci.', 'img': None, 'icon': 'clock'},
            {'href': '/osobni-doprava/kariera', 'title': 'Kariéra', 'desc': 'Hledáme řidiče, autoelektrikáře a karosáře.', 'img': None, 'icon': 'users'},
            {'href': '/osobni-doprava/kontakty', 'title': 'Kontakty', 'desc': 'Dispečink, obchod a vedení.', 'img': None, 'icon': 'phone'}])

# drop references to images that could not be downloaded
IMGROOT = os.path.join(HERE, '..', '..', 'assets', 'images')
def have(rel): return os.path.isfile(os.path.join(IMGROOT, re.sub(r'^/images/', '', rel)))
for k, d in pages.items():
    if not k.startswith(('servisy', 'osobni-doprava')): continue
    d['gallery'] = [g for g in d.get('gallery', []) if have(g)]
    for c in d.get('cards', []):
        if c.get('img') and not have(c['img']): c['img'] = None
    if d.get('hero') and not have(d['hero']): d.pop('hero')
    d['body'] = re.sub(r'<(?:a[^>]*>)?<img src="/images/([^"]+)"[^>]*>(?:</a>)?', lambda m: m.group(0) if have(m.group(1)) else '', d['body'])
json.dump(pages, open(PAGES, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print('pages total', len(pages), '| new', sum(1 for k in pages if k.startswith(('servisy', 'osobni-doprava'))))
