<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Tentang & Kontak';
$metaDescription = 'Tentang TechBlog, visi kami, dan cara menghubungi tim.';
include __DIR__ . '/includes/header.php';
?>

<section class="section article-hero">
    <div class="container">
        <h1 data-aos="fade-up">Tentang &amp; Kontak</h1>
        <p class="muted" data-aos="fade-up">Kami menyajikan konten teknologi yang jelas, relevan, dan menyenangkan dibaca.</p>
    </div>
</section>

<section class="section" style="padding-top:0">
    <div class="container about-grid">
        <div data-aos="fade-up">
            <h2>Tentang website</h2>
            <p><?= e(SITE_NAME) ?> adalah blog teknologi yang membahas tren AI, pengembangan perangkat lunak, perangkat keras, keamanan siber, dan dunia startup. Konten ditulis untuk pembaca yang ingin memahami dampak teknologi tanpa jargon berlebihan.</p>
            <p class="muted">Proyek ini cocok sebagai portofolio atau tugas akademik: struktur database relasional, UI responsif, dan fitur interaktif seperti pencarian, komentar, serta mode gelap.</p>
        </div>
        <div data-aos="fade-up" data-aos-delay="80">
            <h2>Media sosial</h2>
            <ul class="footer-links">
                <li><a href="https://twitter.com" target="_blank" rel="noopener">Twitter / X</a></li>
                <li><a href="https://github.com" target="_blank" rel="noopener">GitHub</a></li>
                <li><a href="https://linkedin.com" target="_blank" rel="noopener">LinkedIn</a></li>
            </ul>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:560px" data-aos="fade-up">
        <h2>Kirim pesan</h2>
        <form id="contact-form" action="<?= e(url('api/contact.php')) ?>" method="post">
            <div class="form-group">
                <label for="c_name">Nama</label>
                <input type="text" id="c_name" name="name" required maxlength="120">
            </div>
            <div class="form-group">
                <label for="c_user">User</label>
                <input type="text" id="c_user" name="user" required>
            </div>
            <div class="form-group">
                <label for="c_msg">Pesan</label>
                <textarea id="c_msg" name="message" required placeholder="Halo, saya ingin bertanya tentang..."></textarea>
            </div>
            <button type="submit" class="btn">Kirim</button>
            <p id="contact-msg" class="muted" style="margin-top:0.75rem"></p>
        </form>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
