<?php

require_once dirname(__FILE__) . '/MetaBox.php';

class CustomObject {
    
    public $type;
    public $error_array = array();
    public $register_options = array();
    public $metaboxes = 0;
    public $metabox_options = array();
    
    public function __construct( $type, $options = array(), $labels = array() ) {
                    
        // Pass in all of your options in the options array, labels in the labels array -- duh
        // 
        // For a complete list of available options, see: http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
        
        $this->type = $type;
        
        if ( !!$options ) {     
            $this->register_options = $options;
            // Setting up local class variables for convenience
            // TODO: Is this way too expensive for performance? We could easily swap it out.
            foreach ( $options as $key => $value ) {
                $this->$key = $value; 
            }
        }
        
        if ( !!$labels ) {  
            // Setting up local class variables for convenience
            // TODO: Is this way too expensive for performance? We could easily swap it out.    
            foreach ( $labels as $key => $value ) {
                $this->$key = $value;
            }       
        }
        
        if ( $this->validate() ) {
            
            // Empty the error array
            $this->error_array = array();
            
            // Register this object's create_object_type() method to the init action
            add_action( 'init', array( &$this, 'create_object_type' ) );
            
            /*
                FYI This last action (and others below) was added using an array() with an object/method reference.
                This makes these classes so much more powerful, because each object instance
                can register itself, with its object properties, to an action, instead of creating
                a hundred silly functions in the global scope. No more create_object_type_1 functions necessary!
                
                For more info: https://twitter.com/#!/markjaquith/status/76038034440728576              
                Also, it's not necessary to use &$this, just $this works as of PHP 5. https://twitter.com/#!/mattwiebe/status/76040791725838336
            */
            
        }
    
        else { return $this->error_array; }
        
    } // end __construct()
    
    
    public function validate() {
        
        // If no type has been set, we can't create this object
        // Set an error message in error_array and be done with it
        if ( !isset( $this->type ) || empty( $this->type ) ) {
            $this->error_array[] = "A new custom object needs a <em>type</em>, like 'book', 'restaurant', or 'person'.";
            return false;
        }
        else {
            $this->type = str_replace( " ", "_", $this->type );
            if ( substr( $this->type, -1) == "s" ) { $this->type = substr( $this->type, 0, -1); }
            $this->type = strtolower( $this->type );
        }
        
        // A couple other required setups once $this->type has been set
        if ( !isset( $this->singular_name ) && !!$this->type ) {
            $spaced_type = str_replace( "_", " ", $this->type );
            $this->singular_name = ucwords( $spaced_type );
        }
        if ( !isset( $this->plural_name ) && !!$this->singular_name ) {
            $this->plural_name = $this->singular_name . "s";
        }
        
        return true;
    
    } // end validate()
    
    
    public function setup_labels() {
    
        // I've found that I usually just want these labels to be what you'd expect
        // So I leave $labels an empty array and let this method do its magic.
        
        if ( !isset( $this->add_new ) ) {
            $this->add_new = "Add New " . $this->singular_name;
        }
        if ( !isset( $this->add_new_item ) ) {
            $this->add_new_item = "Add New " . $this->singular_name;
        }
        if ( !isset( $this->edit ) ) { 
            $this->edit = "Edit"; 
        }
        if ( !isset( $this->edit_item ) ) { 
            $this->edit_item = "Edit " . $this->singular_name; 
        }
        if ( !isset( $this->new_item ) ) { 
            $this->new_item = "New " . $this->singular_name; 
        }
        if ( !isset( $this->view ) ) { 
            $this->view = "View " . $this->singular_name . " Page"; 
        }
        if ( !isset( $this->view_item ) ) { 
            $this->view_item = "View " . $this->singular_name; 
        }
        if ( !isset( $this->search_items ) ) { 
            $this->search_items = "Search " . $this->plural_name; 
        }
        if ( !isset( $this->not_found ) ) {
            $this->not_found = "No matching " . strtolower( $this->plural_name ) . " found";
        }
        if ( !isset( $this->not_found_in_trash ) ) {
            $this->not_found_in_trash = "No " . strtolower( $this->plural_name ) . " found in Trash";
        }
        if ( !isset( $this->parent_item_colon ) ) {
            $this->parent_item_colon = "Parent " . $this->singular_name;
        }
        
        $this->register_options['labels'] = array(
            'name' => __( $this->plural_name ),
            'singular_name' => __( $this->singular_name ),
            'add_new' => __( $this->add_new ),
            'add_new_item' => __( $this->add_new_item ),
            'edit' => __( $this->edit ),
            'edit_item' => __( $this->edit_item ),
            'new_item' => __( $this->new_item ),
            'view' => __( $this->view ),
            'view_item' => __( $this->view_item ),
            'search_items' => __( $this->search_items ),
            'not_found' => __( $this->not_found ),
            'not_found_in_trash' => __( $this->not_found_in_trash ),
            'parent_item_colon' => __( $this->parent_item_colon ),
        );
    
    } // end setup_labels()


