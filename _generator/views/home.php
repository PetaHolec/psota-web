<?php
$slides = [
    ['fotogalerie/transport/transport_006.jpg',    'Nákladní doprava',  'truck',  '/nakladni-doprava', '105 lowdeckových souprav, nepřetržitý dispečink a GPS dohled.'],
    ['fotogalerie/stk_znojmo/stkznojmo_01.jpg',    'STK a emise',       'check',  '/stk',              'Čtyři stanice technické kontroly. Bez objednání a bez čekání.'],
    ['fotogalerie/autocentrum/autocentrum_001.jpg', 'Autocentrum',      'building', '/autocentrum',      'Areál služeb nejen pro motoristy na ploše 40 000 m².'],
    ['fotogalerie/ucnovske/ucnovske_0012.jpg',     'Učňovské středisko', 'school', '/ucnovske-stredisko', 'Servis vozidel a autoškola skupin A1, B, C a C+E.'],
];
$activities = [
    ['Nákladní doprava', 'Vnitrostátní a mezinárodní kamionová doprava', '/nakladni-doprava', 'truck', 'fotogalerie/transport/transport_007.jpg', false],
    ['Přeprava osob', 'Národní autobusová doprava a MHD ve Znojmě', '/osobni-doprava', 'bus', 'ext/zds/wp-content/uploads/2024/05/head.jpg', false],
    ['Servis a mycí centrum', 'Servis osobních, dodávkových a nákladních vozidel', '/servisy', 'wrench', 'ext/ppl/media/com_twojtoolbox/P1016255.JPG', false],
    ['STK a emise', 'Stanice technických kontrol a měření emisí (Znojmo, Břeclav, Hrušovany)', '/stk', 'check', 'fotogalerie/stk_znojmo/stkznojmo_05.jpg', false],
    ['Učňovské středisko', 'Školící středisko, které poskytuje také servis vozidel a autoškolu', '/ucnovske-stredisko', 'school', 'fotogalerie/ucnovske/ucnovske_0013.jpg', false],
    ['Pronájem prostor', 'Skladové haly, kanceláře a prostory určené k podnikání', '/autocentrum', 'building', 'fotogalerie/autocentrum/autocentrum_003.jpg', false],
];
$companies = [
    ['Kamionová přeprava', 'logo/logo_transport.png', '/nakladni-doprava'],
    ['Prostory k podnikání', 'logo/logo_autocentrum.png', '/autocentrum'],
    ['Servisy vozidel a mycí centrum', 'logo/logo_ppl.png', '/servisy'],
    ['STK a měření emisí Znojmo', 'logo/logo_stkznojmo.png', '/stk/nabidka-sluzeb/stk-znojmo'],
    ['STK a měření emisí Břeclav', 'logo/logo_stkbreclav.png', '/stk/nabidka-sluzeb/stk-breclav'],
    ['Restaurace', 'logo/logo_restaurace.png', '/restaurace'],
    ['Učňovské středisko', 'logo/logo_ucnovske.png', '/ucnovske-stredisko'],
    ['STK4you Znojmo – nákladní vozidla', 'logo/logo_stk4you.png', '/stk/nabidka-sluzeb/stk-znojmo-nakladni'],
    ['STK Hrušovany', 'logo/logo_stk_hrusovany.png', '/stk/nabidka-sluzeb/stk-hrusovany'],
];
$news = array_slice(news_all(), 0, 4);

layout_start(['path' => '/', 'body' => 'home']);
?>

