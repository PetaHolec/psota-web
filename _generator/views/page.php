<?php
/** Generic division + sub-page renderer (URL structure kept from the original site). */
$div  = DIVISIONS[$first];
$pg   = page($path);
if (!$pg) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}
$n    = count($segments);
$rel  = $segments[1] ?? '';
$leaf = $segments[$n - 1];

/* ---- breadcrumbs: division → each deeper level with a known title */
$crumbs = [];
if ($n > 1) $crumbs[] = [$div['title'], "/$first"];
for ($i = 2; $i <= $n; $i++) {
    $p = '/' . implode('/', array_slice($segments, 0, $i));
    $t = page($p)['title'] ?? ucfirst($segments[$i - 1]);
    $crumbs[] = [$t, $i === $n ? null : $p];
}
if ($n === 1) $crumbs[] = [$div['title'], null];

$h1   = $n === 1 ? $div['title'] : $pg['title'];
$lead = $n === 1 ? $div['lead'] : null;

/* ---- sidebar info: page's own, else the division's main one */
$info = $pg['info'] ?? [];
if (empty($info['hours']) && $first !== 'stk') $info = page("$first/nabidka-sluzeb")['info'] ?? [];
$hasInfo = (!empty($info['hours']) || !empty($info['contact'])) && $leaf !== 'historie-spolecnosti';
$branchesAside = $first === 'stk' && !$hasInfo;

$metaDesc = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($pg['body'] ?? ''))));
layout_start([
    'title' => $n === 1 ? $div['title'] : $pg['title'] . ' – ' . $div['title'],
    'description' => $metaDesc !== '' ? mb_strimwidth($metaDesc, 0, 158, '…') : $div['lead'],
    'path' => $path,
    'image' => $div['hero'],
    'body' => 'inner div-' . $first,
]);
page_hero($h1, $crumbs, $pg['hero'] ?? $div['hero'], $lead ?? ($n > 1 ? null : null), $n === 1 ? $div['icon'] : null);

/* ---- sub navigation */
?>
<div class="subnav" data-subnav>
  <div class="wrap subnav__in">
    <a class="subnav__home<?= $n === 1 ? ' is-on' : '' ?>" href="/<?= e($first) ?>"><?= icon($div['icon']) ?><span><?= e($div['title']) ?></span></a>
    <?php foreach ($div['menu'] as $mi): [$s, $label] = $mi; $on = $rel === $s; ?>
      <a class="<?= $on ? 'is-on' : '' ?>" href="/<?= e("$first/$s") ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
</div>
<?php foreach ($div['menu'] as $mi): if (($mi[0] ?? '') === $rel && !empty($mi[2]) && $n >= 2): ?>
<div class="subnav subnav--2"><div class="wrap subnav__in">
  <a class="<?= $n === 2 ? 'is-on' : '' ?>" href="/<?= e("$first/$rel") ?>"><?= e($mi[1]) ?></a>
  <?php foreach ($mi[2] as [$kl, $kh]): ?><a class="<?= $path === $kh || str_starts_with($path, $kh . '/') ? 'is-on' : '' ?>" href="<?= e($kh) ?>"><?= e($kl) ?></a><?php endforeach; ?>
</div></div>
<?php endif; endforeach; ?>

<section class="section section--content">
  <div class="wrap content-grid<?= ($hasInfo || $branchesAside) ? ' has-aside' : '' ?>">
    <div class="content-main">
