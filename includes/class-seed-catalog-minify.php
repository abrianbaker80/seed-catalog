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
     * Minify JavaScript content
     *
     * @param string $js The JavaScript content to minify
     * @return string The minified JavaScript
     */
    public static function js($js) {
        if (!class_exists('JSMin\JSMin')) {
            require_once SEED_CATALOG_PLUGIN_DIR . 'vendor/jsmin/jsmin.php';
        }
        
        try {
            return JSMin::minify($js);
        } catch (Exception $e) {
            // If minification fails, return the original content
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
        
        // If minified version doesn't exist or is older than source
        if (!file_exists($min_file) || filemtime($min_file) < filemtime($file)) {
            self::create_minified_file($file, $pathinfo['extension']);
        }
        
        return str_replace(
            SEED_CATALOG_PLUGIN_DIR,
            SEED_CATALOG_PLUGIN_URL,
            $min_file
        );
    }
}