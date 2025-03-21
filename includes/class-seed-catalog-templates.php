<?php
/**
 * The template functionality of the plugin.
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
 * The class responsible for handling custom templates for seed display.
 *
 * @since      1.0.0
 */
class Seed_Catalog_Templates {

    /**
     * Replace the default single post template with our custom template for seed posts
     *
     * @since    1.0.0
     * @param    string   $single_template    The path to the template
     * @return   string   Path to the template file
     */
    public function seed_single_template($single_template) {
        global $post;

        if ($post->post_type === 'seed') {
            $custom_template = SEED_CATALOG_PLUGIN_DIR . 'templates/single-seed.php';
            
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $single_template;
    }

    /**
     * Replace the default archive template with our custom template for seed archives
     *
     * @since    1.0.0
     * @param    string   $archive_template    The path to the template
     * @return   string   Path to the template file
     */
    public function seed_archive_template($archive_template) {
        if (is_post_type_archive('seed') || is_tax('seed_category')) {
            $custom_template = SEED_CATALOG_PLUGIN_DIR . 'templates/archive-seed.php';
            
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $archive_template;
    }

    /**
     * Filter to load custom template for seed taxonomies
     *
     * @since    1.0.0
     * @param    string    $template    The path of the template to include
     * @return   string    The path of the template to include
     */
    public function seed_taxonomy_template($template) {
        if (is_tax('seed_category')) {
            $custom_template = SEED_CATALOG_PLUGIN_DIR . 'templates/taxonomy-seed_category.php';
            return file_exists($custom_template) ? $custom_template : $template;
        }
        return $template;
    }

    /**
     * Display seed details in a formatted manner
     *
     * @since    1.0.0
     * @param    int      $post_id    The ID of the seed post
     * @return   void
     */
    public static function display_seed_details($post_id = null) {
        if (empty($post_id)) {
            $post_id = get_the_ID();
        }

        if (!$post_id) {
            return;
        }

        // Get all meta fields
        $seed_name = get_post_meta($post_id, 'seed_name', true);
        $seed_variety = get_post_meta($post_id, 'seed_variety', true);
        $purchase_date = get_post_meta($post_id, 'purchase_date', true);
        $notes = get_post_meta($post_id, 'notes', true);
        $days_to_maturity = get_post_meta($post_id, 'days_to_maturity', true);
        $planting_depth = get_post_meta($post_id, 'planting_depth', true);
        $planting_spacing = get_post_meta($post_id, 'planting_spacing', true);
        $sunlight_needs = get_post_meta($post_id, 'sunlight_needs', true);
        $watering_requirements = get_post_meta($post_id, 'watering_requirements', true);
        $harvesting_tips = get_post_meta($post_id, 'harvesting_tips', true);
        $companion_plants = get_post_meta($post_id, 'companion_plants', true);

        // Get additional comprehensive info
        $plant_type = get_post_meta($post_id, 'plant_type', true);
        $growth_habit = get_post_meta($post_id, 'growth_habit', true);
        $plant_size = get_post_meta($post_id, 'plant_size', true);
        $seed_count = get_post_meta($post_id, 'seed_count', true);
        $seed_type = get_post_meta($post_id, 'seed_type', true);
        $germination_rate = get_post_meta($post_id, 'germination_rate', true);
        $hardiness_zones = get_post_meta($post_id, 'hardiness_zones', true);
        $container_suitability = get_post_meta($post_id, 'container_suitability', true);
        $bloom_time = get_post_meta($post_id, 'bloom_time', true);
        $flavor_profile = get_post_meta($post_id, 'flavor_profile', true);
        $special_characteristics = get_post_meta($post_id, 'special_characteristics', true);
        $pest_disease_info = get_post_meta($post_id, 'pest_disease_info', true);

        // Format purchase date if exists
        $formatted_date = '';
        if (!empty($purchase_date)) {
            $date = \DateTime::createFromFormat('Y-m-d', $purchase_date);
            if ($date) {
                $formatted_date = $date->format(get_option('date_format'));
            } else {
                $formatted_date = $purchase_date;
            }
        }

        // Display seed details
        ?>
        <div class="seed-catalog-details">
            <div class="seed-catalog-section seed-catalog-basic-info">
                <h3><?php _e('Seed Information', 'seed-catalog'); ?></h3>
                <div class="seed-catalog-details-grid">
                    <?php if (!empty($seed_name)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Seed Name', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($seed_name); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($seed_variety)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Variety', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($seed_variety); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($formatted_date)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Purchase Date', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($formatted_date); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($plant_type)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Plant Type', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($plant_type); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($seed_type)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Seed Type', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($seed_type); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($days_to_maturity) || !empty($planting_depth) || !empty($planting_spacing)) : ?>
            <div class="seed-catalog-section seed-catalog-planting-info">
                <h3><?php _e('Planting Information', 'seed-catalog'); ?></h3>
                <div class="seed-catalog-details-grid">
                    <?php if (!empty($days_to_maturity)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Days to Maturity', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($days_to_maturity); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($planting_depth)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Planting Depth', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($planting_depth); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($planting_spacing)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Spacing', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($planting_spacing); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($growth_habit)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Growth Habit', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($growth_habit); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($plant_size)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Plant Size', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($plant_size); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sunlight_needs) || !empty($watering_requirements) || !empty($harvesting_tips)) : ?>
            <div class="seed-catalog-section seed-catalog-care-info">
                <h3><?php _e('Care Instructions', 'seed-catalog'); ?></h3>
                <div class="seed-catalog-details-grid">
                    <?php if (!empty($sunlight_needs)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Sunlight Needs', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($sunlight_needs); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($watering_requirements)) : ?>
                    <div class="seed-catalog-detail-item seed-catalog-detail-full">
                        <span class="seed-catalog-detail-label"><?php _e('Watering Requirements', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo wp_kses_post($watering_requirements); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($harvesting_tips)) : ?>
                    <div class="seed-catalog-detail-item seed-catalog-detail-full">
                        <span class="seed-catalog-detail-label"><?php _e('Harvesting Tips', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo wp_kses_post($harvesting_tips); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pest_disease_info)) : ?>
                    <div class="seed-catalog-detail-item seed-catalog-detail-full">
                        <span class="seed-catalog-detail-label"><?php _e('Pest & Disease Information', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo wp_kses_post($pest_disease_info); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($companion_plants) || !empty($hardiness_zones) || !empty($container_suitability)) : ?>
            <div class="seed-catalog-section seed-catalog-additional-info">
                <h3><?php _e('Additional Information', 'seed-catalog'); ?></h3>
                <div class="seed-catalog-details-grid">
                    <?php if (!empty($companion_plants)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Companion Plants', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($companion_plants); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($hardiness_zones)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Hardiness Zones', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($hardiness_zones); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($container_suitability)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Container Suitability', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($container_suitability); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($special_characteristics)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Special Characteristics', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($special_characteristics); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($seed_count) || !empty($germination_rate) || !empty($bloom_time) || !empty($flavor_profile)) : ?>
            <div class="seed-catalog-section seed-catalog-advanced-info">
                <h3><?php _e('Advanced Details', 'seed-catalog'); ?></h3>
                <div class="seed-catalog-details-grid">
                    <?php if (!empty($seed_count)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Seed Count', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($seed_count); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($germination_rate)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Germination Rate', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($germination_rate); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($bloom_time)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Bloom Time', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($bloom_time); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($flavor_profile)) : ?>
                    <div class="seed-catalog-detail-item">
                        <span class="seed-catalog-detail-label"><?php _e('Flavor Profile', 'seed-catalog'); ?></span>
                        <span class="seed-catalog-detail-value"><?php echo esc_html($flavor_profile); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($notes)) : ?>
            <div class="seed-catalog-section seed-catalog-notes">
                <h3><?php _e('Notes', 'seed-catalog'); ?></h3>
                <div class="seed-catalog-detail-item seed-catalog-detail-full">
                    <div class="seed-catalog-detail-value"><?php echo wp_kses_post($notes); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="seed-catalog-section seed-catalog-categories">
                <?php
                $terms = get_the_terms($post_id, 'seed_category');
                if (!empty($terms) && !is_wp_error($terms)) : ?>
                    <h3><?php _e('Categories', 'seed-catalog'); ?></h3>
                    <div class="seed-catalog-categories-list">
                        <?php
                        $term_links = array();
                        foreach ($terms as $term) {
                            $term_links[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                        }
                        echo implode(', ', $term_links);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}