<?php namespace TriTan;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

use TriTan\Config;

/**
 * Post Type API: Post Type Class
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
final class PostType
{

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
        if (!$id) {
            return false;
        }
        
        $posttype = app()->db->table(Config::get('tbl_prefix') . 'posttype')
            ->where('posttype_id', (int) $id)
            ->first();
        if (!$posttype) {
            return false;
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
        foreach (get_object_vars($posttype) as $key => $value) {
            $this->$key = $value;
        }
    }
}
