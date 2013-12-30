<?php

require_once(dirname(__FILE__) . '/Query.php');

class SimpleJSONAPI {

    /**
     * The hard-coded key or key function to check against
     * a request key for simple authentication 
     * 
     * Note: Leave as NULL to disable authentication)
     * 
     * @var mixed
     */
    protected $key = NULL;


    /**
     * Name of the query string key to check in $_GET
     * for the request key to check for authentication
     * 
     * @var string
     */
    protected $key_name;


    /**
     * Default values when querying for WP objects
     * 
     * @var array
     */
    protected $query_defaults = array(
        'posts_per_page' => -1
    );



    public function __construct() {
        add_action('init', array($this, 'register_rewrites'));
        add_filter('query_vars', array($this, 'add_query_vars'));
    }


    /**
     * Set a key or key retrieving function for simple auth
     * @param mixed $key      Hard-coded string key or callable key retrieving function
     * @param string $key_name Query string key to find comparison value in $_REQUEST
     */
    public function set_key($key) {
        $this->key = !empty($key) ? $key : NULL;
    }


    public function set_expected_key_name($name) {
        $this->key_name = !empty($name) ? $name : "key";
    }


    /**
     * Turn on the API
     * 
     * @return NULL 
     */
    public function activate() {
        add_action('wp', array($this, 'check_request'));
    }


    /**
     * Turn off the API 
     * 
     * Note: This may not work without attaching this to a 
     * hook that fires after the action has been set but before
     * the check actually happens... ?
     * 
     * @return NULL
     */
    public function deactivate() {
        remove_action('wp', array($this, 'check_request'));
    }


    /**
     * Create the appropriate rewrite rules
     * 
     * @return NULL
     */
    public function register_rewrites() {
        // add_rewrite_tag('%api_query_type%','([^&]+)');
        add_rewrite_rule('^api/([^/]*)/?([^/]*)?/?', 'index.php?api_query_type=$matches[1]&api_query_id=$matches[2]', 'top');
    }


    /**
     * Register query string variables for WP
     * 
     * @param array $query_vars WP-passed query vars array
     * 
     * @return array modified query vars array
     */
    public function add_query_vars($query_vars) {
        $query_vars[] = 'api_query_type';
        $query_vars[] = 'api_query_id';
        return $query_vars;
    }


    /**
     * Interrupt the WP load cycle to check if it's
     * an API request--if it is, fulfill request
     * 
     * @return NULL
     */
    public function check_request() {

        global $wp_query;
        $wpvars = $wp_query->query_vars;
        $query = new Query();

        // If it's not an API request, return and move on
        if (empty($wpvars['api_query_type']) && empty($wpvars['api_query_id'])) {
            return;
        }

        if (!empty($this->key) && $_GET[$this->key_name] !== $this->key) {
            http_response_code(403);
            die();
        }

        header("Content-type: application/json");

        if (!empty($wpvars['api_query_id'])) {
            $single = $query->get_single($wpvars['api_query_id']);
            echo json_encode($single);
            die();
        }

        $collection = $query->get_collection($this->get_args($wpvars, $_GET));
        echo json_encode($collection);
        die();

    }


    /**
     * Check to see if there is a registered api key or
     * key function and check user key against it
     * 
     * @param  string $user_key Key passed in via request
     * 
     * @return bool
     */
    protected function check_key($user_key) {
        if (empty($this->key)) {
            return true;
        }

        if (is_callable($this->key)) {
            return $this->key() === $user_key;
        }

        return $this->key === $user_key;
    }


    /**
     * Combine defaults and passed args and return
     * one processed args array for use in query
     * 
     * @param  array $wpvars Registered WP query vars
     * @param  array $get    $_GET array
     * 
     * @return array         processed args
     */
    public function get_args($wpvars, $get) {
        $args = array();
        if (!empty($wpvars['api_query_id'])) {
            $args['post_id'] = $wpvars['api_query_id'];
        }
        if (!empty($wpvars['api_query_type'])) {
            $args['post_type'] = $wpvars['api_query_type'];
        }

        return $args + $get + $this->query_defaults;
    }
}