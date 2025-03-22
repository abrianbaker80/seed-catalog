<?php
/**
 * Fired during plugin activation.
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
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */
class Seed_Catalog_Activator {

    /**
     * Activate the plugin.
     *
     * Set up database tables, register post types, flush rewrite rules,
     * and initialize default options needed for the plugin to function properly.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Defer activation message until init
        \add_action('init', array(__CLASS__, 'add_activation_message'), 5);
        
        self::initialize_options();
        self::register_post_types();
        self::create_example_content();
        
        // Flush rewrite rules after registering custom post types
        \flush_rewrite_rules();
    }
    
    /**
     * Add activation message after translations are loaded
     */
    public static function add_activation_message() {
        \add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Seed Catalog has been activated successfully!', 'seed-catalog'); ?></p>
            </div>
            <?php
        });
    }
    
    /**
     * Initialize plugin options with default values.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function initialize_options() {
        // Set plugin version in options table
        \add_option('seed_catalog_version', SEED_CATALOG_VERSION);
        
        // Set default API key option (empty)
        if (!\get_option('seed_catalog_gemini_api_key')) {
            \add_option('seed_catalog_gemini_api_key', '');
        }
        
        // Set default display options
        if (!\get_option('seed_catalog_items_per_page')) {
            \add_option('seed_catalog_items_per_page', 12);
        }
        
        // Set default first activation flag
        \add_option('seed_catalog_first_activation', 'yes');
    }
    
    /**
     * Register custom post types and taxonomies.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function register_post_types() {
        // Temporarily register post types and taxonomies for activation
        require_once SEED_CATALOG_PLUGIN_DIR . 'includes/class-seed-catalog-post-types.php';
        $post_types = new Seed_Catalog_Post_Types();
        $post_types->register_seed_post_type();
        $post_types->register_seed_taxonomy();
    }
    
    /**
     * Create example content to help users get started.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function create_example_content() {
        // Only create sample content on first activation
        if (\get_option('seed_catalog_first_activation') !== 'yes') {
            return;
        }
        
        // Create default categories
        $default_categories = array(
            'Vegetables' => 'Seeds for growing vegetables in your garden.',
            'Fruits' => 'Seeds for growing fruits in your garden.',
            'Herbs' => 'Seeds for growing culinary and medicinal herbs.',
            'Flowers' => 'Seeds for growing ornamental flowers.'
        );
        
        foreach ($default_categories as $name => $description) {
            if (!\term_exists($name, 'seed_category')) {
                \wp_insert_term($name, 'seed_category', array(
                    'description' => $description
                ));
            }
        }
        
        // Create a sample seed entry
        $sample_tomato = array(
            'post_title' => 'Brandywine Tomato',
            'post_content' => 'The Brandywine tomato is an heirloom cultivar of tomato. It is one of the most popular home garden tomatoes in the United States. This variety consistently ranks high in taste tests.',
            'post_status' => 'publish',
            'post_type' => 'seed',
        );
        
        // Check if sample seed already exists using WP_Query instead of deprecated get_page_by_title()
        $existing_query = new \WP_Query(
            array(
                'post_type' => 'seed',
                'title' => 'Brandywine Tomato',
                'post_status' => 'any',
                'posts_per_page' => 1,
                'no_found_rows' => true,
            )
        );
        
        if (!$existing_query->have_posts()) {
            $post_id = \wp_insert_post($sample_tomato);
            
            if (!\is_wp_error($post_id) && $post_id > 0) {
                // Set the category
                $vegetable_term = \get_term_by('name', 'Vegetables', 'seed_category');
                if ($vegetable_term) {
                    \wp_set_object_terms($post_id, array($vegetable_term->term_id), 'seed_category');
                }
                
                // Add meta data
                \update_post_meta($post_id, 'seed_name', 'Tomato');
                \update_post_meta($post_id, 'seed_variety', 'Brandywine');
                \update_post_meta($post_id, 'days_to_maturity', '80-100');
                \update_post_meta($post_id, 'planting_depth', '1/4 inch');
                \update_post_meta($post_id, 'planting_spacing', '24-36 inches');
                \update_post_meta($post_id, 'sunlight_needs', 'Full Sun');
                \update_post_meta($post_id, 'watering_requirements', 'Regular, consistent watering. About 1-2 inches per week.');
                \update_post_meta($post_id, 'harvesting_tips', 'Harvest when fruits are fully colored but still firm to the touch.');
            }
        }
        
        // Update the first activation flag
        \update_option('seed_catalog_first_activation', 'no');
    }
}