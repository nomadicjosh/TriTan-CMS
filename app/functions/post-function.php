<?php

namespace TriTan\Functions\Post;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use TriTan\Functions\Posttype;
use TriTan\Functions\Cache;
use TriTan\Functions\Meta;
use TriTan\Functions\Db;
use TriTan\Functions\Core;
use TriTan\Functions\Hook;
use TriTan\Functions\User;

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
 *              If set to true, data will return as an object, else as an array.
 *              Default: false.
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
    $modified = format_date(Core\_escape($post['post_modified']), $datetime);
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
 * Purpose of this function is for the `post_content`
 * filter.
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
    $content = Core\_escape($post['post_content']);
    /**
     * Filters the post date.
     *
     * @since 0.9.9
     *
     * @param string $content The post's content.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_content', $content, (int) $post_id);
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
    $posttype = Posttype\get_posttype(Core\_escape($post['post_type']['posttype_id']));
    $posttype_name = Core\_escape($posttype['posttype_title']);
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
    $link = Core\get_base_url() . get_post_posttype($post_id) . '/';
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
    $title = Core\_escape($post['post_title']);
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
    $slug = Core\_escape($post['post_slug']);
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

    if ((int) Core\_escape($_post['post_id']) <= 0) {
        return false;
    }

    $relative_url = Core\_escape($_post['post_relative_url']);
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

    if (empty(Core\_escape($_post['post_id']))) {
        return false;
    }

    $link = Core\get_base_url() . get_relative_url($_post);
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
 * @param string $status    Should it retrieve all statuses, published, draft, etc.?
 * @return object
 */
