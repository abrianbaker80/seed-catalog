<?php
/**
 * Asset minification utility class
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */

namespace SeedCatalog;

use Exception;
use JSMin;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Asset minification utility class
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */

class Seed_Catalog_Minify {
    /**
     * Minify CSS content
     *
     * @param string $css The CSS content to minify
     * @return string The minified CSS
     */
    public static function css($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove space after colons
        $css = str_replace(': ', ':', $css);
        
        // Remove whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        
        return $css;
    }

    /**
     * Check if JSMin is available
     *
     * @return bool True if JSMin is available
     */
    private static function has_jsmin() {
        return file_exists(SEED_CATALOG_PLUGIN_DIR . 'vendor/linkorb/jsmin-php/src/jsmin-1.1.1.php');
    }

    /**
     * Minify JS content. Falls back to basic minification if JSMin isn't available
     *
     * @param string $js The JavaScript content to minify
     * @return string The minified JavaScript
     */
    public static function js($js) {
        // Basic minification if JSMin isn't available
        if (!self::has_jsmin()) {
            // Remove comments and extra whitespace
            $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
            $js = preg_replace('/\s+/', ' ', $js);
            $js = preg_replace('/\s*([\{\}\[\]\(\)=><;,:])\s*/', '$1', $js);
            return $js;
        }

        // Use JSMin if available
        require_once SEED_CATALOG_PLUGIN_DIR . 'vendor/linkorb/jsmin-php/src/jsmin-1.1.1.php';
        try {
            return \JSMin::minify($js);
        } catch (Exception $e) {
            // Log error if debug is enabled
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Seed Catalog JSMin Error: ' . $e->getMessage());
            }
            // Return original content on error
            return $js;
        }
    }

    /**
     * Create minified version of a file
     *
     * @param string $source_file Path to the source file
     * @param string $type File type ('css' or 'js')
     * @return string Path to the minified file
     */
    public static function create_minified_file($source_file, $type) {
        $content = file_get_contents($source_file);
        if ($content === false) {
            return $source_file; // Return original if can't read
        }

        // Create minified version filename
        $pathinfo = pathinfo($source_file);
        $min_file = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.min.' . $pathinfo['extension'];
        
        // Minify content based on type
        $minified = $type === 'css' ? self::css($content) : self::js($content);
        
        // Write minified file
        if (file_put_contents($min_file, $minified) === false) {
            return $source_file; // Return original if can't write
        }
        
        return $min_file;
    }

    /**
     * Get the minified version of a file URL
     *
     * @param string $url URL of the original file
     * @return string URL of the minified version
     */
    public static function get_minified_url($url) {
        // Convert URL to file path
        $file = str_replace(
            SEED_CATALOG_PLUGIN_URL,
            SEED_CATALOG_PLUGIN_DIR,
            $url
        );
        
        $pathinfo = pathinfo($file);
        $min_file = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.min.' . $pathinfo['extension'];
        
        // If minified version doesn't exist or is older than source or SCRIPT_DEBUG is enabled
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            return $url; // Use unminified version in debug mode
        }

        if (!file_exists($min_file) || filemtime($min_file) < filemtime($file)) {
            // Create minified version
            try {
                $content = file_get_contents($file);
                if ($content === false) {
                    return $url; // Return original if can't read
                }

                // Minify based on file type
                $minified = $pathinfo['extension'] === 'css' ? 
                    self::css($content) : 
                    self::js($content);
                
                if (file_put_contents($min_file, $minified) === false) {
                    return $url; // Return original if can't write
                }
            } catch (Exception $e) {
                // Log error if debug is enabled
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Seed Catalog Minify Error: ' . $e->getMessage());
                }
                return $url; // Return original URL on error
            }
        }
        
        return str_replace(
            SEED_CATALOG_PLUGIN_DIR,
            SEED_CATALOG_PLUGIN_URL,
            $min_file
        );
    }
}