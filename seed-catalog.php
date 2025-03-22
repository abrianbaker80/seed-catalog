<?php
/**
 * Plugin Name: Seed Catalog
 * Plugin URI: https://github.com/abrianbaker80/seed-catalog
 * Description: A comprehensive seed catalog management system with AI-powered plant information retrieval.
 * Version: 1.0.1
 * Author: Allen Baker
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: seed-catalog
 * Domain Path: /languages
 * GitHub Plugin URI: abrianbaker80/seed-catalog
 * GitHub Plugin Branch: master
 *
 * @package Seed_Catalog
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('SEED_CATALOG_VERSION', '1.0.1');

/**
 * Define plugin constants.
 */
define('SEED_CATALOG_PLUGIN_FILE', __FILE__);
define('SEED_CATALOG_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SEED_CATALOG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SEED_CATALOG_PLUGIN_URL', plugins_url('/', __FILE__));

/**
 * The code that runs during plugin activation.
 */
function seed_catalog_activate() {
    require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-activator.php';
    SeedCatalog\Seed_Catalog_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function seed_catalog_deactivate() {
    require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-deactivator.php';
    SeedCatalog\Seed_Catalog_Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstallation.
 * This is referenced by the uninstall.php file.
 */
function seed_catalog_uninstall() {
    require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-uninstaller.php';
    SeedCatalog\Seed_Catalog_Uninstaller::uninstall();
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'seed_catalog_activate');
register_deactivation_hook(__FILE__, 'seed_catalog_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog.php';

/**
 * Load the settings class for managing API keys and OAuth credentials
 */
require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-settings.php';

/**
 * Load the version manager class
 */
require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-version-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_seed_catalog() {
    // Handle plugin initialization errors gracefully
    try {
        // Initialize version manager
        new SeedCatalog\Seed_Catalog_Version_Manager(__FILE__);
        
        // Initialize settings
        new SeedCatalog\Seed_Catalog_Settings();
        
        // Initialize the main plugin
        $plugin = new SeedCatalog\Seed_Catalog();
        $plugin->run();
    } catch (Exception $e) {
        // Log the error
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Seed Catalog Plugin Error: ' . $e->getMessage());
        }
        
        // Display admin notice if in admin area
        if (is_admin()) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="error"><p>';
                echo '<strong>Seed Catalog Error:</strong> ';
                echo esc_html($e->getMessage());
                echo '</p></div>';
            });
        }
    }
}

/**
 * Check plugin version and run any necessary updates.
 * 
 * @since    1.0.0
 */
function seed_catalog_check_version() {
    $current_version = get_option('seed_catalog_version', '0.0.0');
    
    if (version_compare($current_version, SEED_CATALOG_VERSION, '<')) {
        // Load the upgrader class
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-upgrader.php';
        
        // Run version-specific upgrade routines
        try {
            SeedCatalog\Seed_Catalog_Upgrader::upgrade($current_version, SEED_CATALOG_VERSION);
            update_option('seed_catalog_version', SEED_CATALOG_VERSION);
        } catch (Exception $e) {
            // Log update errors
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Seed Catalog Plugin Update Error: ' . $e->getMessage());
            }
            
            // Display admin notice
            if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="error"><p>';
                    echo '<strong>Seed Catalog Update Error:</strong> ';
                    echo esc_html($e->getMessage());
                    echo '</p></div>';
                });
            }
        }
    }
}

// Launch the plugin
add_action('plugins_loaded', 'seed_catalog_check_version', 10);
add_action('plugins_loaded', 'run_seed_catalog', 11);

/**
 * GitHub Plugin Updater
 * Enable updates from GitHub repository
 *
 * @since 1.0.0
 */
class Seed_Catalog_GitHub_Updater {
    private $slug;
    private $plugin_data;
    private $username;
    private $repo;
    private $plugin_file;
    private $github_response;

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
        
        $this->slug = plugin_basename($plugin_file);
        $this->plugin_data = get_plugin_data($plugin_file);
        
        // Set from GitHub Plugin URI in the plugin header
        list($this->username, $this->repo) = explode('/', trim(str_replace('https://github.com/', '', $this->plugin_data['GitHub Plugin URI'])));
    }

    // Check for updates against the GitHub repository
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get the remote version
        $this->get_repository_info();

        // If we have a new version, create the update response
        if ($this->github_response && version_compare($this->github_response['tag_name'], $this->plugin_data['Version'], '>')) {
            $plugin = array(
                'slug' => $this->slug,
                'plugin' => $this->slug,
                'new_version' => $this->github_response['tag_name'],
                'url' => $this->plugin_data['PluginURI'],
                'package' => $this->github_response['zipball_url'],
            );
            $transient->response[$this->slug] = (object) $plugin;
        }

        return $transient;
    }

    // Push in plugin version information to display in the details popup
    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!empty($args->slug) && $args->slug === $this->slug) {
            $this->get_repository_info();

            if ($this->github_response) {
                $plugin = array(
                    'name'              => $this->plugin_data['Name'],
                    'slug'              => $this->slug,
                    'version'           => $this->github_response['tag_name'],
                    'author'            => $this->plugin_data['AuthorName'],
                    'author_profile'    => $this->plugin_data['AuthorURI'],
                    'last_updated'      => $this->github_response['published_at'],
                    'homepage'          => $this->plugin_data['PluginURI'],
                    'short_description' => $this->plugin_data['Description'],
                    'sections'          => array(
                        'Description'   => $this->plugin_data['Description'],
                        'Updates'       => $this->github_response['body'],
                    ),
                    'download_link'     => $this->github_response['zipball_url']
                );
                return (object) $plugin;
            }
        }

        return $result;
    }

    // Get GitHub repository info
    private function get_repository_info() {
        if (!empty($this->github_response)) {
            return;
        }

        // Query the GitHub API
        $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repo);
        
        // Get the response
        $response = wp_remote_get($request_uri);
        
        // If we get a valid response
        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $response_body = wp_remote_retrieve_body($response);
            $this->github_response = json_decode($response_body, true);
        }
    }

    // Rename the plugin folder after installation
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path($this->plugin_file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        // Activate plugin
        activate_plugin($this->slug);

        return $result;
    }
}

// Initialize GitHub updater
if (is_admin()) {
    new Seed_Catalog_GitHub_Updater(__FILE__);
}