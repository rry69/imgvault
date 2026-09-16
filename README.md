# ImgVault

Upload gambar, dapat direct link instan. PHP + MySQL + ImgBB.

## Setup

1. Import `schema.sql` via phpMyAdmin.
2. Copy `.env.example` ke `.env`, isi `IMGBB_API_KEY` (daftar di [api.imgbb.com](https://api.imgbb.com)).
3. Sesuaikan `config/database.php` dengan kredensial DB.
4. Pastikan `uploads/temp/` writable (755).

## Struktur

```
index.php dashboard.php schema.sql
api/ config/ includes/ providers/ assets/
```
