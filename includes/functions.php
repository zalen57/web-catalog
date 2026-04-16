<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function excerpt_plain(string $html, int $max = 160): string {
    $t = strip_tags($html);
    $t = preg_replace('/\s+/', ' ', $t);
    $t = trim($t);
    if (mb_strlen($t) <= $max) {
        return $t;
    }
    return mb_substr($t, 0, $max - 1) . '…';
}

function article_url(string $slug): string {
    return url('article.php?slug=' . rawurlencode($slug));
}

function category_url(string $slug): string {
    return url('category.php?slug=' . rawurlencode($slug));
}

function author_url(int $id): string {
    return url('author.php?id=' . $id);
}

/** Label kategori tanpa emoji (hindari karakter rusak di beberapa browser/DB). */
function category_label(array $row): string {
    return trim((string)($row['nama_kategori'] ?? ''));
}

/** Huruf pertama untuk ikon kategori */
function category_initial(array $row): string {
    $n = category_label($row);
    if ($n === '') {
        return '?';
    }
    return mb_strtoupper(mb_substr($n, 0, 1));
}

function like_count(PDO $pdo, int $articleId): int {
    $st = $pdo->prepare('SELECT COUNT(*) FROM article_likes WHERE article_id = ?');
    $st->execute([$articleId]);
    return (int) $st->fetchColumn();
}

function user_liked(PDO $pdo, int $articleId, string $guestKey): bool {
    $st = $pdo->prepare('SELECT 1 FROM article_likes WHERE article_id = ? AND guest_key = ? LIMIT 1');
    $st->execute([$articleId, $guestKey]);
    return (bool) $st->fetchColumn();
}

function user_bookmarked(PDO $pdo, int $articleId, string $guestKey): bool {
    $st = $pdo->prepare('SELECT 1 FROM article_bookmarks WHERE article_id = ? AND guest_key = ? LIMIT 1');
    $st->execute([$articleId, $guestKey]);
    return (bool) $st->fetchColumn();
}
