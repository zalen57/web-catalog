<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
    header('Location: ' . url('index.php'));
    exit;
}

$pdo = db();
$userColumn = db_has_column('users', 'user') ? 'user' : 'email';
$st = $pdo->prepare('SELECT id, nama, `' . $userColumn . '` AS user, foto, bio, role FROM users WHERE id = ? LIMIT 1');
$st->execute([$id]);
$user = $st->fetch();
if (!$user) {
    http_response_code(404);
    $pageTitle = 'Penulis tidak ditemukan';
    include __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><p>Penulis tidak ditemukan.</p></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$st = $pdo->prepare(
    'SELECT a.*, c.nama_kategori, c.slug AS cat_slug
     FROM articles a
     JOIN categories c ON c.id = a.kategori_id
     WHERE a.author_id = ?
     ORDER BY a.tanggal_publish DESC'
);
$st->execute([$id]);
$articles = $st->fetchAll();

$pageTitle = 'Profil: ' . $user['nama'];
$metaDescription = excerpt_plain($user['bio'] ?? '', 200) ?: 'Profil penulis ' . $user['nama'] . ' di ' . SITE_NAME . '.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="profile-hero" data-aos="fade-up">
            <img class="avatar" src="<?= e($user['foto'] ?? '') ?>" alt="" width="120" height="120" loading="lazy">
            <div>
                <h1 style="margin:0 0 0.5rem"><?= e($user['nama']) ?></h1>
                <p class="muted" style="margin:0 0 0.75rem"><?= e($user['role']) ?></p>
                <p style="margin:0;max-width:560px"><?= nl2br(e($user['bio'] ?? '')) ?></p>
            </div>
        </div>

        <h2 data-aos="fade-up">Artikel oleh <?= e($user['nama']) ?></h2>
        <div class="card-grid" style="margin-top:1.5rem">
            <?php foreach ($articles as $i => $a): ?>
                <article class="card" data-aos="fade-up" data-aos-delay="<?= (int)($i * 40) ?>">
                    <div class="card-thumb">
                        <img src="<?= e($a['thumbnail'] ?? '') ?>" alt="" loading="lazy" width="400" height="250">
                    </div>
                    <div class="card-body">
                        <div class="card-meta"><?= e(category_label($a)) ?></div>
                        <h3 class="card-title"><a href="<?= e(article_url($a['slug'])) ?>"><?= e($a['judul']) ?></a></h3>
                        <p class="card-excerpt"><?= e($a['excerpt'] ?? excerpt_plain($a['konten'], 100)) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (empty($articles)): ?>
            <p class="muted">Belum ada artikel.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
