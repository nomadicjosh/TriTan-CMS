<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * TriTan CMS Post Type Functions
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Retrieves post type data given a post type ID or post type array.
 *
 * @since 1.0.0
 * @param string|Post Type|null $posttype
 *            Post Type ID or post type array.
 * @param bool $array
 *            If set to true, data will return as an array, else as an object.
 * @return array|object
 */
function get_posttype($posttype, $array = true)
{
    if ($posttype instanceof \TriTan\PostType) {
        $_posttype = $posttype;
    } elseif (is_object($posttype)) {
        if (empty($posttype->posttype_id)) {
            $_posttype = new \TriTan\PostType($posttype);
        } else {
            $_posttype = \TriTan\PostType::get_instance($posttype->posttype_id);
        }
    } else {
        $_posttype = \TriTan\PostType::get_instance($posttype);
    }

    if (!$_posttype) {
        return null;
    }

    if ($array == false) {
        $_posttype = array_to_object($_posttype);
    }
    
    /**
	 * Fires after a post type is retrieved.
	 *
	 * @since 1.0.0
	 * @param Posttype $_posttype Posttype data.
	 */
	$_posttype = app()->hook->{'apply_filter'}( 'get_posttype', $_posttype );

    return $_posttype;
}
