# CLAUDE CODE — BUILD PROMPT
## SEWOLAH Laravel Rebuild (Landing Page + Admin Panel)

> Salin **keseluruhan fail ini** sebagai prompt pertama kepada Claude Code di dalam repo kosong. Rujuk sekali `PRD_SEWOLAH_Laravel.md` (letakkan dalam root repo) sebagai spesifikasi penuh — prompt ini adalah arahan pelaksanaan (execution instructions) untuk PRD tersebut.

---

## 0. KONTEKS

Anda akan membina semula sistem **SEWOLAH** (perkhidmatan sewa kereta premium untuk pelancong ke Kuala Lumpur) daripada 2 prototaip HTML statik (`SEWOLAH Landing Page.dc.html` dan `SEWOLAH Admin Panel.dc.html`) menjadi aplikasi **Laravel production-ready**. Baca `PRD_SEWOLAH_Laravel.md` dahulu sebelum mula — ia mengandungi UI copy penuh, skema DB, senarai fungsi dan senarai "adjustments" yang WAJIB dilaksanakan.

**Matlamat akhir:** UI dan UX 100% seiras dengan 2 fail `.dc.html` asal (warna, copy, layout, flow borang 3-langkah, dashboard admin), tetapi dijalankan atas Laravel + MySQL sebenar, 100% mobile responsive, dwibahasa (BM/EN), sedia untuk deploy ke Laravel Forge melalui GitHub.

---

## 1. TECH STACK (WAJIB IKUT)

- PHP **8.4**
- Laravel **latest stable** (`laravel/laravel` versi terkini semasa `composer create-project`)
- MySQL **8.4**
- Blade + **Livewire 3**
- **Tailwind CSS** (Vite)
- Alpine.js (built-in dengan Livewire)
- Laravel **Breeze** (mode Blade + Livewire, guna sebagai asas auth admin) — ubahsuai guard kepada keperluan admin-only
- Pest atau PHPUnit untuk testing

---

## 2. LANGKAH PEMBINAAN (IKUT TURUTAN)

### Step 1 — Scaffold Projek
```bash
composer create-project laravel/laravel sewolah
cd sewolah
composer require livewire/livewire
composer require laravel/breeze --dev
php artisan breeze:install blade --dark   # pilih Blade stack; kita override styling dengan brand color lepas ni
php artisan migrate
npm install
```
Konfigurasikan Tailwind dengan warna jenama dalam `tailwind.config.js`:
```js
theme: {
  extend: {
    colors: {
      brand: {
        black: '#000000',
        white: '#FFFFFF',
        red: '#E31E24',
        redHover: '#ff4b52',
        dark: '#161616',
        light: '#F5F5F5',
      }
    },
    fontFamily: {
      sans: ['"Plus Jakarta Sans"', 'sans-serif'],
    }
  }
}
```

### Step 2 — Migrations & Models
Cipta migration & model untuk table berikut (rujuk PRD Seksyen 5 untuk kolum penuh):
- `leads`
- `vehicles`
- `pixel_settings`
- `page_settings`
- Ubah suai table `users` sedia ada: tambah kolum `role` (`enum: super_admin, admin`) — guna ini sebagai model Admin (tak perlu table berasingan).

Setiap model mesti ada `$fillable`/`$casts` betul (`tags` sebagai `array`/`json` cast pada `Vehicle`, `consent` sebagai `boolean` pada `Lead`, dsb).

### Step 3 — Seeders
- `VehicleSeeder`: masukkan 3 kenderaan asal (Proton X70, Volkswagen Arteon R-Line, Toyota Alphard SC) dengan `tags` bilingual (`{"ms": [...], "en": [...]}`) — rujuk data asal dalam `renderVals()` fail `SEWOLAH Landing Page.dc.html` untuk teks tepat.
- `PageSettingSeeder`: default `whatsapp_number = 601116946696`, `admin_notification_email = sewolah@gmail.com`.
- `AdminUserSeeder`: cipta 1 user `role=super_admin` (guna `.env` untuk email/password default, JANGAN hardcode plaintext dalam kod — guna `Hash::make(env('ADMIN_DEFAULT_PASSWORD'))`).

