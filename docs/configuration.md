# Configuration Guide

## Admin Settings

### General Settings

1. **Core Options**
   ```php
   // Settings available in Seeds > Settings > General
   'enable_ai_features' => true,        // Enable/disable AI integration
   'items_per_page' => 24,             // Default items per page
   'grid_columns' => 4,                // Default grid columns
   'enable_responsive' => true,        // Enable responsive design
   'image_quality' => 80,             // JPEG compression quality
   'cache_duration' => 3600,          // Cache lifetime in seconds
   ```

2. **Display Settings**
   ```php
   'template_override' => true,        // Allow theme template override
   'load_fontawesome' => true,        // Include Font Awesome
   'enable_lazyload' => true,         // Enable image lazy loading
   'show_breadcrumbs' => true,        // Show navigation breadcrumbs
   'excerpt_length' => 55,            // Excerpt word count
   ```

3. **URL Settings**
   ```php
   'seed_base' => 'seeds',            // Base URL slug
   'category_base' => 'variety',      // Category URL slug
   'tag_base' => 'attributes',        // Tag URL slug
   ```

### AI Integration

1. **Gemini API Settings**
   ```php
   'gemini_api_key' => '',            // Your API key
   'confidence_threshold' => 0.85,    // Minimum confidence score
   'enable_suggestions' => true,      // Enable growing suggestions
   'max_daily_requests' => 1000,      // API request limit
   ```

2. **Image Analysis**
   ```php
   'analyze_uploads' => true,         // Auto-analyze uploaded images
   'store_raw_results' => false,      // Store full API responses
   'max_image_size' => 5242880,      // Max upload size (5MB)
   'allowed_mime_types' => ['jpg', 'jpeg', 'png'],
   ```

### Export/Import

1. **Export Settings**
   ```php
   'export_formats' => ['csv', 'json', 'xlsx'],
   'include_images' => true,          // Include image files
   'compression' => true,             // Compress exports
   'batch_size' => 100,              // Items per export batch
   ```

2. **Import Settings**
   ```php
   'duplicate_handling' => 'update',  // update/skip/create_new
   'image_handling' => 'download',    // download/link/skip
   'validation_level' => 'strict',    // strict/moderate/loose
   ```

### Email Notifications

1. **Admin Notifications**
   ```php
   'notify_on_import' => true,        // Import completion
   'notify_on_error' => true,         // System errors
   'notification_email' => '',        // Override admin email
   ```

2. **User Notifications**
   ```php
   'enable_stock_alerts' => true,     // Low stock notifications
   'stock_threshold' => 5,           // Low stock threshold
   'notification_template' => '',     // Email template path
   ```

### Performance

1. **Caching**
   ```php
   'enable_cache' => true,           // Enable object caching
   'cache_prefix' => 'seed_',        // Cache key prefix
   'excluded_urls' => [],            // URLs to exclude
   'cache_headers' => true,          // Send cache headers
   ```

2. **Asset Loading**
   ```php
   'combine_assets' => true,         // Combine CSS/JS files
   'minify_assets' => true,         // Minify CSS/JS
   'defer_js' => true,             // Defer JS loading
   'preload_assets' => true,       // Preload critical assets
   ```

### Security

1. **Access Control**
   ```php
   'required_capability' => 'edit_seeds',
   'enable_api' => true,            // Enable REST API
   'api_authentication' => true,    // Require authentication
   'allowed_ip_ranges' => [],       // IP whitelist
   ```

2. **Upload Security**
   ```php
   'sanitize_filenames' => true,    // Clean uploaded filenames
   'scan_uploads' => true,         // Scan for malware
   'max_upload_size' => 10485760,  // 10MB max upload
   ```

## Advanced Configuration

### Custom Post Type

1. **Labels**
   ```php
   'labels' => [
       'name' => 'Seeds',
       'singular_name' => 'Seed',
       // ...custom labels
   ]
   ```

2. **Capabilities**
   ```php
   'capabilities' => [
       'edit_post' => 'edit_seed',
       'edit_posts' => 'edit_seeds',
       // ...custom capabilities
   ]
   ```

### Meta Boxes

1. **Field Configuration**
   ```php
   'meta_boxes' => [
       'growing_info' => [
           'title' => 'Growing Information',
           'context' => 'normal',
           'priority' => 'high',
           'fields' => [
               // ...field definitions
           ]
       ]
   ]
   ```

2. **Field Types**
   ```php
   'field_types' => [
       'text',
       'textarea',
       'select',
       'radio',
       'checkbox',
       'date',
       'color',
       'image'
   ]
   ```

### Template System

1. **Override Locations**
   ```php
   'template_paths' => [
       get_stylesheet_directory() . '/seed-catalog',
       get_template_directory() . '/seed-catalog',
       plugin_dir_path(__FILE__) . 'templates'
   ]
   ```

2. **Template Parts**
   ```php
   'template_parts' => [
       'archive' => 'archive-seed.php',
       'single' => 'single-seed.php',
       'category' => 'taxonomy-seed-category.php'
   ]
   ```

### Hooks and Filters

1. **Action Hooks**
   ```php
   // Available action hooks
   do_action('seed_catalog_init');
   do_action('seed_catalog_admin_init');
   do_action('seed_catalog_frontend_init');
   do_action('seed_catalog_api_init');
   ```

2. **Filter Hooks**
   ```php
   // Available filter hooks
   apply_filters('seed_catalog_settings', $settings);
   apply_filters('seed_catalog_meta_fields', $fields);
   apply_filters('seed_catalog_template', $template);
   apply_filters('seed_catalog_query_args', $args);
   ```

### Database

1. **Custom Tables**
   ```sql
   $table_prefix . 'seed_catalog_stats'
   $table_prefix . 'seed_catalog_logs'
   $table_prefix . 'seed_catalog_relationships'
   ```

2. **Indexes**
   ```sql
   KEY `seed_type_idx` (`seed_type`)
   KEY `taxonomy_idx` (`taxonomy`)
   KEY `meta_key_idx` (`meta_key`)
   ```

### REST API

1. **Endpoints**
   ```php
   'api_endpoints' => [
       'seeds' => true,
       'categories' => true,
       'tags' => true,
       'search' => true
   ]
   ```

2. **API Settings**
   ```php
   'api_version' => 'v1',
   'api_base' => 'seed-catalog/v1',
   'enable_cors' => true,
   'cors_origins' => ['*']
   ```

## Configuration Files

### Constants
```php
// wp-config.php or plugin file
define('SEED_CATALOG_DEBUG', false);
define('SEED_CATALOG_PATH', plugin_dir_path(__FILE__));
define('SEED_CATALOG_URL', plugin_dir_url(__FILE__));
define('SEED_CATALOG_VERSION', '1.0.0');
```

### Environment Variables
```env
# .env
GEMINI_API_KEY=your-api-key-here
SEED_CATALOG_ENV=production
SEED_CATALOG_DEBUG=false
SEED_CATALOG_CACHE_PATH=/path/to/cache
```

## Using wp-config.php
```php
// WordPress configuration overrides
define('SEED_CATALOG_FORCE_SSL', true);
define('SEED_CATALOG_DISABLE_API', false);
define('SEED_CATALOG_MEMORY_LIMIT', '256M');
```

## Command Line

### WP-CLI Commands
```bash
# Available commands
wp seed-catalog reset-cache
wp seed-catalog rebuild-index
wp seed-catalog export [format]
wp seed-catalog import [file]
wp seed-catalog optimize-images
```