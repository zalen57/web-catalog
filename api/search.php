<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
if (mb_strlen($q) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$pdo = db();
$like = '%' . $q . '%';
$st = $pdo->prepare(
    'SELECT a.judul, a.slug
     FROM articles a
     JOIN categories c ON c.id = a.kategori_id
     WHERE a.judul LIKE ? OR a.konten LIKE ?
     ORDER BY a.tanggal_publish DESC
     LIMIT 12'
);
$st->execute([$like, $like]);
$rows = $st->fetchAll();
$out = [];
foreach ($rows as $r) {
    $out[] = [
        'title' => $r['judul'],
        'url' => url('article.php?slug=' . rawurlencode($r['slug'])),
    ];
}
echo json_encode(['results' => $out]);
