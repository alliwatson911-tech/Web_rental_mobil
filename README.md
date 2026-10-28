# Padaidi Rental Mobil

Sistem aplikasi web manajemen sewa kendaraan (mobil) berbasis **Laravel**, **MySQL**, dan **Tailwind CSS**. Sistem ini mendukung operasional rental mobil lepas kunci (*self-drive*) maupun dengan sopir (*with driver*), kalkulasi harga server-side yang aman dari manipulasi, modul keanggotaan pelanggan (*Customer Membership*) berjenjang, serta mesin pengelolaan diskon dan voucher promo.

---

## Fitur Utama Sistem

1. **Pemesanan Kendaraan Real-Time:**
   * Deteksi ketersediaan armada mobil akurat dengan penguncian baris database (`SELECT ... FOR UPDATE`) untuk mencegah bentrok tanggal (*double-booking*).
   * Opsi paket sewa lepas kunci dan dengan sopir, serta berbagai layanan tambahan (*Addons*) seperti asuransi dan antar-jemput.
   * Mendukung pemesanan cepat oleh tamu (*Guest Checkout*) tanpa kewajiban login.

2. **Modul Keanggotaan Pelanggan (Fase 1):**
   * **Pendaftaran & Otentikasi Cepat:** Login fleksibel menggunakan alamat email atau nomor WhatsApp.
   * **Tingkat Keanggotaan Otomatis (Tier):**
     * **Reguler:** Tier awal saat mendaftar (0% diskon).
     * **Silver:** Minimal 3 transaksi sewa selesai (Diskon 5% tarif sewa mobil).
     * **Gold:** Minimal 10 transaksi sewa selesai (Diskon 10% tarif sewa mobil).
   * **Poin Loyalitas:** 1 poin diperoleh otomatis setiap kelipatan Rp 100.000 dari transaksi sewa yang berstatus `Completed`.
   * **Verifikasi Identitas Aman (e-KYC):** Unggah e-KTP dan SIM A ke penyimpanan privat (`Storage::disk('local')`) terenkripsi dan tidak dapat diakses publik.
   * **Kepatuhan Privasi Data (UU PDP):** Log audit akses dokumen sensitif (`document_access_logs`) mencatat setiap aktivitas pratinjau berkas identitas dengan alamat IP dan *User-Agent*.
   * **Kontrol Admin Penuh:** Pencarian member, penonaktifan akun bermasalah, verifikasi/penolakan e-KYC, dan kemampuan *override* tier manual dengan catatan alasan wajib.

3. **Mesin Diskon & Kode Promo (Fase 2):**
   * **CRUD Diskon di Admin (`/admin/promosi`):** Pembuatan promo kupon voucher, diskon pengguna baru (*first-time renter*), promo musiman/periode, dan diskon tier.
   * **Aturan Fleksibel:** Pilihan persentase (%) atau nominal tetap (Rp), batas maksimal potongan, syarat minimal transaksi, minimal durasi sewa, kuota pemakaian global, serta kuota maksimal per pengguna.
   * **Cakupan Spesifik (Scope):** Pembatasan promo berdasarkan kategori mobil (misal: khusus SUV), unit mobil tertentu, atau lokasi penjemputan.
   * **Urutan Perhitungan Server-Side yang Tegas:**
     1. Diskon persentase (tier member atau voucher %) dihitung dari subtotal sewa mobil murni (tidak memotong addons).
     2. Diskon nominal diterapkan setelah diskon persentase.
     3. Total potongan maksimal 100% dari subtotal sewa mobil (tidak boleh negatif).
   * **Pencatatan Penebusan (*Redemptions*) & Rollback:** Setiap pemakaian promo dicatat ke database; jika pesanan dibatalkan, kuota promo otomatis dikembalikan.
   * **Laporan Pemakaian Promo (`/admin/promosi/laporan`):** Menampilkan total promo terpakai, total subsidi diskon, dan rata-rata penghematan transaksi.

4. **Gerbang Pembayaran Terperinci (*Payment Gateway* - Fase 3):**
   * **Layer Abstraksi Gateway:** Berbasis kontrak `PaymentGatewayInterface` dengan driver `FakePaymentGateway` (untuk pengujian & demo offline) dan `XenditPaymentGateway` (untuk produksi/sandbox).
   * **Kanal Pembayaran Otomatis:** QRIS dinamis instan serta Virtual Account bank resmi (BCA, Mandiri, BNI, BRI, Permata).
   * **Opsi Fleksibel Uang Muka (DP 30%) & Pelunasan:** Nominal dihitung murni di server secara aman.
   * **Webhook Aman & Idempoten:** Verifikasi tanda tangan digital/token, proteksi duplikasi peristiwa mentah via `webhook_events`, dan auto-konfirmasi pesanan saat pembayaran diterima.
   * **Penjadwal Otomatis (Scheduler):**
     * `php artisan payments:expire`: Menutup otomatis tagihan pending yang kedaluwarsa.
     * `php artisan payments:reconcile`: Menyinkronkan status pembayaran pending langsung ke gateway.
   * **Pengembalian Dana (*Refund*) & Ekspor Data:** Pencatatan alasan refund dengan proteksi over-refund di `payment_refunds`, serta ekspor riwayat transaksi ke berkas CSV.
   * **Faktur / Invoice Resmi:** Halaman faktur siap cetak / simpan PDF (`/pesanan/{code}/invoice`) dengan layout A4 rapi dan stempel digital resmi.

