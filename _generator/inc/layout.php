<?php
declare(strict_types=1);

function layout_start(array $m = []): void {
    $title = ($m['title'] ?? '') ? $m['title'] . ' | PSOTA Group' : 'PSOTA Group – doprava, servis, STK, autoškola a restaurace ve Znojmě';
    $desc  = $m['description'] ?? 'PSOTA holding Znojmo: nákladní a osobní doprava, STK a měření emisí, autoservisy a mytí, učňovské středisko s autoškolou, restaurace a pronájem prostor. Od roku 1990.';
    $ogImg = SITE_URL . img($m['image'] ?? 'facebook_preview.jpg');
    $path  = $m['path'] ?? '/';
    $nav   = nav();
    $bodyClass = $m['body'] ?? '';
    ?><!doctype html>
<html lang="<?= LANG_CODE ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e(SITE_URL . lang_path($path, LANG_CODE)) ?>">
<?php foreach (["cs","en","de","pl"] as $lc): if ($lc !== "cs" && is_old_news($path)) continue; ?><link rel="alternate" hreflang="<?= $lc ?>" href="<?= e(SITE_URL . lang_path($path, $lc)) ?>">
<?php endforeach; ?>
<meta property="og:type" content="website">
<meta property="og:locale" content="<?= ["cs"=>"cs_CZ","en"=>"en_US","de"=>"de_DE","pl"=>"pl_PL"][LANG_CODE] ?>">
<meta property="og:site_name" content="PSOTA Group">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e($ogImg) ?>">
<meta name="theme-color" content="#d9141b">
<link rel="icon" type="image/png" sizes="32x32" href="<?= img('favicon/favicon-32x32.png') ?>">
<link rel="icon" type="image/png" sizes="96x96" href="<?= img('favicon/favicon-96x96.png') ?>">
<link rel="apple-touch-icon" href="<?= img('favicon/apple-icon-180x180.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap&subset=latin-ext" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'PSOTA holding', 'url' => SITE_URL,
    'logo' => SITE_URL . img('logo/logo_group.png'), 'foundingDate' => '1990',
    'telephone' => HOLDING['phone'], 'email' => HOLDING['email'],
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => HOLDING['street'], 'postalCode' => '669 02', 'addressLocality' => 'Znojmo', 'addressCountry' => 'CZ'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip" href="#obsah">Přeskočit na obsah</a>
<div class="progress" aria-hidden="true"><i></i></div>

<header class="header" data-header>
  <div class="wrap header__in">
    <a class="brand" href="/" aria-label="PSOTA holding – úvodní strana"><img src="<?= img('holding_logo_red_black_60.png') ?>" alt="PSOTA holding" width="435" height="60"></a>
    <nav class="nav" aria-label="Hlavní navigace" id="nav">
      <ul class="nav__list">
        <?php foreach ($nav as $slug => $it): $active = str_starts_with($path, "/$slug"); ?>
          <li class="nav__item has-sub<?= $active ? ' is-active' : '' ?>">
            <a class="nav__link" href="<?= e($it['href']) ?>"><?= e($it['label']) ?></a>
            <button type="button" class="nav__more" aria-label="Rozbalit <?= e($it['label']) ?>" aria-expanded="false"><?= icon('down') ?></button>
            <div class="sub">
              <ul>
                <?php foreach ($it['children'] as $c): ?>
                  <li class="<?= $c['kids'] ? 'has-kids' : '' ?>">
                    <a href="<?= e($c['href']) ?>"><?= e($c['label']) ?></a>
                    <?php if ($c['kids']): ?>
                      <ul class="kids"><?php foreach ($c['kids'] as [$kl, $kh]): ?><li><a href="<?= e($kh) ?>"><?= e($kl) ?></a></li><?php endforeach; ?></ul>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </li>
        <?php endforeach; ?>
        <li class="nav__item<?= $path === '/kontakt' ? ' is-active' : '' ?>"><a class="nav__link" href="/kontakt">Kontakt</a></li>
      </ul>
    </nav>
    <div class="langs" data-lang role="group" aria-label="Jazyk webu"><a class="langs__btn<?= LANG_CODE === 'cs' ? ' is-on' : '' ?>" data-nolang href="<?= e(lang_path($path, 'cs')) ?>" hreflang="cs" lang="cs" aria-label="Čeština" title="Čeština"><svg class="flag" viewBox="0 0 24 16" aria-hidden="true"><rect width="24" height="8" fill="#fff"/><rect y="8" width="24" height="8" fill="#d7141a"/><path d="M0 0l12 8L0 16z" fill="#11457e"/></svg><span>CS</span></a><a class="langs__btn<?= LANG_CODE === 'en' ? ' is-on' : '' ?>" data-nolang href="<?= e(lang_path($path, 'en')) ?>" hreflang="en" lang="en" aria-label="English" title="English"><svg class="flag" viewBox="0 0 24 16" aria-hidden="true"><rect width="24" height="16" fill="#012169"/><path d="M0 0l24 16M24 0L0 16" stroke="#fff" stroke-width="3.2"/><path d="M0 0l24 16M24 0L0 16" stroke="#c8102e" stroke-width="1.4"/><path d="M12 0v16M0 8h24" stroke="#fff" stroke-width="5"/><path d="M12 0v16M0 8h24" stroke="#c8102e" stroke-width="3"/></svg><span>EN</span></a><a class="langs__btn<?= LANG_CODE === 'de' ? ' is-on' : '' ?>" data-nolang href="<?= e(lang_path($path, 'de')) ?>" hreflang="de" lang="de" aria-label="Deutsch" title="Deutsch"><svg class="flag" viewBox="0 0 24 16" aria-hidden="true"><rect width="24" height="5.34" fill="#000"/><rect y="5.33" width="24" height="5.34" fill="#d00"/><rect y="10.66" width="24" height="5.34" fill="#ffce00"/></svg><span>DE</span></a><a class="langs__btn<?= LANG_CODE === 'pl' ? ' is-on' : '' ?>" data-nolang href="<?= e(lang_path($path, 'pl')) ?>" hreflang="pl" lang="pl" aria-label="Polski" title="Polski"><svg class="flag" viewBox="0 0 24 16" aria-hidden="true"><rect width="24" height="8" fill="#fff"/><rect y="8" width="24" height="8" fill="#dc143c"/></svg><span>PL</span></a></div>
    <button class="burger" type="button" aria-label="Menu" aria-expanded="false" aria-controls="nav"><?= icon('menu', 'ico ico--open') ?><?= icon('close', 'ico ico--close') ?></button>
  </div>
</header>

<main id="obsah">
<?php
}

