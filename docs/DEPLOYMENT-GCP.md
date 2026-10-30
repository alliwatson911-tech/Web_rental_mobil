# Panduan Lengkap Hosting Web Padaidi Rent Car di Google Cloud Platform (GCP)

Dokumen ini menjelaskan arsitektur, konfigurasi container produksi, dan panduan langkah-demi-langkah untuk melakukan *deployment* website **Padaidi Rent Car (Laravel 13 + PHP 8.3 + Tailwind/Vite)** ke **Google Cloud Web Hosting**.

---

## 1. Arsitektur Deployment yang Direkomendasikan: Google Cloud Run

Untuk aplikasi modern berbasis Laravel, **Google Cloud Run** adalah platform hosting resmi Google yang paling direkomendasikan karena:

| Keunggulan | Penjelasan Teknis |
| :--- | :--- |
| **Serverless & Auto-Scaling** | Otomatis menaikkan kapasitas saat banyak penyewa mengakses, dan otomatis *scale to 0* saat malam sepi (sehingga sangat hemat biaya). |
| **Free Tier Sangat Murah / Gratis** | Google Cloud memberikan kuota gratis **2 juta permintaan (requests)/bulan**, 360.000 vCPU-detik, dan 180.000 GiB-detik setiap bulannya. |
| **Lokasi Server Jakarta (`asia-southeast2`)** | Latensi sangat rendah (< 25 ms) dari Makassar dan seluruh wilayah Indonesia. |
| **Otomatis HTTPS (SSL Gratis)** | Google mengelola sertifikat TLS/SSL secara otomatis tanpa konfigurasi manual Let's Encrypt. |
| **Cloud Build Tanpa Docker Lokal** | Anda **tidak wajib** menginstal Docker di laptop Windows Anda. Google Cloud Build akan otomatis mengkompilasi file dan membuat image container di server Google Cloud. |

```
[ Pengunjung / Browser ]
           │
           ▼ (HTTPS / Custom Domain)
[ Google Cloud Load Balancer / CDN ]
           │
           ▼
[ Google Cloud Run (Container) ] ◄── Region Jakarta (asia-southeast2)
   ├── Nginx (Reverse Proxy & Static Cache 1y)
   ├── PHP 8.3 FPM (OPcache Enabled)
   └── Laravel 13 Application (Routes/Views Cached)
           │
           ▼
[ Database Pilihan ]
   ├── Opsi 1: Google Cloud SQL (Managed MySQL 8.0)
   ├── Opsi 2: External Managed MySQL (Aiven / PlanetScale)
   └── Opsi 3: SQLite File (Demo / Staging Instan)
```

---

## 2. Berkas Konfigurasi yang Telah Disiapkan

Dalam repositori ini, seluruh berkas konfigurasi *production container* telah dibuat dan siap pakai:

