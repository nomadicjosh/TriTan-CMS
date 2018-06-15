<?php

namespace TriTan\Functions;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * TriTan CMS Post Type Functions
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Retrieves post type data given a post type ID or post type array.
 *
 * @file app/functions/posttype-function.php
 * 
 * @since 0.9
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
     * @since 0.9
     * @param Posttype $_posttype Posttype data.
     */
    $_posttype = app()->hook->{'apply_filter'}('get_posttype', $_posttype);

    return $_posttype;
}

/**
 * A function which retrieves a TriTan CMS post type title.
 * 
 * Purpose of this function is for the `posttype_title`
 * filter.
 * 
 * @file app/functions/posttype-function.php
 *
 * @since 0.9.9
 * @param int $posttype_id The unique id of a posttype.
 * @return string
 */
function get_posttype_title($posttype_id = 0)
{
    $posttype = get_posttype($posttype_id);
    $title = _escape($posttype['posttype_title']);
    /**
     * Filters the posttype title.
     *
     * @since 0.9.9
     *
     * @param string    $title The posttype's title.
     * @param string    $posttype_id The posttype ID.
     */
    return app()->hook->{'apply_filter'}('posttype_title', $title, $posttype_id);
}

/**
 * A function which retrieves a TriTan CMS posttype slug.
 * 
 * Purpose of this function is for the `posttype_slug`
 * filter.
 * 
 * @file app/functions/posttype-function.php
 *
 * @since 0.9.9
 * @param int $posttype_id The unique id of a posttype.
 * @return string
 */
function get_posttype_slug($posttype_id = 0)
{
    $posttype = get_posttype($posttype_id);
    $slug = _escape($posttype['posttype_slug']);
    /**
     * Filters the posttype's slug.
     *
     * @since 0.9.9
     *
     * @param string    $slug The posttype's slug.
     * @param int       $posttype_id The posttype ID.
     */
    return app()->hook->{'apply_filter'}('posttype_slug', $slug, $posttype_id);
}

/**
 * A function which retrieves a TriTan CMS posttype's permalink.
 * 
 * Purpose of this function is for the `posttype_permalink`
 * filter.
 * 
 * @file app/functions/posttype-function.php
 *
 * @since 0.9.9
 * @param int $posttype_id Posttype id.
 * @return string
 */
function get_posttype_permalink($posttype_id = 0)
{
    $link = get_base_url() . get_posttype_slug($posttype_id) . '/';
    /**
     * Filters the posttype's link.
     *
     * @since 0.9.9
     *
     * @param string    $link The posttype's permalink.
     * @param int       $posttype_id The posttype id.
     */
    return app()->hook->{'apply_filter'}('posttype_permalink', $link, $posttype_id);
}
