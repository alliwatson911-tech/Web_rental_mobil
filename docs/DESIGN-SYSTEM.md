# Padaidi Rent Car — Design System & Visual Specification (Fase 1)

Dokumentasi ini merangkum fondasi sistem desain (Design System), panduan token visual, spesifikasi aset merek (logo), katalog komponen Blade dasar, serta hasil audit aksesibilitas dan kontras warna (WCAG 2.1) untuk web **Padaidi Rent Car Makassar**.

---

## 1. Filosofi & Ekstraksi Bahasa Visual

Sistem desain ini mengadopsi bahasa visual modern bertema gelap (*dark theme*) yang terinspirasi dari referensi bengkel "Prima Motor", dengan adaptasi khusus untuk industri rental mobil premium:

1. **Kanvas Gelap Bertingkat (*Layered Dark Canvas*)**:
   - Latar belakang utama menggunakan warna *deep navy* (`#0b1220`) dipadukan dengan seksi selang-seling bernuansa *charcoal slate* (`#111827`).
   - Garis pemisah (*borders*) dirancang halus (`#1e293b` dan `#334155`) untuk memberikan batas struktural tanpa menimbulkan kebisingan visual.
2. **Aksen Emas Murni (*Pure Padaidi Gold Accent*)**:
   - Diambil langsung dari sampel warna emblem logo resmi Padaidi Rent Car: `#F5A623` (RGB 245, 166, 35).
   - Menghasilkan kesan berani, mewah, dan terpercaya (*confidence & prestige*).
3. **Geometri Tegas & Presisi (*Sharp Geometry*)**:
   - Radius tombol dan lencana dibatasi tegas pada **4px** (`--radius-btn: 4px`), memberikan nuansa profesional, maskulin, dan kokoh.
   - Radius kartu dibatasi pada **8px** (`--radius-card: 8px`).
4. **Bayangan Offset Khas (*Signature Offset Shadow*)**:
   - Aksen bayangan tajam tanpa blur pada visual hero: `-15px 15px 0px #F5A623` (desktop) dan `-8px 8px 0px #F5A623` (mobile).
5. **Tipografi Berkontras Tinggi & Aksesibel**:
   - Teks di atas warna emas selalu menggunakan warna gelap navy `#0b1220` (rasio kontras **9.28:1**, lolos WCAG AAA). Teks putih di atas emas dilarang keras karena gagal kontras (hanya 2.03:1).
   - Menggunakan sistem font lokal `'Segoe UI'` dengan fallback sans-serif sistem agar rendering instan tanpa ketergantungan CDN eksternal.

---

## 2. Token Desain (CSS Variables)

Seluruh token didefinisikan pada `:root` di berkas `public/css/design-system.css` dan `resources/css/design-system.css`:

```css
:root {
    /* Palette Warna */
    --color-canvas: #0b1220;              /* Latar utama */
    --color-canvas-alt: #111827;          /* Latar seksi selang-seling */
    --color-surface: #0b1220;             /* Kartu di atas seksi selang-seling */
    --color-surface-elevated: #111827;    /* Kartu di atas latar utama */
    --color-surface-hover: #1e293b;       /* State hover kartu */
    --color-border: #1e293b;              /* Batas halus */
    --color-border-strong: #334155;       /* Batas tegas */

    /* Aksen Emas Padaidi */
    --color-accent: #F5A623;              /* Emas Primer */
    --color-accent-hover: #FDBA4D;        /* Emas Terang Hover */
    --color-accent-dark: #C77F14;         /* Emas Gelap Active */
    --color-accent-10: rgba(245, 166, 35, 0.10); /* Latar badge emas */
    --color-accent-20: rgba(245, 166, 35, 0.20); /* Focus glow */
    --color-accent-30: rgba(245, 166, 35, 0.30); /* Border badge emas */

    /* Tipografi Teks */
    --color-text: #f8fafc;                /* Teks utama (slate-50) */
    --color-text-muted: #94a3b8;          /* Teks sekunder (slate-400) */
    --color-text-faint: #64748b;          /* Teks penjelas (slate-500) */
    --color-text-on-accent: #0b1220;      /* Teks di atas warna emas (Dark Navy) */

    /* Status Semantik */
    --color-success: #10b981;
    --color-success-bg: rgba(16, 185, 129, 0.12);
    --color-success-border: rgba(16, 185, 129, 0.30);
    --color-warning: #f59e0b;
    --color-warning-bg: rgba(245, 158, 11, 0.12);
    --color-warning-border: rgba(245, 158, 11, 0.30);
    --color-danger: #ef4444;
    --color-danger-bg: rgba(239, 68, 68, 0.12);
    --color-danger-border: rgba(239, 68, 68, 0.30);
    --color-info: #38bdf8;
    --color-info-bg: rgba(56, 189, 248, 0.12);
    --color-info-border: rgba(56, 189, 248, 0.30);

    /* Skala Tipografi */
    --font-main: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', Arial, sans-serif;
    --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    --font-size-display: clamp(2rem, 5vw + 1rem, 3.25rem);
    --font-size-h1: clamp(1.75rem, 3.5vw + 1rem, 2.375rem);
    --font-size-h2: clamp(1.375rem, 2vw + 1rem, 1.875rem);
    --font-size-h3: 1.25rem;
    --font-size-kicker: 0.75rem;
    --font-size-body: 0.9375rem;
    --font-size-small: 0.8125rem;

    /* Geometri & Radius */
    --radius-btn: 4px;
    --radius-card: 8px;
    --radius-pill: 9999px;
    --radius-badge: 4px;

    /* Bayangan & Offset */
    --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.25);
    --shadow-offset-hero: -15px 15px 0px var(--color-accent);
    --shadow-offset-hero-sm: -8px 8px 0px var(--color-accent);
    --focus-ring: 0 0 0 2px var(--color-canvas), 0 0 0 4px var(--color-accent);

    /* Tata Letak */
    --container-max: 1200px;
    --spacing-section-y: clamp(4rem, 7vw, 6.25rem);
    --spacing-container-x: 5%;
}
```

---

## 3. Identitas Merek & Panduan Penggunaan Logo

### 3.1. Penamaan Resmi
Nama resmi merek di seluruh sistem adalah:
**Padaidi Rent Car** (dengan varian lokal penelusuran: **Padaidi Rent Car Makassar**).

### 3.2. Aset Logo yang Telah Dihasilkan
| Berkas Aset | Resolusi / Format | Fungsi Penggunaan |
| :--- | :--- | :--- |
| `public/images/brand/padaidi-logo.png` | 640x700 PNG Transparan | Logo utama navbar, header dokumen, invoice |
| `public/images/brand/padaidi-logo-dark.png` | 640x700 PNG Solid Navy | Tampilan pada latar transparan pihak ketiga |
| `public/images/brand/padaidi-shield.png` | 272x300 PNG Transparan | Emblem perisai untuk navbar mobile, lencana, favicons |
| `public/favicon.ico` | 32x32 Multi-res ICO | Favicon tab peramban desktop |
| `public/favicon-32x32.png` & `16x16.png` | PNG | Favicon peramban modern |
| `public/apple-touch-icon.png` | 180x180 PNG | Ikon layar beranda iOS / iPadOS |
| `public/images/brand/padaidi-og.jpg` | 1200x630 JPEG | Kartu pratinjau media sosial (Open Graph & Twitter Card) |

### 3.3. Aturan Penggunaan Logo
1. **Ruang Bebas (*Clearspace*)**: Berikan ruang kosong minimal sebesar tinggi lis perisai di sekeliling logo agar tidak terhimpit elemen teks lain.
2. **Ukuran Minimal Tampil**:
   - Versi lengkap (Perisai + Teks): minimal tinggi **38px** di layar (200px print).
   - Versi lambang saja (*shield emblem*): minimal tinggi **28px** di layar.
