param (
    [string]$OutputDir = "$PSScriptRoot\baseline",
    [string]$BaseUrl = "http://127.0.0.1:8000"
)

$ChromePath = "C:\Program Files\Google\Chrome\Application\chrome.exe"
if (-not (Test-Path $ChromePath)) {
    Write-Error "Google Chrome not found at $ChromePath"
    exit 1
}

if (-not (Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
}

$Viewports = @(
    @{ Name = "desktop-1920"; Width = 1920; Height = 1080; Category = "desktop" },
    @{ Name = "desktop-1440"; Width = 1440; Height = 900;  Category = "desktop" },
    @{ Name = "desktop-1280"; Width = 1280; Height = 800;  Category = "desktop" },
    @{ Name = "tablet-768";   Width = 768;  Height = 1024; Category = "tablet" },
    @{ Name = "mobile-430";   Width = 430;  Height = 932;  Category = "mobile" },
    @{ Name = "mobile-390";   Width = 390;  Height = 844;  Category = "mobile" },
    @{ Name = "mobile-360";   Width = 360;  Height = 800;  Category = "mobile" }
)

$Pages = @(
    @{ Slug = "01-home"; Path = "/" },
    @{ Slug = "02-armada"; Path = "/armada" },
    @{ Slug = "03-detail-mobil"; Path = "/mobil/toyota-agya" },
    @{ Slug = "04-syarat-sewa"; Path = "/syarat-sewa" },
    @{ Slug = "05-kontak"; Path = "/kontak" },
    @{ Slug = "06-cek-pesanan"; Path = "/cek-pesanan" },
    @{ Slug = "07-pesan-mobil"; Path = "/pesan/toyota-agya" },
    @{ Slug = "08-detail-pesanan"; Path = "/pesanan/PDD-CGSYDG" },
    @{ Slug = "09-member-login"; Path = "/member/login" },
    @{ Slug = "10-member-register"; Path = "/member/register" }
)

Write-Host "===================================================="
Write-Host " Padaidi Rent Car - Baseline Visual Capture Suite   "
Write-Host " Output Directory: $OutputDir"
Write-Host " Base URL: $BaseUrl"
Write-Host " Total captures: ($($Pages.Count) pages x $($Viewports.Count) viewports = $($Pages.Count * $Viewports.Count))"
Write-Host "===================================================="

$Total = $Pages.Count * $Viewports.Count
$Current = 0

foreach ($page in $Pages) {
    foreach ($vp in $Viewports) {
        $Current++
        $FileName = "$($page.Slug)_$($vp.Name).png"
        $FilePath = Join-Path $OutputDir $FileName
        $TargetUrl = "$BaseUrl$($page.Path)"

        Write-Host "[$Current/$Total] Capturing $($page.Slug) @ $($vp.Name) ($($vp.Width)x$($vp.Height))..."

        $Args = @(
            "--headless",
            "--disable-gpu",
            "--hide-scrollbars",
            "--window-size=$($vp.Width),$($vp.Height)",
            "--screenshot=`"$FilePath`"",
            "`"$TargetUrl`""
        )

        $Process = Start-Process -FilePath $ChromePath -ArgumentList $Args -Wait -PassThru -NoNewWindow
        
        if (Test-Path $FilePath) {
            $SizeKb = [Math]::Round((Get-Item $FilePath).Length / 1024, 1)
            Write-Host "  -> Saved: $FileName ($SizeKb KB)" -ForegroundColor Green
        } else {
            Write-Host "  -> FAILED to capture: $FileName" -ForegroundColor Red
        }
    }
}

Write-Host "===================================================="
Write-Host " Baseline capture completed successfully!          "
Write-Host "===================================================="
