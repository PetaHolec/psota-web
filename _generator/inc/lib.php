<?php
declare(strict_types=1);

define('LANG_CODE', getenv('SITE_LANG') ?: 'cs');
const SITE_NAME = 'PSOTA Group';
const SITE_URL  = 'https://www.psota-znojmo.cz';
const HOLDING = [
    'name'    => 'PSOTA holding',
    'street'  => 'Dobšická 6',
    'city'    => '669 02 Znojmo',
    'phone'   => '+420 515 210 540',
    'email'   => 'info@psota-znojmo.cz',
    'lat'     => 48.8532028,
    'lng'     => 16.0731943,
];

/* ------------------------------------------------------------------ helpers */

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function asset(string $path): string { return '/assets/' . ltrim($path, '/'); }

function img(string $path): string { return '/assets/images/' . ltrim($path, '/'); }

function thumb(string $path): string {
    $path = ltrim($path, '/');
    $t = __DIR__ . '/../../assets/thumbs/' . $path;
    return is_file($t) ? '/assets/thumbs/' . $path : img($path);
}

function tel_href(string $t): string { return 'tel:' . preg_replace('/[^+\d]/', '', $t); }

function data(string $name): array {
    static $cache = [];
    return $cache[$name] ??= json_decode((string)file_get_contents(__DIR__ . "/../data/$name.json"), true) ?: [];
}