<!-- ============================================================ HERO -->
<section class="hero" id="top" data-hero>
  <div class="hero__slides" aria-hidden="true">
    <?php foreach ($slides as $i => $s): ?>
      <div class="hero__slide<?= $i === 0 ? ' is-on' : '' ?>" style="background-image:url('<?= img($s[0]) ?>')"></div>
    <?php endforeach; ?>
    <div class="hero__shade"></div>
  </div>
  <div class="hero__grid" aria-hidden="true"></div>

  <div class="wrap hero__in">
    <p class="eyebrow eyebrow--light"><span class="dot"></span> Znojmo · od roku 1990</p>
    <h1 class="hero__title split-words">Spolehlivý dodavatel kvalitních služeb pro všechny.</h1>
    <p class="hero__lead">Doprava, servis, STK, autoškola i restaurace na jednom místě. Regionální, celostátní i mezinárodní dopravní obslužnost a veškeré služby pro motoristy v areálu AUTOCENTRUM.</p>
    <div class="hero__cta">
      <a class="btn btn--red btn--lg magnetic" href="#cinnosti">Prozkoumat činnosti <?= icon('arrow') ?></a>
      <a class="btn btn--ghost-light btn--lg magnetic" href="/stk/rezervace-terminu">Rezervace STK</a>
    </div>

    <div class="hero__tabs" role="tablist" aria-label="Ukázky činností">
      <?php foreach ($slides as $i => $s): ?>
        <a class="hero__tab<?= $i === 0 ? ' is-on' : '' ?>" role="tab" href="<?= e($s[3]) ?>" data-slide="<?= $i ?>">
          <span class="hero__tab-ico"><?= icon($s[2]) ?></span>
          <span class="hero__tab-txt"><b><?= e($s[1]) ?></b><small><?= e($s[4]) ?></small></span>
          <i class="hero__tab-bar"></i>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="hero__road" aria-hidden="true">
    <div class="hero__truck"><?= icon('truck') ?></div>
  </div>
  <a class="hero__scroll" href="#cinnosti" aria-label="Posunout dolů"><span></span></a>
</section>

<!-- ============================================================ MARQUEE -->
<section class="marquee" aria-label="Společnosti holdingu">
  <div class="marquee__track">
    <?php for ($r = 0; $r < 2; $r++): foreach ($companies as $c): ?>
      <span class="marquee__item" <?= $r ? 'aria-hidden="true"' : '' ?>><img src="<?= img($c[1]) ?>" alt="<?= $r ? '' : e($c[0]) ?>" loading="lazy"><b><?= e($c[0]) ?></b></span>
    <?php endforeach; endfor; ?>
  </div>
</section>

<!-- ============================================================ ČINNOSTI -->
<section class="section" id="cinnosti">
  <div class="wrap">
    <header class="sec-head reveal">
      <p class="eyebrow">Činnosti holdingu</p>
      <h2 class="h-sec">Všechno, co vaše vozidlo a firma potřebují</h2>
    </header>
    <div class="cards">
      <?php foreach ($activities as $i => [$t, $d, $href, $ic, $im, $ext]): ?>
        <a class="acard reveal" style="--d:<?= $i % 3 ?>" href="<?= e($href) ?>" <?= $ext ? 'target="_blank" rel="noopener"' : '' ?>>
          <span class="acard__img" style="background-image:url('<?= thumb($im) ?>')"></span>
          <span class="acard__body">
            <span class="acard__ico"><?= icon($ic) ?></span>
            <strong><?= e($t) ?></strong>
            <small><?= e($d) ?></small>
            <span class="acard__more">Zjistit více <?= icon($ext ? 'external' : 'arrow') ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================ STATS -->
<section class="stats" data-stats>
  <div class="stats__bg" aria-hidden="true"></div>
  <div class="wrap stats__grid">
    <div class="stat reveal"><b data-count="791">0</b><span class="stat__unit">mil. Kč</span><h3>roční obrat</h3><p>Díky našemu obratu jsme schopni s vámi pracovat na jakémkoliv projektu.</p></div>
    <div class="stat reveal" style="--d:1"><b data-count="392">0</b><span class="stat__unit">lidí</span><h3>zaměstnanců</h3><p>Profesionální personál je k dispozici na všech našich pracovištích.</p></div>
    <div class="stat reveal" style="--d:2"><b data-count="<?= (int)date('Y') - 1990 ?>">0</b><span class="stat__unit">let</span><h3>na trhu</h3><p>Posbírali jsme mnoho zkušeností, které můžeme nabídnout i vám.</p></div>
    <div class="stat reveal" style="--d:3"><b data-count="138600" data-sep="1">0</b><span class="stat__unit">m²</span><h3>rozloha areálů</h3><p>Na této ploše pro vás poskytujeme prvotřídní služby.</p></div>
  </div>
