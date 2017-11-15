<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use Cocur\Slugify\Slugify;
use Cascade\Cascade;

/**
 * TriTan CMS Database Related Functions
 *
 * For the most part, these are general purpose functions
 * that use the database to retrieve information.
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Auto increments the table's primary key.
 * 
 * @since 1.0.0
 * @param string $table Table in the document.
 * @param int $pk Primary key field name.
 * @return int
 */
function auto_increment($table, $pk)
{
    $sql = app()->db->table($table)
        ->sortBy($pk, 'desc')
        ->first();
    if (count($sql) <= 0 || null == $sql || false == $sql) {
        $auto_increment = 1;
    } else {
        $auto_increment = $sql[$pk] + 1;
    }
    return $auto_increment;
}

/**
 * Used by ttcms_check_password in order to rehash
 * an old password that was hashed using MD5 function.
 *
 * @since 1.0.0
 * @param string $password
 *            User password.
 * @param int $user_id
 *            User ID.
 * @return mixed
 */
function ttcms_set_password($password, $user_id)
{
    $hash = ttcms_hash_password($password);
    $user = app()->db->table("user");
    $user->begin();
    try {
        $user->where('user_id', $user_id)->update([
            'user_pass' => $hash
        ]);
        $user->commit();
    } catch (Exception $ex) {
        $user->rollback();
        Cascade::getLogger('error')->error($ex->getMessage());
        _ttcms_flash()->error(_t('Password was not updated.', 'tritan-cms'));
    }
}

/**
 * Retrieve post type by a given field from the post type table.
 *
 * @since 1.0.0
 * @param string $field The field to retrieve the post type with.
 * @param string $value A value for $field (_id, post_id, posttype_slug).
 */
function get_posttype_by($field, $value)
{
    $posttype = app()->db->table(Config::get('tbl_prefix') . 'posttype')
        ->where($field, $value)
        ->first();

    return $posttype;
}

/**
 * Retrieve post by a given field from the post table.
 *
 * @since 1.0.0
 * @param string $field The field to retrieve the post with.
 * @param string|int|string $value A value for $field (_id, post_id, post_slug).
 */
function get_post_by($field, $value)
{
    $post = app()->db->table(Config::get('tbl_prefix') . 'post')
        ->where($field, $value)
        ->first();

    return $post;
}

/**
 * A function which retrieves a TriTan CMS post id.
 *
 * @since 1.0.0
 * @param string $post_slug The unique slug of a post.
 * @return integer
 */
function get_post_id($post_slug = null)
{
    $post = app()->db->table(Config::get('tbl_prefix') . 'post')
        ->where('post_slug', $post_slug)
        ->first();

    return _escape($post['post_id']);
}

/**
 * Creates unique slug based on title
 * @param type $title
 * @param type $table
 * @return type
 */
function ttcms_slugify($title, $table = null)
{
    /**
     * Instantiate the slugify class.
     */
    $slugify = new Slugify();
    $slug = $slugify->slugify($title);
    /**
     * Slug field to filter by based on table
     * being called.
     */
    $field = $table . '_slug';

    $titles = [];
    /**
     * Query post/page table.
     */
    $results = app()->db->table(Config::get('tbl_prefix') . $table)
        ->where("$field", 'match', "/$slug(-[0-9]+)?$/")
        ->get();
    if (count($results) > 0) {
        foreach ($results as $item) {
            $titles[] = $item["$field"];
        }
    }

    $total = count($titles);
    $last = end($titles);

    /**
     * No equal results, return $slug
     */
    if ($total == 0)
        return $slug;

    /**
     * If we have only one result, we look if it has a number at the end
     */
    elseif ($total == 1) {
        /**
         * Take the only value of the array, because there is only 1
         */
        $exists = $titles[0];

        /**
         * Kill the slug and see what happens
         */
        $exists = str_replace($slug, "", $exists);

        /**
         * If there is no light about, there was no number at the end.
         * We added it now
         */
        if ("" == trim($exists))
            return $slug . "-1";

        /**
         * If not..........
         */
        else {
            /**
             * Obtain the number because of REGEX it will be there... ;-)
             */
            $number = str_replace("-", "", $exists);

            /**
             * Number plus one.
             */
            $number++;

            return $slug . "-" . $number;
        }
    }

    /**
     * If there is more than one result, we need the last one
     */ else {
        /**
         * Last value
         */
        $exists = $last;

        /**
         * Delete the actual slug and see what happens
         */
        $exists = str_replace($slug, "", $exists);

        /**
         * Obtain the number, easy.
         */
        $number = str_replace("-", "", $exists);

        /**
         * Increment number +1
         */
        $number++;

        return $slug . "-" . $number;
    }
}

