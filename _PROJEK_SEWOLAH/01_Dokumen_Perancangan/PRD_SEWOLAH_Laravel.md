# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## SEWOLAH — Car Rental Platform (Laravel Rebuild)

**Versi:** 2.0 (Laravel/Livewire Rebuild)
**Sumber asal:** `SEWOLAH Landing Page.dc.html`, `SEWOLAH Admin Panel.dc.html`, `support.js`, `PRD_Landing_Page_SEWOLAH.docx`
**Disediakan untuk:** Pasukan pembangunan / Claude Code
**Bahasa dokumen:** Bahasa Malaysia + English (istilah teknikal kekal dalam English)

---

## 1. LATAR BELAKANG & OBJEKTIF

SEWOLAH ialah perkhidmatan sewa kereta premium untuk pelancong yang terbang ke Kuala Lumpur (family traveller & business traveller). Prototaip sedia ada dibina sebagai dua fail HTML "design-canvas" (single-page React-like component, state disimpan dalam `localStorage`, tiada backend sebenar):

1. **`SEWOLAH Landing Page.dc.html`** — landing page awam + borang tempahan 3-langkah + redirect WhatsApp.
2. **`SEWOLAH Admin Panel.dc.html`** — panel admin (login demo, dashboard, senarai leads, tetapan pixel, tetapan landing page).

Objektif projek ini: **tiru 100% UI/UX & fungsi** kedua-dua fail tersebut, dan bina semula sebagai aplikasi web production-grade menggunakan:

- **Laravel (versi latest stable)**
- **PHP 8.4**
- **MySQL 8.4**
- **Blade** (templating)
- **Livewire 3** (interaktiviti — menggantikan komponen "DCLogic" React-like asal)
- **Tailwind CSS** (styling, menggantikan inline `style=""` asal)
- **Alpine.js** (built-in dengan Livewire, untuk micro-interaction seperti sticky CTA, mobile menu)

Sistem mesti:
- 100% mobile responsive (mobile-first, sama seperti requirement asal).
- Dwibahasa penuh (Bahasa Malaysia sebagai default + English), boleh tukar bahasa (language switcher).
- Disimpan di GitHub (repo persendirian) dan di-deploy melalui **Laravel Forge**.
- Menggantikan semua simulasi `localStorage` dengan **backend sebenar** (MySQL, queue, email/WhatsApp notification, tracking).

---

## 2. SKOP SISTEM (2 MODUL UTAMA)

### Modul A — Public Landing Page (`/`)
Halaman awam sepenuhnya bilingual, mengandungi semua 15 seksyen asal + borang tempahan 3-langkah.

### Modul B — Admin Panel (`/admin`)
Panel pentadbiran dengan authentication sebenar (bukan hardcoded), dashboard analitik, pengurusan leads/tempahan, tetapan pixel tracking, dan tetapan kandungan landing page.

---

## 3. AUDIENS SASARAN (kekal sama seperti PRD asal)

**A. Family Traveller** — datang KL bersama keluarga (spouse, anak, ibu bapa), banyak luggage, bagasi. Pain point: tidak mahu drive jauh, penat, bergantung e-hailing, perlu fleksibiliti pelbagai lokasi.

**B. Business Traveller** — business owner, SME owner, director, executive, corporate guest. Pain point: banyak meeting di lokasi berbeza, tidak mahu tunggu e-hailing, perlukan kenderaan profesional.

**Core insight (mesej utama):** *"Tak perlu drive jauh ke KL. Sampai airport, kereta dah ready."*

---

## 4. TECH STACK & ARKITEKTUR

