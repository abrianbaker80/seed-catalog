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
     * Register the custom post type 'seed'.
     *
     * @since    1.0.0
     */
    public function register_seed_post_type() {
        // Make sure we're running in the init hook before processing translations
        if (!did_action('init')) {
            return;
        }

        $labels = array(
            'name'                  => _x('Seeds', 'Post Type General Name', 'seed-catalog'),
            'singular_name'         => _x('Seed', 'Post Type Singular Name', 'seed-catalog'),
            'menu_name'             => __('Seeds', 'seed-catalog'),
            'name_admin_bar'        => __('Seed', 'seed-catalog'),
            'archives'              => __('Seed Archives', 'seed-catalog'),
            'attributes'            => __('Seed Attributes', 'seed-catalog'),
            'parent_item_colon'     => __('Parent Seed:', 'seed-catalog'),
            'all_items'             => __('All Seeds', 'seed-catalog'),
            'add_new_item'          => __('Add New Seed', 'seed-catalog'),
            'add_new'               => __('Add New', 'seed-catalog'),
            'new_item'              => __('New Seed', 'seed-catalog'),
            'edit_item'             => __('Edit Seed', 'seed-catalog'),
            'update_item'           => __('Update Seed', 'seed-catalog'),
            'view_item'             => __('View Seed', 'seed-catalog'),
            'view_items'            => __('View Seeds', 'seed-catalog'),
            'search_items'          => __('Search Seed', 'seed-catalog'),
            'not_found'             => __('Not found', 'seed-catalog'),
            'not_found_in_trash'    => __('Not found in Trash', 'seed-catalog'),
            'featured_image'        => __('Plant Image', 'seed-catalog'),
            'set_featured_image'    => __('Set plant image', 'seed-catalog'),
            'remove_featured_image' => __('Remove plant image', 'seed-catalog'),
            'use_featured_image'    => __('Use as plant image', 'seed-catalog'),
            'insert_into_item'      => __('Insert into seed', 'seed-catalog'),
            'uploaded_to_this_item' => __('Uploaded to this seed', 'seed-catalog'),
            'items_list'            => __('Seeds list', 'seed-catalog'),
            'items_list_navigation' => __('Seeds list navigation', 'seed-catalog'),
            'filter_items_list'     => __('Filter seeds list', 'seed-catalog'),
        );

        $args = array(
            'label'                 => __('Seed', 'seed-catalog'),
            'description'           => __('Seed catalog entries', 'seed-catalog'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-seedling',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,  // Enable Gutenberg editor
            'rest_base'             => 'seeds',
        );

        register_post_type('seed', $args);
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