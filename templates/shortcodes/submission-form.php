<?php
/**
 * Template for rendering the seed submission form.
 *
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/templates/shortcodes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="seed-catalog-submission-form-container">
    <?php if (!empty($success_message)): ?>
        <div class="seed-catalog-message success" role="alert">
            <?php echo esc_html($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="seed-catalog-message error" role="alert">
            <?php echo esc_html($error_message); ?>
        </div>
    <?php endif; ?>

    <h2><?php echo esc_html($atts['title']); ?></h2>

    <form method="post" class="seed-catalog-submission-form" enctype="multipart/form-data">
        <?php wp_nonce_field('seed_submission_form', 'seed_submission_nonce'); ?>

        <div class="seed-catalog-form-field">
            <label for="seed_title"><?php esc_html_e('Seed Title *', 'seed-catalog'); ?></label>
            <input type="text" 
                   id="seed_title" 
                   name="seed_title" 
                   required 
                   aria-required="true"
                   value="<?php echo isset($_POST['seed_title']) ? esc_attr($_POST['seed_title']) : ''; ?>">
            <p class="description"><?php esc_html_e('Enter a title for your seed submission.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="seed_name"><?php esc_html_e('Seed Name', 'seed-catalog'); ?></label>
            <input type="text" 
                   id="seed_name" 
                   name="seed_name"
                   value="<?php echo isset($_POST['seed_name']) ? esc_attr($_POST['seed_name']) : ''; ?>">
            <p class="description"><?php esc_html_e('Common name of the seed.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="seed_variety"><?php esc_html_e('Variety', 'seed-catalog'); ?></label>
            <input type="text" 
                   id="seed_variety" 
                   name="seed_variety"
                   value="<?php echo isset($_POST['seed_variety']) ? esc_attr($_POST['seed_variety']) : ''; ?>">
            <p class="description"><?php esc_html_e('Specific variety of the seed if known.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="days_to_maturity"><?php esc_html_e('Days to Maturity', 'seed-catalog'); ?></label>
            <input type="number" 
                   id="days_to_maturity" 
                   name="days_to_maturity" 
                   min="0"
                   value="<?php echo isset($_POST['days_to_maturity']) ? esc_attr($_POST['days_to_maturity']) : ''; ?>">
            <p class="description"><?php esc_html_e('Number of days until the plant reaches maturity.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="planting_depth"><?php esc_html_e('Planting Depth', 'seed-catalog'); ?></label>
            <input type="text" 
                   id="planting_depth" 
                   name="planting_depth"
                   value="<?php echo isset($_POST['planting_depth']) ? esc_attr($_POST['planting_depth']) : ''; ?>">
            <p class="description"><?php esc_html_e('How deep to plant the seeds.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="planting_spacing"><?php esc_html_e('Planting Spacing', 'seed-catalog'); ?></label>
            <input type="text" 
                   id="planting_spacing" 
                   name="planting_spacing"
                   value="<?php echo isset($_POST['planting_spacing']) ? esc_attr($_POST['planting_spacing']) : ''; ?>">
            <p class="description"><?php esc_html_e('How far apart to space the plants.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="sunlight_needs"><?php esc_html_e('Sunlight Requirements', 'seed-catalog'); ?></label>
            <select id="sunlight_needs" name="sunlight_needs">
                <option value=""><?php esc_html_e('Select sunlight needs', 'seed-catalog'); ?></option>
                <option value="Full Sun" <?php selected(isset($_POST['sunlight_needs']) ? $_POST['sunlight_needs'] : '', 'Full Sun'); ?>><?php esc_html_e('Full Sun', 'seed-catalog'); ?></option>
                <option value="Partial Sun" <?php selected(isset($_POST['sunlight_needs']) ? $_POST['sunlight_needs'] : '', 'Partial Sun'); ?>><?php esc_html_e('Partial Sun', 'seed-catalog'); ?></option>
                <option value="Partial Shade" <?php selected(isset($_POST['sunlight_needs']) ? $_POST['sunlight_needs'] : '', 'Partial Shade'); ?>><?php esc_html_e('Partial Shade', 'seed-catalog'); ?></option>
                <option value="Full Shade" <?php selected(isset($_POST['sunlight_needs']) ? $_POST['sunlight_needs'] : '', 'Full Shade'); ?>><?php esc_html_e('Full Shade', 'seed-catalog'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Select the amount of sunlight needed.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="watering_requirements"><?php esc_html_e('Watering Requirements', 'seed-catalog'); ?></label>
            <textarea id="watering_requirements" 
                      name="watering_requirements" 
                      rows="3"><?php echo isset($_POST['watering_requirements']) ? esc_textarea($_POST['watering_requirements']) : ''; ?></textarea>
            <p class="description"><?php esc_html_e('Describe the watering needs.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="harvesting_tips"><?php esc_html_e('Harvesting Tips', 'seed-catalog'); ?></label>
            <textarea id="harvesting_tips" 
                      name="harvesting_tips" 
                      rows="3"><?php echo isset($_POST['harvesting_tips']) ? esc_textarea($_POST['harvesting_tips']) : ''; ?></textarea>
            <p class="description"><?php esc_html_e('Share tips for harvesting.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-field">
            <label for="companion_plants"><?php esc_html_e('Companion Plants', 'seed-catalog'); ?></label>
            <textarea id="companion_plants" 
                      name="companion_plants" 
                      rows="3"><?php echo isset($_POST['companion_plants']) ? esc_textarea($_POST['companion_plants']) : ''; ?></textarea>
            <p class="description"><?php esc_html_e('List plants that grow well together with this one.', 'seed-catalog'); ?></p>
        </div>

        <?php if (taxonomy_exists('seed_category')): ?>
            <div class="seed-catalog-form-field">
                <label for="seed_categories[]"><?php esc_html_e('Categories', 'seed-catalog'); ?></label>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'seed_category',
                    'hide_empty' => false,
                ));
                if (!empty($categories) && !is_wp_error($categories)): ?>
                    <select id="seed_categories" name="seed_categories[]" multiple>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Select one or more categories.', 'seed-catalog'); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="seed-catalog-form-field">
            <label for="seed_description"><?php esc_html_e('Additional Information', 'seed-catalog'); ?></label>
            <textarea id="seed_description" 
                      name="seed_description" 
                      rows="5"><?php echo isset($_POST['seed_description']) ? esc_textarea($_POST['seed_description']) : ''; ?></textarea>
            <p class="description"><?php esc_html_e('Any additional details about the seed.', 'seed-catalog'); ?></p>
        </div>

        <div class="seed-catalog-form-submit">
            <button type="submit" class="button button-primary"><?php echo esc_html($atts['submit_text']); ?></button>
        </div>
    </form>
</div>