<?php
$_SERVER['REQUEST_URI'] = $argv[1];
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/index.php';
