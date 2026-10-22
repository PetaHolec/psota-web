<?php
layout_start(['title' => 'O holdingu', 'description' => 'PSOTA holding: od roku 1990 spolehlivý dodavatel kvalitních služeb. Doprava, servis, STK, učňovské středisko a restaurace ve Znojmě.', 'path' => '/o-nas', 'body' => 'inner']);
page_hero('O holdingu', [['O holdingu', null]], 'fotogalerie/transport/transport_007.jpg', 'Od jednoho nákladního vozu k holdingu s 392 zaměstnanci a obratem 791 milionů korun.', 'building');
$hist = page('nakladni-doprava/historie-spolecnosti');
preg_match_all('#<strong>(\d{4})</strong>(.*?)(?=<br\s*/?>\s*<strong>\d{4}</strong>|$)#s', $hist['body'] ?? '', $m, PREG_SET_ORDER);
$pick = array_filter($m, fn($r) => in_array($r[1], ['1990', '1997', '2001', '2004', '2012', '2018'], true));
?>
<section class="section section--content">
  <div class="wrap wrap--narrow">
    <div class="prose prose--lg reveal">
      <p>Již od roku 1990 plní náš holding stále stejné poslání: <strong>spolehlivý dodavatel kvalitních služeb pro všechny</strong>.</p>
      <p>Dnešní společnosti holdingu zajišťují regionální, celostátní i mezinárodní dopravní obslužnost 80 autobusy, více než 100 návěsovými soupravami a dále veškeré služby pro motoristy v areálu AUTOCENTRUM. Naleznete zde mimo centra logistiky i STK pro všechny typy vozidel, čerpací stanici na CNG plyn, autoservisy, pneuservisy, prodejny náhradních dílů a pneu pro vozidla osobní i nákladní, servis a cejchování tachografů, myčku a učňovské středisko pro praktickou výuku v oboru automechanik.</p>
      <p>Pro všechny návštěvníky Autocentra nabízí naše restaurace u STK i oblíbenou kuchyni. V roce 2018 jsme otevřeli novou STK také v Břeclavi.</p>
    </div>
  </div>
</section>

<section class="stats stats--inline" data-stats>
  <div class="stats__bg" aria-hidden="true"></div>
  <div class="wrap stats__grid">
    <div class="stat reveal"><b data-count="791">0</b><span class="stat__unit">mil. Kč</span><h3>roční obrat</h3></div>
    <div class="stat reveal" style="--d:1"><b data-count="392">0</b><span class="stat__unit">lidí</span><h3>zaměstnanců</h3></div>
    <div class="stat reveal" style="--d:2"><b data-count="<?= (int)date('Y') - 1990 ?>">0</b><span class="stat__unit">let</span><h3>na trhu</h3></div>
    <div class="stat reveal" style="--d:3"><b data-count="138600" data-sep="1">0</b><span class="stat__unit">m²</span><h3>rozloha areálů</h3></div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <header class="sec-head reveal"><p class="eyebrow">Milníky</p><h2 class="h-sec">Pár zastávek na naší cestě</h2></header>
    <div class="milestones">
      <?php foreach (array_values($pick) as $i => $r): ?>
        <div class="mile reveal" style="--d:<?= $i % 3 ?>"><b><?= e($r[1]) ?></b><p><?= e(trim(html_entity_decode(strip_tags($r[2])))) ?></p></div>
      <?php endforeach; ?>
    </div>
    <p class="reveal" style="text-align:center;margin-top:2rem"><a class="btn btn--red" href="/nakladni-doprava/historie-spolecnosti">Celá historie společnosti <?= icon('arrow') ?></a></p>
  </div>
</section>

<section class="section section--tint">
  <div class="wrap">
    <header class="sec-head reveal"><p class="eyebrow">Naše divize</p><h2 class="h-sec">Kam dál</h2></header>
    <div class="cards">
      <?php foreach (DIVISIONS as $slug => $d): ?>
        <a class="acard reveal" href="/<?= e($slug) ?>">
          <span class="acard__img" style="background-image:url('<?= thumb($d['hero']) ?>')"></span>
          <span class="acard__body"><span class="acard__ico"><?= icon($d['icon']) ?></span><strong><?= e($d['title']) ?></strong><small><?= e($d['lead']) ?></small><span class="acard__more">Zjistit více <?= icon('arrow') ?></span></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php layout_end();
