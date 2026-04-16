<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug = isset($_GET['slug']) ? (string)$_GET['slug'] : '';
if ($slug === '') {
    header('Location: ' . url('articles.php'));
    exit;
}

$pdo = db();
$st = $pdo->prepare(
    'SELECT a.*, u.nama AS penulis, u.id AS author_id, u.foto AS author_foto, u.bio AS author_bio,
            c.nama_kategori, c.slug AS cat_slug
     FROM articles a
     JOIN users u ON u.id = a.author_id
     JOIN categories c ON c.id = a.kategori_id
     WHERE a.slug = ?
     LIMIT 1'
);
$st->execute([$slug]);
$article = $st->fetch();
if (!$article) {
    http_response_code(404);
    $pageTitle = 'Tidak ditemukan';
    include __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><p>Artikel tidak ditemukan.</p><a class="btn" href="' . e(url('articles.php')) . '">Kembali</a></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$pdo->prepare('UPDATE articles SET views = views + 1 WHERE id = ?')->execute([(int)$article['id']]);
$article['views'] = (int)$article['views'] + 1;

$st = $pdo->prepare(
    'SELECT t.nama_tag, t.slug FROM tags t
     INNER JOIN article_tags at ON at.tag_id = t.id
     WHERE at.article_id = ?'
);
$st->execute([(int)$article['id']]);
$tags = $st->fetchAll();

$gk = guest_key();
$likes = like_count($pdo, (int)$article['id']);
$liked = user_liked($pdo, (int)$article['id'], $gk);
$bookmarked = user_bookmarked($pdo, (int)$article['id'], $gk);

$st = $pdo->prepare(
    'SELECT c.*, u.nama AS u_nama, u.foto AS u_foto
     FROM comments c
     LEFT JOIN users u ON u.id = c.user_id
     WHERE c.article_id = ?
     ORDER BY c.tanggal DESC'
);
$st->execute([(int)$article['id']]);
$comments = $st->fetchAll();

$st = $pdo->prepare(
    'SELECT a.*, u.nama AS penulis, c.nama_kategori
     FROM articles a
     JOIN users u ON u.id = a.author_id
     JOIN categories c ON c.id = a.kategori_id
     WHERE a.kategori_id = ? AND a.id != ?
     ORDER BY a.tanggal_publish DESC
     LIMIT 3'
);
$st->execute([(int)$article['kategori_id'], (int)$article['id']]);
$related = $st->fetchAll();

$pageTitle = $article['judul'];
$metaDescription = $article['excerpt'] ?? excerpt_plain($article['konten'], 160);
$shareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . $_SERVER['REQUEST_URI'];
include __DIR__ . '/includes/header.php';
?>

<article>
    <section class="article-hero">
        <div class="container">
            <nav class="breadcrumb" aria-label="Breadcrumb" data-aos="fade-up">
                <a href="<?= e(url('index.php')) ?>">Beranda</a>
                · <a href="<?= e(url('category.php?slug=' . rawurlencode($article['cat_slug']))) ?>"><?= e(category_label($article)) ?></a>
                · <span><?= e($article['judul']) ?></span>
            </nav>
            <h1 data-aos="fade-up"><?= e($article['judul']) ?></h1>
            <div class="article-meta-bar" data-aos="fade-up" data-aos-delay="50">
                <span class="author-inline">
                    <img src="<?= e($article['author_foto'] ?? '') ?>" alt="" width="40" height="40" loading="lazy">
                    <span><a href="<?= e(author_url((int)$article['author_id'])) ?>"><?= e($article['penulis']) ?></a></span>
                </span>
                <time datetime="<?= e($article['tanggal_publish']) ?>"><?= e(date('d F Y', strtotime($article['tanggal_publish']))) ?></time>
                <span><?= (int)$article['views'] ?> tayangan</span>
            </div>
            <div class="article-cover" data-aos="zoom-in">
                <img src="<?= e($article['thumbnail'] ?? '') ?>" alt="" loading="lazy" width="1200" height="630">
            </div>
            <div class="prose" data-aos="fade-up">
                <?= $article['konten'] ?>
            </div>

            <div class="tag-list" data-aos="fade-up">
                <?php foreach ($tags as $t): ?>
                    <span class="tag"><?= e($t['nama_tag']) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="article-actions" data-aos="fade-up">
                <button type="button" class="btn btn-sm btn-icon" id="btn-like" data-article="<?= (int)$article['id'] ?>" data-active="<?= $liked ? '1' : '0' ?>">
                    <span id="like-label"><?= $liked ? 'Disukai' : 'Suka' ?></span> (<span id="like-count"><?= (int)$likes ?></span>)
                </button>
                <button type="button" class="btn btn-sm btn-ghost" id="btn-bookmark" data-article="<?= (int)$article['id'] ?>" data-active="<?= $bookmarked ? '1' : '0' ?>">
                    <?= $bookmarked ? 'Tersimpan' : 'Simpan' ?>
                </button>
                <div class="share-row">
                    <span class="muted">Bagikan:</span>
                    <a class="btn btn-sm btn-outline" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text=<?= rawurlencode($article['judul']) ?>&url=<?= rawurlencode($shareUrl) ?>">Twitter</a>
                    <a class="btn btn-sm btn-outline" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>">Facebook</a>
                    <button type="button" class="btn btn-sm btn-ghost" id="copy-link" data-url="<?= e($shareUrl) ?>">Salin link</button>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:0">
        <div class="container">
            <h2 data-aos="fade-up">Komentar</h2>
            <div class="comment-list" id="comment-list" data-aos="fade-up">
                <?php foreach ($comments as $c): ?>
                    <div class="comment-item">
                        <div class="comment-meta">
                            <?php if (!empty($c['u_nama'])): ?>
                                <strong><?= e($c['u_nama']) ?></strong>
                            <?php else: ?>
                                <strong><?= e($c['guest_nama'] ?? 'Tamu') ?></strong>
                            <?php endif; ?>
                            · <?= e(date('d M Y H:i', strtotime($c['tanggal']))) ?>
                        </div>
                        <div><?= nl2br(e($c['isi_komentar'])) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                    <p class="muted" id="no-comments">Belum ada komentar. Jadilah yang pertama.</p>
                <?php endif; ?>
            </div>

            <form id="comment-form" class="card-body" style="max-width:560px;padding:0" data-aos="fade-up">
                <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                <?php if (!current_user()): ?>
                    <div class="form-group">
                        <label for="guest_nama">Nama</label>
                        <input type="text" id="guest_nama" name="guest_nama" required maxlength="120" placeholder="Nama Anda">
                    </div>
                    <div class="form-group">
                        <label for="guest_user">User (opsional)</label>
                        <input type="text" id="guest_user" name="guest_user" placeholder="user_tamu">
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="isi_komentar">Komentar</label>
                    <textarea id="isi_komentar" name="isi_komentar" required placeholder="Tulis komentar Anda..."></textarea>
                </div>
                <button type="submit" class="btn btn-sm">Kirim</button>
                <p id="comment-msg" class="muted" style="margin-top:0.75rem"></p>
            </form>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 data-aos="fade-up">Artikel terkait</h2>
            <div class="card-grid">
                <?php foreach ($related as $i => $r): ?>
                    <article class="card" data-aos="fade-up" data-aos-delay="<?= (int)($i * 60) ?>">
                        <div class="card-thumb">
                            <img src="<?= e($r['thumbnail'] ?? '') ?>" alt="" loading="lazy" width="400" height="250">
                        </div>
                        <div class="card-body">
                            <div class="card-meta"><?= e(category_label($r)) ?></div>
                            <h3 class="card-title"><a href="<?= e(article_url($r['slug'])) ?>"><?= e($r['judul']) ?></a></h3>
                            <p class="card-excerpt"><?= e($r['excerpt'] ?? excerpt_plain($r['konten'], 100)) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</article>

<?php include __DIR__ . '/includes/footer.php'; ?>
