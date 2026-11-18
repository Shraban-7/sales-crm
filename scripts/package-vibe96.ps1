#Requires -Version 5.1
<#
.SYNOPSIS
    Packages the Sales CRM repository into dist/sales-crm-vibe96.zip for Vibe96 marketplace upload.
.DESCRIPTION
    1. Compiles production frontend assets via `npm run build` into `public/build/`.
    2. Creates a clean zip archive at `dist/sales-crm-vibe96.zip` excluding `.git`, `.env`,
       `node_modules`, `vendor`, `dist`, `storage/logs/*.log`, `storage/app/private/*`,
       `storage/app/mpdf/*`, `database/*.sqlite`, and temporary runtime caches.
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $RepoRoot

try {
    Write-Host "==> [1/3] Building production assets (npm run build)..." -ForegroundColor Cyan
    & npm run build
    if ($LASTEXITCODE -ne 0) {
        throw "npm run build failed with exit code $LASTEXITCODE"
    }

    $ManifestPath = Join-Path $RepoRoot 'public\build\manifest.json'
    if (-not (Test-Path -LiteralPath $ManifestPath)) {
        throw "Expected Vite manifest not found at $ManifestPath"
    }

    Write-Host "==> [2/3] Preparing dist/sales-crm-vibe96.zip..." -ForegroundColor Cyan
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
        '^CLAUDE\.md$',
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
    Write-Host "==> [3/3] Packaged $IncludedCount files into dist/sales-crm-vibe96.zip ($SizeKB KB)" -ForegroundColor Green
}
finally {
    Pop-Location
}
