-- Perbaikan gambar artikel JS + hapus emoji kategori (jalankan jika DB sudah ada)
UPDATE articles SET thumbnail = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&h=630&fit=crop'
WHERE slug = 'tips-menulis-kode-javascript-yang-lebih-bersih';

UPDATE categories SET emoji = '';
