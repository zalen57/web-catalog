<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pdo = db();

$featured = $pdo->query(
    'SELECT a.*, u.nama AS penulis, c.nama_kategori, c.slug AS cat_slug
     FROM articles a
     JOIN users u ON u.id = a.author_id
     JOIN categories c ON c.id = a.kategori_id
     WHERE a.featured = 1
     ORDER BY a.tanggal_publish DESC
     LIMIT 6'
)->fetchAll();

$categories = $pdo->query('SELECT * FROM categories ORDER BY nama_kategori')->fetchAll();

$latest = $pdo->query(
    'SELECT a.*, u.nama AS penulis, c.nama_kategori, c.slug AS cat_slug
     FROM articles a
     JOIN users u ON u.id = a.author_id
     JOIN categories c ON c.id = a.kategori_id
     ORDER BY a.tanggal_publish DESC
     LIMIT 6'
)->fetchAll();

$trending = $pdo->query(
    'SELECT id, judul, slug, views, thumbnail FROM articles
     ORDER BY views DESC
     LIMIT 5'
)->fetchAll();

$pageTitle = 'Beranda';
$metaDescription = 'Blog teknologi: AI, programming, gadget, cyber security, startup. Artikel pilihan dan terbaru.';
include __DIR__ . '/includes/header.php';
?>

<section class="hero" data-aos="fade-up">
    <div class="container hero-grid">
        <div>
            <h1>Wawasan Teknologi untuk Hari Ini</h1>
            <p class="hero-lead">Eksplorasi AI, pengembangan software, gadget, keamanan siber, dan ekosistem startup — dalam gaya blog profesional yang nyaman dibaca.</p>
            <a class="btn" href="<?= e(url('articles.php')) ?>">Jelajahi artikel</a>
        </div>
        <div class="hero-visual" data-aos="zoom-in" data-aos-delay="100">
            <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=900&h=560&fit=crop" alt="Ilustrasi teknologi" loading="lazy" width="900" height="560">
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2>Artikel unggulan</h2>
            <a class="btn btn-outline btn-sm" href="<?= e(url('articles.php')) ?>">Lihat semua</a>
        </div>
        <div class="card-grid">
            <?php foreach ($featured as $i => $a): ?>
                <article class="card" data-aos="fade-up" data-aos-delay="<?= (int)($i * 60) ?>">
                    <div class="card-thumb">
                        <img src="<?= e($a['thumbnail'] ?? '') ?>" alt="" loading="lazy" width="400" height="250">
                    </div>
                    <div class="card-body">
                        <div class="card-meta"><?= e(category_label($a)) ?> · <?= e(date('d M Y', strtotime($a['tanggal_publish']))) ?></div>
                        <h3 class="card-title"><a href="<?= e(article_url($a['slug'])) ?>"><?= e($a['judul']) ?></a></h3>
                        <p class="card-excerpt"><?= e($a['excerpt'] ?? excerpt_plain($a['konten'], 120)) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" style="padding-top:0">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2>Kategori populer</h2>
        </div>
        <div class="cat-pills" data-aos="fade-up">
            <?php foreach ($categories as $c): ?>
                <a class="cat-pill" href="<?= e(url('category.php?slug=' . rawurlencode($c['slug']))) ?>"><?= e(category_label($c)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2>Trending</h2>
            <span class="muted">Berdasarkan jumlah tayangan</span>
        </div>
        <ol style="margin:0;padding-left:1.25rem;max-width:640px" data-aos="fade-up">
            <?php foreach ($trending as $t): ?>
                <li style="margin-bottom:0.5rem">
                    <a href="<?= e(article_url($t['slug'])) ?>"><?= e($t['judul']) ?></a>
                    <span class="muted"> — <?= (int)$t['views'] ?> views</span>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2>Artikel terbaru</h2>
        </div>
        <div class="card-grid">
            <?php foreach ($latest as $i => $a): ?>
                <article class="card" data-aos="fade-up" data-aos-delay="<?= (int)($i * 50) ?>">
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
    </div>
</section>

<section class="section">
    <div class="container" data-aos="zoom-in">
        <div class="newsletter">
            <h2>Newsletter</h2>
            <p>Dapatkan ringkasan artikel terbaru langsung ke akun user Anda. Tanpa spam.</p>
            <form class="newsletter-form" id="newsletter-form" action="<?= e(url('api/newsletter.php')) ?>" method="post">
                <input type="text" name="user" required placeholder="username_anda" autocomplete="username">
                <button type="submit" class="btn">Berlangganan</button>
            </form>
            <p id="newsletter-msg" class="muted" style="margin-top:0.75rem;font-size:0.9rem"></p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
