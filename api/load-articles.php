<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$cat = isset($_GET['cat']) ? (string)$_GET['cat'] : '';
$sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'new';
$dateFrom = isset($_GET['from']) ? (string)$_GET['from'] : '';
$dateTo = isset($_GET['to']) ? (string)$_GET['to'] : '';
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

$where = ['1=1'];
$params = [];

if ($cat !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $cat;
}
if ($q !== '') {
    $where[] = '(a.judul LIKE ? OR a.konten LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($dateFrom !== '') {
    $where[] = 'DATE(a.tanggal_publish) >= ?';
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $where[] = 'DATE(a.tanggal_publish) <= ?';
    $params[] = $dateTo;
}

$order = 'a.tanggal_publish DESC';
if ($sort === 'popular') {
    $order = 'a.views DESC, a.tanggal_publish DESC';
} elseif ($sort === 'old') {
    $order = 'a.tanggal_publish ASC';
}

$pdo = db();
$sqlCount = 'SELECT COUNT(*) FROM articles a JOIN categories c ON c.id = a.kategori_id WHERE ' . implode(' AND ', $where);
$st = $pdo->prepare($sqlCount);
$st->execute($params);
$total = (int) $st->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$sql = 'SELECT a.*, u.nama AS penulis, c.nama_kategori, c.slug AS cat_slug
        FROM articles a
        JOIN users u ON u.id = a.author_id
        JOIN categories c ON c.id = a.kategori_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY ' . $order . '
        LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
$st = $pdo->prepare($sql);
$st->execute($params);
$articles = $st->fetchAll();

function excerpt_plain_local(string $html, int $max = 120): string {
    $t = strip_tags($html);
    $t = preg_replace('/\s+/', ' ', trim($t));
    if (mb_strlen($t) <= $max) {
        return $t;
    }
    return mb_substr($t, 0, $max - 1) . '…';
}

ob_start();
foreach ($articles as $a) {
    $ex = $a['excerpt'] ?? excerpt_plain_local($a['konten'], 120);
    $u = article_url($a['slug']);
    ?>
    <article class="card" data-aos="fade-up">
        <div class="card-thumb">
            <img src="<?= htmlspecialchars($a['thumbnail'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" width="400" height="250">
        </div>
        <div class="card-body">
            <div class="card-meta"><?= htmlspecialchars($a['nama_kategori'] ?? '', ENT_QUOTES, 'UTF-8') ?> · <?= (int)$a['views'] ?> views</div>
            <h2 class="card-title"><a href="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($a['judul'], ENT_QUOTES, 'UTF-8') ?></a></h2>
            <p class="card-excerpt"><?= htmlspecialchars($ex, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </article>
    <?php
}
$html = ob_get_clean();

echo json_encode([
    'ok' => true,
    'html' => $html,
    'page' => $page,
    'totalPages' => $totalPages,
    'hasMore' => $page < $totalPages,
]);
