<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'erins_techblog');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'TechBlog');

/** Path prefix e.g. /ErinS — no trailing slash */
function base_url(): string {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir = dirname(str_replace('\\', '/', $script));
    if ($dir === '/' || $dir === '.') {
        $cached = '';
    } else {
        $cached = rtrim($dir, '/');
    }
    return $cached;
}

function url(string $path = ''): string {
    $path = ltrim($path, '/');
    $b = base_url();
    return ($b ? $b . '/' : '/') . $path;
}

function guest_key(): string {
    if (empty($_SESSION['guest_key'])) {
        $_SESSION['guest_key'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['guest_key'];
}
