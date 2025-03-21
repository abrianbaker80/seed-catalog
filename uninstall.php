<?php
/**
 * Uninstaller for the Seed Catalog plugin.
 * This file will be triggered automatically when the plugin is deleted through WordPress.
 *
 * @package Seed_Catalog
 */

// If uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Define constants needed by uninstaller
if (!defined('SEED_CATALOG_PLUGIN_DIR')) {
    define('SEED_CATALOG_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Load the uninstaller class
require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-uninstaller.php';

// Run the uninstaller
try {
    Seed_Catalog_Uninstaller::uninstall();
} catch (Exception $e) {
    // Log uninstall errors
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Seed Catalog Plugin Uninstall Error: ' . $e->getMessage());
    }
}