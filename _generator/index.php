<?php
declare(strict_types=1);

require __DIR__ . '/inc/lib.php';
require __DIR__ . '/inc/layout.php';

$uri  = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$path = '/' . trim(preg_replace('#/+#', '/', $uri), '/');
if ($path !== '/' && str_ends_with($uri, '/') && $uri !== '/') {
    header('Location: ' . $path, true, 301);
    exit;
}

$segments = $path === '/' ? [] : explode('/', trim($path, '/'));
$first    = $segments[0] ?? '';

switch (true) {
    case $path === '/':
        require __DIR__ . '/views/home.php';
        break;
    case $path === '/o-nas':
        require __DIR__ . '/views/about.php';
        break;
    case $path === '/kontakt':
        require __DIR__ . '/views/contact.php';
        break;
    case $path === '/aktuality':
        header('Location: /aktuality-stk', true, 301);
        break;
    case $path === '/aktuality-stk':
        require __DIR__ . '/views/news_list.php';
        break;
    case $first === 'aktuality-stk' && count($segments) === 2:
        require __DIR__ . '/views/news_item.php';
        break;
    case $path === '/sitemap.xml':
        require __DIR__ . '/views/sitemap.php';
        break;
    case isset(DIVISIONS[$first]):
        require __DIR__ . '/views/page.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/views/404.php';
}