### Step 4 — Localization (Dwibahasa)
- Cipta `lang/ms/landing.php`, `lang/ms/admin.php`, `lang/ms/validation.php` (custom messages), dan versi `lang/en/*` setara.
- **Pindahkan SEMUA copy** dari `SEWOLAH Landing Page.dc.html` (Malay, sudah ada dalam fail) ke `lang/ms/landing.php` sebagai array bersarang ikut seksyen (`hero`, `problem`, `solution`, `vehicles`, `whySewolah`, `howItWorks`, `bookingForm`, `footer`, dll).
- **Terjemahkan setiap string ke English** dan letak dalam `lang/en/landing.php` dengan struktur array yang **sama persis** (key sepadan).
- Middleware `SetLocale` (`app/Http/Middleware/SetLocale.php`): baca `session('locale')`, default `ms`, apply `App::setLocale()`. Daftar dalam `bootstrap/app.php` (Laravel 11+) sebagai middleware global web.
- Livewire component `LanguageSwitcher` (butang BM/EN di header & footer) — `wire:click` set session locale + refresh page (guna `redirect(request()->header('Referer'))` atau `$this->js('window.location.reload()')`).

### Step 5 — Layout & Komponen Awam (Public)
Cipta `resources/views/layouts/public.blade.php` sebagai layout utama (head, fonts, meta SEO dinamik, pixel snippet dinamik, `@livewireStyles`/`@livewireScripts`).

Bina Blade component untuk **setiap seksyen** disenaraikan dalam PRD Seksyen 6 (16 seksyen). Setiap component:
- Guna Tailwind classes (bukan inline style) tapi **kekalkan visual 100% sama** (warna, spacing, radius, saiz font — ikut nilai px/rem yang ada dalam `.dc.html` asal sebagai rujukan tepat).
- Semua teks guna `{{ __('landing.xxx.yyy') }}`.
- Mobile responsive by default (rujuk PRD Seksyen 11).

**Rujuk fail asal `SEWOLAH Landing Page.dc.html` baris demi baris** semasa membina setiap component — copy struktur HTML, class/hierarchy, dan data loop (`sc-for` → Blade `@foreach`, `sc-if` → Blade `@if`) TANPA mengubah susunan atau menghilangkan sebarang seksyen.

### Step 6 — Livewire: BookingForm (Komponen Paling Kritikal)
Cipta `app/Livewire/Public/BookingForm.php` + view `resources/views/livewire/public/booking-form.blade.php`.

Logik yang WAJIB ada (rujuk PRD Seksyen 7 penuh):
- Public properties untuk semua field borang (`origin, airport, purpose, arrivalDate, arrivalTime, endDate, vehicleId, otherVehicleModel, passengers, luggage, destination, fullName, phone, notes, consent`).
- `public int $step = 1;`
- Method `nextStep()`, `prevStep()` dengan validation rules berlainan ikut step (guna `#[Validate]` attribute atau `rules()` method dinamik ikut `$this->step`).
- Rule nombor telefon Malaysia: `'phone' => ['required','regex:/^(\+?6?01)[0-46-9]-*[0-9]{7,8}$/']`.
- Rule `endDate >= arrivalDate`.
- Rule `otherVehicleModel` required_if `vehicleId` = ID kenderaan "Other Vehicle" (atau guna value khas `'other'`).
- Method `selectVehicle($vehicleId)` — dipanggil dari CTA kad kenderaan di Seksyen Featured Vehicles (`wire:click="selectVehicle({{ $vehicle->id }})"`) — auto scroll & pra-isi.
- Method `submit()`:
  1. Validate semua field.
  2. Create record `Lead` (termasuk UTM data yang disimpan dalam session sejak page load — cipta middleware/listener `CaptureUtmParams` untuk simpan query string `utm_*`, `fbclid`, `referrer` ke session semasa first visit).
  3. Dispatch queued job/Mailable `NewLeadNotification` ke `page_settings.admin_notification_email`.
  4. `$this->submitted = true`.
  5. Dispatch browser event `open-whatsapp` dengan payload URL `wa.me` + mesej (guna helper untuk generate mesej ikut locale — rujuk PRD Seksyen 7.4 untuk kedua-dua template BM & EN).
  6. Dispatch event untuk Meta Pixel `Lead` (Alpine/JS listener yang panggil `fbq('track','Lead')` bila event Livewire diterima).
- Guna `wire:model.live` pada field untuk enable/disable butang "SETERUSNYA" secara real-time (padan dengan `step1Disabled`/`step2Disabled`/`step3Disabled` logic asal).
- Progress dots & label "LANGKAH X/3" (BM) / "STEP X/3" (EN) — computed property.