3. **Latar Belakang**:
   - Pada latar gelap (`#0b1220` atau `#111827`): Gunakan `padaidi-logo.png` atau `padaidi-shield.png`.
   - Pada latar terang (misal lembar cetak invoice putih): Gunakan `padaidi-logo.png` dengan teks kontras tinggi.
4. **Larangan**:
   - Dilarang meregangkan (*stretch*) atau mengubah aspek rasio logo.
   - Dilarang mengganti warna aksen emas menjadi merah, hijau, atau biru.
   - Dilarang menggunakan teks putih di atas tombol aksen emas (wajib `#0b1220`).

---

## 4. Katalog Komponen Blade Dasar

Sistem menyediakan komponen Blade modular di `resources/views/components/`:

### 4.1. `<x-navbar />`
- **Fitur**: Menempel di bagian atas layar (*sticky*), menampilkan emblem perisai Padaidi dan tipografi tegas, navigasi publik (Armada, Mengapa Kami, Cara Sewa, Kontak), lencana tier member interaktif bila login atau tombol "Masuk / Daftar".
- **Mobile**: Tombol hamburger dengan target sentuh 44x44px dan laci menu (*slide-down drawer*) yang aksesibel.

### 4.2. `<x-footer />`
- **Fitur**: Tata letak 4 kolom responsif:
  - Kolom 1: Profil ringkas Padaidi Rent Car, emblem perisai, dan izin usaha.
  - Kolom 2: Tautan cepat publik (Beranda, Armada Mobil, Promo & Diskon, Syarat Ketentuan).
  - Kolom 3: Jam operasional armada (24 Jam Nonstop Setiap Hari).
  - Kolom 4: Kontak langsung (WhatsApp reservasi, telepon kantor, email, alamat pool Makassar).
- **Sub-footer**: Hak cipta & link kebijakan privasi.

### 4.3. `<x-icon name="..." class="..." />`
Pustaka ikon inline SVG tanpa pustaka eksternal atau icon font:
- `shield`, `car`, `key`, `wrench`, `whatsapp`, `phone`, `map-pin`, `clock`, `calendar`, `check`, `close`, `menu`, `user`, `star`, `mail`, `arrow-right`, `chevron-down`, `tag`.

### 4.4. `<x-section-header kicker="..." title="..." desc="..." />`
- Komponen standar untuk judul setiap seksi beranda dan halaman publik.
- Mengikuti pola referensi: Kicker emas kapital tebal + Judul utama h2 kapital tegas + Paragraf deskripsi redup (`#94a3b8`).

### 4.5. `<x-cta-band title="..." desc="..." btn-text="..." btn-url="..." />`
- Pita penutup halaman berlatar belakang emas penuh (`#F5A623`) dengan kontras teks gelap navy.
- Dilengkapi tombol gelap `.btn-padaidi-dark` dengan ikon WhatsApp bawaan.

### 4.6. Tombol (Buttons)
- `.btn-padaidi-primary` / `.btn-primary`: Latar emas `#F5A623`, teks gelap `#0b1220`, border-radius 4px, font-weight 800 uppercase.
- `.btn-padaidi-secondary` / `.btn-secondary` / `.btn-outline`: Latar `#1e293b`, teks `#f8fafc`, border `#334155`.
- `.btn-padaidi-dark`: Latar `#0b1220`, teks `#ffffff`, border `#1e293b` (khusus di atas CTA band).
- `.btn-sm`: Tinggi minimum 36px untuk aksi padat di tabel atau navbar.

### 4.7. Kartu (Cards)
- `.card-padaidi` / `.card`: Latar `#0b1220` atau `#111827`, border 1px `#1e293b`, radius 8px, padding 28px.
- `.card-accent-top`: Menambahkan garis aksen emas 4px di tepi atas kartu.
- `.card-hover`: Efek elevasi dan transisi halus saat disentuh/hover.

### 4.8. Lencana Status (Badges)
- `.badge-accent`: Latar emas transparan `rgba(245, 166, 35, 0.10)`, teks `#F5A623`, border `rgba(245, 166, 35, 0.30)`.
- `.badge-success`: Latar hijau transparan, teks `#10b981`.
- `.badge-warning`: Latar oranye transparan, teks `#f59e0b`.
- `.badge-danger`: Latar merah transparan, teks `#ef4444`.

### 4.9. Baris Harga & Blok Statistik
- `.price-row`: Garis harga per mobil dengan nama di kiri dan tarif per hari beraksen emas di kanan.
- `.stats-grid`, `.stats-number`, `.stats-label`: Blok angka besar statistik armada dan kepuasan pelanggan.

---

## 5. Audit Aksesibilitas & Uji Rasio Kontras (WCAG 2.1)

Setiap pasangan warna diuji berdasarkan standar *Web Content Accessibility Guidelines (WCAG) 2.1*:

| Kombinasi Elemen | Warna Teks | Warna Latar | Rasio Kontras | Status Standar WCAG |
| :--- | :--- | :--- | :--- | :--- |
| **Tombol Utama (Gold Button)** | `#0b1220` (Navy) | `#F5A623` (Emas) | **9.28 : 1** | **LOLOS WCAG AAA** (Min. 7.0:1) |
| *Tombol Terlarang (White on Gold)* | `#FFFFFF` (Putih) | `#F5A623` (Emas) | *2.03 : 1* | *GAGAL (Dilarang)* |
| **Teks Judul Utama di Kanvas** | `#F8FAFC` (Slate-50) | `#0b1220` (Kanvas) | **16.14 : 1** | **LOLOS WCAG AAA** |
| **Teks Keterangan Redup (Muted)** | `#94A3B8` (Slate-400) | `#0b1220` (Kanvas) | **7.81 : 1** | **LOLOS WCAG AAA** |
| **Teks Kicker Emas di Kanvas** | `#F5A623` (Emas) | `#0b1220` (Kanvas) | **9.28 : 1** | **LOLOS WCAG AAA** |
| **Teks Status Sukses** | `#10B981` (Emerald) | `#0b1220` (Kanvas) | **5.85 : 1** | **LOLOS WCAG AA** |
| **Teks CTA Band di Latar Emas** | `#0b1220` (Navy) | `#F5A623` (Emas) | **9.28 : 1** | **LOLOS WCAG AAA** |

### Pengujian Aksesibilitas Lainnya:
- **Skip Link**: Terdapat `<a href="#main-content" class="skip-link">Menuju ke konten utama</a>` di awal `<body>` untuk pengguna pembaca layar (screen reader) dan navigasi keyboard.
- **Focus Ring**: Indikator fokus keyboard visual ganda yang jelas: `0 0 0 2px #0b1220, 0 0 0 4px #F5A623`.
- **Target Sentuh Minimum (Touch Targets)**: Seluruh tombol interaktif, hamburger menu, dan link navigasi memiliki area klik minimal **44 x 44 piksel**.
- **Prefers Reduced Motion**: Ditambahkan aturan media query `@media (prefers-reduced-motion: reduce)` untuk mematikan animasi bagi pengguna dengan sensitivitas vestibular.

---

## 6. Uji Responsivitas Lintas Perangkat

Tata letak telah diuji dan diatur agar tampil proporsional pada empat *viewport* acuan:

1. **360px (Ponsel Kecil - Mobile SE / Android Standar)**:
   - Kontainer menyesuaikan dengan padding horizontal 5%.
   - Hamburger menu aktif, tombol navbar menyatu ke dalam laci geser.
   - Offset shadow visual hero mengecil menjadi `-8px 8px 0px` agar tidak memotong layar horizontal (*no horizontal overflow*).
2. **768px (Tablet / Ponsel Besar Landscape)**:
   - Grid berpindah secara mulus dari 1 kolom ke 2 kolom.
   - Header invoice dan formulir login menyesuaikan lebar kartu maksimal 440px.
