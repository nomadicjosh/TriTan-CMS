<?php
use TriTan\Container as c;
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
 * Retrieve post type by a given field from the post type table.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9
 * @param string     $field The field to retrieve the post type with.
 * @param int|string $value A value for $field (_id, post_id, posttype_slug).
 */
function get_posttype_by(string $field, $value)
{
    $db = new \TriTan\Database();
    $posttype = $db->table(c::getInstance()->get('tbl_prefix') . 'posttype')
            ->where($field, $value)
            ->first();
    return $posttype;
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
function get_post_id($post_slug = null) : int
{
    $db = new \TriTan\Database();
    $post = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
            ->where('post_slug', $post_slug)
            ->first();

    return (int) esc_html($post['post_id']);
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
function ttcms_slugify(string $title, $table = null)
{
    $db = new \TriTan\Database();
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
     * Query post/posttype/site document.
     */
    if ($table === 'site') {
        $table = $table;
    } else {
        $table = c::getInstance()->get('tbl_prefix') . $table;
    }

    $results = $db->table($table)
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
    if ($total == 0) {
        return $slug;
    } elseif ($total == 1) { // If we have only one result, we look if it has a number at the end.
        /**
         * Take the only value of the array, because there is only 1.
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
        if ("" == trim($exists)) {
            return $slug . "-1";
        } else { // If not..........
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
    } else { // If there is more than one result, we need the last one.
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
function get_all_posts($post_type = null, int $limit = 0, $offset = null, $status = 'all')
{
    $db = new \TriTan\Database();
    if ($post_type != null) {
        $posts = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
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
        $posts = $db->table(c::getInstance()->get('tbl_prefix') . 'post');

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
        } elseif ($limit > 0) {
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
    $db = new \TriTan\Database();
    $links = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
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
function update_meta_cache(string $meta_type, $array_ids)
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
    $db = new \TriTan\Database();
    $encrypt = $db->table('encryption');

    if ($encrypt->count() > 0) {
        return false;
    }

    $encrypt->begin();
    try {
        $key = \Defuse\Crypto\Key::createNewRandomKey();
        $encrypt->insert([
            'key' => $key->saveToAsciiSafeString(),
            'created_at' => (string) (new \TriTan\Common\Date())->{'current'}('laci')
        ]);
        $encrypt->commit();
    } catch (Exception $ex) {
        $encrypt->rollback();
        Cascade::getLogger('error')->error(
            sprintf(
                'SQLSTATE[%s]: %s',
                $ex->getCode(),
                $ex->getMessage()
            ),
            [
                'Db Functions' => 'php_encryption'
            ]
        );
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
function does_option_exist(string $option_key) : bool
{
    $db = new \TriTan\Database();
    $key = $db->table(c::getInstance()->get('tbl_prefix') . 'option')
            ->where('option_key', '=', $option_key)
            ->first();
    return (int) $key['option_id'] > 0;
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
function update_post_relative_url_posttype(int $id, string $old_slug, string $new_slug)
{
    $db = new \TriTan\Database();
    $post = $db->table(c::getInstance()->get('tbl_prefix') . 'post');
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
        ttcms()->obj['flash']->{'error'}(ttcms()->obj['flash']->{'notice'}(409));
    }

    $collection = $db->table(c::getInstance()->get('tbl_prefix') . 'post');
    $query = $collection->where('post_type.posttype_id', (int) $id)->map(function ($data) use ($old_slug, $new_slug) {
        $data['post_relative_url'] = str_replace(
            (string) $old_slug,
            (string) $new_slug,
            (string) $data['post_relative_url']
        );
        return $data;
    });
    $query->save();
}

/**
 * Checks if a slug exists among records from the posttype document.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9.9
 * @param int       $posttype_id    Posttype id to check against.
 * @param string    $slug           Slug to search for.
 * @return boolean
 */
function ttcms_posttype_slug_exist(int $posttype_id, string $slug) : bool
{
    $db = new \TriTan\Database();
    $exist = $db->table(c::getInstance()->get('tbl_prefix') . 'posttype')
            ->where('posttype_slug', $slug)
            ->where('posttype_id', 'not in', $posttype_id)
            ->count();
    return $exist > 0;
}

/**
 * Checks if a slug exists among records from the post document.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9.9
 * @param int|null  $post_id    Post id to check against or null.
 * @param string    $slug       Slug to search for.
 * @param string    $post_type  The post type to filter.
 * @return boolean
 */
function ttcms_post_slug_exist($post_id, string $slug, string $post_type) : bool
{
    $db = new \TriTan\Database();
    $exist = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
            ->where('post_slug', $slug)
            ->where('post_id', 'not in', $post_id)
            ->where('post_type.post_posttype', $post_type)
            ->count();
    return $exist > 0;
}

/**
 * Checks if a slug exists among records from the site document.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9.9
 * @param int|null  $site_id    Site id to check against or null.
 * @param string    $slug       Slug to search for.
 * @return boolean
 */
function ttcms_site_slug_exist($site_id, string $slug) : bool
{
    $db = new \TriTan\Database();
    $exist = $db->table('site')
            ->where('site_slug', $slug)
            ->where('site_id', 'not in', $site_id)
            ->count();
    return $exist > 0;
}

/**
 * Checks if a post has any children.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9.9
 * @param int $post_id Post id to check.
 * @return bool|array False if not, array of children if true.
 */
function is_post_parent(int $post_id)
{
    $db = new \TriTan\Database();
    $children = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
            ->where('post_attributes.parent.parent_id', $post_id);
    if ($children->count() <= 0) {
        return false;
    }
    return $children->get();
}

/**
 * Checks if a given posttype exists on posts.
 *
 * @since 0.9.9
 * @param int $posttype_id Posttype id to check for.
 * @return bool True if exists, false otherwise;
 */
function is_post_posttype_exist($posttype_id) : bool
{
    $db = new \TriTan\Database();
    $exist = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
            ->where('post_type.posttype_id', $posttype_id)
            ->count();
    return $exist > 0;
}

/**
 * Reassigns posts to a different user.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9.9
 * @param int $user_id    ID of user being removed.
 * @param int $assign_id  ID of user to whom posts will be assigned.
 */
function reassign_posts(int $user_id, int $assign_id)
{
    $db = new \TriTan\Database();
    $count = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
            ->where('post_author', (int) $user_id)
            ->count();
    if ($count > 0) {
        $reassign = $db->table(c::getInstance()->get('tbl_prefix') . 'post');
        $reassign->begin();
        try {
            $reassign->where('post_author', (int) $user_id)
                ->update([
                    'post_author' => (int) $assign_id
                ]);
            $reassign->commit();
        } catch (Exception $ex) {
            $reassign->rollback();
            ttcms()->obj['flash']->error(
                sprintf(
                    esc_html__(
                        'Reassign post error: %s'
                    ),
                    $ex->getMessage()
                )
            );
        }
    }
}

/**
 * Reassigns sites to a different user.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9.9
 * @param int   $user_id    ID of user being removed.
 * @param array $params     User parameters (assign_id and role).
 */
function reassign_sites(int $user_id, array $params = [])
{
    $db = new \TriTan\Database();

    if (!is_numeric($user_id)) {
        return false;
    }

    if ((int) $user_id <= 0) {
        return false;
    }

    $count = $db->table('site')
            ->where('site_owner', (int) $user_id)
            ->count();

    if ($count > 0) {
        $reassign = $db->table('site');
        $reassign->begin();
        try {
            $reassign->where('site_owner', (int) $user_id)
                    ->update([
                        'site_owner' => (int) $params['assign_id']
                    ]);
            $reassign->commit();
        } catch (Exception $ex) {
            $reassign->rollback();
            ttcms()->obj['flash']->error(
                sprintf(
                    esc_html__(
                        'Reassign site error: %s'
                    ),
                    $ex->getMessage()
                )
            );
        }
    }
}

/**
 * Checks if the requested user is an admin of any sites or has any admin roles.
 *
 * @file app/functions/db-function.php
 *
 * @since 0.9.9
 * @param int $user_id ID of user to check.
 * @return bool Returns true if user has sites and false otherwise.
 */
function does_user_have_sites(int $user_id = 0)
{
    $db = new \TriTan\Database();
    $owner = $db->table('site')
            ->where('site_owner', $user_id)
            ->count();
    if ($owner > 0) {
        return true;
    }

    $option = get_user_option('role', $user_id);
    if ((int) $option == (int) 1 || (int) $option == (int) 2) {
        return true;
    }
    return false;
}

/**
 * Get an array of sites by user.
 *
 * @since 0.9.9
 * @param int $user_id The user's id.
 * @return array
 */
function get_users_sites(int $user_id)
{
    $db = new \TriTan\Database();
    $sites = $db->table('site')
            ->where('site_owner', (int) $user_id)
            ->get();
    return $sites;
}

/**
 * Populate the option cache.
 *
 * @access private
 * @since 0.9.9
 */
function populate_options_cache()
{
    $db = new TriTan\Database();
    $options = $db->table(c::getInstance()->get('tbl_prefix') . 'option')->all();
    foreach ($options as $value) {
        ttcms()->obj['cache']->{'create'}($value['option_key'], $value, 'option');
    }
}

/**
 * Populate the user cache.
 *
 * @access private
 * @since 0.9.9
 */
function populate_usermeta_cache()
{
    $db = new TriTan\Database();

    $umeta = $db->table('usermeta')->all();
    (new \TriTan\Common\MetaData(
        $db,
        new \TriTan\Common\Context\HelperContext()
    ))->{'updateMetaDataCache'}('user', $umeta);
}
