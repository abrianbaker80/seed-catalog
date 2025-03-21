# Installation Guide

## System Requirements

### WordPress Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher

### PHP Extensions
- gd
- mbstring
- zip
- curl
- dom
- SimpleXML
- xml
- xmlreader
- xmlwriter
- fileinfo

### Memory Requirements
- PHP Memory Limit: 256M minimum
- Max Upload Size: 64M recommended
- Max Execution Time: 300 seconds

## Installation Methods

### Method 1: WordPress Plugin Directory
1. Log in to WordPress admin
2. Go to Plugins > Add New
3. Search for "Seed Catalog"
4. Click "Install Now"
5. Click "Activate"

### Method 2: Manual Upload
1. Download the latest release from [GitHub Releases](https://github.com/yourusername/seed-catalog/releases)
2. Log in to WordPress admin
3. Go to Plugins > Add New > Upload Plugin
4. Choose the downloaded zip file
5. Click "Install Now"
6. Click "Activate"

### Method 3: Composer
```bash
composer require your-vendor/seed-catalog
```

Add to your composer.json:
```json
{
    "require": {
        "your-vendor/seed-catalog": "^1.0",
        "phpoffice/phpspreadsheet": "^1.25",
        "ezyang/htmlpurifier": "^4.16"
    }
}
```

## Post-Installation Setup

### 1. Directory Permissions
```bash
# Required directories
wp-content/uploads/seed-catalog/
wp-content/uploads/seed-catalog/images/
wp-content/uploads/seed-catalog/exports/
wp-content/uploads/seed-catalog/cache/

# Set permissions
chmod 755 wp-content/uploads/seed-catalog
chmod 755 wp-content/uploads/seed-catalog/images
chmod 755 wp-content/uploads/seed-catalog/exports
chmod 755 wp-content/uploads/seed-catalog/cache
```

### 2. Database Tables
The plugin will automatically create these tables:
- wp_seed_catalog_stats
- wp_seed_catalog_logs
- wp_seed_catalog_relationships

### 3. Configuration Files

#### WordPress Configuration
Add to wp-config.php:
```php
// Optional configurations
define('SEED_CATALOG_DEBUG', false);
define('SEED_CATALOG_FORCE_SSL', true);
define('SEED_CATALOG_MEMORY_LIMIT', '256M');
```

#### Environment Variables
Create or update .env file:
```env
GEMINI_API_KEY=your-api-key-here
SEED_CATALOG_ENV=production
SEED_CATALOG_DEBUG=false
SEED_CATALOG_CACHE_PATH=/path/to/cache
```

### 4. Initial Configuration

1. **Access Settings**
   - Navigate to Seeds > Settings
   - Configure basic options
   - Set up user roles and permissions

2. **Template Setup**
   - Check theme compatibility
   - Copy template files if needed:
     - archive-seed.php
     - single-seed.php
     - taxonomy-seed-category.php

3. **Asset Configuration**
   - Configure CSS/JS loading
   - Set up responsive breakpoints
   - Configure image sizes

## Integration Steps

### 1. Theme Integration
Add to your theme's functions.php:
```php
add_theme_support('seed-catalog');
add_theme_support('seed-catalog-templates');
```

### 2. Template Hierarchy
```
├── your-theme/
│   ├── seed-catalog/
│   │   ├── archive-seed.php
│   │   ├── single-seed.php
│   │   └── taxonomy-seed-category.php
```

### 3. Shortcode Integration
```php
// Basic catalog display
[seed_catalog]

// Filtered catalog
[seed_catalog category="vegetables" tags="organic,heirloom"]

// Custom layout
[seed_catalog layout="grid" columns="4" items_per_page="12"]
```

## Plugin Dependencies

### Required Plugins
1. **Advanced Custom Fields PRO**
   - Version: 5.12 or higher
   - Purpose: Custom fields management

2. **Classic Editor**
   - Version: 1.6 or higher
   - Purpose: Content editing support

### Optional Plugins
1. **WooCommerce**
   - Version: 7.0 or higher
   - Purpose: E-commerce integration

2. **WPML**
   - Version: 4.5 or higher
   - Purpose: Multilingual support

## Updates and Maintenance

### Automatic Updates
1. Enable WordPress automatic updates
2. Configure update notifications
3. Set up backup system

### Manual Updates
```bash
# Via WP-CLI
wp plugin update seed-catalog

# Via Composer
composer update your-vendor/seed-catalog
```

## Common Issues

### 1. Permission Issues
```bash
# Fix common permission problems
sudo chown -R www-data:www-data wp-content/uploads/seed-catalog
find wp-content/uploads/seed-catalog -type d -exec chmod 755 {} \;
find wp-content/uploads/seed-catalog -type f -exec chmod 644 {} \;
```

### 2. Memory Issues
Add to wp-config.php:
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

### 3. Upload Issues
Add to php.ini:
```ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
```

## Security Recommendations

### 1. File Permissions
```bash
# Secure core files
chmod 444 wp-config.php
chmod 444 .htaccess

# Secure uploads
chmod 755 wp-content/uploads
find wp-content/uploads -type f -exec chmod 644 {} \;
```

### 2. API Security
1. Generate strong API keys
2. Implement rate limiting
3. Use SSL certificates

### 3. Database Security
1. Use strong table prefix
2. Regular backups
3. Limit database user privileges

## Health Checks

### 1. System Status
Navigate to Seeds > Tools > System Status to verify:
- PHP Version
- WordPress Version
- File Permissions
- Database Status

### 2. Performance Check
- Cache Status
- Image Optimization
- Database Optimization

### 3. Security Check
- File Integrity
- SSL Status
- API Key Validation

## Uninstallation

### Clean Uninstall
1. Deactivate plugin
2. Delete plugin files
3. Remove database tables (optional)
4. Clean up wp-content/uploads/seed-catalog

### Data Backup
```bash
# Backup database tables
wp db export seed-catalog-backup.sql

# Backup uploads
zip -r seed-catalog-uploads.zip wp-content/uploads/seed-catalog
```

## Support Resources

### Documentation
- [User Guide](docs/user-guide.md)
- [API Documentation](docs/api-documentation.md)
- [Developer Guide](docs/developer-guide.md)

### Community
- GitHub Issues
- WordPress.org Forums
- Stack Overflow Tags