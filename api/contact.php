<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$user = trim((string)($_POST['user'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
if ($name === '' || $message === '' || $user === '') {
    echo json_encode(['ok' => false, 'message' => 'Mohon lengkapi form dengan benar.']);
    exit;
}

$line = date('c') . "\t" . $user . "\t" . $name . "\t" . str_replace(["\r", "\n"], ' ', $message) . "\n";
$dir = dirname(__DIR__) . '/storage';
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}
@file_put_contents($dir . '/contact_messages.log', $line, FILE_APPEND | LOCK_EX);

echo json_encode(['ok' => true, 'message' => 'Pesan terkirim. Kami akan membaca inbox segera.']);
