<?php
/**
 * Direct API Test Tool
 * 
 * This diagnostic page directly tests the Gemini API response and JSON parsing
 * for the seed varieties search functionality.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die;
}

/**
 * Register shortcode and admin page for API testing
 */
function seed_catalog_api_direct_test() {
    add_shortcode('seed_catalog_api_test', 'seed_catalog_api_test_shortcode');
}
add_action('init', 'seed_catalog_api_direct_test');

// Shortcode to display the API test tool
function seed_catalog_api_test_shortcode() {
    ob_start();
    ?>
    <div class="seed-catalog-api-test">
        <h2>Seed Catalog API Test</h2>
        <div id="api-test-container">
            <button id="test-parse" class="button button-primary">Test API Response</button>
            <div id="result"></div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('test-parse').addEventListener('click', function() {
                    var resultDiv = document.getElementById('result');
                    resultDiv.innerHTML = '<div class="loading">Testing API response...</div>';
                    
                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'seed_catalog_test_parse',
                            nonce: '<?php echo wp_create_nonce('seed_catalog_api_test'); ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultDiv.innerHTML = '<div class="success"><h3>API Response:</h3><pre>' + 
                                JSON.stringify(data.data.api_response, null, 2) + 
                                '</pre><h3>Parsed Result:</h3><pre>' + 
                                JSON.stringify(data.data.parsed_result, null, 2) + 
                                '</pre></div>';
                        } else {
                            resultDiv.innerHTML = '<div class="error">Error: ' + data.data + '</div>';
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = '<div class="error">Fetch error: ' + error.message + '</div>';
                    });
                });
            });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

// AJAX handler for testing API key
function seed_catalog_test_api_key() {
    check_ajax_referer('seed_catalog_api_test', 'nonce');
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
        return;
    }
    
    // Check if we have an API key from the request or use the configured one
    $api_key = !empty($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : get_option('seed_catalog_gemini_api_key', '');
    
    if (empty($api_key)) {
        wp_send_json_error('No API key provided or configured');
        return;
    }
    
    // Create instance of Gemini API class
    $gemini_api = new Seed_Catalog_Gemini_API();
    $gemini_api->set_api_key($api_key);
    
    // Test with a simple prompt
    $test_prompt = "Hello, this is a test of the Gemini API. Please respond with 'API test successful' if you receive this message.";
    
    $response = $gemini_api->test_api($test_prompt, $api_key);
    
    if ($response['success']) {
        wp_send_json_success($response);
    } else {
        wp_send_json_error($response['message']);
    }
}
add_action('wp_ajax_seed_catalog_test_api_key', 'seed_catalog_test_api_key');

// AJAX handler for testing search
function seed_catalog_test_search() {
    check_ajax_referer('seed_catalog_api_test', 'nonce');
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
        return;
    }
    
    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    
    if (empty($term)) {
        wp_send_json_error('No search term provided');
        return;
    }
    
    // Use provided API key or the configured one
    $api_key = !empty($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : get_option('seed_catalog_gemini_api_key', '');
    
    if (empty($api_key)) {
        wp_send_json_error('No API key provided or configured');
        return;
    }
    
    // Create instance of Gemini API class
    $gemini_api = new Seed_Catalog_Gemini_API();
    $gemini_api->set_api_key($api_key);
    
    // Prepare the search prompt
    $prompt = "List 5-10 common varieties of {$term} plants that are typically grown from seeds. Format the response as JSON with the following structure:
    {
        \"varieties\": [
            {
                \"name\": \"Variety name\",
                \"description\": \"Brief description\"
            }
        ]
    }
    Do not include any explanations, only return valid JSON.";
    
    // Make the API call
    $api_response = $gemini_api->test_api($prompt, $api_key);
    
    if (!$api_response['success']) {
        wp_send_json_error('API Error: ' . $api_response['message']);
        return;
    }
    
    // Get the raw API response text
    $response_text = $api_response['data'];
    
    // Use a public method to get the parsed JSON
    $parsed_result = $gemini_api->parse_json_response($response_text);
    // If the method doesn't exist, you'll need to add it to your Seed_Catalog_Gemini_API class
    
    // Return both the raw response and the parsed result
    wp_send_json_success(array(
        'api_response' => $response_text,
        'parsed_result' => $parsed_result
    ));
}
add_action('wp_ajax_seed_catalog_test_search', 'seed_catalog_test_search');

// AJAX handler for testing JSON parsing
function seed_catalog_test_parse() {
    check_ajax_referer('seed_catalog_api_test', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
        return;
    }

    // Get API key from options
    $api_key = get_option('seed_catalog_gemini_api_key', '');
    if (empty($api_key)) {
        wp_send_json_error('No API key configured');
        return;
    }

    $gemini_api = new Seed_Catalog_Gemini_API();
    $gemini_api->set_api_key($api_key);
    
    // Use a test search term
    $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : 'squash';
    
    try {
        // Call the public method that wraps the protected functionality
        $response = $gemini_api->test_seed_varieties($search_term);
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        } else {
            wp_send_json_success($response);
        }
    } catch (Exception $e) {
        wp_send_json_error('API Error: ' . $e->getMessage());
    }
}

add_action('wp_ajax_seed_catalog_test_parse', 'seed_catalog_test_parse');