---

## Kebutuhan Sistem

* **PHP:** Versi 8.2 atau lebih tinggi (didukung hingga PHP 8.5)
* **Ekstensi PHP:** `pdo_mysql`, `fileinfo`, `gd`/`imagick`, `mbstring`, `openssl`
* **Basis Data:** MySQL 8.0+ atau MariaDB 10.4+
* **Composer:** 2.0+

---

## Panduan Instalasi & Menjalankan Proyek

1. **Clone & Masuk Direktori:**
   ```bash
   cd c:\XAMPP\htdocs\padaidi
   ```

2. **Salin Berkas Konfigurasi Lingkungan:**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. **Konfigurasi Database & Gateway di `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=padaidi
   DB_USERNAME=root
   DB_PASSWORD=

   # Pengaturan Payment Gateway (Fase 3)
   PAYMENT_GATEWAY_DRIVER=fake # Ubah ke 'xendit' untuk mode sandbox/produksi
   PAYMENT_EXPIRY_HOURS=24
   PAYMENT_DP_ENABLED=true
   PAYMENT_DP_PERCENTAGE=30

   # Xendit (Jika Driver = xendit)
   XENDIT_SECRET_KEY=xnd_development_xxxxxxxx
   XENDIT_WEBHOOK_TOKEN=token_callback_anda
   XENDIT_ENV=development
   ```

4. **Jalankan Migrasi & Seeder Database:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```

---

## Akun & Kode Promo Bawaan (*Default Seed Data*)

### Akun Pengujian
| Peran (Role) | Email | Kata Sandi | Keterangan |
| :--- | :--- | :--- | :--- |
| **Admin Utama** | `admin@padaidi.test` | `padaidi-admin-123` | Akses penuh panel admin |
| **Admin Operasional** | `admin2@padaidi.test` | `padaidi-admin-123` | Pengelola reservasi & member |
| **Member Contoh** | `member@padaidi.test` | `padaidi-member-123` | Akun pelanggan untuk pengujian |

### Kode Promo Contoh
| Kode Voucher | Tipe & Nilai | Syarat & Ketentuan |
| :--- | :--- | :--- |
| `PADAIDI50K` | Nominal Rp 50.000 | Min. subtotal Rp 300.000, kuota 100x |
| `HEMAT10` | Persentase 10% (Maks Rp 100.000) | Min. sewa 2 hari, min. subtotal Rp 500.000 |
| `NEWUSER` | Persentase 15% (Maks Rp 150.000) | Khusus transaksi pertama pengguna baru |

---

## Menjalankan Uji Otomatis (*Automated Tests*)

Seluruh modul dan logika bisnis diuji secara ketat menggunakan PHPUnit dengan SQLite in-memory:

```bash
php vendor/phpunit/phpunit/phpunit
```

*Status:* **73 tests, 473 assertions, 100% PASSING**.

Cakupan pengujian meliputi:
* Registrasi, login (email & no HP), lupa password, dan keamanan otentikasi.
* Pencegahan pesanan ganda (*Double Booking prevention under concurrency*).
* Unggah berkas privat e-KYC dan pencegahan akses tidak terotorisasi (403 Forbidden).
* Pencatatan audit trail akses dokumen sensitif.
* Kenaikan tier otomatis (Reguler &rarr; Silver &rarr; Gold) dan akumulasi poin loyalitas.
* CRUD promo admin dan laporan penebusan promo.
* Validasi kupon promo (kuota habis, kuota per user, minimal hari/subtotal sewa, cakupan kategori/mobil/lokasi, pengguna baru).
* Urutan tegas penerapan diskon dan penggabungan tier dengan kode promo.
* Uji baku perhitungan keuangan dari Context (Toyota Agya 3 hari + Asuransi + Diskon Tier Silver 5% + Voucher Rp 50.000 = Rp 955.000, DP 30% = Rp 286.500, Sisa = Rp 668.500).
* Pembuatan tagihan payment gateway (QRIS & Virtual Account) untuk DP 30% dan Pelunasan Penuh.
* Webhook gateway yang aman, idempoten, dan memicu auto-konfirmasi pesanan.
* Penolakan webhook palsu / token tidak sah (401 Unauthorized).
* Penutupan otomatis tagihan kedaluwarsa (`payments:expire`) dan sinkronisasi gateway (`payments:reconcile`).
* Pengembalian dana (*refund*) bertahap/penuh oleh admin dengan proteksi saldo berlebih, serta ekspor CSV transaksi.
* Akses faktur / invoice resmi siap cetak bagi pelanggan dan admin.

---

## Dokumentasi Arsitektur & Basis Data

* [Dokumentasi Alur Bisnis & Roadmap (docs/MEMBER-DISKON-PAYMENT.md)](docs/MEMBER-DISKON-PAYMENT.md)
* [Dokumentasi Perubahan Skema Basis Data & ERD (docs/DATABASE-UPDATE.md)](docs/DATABASE-UPDATE.md)
* [Alur Reservasi Mobil (docs/ALUR.md)](docs/ALUR.md)
