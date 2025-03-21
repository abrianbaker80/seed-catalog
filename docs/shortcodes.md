# Shortcode Reference

## Available Shortcodes

### Basic Display

#### Seed List
```
[seed_list]
```

Parameters:
- `columns` (int) - Number of grid columns (1-6)
- `per_page` (int) - Items per page (1-100)
- `category` (string) - Filter by category slug
- `tag` (string) - Filter by tag
- `orderby` (string) - Sort by: title, date, modified, random
- `order` (string) - Sort direction: ASC or DESC
- `featured` (bool) - Show only featured seeds
- `search` (string) - Initial search term

Example:
```
[seed_list columns="4" per_page="12" category="vegetables" orderby="title" order="ASC"]
```

#### Search Form
```
[seed_search]
```

Parameters:
- `placeholder` (string) - Search input placeholder
- `button_text` (string) - Search button text
- `target` (string) - ID of seed list to filter
- `live` (bool) - Enable live search
- `min_chars` (int) - Minimum characters for live search

Example:
```
[seed_search placeholder="Search seeds..." button_text="Find" live="true" min_chars="3"]
```

#### Categories List
```
[seed_categories]
```

Parameters:
- `display` (string) - Display style: list, grid, dropdown
- `show_count` (bool) - Show seed count per category
- `hide_empty` (bool) - Hide empty categories
- `parent` (int) - Show only children of this category ID
- `columns` (int) - Grid columns (if display="grid")

Example:
```
[seed_categories display="grid" columns="3" show_count="true"]
```

### Advanced Display

#### Single Seed
```
[seed_single id="123"]
```

Parameters:
- `id` (int) - Seed post ID
- `template` (string) - Custom template name
- `show_meta` (bool) - Display meta information
- `show_gallery` (bool) - Display image gallery
- `show_related` (bool) - Show related seeds

Example:
```
[seed_single id="123" template="compact" show_meta="true"]
```

#### Featured Seeds
```
[seed_featured]
```

Parameters:
- `count` (int) - Number of seeds to display
- `layout` (string) - Display layout: grid, carousel, list
- `category` (string) - Filter by category
- `random` (bool) - Randomize order

Example:
```
[seed_featured count="6" layout="carousel" random="true"]
```

#### Category Grid
```
[seed_category_grid]
```

Parameters:
- `columns` (int) - Grid columns
- `show_description` (bool) - Show category descriptions
- `show_thumbnail` (bool) - Show category images
- `orderby` (string) - Sort by: name, count, ID
- `parent` (int) - Show children of category ID

Example:
```
[seed_category_grid columns="3" show_description="true"]
```

### Interactive Features

#### Filter Bar
```
[seed_filters]
```

Parameters:
- `target` (string) - ID of seed list to filter
- `taxonomies` (string) - Comma-separated list of taxonomies
- `type` (string) - Filter type: checkbox, radio, dropdown
- `live` (bool) - Apply filters immediately
- `clear` (bool) - Show clear filters button

Example:
```
[seed_filters taxonomies="seed_category,seed_tag" type="checkbox" live="true"]
```

#### Sort Control
```
[seed_sort]
```

Parameters:
- `target` (string) - ID of seed list to sort
- `options` (string) - Comma-separated sort options
- `default` (string) - Default sort option
- `live` (bool) - Apply sort immediately

Example:
```
[seed_sort options="title,date,popularity" default="title" live="true"]
```

### Data Display

#### Seed Stats
```
[seed_stats]
```

Parameters:
- `show` (string) - Stats to show: total, categories, featured
- `layout` (string) - Display layout: inline, block, cards
- `category` (string) - Filter by category

Example:
```
[seed_stats show="total,categories" layout="cards"]
```

#### Growing Calendar
```
[seed_calendar]
```

Parameters:
- `type` (string) - Calendar type: planting, harvesting, both
- `zone` (string) - USDA zone filter
- `compact` (bool) - Use compact display
- `category` (string) - Filter by category

Example:
```
[seed_calendar type="planting" zone="6" compact="true"]
```

## Combining Shortcodes

### Search and Filter Example
```
[seed_search target="seed-list-1"]
[seed_filters target="seed-list-1" live="true"]
[seed_list id="seed-list-1" per_page="24" columns="4"]
```

### Category Browser Example
```
[seed_category_grid columns="3"]
[seed_filters taxonomies="seed_tag"]
[seed_sort options="title,date"]
[seed_list per_page="12"]
```

## Shortcode Templates

### Custom Template Example
```php
// functions.php
add_filter('seed_catalog_shortcode_template', function($template, $shortcode) {
    if ($shortcode === 'seed_list' && isset($atts['template']) && $atts['template'] === 'custom') {
        return get_stylesheet_directory() . '/seed-catalog/custom-list.php';
    }
    return $template;
}, 10, 2);
```

### Template Variables
Available in template files:
- `$seeds` - Array of seed posts
- `$atts` - Shortcode attributes
- `$content` - Enclosed content
- `$settings` - Plugin settings

## JavaScript Events

Events fired on shortcode interactions:
```javascript
// Search
document.addEventListener('seed_catalog_search', function(e) {
    console.log('Search term:', e.detail.term);
});

// Filter
document.addEventListener('seed_catalog_filter', function(e) {
    console.log('Filters:', e.detail.filters);
});

// Sort
document.addEventListener('seed_catalog_sort', function(e) {
    console.log('Sort by:', e.detail.orderby);
});
```

## CSS Classes

Common classes for styling:
```css
.seed-catalog-grid {}
.seed-catalog-search {}
.seed-catalog-filters {}
.seed-catalog-sort {}
.seed-catalog-pagination {}
```

## Best Practices

1. **Performance**
   - Use pagination for large catalogs
   - Enable caching when possible
   - Optimize images
   - Use lazy loading

2. **Accessibility**
   - Include ARIA labels
   - Ensure keyboard navigation
   - Use semantic HTML
   - Maintain color contrast

3. **Mobile Responsiveness**
   - Test on multiple devices
   - Use appropriate breakpoints
   - Consider touch interfaces
   - Optimize for small screens

4. **SEO**
   - Use descriptive titles
   - Include meta descriptions
   - Implement structured data
   - Enable social sharing