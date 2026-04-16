<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$pdo = db();
$totalArticles = (int)$pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalComments = (int)$pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();
$totalSubscribers = (int)$pdo->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();
$userColumn = db_has_column('users', 'user') ? 'user' : 'email';
$guestIdentityColumn = db_has_column('comments', 'guest_user') ? 'guest_user' : 'guest_email';

$recentArticles = $pdo->query(
    'SELECT a.id, a.judul, a.slug, a.tanggal_publish, u.nama AS author_name
     FROM articles a
     JOIN users u ON u.id = a.author_id
     ORDER BY a.tanggal_publish DESC
     LIMIT 5'
)->fetchAll();

$recentUsers = $pdo->query(
    'SELECT id, nama, `' . $userColumn . '` AS login_user, role, created_at
     FROM users
     ORDER BY id DESC
     LIMIT 5'
)->fetchAll();

$popularArticles = $pdo->query(
    'SELECT id, judul, slug, views
     FROM articles
     ORDER BY views DESC, tanggal_publish DESC
     LIMIT 5'
)->fetchAll();

$recentComments = $pdo->query(
    'SELECT c.isi_komentar, c.tanggal, c.guest_nama, c.`' . $guestIdentityColumn . '` AS guest_identity,
            a.slug AS article_slug, a.judul AS article_title, u.nama AS user_name
     FROM comments c
     JOIN articles a ON a.id = c.article_id
     LEFT JOIN users u ON u.id = c.user_id
     ORDER BY c.tanggal DESC
     LIMIT 5'
)->fetchAll();

$avgCommentsPerArticle = $totalArticles > 0 ? $totalComments / $totalArticles : 0.0;
$subscriberUserRatio = $totalUsers > 0 ? ($totalSubscribers / $totalUsers) * 100 : 0.0;
$engagementScore = min(100.0, ($avgCommentsPerArticle * 20) + ($subscriberUserRatio * 0.6));
$engagementScoreInt = (int)round($engagementScore);
$todayLabel = date('d M Y');
$currentAdminName = (string)(current_user()['nama'] ?? 'Admin');

