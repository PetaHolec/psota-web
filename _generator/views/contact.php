<?php
layout_start(['title' => 'Kontakt', 'description' => 'Kontakty na PSOTA holding Znojmo: adresa, telefon, e-mail, kontaktní formulář a kontakty na jednotlivé divize.', 'path' => '/kontakt', 'body' => 'inner']);
page_hero('Kontakt', [['Kontakt', null]], 'fotogalerie/autocentrum/autocentrum_001.jpg', 'Napište nám, zavolejte nebo se zastavte v areálu AUTOCENTRUM.', 'mail');

$units = [
    ['Nákladní doprava', 'truck', 'Chvalovice 81, 669 02 Znojmo', '+420 515 210 543', 'dispo@psota-znojmo.cz', '/nakladni-doprava/kontakty'],
    ['Autocentrum', 'building', 'Dobšická 3697/6, 669 02 Znojmo', '+420 515 210 545', 'karel.psota@psota-znojmo.cz', '/autocentrum/kontakty'],
    ['STK Znojmo', 'check', 'Dobšická 3697/6, 669 02 Znojmo', '+420 601 601 609', 'stk.znojmo@psota-znojmo.cz', '/stk/kontakty'],
    ['Učňovské středisko', 'school', 'Dobšická 3697/6, 669 02 Znojmo', '+420 515 210 553', 'ucnovske.stredisko@psota-znojmo.cz', '/ucnovske-stredisko/kontakty'],
    ['Restaurace u STK', 'food', 'Družstevní 2825/3, 669 02 Znojmo', '+420 601 232 388', 'restauraceustk@psota-znojmo.cz', '/restaurace/kontakty'],
];
?>
<section class="section section--content">
  <div class="wrap contact__grid contact__grid--light">
    <div>
      <h2 class="h-sec h-sec--sm reveal">PSOTA holding</h2>
      <ul class="contact__list contact__list--dark reveal">
        <li><?= icon('pin') ?><span><?= e(HOLDING['street']) ?>, <?= e(HOLDING['city']) ?></span></li>
        <li><?= icon('phone') ?><a href="<?= tel_href(HOLDING['phone']) ?>"><?= e(HOLDING['phone']) ?></a></li>
        <li><?= icon('mail') ?><a href="mailto:<?= e(HOLDING['email']) ?>"><?= e(HOLDING['email']) ?></a></li>
      </ul>
      <div class="map reveal">
        <iframe title="Mapa – areál AUTOCENTRUM PSOTA, Dobšická 3697/6, Znojmo" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen src="<?= e(map_embed_url(locations()['areal'])) ?>"></iframe>
      </div>
      <p class="small muted">GPS: <?= HOLDING['lat'] ?>, <?= HOLDING['lng'] ?> · <a href="https://www.google.com/maps?q=<?= HOLDING['lat'] ?>,<?= HOLDING['lng'] ?>" target="_blank" rel="noopener">Otevřít v Mapách Google</a></p>
    </div>
    <div class="card card--form reveal" style="--d:1">
      <h2 class="h-sec h-sec--sm">Napište nám</h2>
      <?php contact_form('cf-page'); ?>
    </div>
  </div>
</section>

<section class="section section--tint">
  <div class="wrap">
    <header class="sec-head reveal"><p class="eyebrow">Kontakty podle divizí</p><h2 class="h-sec">Se kterým oddělením potřebujete mluvit?</h2></header>
    <div class="cards cards--units">
      <?php foreach ($units as $i => [$t, $ic, $addr, $tel, $mail, $href]): ?>
        <article class="ucard reveal" style="--d:<?= $i % 3 ?>">
          <span class="acard__ico"><?= icon($ic) ?></span>
          <h3><?= e($t) ?></h3>
          <p><?= icon('pin') ?><?= e($addr) ?></p>
          <p><?= icon('phone') ?><a href="<?= tel_href($tel) ?>"><?= e($tel) ?></a></p>
          <p><?= icon('mail') ?><a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></p>
          <a class="acard__more" href="<?= e($href) ?>">Všechny kontakty <?= icon('arrow') ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php layout_end();
