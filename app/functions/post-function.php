<?php

namespace TriTan\Functions;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;

/**
 * TriTan CMS Post Functions
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * This function checks to see if the current TriTan CMS query has any
 * results to loop over.
 * 
 * @file app/functions/post-function.php
 * 
 * @since 0.9
 * @return int|null
 */
function has_posts()
{
    $posts = app()->db->table(Config::get('tbl_prefix') . 'post')
            ->where('post_type.post_posttype', 'post')
            ->count();
    return $posts > 0;
}

/**
 * 
 * @file app/functions/post-function.php
 * 
 * @since 0.9
 * @return object
 */
function the_post()
{
    $posts = app()->db->table(Config::get('tbl_prefix') . 'post')
            ->where('post_type.post_posttype', 'post')
            ->get();
    return $posts;
}

/**
 * Retrieves post data given a post ID or post array.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int|Post|null $post
 *            Post ID or post array.
 * @param bool $object
 *            If set to true, data will return as an object, else as an array.
 * @return array|object
 */
function get_post($post, $object = false)
{
    if ($post instanceof \TriTan\Post) {
        $_post = $post;
    } elseif (is_object($post)) {
        if (empty($post->post_id)) {
            $_post = new \TriTan\Post($post);
        } else {
            $_post = \TriTan\Post::get_instance($post->post_id);
        }
    } else {
        $_post = \TriTan\Post::get_instance($post);
    }

    if (!$_post) {
        return null;
    }

    if ($object === false) {
        $_post = (array) $_post;
    }

    /**
     * Fires after a post is retrieved.
     *
     * @since 0.9
     * @param Post $_post Post data.
     */
    $_post = app()->hook->{'apply_filter'}('get_post', $_post);

    return $_post;
}

/**
 * A function which retrieves TriTan CMS post date.
 * 
 * Purpose of this function is for the `post_date`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_date($post_id = 0)
{
    $post = get_post($post_id);
    $date = \Jenssegers\Date\Date::parse(_escape($post['post_created']))->format(app()->hook->{'get_option'}('date_format'));
    /**
     * Filters the post date.
     *
     * @since 0.9
     *
     * @param string $date The post's date.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_date', $date, $post_id);
}

/**
 * A function which retrieves TriTan CMS post time.
 * 
 * Purpose of this function is for the `post_time`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_time($post_id = 0)
{
    $post = get_post($post_id);
    $time = \Jenssegers\Date\Date::parse(_escape($post['post_created']))->format(app()->hook->{'get_option'}('time_format'));
    /**
     * Filters the post time.
     *
     * @since 0.9
     *
     * @param string $time The post's time.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_time', $time, $post_id);
}

/**
 * A function which retrieves TriTan CMS post datetime.
 * 
 * Purpose of this function is for the `post_datetime`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_datetime($post_id = 0)
{
    $datetime = concat_ws(' ', get_post_date($post_id), get_post_time($post_id));
    /**
     * Filters the post's datetime.
     *
     * @since 0.9
     *
     * @param string $datetime The post's datetime.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_datetime', $datetime, $post_id);
}

/**
 * A function which retrieves TriTan CMS post modified datetime.
 * 
 * Purpose of this function is for the `post_modified`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_modified($post_id = 0)
{
    $post = get_post($post_id);
    $datetime = app()->hook->{'get_option'}('date_format') . ' ' . app()->hook->{'get_option'}('time_format');
    $modified = \Jenssegers\Date\Date::parse(_escape($post['post_modified']))->format($datetime);
    /**
     * Filters the post date.
     *
     * @since 0.9
     *
     * @param string $modified The post's modified datetime.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_modified', $modified, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post content.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_content($post_id = 0)
{
    $post = get_post($post_id);
    return _escape($post['post_content']);
}

/**
 * A function which retrieves a TriTan CMS post posttype name.
 * 
 * Purpose of this function is for the `post_posttype_name`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_type_name($post_id = 0)
{
    $post = get_post($post_id);
    $posttype = get_posttype(_escape($post['post_type']['posttype_id']));
    $posttype_name = _escape($posttype['posttype_title']);
    /**
     * Filters the post posttype name.
     *
     * @since 0.9
     *
     * @param string $posttype_name The post's posttype name.
     * @param string  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_posttype_name', $posttype_name, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post posttype slug.
 * 
 * Purpose of this function is for the `post_posttype_slug`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_posttype_slug($post_id = 0)
{
    $post = get_post($post_id);
    $posttype = get_posttype(_escape($post['post_type']['posttype_id']));
    $posttype_slug = _escape($posttype['posttype_slug']);
    /**
     * Filters the post posttype slug.
     *
     * @since 0.9
     *
     * @param string $posttype_slug The post's posttype slug.
     * @param string  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_posttype_slug', $posttype_slug, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post posttype link.
 * 
 * Purpose of this function is for the `post_posttype_link`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param into $post_id The unique id of a post.
 * @return string
 */
