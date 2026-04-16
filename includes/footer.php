    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <span class="logo-mark">TB</span>
                <strong><?= e(SITE_NAME) ?></strong>
                <p>Artikel teknologi pilihan untuk developer &amp; penggemar gadget.</p>
            </div>
            <div>
                <h4>Navigasi</h4>
                <ul class="footer-links">
                    <li><a href="<?= e(url('articles.php')) ?>">Semua artikel</a></li>
                    <li><a href="<?= e(url('category.php')) ?>">Kategori</a></li>
                    <li><a href="<?= e(url('about.php')) ?>">Tentang</a></li>
                </ul>
            </div>
            <div>
                <h4>Media sosial</h4>
                <ul class="footer-social">
                    <li><a href="https://twitter.com" target="_blank" rel="noopener">Twitter / X</a></li>
                    <li><a href="https://github.com" target="_blank" rel="noopener">GitHub</a></li>
                    <li><a href="https://linkedin.com" target="_blank" rel="noopener">LinkedIn</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= (int)date('Y') ?> <?= e(SITE_NAME) ?>. Dibuat untuk portofolio &amp; tugas.</p>
            </div>
        </div>
    </footer>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
