<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash if present
$uri = rtrim($uri, '/');

if ($uri === '' || $uri === '/index') {
    include __DIR__ . '/index.php';
    exit;
}

$file = __DIR__ . $uri . '.php';
if (file_exists($file)) {
    include $file;
    exit;
}

// Fallback for static assets or 404
$static_file = __DIR__ . $uri;
if (file_exists($static_file) && !is_dir($static_file)) {
    return false;
}

http_response_code(404);
echo "404 Page Not Found";
