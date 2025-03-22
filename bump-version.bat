@echo off
REM Script to increment plugin version before pushing to GitHub

echo === Seed Catalog Version Management ===
echo.

REM Get the current version from both file sources
for /f "tokens=* usebackq" %%a in (`findstr /C:"* Version:" seed-catalog.php`) do set CURRENT_VERSION_HEADER=%%a
for /f "tokens=* usebackq" %%a in (`findstr /C:"SEED_CATALOG_VERSION" seed-catalog.php ^| findstr /V "define.*SEED_CATALOG_VERSION"`) do set CURRENT_VERSION_CONST=%%a

echo Current version in header: %CURRENT_VERSION_HEADER%
echo Current version constant: %CURRENT_VERSION_CONST%
echo.

echo Running version increment...
"C:\laragon\bin\php\php-8.2.27-Win32-vs16-x64\php.exe" scripts\increment-version.php

echo.
REM Get the new version after increment
for /f "tokens=* usebackq" %%a in (`findstr /C:"* Version:" seed-catalog.php`) do set NEW_VERSION_HEADER=%%a
echo New version: %NEW_VERSION_HEADER%
echo.

echo === Process Complete ===
echo Ready to commit! Use these commands:
echo   git add .
echo   git commit -m "Bump version: %NEW_VERSION_HEADER:~11%"
echo   git push origin master
echo.