<?php
/**
 * Script to increment plugin version number manually or during Git commits
 * 
 * @package Seed_Catalog
 */

// Load autoloader
require_once dirname(__FILE__) . '/../vendor/autoload.php';

// Load the version manager class
require_once dirname(__FILE__) . '/../includes/class-seed-catalog-version-manager.php';

// Initialize version manager with plugin file path
$plugin_file = dirname(__FILE__) . '/../seed-catalog.php';
$version_manager = new SeedCatalog\Seed_Catalog_Version_Manager($plugin_file);

// Always increment the version when this script is run
try {
    // Increment the version
    $new_version = $version_manager->increment_version();
    echo "Successfully incremented version to {$new_version}\n";
    exit(0);
} catch (Exception $e) {
    echo "Error incrementing version: " . $e->getMessage() . "\n";
    exit(1);
}