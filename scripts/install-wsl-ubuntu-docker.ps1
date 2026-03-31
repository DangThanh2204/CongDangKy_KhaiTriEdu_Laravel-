param(
    [string]$UbuntuDistro = 'Ubuntu',
    [switch]$SkipDocker
)

$ErrorActionPreference = 'Stop'

function Write-Step {
    param([string]$Message)
    Write-Host "`n==> $Message" -ForegroundColor Cyan
}

function Get-FeatureState {
    param([string]$FeatureName)
    $output = & dism.exe /online /Get-FeatureInfo /FeatureName:$FeatureName
    if ($LASTEXITCODE -ne 0) {
        throw "Khong doc duoc trang thai feature: $FeatureName"
    }

    $stateLine = $output | Where-Object { $_ -match '^State\s*:\s*' } | Select-Object -First 1
    if (-not $stateLine) {
        return 'Unknown'
    }

    return ($stateLine -replace '^State\s*:\s*', '').Trim()
}

function Ensure-FeatureEnabled {
    param([string]$FeatureName)

    $state = Get-FeatureState -FeatureName $FeatureName
    Write-Host "$FeatureName: $state"

    if ($state -eq 'Enabled') {
        return $false
    }

    Write-Step "Bat feature $FeatureName"
    & dism.exe /online /enable-feature /featurename:$FeatureName /all /norestart
    if ($LASTEXITCODE -ne 0) {
        throw "Khong bat duoc feature $FeatureName"
    }

    return $true
}

function Command-Exists {
    param([string]$Name)
    return [bool](Get-Command $Name -ErrorAction SilentlyContinue)
}

Write-Host 'Script nay can chay trong PowerShell voi quyen Administrator.' -ForegroundColor Yellow

$restartNeeded = $false
$restartNeeded = (Ensure-FeatureEnabled -FeatureName 'Microsoft-Windows-Subsystem-Linux') -or $restartNeeded
$restartNeeded = (Ensure-FeatureEnabled -FeatureName 'VirtualMachinePlatform') -or $restartNeeded

if ($restartNeeded) {
    Write-Host "`nDa bat xong feature nen WSL. Hay restart Windows, mo lai PowerShell Administrator, roi chay lai script nay." -ForegroundColor Green
    exit 0
}

Write-Step 'Cau hinh WSL2 lam mac dinh'
try {
    & wsl.exe --set-default-version 2
} catch {
    Write-Host 'Khong set duoc default version ngay luc nay. Neu may vua moi reboot, thu chay lai script them 1 lan nua.' -ForegroundColor Yellow
}

Write-Step "Cai distro $UbuntuDistro"
try {
    & wsl.exe --install -d $UbuntuDistro
} catch {
    Write-Host 'Lenh cai Ubuntu tra ve loi. Neu Ubuntu da duoc cai roi, ban co the bo qua buoc nay.' -ForegroundColor Yellow
}

Write-Step 'Kiem tra trang thai WSL'
try {
    & wsl.exe --status
} catch {
    Write-Host 'Chua doc duoc wsl --status. Co the can reboot them 1 lan sau khi cai Ubuntu.' -ForegroundColor Yellow
}

if (-not $SkipDocker) {
    if (-not (Command-Exists -Name 'winget')) {
        Write-Host 'Khong tim thay winget. Bo qua cai Docker Desktop tu dong.' -ForegroundColor Yellow
    } else {
        Write-Step 'Cai Docker Desktop'
        & winget install -e --id Docker.DockerDesktop --accept-source-agreements --accept-package-agreements
        if ($LASTEXITCODE -ne 0) {
            Write-Host 'Cai Docker Desktop khong hoan tat tu dong. Ban co the cai thu cong tu https://www.docker.com/products/docker-desktop/' -ForegroundColor Yellow
        }
    }
}

Write-Host "`nHoan tat cac buoc tu dong co the lam duoc." -ForegroundColor Green
Write-Host 'Buoc tiep theo:' -ForegroundColor Cyan
Write-Host '1. Restart neu Windows yeu cau'
Write-Host '2. Mo Docker Desktop va bat WSL2 integration voi Ubuntu'
Write-Host '3. Mo Ubuntu lan dau va tao user Linux'
Write-Host '4. Cai FireFly CLI trong Ubuntu'
Write-Host '5. Chay ff init fabric va ff start <stack-name>'