function layout_end(): void {
    $nav = nav();
    ?>
</main>

<footer class="footer">
  <div class="footer__road" aria-hidden="true"><i></i></div>
  <div class="wrap footer__grid">
    <div class="footer__brand">
      <img src="<?= img('logo/logo_group.png') ?>" alt="PSOTA Group" width="180" height="180" loading="lazy">
      <p>Od roku 1990 spolehlivý dodavatel kvalitních služeb pro všechny.</p>
    </div>
    <div>
      <h3>PSOTA holding</h3>
      <address>
        <p><?= icon('pin') ?><span><?= e(HOLDING['street']) ?><br><?= e(HOLDING['city']) ?></span></p>
        <p><?= icon('phone') ?><a href="<?= tel_href(HOLDING['phone']) ?>"><?= e(HOLDING['phone']) ?></a></p>
        <p><?= icon('mail') ?><a href="mailto:<?= e(HOLDING['email']) ?>"><?= e(HOLDING['email']) ?></a></p>
      </address>
      <p><a class="btn btn--ghost btn--sm" href="https://www.google.com/maps?q=<?= HOLDING['lat'] ?>,<?= HOLDING['lng'] ?>" target="_blank" rel="noopener">Navigovat <?= icon('arrow') ?></a></p>
    </div>
    <div>
      <h3>Činnosti</h3>
      <ul>
        <li><a href="/nakladni-doprava">Nákladní doprava</a></li>
        <li><a href="/osobni-doprava">Osobní doprava</a></li>
        <li><a href="/servisy">Servisy a mytí</a></li>
        <li><a href="/servisy/elektroauta">Elektroauta</a></li>
        <li><a href="/stk">STK a emise</a></li>
        <li><a href="/ucnovske-stredisko">Učňovské středisko</a></li>
        <li><a href="/ucnovske-stredisko/autoskola">Autoškola</a></li>
        <li><a href="/restaurace">Restaurace</a></li>
        <li><a href="/autocentrum">Autocentrum a pronájem</a></li>
      </ul>
    </div>
    <div>
      <h3>Rychlé odkazy</h3>
      <ul>
        <li><a href="/o-nas">O holdingu</a></li>
        <li><a href="/stk/rezervace-terminu">Rezervace STK</a></li>
        <li><a href="/stk/ceniky">Ceníky STK</a></li>
        <li><a href="/nakladni-doprava/volna-mista">Volná místa – řidič MKD</a></li>
        <li><a href="/restaurace/menu">Denní menu</a></li>
        <li><a href="/aktuality-stk">Aktuality</a></li>
        <li><a href="/kontakt">Kontakt a formulář</a></li>
      </ul>
    </div>
  </div>
  <div class="footer__pay">
    <div class="wrap footer__pay-in"><p class="footer__label">Přijímáme platební karty</p><?php payment_cards(); ?></div>
  </div>
  <div class="footer__bar">
    <div class="wrap footer__bar-in">
      <span>© <?= date('Y') ?> PSOTA holding, Znojmo. Všechna práva vyhrazena.</span>
      <div class="footer__end">
        <a href="#top" data-top>Nahoru ↑</a>
        <a class="footer__credit" href="https://skocdal.cz" target="_blank" rel="noopener">Tvorba webu <strong>SkočDál</strong></a>
      </div>
    </div>
  </div>
</footer>

<script src="<?= asset('js/main.js') ?>?v=<?= filemtime(__DIR__ . '/../../assets/js/main.js') ?>" defer></script>
</body>
</html>
<?php
}

