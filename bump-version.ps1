# Script to increment plugin version before pushing to GitHub

Write-Host "=== Seed Catalog Version Management ===" -ForegroundColor Cyan
Write-Host ""

# Get the current version from both file sources
$currentVersionHeader = Get-Content -Path "seed-catalog.php" | Select-String -Pattern "\* Version:" | Select-Object -First 1
$currentVersionConstant = Get-Content -Path "seed-catalog.php" | Select-String -Pattern "define\('SEED_CATALOG_VERSION'" | Select-Object -First 1

Write-Host "Current version in header: $currentVersionHeader" -ForegroundColor Yellow
Write-Host "Current version constant: $currentVersionConstant" -ForegroundColor Yellow
Write-Host ""

Write-Host "Running version increment..." -ForegroundColor Green
& "C:\laragon\bin\php\php-8.2.27-Win32-vs16-x64\php.exe" scripts\increment-version.php

Write-Host ""
# Get the new version after increment
$newVersionHeader = Get-Content -Path "seed-catalog.php" | Select-String -Pattern "\* Version:" | Select-Object -First 1
$newVersionValue = $newVersionHeader -replace '.*Version:\s*', ''
Write-Host "New version: $newVersionHeader" -ForegroundColor Green
Write-Host ""

Write-Host "=== Process Complete ===" -ForegroundColor Cyan
Write-Host "Ready to commit! Use these commands:" -ForegroundColor White
Write-Host "  git add ." -ForegroundColor Yellow
Write-Host "  git commit -m ""Bump version: $newVersionValue""" -ForegroundColor Yellow  
Write-Host "  git push origin master" -ForegroundColor Yellow
Write-Host ""