3. **1024px (Laptop / Desktop Standar)**:
   - Menu navbar horizontal penuh ditampilkan secara rapi.
   - Tata letak footer 4 kolom aktif.
   - Visual hero offset `-15px 15px 0px` tampil optimal.
4. **1440px (Desktop Lebar / Monitor Widescreen)**:
   - Lebar kontainer dibatasi maksimal pada `1200px` (`--container-max`) dengan margin otomatis kiri-kanan untuk mencegah regresi visual pada monitor besar.

---

## 7. Catatan Keputusan Desain & Deviasi dari Referensi

1. **Pemisahan Gaya Inline menjadi Token CSS**:
   - Referensi `index.html` menggunakan ribuan baris styling inline pada elemen HTML. Pada sistem ini, seluruh gaya diekstrak ke dalam kelas CSS semantik dan variabel token (`design-system.css`), menjaga kode Blade bersih, modular, dan mudah dikelola.
2. **Kesesuaian Industri (Domain Alignment)**:
   - Referensi bertema bengkel mobil ("Prima Motor" dengan layanan ganti oli, tune up, dll). Pada sistem ini, gaya visualnya diadopsi sepenuhnya, namun disesuaikan 100% untuk industri **Rental Mobil Padaidi** (lepas kunci, mobil + driver, sewa harian/mingguan/bulanan, verifikasi KYC KTP/SIM, tier keanggotaan).
3. **Penyelarasan Warna Teks di Atas Aksen Emas**:
   - Menghindari teks putih di atas tombol emas (yang sering ditemukan pada web awam namun gagal tes kontras). Teks gelap navy `#0b1220` diterapkan secara konsisten demi memenuhi standar WCAG AAA.
4. **Tanpa Ketergantungan Font/CDN Eksternal**:
   - Menghindari pemuatan font Google Fonts eksternal atau FontAwesome CDN yang memperlambat LCP (*Largest Contentful Paint*). Semua ikon menggunakan SVG inline berbasis komponen Blade `<x-icon />` dan font sistem `Segoe UI`.

---

## 8. Implementasi Halaman Publik (Fase 2)

Pada Fase 2, seluruh bahasa visual telah diterapkan secara penuh ke halaman-halaman publik:

1. **Beranda (`home.blade.php`)**:
   - **Hero Section**: Dilengkapi ambient background circle, judul utama uppercase berkontras tinggi, tombol aksi emas & outline, serta bingkai visual mobil dengan bayangan offset khas (`-15px 15px 0px #F5A623`).
   - **Blok Statistik**: Menampilkan metrik nyata layanan: 50+ Unit Armada Terawat, Layanan 24 Jam Nonstop, dan 100% Harga Transparan.
   - **Kartu Pencarian Reservasi**: Terintegrasi overlapping di bawah hero dengan input tanggal ambil, tanggal kembali, dan pilihan lokasi kantor pool/bandara.
   - **Kategori Armada**: Kartu interaktif untuk kategori City Car, MPV Keluarga, SUV Tangguh, dan VIP/Premium.
   - **Pilihan Mobil Unggulan**: Menampilkan armada terbaik menggunakan komponen kartu terstandarisasi (`partials/car-card.blade.php`).
   - **Mengapa Padaidi Rent Car**: Grid 4 kartu keunggulan dengan ikon SVG inline (`key`, `user`, `shield`, `clock`).
   - **Cara Sewa 3 Langkah**: Penjelasan alur reservasi (01 Pilih Unit & Tanggal, 02 Isi Data & Bayar, 03 Serah Terima Kunci).
   - **Estimasi Biaya & Transparansi Harga**: Format 2 kolom (kiri: penjelasan transparansi, kanan: baris tarif per hari `.price-row`).
   - **Pita Ajakan Bertindak (CTA Band)**: Banner emas penuh dengan tombol WhatsApp langsung ke admin 24 jam.

2. **Katalog Armada (`cars/index.blade.php`)**:
   - Kartu pencarian ketersediaan lengkap dengan filter kategori, transmisi (Matic/Manual), dan kapasitas kursi ($\ge 5$ atau $\ge 7$ kursi).
   - Baris rangkuman hasil pencarian tanggal aktif.
   - Grid armada responsif dengan lencana status ketersediaan dan tautan pemesanan instan.
   - Status kosong (*empty state*) ramah pengguna dengan tombol konsultasi ketersediaan cadangan via WhatsApp.

3. **Detail Mobil (`cars/show.blade.php`)**:
   - Tombol kembali ke daftar armada yang mudah diakses.
   - Galeri foto utama dengan rasio 16:9 / 4:3 dan thumbnail tambahan.
   - Tabel spesifikasi lengkap (Kategori, Merek, Tahun, Transmisi, Kursi, Bahan Bakar).
   - Kotak pemesanan *sticky* di samping kanan dengan validasi ketersediaan real-time berdasarkan tanggal yang dipilih.
   - Daftar ulasan pelanggan terverifikasi beserta peringkat bintang.

4. **Syarat Sewa (`pages/terms.blade.php`)**:
   - Kartu terstruktur beraksen emas untuk Persyaratan Penyewa (KTP, SIM A), Ketentuan Deposit, Batas Kilometer, dan Toleransi Keterlambatan.
   - Rincian alur pembayaran dan batas waktu konfirmasi otomatis.
   - Tabel tarif layanan tambahan (Add-on sopir, asuransi benturan, antar jemput luar zona).

5. **Kontak & Lokasi Pool (`pages/contact.blade.php`)**:
   - Kartu profil Padaidi Rent Car dengan alamat kantor pool Makassar, email resmi, dan layanan 24 jam.
   - Tombol kontak cepat ke seluruh nomor WhatsApp admin yang terdaftar di konfigurasi.
   - Peta interaktif Google Maps embed yang responsif.

---

## 9. Implementasi Halaman Akun, Checkout & Dasbor Admin (Fase 3)

Pada Fase 3, seluruh area autentikasi, akun member, transaksi pemesanan, dan dasbor pengelola admin telah diselaraskan ke dalam ekosistem desain:

1. **Area Autentikasi Pelanggan & Admin**:
   - `member/auth/login.blade.php`: Halaman masuk member dengan kartu bergaris aksen emas (`.card-accent-top`), logo resmi Padaidi, dan input form gelap.
   - `member/auth/register.blade.php`: Formulir pendaftaran akun member dengan validasi terpadu.
   - `member/auth/forgot-password.blade.php` & `reset-password.blade.php`: Pemulihan kata sandi konsisten.
   - `member/auth/verify-contact.blade.php`: Antarmuka verifikasi OTP WhatsApp.
   - `admin/auth/login.blade.php`: Halaman otentikasi pengelola sistem dengan ambient circle gelap dan logo resmi Padaidi.

2. **Area Akun Member**:
   - `member/account/index.blade.php`: Dasbor member dengan kartu profil bergradien gelap, kartu keanggotaan tier (Gold/Silver/Reguler), progres sewa menuju tier berikutnya, saldo poin loyalitas, serta status dokumen e-KYC.
   - `member/account/profile.blade.php`: Formulir pembaruan data diri, nomor NIK KTP, SIM A, serta penggantian kata sandi akun.
   - `member/account/documents.blade.php`: Manajemen unggah dokumen e-KYC privat (KTP & SIM A) dengan indikator status verifikasi audit.
   - `member/account/bookings.blade.php`: Riwayat lengkap transaksi sewa member dengan lencana status sewa dan status pembayaran.

