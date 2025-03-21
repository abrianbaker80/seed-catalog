<?php
/**
 * Handles plugin upgrades between versions.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */

namespace SeedCatalog;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin upgrades.
 *
 * This class defines all code necessary to run during the plugin's upgrade
 * process when the version number changes.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */
class Seed_Catalog_Upgrader {

    /**
     * Upgrade the plugin from a previous version.
     *
     * Runs version-specific upgrade routines based on the previous version.
     *
     * @since    1.0.0
     * @param    string    $previous_version    The previous version of the plugin.
     * @param    string    $current_version     The current version of the plugin.
     * @throws   \Exception                      If upgrade fails.
     */
    public static function upgrade($previous_version, $current_version) {
        // Run specific upgrade routines based on the version
        try {
            // If this is a fresh install, no updates needed
            if ($previous_version === '0.0.0') {
                return;
            }
            
            // Version 1.0.0 - 1.1.0 upgrade
            if (version_compare($previous_version, '1.1.0', '<') && 
                version_compare($current_version, '1.1.0', '>=')) {
                self::upgrade_to_1_1_0();
            }
            
            // Additional version upgrades can be added as needed
            
            // Always flush rewrite rules after upgrade
            flush_rewrite_rules();
            
            // Log the upgrade
            self::log_upgrade($previous_version, $current_version);
            
        } catch (Exception $e) {
            // Log details and rethrow
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    'Seed Catalog upgrade error from %s to %s: %s', 
                    $previous_version, 
                    $current_version, 
                    $e->getMessage()
                ));
            }
            
            throw $e;
        }
    }
    
    /**
     * Upgrade to version 1.1.0
     *
     * @since    1.0.0
     * @access   private
     * @throws   \Exception    If upgrade fails.
     */
    private static function upgrade_to_1_1_0() {
        // Add the items_per_page option if it doesn't exist
        if (!get_option('seed_catalog_items_per_page')) {
            add_option('seed_catalog_items_per_page', 12);
        }
        
        // Add cleanup settings with defaults
        if (!get_option('seed_catalog_cleanup_settings')) {
            add_option('seed_catalog_cleanup_settings', array(
                'remove_posts' => true,
                'remove_options' => true,
                'remove_taxonomies' => true
            ));
        }
        
        // Update existing posts if needed
        $args = array(
            'post_type' => 'seed',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        $seed_posts = get_posts($args);
        
        foreach ($seed_posts as $post_id) {
            // Add any new meta fields with default values
            
            // Example: Add a new meta field if it doesn't exist
            if (!metadata_exists('post', $post_id, 'germination_rate')) {
                update_post_meta($post_id, 'germination_rate', '');
            }
        }
    }
    
    /**
     * Log the upgrade.
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $previous_version    The previous version of the plugin.
     * @param    string    $current_version     The current version of the plugin.
     */
    private static function log_upgrade($previous_version, $current_version) {
        // Get the existing upgrade log or create a new one
        $upgrade_log = get_option('seed_catalog_upgrade_log', array());
        
        // Add this upgrade to the log
        $upgrade_log[] = array(
            'from' => $previous_version,
            'to' => $current_version,
            'date' => current_time('mysql'),
            'timestamp' => time(),
        );
        
        // Keep only the last 10 upgrades to avoid bloat
        if (count($upgrade_log) > 10) {
            $upgrade_log = array_slice($upgrade_log, -10);
        }
        
        // Save the log
        update_option('seed_catalog_upgrade_log', $upgrade_log);
    }
}