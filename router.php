<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if (is_file($file)) {
    if (str_ends_with($file, '.php')) {
        require $file;
    } else {
        return false;
    }
} else {
    require __DIR__ . '/index.html';
}