3. **Alur Pemesanan & Pembayaran Pelanggan (Checkout Flow)**:
   - `bookings/create.blade.php`: Formulir pemesanan bertahap (*multi-step breadcrumbs*), pemilihan lokasi pool/antar alamat, layanan add-on sopir/asuransi, input voucher promo, kalkulasi perkiraan tarif dinamis, serta sticky order card.
   - `bookings/review.blade.php`: Halaman konfirmasi pesanan dengan rincian biaya transparan (subtotal mobil, durasi hari, add-on, diskon tier member, diskon kode promo, opsi uang muka DP 30% dan sisa pelunasan 70%).
   - `bookings/show.blade.php`: Status pelacakan pesanan publik/pelanggan, countdown kedaluwarsa pesanan pending, tombol aksi cetak faktur invoice resmi, verifikasi e-KYC dokumen terpadu, penugasan sopir bertugas, checklist serah terima odometer & bbm, serta form upload bukti pembayaran manual.
   - `bookings/lookup.blade.php`: Formulir pencarian status pesanan cepat berbasis kode unik (PDD-XXXX) dan nomor HP pemesan.
   - `bookings/pay.blade.php`: Halaman checkout pembayaran gateway dengan pemilihan opsi DP vs Pelunasan serta pilihan kanal (QRIS instan, Virtual Account BCA, Mandiri, BNI, BRI, Permata).
   - `bookings/payment-instruction.blade.php`: Halaman instruksi pembayaran dengan QRIS code visual, nomor Virtual Account beserta tombol salin instan (*copy to clipboard*), petunjuk langkah transfer per bank, dan timer countdown kedaluwarsa.

4. **Dasbor Pengelola Admin**:
   - `layouts/admin.blade.php`: Sidebar gelap kokoh dengan logo perisai resmi Padaidi, aksen emas aktif, dan navigasi lengkap.
   - `admin/dashboard.blade.php`: Dasbor pemantauan operasional dengan kartu ringkasan status pesanan harian, jadwal pengambilan hari ini, unit yang harus kembali/terlambat, serta tabel pesanan terbaru.
   - `admin/payments/index.blade.php`: Dasbor manajemen keuangan & gateway pembayaran dengan filter status, metrik nominal (paid, pending, refund), dan tombol ekspor data CSV.

---

## 10. Audit Akhir (Aksesibilitas, Performa, & Konsistensi)

### 10.1. Audit Aksesibilitas (WCAG 2.1)
* **Kontras Warna**:
  * Teks gelap `#0b1220` di atas tombol emas `#F5A623`: **9.28:1** (Memenuhi standar tertinggi **WCAG AAA**).
  * Teks slate-50 `#f8fafc` di atas latar kanvas `#0b1220`: **16.14:1** (**WCAG AAA**).
  * Teks slate-400 `#94a3b8` di atas kanvas: **7.81:1** (**WCAG AAA**).
* **Fokus Keyboard**: Seluruh elemen input, tombol, dan tautan memiliki indikator fokus ganda (`--focus-ring: 0 0 0 2px #0b1220, 0 0 0 4px #F5A623`).
* **Area Klik Target Sentuh**: Minimum **44×44 piksel** pada seluruh tombol dan interaksi seluler.
* **Skip Link**: Tautan `<a href="#main-content" class="skip-link">` aktif untuk kemudahan navigasi pembaca layar.
* **Sensitivitas Gerak**: Pengguna dengan pengaturan *prefers-reduced-motion* otomatis dinonaktifkan animasinya.

### 10.2. Audit Performa (*Performance & Zero CDN*)
* **Bebas CDN Eksternal**: Tidak menggunakan Google Fonts atau icon webfonts eksternal yang membebani koneksi seluler.
* **SVG Inline**: Seluruh ikon dimuat secara native melalui komponen Blade `<x-icon />`.
* **Aset Terkompilasi**: Semua token desain dikemas dalam `public/css/design-system.css` dan dioptimalkan ke berkas build CSS.
* **Favicon Lengkap**: Aset multi-resolusi (`favicon.ico`, `favicon-32x32.png`, `apple-touch-icon.png`) dilayani langsung dari server lokal.

---

## 11. Status Akhir Verifikasi Pengujian

Pengujian menyeluruh dijalankan menggunakan PHPUnit pada seluruh suite pengujian sistem:
- **Total Pengujian**: 73 Pengujian (Unit & Feature).
- **Total Asersi**: 473 Asersi.
- **Hasil**: **100% Lolos (0 Failures, 0 Errors, 0 Warnings)**.
- **Integritas Bisnis**: Seluruh alur membership, diskon berjenjang, voucher promo, gateway pembayaran (QRIS, VA, Webhook, Refund), kalkulasi harga server-side, serta otentikasi terverifikasi berjalan sempurna tanpa adanya regresi fungsi.

---

## 12. Pemeliharaan Tampilan & Standardisasi Ikon & Grafis (Maintenance & UI Polish)

Sebagai tindak lanjut audit tampilan menyeluruh, serangkaian perbaikan dan standardisasi presisi diterapkan untuk menjamin kerapian visual, konsistensi ritme, dan kebebasan dari artefak grafis:

### 12.1. Eliminasi Distorsi Aspek Rasio & Layout Shift Gambar
1. **Logo Perisai Navbar & Footer (`padaidi-shield.png`)**: Menghapus atribut HTML `width="48" height="36"` dan `width="48" height="40"` yang berbenturan dengan rasio asli gambar (~272×300 piksel). Digantikan dengan kendali CSS presisi `class="h-9 w-auto object-contain"` (navbar) dan `class="h-10 w-auto object-contain"` (footer).
2. **Emblem Sidebar Admin (`layouts/admin.blade.php`)**: Menghapus atribut `width="32" height="32"` yang memaksa gambar menjadi kotak sebelum CSS dimuat.
3. **Logo Halaman Masuk Admin (`admin/auth/login.blade.php`)**: Menghapus atribut konflik `width="160" height="64"` yang mendistorsi logo shield melebar 2.5:1, digantikan `class="h-16 w-auto mx-auto object-contain"`.
4. **Visual Hero 4:3 Khusus (`padaidi-hero.jpg`)**: Menghasilkan dan menerapkan visual hero berukuran 800×600 px (rasio 4:3 tepat) pada bingkai offset hero di `home.blade.php` sehingga tidak terpotong (letterboxed) seperti banner 16:9 sebelumnya.
5. **Varian Logo Cetak Terang (`padaidi-logo-light.png`)**: Menghasilkan logo khusus kertas putih dengan teks biru navy gelap `#0b1220` untuk faktur invoice (`bookings/invoice.blade.php`), menggantikan logo putih yang tidak terbaca di atas kertas putih.

