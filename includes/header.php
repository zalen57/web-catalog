<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
if (!isset($metaDescription)) {
    $metaDescription = 'Blog teknologi modern: AI, programming, gadget, keamanan siber, dan startup.';
}
$cu = current_user() ?? null;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — <?= e(SITE_NAME) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
    <a class="skip-link" href="#main-content">Langsung ke konten</a>
    <header class="site-header">
        <div class="container header-inner">
            <a class="logo" href="<?= e(url('index.php')) ?>" data-aos="fade-right">
                <span class="logo-mark">TB</span>
                <span class="logo-text"><?= e(SITE_NAME) ?></span>
            </a>
            <button type="button" class="nav-toggle" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <nav class="main-nav" aria-label="Utama">
                <a href="<?= e(url('index.php')) ?>">Beranda</a>
                <a href="<?= e(url('articles.php')) ?>">Artikel</a>
                <a href="<?= e(url('category.php')) ?>">Kategori</a>
                <a href="<?= e(url('about.php')) ?>">Tentang &amp; Kontak</a>
                <?php if ($cu): ?>
                    <?php if (($cu['role'] ?? '') === 'admin'): ?>
                        <a href="<?= e(url('admin.php')) ?>">Admin</a>
                    <?php endif; ?>
                    <a href="<?= e(url('author.php?id=' . (int)$cu['id'])) ?>">Profil</a>
                    <a href="<?= e(url('logout.php')) ?>">Keluar</a>
                <?php else: ?>
                    <a href="<?= e(url('login.php')) ?>">Masuk</a>
                <?php endif; ?>
            </nav>
            <div class="header-actions">
                <button type="button" class="theme-toggle" aria-label="Toggle dark mode" title="Mode gelap/terang">
                    <svg class="theme-icon theme-icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12zm0-16a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1zm0 18a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1zM4.22 4.22a1 1 0 0 1 1.42 0l.7.7a1 1 0 1 1-1.42 1.42l-.7-.7a1 1 0 0 1 0-1.42zm12.72 12.72a1 1 0 0 1 1.42 0l.7.7a1 1 0 1 1-1.42 1.42l-.7-.7a1 1 0 0 1 0-1.42zM2 12a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1zm18 0a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2h-1a1 1 0 0 1-1-1zM4.22 19.78a1 1 0 0 1 0-1.42l.7-.7a1 1 0 1 1 1.42 1.42l-.7.7a1 1 0 0 1-1.42 0zM17.66 6.34a1 1 0 0 1 0-1.42l.7-.7a1 1 0 1 1 1.42 1.42l-.7.7a1 1 0 0 1-1.42 0z"/></svg>
                    <svg class="theme-icon theme-icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
            </div>
        </div>
    </header>
    <main id="main-content">
