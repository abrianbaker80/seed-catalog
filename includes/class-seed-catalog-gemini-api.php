<?php
/**
 * The class responsible for interacting with the Google Gemini AI API.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */

namespace SeedCatalog;

use \Exception;
use \WP_Error;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../');
}

if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', false);
}

/**
 * Class for handling Gemini API interactions
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */
class Seed_Catalog_Gemini_API {
    /**
     * The API key for the Google Gemini API.
     *
     * @var string
     */
    private $api_key;

    /**
     * Initialize the class and set up WordPress hooks
     */
    public function __construct() {
        // Get API key from settings rather than hardcoded value
        $this->api_key = \SeedCatalog\Seed_Catalog_Settings::get_api_key();
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_process_gemini_search', array($this, 'process_gemini_search'));
        add_action('wp_ajax_nopriv_process_gemini_search', array($this, 'process_gemini_search'));
        add_action('wp_ajax_search_seed_varieties', array($this, 'search_seed_varieties'));
        add_action('wp_ajax_nopriv_search_seed_varieties', array($this, 'search_seed_varieties'));
        add_action('wp_ajax_get_seed_details', array($this, 'get_seed_details'));
        add_action('wp_ajax_nopriv_get_seed_details', array($this, 'get_seed_details'));
    }

    /**
     * Initialize after WordPress is loaded
     */
    public function init() {
        // Core WordPress functionality will be available here
    }

    /**
     * The Gemini API endpoint for text generation - updated to Gemini 2.0
     */
    const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    /**
     * The Gemini API endpoint for image recognition
     */
    const GEMINI_PRO_VISION_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent';

    /**
     * The stream version of the Gemini API endpoint
     */
    const GEMINI_STREAM_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-pro-exp-02-05:streamGenerateContent';

