#Requires -Version 5.1
<#
.SYNOPSIS
    Packages the Sale Fusion CRM repository into dist/sales-crm-vibe96.zip for Vibe96 submission.
.DESCRIPTION
    1. Verifies frontend assets build cleanly via `npm run build` (without bundling `public/build/` in the ZIP).
    2. Verifies all Vibe96 Must-Have & Nice-to-Have files exist before packaging.
    3. Creates a clean zip archive at `dist/sales-crm-vibe96.zip` excluding `.git`, `.gemini`, `.env`,
       `node_modules`, `vendor`, `public/build`, `dist`, `storage/logs/*.log`, `storage/app/private/*`,
       `storage/app/mpdf/*`, `database/*.sqlite`, and temporary runtime caches.
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $RepoRoot

try {
    Write-Host "==> [1/4] Verifying frontend build (npm run build)..." -ForegroundColor Cyan
    & npm run build
    if ($LASTEXITCODE -ne 0) {
        throw "npm run build failed with exit code $LASTEXITCODE"
    }

    Write-Host "==> [2/4] Checking required Vibe96 submission files..." -ForegroundColor Cyan
    $RequiredFiles = @(
        'README.md',
        'HowToSetup.md',
        '.env.example',
        'database/schema.sql',
        'database/seed.sql',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'routes/web.php',
        'routes/api.php',
        'CHANGELOG.md',
        'CLAUDE.md',
        'docker-compose.yml',
        'LICENSE-NOTE.md'
    )

    foreach ($Req in $RequiredFiles) {
        $FullPath = Join-Path $RepoRoot ($Req -replace '/', '\')
        if (-not (Test-Path -LiteralPath $FullPath)) {
            throw "Required Vibe96 file missing: $Req"
        }
    }

    Write-Host "==> [3/4] Preparing dist/sales-crm-vibe96.zip..." -ForegroundColor Cyan
    $DistDir = Join-Path $RepoRoot 'dist'
    if (-not (Test-Path -LiteralPath $DistDir)) {
        New-Item -ItemType Directory -Path $DistDir -Force | Out-Null
    }

    $ZipPath = Join-Path $DistDir 'sales-crm-vibe96.zip'
    if (Test-Path -LiteralPath $ZipPath) {
        Remove-Item -LiteralPath $ZipPath -Force
    }

    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem

    $ExcludedPatterns = @(
        '^\.git(/|$)',
        '^\.gemini(/|$)',
        '^\.idea(/|$)',
        '^\.vscode(/|$)',
        '^\.cursor(/|$)',
        '^\.codex(/|$)',
        '^\.zed(/|$)',
        '^\.phpunit\.cache(/|$)',
        '^\.phpunit\.result\.cache$',
        '^node_modules(/|$)',
        '^vendor(/|$)',
        '^dist(/|$)',
        '^public/build(/|$)',
        '^public/hot$',
        '^public/storage(/|$)',
        '^storage/logs/.*\.log$',
        '^storage/pail(/|$)',
        '^storage/app/private/.*',
        '^storage/app/mpdf/.*',
        '^storage/framework/views/.*\.php$',
        '^storage/framework/testing/disks/.*',
        '^storage/framework/sessions/(?!\.gitignore$).*',
        '^storage/framework/cache/data/(?!\.gitignore$).*',
        '^database/.*\.sqlite(-journal|-shm|-wal)?$',
        '.*\.log$',
        '^AGENTS\.md$',
        '^GEMINI\.md$',
        '^project-discovery-report\.md$',
        '^report\.md$',
        '^docs/CHANGELOG\.md$',
        '^docs/prompts(/|$)'
    )

    $AllFiles = Get-ChildItem -LiteralPath $RepoRoot -Recurse -Force -File
    $IncludedCount = 0

    $ZipStream = [System.IO.File]::Open($ZipPath, [System.IO.FileMode]::Create)
    try {
        $Archive = New-Object System.IO.Compression.ZipArchive($ZipStream, [System.IO.Compression.ZipArchiveMode]::Create, $false)
        try {
            foreach ($File in $AllFiles) {
                $RelPath = $File.FullName.Substring($RepoRoot.Length).TrimStart('\', '/') -replace '\\', '/'

                # Exclude .env and any .env.* except .env.example
                if ($RelPath -match '^\.env(\..+)?$' -and $RelPath -ne '.env.example') {
                    continue
                }

                $Skip = $false
                foreach ($Pattern in $ExcludedPatterns) {
                    if ($RelPath -match $Pattern) {
                        $Skip = $true
                        break
                    }
                }

                if ($Skip) {
                    continue
                }

                [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                    $Archive,
                    $File.FullName,
                    $RelPath,
                    [System.IO.Compression.CompressionLevel]::Optimal
                ) | Out-Null
                $IncludedCount++
            }
        }
        finally {
            $Archive.Dispose()
        }
    }
    finally {
        $ZipStream.Dispose()
    }

    $ZipInfo = Get-Item -LiteralPath $ZipPath
    $SizeKB = [math]::Round($ZipInfo.Length / 1KB, 2)
    Write-Host "==> [4/4] Packaged $IncludedCount files into dist/sales-crm-vibe96.zip ($SizeKB KB)" -ForegroundColor Green
}
finally {
    Pop-Location
}
