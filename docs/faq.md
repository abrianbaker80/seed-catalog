# Frequently Asked Questions

## General Questions

### Q: What is the Seed Catalog plugin?
A: The Seed Catalog plugin is a comprehensive WordPress solution for managing and displaying seed collections, featuring AI-powered plant identification, detailed seed information management, and responsive display options.

### Q: What are the system requirements?
A: The plugin requires:
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+ or MariaDB 10.1+
- Google Gemini API key (for AI features)
- 64MB minimum PHP memory

## Setup & Configuration

### Q: How do I get started with the AI features?
A: Follow these steps:
1. Get a Gemini API key from Google AI Studio
2. Go to Settings > Seed Catalog
3. Enter your API key
4. Test the connection
5. Start using image recognition and auto-population features

### Q: Can I use the plugin without the AI features?
A: Yes! The AI features are optional. You can manually enter all seed information and still use all other plugin features.

### Q: How do I customize the display layout?
A: You can:
1. Use shortcode parameters to adjust grid layouts
2. Override templates in your theme
3. Add custom CSS
4. Modify settings in the admin panel

### Q: How do I set up automatic data backups?
A: The plugin includes built-in backup functionality:
1. Go to Seeds > Settings > Backup
2. Set backup frequency (daily, weekly, monthly)
3. Choose backup location (local or cloud)
4. Select data to include (seeds, categories, settings)
5. Enable automatic restoration point creation

## Features & Usage

### Q: What file formats can I use for seed images?
A: The plugin supports:
- JPEG/JPG
- PNG
- WebP
- GIF
- Maximum file size depends on your WordPress configuration

### Q: Can I import existing seed data?
A: Yes! You can:
1. Import via CSV file
2. Use the WordPress importer
3. Use the provided API endpoints
4. Bulk import through the admin interface

### Q: How do I add a seed catalog to my page?
A: Use these shortcodes:
```
[seed_list] - Display all seeds
[seed_search] - Add a search form
[seed_categories] - Show category list
```

### Q: Can I create a printable version of my catalog?
A: Yes! The plugin offers several export options:
1. Use [seed_list print="true"] to display a print-friendly version
2. Go to Seeds > Export to generate PDF catalogs
3. Enable "Print View" in the catalog display settings
4. Use the bulk action "Generate PDF" from the Seeds list

## Common Issues

### Q: Why isn't the AI recognition working?
A: Check these common causes:
1. Invalid or expired API key
2. Image too large or wrong format
3. Poor image quality
4. Network connectivity issues
5. Rate limit exceeded

### Q: The grid layout looks broken. How do I fix it?
A: Try these solutions:
1. Clear your cache
2. Check for theme conflicts
3. Verify responsive settings
4. Update to latest version
5. Check console for JavaScript errors

### Q: Can I use custom fields?
A: Yes! You can:
1. Add custom fields via code
2. Use the built-in custom fields interface
3. Extend existing fields
4. Create field templates

### Q: Why are my seed images not displaying correctly?
A: This could be due to:
1. Image size exceeding limits
2. Missing WebP support on your server
3. Caching issues
4. Theme conflicts
5. Try regenerating thumbnails via Seeds > Tools > Regenerate Images

## Performance

### Q: How can I optimize performance?
A: Best practices include:
1. Enable caching
2. Optimize images
3. Use pagination
4. Enable lazy loading
5. Minimize plugins

### Q: What's the recommended number of seeds per page?
A: We recommend:
- 12-24 seeds for grid view
- 9-16 for card view
- Use pagination for larger catalogs
- Consider user experience and load times

### Q: My catalog is slow with many seeds. How can I improve it?
A: For large catalogs:
1. Enable incremental loading
2. Use the built-in caching system
3. Implement AJAX pagination
4. Optimize database queries (Settings > Advanced)
5. Consider upgrading hosting resources

## Security

### Q: Is my API key secure?
A: Yes! We:
1. Encrypt API keys in the database
2. Use WordPress security best practices
3. Implement rate limiting
4. Provide secure API endpoints

### Q: Who can edit seed information?
A: By default:
- Administrators have full access
- Editors can manage seeds
- Authors can create/edit their seeds
- Custom roles can be configured

### Q: How can I restrict access to certain seed categories?
A: You can:
1. Use the "Category Permissions" feature
2. Assign user roles to specific categories
3. Create membership levels with the supported plugins
4. Implement content restrictions via the API
5. Set up custom capability checks

## Integration

### Q: Can I use this with my existing theme?
A: Yes! The plugin:
1. Works with any WordPress theme
2. Provides template overrides
3. Uses standard WordPress hooks
4. Includes responsive layouts

### Q: Does it work with page builders?
A: Yes, compatible with:
- Elementor
- WPBakery
- Divi Builder
- Gutenberg
- Other major page builders

### Q: Can I integrate with e-commerce plugins?
A: Yes, we offer integration with:
1. WooCommerce (built-in)
2. Easy Digital Downloads (add-on)
3. WP EasyCart
4. Custom shop solutions via our API
5. Point-of-sale systems using webhooks

## Updates & Support

### Q: How often is the plugin updated?
A: We provide:
- Monthly maintenance updates
- Quarterly feature updates
- Immediate security patches
- WordPress version compatibility updates

### Q: Where can I get help?
A: Support is available through:
1. Plugin documentation
2. Support forum
3. Email support
4. Video tutorials
5. Community forums

### Q: What is the update process?
A: Updates are simple:
1. Backup your data first
2. Use WordPress auto-updates
3. Review changelog for breaking changes
4. Test on staging environment if possible
5. Contact support if issues arise

## Advanced Usage

### Q: Can I extend the plugin?
A: Yes! You can:
1. Use provided hooks and filters
2. Create custom templates
3. Add custom shortcodes
4. Extend core classes
5. Create add-ons

### Q: Is there an API available?
A: Yes! The plugin includes:
1. RESTful API endpoints
2. Documentation for all endpoints
3. Authentication methods
4. Example implementations

### Q: Can I sync with external databases or services?
A: Yes, through several methods:
1. Use the webhook system for real-time sync
2. Schedule automated imports/exports
3. Leverage the REST API
4. Create custom integration add-ons
5. Use the built-in connector for popular services

## Troubleshooting Tips

### Q: How do I debug issues?
A: Follow these steps:
1. Enable WordPress debug mode
2. Check error logs
3. Use the built-in diagnostic tools
4. Review system status
5. Contact support with details

### Q: What should I try before contacting support?
A: Try these first:
1. Update to latest version
2. Clear cache
3. Check compatibility
4. Review error logs
5. Search documentation

### Q: How do I recover from a failed update?
A: If an update causes issues:
1. Restore from backup
2. Use the built-in rollback feature
3. Run the diagnostic tool
4. Check the error log
5. Contact support with specific errors