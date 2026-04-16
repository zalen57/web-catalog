<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];
$articleId = (int)($data['article_id'] ?? 0);
if ($articleId < 1) {
    echo json_encode(['ok' => false]);
    exit;
}

$pdo = db();
$gk = guest_key();

$st = $pdo->prepare('SELECT 1 FROM article_bookmarks WHERE article_id = ? AND guest_key = ?');
$st->execute([$articleId, $gk]);
$exists = (bool) $st->fetchColumn();

if ($exists) {
    $pdo->prepare('DELETE FROM article_bookmarks WHERE article_id = ? AND guest_key = ?')->execute([$articleId, $gk]);
    $active = false;
} else {
    $pdo->prepare('INSERT INTO article_bookmarks (article_id, guest_key) VALUES (?, ?)')->execute([$articleId, $gk]);
    $active = true;
}

echo json_encode(['ok' => true, 'active' => $active]);
