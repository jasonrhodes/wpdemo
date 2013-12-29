<?php

class MetaBox {
    
    public $options;
    public $type;
    private static $nonce_name = 'wp-custom-object-nonce';
    
    function __construct( $options = array(), $type ) {
        
        if ( !is_array( $options ) ) $options = array( $options );
        
        // Save the $options to the class so functions can use it later
        $this->options = $options;
        foreach ( $this->options as $k => $v ) {
            $this->$k = $v;
        }
        
        if ( !isset( $this->type ) ) $this->type = $type;
        
        // Set some defaults if params weren't passed
        $this->setup_options();     
        
        // Register this meta box to a WP action
        if ( has_action( 'add_meta_boxes' ) ) {
            // WP 3.0+
            add_action( 'add_meta_boxes', array( $this, 'wpco_add_meta_box' ) );
        }
        else {
            // backwards compatible?
            add_action( 'admin_init', array( $this, 'wpco_add_meta_box' ), 1 );
        }
        
        // Make sure you save any data
        add_action( 'save_post', array( $this, 'wpco_save_meta_box' ) );
        
    }
    
    function setup_options() {
    
        $allowed_context = array(
            'normal', 'advanced', 'side'
        );
        
        $allowed_priority = array(
            'high', 'core', 'default', 'low'
        );
        
        $this->id = isset( $this->id ) && !empty( $this->id ) ? $this->id : "metabox-" . time();
        
        $this->title = isset( $this->title ) && !empty( $this->title ) ? $this->title : ucwords( $this->type ) . " Meta Box";
        
        $this->context = isset( $this->context ) && in_array( $this->context, $allowed_context ) ? $this->context : 'advanced';
        
        $this->priority = isset( $this->priority ) && in_array( $this->priority, $allowed_priority ) ? $this->priority : 'default';
    
    }
    
    function wpco_add_meta_box() {
    
        add_meta_box( $this->id, $this->title, array( $this, 'wpco_metabox_content' ), $this->type, $this->context, $this->priority );
    
    } // end wpco_add_meta_box() method
    
    function wpco_save_meta_box( $id ) {
    
        // Verify if this is an auto save routine. 
      // If it is, our form has not been submitted, so we don't want to do anything
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
          return;
    
      // Verify this came from the our screen and with proper authorization,
      // because save_post can be triggered at other times
      $nonce_name = 'wp_custom_object_nonce';
      $nonce_action = 'save_metabox_data';
     
     // wp_verify_nonce refused to work here, so I done busted it for now...
    
      // TODO: use the array of fields from the wpco_metabox_content TODO item, and save each one individually
      
      if ( is_array( $this->fields ) ) {
        
        //update_post_meta( $id, '_super_test2', $this->fields );
        
        foreach ( $this->fields as $field ) {
                $fieldname = $field['name'];
            $varname = "_".$fieldname;
            if ( isset( $_POST[$fieldname] ) ) {
                update_post_meta( $id, $varname, $_POST[$fieldname] );
            }
        }
      
      }
      
    } // end wpco_save_meta_box() method
    
    
    function wpco_metabox_content() {
        
        global $post;
        $id = get_the_ID();
        
        // Generate a semi-secure nonce name and action
        $nonce_action = 'save_metabox_data';
        $nonce_name = 'wp_custom_object_nonce';
        wp_nonce_field( $nonce_action, $nonce_name );
        
        if ( !!$this->description ) {
            echo "<p>" . $this->description . "</p>";
        }
        
        echo "<div style='padding: 10px 0;'>";
        
        if ( !$this->fields || !is_array( $this->fields ) ) { 
            echo "No content for this meta box.";
            return;
        }
        
        $default_field = array(
            'name' => false,
            'label' => false,
            'type' => 'text',
            'value' => '',
            'options' => array()
        );

        foreach ($this->fields as $field) {
            
            $field = $field + $default_field;
            extract($field);
            if (empty($name)) continue;

            $meta_value = get_post_meta($id, "_" . $name, true);
            
            if (isset($meta_value)) {
                $value = $meta_value;
            }
            elseif (isset($_POST[$name])) { 
                $value = $_POST[$name]; 
            }
            
            switch ($type) {
                
                case false:
                    echo "Field type not set.";
                    break;
                
                case 'textarea':
                    echo "<label for='{$name}' style='display: block; margin-bottom: 5px;'>{$label}</label>";
                    echo "<textarea class='widefat' name='{$name}' id='{$name}'>{$value}</textarea>";
                    break;
                    
                case 'checkbox':
                    echo "<p style='margin: 15px 0;'><input";
                    if ( !!$value ) echo " checked";
                    echo " type='checkbox' name='{$name}' id='{$name}' value='" . $field["value"] . "' />";
                    echo "<label for='{$name}' style='margin-left: 8px;'>{$label}</label></p>";
                    break;
                    
                case 'radio':
                    echo "<div style='margin: 15px 0;'>";
                    echo "<p style='margin-bottom: 5px;'><label for='{$name}'>{$label}</label></p>";
                    $i = 1;
                    foreach ( $field['options'] as $opt ) {
                        if (!is_array($opt)) { 
                            $opt_value = $opt_label = $opt; 
                        } else {
                            $opt_label = $opt[0];
                            $opt_value = isset($opt[1]) ? $opt[1] : $opt_label;
                        }
                        echo "<p><input";
                        if ( $opt_value == $value ) echo " checked";
                        echo " type='radio' name='{$name}' id='{$name}-{$i}' value='{$opt_value}' />";
                        echo " <label for='{$name}-{$i}'>{$opt_label}</label></p>";
                        $i++;
                    }
                    echo "</div>";
                    break;
                
                case 'select':
                    if ( !!$label ) { echo "<label for='{$name}'>{$label}</label>"; }
                    echo "<select class='widefat' style='margin: 15px 0;' name='{$name}' id='{$name}'>";
                    echo "<option value=''>--select one--</option>";
                    foreach ( $field['options'] as $opt ) {
                        if (!is_array($opt)) { 
                            $opt_label = $opt_label = $opt; 
                        } else {
                            $opt_label = $opt[0];
                            $opt_value = isset($opt[1]) ? $opt[1] : $opt_label;
                        }
                        echo "<option value='{$opt_value}'";
                        if ( $value == $opt_value ) echo " selected";
                        echo ">{$opt_label}</option>";
                    }
                    echo "</select>";
                    break;

                    
                default:
                    echo "<label for='{$name}' style='display: block; margin-bottom: 5px;'>{$label}</label>";
                    echo "<input class='widefat' style='margin-bottom: 15px;' type='text' name='{$name}' id='{$name}'";
                    if ( $value ) echo " value='{$value}'";
                    echo " />";
                    break;
                    
            }
            
        }
        
        echo "</div>";
                
    } // end wpco_metabox_content() method  
    
}