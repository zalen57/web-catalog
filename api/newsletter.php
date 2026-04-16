<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$user = trim((string)($_POST['user'] ?? ''));
if ($user === '') {
    echo json_encode(['ok' => false, 'message' => 'User tidak valid']);
    exit;
}

$pdo = db();
$newsletterColumn = db_has_column('newsletter_subscribers', 'user') ? 'user' : 'email';
try {
    $pdo->prepare('INSERT INTO newsletter_subscribers (`' . $newsletterColumn . '`) VALUES (?)')->execute([$user]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000' || strpos($e->getMessage(), 'Duplicate') !== false) {
        echo json_encode(['ok' => true, 'message' => 'User Anda sudah terdaftar. Terima kasih!']);
        exit;
    }
    echo json_encode(['ok' => false, 'message' => 'Gagal menyimpan']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Terima kasih! Anda berhasil berlangganan.']);
