<?php

namespace TriTan\Functions;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use Cascade\Cascade;

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
 * @param int|Post $posttype
 *            Post Type ID or post type array.
 * @param bool $array
 *              If set to true, data will return as an array, else as an object.
 *              Default: true.
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
     * @param string    $posttype_id The posttype id.
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
     * @param int       $posttype_id The posttype id.
     */
    return app()->hook->{'apply_filter'}('posttype_slug', $slug, $posttype_id);
}

/**
 * A function which retrieves a TriTan CMS posttype description.
 * 
 * Purpose of this function is for the `posttype_description`
 * filter.
 * 
 * @file app/functions/posttype-function.php
 *
 * @since 0.9.9
 * @param int $posttype_id The unique id of a posttype.
 * @return string
 */
function get_posttype_description($posttype_id = 0)
{
    $posttype = get_posttype($posttype_id);
    $description = _escape($posttype['posttype_description']);
    /**
     * Filters the posttype's description.
     *
     * @since 0.9.9
     *
     * @param string    $description The posttype's description.
     * @param int       $posttype_id The posttype id.
     */
    return app()->hook->{'apply_filter'}('posttype_description', $description, $posttype_id);
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

/**
 * Creates a unique posttype slug.
 * 
 * @since 0.9.8
 * @param string $original_slug     Original slug of posttype.
 * @param string $original_title    Original title of posttype.
 * @param int $posttype_id          Unique posttype id.
 * @return string Unique posttype slug.
 */
function ttcms_unique_posttype_slug($original_slug, $original_title, $posttype_id)
{
    if (ttcms_posttype_slug_exist($posttype_id, $original_slug)) {
        $posttype_slug = ttcms_slugify($original_title, 'posttype');
    } else {
        $posttype_slug = $original_slug;
    }
    /**
     * Filters the unique posttype slug before returned.
     * 
     * @since 0.9.9
     * @param string    $posttype_slug      Unique posttype slug.
     * @param string    $original_slug      The posttype's original slug.
     * @param string    $original_title     The posttype's original title before slugified.
     * @param int       $posttype_id        The posttype's unique id.
     */
    return app()->hook->{'apply_filter'}('ttcms_unique_posttype_slug', $posttype_slug, $original_slug, $original_title, $posttype_id);
}

/**
 * Insert or update a posttype.
 *
 * All of the `$posttypedata` array fields have filters associated with the values. The filters
 * have the prefix 'pre_' followed by the field name. An example using 'posttype_title' would have
 * the filter called, 'pre_posttype_title' that can be hooked into.
 * 
 * @file app/functions/posttype-function.php
 *
 * @since 0.9.9
 * @param array $posttypedata An array of data that is used for insert or update.
 *
 *      @type string $posttype_title        The posttype's title.
 *      @type string $posttype_slug         The posttype's slug.
 *      @type string $posttype_description  The posttype's description.
 * 
 * @return int|Exception|null   The newly created posttype's posttype_id, Exception or returns null
 *                              if the posttype could not be created or updated.
 */
function ttcms_insert_posttype($posttypedata, $exception = false)
{
    // Are we updating or creating?
    if (!empty($posttypedata['posttype_id'])) {
        $update = true;
        $posttype_id = (int) $posttypedata['posttype_id'];
        $posttype_before = get_posttype((int) $posttype_id, false);

        if (is_null($posttype_before)) {
            if ($exception) {
                throw new Exception(_t('Invalid posttype id.', 'tritan-cms'), 'invalid_posttype_id');
            } else {
                return null;
            }
        }

        $previous_slug = get_posttype_slug((int) $posttype_id);
        /**
         * Fires immediately before a posttype is inserted into the posttype document.
         *
         * @since 0.9.9
         * @param string    $previous_slug  Slug of the post before it was created.
         *                                  or updated.
         * @param int       $posttype_id    The posttype's posttype_id.
         * @param bool      $update         Whether this is an existing posttype or a new posttype.
         */
        app()->hook->{'do_action'}('posttype_previous_slug', $previous_slug, (int) $posttype_id, $update);
    } else {
        $update = false;
        $posttype_id = auto_increment(Config::get('tbl_prefix') . 'posttype', 'posttype_id');

        $previous_slug = null;
        /**
         * Fires immediately before a posttype is inserted into the posttype document.
         *
         * @since 0.9.9
         * @param string    $previous_slug  Slug of the posttype before it is created.
         *                                  or updated.
         * @param int       $posttype_id    The posttype's posttype_id.
         * @param bool      $update         Whether this is an existing posttype or a new posttype.
         */
        app()->hook->{'do_action'}('posttype_previous_slug', $previous_slug, (int) $posttype_id, $update);
    }

    $raw_posttype_title = $posttypedata['posttype_title'];
    /**
     * Filters a posttypes's title before the posttype is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_posttype_title The posttype's title.
     */
    $posttype_title = app()->hook->{'apply_filter'}('pre_posttype_title', (string) $raw_posttype_title);

    if (isset($posttypedata['posttype_slug'])) {
        /**
         * ttcms_unique_posttype_slug will take the original slug supplied and check
         * to make sure that it is unique. If not unique, it will make it unique
         * by adding a number at the end.
         */
        $posttype_slug = ttcms_unique_posttype_slug($posttypedata['posttype_slug'], $posttype_title, $posttype_id);
    } else {
        /**
         * For an update, don't modify the post_slug if it
         * wasn't supplied as an argument.
         */
        $posttype_slug = $posttype_before->posttype_slug;
    }

    $raw_posttype_slug = $posttype_slug;
    /**
     * Filters a posttypes's slug before the posttype is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_posttype_slug The posttype's slug.
     */
    $posttype_slug = app()->hook->{'apply_filter'}('pre_posttype_slug', (string) $raw_posttype_slug);

    $raw_posttype_description = $posttypedata['posttype_description'];
    /**
     * Filters a posttypes's description before the posttype is created or updated.
     *
     * @since 0.9.9
     * @param string $raw_posttype_description The posttype's description.
     */
    $posttype_description = app()->hook->{'apply_filter'}('pre_posttype_description', (string) $raw_posttype_description);

    /*
     * Filters whether the posttype is null.
     * 
     * @since 0.9.9
     * @param bool  $maybe_empty Whether the posttype should be considered "null".
     * @param array $_postdata   Array of post data.
     */
    $maybe_null = !$posttype_title && !$posttype_slug;
    if (app()->hook->{'apply_filter'}('ttcms_insert_posttype_empty_content', $maybe_null, $posttypedata)) {
        if ($exception) {
            throw new Exception(_t('The title and slug are null'), 'empty_content');
        } else {
            return null;
        }
    }

    $compacted = compact('posttype_title', 'posttype_slug', 'posttype_description');
    $data = ttcms_unslash($compacted);

    /**
     * Filters posttype data before the record is created or updated.
     *
     * @since 0.9.9
     * @param array    $data
     *     Values and keys for the user.
     *
     *      @type string $posttype_title    The posttype's title.
     *      @type string $posttype_slug     The posttype's slug.
     *      @type string $posttype_author   The posttype's description.
     * 
     * @param bool     $update          Whether the posttype is being updated rather than created.
     * @param int|null $posttype_id     ID of the posttype to be updated or created.
     */
    $data = app()->hook->{'apply_filter'}('ttcms_before_insert_posttype_data', $data, $update, $posttype_id);
    $where = ['posttype_id' => (int) $posttype_id];

    if ($update) {
        $_posttype = app()->db->table(Config::get('tbl_prefix') . 'posttype');
        $_posttype->begin();
        try {
            $_posttype->where('posttype_id', (int) $where['posttype_id'])
                    ->update($data);
            $_posttype->commit();
        } catch (Exception $ex) {
            $_posttype->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            return null;
        }
    } else {
        $data = array_merge($where, $data);
        /**
         * Fires immediately before a posttype is inserted into the posttype document.
         *
         * @since 0.9.9
         * @param int   $posttype_id    Posttype id.
         * @param array $data           Array of posttype data.
         */
        app()->hook->{'do_action'}('pre_posttype_insert', (int) $posttype_id, $data);

        $_posttype = app()->db->table(Config::get('tbl_prefix') . 'posttype');
        $_posttype->begin();
        try {
            $_posttype->insert($data);
            $_posttype->commit();
        } catch (Exception $ex) {
            $_posttype->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            return null;
        }
    }

    clean_posttype_cache((int) $posttype_id);
    $posttype = get_posttype((int) $posttype_id, false);

    if ($update) {
        /**
         * Action hook triggered after existing posttype has been updated.
         *
         * @since 0.9.9
         * @param int   $posttype_id    Posttype id.
         * @param array $posttype       Posttype object.
         */
        app()->hook->{'do_action'}('update_posttype', (int) $posttype_id, $posttype);
        $posttype_after = get_posttype((int) $posttype_id, false);

        /**
         * If posttype slug has changed, update all posts that may be affected
         * by this change.
         * 
         * @since 0.9.6
         */
        if ((string) _escape($posttype_before->posttype_slug) != (string) _escape($posttype_after->posttype_slug)) {
            update_post_relative_url_posttype($posttype_id, _escape($posttype_before->posttype_slug), (string) _escape($posttype_after->posttype_slug));
        }

        ttcms_cache_flush_namespace('post');
        ttcms_cache_flush_namespace('post_meta');

        /**
         * Action hook triggered after existing post has been updated.
         *
         * @since 0.9.9
         * @param int       $posttype_id      Posttype id.
         * @param object    $posttype_after   Posttype object following the update.
         * @param object    $posttype_before  Posttype object before the update.
         */
        app()->hook->{'do_action'}('posttype_updated', (int) $posttype_id, $posttype_after, $posttype_before);
    }

    /**
     * Action hook triggered after posttype has been saved.
     *
     * @since 0.9.9
     * @param int   $posttype_id    The posttype's id.
     * @param array $posttype       Posttype object.
     * @param bool  $update         Whether this is an existing posttype or a new posttype.
     */
    app()->hook->{'do_action'}('ttcms_after_insert_posttype_data', (int) $posttype_id, $posttype, $update);

    return (int) $posttype_id;
}

/**
 * Update a posttype in the post document.
 * 
 * See {@see ttcms_insert_posttype()} For what fields can be set in $posttypedata.
 * 
 * @file app/functions/posttype-function.php
 *
 * @since 0.9.9
 * @param array|object $posttypedata An array of posttype data or a posttype object.
 * @return int|Exception|null The updated posttype's id, Exception or return null if posttype could not be updated.
 */
function ttcms_update_posttype($posttypedata = [], $exception = false)
{
    if (is_object($posttypedata)) {
        $posttypedata = get_object_vars($posttypedata);
    }

    // First, get all of the original fields.
    $posttype = get_posttype((int) $posttypedata['posttype_id']);

    if (is_null($posttype)) {
        if ($exception) {
            throw new Exception(_t('Invalid posttype id.'), 'invalid_posttype_id');
        }
        return null;
    }

    // Merge old and new fields with new fields overwriting old ones.
    $_posttypedata = array_merge($posttype, $posttypedata);

    return ttcms_insert_posttype($_posttypedata);
}

/**
 * Deletes a posttype from the posttype document.
 * 
 * @since 0.9.9
 * @param int $posttype_id The id of the posttype to delete.
 * @return boolean
 */
function ttcms_delete_posttype($posttype_id = 0)
{
    $posttype = get_posttype($posttype_id);

    if (!$posttype) {
        return false;
    }

    /**
     * Action hook fires before a posttype is deleted.
     *
     * @since 0.9.9
     * @param int $posttype_id Posttype id.
     */
    app()->hook->{'do_action'}('before_delete_posttype', (int) $posttype_id);

    /**
     * Action hook fires immediately before a posttype is deleted from the
     * posttype document.
     *
     * @since 0.9.9
     * @param int $posttype_id Posttype ID.
     */
    app()->hook->{'do_action'}('delete_posttype', (int) $posttype_id);

    $delete = app()->db->table(Config::get('tbl_prefix') . 'posttype');
    $delete->begin();
    try {
        $delete->where('posttype_id', (int) $posttype_id)->delete();
        $delete->commit();

        $post = app()->db->table(Config::get('tbl_prefix') . 'post');
        $post->begin();
        try {
            $post->where('post_type.posttype_id', (int) $posttype_id)
                    ->delete();
            $post->commit();
        } catch (Exception $ex) {
            $post->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }
    } catch (Exception $ex) {
        $delete->rollback();
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        return false;
    }

    /**
     * Action hook fires immediately after a posttype is deleted from the posttype document.
     *
     * @since 0.9.9
     * @param int $posttype_id Posttype id.
     */
    app()->hook->{'do_action'}('deleted_posttype', (int) $posttype_id);

    clean_posttype_cache($posttype);
    ttcms_cache_delete('posttype', 'posttype');
    ttcms_cache_delete('post', 'post');

    /**
     * Action hook fires after a posttype is deleted.
     *
     * @since 0.9.9
     * @param int $posttype_id Posttype id.
     */
    app()->hook->{'do_action'}('after_delete_posttype', (int) $posttype_id);

    return $posttype;
}

/**
 * Clean posttype caches.
 * 
 * Uses `clean_posttype_cache` action.
 * 
 * @file app/functions/posttype-function.php
 *
 * @since 0.9.9
 * @param array|int|object $posttype Posttype array, posttype_id, posttype object to be cleaned from the cache.
 */
function clean_posttype_cache($posttype)
{
    $_posttype = get_posttype($posttype);
    if (empty($_posttype)) {
        return;
    }

    ttcms_cache_delete((int) _escape($_posttype['posttype_id']), 'posttype');
    ttcms_cache_delete('posttype', 'posttype');

    /**
     * Fires immediately after the given posttype's cache is cleaned.
     *
     * @since 0.9.9
     * @param int   $_posttype['posttype_id']   Posttype id.
     * @param array $_posttype                  Posttype array.
     */
    app()->hook->{'do_action'}('clean_posttype_cache', (int) _escape($_posttype['posttype_id']), $_posttype);
}
