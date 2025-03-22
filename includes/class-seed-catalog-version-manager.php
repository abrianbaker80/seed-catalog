<?php
/**
 * The class responsible for managing plugin version information.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */

namespace SeedCatalog;

/**
 * Handles version management for the plugin
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */
class Seed_Catalog_Version_Manager {

    /**
     * The current version
     *
     * @var string
     */
    private $version;

    /**
     * The plugin file path
     *
     * @var string
     */
    private $plugin_file;

    /**
     * Initialize the class
     *
     * @param string $plugin_file The main plugin file path
     */
    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->version = $this->get_current_version();
        
        // Only add WordPress hooks if we're in WordPress context
        if (defined('ABSPATH')) {
            if (is_admin()) {
                add_action('admin_init', array($this, 'check_for_version_update'));
            }
        }
    }

    /**
     * Get the current version from the plugin header
     *
     * @return string The current version
     */
    public function get_current_version() {
        if (!function_exists('get_file_contents')) {
            // Basic function to read plugin header when WordPress functions aren't available
            $content = file_get_contents($this->plugin_file);
            if (preg_match('/\* Version:\s*(.*)$/m', $content, $matches)) {
                return trim($matches[1]);
            }
            return '1.0.0'; // Default version if not found
        }
        
        // Use WordPress functions if available
        if (function_exists('get_plugin_data')) {
            $plugin_data = get_plugin_data($this->plugin_file);
            return $plugin_data['Version'];
        }
        
        return '1.0.0';
    }

    /**
     * Check if a new version should be generated based on certain conditions
     */
    public function check_for_version_update() {
        // Only proceed for admin users with sufficient permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get the last updated time of the main plugin file
        $file_modified_time = filemtime($this->plugin_file);
        
        // Get the last time we updated the version
        $last_version_update = get_option('seed_catalog_last_version_update', 0);
        
        // If the file was modified after the last version update, increment the version
        if ($file_modified_time > $last_version_update) {
            $this->increment_version();
            
            // Store the current time as the last update time
            update_option('seed_catalog_last_version_update', time());
        }
    }

    /**
     * Increment the plugin version and update relevant files
     */
    public function increment_version() {
        // Parse current version
        $version_parts = explode('.', $this->version);
        
        // Increment the patch version (third number)
        if (count($version_parts) >= 3) {
            $version_parts[2] = intval($version_parts[2]) + 1;
        } else {
            // If we don't have a three-part version, just add .1
            $version_parts[] = '1';
        }
        
        // Create the new version string
        $new_version = implode('.', $version_parts);
        
        // Update the version in the plugin file
        $this->update_version_in_file($new_version);
        
        // Update WordPress option if we're in WordPress context
        if (function_exists('update_option')) {
            update_option('seed_catalog_version', $new_version);
        }
        
        // Update the class property
        $this->version = $new_version;
        
        return $new_version;
    }

    /**
     * Update the version number in the plugin header
     *
     * @param string $new_version The new version number
     * @return bool Whether the update was successful
     */
    private function update_version_in_file($new_version) {
        // Read the plugin file
        $plugin_content = file_get_contents($this->plugin_file);
        
        if ($plugin_content === false) {
            return false;
        }
        
        // Update version in plugin header
        $plugin_content = preg_replace(
            '/\* Version:\s*(.*)$/m',
            '* Version: ' . $new_version,
            $plugin_content
        );
        
        // Update version constant
        $plugin_content = preg_replace(
            '/define\s*\(\s*[\'"]SEED_CATALOG_VERSION[\'"]\s*,\s*[\'"](.*)[\'"]\s*\)/m',
            'define(\'SEED_CATALOG_VERSION\', \'' . $new_version . '\')',
            $plugin_content
        );
        
        // Write the modified content back to the file
        $result = file_put_contents($this->plugin_file, $plugin_content);
        
        return ($result !== false);
    }

    /**
     * Force a version bump (for testing purposes)
     * 
     * @return string The new version
     */
    public function force_version_bump() {
        if (defined('ABSPATH') && !current_user_can('manage_options')) {
            return $this->version;
        }
        
        return $this->increment_version();
    }
}