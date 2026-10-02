# SEWOLAH — Laravel/Livewire Platform

Website dan admin panel SEWOLAH (sewolah.com) — **Laravel 11, PHP 8.4, MySQL, Blade + Livewire 3**. Deploy melalui GitHub (`dbenahub/sewolah`) + Laravel Forge.

## Halaman utama

| URL | Fungsi | Fail utama |
|---|---|---|
| `/` | Laman utama korporat (rangkaian kereta sewa Semenanjung) | `app/Livewire/Public/Home.php`, `resources/views/livewire/public/home.blade.php` |
| `/form` (`/borang`, `/tempah`) | Borang permohonan tempahan umum (5 kategori pelanggan, min. 3 hari bekerja) | `app/Livewire/Public/RequestForm.php`, `resources/views/livewire/public/request-form.blade.php` |
| `/outstation` | Landing page outstation / KLIA (paparan lama) | `app/Livewire/Public/LandingPage.php`, `BookingForm.php` |
| `/admin` | Admin panel (tempahan, kenderaan, tetapan) | `app/Livewire/Admin/*` |

Gaya laman utama & borang: `public/css/sewolah.css` (CSS biasa, tiada build). Teks BM/EN: `lang/ms/*.php`, `lang/en/*.php`. Polisi notis minimum: `config/sewolah.php` (`SEWOLAH_MIN_WORKING_DAYS`, default 3).

## Susunan folder (di komputer)

```
Web Apps - SEWOLAH/
├── 00 - PANDUAN (buka saya).html   ← panduan & pautan penting
├── 1 - HANTAR KE GITHUB.bat        ← hantar perubahan kod ke GitHub
├── 01_Sistem_Laravel/              ← REPO GIT ini (kod website) — deploy oleh Forge
├── 02_Dokumen/                     ← PRD, build prompt, dokumen asal
├── 03_Rekaan_Asal_HTML/            ← prototaip .dc.html + support.js + assets/
├── 04_Bahan_Jenama/                ← logo, cover Facebook, gambar kereta & foto asal
├── 05_Preview_Website/             ← tangkapan skrin laman
└── 06_Arkib/                       ← fail lama / log
```

Hanya `01_Sistem_Laravel/` berada dalam repo GitHub. Folder lain ialah bahan rujukan di komputer sahaja.
Jangan ubah susunan folder di dalam `01_Sistem_Laravel/` — Forge deploy terus dari struktur ini.

## Setup local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

## Ujian

```bash
vendor/bin/phpunit --testsuite Feature
```

## Deploy

1. Klik dua kali `PUSH_KE_GITHUB.bat` (atau `git push origin main`).
2. Forge → site `sewolah.com` → **Deploy**. Deploy script menjalankan `git pull`, `composer install`, `php artisan migrate --force`, `npm run build` dan cache.
