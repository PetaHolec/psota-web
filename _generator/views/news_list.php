<?php
$byYear = [];
foreach (news_all() as $nw) $byYear[substr((string)($nw['iso'] ?? '0000'), 0, 4)][] = $nw;
layout_start(['title' => 'Aktuality STK', 'description' => 'Oznámení pro zákazníky: provozní doba STK o svátcích a další změny.', 'path' => '/aktuality-stk', 'body' => 'inner']);
page_hero('Aktuality', [['Aktuality – STK', null]], 'fotogalerie/stk_breclav/stkbreclav_01.jpg', 'Oznámení pro zákazníky a změny provozní doby na stanicích STK.', 'bell');
?>
<section class="section section--content">
  <div class="wrap wrap--narrow">
    <?php foreach ($byYear as $year => $items): ?>
      <h2 class="h-sec h-sec--sm reveal"><?= e($year) ?></h2>
      <div class="newslist">
        <?php foreach ($items as $i => $nw): ?>
          <a class="nrow reveal" href="/aktuality-stk/<?= e($nw['slug']) ?>">
            <time datetime="<?= e($nw['iso'] ?? '') ?>"><?= e($nw['date']) ?></time>
            <span><b><?= e($nw['title']) ?></b><?php if (LANG_CODE === 'cs'): ?><small><?= e(mb_strimwidth(trim(html_entity_decode(strip_tags(str_replace(['<br />', '<br>'], ' ', $nw['body'])))), 0, 140, '…')) ?></small><?php endif; ?></span>
            <?= icon('arrow') ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php layout_end();