/**
 * Function used to dynamically generate post screens
 * based on post type.
 * 
 * @since 1.0.0
 * @access private
 * @return array
 */
function get_all_post_types()
{
    $post_types = app()->db->table(Config::get('tbl_prefix') . 'posttype')->all();
    return $post_types;
}

/**
 * Retrieves all posts
 * 
 * @since 1.0.0
 * @access private
 * @param string Post slug.
 * @param int Post id.
 * @return array
 */
function get_post_dropdown_list($slug = null, $post_id = 0)
{
    $posts = app()->db->table(Config::get('tbl_prefix') . 'post')
        ->where('post_status', 'published')
        ->where('post_id', 'not in', $post_id)
        ->get();
    foreach ($posts as $post) {
        echo '<option value="' . _escape($post['post_slug']) . '"' . selected($slug, _escape($post['post_slug']), false) . '>' . _escape($post['post_title']) . '</option>';
    }
}

/**
 * Returns the number of posts within a given post type.
 * 
 * @since 1.0.0
 * @param int $slug Post type slug.
 * @return int
 */
function number_posts_per_type($slug)
{
    $count = app()->db->table(Config::get('tbl_prefix') . 'post')
        ->where('post_type.post_posttype', $slug)
        ->get();
    return count($count);
}

/**
 * Retrieve all published posts or all published posts by post type.
 * 
 * @since 1.0.0
 * @access private
 * @param string $post_type Post type.
 * @return array
 */
function get_all_posts($post_type = null)
{
    if ($post_type != null) {
        $posts = app()->db->table(Config::get('tbl_prefix') . 'post')
            ->where('post_type.post_posttype', $post_type)
            ->where('post_status', 'published')
            ->get();
        return $posts;
    } else {
        $posts = app()->db->table(Config::get('tbl_prefix') . 'post')
            ->where('post_status', 'published')
            ->get();
        return $posts;
    }
}

/**
 * Returns a list of internal links for TinyMCE.
 * 
 * @since 1.0.0
 * @return array
 */
function tinymce_link_list()
{
    $links = app()->db->table(Config::get('tbl_prefix') . 'post')
        ->where('post_status', 'published')
        ->get(['post_title', 'post_relative_url']);
    return $links;
}

/**
 * Update the metadata cache for the specified arrays.
 *
 * @since 1.0.0
 * @param string    $meta_type  Type of array metadata is for (e.g., post or user)
 * @param int|array $array_ids Array or comma delimited list of array IDs to update cache for
 * @return array|false Metadata cache for the specified arrays, or false on failure.
 */
function update_meta_cache($meta_type, $array_ids)
{
    if (!$meta_type || !$array_ids) {
        return false;
    }

    $table = _get_meta_table($meta_type);
    if (!$table) {
        return false;
    }

    $column = $meta_type . '_id';

    if (!is_array($array_ids)) {
        $array_ids = preg_replace('|[^0-9,]|', '', $array_ids);
        $array_ids = explode(',', $array_ids);
    }

    $array_ids = array_map('intval', $array_ids);

    $cache_key = $meta_type . '_meta';
    $ids = [];
    $cache = [];
    foreach ($array_ids as $id) {
        $cached_array = ttcms_cache_get($id, $cache_key);
        if (false === $cached_array) {
            $ids[] = $id;
        } else {
            $cache[$id] = $cached_array;
        }
    }

    if (empty($ids)) {
        return $cache;
    }

    // Get meta info
    $id_list = join(',', $ids);
    $meta_list = app()->db->table($table)
        ->where($column, 'in', $id_list)
        ->sortBy('meta_id')
        ->get();

    if (!empty($meta_list)) {
        foreach ($meta_list as $metarow) {
            $mpid = intval($metarow[$column]);
            $mkey = $metarow['meta_key'];
            $mval = $metarow['meta_value'];
            // Force subkeys to be array type:
            if (!isset($cache[$mpid]) || !is_array($cache[$mpid])) {
                $cache[$mpid] = [];
            }
            if (!isset($cache[$mpid][$mkey]) || !is_array($cache[$mpid][$mkey])) {
                $cache[$mpid][$mkey] = [];
            }
            // Add a value to the current pid/key:
            $cache[$mpid][$mkey][] = $mval;
        }
    }
    foreach ($ids as $id) {
        if (!isset($cache[$id])) {
            $cache[$id] = [];
        }
        ttcms_cache_add($id, $cache[$id], $cache_key);
    }
    return $cache;
}
