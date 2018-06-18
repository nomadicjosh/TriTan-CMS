<?php

namespace TriTan\Functions;

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
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Auto increments the table's primary key.
 * 
 * @file app/functions/db-function.php
 * 
 * @since 0.9
 * @param string $table Table in the document.
 * @param int $pk Primary key field name.
 * @return int
 */
function auto_increment($table, $pk)
{
    $sql = app()->db->table($table)
            ->sortBy($pk, 'desc')
            ->first();
    if (@count($sql) <= 0 || null == $sql || false == $sql) {
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
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
 * 
 * @file app/functions/db-function.php
 * 
 * @since 0.9
 * @param string $title Text to be slugified.
 * @param string $table Table the text is saved to (i.e. post, posttype)
 * @return string
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
            ->where("$field", 'match', "/$slug(-[0-9]+)?$/");
    if ($results->count() > 0) {
        foreach ($results->get() as $item) {
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
 * @access private
 * @param string $slug  Post slug.
 * @param int $post_id  Post id.
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
 * @param int $slug Post type slug.
 * @return int
 */
function number_posts_per_type($slug)
{
    $count = app()->db->table(Config::get('tbl_prefix') . 'post')
            ->where('post_type.post_posttype', $slug)
            ->count();
    return $count;
}

/**
 * Retrieve all published posts or all published posts by post type.
 * 
 * @file app/functions/db-function.php
 * 
 * @since 0.9
 * @param string $post_type Post type.
 * @param int $limit        Number of posts to show.
 * @param null|int $offset  The offset of the first row to be returned.
 * @return array
 */
function get_all_posts($post_type = null, $limit = 0, $offset = null, $status = 'all')
{
    if ($post_type != null) {
        $posts = app()->db->table(Config::get('tbl_prefix') . 'post')
                ->where('post_type.post_posttype', $post_type);

        if ($status !== 'all') {
            $posts->where('post_status', $status);
        }

        if ($limit > 0 && $offset != null) {
            $posts->take($limit, $offset);
        } elseif ($limit > 0 && $offset == null) {
            $posts->take($limit);
        } elseif ($limit <= 0 && $offset != null) {
            $posts->skip($offset);
        }
        return $posts->get();
    } else {
        $posts = app()->db->table(Config::get('tbl_prefix') . 'post');

        if ($status !== 'all') {
            $posts->where('post_status', $status);
        }

        if ($limit > 0 && $offset != null) {
            $posts->take($limit, $offset);
        } elseif ($limit > 0 && $offset == null) {
            $posts->take($limit);
        } elseif ($limit <= 0 && $offset != null) {
            $posts->skip($offset);
        }

        if ($post_type === null && $limit <= 0 && $offset === null && (empty($status) || $status === 'all')) {
            return $posts->all();
        } elseif($limit > 0) {
            return $posts->all();
        } else {
            return $posts->get();
        }
        return false;
    }
}

/**
 * Returns a list of internal links for TinyMCE.
 * 
 * @file app/functions/db-function.php
 * 
 * @since 0.9
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
 * @file app/functions/db-function.php
 * 
 * @since 0.9
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

/**
 * Generates the encryption table if it does not exist.
 * 
 * @file app/functions/db-function.php
 * 
 * @since 0.9
 * @access private
 * @return bool
 */
function generate_php_encryption()
{
    $encrypt = app()->db->table('php_encryption');

    if ($encrypt->count() > 0) {
        return false;
    }

    $encrypt->begin();
    try {
        $key = \Defuse\Crypto\Key::createNewRandomKey();
        $encrypt->insert([
            'encryption_id' => (int) 1,
            'key' => $key->saveToAsciiSafeString(),
            'created_at' => (string) \Jenssegers\Date\Date::now()
        ]);
        $encrypt->commit();
    } catch (Exception $ex) {
        $encrypt->rollback();
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()), ['Db Functions' => 'php_encryption']);
    }
}

/**
 * Checks if a key exists in the option table.
 * 
 * @file app/functions/db-function.php
 * 
 * @since 0.9.4
 * @param string $option_key Key to check against.
 * @return bool
 */
function is_option_exist($option_key)
{
    $key = app()->db->table(Config::get('tbl_prefix') . 'option')
            ->where('option_key', '=', $option_key)
            ->first();

    if (_escape((int) $key['option_id']) <= 0) {
        return false;
    }

    return true;
}

/**
 * Update post's relative url if posttype slug has been updated.
 * 
 * @file app/functions/db-function.php
 * 
 * @since 0.9.6
 * @access private
 * @param int $id           Unique Posttype id.
 * @param string $old_slug  Old posttype slug.
 * @param string $new_slug  New posttype slug.
 */
function update_post_relative_url_posttype($id, $old_slug, $new_slug)
{
    $post = app()->db->table(Config::get('tbl_prefix') . 'post');
    $post->begin();
    try {
        $post->where('post_type.posttype_id', (int) $id)
                ->update([
                    'post_type.post_posttype' => (string) $new_slug
        ]);
        $post->commit();
    } catch (Exception $ex) {
        $post->rollback();
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        _ttcms_flash()->{'error'}(_ttcms_flash()->notice(409));
    }

    $collection = app()->db->table(Config::get('tbl_prefix') . 'post');
    $query = $collection->where('post_type.posttype_id', (int) $id)->map(function($data) use($old_slug, $new_slug) {
        $data['post_relative_url'] = str_replace((string) $old_slug, (string) $new_slug, (string) $data['post_relative_url']);
        return $data;
    });
    $query->save();
}

/**
 * Updates the post.
 * 
 * To be only used by `ttcms_insert_post`.
 * 
 * @access private
 * @since 0.9.9
 * @param array $data   Array of post data.
 */
function ttcms_post_insert_document($data)
{
    $posttype = get_posttype_by('posttype_slug', $data['post_posttype']);
    $post = app()->db->table(Config::get('tbl_prefix') . 'post');
    $post->begin();
    try {
        $post->insert([
            'post_id' => (int) $data['post_id'],
            'post_title' => if_null($data['post_title']),
            'post_slug' => if_null($data['post_slug']),
            'post_content' => if_null($data['post_content']),
            'post_author' => if_null($data['post_author']),
            'post_type' => [
                'posttype_id' => if_null(_escape($posttype['posttype_id'])),
                'post_posttype' => if_null($data['post_posttype'])
            ],
            'post_attributes' => [
                'parent' => [
                    'parent_id' => if_null(get_post_id($data['post_parent'])),
                    'post_parent' => if_null($data['post_parent'])
                ],
                'post_sidebar' => if_null($data['post_sidebar']),
                'post_show_in_menu' => if_null($data['post_show_in_menu']),
                'post_show_in_search' => if_null($data['post_show_in_search'])
            ],
            'post_relative_url' => if_null($data['post_relative_url']),
            'post_featured_image' => if_null($data['post_featured_image']),
            'post_status' => if_null($data['post_status']),
            'post_created' => (string) \Jenssegers\Date\Date::now(),
            'post_published' => if_null($data['post_published'])
        ]);
        $post->commit();
    } catch (Exception $ex) {
        $post->rollback();
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        return false;
    }
}

/**
 * Updates the post.
 * 
 * To be only used by `ttcms_insert_post`.
 * 
 * @access private
 * @since 0.9.9
 * @param array $data   Array of post data.
 */
function ttcms_post_update_document($data)
{
    $posttype = get_posttype_by('posttype_slug', $data['post_posttype']);
    $post = app()->db->table(Config::get('tbl_prefix') . 'post');
    $post->begin();
    try {
        $post->where('post_id', (int) $data['post_id'])->update([
            'post_title' => if_null($data['post_title']),
            'post_slug' => if_null($data['post_slug']),
            'post_content' => if_null($data['post_content']),
            'post_author' => if_null($data['post_author']),
            'post_type' => [
                'posttype_id' => if_null(_escape($posttype['posttype_id'])),
                'post_posttype' => if_null($data['post_posttype'])
            ],
            'post_attributes' => [
                'parent' => [
                    'parent_id' => if_null(get_post_id($data['post_parent'])),
                    'post_parent' => if_null($data['post_parent'])
                ],
                'post_sidebar' => if_null($data['post_sidebar']),
                'post_show_in_menu' => if_null($data['post_show_in_menu']),
                'post_show_in_search' => if_null($data['post_show_in_search'])
            ],
            'post_relative_url' => if_null($data['post_relative_url']),
            'post_featured_image' => if_null($data['post_featured_image']),
            'post_status' => if_null($data['post_status']),
            'post_published' => if_null($data['post_published']),
            'post_modified' => (string) format_date()
        ]);
        $post->commit();

        $parent = app()->db->table(Config::get('tbl_prefix') . 'post');
        $parent->where('post_attributes.parent.parent_id', (int) $data['post_id'])
                ->update([
                    'post_attributes.parent.post_parent' => $data['post_slug']
        ]);
    } catch (Exception $ex) {
        $post->rollback();
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        return false;
    }
}

/**
 * Checks if a slug exist among records from a document.
 * 
 * Currently supports the post and posttype document.
 * 
 * @since 0.9.9
 * @param int       $post_id    Post id to check against.
 * @param string    $slug       Slug to search for.
 * @param string    $post_type  The post type to filter.
 * @return boolean
 */
function ttcms_post_slug_exist($post_id, $slug, $post_type)
{
    $exist = app()->db->table(Config::get('tbl_prefix') . 'post')
            ->where('post_slug', $slug)
            ->where('post_id', 'not in', $post_id)
            ->where('post_type.post_posttype', $post_type)
            ->count();
    if ($exist > 0) {
        return true;
    }
    return false;
}

/**
 * Checks if a post has any children.
 * 
 * @since 0.9.9
 * @param int $post_id Post id to check.
 * @return bool|array False if not, array of children if true.
 */
function is_post_parent($post_id)
{
    $children = app()->db->table(Config::get('tbl_prefix') . 'post')
            ->where('post_attributes.parent.parent_id', $post_id);
    if ($children->count() <= 0) {
        return false;
    }
    return $children->get();
}