</section>

<!-- ============================================================ CESTA -->
<section class="section journey">
  <div class="wrap journey__grid">
    <div class="journey__txt">
      <p class="eyebrow reveal">O holdingu</p>
      <h2 class="h-sec reveal">Už od roku 1990 plníme stále stejné poslání</h2>
      <p class="reveal">Dnešní společnosti holdingu zajišťují regionální, celostátní i mezinárodní dopravní obslužnost <strong>80 autobusy</strong>, více než <strong>100 návěsovými soupravami</strong> a dále veškeré služby pro motoristy v areálu AUTOCENTRUM.</p>
      <p class="reveal">Naleznete zde mimo centra logistiky i STK pro všechny typy vozidel, čerpací stanici na CNG, autoservisy, pneuservisy, prodejny náhradních dílů a pneu pro vozidla osobní i nákladní, servis a cejchování tachografů, myčku a učňovské středisko pro praktickou výuku v oboru automechanik. Pro návštěvníky nabízí naše restaurace oblíbenou kuchyni. V roce 2018 jsme otevřeli novou STK také v Břeclavi.</p>
      <p class="reveal"><a class="btn btn--dark" href="/o-nas">Příběh holdingu <?= icon('arrow') ?></a> <a class="btn btn--ghost" href="/nakladni-doprava/historie-spolecnosti">Historie společnosti</a></p>
    </div>
    <div class="journey__art">
      <?php
      $pins = [ // [odkaz, popisek, ikona, x %, y %, strana popisku, zpoždění]
          ['/stk',              'Stanice technických kontrol', 'check',    23.2, 80.1, 'b', 0],
          ['/osobni-doprava',   'Autobusová přeprava',         'bus',      71.8, 80.3, 'r', 1],
          ['/nakladni-doprava', 'Nákladní doprava',            'truck',    33.2, 59.8, 'l', 2],
          ['/autocentrum',      'Komerční prostory',           'building', 62.9, 43.8, 't', 3],
          ['/servisy',          'Servis a mytí',               'wrench',   88.3, 20.6, 'l', 4],
      ];
      $road = 'M30 640 C57 629 128 580 190 575 C252 570 332 602 400 610 C468 618 544 628 600 620 C656 612 726 584 735 560 C744 536 699 496 655 478 C611 460 524 463 470 455 C416 447 361 447 330 430 C299 413 277 376 285 355 C293 334 334 313 380 305 C426 297 500 305 560 308 C620 311 692 328 740 325 C788 322 837 309 850 292 C863 275 815 246 820 222 C825 198 852 168 880 150 C908 132 968 121 985 115';
      ?>
      <div class="jmap reveal" role="group" aria-label="Cesta holdingu: STK, nákladní doprava, autobusová přeprava, komerční prostory, servis a mytí">
        <svg class="jmap__svg" viewBox="0 0 1000 720" aria-hidden="true" focusable="false">
          <defs>
            <radialGradient id="jground" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#14161a" stop-opacity=".09"/><stop offset="1" stop-color="#14161a" stop-opacity="0"/></radialGradient>
            <filter id="jblur" x="-10%" y="-10%" width="120%" height="130%"><feGaussianBlur stdDeviation="9"/></filter>
            <path id="jroad" d="<?= $road ?>" fill="none"/>
            <mask id="jmask" maskUnits="userSpaceOnUse" x="0" y="0" width="1000" height="720"><path d="<?= $road ?>" class="jmap__mask" fill="none" stroke="#fff" stroke-width="150" stroke-linecap="round" pathLength="1000"/></mask>
          </defs>
          <ellipse cx="520" cy="400" rx="520" ry="330" fill="url(#jground)"/>
          <g mask="url(#jmask)">
            <use href="#jroad" fill="none" stroke="#14161a" stroke-opacity=".28" stroke-width="82" transform="translate(0 40)" filter="url(#jblur)"/>
            <use href="#jroad" fill="none" stroke="#8d939d" stroke-width="82" transform="translate(0 24)"/>
            <use href="#jroad" fill="none" stroke="#c9ced6" stroke-width="82" transform="translate(0 16)"/>
            <use href="#jroad" fill="none" stroke="#e8eaee" stroke-width="82" transform="translate(0 8)"/>
            <use href="#jroad" fill="none" stroke="#1b1d22" stroke-width="76"/>
            <use href="#jroad" fill="none" stroke="#fff" stroke-opacity=".85" stroke-width="68"/>
            <use href="#jroad" fill="none" stroke="#1b1d22" stroke-width="64"/>
            <use href="#jroad" class="jmap__dash" fill="none" stroke="#fff" stroke-width="3.5" stroke-dasharray="20 16"/>
          </g>
          <g class="jmap__veh">
            <g><ellipse cx="0" cy="5" rx="22" ry="8" fill="#000" opacity=".28"/><rect x="-21" y="-9" width="42" height="18" rx="5" fill="#d9141b"/><rect x="-21" y="-9" width="42" height="7" rx="4" fill="#ef3a40"/><rect x="-14" y="-6" width="24" height="12" rx="2.5" fill="#ffe3e4" opacity=".92"/><path d="M-6 -6v12M2 -6v12" stroke="#d9141b" stroke-width="1.2"/><rect x="13" y="-6" width="6" height="12" rx="2" fill="#7d0a0f"/>
              <animateMotion dur="38s" begin="-6s" repeatCount="indefinite" rotate="auto"><mpath href="#jroad"/></animateMotion></g>
            <g><ellipse cx="0" cy="5" rx="30" ry="8" fill="#000" opacity=".28"/><rect x="-30" y="-8" width="38" height="16" rx="3" fill="#f2f3f5"/><rect x="10" y="-8" width="16" height="16" rx="3.5" fill="#2b2e35"/><rect x="19" y="-6" width="6" height="12" rx="2" fill="#7fb4e6"/>
              <animateMotion dur="46s" begin="-24s" repeatCount="indefinite" rotate="auto"><mpath href="#jroad"/></animateMotion></g>
          </g>
        </svg>
        <ol class="jmap__pins">
          <?php foreach ($pins as [$href, $label, $ico, $x, $y, $side, $i]): ?>
          <li class="pin pin--<?= $side ?>" style="--x:<?= $x ?>;--y:<?= $y ?>;--i:<?= $i ?>">
            <a href="<?= $href ?>">
              <i class="pin__sh" aria-hidden="true"></i><i class="pin__ring" aria-hidden="true"></i>
              <svg class="pin__drop" viewBox="0 0 48 64" aria-hidden="true"><path class="pin__a" d="M24 62C24 62 3 38 3 22a21 21 0 0 1 42 0c0 16-21 40-21 40z"/><path class="pin__b" d="M24 62C24 62 45 38 45 22A21 21 0 0 0 24 1z"/><circle cx="24" cy="22" r="14.5" fill="#fff"/></svg>
              <span class="pin__ico"><?= icon($ico) ?></span>
              <b class="pin__label"><?= e($label) ?></b>
            </a>
          </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ SPOLEČNOSTI -->