function the_posts($post_type = null, $limit = 0, $offset = null, $status = 'all')
{
    return Db\get_all_posts($post_type, $limit, $offset, $status);
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
    $css = '<style>' . Core\_escape($post['post_css']) . '</style>';
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
    $js = '<script>' . Core\_escape($post['post_js']) . '</script>';
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
    return Meta\get_metadata(Config::get('tbl_prefix') . 'post', $post_id, $key, $single);
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
    return Meta\get_metadata_by_mid(Config::get('tbl_prefix') . 'post', $mid);
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
    return Meta\update_metadata(Config::get('tbl_prefix') . 'post', $post_id, $meta_key, $meta_value, $prev_value);
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
    $_meta_key = Core\ttcms_unslash($meta_key);
    $_meta_value = Core\ttcms_unslash($meta_value);
    return Meta\update_metadata_by_mid(Config::get('tbl_prefix') . 'post', $mid, $_meta_key, $_meta_value);
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
    return Meta\add_metadata(Config::get('tbl_prefix') . 'post', $post_id, $meta_key, $meta_value, $unique);
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
    return Meta\delete_metadata(Config::get('tbl_prefix') . 'post', $post_id, $meta_key, $meta_value);
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
    return Meta\delete_metadata_by_mid(Config::get('tbl_prefix') . 'post', $mid);
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
    $_post_id = Core\absint($post_id);
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

/**
 * A function which retrieves a TriTan CMS post author id.
 * 
 * Purpose of this function is for the `post_author_id`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_author_id($post_id = 0)
{
    $post = get_post($post_id);
    $author_id = Core\_escape($post['post_author']);
    /**
     * Filters the post author id.
     *
     * @since 0.9.9
     *
     * @param string    $author_id The post's author id.
     * @param string    $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_author_id', $author_id, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post author.
 * 
 * Purpose of this function is for the `post_author`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int   $post_id The unique id of a post.
 * @param bool  $reverse If first name should appear first or not. Default is false.
 * @return string
 */
function get_post_author($post_id = 0, $reverse = false)
{
    $post = get_post($post_id);
    $author = User\get_name(Core\_escape($post['post_author']), $reverse);
    /**
     * Filters the post author.
     *
     * @since 0.9.9
     *
     * @param string    $author The post's author.
     * @param string    $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_author', $author, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post status.
 * 
 * Purpose of this function is for the `post_status`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int   $post_id The unique id of a post.
 * @return string
 */
function get_post_status($post_id = 0)
{
    $post = get_post($post_id);
    $status = Core\_escape($post['post_status']);
    /**
     * Filters the post status.
     *
     * @since 0.9.9
     *
     * @param string    $status The post's status.
     * @param string    $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_status', $status, $post_id);
}

/**
 * A function which retrieves TriTan CMS post date.
 * 
 * Uses `call_user_func_array()` function to return appropriate post date function.
 * Dynamic part is the variable $type, which calls the date function you need.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @param string $type Type of date to return: created, published, modified. Default: published.
 * @return string
 */
function get_post_date($post_id = 0, $type = 'published')
{
    return call_user_func_array("Tritan\Functions\Post\get_post_{$type}_date", [&$post_id]);
}

/**
 * A function which retrieves TriTan CMS post time.
 * 
 * Uses `call_user_func_array()` function to return appropriate post time function.
 * Dynamic part is the variable $type, which calls the date function you need.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @param string $type Type of date to return: created, published, modified. Default: published.
 * @return string
 */
function get_post_time($post_id = 0, $type = 'published')
{
    return call_user_func_array("Tritan\Functions\Post\get_post_{$type}_time", [&$post_id]);
}

/**
 * A function which retrieves TriTan CMS post created date.
 * 
 * Purpose of this function is for the `post_created_date`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_created_date($post_id = 0)
{
    $post = get_post($post_id);
    $date = format_date(Core\_escape($post['post_created']), app()->hook->{'get_option'}('date_format'));
    /**
     * Filters the post created date.
     *
     * @since 0.9.9
     *
     * @param string $date The post's created date.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_created_date', $date, $post_id);
}

/**
 * A function which retrieves TriTan CMS post created time.
 * 
 * Purpose of this function is for the `post_created_time`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_created_time($post_id = 0)
{
    $post = get_post($post_id);
    $time = format_date(Core\_escape($post['post_created']), app()->hook->{'get_option'}('time_format'));
    /**
     * Filters the post created time.
     *
     * @since 0.9.9
     *
     * @param string $time The post's created time.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_created_time', $time, $post_id);
}

/**
 * A function which retrieves TriTan CMS post published date.
 * 
 * Purpose of this function is for the `post_published_date`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_published_date($post_id = 0)
{
    $post = get_post($post_id);
    $date = format_date(Core\_escape($post['post_published']), app()->hook->{'get_option'}('date_format'));
    /**
     * Filters the post published date.
     *
     * @since 0.9.9
     *
     * @param string $date The post's published date.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_published_date', $date, $post_id);
}

/**
 * A function which retrieves TriTan CMS post published time.
 * 
 * Purpose of this function is for the `post_published_time`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_published_time($post_id = 0)
{
    $post = get_post($post_id);
    $time = format_date(Core\_escape($post['post_published']), app()->hook->{'get_option'}('time_format'));
    /**
     * Filters the post published time.
     *
     * @since 0.9.9
     *
     * @param string $time The post's published time.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_published_time', $time, $post_id);
}

/**
 * A function which retrieves TriTan CMS post modified date.
 * 
 * Purpose of this function is for the `post_modified_date`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_modified_date($post_id = 0)
{
    $post = get_post($post_id);
    $date = format_date(Core\_escape($post['post_modified']), app()->hook->{'get_option'}('date_format'));
    /**
     * Filters the post modified date.
     *
     * @since 0.9.9
     *
     * @param string $date The post's modified date.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_modified_date', $date, $post_id);
}

/**
 * A function which retrieves TriTan CMS post modified time.
 * 
 * Purpose of this function is for the `post_modified_time`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_modified_time($post_id = 0)
{
    $post = get_post($post_id);
    $time = format_date(Core\_escape($post['post_modified']), app()->hook->{'get_option'}('time_format'));
    /**
     * Filters the post modified time.
     *
     * @since 0.9.9
     *
     * @param string $time The post's modified time.
     * @param int  $post_id The post ID.
     */
    return app()->hook->{'apply_filter'}('post_modified_time', $time, $post_id);
}

/**
 * A function which retrieves TriTan CMS post posttype id.
 * 
 * Purpose of this function is for the `post_posttype_id`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_posttype_id($post_id = 0)
{
    $post = Db\get_post_by('post_id', $post_id);
    $posttype_id = Core\_escape($post['post_type']['posttype_id']);
    /**
     * Filters the post posttype id.
     *
     * @since 0.9.9
     *
     * @param int   $posttype_id    The post's posttype id.
     * @param int   $post_id        The post ID.
     */
    return app()->hook->{'apply_filter'}('post_posttype_id', (int) $posttype_id, (int) $post_id);
}

/**
 * A function which retrieves TriTan CMS post posttype.
 * 
 * Purpose of this function is for the `post_posttype`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_posttype($post_id = 0)
{
    $post = Db\get_post_by('post_id', $post_id);
    $posttype = Core\_escape($post['post_type']['post_posttype']);
    /**
     * Filters the post posttype.
     *
     * @since 0.9.9
     *
     * @param string    $posttype   The post's posttype.
     * @param int       $post_id    The post ID.
     */
    return app()->hook->{'apply_filter'}('post_posttype', $posttype, (int) $post_id);
}

