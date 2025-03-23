<?php
/**
 * Register custom post types and taxonomies.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */

namespace SeedCatalog;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The class responsible for defining all custom post types and taxonomies used in the plugin.
 *
 * @since      1.0.0
 */
class Seed_Catalog_Post_Types {

    /**
     * Register the custom post type for seeds
     */
    public function register_seed_post_type() {
        $labels = array(
            'name'               => _x('Seeds', 'post type general name', 'seed-catalog'),
            'singular_name'      => _x('Seed', 'post type singular name', 'seed-catalog'),
            'menu_name'          => _x('Seeds', 'admin menu', 'seed-catalog'),
            'name_admin_bar'     => _x('Seed', 'add new on admin bar', 'seed-catalog'),
            'add_new'            => _x('Add New', 'seed', 'seed-catalog'),
            'add_new_item'       => __('Add New Seed', 'seed-catalog'),
            'new_item'           => __('New Seed', 'seed-catalog'),
            'edit_item'          => __('Edit Seed', 'seed-catalog'),
            'view_item'          => __('View Seed', 'seed-catalog'),
            'all_items'          => __('All Seeds', 'seed-catalog'),
            'search_items'       => __('Search Seeds', 'seed-catalog'),
            'parent_item_colon'  => __('Parent Seeds:', 'seed-catalog'),
            'not_found'          => __('No seeds found.', 'seed-catalog'),
            'not_found_in_trash' => __('No seeds found in Trash.')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Seed catalog entries.', 'seed-catalog'),
            'public'            => true,
            'publicly_queryable' => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'menu_position'     => 20,
            'menu_icon'         => 'dashicons-seedling',
            'query_var'         => true,
            'rewrite'           => array('slug' => 'seed'),
            'capability_type'   => 'post',
            'has_archive'       => true,
            'hierarchical'      => false,
            'supports'          => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
            'show_in_rest'      => true,
            'rest_base'         => 'seeds',
        );

        register_post_type('seed', $args);

        // Register settings page under Seeds menu
        add_action('admin_menu', array($this, 'register_admin_menu'));
    }

    /**
     * Register admin menu items
     */
    public function register_admin_menu() {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        add_submenu_page(
            'edit.php?post_type=seed',
            __('Seed Catalog Settings', 'seed-catalog'),
            __('Settings', 'seed-catalog'),
            'manage_options',
            'seed-catalog-settings',
            array('\SeedCatalog\Seed_Catalog_Settings', 'render_settings_page')
        );
    }

    /**
     * Register the taxonomy 'seed_category'.
     *
     * @since    1.0.0
     */
    public function register_seed_taxonomy() {
        // Make sure we're running in the init hook before processing translations
        if (!did_action('init')) {
            return;
        }

        $labels = array(
            'name'                       => _x('Seed Categories', 'Taxonomy General Name', 'seed-catalog'),
            'singular_name'              => _x('Seed Category', 'Taxonomy Singular Name', 'seed-catalog'),
            'menu_name'                  => __('Seed Categories', 'seed-catalog'),
            'all_items'                  => __('All Categories', 'seed-catalog'),
            'parent_item'                => __('Parent Category', 'seed-catalog'),
            'parent_item_colon'          => __('Parent Category:', 'seed-catalog'),
            'new_item_name'              => __('New Category Name', 'seed-catalog'),
            'add_new_item'               => __('Add New Category', 'seed-catalog'),
            'edit_item'                  => __('Edit Category', 'seed-catalog'),
            'update_item'                => __('Update Category', 'seed-catalog'),
            'view_item'                  => __('View Category', 'seed-catalog'),
            'separate_items_with_commas' => __('Separate categories with commas', 'seed-catalog'),
            'add_or_remove_items'        => __('Add or remove categories', 'seed-catalog'),
            'choose_from_most_used'      => __('Choose from the most used', 'seed-catalog'),
            'popular_items'              => __('Popular Categories', 'seed-catalog'),
            'search_items'               => __('Search Categories', 'seed-catalog'),
            'not_found'                  => __('Not Found', 'seed-catalog'),
            'no_terms'                   => __('No categories', 'seed-catalog'),
            'items_list'                 => __('Categories list', 'seed-catalog'),
            'items_list_navigation'      => __('Categories list navigation', 'seed-catalog'),
        );

        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,  // Enable Gutenberg editor
        );

        register_taxonomy('seed_category', array('seed'), $args);
    }
}