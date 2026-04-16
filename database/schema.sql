-- TechBlog ErinS — MySQL schema (utf8mb4)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS article_bookmarks;
DROP TABLE IF EXISTS article_likes;
DROP TABLE IF EXISTS article_tags;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  user VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','penulis','user') NOT NULL DEFAULT 'user',
  foto VARCHAR(500) DEFAULT NULL,
  bio TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  emoji VARCHAR(16) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(500) NOT NULL,
  slug VARCHAR(520) NOT NULL UNIQUE,
  konten LONGTEXT NOT NULL,
  excerpt TEXT,
  thumbnail VARCHAR(500) DEFAULT NULL,
  author_id INT UNSIGNED NOT NULL,
  kategori_id INT UNSIGNED NOT NULL,
  tanggal_publish DATETIME NOT NULL,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_art_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_art_cat FOREIGN KEY (kategori_id) REFERENCES categories(id) ON DELETE RESTRICT,
  INDEX idx_publish (tanggal_publish),
  INDEX idx_views (views),
  INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_tag VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_tags (
  article_id INT UNSIGNED NOT NULL,
  tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (article_id, tag_id),
  CONSTRAINT fk_at_art FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  CONSTRAINT fk_at_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  guest_nama VARCHAR(120) DEFAULT NULL,
  guest_user VARCHAR(255) DEFAULT NULL,
  article_id INT UNSIGNED NOT NULL,
  isi_komentar TEXT NOT NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_com_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_com_art FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  INDEX idx_art (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id INT UNSIGNED NOT NULL,
  guest_key VARCHAR(64) NOT NULL,
  UNIQUE KEY uq_like (article_id, guest_key),
  CONSTRAINT fk_like_art FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_bookmarks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id INT UNSIGNED NOT NULL,
  guest_key VARCHAR(64) NOT NULL,
  UNIQUE KEY uq_bm (article_id, guest_key),
  CONSTRAINT fk_bm_art FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: users (password plain text: erin07)
INSERT INTO users (nama, user, password, role, foto, bio) VALUES
('Admin Erin', 'admin@techblog.local', 'erin07', 'admin', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop', 'Editor & pendiri TechBlog.'),
('Erin Susnita', 'erinsusnita', 'erin07', 'admin', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&h=200&fit=crop', 'Penulis fokus AI & etika teknologi.'),
('Rei Pratama', 'reipratama', 'erin07', 'penulis', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop', 'Developer & kontributor programming.'),
('Ersya Team', 'ersyateam', 'erin07', 'penulis', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop', 'Jurnalis gadget & startup.');

INSERT INTO categories (nama_kategori, slug, emoji) VALUES
('AI', 'ai', ''),
('Programming', 'programming', ''),
('Gadget', 'gadget', ''),
('Cyber Security', 'cyber-security', ''),
('Startup', 'startup', '');

INSERT INTO tags (nama_tag, slug) VALUES
('Artificial Intelligence', 'artificial-intelligence'),
('Machine Learning', 'machine-learning'),
('Etika', 'etika'),
('Karier', 'karier'),
('Tren 2025', 'tren-2025'),
('JavaScript', 'javascript'),
('Tips', 'tips');

-- Artikel utama (rich HTML)
INSERT INTO articles (judul, slug, konten, excerpt, thumbnail, author_id, kategori_id, tanggal_publish, views, featured) VALUES
('Perkembangan AI di Tahun 2025: Peluang dan Tantangan', 'perkembangan-ai-di-tahun-2025-peluang-dan-tantangan',
'<p>Artificial Intelligence terus berkembang pesat. Di tahun <strong>2025</strong>, kita menyaksikan lonjakan adopsi <em>generative AI</em> di berbagai sektor—dari kreatif hingga otomasi bisnis.</p>
<h2>Tren AI Generatif</h2>
<p>Model bahasa besar (LLM) dan multimodal semakin terintegrasi ke dalam alat produktivitas. Perusahaan mengoptimalkan alur kerja dengan asisten AI yang memahami konteks dokumen dan percakapan.</p>
<h2>Dampak ke Pekerjaan</h2>
<p>Peran baru muncul di bidang <strong>AI governance</strong>, prompt engineering, dan validasi output. Sementara itu, tugas repetitif banyak terotomatisasi—menuntut adaptasi keterampilan dan pembelajaran berkelanjutan.</p>
<h2>Etika Teknologi</h2>
<p>Privasi data, bias model, dan transparansi keputusan menjadi fokus regulasi dan diskusi publik. Kolaborasi antara regulator, industri, dan masyarakat sipil penting untuk memastikan AI memberi manfaat secara adil.</p>
<p><strong>Kesimpulan:</strong> Peluang besar ada bagi yang siap belajar; tantangan utama adalah menjaga keseimbangan antara inovasi dan tanggung jawab sosial.</p>',
'Tren generative AI, dampak ke dunia kerja, dan isu etika teknologi di tahun 2025.',
'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=1200&h=630&fit=crop',
2, 1, '2025-03-15 10:00:00', 1240, 1);

INSERT INTO articles (judul, slug, konten, excerpt, thumbnail, author_id, kategori_id, tanggal_publish, views, featured) VALUES
('Tips Menulis Kode JavaScript yang Lebih Bersih', 'tips-menulis-kode-javascript-yang-lebih-bersih',
'<p>Kode yang mudah dibaca mengurangi bug dan mempercepat onboarding tim. Berikut praktik yang sering diabaikan namun berdampak besar.</p>
<h2>Struktur Modul</h2>
<p>Pecah file berdasarkan tanggung jawab. Hindari file raksasa yang mencampur logika UI, API, dan utilitas.</p>
<h2>Naming & Konsistensi</h2>
<p>Gunakan konvensi yang disepakati tim (camelCase, konstanta UPPER_SNAKE). Nama fungsi harus menjelaskan <em>apa</em> yang dilakukan, bukan hanya <em>bagaimana</em>.</p>
<p>Refactor kecil dan sering lebih aman daripada big bang rewrite.</p>',
'Praktik struktur modul, penamaan, dan refactor untuk JS skala tim.',
'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&h=630&fit=crop',
3, 2, '2025-03-20 14:30:00', 890, 1);

INSERT INTO articles (judul, slug, konten, excerpt, thumbnail, author_id, kategori_id, tanggal_publish, views, featured) VALUES
('Review Smartphone Flagship: Apa yang Layak di 2025?', 'review-smartphone-flagship-apa-yang-layak-di-2025',
'<p>Pasar <strong>gadget</strong> tahun ini menonjolkan efisiensi chip, kamera komputasi, dan daya tahan baterai. Pilih perangkat berdasarkan pola pakai, bukan hanya skor benchmark.</p>
<ul><li>Prioritaskan update software jangka panjang</li><li>Perhatikan kualitas layar outdoor</li><li>Uji konektivitas di lingkungan Anda sehari-hari</li></ul>',
'Kriteria memilih flagship: software, layar, dan kebutuhan nyata.',
'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1200&h=630&fit=crop',
4, 3, '2025-03-22 09:00:00', 2100, 1);

INSERT INTO articles (judul, slug, konten, excerpt, thumbnail, author_id, kategori_id, tanggal_publish, views, featured) VALUES
('Keamanan Siber untuk UMKM: Mulai dari Mana?', 'keamanan-siber-untuk-umkm-mulai-dari-mana',
'<p>UMKM sering menjadi target karena asumsi pertahanan lemah. Langkah pertama: inventaris aset digital, backup rutin, dan autentikasi dua faktor untuk user serta akun cloud.</p>
<p>Edukasi karyawan tentang phishing tetap menjadi pertahanan paling cost-effective.</p>',
'Langkah praktis keamanan siber untuk bisnis kecil.',
'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=1200&h=630&fit=crop',
3, 4, '2025-03-25 11:15:00', 756, 0);

INSERT INTO articles (judul, slug, konten, excerpt, thumbnail, author_id, kategori_id, tanggal_publish, views, featured) VALUES
('Startup Teknologi di Indonesia: Momentum dan Risiko', 'startup-teknologi-di-indonesia-momentum-dan-risiko',
'<p>Ekosistem startup terus mendapat modal ventura dan dukungan kebijakan. Namun, unit economics dan retensi pengguna tetap jadi penentu keberlanjutan.</p>
<p>Fokus pada masalah nyata dan iterasi cepat berdasarkan data pengguna sering lebih berharga daripada fitur yang "keren" tapi jarang dipakai.</p>',
'Gambaran singkat momentum startup tech dan risiko yang perlu diwaspadai.',
'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=1200&h=630&fit=crop',
4, 5, '2025-03-28 16:45:00', 445, 0);

INSERT INTO article_tags (article_id, tag_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5),
(2, 6), (2, 7),
(3, 7),
(4, 7),
(5, 7);

INSERT INTO comments (user_id, article_id, isi_komentar, tanggal) VALUES
(4, 1, 'Artikelnya ringkas dan relevan untuk diskusi tim kami. Terima kasih!', '2025-03-16 08:00:00');
