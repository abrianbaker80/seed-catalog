<?php
/**
 * The diagnostic functionality of the plugin.
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
 * The diagnostic functionality of the plugin.
 *
 * This class handles all diagnostic tools and system status checks
 * to help users identify and solve problems with the plugin.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */
class Seed_Catalog_Diagnostic {

    /**
     * Initialize the diagnostic tools.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Constructor is intentionally empty
        // All setup happens through hooks registered in the main plugin class
    }

    /**
     * Register the diagnostic shortcode.
     *
     * @since    1.0.0
     */
    public function register_diagnostic_shortcode() {
        add_shortcode('seed_catalog_diagnostic', array($this, 'diagnostic_shortcode_callback'));
    }

    /**
     * Add a menu item for the diagnostic page.
     *
     * @since    1.0.0
     */
    public function add_diagnostic_menu() {
        add_submenu_page(
            'edit.php?post_type=seed',
            __('Seed Catalog Diagnostic', 'seed-catalog'),
            __('Diagnostic Tool', 'seed-catalog'),
            'manage_options',
            'seed-catalog-diagnostic',
            array($this, 'render_diagnostic_page')
        );
    }

    /**
     * Shortcode callback function for the diagnostic tool.
     *
     * @since    1.0.0
     * @return   string    The HTML output for the shortcode.
     */
    public function diagnostic_shortcode_callback() {
        ob_start();
        
        try {
            // Get diagnostic results
            $diagnostics = $this->run_diagnostics();
            
            // Load the template
            $template_file = SEED_CATALOG_PLUGIN_DIR . 'templates/diagnostic.php';
            
            if (file_exists($template_file)) {
                // Make diagnostic results available to the template
                $diagnostic_results = $diagnostics;
                include $template_file;
            } else {
                echo '<div class="error"><p>';
                echo sprintf(
                    /* translators: %s: Template file path */
                    esc_html__('Error: Diagnostic template file not found at %s', 'seed-catalog'),
                    esc_html($template_file)
                );
                echo '</p></div>';
            }
        } catch (\Exception $e) {
            echo '<div class="error"><p>';
            echo esc_html__('Error running diagnostics: ', 'seed-catalog') . esc_html($e->getMessage());
            echo '</p></div>';
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo '<div class="error"><p><strong>';
                echo esc_html__('Debug information:', 'seed-catalog');
                echo '</strong><br>';
                echo esc_html($e->getTraceAsString());
                echo '</p></div>';
            }
        }
        
