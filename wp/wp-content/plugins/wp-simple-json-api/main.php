<?php
/**
 * Plugin Name: Simple JSON API
 * Plugin URI: https://github.com/johnshopkins/wp-simple-json-api
 * Description: Generates a simple read-only JSON API
 * Version: 0.2
 * Author: Jason Rhodes
 * Author URI: http://notrobotic.com
 * License: MIT
 */

require dirname(__FILE__) . '/classes/SimpleJSONAPI.php';
require dirname(__FILE__) . '/classes/SimpleJSONAPISettingsPage.php';

$options = get_option('simple_json_api_options');

// Create and activate the API
$api = new SimpleJSONAPI();
$api->set_key($options['master_key']);
$api->set_expected_key_name($options['key_name']);
$api->activate();

// Flush rewrite rules when plugin is activated/deactivated
register_activation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

// Create and register settings page
if(is_admin()) {
    $settings_page = new SimpleJSONAPISettingsPage();
}

// // Add settings link on plugin page 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", function ($links) {
    $settings_link = '<a href="options-general.php?page=simple-json-api-admin">Settings</a>'; 
    array_unshift($links, $settings_link); 
    return $links; 
});
