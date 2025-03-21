<?php
/**
 * The API test utility class.
 *
 * Provides functionality for testing API connectivity and responses.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */

namespace SeedCatalog;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The API test utility class.
 *
 * This class handles all API testing functionality including
 * connection tests, response validation, and error reporting.
 */
class Seed_Catalog_API_Test_Util {

    /**
     * The Gemini API instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      \Seed_Catalog_Gemini_API    $api    The Gemini API instance.
     */
    protected $api;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api = new Seed_Catalog_Gemini_API();
        
        // Register AJAX handlers and admin hooks
        add_action('wp_ajax_seed_catalog_test_api', array($this, 'handle_api_test'));
        add_action('admin_footer', array($this, 'output_test_js'));
    }

    /**
     * Test the API connection and return the result
     *
     * @since     1.0.0
     * @param     string|null    $api_key    Optional API key to test. If not provided, uses the stored key.
     * @return    array|\WP_Error            The test result or error. Returns array on success, WP_Error on failure.
     */
    public function test_api_connection($api_key = null) {
        if ($api_key === null) {
            $api_key = get_option('seed_catalog_gemini_api_key', '');
        }
        
        if (empty($api_key)) {
            return new \WP_Error(
                'api_key_missing',
                __('API key is empty. Please enter an API key before testing.', 'seed-catalog')
            );
        }

        try {
            // Use the public test_api_connection method instead of directly accessing protected methods
            $response = $this->api->test_api_connection($api_key);
            
            if ($response['success']) {
                if (strpos($response['data'], 'API test successful') !== false) {
                    return array(
                        'success' => true,
                        'message' => __('API connection successful!', 'seed-catalog'),
                        'response' => $response['data']
                    );
                }
                
                return new \WP_Error(
                    'api_unexpected_response',
                    __('Unexpected API response. The API is connected but returned unexpected content.', 'seed-catalog'),
                    $response['data']
                );
            }
            
            // Map error types to user-friendly messages
            $error_type = isset($response['error_type']) ? $response['error_type'] : 'api_unknown_error';
            
            $error_messages = array(
                'api_auth_error' => __('Authentication failed. Your API key may be invalid. Please check your API key and try again.', 'seed-catalog'),
                'api_rate_limit' => __('API rate limit exceeded. Please wait a few minutes and try again.', 'seed-catalog'),
                'api_server_error' => __('The API server encountered an error. Please try again later.', 'seed-catalog'),
                'api_unknown_error' => __('Unknown API error occurred. Please try again.', 'seed-catalog')
            );
            
            $error_message = isset($error_messages[$error_type]) ? $error_messages[$error_type] : $error_messages['api_unknown_error'];
            
            if (isset($response['message']) && WP_DEBUG) {
                $error_message .= ' ' . sprintf(__('(Debug: %s)', 'seed-catalog'), $response['message']);
            }
            
            return new \WP_Error($error_type, $error_message, $response);
            
        } catch (\Exception $e) {
            $error_message = sprintf(
                /* translators: %s: Error message */
                __('API test failed with error: %s', 'seed-catalog'),
                $e->getMessage()
            );
            
            if (WP_DEBUG) {
                $error_message .= ' ' . sprintf(__('(Debug: %s)', 'seed-catalog'), $e->getTraceAsString());
            }
            
            return new \WP_Error('api_exception', $error_message);
        }
    }

    /**
     * Handle the AJAX request for API testing
     *
     * @since    1.0.0
     * @return   void
     */
    public function handle_api_test() {
        try {
            if (!check_ajax_referer('seed_catalog_test_api', 'nonce', false)) {
                wp_send_json_error(array(
                    'message' => __('Security check failed.', 'seed-catalog'),
                    'error_type' => 'security_error'
                ));
                return;
            }
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array(
                    'message' => __('You do not have permission to perform this action.', 'seed-catalog'),
                    'error_type' => 'permission_error'
                ));
                return;
            }
            
            // Get API key from request or options
            $api_key = !empty($_POST['api_key']) 
                ? sanitize_text_field(wp_unslash($_POST['api_key']))
                : get_option('seed_catalog_gemini_api_key');
            
            $result = $this->test_api_connection($api_key);
            
            if (is_wp_error($result)) {
                wp_send_json_error(array(
                    'message' => $result->get_error_message(),
                    'error_type' => $result->get_error_code(),
                    'error_data' => $result->get_error_data()
                ));
                return;
            }
            
            wp_send_json_success($this->clean_api_response($result));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $this->format_error_message($e->getMessage(), WP_DEBUG ? $e->getTraceAsString() : null),
                'error_type' => 'exception'
            ));
        }
    }

    /**
     * Output the JavaScript for the API test functionality
     */
    public function output_test_js() {
        $screen = get_current_screen();
        if ($screen && $screen->id !== 'settings_page_seed-catalog') {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            const button = $('#test-gemini-api');
            const resultContainer = $('#api-test-result');
            
            button.on('click', function(e) {
                e.preventDefault();
                
                // Show loading state
                button.prop('disabled', true).text(<?php echo wp_json_encode(__('Testing...', 'seed-catalog')); ?>);
                resultContainer.html('<span class="testing">' + <?php echo wp_json_encode(__('Testing API connection...', 'seed-catalog')); ?> + '</span>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'seed_catalog_test_api',
                        nonce: '<?php echo wp_create_nonce('seed_catalog_test_api'); ?>',
                        api_key: $('#seed_catalog_gemini_api_key').val()
                    },
                    success: function(response) {
                        button.prop('disabled', false).text(<?php echo wp_json_encode(__('Test API Connection', 'seed-catalog')); ?>);
                        
                        if (response.success) {
                            resultContainer.html('<span class="success">' + <?php echo wp_json_encode(__('Success! API connection is working correctly.', 'seed-catalog')); ?> + '</span>');
                        } else {
                            var errorMsg = response.data && response.data.message 
                                ? response.data.message 
                                : <?php echo wp_json_encode(__('Unknown error occurred.', 'seed-catalog')); ?>;
                            resultContainer.html('<span class="error">' + <?php echo wp_json_encode(__('Error:', 'seed-catalog')); ?> + ' ' + errorMsg + '</span>');
                            
                            // Log additional error details in debug mode
                            if (response.data && response.data.error_data && window.console && window.console.debug) {
                                console.debug('API Test Error Details:', response.data.error_data);
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        button.prop('disabled', false).text(<?php echo wp_json_encode(__('Test API Connection', 'seed-catalog')); ?>);
                        resultContainer.html('<span class="error">' + <?php echo wp_json_encode(__('Error: Unable to complete the test. Check your internet connection.', 'seed-catalog')); ?> + '</span>');
                        
                        // Log AJAX error details in debug mode
                        if (window.console && window.console.debug) {
                            console.debug('API Test AJAX Error:', { status: textStatus, error: errorThrown });
                        }
                    }
                });
            });

            // Add the button after the API key field
            var apiKeyField = $('#seed_catalog_gemini_api_key').closest('tr');
            if (apiKeyField.length) {
                $('<tr><th></th><td></td></tr>')
                    .insertAfter(apiKeyField)
                    .find('td')
                    .append($('.api-test-container'));
            }
        });
        </script>
        <style>
            .api-test-container {
                margin-top: 10px;
            }
            #api-test-result {
                display: inline-block;
                margin-left: 10px;
                vertical-align: middle;
            }
            #api-test-result .success {
                color: #46b450;
                font-weight: bold;
            }
            #api-test-result .error {
                color: #dc3232;
                font-weight: bold;
            }
            #api-test-result .testing {
                color: #666;
                font-style: italic;
            }
            #test-gemini-api:disabled {
                cursor: not-allowed;
                opacity: 0.7;
            }
            @keyframes testing-pulse {
                0% { opacity: 1; }
                50% { opacity: 0.6; }
                100% { opacity: 1; }
            }
            #api-test-result .testing {
                animation: testing-pulse 1.5s infinite;
            }
        </style>
        <?php
    }

    /**
     * Format error message with debug information if enabled
     *
     * @since     1.0.0
     * @access    private
     * @param     string    $message     The main error message
     * @param     mixed     $debug_data  Additional debug data to include if WP_DEBUG is true
     * @return    string                 Formatted error message
     */
    private function format_error_message($message, $debug_data = null) {
        if (!WP_DEBUG || empty($debug_data)) {
            return $message;
        }

        $debug_info = is_string($debug_data) ? $debug_data : wp_json_encode($debug_data);
        return sprintf('%s %s', $message, sprintf(__('(Debug: %s)', 'seed-catalog'), $debug_info));
    }

    /**
     * Clean and prepare the API response for JSON output
     *
     * @since     1.0.0
     * @access    private
     * @param     array    $response    The API response to clean
     * @return    array                 Cleaned response data
     */
    private function clean_api_response($response) {
        // Remove sensitive data
        if (isset($response['api_key'])) {
            unset($response['api_key']);
        }

        // Limit response size for better performance
        if (isset($response['data']) && strlen($response['data']) > 1000) {
            $response['data'] = substr($response['data'], 0, 1000) . '...';
        }

        return $response;
    }

    /**
     * Display the API test button in the plugin settings page
     *
     * @since    1.0.0
     * @return   void
     */
    public static function display_test_button() {
        ?>
        <div class="api-test-container">
            <button id="test-gemini-api" class="button button-secondary">
                <?php esc_html_e('Test API Connection', 'seed-catalog'); ?>
            </button>
            <span id="api-test-result"></span>
        </div>
        <?php
    }
}

// Initialize the class
add_action('admin_init', function() {
    new Seed_Catalog_API_Test_Util();
});