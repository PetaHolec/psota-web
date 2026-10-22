<?php
layout_start(['title' => 'Stránka nenalezena', 'path' => $path]);
?>
<section class="notfound">
  <div class="wrap">
    <div class="notfound__road" aria-hidden="true"><span><?= icon('truck') ?></span></div>
    <p class="eyebrow">Chyba 404</p>
    <h1>Tady silnice končí.</h1>
    <p>Stránka, kterou hledáte, neexistuje nebo se přestěhovala. Vraťte se na hlavní cestu.</p>
    <p><a class="btn btn--red" href="/">Na úvodní stranu <?= icon('arrow') ?></a> <a class="btn btn--ghost" href="/kontakt">Kontaktovat nás</a></p>
  </div>
</section>
<?php layout_end();