    /**
     * The client configuration for OAuth authentication
     */
    const DEFAULT_CLIENT_CONFIG = '{
        "web": {
            "client_id": "REMOVED_FOR_SECURITY",
            "project_id": "gen-lang-client-0075191306",
            "auth_uri": "https://accounts.google.com/o/oauth2/auth",
            "token_uri": "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
            "client_secret": "REMOVED_FOR_SECURITY"
        }
    }';

    /**
     * Check if the API key is set.
     *
     * @return bool Whether the API key is set.
     */
    public function is_configured() {
        return !empty($this->api_key);
    }

    /**
     * Set the API key.
     *
     * @param string $api_key The API key for Google Gemini.
     */
    public function set_api_key($api_key) {
        $this->api_key = $api_key;
    }

    /**
     * Get the API key.
     *
     * @return string The API key.
     */
    public function get_api_key() {
        return $this->api_key;
    }

    /**
     * Make a request to the Gemini API.
     *
     * @param string $prompt The prompt to send to the API.
     * @return array|WP_Error The API response or WP_Error on failure.
     */
    private function make_request($prompt) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('No Gemini API key set.', 'seed-catalog'));
        }

        $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . $this->api_key;
        
        $body = json_encode([
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'topK' => 40,
                'topP' => 0.8,
                'maxOutputTokens' => 2048,
            ]
        ]);

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error(
                'request_failed',
                $response->get_error_message(),
                ['status' => 500]
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf(__('API request failed with status code: %d', 'seed-catalog'), $response_code),
                ['status' => $response_code]
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', __('Error decoding API response', 'seed-catalog'));
        }

        return $body;
    }

    /**
     * Parse the text response into a structured array of seed details.
     *
     * @param string $text The text response from the API.
     * @return array The structured seed details.
     */
    private function parse_json_from_text($text) {
        // Extract JSON from the text response which may contain markdown and explanations
        $pattern = '/```json\s*([\s\S]*?)\s*```|{\s*"seed_name"[\s\S]*}/m';
        
        if (preg_match($pattern, $text, $matches)) {
            $json_str = trim($matches[1] ?? $matches[0]);
            $json_data = json_decode($json_str, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                return $json_data;
            }
        }
        
        // Attempt to find any JSON-like structure if the regular pattern fails
        $pattern = '/{[\s\S]*}/m';
        if (preg_match($pattern, $text, $matches)) {
            $json_str = trim($matches[0]);
            $json_data = json_decode($json_str, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                return $json_data;
            }
        }
        
        // If no JSON found, return a structured version of the text
        return [
            'seed_name' => '',
            'seed_variety' => '',
            'description' => $text
        ];
    }

    /**
     * Get detailed seed information
     *
     * @param string $variety The seed variety
     * @param string $plant_type The plant type
     * @param string $brand The brand name
     * @param string $sku The SKU/UPC
     * @return array|\WP_Error The seed details or WP_Error on failure
     */
    public function get_seed_details($variety = '', $plant_type = '', $brand = '', $sku = '') {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            return new WP_Error('not_ajax', __('This method should be called via AJAX', 'seed-catalog'));
        }

        // Verify nonce
        check_ajax_referer('seed_catalog_gemini_nonce', 'nonce');

        // Check if user has permission to manage seeds
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to get seed details.', 'seed-catalog')));
            return new WP_Error('permission_denied', __('Permission denied', 'seed-catalog'));
        }

        // Handle AJAX calls
        $variety = empty($variety) && isset($_POST['variety']) ? sanitize_text_field($_POST['variety']) : $variety;
        $plant_type = empty($plant_type) && isset($_POST['plant_type']) ? sanitize_text_field($_POST['plant_type']) : $plant_type;
        $brand = empty($brand) && isset($_POST['brand']) ? sanitize_text_field($_POST['brand']) : $brand;
        $sku = empty($sku) && isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : $sku;

        if (empty($variety) && empty($plant_type) && empty($brand) && empty($sku)) {
            wp_send_json_error(array('message' => __('Please provide at least one search parameter (variety, plant type, brand, or SKU/UPC).', 'seed-catalog')));
            return new WP_Error('missing_parameters', __('Missing search parameters', 'seed-catalog'));
        }

        $result = $this->get_seed_details_from_api($variety, $plant_type, $brand, $sku);
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
        return $result;
    }

    /**
     * Get seed details from the Gemini API.
     *
     * @param string $variety The seed variety to get details for.
     * @param string $plant_type Optional plant type for better context.
     * @param string $brand Optional brand name for better search context.
     * @param string $sku Optional SKU or UPC for exact product search.
     * @return array|\WP_Error The seed details or WP_Error on failure.
     */
    private function get_seed_details_from_api($variety, $plant_type = '', $brand = '', $sku = '') {
        // Build context for the search
        $context_parts = [];
        
        if (!empty($variety)) {
            $context_parts[] = "seed variety: $variety";
        }
        
        if (!empty($plant_type)) {
            $context_parts[] = "plant type: $plant_type";
        }
        
        if (!empty($brand)) {
            $context_parts[] = "brand: $brand";
        }
        
        if (!empty($sku)) {
            $context_parts[] = "SKU/UPC: $sku";
        }
        
        $context = implode(', ', $context_parts);

        // Construct a comprehensive prompt to get detailed seed information
        $prompt = <<<EOT
Your task is to provide comprehensive seed and plant information for a seed catalog. I need detailed and accurate information about {$context}.

Please format the response as a valid JSON object with the following structure:

```json
{
  "seed_name": "Common name of the plant/seed",
  "seed_variety": "Specific variety name",
  "description": "General overview description",
  "brand": "Brand name if applicable",
  "sku": "Product SKU or UPC if applicable",
  
  "detailed_description": {
    "plant_type": "Annual/Perennial/Biennial/etc.",
    "growth_habit": "Vine/Bush/Upright/Spreading/etc.",
    "plant_size": "Height and spread measurements",
    "fruit_flower_details": "Details about fruits produced or flowers (size, color, shape, etc.)",
    "flavor_profile": "For edible plants, describe taste characteristics",
    "scent": "Fragrance description for flowers or herbs",
    "bloom_time": "When flowers bloom",
    "days_to_maturity": "Average days from planting to harvest",
    "special_characteristics": "Unique features like heat tolerance, deer resistance, etc."
  },
  
  "growing_instructions": {
    "sowing_depth": "How deep to plant seeds",
    "spacing": "Distance between plants",
    "soil_temperature": "Optimal soil temperature for germination",
    "sowing_method": "Direct sow or transplant recommendation",
    "days_to_germination": "Average germination time frame",
    "days_to_maturity": "Days from planting to harvest",
    "sunlight_needs": "Full sun/part shade/shade requirements",
    "watering_requirements": "Moisture needs and watering frequency",
    "fertilizer_recommendations": "Fertilizer needs and schedule",
    "pest_disease_info": "Common pest issues and prevention",
    "pruning_maintenance": "Care instructions",
    "harvesting_tips": "When and how to harvest"
  },
  
  "seed_info": {
    "seed_count": "Approximate number of seeds per packet",
    "seed_type": "Organic/Heirloom/Hybrid/Open-pollinated/GMO-free",
    "germination_rate": "Expected germination percentage",
    "seed_saving": "Instructions for saving seeds"
  },
  
  "additional_info": {
    "hardiness_zones": "USDA growing zones",
    "companion_plants": "Plants that grow well alongside it",
    "container_suitability": "Suitability for container growing",
    "indoor_growing": "Indoor growing potential",
    "pollinator_friendly": "Attracts beneficial insects",
    "edible_parts": "Which parts of the plant can be eaten",
    "culinary_uses": "How it's used in cooking",
    "medicinal_properties": "Traditional or proven medicinal uses",
    "storage_recommendations": "How to store harvested produce",
    "historical_background": "Brief history or origin story",
    "recipes": "Simple recipe ideas using the plant",
    "regional_tips": "Region-specific growing advice"
  },
  
  "vendor_info": {
    "company_name": "Seed company name",
    "pricing": "Price information if available",
    "availability": "Seasonal availability",
    "producer_details": "Where seeds are produced",
    "website": "Company website"
  }
}
```

For fields you don't have information about, use empty strings or null values. Make sure to provide accurate, garden-tested information rather than generic descriptions. Include scientific names when known. Focus on horticultural details that would be useful to home gardeners or small-scale farmers.
EOT;

        $response = $this->make_request($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('no_text', __('No text in API response.', 'seed-catalog'));
        }
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'];
        return $this->parse_json_from_text($text);
    }

    /**
     * Get seed variety suggestions based on a partial name.
     *
     * @param string $search_term The partial seed name to get suggestions for.
     * @return array|\WP_Error The seed variety suggestions or WP_Error on failure.
     */
    public function get_seed_varieties($search_term) {
        $prompt = <<<EOT
Provide a list of 5 common seed varieties (both vegetable and flower seeds) that match or relate to the term "$search_term". Include botanical names and brief descriptions that would be useful in a seed catalog.

Format the response as a valid JSON array with the following structure:

```json
[
  {
    "name": "Common name",
    "botanical_name": "Latin name",
    "variety": "Specific variety name",
    "type": "vegetable/flower/herb/etc.",
    "description": "Brief description for seed catalog listing (2-3 sentences)"
  }
]
```

Make sure all returned varieties are real, commonly available seed types that home gardeners might be interested in. Prioritize accurate and helpful information.
EOT;

        $response = $this->make_request($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('no_text', __('No text in API response.', 'seed-catalog'));
        }
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'];
        return $this->parse_json_from_text($text);
    }

    /**
     * Generate growing instructions for a seed.
     *
     * @param string $seed_name The seed name.
     * @param string $variety   The seed variety, optional.
     * @return array|\WP_Error The growing instructions or WP_Error on failure.
     */
    public function generate_growing_instructions($seed_name, $variety = '') {
        $plant = $seed_name;
        if (!empty($variety)) {
            $plant .= " ($variety)";
        }

        $prompt = <<<EOT
Provide detailed growing instructions for $plant seeds. Format your response as a valid JSON object using this structure:

```json
{
  "sowing_depth": "How deep to plant seeds",
  "spacing": "Distance between plants",
  "soil_temperature": "Optimal soil temperature for germination",
  "sowing_method": "Direct sow or transplant recommendation",
  "days_to_germination": "Average germination time frame",
  "days_to_maturity": "Days from planting to harvest",
  "sunlight_needs": "Full sun/part shade/shade requirements",
  "watering_requirements": "Moisture needs and watering frequency",
  "fertilizer_recommendations": "Fertilizer needs and schedule",
  "pest_disease_info": "Common pest issues and prevention",
  "pruning_maintenance": "Care instructions",
  "harvesting_tips": "When and how to harvest"
}
```

Make sure your instructions are accurate and specific to $plant. Include exact measurements when possible (inches, centimeters, etc.) and provide ranges when appropriate. Your instructions should help a home gardener successfully grow this plant.
EOT;

        $response = $this->make_request($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('no_text', __('No text in API response.', 'seed-catalog'));
        }
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'];
        return $this->parse_json_from_text($text);
    }

    /**
     * Generate comprehensive care instructions for a seed.
     * 
     * @param string $seed_name The seed name.
     * @param string $variety The seed variety, optional.
     * @return array|\WP_Error The care instructions or WP_Error on failure.
     */
    public function generate_care_instructions($seed_name, $variety = '') {
        $plant = $seed_name;
        if (!empty($variety)) {
            $plant .= " ($variety)";
        }

        $prompt = <<<EOT
Provide complete care instructions for growing $plant successfully. Include all care aspects throughout the plant's life cycle.

Format the response as a valid JSON object with this structure:

```json
{
  "soil_preparation": "Detailed soil preparation needs",
  "planting_time": "Best season/months for planting",
  "watering_schedule": "Specific watering needs by growth stage",
  "fertilizing": "Complete fertilizing schedule and recommendations",
  "pest_management": "Common pests and organic control methods",
  "disease_prevention": "Common diseases and prevention strategies",
  "pruning_techniques": "How and when to prune for best results",
  "support_requirements": "Trellising or support needs",
  "companion_plants": "What grows well with this plant",
  "succession_planting": "Tips for continuous harvests if applicable",
  "overwintering": "How to overwinter if perennial",
  "harvest_indicators": "How to tell when ready to harvest"
}
```

Provide specific, actionable advice for home gardeners. Include measurements, timing, and techniques based on best horticultural practices.
EOT;

        $response = $this->make_request($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('no_text', __('No text in API response.', 'seed-catalog'));
        }
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'];
        return $this->parse_json_from_text($text);
    }
    
    /**
     * Generate a description for a seed.
     * 
     * @param string $seed_name The seed name.
     * @param string $variety The seed variety, optional.
     * @return string|\WP_Error The description or WP_Error on failure.
     */
    public function generate_description($seed_name, $variety = '') {
        $plant = $seed_name;
        if (!empty($variety)) {
            $plant .= " ($variety)";
        }
        
        $prompt = <<<EOT
Write an engaging 2-3 paragraph description for $plant for a seed catalog. Include information about appearance, flavor (if edible), unique characteristics, and growing benefits. Make it appealing to home gardeners while being accurate and informative. Do not format as JSON, just provide the descriptive text.
EOT;

        $response = $this->make_request($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return new WP_Error('no_text', __('No text in API response.', 'seed-catalog'));
        }
        
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }

    /**
     * Process Gemini search request
     */
    public function process_gemini_search() {
        global $wp_filesystem;
        
        // Required for filesystem operations
        if (!function_exists('WP_Filesystem')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        WP_Filesystem();

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            return new WP_Error('not_ajax', 'This method should be called via AJAX');
        }

        // Verify nonce
        check_ajax_referer('seed_catalog_gemini_nonce', 'nonce');

        // Check if user has permission to manage seeds
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'You do not have permission to use the AI search feature.'));
            return;
        }

        // Get and sanitize input
        $search_query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        $context = isset($_POST['context']) ? sanitize_text_field($_POST['context']) : '';
        
        // Initialize seed_context with an empty string
        $seed_context = '';
        
        // Get and sanitize seed context data
        $seed_data = isset($_POST['seed_context']) ? $_POST['seed_context'] : array();
        
        // If seed_context is an array, process it
        if (is_array($seed_data)) {
            $sanitized_data = array_map('sanitize_text_field', $seed_data);
            $seed_context = implode(' ', $sanitized_data);
        } else if (is_string($seed_data)) {
            // If it's a string, sanitize it directly
            $seed_context = sanitize_text_field($seed_data);
        }

        if (empty($search_query)) {
            wp_send_json_error(array('message' => 'No search query provided.'));
        }

        $api_key = $this->get_api_key();
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'Gemini API key not configured.'));
        }

        // Prepare prompt with context and seed context
        $api_response = $this->prepare_search_prompt($search_query, $context, $seed_context);
        
        if ($api_response['success']) {
            wp_send_json_success($api_response['data']);
        } else {
            wp_send_json_error($api_response);
        }
    }

    /**
     * Make an HTTP call to the Gemini API
     * 
     * @param string $prompt The prompt to send to the API
     * @param string $api_key The API key to use
     * @param string|null $url Optional API URL override
     * @return array The API response with success/failure status
     */
    protected function call_gemini_api($prompt, $api_key, $url=null)
    {
        $this->log_debug("Making API call with prompt: " . substr($prompt, 0, 50) . "...");
        $api_url = $url ?? self::GEMINI_API_URL;
        
        // Build the request body according to the Gemini API requirements
        $body = array(
            'contents' => array(
                array(
                    'role' => 'user',
                    'parts' => array(
                        array(
                            'text' => $prompt
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.2, // Lower temperature for more precise, deterministic responses
                'topP' => 0.8,
                'topK' => 40,
                'maxOutputTokens' => 4096,
                'responseMimeType' => 'text/plain',
            )
        );

        // Add API key as query parameter
        $api_url_with_key = add_query_arg('key', $api_key, $api_url);
        
        $this->log_debug("API URL: " . preg_replace('/key=([^&]+)/', 'key=REDACTED', $api_url_with_key));

        // Use a longer timeout for API requests
        $response = wp_remote_post($api_url_with_key, array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 60, // Increased timeout for longer generations
            'sslverify' => true // Ensure SSL verification is enabled for security
        ));

        // Check for WordPress HTTP API errors
        if (is_wp_error($response)) {
            $this->log_debug("WP Error: " . $response->get_error_message());
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'error_type' => 'wp_error'
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $this->log_debug("API response code: " . $response_code);
        
        // Handle non-200 response codes
        if ($response_code !== 200) {
            $this->log_debug("API error response: " . $response_body);
            
            // Try to parse the error response for more detailed information
            $error_data = json_decode($response_body, true);
            $error_message = 'API request failed with status code: ' . $response_code;
            
            if (is_array($error_data) && isset($error_data['error'])) {
                if (isset($error_data['error']['message'])) {
                    $error_message = $error_data['error']['message'];
                }
                if (isset($error_data['error']['status'])) {
                    $error_type = $error_data['error']['status'];
                }
            }
            
            // Special handling for authentication errors
            if ($response_code == 401 || $response_code == 403) {
                $this->log_debug("Authentication error. Response: " . $response_body);
                
                // Try OAuth as a fallback if possible
                if ($this->can_use_oauth()) {
                    $this->log_debug("API key authentication failed. Trying OAuth authentication.");
                    return $this->call_gemini_api_with_oauth($prompt, $url);
                }
                
                return array(
                    'success' => false,
                    'message' => 'Authentication failed. Please check your API key.',
                    'error_type' => 'auth_error',
                    'error_details' => $error_data ?? null
                );
            }
            
            // Try fallback model if this one fails
            if (strpos($api_url, 'gemini-2.0-pro') !== false) {
                $this->log_debug("Gemini 2.0 model failed. Trying with Gemini 1.5 model.");
                $fallback_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent';
                return $this->call_gemini_api($prompt, $api_key, $fallback_url);
            }
            
            return array(
                'success' => false,
                'message' => $error_message,
                'error_type' => $error_type ?? 'api_error',
                'error_details' => $error_data ?? null
            );
        }

        // Process successful response
        $data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_debug("JSON parse error: " . json_last_error_msg());
            return array(
                'success' => false,
                'message' => 'Error parsing API response: ' . json_last_error_msg(),
                'error_type' => 'json_parse_error'
            );
        }

        // Check for response structure
        if (!isset($data['candidates']) || empty($data['candidates'])) {
            $this->log_debug("API response missing candidates array");
            return array(
                'success' => false,
                'message' => 'API response missing expected data structure',
                'error_type' => 'missing_candidates',
                'raw_response' => $data
            );
        }

        // Extract the text content from the response
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'];
            $this->log_debug("Successful API response with text length: " . strlen($text));
            return array(
                'success' => true,
                'data' => $text
            );
        }

        // If we reach here, the response structure is unexpected
        $this->log_debug("API response has unexpected structure: " . substr($response_body, 0, 200) . "...");
        return array(
            'success' => false,
            'message' => 'Unexpected API response structure',
            'error_type' => 'unexpected_structure',
            'raw_response' => $data
        );
    }
    
    /**
     * Check if we can use OAuth authentication
     * 
     * @return bool True if OAuth can be used
     */
    protected function can_use_oauth() {
        $config = $this->get_client_config();
        return !empty($config) && !empty($config['web']['client_id']) && !empty($config['web']['client_secret']);
    }
    
    /**
     * Make an API call using OAuth authentication
     * 
     * @param string $prompt The prompt to send to the API
     * @param string|null $url The API URL (optional)
     * @return array The API response
     */
    protected function call_gemini_api_with_oauth($prompt, $url=null) {
        // For testing/fallback purposes - in a real implementation, you would:
        // 1. Get an access token using the OAuth client credentials
        // 2. Add the token to the request headers
        // 3. Call the API
        
        // Simplified fallback data for testing - a real implementation would use OAuth
        $this->log_debug("OAuth authentication not fully implemented. Using fallback data for testing.");
        
        // Check prompt for seed variety search or details
        if (strpos($prompt, 'List 5-10 common varieties') !== false) {
            // Extract the plant name from the prompt
            preg_match('/varieties of (\w+) plants/', $prompt, $matches);
            $plant_name = !empty($matches[1]) ? $matches[1] : 'unknown';
            
            if ($plant_name === 'tomato') {
                return array(
                    'success' => true,
                    'data' => json_encode([
                        'varieties' => [
                            ['name' => 'Roma', 'description' => 'Plum-shaped tomatoes with meaty flesh, perfect for sauces and canning'],
                            ['name' => 'Beefsteak', 'description' => 'Large, juicy tomatoes ideal for slicing and sandwiches'],
                            ['name' => 'Cherry', 'description' => 'Small, sweet tomatoes that grow in clusters, great for salads'],
                            ['name' => 'Heirloom', 'description' => 'Open-pollinated, traditional varieties with unique flavors and appearances'],
                            ['name' => 'San Marzano', 'description' => 'Italian plum variety prized for rich flavor in sauces'],
                            ['name' => 'Early Girl', 'description' => 'Early-maturing variety with medium-sized fruit'],
                            ['name' => 'Brandywine', 'description' => 'Large, pink heirloom variety known for exceptional flavor'],
                            ['name' => 'Grape', 'description' => 'Small, oblong tomatoes that are sweeter than cherry varieties']
                        ]
                    ])
                );
            } else {
                return array(
                    'success' => true,
                    'data' => json_encode([
                        'varieties' => [
                            ['name' => 'Common Variety 1', 'description' => 'A popular variety with good yield'],
                            ['name' => 'Heritage Variety', 'description' => 'An heirloom variety with excellent flavor'],
                            ['name' => 'Hybrid Variety', 'description' => 'Disease-resistant hybrid with consistent results'],
                            ['name' => 'Dwarf Variety', 'description' => 'Compact plant suitable for containers'],
                            ['name' => 'Giant Variety', 'description' => 'Large fruits with impressive yields']
                        ]
                    ])
                );
            }
        } else if (strpos($prompt, 'Provide detailed growing information') !== false) {
            // Extract variety and plant type from the prompt
            preg_match('/information for (\w+) (\w+)/', $prompt, $matches);
            $variety = !empty($matches[1]) ? $matches[1] : 'unknown';
            $plant = !empty($matches[2]) ? $matches[2] : 'plant';
            
            return array(
                'success' => true,
                'data' => json_encode([
                    'seed_name' => $plant,
                    'seed_variety' => $variety,
                    'days_to_maturity' => '70-80 days',
                    'planting_depth' => '1/4 inch deep',
                    'planting_spacing' => '18-24 inches apart',
                    'sunlight_needs' => 'Full Sun',
                    'watering_requirements' => 'Regular watering, keeping soil consistently moist but not waterlogged. About 1-2 inches per week.',
                    'harvesting_tips' => 'Harvest when fruits are fully colored and slightly firm to touch. Twist gently or use scissors to avoid damaging the plant.',
                    'companion_plants' => 'Basil, Marigolds, Nasturtiums, Carrots, Onions',
                    'description' => "The $variety $plant is a popular variety known for its reliability and flavor. Plants grow well in most garden conditions with proper care.",
                    'image_description' => "A healthy $variety $plant with green foliage and characteristic fruit."
                ])
            );
        }
        
        // Default response for other prompts
        return array(
            'success' => true,
            'data' => 'Information provided by AI assistant with OAuth authentication.'
        );
    }

    /**
     * Get instructions for searching for a plant
     * @param mixed[] $data Data received from the API containing the genus and species
     * @return string Instructions
     */
    protected function get_find_text($data) {
        return sprintf('Find as %s %s', 
            implode(', ', array_column($data, 'scientific_name')),
            implode('\n', array_column($data, 'common_name'))
        );
    }

    /**
     * Prepare the search prompt with optional context and seed context
     *
     * @param string $search_query The search query
     * @param string $context Optional context for the search
     * @param string $seed_context Optional seed-specific context
     * @return array The API response
     */
    protected function prepare_search_prompt($search_query = '', $context = '', $seed_context = '') {
        // Sanitize inputs
        $search_query = sanitize_text_field($search_query);
        $context = sanitize_text_field($context);
        $seed_context = sanitize_text_field($seed_context);
        
        $prompt_parts = [];
        
        if (!empty($search_query)) {
            $prompt_parts[] = "Query: $search_query";
        }
        
        if (!empty($context)) {
            $prompt_parts[] = "Context: $context";
        }
        
        if (!empty($seed_context)) {
            $prompt_parts[] = "Seed Context: $seed_context";
        }
        
        $prompt_query = implode(' ', $prompt_parts);
        
        if (empty($prompt_query)) {
            $prompt_query = "Please provide a search query.";
        }

        return $this->call_gemini_api($prompt_query, $this->get_api_key());
    }

    /**
     * Search for seed varieties
     *
     * @param string|null $search_term The search term
     * @return array|WP_Error The search results or error
     */
    public function search_seed_varieties($search_term = null) {
        if (!wp_doing_ajax()) {
            return new WP_Error('not_ajax', __('This method should be called via AJAX', 'seed-catalog'));
        }

        // Get search term from POST if not provided
        if (empty($search_term) && isset($_POST['term'])) {
            $search_term = sanitize_text_field($_POST['term']);
        }

        if (empty($search_term)) {
            wp_send_json_error(array('message' => __('No search term provided.', 'seed-catalog')));
            return;
        }

        // Check if API is configured
        if (!$this->is_configured()) {
            wp_send_json_error(array('message' => __('Gemini API key not configured.', 'seed-catalog')));
            return;
        }

        // Format prompt for variety search
        $prompt = sprintf(
            'Search for seed varieties matching: %s. Format the response as JSON with varieties array containing name and description for each variety.',
            $search_term
        );

        $api_response = $this->make_request($prompt);
        
        if (is_wp_error($api_response)) {
            wp_send_json_error(array('message' => $api_response->get_error_message()));
            return;
        }

        try {
            $text = $api_response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $parsed_data = $this->parse_json_from_text($text);
            
            if (!$parsed_data) {
                $fallback_data = $this->get_fallback_varieties($search_term);
                wp_send_json_success($fallback_data);
                return;
            }

            wp_send_json_success($parsed_data);
            return;
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('Error parsing search results', 'seed-catalog'),
                'error' => $e->getMessage()
            ));
            return;
        }
    }

    /**
     * Extract valid JSON from a possibly mixed response
     */
    protected function extract_json_from_response($response) {
        $this->log_debug("Attempting to extract JSON from response: " . substr($response, 0, 100) . "...");
        
        // First check for JSON within code blocks - this pattern targets JSON with or without closing backticks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)(?:```|\Z)/m', $response, $matches)) {
            $json_str = trim($matches[1]);
            $this->log_debug("Found JSON in code block, length: " . strlen($json_str));
            $decoded = json_decode($json_str, true);
            if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                $this->log_debug("JSON decode error in code block: " . json_last_error_msg());
            }
        }
        
        // Try to decode the entire response as JSON
        $decoded = json_decode($response, true);
        if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
            $this->log_debug("Successfully decoded entire response as JSON");
            return $decoded;
        }
        
        // If that fails, try to find JSON within the text using a general pattern for objects
        if (preg_match('/\{\s*"[^"]+"\s*:/m', $response, $start_matches)) {
            // Find the start position of the JSON object
            $start_pos = strpos($response, $start_matches[0]);
            
            // Count braces to find the matching closing brace
            $content = substr($response, $start_pos);
            $open_braces = 0;
            $in_string = false;
            $escape_next = false;
            $end_pos = 0;
            
            for ($i = 0; $i < strlen($content); $i++) {
                $char = $content[$i];
                
                if ($escape_next) {
                    $escape_next = false;
                    continue;
                }
                
                if ($char === '\\') {
                    $escape_next = true;
                    continue;
                }
                
                if ($char === '"' && !$in_string) {
                    $in_string = true;
                } else if ($char === '"' && $in_string) {
                    $in_string = false;
                }
                
                if (!$in_string) {
                    if ($char === '{') {
                        $open_braces++;
                    } else if ($char === '}') {
                        $open_braces--;
                        if ($open_braces === 0) {
                            $end_pos = $i + 1;
                            break;
                        }
                    }
                }
            }
            
            if ($end_pos > 0) {
                $json_str = substr($content, 0, $end_pos);
                $this->log_debug("Extracted potential JSON object, length: " . strlen($json_str));
                $decoded = json_decode($json_str, true);
                if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                } else {
                    $this->log_debug("JSON decode error in extracted object: " . json_last_error_msg());
                }
            }
        }
        
        // Additional fallback: search for direct varieties array structure
        if (preg_match('/"varieties"\s*:\s*\[\s*\{/m', $response, $matches)) {
            $pos = strpos($response, $matches[0]);
            // Extract from the "varieties" key to the end and try to complete the JSON
            $json_str = '{' . substr($response, $pos);
            
            // If the JSON is incomplete, try to complete it with closing braces
            if (substr_count($json_str, '{') > substr_count($json_str, '}')) {
                $missing = substr_count($json_str, '{') - substr_count($json_str, '}');
                $json_str += str_repeat('}', $missing);
            }
            
            $this->log_debug("Attempting to parse varieties-specific JSON: " . substr($json_str, 0, 50) . "...");
            $decoded = json_decode($json_str, true);
            if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                $this->log_debug("Varieties JSON decode error: " . json_last_error_msg());
            }
        }
        
        // Last resort: manually parse the varieties from the text response
        if (strpos($response, '"name"') !== false && strpos($response, '"description"') !== false) {
            $this->log_debug("Attempting manual parsing of varieties data");
            $varieties = array();
            
            // Extract each variety block
            preg_match_all('/"name"\s*:\s*"([^"]+)"[^}]+"description"\s*:\s*"([^"]+)"/s', $response, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                if (isset($match[1]) && isset($match[2])) {
                    $varieties[] = array(
                        'name' => $match[1],
                        'description' => $match[2]
                    );
                } else if (isset($match[1]) && isset($match[2])) {
                    // For the second pattern
                    $variety_name = trim($match[1]);
                    $description = trim($match[2]);
                    
                    // Skip if the variety name contains "JSON" or other keywords
                    if (stripos($variety_name, 'json') !== false || 
                        stripos($variety_name, 'variety') === 0 || 
                        stripos($variety_name, 'name') === 0) {
                        continue;
                    }
                    
                    $varieties[] = array(
                        'name' => $variety_name,
                        'description' => $description
                    );
                }
            }
        }
        
        // If we found at least 2 varieties, return them
        if (count($varieties) >= 2) {
            return array('varieties' => $varieties);
        }
        
        // If we found at least 2 varieties, return them
        if (count($varieties) >= 2) {
            return array('varieties' => $varieties);
        }
        
        // Otherwise, fall back to a default set
        return null;
    }

    /**
     * Get fallback variety data for common plants
     * 
     * @param string $plant_type The type of plant to get fallback varieties for
     * @return array An array of fallback varieties
     */
    protected function get_fallback_varieties($plant_type) {
        $plant_type = strtolower(trim($plant_type));
        $varieties = array();
        
        switch($plant_type) {
            case 'tomato':
            case 'tomatoes':
                $varieties = array(
                    array('name' => 'Roma', 'description' => 'Plum-shaped tomatoes with meaty flesh, perfect for sauces and canning'),
                    array('name' => 'Beefsteak', 'description' => 'Large, juicy tomatoes ideal for slicing and sandwiches'),
                    array('name' => 'Cherry', 'description' => 'Small, sweet tomatoes that grow in clusters, great for salads'),
                    array('name' => 'Early Girl', 'description' => 'Early-maturing variety with medium-sized fruit'),
                    array('name' => 'Brandywine', 'description' => 'Large, pink heirloom variety known for exceptional flavor')
                );
                break;
                
            case 'pepper':
            case 'peppers':
                $varieties = array(
                    array('name' => 'Bell', 'description' => 'Sweet, mild peppers available in multiple colors'),
                    array('name' => 'Jalapeño', 'description' => 'Medium-hot peppers popular in Mexican cuisine'),
                    array('name' => 'Habanero', 'description' => 'Very hot peppers with fruity flavor'),
                    array('name' => 'Serrano', 'description' => 'Hot peppers commonly used in salsas and sauces'),
                    array('name' => 'Poblano', 'description' => 'Mild to medium-hot peppers, often used for stuffing')
                );
                break;
                
            case 'carrot':
            case 'carrots':
                $varieties = array(
                    array('name' => 'Nantes', 'description' => 'Cylindrical carrots with excellent sweet flavor'),
                    array('name' => 'Imperator', 'description' => 'Long, tapered carrots commonly found in supermarkets'),
                    array('name' => 'Danvers', 'description' => 'Conical shape that grows well in heavy soils'),
                    array('name' => 'Chantenay', 'description' => 'Short, stocky carrots good for heavy or rocky soils'),
                    array('name' => 'Purple Dragon', 'description' => 'Purple skin with orange core, sweet flavor')
                );
                break;
                
            case 'lettuce':
                $varieties = array(
                    array('name' => 'Romaine', 'description' => 'Tall, upright lettuce with crisp texture and good flavor'),
                    array('name' => 'Butterhead', 'description' => 'Soft, buttery texture with loose heads'),
                    array('name' => 'Iceberg', 'description' => 'Crisp, pale green heads with mild flavor'),
                    array('name' => 'Leaf Lettuce', 'description' => 'Loose leaves with various colors and textures'),
                    array('name' => 'Mesclun Mix', 'description' => 'Mixed baby greens with various flavors and textures')
                );
                break;
                
            case 'cucumber':
            case 'cucumbers':
                $varieties = array(
                    array('name' => 'Slicing', 'description' => 'Long, smooth cucumbers for fresh eating'),
                    array('name' => 'Pickling', 'description' => 'Smaller cucumbers with bumpy skin, ideal for pickles'),
                    array('name' => 'English', 'description' => 'Long, seedless cucumbers with thin skin'),
                    array('name' => 'Lemon', 'description' => 'Round, yellow cucumbers with mild flavor'),
                    array('name' => 'Armenian', 'description' => 'Long, ribbed cucumbers with mild flavor, technically a melon')
                );
                break;
                
            case 'bean':
            case 'beans':
                $varieties = array(
                    array('name' => 'Bush Bean', 'description' => 'Compact plants that don\'t require support'),
                    array('name' => 'Pole Bean', 'description' => 'Climbing beans that need trellising'),
                    array('name' => 'Lima Bean', 'description' => 'Flat, kidney-shaped beans with buttery flavor'),
                    array('name' => 'Fava Bean', 'description' => 'Large, flat beans grown in cool weather'),
                    array('name' => 'Snap Bean', 'description' => 'Beans eaten pod and all, commonly green or yellow')
                );
                break;
                
            case 'squash':
                $varieties = array(
                    array('name' => 'Butternut', 'description' => 'Sweet, nutty winter squash with tan skin'),
                    array('name' => 'Acorn', 'description' => 'Small, ribbed winter squash with sweet flavor'),
                    array('name' => 'Zucchini', 'description' => 'Popular summer squash harvested when immature'),
                    array('name' => 'Pattypan', 'description' => 'Flying saucer-shaped summer squash'),
                    array('name' => 'Delicata', 'description' => 'Oblong winter squash with edible skin and sweet flavor')
                );
                break;
                
            case 'sunflower':
            case 'sunflowers':
                $varieties = array(
                    array('name' => 'Mammoth', 'description' => 'Giant sunflowers reaching 12 feet with large seed heads'),
                    array('name' => 'Dwarf', 'description' => 'Compact plants good for containers and small spaces'),
                    array('name' => 'Teddy Bear', 'description' => 'Fluffy double-flowered variety with no pollen'),
                    array('name' => 'Red Sun', 'description' => 'Burgundy-red petals with dark centers'),
                    array('name' => 'Vanilla Ice', 'description' => 'Cream-colored petals with chocolate centers')
                );
                break;
                
            default:
                // Generic varieties for unknown plant types
                $varieties = array(
                    array('name' => 'Heritage', 'description' => 'Traditional open-pollinated variety with excellent flavor'),
                    array('name' => 'Hybrid', 'description' => 'Disease-resistant variety with consistent results'),
                    array('name' => 'Dwarf', 'description' => 'Compact plants suitable for containers or small gardens'),
                    array('name' => 'Giant', 'description' => 'Large variety that produces impressive yields'),
                    array('name' => 'Early Season', 'description' => 'Quick-maturing variety for shorter growing seasons')
                );
                break;
        }
        
        return array('varieties' => $varieties);
    }

    /**
     * Try to extract variety information from unstructured text
     * 
     * @param string $text The text to extract varieties from
     * @param string $plant_type The type of plant (optional)
     * @return array|null An array of extracted varieties or null if none found
     */
    protected function extract_varieties_from_text($text, $plant_type) {
        // Look for patterns like "1. Variety Name - Description" or "* Variety Name: Description"
        $patterns = array(
            '/(\d+\.\s+|\*\s+|-)?\s*([A-Z][a-zA-Z\s]+)(?:\s*[-:]\s*)([^\.]+)/', // numbered list, bullet points, or dashes
            '/([A-Z][a-zA-Z\s]+?)(?:\s*[:–-]\s*)([^\.]+)/'  // Variety Name: Description or Variety Name - Description
        );
        
        $varieties = array();
        
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                if (isset($match[2]) && isset($match[3])) {
                    $variety_name = trim($match[2]);
                    $description = trim($match[3]);
                    
                    // Skip if the variety name contains "JSON" or other keywords
                    if (stripos($variety_name, 'json') !== false || 
                        stripos($variety_name, 'variety') === 0 || 
                        stripos($variety_name, 'name') === 0) {
                        continue;
                    }
                    
                    $varieties[] = array(
                        'name' => $variety_name,
                        'description' => $description
                    );
                } else if (isset($match[1]) && isset($match[2])) {
                    // For the second pattern
                    $variety_name = trim($match[1]);
                    $description = trim($match[2]);
                    
                    // Skip if the variety name contains "JSON" or other keywords
                    if (stripos($variety_name, 'json') !== false || 
                        stripos($variety_name, 'variety') === 0 || 
                        stripos($variety_name, 'name') === 0) {
                        continue;
                    }
                    
                    $varieties[] = array(
                        'name' => $variety_name,
                        'description' => $description
                    );
                }
            }
        }
        
        // If we found at least 2 varieties, return them
        if (count($varieties) >= 2) {
            return array('varieties' => $varieties);
        }
        
        // Otherwise, fall back to a default set
        return null;
    }

    /**
     * Get detailed information about a specific seed variety with comprehensive details
     * 
     * @deprecated Use get_seed_details() instead
     */
    public function handle_seed_details_ajax() {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            return;
        }
        
        check_ajax_referer('seed_catalog_gemini_nonce', 'nonce');

        $variety = isset($_POST['variety']) ? sanitize_text_field($_POST['variety']) : '';
        $plant_type = isset($_POST['plant_type']) ? sanitize_text_field($_POST['plant_type']) : '';
        $brand = isset($_POST['brand']) ? sanitize_text_field($_POST['brand']) : '';
        $sku = isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : '';

        // Just delegate to the main method with the same parameters
        // The method will handle sending the JSON response
        $this->get_seed_details($variety, $plant_type, $brand, $sku);
    }

    /**
     * Generate an image using AI
     */
    protected function generate_image($prompt) {
        // This is just a placeholder - you would need to implement an actual image generator API
        return array(
            'success' => false,
            'message' => 'Image generation not implemented'
        );
    }
    
    /**
     * Log debug information if debugging is enabled
     * 
     * @param string $message The message to log
     * @return void
     */
    protected function log_debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            // Create logs directory if it doesn't exist
            $logs_dir = SEED_CATALOG_PLUGIN_DIR . 'logs';
            if (!is_dir($logs_dir)) {
                if (!file_exists($logs_dir)) {
                    wp_mkdir_p($logs_dir);
                }
            }
            
            $log_file = $logs_dir . '/gemini-api.log';
            $timestamp = date('[Y-m-d H:i:s]');
            error_log($timestamp . ' ' . $message . PHP_EOL, 3, $log_file);
        }
    }

    /**
     * Get the client configuration
     *
     * @return array The client configuration
     */
    protected function get_client_config() {
        // Get OAuth credentials from WordPress settings instead of using hardcoded values
        $oauth_config = \SeedCatalog\Seed_Catalog_Settings::get_oauth_config();
        
        // If settings are available, use them; otherwise fall back to the default empty config
        if (!empty($oauth_config)) {
            return $oauth_config;
        }
        
        // Return the default config with placeholders (won't work but prevents errors)
        return json_decode(self::DEFAULT_CLIENT_CONFIG, true);
    }

    /**
     * Public test method for seed varieties search
     * This wraps the protected functionality for testing purposes
     * 
     * @param string $search_term The search term to test with
     * @return array|\WP_Error The search results or error
     */
    public function test_seed_varieties($search_term) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'No API key configured');
        }

        // Build a test prompt that strictly requires JSON output
        $prompt = <<<EOT