<section class="section section--tint">
  <div class="wrap">
    <header class="sec-head reveal">
      <p class="eyebrow">Společnosti holdingu</p>
      <h2 class="h-sec">Skupina, která táhne za jeden provaz</h2>
    </header>
    <div class="logos">
      <?php foreach ($companies as $i => [$t, $logo, $href]): $ext = str_starts_with($href, 'http'); ?>
        <a class="logo-tile reveal" style="--d:<?= $i % 3 ?>" href="<?= e($href) ?>" <?= $ext ? 'target="_blank" rel="noopener"' : '' ?>>
          <img src="<?= img($logo) ?>" alt="" loading="lazy">
          <span><?= e($t) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================ STK BAND -->
<section class="band">
  <div class="band__bg" style="background-image:url('<?= img('fotogalerie/stk_breclav/stkbreclav_01.jpg') ?>')"></div>
  <div class="wrap band__in">
    <div class="band__txt reveal">
      <p class="eyebrow eyebrow--light">STK a měření emisí</p>
      <h2>STK se zavedeným systémem bez objednání a bez čekání.</h2>
      <p>Vyberte nejbližší pobočku. Prohlídky provádíme ve Znojmě, Břeclavi a Hrušovanech nad Jevišovkou, nákladní vozidla na STK4you.</p>
      <p><a class="btn btn--red btn--lg magnetic" href="/stk">Vybrat pobočku <?= icon('arrow') ?></a> <a class="btn btn--ghost-light btn--lg magnetic" href="/stk/ceniky">Ceníky</a></p>
    </div>
    <ul class="band__list reveal" style="--d:1">
      <?php foreach (BRANCHES as $bs => [$bn, $bimg, $blogo, $bd]): $bi = branch_info($bs); ?>
        <li><a href="/stk/nabidka-sluzeb/<?= e($bs) ?>"><span><b><?= e($bn) ?></b><small><?= e($bd) ?></small></span><?= $bi ? open_badge($bi['hours'] ?? []) : '' ?><?= icon('arrow') ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ============================================================ AKTUALITY -->