/** Hero band for inner pages. */
function page_hero(string $title, array $crumbs = [], ?string $image = null, ?string $lead = null, ?string $iconName = null): void { ?>
<section class="phero" id="top">
  <?php if ($image): ?><div class="phero__bg" style="background-image:url('<?= e(thumb_or_img($image)) ?>')"></div><?php endif; ?>
  <div class="phero__road" aria-hidden="true"></div>
  <div class="wrap phero__in">
    <nav class="crumbs" aria-label="Drobečková navigace">
      <a href="/">Domů</a>
      <?php foreach ($crumbs as [$l, $h]): ?><span>/</span><?php if ($h): ?><a href="<?= e($h) ?>"><?= e($l) ?></a><?php else: ?><em><?= e($l) ?></em><?php endif; ?><?php endforeach; ?>
    </nav>
    <h1 class="split-words"><?php if ($iconName): ?><span class="phero__ico"><?= icon($iconName) ?></span><?php endif; ?><?= e($title) ?></h1>
    <?php if ($lead): ?><p class="phero__lead"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
<?php }

function thumb_or_img(string $p): string { return img($p); }

/** Contact form (used on home + /kontakt). */
function contact_form(string $id = 'cf'): void { ?>
<form class="cform" id="<?= e($id) ?>" action="mailto:info@psota-znojmo.cz" method="post" enctype="text/plain" novalidate data-contact>
  <div class="cform__row">
    <label class="field"><span>Jméno</span><input type="text" name="name" required autocomplete="name" placeholder="Vaše jméno"></label>
    <label class="field"><span>E-mail</span><input type="email" name="email" required autocomplete="email" placeholder="vas@email.cz"></label>
  </div>
  <label class="field"><span>Zpráva</span><textarea name="message" rows="5" required placeholder="Jak vám můžeme pomoci?"></textarea></label>
    <label class="check"><input type="checkbox" name="consent" required><span>Potvrzuji seznámení s informacemi o zpracování osobních údajů, které budou použity pouze za účelem kontaktování zákazníka.</span></label>
  <div class="cform__foot">
    <button class="btn btn--red" type="submit"><span>Odeslat zprávu</span><?= icon('arrow') ?></button>
    <p class="cform__msg" role="status" aria-live="polite"></p>
  </div>
</form>
<?php }

