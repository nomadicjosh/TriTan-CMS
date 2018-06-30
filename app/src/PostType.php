<?php

namespace TriTan;

if (!defined('BASE_PATH')) {
    exit('No direct script access allowed');
}
use TriTan\Config;
use TriTan\Functions\Cache;
use TriTan\Functions\Post;

/**
 * Post Type API: Post Type Class
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
final class PostType
{
    
    /**
     * Posttype ID.
     *
     * @since 0.9.9
     * @var int
     */
    public $posttype_id;

    /**
     * Posttype title.
     *
     * @since 0.9.9
     * @var string
     */
    public $posttype_title = '';

    /**
     * Posttype slug.
     *
     * @since 0.9.9
     * @var string
     */
    public $posttype_slug = '';

    /**
     * Posttype description.
     *
     * @since 0.9.9
     * @var string
     */
    public $posttype_description = '';

    /**
     * Retrieve Post Type instance.
     *
     * @global app $app TriTan CMS application object.
     *
     * @param int $id
     *            Post Type ID.
     * @return Post Type|false Post Type array, false otherwise.
     */
    public static function get_instance($id)
    {
        $_id = (int) $id;
        if (!$_id) {
            return false;
        }

        $posttype = Cache\ttcms_cache_get($_id, 'posttype');
        if (!$posttype) {
            $posttype = app()->db->table(Config::get('tbl_prefix') . 'posttype')
                    ->where('posttype_id', (int) $_id)
                    ->first();
            if (!$posttype) {
                return false;
            }
            Cache\ttcms_cache_add($_id, $posttype, 'posttype');
        }

        return $posttype;
    }

    /**
     * Constructor.
     *
     * @param Post Type|object $posttype
     *            Post Type object.
     */
    public function __construct($posttype)
    {
        if (!is_object($posttype)) {
            foreach ($posttype as $key => $value) {
                $this->$key = $value;
            }
        } else {
            foreach (get_object_vars($posttype) as $key => $value) {
                $this->$key = $value;
            }
        }
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