<section class="section">
  <div class="wrap">
    <header class="sec-head sec-head--row reveal">
      <div><p class="eyebrow">Aktuality</p><h2 class="h-sec">Oznámení pro zákazníky</h2></div>
      <a class="btn btn--ghost" href="/aktuality-stk">Všechny aktuality <?= icon('arrow') ?></a>
    </header>
    <div class="news">
      <?php foreach ($news as $i => $n): ?>
        <a class="ncard reveal" style="--d:<?= $i ?>" href="/aktuality-stk/<?= e($n['slug']) ?>">
          <span class="ncard__date"><?= icon('bell') ?><?= e($n['date']) ?></span>
          <strong><?= e($n['title']) ?></strong>
<?php if (LANG_CODE === 'cs'): ?><span class="ncard__txt"><?= e(mb_strimwidth(trim(html_entity_decode(strip_tags(str_replace(['<br />', '<br>'], ' ', $n['body'])))), 0, 130, '…')) ?></span><?php endif; ?>
          <span class="acard__more">Celý článek <?= icon('arrow') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================ KONTAKT -->
<section class="section section--dark contact-sec" id="kontakt">
  <div class="wrap contact__grid">
    <div class="reveal">
      <p class="eyebrow eyebrow--light">Napište nám</p>
      <h2 class="h-sec h-sec--light">Máte dotaz nebo poptávku?</h2>
      <ul class="contact__list">
        <li><?= icon('pin') ?><span><?= e(HOLDING['street']) ?>, <?= e(HOLDING['city']) ?></span></li>
        <li><?= icon('phone') ?><a href="<?= tel_href(HOLDING['phone']) ?>"><?= e(HOLDING['phone']) ?></a></li>
        <li><?= icon('mail') ?><a href="mailto:<?= e(HOLDING['email']) ?>"><?= e(HOLDING['email']) ?></a></li>
      </ul>
      <div class="map map--sm">
        <iframe title="Mapa – areál AUTOCENTRUM PSOTA, Dobšická 3697/6, Znojmo" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen src="<?= e(map_embed_url(locations()['areal'])) ?>"></iframe>
      </div>
    </div>
    <div class="card card--form reveal" style="--d:1">
      <?php contact_form('cf-home'); ?>
    </div>
  </div>
</section>

<?php layout_end();