$pageTitle = 'Admin Panel';
$metaDescription = 'Halaman admin untuk mengontrol konten website.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container admin-wrap">
        <header class="admin-topbar card">
            <div>
                <p class="admin-kicker">ADMIN DASHBOARD</p>
                <h1 class="mt-0">TechBlog Control Center</h1>
                <p class="muted">Halo <?= e($currentAdminName) ?>, kelola konten, user, dan performa website secara terpusat.</p>
            </div>
            <div class="admin-topbar-actions">
                <span class="admin-date-pill"><?= e($todayLabel) ?></span>
                <a class="btn btn-sm" href="<?= e(url('articles.php')) ?>">Kelola Artikel</a>
                <a class="btn btn-outline btn-sm" href="<?= e(url('index.php')) ?>">Lihat Website</a>
            </div>
        </header>

        <div class="admin-kpi-grid">
            <article class="admin-kpi-card card">
                <p class="admin-stat-label">Total Artikel</p>
                <p class="admin-stat-value"><?= $totalArticles ?></p>
            </article>
            <article class="admin-kpi-card card">
                <p class="admin-stat-label">Total User</p>
                <p class="admin-stat-value"><?= $totalUsers ?></p>
            </article>
            <article class="admin-kpi-card card">
                <p class="admin-stat-label">Total Komentar</p>
                <p class="admin-stat-value"><?= $totalComments ?></p>
            </article>
            <article class="admin-kpi-card card">
                <p class="admin-stat-label">Subscriber</p>
                <p class="admin-stat-value"><?= $totalSubscribers ?></p>
            </article>
        </div>

        <div class="admin-layout">
            <div class="admin-primary">
                <article class="card admin-panel">
                    <div class="admin-panel-head">
                        <h3 class="mt-0">Performance Snapshot</h3>
                        <span class="muted">ringkasan cepat</span>
                    </div>
                    <div class="admin-insights">
                        <div class="admin-insight">
                            <p class="admin-stat-label">Skor Engagement</p>
                            <div class="admin-meter">
                                <span style="width: <?= $engagementScoreInt ?>%"></span>
                            </div>
                            <p class="muted"><?= $engagementScoreInt ?> / 100</p>
                        </div>
                        <div class="admin-insight">
                            <p class="admin-stat-label">Konversi User ke Subscriber</p>
                            <div class="admin-meter">
                                <span style="width: <?= (int)round(min(100, $subscriberUserRatio)) ?>%"></span>
                            </div>
                            <p class="muted"><?= e(number_format($subscriberUserRatio, 1)) ?>%</p>
                        </div>
                    </div>
                </article>

                <article class="card admin-panel">
                    <div class="admin-panel-head">
                        <h3 class="mt-0">Artikel Terbaru</h3>
                        <span class="muted">5 data terakhir</span>
                    </div>
                    <div class="admin-list">
                        <?php foreach ($recentArticles as $a): ?>
                            <a class="admin-list-item" href="<?= e(url('article.php?slug=' . rawurlencode((string)$a['slug']))) ?>">
                                <div>
                                    <strong><?= e($a['judul']) ?></strong>
                                    <p class="muted">oleh <?= e($a['author_name']) ?></p>
                                </div>
                                <small class="muted admin-list-meta"><?= e(date('d M Y', strtotime((string)$a['tanggal_publish']))) ?></small>
                            </a>
                        <?php endforeach; ?>
                        <?php if (empty($recentArticles)): ?>
                            <p class="muted">Belum ada artikel.</p>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="card admin-panel">
                    <div class="admin-panel-head">
                        <h3 class="mt-0">Komentar Terbaru</h3>
                        <span class="muted">5 data terakhir</span>
                    </div>
                    <div class="admin-list">
                        <?php foreach ($recentComments as $c): ?>
                            <a class="admin-list-item" href="<?= e(url('article.php?slug=' . rawurlencode((string)$c['article_slug']))) ?>">
                                <div>
                                    <strong><?= e((string)($c['user_name'] ?: $c['guest_nama'] ?: $c['guest_identity'] ?: 'Guest')) ?></strong>
                                    <p class="muted"><?= e(excerpt_plain((string)$c['isi_komentar'], 80)) ?></p>
                                </div>
                                <small class="muted admin-list-meta"><?= e(date('d M H:i', strtotime((string)$c['tanggal']))) ?></small>
                            </a>
                        <?php endforeach; ?>
                        <?php if (empty($recentComments)): ?>
                            <p class="muted">Belum ada komentar.</p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

            <aside class="admin-sidebar">
                <article class="card admin-panel">
                    <div class="admin-panel-head">
                        <h3 class="mt-0">Quick Actions</h3>
                        <span class="muted">akses cepat</span>
                    </div>
                    <div class="admin-action-stack">
                        <a class="admin-action-tile" href="<?= e(url('articles.php')) ?>">
                            <strong>Kelola Artikel</strong>
                            <p class="muted">Monitoring konten dan performa artikel.</p>
                        </a>
                        <a class="admin-action-tile" href="<?= e(url('category.php')) ?>">
                            <strong>Kelola Kategori</strong>
                            <p class="muted">Atur struktur topik konten.</p>
                        </a>
                        <a class="admin-action-tile" href="<?= e(url('author.php?id=' . (int)(current_user()['id'] ?? 0))) ?>">
                            <strong>Profil Admin</strong>
                            <p class="muted">Update data akun dan profil.</p>
                        </a>
                        <a class="admin-action-tile" href="<?= e(url('logout.php')) ?>">
                            <strong>Logout</strong>
                            <p class="muted">Keluar dari panel admin.</p>
                        </a>
                    </div>
                </article>

                <article class="card admin-panel">
                    <div class="admin-panel-head">
                        <h3 class="mt-0">Artikel Populer</h3>
                        <span class="muted">berdasarkan views</span>
                    </div>
                    <div class="admin-list">
                        <?php foreach ($popularArticles as $a): ?>
                            <a class="admin-list-item" href="<?= e(url('article.php?slug=' . rawurlencode((string)$a['slug']))) ?>">
                                <div>
                                    <strong><?= e($a['judul']) ?></strong>
                                    <p class="muted"><?= (int)$a['views'] ?> views</p>
                                </div>
                                <span class="admin-role">Top</span>
                            </a>
                        <?php endforeach; ?>
                        <?php if (empty($popularArticles)): ?>
                            <p class="muted">Belum ada data popularitas.</p>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="card admin-panel">
                    <div class="admin-panel-head">
                        <h3 class="mt-0">User Terbaru</h3>
                        <span class="muted">5 data terakhir</span>
                    </div>
                    <div class="admin-list">
                        <?php foreach ($recentUsers as $u): ?>
                            <div class="admin-list-item">
                                <div>
                                    <strong><?= e($u['nama']) ?></strong>
                                    <p class="muted">@<?= e((string)$u['login_user']) ?></p>
                                </div>
                                <span class="admin-role"><?= e((string)$u['role']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </aside>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