/**
 * A function which retrieves TriTan CMS post parent id.
 * 
 * Purpose of this function is for the `post_parent_id`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_parent_id($post_id = 0)
{
    $post = Db\get_post_by('post_id', $post_id);
    $parent_id = Core\_escape($post['post_attributes']['parent']['parent_id']);
    /**
     * Filters the post parent id.
     *
     * @since 0.9.9
     *
     * @param int   $parent_id  The post's parent id.
     * @param int   $post_id    The post ID.
     */
    return app()->hook->{'apply_filter'}('post_parent_id', (int) $parent_id, (int) $post_id);
}

/**
 * A function which retrieves TriTan CMS post parent.
 * 
 * Purpose of this function is for the `post_parent`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_parent($post_id = 0)
{
    $post = Db\get_post_by('post_id', $post_id);
    $parent = Core\_escape($post['post_attributes']['parent']['post_parent']);
    /**
     * Filters the post parent.
     *
     * @since 0.9.9
     *
     * @param string    $parent     The post's parent.
     * @param int       $post_id    The post ID.
     */
    return app()->hook->{'apply_filter'}('post_parent', $parent, (int) $post_id);
}

/**
 * A function which retrieves TriTan CMS post sidebar.
 * 
 * Purpose of this function is for the `post_sidebar`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_sidebar($post_id = 0)
{
    $post = Db\get_post_by('post_id', $post_id);
    $sidebar = Core\_escape($post['post_attributes']['post_sidebar']);
    /**
     * Filters the post sidebar.
     *
     * @since 0.9.9
     *
     * @param int   $sidebar    The post's sidebar option.
     * @param int   $post_id    The post ID.
     */
    return app()->hook->{'apply_filter'}('post_sidebar', (int) $sidebar, (int) $post_id);
}

/**
 * A function which retrieves TriTan CMS post show in menu.
 * 
 * Purpose of this function is for the `post_show_in_menu`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_show_in_menu($post_id = 0)
{
    $post = Db\get_post_by('post_id', $post_id);
    $menu = Core\_escape($post['post_attributes']['post_show_in_menu']);
    /**
     * Filters the post show in menu.
     *
     * @since 0.9.9
     *
     * @param int   $menu       The post's show in menu option.
     * @param int   $post_id    The post ID.
     */
    return app()->hook->{'apply_filter'}('post_show_in_menu', (int) $menu, (int) $post_id);
}

