<?php
/**
 * The class responsible for plugin settings
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */

namespace SeedCatalog;

/**
 * Handles plugin settings including API keys and OAuth credentials
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 */
class Seed_Catalog_Settings {
    /**
     * The option name for the Gemini API key
     */
    const OPTION_API_KEY = 'seed_catalog_gemini_api_key';

    /**
     * The option name for the Google OAuth client ID
     */
    const OPTION_OAUTH_CLIENT_ID = 'seed_catalog_oauth_client_id';

    /**
     * The option name for the Google OAuth client secret
     */
    const OPTION_OAUTH_CLIENT_SECRET = 'seed_catalog_oauth_client_secret';

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        // Settings page is now registered in class-seed-catalog-post-types.php
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Register settings section
        add_settings_section(
            'seed_catalog_api_settings',
            __('API Settings', 'seed-catalog'),
            array($this, 'render_api_settings_section'),
            'seed_catalog_settings'
        );

        // Register API key field
        register_setting(
            'seed_catalog_settings',
            self::OPTION_API_KEY,
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add API key field
        add_settings_field(
            self::OPTION_API_KEY,
            __('Gemini API Key', 'seed-catalog'),
            array($this, 'render_api_key_field'),
            'seed_catalog_settings',
            'seed_catalog_api_settings'
        );

        // Register settings
        register_setting('seed_catalog_settings', self::OPTION_OAUTH_CLIENT_ID);
        register_setting('seed_catalog_settings', self::OPTION_OAUTH_CLIENT_SECRET);

        // Add settings section
        add_settings_section(
            'seed_catalog_api_settings',
            __('API Settings', 'seed-catalog'),
            array($this, 'render_api_settings_section'),
            'seed_catalog_settings'
        );
        
        // Add version management section
        add_settings_section(
            'seed_catalog_version_settings',
            __('Version Management', 'seed-catalog'),
            array($this, 'render_version_settings_section'),
            'seed_catalog_settings'
        );

        // Add settings fields
        add_settings_field(
            self::OPTION_API_KEY,
            __('Gemini API Key', 'seed-catalog'),
            array($this, 'render_api_key_field'),
            'seed_catalog_settings',
            'seed_catalog_api_settings'
        );

        add_settings_field(
            self::OPTION_OAUTH_CLIENT_ID,
            __('Google OAuth Client ID', 'seed-catalog'),
            array($this, 'render_oauth_client_id_field'),
            'seed_catalog_settings',
            'seed_catalog_api_settings'
        );

        add_settings_field(
            self::OPTION_OAUTH_CLIENT_SECRET,
            __('Google OAuth Client Secret', 'seed-catalog'),
            array($this, 'render_oauth_client_secret_field'),
            'seed_catalog_settings',
            'seed_catalog_api_settings'
        );
        
        // Add version info field
        add_settings_field(
            'seed_catalog_version_info',
            __('Current Version', 'seed-catalog'),
            array($this, 'render_version_info_field'),
            'seed_catalog_settings',
            'seed_catalog_version_settings'
        );
        
        // Register AJAX handlers for version management
        add_action('wp_ajax_seed_catalog_force_version_update', array($this, 'handle_force_version_update'));
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Show message if settings were updated
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'seed_catalog_messages',
                'seed_catalog_message',
                __('Settings Saved', 'seed-catalog'),
                'updated'
            );
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('seed_catalog_settings');
                do_settings_sections('seed_catalog_settings');
                submit_button(__('Save Settings', 'seed-catalog'));
                ?>
            </form>
            <div class="card">
                <h2><?php _e('Getting Started with API Integration', 'seed-catalog'); ?></h2>
                <p>
                    <?php _e('To use the AI-powered features of Seed Catalog, you need to:', 'seed-catalog'); ?>
                </p>
                <ol>
                    <li><?php _e('Create a Google Cloud Platform account', 'seed-catalog'); ?></li>
                    <li><?php _e('Enable the Gemini API in Google AI Studio', 'seed-catalog'); ?></li>
                    <li><?php _e('Create an API key for Gemini', 'seed-catalog'); ?></li>
                </ol>
                <p>
                    <a href="https://makersuite.google.com/app" target="_blank" class="button">
                        <?php _e('Get API Key from Google AI Studio', 'seed-catalog'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Render API settings section description
     */
    public function render_api_settings_section() {
        echo '<p>' . __('Configure your Google Gemini API credentials for AI-powered seed information.', 'seed-catalog') . '</p>';
    }

    /**
     * Render version settings section description
     */
    public function render_version_settings_section() {
        echo '<p>' . __('Manage the version of the Seed Catalog plugin.', 'seed-catalog') . '</p>';
    }

    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $api_key = get_option(self::OPTION_API_KEY, '');
        ?>
        <input type="password" 
               id="<?php echo self::OPTION_API_KEY; ?>"
               name="<?php echo self::OPTION_API_KEY; ?>" 
               value="<?php echo esc_attr($api_key); ?>" 
               class="regular-text"
               autocomplete="off" />
        <p class="description">
            <?php _e('Enter your Google Gemini API key here. This is required for AI-powered seed information.', 'seed-catalog'); ?>
        </p>
        <?php
        // Add a test button if we have an API key
        if (!empty($api_key)) {
            ?>
            <button type="button" id="test-gemini-api" class="button">
                <?php _e('Test API Connection', 'seed-catalog'); ?>
            </button>
            <span id="api-test-result" style="display:none; margin-left: 10px;"></span>
            <script>
            jQuery(document).ready(function($) {
                $('#test-gemini-api').on('click', function() {
                    const $button = $(this);
                    const $result = $('#api-test-result');
                    
                    $button.prop('disabled', true);
                    $result.html('Testing...').removeClass('notice-error notice-success').show();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'test_gemini_api',
                            nonce: '<?php echo wp_create_nonce('seed_catalog_test_api'); ?>',
                            api_key: $('#<?php echo self::OPTION_API_KEY; ?>').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                $result.html('✓ API connection successful').addClass('notice-success');
                            } else {
                                $result.html('✗ ' + response.data.message).addClass('notice-error');
                            }
                        },
                        error: function() {
                            $result.html('✗ Connection error').addClass('notice-error');
                        },
                        complete: function() {
                            $button.prop('disabled', false);
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }

    /**
     * Render OAuth client ID field
     */
    public function render_oauth_client_id_field() {
        $client_id = get_option(self::OPTION_OAUTH_CLIENT_ID, '');
        ?>
        <input type="text" 
               name="<?php echo self::OPTION_OAUTH_CLIENT_ID; ?>" 
               value="<?php echo esc_attr($client_id); ?>" 
               class="regular-text" />
        <p class="description">
            <?php _e('Google OAuth Client ID for advanced authentication (optional, required for image recognition).', 'seed-catalog'); ?>
        </p>
        <?php
    }

    /**
     * Render OAuth client secret field
     */
    public function render_oauth_client_secret_field() {
        $client_secret = get_option(self::OPTION_OAUTH_CLIENT_SECRET, '');
        ?>
        <input type="password" 
               name="<?php echo self::OPTION_OAUTH_CLIENT_SECRET; ?>" 
               value="<?php echo esc_attr($client_secret); ?>" 
               class="regular-text"
               autocomplete="off" />
        <p class="description">
            <?php _e('Google OAuth Client Secret for advanced authentication (optional, required for image recognition).', 'seed-catalog'); ?>
        </p>
        <?php
    }

    /**
     * Render version info field
     */
    public function render_version_info_field() {
        // Get the current version from the main plugin file
        $plugin_data = get_plugin_data(SEED_CATALOG_PLUGIN_FILE);
        $current_version = $plugin_data['Version'];
        
        // Get the stored version from options
        $stored_version = get_option('seed_catalog_version', '0.0.0');
        
        // Get info about the last time version was updated
        $last_version_update = get_option('seed_catalog_last_version_update', 0);
        $last_update_date = $last_version_update ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_version_update) : __('Never', 'seed-catalog');
        
        // Add nonce for security
        $nonce = wp_create_nonce('seed_catalog_version_update');
        ?>
        <div class="seed-catalog-version-info">
            <p><?php echo sprintf(__('Current plugin version: <strong>%s</strong>', 'seed-catalog'), esc_html($current_version)); ?></p>
            <p><?php echo sprintf(__('Stored database version: <strong>%s</strong>', 'seed-catalog'), esc_html($stored_version)); ?></p>
            <p><?php echo sprintf(__('Last automatic update: <strong>%s</strong>', 'seed-catalog'), esc_html($last_update_date)); ?></p>
            
            <p><button type="button" id="seed-catalog-force-version-update" class="button" data-nonce="<?php echo esc_attr($nonce); ?>"><?php _e('Force Version Update', 'seed-catalog'); ?></button></p>
            
            <div id="version-update-result" class="notice notice-info hidden">
                <p></p>
            </div>
            
            <p class="description"><?php _e('The plugin will automatically increment the version when changes are detected. Use the button above to force a version increment for testing.', 'seed-catalog'); ?></p>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#seed-catalog-force-version-update').on('click', function() {
                var $button = $(this);
                var $result = $('#version-update-result');
                
                $button.prop('disabled', true).text('<?php _e('Updating...', 'seed-catalog'); ?>');
                $result.addClass('hidden');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'seed_catalog_force_version_update',
                        nonce: $(this).data('nonce')
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.removeClass('notice-info notice-error').addClass('notice-success').removeClass('hidden')
                                .find('p').text(response.data.message);
                                
                            // Update the displayed version info
                            if (response.data.new_version) {
                                $('.seed-catalog-version-info p:first-child strong').text(response.data.new_version);
                            }
                        } else {
                            $result.removeClass('notice-info notice-success').addClass('notice-error').removeClass('hidden')
                                .find('p').text(response.data.message || '<?php _e('An unknown error occurred.', 'seed-catalog'); ?>');
                        }
                    },
                    error: function() {
                        $result.removeClass('notice-info notice-success').addClass('notice-error').removeClass('hidden')
                            .find('p').text('<?php _e('Failed to communicate with the server.', 'seed-catalog'); ?>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php _e('Force Version Update', 'seed-catalog'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Handle force version update via AJAX
     */
    public function handle_force_version_update() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seed_catalog_version_update')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'seed-catalog')));
            return;
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'seed-catalog')));
            return;
        }
        