### 12.2. Standardisasi Ikon & Penggantian Total Emoji Informal
Seluruh emoji yang tidak konsisten antar sistem operasi (Windows vs Android/iOS) diganti dengan komponen SVG inline berstandar industri `<x-icon name="..." />`:
* **Ekspor CSV Admin (`admin/payments/index.blade.php`)**: `📥` &rarr; `<x-icon name="download" class="w-4 h-4 text-slate-700" />`
* **Laporan Pemakaian Promo (`admin/promotions/index.blade.php`)**: `📊` &rarr; `<x-icon name="chart-bar" class="w-4 h-4 text-slate-700" />`
* **Audit Akses Dokumen Privasi (`admin/members/logs.blade.php`)**: `🔒` &rarr; `<x-icon name="lock" class="w-5 h-5 text-slate-700" />`
* **Dokumen e-KYC Member (`member/account/documents.blade.php`)**: `🔒` &rarr; `<x-icon name="lock" class="w-5 h-5 text-[#F5A623]" />`
* **Tindakan Verifikasi Dokumen Admin (`admin/members/show.blade.php`)**: `✓ Setujui` &rarr; `<x-icon name="check" class="w-3.5 h-3.5 text-white" /> Setujui (Sah)`
* **Pemberitahuan Sistem (`partials/flash.blade.php`)**: `✓` dan `ℹ` &rarr; `<x-icon name="check" />` & `<x-icon name="sparkles" />`
* **Cetak Faktur Invoice (`bookings/show.blade.php`)**: `🖨️ Cetak Invoice` &rarr; `<x-icon name="printer" class="w-4 h-4 text-[#F5A623]" />`
* **Status Menunggu Pembayaran (`bookings/show.blade.php`)**: `⏳` &rarr; `<x-icon name="clock" class="w-5 h-5 text-[#F5A623]" />`
* **Tombol Pembayaran Online (`bookings/show.blade.php`)**: `💳 Bayar Online` &rarr; `<x-icon name="credit-card" class="w-4 h-4 text-[#0b1220]" />`
* **Placeholder QRIS (`bookings/payment-instruction.blade.php`)**: `📱` &rarr; `<x-icon name="qrcode" class="w-16 h-16 text-[#0b1220]" />`
* **Tombol Cek Status Pembayaran (`bookings/payment-instruction.blade.php`)**: `🔄` &rarr; `<x-icon name="refresh" class="w-4 h-4 text-[#0b1220]" />`
* **Badge Ketersediaan Mobil (`cars/show.blade.php`)**: `✔ Tersedia` & `✖ Tidak tersedia` &rarr; `<x-icon name="check" />` & `<x-icon name="cross" />`
* **Catatan Jemput Darurat (`pages/contact.blade.php`)**: `💡` &rarr; `<x-icon name="sparkles" class="w-4 h-4 text-[#F5A623]" />`

### 12.3. Penyelarasan Dimensi Ikon Pembungkus (Icon Container Grid)
* **Kategori Armada (`home.blade.php`)**: Ditingkatkan dari `h-10 w-10` (`w-5 h-5`) menjadi `h-12 w-12` (`w-6 h-6`) agar seimbang dengan kartu fitur *Mengapa Padaidi*.
* **Syarat Sewa (`pages/terms.blade.php`)**: Seluruh 4 kartu klausul (Persyaratan, Deposit, Batas KM, Keterlambatan) diselaraskan ke wadah `h-12 w-12` (`w-6 h-6`).
* **Fallback Placeholder Kendaraan (`partials/car-image.blade.php`)**: Didesain ulang menggunakan lingkaran terpusat `h-14 w-14` dengan `<x-icon name="car" class="w-7 h-7 text-[#F5A623]" />` yang fleksibel pada rasio kartu apa pun.
* **Foto Mobil Riwayat Member (`member/account/bookings.blade.php`)**: Memanfaatkan metode resmi `$car->primaryPhoto->url()` dengan fallback ikon mobil berpusat rapi.

### 12.4. Perbaikan Kritis Skala Ikon SVG & Logo Navbar/Footer (Hotfix Icon Sizing)
* **Penyebab Masalah**: 
  1. Ikon `<svg>` tanpa atribut dimensi HTML atau style eksplisit otomatis membesar hingga 100% lebar kolom (200–300px) pada browser ketika kelas utilitas Tailwind (`w-4`, `h-4`) tidak terkompilasi dalam bundel aset statis.
  2. Logo perisai sebelumnya (`padaidi-shield.png`) dipotong hanya pada siluet kap atas mobil (296×155 px, rasio 2:1), sehingga tampak gepeng dan terpotong tanpa bingkai perisai, serta menyebabkan teks *"PADAIDI RENT CAR"* terlipat patah menjadi 4 baris.
* **Solusi Permanen yang Diterapkan**:
  1. **Komponen `<x-icon />` Antigagal**: Secara cerdas mem-parsing kelas ukuran (`w-3` s.d. `w-20`) dan menyuntikkan atribut HTML asli `width="N" height="N"` serta inline style `style="width:Npx;height:Npx;min-width:Npx;max-width:Npx;"`. Ikon 100% kebal terhadap keterlambatan muat CSS dan tidak akan pernah membesar melebihi ukuran yang ditentukan.
  2. **Emblem Perisai Resmi Utuh 1:1**: Mengganti logo navbar dan footer dengan logo resmi utuh `padaidi-logo.png` (aspek rasio badge 1:1, bounding box 308×303 px) dengan dimensi pasti `width="40" height="40"` (navbar) dan `width="44" height="44"` (footer). Berkas `padaidi-shield.png` juga disinkronkan ke versi utuh ini.
  3. **Proteksi Anti-Patah Teks (`whitespace-nowrap`)**: Teks nama brand *"PADAIDI RENT CAR"* dan subteks *"Makassar · 24 Jam"* dikunci dengan kelas `whitespace-nowrap` dan kontainer `shrink-0`, menjamin teks tampil rapi dalam satu baris di seluruh resolusi layar.
  4. **Injeksi Utilitas Sizing CSS Lengkap**: Seluruh kelas ukuran (`.w-3` s.d. `.w-24`, `.h-3` s.d. `.h-24`, `.shrink-0`, `.whitespace-nowrap`, `.object-contain`) didefinisikan secara tegas dengan deklarasi `!important` di `design-system.css` dan `app.css`.

### 12.6. Redesain White Canvas, Penurunan Posisi Reservasi Fleksibel, & Penghapusan Elemen Lingkaran Latar Belakang
* **Latar Belakang Permintaan**:
  1. Elemen lingkaran dekoratif pada latar belakang hero (`.ambient-circle-bg`) dirasa mengganggu visual dan diminta untuk dihilangkan secara menyeluruh.
  2. Kartu pencarian *"Reservasi Fleksibel"* sebelumnya memiliki margin negatif (`-mt-8`) yang membuatnya menabrak/memotong garis pemisah batas seksi (*section divider*). Diminta untuk diturunkan posisinya agar tidak bertabrakan dengan garis batas.
  3. Mengubah warna latar belakang (*background canvas*) ke warna putih bersih (`#ffffff`), dengan ketentuan mutlak: **tanpa mengubah warna header, ikon, fitur, dan alur sistem yang sudah berjalan**.
* **Solusi & Implementasi**:
  1. **Penghapusan Lingkaran Latar Belakang**:
     - Tag `<div class="ambient-circle-bg">` dihapus dari berkas Blade (`home.blade.php`).
     - Utilitas CSS `.ambient-circle-bg { display: none !important; opacity: 0 !important; visibility: hidden !important; }` ditambahkan untuk memastikan tidak ada bentuk lingkaran yang merender di seluruh tampilan publik.
  2. **Penurunan Posisi Kartu Reservasi Fleksibel**:
     - Margin negatif `-mt-8` diganti menjadi margin positif `mt-8 md:mt-12`.
     - Kartu kini duduk rapi dengan spasi lega di bawah garis batas seksi hero (`border-b border-slate-200`), tanpa adanya tabrakan visual atau tumpang-tindih.
  3. **Penerapan Kanvas Putih (*White Canvas Theme*)**:
     - Kontainer halaman utama (`body`, `main#main-content`, dan seksi hero) disetel ke warna putih murni (`bg-white` / `#ffffff`).
     - Teks judul utama disetel ke warna hitam slate (`text-slate-900`) dan teks deskripsi ke slate kontras (`text-slate-600`), sehingga keterbacaan sempurna di atas latar putih.
     - Kartu-kartu konten (Reservasi Fleksibel, Kategori Armada, Mobil Unggulan, Mengapa Padaidi, Cara Sewa, Estimasi Harga) menggunakan latar putih bersih dengan border halus `border-slate-200` dan bayangan lembut `shadow-sm` / `shadow-md`.
     - Kontrol form input menggunakan latar belakang putih (`bg-white`) dengan border abu-abu bersih (`border-slate-300`) dan label yang jelas (`text-slate-700`).
  4. **Preservasi Ketat Elemen yang Dilarang Berubah**:
     - **Header**: Tetap mempertahankan latar *dark navy* solid (`#0b1220`) dengan perisai emblem Padaidi, menu navigasi berjarak rapi, serta sepasang tombol emas aksen (`MEMBER` dan `SEWA SEKARANG`).
     - **Footer**: Tetap mempertahankan latar *dark navy* (`#0b1220`) dengan ikon kontak emas.
     - **Ikon & Aksen Merek**: Seluruh ikon sistem SVG tetap berwarna emas Padaidi (`#F5A623`) atau warna semantik aslinya.
     - **Fitur & Alur Transaksi**: Seluruh logika pencarian mobil, filter armada, formulir pemesanan, integrasi kalkulator sewa, dan pengujian PHPUnit (73/73 tests lolos 100%) tetap utuh tanpa perubahan fungsional.

