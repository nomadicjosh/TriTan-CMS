<?php

namespace TriTan;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions as func;

/**
 * Post API: Post Class
 *
 * @package TriTan_CMS
 * @author  Joshua Parker <joshmac3@icloud.com>
 * @license GPLv3
 *         
 * @since 0.9
 */
final class Post
{

    /**
     * Post ID.
     *
     * @since 0.9
     * @var   int
     */
    public $post_id;

    /**
     * Post title.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_title = '';

    /**
     * Post slug.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_slug = '';

    /**
     * Post content/body.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_content = '';

    /**
     * Post author.
     *
     * @since 0.9.9
     * @var   int
     */
    public $post_author = 0;

    /**
     * Post type.
     *
     * @since 0.9.9
     * @var   array
     */
    public $post_type = [];

    /**
     * Post attributes.
     *
     * @since 0.9.9
     * @var   array
     */
    public $post_attributes = [];

    /**
     * Post relative url.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_relative_url = '';

    /**
     * Post featured image.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_featured_image = '';

    /**
     * Post status.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_status = 'published';

    /**
     * Post publication date.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_created = '0000-00-00 00:00:00';

    /**
     * Post modification date.
     *
     * @since 0.9.9
     * @var   string
     */
    public $post_modified = '0000-00-00 00:00:00';

    /**
     * Retrieve Post instance.
     *
     * @global app $app TriTan CMS application object.
     *        
     * @param int $post_id
     *            Post ID.
     * @return Post|false Post array, false otherwise.
     */
    public static function get_instance($post_id)
    {
        $_post_id = (int) $post_id;
        if (!$_post_id) {
            return false;
        }

        $_post = func\ttcms_cache_get($_post_id, 'post');
        if (!$_post) {
            $_post = app()->db->table(Config::get('tbl_prefix') . 'post')
                ->where('post_id', (int) $_post_id)
                ->first();
            if (!$_post) {
                return false;
            }
            func\ttcms_cache_add($_post_id, $_post, 'post');
        }

        return new Post($_post);
    }

    /**
     * Constructor.
     *
     * @param Post|object $post
     *            Post object.
     */
    public function __construct($post)
    {
        if (!is_object($post)) {
            foreach ($post as $key => $value) {
                $this->$key = $value;
            }
        } else {
            foreach (get_object_vars($post) as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Issetter.
     *
     * @since 0.9
     * @param string $key Property to check if set.
     * @return bool
     */
    public function __isset($key)
    {
        return func\metadata_exists('post', $this->post_id, Config::get('tbl_prefix') . $key);
    }

    /**
     * Getter.
     *
     * @since 0.9
     * @param string $key Key to get.
     * @return mixed
     */
    public function __get($key)
    {
        return func\get_post_meta($this->post_id, $key, true);
    }

    /**
     * Convert object to array.
     *
     * @since 0.9.9
     * @return array Object as array.
     */
    public function to_array()
    {
        return get_object_vars($this);
    }

}
