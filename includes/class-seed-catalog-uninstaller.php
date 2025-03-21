<?php
/**
 * Fired during plugin uninstallation.
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
 * Fired during plugin uninstallation.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */
class Seed_Catalog_Uninstaller {

    /**
     * Uninstall the plugin.
     *
     * Clean up all plugin data including settings, post types, taxonomies, and custom data.
     *
     * @since    1.0.0
     */
    public static function uninstall() {
        self::maybe_remove_all_data();
    }
    
    /**
     * Remove all plugin data.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function maybe_remove_all_data() {
        // Get the cleanup settings
        $cleanup_settings = get_option('seed_catalog_cleanup_settings', array(
            'remove_posts' => true,
            'remove_options' => true,
            'remove_taxonomies' => true
        ));
        
        // If settings say to remove posts, do it
        if (!empty($cleanup_settings['remove_posts'])) {
            self::remove_seed_posts();
        }
        
        // If settings say to remove taxonomies, do it
        if (!empty($cleanup_settings['remove_taxonomies'])) {
            self::remove_seed_taxonomies();
        }
        
        // If settings say to remove options, do it
        if (!empty($cleanup_settings['remove_options'])) {
            self::remove_options();
        }
        
        // Always remove transients
        self::remove_transients();
    }
    
    /**
     * Remove all seed posts.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function remove_seed_posts() {
        // Get all seed posts
        $args = array(
            'post_type' => 'seed',
            'nopaging' => true,
            'fields' => 'ids',
            'post_status' => 'any',
        );
        
        $seed_posts = get_posts($args);
        
        // Delete each post permanently
        foreach ($seed_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
    
    /**
     * Remove all seed taxonomy terms.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function remove_seed_taxonomies() {
        // Get all seed category terms
        $terms = get_terms(array(
            'taxonomy' => 'seed_category',
            'hide_empty' => false,
        ));
        
        // Delete each term
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'seed_category');
        }
    }
    
    /**
     * Remove all plugin options.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function remove_options() {
        // List all options to remove
        $options = array(
            'seed_catalog_version',
            'seed_catalog_gemini_api_key',
            'seed_catalog_items_per_page',
            'seed_catalog_first_activation',
            'seed_catalog_cleanup_settings',
            // Add any other options that need to be removed
        );
        
        // Delete each option
        foreach ($options as $option) {
            delete_option($option);
        }
    }
    
    /**
     * Remove all plugin transients.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function remove_transients() {
        // List all transients to remove
        $transients = array(
            'seed_catalog_cache',
            'seed_catalog_api_test_results',
            // Add any other transients that need to be removed
        );
        
        // Delete each transient
        foreach ($transients as $transient) {
            delete_transient($transient);
        }
    }
}