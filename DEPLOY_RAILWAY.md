# Deploy Laravel (Dashboard Diklat) ke Railway

Panduan ringkas dan praktis untuk menjalankan aplikasi Laravel ini di Railway dengan biaya murah/gratis.

## Prasyarat
- Repo sudah ada di GitHub/GitLab/Bitbucket.
- `composer.json` valid dan bekerja lokal.
- PHP 8.2+ kompatibel (disarankan).
- Database MySQL/MariaDB siap (Railway dapat membuat service DB).

## Konsep Utama Railway
- Build: otomatis via Nixpacks (mendeteksi PHP & Composer).
- Start: jalankan server dengan port `PORT` dari environment.
- Service terpisah: App (Laravel) dan Database (MySQL).
- Free tier cocok untuk demo/staging; layanan dapat “sleep”.

## Langkah Deploy via Dashboard (UI)
1. Buat akun di Railway: https://railway.app/ dan login.
2. New Project → Deploy from Repo → pilih repo aplikasi ini.
3. Setelah service App terbentuk, tambah Database:
   - Add Service → MySQL → tunggu provisioning.
4. Hubungkan App ke Database:
   - Masuk ke service App → Variables → tambahkan variabel berikut (gunakan nilai dari service MySQL Railway):
     - `DB_CONNECTION=mysql`
     - `DB_HOST=<host dari MySQL service>`
     - `DB_PORT=<port dari MySQL service>`
     - `DB_DATABASE=<database dari MySQL service>`
     - `DB_USERNAME=<user dari MySQL service>`
     - `DB_PASSWORD=<password dari MySQL service>`
5. Set variabel aplikasi:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://<domain-subdomain-railway>`
   - `APP_KEY=<isi dengan hasil php artisan key:generate --show dari lokal>`
6. Konfigurasi Start Command (service App → Settings/Start Command):
   - `php -S 0.0.0.0:$PORT -t public`
   - Ini akan menjalankan built-in PHP server dengan document root `public`.
7. Build otomatis:
   - Biarkan Nixpacks default (Railway akan menjalankan `composer install` saat build).
   - Bila perlu, aktifkan `COMPOSER_MIRROR_PATH_REPOS=1` untuk cache composer.
8. Deploy:
   - Setelah variabel set, klik Deploy/Re-deploy. Tunggu hingga status Healthy.
9. Jalankan migrasi database:
   - Buka service App → Launch Shell (atau Railway CLI) → jalankan:
     - `php artisan migrate --force`
   - Jika perlu storage symlink:
     - `php artisan storage:link`
10. Uji aplikasi:
   - Buka URL yang diberikan Railway (mis. `https://<app-name>.up.railway.app`).

## Alternatif via Railway CLI (opsional)
- Instal: `npm i -g @railway/cli`
- Login: `railway login`
- Inisialisasi: `railway init`
- Set env vars: `railway variables set APP_KEY=... DB_HOST=...` (atau lewat UI).
- Deploy: `railway up`

## Catatan Penting
- Free tier: layanan dapat “sleep” saat idle; job `queue:work` dan `schedule:run` tidak andal di gratisan.
- Simpan file upload di storage; pastikan `storage` dapat ditulis. Gunakan S3/Cloud storage bila perlu.
- Jika menggunakan fitur berat (CSV besar), pertimbangkan upgrade plan atau jalankan impor secara bertahap.

## Troubleshooting
- 500 error setelah deploy:
  - Cek `APP_KEY`, `APP_URL`, dan kredensial DB. Jalankan `php artisan config:clear` lalu `php artisan config:cache` via shell.
- Tidak bisa konek DB:
  - Pastikan env `DB_*` sesuai variabel MySQL Railway, dan service App diberi akses ke service DB.
- Halaman kosong/static:
  - Pastikan Start Command mengarah ke `public`: `php -S 0.0.0.0:$PORT -t public`.
- Composer gagal saat build:
  - Periksa versi PHP target; tambahkan platform ke `composer.json` bila perlu ("platform": {"php": "8.2"}).

## Produksi Ringan (disarankan jika butuh stabil)
- Pertimbangkan VPS murah (mis. $4–$5) dengan Nginx + PHP‑FPM + Let’s Encrypt untuk uptime stabil dan kontrol penuh.

