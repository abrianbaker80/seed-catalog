# Script to increment plugin version before pushing to GitHub

Write-Host "=== Seed Catalog Version Management ===" -ForegroundColor Cyan
Write-Host ""

# Get the current version
$content = Get-Content -Path "seed-catalog.php" | Select-String -Pattern "\* Version:"
Write-Host "Current version: $content" -ForegroundColor Yellow
Write-Host ""

Write-Host "Running version increment..." -ForegroundColor Green
& "C:\laragon\bin\php\php-8.2.27-Win32-vs16-x64\php.exe" scripts\increment-version.php

Write-Host ""
Write-Host "=== Process Complete ===" -ForegroundColor Cyan
Write-Host "Don't forget to commit and push your changes!"
Write-Host ""