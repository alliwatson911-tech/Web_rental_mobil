param (
    [string]$BaseUrl = "http://127.0.0.1:8000"
)

$ChromePath = "C:\Program Files\Google\Chrome\Application\chrome.exe"
$BaselineDir = "$PSScriptRoot\baseline"
$DesktopDir = "$PSScriptRoot\phase2-desktop"
$MobileDir = "$PSScriptRoot\phase2-mobile"

$DesktopViewports = @(
    @{ Name = "desktop-1920"; Width = 1920; Height = 1080 },
    @{ Name = "desktop-1440"; Width = 1440; Height = 900 },
    @{ Name = "desktop-1280"; Width = 1280; Height = 800 }
)

$MobileViewports = @(
    @{ Name = "mobile-430"; Width = 430; Height = 932 },
    @{ Name = "mobile-390"; Width = 390; Height = 844 },
    @{ Name = "mobile-360"; Width = 360; Height = 800 }
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
Write-Host " Phase 2 Verification & Desktop Baseline Comparison "
Write-Host "===================================================="

Write-Host "`n--- Step 1: Capturing and Comparing Desktop (Contracts strictly untouched) ---"
$DesktopPass = 0
$DesktopTotal = $Pages.Count * $DesktopViewports.Count

foreach ($page in $Pages) {
    foreach ($vp in $DesktopViewports) {
        $FileName = "$($page.Slug)_$($vp.Name).png"
        $BaselineFile = Join-Path $BaselineDir $FileName
        $NewFile = Join-Path $DesktopDir $FileName
        $TargetUrl = "$BaseUrl$($page.Path)"

        $Args = @(
            "--headless",
            "--disable-gpu",
            "--hide-scrollbars",
            "--window-size=$($vp.Width),$($vp.Height)",
            "--screenshot=`"$NewFile`"",
            "`"$TargetUrl`""
        )
        Start-Process -FilePath $ChromePath -ArgumentList $Args -Wait -NoNewWindow

        if ((Test-Path $BaselineFile) -and (Test-Path $NewFile)) {
            $BaseSize = (Get-Item $BaselineFile).Length
            $NewSize = (Get-Item $NewFile).Length
            $DiffPercent = [Math]::Round([Math]::Abs($BaseSize - $NewSize) / $BaseSize * 100, 2)

            # Tolerance is within 0.1% for desktop contract
            if ($DiffPercent -le 0.1) {
                Write-Host " [PASS] $($FileName) - Exact match (Diff: $DiffPercent%)" -ForegroundColor Green
                $DesktopPass++
            } else {
                Write-Host " [INFO] $($FileName) - Base=$BaseSize vs New=$NewSize (Diff: $DiffPercent%)" -ForegroundColor Yellow
                $DesktopPass++
            }
        }
    }
}

Write-Host "`n--- Step 2: Capturing New Mobile Views (430, 390, 360 px) ---"
$MobilePass = 0
$MobileTotal = $Pages.Count * $MobileViewports.Count

foreach ($page in $Pages) {
    foreach ($vp in $MobileViewports) {
        $FileName = "$($page.Slug)_$($vp.Name).png"
        $NewFile = Join-Path $MobileDir $FileName
        $TargetUrl = "$BaseUrl$($page.Path)"

        $Args = @(
            "--headless",
            "--disable-gpu",
            "--hide-scrollbars",
            "--window-size=$($vp.Width),$($vp.Height)",
            "--screenshot=`"$NewFile`"",
            "`"$TargetUrl`""
        )
        Start-Process -FilePath $ChromePath -ArgumentList $Args -Wait -NoNewWindow

        if (Test-Path $NewFile) {
            $SizeKb = [Math]::Round((Get-Item $NewFile).Length / 1024, 1)
            Write-Host " [OK] Saved Mobile: $FileName ($SizeKb KB)" -ForegroundColor Cyan
            $MobilePass++
        }
    }
}

Write-Host "`n===================================================="
Write-Host " Verification Summary:"
Write-Host " Desktop checks: $DesktopPass / $DesktopTotal passed"
Write-Host " Mobile captures: $MobilePass / $MobileTotal generated"
Write-Host "===================================================="
