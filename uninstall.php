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

// Direct uninstallation process that doesn't rely on classes
function seed_catalog_do_uninstall() {
    // Remove plugin options
    delete_option('seed_catalog_version');
    delete_option('seed_catalog_items_per_page');
    delete_option('seed_catalog_gemini_api_key');
    delete_option('seed_catalog_enable_debug');
    delete_option('seed_catalog_first_activation');
    
    // Remove transients
    delete_transient('seed_catalog_cache');
    delete_transient('seed_catalog_api_test_results');
    
    // Optional: Remove posts and taxonomies if configuration says to
    $remove_data = get_option('seed_catalog_remove_data_on_uninstall', false);
    
    if ($remove_data) {
        // Get all seed posts
        $args = array(
            'post_type' => 'seed',
            'nopaging' => true,
            'fields' => 'ids',
            'post_status' => 'any'
        );
        
        $seeds = get_posts($args);
        
        // Delete each seed post
        foreach ($seeds as $post_id) {
            wp_delete_post($post_id, true);
        }
        
        // Delete taxonomy terms
        $terms = get_terms(array(
            'taxonomy' => 'seed_category',
            'hide_empty' => false
        ));
        
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'seed_category');
        }
    }
    
    // Flush rewrite rules after uninstall
    flush_rewrite_rules();
    
    // Log uninstallation success when debug is enabled
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Seed Catalog Plugin: Uninstallation complete');
    }
}

// Run the uninstaller directly
seed_catalog_do_uninstall();