function icon(string $name, string $class = 'ico'): string {
    static $paths = [
        'truck'  => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
        'bus'    => '<path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/>',
        'wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
        'check'  => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
        'school' => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>',
        'food'   => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>',
        'phone'  => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail'   => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'pin'    => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'clock'  => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'arrow'  => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'file'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        'menu'   => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close'  => '<path d="M18 6 6 18M6 6l12 12"/>',
        'down'   => '<path d="m6 9 6 6 6-6"/>',
        'car'    => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
        'external' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'bolt'   => '<path d="M13 2 3 14h9l-1 8 10-12h-9z"/>',
        'user'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'bell'   => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        'users'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'plus'   => '<path d="M5 12h14M12 5v14"/>',
    ];
    return '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

/** Cleans scraped legacy markup so it renders well in the new design. */
function prose(string $html): string {
    $html = str_replace(['/images/', 'src="images/'], ['/assets/images/', 'src="/assets/images/'], $html);
    $html = preg_replace('#<(h[1-4])([^>]*)>(.*?)</\1>#is', '<$1$2>$3</$1>', $html) ?? $html;
    $html = preg_replace_callback('#<(h[1-4])([^>]*)>(.*?)</\1>#is', function ($m) {
        $inner = preg_replace('#(<br\s*/?>|&nbsp;|\s)+$#i', '', preg_replace('#^(<br\s*/?>|&nbsp;|\s)+#i', '', $m[3]));
        $inner = preg_replace('#<br\s*/?>#i', ' ', $inner);
        $txt = trim(strip_tags($inner, '<img>'));
        return $txt === '' ? '' : "<{$m[1]}{$m[2]}>$inner</{$m[1]}>";
    }, $html) ?? $html;
    $html = preg_replace('#<p>\s*<strong>\s*<img[^>]*loga_120[^>]*>\s*</strong>\s*</p>#i', '', $html) ?? $html;
    $html = preg_replace('#^\s*<hr\s*/?>#i', '', trim($html)) ?? $html;
    $html = preg_replace('#<hr\s*/?>\s*$#i', '', trim($html)) ?? $html;
    $html = preg_replace('#(<br\s*/?>\s*){3,}#i', '<br><br>', $html) ?? $html;
    // external links open in new tab
    $html = preg_replace_callback('#<a\s+([^>]*href="https?://[^"]+"[^>]*)>#i', function ($m) {
        $a = $m[1];
        if (stripos($a, 'target=') === false) $a .= ' target="_blank"';
        if (stripos($a, 'rel=') === false) $a .= ' rel="noopener"';
        return "<a $a>";
    }, $html) ?? $html;
    // wide tables scroll on mobile
    $html = preg_replace('#<table#i', '<div class="table-wrap"><table', $html) ?? $html;
    $html = preg_replace('#</table>#i', '</table></div>', $html) ?? $html;
    return $html;
}

/* --------------------------------------------------------------- site map */

const DIVISIONS = [
    'nakladni-doprava' => [
        'title' => 'Nákladní doprava', 'icon' => 'truck', 'hero' => 'fotogalerie/transport/transport_006.jpg',
        'lead'  => 'Vnitrostátní a mezinárodní kamionová doprava. 105 lowdeckových souprav, nepřetržitý dispečink a GPS dohled.',
        'logo'  => 'logo_crop/logo_transport.png',
        'menu'  => [
            ['nabidka-sluzeb', 'Nabídka služeb'], ['historie-spolecnosti', 'Historie společnosti'],
            ['doklady-ke-stazeni', 'Doklady ke stažení'], ['volna-mista', 'Volná místa'], ['kontakty', 'Kontakty'],
        ],
    ],
    'osobni-doprava' => [
        'title' => 'Osobní doprava', 'icon' => 'bus', 'hero' => 'ext/zds/wp-content/uploads/2024/05/head.jpg',
        'lead'  => 'Znojemská dopravní společnost – PSOTA: regionální linky IDS JMK, dálkové spoje, zájezdy a turistický vláček ve Znojmě.',
        'logo'  => 'ext/ppl/images/logo_zds.png',
        'menu'  => [
            ['linky', 'Linky a doprava', [['Regionální linky IDS JMK', '/osobni-doprava/linky/regionalni'], ['Znojmo – Jihlava – Praha', '/osobni-doprava/linky/znojmo-jihlava-praha'], ['Turistický vláček', '/osobni-doprava/linky/turisticky-vlacek'], ['MAD Uherské Hradiště', '/osobni-doprava/linky/mad-uherske-hradiste'], ['Zájezdová doprava', '/osobni-doprava/linky/zajezdova-doprava'], ['Ztráty a nálezy', '/osobni-doprava/linky/ztraty-a-nalezy']]],
            ['sluzby', 'Služby', [['CNG stanice', '/osobni-doprava/sluzby/cng-stanice'], ['Čerpací stanice', '/osobni-doprava/sluzby/cerpaci-stanice'], ['Pronájem – Penzion Renata', '/osobni-doprava/sluzby/pronajem']]],
            ['historie-spolecnosti', 'Historie společnosti'], ['kariera', 'Kariéra'], ['ceny-pohonnych-hmot', 'Ceny PHM'], ['prepravni-rad', 'Přepravní řád'], ['kontakty', 'Kontakty'],
        ],
    ],
    'stk' => [
        'title' => 'STK a měření emisí', 'nav' => 'STK a emise', 'icon' => 'check', 'hero' => 'fotogalerie/stk_znojmo/stkznojmo_01.jpg',
        'lead'  => 'Stanice technické kontroly ve Znojmě, Břeclavi a Hrušovanech. Bez objednání a bez čekání.',
        'logo'  => 'logo_crop/logo_stkznojmo.png',
        'menu'  => [
            ['nabidka-sluzeb', 'Nabídka služeb'], ['ceniky', 'Ceníky'], ['stanovene-terminy-prohlidek', 'Termíny prohlídek'],
            ['jak-se-pripravit-na-stk', 'Jak se připravit'], ['prestupky-a-postihy', 'Přestupky a pokuty'],
            ['povinna-vybava', 'Povinná výbava'], ['rezervace-terminu', 'Rezervace termínu'], ['kontakty', 'Kontakty'],
        ],
    ],
    'servisy' => [
        'title' => 'Servisy a mytí', 'icon' => 'wrench', 'hero' => 'ext/ppl/images/slide-autocentrum-znojmo-autoservis-servis-nakladnich-aut.jpg',
        'lead'  => 'Autoservis a pneuservis všech značek, autorizovaný servis a prodej IVECO, mycí centrum, náhradní díly, půjčovna dodávek a elektroauta BirdieCar.',
        'logo'  => 'logo/logo_ppl.png',
        'menu'  => [
            ['autoservis', 'Autoservis', [['Servis osobních vozidel', '/servisy/autoservis/osobni-vozidla'], ['Servis dodávkových vozidel', '/servisy/autoservis/dodavkova-vozidla'], ['Servis nákladních vozidel', '/servisy/autoservis/nakladni-vozidla'], ['Ověření tachografů', '/servisy/autoservis/overeni-tachografu'], ['Plnění klimatizací', '/servisy/autoservis/plneni-klimatizaci'], ['Odtahová a asistenční služba', '/servisy/autoservis/odtahova-a-asistencni-sluzba'], ['On-line objednání na servis', '/servisy/autoservis/objednani-na-servis']]],
            ['pneuservis', 'Pneuservis'], ['myti-vozidel', 'Mytí vozidel'], ['prodej-nahradnich-dilu', 'Náhradní díly'],
            ['iveco', 'IVECO', [['Prodej užitkových vozidel', '/servisy/iveco/prodej-uzitkovych-vozidel'], ['Prodej nákladních automobilů', '/servisy/iveco/prodej-nakladnich-automobilu'], ['Autorizovaný servis', '/servisy/iveco/autorizovany-servis']]],
            ['pujcovna-dodavek', 'Půjčovna dodávek'],
            ['elektroauta', 'Elektroauta', [['O značce BirdieCar', '/servisy/elektroauta/o-nas'], ['Modely', '/servisy/elektroauta/modely'], ['Servis elektroaut', '/servisy/elektroauta/servis'], ['Reference', '/servisy/elektroauta/reference'], ['Dealerská síť', '/servisy/elektroauta/prodejci'], ['Cenntro', '/servisy/elektroauta/cenntro'], ['Aktuality BirdieCar', '/servisy/elektroauta/aktuality'], ['Kontakty', '/servisy/elektroauta/kontakty']]],
            ['aktuality', 'Aktuality'], ['kontakty', 'Náš tým'],
        ],
    ],
    'ucnovske-stredisko' => [
        'title' => 'Učňovské středisko', 'icon' => 'school', 'hero' => 'fotogalerie/ucnovske/ucnovske_0012.jpg',
        'lead'  => 'Servis vozidel v rukou zkušených odborníků a budoucích mechaniků. Autoškola skupin A1, B, C, C+E a profesní školení řidičů.',
        'logo'  => 'logo_crop/logo_ucnovske.png',
        'menu'  => [
            ['nabidka-sluzeb', 'Nabídka služeb'], ['cenik', 'Ceník'], ['autoskola', 'Autoškola'], ['kontakty', 'Kontakty'],
        ],
    ],
    'restaurace' => [
        'title' => 'Restaurace u STK', 'nav' => 'Restaurace', 'icon' => 'food', 'hero' => 'fotogalerie/restaurace/restaurace_01.jpeg',
        'lead'  => 'Denní menu pro návštěvníky autocentra i široké okolí. Nově v areálu TOS, Družstevní 2825/3.',
        'logo'  => 'logo_crop/logo_restaurace.png',
        'menu'  => [['nabidka-sluzeb', 'Nabídka služeb'], ['menu', 'Denní menu'], ['kontakty', 'Kontakty']],
    ],
    'autocentrum' => [
        'title' => 'Autocentrum', 'icon' => 'building', 'hero' => 'fotogalerie/autocentrum/autocentrum_001.jpg',
        'lead'  => 'Areál služeb nejen pro motoristy na 40 000 m². Pronájem kanceláří, skladů a výrobních prostor.',
        'logo'  => 'logo_crop/logo_autocentrum.png',
        'menu'  => [['nabidka-sluzeb', 'Nabídka služeb'], ['kontakty', 'Kontakty']],
    ],
];

const BRANCHES = [
    'stk-znojmo'         => ['STK Znojmo', 'stk_znojmo.jpg', 'logo_crop/logo_stkznojmo.png', 'osobní a dodávková vozidla, motocykly, autobusy'],
    'stk-znojmo-nakladni' => ['STK4you Znojmo', 'stk4you-znojmo.jpg', 'logo_crop/stk4you_logo_cropped.png', 'nákladní vozidla a přívěsy'],
    'stk-breclav'        => ['STK Břeclav', 'stk_breclav.jpg', 'logo_crop/logo_stkbreclav.png', 'všechny typy vozidel včetně emisí'],
    'stk-hrusovany'      => ['STK Hrušovany', 'stk-hrusovany.jpg', 'logo-stk-hrusovany-500.png', 'stanice v Hrušovanech nad Jevišovkou'],
];

const NAV_SERVICES = [
    ['Autoservis', 'https://pplautocentrum.cz/autoservis'],
    ['Rezervace termínu na servis', 'https://pplautocentrum.cz/autoservis/objednani-na-servis'],
    ['Pneuservis', 'https://pplautocentrum.cz/pneuservis'],
    ['Půjčovna dodávek', 'https://pplautocentrum.cz/pujcovna-dodavek'],
    ['Mytí nákladních vozidel', 'https://pplautocentrum.cz/myti-nakladnich-vozidel'],
    ['Prodej náhradních dílů', 'https://pplautocentrum.cz/prodej-nahradnich-dilu'],
    ['IVECO servis a prodej', 'https://pplautocentrum.cz/iveco'],
    ['BirdieCar elektroauta', 'https://birdiecar-elektroauta.cz/'],
    ['Cenntro elektroauta', 'https://pplautocentrum.cz/cenntro/prodej-elektroaut'],
    ['Náš tým', 'https://pplautocentrum.cz/nas-tym/kontakty'],
];

function nav(): array {
    $items = [];
    foreach (['nakladni-doprava', 'osobni-doprava', 'servisy', 'stk', 'ucnovske-stredisko', 'restaurace', 'autocentrum'] as $slug) {
        $d = DIVISIONS[$slug];
        $children = [];
        foreach ($d['menu'] as $mi) {
            [$s, $label] = $mi;
            $kids = $mi[2] ?? [];
            if ($slug === 'stk' && in_array($s, ['nabidka-sluzeb', 'ceniky'], true)) {
                foreach (BRANCHES as $bs => $b) $kids[] = [$b[0], "/stk/$s/$bs"];
            }
            if ($slug === 'stk' && $s === 'rezervace-terminu') {
                $kids = [['STK Znojmo – osobní', '/stk/rezervace-terminu/stk-znojmo-osobni'], ['STK Znojmo – nákladní', '/stk/rezervace-terminu/stk-znojmo-nakladni']];
            }
            if ($slug === 'ucnovske-stredisko' && $s === 'autoskola') {
                $kids = [
                    ['On-line přihlášení žáků', '/ucnovske-stredisko/autoskola/elektronicka-prihlaska-do-autoskoly'],
                    ['Plánování jízd', '/ucnovske-stredisko/autoskola/prihlaseni-a-planovani-jizd'],
                    ['Přihlášení ke školení řidičů', '/ucnovske-stredisko/autoskola/on-line-prihlaseni-ke-skoleni-ridicu'],
                ];
            }
            $children[] = ['label' => $label, 'href' => "/$slug/$s", 'kids' => $kids];
        }
        if ($slug === 'stk') $children[] = ['label' => 'Aktuality', 'href' => '/aktuality-stk', 'kids' => []];
        $items[$slug] = ['label' => $d['nav'] ?? $d['title'], 'href' => "/$slug", 'icon' => $d['icon'], 'children' => $children];
    }
    return $items;
}

/* ------------------------------------------------------------------ model */

function page_key(string $path): string { return str_replace('/', '__', trim($path, '/')); }

function page(string $path): ?array { return data('pages')[page_key($path)] ?? null; }

/** Today's opening hours entry as ['Pondělí','07:00 - 17:00'] list → JSON map for JS (0=Sun). */
function hours_json(array $hours): string {
    $days = ['Pondělí' => 1, 'Úterý' => 2, 'Středa' => 3, 'Čtvrtek' => 4, 'Pátek' => 5, 'Sobota' => 6, 'Neděle' => 0];
    $map = [];
    foreach ($hours as [$d, $t]) {
        $d = trim($d);
        if (isset($days[$d])) $map[$days[$d]] = preg_replace('/\s+/', '', $t);
    }
    return json_encode($map, JSON_UNESCAPED_UNICODE);
}

function open_badge(array $hours): string {
    if (!$hours) return '';
    return '<span class="open-badge" data-hours="' . e(hours_json($hours)) . '"><i></i><b>…</b></span>';
}

function branch_info(string $slug): ?array {
    return page("stk/nabidka-sluzeb/$slug")['info'] ?? null;
}

function news_all(): array {
    $all = data('news');
    // translated versions carry only the most recent notices; the full archive stays Czech-only
    return LANG_CODE === 'cs' ? $all : array_values(array_filter($all, fn($n) => ($n['iso'] ?? '') >= '2025-10-01'));
}

function czech_date(?string $d): string { return (string)$d; }

function robots_noindex(): bool { return false; }

/** Path of the same page in another language (cs = no prefix). */
function is_old_news(string $path): bool {
    return (bool)preg_match('#^/aktuality-stk/(\d+)-#', $path, $m) && (int)$m[1] < 110;
}

function lang_path(string $path, string $code): string {
    if ($code === 'cs') return $path;
    if (is_old_news($path)) $path = '/aktuality-stk';   // the archive exists only in Czech
    return '/' . $code . ($path === '/' ? '/' : $path);
}


/* ------------------------------------------------------------------ locations / maps */
/** Exact places of every service (data/locations.json) and which pages show which of them (data/loc_rules.json). */
function locations(): array {
    static $l = null;
    return $l ??= json_decode(file_get_contents(__DIR__ . '/../data/locations.json'), true) ?: [];
}

function page_locs(string $path): array {
    static $rules = null;
    $rules ??= json_decode(file_get_contents(__DIR__ . '/../data/loc_rules.json'), true) ?: [];
    $p = trim($path, '/');
    $all = locations();
    foreach ($rules as [$re, $ids]) {
        if (preg_match('~' . $re . '~', $p)) {
            return array_values(array_filter(array_map(fn($id) => isset($all[$id]) ? ['id' => $id] + $all[$id] : null, $ids)));
        }
    }
    return [];
}

function map_embed_url(array $l): string {
    // places with their own Google listing open on the business pin; the rest are centred on the exact coordinates (pin drawn by CSS)
    if (!empty($l['cid'])) return 'https://maps.google.com/maps?cid=' . $l['cid'] . '&hl=' . LANG_CODE . '&z=17&output=embed';
    return 'https://maps.google.com/maps?q=' . $l['lat'] . ',' . $l['lng'] . '&hl=' . LANG_CODE . '&z=18&output=embed';
}
function map_dir_url(array $l): string {
    return 'https://www.google.com/maps/dir/?api=1&destination=' . $l['lat'] . ',' . $l['lng'];
}
function map_open_url(array $l): string {
    return 'https://www.google.com/maps/search/?api=1&query=' . $l['lat'] . ',' . $l['lng'];
}