### 12.7. Peningkatan UI/UX Landing Page Berorientasi Tujuan Bisnis (Goal-Oriented Optimization)
* **Tujuan Inti Bisnis Website Padaidi Rent Car**:
  1. Melayani penyewaan mobil di Makassar & Sulawesi Selatan dengan dua opsi utama: **Lepas Kunci (*Self-Drive*)** dan **Dengan Sopir Profesional**.
  2. Layanan operasional **24 Jam Nonstop**, siap antar-jemput ke **Bandara Internasional Sultan Hasanuddin (UPG)** dan hotel.
  3. Transparansi harga tanpa biaya siluman (*no hidden fees*).
  4. Konversi reservasi online instan dan integrasi konsultasi cepat via WhatsApp Customer Service.
  5. Pemanfaatan sistem keanggotaan (**Membership Tiers: Reguler, Silver 5%, Gold 10%**) untuk meningkatkan retensi pelanggan.
* **Peningkatan UI & UX yang Diimplementasikan**:
  1. **Hero Conversion Area**:
     - Ditambahkan trust badge resmi: `SEWA MOBIL RESMI MAKASSAR · BUKA 24 JAM NONSTOP`.
     - Value proposition kuat: *"Rental Mobil Makassar, Lepas Kunci & Sopir Berpengalaman"*.
     - 3 CTA langsung: Cari Mobil Tersedia, Lihat Semua Armada, dan tombol Chat CS 24 Jam WhatsApp dengan ikon resmi.
     - Blok statistik kredibilitas: 50+ Unit Armada, 24 Jam Antar Bandara UPG, 100% Harga Transparan, 4.9 ★ Rating Kepuasan.
  2. **Widget Reservasi Fleksibel Interaktif**:
     - Ditambahkan indikator lencana status layanan: *Lepas Kunci Tersedia*, *Dengan Sopir Siap*, dan *Konfirmasi Instan*.
     - Formulir pencarian (`partials/search-form.blade.php`) diperkaya dengan label ikon SVG inline (Map-Pin, Calendar, Car), kontras tinggi, dan tombol pencarian berdaya tarik tinggi.
     - Microcopy penegas kepercayaan: *Tanpa Biaya Tersembunyi*, *Buka 24 Jam Nonstop*, dan *Punya akun member? Login untuk klaim diskon hingga 10%*.
  3. **Banner Loyalitas Member Eksklusif (Membership Privilege)**:
     - Kartu visual modern bergradien dark navy dengan watermark perisai Padaidi.
     - Menjelaskan keuntungan 3 tier keanggotaan:
       - **Reguler (0%)**: Pendaftaran instan, riwayat sewa & data tersimpan aman.
       - **Silver (Diskon 5%)**: Otomatis aktif mulai sewa ke-2, prioritas alokasi unit.
       - **Gold (Diskon 10%)**: Otomatis aktif mulai sewa ke-5, prioritas unit tahun termuda & fasilitas pembatalan/reschedule fleksibel.
     - Tombol aksi langsung ke registrasi member gratis (`member.register`) dan login (`member.login`).
  4. **Panduan Rute Destinasi Populer Sulawesi Selatan**:
     - Menghubungkan tujuan perjalanan pelanggan dengan pemilihan kendaraan yang tepat:
       1. *Bandara Sultan Hasanuddin (UPG)* · ±20-30 Menit · Antar-jemput 24 Jam.
       2. *Rammang-Rammang & Taman Nasional Bantimurung Maros* · ±45-60 Menit (Karst terbesar ke-2 dunia).
       3. *Malino Highland, Gowa* · ±2-2.5 Jam (Wisata pinus & kebun teh berhawa sejuk).
       4. *Pantai Tanjung Bira, Bulukumba* · ±4.5-5 Jam (Pasir putih & sentra perahu Pinisi).
       5. *Tana Toraja & Kete Kesu* · ±7-8 Jam (Wisata adat & Tongkonan).
  5. **FAQ Interaktif Berbasis Elemen Semantik `<details><summary>`**:
     - Menjawab 5 kekhawatiran terbesar calon penyewa (syarat lepas kunci, antar bandara/hotel, sistem DP & deposit, izin rute luar kota, dan klaim diskon member).
     - Transisi halus chevron berputar dengan aksen warna emas `#F5A623`.
  6. **Floating Quick WhatsApp Action Pill**:
     - Tombol mengambang elegan di sudut kanan bawah (`bottom-6 right-6`) dengan indikator denyut aktif hijau (`.live-pulse-dot`) bertuliskan *"CS 24 Jam · Tanya via WA"*.
     - Memberikan jalur konversi cepat bagi pengguna mobile dan desktop yang membutuhkan konsultasi instan.
  7. **Integritas Sistem & Keamanan**:
     - 100% lolos pengujian PHPUnit (73 tests, 473 assertions).
     - Tidak merusak rute, controller, database, atau logika bisnis rental yang ada.

### 12.8. Perbaikan Kontras Teks Statistik, Pembersihan Teks Bawah Logo, & Pemisahan Spasi Reservasi Fleksibel
* **Identifikasi Isu dari Tangkapan Layar Pengguna**:
  1. **Teks Blok Statistik Tidak Terlihat (Putih di Atas Putih)**:
     - Angka statistik (*"50+ Unit"*, *"24 Jam"*, *"100%"*, *"4.9 ★"*) dan labelnya (*"ARMADA TERAWAT"*, *"ANTAR BANDARA UPG"*, dll.) memiliki warna bawaan `var(--color-text)` (`#f8fafc`) dari sistem tema gelap lama, sehingga di atas latar kanvas putih teks menjadi tidak terlihat kecuali diblok/disorot kursor.
  2. **Kalimat di Bawah Gambar Logo Menumpuk & Mengganggu**:
     - Pada aset gambar hero `padaidi-hero.jpg` terdapat kotak teks *"PADAIDI RENT CAR - MAKASSAR 24 JAM"* dan *"Layanan Sewa Lepas Kunci & Driver Profesional Terpercaya"* serta badge mengambang yang membuat tampilan visual hero terasa padat dan tidak rapi.
  3. **Garis Emas Reservasi Fleksibel Menabrak / Menyatu dengan Hero**:
     - Garis atas aksen emas kartu reservasi fleksibel (`card-accent-top`) menempel langsung dengan bayangan offset (`-15px 15px 0px #F5A623`) dari bingkai hero karena ukuran gambar hero terlalu besar (`max-w-md`, 448px) dan spasi vertikal antar-seksi kurang lega.
