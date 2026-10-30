[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$gcloudBin = "$env:LOCALAPPDATA\Google\Cloud SDK\google-cloud-sdk\bin\gcloud.cmd"
if (-not (Test-Path $gcloudBin)) {
    $gcloudCmd = Get-Command gcloud -ErrorAction SilentlyContinue
    if ($gcloudCmd) {
        $gcloudBin = $gcloudCmd.Source
    }
}

if (-not (Test-Path $gcloudBin)) {
    Write-Host "[!] Google Cloud SDK belum terdeteksi di komputer ini." -ForegroundColor Red
    Write-Host "    Silakan unduh: https://cloud.google.com/sdk/docs/install-sdk"
    exit 1
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  PADAIDI RENT CAR - GOOGLE CLOUD RUN DEPLOYMENT WIZARD" -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Autentikasi
$authAccount = (& $gcloudBin auth list --filter="status:ACTIVE" --format="value(account)" 2>$null)
if (-not $authAccount) {
    Write-Host ""
    Write-Host "[*] Belum ada akun Google Cloud yang aktif." -ForegroundColor Yellow
    Write-Host "    Membuka browser untuk login ke Google Cloud..." -ForegroundColor Cyan
    & $gcloudBin auth login
    $authAccount = (& $gcloudBin auth list --filter="status:ACTIVE" --format="value(account)" 2>$null)
}
Write-Host "[OK] Terautentikasi sebagai: $authAccount" -ForegroundColor Green

# 2. Cek dan pilih Project ID
$currentProject = (& $gcloudBin config get-value project 2>$null)
if (-not $currentProject -or $currentProject -eq "(unset)") {
    Write-Host ""
    Write-Host "Daftar Project Google Cloud yang tersedia:" -ForegroundColor Yellow
    & $gcloudBin projects list
    Write-Host ""
    $projectId = Read-Host "Masukkan Project ID yang ingin digunakan"
    & $gcloudBin config set project $projectId
    $currentProject = $projectId
}
Write-Host "[OK] Menggunakan GCP Project: $currentProject" -ForegroundColor Green

# 3. Tentukan Region (Jakarta)
$region = "asia-southeast2"
Write-Host "[OK] Region: $region (Jakarta, Indonesia)" -ForegroundColor Green

# 4. Aktifkan Layanan Google Cloud API
Write-Host ""
Write-Host "[*] Mengaktifkan Cloud Run, Cloud Build, dan Artifact Registry..." -ForegroundColor Yellow
& $gcloudBin services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com --project=$currentProject

# 5. Siapkan APP_KEY Laravel
$appKey = & "C:\xampp\php\php.exe" artisan key:generate --show 2>$null
if (-not $appKey) {
    $appKey = "base64:c5L2n3padaidiSecretKeyProductionRun123456="
}

# 6. Deploy ke Cloud Run
Write-Host ""
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  MEMULAI PROSES BUILD DAN DEPLOY KE CLOUD RUN..." -ForegroundColor Yellow
Write-Host "  (Proses ini berjalan di cloud, sekitar 2-3 menit)" -ForegroundColor Gray
Write-Host "==========================================================" -ForegroundColor Cyan

$envVars = @(
    "APP_NAME=Padaidi Rent Car",
    "APP_ENV=production",
    "APP_DEBUG=false",
    "APP_KEY=$appKey",
    "APP_TIMEZONE=Asia/Makassar",
    "APP_LOCALE=id",
    "AUTO_MIGRATE=true",
    "AUTO_SEED=true",
    "SESSION_DRIVER=database",
    "CACHE_STORE=database",
    "FILESYSTEM_DISK=public"
) -join ","

$deployArgs = @(
    "run", "deploy", "padaidi-web",
    "--source", ".",
    "--region", $region,
    "--project", $currentProject,
    "--platform", "managed",
    "--allow-unauthenticated",
    "--port", "8080",
    "--memory", "512Mi",
    "--cpu", "1",
    "--min-instances", "0",
    "--max-instances", "10",
    "--set-env-vars", $envVars
)

& $gcloudBin @deployArgs

Write-Host ""
Write-Host "==========================================================" -ForegroundColor Green
Write-Host "  DEPLOYMENT SELESAI!" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
$serviceUrl = & $gcloudBin run services describe padaidi-web --platform=managed --region=$region --project=$currentProject --format="value(status.url)"
Write-Host "Website Padaidi Rent Car kini aktif di:" -ForegroundColor Yellow
Write-Host "-> $serviceUrl (HTTPS Aktif)" -ForegroundColor Cyan
Write-Host ""
