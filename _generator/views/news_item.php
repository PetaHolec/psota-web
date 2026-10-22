<?php
$item = null;
foreach (news_all() as $nw) if ($nw['slug'] === $segments[1]) { $item = $nw; break; }
if (!$item) { http_response_code(404); require __DIR__ . '/404.php'; return; }
layout_start(['title' => $item['title'] . ' (' . $item['date'] . ')', 'description' => mb_strimwidth(trim(html_entity_decode(strip_tags(str_replace(['<br />', '<br>'], ' ', $item['body'])))), 0, 158, '…'), 'path' => $path, 'body' => 'inner']);
page_hero($item['title'], [['Aktuality – STK', '/aktuality-stk'], [$item['date'], null]], 'fotogalerie/stk_znojmo/stkznojmo_05.jpg', null, 'bell');
$all = news_all(); $idx = array_search($item, $all, true);
?>
<section class="section section--content">
  <div class="wrap wrap--narrow">
    <article class="card article reveal">
      <p class="ncard__date"><?= icon('bell') ?><time datetime="<?= e($item['iso'] ?? '') ?>"><?= e($item['date']) ?></time></p>
      <div class="prose"><?= prose($item['body']) ?></div>
    </article>
    <nav class="pager">
      <?php if (isset($all[$idx + 1])): ?><a class="btn btn--ghost" href="/aktuality-stk/<?= e($all[$idx + 1]['slug']) ?>">← Starší</a><?php else: ?><span></span><?php endif; ?>
      <a class="btn btn--ghost" href="/aktuality-stk">Všechny aktuality</a>
      <?php if ($idx > 0): ?><a class="btn btn--ghost" href="/aktuality-stk/<?= e($all[$idx - 1]['slug']) ?>">Novější →</a><?php else: ?><span></span><?php endif; ?>
    </nav>
  </div>
</section>
<?php layout_end();
