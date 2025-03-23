<?php
/**
 * The meta boxes functionality of the plugin.
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
 * The class responsible for creating meta boxes and saving custom field data.
 *
 * @since      1.0.0
 */
class Seed_Catalog_Meta_Boxes {

    /**
     * Add meta boxes for the seed post type.
     *
     * @since    1.0.0
     */
    public function add_seed_meta_boxes() {
        // Ensure translations are loaded before adding meta boxes
        if (!did_action('init')) {
            add_action('init', array($this, 'add_seed_meta_boxes'), 20);
            return;
        }
        
        add_meta_box(
            'seed_details',
            __('Seed Details', 'seed-catalog'),
            array($this, 'render_seed_details_meta_box'),
            'seed',
            'normal',
            'high'
        );

        add_meta_box(
            'planting_info',
            __('Planting Information', 'seed-catalog'),
            array($this, 'render_planting_info_meta_box'),
            'seed',
            'normal',
            'high'
        );

        add_meta_box(
            'growing_info',
            __('Growing Information', 'seed-catalog'),
            array($this, 'render_growing_info_meta_box'),
            'seed',
            'normal',
            'high'
        );
        
        add_meta_box(
            'advanced_seed_info',
            __('Advanced Seed Information', 'seed-catalog'),
            array($this, 'render_advanced_seed_info_meta_box'),
            'seed',
            'normal',
            'high'
        );
        
        add_meta_box(
            'gemini_api_info',
            __('AI-Assisted Information', 'seed-catalog'),
            array($this, 'render_gemini_api_meta_box'),
            'seed',
            'normal',
            'high'
        );
    }

    /**
     * Render the seed details meta box.
     *
     * @since    1.0.0
     * @param    \WP_Post    $post    The post object.
     */
    public function render_seed_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('seed_catalog_save_meta_data', 'seed_catalog_meta_nonce');

        // Get meta values
        $seed_name = get_post_meta($post->ID, 'seed_name', true);
        $seed_variety = get_post_meta($post->ID, 'seed_variety', true);
        $purchase_date = get_post_meta($post->ID, 'purchase_date', true);
        $notes = get_post_meta($post->ID, 'notes', true);
        $plant_type = get_post_meta($post->ID, 'plant_type', true);
        $seed_type = get_post_meta($post->ID, 'seed_type', true);