function get_post_posttype_link($post_id = 0)
{
    $link = get_base_url() . get_post_posttype_slug($post_id) . '/';
    /**
     * Filters the post posttype link.
     *
     * @since 0.9
     *
     * @param string $link The post's posttype link.
     * @param string  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_posttype_link', $link, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post title.
 * 
 * Purpose of this function is for the `post_title`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_title($post_id = 0)
{
    $post = get_post($post_id);
    $title = _escape($post['post_title']);
    /**
     * Filters the post title.
     *
     * @since 0.9
     *
     * @param string $title The post's title.
     * @param string  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_title', $title, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post slug.
 * 
 * Purpose of this function is for the `post_slug`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_slug($post_id = 0)
{
    $post = get_post($post_id);
    $slug = _escape($post['post_slug']);
    /**
     * Filters the post's slug.
     *
     * @since 0.9
     *
     * @param string $slug The post's slug.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_slug', $slug, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post's relative url.
 * 
 * Purpose of this function is for the `post_relative_url`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.5
 * @param int|array $post Post id or array.
 * @return string
 */
function get_relative_url($post = 0)
{
    if (is_array($post)) {
        $_post = $post;
    } else {
        $_post = get_post($post);
    }

    if ((int) _escape($_post['post_id']) <= 0) {
        return false;
    }

    $relative_url = _escape($_post['post_relative_url']);
    /**
     * Filters the post's relative_url.
     *
     * @since 0.9.5
     *
     * @param string $relative_url The post's relative url.
     * @param string|array  $_post The post id or array.
     */
    return app()->hook->{'apply_filter'}('post_relative_url', $relative_url, $_post);
}

/**
 * A function which retrieves a TriTan CMS post's permalink.
 * 
 * Purpose of this function is for the `permalink`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int|array $post Post id or array.
 * @return string
 */
function get_permalink($post = 0)
{
    if (is_array($post)) {
        $_post = $post;
    } else {
        $_post = get_post($post);
    }

    if (empty(_escape($_post['post_id']))) {
        return false;
    }

    $link = get_base_url() . get_relative_url($_post);
    /**
     * Filters the post's link.
     *
     * @since 0.9
     *
     * @param string $link The post's link.
     * @param string|array  $_post The post id or array.
     */
    return app()->hook->{'apply_filter'}('permalink', $link, $_post);
}

/**
 * The TriTan CMS post filter.
 * 
 * Uses `the_content` filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function the_content($post_id = 0)
{
    $post_content = get_post_content($post_id);
    $post_content = app()->hook->{'apply_filter'}('the_content', $post_content);
    $post_content = str_replace(']]>', ']]&gt;', $post_content);
    return $post_content;
}

/**
 * Wrapper function for get_all_posts.
 * 
 * @file app/functions/post-function.php
 * 
 * @since 0.9
 * @param string $post_type The post type.
 * @param int $limit        Number of posts to show.
 * @param null|int $offset  The offset of the first row to be returned.
 * @return object
 */
function the_posts($post_type = null, $limit = 0, $offset = null)
{
    return get_all_posts($post_type, $limit, $offset);
}

/**
 * A function which retrieves TriTan CMS post css.
 * 
 * Purpose of this function is for the `post_css`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function post_css($post_id = 0)
{
    $post = get_post($post_id);
    $css = '<style>' . _escape($post['post_css']) . '</style>';
    /**
     * Filters the post css code.
     *
     * @since 0.9
     *
     * @param string $css The post's css code.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_css', $css, $post_id);
}

/**
 * A function which retrieves TriTan CMS post javascript.
 * 
 * Purpose of this function is for the `post_js`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function post_js($post_id = 0)
{
    $post = get_post($post_id);
    $js = '<script>' . _escape($post['post_js']) . '</script>';
    /**
     * Filters the post javascript code.
     *
     * @since 0.9
     *
     * @param string $js The post's javascript code.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_js', $js, $post_id);
}

/**
 * Adds label to post's status.
 * 
 * @file app/functions/post-function.php
 * 
 * @since 0.9
 * @param string $status
 * @return string
 */
function ttcms_post_status_label($status)
{
    $label = [
        'published' => 'label-success',
        'draft' => 'label-warning',
        'archived' => 'label-danger'
    ];

    return $label[$status];
}

/**
 * Retrieve post meta field for a post.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int    $post_id Post ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns
 *                        data for all keys. Default empty.
 * @param bool   $single  Optional. Whether to return a single value. Default false.
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function get_post_meta($post_id, $key = '', $single = false)
{
    return get_metadata(Config::get('tbl_prefix') . 'post', $post_id, $key, $single);
}

/**
 * Get post meta data by meta ID.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $mid
 * @return array|bool
 */
function get_post_meta_by_mid($mid)
{
    return get_metadata_by_mid(Config::get('tbl_prefix') . 'post', $mid);
}

/**
 * Update post meta field based on post ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and post ID.
 *
 * If the meta field for the post does not exist, it will be added.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int    $post_id    Post ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '')
{
    return update_metadata(Config::get('tbl_prefix') . 'post', $post_id, $meta_key, $meta_value, $prev_value);
}

/**
 * Update post meta data by meta ID.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $mid
 * @param string $meta_key
 * @param string $meta_value
 * @return bool
 */
function update_post_meta_by_mid($mid, $meta_key, $meta_value)
{
    $_meta_key = ttcms_unslash($meta_key);
    $_meta_value = ttcms_unslash($meta_value);
    return update_metadata_by_mid(Config::get('tbl_prefix') . 'post', $mid, $_meta_key, $_meta_value);
}

/**
 * Add meta data field to a post.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int    $post_id    Post ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default false.
 * @return int|false Meta ID on success, false on failure.
 */
function add_post_meta($post_id, $meta_key, $meta_value, $unique = false)
{
    return add_metadata(Config::get('tbl_prefix') . 'post', $post_id, $meta_key, $meta_value, $unique);
}

/**
 * Remove metadata matching criteria from a post.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int    $post_id    Post ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 * @return bool True on success, false on failure.
 */
function delete_post_meta($post_id, $meta_key, $meta_value = '')
{
    return delete_metadata(Config::get('tbl_prefix') . 'post', $post_id, $meta_key, $meta_value);
}

/**
 * Delete post meta data by meta ID.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $mid
 * @return bool
 */
function delete_post_meta_by_mid($mid)
{
    return delete_metadata_by_mid(Config::get('tbl_prefix') . 'post', $mid);
}

/**
 * Retrieve post meta fields, based on post ID.
 *
 * The post meta fields are retrieved from the cache where possible,
 * so the function is optimized to be called more than once.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The post's id.
 * @return array Post meta for the given post.
 */
function get_post_custom($post_id = 0)
{
    $_post_id = absint($post_id);
    return get_post_meta($_post_id);
}

/**
 * Retrieve meta field names for a post.
 *
 * If there are no meta fields, then nothing (null) will be returned.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The post's id.
 * @return array|void Array of the keys, if retrieved.
 */
function get_post_custom_keys($post_id = 0)
{
    $custom = get_post_custom($post_id);
    if (!is_array($custom)) {
        return;
    }
    if ($keys = array_keys($custom)) {
        return $keys;
    }
}

/**
 * Retrieve values for a custom post field.
 *
 * The parameters must not be considered optional. All of the post meta fields
 * will be retrieved and only the meta field key values returned.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param string $key     Optional. Meta field key. Default empty.
 * @param int    $post_id The post's id.
 * @return array|null Meta field values.
 */
function get_post_custom_values($key = '', $post_id = 0)
{
    if (!$key) {
        return null;
    }
    $custom = get_post_custom($post_id);
    return isset($custom[$key]) ? $custom[$key] : null;
}

/**
 * Displays the permalink for the current post.
 * 
 * Uses `the_permalink` filter.
 * 
 * @file app/functions/post-function.php
 * 
 * @since 0.9
 * @param int|array $post Post ID or post array.
 */
function the_permalink($post = 0)
{
    /**
     * Filters the display of the permalink for the current post.
     *
     * @since 0.9
     * @param string            $permalink The permalink for the current post.
     * @param int|array $post   Post ID, Post array, or 0. Default 0.
     */
    echo app()->hook->{'apply_filter'}('the_permalink', get_permalink($post), $post);
}