        return ob_get_clean();
    }

    /**
     * Render the diagnostic page.
     *
     * @since    1.0.0
     */
    public function render_diagnostic_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'seed-catalog'));
        }
        
        ?><!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php esc_html_e('Seed Catalog Diagnostic', 'seed-catalog'); ?></title>
            <?php wp_head(); ?>
        </head>
        <body <?php body_class('wp-admin seed-catalog-diagnostic-page'); ?>>
            <div class="wrap">
                <h1><?php esc_html_e('Seed Catalog Diagnostic Tool', 'seed-catalog'); ?></h1>
                <p><?php esc_html_e('This tool helps diagnose issues with the Seed Catalog plugin.', 'seed-catalog'); ?></p>
                
                <?php echo do_shortcode('[seed_catalog_diagnostic]'); ?>
                
                <div class="seed-catalog-diagnostic-actions">
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=seed&page=seed-catalog-settings')); ?>" class="button"><?php esc_html_e('Go to Settings', 'seed-catalog'); ?></a>
                </div>
            </div>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }
    
    /**
     * Run diagnostic tests and collect results.
     *
     * @since    1.0.0
     * @return   array    The diagnostic results.
     */
    private function run_diagnostics() {
        $results = array();
        
        // WordPress Environment
        $results['wordpress'] = $this->check_wordpress_environment();
        
        // PHP Environment
        $results['php'] = $this->check_php_environment();
        
        // Plugin Status
        $results['plugin'] = $this->check_plugin_status();
        
        // Database Status
        $results['database'] = $this->check_database_status();
        
        // API Connectivity
        $results['api'] = $this->check_api_connectivity();
        
        return $results;
    }
    
    /**
     * Check WordPress environment.
     *
     * @since    1.0.0
     * @return   array    The WordPress environment status.
     */
    private function check_wordpress_environment() {
        global $wp_version;
        
        $results = array(
            'title' => __('WordPress Environment', 'seed-catalog'),
            'items' => array()
        );
        
        // WordPress version
        $wp_min_version = '5.6'; // Minimum required WP version
        $wp_version_status = version_compare($wp_version, $wp_min_version, '>=');
        
        $results['items'][] = array(
            'label' => __('WordPress Version', 'seed-catalog'),
            'value' => $wp_version,
            'status' => $wp_version_status ? 'good' : 'bad',
            'message' => $wp_version_status ? 
                __('Your WordPress version is up to date.', 'seed-catalog') : 
                sprintf(
                    /* translators: %s: Minimum required WordPress version */
                    __('You are using an older version of WordPress. The plugin requires at least version %s.', 'seed-catalog'),
                    $wp_min_version
                )
        );
        
        // Check if site is multisite
        $results['items'][] = array(
            'label' => __('Multisite', 'seed-catalog'),
            'value' => is_multisite() ? __('Yes', 'seed-catalog') : __('No', 'seed-catalog'),
            'status' => 'info',
            'message' => is_multisite() ? 
                __('Your site is running as a multisite network.', 'seed-catalog') : 
                __('Your site is running as a single site.', 'seed-catalog')
        );
        
        // Active theme
        $theme = wp_get_theme();
        $results['items'][] = array(
            'label' => __('Active Theme', 'seed-catalog'),
            'value' => $theme->get('Name') . ' ' . $theme->get('Version'),
            'status' => 'info',
            'message' => sprintf(
                /* translators: %1$s: Theme name, %2$s: Theme author */
                __('Theme: %1$s by %2$s', 'seed-catalog'),
                $theme->get('Name'),
                $theme->get('Author')
            )
        );
        
        return $results;
    }
    
    /**
     * Check PHP environment.
     *
     * @since    1.0.0
     * @return   array    The PHP environment status.
     */
    private function check_php_environment() {
        $results = array(
            'title' => __('PHP Environment', 'seed-catalog'),
            'items' => array()
        );
        
        // PHP version
        $php_min_version = '7.4'; // Minimum required PHP version
        $php_version_status = version_compare(PHP_VERSION, $php_min_version, '>=');
        
        $results['items'][] = array(
            'label' => __('PHP Version', 'seed-catalog'),
            'value' => PHP_VERSION,
            'status' => $php_version_status ? 'good' : 'bad',
            'message' => $php_version_status ? 
                __('Your PHP version is compatible.', 'seed-catalog') : 
                sprintf(
                    /* translators: %s: Minimum required PHP version */
                    __('You are using an older version of PHP. The plugin recommends at least version %s.', 'seed-catalog'),
                    $php_min_version
                )
        );
        
        // PHP memory limit
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->convert_to_bytes($memory_limit);
        $memory_limit_status = $memory_limit_bytes >= 64 * 1024 * 1024; // 64MB minimum
        
        $results['items'][] = array(
            'label' => __('PHP Memory Limit', 'seed-catalog'),
            'value' => $memory_limit,
            'status' => $memory_limit_status ? 'good' : 'warning',
            'message' => $memory_limit_status ? 
                __('Memory limit is sufficient.', 'seed-catalog') : 
                __('Memory limit is low and may cause issues with plugin functionality.', 'seed-catalog')
        );
        
        // PHP time limit
        $time_limit = ini_get('max_execution_time');
        $time_limit_status = ($time_limit >= 30 || $time_limit == 0); // 30 seconds minimum
        
        $results['items'][] = array(
            'label' => __('PHP Time Limit', 'seed-catalog'),
            'value' => $time_limit,
            'status' => $time_limit_status ? 'good' : 'warning',
            'message' => $time_limit_status ? 
                __('Time limit is sufficient.', 'seed-catalog') : 
                __('Time limit is low and may cause issues with API operations.', 'seed-catalog')
        );
        
        // PHP extensions
        $required_extensions = array('curl', 'json', 'mbstring');
        foreach ($required_extensions as $extension) {
            $extension_loaded = extension_loaded($extension);
            $results['items'][] = array(
                'label' => sprintf(
                    /* translators: %s: PHP extension name */
                    __('PHP Extension: %s', 'seed-catalog'),
                    $extension
                ),
                'value' => $extension_loaded ? __('Enabled', 'seed-catalog') : __('Disabled', 'seed-catalog'),
                'status' => $extension_loaded ? 'good' : 'bad',
                'message' => $extension_loaded ? 
                    sprintf(
                        /* translators: %s: PHP extension name */
                        __('The %s extension is enabled.', 'seed-catalog'),
                        $extension
                    ) : 
                    sprintf(
                        /* translators: %s: PHP extension name */
                        __('The %s extension is required but not enabled on your server.', 'seed-catalog'),
                        $extension
                    )
            );
        }
        
        return $results;
    }
    
    /**
     * Check plugin status.
     *
     * @since    1.0.0
     * @return   array    The plugin status.
     */
    private function check_plugin_status() {
        $results = array(
            'title' => __('Plugin Status', 'seed-catalog'),
            'items' => array()
        );
        
        // Plugin version
        $results['items'][] = array(
            'label' => __('Plugin Version', 'seed-catalog'),
            'value' => SEED_CATALOG_VERSION,
            'status' => 'info',
            'message' => sprintf(
                /* translators: %s: Plugin version */
                __('You are running Seed Catalog version %s.', 'seed-catalog'),
                SEED_CATALOG_VERSION
            )
        );
        
        // API Key status
        $api_key = get_option('seed_catalog_gemini_api_key', '');
        $api_key_set = !empty($api_key);
        
        $results['items'][] = array(
            'label' => __('API Key', 'seed-catalog'),
            'value' => $api_key_set ? __('Set', 'seed-catalog') : __('Not Set', 'seed-catalog'),
            'status' => $api_key_set ? 'good' : 'warning',
            'message' => $api_key_set ? 
                __('Gemini API key is set.', 'seed-catalog') : 
                __('Gemini API key is not set. Some features will not work.', 'seed-catalog')
        );
        
        // Check templates status
        $template_files = array(
            'single-seed.php' => SEED_CATALOG_PLUGIN_DIR . 'templates/single-seed.php',
            'archive-seed.php' => SEED_CATALOG_PLUGIN_DIR . 'templates/archive-seed.php',
            'diagnostic.php' => SEED_CATALOG_PLUGIN_DIR . 'templates/diagnostic.php'
        );
        
        foreach ($template_files as $template_name => $template_path) {
            $template_exists = file_exists($template_path);
            $results['items'][] = array(
                'label' => sprintf(
                    /* translators: %s: Template name */
                    __('Template: %s', 'seed-catalog'),
                    $template_name
                ),
                'value' => $template_exists ? __('Found', 'seed-catalog') : __('Missing', 'seed-catalog'),
                'status' => $template_exists ? 'good' : 'bad',
                'message' => $template_exists ? 
                    sprintf(
                        /* translators: %s: Template path */
                        __('Template file found at %s.', 'seed-catalog'),
                        $template_path
                    ) : 
                    sprintf(
                        /* translators: %s: Template path */
                        __('Template file missing from %s.', 'seed-catalog'),
                        $template_path
                    )
            );
        }
        
        return $results;
    }
    
    /**
     * Check database status.
     *
     * @since    1.0.0
     * @return   array    The database status.
     */
    private function check_database_status() {
        global $wpdb;
        
        $results = array(
            'title' => __('Database Status', 'seed-catalog'),
            'items' => array()
        );
        
        // Check if any seeds exist
        $seed_count = wp_count_posts('seed');
        $total_seeds = array_sum((array) $seed_count);
        
        $results['items'][] = array(
            'label' => __('Seed Entries', 'seed-catalog'),
            'value' => $total_seeds,
            'status' => 'info',
            'message' => sprintf(
                /* translators: %d: Number of seed entries */
                _n(
                    'Your catalog contains %d seed entry.',
                    'Your catalog contains %d seed entries.',
                    $total_seeds,
                    'seed-catalog'
                ),
                $total_seeds
            )
        );
        
        // Check if any categories exist
        $category_count = wp_count_terms(['taxonomy' => 'seed_category']);
        
        $results['items'][] = array(
            'label' => __('Seed Categories', 'seed-catalog'),
            'value' => $category_count,
            'status' => 'info',
            'message' => sprintf(
                /* translators: %d: Number of seed categories */
                _n(
                    'Your catalog contains %d seed category.',
                    'Your catalog contains %d seed categories.',
                    $category_count,
                    'seed-catalog'
                ),
                $category_count
            )
        );
        
        // Check for database tables
        $table_prefix = $wpdb->prefix;
        $plugin_tables = array(); // Add tables if using custom tables
        
        foreach ($plugin_tables as $table) {
            $table_name = $table_prefix . $table;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
            
            $results['items'][] = array(
                'label' => sprintf(
                    /* translators: %s: Table name */
                    __('Table: %s', 'seed-catalog'),
                    $table_name
                ),
                'value' => $table_exists ? __('Exists', 'seed-catalog') : __('Missing', 'seed-catalog'),
                'status' => $table_exists ? 'good' : 'bad',
                'message' => $table_exists ? 
                    sprintf(
                        /* translators: %s: Table name */
                        __('Database table %s exists.', 'seed-catalog'),
                        $table_name
                    ) : 
                    sprintf(
                        /* translators: %s: Table name */
                        __('Database table %s is missing.', 'seed-catalog'),
                        $table_name
                    )
            );
        }
        
        return $results;
    }
    
    /**
     * Check API connectivity.
     *
     * @since    1.0.0
     * @return   array    The API connectivity status.
     */
    private function check_api_connectivity() {
        $results = array(
            'title' => __('API Connectivity', 'seed-catalog'),
            'items' => array()
        );
        
        // Check Gemini API Key status
        $api_key = get_option('seed_catalog_gemini_api_key', '');
        $api_key_set = !empty($api_key);
        
        // Check if the API test class exists
        if (class_exists('Seed_Catalog_API_Test_Util')) {
            
            // API connection test
            $connection_test = array(
                'label' => __('API Connection', 'seed-catalog'),
                'value' => __('Testing...', 'seed-catalog'),
                'status' => 'info',
                'message' => __('API connection test not performed.', 'seed-catalog')
            );
            
            if ($api_key_set) {
                try {
                    $api_test = new Seed_Catalog_API_Test_Util();
                    $test_result = $api_test->test_api_connection();
                    
                    if ($test_result === true) {
                        $connection_test = array(
                            'label' => __('API Connection', 'seed-catalog'),
                            'value' => __('Success', 'seed-catalog'),
                            'status' => 'good',
                            'message' => __('Successfully connected to the Gemini API.', 'seed-catalog')
                        );
                    } else {
                        $connection_test = array(
                            'label' => __('API Connection', 'seed-catalog'),
                            'value' => __('Failed', 'seed-catalog'),
                            'status' => 'bad',
                            'message' => sprintf(
                                /* translators: %s: Error message */
                                __('Failed to connect to the Gemini API: %s', 'seed-catalog'),
                                $test_result
                            )
                        );
                    }
                } catch (\Exception $e) {
                    $connection_test = array(
                        'label' => __('API Connection', 'seed-catalog'),
                        'value' => __('Error', 'seed-catalog'),
                        'status' => 'bad',
                        'message' => sprintf(
                            /* translators: %s: Error message */
                            __('Error testing API connection: %s', 'seed-catalog'),
                            $e->getMessage()
                        )
                    );
                }
            } else {
                $connection_test = array(
                    'label' => __('API Connection', 'seed-catalog'),
                    'value' => __('Skipped', 'seed-catalog'),
                    'status' => 'warning',
                    'message' => __('API key is not set, skipping connection test.', 'seed-catalog')
                );
            }
            
            $results['items'][] = $connection_test;
        } else {
            $results['items'][] = array(
                'label' => __('API Test Utility', 'seed-catalog'),
                'value' => __('Missing', 'seed-catalog'),
                'status' => 'warning',
                'message' => __('API test utility class not found.', 'seed-catalog')
            );
        }
        
        return $results;
    }
    
    /**
     * Convert a memory limit string to bytes.
     *
     * @since    1.0.0
     * @param    string    $value    The memory limit string (e.g., '128M').
     * @return   int                 The memory limit in bytes.
     */
    private function convert_to_bytes($value) {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
                // Fall through
            case 'm':
                $value *= 1024;
                // Fall through
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}