/** Sidebar info card for a company/branch. */
function info_card(array $info, string $fallbackLogo = ''): void {
    $logo = !empty($info['logo']) ? preg_replace('#^/images/#', '', $info['logo']) : $fallbackLogo; ?>
<aside class="card info reveal">
  <?php if ($logo): ?><img class="info__logo" src="<?= img($logo) ?>" alt="" loading="lazy"><?php endif; ?>
  <?php if (!empty($info['name'])): ?><h3><?= e($info['name']) ?></h3><?php endif; ?>
  <?php if (!empty($info['contact'])): ?><div class="info__contact"><?= prose_contact($info['contact']) ?></div><?php endif; ?>
  <?php if (!empty($info['hours'])): ?>
    <h4><?= icon('clock') ?><?= e($info['hours_title'] ?? 'Provozní doba') ?> <?= open_badge($info['hours']) ?></h4>
    <table class="hours" data-hours-table>
      <?php foreach ($info['hours'] as [$d, $t]): ?><tr data-day="<?= e(trim($d)) ?>"><td><?= e($d) ?></td><td><?= e($t) ?></td></tr><?php endforeach; ?>
    </table>
    <p class="muted small">Aktuální změny sledujte v <a href="/aktuality-stk">aktualitách</a>.</p>
  <?php endif; ?>
</aside>
<?php }

function prose_contact(string $html): string {
    $html = preg_replace('#color:[^;"]+;?#', '', $html) ?? $html;
    $html = str_replace("\xc2\xa0", ' ', $html);
    return strip_tags($html, '<p><br><a><strong>');
}

/** "Kde nás najdete": one Google map card per exact place of the service. */
function loc_section(array $locs, string $tint = 'tint'): void {
    if (!$locs) return; ?>
<section class="section section--<?= $tint ?> locsec">
  <div class="wrap">
    <header class="sec-head reveal"><p class="eyebrow">Mapa</p><h2 class="h-sec">Kde nás najdete</h2></header>
    <div class="locgrid locgrid--<?= min(count($locs), 4) ?>">
      <?php foreach ($locs as $i => $l): ?>
      <article class="loc reveal" style="--d:<?= $i % 2 ?>">
        <div class="loc__map<?= empty($l['cid']) ? ' loc__map--pin' : '' ?>"><iframe title="<?= e($l['name']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen src="<?= e(map_embed_url($l)) ?>"></iframe><?php if (empty($l['cid'])): ?><i class="loc__pin" aria-hidden="true"></i><?php endif; ?></div>
        <div class="loc__body">
          <h3><?= e($l['name']) ?></h3>
          <p><?= icon('pin') ?><span><?= e($l['addr']) ?></span></p>
          <p class="loc__btns"><a class="btn btn--dark btn--sm" href="<?= e(map_dir_url($l)) ?>" target="_blank" rel="noopener">Navigovat <?= icon('arrow') ?></a> <a class="btn btn--ghost btn--sm" href="<?= e(map_open_url($l)) ?>" target="_blank" rel="noopener">Otevřít v Mapách Google</a></p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php }

function gallery(array $files, string $label = 'Fotogalerie'): void {
    if (!$files) return; ?>
<section class="section section--tint">
  <div class="wrap">
    <h2 class="h-sec reveal"><?= e($label) ?></h2>
    <div class="gallery">
      <?php foreach ($files as $i => $f): $f = preg_replace('#^/images/#', '', $f); ?>
        <a class="gallery__item reveal" style="--d:<?= $i % 6 ?>" href="<?= img($f) ?>" data-lightbox="g">
          <img src="<?= thumb($f) ?>" alt="<?= e($label) ?> – fotografie <?= $i + 1 ?>" loading="lazy">
          <span class="gallery__zoom"><?= icon('plus') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php }

