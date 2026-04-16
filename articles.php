<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY nama_kategori')->fetchAll();

$cat = isset($_GET['cat']) ? (string)$_GET['cat'] : '';
$sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'new';
$dateFrom = isset($_GET['from']) ? (string)$_GET['from'] : '';
$dateTo = isset($_GET['to']) ? (string)$_GET['to'] : '';
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

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

$sqlCount = 'SELECT COUNT(*) FROM articles a JOIN categories c ON c.id = a.kategori_id WHERE ' . implode(' AND ', $where);
$st = $pdo->prepare($sqlCount);
$st->execute($params);
$total = (int) $st->fetchColumn();

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

$totalPages = max(1, (int)ceil($total / $perPage));

$pageTitle = 'Semua artikel';
$metaDescription = 'Daftar artikel teknologi dengan filter kategori, tanggal, dan popularitas.';
include __DIR__ . '/includes/header.php';
?>

<section class="section article-hero">
    <div class="container">
        <h1 data-aos="fade-up">Artikel</h1>
        <p class="muted" data-aos="fade-up" data-aos-delay="50">Filter, urutkan, dan cari konten yang Anda butuhkan.</p>

        <form class="filters-bar" method="get" action="" data-aos="fade-up">
            <div class="search-wrap" style="flex:2;min-width:240px">
                <input type="search" name="q" id="articles-search" placeholder="Cari judul atau isi..." value="<?= e($q) ?>" autocomplete="off">
                <div class="live-results" id="live-search-results" role="listbox" aria-label="Hasil cepat"></div>
            </div>
            <label class="muted" style="display:flex;align-items:center;gap:0.35rem">
                Kategori
                <select name="cat" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c['slug']) ?>" <?= $cat === $c['slug'] ? 'selected' : '' ?>><?= e(category_label($c)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="muted" style="display:flex;align-items:center;gap:0.35rem">
                Urutkan
                <select name="sort" onchange="this.form.submit()">
                    <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popularitas (views)</option>
                    <option value="old" <?= $sort === 'old' ? 'selected' : '' ?>>Terlama</option>
                </select>
            </label>
            <label class="muted" style="display:flex;align-items:center;gap:0.35rem">
                Dari
                <input type="date" name="from" value="<?= e($dateFrom) ?>" onchange="this.form.submit()">
            </label>
            <label class="muted" style="display:flex;align-items:center;gap:0.35rem">
                Sampai
                <input type="date" name="to" value="<?= e($dateTo) ?>" onchange="this.form.submit()">
            </label>
            <button type="submit" class="btn btn-sm">Terapkan</button>
        </form>
        <p class="search-hint">Tips: ketik di kotak pencarian untuk saran real-time (AJAX).</p>
    </div>
</section>

<section class="section" style="padding-top:0">
    <div class="container">
        <div id="articles-grid" class="card-grid">
            <?php foreach ($articles as $i => $a): ?>
                <article class="card" data-aos="fade-up" data-aos-delay="<?= (int)($i * 40) ?>">
                    <div class="card-thumb">
                        <img src="<?= e($a['thumbnail'] ?? '') ?>" alt="" loading="lazy" width="400" height="250">
                    </div>
                    <div class="card-body">
                        <div class="card-meta"><?= e(category_label($a)) ?> · <?= (int)$a['views'] ?> views</div>
                        <h2 class="card-title"><a href="<?= e(article_url($a['slug'])) ?>"><?= e($a['judul']) ?></a></h2>
                        <p class="card-excerpt"><?= e($a['excerpt'] ?? excerpt_plain($a['konten'], 120)) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (empty($articles)): ?>
            <p class="text-center muted">Tidak ada artikel yang cocok dengan filter Anda.</p>
        <?php endif; ?>

        <div class="text-center" style="margin-top:2rem">
            <?php if ($page < $totalPages): ?>
                <button type="button" class="btn btn-outline" id="load-more-articles" data-page="<?= (int)$page ?>" data-total="<?= (int)$totalPages ?>"
                    data-query="<?= e(http_build_query(['cat' => $cat, 'sort' => $sort, 'from' => $dateFrom, 'to' => $dateTo, 'q' => $q])) ?>">
                    Muat lebih banyak
                </button>
            <?php endif; ?>
            <p class="muted" style="margin-top:1rem;font-size:0.9rem">Halaman <?= (int)$page ?> dari <?= (int)$totalPages ?> — Total <?= (int)$total ?> artikel</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
