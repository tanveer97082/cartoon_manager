<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Static files (CSS, JS, images) ko waise hi serve karein agar exist karte hain
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Root URL par index.php load karein
if ($uri === '/' || $uri === '/index') {
    include __DIR__ . '/index.php';
    return true;
}

// Bina .php ke clean URLs ko map karein (jaise /episodes -> episodes.php)
$file = __DIR__ . $uri . '.php';
if (file_exists($file)) {
    include $file;
    return true;
}

// Agar file direct match ho jaye
if (file_exists(__DIR__ . $uri)) {
    return false;
}

// 404 Not Found
http_response_code(404);
echo "404 Page Not Found";