        // Get the version manager instance
        global $seed_catalog_version_manager;
        
        if (!$seed_catalog_version_manager) {
            // If the global instance isn't available, create a new one
            $version_manager = new Seed_Catalog_Version_Manager(SEED_CATALOG_PLUGIN_FILE);
            $new_version = $version_manager->force_version_bump();
        } else {
            // Use the global instance
            $new_version = $seed_catalog_version_manager->force_version_bump();
        }
        
        // Return success response with the new version
        wp_send_json_success(array(
            'message' => sprintf(__('Version successfully updated to %s.', 'seed-catalog'), $new_version),
            'new_version' => $new_version
        ));
    }

    /**
     * Get the Gemini API key
     * 
     * @return string The API key
     */
    public static function get_api_key() {
        return get_option(self::OPTION_API_KEY, '');
    }

    /**
     * Get the OAuth client configuration
     * 
     * @return array The client configuration (empty array if not configured)
     */
    public static function get_oauth_config() {
        $client_id = get_option(self::OPTION_OAUTH_CLIENT_ID, '');
        $client_secret = get_option(self::OPTION_OAUTH_CLIENT_SECRET, '');
        
        if (empty($client_id) || empty($client_secret)) {
            // Return an empty array instead of null to match the declared return type
            return array();
        }
        
        return array(
            'web' => array(
                'client_id' => $client_id,
                'project_id' => 'seed-catalog-plugin',
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_secret' => $client_secret
            )
        );
    }
}