/**
 * A function which retrieves TriTan CMS post show in search.
 * 
 * Purpose of this function is for the `post_show_in_search`
 * filter.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_show_in_search($post_id = 0)
{
    $post = Db\get_post_by('post_id', $post_id);
    $search = Core\_escape($post['post_attributes']['post_show_in_search']);
    /**
     * Filters the post show in search.
     *
     * @since 0.9.9
     *
     * @param int   $search     The post's show in search option.
     * @param int   $post_id    The post ID.
     */
    return app()->hook->{'apply_filter'}('post_show_in_search', (int) $search, (int) $post_id);
}

/**
 * Creates a unique post slug.
 * 
 * @file app/functions/post-function.php
 * 
 * @since 0.9.8
 * @param string $original_slug     Original slug of post.
 * @param string $original_title    Original title of post.
 * @param int $post_id              Unique post id.
 * @param string $post_type         Post type of post.
 * @return string Unique post slug.
 */
function ttcms_unique_post_slug($original_slug, $original_title, $post_id, $post_type)
{
    if (Db\ttcms_post_slug_exist($post_id, $original_slug, $post_type)) {
        $post_slug = Db\ttcms_slugify($original_title, 'post');
    } else {
        $post_slug = $original_slug;
    }
    /**
     * Filters the unique post slug before returned.
     * 
     * @since 0.9.9
     * @param string    $post_slug      Unique post slug.
     * @param string    $original_slug  The post's original slug.
     * @param string    $original_title The post's original title before slugified.
     * @param int       $post_id        The post's unique id.
     * @param string    $post_type      The post's post type.
     */
    return app()->hook->{'apply_filter'}('ttcms_unique_post_slug', $post_slug, $original_slug, $original_title, $post_id, $post_type);
}

/**
 * Insert or update a post.
 *
 * All of the `$postdata` array fields have filters associated with the values. The filters
 * have the prefix 'pre_' followed by the field name. An example using 'post_status' would have
 * the filter called, 'pre_post_status' that can be hooked into.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param array $postdata An array of data that is used for insert or update.
 *
 *      @type string $post_title            The post's title.
 *      @type string $post_slug             The post's slug.
 *      @type string $post_author           The post's author.
 *      @type string $post_posttype         The post's posttype.
 *      @type string $post_parent           The post's parent.
 *      @type string $post_sidebar          The post's sidebar.
 *      @type string $post_show_in_menu     Whether to show post in menu.
 *      @type string $post_show_in_search   Whether to show post in search.
 *      @type string $post_relative_url     The post's relative url.
 *      @type string $post_featured_image   THe post's featured image.
 *      @type string $post_status           THe post's status.
 *      @type string $post_published        Timestamp describing the moment when the post
 *                                          was published. Defaults to Y-m-d h:i A.
 * 
 * @param bool $exception Whether or not to throw an exception.
 * @return int|Exception|null   The newly created post's post_id or throws an exception or returns null
 *                              if the post could not be created or updated.
 */
