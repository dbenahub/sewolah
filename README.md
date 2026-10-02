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

## Susunan folder

```
Web Apps - SEWOLAH/
├── app/ bootstrap/ config/ database/      ← kod website (JANGAN pindah)
├── lang/ public/ resources/ routes/
├── storage/ tests/
├── artisan, composer.json, package.json,  ← fail konfigurasi Laravel (JANGAN pindah)
│   vite.config.js, tailwind.config.js, ...
├── PUSH_KE_GITHUB.bat                     ← klik dua kali untuk hantar perubahan ke GitHub
├── README.md                              ← fail ini
└── _PROJEK_SEWOLAH/                       ← bahan projek (TIDAK digunakan oleh website)
    ├── 01_Dokumen_Perancangan/            ← PRD, build prompt, dokumen asal
    ├── 02_Prototaip_Reka_Bentuk/          ← prototaip .dc.html + support.js + assets/
    ├── 03_Bahan_Jenama_Asal/              ← logo, cover Facebook, gambar kereta & foto asal
    ├── 04_Preview_Website/                ← tangkapan skrin laman baru
    └── 05_Arkib/                          ← fail lama / sandaran
```

Semua fail kod website mesti kekal di tempat asal — Forge deploy terus dari struktur ini.

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
