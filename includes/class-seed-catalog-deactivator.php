<?php
/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */

namespace SeedCatalog;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */
class Seed_Catalog_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Clean up temporary data and flush rewrite rules.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        self::cleanup_temporary_data();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Clean up temporary data.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function cleanup_temporary_data() {
        // Remove any transients
        delete_transient('seed_catalog_cache');
        delete_transient('seed_catalog_api_test_results');
        
        // Other cleanup as needed
        // Note: We don't remove settings or content - that's for uninstall
    }
}