### Step 7 — Layout & Livewire Admin Panel
- `resources/views/layouts/admin.blade.php` — sidebar 220px desktop, **drawer/bottom-nav di mobile** (adjustment WAJIB — asal tiada responsive handling untuk admin).
- Route group `/admin` dengan middleware `auth`, `role:super_admin|admin` (cipta middleware `EnsureAdminRole` ringkas berasaskan kolum `role`).
- Halaman login guna Breeze punya `login.blade.php` yang di-restyle ikut visual admin asal (background `#0A0A0A`, kad `#141414`, logo, "SUPER ADMIN PANEL" label) — **BUANG** teks "Demo: admin / sewolah2026" (itu risiko keselamatan, bukan untuk production).
- `app/Livewire/Admin/Dashboard.php`:
  - Computed properties untuk stat cards, chart bars, vehicle breakdown, funnel — **query terus dari `Lead::query()`**, bukan `localStorage`.
  - Filter tempoh (Mingguan/Bulanan/Custom) — public property `$range = 'week'` + `$customFrom`/`$customTo`.
  - Guna Chart.js (via `resources/js/charts.js` + Alpine `x-data` wrapper) untuk bar chart trend — hantar data dari PHP sebagai JSON prop ke Alpine component. (Boleh kekalkan versi custom CSS-bar jika mahu lebih ringkas — kedua-dua boleh diterima, asalkan visual & data tepat.)
- `app/Livewire/Admin/BookingsTable.php`:
  - `WithPagination` trait.
  - Public properties `$search`, `$statusFilter`, `$sortField`, `$sortDirection`.
  - Method `updateStatus($leadId, $status)`.
  - Method `exportCsv()` — return `StreamedResponse` guna `League\Csv` atau `Maatwebsite\Excel` (`composer require maatwebsite/excel` jika mahu Excel).
  - Modal/side-panel "Detail Pelanggan" (Livewire nested component atau `x-data` modal Alpine, papar semua field lead).
