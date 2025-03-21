# Seed Catalog

A comprehensive WordPress plugin for managing and displaying seed collections with AI-powered plant information retrieval.

## Features

- 🌱 Complete seed management system
- 🤖 AI-powered plant identification using Google Gemini
- 🎯 Intuitive admin interface
- 📱 Responsive, accessible design
- 🔍 Advanced search and filtering
- 📊 Data export capabilities
- ♿ WCAG 2.1 compliant

## Installation

1. Download the latest release from the [Releases page](https://github.com/abrianbaker80/seed-catalog-wp/releases)
2. Upload the plugin files to the `/wp-content/plugins/seed-catalog` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the plugin settings under "Seed Catalog" in the WordPress admin menu

## Plugin Structure

```
seed-catalog/
├── seed-catalog.php           # Main plugin file
├── uninstall.php             # Cleanup on uninstall
├── composer.json             # Composer dependencies
├── phpconfig.json           # PHP configuration
├── phpcs.xml               # Code style configuration
├── README.md               # This file
│
├── admin/                  # Admin interface components
│   ├── class-seed-catalog-admin.php
│   ├── css/
│   │   └── seed-catalog-admin.css
│   └── js/
│       └── seed-catalog-admin.js
│
├── includes/              # Core plugin classes
│   ├── class-seed-catalog.php
│   ├── class-seed-catalog-activator.php
│   ├── class-seed-catalog-api-test-util.php
│   ├── class-seed-catalog-api-test.php
│   ├── class-seed-catalog-deactivator.php
│   ├── class-seed-catalog-diagnostic.php
│   ├── class-seed-catalog-exporter.php
│   ├── class-seed-catalog-gemini-api.php
│   ├── class-seed-catalog-loader.php
│   ├── class-seed-catalog-meta-boxes.php
│   ├── class-seed-catalog-minify.php
│   ├── class-seed-catalog-post-types.php
│   ├── class-seed-catalog-shortcodes.php
│   ├── class-seed-catalog-templates.php
│   ├── class-seed-catalog-uninstaller.php
│   └── class-seed-catalog-upgrader.php
│
├── public/               # Frontend components
│   ├── class-seed-catalog-public.php
│   ├── css/
│   │   ├── seed-catalog-public.css
│   │   └── seed-catalog-responsive.css
│   └── js/
│       ├── seed-catalog-public.js
│       └── seed-catalog-responsive.js
│
├── templates/           # Template files
│   ├── archive-seed.php
│   ├── diagnostic.php
│   └── single-seed.php
│
└── docs/               # Documentation
    ├── accessibility.md
    ├── api-documentation.md
    ├── configuration.md
    ├── developer-guide.md
    ├── faq.md
    ├── getting-started.md
    ├── installation.md
    ├── shortcodes.md
    └── user-guide.md
```

## Core Components

### Main Plugin Files
- `seed-catalog.php` - Plugin initialization and setup
- `uninstall.php` - Clean uninstallation routines
- Configuration files (composer.json, phpconfig.json, phpcs.xml)

### Admin Interface (admin/)
- `class-seed-catalog-admin.php` - Admin functionality
- Admin styles and scripts for the backend interface

### Core Classes (includes/)
1. **Plugin Core**
   - `class-seed-catalog.php` - Main plugin class
   - `class-seed-catalog-loader.php` - Hook/action management
   - `class-seed-catalog-activator.php` - Installation routines
   - `class-seed-catalog-deactivator.php` - Deactivation handling
   - `class-seed-catalog-uninstaller.php` - Uninstallation cleanup

2. **Feature Classes**
   - `class-seed-catalog-post-types.php` - Custom post types
   - `class-seed-catalog-meta-boxes.php` - Custom fields
   - `class-seed-catalog-shortcodes.php` - Shortcode system
   - `class-seed-catalog-templates.php` - Template handling
   - `class-seed-catalog-exporter.php` - Data export functionality
   - `class-seed-catalog-gemini-api.php` - AI integration

3. **Utility Classes**
   - `class-seed-catalog-minify.php` - Asset optimization
   - `class-seed-catalog-diagnostic.php` - Debug tools
   - `class-seed-catalog-api-test.php` - API testing
   - `class-seed-catalog-api-test-util.php` - Testing utilities
   - `class-seed-catalog-upgrader.php` - Version upgrades

### Frontend (public/)
- `class-seed-catalog-public.php` - Public functionality
- Responsive CSS and JavaScript for the frontend

### Templates (templates/)
- `archive-seed.php` - Seed listing template
- `single-seed.php` - Individual seed display
- `diagnostic.php` - Debug information display

## Quick Start

1. Install and activate the plugin
2. Go to Settings > Seed Catalog
3. Enter your Google Gemini API key
4. Start adding seeds to your catalog!

## Documentation

Detailed documentation is available in the `docs/` directory:

- [Getting Started](docs/getting-started.md)
- [Installation](docs/installation.md)
- [Configuration](docs/configuration.md)
- [User Guide](docs/user-guide.md)
- [Developer Guide](docs/developer-guide.md)
- [API Documentation](docs/api-documentation.md)
- [Shortcodes Reference](docs/shortcodes.md)
- [Accessibility](docs/accessibility.md)
- [FAQ](docs/faq.md)

## Configuration

### API Integration

To use the AI-powered features, you'll need to:

1. Obtain a Google Gemini API key from [Google AI Studio](https://makersuite.google.com/)
2. Enter your API key in the plugin settings page

## Usage

- Use the `[seed_catalog]` shortcode to display your seed catalog on any page
- Additional shortcode parameters are available for customizing the display

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0+ License - see the LICENSE file for details.

## Acknowledgments

- AI-powered plant data provided by Google Gemini API