function ttcms_insert_post($postdata, $exception = false)
{
    $user_id = User\get_current_user_id();

    $defaults = [
        'post_title' => null,
        'post_content' => null,
        'post_author' => (int) $user_id,
        'post_posttype' => 'post',
        'post_parent' => null,
        'post_sidebar' => (int) 0,
        'post_show_in_menu' => (int) 0,
        'post_show_in_search' => (int) 0,
        'post_relative_url' => null,
        'post_featured_image' => null,
        'post_status' => 'draft'
    ];

    $_postdata = Core\ttcms_parse_args($postdata, $defaults);

    // Are we updating or creating?
    if (!empty($_postdata['post_id'])) {
        $update = true;
        $post_id = (int) $_postdata['post_id'];
        $post_before = get_post((int) $post_id, true);

        if (is_null($post_before)) {
            if ($exception) {
                throw new Exception(Core\_t('Invalid post id.', 'tritan-cms'), 'invalid_post_id');
            } else {
                return null;
            }
        }

        $previous_status = get_post_status((int) $post_id);
        /**
         * Fires immediately before a post is inserted into the post document.
         *
         * @since 0.9.9
         * @param string    $previous_status    Status of the post before it is created.
         *                                      or updated.
         * @param int       $post_id            The post's post_id.
         * @param bool      $update             Whether this is an existing post or a new post.
         */
        app()->hook->{'do_action'}('post_previous_status', $previous_status, (int) $post_id, $update);
    } else {
        $update = false;
        $post_id = Db\auto_increment(Config::get('tbl_prefix') . 'post', 'post_id');

        $previous_status = 'new';
        /**
         * Fires immediately before a post is inserted into the post document.
         *
         * @since 0.9.9
         * @param string    $previous_status    Status of the post before it is created.
         *                                      or updated.
         * @param int       $post_id            The post's post_id.
         * @param bool      $update             Whether this is an existing post or a new post.
         */
        app()->hook->{'do_action'}('post_previous_status', $previous_status, (int) $post_id, $update);
    }

    if (isset($_postdata['post_title'])) {
        $post_title = $_postdata['post_title'];
    } else {
        /**
         * For an update, don't modify the post_title if it
         * wasn't supplied as an argument.
         */
        $post_title = $post_before->post_title;
    }

    $raw_post_posttype = $_postdata['post_posttype'];
    /**
     * Filters a post's posttype before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_post_posttype The post's post type.
     */
    $post_posttype = app()->hook->{'apply_filter'}('pre_post_posttype', (string) $raw_post_posttype);

    $raw_post_title = $post_title;
    /**
     * Filters a post's title before created/updated.
     *
     * @since 0.9.9
     * @param string $raw_post_title The post's title.
     */
    $post_title = app()->hook->{'apply_filter'}('pre_post_title', (string) $raw_post_title);

    if (isset($_postdata['post_slug'])) {
        /**
         * ttcms_unique_post_slug will take the original slug supplied and check
         * to make sure that it is unique. If not unique, it will make it unique
         * by adding a number at the end.
         */
        $post_slug = ttcms_unique_post_slug($_postdata['post_slug'], $post_title, $post_id, $post_posttype);
    } else {
        /**
         * For an update, don't modify the post_slug if it
         * wasn't supplied as an argument.
         */
        $post_slug = $post_before->post_slug;
    }

    $raw_post_slug = $post_slug;
    /**
     * Filters a post's slug before created/updated.
     *
     * @since 0.9.9
     * @param string $raw_post_slug The post's slug.
     */
    $post_slug = app()->hook->{'apply_filter'}('pre_post_slug', (string) $raw_post_slug);

    $raw_post_content = $_postdata['post_content'];
    /**
     * Filters a post's content before created/updated.
     *
     * @since 0.9.9
     * @param string $raw_post_slug The post's slug.
     */
    $post_content = app()->hook->{'apply_filter'}('pre_post_content', (string) $raw_post_content);

    /**
     * Check for post author
     *
     * @since 0.9.9
     * @param int $post_author Post author id.
     */
    $post_author = (int) $_postdata['post_author'];

    if ($post_author <= 0 || $post_author === null) {
        if ($exception) {
            throw new Exception(Core\_t('Post author cannot be zero or null.', 'tritan-cms'), 'empty_post_author');
        } else {
            return null;
        }
    }

    $raw_post_parent = $_postdata['post_parent'];
    /**
     * Filters a post's parent before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_post_parent The post's parent.
     */
    $post_parent = app()->hook->{'apply_filter'}('pre_post_parent', (string) $raw_post_parent);

    $raw_post_sidebar = $_postdata['post_sidebar'];
    /**
     * Filters a post's sidebar before the post is created or updated.
     *
     * @since 0.9.9
     * @param int $raw_post_sidebar The post's sidebar.
     */
    $post_sidebar = app()->hook->{'apply_filter'}('pre_post_sidebar', (int) $raw_post_sidebar);

    $raw_post_show_in_menu = $_postdata['post_show_in_menu'];
    /**
     * Filters a post's show in menu before the post is created or updated.
     *
     * @since 0.9.9
     * @param int $raw_post_show_in_menu The post's show in menu.
     */
    $post_show_in_menu = app()->hook->{'apply_filter'}('pre_post_show_in_menu', (int) $raw_post_show_in_menu);

    $raw_post_show_in_search = $_postdata['post_show_in_search'];
    /**
     * Filters a post's show in search before the post is created or updated.
     *
     * @since 0.9.9
     * @param int $raw_post_show_in_search The post's show in search.
     */
    $post_show_in_search = app()->hook->{'apply_filter'}('pre_post_show_in_search', (int) $raw_post_show_in_search);

    $raw_post_relative_url = $post_posttype . '/' . $post_slug . '/';
    /**
     * Filters a post's relative url before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_post_relative_url The post's relative url.
     */
    $post_relative_url = app()->hook->{'apply_filter'}('pre_post_relative_url', (string) $raw_post_relative_url);

    $raw_post_featured_image = Hook\ttcms_optimized_image_upload($_postdata['post_featured_image']);
    /**
     * Filters a post's featured image before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_post_featured_image The post's featured image.
     */
    $post_featured_image = app()->hook->{'apply_filter'}('pre_post_featured_image', (string) $raw_post_featured_image);

    $raw_post_status = $_postdata['post_status'];
    /**
     * Filters a post's status before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_post_status The post's status.
     */
    $post_status = app()->hook->{'apply_filter'}('pre_post_status', (string) $raw_post_status);

    /*
     * Filters whether the post is null.
     * 
     * @since 0.9.9
     * @param bool  $maybe_empty Whether the post should be considered "null".
     * @param array $_postdata   Array of post data.
     */
    $maybe_null = !$post_title && !$post_content;
    if (app()->hook->{'apply_filter'}('ttcms_insert_post_empty_content', $maybe_null, $_postdata)) {
        if ($exception) {
            throw new Exception(Core\_t('The title and content are null'), 'empty_content');
        } else {
            return null;
        }
    }

    if (!$update) {
        if (empty($_postdata['post_published']) || Core\php_like('%0000-00-00 00:00', $_postdata['post_published'])) {
            $post_published = format_date('now', 'Y-m-d h:i A');
        } else {
            $post_published = format_date($_postdata['post_published'], 'Y-m-d h:i A');
        }
    } else {
        $post_published = format_date($_postdata['post_published'], 'Y-m-d h:i A');
    }

    $compacted = compact('post_title', 'post_slug', 'post_content', 'post_author', 'post_posttype', 'post_parent', 'post_sidebar', 'post_show_in_menu', 'post_show_in_search', 'post_relative_url', 'post_featured_image', 'post_status', 'post_published');
    $data = Core\ttcms_unslash($compacted);

    /**
     * Filters post data before the record is created or updated.
     *
     * It only includes data in the post table, not any post metadata.
     *
     * @since 0.9.9
     * @param array    $data
     *     Values and keys for the user.
     *
     *      @type string $post_title            The post's title.
     *      @type string $post_slug             The post's slug.
     *      @type string $post_author           The post's author.
     *      @type string $post_posttype         The post's posttype.
     *      @type string $post_parent           The post's parent.
     *      @type string $post_sidebar          The post's sidebar.
     *      @type string $post_show_in_menu     Whether to show post in menu.
     *      @type string $post_show_in_search   Whether to show post in search.
     *      @type string $post_relative_url     The post's relative url.
     *      @type string $post_featured_image   The post's featured image.
     *      @type string $post_status           The post's status.
     *      @type string $post_published        Timestamp describing the moment when the post
     *                                          was published. Defaults to Y-m-d h:i A.
     * 
     * @param bool     $update Whether the post is being updated rather than created.
     * @param int|null $id     ID of the post to be updated, or NULL if the post is being created.
     */
    $data = app()->hook->{'apply_filter'}('ttcms_before_insert_post_data', $data, $update, $update ? (int) $post_id : null);
    $where = ['post_id' => (int) $post_id];

    if (!$update) {
        $data = array_merge($where, $data);
        /**
         * Fires immediately before a post is inserted into the post document.
         *
         * @since 0.9.9
         * @param int   $post_id Post id.
         * @param array $data    Array of post data.
         */
        app()->hook->{'do_action'}('pre_post_insert', (int) $post_id, $data);
        if (false === Db\ttcms_post_insert_document($data)) {
            if ($exception) {
                throw new Exception(Core\_t('Could not insert post into the post document.'), 'post_document_insert_error');
            } else {
                return null;
            }
        }
    } else {
        $data = array_merge($where, $data);
        /**
         * Fires immediately before an existing post is updated in the post document.
         *
         * @since 0.9.9
         * @param int   $post_id Post id.
         * @param array $data    Array of post data.
         */
        app()->hook->{'do_action'}('pre_post_update', (int) $post_id, $data);
        if (false === Db\ttcms_post_update_document($data)) {
            if ($exception) {
                throw new Exception(Core\_t('Could not update post within the post document.'), 'post_document_update_error');
            } else {
                return null;
            }
        }
    }

    if (!empty($_postdata['meta_field'])) {
        foreach ($_postdata['meta_field'] as $key => $value) {
            update_post_meta((int) $post_id, $key, $value);
        }
    }

    clean_post_cache((int) $post_id);
    $post = get_post((int) $post_id, true);

    if ($update) {
        /**
         * Action hook triggered after existing post has been updated.
         *
         * @since 0.9.9
         * @param int   $post_id    Post id.
         * @param array $post       Post object.
         */
        app()->hook->{'do_action'}('update_post', (int) $post_id, $post);
        $post_after = get_post((int) $post_id, true);
        /**
         * Action hook triggered after existing post has been updated.
         *
         * @since 0.9.9
         * @param int       $post_id      Post id.
         * @param object    $post_after   Post object following the update.
         * @param object    $post_before  Post object before the update.
         */
        app()->hook->{'do_action'}('post_updated', (int) $post_id, $post_after, $post_before);
    }

    /**
     * Action hook triggered after post has been saved. 
     * 
     * TThe dynamic portion of this hook, `$post_posttype`, is the post's
     * post type.
     * 
     * @since 0.9.9
     * @param int   $post_id    The post's id.
     * @param array $post       Post object.
     * @param bool  $update     Whether this is an existing post or a new post.
     */
    app()->hook->{'do_action'}("save_post_{$post_posttype}", (int) $post_id, $post, $update);

    /**
     * Action hook triggered after post has been saved. 
     * 
     * The dynamic portions of this hook, `$post_posttype` and `$post_status`,
     * are the post's post type and status.
     * 
     * @since 0.9.9
     * @param int   $post_id    The post's id.
     * @param array $post       Post object.
     * @param bool  $update     Whether this is an existing post or a new post.
     */
    app()->hook->{'do_action'}("save_post_{$post_posttype}_{$post_status}", (int) $post_id, $post, $update);

    /**
     * Action hook triggered after post has been saved.
     *
     * @since 0.9.9
     * @param int   $post_id    The post's id.
     * @param array $post       Post object.
     * @param bool  $update     Whether this is an existing post or a new post.
     */
    app()->hook->{'do_action'}('ttcms_after_insert_post_data', (int) $post_id, $post, $update);

    return (int) $post_id;
}