<?php
/* ---- legacy body cleanup: drop old clip-art, pull standalone photos out of the text flow */
$body = $pg['body'] ?? '';
$leadFig = null;
foreach (['stk-hrusovany-detail.jpg' => 'Stanice STK Hrušovany', 'kontrolni_stanice.jpg' => 'Měření emisí – STK Znojmo (nákladní vozidla)'] as $f => $alt) {
    if (str_contains($body, $f)) $leadFig = [$f, $alt];
}
$driverFig = str_contains($body, 'ridic.jpg');
$schoolPhotos = [];
if ($first === 'ucnovske-stredisko' && $leaf === 'autoskola') {
    $schoolPhotos = [['autoskola_auta.jpg', 'Autoškola – výcvikový osobní vůz'], ['autoskola_c.jpg', 'Autoškola – výcvik skupiny C'], ['20220919_133554.jpg', 'Autoškola – výcvikové vozidlo'], ['autoskola-moto-a1.jpg', 'Autoškola – výcvik skupiny A1']];
}
$body = preg_replace('#<img[^>]*(transport_relace1|line\.png|paragraf\.png|loga_120|autoskola_auta|autoskola_c|20220919|autoskola-moto-a1|pdf\.png|stkbreclav_01|stkznojmo_01|stk-hrusovany-detail|kontrolni_stanice|ridic\.jpg)[^>]*>#i', '', $body) ?? $body;
$body = preg_replace('#<(strong|b|em|span|a)[^>]*>\s*</\1>#i', '', $body) ?? $body;
$body = preg_replace('#<p>\s*(<br\s*/?>|&nbsp;|\s)*</p>#i', '', $body) ?? $body;

/* ============================================================ content variants */

$isStkPicker = $first === 'stk' && ($n === 1 || ($n === 2 && in_array($rel, ['nabidka-sluzeb', 'ceniky'], true)));
$isReserve   = $first === 'stk' && $n === 2 && $rel === 'rezervace-terminu';

