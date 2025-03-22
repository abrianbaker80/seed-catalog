@echo off
REM Script to increment plugin version before pushing to GitHub

echo === Seed Catalog Version Management ===
echo.

REM Get the current version
for /f "tokens=*" %%a in ('findstr /C:"* Version:" seed-catalog.php') do set CURRENT_VERSION=%%a
echo Current version: %CURRENT_VERSION%
echo.

echo Running version increment...
"C:\laragon\bin\php\php-8.2.27-Win32-vs16-x64\php.exe" scripts\increment-version.php

echo.
echo === Process Complete ===
echo Don't forget to commit and push your changes!
echo.