    public function create_object_type() {
    
        $this->setup_labels();
        register_post_type( $this->type, $this->register_options );
    
    } // end create_object_type()


    /* 
     * And that's it! Your new object type is set up and you don't *need* to do anything else.
     * However...
     * 
     * At this point, your new object looks... exactly like a post. So that's kind of dumb.
     * In order to add value to the new object type, we need to do a few of these other things.
     *
     * To do that, call these methods after you've initialized your new object type, like:
     *      
     *      $event = new CustomObject( $type, $options, $labels ); 
     *      $event->setup_metabox( $metabox_options );
     *
     * Or something like that.
     *
     */
    
    public function setup_metabox( $options=array() ) {
        
        if ( !is_array( $options ) ) $options = array( $options );
        $mb = new MetaBox( $options, $this->type );
        return $mb;

    } // end setup_metabox()
    
    public function disable_addnew() {
        
        /* Use this function to disable the ability to add new objects.
         * Once you've created a few, you may want to lock it down so your
         * users can't create any additional objects of this kind.
         */
         
         /* http://minimalbugs.com/questions/how-to-disable-add-new-post-in-particular-custom-post-types-wordpress */
        
        add_action( 'admin_menu', array( $this, 'disable_addnew_hide_submenu' ) );
        add_action( 'admin_head', array( $this, 'disable_addnew_hide_button' ) );
        add_action( 'admin_menu', array( $this, 'disable_addnew_permissions_redirect' ) );
        add_action( 'admin_init', array( $this, 'disable_addnew_show_notice' ) );
        
    }


    public function disable_addnew_hide_submenu() {
        global $submenu;
        unset($submenu['edit.php?post_type='. $this->type][10]);
    }
    
    public function disable_addnew_hide_button() {
        global $pagenow;
        if ( is_admin() ) {
        if ( $pagenow == 'edit.php' && $_GET['post_type'] == $this->type ) {
          echo "<style type=\"text/css\">.add-new-h2{display: none;}</style>";
            }  
        }
    }
    
    public function disable_addnew_permissions_redirect() {
        $result = stripos( $_SERVER['REQUEST_URI'], 'post-new.php?post_type='. $this->type );
        if ( $result !== false ) {
            wp_redirect( get_option('siteurl') . '/wp-admin/index.php?'. $this->type . '_addnew_disabled=true' );
        }
    }
    
    public function disable_addnew_show_notice() {
        if ( $_GET[$this->type . '_addnew_disabled'] ) {
            add_action( 'admin_notices', array( $this, 'disable_addnew_admin_notice' ) );
        }
    }
    
    public function disable_addnew_admin_notice() {
        // use the class "error" for red notices, and "update" for yellow notices
        echo "<div id='permissions-warning' class='error fade'><p><strong>".__('Adding new ' . $this->plural_name . ' is currently disabled.')."</strong></p></div>";
    }

}