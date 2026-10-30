param (
    [string]$BaseUrl = "http://127.0.0.1:8000"
)

$ChromePath = "C:\Program Files\Google\Chrome\Application\chrome.exe"
if (-not (Test-Path $ChromePath)) {
    Write-Error "Chrome not found at $ChromePath"
    exit 1
}

$BaselineDir = "$PSScriptRoot\baseline"
$DesktopDir = "$PSScriptRoot\phase3-desktop"
$MobileDir = "$PSScriptRoot\phase3-mobile"
$LandscapeDir = "$PSScriptRoot\phase3-landscape"

foreach ($dir in @($DesktopDir, $MobileDir, $LandscapeDir)) {
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Force -Path $dir | Out-Null }
}

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

$LandscapeViewports = @(
    @{ Name = "landscape-844x390"; Width = 844; Height = 390 },
    @{ Name = "landscape-932x430"; Width = 932; Height = 430 }
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
Write-Host " Phase 3 Final Verification & Audit Suite           "
Write-Host "===================================================="

# 1. Desktop verification
Write-Host "`n--- Step 1: Desktop Baseline Comparison ---"
$DesktopPass = 0
foreach ($p in $Pages) {
    foreach ($vp in $DesktopViewports) {
        $FileName = "$($p.Slug)_$($vp.Name).png"
        $OutPath = Join-Path $DesktopDir $FileName
        $BasePath = Join-Path $BaselineDir $FileName
        $Url = "$BaseUrl$($p.Path)"

        $Args = @("--headless", "--disable-gpu", "--hide-scrollbars", "--window-size=$($vp.Width),$($vp.Height)", "--screenshot=`"$OutPath`"", "`"$Url`"")
        Start-Process -FilePath $ChromePath -ArgumentList $Args -Wait -NoNewWindow | Out-Null

        if ((Test-Path $BasePath) -and (Test-Path $OutPath)) {
            $BaseSize = (Get-Item $BasePath).Length
            $NewSize = (Get-Item $OutPath).Length
            $DiffPct = [Math]::Round([Math]::Abs($NewSize - $BaseSize) / [Math]::Max($BaseSize, 1) * 100, 2)
            Write-Host " $($FileName): Base=$BaseSize vs New=$NewSize (Diff: $DiffPct%)"
            $DesktopPass++
        }
    }
}

# 2. Mobile captures
Write-Host "`n--- Step 2: Mobile Captures ---"
$MobilePass = 0
foreach ($p in $Pages) {
    foreach ($vp in $MobileViewports) {
        $FileName = "$($p.Slug)_$($vp.Name).png"
        $OutPath = Join-Path $MobileDir $FileName
        $Url = "$BaseUrl$($p.Path)"

        $Args = @("--headless", "--disable-gpu", "--hide-scrollbars", "--window-size=$($vp.Width),$($vp.Height)", "--screenshot=`"$OutPath`"", "`"$Url`"")
        Start-Process -FilePath $ChromePath -ArgumentList $Args -Wait -NoNewWindow | Out-Null

        if (Test-Path $OutPath) {
            $SizeKb = [Math]::Round((Get-Item $OutPath).Length / 1024, 1)
            Write-Host " [OK] Saved Mobile: $FileName ($SizeKb KB)"
            $MobilePass++
        }
    }
}

# 3. Landscape orientation
Write-Host "`n--- Step 3: Landscape Mode Captures ---"
$LandscapePass = 0
foreach ($p in $Pages | Select-Object -First 3) {
    foreach ($vp in $LandscapeViewports) {
        $FileName = "$($p.Slug)_$($vp.Name).png"
        $OutPath = Join-Path $LandscapeDir $FileName
        $Url = "$BaseUrl$($p.Path)"

        $Args = @("--headless", "--disable-gpu", "--hide-scrollbars", "--window-size=$($vp.Width),$($vp.Height)", "--screenshot=`"$OutPath`"", "`"$Url`"")
        Start-Process -FilePath $ChromePath -ArgumentList $Args -Wait -NoNewWindow | Out-Null

        if (Test-Path $OutPath) {
            $SizeKb = [Math]::Round((Get-Item $OutPath).Length / 1024, 1)
            Write-Host " [OK] Saved Landscape: $FileName ($SizeKb KB)"
            $LandscapePass++
        }
    }
}

Write-Host "`n===================================================="
Write-Host " Phase 3 Verification Complete!"
Write-Host " Desktop checks: $DesktopPass / 30"
Write-Host " Mobile captures: $MobilePass / 30"
Write-Host " Landscape captures: $LandscapePass / 6"
Write-Host "===================================================="

