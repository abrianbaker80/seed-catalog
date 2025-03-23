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
$newVersionValue = $newVersionValue.Trim()
Write-Host "New version: $newVersionValue" -ForegroundColor Green
Write-Host ""

# Prompt for confirmation with clear Y/N options
Write-Host "Do you want to commit, tag and push these changes? (Y/N)" -ForegroundColor Cyan
$confirmation = Read-Host

# Convert input to uppercase and check if it's "Y"
if ($confirmation.ToUpper() -eq "Y") {
    Write-Host "Committing changes..." -ForegroundColor Green
    git add .
    git commit -m "Bump version: $newVersionValue"
    
    Write-Host "Creating tag v$newVersionValue..." -ForegroundColor Green
    git tag "v$newVersionValue"
    
    Write-Host "Pushing changes and tags to remote repository..." -ForegroundColor Green
    git push origin master
    git push origin "v$newVersionValue"
    
    Write-Host "=== Process Complete ===" -ForegroundColor Cyan
    Write-Host "✓ Version bumped to $newVersionValue" -ForegroundColor Green
    Write-Host "✓ Changes committed and pushed" -ForegroundColor Green
    Write-Host "✓ Tag v$newVersionValue created and pushed" -ForegroundColor Green
    Write-Host ""
    Write-Host "GitHub Actions will now automatically create a release." -ForegroundColor Yellow
} else {
    Write-Host "=== Process Completed Without Pushing ===" -ForegroundColor Cyan
    Write-Host "Version has been bumped to $newVersionValue but changes have not been committed." -ForegroundColor Yellow
    Write-Host "To commit manually, use these commands:" -ForegroundColor White
    Write-Host "  git add ." -ForegroundColor Yellow
    Write-Host "  git commit -m ""Bump version: $newVersionValue""" -ForegroundColor Yellow  
    Write-Host "  git tag ""v$newVersionValue""" -ForegroundColor Yellow
    Write-Host "  git push origin master" -ForegroundColor Yellow
    Write-Host "  git push origin ""v$newVersionValue""" -ForegroundColor Yellow
    Write-Host ""
}