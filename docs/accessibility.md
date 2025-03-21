# Accessibility Guide

## Overview

The Seed Catalog plugin is built with accessibility in mind, following WCAG 2.1 guidelines to ensure a great experience for all users.

## Implemented Standards

### 1. Keyboard Navigation

- Full keyboard support for all interactive elements
- Focus indicators for all interactive components
- Skip links for main content areas
- Logical tab order
- Keyboard shortcuts for common actions

#### Keyboard Shortcuts
- `Enter/Space`: Select or activate items
- `Arrow Keys`: Navigate grid items and dropdowns
- `Esc`: Close modals and dropdowns
- `Tab`: Move through interactive elements

### 2. Screen Reader Support

- ARIA landmarks and labels
- Descriptive alt text for images
- Status announcements for dynamic content
- Clear headings hierarchy
- Form labels and descriptions

### 3. Visual Design

- High contrast mode support
- Minimum 4.5:1 color contrast ratio
- Resizable text without loss of functionality
- Focus visible indicators
- Clear visual hierarchy

### 4. Motion and Animation

- Reduced motion support
- No auto-playing content
- Pausable animations
- Optional loading animations

## ARIA Attributes

### Search Form
```html
<div class="seed-catalog-search-container" role="search">
    <input type="search" aria-label="Search seeds">
    <button type="submit" aria-label="Submit search">
</div>
```

### Grid Layout
```html
<div class="seed-catalog-grid" role="list">
    <article class="seed-catalog-item" role="listitem">
        <!-- Content -->
    </article>
</div>
```

### Dynamic Content
```html
<div aria-live="polite" role="status">
    <!-- Dynamic content updates -->
</div>
```

## Color and Contrast

### Default Theme Colors
- Primary Text: #333333 (11.5:1)
- Secondary Text: #666666 (7:1)
- Links: #0066cc (4.5:1)
- Buttons: #28a745 (4.5:1)
- Focus Outline: #2271b1 (4.5:1)

### High Contrast Mode
- Automatically adapts to system preferences
- Clear focus indicators
- Enhanced borders and outlines

## Forms and Controls

### Form Fields
- Clear labels and instructions
- Error messages with suggestions
- Required field indicators
- Focus management

### Validation
- Clear error messages
- Color-independent indicators
- Keyboard-accessible error lists
- Form recovery options

## Images and Media

### Image Requirements
- Alt text for all images
- Decorative images marked appropriately
- Complex images with detailed descriptions
- Responsive image handling

### Media Controls
- Keyboard-accessible controls
- Visible focus states
- Clear play/pause indicators
- Volume controls

## Testing Guidelines

### Manual Testing
1. Keyboard navigation check
2. Screen reader testing
3. High contrast mode verification
4. Reduced motion testing

### Automated Testing
1. WAVE tool evaluation
2. Lighthouse accessibility audit
3. aXe core testing
4. Color contrast analyzers

## Best Practices

### For Developers
1. Maintain semantic HTML structure
2. Implement proper ARIA roles
3. Test with keyboard only
4. Validate color contrast

### For Content Creators
1. Write descriptive alt text
2. Maintain heading hierarchy
3. Use clear link text
4. Provide captions for media

## Common Issues and Solutions

### Focus Management
```javascript
// Proper focus management for modals
const modal = document.querySelector('.seed-catalog-modal');
modal.focus();

// Trap focus within modal
const focusableElements = modal.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
);
```

### Dynamic Content Updates
```javascript
// Announce dynamic content changes
const resultsRegion = document.getElementById('search-results');
resultsRegion.setAttribute('aria-busy', 'true');
// Update content
resultsRegion.setAttribute('aria-busy', 'false');
```

## Resources

1. [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
2. [WordPress Accessibility Handbook](https://make.wordpress.org/accessibility/handbook/)
3. [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
4. [A11Y Project Checklist](https://www.a11yproject.com/checklist/)