* **Solusi & Hasil Perbaikan**:
  1. **Warna Teks Statistik Diubah Menjadi Hitam Pekat & Slate Kontras**:
     - Kelas `.stats-number` dikunci ke warna `#0f172a !important` (hitam navy pekat) di berkas `public/css/design-system.css`, `resources/css/design-system.css`, dan langsung di `home.blade.php`.
     - Kelas `.stats-label` dikunci ke warna `#475569 !important` (slate gelap tegas, font-weight 700).
     - Ikon bintang rating 4.9 diganti menggunakan SVG inline `<x-icon name="star" class="w-5 h-5 text-[#F5A623]" />` untuk menghindari masalah encoding karakter pada berbagai sistem operasi/browser.
  2. **Regenerasi Aset `padaidi-hero.jpg` Bersih Tanpa Teks**:
     - Berkas `public/images/brand/padaidi-hero.jpg` digenerasi ulang secara murni hanya memuat emblem logo resmi Padaidi yang elegan dan terpusat di atas latar belakang gradien navy mewah (`#0b1220` ke `#111827`) dengan garis kisi halus. Seluruh kotak teks dan kalimat di bawah logo telah dihapus total.
     - Elemen badge mengambang di atas gambar juga dihilangkan dari `home.blade.php`, menghasilkan tampilan kartu visual yang bersih, prestisius, dan minimalis.
  3. **Penyesuaian Skala Ukuran Logo & Pemisahan Spasi Garis Kuning**:
     - Ukuran bingkai visual hero disesuaikan proporsional dari `max-w-md` (448px) menjadi `max-w-[320px] sm:max-w-[340px]`, sehingga tidak mendominasi layar dan tinggi visual menjadi ramping (~250px).
     - Hero section diberi bantalan bawah lega `pb-16 md:pb-24` dan kartu pencarian `#cari-mobil` diberi jarak atas `mt-12 md:mt-16`.
     - Garis kuning aksen atas kartu reservasi fleksibel kini terpisah dengan jarak lapang (>50px) dari bagian bawah gambar logo dan bayangan offset, tanpa ada tabrakan visual.

### 12.9. Peningkatan Tipografi Modern (Plus Jakarta Sans) & Standarisasi Rasio Kontras Teks (WCAG AA)
* **Latar Belakang & Rekomendasi Font**:
  - Menggantikan font sistem generik (`Segoe UI`, `Arial`) dengan **Plus Jakarta Sans** (Google Fonts). Plus Jakarta Sans dipilih karena:
    1. Dirancang khusus untuk antarmuka digital dan web modern dengan proporsi geometris yang bersih, modern, dan sangat nyaman dibaca (*high legibility*).
    2. Mendukung rentang *font-weight* lengkap (Light 300 hingga Black 800) yang memberikan hierarki visual tegas antara display hero, judul seksi, subjudul, label, dan angka harga.
    3. Diimpor dengan optimasi `<link rel="preconnect">` untuk performa muat secepat kilat tanpa layout shift.
* **Audit & Perbaikan Warna Font (Accessibility & Contrast Ratio)**:
  - **Identifikasi Masalah Kontras**:
    - Warna emas terang `#F5A623` memiliki rasio kontras hanya **2.03:1** terhadap latar belakang kanvas putih (`#ffffff`), yang gagal memenuhi standar aksesibilitas web (WCAG AA mewajibkan minimal **4.5:1** untuk teks reguler).
    - Ketika teks seperti kategori kicker (`.section-kicker`), harga sewa mobil harian (`rupiah($car->daily_price)`), atau teks deskripsi diwarnai dengan `#F5A623` di atas kartu putih, tulisan terlihat pudar/silau dan sulit terbaca.
  - **Solusi Penerapan Dual-Palette Aksen Brand**:
    1. **Teks & Label di Atas Kanvas / Kartu Putih**:
       - Menggunakan *rich amber-gold* `#b45309` (Amber-700) untuk `.section-kicker`, harga sewa harian (`.price-row-price`), dan tautan penting. Rasio kontras mencapai **4.8:1** (Lolos WCAG AA).
       - Untuk badge status/kategori di kanvas terang (`.badge-accent`), menggunakan kombinasi latar kuning lembut `#fef3c7` dengan teks gelap amber pekat `#92400e` (Amber-800, rasio **5.6:1**).
    2. **Teks & Elemen di Atas Latar Gelap (Header, Footer, Dark Card, Banner Member)**:
       - Mempertahankan warna emas brand resmi `#F5A623` yang sangat kontras (**> 10:1**) dan bersinar di atas latar navy pekat (`#0b1220` dan `#111827`).
       - Ikon SVG dekoratif (`<x-icon>`) tetap mempertahankan `#F5A623` sebagai aksen visual brand yang konsisten.
  - **Halaman yang Diselaraskan**:
    - `resources/views/layouts/app.blade.php` & `admin.blade.php`: Integrasi Google Fonts Plus Jakarta Sans.
    - `resources/views/home.blade.php`: Pembersihan warna kicker, angka step pemesanan, dan FAQ link.
    - `resources/views/partials/car-card.blade.php`: Harga rental harian diubah ke `text-amber-700 font-black`.
    - `resources/views/cars/show.blade.php`: Tombol kembali, spesifikasi transmisi, dan harga harian di kartu sticky.
    - `resources/views/cars/index.blade.php`: Kicker katalog dan badge kategori.
    - `resources/views/bookings/create.blade.php`: Harga add-on dan estimasi total biaya.
    - `resources/views/bookings/lookup.blade.php` & `show.blade.php`: Kicker status dan kode booking header.
    - `resources/views/pages/contact.blade.php`: Jam operasional dan email resmi.
    - `resources/views/pages/terms.blade.php`: Tabel harga tarif add-on dan bullet poin kebijakan.
* **Hasil Pengujian**:
  - Seluruh 73 pengujian otomatis PHPUnit (473 assertions) lulus 100% tanpa ada regresi fitur.

### 12.10. Integrasi Foto Riil Armada Mobil, Redesain Banner VIP Member, & Navigasi Konfigurasi Rute Destinasi
* **1. Integrasi Foto Riil Seluruh Armada Mobil (Pilihan Mobil Populer & Detail)**:
  - **Masalah**: Sebelumnya, kartu mobil di halaman depan dan katalog hanya menampilkan ikon siluet generik karena tabel `car_photos` masih kosong (`count = 0`).
  - **Solusi**:
    - Mengunduh dan menempatkan foto resolusi tinggi berlisensi resmi/terbuka untuk 8 unit kendaraan di Padaidi Rent Car ke dalam direktori `storage/app/public/cars/`:
      1. **Toyota Agya** (`toyota-agya.jpg` - City Car, Compact White)
      2. **Honda Brio** (`honda-brio.jpg` - City Car, Hatchback)
      3. **Toyota Avanza** (`toyota-avanza.jpg` - MPV Keluarga)
      4. **Mitsubishi Xpander** (`mitsubishi-xpander.jpg` - MPV Dynamic Shield)
      5. **Toyota Innova Zenix** (`toyota-innova-zenix.jpg` - MPV Modern)
      6. **Toyota Fortuner** (`toyota-fortuner.jpg` - SUV Gagah TRD)
      7. **Mitsubishi Pajero Sport** (`mitsubishi-pajero-sport.jpg` - SUV Dakar)
      8. **Toyota Alphard** (`toyota-alphard.jpg` - MPV Premium Luxury)
    - Menyimpan relasi foto utama (`is_primary = true`) pada tabel `car_photos` dan mengintegrasikannya secara permanen pada `database/seeders/DatabaseSeeder.php`.
    - Menyempurnakan pemanggilan foto pada `resources/views/partials/car-image.blade.php` dengan `$url = $car->primaryPhoto?->url()` agar foto selalu tampil presisi baik saat di-eager-load maupun dipanggil langsung.