| Layer | Teknologi |
|---|---|
| Bahasa | PHP 8.4 |
| Framework | Laravel 11/12 (versi latest stable semasa `composer create-project`) |
| Database | MySQL 8.4 |
| Templating | Blade |
| Interaktiviti frontend | Livewire 3 + Alpine.js (built-in) |
| CSS Framework | Tailwind CSS 3.x (utility-first, gantikan semua inline style asal) |
| Auth Admin | Laravel Fortify / Breeze (session-based, guard `admin`) |
| Queue | Database/Redis queue (untuk hantar email & webhook tanpa block request) |
| Mail | Laravel Mail (SMTP — Forge / Mailgun / SES) |
| Storage fail | Laravel Filesystem (`public` disk, boleh upgrade ke S3 kemudian) |
| Localization | Laravel `lang/ms` & `lang/en` + middleware locale switch |
| Testing | Pest / PHPUnit (feature test untuk form submission, admin CRUD) |
| Version control | Git → GitHub (private repo) |
| Deployment | Laravel Forge (nginx, PHP-FPM 8.4, MySQL 8.4, SSL Let's Encrypt, queue worker, scheduler) |
| Asset build | Vite |

**Struktur folder cadangan (ringkas):**
```
app/
  Livewire/
    Public/
      BookingForm.php
      LandingPage.php  (opsyenal, jika perlu logik dinamik header/CTA)
    Admin/
      Dashboard.php
      BookingsTable.php
      LeadDetail.php
      PixelSettings.php
      PageSettings.php
      VehicleManager.php   (baharu — lihat Seksyen 10)
  Models/
    Lead.php
    Vehicle.php
    PixelSetting.php
    PageSetting.php
    Admin.php (atau guna model User sedia ada dengan role)
  Mail/
    NewLeadNotification.php
  Notifications/
    NewLeadWhatsAppNotification.php (optional integration)
resources/
  views/
    livewire/...
    components/ (layout, nav, footer, vehicle-card, form-step, dsb.)
  lang/
    ms/*.php
    en/*.php
database/
  migrations/
  seeders/
    VehicleSeeder.php
    DemoLeadSeeder.php
routes/
  web.php
```

---

## 5. SKEMA PANGKALAN DATA (MySQL 8.4)

### 5.1 `leads` (menggantikan `localStorage.sewolah_leads`)
| Column | Type | Nota |
|---|---|---|
| id | bigint PK | |
| full_name | varchar(150) | |
| phone | varchar(30) | format WhatsApp |
| origin | varchar(150) | Datang dari negeri/bandar |
| airport | enum/varchar | KLIA / KLIA2 / Subang Airport / Other |
| arrival_date | date | |
| arrival_time | time | |
| end_date | date | Tarikh tamat sewa |
| purpose | varchar(50) | Family Trip / Business Trip / Corporate / Holiday / Event / Other |
| vehicle_id | FK nullable → vehicles.id | |
| vehicle_name_snapshot | varchar(150) | simpan nama semasa hantar (jika vehicle dipadam kemudian) |
| other_vehicle_model | varchar(150) | nullable |
| passengers | unsignedTinyInt | |
| luggage | varchar(30) | Light / Medium / Heavy / Not Sure |
| destination | varchar(150) | |
| notes | text | nullable |
| consent | boolean | |
| status | enum | `baru`, `dihubungi`, `quotation_dihantar`, `disahkan`, `batal` |
| locale | varchar(5) | `ms` / `en` — bahasa semasa isi borang |
| utm_source, utm_medium, utm_campaign, utm_content, utm_term | varchar(150) nullable | |
| landing_page_url | text nullable | |
| referrer | text nullable | |
| fbclid | varchar(150) nullable | |
| whatsapp_opened_at | timestamp nullable | |
| submitted_at | timestamp | |
| created_at / updated_at | timestamp | |

### 5.2 `vehicles` (baharu — gantikan array hardcoded dalam JS)
| Column | Type | Nota |
|---|---|---|
| id | bigint PK | |
| name | varchar(150) | cth. Proton X70 |
| category | varchar(100) | cth. Family SUV |
| image_path | varchar(255) | |
| tags | json | senarai "suitable for" |
| cta_label | varchar(100) | |
| is_featured | boolean | papar di seksyen "Featured Vehicles" |
| sort_order | int | |
| is_active | boolean | |

### 5.3 `pixel_settings`
| Column | Type |
|---|---|
| id | bigint PK |
| meta_pixel_id | varchar(50) nullable |
| google_ads_id | varchar(50) nullable |
| tiktok_pixel_id | varchar(50) nullable |
| updated_by | FK → admins.id |
| updated_at | timestamp |

### 5.4 `page_settings`
| Column | Type |
|---|---|
| id | bigint PK |
| whatsapp_number | varchar(20) |
| admin_notification_email | varchar(150) |
| updated_at | timestamp |

### 5.5 `admins` (guna table `users` Laravel + kolum `role`, atau table berasingan)
| Column | Type |
|---|---|
| id | bigint PK |
| name | varchar |
| email | varchar unique |
| password | hashed |
| role | enum(`super_admin`,`admin`) |
| last_login_at | timestamp nullable |

> **Nota migrasi data lama:** Sediakan seeder `DemoLeadSeeder` untuk import contoh data (opsyenal) supaya dashboard boleh diuji tanpa data kosong.

---

## 6. MODUL A — PUBLIC LANDING PAGE (spesifikasi seksyen demi seksyen)

Setiap seksyen di bawah **mesti direplikakan 100%** dari `SEWOLAH Landing Page.dc.html` (copy, struktur, urutan) dan dijadikan **Blade component** berasingan supaya boleh diguna semula & mudah diselenggara. Semua teks statik mesti melalui fail bahasa (`__('landing.hero.title')` dsb.) — **tiada hardcoded string dalam Blade**.

| # | Seksyen | Komponen Blade dicadangkan | Fungsi / Nota |
|---|---|---|---|
| 1 | Header/Nav (sticky, blur bg) | `components.public.header` | Logo, nav anchor (Pilihan Kereta / Cara Tempahan / Booking), CTA "TEMPAH KERETA", **mobile hamburger menu (tiada dalam versi asal — WAJIB ditambah, lihat Seksyen 9 "Adjustments")** |
| 2 | Hero | `components.public.hero` | Headline, subcopy, badge "RENT WITH CONFIDENCE", 2 CTA, imej hero, trust line |
| 3 | Partner Trust Bar | `components.public.partner-bar` | GoGreenMatrix strategic partner copy |
| 4 | Problem Section | `components.public.problem-section` | 4 kad masalah (loop dari config/lang, bukan hardcoded array JS) |
| 5 | Solution Section | `components.public.solution-section` | 4 langkah journey (Flight→Airport→Kereta Ready→Destination) |
| 6 | Featured Vehicles | `components.public.vehicle-card` (loop) | Data dari table `vehicles` (bukan array hardcoded). Klik CTA pra-isi pilihan kenderaan dalam borang (Livewire `wire:click`) |
| 7 | Other Vehicle Options | `components.public.other-categories` | Senarai kategori (chip), CTA "MINTA TEAM CARI KERETA" |
| 8 | Family Travel | `components.public.audience-block` (variant family) | |
| 9 | Business Travel | `components.public.audience-block` (variant business, image order reversed) | |
| 10 | Why SEWOLAH | `components.public.why-section` | 5 kelebihan |
| 11 | How It Works | `components.public.how-it-works` | 5 langkah bernombor |
| 12 | **Booking Form (3 langkah)** | `Livewire\Public\BookingForm` | Lihat Seksyen 7 (spesifikasi penuh) |
| 13 | Final CTA | `components.public.final-cta` | |
| 14 | Footer | `components.public.footer` | Nav, strategic partner, copyright, **language switcher diletak di sini/header** |
| 15 | Floating WhatsApp Button | `components.public.whatsapp-float` | Nombor WhatsApp dari `page_settings` (bukan localStorage), posisi naik bila sticky mobile CTA aktif |
| 16 | Sticky Mobile CTA | `components.public.mobile-sticky-cta` | Hanya papar di mobile (`max-width:820px`), animasi pulse, scroll smooth ke `#booking-form` |

**Warna & Font (kekal sama — brand guideline):**
- Hitam `#000000`, Putih `#FFFFFF`, Merah SEWOLAH `#E31E24`, Dark Grey `#161616`, Light Grey `#F5F5F5`.
- Font: **Plus Jakarta Sans** (heading) — guna Google Fonts sama seperti asal, atau self-host untuk performance (lihat Seksyen 9).
- Tiada gradient (kecuali Final CTA section yang memang guna `linear-gradient(180deg,#141414,#000)` dalam asal — kekalkan).

---

## 7. SPESIFIKASI BORANG TEMPAHAN (Livewire Component `BookingForm`)

### 7.1 Langkah (Step) & Medan
**Step 1 — Travel Details**
- Datang Dari Negeri/Bandar (text, required)
- Arrival Airport (select: KLIA / KLIA2 / Subang Airport / Other, required)
- Travel Purpose (select: Family Trip / Business Trip / Corporate / Holiday / Event / Other, required)
- Arrival Date (date, required)
- Arrival Time (time, required)
- Rental End Date (date, required, **mesti ≥ Arrival Date — validation baharu**)

**Step 2 — Vehicle Preference**
- Preferred Vehicle (select — data dinamik dari table `vehicles` + opsyen "Other Vehicle" & "Not Sure — Recommend For Me", required)
- Model/Jenis Kereta Lain (text, required **hanya jika** "Other Vehicle" dipilih)
- Number of Passengers (number, min 1, required)
- Estimated Luggage (select: Light/Medium/Heavy/Not Sure, required)
- Main Destination/Area (text, required)

**Step 3 — Contact Details**
- Full Name (text, required)
- Mobile/WhatsApp Number (tel, required, **validation format MY phone baharu**: `regex:/^(01)[0-9]{8,9}$/` atau terima format `+60`)
- Additional Notes (textarea, optional)
- Consent checkbox (required, wajib true)

### 7.2 UX
- Progress indicator "LANGKAH X/3" + dots (guna Livewire state, bukan reload).
- Butang "SETERUSNYA"/"KEMBALI" disable sehingga field wajib lengkap (guna Livewire real-time validation `wire:model.live`).
- Selepas submit (Step 3): papar success state ("Terima Kasih!") **dan** Livewire dispatch browser event untuk buka WhatsApp (`window.open`) — logik sama seperti asal.

### 7.3 Backend Flow bila Submit
1. Validate semua field (Form Request atau Livewire rules).
2. Simpan rekod ke table `leads` (status = `baru`), termasuk UTM params yang di-capture dari query string semasa page load (simpan dalam session, hantar sekali bersama form).
3. Hantar **email notification** ke `page_settings.admin_notification_email` (queued job — `NewLeadNotification` Mailable).
4. (Opsyenal, jika ada integrasi) hantar **WhatsApp Business API** notification ke admin — jika tiada API rasmi, papar sahaja simulasi seperti asal dan sediakan hook untuk future integration.
5. Trigger event `LeadSubmitted` → listener untuk tracking (Meta Pixel `Lead` event via client-side script tag, guna `pixel_settings`).
6. Redirect/pra-isi mesej WhatsApp dan buka tab baharu ke `https://wa.me/{number}?text={encoded_message}` — **mesej WhatsApp mesti dijana mengikut locale** (BM/EN) pengguna semasa isi borang.
7. Simpan `whatsapp_opened_at` bila event klik/redirect berlaku (guna Livewire `dispatch` + JS listener untuk update via lightweight endpoint/beacon).

### 7.4 Mesej WhatsApp (Template Bilingual)
**BM (asal, kekal):**
```
Hi SEWOLAH, saya telah isi borang tempahan untuk trip saya ke KL.
Nama: {{name}}
Datang Dari: {{origin}}
Airport: {{airport}}
Tarikh/Masa Ketibaan: {{arrival_date_time}}
Tarikh Tamat Sewa: {{end_date}}
Tujuan: {{travel_purpose}}
Kenderaan Pilihan: {{vehicle}}
Model Lain: {{other_vehicle}}
Jumlah Penumpang: {{passengers}}
Luggage: {{luggage}}
Lokasi Utama: {{destination}}
Catatan: {{notes}}

Boleh team SEWOLAH bantu semak availability dan berikan info lanjut untuk tempahan saya?
```
**EN (baharu — untuk locale `en`):**
```
Hi SEWOLAH, I have submitted a booking enquiry for my trip to KL.
Name: {{name}}
Coming From: {{origin}}
Airport: {{airport}}
Arrival Date/Time: {{arrival_date_time}}
Rental End Date: {{end_date}}
Purpose: {{travel_purpose}}
Preferred Vehicle: {{vehicle}}
Other Model: {{other_vehicle}}
Passengers: {{passengers}}
Luggage: {{luggage}}
Main Destination: {{destination}}
Notes: {{notes}}

Could the SEWOLAH team help check availability and share more info for my booking?
```

---

## 8. MODUL B — ADMIN PANEL (`/admin`)

### 8.1 Authentication
- **Ganti** login demo hardcoded (`admin` / `sewolah2026`) dengan **Laravel Auth sebenar** (Breeze/Fortify, guard `admin`, table `users`/`admins` dengan password hashed `bcrypt`).
- Route `/admin` dilindungi middleware `auth:admin`.
- Sokongan "Remember Me", "Forgot Password" (email reset — WAJIB kerana ini production system, bukan demo).
- (Cadangan) 2FA opsyenal guna Fortify untuk super admin.

### 8.2 Dashboard (`Livewire\Admin\Dashboard`)
Replikasi 100% UI asal + data sebenar dari DB:
- **Stat cards (4):** Jumlah Leads, Leads Tempoh Ini, WhatsApp Opens, Booking Disahkan — kira dari table `leads` sebenar (bukan `localStorage`).
- **Filter tempoh:** Mingguan / Bulanan / Custom date range (guna Livewire computed property, query DB terus).
- **Trend Leads chart:** bar chart (boleh kekal custom CSS bar seperti asal, atau upgrade ke Chart.js/ApexCharts untuk polish — cadangan: **Chart.js** untuk interaktiviti lebih baik, kekalkan warna jenama `#E31E24`).
- **Kenderaan Diminati:** breakdown peratusan ikut `vehicle_id` dari leads dalam tempoh dipilih.
- **Conversion Funnel:** Form Start → Borang Siap → WhatsApp Dibuka → Booking Disahkan. **Adjustment:** Form Start sepatutnya dikira sebenar (event tracking bila user mula isi Step 1), bukan anggaran `leadsCount * 1.4` seperti asal.
- **Notifikasi Terkini:** 3 lead terbaru.

### 8.3 Tempahan & Pelanggan (`Livewire\Admin\BookingsTable`)
- Jadual leads (Nama, Telefon, Kenderaan, Tarikh Ketibaan, Status, Aksi) dengan **pagination** (asal tiada — WAJIB tambah untuk skala production).
- **Search & filter** (ikut nama/telefon/status/tarikh/kenderaan) — tiada dalam asal, WAJIB ditambah.
- Detail pelanggan (klik "Lihat" → papar semua field).
- **Update status** lead terus dari panel (baru → dihubungi → quotation dihantar → disahkan / batal) — asal tiada UI untuk update status (status statik dari data), WAJIB ditambah sebagai keperluan operasi harian.
- **Export CSV/Excel** senarai leads — tiada dalam asal, WAJIB ditambah (keperluan biasa untuk sales team).

### 8.4 Tetapan Pixel & Tracking (`Livewire\Admin\PixelSettings`)
- Input Meta Pixel ID, Google Ads Conversion ID, TikTok Pixel ID.
- Simpan ke table `pixel_settings` (bukan `localStorage`).
- Pixel ID ini di-inject secara dinamik ke `<head>` landing page (guna Blade `@if(config/db value)`).

### 8.5 Edit Landing Page (`Livewire\Admin\PageSettings`)
- Nombor WhatsApp Admin, Email Admin notifikasi — simpan ke `page_settings`.
- **Pengurusan Kenderaan (baharu, tiada dalam asal):** CRUD untuk table `vehicles` (tambah/edit/padam kereta, upload gambar, tetapkan "featured", susun `sort_order`) — kerana asal hardcode 3 kereta dalam JS, ini tidak scalable untuk production.
- (Opsyenal) Pengurusan teks landing page lain (headline, copy) via simple key-value content table jika mahu admin edit tanpa deploy kod semula — boleh jadi Phase 2.

### 8.6 Role & Akses
- `super_admin`: akses penuh (termasuk tetapan pixel & user management).
- `admin`: akses dashboard & bookings sahaja (tiada akses tetapan).

---

## 9. DWIBAHASA (BAHASA MALAYSIA + ENGLISH)

- **Default locale:** `ms` (Bahasa Malaysia — sepadan dengan copy asal).
- **Locale kedua:** `en` (English — perlu terjemahan penuh semua copy dari PRD asal).
- Language switcher (BM/EN toggle) di header (desktop) & menu mobile — guna `session('locale')` + middleware `SetLocale`.
- Semua string UI, label borang, mesej validation, mesej WhatsApp, email notification, SEO meta tags — mesti ada versi `lang/ms/*.php` dan `lang/en/*.php`.
- URL structure: guna session-based locale switching (bukan prefix `/en/...`) supaya SEO tetap fokus pada 1 canonical domain untuk fasa 1 — **atau** jika SEO multi-bahasa diperlukan, guna prefix route (`/en`) + `hreflang` tags (cadangkan Phase 2 jika required).
- Field data (nama kenderaan, kategori) — simpan bilingual dalam `vehicles` table (cth. `name`, `category` boleh kekal Inggeris/neutral seperti asal, tapi `tags`/"suitable for" perlu versi JSON dwibahasa: `{"ms": [...], "en": [...]}`).

---

## 10. ADJUSTMENTS / PERKARA YANG PERLU DITAMBAH BAIK DARI VERSI ASAL

Senarai ini menjawab keperluan #5 ("tolong adjust mana yang berkurang") — perkara dalam prototaip asal yang **tidak production-ready** dan mesti diperbetulkan dalam rebuild:

1. **Tiada backend sebenar** — semua data (`leads`, `settings`, `pixels`) disimpan dalam `localStorage` pelayar, hilang bila clear cache / beza device. → Gantikan sepenuhnya dengan MySQL.
2. **Login admin hardcoded** (`admin`/`sewolah2026`, credential dalam kod sumber) — risiko keselamatan besar. → Laravel Auth sebenar + hashed password + forgot-password flow.
3. **Tiada mobile navigation menu** — header asal hanya `flex-wrap` nav link, tiada hamburger untuk skrin sangat kecil. → Tambah hamburger/drawer menu mobile.
4. **Tiada pagination/search pada jadual leads** — tidak scalable bila leads banyak. → Tambah pagination, search, filter, sort.
5. **Tiada update status lead** dari UI (status kekal statik) — team operasi tidak boleh urus lifecycle lead. → Tambah dropdown/update status + activity log.
6. **Kenderaan hardcoded dalam JS** — tambah/tukar kereta perlukan edit kod. → Table `vehicles` + CRUD admin.
7. **Tiada validation format nombor telefon** yang ketat. → Tambah regex validation nombor Malaysia.
8. **Tiada capture UTM/fbclid sebenar** — PRD asal sebut keperluan ini (Seksyen 17 PRD asal) tetapi tiada dalam kod `.dc.html`. → Implement capture UTM dari query string on landing + attach ke lead record.
9. **Meta Pixel events tidak diimplementasi sebenar** dalam kod (hanya UI untuk simpan Pixel ID, tiada event firing PageView/ViewContent/FormStart/Lead/Contact). → Implement client-side pixel snippet + server-side event trigger (Conversions API disyorkan untuk ketepatan).
10. **Tiada email/notifikasi sebenar** — dashboard sebut "simulasi" sahaja. → Implement email queue sebenar (dan sediakan hook WhatsApp Business API/Twilio jika required kemudian).
11. **Tiada export data leads.** → Tambah export CSV/Excel.
12. **Tiada image optimization / lazy load sebenar** (PRD asal minta WebP/AVIF, lazy-load) — kod `.dc.html` guna `<img>` biasa tanpa `loading="lazy"`. → Implement `loading="lazy"`, konversi asset ke WebP, guna Laravel image optimization (Intervention Image / Spatie Media Library).
13. **Tiada SEO meta tag sebenar** dalam kod (title/description ada dalam PRD asal tapi tiada dalam HTML). → Tambah `<title>`, meta description, Open Graph, Twitter Card, `sitemap.xml`, `robots.txt`, JSON-LD (LocalBusiness/Organization schema).
14. **Google Fonts di-load terus dari CDN** (`fonts.googleapis.com`) — boleh jejaskan Lighthouse score & privasi (GDPR-ish). → Self-host font via Laravel Vite/`fontsource`.
15. **Tiada rate-limiting / anti-spam pada borang** (bot boleh spam submission). → Tambah honeypot field + Laravel throttle middleware + (opsyenal) Google reCAPTCHA v3.
16. **Tiada bahasa Inggeris langsung** — 100% BM. → Full dwibahasa seperti Seksyen 9.
17. **Tiada audit/role admin** — hanya 1 login generik. → Role `super_admin`/`admin` + activity log siapa update status/tetapan.
18. **Tiada CSRF protection sebenar** (kerana ia prototaip statik) — Laravel handle ini automatik via Blade `@csrf` + Livewire built-in.
19. **Tiada backup/monitoring** untuk production. → Sediakan backup DB berjadual (Forge scheduled backup / `spatie/laravel-backup`) dan error monitoring (Sentry/Flare — opsyenal).

---

## 11. MOBILE RESPONSIVENESS (WAJIB 100%)

- Mobile-first Tailwind breakpoints: `sm(640) md(768) lg(1024) xl(1280)`.
- Semua grid `repeat(auto-fit,minmax(...))` asal → Tailwind `grid-cols-1 md:grid-cols-2 lg:grid-cols-3/4` setara.
- Sticky mobile CTA (asal `max-width:820px`) → Tailwind `md:hidden` + animasi pulse (Tailwind `animate-pulse` custom / keyframe `sw-pulse` dikekalkan).
- Floating WhatsApp button naik posisi bila sticky CTA aktif (kekalkan logik asal, translate ke Alpine.js/Livewire computed state).
- Borang tempahan: semua field full-width di mobile, touch target minimum 44×44px (button min-height 48px seperti PRD asal Seksyen 19).
- Admin panel: sidebar 220px pada desktop → collapse jadi drawer/bottom-nav pada mobile (asal admin panel **tiada** responsive handling untuk sidebar — ini **adjustment WAJIB**, kerana `grid-template-columns:220px minmax(0,1fr)` akan pecah di skrin kecil).
- Table leads (admin) → responsive scroll horizontal atau card-view di mobile.
- Test breakpoint: 360px (small Android), 390px (iPhone), 768px (tablet), 1024px+ (desktop).

---

## 12. SEO

- Page title: *"SEWOLAH | Kereta Sewa Kuala Lumpur & Selangor Untuk Family & Business Traveller"* (BM) / versi EN setara.
- Meta description bilingual (rujuk PRD asal Seksyen 18).
- Open Graph image (guna `hero-full.jpg` / `Facebook Cover SEWOLAH.png` yang sedia ada dalam folder assets).
- `sitemap.xml`, `robots.txt`.
- Semantic HTML (`<h1>` sekali sahaja, hierarki heading betul — semak semula struktur `.dc.html` asal yang guna banyak `<h2>` tanpa `<h1>` eksplisit pada hero — **WAJIB perbetulkan** `<h1>` mesti pada hero headline).
- Schema.org `LocalBusiness`/`AutomotiveBusiness` JSON-LD.

---

## 13. PERFORMANCE (Target Lighthouse 90+, sama seperti PRD asal)

- Compress & convert semua imej assets (`hero-full.jpg` 3.1MB, `business-travel.jpg` 2MB, dll — saiz asal terlalu besar) ke WebP, resize mengikut keperluan display.
- Lazy-load imej below-the-fold.
- Vite asset bundling + minification, code-splitting Livewire.
- Guna Laravel caching (`config:cache`, `route:cache`, `view:cache` di production Forge).
- CDN opsyenal untuk static assets.

---

## 14. TRACKING / ANALYTICS

- Meta Pixel: `PageView`, `ViewContent`, `FormStart`, `Lead` (hanya lepas submit berjaya + simpan DB), `Contact` (bila WhatsApp dibuka).
- Google Ads Conversion, TikTok Pixel — sama struktur (ID disimpan di `pixel_settings`, snippet dijana dinamik).
- UTM capture: `utm_source, utm_medium, utm_campaign, utm_content, utm_term, fbclid, referrer, landing_page_url, timestamp` — simpan bersama setiap lead.
- (Opsyenal Phase 2) Meta Conversions API (server-side) untuk ketepatan tracking selepas iOS14 changes.

---

## 15. DEPLOYMENT (GITHUB + LARAVEL FORGE)

### 15.1 Git / GitHub
- Repo GitHub persendirian, branch strategy: `main` (production), `develop` (staging), feature branches `feature/xxx`.
- `.gitignore` standard Laravel (`vendor/`, `node_modules/`, `.env`, `storage/*.key`).
- `.env.example` lengkap dengan semua env var yang diperlukan (DB, MAIL, PIXEL defaults, APP_LOCALE=ms).
- GitHub Actions (opsyenal tapi disyorkan): CI pipeline run `pint` (code style), `phpstan`/`larastan` (opsyenal), Pest/PHPUnit test, on push ke `main`/`develop`.

### 15.2 Laravel Forge
- Server: PHP 8.4, MySQL 8.4, Nginx.
- Site di-deploy dari GitHub repo (auto-deploy on push ke `main`).
- Deploy script Forge standard:
  ```
  cd /home/forge/sewolah.com
  git pull origin main
  composer install --no-dev --optimize-autoloader
  php artisan migrate --force
  npm ci && npm run build
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan queue:restart
  ```
- SSL: Let's Encrypt (auto-renew via Forge).
- Queue worker: Forge daemon untuk `php artisan queue:work` (proses email/notification).
- Scheduler: Forge cron `* * * * * php artisan schedule:run` (untuk backup, laporan berkala jika ada).
- Environment terpisah: `staging.sewolah.com` (opsyenal) untuk testing sebelum push ke production.

---

## 16. NON-FUNCTIONAL REQUIREMENTS

- **Security:** HTTPS wajib, CSRF protection (Laravel default), rate limiting borang (`throttle:6,1`), sanitize semua input, password hashing (bcrypt/argon2), honeypot anti-spam.
- **Accessibility:** label pada semua input, kontras warna cukup (semak merah `#E31E24` di atas putih — kontras OK; teks putih atas hitam — OK), `alt` text semua imej (sudah ada dalam asal, kekalkan & lengkapkan).
- **Browser support:** Chrome, Safari, Edge, Firefox (2 versi terkini), iOS Safari & Android Chrome (majoriti trafik mobile).
- **Data retention:** leads disimpan minimum 2 tahun (semak keperluan PDPA Malaysia — consent checkbox sedia ada dalam borang, pastikan Privacy Policy page sebenar dicipta, bukan link `#` kosong seperti asal).

---

## 17. SUCCESS METRICS (kekal dari PRD asal, sekarang boleh diukur sebenar via DB)

- Landing Page Views, Form Starts, Completed Forms
- Landing Page → Lead Conversion Rate
- WhatsApp Opens, Qualified Leads, Confirmed Bookings
- Cost Per Lead, Cost Per Confirmed Booking, Revenue, ROAS
- Semua metrik ini kini boleh dijana terus dari dashboard admin (Seksyen 8.2) menggunakan data sebenar dari MySQL, bukan anggaran.

---

## 18. SENARAI FAIL RUJUKAN ASAL (SUMBER KEBENARAN UI/COPY)

| Fail | Kegunaan |
|---|---|
| `SEWOLAH Landing Page.dc.html` | Sumber 100% struktur, copy & style landing page (BM) |
| `SEWOLAH Admin Panel.dc.html` | Sumber 100% struktur & fungsi admin panel |
| `support.js` | Runtime helper untuk format `.dc.html` asal — **tidak perlu dibawa** ke Laravel (digantikan Livewire) |
| `scraps/prd_text.txt` / `uploads/PRD_Landing_Page_SEWOLAH.docx` | PRD asal (rujukan copy & requirement asal) |
| `assets/*` , `uploads/*` | Imej sumber (logo, hero, kereta, family/business travel) — guna sebagai base untuk optimasi WebP |

---

## 19. OUT OF SCOPE (Phase 1)

- Payment gateway / online payment (sistem ini enquiry-only, bukan instant booking — sama seperti PRD asal Seksyen 12).
- WhatsApp Business API automation penuh (chatbot) — Phase 1 hanya `wa.me` deep link.
- Multi-tenant / multi-cabang.
- Native mobile app.

---

*Tamat dokumen PRD. Rujuk `CLAUDE_CODE_BUILD_PROMPT.md` untuk arahan pembinaan langkah demi langkah.*
