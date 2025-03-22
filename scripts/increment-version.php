<?php
/**
 * Script to increment plugin version number during Git commits
 * 
 * @package Seed_Catalog
 */

// Load WordPress core functions
require_once dirname(__FILE__) . '/../vendor/autoload.php';

// Load the version manager class
require_once dirname(__FILE__) . '/../includes/class-seed-catalog-version-manager.php';

// Initialize version manager with plugin file path
$version_manager = new SeedCatalog\Seed_Catalog_Version_Manager(dirname(__FILE__) . '/../seed-catalog.php');

// Determine if we're running from CLI or Git hook
$is_cli = (php_sapi_name() === 'cli');
$changed_files = shell_exec('git diff --cached --name-only') ?: '';

// Always increment when running directly from CLI, otherwise check for changed files
if ($is_cli || preg_match('/\.(php|js|css)$/', $changed_files)) {
    try {
        // Increment the version
        $new_version = $version_manager->increment_version();
        
        // Add the version file back to Git staging if in Git hook context
        if (!$is_cli) {
            shell_exec('git add seed-catalog.php');
        }
        
        echo "Successfully incremented version to {$new_version}\n";
        exit(0);
    } catch (Exception $e) {
        echo "Error incrementing version: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "No PHP, JS, or CSS files were modified.\n";
    exit(0);
}