        // Output fields
        ?>
        <div class="seed-catalog-form-field">
            <label for="seed_name"><?php esc_html_e('Seed Name', 'seed-catalog'); ?></label>
            <input type="text" id="seed_name" name="seed_name" value="<?php echo esc_attr($seed_name); ?>" class="widefat">
            <p class="description"><?php esc_html_e('Enter the name of the seed.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="seed_variety"><?php esc_html_e('Variety', 'seed-catalog'); ?></label>
            <input type="text" id="seed_variety" name="seed_variety" value="<?php echo esc_attr($seed_variety); ?>" class="widefat">
            <p class="description"><?php esc_html_e('Enter the seed variety if applicable.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="purchase_date"><?php esc_html_e('Purchase Date', 'seed-catalog'); ?></label>
            <input type="date" id="purchase_date" name="purchase_date" value="<?php echo esc_attr($purchase_date); ?>">
            <p class="description"><?php esc_html_e('Date the seeds were purchased.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="plant_type"><?php esc_html_e('Plant Type', 'seed-catalog'); ?></label>
            <select id="plant_type" name="plant_type">
                <option value=""><?php esc_html_e('Select plant type', 'seed-catalog'); ?></option>
                <option value="Annual" <?php selected($plant_type, 'Annual'); ?>><?php esc_html_e('Annual', 'seed-catalog'); ?></option>
                <option value="Perennial" <?php selected($plant_type, 'Perennial'); ?>><?php esc_html_e('Perennial', 'seed-catalog'); ?></option>
                <option value="Biennial" <?php selected($plant_type, 'Biennial'); ?>><?php esc_html_e('Biennial', 'seed-catalog'); ?></option>
                <option value="Bulb" <?php selected($plant_type, 'Bulb'); ?>><?php esc_html_e('Bulb', 'seed-catalog'); ?></option>
                <option value="Herb" <?php selected($plant_type, 'Herb'); ?>><?php esc_html_e('Herb', 'seed-catalog'); ?></option>
                <option value="Vegetable" <?php selected($plant_type, 'Vegetable'); ?>><?php esc_html_e('Vegetable', 'seed-catalog'); ?></option>
                <option value="Fruit" <?php selected($plant_type, 'Fruit'); ?>><?php esc_html_e('Fruit', 'seed-catalog'); ?></option>
                <option value="Flower" <?php selected($plant_type, 'Flower'); ?>><?php esc_html_e('Flower', 'seed-catalog'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Select the type of plant.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="seed_type"><?php esc_html_e('Seed Type', 'seed-catalog'); ?></label>
            <select id="seed_type" name="seed_type">
                <option value=""><?php esc_html_e('Select seed type', 'seed-catalog'); ?></option>
                <option value="Organic" <?php selected($seed_type, 'Organic'); ?>><?php esc_html_e('Organic', 'seed-catalog'); ?></option>
                <option value="Heirloom" <?php selected($seed_type, 'Heirloom'); ?>><?php esc_html_e('Heirloom', 'seed-catalog'); ?></option>
                <option value="Hybrid" <?php selected($seed_type, 'Hybrid'); ?>><?php esc_html_e('Hybrid', 'seed-catalog'); ?></option>
                <option value="Open Pollinated" <?php selected($seed_type, 'Open Pollinated'); ?>><?php esc_html_e('Open Pollinated', 'seed-catalog'); ?></option>
                <option value="GMO-free" <?php selected($seed_type, 'GMO-free'); ?>><?php esc_html_e('GMO-free', 'seed-catalog'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Select the type of seed.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="notes"><?php esc_html_e('Notes', 'seed-catalog'); ?></label>
            <textarea id="notes" name="notes" class="widefat" rows="4"><?php echo esc_textarea($notes); ?></textarea>
            <p class="description"><?php esc_html_e('Any additional notes about the seeds.', 'seed-catalog'); ?></p>
        </div>
        <?php
    }

    /**
     * Render the planting information meta box.
     *
     * @since    1.0.0
     * @param    \WP_Post    $post    The post object.
     */
    public function render_planting_info_meta_box($post) {
        // Get meta values
        $days_to_maturity = get_post_meta($post->ID, 'days_to_maturity', true);
        $planting_depth = get_post_meta($post->ID, 'planting_depth', true);
        $planting_spacing = get_post_meta($post->ID, 'planting_spacing', true);
        $companion_plants = get_post_meta($post->ID, 'companion_plants', true);
        $growth_habit = get_post_meta($post->ID, 'growth_habit', true);
        $plant_size = get_post_meta($post->ID, 'plant_size', true);

        // Output fields
        ?>
        <div class="seed-catalog-form-field">
            <label for="days_to_maturity"><?php esc_html_e('Days to Maturity', 'seed-catalog'); ?></label>
            <input type="number" id="days_to_maturity" name="days_to_maturity" value="<?php echo esc_attr($days_to_maturity); ?>" min="0" class="small-text">
            <p class="description"><?php esc_html_e('Number of days until the plant matures.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="planting_depth"><?php esc_html_e('Planting Depth', 'seed-catalog'); ?></label>
            <input type="text" id="planting_depth" name="planting_depth" value="<?php echo esc_attr($planting_depth); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('Recommended planting depth.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="planting_spacing"><?php esc_html_e('Planting Spacing', 'seed-catalog'); ?></label>
            <input type="text" id="planting_spacing" name="planting_spacing" value="<?php echo esc_attr($planting_spacing); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('Recommended planting spacing.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="growth_habit"><?php esc_html_e('Growth Habit', 'seed-catalog'); ?></label>
            <select id="growth_habit" name="growth_habit">
                <option value=""><?php esc_html_e('Select growth habit', 'seed-catalog'); ?></option>
                <option value="Vine" <?php selected($growth_habit, 'Vine'); ?>><?php esc_html_e('Vine', 'seed-catalog'); ?></option>
                <option value="Bush" <?php selected($growth_habit, 'Bush'); ?>><?php esc_html_e('Bush', 'seed-catalog'); ?></option>
                <option value="Upright" <?php selected($growth_habit, 'Upright'); ?>><?php esc_html_e('Upright', 'seed-catalog'); ?></option>
                <option value="Spreading" <?php selected($growth_habit, 'Spreading'); ?>><?php esc_html_e('Spreading', 'seed-catalog'); ?></option>
                <option value="Climbing" <?php selected($growth_habit, 'Climbing'); ?>><?php esc_html_e('Climbing', 'seed-catalog'); ?></option>
                <option value="Mounding" <?php selected($growth_habit, 'Mounding'); ?>><?php esc_html_e('Mounding', 'seed-catalog'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('How the plant grows.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="plant_size"><?php esc_html_e('Plant Size', 'seed-catalog'); ?></label>
            <input type="text" id="plant_size" name="plant_size" value="<?php echo esc_attr($plant_size); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('Expected mature size (height and spread).', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="companion_plants"><?php esc_html_e('Companion Plants', 'seed-catalog'); ?></label>
            <textarea id="companion_plants" name="companion_plants" class="widefat" rows="3"><?php echo esc_textarea($companion_plants); ?></textarea>
            <p class="description"><?php esc_html_e('List of companion plants.', 'seed-catalog'); ?></p>
        </div>
        <?php
    }

    /**
     * Render the growing information meta box.
     *
     * @since    1.0.0
     * @param    \WP_Post    $post    The post object.
     */
    public function render_growing_info_meta_box($post) {
        // Get meta values
        $sunlight_needs = get_post_meta($post->ID, 'sunlight_needs', true);
        $watering_requirements = get_post_meta($post->ID, 'watering_requirements', true);
        $harvesting_tips = get_post_meta($post->ID, 'harvesting_tips', true);
        $pest_disease_info = get_post_meta($post->ID, 'pest_disease_info', true);
        $hardiness_zones = get_post_meta($post->ID, 'hardiness_zones', true);

        // Output fields
        ?>
        <div class="seed-catalog-form-field">
            <label for="sunlight_needs"><?php esc_html_e('Sunlight Needs', 'seed-catalog'); ?></label>
            <select id="sunlight_needs" name="sunlight_needs">
                <option value=""><?php esc_html_e('Select sunlight requirements', 'seed-catalog'); ?></option>
                <option value="Full Sun" <?php selected($sunlight_needs, 'Full Sun'); ?>><?php esc_html_e('Full Sun', 'seed-catalog'); ?></option>
                <option value="Partial Sun" <?php selected($sunlight_needs, 'Partial Sun'); ?>><?php esc_html_e('Partial Sun', 'seed-catalog'); ?></option>
                <option value="Partial Shade" <?php selected($sunlight_needs, 'Partial Shade'); ?>><?php esc_html_e('Partial Shade', 'seed-catalog'); ?></option>
                <option value="Full Shade" <?php selected($sunlight_needs, 'Full Shade'); ?>><?php esc_html_e('Full Shade', 'seed-catalog'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Select the sunlight requirements.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="hardiness_zones"><?php esc_html_e('Hardiness Zones', 'seed-catalog'); ?></label>
            <input type="text" id="hardiness_zones" name="hardiness_zones" value="<?php echo esc_attr($hardiness_zones); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('USDA hardiness zones (e.g. "4-9").', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="watering_requirements"><?php esc_html_e('Watering Requirements', 'seed-catalog'); ?></label>
            <textarea id="watering_requirements" name="watering_requirements" class="widefat" rows="3"><?php echo esc_textarea($watering_requirements); ?></textarea>
            <p class="description"><?php esc_html_e('Describe watering needs.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="harvesting_tips"><?php esc_html_e('Harvesting Tips', 'seed-catalog'); ?></label>
            <textarea id="harvesting_tips" name="harvesting_tips" class="widefat" rows="3"><?php echo esc_textarea($harvesting_tips); ?></textarea>
            <p class="description"><?php esc_html_e('Tips for harvesting.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="pest_disease_info"><?php esc_html_e('Pest & Disease Information', 'seed-catalog'); ?></label>
            <textarea id="pest_disease_info" name="pest_disease_info" class="widefat" rows="3"><?php echo esc_textarea($pest_disease_info); ?></textarea>
            <p class="description"><?php esc_html_e('Common pest and disease issues and prevention methods.', 'seed-catalog'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Render the advanced seed information meta box.
     *
     * @since    1.0.0
     * @param    \WP_Post    $post    The post object.
     */
    public function render_advanced_seed_info_meta_box($post) {
        // Get meta values
        $seed_count = get_post_meta($post->ID, 'seed_count', true);
        $germination_rate = get_post_meta($post->ID, 'germination_rate', true);
        $container_suitability = get_post_meta($post->ID, 'container_suitability', true);
        $bloom_time = get_post_meta($post->ID, 'bloom_time', true);
        $flavor_profile = get_post_meta($post->ID, 'flavor_profile', true);
        $special_characteristics = get_post_meta($post->ID, 'special_characteristics', true);

        // Output fields
        ?>
        <div class="seed-catalog-form-field">
            <label for="seed_count"><?php esc_html_e('Seed Count', 'seed-catalog'); ?></label>
            <input type="text" id="seed_count" name="seed_count" value="<?php echo esc_attr($seed_count); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('Approximate number of seeds per packet.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="germination_rate"><?php esc_html_e('Germination Rate', 'seed-catalog'); ?></label>
            <input type="text" id="germination_rate" name="germination_rate" value="<?php echo esc_attr($germination_rate); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('Expected germination rate (e.g. "85%").', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="container_suitability"><?php esc_html_e('Container Suitability', 'seed-catalog'); ?></label>
            <select id="container_suitability" name="container_suitability">
                <option value=""><?php esc_html_e('Select suitability', 'seed-catalog'); ?></option>
                <option value="Excellent" <?php selected($container_suitability, 'Excellent'); ?>><?php esc_html_e('Excellent', 'seed-catalog'); ?></option>
                <option value="Good" <?php selected($container_suitability, 'Good'); ?>><?php esc_html_e('Good', 'seed-catalog'); ?></option>
                <option value="Fair" <?php selected($container_suitability, 'Fair'); ?>><?php esc_html_e('Fair', 'seed-catalog'); ?></option>
                <option value="Poor" <?php selected($container_suitability, 'Poor'); ?>><?php esc_html_e('Poor', 'seed-catalog'); ?></option>
                <option value="Not Recommended" <?php selected($container_suitability, 'Not Recommended'); ?>><?php esc_html_e('Not Recommended', 'seed-catalog'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('How well this plant grows in containers.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="bloom_time"><?php esc_html_e('Bloom Time', 'seed-catalog'); ?></label>
            <select id="bloom_time" name="bloom_time">
                <option value=""><?php esc_html_e('Select bloom time', 'seed-catalog'); ?></option>
                <option value="Spring" <?php selected($bloom_time, 'Spring'); ?>><?php esc_html_e('Spring', 'seed-catalog'); ?></option>
                <option value="Early Summer" <?php selected($bloom_time, 'Early Summer'); ?>><?php esc_html_e('Early Summer', 'seed-catalog'); ?></option>
                <option value="Mid Summer" <?php selected($bloom_time, 'Mid Summer'); ?>><?php esc_html_e('Mid Summer', 'seed-catalog'); ?></option>
                <option value="Late Summer" <?php selected($bloom_time, 'Late Summer'); ?>><?php esc_html_e('Late Summer', 'seed-catalog'); ?></option>
                <option value="Fall" <?php selected($bloom_time, 'Fall'); ?>><?php esc_html_e('Fall', 'seed-catalog'); ?></option>
                <option value="Winter" <?php selected($bloom_time, 'Winter'); ?>><?php esc_html_e('Winter', 'seed-catalog'); ?></option>
                <option value="Year Round" <?php selected($bloom_time, 'Year Round'); ?>><?php esc_html_e('Year Round', 'seed-catalog'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('When flowers typically bloom.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="flavor_profile"><?php esc_html_e('Flavor Profile', 'seed-catalog'); ?></label>
            <input type="text" id="flavor_profile" name="flavor_profile" value="<?php echo esc_attr($flavor_profile); ?>" class="widefat">
            <p class="description"><?php esc_html_e('For edible plants, describe taste characteristics.', 'seed-catalog'); ?></p>
        </div>
        
        <div class="seed-catalog-form-field">
            <label for="special_characteristics"><?php esc_html_e('Special Characteristics', 'seed-catalog'); ?></label>
            <textarea id="special_characteristics" name="special_characteristics" class="widefat" rows="3"><?php echo esc_textarea($special_characteristics); ?></textarea>
            <p class="description"><?php esc_html_e('Unique features like heat tolerance, deer resistance, etc.', 'seed-catalog'); ?></p>
        </div>
        <?php
    }

    /**
     * Render the Gemini API integration meta box.
     *
     * @since    1.0.0
     * @param    \WP_Post    $post    The post object.
     */
    public function render_gemini_api_meta_box($post) {
        ?>
        <div class="seed-catalog-form-field gemini-api-field">
            <button type="button" id="seed-catalog-ai-search" class="button button-primary">
                <?php esc_html_e('Find Information with AI', 'seed-catalog'); ?>
            </button>
            <p class="description">
                <?php esc_html_e('Use AI to help find planting information based on the seed name and variety.', 'seed-catalog'); ?>
            </p>
            <div id="seed-catalog-ai-results" class="ai-results hidden">
                <h3><?php esc_html_e('AI Suggested Information', 'seed-catalog'); ?></h3>
                <div id="seed-catalog-ai-content" class="ai-content">
                    <div class="seed-catalog-loading" style="display:none;">
                        <span class="spinner is-active"></span>
                        <?php esc_html_e('Searching for information...', 'seed-catalog'); ?>
                    </div>
                    <div id="seed-catalog-ai-suggestions"></div>
                </div>
            </div>
        </div>

        <div class="seed-catalog-form-field gemini-api-field">
            <h3><?php esc_html_e('Image-Based Seed Identification', 'seed-catalog'); ?></h3>
            <input type="file" id="seed-image-upload" name="seed_image_upload" accept="image/*">
            <button type="button" id="seed-catalog-image-recognition" class="button button-secondary">
                <?php esc_html_e('Identify Seed from Image', 'seed-catalog'); ?>
            </button>
            <div id="seed-catalog-image-results" class="image-results hidden">
                <div class="seed-catalog-loading" style="display:none;">
                    <span class="spinner is-active"></span>
                    <?php esc_html_e('Analyzing image...', 'seed-catalog'); ?>
                </div>
                <div id="seed-catalog-image-suggestions"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Save the meta box data.
     *
     * @since    1.0.0
     * @param    int       $post_id    The ID of the post being saved.
     */
    public function save_seed_meta_data($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['seed_catalog_meta_nonce'])) {
            return $post_id;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['seed_catalog_meta_nonce'], 'seed_catalog_save_meta_data')) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check the user's permissions
        if ('seed' == $_POST['post_type']) {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }

        // Update all meta fields
        $meta_fields = array(
            'seed_name', 
            'seed_variety', 
            'purchase_date',
            'plant_type',
            'seed_type',
            'days_to_maturity',
            'planting_depth',
            'planting_spacing',
            'growth_habit',
            'plant_size',
            'companion_plants',
            'sunlight_needs',
            'hardiness_zones',
            'watering_requirements',
            'harvesting_tips',
            'pest_disease_info',
            'seed_count',
            'germination_rate',
            'container_suitability',
            'bloom_time',
            'flavor_profile',
            'special_characteristics',
            'notes'
        );

        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta(
                    $post_id,
                    $field,
                    sanitize_text_field($_POST[$field])
                );
            }
        }

        // Handle textarea fields with potentially more HTML
        $textarea_fields = array(
            'notes', 
            'watering_requirements', 
            'harvesting_tips', 
            'companion_plants',
            'pest_disease_info',
            'special_characteristics'
        );
        
        foreach ($textarea_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta(
                    $post_id,
                    $field,
                    wp_kses_post($_POST[$field])
                );
            }
        }
    }

    /**
     * Save the meta box data when a seed post is saved.
     *
     * @since    1.0.0
     * @param    int       $post_id    The ID of the post being saved.
     * @param    \WP_Post  $post       The post object.
     */
    public function save_seed_meta($post_id, $post) {
        // Check if our nonce is set
        if (!isset($_POST['seed_catalog_meta_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['seed_catalog_meta_nonce'], 'seed_catalog_save_meta_data')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Ensure it's the correct post type
        if ('seed' !== $post->post_type) {
            return;
        }

        // Call the existing method to handle saving the meta data
        $this->save_seed_meta_data($post_id);
    }
}