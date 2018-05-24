<?php namespace TriTan;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;

/**
 * Post API: Post Class
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
final class Post
{

    /**
     * Post ID.
     *
     * @since 0.9
     * @var int
     */
    public $post_id;

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
        $post_id = (int) $post_id;
        if (!$post_id) {
            return false;
        }

        $_post = ttcms_cache_get($post_id, 'post');
        if (!$_post) {
            $_post = app()->db->table(Config::get('tbl_prefix') . 'post')
                ->where('post_id', (int) $post_id)
                ->first();
            if (!$_post) {
                return false;
            }
            ttcms_cache_add($post_id, $_post, 'post');
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
        return metadata_exists('post', $this->post_id, $key);
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
        return get_post_meta($this->post_id, $key, true);
    }
}