/** Colourful payment-card badges (inline SVG, replaces the old grey bitmap). */
function payment_cards(): void {
    $b = 'viewBox="0 0 56 36" width="56" height="36" role="img"'; ?>
<ul class="pay" aria-label="Přijímáme platební karty">
  <li title="Visa"><svg <?= $b ?> aria-label="Visa"><rect width="56" height="36" rx="6" fill="#fff"/><rect y="0" width="56" height="5" rx="2" fill="#1a1f71"/><rect y="31" width="56" height="5" rx="2" fill="#f7a823"/><text x="28" y="23.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="900" font-style="italic" font-size="15" fill="#1a1f71">VISA</text></svg></li>
  <li title="Mastercard"><svg <?= $b ?> aria-label="Mastercard"><defs><clipPath id="mcc"><circle cx="33" cy="18" r="10"/></clipPath></defs><rect width="56" height="36" rx="6" fill="#fff"/><circle cx="23" cy="18" r="10" fill="#eb001b"/><circle cx="33" cy="18" r="10" fill="#f79e1b"/><circle cx="23" cy="18" r="10" fill="#ff5f00" clip-path="url(#mcc)"/></svg></li>
  <li title="Maestro"><svg <?= $b ?> aria-label="Maestro"><defs><clipPath id="mac"><circle cx="33" cy="15" r="9"/></clipPath></defs><rect width="56" height="36" rx="6" fill="#fff"/><circle cx="23" cy="15" r="9" fill="#e9202b"/><circle cx="33" cy="15" r="9" fill="#0099df"/><circle cx="23" cy="15" r="9" fill="#6c6bbd" clip-path="url(#mac)"/><text x="28" y="31.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="700" font-size="7" fill="#1a1f71">maestro</text></svg></li>
  <li title="American Express"><svg <?= $b ?> aria-label="American Express"><rect width="56" height="36" rx="6" fill="#2e77bb"/><text x="28" y="22" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="900" font-size="12" letter-spacing=".5" fill="#fff">AMEX</text></svg></li>
  <li title="Diners Club"><svg <?= $b ?> aria-label="Diners Club"><rect width="56" height="36" rx="6" fill="#fff"/><circle cx="28" cy="15" r="9.5" fill="#0079be"/><circle cx="28" cy="15" r="6.5" fill="#fff"/><path d="M25 9.6v10.8M31 9.6v10.8" stroke="#0079be" stroke-width="2.2"/><text x="28" y="31.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="700" font-size="6" fill="#0079be">Diners Club</text></svg></li>
  <li title="JCB"><svg <?= $b ?> aria-label="JCB"><rect width="56" height="36" rx="6" fill="#fff"/><rect x="9" y="6" width="12" height="24" rx="4" fill="#0e4c96"/><rect x="22" y="6" width="12" height="24" rx="4" fill="#d3202f"/><rect x="35" y="6" width="12" height="24" rx="4" fill="#17a14c"/><g font-family="Arial,Helvetica,sans-serif" font-weight="900" font-size="11" fill="#fff" text-anchor="middle"><text x="15" y="23">J</text><text x="28" y="23">C</text><text x="41" y="23">B</text></g></svg></li>
  <li title="V PAY"><svg <?= $b ?> aria-label="V PAY"><rect width="56" height="36" rx="6" fill="#fff"/><text x="17" y="25" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="900" font-style="italic" font-size="22" fill="#1a1f71">V</text><text x="39" y="24" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="800" font-size="11" fill="#f7a823">PAY</text></svg></li>
</ul>
<?php }

