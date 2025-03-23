<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks. It maintains the central organization of all
 * plugin components and their integration with WordPress.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */

namespace SeedCatalog;

use Exception;
use WP_Screen;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load required classes
require_once SEED_CATALOG_PLUGIN_DIR . 'public/class-seed-catalog-public.php';

class Seed_Catalog {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Seed_Catalog_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Plugin version.
     *
     * @since    1.1.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Tracks if dependencies have been loaded
     *
     * @since    1.1.0
     * @access   private
     * @var      boolean    $dependencies_loaded    Whether dependencies have been loaded
     */
    private $dependencies_loaded = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        
        // Initialize settings
        new Seed_Catalog_Settings();
        
        // Initialize post types
        $post_types = new Seed_Catalog_Post_Types();
        add_action('init', array($post_types, 'register_seed_post_type'));
        add_action('init', array($post_types, 'register_seed_taxonomy'));
    }

    /**
     * Initialize plugin after WordPress is loaded
     *
     * @since    1.1.0
     */
    public function init_plugin() {
        if ($this->dependencies_loaded) {
            return;
        }

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->dependencies_loaded = true;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     * - Seed_Catalog_Loader - Orchestrates the hooks of the plugin.
     * - Seed_Catalog_Post_Types - Defines custom post types and taxonomies.
     * - Seed_Catalog_Meta_Boxes - Defines custom meta boxes for editing seeds.
     * - Seed_Catalog_Shortcodes - Defines plugin shortcodes.
     * - Seed_Catalog_Templates - Manages custom templates for seed displays.
     * - Seed_Catalog_Exporter - Handles exporting seed data.
     * - Seed_Catalog_Gemini_API - Provides AI-powered seed data generation.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-settings.php';
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-post-types.php';
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-gemini-api.php';
        
        // Essential classes
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-meta-boxes.php';
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-shortcodes.php';
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-templates.php';
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-minify.php';
        
        // Admin and public classes
        require_once SEED_CATALOG_PLUGIN_DIR . 'admin/class-seed-catalog-admin.php';
        require_once SEED_CATALOG_PLUGIN_DIR . 'public/class-seed-catalog-public.php';
        
        // Feature classes
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-exporter.php';
        
        // Diagnostic and testing tools
        if (is_admin()) {
            require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-diagnostic.php';
            require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-api-test-util.php';
        }
    }

    /**
     * Set the plugin locale for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        add_action('init', 'seed_catalog_load_textdomain', 0);
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0 
     * @access   private
     */
    private function set_locale() {
        add_action('init', function() {
            load_plugin_textdomain(
                'seed-catalog',
                false,
                dirname(SEED_CATALOG_PLUGIN_BASENAME) . '/languages'
            );
        }, 0);
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        if (!isset($this->loader)) {
            return;
        }
        
        try {
            // Initialize admin components
            $plugin_admin = new Seed_Catalog_Admin($this->get_version());
            $post_types = new Seed_Catalog_Post_Types();
            $meta_boxes = new Seed_Catalog_Meta_Boxes();
            $gemini_api = new Seed_Catalog_Gemini_API();
            $exporter = new Seed_Catalog_Exporter();

            // Initialize diagnostic tools if in admin and debug is enabled
            $diagnostic = null;
            if (is_admin() && get_option('seed_catalog_enable_debug', false)) {
                $diagnostic_file = SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-diagnostic.php';
                if (file_exists($diagnostic_file)) {
                    require_once $diagnostic_file;
                    if (class_exists('SeedCatalog\\Seed_Catalog_Diagnostic')) {
                        $diagnostic = new Seed_Catalog_Diagnostic();
                    }
                }
            }

            // Admin assets and core functionality
            $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
            $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
            
            // Post types and taxonomies
            $this->loader->add_action('init', $post_types, 'register_seed_post_type');
            $this->loader->add_action('init', $post_types, 'register_seed_taxonomy');
            
            // Meta boxes
            $this->loader->add_action('add_meta_boxes', $meta_boxes, 'add_seed_meta_boxes');
            $this->loader->add_action('save_post_seed', $meta_boxes, 'save_seed_meta', 10, 2);
            
            // AI integration hooks
            $this->loader->add_action('wp_ajax_seed_catalog_save_settings', $plugin_admin, 'ajax_save_settings');
            $this->loader->add_action('wp_ajax_get_seed_details', $plugin_admin, 'ajax_get_seed_details');
            $this->loader->add_action('wp_ajax_seed_catalog_gemini_image_recognition', $plugin_admin, 'ajax_image_recognition');
            
            // File upload handling
            $this->loader->add_filter('upload_mimes', $plugin_admin, 'allowed_mime_types');
            $this->loader->add_filter('wp_check_filetype_and_ext', $plugin_admin, 'check_filetype', 10, 4);
            
            // Admin menu and settings
            $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_settings_page');
            $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
            
            // Register diagnostic tools if available
            if ($diagnostic !== null && get_option('seed_catalog_enable_debug', false)) {
                $this->loader->add_action('admin_footer', $plugin_admin, 'add_debug_panel');
                $this->loader->add_action('admin_notices', $diagnostic, 'display_diagnostic_notices');
            }
            
            // Register export functionality
            $this->loader->add_action('admin_post_seed_catalog_export', $exporter, 'handle_export');
            
            // Add API test handler
            add_action('wp_ajax_test_gemini_api', array($this, 'handle_test_api'));
            
        } catch (Exception $e) {
            $this->log_error('Error initializing admin hooks: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle API test requests
     */
    public function handle_test_api() {
        check_ajax_referer('seed_catalog_test_api', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }

        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No API key provided'));
            return;
        }

        // Test the API connection
        $gemini_api = new Seed_Catalog_Gemini_API();
        $gemini_api->set_api_key($api_key);
        $test_result = $gemini_api->test_api_connection($api_key);

        if ($test_result['success']) {
            wp_send_json_success(array('message' => 'API connection successful'));
        } else {
            wp_send_json_error(array('message' => $test_result['message']));
        }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        if (!isset($this->loader)) {
            return;
        }
        
        try {
            $plugin_public = new Seed_Catalog_Public($this->get_version());
            $shortcodes = new Seed_Catalog_Shortcodes();
            $templates = new Seed_Catalog_Templates();

            // Public assets
            $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
            $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
            
            // Add body classes for seed pages
            $this->loader->add_filter('body_class', $plugin_public, 'add_body_classes');
            
            // Register shortcodes
            $this->loader->add_action('init', $shortcodes, 'register_shortcodes');
            
            // Single seed template
            $this->loader->add_filter('single_template', $templates, 'seed_single_template');
            $this->loader->add_filter('archive_template', $templates, 'seed_archive_template');
            $this->loader->add_filter('taxonomy_template', $templates, 'seed_taxonomy_template');
            
        } catch (Exception $e) {
            $this->log_error('Error defining public hooks: ' . $e->getMessage());
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                add_action('wp_footer', function() use ($e) {
                    echo '<!-- Seed Catalog Error: ' . esc_html($e->getMessage()) . ' -->';
                });
            }
        }
    }

    /**
     * Register all of the hooks related to shortcodes
     *
     * @since    1.1.0
     * @access   private
     */
    private function define_shortcode_hooks() {
        $shortcodes = new Seed_Catalog_Shortcodes();
        
        // Register shortcodes through the loader
        $this->loader->add_shortcode('seed_search', $shortcodes, 'seed_search_shortcode');
        $this->loader->add_shortcode('seed_categories', $shortcodes, 'seed_categories_shortcode');
        $this->loader->add_shortcode('seed_list', $shortcodes, 'seed_list_shortcode');
        $this->loader->add_shortcode('seed_submission_form', $shortcodes, 'seed_submission_form_shortcode');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        if (isset($this->loader)) {
            return $this->loader->run();
        } else {
            $this->log_error('Plugin loader not initialized properly.');
            return false;
        }
    }
    
    /**
     * The version of the plugin.
     *
     * @since     1.1.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Log an error to the WordPress debug log.
     *
     * @since    1.1.0
     * @access   private
     * @param    string    $message    The error message to log.
     */
    private function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Seed Catalog Error: ' . $message);
        }
    }
}