/**
 * Update a post in the post document.
 * 
 * See {@see ttcms_insert_post()} For what fields can be set in $postdata.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param array|object $postdata An array of post data or a post object.
 * @return int|Exception|null The updated post's id or throw an Exception or return null if post could not be updated.
 */
function ttcms_update_post($postdata = [], $exception = false)
{
    if (is_object($postdata)) {
        $postdata = get_object_vars($postdata);
    }

    // First, get all of the original fields.
    $post = get_post((int) $postdata['post_id']);

    if (is_null($post)) {
        if ($exception) {
            throw new Exception(Core\_t('Invalid post ID.'), 'invalid_post');
        }
        return null;
    }

    // Merge old and new fields with new fields overwriting old ones.
    $_postdata = array_merge($post, $postdata);

    return ttcms_insert_post($_postdata, $exception);
}

/**
 * Deletes a post from the post document.
 * 
 * @file app/functions/post-function.php
 * 
 * @since 0.9.9
 * @param int $post_id The id of the post to delete.
 * @return boolean
 */
function ttcms_delete_post($post_id = 0)
{
    $post = get_post($post_id, true);

    if (!$post) {
        return false;
    }

    /**
     * Action hook fires before a post is deleted.
     *
     * @since 0.9.9
     * @param int $post_id Post id.
     */
    app()->hook->{'do_action'}('before_delete_post', (int) $post_id);

    if (Db\is_post_parent($post_id)) {
        foreach (Db\is_post_parent($post_id) as $children) {
            $update_children = app()->db->table(Config::get('tbl_prefix') . 'post');
            $update_children->begin();
            try {
                $update_children->where('post_attributes.parent.parent_id', Core\_escape($children['post_attributes']['parent']['parent_id']))
                        ->where('post_attributes.parent.post_parent', Core\_escape($children['post_attributes']['parent']['post_parent']))
                        ->update([
                            'post_attributes.parent.parent_id' => null,
                            'post_attributes.parent.post_parent' => null
                ]);
                $update_children->commit();
            } catch (Exception $ex) {
                $update_children->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                return false;
            }
        }
    }

    $post_meta_ids = get_post_meta((int) $post_id);
    if ($post_meta_ids) {
        foreach ($post_meta_ids as $mid) {
            delete_post_meta_by_mid((int) $mid);
        }
    }

    /**
     * Action hook fires immediately before a post is deleted from the
     * post document.
     *
     * @since 0.9.9
     * @param int $post_id Post ID.
     */
    app()->hook->{'do_action'}('delete_post', (int) $post_id);

    $delete = app()->db->table(Config::get('tbl_prefix') . 'post');
    $delete->begin();
    try {
        $delete->where('post_id', (int) $post_id)->delete();
        $delete->commit();
    } catch (Exception $ex) {
        $delete->rollback();
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        return false;
    }

    /**
     * Action hook fires immediately after a post is deleted from the post document.
     *
     * @since 0.9.9
     * @param int $post_id Post id.
     */
    app()->hook->{'do_action'}('deleted_post', (int) $post_id);

    clean_post_cache($post);

    if (Db\is_post_parent($post_id)) {
        foreach (Db\is_post_parent($post_id) as $children) {
            clean_post_cache(Core\_escape($children['post_id']));
        }
    }

    /**
     * Action hook fires after a post is deleted.
     *
     * @since 0.9.9
     * @param int $post_id Post id.
     */
    app()->hook->{'do_action'}('after_delete_post', (int) $post_id);

    return $post;
}

/**
 * Clean post caches.
 * 
 * Uses `clean_post_cache` action.
 * 
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param array|int|object $post Post array, post_id, post object to be cleaned from the cache.
 */
function clean_post_cache($post)
{
    $_post = get_post($post);
    if (empty($_post)) {
        return;
    }

    Cache\ttcms_cache_delete((int) Core\_escape($_post['post_id']), 'post');
    Cache\ttcms_cache_delete((int) Core\_escape($_post['post_id']), 'post_meta');

    /**
     * Fires immediately after the given post's cache is cleaned.
     *
     * @since 0.9.9
     * @param int   $_post['post_id']   Post id.
     * @param array $_post              Post array.
     */
    app()->hook->{'do_action'}('clean_post_cache', (int) Core\_escape($_post['post_id']), $_post);
}
