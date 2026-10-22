<?php
header('Content-Type: application/xml; charset=utf-8');
$urls = ['/', '/o-nas', '/kontakt', '/aktuality-stk'];
foreach (array_keys(data('pages')) as $k) {
    if ($k === 'index' || str_starts_with($k, 'aktuality-stk')) continue;
    $urls[] = '/' . str_replace('__', '/', $k);
}
foreach (news_all() as $n) $urls[] = '/aktuality-stk/' . $n['slug'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach (array_unique($urls) as $u) echo '  <url><loc>' . e(SITE_URL . $u) . '</loc></url>' . "\n";
echo '</urlset>';