if ($isStkPicker) {
    $target = $rel === 'ceniky' ? 'ceniky' : 'nabidka-sluzeb';
    ?>
      <p class="lead-p reveal">Zvolte prosím vaši nejbližší pobočku. <strong>STK se zavedeným systémem bez objednání a bez čekání.</strong></p>
      <div class="branches">
        <?php foreach (BRANCHES as $bs => [$bn, $bimg, $blogo, $bd]): $bi = branch_info($bs); ?>
          <a class="bcard reveal" href="/stk/<?= e($target) ?>/<?= e($bs) ?>">
            <span class="bcard__img" style="background-image:url('<?= thumb($bimg) ?>')"></span>
            <span class="bcard__body">
              <img class="bcard__logo" src="<?= img($blogo) ?>" alt="" loading="lazy">
              <strong><?= e($bn) ?></strong>
              <small><?= e($bd) ?></small>
              <?php if ($bi): ?><span class="bcard__meta"><?= icon('pin') ?><?= e(strip_tags(explode('<br', $bi['contact'])[0])) ?></span><?= open_badge($bi['hours'] ?? []) ?><?php endif; ?>
              <span class="acard__more"><?= $target === 'ceniky' ? 'Zobrazit ceník' : 'Zobrazit nabídku' ?> <?= icon('arrow') ?></span>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php if ($n === 1): ?>
      <div class="quick reveal">
        <?php foreach ([['/stk/rezervace-terminu', 'Rezervace termínu', 'Objednejte se online na STK Znojmo.', 'clock'], ['/stk/ceniky', 'Ceníky', 'Technické kontroly a měření emisí.', 'file'], ['/stk/jak-se-pripravit-na-stk', 'Jak se připravit', 'Doklady a příprava vozidla.', 'check'], ['/stk/povinna-vybava', 'Povinná výbava', 'Co musíte mít ve vozidle.', 'car']] as [$h, $t, $d, $ic]): ?>
          <a class="qlink" href="<?= e($h) ?>"><?= icon($ic) ?><span><b><?= e($t) ?></b><small><?= e($d) ?></small></span><?= icon('arrow') ?></a>
        <?php endforeach; ?>
      </div>
      <h2 class="h-sec h-sec--sm reveal">Aktuality z STK</h2>
      <div class="news news--2">
        <?php foreach (array_slice(news_all(), 0, 4) as $i => $nw): ?>
          <a class="ncard reveal" style="--d:<?= $i % 2 ?>" href="/aktuality-stk/<?= e($nw['slug']) ?>">
            <span class="ncard__date"><?= icon('bell') ?><?= e($nw['date']) ?></span>
            <strong><?= e($nw['title']) ?></strong>
<?php if (LANG_CODE === 'cs'): ?><span class="ncard__txt"><?= e(mb_strimwidth(trim(html_entity_decode(strip_tags(str_replace(['<br />', '<br>'], ' ', $nw['body'])))), 0, 120, '…')) ?></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif;
} elseif ($isReserve) { ?>
      <p class="lead-p reveal">Zvolte prosím požadovanou stanici STK a typ vozidla:</p>
      <div class="quick quick--big">
        <a class="qlink reveal" href="/stk/rezervace-terminu/stk-znojmo-osobni"><?= icon('car') ?><span><b>STK Znojmo – osobní vozidla</b><small>Online kalendář volných termínů</small></span><?= icon('arrow') ?></a>
        <a class="qlink reveal" style="--d:1" href="/stk/rezervace-terminu/stk-znojmo-nakladni"><?= icon('truck') ?><span><b>STK Znojmo – nákladní vozidla</b><small>Online kalendář volných termínů</small></span><?= icon('arrow') ?></a>
      </div>
      <p class="muted reveal">Ostatní stanice budou postupně přidány – děkujeme za pochopení. Do ostatních poboček můžete přijet bez objednání.</p>
<?php
} elseif (!empty($pg['iframe'])) { ?>
      <?php if (str_contains($pg['iframe'], 'moje-autoskola')): ?>
        <p class="muted">Formulář se načítá z externího systému autoškoly. Pokud se nezobrazí, <a href="<?= e($pg['iframe']) ?>" target="_blank" rel="noopener">otevřete jej v novém okně</a>.</p>
      <?php else: ?>
        <p class="muted">Kalendář rezervací poskytuje DEKRA. Pokud se nezobrazí, <a href="<?= e($pg['iframe']) ?>" target="_blank" rel="noopener">otevřete jej v novém okně</a>.</p>
      <?php endif; ?>
      <div class="embed" style="--h:<?= (int)($pg['iframe_h'] ?? 900) ?>px">
        <iframe src="<?= e($pg['iframe']) ?>" title="<?= e($pg['title']) ?>" loading="lazy"></iframe>
      </div>
<?php
} elseif (!empty($pg['people'])) {
    if (trim(strip_tags($body)) !== '') { echo '<div class="prose reveal">' . prose($body) . '</div>'; }
    foreach ($pg['people'] as $sec):
        if (!$sec['people']) continue; ?>
      <?php if ($sec['title']): ?><h2 class="h-sec h-sec--sm reveal"><?= e($sec['title']) ?></h2><?php endif; ?>
      <div class="people">
        <?php foreach ($sec['people'] as $i => $p): ?>
          <article class="person reveal" style="--d:<?= $i % 3 ?>">
            <span class="person__ph"><?php if ($p['photo']): ?><img src="<?= img(preg_replace('#^/images/#', '', $p['photo'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?><?= icon('users') ?><?php endif; ?></span>
            <div>
              <h3><?= e($p['name']) ?></h3>
              <?php if ($p['role']): ?><p class="person__role"><?= e($p['role']) ?></p><?php endif; ?>
              <ul>
                <?php foreach ($p['tel'] as $t): [$lab, $num] = array_pad(explode(':', $t, 2), 2, ''); $num = trim($num); if ($num === '') continue; ?>
                  <li><?= icon('phone') ?><a href="<?= tel_href($num) ?>"><?= e($num) ?></a><small><?= e(strtoupper($lab) === 'GSM' || strtolower($lab) === 'mob' ? 'mobil' : 'pevná linka') ?></small></li>
                <?php endforeach; ?>
                <?php foreach ($p['other'] as $o): ?><li class="muted"><?= e($o) ?></li><?php endforeach; ?>
                <?php if ($p['email']): ?><li><?= icon('mail') ?><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></li><?php endif; ?>
              </ul>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endforeach;
} elseif ($leaf === 'historie-spolecnosti') {
    preg_match_all('#<strong>(\d{4})</strong>(.*?)(?=<br\s*/?>\s*<strong>\d{4}</strong>|$)#s', $pg['body'], $m, PREG_SET_ORDER); ?>
      <p class="lead-p reveal">Od jednoho malého nákladního vozidla značky AVIA po moderní vozový park Scania, Mercedes-Benz a Iveco. Projeďte si naši cestu.</p>
      <ol class="timeline" data-timeline>
        <span class="timeline__line" aria-hidden="true"><i></i></span>
        <span class="timeline__truck" aria-hidden="true"><?= icon('truck') ?></span>
        <?php foreach ($m as $i => $row): ?>
          <li class="tl reveal tl--<?= $i % 2 ? 'r' : 'l' ?>">
            <span class="tl__dot"></span>
            <div class="tl__card"><b><?= e($row[1]) ?></b><p><?= e(trim(html_entity_decode(strip_tags($row[2])))) ?></p></div>
          </li>
        <?php endforeach; ?>
      </ol>
<?php
} elseif ($leaf === 'doklady-ke-stazeni') {
    preg_match_all('#<a href="([^"]+\.pdf)"[^>]*>(.*?)</a>#s', $pg['body'], $m, PREG_SET_ORDER); ?>
      <div class="docs">
        <?php foreach ($m as $i => $row): $f = preg_replace('#^/images/#', '', $row[1]); $size = @filesize(__DIR__ . '/../../assets/images/' . $f); ?>
          <a class="doc reveal" style="--d:<?= $i % 3 ?>" href="<?= img($f) ?>" target="_blank" rel="noopener">
            <?= icon('file') ?>
            <span><b><?= e(trim(strip_tags($row[2]))) ?></b><small>PDF<?= $size ? ' · ' . round($size / 1024) . ' kB' : '' ?></small></span>
            <?= icon('arrow') ?>
          </a>
        <?php endforeach; ?>
      </div>
<?php
} elseif ($first === 'restaurace' && $leaf === 'menu') { ?>
      <div class="notice reveal"><?= icon('pin') ?><p><strong>Přestěhovali jsme se do areálu TOS, Družstevní 2825/3, Znojmo.</strong> Těšíme se na vaši návštěvu. Děkujeme.</p></div>
      <div class="menu-tabs" data-tabs>
        <div class="menu-tabs__bar" role="tablist">
          <button type="button" role="tab" class="is-on" data-tab="0">Tento týden</button>
          <button type="button" role="tab" data-tab="1">Příští týden</button>
        </div>
        <div class="menu-tabs__pane is-on"><a href="<?= img('MENU_19102026.png') ?>" data-lightbox="menu"><img src="<?= img('MENU_19102026.png') ?>" alt="Denní menu – týden od 19. 10. 2026" loading="lazy"></a></div>
        <div class="menu-tabs__pane"><a href="<?= img('menu_26102026.png') ?>" data-lightbox="menu"><img src="<?= img('menu_26102026.png') ?>" alt="Denní menu – týden od 26. 10. 2026" loading="lazy"></a></div>
      </div>
<?php
} else { ?>
      <?php if ($first === 'ucnovske-stredisko' && $leaf === 'autoskola'): ?>
        <div class="quick quick--big">
          <a class="qlink reveal" href="/ucnovske-stredisko/autoskola/elektronicka-prihlaska-do-autoskoly"><?= icon('file') ?><span><b>On-line přihláška do autoškoly</b><small>Přihlášení žáků</small></span><?= icon('arrow') ?></a>
          <a class="qlink reveal" style="--d:1" href="/ucnovske-stredisko/autoskola/prihlaseni-a-planovani-jizd"><?= icon('clock') ?><span><b>Plánování jízd</b><small>Rezervace výcvikových jízd</small></span><?= icon('arrow') ?></a>
          <a class="qlink reveal" style="--d:2" href="/ucnovske-stredisko/autoskola/on-line-prihlaseni-ke-skoleni-ridicu"><?= icon('truck') ?><span><b>Školení řidičů</b><small>Profesní způsobilost C, C+E</small></span><?= icon('arrow') ?></a>
        </div>
      <?php endif; ?>
      <?php if ($first === 'nakladni-doprava' && $leaf === 'volna-mista'): ?>
        <div class="notice notice--red reveal"><?= icon('truck') ?><p><strong>Hledáme řidiče MKD.</strong> Nástup možný ihned. Pobočky Znojmo, Hodonín, Jihlava a Kolín.</p></div>
      <?php endif; ?>
      <?php if ($leadFig): ?><figure class="figure reveal"><img src="<?= img($leadFig[0]) ?>" alt="<?= e($leadFig[1]) ?>" loading="lazy"></figure><?php endif; ?>
      <?php if ($schoolPhotos): ?><div class="photo-strip"><?php foreach ($schoolPhotos as $i => [$pf, $pa]): ?><a class="reveal" style="--d:<?= $i ?>" href="<?= img($pf) ?>" data-lightbox="school"><img src="<?= thumb($pf) ?>" alt="<?= e($pa) ?>" loading="lazy"></a><?php endforeach; ?></div><?php endif; ?>
      <?php if ($driverFig): ?>
        <div class="split"><div class="prose"><?= prose($body) ?></div><figure class="figure figure--portrait reveal"><img src="<?= img('ridic.jpg') ?>" alt="Řidič PSOTA transport před tahačem Scania" loading="lazy"></figure></div>
      <?php else: ?>
        <div class="prose"><?= prose($body) ?></div>
      <?php endif; ?>
      <?php if (($first === 'nakladni-doprava' && in_array($leaf, ['nakladni-doprava', 'nabidka-sluzeb'], true))): ?>
        <?php route_map(); ?>
        <div class="facts">
          <div class="fact reveal"><b>105</b><span>lowdeckových souprav 100 m³</span></div>
          <div class="fact reveal" style="--d:1"><b>10 mil. Kč</b><span>pojištění přepravovaného zboží</span></div>
          <div class="fact reveal" style="--d:2"><b>24/7</b><span>nonstop dispečink a GPS sledování</span></div>
          <div class="fact reveal" style="--d:3"><b>6</b><span>jazyků: CZ, DE, EN, FR, PL, RU</span></div>
        </div>
      <?php endif; ?>
<?php } ?>
      <?php if (!empty($pg['cards'])) render_cards($pg['cards']); ?>
    </div>

<?php if ($hasInfo): ?>
    <div class="content-aside"><?php info_card($info, $div['logo']); ?></div>
<?php elseif ($branchesAside): ?>
    <div class="content-aside">
      <aside class="card info reveal">
        <h3>Pobočky STK</h3>
        <?php foreach (BRANCHES as $bs => [$bn, , , $bd]): $bi = branch_info($bs); if (!$bi) continue;
            $today = $bi['hours'][0][1] ?? ''; ?>
          <a class="mini-branch" href="/stk/nabidka-sluzeb/<?= e($bs) ?>">
            <b><?= e($bn) ?></b>
            <?= open_badge($bi['hours']) ?>
            <small><?= e(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(explode('<br', $bi['contact'])[0]))))) ?></small>
          </a>
        <?php endforeach; ?>
        <p class="muted small">Změny provozní doby sledujte v <a href="/aktuality-stk">aktualitách</a>.</p>
      </aside>
    </div>
<?php endif; ?>
  </div>
</section>

<?php loc_section(page_locs($path)); ?>
<?php gallery($pg['gallery'] ?? [], 'Fotogalerie – ' . $div['title']);
layout_end();
