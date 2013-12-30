<?php
/**
 * Wrapper around the WP get_posts and get_post functions.
 * *
 * @see http://codex.wordpress.org/Function_Reference/get_posts
 * @see http://codex.wordpress.org/Function_Reference/get_post
 *
 * @package WordPress
 * @subpackage Simple JSON API
 */

class Query {

    protected $taxonomies = array();
    protected $has_featured = false;

    public function __construct() {

        $this->taxonomies = get_taxonomies();

    }


    /**
     * Returns a fully formed array of WP objects,
     * including metadata and featured image.
     * 
     * @param  array $options WP options
     * @return array          WP objects
     */
    public function get_collection($options) {
        $wp_collection = get_posts($options);
        $mapped_collection = array();
        foreach ($wp_collection as $object) {
            $mapped_collection[] = $this->mapObjectData($object);
        }

        return $mapped_collection;
    }


    /**
     * Returns a single fully formed WP object,
     * including metadata and featured image.
     * 
     * @param int $id WP object ("post") ID
     * @return object     WP object
     */
    public function get_single($id) {
        $single = get_post($id);
        return $this->mapObjectData($single);
    }


    /**
     * Add metadata and featured image to post object
     * 
     * @param  object $object post object
     * @return object         modified post object
     */
    protected function mapObjectData($object) {
        
        if ($this->has_featured && has_post_thumbnail($object->ID)) {
            $object->featured_image = $this->get_featured_image($object);
        }
        if (function_exists("get_fields")) {
           $object->meta = get_fields($object->ID);
        } else {
            $object->meta = get_post_meta($object->ID);
        }

        $object->terms = $this->getTerms($object->ID);

        return $object;
    }

    protected function getTerms($id)
    {
        return array_map(function ($vocab) use ($id) {
            return get_the_terms($id, $vocab);
        }, $this->taxonomies);
    }


    /**
     * Checks to see if the theme supports featured images.
     * 
     * @return boolean TRUE if theme supports featured images;
     *                 FALSE if theme does not support featured images.
     */
    protected function has_featured()
    {
        return get_theme_support("post-thumbnails");
    }


    /**
     * Get the featured image on the given item.
     * 
     * @param  object $item Post object
     * @return array
     */
    protected function get_featured_image($item)
    {
        if (! $this->has_featured()) {
            return null;
        }

        $feature_id = get_post_thumbnail_id($item->ID);

        if (empty($feature_id)) {
            return null;
        }

        $args = array(
            "id" => $feature_id,
            "status" => "any",
            "post_type" => "attachment"
        );

        return get_posts($args);
    }


}