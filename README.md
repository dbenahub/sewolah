# SEWOLAH — Laravel/Livewire Platform

Rebuild penuh landing page + admin panel SEWOLAH (asalnya prototaip `.dc.html`) menggunakan **Laravel (latest), PHP 8.4, MySQL 8.4, Blade + Livewire 3, Tailwind CSS**.

> Rujuk `PRD_SEWOLAH_Laravel.md` untuk spesifikasi penuh dan `CLAUDE_CODE_BUILD_PROMPT.md` untuk senarai kerja lanjutan (Fasa 2) — projek ini ialah **scaffold MVP berfungsi** (landing page + borang tempahan 3-langkah + admin panel asas) yang sedia untuk deploy dan diperluaskan.

## Setup Local

```bash
composer install
cp .env.example .env
php artisan key:generate
# kemaskini .env — DB_*, ADMIN_DEFAULT_EMAIL, ADMIN_DEFAULT_PASSWORD
php artisan migrate --seed
php artisan storage:link
npm install
npm run dev   # atau: npm run build untuk production
php artisan serve
```

Log masuk admin di `/admin/login` menggunakan `ADMIN_DEFAULT_EMAIL` / `ADMIN_DEFAULT_PASSWORD` yang diset dalam `.env` (default seeder: `admin@sewolah.com`).

## Struktur Utama

- `app/Livewire/Public/BookingForm.php` — borang tempahan 3-langkah, validation, simpan lead, redirect WhatsApp.
- `app/Livewire/Public/LandingPage.php` — landing page penuh (16 seksyen), capture UTM.
- `app/Livewire/Admin/*` — Dashboard, BookingsTable (search/filter/export/update status), PixelSettings, PageSettings, VehicleManager (CRUD kenderaan).
- `lang/ms/*`, `lang/en/*` — semua string bilingual (landing + admin).
- `database/migrations/*` — leads, vehicles, pixel_settings, page_settings, users(+role).
- `database/seeders/*` — 3 kenderaan asal, admin user, page settings default.

## Deployment — GitHub + Laravel Forge

1. **Push ke GitHub** (repo `dbenahub/sewolah`):
   ```bash
   git add .
   git commit -m "Scaffold Laravel/Livewire SEWOLAH platform"
   git push
   ```
2. **Forge**: New Site → sambung repo `dbenahub/sewolah`, branch `main`.
3. **Environment**: copy isi `.env.example` ke `.env` dalam Forge (isi `DB_*` ikut database Forge, `APP_KEY` dijana semasa deploy, `ADMIN_DEFAULT_EMAIL`/`ADMIN_DEFAULT_PASSWORD` untuk seeder pertama).
4. **Deploy script** (Forge → Site → App / Deploy Script):
   ```bash
   cd /home/forge/sewolah.on-forge.com
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   npm ci
   npm run build
   php artisan storage:link || true
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan queue:restart
   ```
5. **Seed data pertama sahaja** (sekali sahaja selepas migrate pertama, via Forge SSH/Commands tab):
   ```bash
   php artisan db:seed
   ```
6. Aktifkan **Queue Worker** (Forge → Site → Queue) untuk proses email lead notification, dan **SSL** (Let's Encrypt).

## Apa yang belum lengkap (Fasa 2)

Scaffold ini merangkumi flow teras (landing page, borang tempahan, WhatsApp redirect, admin dashboard/bookings/settings/vehicles). Item berikut dari PRD masih perlu ditambah oleh Claude Code / pembangun seterusnya:
- Pemecahan landing page kepada Blade component berasingan per-seksyen (kini satu view besar).
- Meta/Google/TikTok Conversions API (server-side), bukan client-side pixel sahaja.
- Sitemap.xml, JSON-LD schema penuh.
- Google reCAPTCHA v3 (honeypot asas sudah ada).
- Toggle 2FA admin, activity log.
- Test suite tambahan (baru 1 feature test ditulis sebagai contoh).

Rujuk `CLAUDE_CODE_BUILD_PROMPT.md` untuk checklist penuh.
