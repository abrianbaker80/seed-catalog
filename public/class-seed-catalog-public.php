<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 */

namespace SeedCatalog;

use WP_Query;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Seed_Catalog_Public {

    /**
     * The version of this plugin.
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($version = '') {
        $this->version = !empty($version) ? $version : SEED_CATALOG_VERSION;
        
        // Wait for init hook before registering AJAX handlers that use translations
        add_action('init', array($this, 'register_ajax_handlers'), 5);
    }
    
    /**
     * Register AJAX handlers after init hook
     */
    public function register_ajax_handlers() {
        // Register AJAX handlers
        add_action('wp_ajax_search_seed_varieties', array($this, 'handle_variety_search'));
        add_action('wp_ajax_nopriv_search_seed_varieties', array($this, 'handle_variety_search'));
        
        // Add AJAX handler for seed details
        add_action('wp_ajax_get_seed_details', array($this, 'handle_seed_details'));
        add_action('wp_ajax_nopriv_get_seed_details', array($this, 'handle_seed_details'));
        
        // Register AJAX handler for Gemini process search (direct passthrough to Gemini API class)
        add_action('wp_ajax_process_gemini_search', array($this, 'handle_gemini_search'));
        add_action('wp_ajax_nopriv_process_gemini_search', array($this, 'handle_gemini_search'));
    }
    
    /**
     * Handle AJAX requests for Gemini search
     * This is a wrapper for the Gemini API process_gemini_search method
     */
    public function handle_gemini_search() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_gemini_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
            return;
        }
        
        // Use the Gemini API class to perform the search
        if (!class_exists('SeedCatalog\\Seed_Catalog_Gemini_API')) {
            require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-gemini-api.php';
        }
        
        $gemini_api = new Seed_Catalog_Gemini_API();
        
        // Make sure the API key is set
        if (!$gemini_api->is_configured()) {
            wp_send_json_error(array('message' => 'Gemini API key not configured. Please configure it in the plugin settings.'));
            return;
        }
        
        // Call the process_gemini_search method
        $gemini_api->process_gemini_search();
        
        // This should not be reached as process_gemini_search should handle the response
        wp_die();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'seed-catalog-public',
            SEED_CATALOG_PLUGIN_URL . 'public/css/seed-catalog-public.css',
            array(),
            $this->version,
            'all'
        );
        
        wp_enqueue_style(
            'seed-catalog-responsive',
            SEED_CATALOG_PLUGIN_URL . 'public/css/seed-catalog-responsive.css',
            array('seed-catalog-public'),
            $this->version,
            'all'
        );
        
        wp_enqueue_style('dashicons');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Get asset URLs based on environment
        $js_urls = array(
            'seed-catalog-public' => SEED_CATALOG_PLUGIN_URL . 'public/js/seed-catalog-public.js',
            'seed-catalog-responsive' => SEED_CATALOG_PLUGIN_URL . 'public/js/seed-catalog-responsive.js'
        );
        
        if (defined('SCRIPT_DEBUG') && !SCRIPT_DEBUG) {
            foreach ($js_urls as $handle => $url) {
                $js_urls[$handle] = Seed_Catalog_Minify::get_minified_url($url);
            }
        }
        
        // Enqueue main script
        wp_enqueue_script(
            'seed-catalog-public',
            $js_urls['seed-catalog-public'],
            array('jquery'),
            $this->get_asset_version($js_urls['seed-catalog-public']),
            true
        );
        
        // Add enhanced responsive JavaScript
        wp_enqueue_script(
            'seed-catalog-responsive',
            $js_urls['seed-catalog-responsive'],
            array('jquery', 'seed-catalog-public'),
            $this->get_asset_version($js_urls['seed-catalog-responsive']),
            true
        );
        
        // Localize script for AJAX calls
        wp_localize_script('seed-catalog-public', 'seedCatalogPublic', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('seed_catalog_gemini_nonce'),  // Using Gemini nonce for AI features
            'postsPerPage' => 10,
            'infiniteScroll' => true,
            'isMobile' => wp_is_mobile(), // Add mobile detection for JS
        ));
    }

    /**
     * Get the version for an asset file
     * 
     * @param string $url URL of the asset file
     * @return string Version string based on file modification time or plugin version
     */
    private function get_asset_version($url) {
        $file = str_replace(SEED_CATALOG_PLUGIN_URL, SEED_CATALOG_PLUGIN_DIR, $url);
        
        if (file_exists($file)) {
            // Use file modification time for version in development
            if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
                return (string)filemtime($file);
            }
            
            // Use plugin version with file hash for production
            return $this->version . '.' . substr(md5_file($file), 0, 8);
        }
        
        // Fallback to plugin version
        return $this->version;
    }

    /**
     * Add custom body class for seed pages
     * 
     * @param array $classes Array of body classes
     * @return array Modified body classes
     */
    public function add_body_classes($classes) {
        if (is_singular('seed') || is_post_type_archive('seed') || is_tax('seed_category')) {
            $classes[] = 'seed-catalog-page';
        }
        return $classes;
    }

    /**
     * Process AJAX search requests for the frontend
     */
    public function ajax_search_seeds() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_public_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        // Check if user has permission to search seeds
        if (!current_user_can('read')) {
            wp_send_json_error(array('message' => 'You do not have permission to search seeds.'));
            return;
        }

        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        if (empty($search_term)) {
            wp_send_json_error(array('message' => 'No search term provided.'));
        }

        // Set up the query arguments
        $args = array(
            'post_type' => 'seed',
            'posts_per_page' => 10,
            's' => $search_term,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'seed_name',
                    'value' => $search_term,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'seed_variety',
                    'value' => $search_term,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'notes',
                    'value' => $search_term,
                    'compare' => 'LIKE'
                )
            )
        );

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $result = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'seed_name' => get_post_meta(get_the_ID(), 'seed_name', true),
                    'seed_variety' => get_post_meta(get_the_ID(), 'seed_variety', true)
                );

                // Add featured image if available
                if (has_post_thumbnail()) {
                    $result['image'] = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                }

                $results[] = $result;
            }
            wp_reset_postdata();

            wp_send_json_success(array(
                'results' => $results
            ));
        } else {
            wp_send_json_error(array('message' => 'No seeds found matching your search criteria.'));
        }

        wp_die();
    }

    /**
     * Process AJAX requests for filtering seeds by category
     */
    public function ajax_filter_seeds_by_category() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_public_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        // Check if user has permission to search seeds
        if (!current_user_can('read')) {
            wp_send_json_error(array('message' => 'You do not have permission to search seeds.'));
            return;
        }

        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        // Set up the query arguments
        $args = array(
            'post_type' => 'seed',
            'posts_per_page' => -1, // Get all matching seeds
        );

        // Add tax query if category is selected
        if ($category_id > 0) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'seed_category',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ),
            );
        }

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $result = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'seed_name' => get_post_meta(get_the_ID(), 'seed_name', true),
                    'seed_variety' => get_post_meta(get_the_ID(), 'seed_variety', true)
                );

                // Add featured image if available
                if (has_post_thumbnail()) {
                    $result['image'] = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                }

                $results[] = $result;
            }
            wp_reset_postdata();

            wp_send_json_success(array(
                'results' => $results
            ));
        } else {
            wp_send_json_error(array('message' => 'No seeds found in this category.'));
        }

        wp_die();
    }

    /**
     * Handle AJAX requests for seed variety search
     * This is a wrapper for the Gemini API search_seed_varieties method
     */
    public function handle_variety_search() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_gemini_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
            return;
        }

        // Get the search term
        $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
        
        if (empty($search_term)) {
            wp_send_json_error(array('message' => 'No search term provided.'));
            return;
        }
        
        // Use the Gemini API class to perform the search
        if (!class_exists('SeedCatalog\\Seed_Catalog_Gemini_API')) {
            require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-gemini-api.php';
        }
        
        $gemini_api = new Seed_Catalog_Gemini_API();
        
        // Make sure the API key is set
        if (!$gemini_api->is_configured()) {
            wp_send_json_error(array('message' => 'Gemini API key not configured. Please configure it in the plugin settings.'));
            return;
        }
        
        // Call the search method - it will handle the wp_send_json_* responses internally
        $gemini_api->search_seed_varieties($search_term);
        
        wp_die();
    }
    
    /**
     * Handle AJAX requests for seed details
     * This is a wrapper for the Gemini API get_seed_details method
     */
    public function handle_seed_details() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_gemini_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
            return;
        }
        
        // Get the parameters
        $variety = isset($_POST['variety']) ? sanitize_text_field($_POST['variety']) : '';
        $plant_type = isset($_POST['plant_type']) ? sanitize_text_field($_POST['plant_type']) : '';
        $brand = isset($_POST['brand']) ? sanitize_text_field($_POST['brand']) : '';
        $sku = isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : '';
        
        // Use the Gemini API class to get seed details
        if (class_exists('Seed_Catalog_Gemini_API')) {
            $gemini_api = new Seed_Catalog_Gemini_API();
            
            // Make sure the API key is set
            if (!$gemini_api->is_configured()) {
                wp_send_json_error(array('message' => 'Gemini API key not configured.'));
                return;
            }
            
            // Let the Gemini API class handle the seed details request
            // The method already has proper response handling
            $gemini_api->get_seed_details($variety, $plant_type, $brand, $sku);
        } else {
            wp_send_json_error(array('message' => 'Gemini API class not found.'));
        }
        
        // This should not be reached, but just in case
        wp_die();
    }
}