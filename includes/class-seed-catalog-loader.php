<?php
/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */

namespace SeedCatalog;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all actions and filters for the plugin.
 *
 * @since      1.0.0
 * @package    Seed_Catalog
 * @subpackage Seed_Catalog/includes
 */
class Seed_Catalog_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * The array of shortcodes registered with WordPress.
     *
     * @since    1.1.0
     * @access   protected
     * @var      array    $shortcodes    The shortcodes registered with WordPress.
     */
    protected $shortcodes;

    /**
     * Errors that occurred during hook registration.
     *
     * @since    1.1.0
     * @access   protected
     * @var      array    $errors    Any errors that occurred during hook registration.
     */
    protected $errors;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
        $this->shortcodes = array();
        $this->errors = array();
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string    $hook             The name of the WordPress action that is being registered.
     * @param    object    $component        A reference to the instance of the object on which the action is defined.
     * @param    string    $callback         The name of the function definition on the $component.
     * @param    int       $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int       $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     * @return   bool      Whether the action was successfully added.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        // Validate input parameters
        if (empty($hook) || !is_object($component) || empty($callback)) {
            $this->log_error(
                sprintf('Invalid action parameters. Hook: %s, Callback: %s', $hook, $callback)
            );
            return false;
        }

        // Check if the callback method exists
        if (!method_exists($component, $callback)) {
            $this->log_error(
                sprintf('Callback method %s does not exist in component %s', $callback, get_class($component))
            );
            return false;
        }

        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
        return true;
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string    $hook             The name of the WordPress filter that is being registered.
     * @param    object    $component        A reference to the instance of the object on which the filter is defined.
     * @param    string    $callback         The name of the function definition on the $component.
     * @param    int       $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int       $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     * @return   bool      Whether the filter was successfully added.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        // Validate input parameters
        if (empty($hook) || !is_object($component) || empty($callback)) {
            $this->log_error(
                sprintf('Invalid filter parameters. Hook: %s, Callback: %s', $hook, $callback)
            );
            return false;
        }

        // Check if the callback method exists
        if (!method_exists($component, $callback)) {
            $this->log_error(
                sprintf('Callback method %s does not exist in component %s', $callback, get_class($component))
            );
            return false;
        }

        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
        return true;
    }
    
    /**
     * Add a new shortcode to the collection to be registered with WordPress.
     *
     * @since    1.1.0
     * @param    string    $tag             The name of the shortcode tag.
     * @param    object    $component       A reference to the instance of the object on which the shortcode is defined.
     * @param    string    $callback        The name of the function definition on the $component.
     * @return   bool      Whether the shortcode was successfully added.
     */
    public function add_shortcode($tag, $component, $callback) {
        // Validate input parameters
        if (empty($tag) || !is_object($component) || empty($callback)) {
            $this->log_error(
                sprintf('Invalid shortcode parameters. Tag: %s, Callback: %s', $tag, $callback)
            );
            return false;
        }

        // Check if the callback method exists
        if (!method_exists($component, $callback)) {
            $this->log_error(
                sprintf('Callback method %s does not exist in component %s', $callback, get_class($component))
            );
            return false;
        }

        $this->shortcodes[] = array(
            'tag'         => $tag,
            'component'   => $component,
            'callback'    => $callback,
        );
        
        return true;
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array     $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string    $hook             The name of the WordPress filter that is being registered.
     * @param    object    $component        A reference to the instance of the object on which the filter is defined.
     * @param    string    $callback         The name of the function definition on the $component.
     * @param    int       $priority         The priority at which the function should be fired.
     * @param    int       $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array     The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Register the filters, actions, and shortcodes with WordPress.
     *
     * @since    1.0.0
     * @return   bool    Whether all hooks were registered successfully.
     */
    public function run() {
        $success = true;
        
        // Register filters
        foreach ($this->filters as $hook) {
            try {
                add_filter(
                    $hook['hook'],
                    array($hook['component'], $hook['callback']),
                    $hook['priority'],
                    $hook['accepted_args']
                );
            } catch (Exception $e) {
                $this->log_error(
                    sprintf(
                        'Error registering filter %s->%s on hook %s: %s',
                        get_class($hook['component']),
                        $hook['callback'],
                        $hook['hook'],
                        $e->getMessage()
                    )
                );
                $success = false;
            }
        }

        // Register actions
        foreach ($this->actions as $hook) {
            try {
                add_action(
                    $hook['hook'],
                    array($hook['component'], $hook['callback']),
                    $hook['priority'],
                    $hook['accepted_args']
                );
            } catch (Exception $e) {
                $this->log_error(
                    sprintf(
                        'Error registering action %s->%s on hook %s: %s',
                        get_class($hook['component']),
                        $hook['callback'],
                        $hook['hook'],
                        $e->getMessage()
                    )
                );
                $success = false;
            }
        }
        
        // Register shortcodes
        foreach ($this->shortcodes as $shortcode) {
            try {
                add_shortcode(
                    $shortcode['tag'],
                    array($shortcode['component'], $shortcode['callback'])
                );
            } catch (Exception $e) {
                $this->log_error(
                    sprintf(
                        'Error registering shortcode %s->%s with tag [%s]: %s',
                        get_class($shortcode['component']),
                        $shortcode['callback'],
                        $shortcode['tag'],
                        $e->getMessage()
                    )
                );
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Get any errors that occurred during hook registration.
     *
     * @since    1.1.0
     * @return   array    The errors that occurred.
     */
    public function get_errors() {
        return $this->errors;
    }
    
    /**
     * Log an error to the internal error collection and the debug log.
     *
     * @since    1.1.0
     * @access   private
     * @param    string    $message    The error message to log.
     */
    private function log_error($message) {
        $this->errors[] = $message;
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Seed Catalog Loader Error: ' . $message);
        }
    }
}