<?php

class SimpleJSONAPISettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Simple JSON API Admin', 
            'Simple JSON API', 
            'manage_options', 
            'simple-json-api-admin', 
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('simple_json_api_options');
        ?>
        <div class="wrap">
            <?php screen_icon("plugins"); ?>
            <h2>Simple JSON API Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields('simple_json_api_option_group');   
                do_settings_sections('simple-json-api-admin');
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'simple_json_api_option_group', // Option group
            'simple_json_api_options', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'simple_json_api_auth_section', // ID
            'Authentication', // Title
            array($this, 'print_auth_section_info'), // Callback
            'simple-json-api-admin' // Page
        );

        add_settings_field(
            'master_key', // ID
            'Master Key', // Title 
            array($this, 'field_master_key_callback'), // Callback
            'simple-json-api-admin', // Page
            'simple_json_api_auth_section' // Section           
        );      

        add_settings_field(
            'key_name', 
            'Expected Key Name (Query String)', 
            array($this, 'field_key_name_callback'), 
            'simple-json-api-admin', 
            'simple_json_api_auth_section'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        // No sanitization necessary for now
        return $input;
    }

    /** 
     * Print the Auth Section text
     */
    public function print_auth_section_info()
    {
        print 'You can restrict access to your API via one hard-coded key. <strong>Leave the master key field blank to allow unrestricted access to the /api endpoint</strong>.';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function field_master_key_callback()
    {
        printf(
            '<input type="text" id="master_key" class="regular-text ltr" name="simple_json_api_options[master_key]" value="%s" />',
            esc_attr($this->options['master_key'])
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function field_key_name_callback()
    {
        printf(
            '<input type="text" id="key_name" class="regular-text ltr" name="simple_json_api_options[key_name]" value="%s" /><br><p class="description">%s</p>',
            esc_attr($this->options['key_name']),
            "Default value is 'key', ie api/post?key=myapikey"
        );
    }
}