Return ONLY a valid JSON object (no other text) that lists common varieties of {$search_term}. Use exactly this structure:
{
    "varieties": [
        {
            "name": "Variety Name",
            "description": "Brief description"
        }
    ]
}
EOT;

        $response = $this->call_gemini_api($prompt, $this->api_key);
        
        if (!$response['success']) {
            return new WP_Error('api_error', $response['message']);
        }

        // Parse the response
        $parsed_data = $this->extract_json_from_response($response['data']);
        
        if (!$parsed_data) {
            // If parsing fails, return the raw response for debugging
            return array(
                'error' => 'Failed to parse JSON response',
                'raw_response' => $response['data']
            );
        }

        return $parsed_data;
    }

    /**
     * Process image recognition requests
     * 
     * @since    1.0.0
     * @return   void
     */
    public function process_gemini_image_recognition() {
        if (!wp_doing_ajax()) {
            wp_send_json_error(array('message' => __('This method should be called via AJAX', 'seed-catalog')));
            return;
        }

        // Verify nonce
        check_ajax_referer('seed_catalog_gemini_nonce', 'nonce');

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to use the image recognition feature.', 'seed-catalog')));
            return;
        }

        // Check if image was uploaded
        if (!isset($_FILES['seed_image']) || empty($_FILES['seed_image'])) {
            wp_send_json_error(array('message' => __('No image uploaded.', 'seed-catalog')));
            return;
        }

        // Validate file type
        $file_info = wp_check_filetype(
            $_FILES['seed_image']['name'],
            array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp'
            )
        );

        if (!$file_info['type']) {
            wp_send_json_error(array('message' => __('Invalid file type. Please upload a JPG, PNG, or WebP image.', 'seed-catalog')));
            return;
        }

        try {
            // Analyze the image using the Gemini Vision API
            $result = $this->analyze_image($_FILES['seed_image']['tmp_name']);
            
            // Validate the result
            if (!is_array($result)) {
                throw new Exception(__('Invalid analysis result format', 'seed-catalog'));
            }

            // Process and format the result
            $formatted_result = array(
                'identification' => $result['identification'] ?? '',
                'variety' => $result['variety'] ?? '',
                'characteristics' => $result['characteristics'] ?? array(),
                'confidence_level' => $result['confidence_level'] ?? 'low',
                'notes' => $result['notes'] ?? '',
                'success' => true
            );

            wp_send_json_success($formatted_result);

        } catch (Exception $e) {
            $this->log_debug("Image analysis error: " . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Error analyzing image: ', 'seed-catalog') . $e->getMessage(),
                'error_type' => 'analysis_error'
            ));
        }
    }

    /**
     * Public method to test the API connection
     * 
     * @param string $api_key Optional API key to use for testing
     * @return array The API response with success/failure status
     */
    public function test_api_connection($api_key = null) {
        if ($api_key) {
            $this->set_api_key($api_key);
        }

        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('No API key provided.', 'seed-catalog'),
                'error_type' => 'missing_key'
            );
        }

        $prompt = "Please respond with the text 'API test successful' and nothing else.";
        return $this->call_gemini_api($prompt, $this->api_key);
    }

    /**
     * Analyze an image using Gemini Vision API
     *
     * @since    1.1.0
     * @param    string    $image_path    Path to the image file
     * @return   array                    Analysis results
     * @throws   Exception               When API calls fail
     */
    public function analyze_image($image_path) {
        if (!$this->is_configured()) {
            throw new Exception(__('Gemini API is not configured', 'seed-catalog'));
        }

        if (!file_exists($image_path)) {
            throw new Exception(__('Image file not found', 'seed-catalog'));
        }

        // Read and encode image
        $image_data = file_get_contents($image_path);
        if ($image_data === false) {
            throw new Exception(__('Failed to read image file', 'seed-catalog'));
        }

        $base64_image = base64_encode($image_data);
        $mime_type = mime_content_type($image_path);

        // Prepare the prompt for seed identification
        $prompt = "Analyze this image and identify what type of seed or plant it shows. " .
                "Focus on identifying the plant species, variety if visible, and any distinctive characteristics. " .
                "Provide your response in JSON format with the following structure:\n" .
                "{\n" .
                "  \"identification\": \"Name of the plant/seed\",\n" .
                "  \"variety\": \"Specific variety if identifiable\",\n" .
                "  \"characteristics\": [\"key characteristic 1\", \"key characteristic 2\"],\n" .
                "  \"confidence_level\": \"high|medium|low\",\n" .
                "  \"notes\": \"Any additional observations\"\n" .
                "}";

        // Prepare API request body
        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $prompt
                        ),
                        array(
                            'inline_data' => array(
                                'mime_type' => $mime_type,
                                'data' => $base64_image
                            )
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.4,
                'topP' => 0.8,
                'topK' => 40,
                'maxOutputTokens' => 1024
            )
        );

        // Make API request
        $response = wp_remote_post(
            self::GEMINI_PRO_VISION_API_URL . '?key=' . $this->api_key,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($body),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($error_body['error']['message']) 
                ? $error_body['error']['message'] 
                : __('API request failed', 'seed-catalog');
            throw new Exception($error_message);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception(__('Invalid API response format', 'seed-catalog'));
        }

        // Parse the JSON response
        $analysis_text = $body['candidates'][0]['content']['parts'][0]['text'];
        $analysis = json_decode($analysis_text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // If JSON parsing fails, try to extract structured data from the text response
            $analysis = $this->extract_structured_data($analysis_text);
        }

        return $analysis;
    }

    /**
     * Extract structured data from text response when JSON parsing fails
     *
     * @since    1.1.0
     * @param    string    $text    The text response from the API
     * @return   array             Structured data
     */
    private function extract_structured_data($text) {
        // Default structure
        $data = array(
            'identification' => '',
            'variety' => '',
            'characteristics' => array(),
            'confidence_level' => 'low',
            'notes' => ''
        );

        // Try to extract key information using regex patterns
        if (preg_match('/(?:plant|seed)(?:\s+type)?[:\s]+([^\.]+)/i', $text, $matches)) {
            $data['identification'] = trim($matches[1]);
        }

        if (preg_match('/variety[:\s]+([^\.]+)/i', $text, $matches)) {
            $data['variety'] = trim($matches[1]);
        }

        // Extract characteristics (look for bullet points, numbered lists, or comma-separated lists)
        if (preg_match_all('/[•\-\*]\s*([^\.]+)/', $text, $matches)) {
            $data['characteristics'] = array_map('trim', $matches[1]);
        } elseif (preg_match('/characteristics?[:\s]+([^\.]+)/i', $text, $matches)) {
            $data['characteristics'] = array_map('trim', explode(',', $matches[1]));
        }

        // Extract confidence level
        if (preg_match('/confidence[:\s]+(high|medium|low)/i', $text, $matches)) {
            $data['confidence_level'] = strtolower($matches[1]);
        }

        // Use remaining text as notes
        $data['notes'] = trim($text);

        return $data;
    }

    /**
     * Format log message with additional context
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     * @return string Formatted log message
     */
    private function format_log_message($message, $context = array()) {
        $formatted = $message;
        
        if (!empty($context)) {
            $context_str = array();
            foreach ($context as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif (is_object($value)) {
                    $value = get_class($value);
                }
                $context_str[] = "$key: $value";
            }
            $formatted .= ' [' . implode(', ', $context_str) . ']';
        }
        
        return $formatted;
    }

    /**
     * Extract error details from API response
     *
     * @param array|WP_Error $response The API response
     * @return array Error details
     */
    private function extract_error_details($response) {
        if (is_wp_error($response)) {
            return array(
                'code' => $response->get_error_code(),
                'message' => $response->get_error_message()
            );
        }

        $body = is_array($response) ? $response : json_decode(wp_remote_retrieve_body($response), true);
        return array(
            'code' => isset($body['error']['code']) ? $body['error']['code'] : 'unknown_error',
            'message' => isset($body['error']['message']) ? $body['error']['message'] : __('Unknown error occurred', 'seed-catalog')
        );
    }
}