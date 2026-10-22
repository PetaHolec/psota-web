<?php
// Router for the PHP built-in server:  php -S localhost:8080 router.php
$file = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($file !== '/' && is_file(__DIR__ . $file) && !str_ends_with($file, '.json') && !str_starts_with($file, '/inc/') && !str_starts_with($file, '/data/') && !str_starts_with($file, '/views/')) {
    return false; // serve static file / api script
}
require __DIR__ . '/index.php';