/** Animated map of the carrier's routes drawn on a real (Natural Earth, public domain) map of Europe. */
function route_map(): void {
    $geo = json_decode((string)file_get_contents(__DIR__ . '/../data/europe.json'), true);
    $flag = [
        'cz' => '<rect width="24" height="8" fill="#fff"/><rect y="8" width="24" height="8" fill="#d7141a"/><path d="M0 0l12 8L0 16z" fill="#11457e"/>',
        'de' => '<rect width="24" height="5.34" fill="#000"/><rect y="5.33" width="24" height="5.34" fill="#d00"/><rect y="10.66" width="24" height="5.34" fill="#ffce00"/>',
        'pl' => '<rect width="24" height="8" fill="#fff"/><rect y="8" width="24" height="8" fill="#dc143c"/>',
        'ua' => '<rect width="24" height="8" fill="#0057b7"/><rect y="8" width="24" height="8" fill="#ffd700"/>',
        'sk' => '<rect width="24" height="5.34" fill="#fff"/><rect y="5.33" width="24" height="5.34" fill="#0b4ea2"/><rect y="10.66" width="24" height="5.34" fill="#ee1c25"/><path d="M6 3.5h6v6.5c0 2-3 3.2-3 3.2S6 12 6 10z" fill="#ee1c25" stroke="#fff" stroke-width="1"/>',
        'hu' => '<rect width="24" height="5.34" fill="#cd2a3e"/><rect y="5.33" width="24" height="5.34" fill="#fff"/><rect y="10.66" width="24" height="5.34" fill="#436f4d"/>',
        'at' => '<rect width="24" height="5.34" fill="#ed2939"/><rect y="5.33" width="24" height="5.34" fill="#fff"/><rect y="10.66" width="24" height="5.34" fill="#ed2939"/>',
        'it' => '<rect width="8" height="16" fill="#009246"/><rect x="8" width="8" height="16" fill="#fff"/><rect x="16" width="8" height="16" fill="#ce2b37"/>',
        'fr' => '<rect width="8" height="16" fill="#0055a4"/><rect x="8" width="8" height="16" fill="#fff"/><rect x="16" width="8" height="16" fill="#ef4135"/>',
    ];
    $labels = ['de' => 'Německo', 'pl' => 'Polsko', 'ua' => 'Ukrajina', 'sk' => 'Slovensko', 'hu' => 'Maďarsko', 'at' => 'Rakousko', 'it' => 'Itálie', 'fr' => 'Francie'];
    $bend   = ['de' => -1, 'pl' => -1, 'ua' => -1, 'sk' => 1, 'hu' => 1, 'at' => 1, 'it' => 1, 'fr' => -1];
    $off = ['de' => [-10, -9], 'pl' => [3, -11], 'ua' => [4, -9], 'sk' => [12, -1], 'hu' => [11, 8], 'at' => [-10, 7], 'it' => [10, 3], 'fr' => [-6, -9]];
    $W = (float)$geo['w']; $H = (float)$geo['h']; $pos = $geo['pos']; $hub = $pos['hub']; ?>
<section class="rmap reveal" aria-labelledby="rmap-h">
  <div class="rmap__head">
    <p class="eyebrow eyebrow--light">Naše trasy</p>
    <h2 id="rmap-h">Kam pravidelně jezdíme</h2>
    <p>Specializujeme se na přepravy mezi ČR a Rakouskem včetně Tyrolska. Pravidelně jezdíme také do Slovenska, Polska, Maďarska, Německa, Itálie, Francie a na Ukrajinu.</p>
  </div>
  <div class="rmap__scroll">
  <div class="rmap__stage" style="aspect-ratio:<?= $W ?>/<?= $H ?>" role="img" aria-label="Mapa Evropy s trasami ze Znojma">
    <svg class="rmap__geo" viewBox="0 0 <?= $W ?> <?= $H ?>" aria-hidden="true">
      <?php foreach ($geo['countries'] as $c): ?><path class="rmap__c<?= $c['code'] ? ' is-route' : '' ?><?= $c['code'] === 'cz' ? ' is-home' : '' ?>" d="<?= $c['d'] ?>"/><?php endforeach; ?>
    </svg>
    <svg class="rmap__lines" viewBox="0 0 <?= $W ?> <?= $H ?>" aria-hidden="true">
      <?php $i = 0; foreach ($labels as $c => $lab): $i++;
        $x1 = $hub[0] / 100 * $W; $y1 = $hub[1] / 100 * $H; $x2 = $pos[$c][0] / 100 * $W; $y2 = $pos[$c][1] / 100 * $H;
        $mx = ($x1 + $x2) / 2; $my = ($y1 + $y2) / 2; $dx = $x2 - $x1; $dy = $y2 - $y1; $len = max(1, sqrt($dx * $dx + $dy * $dy));
        $cx = $mx - $dy / $len * $len * .22 * $bend[$c]; $cy = $my + $dx / $len * $len * .22 * $bend[$c];
        $d = sprintf('M%.1f %.1f Q%.1f %.1f %.1f %.1f', $x1, $y1, $cx, $cy, $x2, $y2); ?>
        <path class="rmap__path" d="<?= $d ?>" id="rp-<?= $c ?>"/>
        <circle class="rmap__dot" r="4.5"><animateMotion dur="<?= round(3.2 + $i * 0.35, 2) ?>s" repeatCount="indefinite" begin="<?= round($i * 0.4, 1) ?>s"><mpath href="#rp-<?= $c ?>"/></animateMotion></circle>
        <line class="rmap__leader" x1="<?= round($x2, 1) ?>" y1="<?= round($y2, 1) ?>" x2="<?= round(($pos[$c][0] + $off[$c][0]) / 100 * $W, 1) ?>" y2="<?= round(($pos[$c][1] + $off[$c][1]) / 100 * $H, 1) ?>"/>
        <circle class="rmap__pin" cx="<?= round($x2, 1) ?>" cy="<?= round($y2, 1) ?>" r="4.2"/>
      <?php endforeach; ?>
    </svg>
    <div class="rmap__hub" style="left:<?= $hub[0] ?>%;top:<?= $hub[1] ?>%">
      <span class="rmap__pulse"></span><span class="rmap__pulse rmap__pulse--2"></span>
      <span class="rmap__hub-ico"><?= icon('truck') ?></span>
      <b>Znojmo</b>
    </div>
    <?php foreach ($labels as $c => $lab): ?>
      <div class="rmap__node" style="left:<?= round($pos[$c][0] + $off[$c][0], 2) ?>%;top:<?= round($pos[$c][1] + $off[$c][1], 2) ?>%">
        <svg class="rmap__flag" viewBox="0 0 24 16" aria-hidden="true"><?= $flag[$c] ?></svg>
        <span><?= e($lab) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  </div>
  <ul class="rmap__legend">
    <li><?= icon('check') ?>Celovozové i kusové zásilky 0,5–24 t</li>
    <li><?= icon('check') ?>Kusové zboží 0,5–10 t v relaci Vídeň–ČR</li>
    <li><?= icon('check') ?>ADR zboží mimo tříd 1 a 7</li>
    <li><?= icon('check') ?>Zboží pojištěné na 10 mil. Kč, GPS dohled 24/7</li>
  </ul>
  <p class="rmap__credit">Mapové podklady: Natural Earth (public domain)</p>
</section>
<?php }