1. **[`Dockerfile`](file:///c:/XAMPP/htdocs/padaidi/Dockerfile)**:
   - Base image: `php:8.3-fpm-alpine` (ringan, booting < 1 detik).
   - Ekstensi PHP terpasang: `pdo_mysql`, `pdo_sqlite`, `bcmath`, `mbstring`, `opcache`, `gd`, `intl`, `zip`, `exif`.
   - Composer v2 teroptimasi (`--optimize-autoloader --no-dev`).
   - Menyertakan seluruh foto armada resolusi tinggi yang sudah tersimpan di `storage/app/public/cars/`.
2. **[`docker/nginx.conf`](file:///c:/XAMPP/htdocs/padaidi/docker/nginx.conf)**:
   - Server block teroptimasi untuk Cloud Run (mendukung alokasi `$PORT` dinamis).
   - Kompresi Gzip aktif, batas upload berkas KTP/SIM hingga 25MB, dan cache aset statis 1 tahun.
3. **[`docker/php.ini`](file:///c:/XAMPP/htdocs/padaidi/docker/php.ini)**:
   - Pengaturan PHP produksi: `memory_limit=256M`, `timezone=Asia/Makassar` (WITA), dan OPcache produksi.
4. **[`docker/supervisord.conf`](file:///c:/XAMPP/htdocs/padaidi/docker/supervisord.conf)**:
   - Menjalankan Nginx dan PHP-FPM secara bersamaan dengan logging langsung ke Google Cloud Logging.
5. **[`docker/entrypoint.sh`](file:///c:/XAMPP/htdocs/padaidi/docker/entrypoint.sh)**:
   - Mengatur izin direktori penyimpanan, menjalankan migrasi database otomatis (`AUTO_MIGRATE=true`), seeder unit armada otomatis (`AUTO_SEED=true`), dan pemanasan cache (`config:cache`, `route:cache`, `view:cache`).
6. **[`cloudbuild.yaml`](file:///c:/XAMPP/htdocs/padaidi/cloudbuild.yaml)**:
   - Pipeline otomatis Google Cloud Build untuk build image dan deploy ke Cloud Run.
7. **[`deploy-gcp.ps1`](file:///c:/XAMPP/htdocs/padaidi/deploy-gcp.ps1)** & **[`deploy-gcp.bat`](file:///c:/XAMPP/htdocs/padaidi/deploy-gcp.bat)**:
   - Skrip pembantu interaktif untuk eksekusi deployment langsung dari Windows.

---

## 3. Langkah-Langkah Deployment ke Google Cloud

### Langkah 1: Pasang Google Cloud SDK (gcloud CLI) di Komputer

Jika Google Cloud SDK belum terpasang di komputer Windows Anda:
1. Unduh installer resmi Google Cloud SDK:  
   👉 **[Google Cloud SDK Installer (Windows)](https://dl.google.com/dl/cloudsdk/channels/rapid/GoogleCloudSDKInstaller.exe)**
2. Jalankan file `.exe` dan ikuti instruksi hingga selesai (centang opsi *"Run gcloud init"*).
3. Atau jalankan perintah berikut di PowerShell (sebagai Administrator):
   ```powershell
   (New-Object Net.WebClient).DownloadFile("https://dl.google.com/dl/cloudsdk/channels/rapid/GoogleCloudSDKInstaller.exe", "$env:Temp\GoogleCloudSDKInstaller.exe")
   Start-Process "$env:Temp\GoogleCloudSDKInstaller.exe"
   ```

*(Alternatif tanpa install software: Anda juga bisa membuka **[Google Cloud Shell](https://shell.cloud.google.com)** langsung di browser Chrome, clone repo ini, dan jalankan perintah deploy).*

---

### Langkah 2: Login dan Pilih Project Google Cloud

Buka terminal PowerShell atau Command Prompt baru, lalu jalankan:

```bash
# 1. Login ke akun Google Cloud Anda
gcloud auth login

# 2. Login Application Default Credentials
gcloud auth application-default login

# 3. Buat Project Baru (contoh: padaidi-car-rental) atau pilih yang sudah ada
gcloud projects create padaidi-car-rental --name="Padaidi Rent Car"

# 4. Set project aktif
gcloud config set project padaidi-car-rental
```

> **Catatan Akun Baru**: Jika project baru dibuat, pastikan Anda telah menautkan akun Billing (kartu kredit/debit atau uji coba gratis Google Cloud senilai $300) di [Google Cloud Console Billing](https://console.cloud.google.com/billing).

---

### Langkah 3: Aktifkan API Layanan Google Cloud

Aktifkan API Cloud Run, Cloud Build, dan Artifact Registry:

```bash
gcloud services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com
```

---

### Langkah 4: Jalankan Perintah Deploy (1-Perintah Selesai!)

Dari direktori proyek `c:\XAMPP\htdocs\padaidi`, cukup jalankan salah satu dari dua cara berikut:

#### Cara A: Menggunakan Skrip Otomatis
Cukup klik ganda file **`deploy-gcp.bat`** atau jalankan di PowerShell:
```powershell
.\deploy-gcp.ps1
```

#### Cara B: Menjalankan Perintah `gcloud` Langsung
```bash
gcloud run deploy padaidi-web \
  --source . \
  --region asia-southeast2 \
  --platform managed \
  --allow-unauthenticated \
  --port 8080 \
  --memory 512Mi \
  --cpu 1 \
  --min-instances 0 \
  --max-instances 10 \
  --set-env-vars "APP_NAME=Padaidi Rent Car,APP_ENV=production,APP_DEBUG=false,APP_TIMEZONE=Asia/Makassar,APP_LOCALE=id,AUTO_MIGRATE=true,AUTO_SEED=true,SESSION_DRIVER=database,CACHE_STORE=database,FILESYSTEM_DISK=public"
```

**Proses yang berjalan secara otomatis:**
1. Google Cloud Build mengompresi source code Laravel dan mengunggahnya ke Cloud Build.
2. Server Google Cloud meng-compile container menggunakan `Dockerfile`.
3. Container disimpan di Google Artifact Registry region Jakarta.
4. Cloud Run meluncurkan kontainer dan memberikan alamat URL HTTPS publik (contoh: `https://padaidi-web-xxxxxx-as.a.run.app`).

---

## 4. Konfigurasi Database di Google Cloud

Aplikasi Padaidi mendukung beberapa opsi database:

### Opsi 1: Google Cloud SQL MySQL (Direkomendasikan untuk Produksi Enterprise)
1. Buat instance MySQL di Google Cloud Console:
   ```bash
   gcloud sql instances create padaidi-mysql \
     --database-version=MYSQL_8_0 \
     --tier=db-f1-micro \
     --region=asia-southeast2 \
     --root-password="PasswordKuatAnda123!"
   ```
2. Buat database:
   ```bash
   gcloud sql databases create padaidi --instance=padaidi-mysql
   ```
3. Hubungkan ke Cloud Run dengan menambahkan argumen saat deploy:
   ```bash
   --add-cloudsql-instances=padaidi-car-rental:asia-southeast2:padaidi-mysql \
   --update-env-vars="DB_CONNECTION=mysql,DB_SOCKET=/cloudsql/padaidi-car-rental:asia-southeast2:padaidi-mysql,DB_DATABASE=padaidi,DB_USERNAME=root,DB_PASSWORD=PasswordKuatAnda123!"
   ```

### Opsi 2: Database MySQL Cloud Gratis / Serverless (Sangat Hemat Biaya)
Anda juga dapat menggunakan managed MySQL seperti [Aiven.io](https://aiven.io) (tersedia paket gratis) atau PlanetScale, lalu masukkan host dan kredensialnya ke variabel `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD`.

### Opsi 3: SQLite Cepat (Untuk Preview & Staging Instan)
Secara default, container Padaidi sudah dilengkapi driver `sqlite`. Anda dapat langsung menguji web tanpa perlu membuat server database terpisah.

---

## 5. Menghubungkan Custom Domain & SSL Gratis

Setelah service Cloud Run aktif:
1. Buka [Google Cloud Console > Cloud Run > Domain Mappings](https://console.cloud.google.com/run/domains).
2. Klik **Add Mapping**.
3. Pilih service `padaidi-web` dan masukkan nama domain Anda (misal: `padaidirentcar.com` atau `sewa.padaidirentcar.com`).
4. Google Cloud akan memberikan DNS Record (tipe `A` / `AAAA` atau `CNAME`).
5. Pasang DNS record tersebut di registrar domain Anda (Niagahoster, Domainesia, Cloudflare, IDwebhost, dll.).
6. Google Cloud akan otomatis menerbitkan **Sertifikat SSL/TLS resmi** dalam 15–30 menit.

---

## 6. Monitoring & Melihat Log Aplikasi

Untuk melihat log akses web dan error secara real-time:
```bash
gcloud run services logs tail padaidi-web --region asia-southeast2
```
Atau melalui peramban di [Google Cloud Logs Explorer](https://console.cloud.google.com/logs).

