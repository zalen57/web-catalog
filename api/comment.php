<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$articleId = (int)($_POST['article_id'] ?? 0);
$isi = trim((string)($_POST['isi_komentar'] ?? ''));
if ($articleId < 1 || $isi === '') {
    echo json_encode(['ok' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$pdo = db();
$st = $pdo->prepare('SELECT id FROM articles WHERE id = ?');
$st->execute([$articleId]);
if (!$st->fetch()) {
    echo json_encode(['ok' => false, 'error' => 'Artikel tidak ada']);
    exit;
}

$user = current_user();
$guestNama = trim((string)($_POST['guest_nama'] ?? ''));
$guestUser = trim((string)($_POST['guest_user'] ?? ''));

if ($user) {
    $pdo->prepare(
        'INSERT INTO comments (user_id, article_id, isi_komentar) VALUES (?, ?, ?)'
    )->execute([(int)$user['id'], $articleId, $isi]);
    $name = $user['nama'];
} else {
    if ($guestNama === '') {
        echo json_encode(['ok' => false, 'error' => 'Nama wajib untuk tamu']);
        exit;
    }
    $guestIdentityColumn = db_has_column('comments', 'guest_user') ? 'guest_user' : 'guest_email';
    $pdo->prepare(
        'INSERT INTO comments (user_id, guest_nama, `' . $guestIdentityColumn . '`, article_id, isi_komentar) VALUES (NULL, ?, ?, ?, ?)'
    )->execute([$guestNama, $guestUser ?: null, $articleId, $isi]);
    $name = $guestNama;
}

echo json_encode([
    'ok' => true,
    'name' => $name,
    'time' => date('d M Y H:i'),
    'text' => $isi,
]);
