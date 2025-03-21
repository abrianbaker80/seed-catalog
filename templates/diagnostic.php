<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="seed-catalog-diagnostic">
    <h2>Seed Catalog Diagnostic Tool</h2>
    <p>This page will help diagnose issues with the Seed Catalog plugin.</p>
    
    <div id="diagnostic-results"></div>
    
    <div class="seed-catalog-input-group">
        <label for="test-search">Enter a seed name to test search:</label>
        <div class="test-search-container">
            <input type="text" id="test-search" placeholder="e.g., squash">
            <button id="test-search-button">Search Varieties</button>
        </div>
        <div class="seed-catalog-message" style="display:none;"></div>
        <div class="seed-catalog-variety-list" style="display:none;"></div>
    </div>

    <?php 
    // Add API test section
    echo do_shortcode('[seed_catalog_api_test]');
    ?>
</div>

<script>
    jQuery(document).ready(function($) {
        $('#diagnostic-results').append('<p>jQuery loaded: ' + (typeof $ === 'function' ? 'Yes' : 'No') + '</p>');
        $('#diagnostic-results').append('<p>SeedCatalogPublic variable: ' + (typeof seedCatalogPublic === 'object' ? 'Yes' : 'No') + '</p>');
        $('#diagnostic-results').append('<p>seedCatalogDebug variable: ' + (typeof window.seedCatalogDebug === 'object' ? 'Yes' : 'No') + '</p>');
        
        if (typeof window.seedCatalogDebug === 'object') {
            $('#diagnostic-results').append('<p>Debug functions available!</p>');
        }
        
        $('#test-search-button').on('click', function() {
            $('#diagnostic-results').append('<p>Search button clicked</p>');
            
            const term = $('#test-search').val();
            if (!term) {
                $('#diagnostic-results').append('<p class="error">Please enter a search term</p>');
                return;
            }
            
            if (typeof window.seedCatalogDebug === 'object' && typeof window.seedCatalogDebug.searchVarieties === 'function') {
                $('#diagnostic-results').append('<p>Calling searchVarieties with term: ' + term + '</p>');
                window.seedCatalogDebug.searchVarieties(term);
            } else {
                $('#diagnostic-results').append('<p class="error">searchVarieties function not available!</p>');
                
                // Try direct AJAX call as fallback
                $('#diagnostic-results').append('<p>Attempting direct AJAX call...</p>');
                $.ajax({
                    url: seedCatalogPublic.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'search_seed_varieties',
                        nonce: seedCatalogPublic.nonce,
                        term: term
                    },
                    success: function(response) {
                        $('#diagnostic-results').append('<p>AJAX Success!</p>');
                        if (response.success && response.data) {
                            // Handle both array and object formats
                            const data = response.data;
                            const varieties = Array.isArray(data) ? data : data.varieties;
                            
                            if (varieties && varieties.length) {
                                $('#diagnostic-results').append('<h4>Found Varieties:</h4>');
                                const list = $('<ul></ul>');
                                varieties.forEach(function(variety) {
                                    list.append(`<li><strong>${variety.name}</strong>: ${variety.description}</li>`);
                                });
                                $('#diagnostic-results').append(list);
                            } else {
                                $('#diagnostic-results').append('<p class="error">No varieties found in response</p>');
                                $('#diagnostic-results').append('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                            }
                        } else {
                            $('#diagnostic-results').append('<p class="error">Invalid response format: ' + JSON.stringify(response) + '</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#diagnostic-results').append('<p class="error">AJAX Error: ' + error + '</p>');
                        if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                $('#diagnostic-results').append('<p>Response: ' + JSON.stringify(response) + '</p>');
                            } catch(e) {
                                $('#diagnostic-results').append('<p>Raw response: ' + xhr.responseText + '</p>');
                            }
                        }
                    }
                });
            }
        });
    });
</script>

<style>
    #seed-catalog-diagnostic {
        max-width: 800px;
        margin: 30px auto;
        padding: 20px;
        background: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 5px;
    }
    
    #diagnostic-results {
        margin: 20px 0;
        padding: 15px;
        background: #f5f5f5;
        border-radius: 5px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    #diagnostic-results pre {
        white-space: pre-wrap;
        background: #f0f0f0;
        padding: 10px;
        border-radius: 3px;
    }
    
    #diagnostic-results p.error {
        color: #d9534f;
        background: #fce4e4;
        padding: 5px 10px;
        border-radius: 3px;
    }
    
    .test-search-container {
        display: flex;
        margin-bottom: 15px;
        gap: 10px;
    }
    
    #test-search {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    #test-search-button {
        padding: 8px 15px;
        background: #0073aa;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .seed-catalog-message,
    .seed-catalog-variety-list,
    .seed-catalog-error {
        margin-top: 10px;
        padding: 10px;
        border-radius: 4px;
    }
    
    .seed-catalog-message.error,
    .seed-catalog-error {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }
    
    .seed-catalog-message.success {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
    }
    
    .seed-catalog-message.info {
        color: #0c5460;
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
    }
    
    .seed-catalog-variety-list {
        border: 1px solid #ddd;
        background: #fff;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .variety-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }
    
    .variety-item:hover {
        background: #f5f5f5;
    }
    
    .variety-item h4 {
        margin: 0 0 5px;
    }
    
    .variety-item p {
        margin: 0;
        font-size: 0.9em;
        color: #666;
    }
</style>

<?php get_footer(); ?>