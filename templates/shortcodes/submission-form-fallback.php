<?php
/**
 * Fallback template for the seed submission form.
 * This is used when the main template is not found.
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

    <form method="post" class="seed-catalog-submission-form">
        <?php wp_nonce_field('seed_submission_form', 'seed_submission_nonce'); ?>

        <div class="seed-catalog-form-field">
            <label for="seed_title"><?php esc_html_e('Seed Title *', 'seed-catalog'); ?></label>
            <input type="text" 
                   id="seed_title" 
                   name="seed_title" 
                   required 
                   aria-required="true">
        </div>

        <div class="seed-catalog-form-field">
            <label for="seed_description"><?php esc_html_e('Description', 'seed-catalog'); ?></label>
            <textarea id="seed_description" 
                      name="seed_description" 
                      rows="5"></textarea>
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
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="seed-catalog-form-submit">
            <button type="submit" class="button button-primary"><?php echo esc_html($atts['submit_text']); ?></button>
        </div>
    </form>
</div>