- `app/Livewire/Admin/PixelSettings.php`: form simpan `meta_pixel_id`, `google_ads_id`, `tiktok_pixel_id` ke table `pixel_settings` (guna `updateOrCreate` — 1 row sahaja, atau `firstOrCreate(['id'=>1])`).
- `app/Livewire/Admin/PageSettings.php`: form simpan `whatsapp_number`, `admin_notification_email`.
- `app/Livewire/Admin/VehicleManager.php` (BAHARU — rujuk PRD Seksyen 8.5 & Adjustment #6): CRUD penuh kenderaan (nama, kategori, upload gambar guna `WithFileUploads`, tags BM/EN, featured toggle, sort order, active toggle).

### Step 8 — Meta Pixel / Tracking Integration
- Blade partial `components.pixel-scripts` — inject snippet Facebook Pixel, Google Ads gtag, TikTok Pixel **secara kondisional** (`@if($pixelSettings->meta_pixel_id)`), guna nilai dari `pixel_settings` (bukan hardcode).
- Fire events:
  - `PageView` — automatik dalam snippet standard.
  - `ViewContent` — bila scroll masuk seksyen Featured Vehicles (Alpine `x-intersect` / simple JS IntersectionObserver dispatch fbq event).
  - `FormStart` — bila Livewire `BookingForm` step berubah dari null→1 pertama kali user interact (dispatch browser event dari component).
  - `Lead` — hanya lepas `submit()` berjaya (Step 6).
  - `Contact` — bila WhatsApp dibuka (event `open-whatsapp` listener).

### Step 9 — SEO
- `resources/views/layouts/public.blade.php`: `<title>`, `<meta name="description">`, Open Graph (`og:title`, `og:description`, `og:image` guna `hero-full.jpg`/cover asset sedia ada), Twitter Card, `<link rel="canonical">`.
- JSON-LD `AutomotiveBusiness` schema (nama, alamat kawasan servis KL & Selangor, telefon dari `page_settings`).
- `public/robots.txt` + route `sitemap.xml` (guna package `spatie/laravel-sitemap` atau generate manual).

### Step 10 — Anti-Spam & Security
- Honeypot field tersembunyi dalam `BookingForm` (field `website` yang mesti kosong — jika ada nilai, reject silently).
- `throttle:6,1` middleware pada route booking form submission (jika guna route berasingan) atau Livewire rate limiting (`Illuminate\Support\Facades\RateLimiter`).
- Laravel default CSRF (Livewire handle automatik).
- Validation & sanitization semua input (Laravel default escaping dalam Blade `{{ }}` sudah cukup — JANGAN guna `{!! !!}` untuk user input).

### Step 11 — Testing
Cipta Feature test (Pest disyorkan) untuk:
- `BookingFormTest`: submit borang penuh (happy path) → assert `Lead` created, assert email queued, assert event `open-whatsapp` dispatched.
- `BookingFormValidationTest`: field kosong/invalid → assert error.
- `AdminAuthTest`: login gagal/berjaya, akses `/admin` tanpa login → redirect.
- `AdminDashboardTest`: assert stat cards papar jumlah lead betul.
- `AdminBookingsTableTest`: search, filter status, update status, export CSV.
- `LocalizationTest`: tukar locale BM↔EN, assert string berubah.

### Step 12 — Assets
- Import semua imej dari folder asal (`assets/*.jpg`, `assets/*.png`, `uploads/*`) ke `public/images/` atau `storage/app/public/vehicles/` (untuk imej kenderaan yang diurus via `VehicleManager`).
- Convert & compress ke WebP (guna Intervention Image atau `php artisan` command custom) — target saiz jauh lebih kecil dari asal (`hero-full.jpg` 3.1MB → target <300KB WebP).
- `loading="lazy"` pada semua imej below-the-fold.

### Step 13 — Deployment Prep
- `.env.example` lengkap: `APP_NAME=SEWOLAH`, `APP_LOCALE=ms`, `DB_CONNECTION=mysql`, `MAIL_*`, `ADMIN_DEFAULT_PASSWORD` (untuk seeder sahaja, buang selepas first deploy).
- `README.md` ringkas: cara setup local (`composer install`, `npm install`, `.env`, `migrate --seed`, `npm run dev`).
- Sediakan Forge deployment script (rujuk PRD Seksyen 15.2) — letak dalam `README.md` bahagian "Deployment" supaya senang copy-paste ke Forge dashboard.
- (Opsyenal) `.github/workflows/ci.yml` — run `composer install`, `php artisan test`, `npm run build` pada setiap push/PR.

---

## 3. CHECKLIST PENERIMAAN (Definition of Done)

Tandakan semua sebelum anggap projek siap:

- [ ] Landing page 100% seiras `.dc.html` asal (semua 16 seksyen, copy tepat, warna tepat, CTA berfungsi).
- [ ] Borang tempahan 3-langkah berfungsi penuh (validation, progress indicator, WhatsApp redirect dengan mesej pra-isi betul).
- [ ] Semua submission tersimpan dalam MySQL (`leads` table), bukan localStorage.
- [ ] Admin panel: login sebenar (bukan hardcoded), dashboard data live, jadual leads dengan search/filter/pagination/export, update status, tetapan pixel & page settings berfungsi, CRUD kenderaan berfungsi.
- [ ] Dwibahasa penuh — semua string (landing, borang, admin, email, mesej WhatsApp) ada versi BM & EN, language switcher berfungsi.
- [ ] 100% mobile responsive — landing page & admin panel diuji pada 360px, 390px, 768px, 1024px+.
- [ ] SEO meta lengkap (title, description, OG, JSON-LD, sitemap, robots.txt).
- [ ] Pixel tracking (Meta/Google/TikTok) berfungsi dinamik ikut settings admin, event `PageView/ViewContent/FormStart/Lead/Contact` fire dengan betul.
- [ ] Anti-spam (honeypot + rate limit) aktif pada borang.
- [ ] Semua 19 "Adjustments" dalam PRD Seksyen 10 telah dilaksanakan.
- [ ] Test suite (Pest/PHPUnit) lulus sepenuhnya.
- [ ] `.env.example`, `README.md`, deployment script Forge sedia untuk hand-off.
- [ ] Repo di-push ke GitHub, branch `main` bersih & boleh di-deploy terus ke Forge tanpa error.

---

## 4. NOTA UNTUK CLAUDE CODE

- **Jangan** bawa masuk fail `support.js` atau format `<x-dc>` / `sc-for` / `sc-if` ke dalam Laravel — itu format khusus design-canvas prototaip dan digantikan sepenuhnya dengan Blade/Livewire syntax standard.
- **Sentiasa rujuk balik** `SEWOLAH Landing Page.dc.html` dan `SEWOLAH Admin Panel.dc.html` sebagai *source of truth* untuk copy Bahasa Malaysia, urutan seksyen, dan logik state (contoh: `step1Disabled` logic, `isOtherVehicle` conditional, format mesej WhatsApp) — jangan reka semula logik dari awal, **port** logik JS asal ke PHP/Livewire dengan tepat.
- Bila ragu-ragu tentang sesuatu nilai visual (padding, saiz font, warna), **ambil nilai px/rem yang tertera dalam inline style `.dc.html` asal** sebagai rujukan tepat, walaupun ditukar ke Tailwind utility class.
- Utamakan menyiapkan **Modul A (Landing Page + Booking Form)** dahulu sepenuhnya berfungsi hujung-ke-hujung (termasuk simpan DB + WhatsApp redirect) sebelum menyempurnakan **Modul B (Admin Panel)** — ini membolehkan ujian end-to-end lebih awal.
- Selepas siap setiap modul besar, jalankan `php artisan test` dan laporkan status sebelum meneruskan ke langkah seterusnya.
