<?php
/**
 * The class responsible for registering and handling plugin shortcodes.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */

namespace SeedCatalog;

use \WP_Error;
use \WP_Query;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The class responsible for registering and handling plugin shortcodes.
 *
 * This class defines all shortcodes used throughout the plugin including search forms,
 * category listings, seed display grids, and submission forms.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */
class Seed_Catalog_Shortcodes {

    /**
     * Initialize the shortcodes
     */
    public function __construct() {
        add_shortcode('seed_search', array($this, 'render_search_shortcode'));
        add_shortcode('seed_categories', array($this, 'render_categories_shortcode'));
        add_shortcode('seed_list', array($this, 'render_seed_list_shortcode'));
        add_shortcode('seed_submission_form', array($this, 'seed_submission_form_shortcode'));
    }

    /**
     * Render the search form shortcode with accessibility improvements
     *
     * @param array $atts Shortcode attributes
     * @return string Rendered HTML
     */
    public function render_search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => __('Search seeds...', 'seed-catalog'),
            'button_text' => __('Search', 'seed-catalog'),
        ), $atts);

        // Buffer output
        ob_start();

        ?>
        <div class="seed-catalog-search-container" role="search">
            <form method="get" class="seed-catalog-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <div class="seed-catalog-search-box">
                    <label for="seed-catalog-search" class="screen-reader-text">
                        <?php esc_html_e('Search seeds', 'seed-catalog'); ?>
                    </label>
                    <input type="search" 
                           id="seed-catalog-search"
                           class="seed-catalog-search-field" 
                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                           value="<?php echo esc_attr(get_search_query()); ?>" 
                           name="s"
                           aria-label="<?php esc_attr_e('Search seeds', 'seed-catalog'); ?>" />
                    <input type="hidden" name="post_type" value="seed" />
                    <button type="submit" class="seed-catalog-search-submit" aria-label="<?php esc_attr_e('Submit search', 'seed-catalog'); ?>">
                        <?php echo esc_html($atts['button_text']); ?>
                    </button>
                </div>
            </form>
            <div id="seed-catalog-search-results" 
                 class="seed-catalog-search-results" 
                 role="region" 
                 aria-live="polite"
                 aria-label="<?php esc_attr_e('Search results', 'seed-catalog'); ?>"></div>
        </div>
        <?php

        // Return the buffer
        return ob_get_clean();
    }

    /**
     * Render the categories shortcode with accessibility improvements
     *
     * @param array $atts Shortcode attributes
     * @return string Rendered HTML
     */
    public function render_categories_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'list', // list or dropdown
            'show_count' => true,
            'hide_empty' => false,
        ), $atts);

        $terms = get_terms(array(
            'taxonomy' => 'seed_category',
            'hide_empty' => $atts['hide_empty'],
        ));

        if (is_wp_error($terms)) {
            return '';
        }

        ob_start();

        if ($atts['style'] === 'dropdown') {
            ?>
            <div class="seed-catalog-category-dropdown-container">
                <label for="seed-catalog-category" class="screen-reader-text">
                    <?php esc_html_e('Select category', 'seed-catalog'); ?>
                </label>
                <select id="seed-catalog-category" 
                        class="seed-catalog-category-dropdown" 
                        onChange="window.location.href=this.value"
                        aria-label="<?php esc_attr_e('Seed categories', 'seed-catalog'); ?>">
                    <option value=""><?php esc_html_e('All Categories', 'seed-catalog'); ?></option>
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_url(get_term_link($term)); ?>">
                            <?php 
                            echo esc_html($term->name);
                            if ($atts['show_count']) {
                                echo ' (' . esc_html($term->count) . ')';
                            }
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php
        } else {
            ?>
            <div class="seed-catalog-categories-list" role="navigation" aria-label="<?php esc_attr_e('Seed categories', 'seed-catalog'); ?>">
                <ul role="list">
                    <?php foreach ($terms as $term) : ?>
                        <li role="listitem">
                            <a href="<?php echo esc_url(get_term_link($term)); ?>"
                               aria-label="<?php echo esc_attr(sprintf(__('View seeds in category %s', 'seed-catalog'), $term->name)); ?>">
                                <?php 
                                echo esc_html($term->name);
                                if ($atts['show_count']) {
                                    echo ' <span class="count">(' . esc_html($term->count) . ')</span>';
                                }
                                ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Render the seed list shortcode with accessibility improvements
     *
     * @param array $atts Shortcode attributes
     * @return string Rendered HTML
     */
    public function render_seed_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'posts_per_page' => 9,
            'columns' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
            'title' => '',
            'show_image' => true,
        ), $atts);

        $args = array(
            'post_type' => 'seed',
            'posts_per_page' => $atts['posts_per_page'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );

        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'seed_category',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['category']),
                ),
            );
        }

        $seeds_query = new WP_Query($args);
        
        // Buffer output
        ob_start();
        
        // Load the template file if it exists
        $template_file = SEED_CATALOG_PLUGIN_DIR . 'templates/shortcodes/seed-list.php';
        
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            // Fallback inline template with accessibility improvements
            ?>
            <div class="seed-catalog-list-container">
                <?php if (!empty($atts['title'])): ?>
                    <h2 class="seed-catalog-list-title"><?php echo esc_html($atts['title']); ?></h2>
                <?php endif; ?>

                <?php if ($seeds_query->have_posts()): ?>
                    <div class="seed-catalog-grid seed-catalog-columns-<?php echo esc_attr($atts['columns']); ?>" role="list">
                        <?php while ($seeds_query->have_posts()): $seeds_query->the_post(); 
                            $seed_name = get_post_meta(get_the_ID(), 'seed_name', true);
                            $seed_variety = get_post_meta(get_the_ID(), 'seed_variety', true);
                            $days_to_maturity = get_post_meta(get_the_ID(), 'days_to_maturity', true);
                        ?>
                            <article class="seed-catalog-item" role="listitem">
                                <div class="seed-catalog-item-inner">
                                    <?php if ($atts['show_image'] && has_post_thumbnail()): ?>
                                        <div class="seed-catalog-item-image">
                                            <a href="<?php the_permalink(); ?>" 
                                               aria-label="<?php echo esc_attr(sprintf(__('View details for %s', 'seed-catalog'), get_the_title())); ?>">
                                                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <div class="seed-catalog-item-content">
                                        <h3 class="seed-catalog-item-title">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_title(); ?>
                                            </a>
                                        </h3>

                                        <?php if (!empty($seed_name)): ?>
                                            <div class="seed-catalog-item-meta seed-name">
                                                <span class="meta-label"><?php esc_html_e('Seed:', 'seed-catalog'); ?></span>
                                                <span class="meta-value"><?php echo esc_html($seed_name); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($seed_variety)): ?>
                                            <div class="seed-catalog-item-meta seed-variety">
                                                <span class="meta-label"><?php esc_html_e('Variety:', 'seed-catalog'); ?></span>
                                                <span class="meta-value"><?php echo esc_html($seed_variety); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($days_to_maturity)): ?>
                                            <div class="seed-catalog-item-meta maturity">
                                                <span class="meta-label"><?php esc_html_e('Days to Maturity:', 'seed-catalog'); ?></span>
                                                <span class="meta-value"><?php echo esc_html($days_to_maturity); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <a href="<?php the_permalink(); ?>" 
                                           class="seed-catalog-read-more"
                                           aria-label="<?php echo esc_attr(sprintf(__('Read more about %s', 'seed-catalog'), get_the_title())); ?>">
                                            <?php esc_html_e('View Details', 'seed-catalog'); ?>
                                            <span class="screen-reader-text"><?php echo sprintf(__('about %s', 'seed-catalog'), get_the_title()); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <p class="seed-catalog-no-seeds" role="alert"><?php esc_html_e('No seeds found.', 'seed-catalog'); ?></p>
                <?php endif; ?>
            </div>
            <?php
        }
        
        // Return the buffer
        return ob_get_clean();
    }

    /**
     * Render the seed submission form shortcode
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes
     * @return   string   The HTML output for the shortcode
     */
    public function seed_submission_form_shortcode($atts) {
        // Sanitize and merge shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Submit a Seed', 'seed-catalog'),
                'submit_text' => __('Submit Seed', 'seed-catalog'),
                'redirect' => '',
                'login_required' => 'yes',
            ),
            $atts,
            'seed_submission_form'
        );
        
        // Normalize boolean attributes
        $login_required = ('yes' === strtolower($atts['login_required']));
        
        // Check if user is logged in when login is required
        if ($login_required && !is_user_logged_in()) {
            return '<div class="seed-catalog-form-message">' . 
                __('You must be logged in to submit seeds.', 'seed-catalog') . ' ' .
                sprintf(
                    '<a href="%s">%s</a>',
                    esc_url(wp_login_url(get_permalink())),
                    __('Log in', 'seed-catalog')
                ) .
                '</div>';
        }

        $success_message = '';
        $error_message = '';

        // Process form if submitted
        if (isset($_POST['seed_submission_nonce']) && 
            wp_verify_nonce(sanitize_key($_POST['seed_submission_nonce']), 'seed_submission_form')) {
            
            $process_result = $this->process_submission_form();
            if (is_wp_error($process_result)) {
                $error_message = $process_result->get_error_message();
            } else {
                $success_message = __('Thank you! Your seed submission has been received and is awaiting review.', 'seed-catalog');
                
                // Redirect if a URL was provided
                if (!empty($atts['redirect'])) {
                    $redirect_url = esc_url_raw($atts['redirect']);
                    if (!empty($redirect_url)) {
                        wp_redirect($redirect_url);
                        exit;
                    }
                }
            }
        }

        // Buffer output
        ob_start();
        
        // Load the template file if it exists
        $template_file = SEED_CATALOG_PLUGIN_DIR . 'templates/shortcodes/submission-form.php';
        
        // Pass variables to template
        $template_vars = array(
            'atts' => $atts,
            'success_message' => $success_message,
            'error_message' => $error_message,
        );
        
        if (file_exists($template_file)) {
            // Extract variables to make them available to the template
            extract($template_vars); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
            include $template_file;
        } else {
            // Use the standard submission form as fallback
            include SEED_CATALOG_PLUGIN_DIR . 'templates/shortcodes/submission-form-fallback.php';
        }
        
        // Return the buffer
        return ob_get_clean();
    }
    
    /**
     * Process the submission form data
     *
     * @since    1.1.0
     * @access   private
     * @return   mixed    WP_Error on failure, Post ID on success
     */
    private function process_submission_form() {
        // Validate required fields
        if (empty($_POST['seed_title'])) {
            return new WP_Error('missing_title', __('Please enter a title for your seed.', 'seed-catalog'));
        }
        
        // Basic validation passed, create a new seed post
        $seed_data = array(
            'post_title'    => sanitize_text_field(wp_unslash($_POST['seed_title'])),
            'post_content'  => isset($_POST['seed_description']) ? wp_kses_post(wp_unslash($_POST['seed_description'])) : '',
            'post_status'   => 'pending',
            'post_type'     => 'seed',
            'post_author'   => get_current_user_id(),
        );
        
        $post_id = wp_insert_post($seed_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Process and save custom fields
        $meta_fields = array(
            'seed_name',
            'seed_variety',
            'days_to_maturity',
            'planting_depth',
            'planting_spacing',
            'sunlight_needs',
            'watering_requirements',
            'harvesting_tips',
            'companion_plants',
        );
        
        foreach ($meta_fields as $field) {
            if (!empty($_POST[$field])) {
                // Handle numeric fields differently
                if ('days_to_maturity' === $field) {
                    update_post_meta($post_id, $field, \absint($_POST[$field]));
                } else {
                    update_post_meta($post_id, $field, sanitize_text_field(wp_unslash($_POST[$field])));
                }
            }
        }
        
        // Process categories if submitted
        if (!empty($_POST['seed_categories']) && is_array($_POST['seed_categories'])) {
            $term_ids = array_map('\absint', $_POST['seed_categories']);
            wp_set_object_terms($post_id, $term_ids, 'seed_category');
        }
        
        // Allow other plugins to hook into the form processing
        do_action('seed_catalog_after_submission_save', $post_id, $_POST);
        
        return $post_id;
    }
}