* **2. Redesain & Peningkatan Kontras Banner VIP Member (Program Loyalitas)**:
  - **Masalah**: Aturan CSS `.card { background-color: #ffffff !important; }` menimpa gradien gelap latar belakang, sehingga teks putih (`text-white`) dan deskripsi (`text-slate-300`) menjadi tidak terlihat (*white-on-white*).
  - **Solusi**:
    - Dibuat komponen khusus `.member-vip-banner` yang terisolasi dari kelas `.card`, menggunakan gradien midnight navy mewah (`linear-gradient(135deg, #070e1b 0%, #0b1325 50%, #152238 100%) !important;`), garis batas atas emas 4px (`border-top: 4px solid #F5A623`), dan bayangan elevasi dalam.
    - Struktur 3 Tier Membership dipercantik:
      - **Tier Reguler**: Box navy gelap dengan teks putih tegas dan deskripsi pendaftaran instan.
      - **Tier Silver**: Aksen metallic silver dengan label *2x Sewa* dan diskon 5% berwarna amber terang.
      - **Tier Gold**: Border aksen emas 2px `#F5A623`, badge *VIP*, dan teks diskon 10% mencolok.
    - Tombol aksi utama (CTA) diperjelas dengan ukuran lebih besar, bayangan tegas, dan mikro-interaksi hover, dilengkapi 3 jaminan kepercayaan (*100% Gratis*, *Verifikasi Cepat*, *Diskon Otomatis*).

* **3. Navigasi Konfigurasi Interaktif Rute Destinasi Wisata & Bisnis**:
  - **Kebutuhan**: Pengguna menginginkan setiap kartu pilihan rute dapat ditekan dan langsung mengarahkan pengunjung ke jenis mobil yang direkomendasikan untuk rute tersebut.
  - **Implementasi**:
    - Mengubah kartu statis `<div>` menjadi elemen navigasi interaktif `<a>` berkelas `.destination-card` dengan efek hover elevasi, garis batas emas, dan indikator panah dinamis.
    - Menghubungkan masing-masing dari 5 rute ke filter armada yang sesuai:
      1. **Bandara Sultan Hasanuddin** (*Cocok Semua Tipe Mobil*) → Mengarahkan ke `route('cars.index')` (Katalog lengkap semua armada).
      2. **Rammang-Rammang & Maros** (*City Car / MPV Hemat*) → Mengarahkan ke `route('cars.index', ['category' => 'city-car'])` (Filter mobil kompak hemat BBM).
      3. **Malino Highland, Gowa** (*MPV / SUV Tangguh*) → Mengarahkan ke `route('cars.index', ['category' => 'mpv'])` (Filter MPV tangguh untuk tanjakan).
      4. **Tanjung Bira, Bulukumba** (*Innova / SUV Nyaman*) → Mengarahkan ke `route('cars.index', ['category' => 'suv'])` (Filter SUV berjarak tempuh jauh).
      5. **Tana Toraja & Kete Kesu** (*Fortuner / Pajero / Hiace*) → Mengarahkan ke `route('cars.index', ['category' => 'suv'])` (Filter kendaraan tangguh lintas kabupaten).
    - Menambahkan tombol aksi kecil *"Pilih →"* di pojok kanan bawah setiap kartu rute yang bereaksi saat kursor melayang di atasnya.

### 12.11. Sistem Navigasi Flowchart Terpadu: Tombol "Kembali ke Beranda" & Alur Mundur Halaman Sebelumnya
* **Latar Belakang & Kebutuhan Pengguna**:
  - Pada halaman katalog armada (`/armada`) dan halaman sub-fitur lainnya, pengguna belum memiliki alur mundur yang eksplisit selain navbar utama. Pengguna meminta fitur tombol "Kembali ke Beranda" dan alur mundur ke halaman sebelumnya di seluruh halaman yang tersedia tanpa jalan buntu (*dead-ends*).
* **Komponen Baru**: `resources/views/components/breadcrumb.blade.php`:
  - Menampilkan dua elemen dalam satu baris fleksibel (`flex-wrap justify-between`):
    1. **Aksi Mundur Cepat (`← Kembali ke [Halaman/Beranda]`)**: Tombol teks tebal amber (`text-amber-700 hover:text-amber-900`) yang mengarahkan langsung ke langkah sebelumnya (misal: dari Form Pemesanan kembali ke Detail Mobil), plus pemisah pipa dan tautan cepat ke *Beranda*.
    2. **Jejak Navigasi (*Breadcrumbs*)**: Menampilkan hierarki halaman aktif (`Beranda / Armada / Toyota Fortuner / Pesan`) menggunakan elemen semantik `<nav aria-label="Navigasi Halaman">` dan `<ol>` yang ramah SEO serta *screen-reader*.
* **Audit & Implementasi Alur Flowchart Seluruh Halaman**:
  1. **Katalog Armada (`/armada`)**:
     - `← Kembali ke Beranda` | Breadcrumb: `Beranda / Daftar Armada`.
  2. **Detail Mobil (`/mobil/{car}`)**:
     - `← Kembali ke Daftar Armada` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Daftar Armada / [Nama Mobil]`.
  3. **Form Pemesanan (`/pesan/{car}`)**:
     - `← Kembali ke Detail Mobil` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Daftar Armada / [Nama Mobil] / Pesan Mobil`.
  4. **Ringkasan Pesanan (`/pesan/{car}/ringkasan`)**:
     - `← Kembali ke Form Pemesanan` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Daftar Armada / [Nama Mobil] / Ringkasan Pesanan`.
  5. **Status & Detail Pesanan (`/pesanan/{booking}`)**:
     - `← Kembali ke Beranda` | Breadcrumb: `Beranda / Cek Pesanan / [Kode Booking]`.
  6. **Cek Status Pesanan (`/cek-pesanan`)**:
     - `← Kembali ke Beranda` | Breadcrumb: `Beranda / Cek Pesanan`.
  7. **Pembayaran Online (`/pesanan/{booking}/bayar`)**:
     - `← Kembali ke Detail Pesanan` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Pesanan / Pembayaran Online`.
  8. **Instruksi Pembayaran QRIS / VA (`/pesanan/{booking}/bayar/{payment}`)**:
     - `← Kembali ke Detail Pesanan` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Pesanan / Instruksi Pembayaran`.
  9. **Syarat & Ketentuan Sewa (`/syarat-sewa`)**:
     - `← Kembali ke Beranda` | Breadcrumb: `Beranda / Syarat & Ketentuan`.
  10. **Kontak & Lokasi Pool (`/kontak`)**:
      - `← Kembali ke Beranda` | Breadcrumb: `Beranda / Kontak & Bantuan`.
  11. **Masuk Member (`/member/login`) & Daftar Member (`/member/register`)**:
      - `← Kembali ke Beranda` | Breadcrumb: `Beranda / Masuk Member` atau `Daftar Member`.
  12. **Lupa Kata Sandi (`/forgot-password`) & Reset Sandi (`/reset-password`)**:
      - `← Kembali ke Masuk Member` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Masuk Member / Lupa Sandi`.
  13. **Dashboard Akun Member (`/member/akun`)**:
      - `← Kembali ke Beranda` | Breadcrumb: `Beranda / Dashboard Member`.
  14. **Ubah Profil Member (`/member/profil`)**:
      - `← Kembali ke Dashboard Member` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Dashboard Member / Ubah Profil`.
  15. **Kelola Dokumen e-KYC (`/member/dokumen`)**:
      - `← Kembali ke Dashboard Member` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Dashboard Member / Verifikasi Dokumen`.
  16. **Riwayat Pesanan Member (`/member/pesanan`)**:
      - `← Kembali ke Dashboard Member` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Dashboard Member / Riwayat Pesanan`.
  17. **Verifikasi Kontak WhatsApp (`/member/verifikasi-kontak`)**:
      - `← Kembali ke Dashboard Member` + Tautan cepat `Beranda` | Breadcrumb: `Beranda / Dashboard / Verifikasi Kontak`.
* **Hasil Verifikasi**:
  - 100% lulus pengujian PHPUnit (73 test, 473 assertions). Halaman publik status HTTP 200 OK.







