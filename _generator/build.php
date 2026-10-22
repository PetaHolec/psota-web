<?php
// Jednorázový generátor statických stránek: php build.php   (pak: python3 i18n.py apply && python3 relink.py)
$root = dirname(__DIR__);
$pages = json_decode(file_get_contents(__DIR__ . '/data/pages.json'), true);
$news  = json_decode(file_get_contents(__DIR__ . '/data/news.json'), true);
$routes = ['/', '/o-nas', '/kontakt', '/aktuality-stk'];
foreach (array_keys($pages) as $k) {
    if ($k === 'index' || str_starts_with($k, 'aktuality-stk')) continue;
    $routes[] = '/' . str_replace('__', '/', $k);
}
$newsRoutes = array_map(fn($n) => '/aktuality-stk/' . $n['slug'], $news);
$newsRecent = array_map(fn($n) => '/aktuality-stk/' . $n['slug'], array_filter($news, fn($n) => ($n['iso'] ?? '') >= '2025-10-01'));

$render = function (string $uri, string $lang): string {
    return (string)shell_exec('SITE_LANG=' . $lang . ' php ' . escapeshellarg(__DIR__ . '/build_one.php') . ' ' . escapeshellarg($uri));
};

foreach (['cs', 'en', 'de', 'pl'] as $lang) {
    $dir = $lang === 'cs' ? '' : $lang . '/';
    if ($lang !== 'cs') shell_exec('rm -rf ' . escapeshellarg($root . '/' . $lang));
    foreach (array_merge($routes, $lang === 'cs' ? $newsRoutes : $newsRecent) as $r) {
        $file = $dir . ($r === '/' ? 'index.html' : trim($r, '/') . '/index.html');
        $abs = $root . '/' . $file;
        @mkdir(dirname($abs), 0775, true);
        file_put_contents($abs, $render($r, $lang));
    }
    echo "$lang: " . count($routes) . " stránek\n";
}
// sitemap (všechny jazyky) + 404 (česky)
$urls = [];
foreach (array_merge($routes, $newsRoutes) as $r) foreach (['cs', 'en', 'de', 'pl'] as $l) { if ($l !== 'cs' && in_array($r, $newsRoutes) && !in_array($r, $newsRecent)) continue; $urls[] = $l === 'cs' ? $r : '/' . $l . ($r === '/' ? '/' : $r); }
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) $xml .= '  <url><loc>https://www.psota-znojmo.cz' . $u . '</loc></url>' . "\n";
file_put_contents($root . '/sitemap.xml', $xml . '</urlset>');
file_put_contents($root . '/404.html', $render('/neexistuje', 'cs'));
