<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and admin-specific hooks including
 * the settings page, styles, and scripts.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/admin
 */

namespace SeedCatalog;

use WP_Screen;
use Exception;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Because we're in the same namespace, we don't need to use a use statement for Seed_Catalog_Minify

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/admin
 */
class Seed_Catalog_Admin {

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
        
        // Register AJAX handlers if in admin
        if (is_admin()) {
            // Core AJAX handlers
            add_action('wp_ajax_seed_catalog_save_settings', array($this, 'ajax_save_settings'));
            add_action('wp_ajax_get_seed_details', array($this, 'ajax_get_seed_details'));
            add_action('wp_ajax_seed_catalog_gemini_image_recognition', array($this, 'ajax_image_recognition'));
            
            // Initialize all admin functionality
            $this->register_actions();
            
            // Add settings page
            add_action('admin_menu', array($this, 'add_plugin_settings_page'));
            
            // Register settings
            add_action('admin_init', array($this, 'register_settings'));
            
            // Handle media upload for seed images
            add_filter('upload_mimes', array($this, 'allowed_mime_types'));
            add_filter('wp_check_filetype_and_ext', array($this, 'check_filetype'), 10, 4);
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        
        // Only load on seed post type, settings page, or export page
        if (!$screen || !$this->is_seed_catalog_admin_page($screen)) {
            return;
        }
        
        // Get asset URL based on environment
        $css_url = SEED_CATALOG_PLUGIN_URL . 'admin/css/seed-catalog-admin.css';
        if (defined('SCRIPT_DEBUG') && !SCRIPT_DEBUG) {
            $css_url = Seed_Catalog_Minify::get_minified_url($css_url);
        }
        
        wp_enqueue_style(
            'seed-catalog-admin',
            $css_url,
            array(),
            $this->get_asset_version($css_url),
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        
        // Only load on seed post type, settings page, or export page
        if (!$screen || !$this->is_seed_catalog_admin_page($screen)) {
            return;
        }
        
        // Get asset URL based on environment
        $js_url = SEED_CATALOG_PLUGIN_URL . 'admin/js/seed-catalog-admin.js';
        if (defined('SCRIPT_DEBUG') && !SCRIPT_DEBUG) {
            $js_url = Seed_Catalog_Minify::get_minified_url($js_url);
        }
        
        wp_enqueue_script(
            'seed-catalog-admin',
            $js_url,
            array('jquery'),
            $this->get_asset_version($js_url),
            true
        );

        wp_localize_script('seed-catalog-admin', 'seedCatalogAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('seed_catalog_gemini_nonce')
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
     * Check if current screen is related to Seed Catalog plugin.
     *
     * @since    1.1.0
     * @access   private
     * @param    WP_Screen    $screen    Current admin screen.
     * @return   boolean                 True if current screen is a Seed Catalog admin page.
     */
    private function is_seed_catalog_admin_page($screen) {
        if (!$screen) {
            return false;
        }
        
        $seed_catalog_pages = array(
            'seed',                      // Post type
            'edit-seed',                 // Post type list
            'seed_category',             // Taxonomy
            'edit-seed_category',        // Taxonomy list 
            'seed_page_seed-catalog-settings',  // Settings page
            'seed_page_seed-catalog-export'     // Export page
        );
        
        return in_array($screen->id, $seed_catalog_pages);
    }

    /**
     * Add plugin settings page.
     *
     * @since    1.0.0
     */
    public function add_plugin_settings_page() {
        add_submenu_page(
            'edit.php?post_type=seed',
            __('Seed Catalog Settings', 'seed-catalog'),
            __('Settings', 'seed-catalog'),
            'manage_options',
            'seed-catalog-settings',
            array($this, 'render_settings_page')
        );

        // Also add an export page
        add_submenu_page(
            'edit.php?post_type=seed',
            __('Export Seed Catalog', 'seed-catalog'),
            __('Export', 'seed-catalog'),
            'edit_posts',
            'seed-catalog-export',
            array($this, 'render_export_page')
        );
    }

    /**
     * Render the plugin settings page content.
     *
     * @since    1.0.0
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'seed-catalog'));
        }
        
        // Show settings errors
        settings_errors('seed_catalog_settings');
        
        ?>
        <div class="wrap seed-catalog-settings-page">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('seed_catalog_settings');
                do_settings_sections('seed_catalog_settings');
                submit_button(__('Save Settings', 'seed-catalog'));
                ?>
            </form>
            
            <?php 
            // Display API test button if available
            if (function_exists('seed_catalog_display_api_test_button')) {
                seed_catalog_display_api_test_button();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render the export page content.
     *
     * @since    1.0.0
     */
    public function render_export_page() {
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'seed-catalog'));
        }
        
        ?>
        <div class="wrap seed-catalog-export-page">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="card">
                <h2><?php _e('Export Your Seed Catalog', 'seed-catalog'); ?></h2>
                <p><?php _e('Use the button below to export your entire seed catalog to a CSV file that can be opened in Excel or other spreadsheet software.', 'seed-catalog'); ?></p>
                
                <?php 
                // Create the exporter instance
                $exporter = new Seed_Catalog_Exporter();
                if (method_exists($exporter, 'render_export_button')) {
                    $exporter->render_export_button();
                } else {
                    echo '<div class="notice notice-error"><p>';
                    _e('Export functionality is not available. The exporter class may be missing or incompatible.', 'seed-catalog');
                    echo '</p></div>';
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register setting
        register_setting(
            'seed_catalog_settings',
            'seed_catalog_gemini_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add settings section
        add_settings_section(
            'seed_catalog_gemini_settings',
            __('Gemini AI API Settings', 'seed-catalog'),
            array($this, 'gemini_settings_section_callback'),
            'seed_catalog_settings'
        );

        // Add settings field
        add_settings_field(
            'seed_catalog_gemini_api_key',
            __('API Key', 'seed-catalog'),
            array($this, 'gemini_api_key_field_callback'),
            'seed_catalog_settings',
            'seed_catalog_gemini_settings'
        );
        
        // Add display settings section
        add_settings_section(
            'seed_catalog_display_settings',
            __('Display Settings', 'seed-catalog'),
            array($this, 'display_settings_section_callback'),
            'seed_catalog_settings'
        );
        
        // Register display settings
        register_setting(
            'seed_catalog_settings',
            'seed_catalog_items_per_page',
            array(
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 12
            )
        );
        
        // Add display settings field
        add_settings_field(
            'seed_catalog_items_per_page',
            __('Items Per Page', 'seed-catalog'),
            array($this, 'items_per_page_field_callback'),
            'seed_catalog_settings',
            'seed_catalog_display_settings'
        );
    }

    /**
     * Callback for the Gemini settings section.
     *
     * @since    1.0.0
     */
    public function gemini_settings_section_callback() {
        echo '<p>' . __('Configure your Gemini AI API integration to enable AI-assisted seed information retrieval and image recognition.', 'seed-catalog') . '</p>';
        echo '<p>' . sprintf(
            __('You can get a Gemini API key from %s.', 'seed-catalog'),
            '<a href="https://ai.google.dev/" target="_blank" rel="noopener noreferrer">Google AI Studio</a>'
        ) . '</p>';
    }

    /**
     * Callback for the display settings section.
     *
     * @since    1.1.0
     */
    public function display_settings_section_callback() {
        echo '<p>' . __('Configure how your seed catalog appears on the front end of your site.', 'seed-catalog') . '</p>';
    }

    /**
     * Callback for the Gemini API Key field.
     *
     * @since    1.0.0
     */
    public function gemini_api_key_field_callback() {
        $api_key = get_option('seed_catalog_gemini_api_key', '');
        ?>
        <input type="password" 
               id="seed_catalog_gemini_api_key" 
               name="seed_catalog_gemini_api_key" 
               value="<?php echo esc_attr($api_key); ?>" 
               class="regular-text" 
               autocomplete="new-password">
        <button type="button" id="toggle-api-key" class="button button-secondary">
            <?php _e('Show/Hide', 'seed-catalog'); ?>
        </button>
        <p class="description">
            <?php _e('Enter your Gemini API key here. This will be used for AI-assisted seed information retrieval and image recognition.', 'seed-catalog'); ?>
        </p>
        <script>
            jQuery(document).ready(function($) {
                $('#toggle-api-key').on('click', function() {
                    var apiKeyField = $('#seed_catalog_gemini_api_key');
                    if (apiKeyField.attr('type') === 'password') {
                        apiKeyField.attr('type', 'text');
                    } else {
                        apiKeyField.attr('type', 'password');
                    }
                });
            });
        </script>
        <?php
    }
    
    /**
     * Callback for the items per page field.
     *
     * @since    1.1.0
     */
    public function items_per_page_field_callback() {
        $items_per_page = get_option('seed_catalog_items_per_page', 12);
        ?>
        <input type="number" 
               id="seed_catalog_items_per_page" 
               name="seed_catalog_items_per_page" 
               value="<?php echo esc_attr($items_per_page); ?>" 
               class="small-text" 
               min="1" 
               max="100" 
               step="1">
        <p class="description">
            <?php _e('Number of seeds to display per page in catalog views.', 'seed-catalog'); ?>
        </p>
        <?php
    }
    
    /**
     * Handle AJAX saving of settings.
     * 
     * @since    1.1.0
     */
    public function ajax_save_settings() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'seed-catalog')));
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to change settings.', 'seed-catalog')));
            return;
        }
        
        // Get settings
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        // Validate and save each setting
        $saved = array();
        
        if (isset($settings['api_key'])) {
            $api_key = sanitize_text_field($settings['api_key']);
            update_option('seed_catalog_gemini_api_key', $api_key);
            $saved[] = 'api_key';
        }
        
        if (isset($settings['items_per_page'])) {
            $raw_items = intval($settings['items_per_page']);
            $items_per_page = max(1, $raw_items); // Ensure minimum of 1
            update_option('seed_catalog_items_per_page', $items_per_page);
            $saved[] = 'items_per_page';
        }
        
        if (empty($saved)) {
            wp_send_json_error(array('message' => __('No valid settings provided.', 'seed-catalog')));
            return;
        }
        
        wp_send_json_success(array(
            'message' => __('Settings saved successfully.', 'seed-catalog'),
            'saved' => $saved
        ));
    }

    /**
     * Add custom debug panel to admin footer.
     * 
     * @since    1.1.0
     */
    public function add_debug_panel() {
        $screen = get_current_screen();
        
        if (!$screen || !$this->is_seed_catalog_admin_page($screen)) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="seed-catalog-debug-panel hidden">
            <h3><?php _e('Seed Catalog Debug Info', 'seed-catalog'); ?></h3>
            <div class="debug-content"></div>
        </div>
        <?php
    }

    /**
     * Add meta boxes for seed details and AI integration.
     *
     * @since    1.1.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'gemini_api_info',
            __('AI-Assisted Information', 'seed-catalog'),
            array($this, 'render_gemini_meta_box'),
            'seed',
            'normal',
            'high'
        );
    }

    /**
     * Render the Gemini API meta box content.
     *
     * @since    1.1.0
     * @param    /WP_Post    $post    The post object.
     */
    public function render_gemini_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('seed_catalog_gemini_meta_box', 'seed_catalog_gemini_nonce');
        
        ?>
        <div class="seed-catalog-gemini-integration">
            <div class="seed-catalog-input-group">
                <button type="button" id="seed-catalog-ai-search" class="button seed-catalog-ai-suggest">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php _e('Get AI Suggestions', 'seed-catalog'); ?>
                </button>
                <div class="seed-catalog-loading" style="display:none;">
                    <span class="spinner is-active"></span>
                    <?php _e('Searching for information...', 'seed-catalog'); ?>
                </div>
            </div>

            <div id="seed-catalog-ai-results" class="hidden">
                <div id="seed-catalog-ai-suggestions"></div>
            </div>

            <div class="seed-catalog-image-recognition">
                <h3><?php _e('Image Recognition', 'seed-catalog'); ?></h3>
                <input type="file" id="seed_image_upload" name="seed_image_upload" accept="image/*">
                <button type="button" id="seed-catalog-image-recognition" class="button">
                    <?php _e('Identify Seed from Image', 'seed-catalog'); ?>
                </button>
                <div class="seed-catalog-loading" style="display:none;">
                    <span class="spinner is-active"></span>
                    <?php _e('Analyzing image...', 'seed-catalog'); ?>
                </div>
                <div id="seed-catalog-image-results" class="hidden">
                    <div id="seed-catalog-image-suggestions"></div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add settings section for API diagnostics.
     *
     * @since    1.1.0
     */
    public function add_diagnostic_settings() {
        add_settings_section(
            'seed_catalog_diagnostic_settings',
            __('Diagnostic Tools', 'seed-catalog'),
            array($this, 'diagnostic_settings_section_callback'),
            'seed_catalog_settings'
        );

        add_settings_field(
            'seed_catalog_enable_debug',
            __('Enable Debug Mode', 'seed-catalog'),
            array($this, 'debug_mode_field_callback'),
            'seed_catalog_settings',
            'seed_catalog_diagnostic_settings'
        );

        register_setting(
            'seed_catalog_settings',
            'seed_catalog_enable_debug',
            array(
                'type' => 'boolean',
                'default' => false
            )
        );
    }

    /**
     * Callback for the diagnostic settings section.
     *
     * @since    1.1.0
     */
    public function diagnostic_settings_section_callback() {
        echo '<p>' . __('Tools for troubleshooting and testing the Seed Catalog plugin.', 'seed-catalog') . '</p>';
    }

    /**
     * Callback for the debug mode field.
     *
     * @since    1.1.0
     */
    public function debug_mode_field_callback() {
        $debug_enabled = get_option('seed_catalog_enable_debug', false);
        ?>
        <label>
            <input type="checkbox" 
                   name="seed_catalog_enable_debug" 
                   value="1" 
                   <?php checked($debug_enabled, true); ?>>
            <?php _e('Show debug information in admin area', 'seed-catalog'); ?>
        </label>
        <p class="description">
            <?php _e('When enabled, debug information will be shown to administrators.', 'seed-catalog'); ?>
        </p>
        <?php
    }

    /**
     * Add inline styles for admin notices.
     *
     * @since    1.1.0
     */
    public function admin_notice_styles() {
        $screen = get_current_screen();
        
        if (!$screen || !$this->is_seed_catalog_admin_page($screen)) {
            return;
        }
        
        ?>
        <style>
            .seed-catalog-notice {
                padding: 10px;
                margin: 5px 0 15px;
                border-left: 4px solid #00a0d2;
                background: #fff;
            }
            .seed-catalog-notice.notice-success {
                border-left-color: #46b450;
            }
            .seed-catalog-notice.notice-error {
                border-left-color: #dc3232;
            }
            .seed-catalog-notice.notice-warning {
                border-left-color: #ffb900;
            }
        </style>
        <?php
    }

    /**
     * Register actions for admin area.
     *
     * @since    1.1.0
     */
    public function register_actions() {
        // Add admin notices styling
        add_action('admin_head', array($this, 'admin_notice_styles'));
        
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        
        // Add debug panel for administrators
        if (current_user_can('manage_options') && get_option('seed_catalog_enable_debug', false)) {
            add_action('admin_footer', array($this, 'add_debug_panel'));
        }
        
        // Add diagnostic settings
        add_action('admin_init', array($this, 'add_diagnostic_settings'));
    }

    /**
     * Add allowed mime types for seed images.
     *
     * @since    1.1.0
     * @param    array    $mimes    Array of allowed mime types.
     * @return   array              Modified array of mime types.
     */
    public function allowed_mime_types($mimes) {
        // Add common image formats
        $mimes['jpg|jpeg|jpe'] = 'image/jpeg';
        $mimes['gif'] = 'image/gif';
        $mimes['png'] = 'image/png';
        $mimes['webp'] = 'image/webp';
        
        return $mimes;
    }

    /**
     * Additional file type checking.
     *
     * @since    1.1.0
     * @param    array     $data     Values for the extension, mime type, and corrected filename.
     * @param    string    $file     Full path to the file.
     * @param    string    $filename The name of the file.
     * @param    array     $mimes    Array of allowed mime types.
     * @return   array               Modified file data array.
     */
    public function check_filetype($data, $file, $filename, $mimes) {
        $filetype = wp_check_filetype($filename, $mimes);
        
        // Only process image files
        if (strpos($filetype['type'], 'image/') === 0) {
            $data['ext'] = $filetype['ext'];
            $data['type'] = $filetype['type'];
        }
        
        return $data;
    }

    /**
     * Handle AJAX request for getting seed details.
     * 
     * @since    1.1.0
     */
    public function ajax_get_seed_details() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_gemini_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'seed-catalog')));
            return;
        }
        
        // Get search parameters
        $variety = isset($_POST['variety']) ? sanitize_text_field($_POST['variety']) : '';
        $plant_type = isset($_POST['plant_type']) ? sanitize_text_field($_POST['plant_type']) : '';
        $brand = isset($_POST['brand']) ? sanitize_text_field($_POST['brand']) : '';
        $sku = isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : '';
        
        if (empty($variety) && empty($plant_type)) {
            wp_send_json_error(array('message' => __('Please provide a seed name or variety.', 'seed-catalog')));
            return;
        }
        
        // Use the Gemini API to get seed details
        if (class_exists('Seed_Catalog_Gemini_API')) {
            $gemini_api = new Seed_Catalog_Gemini_API();
            
            if (!$gemini_api->is_configured()) {
                wp_send_json_error(array('message' => __('Gemini API is not configured. Please add your API key in the settings.', 'seed-catalog')));
                return;
            }
            
            try {
                $result = $gemini_api->get_seed_details($variety, $plant_type, $brand, $sku);
                wp_send_json_success($result);
            } catch (Exception $e) {
                wp_send_json_error(array('message' => $e->getMessage()));
            }
        } else {
            wp_send_json_error(array('message' => __('Gemini API integration is not available.', 'seed-catalog')));
        }
        
        wp_die();
    }
    
    /**
     * Handle AJAX request for image recognition.
     * 
     * @since    1.1.0
     */
    public function ajax_image_recognition() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_gemini_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'seed-catalog')));
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['seed_image']) || !is_uploaded_file($_FILES['seed_image']['tmp_name'])) {
            wp_send_json_error(array('message' => __('No image file was uploaded.', 'seed-catalog')));
            return;
        }
        
        // Validate file type
        $file_info = wp_check_filetype_and_ext(
            $_FILES['seed_image']['tmp_name'],
            $_FILES['seed_image']['name'],
            array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
                'webp' => 'image/webp'
            )
        );
        
        if (!$file_info['type'] || strpos($file_info['type'], 'image/') !== 0) {
            wp_send_json_error(array('message' => __('Invalid file type. Please upload an image file.', 'seed-catalog')));
            return;
        }
        
        // Use the Gemini API for image recognition
        if (class_exists('Seed_Catalog_Gemini_API')) {
            $gemini_api = new Seed_Catalog_Gemini_API();
            
            if (!$gemini_api->is_configured()) {
                wp_send_json_error(array('message' => __('Gemini API is not configured. Please add your API key in the settings.', 'seed-catalog')));
                return;
            }
            
            try {
                $result = $gemini_api->analyze_image($_FILES['seed_image']['tmp_name']);
                wp_send_json_success($result);
            } catch (Exception $e) {
                wp_send_json_error(array('message' => $e->getMessage()));
            }
        } else {
            wp_send_json_error(array('message' => __('Gemini API integration is not available.', 'seed-catalog')));
        }
        
        wp_die();
    }

    /**
     * Process image upload and analysis
     */
    public function handle_image_analysis() {
        check_ajax_referer('seed_catalog_gemini_nonce', 'nonce');

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to use image analysis.', 'seed-catalog')));
            return;
        }

        // Validate file upload
        if (!isset($_FILES['seed_image']) || empty($_FILES['seed_image'])) {
            wp_send_json_error(array('message' => __('No image uploaded.', 'seed-catalog')));
            return;
        }

        $file_info = wp_check_filetype(
            $_FILES['seed_image']['name'],
            array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp'
            )
        );

        if (!$file_info['type'] || strpos($file_info['type'], 'image/') !== 0) {
            wp_send_json_error(array('message' => __('Invalid file type. Please upload an image file.', 'seed-catalog')));
            return;
        }

        // Use the Gemini API for image recognition
        if (class_exists('Seed_Catalog_Gemini_API')) {
            $gemini_api = new Seed_Catalog_Gemini_API();

            if (!$gemini_api->is_configured()) {
                wp_send_json_error(array('message' => __('Gemini API is not configured. Please add your API key in the settings.', 'seed-catalog')));
                return;
            }

            try {
                $result = $gemini_api->analyze_image($_FILES['seed_image']['tmp_name']);
                wp_send_json_success($result);
            } catch (Exception $e) {
                wp_send_json_error(array('message' => $e->getMessage()));
            }
        } else {
            wp_send_json_error(array('message' => __('Gemini API integration is not available.', 'seed-catalog')));
        }

        wp_die();
    }
}