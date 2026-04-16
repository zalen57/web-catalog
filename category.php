<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$slug = isset($_GET['slug']) ? (string)$_GET['slug'] : '';

if ($slug === '') {
    $pageTitle = 'Kategori';
    $metaDescription = 'Jelajahi artikel berdasarkan kategori: AI, programming, gadget, cyber security, startup.';
    include __DIR__ . '/includes/header.php';
    $cats = $pdo->query('SELECT c.*, COUNT(a.id) AS jumlah FROM categories c LEFT JOIN articles a ON a.kategori_id = c.id GROUP BY c.id ORDER BY c.nama_kategori')->fetchAll();
    ?>
    <section class="section article-hero">
        <div class="container">
            <h1 data-aos="fade-up">Kategori</h1>
            <p class="muted" data-aos="fade-up">Pilih topik yang ingin Anda dalami.</p>
            <div class="card-grid" style="margin-top:2rem">
                <?php foreach ($cats as $i => $c): ?>
                    <a class="card cat-pill" href="<?= e(url('category.php?slug=' . rawurlencode($c['slug']))) ?>" style="text-align:center;padding:2rem;display:block;text-decoration:none" data-aos="fade-up" data-aos-delay="<?= (int)($i * 50) ?>">
                        <span class="cat-icon-lg" aria-hidden="true"><?= e(category_initial($c)) ?></span>
                        <h2 class="card-title" style="margin-top:0.5rem"><?= e($c['nama_kategori']) ?></h2>
                        <p class="muted"><?= (int)$c['jumlah'] ?> artikel</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$st = $pdo->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
$st->execute([$slug]);
$cat = $st->fetch();
if (!$cat) {
    header('Location: ' . url('category.php'));
    exit;
}

$st = $pdo->prepare(
    'SELECT a.*, u.nama AS penulis, c.nama_kategori, c.slug AS cat_slug
     FROM articles a
     JOIN users u ON u.id = a.author_id
     JOIN categories c ON c.id = a.kategori_id
     WHERE c.slug = ?
     ORDER BY a.tanggal_publish DESC'
);
$st->execute([$slug]);
$articles = $st->fetchAll();

$pageTitle = 'Kategori: ' . $cat['nama_kategori'];
$metaDescription = 'Artikel dalam kategori ' . $cat['nama_kategori'] . ' di ' . SITE_NAME . '.';
include __DIR__ . '/includes/header.php';
?>

<section class="section article-hero">
    <div class="container">
        <h1 data-aos="fade-up"><?= e(category_label($cat)) ?></h1>
        <p class="muted" data-aos="fade-up">Semua artikel dalam kategori ini.</p>
    </div>
</section>

<section class="section" style="padding-top:0">
    <div class="container">
        <div class="card-grid">
            <?php foreach ($articles as $i => $a): ?>
                <article class="card" data-aos="fade-up" data-aos-delay="<?= (int)($i * 40) ?>">
                    <div class="card-thumb">
                        <img src="<?= e($a['thumbnail'] ?? '') ?>" alt="" loading="lazy" width="400" height="250">
                    </div>
                    <div class="card-body">
                        <div class="card-meta"><?= e(date('d M Y', strtotime($a['tanggal_publish']))) ?> · <?= (int)$a['views'] ?> views</div>
                        <h2 class="card-title"><a href="<?= e(article_url($a['slug'])) ?>"><?= e($a['judul']) ?></a></h2>
                        <p class="card-excerpt"><?= e($a['excerpt'] ?? excerpt_plain($a['konten'], 120)) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (empty($articles)): ?>
            <p class="text-center muted">Belum ada artikel di kategori ini.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
