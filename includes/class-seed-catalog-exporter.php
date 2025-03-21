<?php
/**
 * The exporter functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */

namespace SeedCatalog;

use DateTime;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The class responsible for exporting seed data to Excel.
 *
 * @since      1.0.0
 */
class Seed_Catalog_Exporter {

    /**
     * Export seeds to Excel file
     *
     * @since    1.0.0
     */
    public function export_seeds_to_excel() {
        // Check for nonce security
        if (!isset($_GET['nonce']) || !\wp_verify_nonce($_GET['nonce'], 'seed_catalog_export_nonce')) {
            \wp_die(\__('Security check failed.', 'seed-catalog'));
        }

        // Check user capabilities
        if (!\current_user_can('edit_posts')) {
            \wp_die(\__('You do not have sufficient permissions to export data.', 'seed-catalog'));
        }

        // Get all seeds
        $args = array(
            'post_type' => 'seed',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );

        $seeds = \get_posts($args);

        if (empty($seeds)) {
            \wp_die(\__('No seeds found to export.', 'seed-catalog'));
        }

        // Set headers for Excel file download
        $filename = 'seed-catalog-export-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Create file handle for output
        $output = fopen('php://output', 'w');
        
        // Set UTF-8 BOM for Excel to recognize special characters
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Define column headers
        $headers = array(
            'ID',
            \__('Title', 'seed-catalog'),
            \__('Seed Name', 'seed-catalog'),
            \__('Variety', 'seed-catalog'),
            \__('Purchase Date', 'seed-catalog'),
            \__('Days to Maturity', 'seed-catalog'),
            \__('Planting Depth', 'seed-catalog'),
            \__('Planting Spacing', 'seed-catalog'),
            \__('Sunlight Needs', 'seed-catalog'),
            \__('Watering Requirements', 'seed-catalog'),
            \__('Harvesting Tips', 'seed-catalog'),
            \__('Companion Plants', 'seed-catalog'),
            \__('Notes', 'seed-catalog'),
            \__('Categories', 'seed-catalog')
        );
        
        // Write headers
        fputcsv($output, $headers);
        
        // Write data rows
        foreach ($seeds as $seed) {
            $seed_categories = \get_the_terms($seed->ID, 'seed_category');
            $categories = '';
            
            if (!empty($seed_categories) && !\is_wp_error($seed_categories)) {
                $category_names = array();
                foreach ($seed_categories as $category) {
                    $category_names[] = $category->name;
                }
                $categories = implode(', ', $category_names);
            }
            
            // Format the date if it exists
            $purchase_date = \get_post_meta($seed->ID, 'purchase_date', true);
            $formatted_date = '';
            if (!empty($purchase_date)) {
                $date = DateTime::createFromFormat('Y-m-d', $purchase_date);
                if ($date) {
                    $formatted_date = $date->format(\get_option('date_format'));
                } else {
                    $formatted_date = $purchase_date;
                }
            }
            
            $row = array(
                $seed->ID,
                $seed->post_title,
                \get_post_meta($seed->ID, 'seed_name', true),
                \get_post_meta($seed->ID, 'seed_variety', true),
                $formatted_date,
                \get_post_meta($seed->ID, 'days_to_maturity', true),
                \get_post_meta($seed->ID, 'planting_depth', true),
                \get_post_meta($seed->ID, 'planting_spacing', true),
                \get_post_meta($seed->ID, 'sunlight_needs', true),
                \get_post_meta($seed->ID, 'watering_requirements', true),
                \get_post_meta($seed->ID, 'harvesting_tips', true),
                \get_post_meta($seed->ID, 'companion_plants', true),
                \get_post_meta($seed->ID, 'notes', true),
                $categories
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Render export button in admin
     *
     * @since    1.0.0
     */
    public function render_export_button() {
        $export_url = \add_query_arg(
            array(
                'action' => 'seed_catalog_export',
                'nonce' => \wp_create_nonce('seed_catalog_export_nonce')
            ),
            \admin_url('admin-post.php')
        );
        
        ?>
        <div class="seed-catalog-export-container">
            <a href="<?php echo \esc_url($export_url); ?>" class="button button-primary">
                <?php \_e('Export Seed Catalog to Excel', 'seed-catalog'); ?>
            </a>
            <p class="description">
                <?php \_e('Download your complete seed catalog as a CSV file that can be opened in Excel or other spreadsheet programs.', 'seed-catalog'); ?>
            </p>
        </div>
        <?php
    }
}