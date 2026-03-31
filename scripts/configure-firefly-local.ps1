param(
    [string]$EnvPath = ".env",
    [string]$FireflyUrl = "http://127.0.0.1:5000",
    [string]$ApiKey = "",
    [string]$Namespace = "default",
    [string]$TokenPool = "",
    [string]$TokenName = "KhaiTriCredit",
    [string]$PlatformIdentity = "platform",
    [string]$Signer = "",
    [string]$AuditTopic = "audit"
)

$resolvedEnvPath = Resolve-Path -LiteralPath $EnvPath -ErrorAction SilentlyContinue
if (-not $resolvedEnvPath) {
    throw "Khong tim thay file env: $EnvPath"
}

$path = $resolvedEnvPath.Path
$content = Get-Content -Raw -Encoding utf8 $path
$content = $content -replace "`r`n", "`n"

function Set-Or-AppendEnvVar {
    param(
        [string]$Body,
        [string]$Key,
        [string]$Value
    )

    $escapedKey = [regex]::Escape($Key)
    if ($Body -match "(?m)^$escapedKey=") {
        return [regex]::Replace($Body, "(?m)^$escapedKey=.*$", "$Key=$Value")
    }

    return ($Body.TrimEnd() + "`n$Key=$Value`n")
}

$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_URL' -Value $FireflyUrl
$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_API_KEY' -Value $ApiKey
$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_NAMESPACE' -Value $Namespace
$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_TOKEN_POOL' -Value $TokenPool
$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_TOKEN_NAME' -Value $TokenName
$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_PLATFORM_IDENTITY' -Value $PlatformIdentity
$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_SIGNER' -Value $Signer
$content = Set-Or-AppendEnvVar -Body $content -Key 'FIREFLY_AUDIT_TOPIC' -Value $AuditTopic

[System.IO.File]::WriteAllText($path, $content, [System.Text.UTF8Encoding]::new($false))

Write-Host "Da cap nhat FireFly config trong: $path" -ForegroundColor Green
Write-Host "" 
Write-Host "Gia tri hien tai:" -ForegroundColor Cyan
Write-Host "FIREFLY_URL=$FireflyUrl"
Write-Host "FIREFLY_NAMESPACE=$Namespace"
Write-Host "FIREFLY_TOKEN_POOL=$TokenPool"
Write-Host "FIREFLY_PLATFORM_IDENTITY=$PlatformIdentity"
Write-Host "FIREFLY_SIGNER=$Signer"
Write-Host "FIREFLY_AUDIT_TOPIC=$AuditTopic"
Write-Host ""
Write-Host "Buoc tiep theo:" -ForegroundColor Yellow
Write-Host "1. Dam bao FireFly stack dang chay"
Write-Host "2. Tao token pool va dien FIREFLY_TOKEN_POOL"
Write-Host "3. Map user/wallet sang FireFly identity that neu muon transfer token that su"
Write-Host "4. Chay: php artisan optimize:clear"