/** Card grid for section overviews (pages.json "cards"). */
function render_cards(array $cards): void {
    if (!$cards) return; ?>
<div class="cards cards--sub">
  <?php foreach ($cards as $i => $c): $ext = str_starts_with($c['href'], 'http'); ?>
    <a class="acard reveal" style="--d:<?= $i % 3 ?>" href="<?= e($c['href']) ?>" <?= $ext ? 'target="_blank" rel="noopener"' : '' ?>>
      <?php if (!empty($c['img'])): ?><span class="acard__img" style="background-image:url('<?= thumb($c['img']) ?>')"></span>
      <?php else: ?><span class="acard__img acard__img--ico"><?= icon($c['icon'] ?? 'arrow') ?></span><?php endif; ?>
      <span class="acard__body">
        <span class="acard__ico"><?= icon($c['icon'] ?? 'arrow') ?></span>
        <strong><?= e($c['title']) ?></strong>
        <?php if (!empty($c['desc'])): ?><small><?= e($c['desc']) ?></small><?php endif; ?>
        <?php if (!empty($c['meta'])): ?><span class="acard__meta"><?php foreach ($c['meta'] as [$k, $v]): ?><i><b><?= e((string)$v) ?></b><?= e($k) ?></i><?php endforeach; ?></span><?php endif; ?>
        <span class="acard__more">Zjistit více <?= icon('arrow') ?></span>
      </span>
    </a>
  <?php endforeach; ?>
</div>
<?php }
