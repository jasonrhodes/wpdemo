<?php
/**
* @package Application Content Types
* @version 1.0
*/
/*
Plugin Name: Application Content Types
Description: Set up content types for the website/application.
*/

require_once dirname(__FILE__) . '/CustomObject.php';
require_once dirname(__FILE__) . '/MetaBox.php';

$features = new CustomObject('feature', array(
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail')
));

$features->setup_metabox(array(
    'id' => 'feature-options-metabox',
    'title' => 'Feature Options',
    'description' => 'This is where you will adjust feature options',
    'context' => 'side',
    'priority' => 'default',
    'fields' => array(
        array(
            'name' => 'feature_url',
            'label' => 'Link URL'
        ),
        array(
            'name' => 'layout',
            'label' => 'Layout',
            'type' => 'radio',
            'options' => array('full', 'overlay', 'right-image')
        )
    )
));