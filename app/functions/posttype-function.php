<?php
use TriTan\Common\Hooks\ActionFilterHook as hook;
use TriTan\Common\Posttype\PosttypeRepository;
use TriTan\Common\Posttype\PosttypeMapper;
use TriTan\Common\Posttype\PosttypeCache;
use TriTan\Common\Posttype\Posttype;
use TriTan\Common\Context\HelperContext;
use TriTan\Database;

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
 * @param int|Posttype $posttype Posttype ID or postttype object.
 * @param bool         $object   If set to true, data will return as an object, else as an array.
 *                               Default: true.
 * @return array|object
 */
function get_posttype($posttype, $object = true)
{    
    if ($posttype instanceof \TriTan\Common\Posttype\Posttype) {
        $_posttype = $posttype;
    } elseif (is_object($posttype)) {
        if (empty($posttype->getId())) {
            return null;
        } else {
            $_posttype = (new PosttypeRepository(
                new PosttypeMapper(
                    new Database(),
                    new HelperContext()
                )
            ))->{'findById'}((int) $posttype->getId());
        }
    } else {
        $_posttype = (new PosttypeRepository(
            new PosttypeMapper(
                new Database(),
                new HelperContext()
            )
        ))->{'findById'}((int) $posttype);
    }

    if (!$_posttype) {
        return null;
    }

    if ($object === false) {
        $_posttype = $_posttype->toArray();
    }

    /**
     * Fires after a post type is retrieved.
     *
     * @since 0.9.9
     * @param Posttype $_posttype Posttype data.
     */
    $_posttype = hook::getInstance()->{'applyFilter'}('get_posttype', $_posttype);

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
    $title = esc_html($posttype->getTitle());
    /**
     * Filters the posttype title.
     *
     * @since 0.9.9
     *
     * @param string    $title The posttype's title.
     * @param string    $posttype_id The posttype id.
     */
    return hook::getInstance()->{'applyFilter'}('posttype_title', $title, $posttype_id);
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
    $slug = esc_html($posttype->getSlug());
    /**
     * Filters the posttype's slug.
     *
     * @since 0.9.9
     *
     * @param string    $slug The posttype's slug.
     * @param int       $posttype_id The posttype id.
     */
    return hook::getInstance()->{'applyFilter'}('posttype_slug', $slug, $posttype_id);
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
    $description = esc_html($posttype->getDescription());
    /**
     * Filters the posttype's description.
     *
     * @since 0.9.9
     *
     * @param string    $description The posttype's description.
     * @param int       $posttype_id The posttype id.
     */
    return hook::getInstance()->{'applyFilter'}('posttype_description', $description, $posttype_id);
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
    $link = esc_url( site_url( get_posttype_slug($posttype_id) . '/' ) );
    /**
     * Filters the posttype's link.
     *
     * @since 0.9.9
     *
     * @param string    $link The posttype's permalink.
     * @param int       $posttype_id The posttype id.
     */
    return hook::getInstance()->{'applyFilter'}('posttype_permalink', $link, $posttype_id);
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
    if($posttype_id <= 0){
        $posttype_slug = ttcms_slugify($original_title, 'posttype');
    } elseif (ttcms_posttype_slug_exist($posttype_id, $original_slug)) {
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
    return hook::getInstance()->{'applyFilter'}('ttcms_unique_posttype_slug', $posttype_slug, $original_slug, $original_title, $posttype_id);
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
 * @return int|Exception|null The newly created posttype's posttype_id, Exception or returns null
 *                            if the posttype could not be created or updated.
 */
function ttcms_insert_posttype($posttypedata, $exception = false)
{
    // Are we updating or creating?
    if (!empty($posttypedata['posttype_id'])) {
        $update = true;
        $posttype_id = (int) $posttypedata['posttype_id'];
        $posttype_before = get_posttype((int) $posttype_id);

        if (is_null($posttype_before)) {
            if ($exception) {
                throw new Exception(t__('Invalid posttype id.', 'tritan-cms'), 'invalid_posttype_id');
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
        hook::getInstance()->{'doAction'}('posttype_previous_slug', $previous_slug, (int) $posttype_id, $update);
        
        /**
         * Create new posttype object.
         */
        $posttype = new Posttype();
        $posttype->setId($posttype_id);
    } else {
        $update = false;

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
        hook::getInstance()->{'doAction'}('posttype_previous_slug', $previous_slug, (int) $posttype_id, $update);
        
        /**
         * Create new posttype object.
         */
        $posttype = new Posttype();
    }

    $raw_posttype_title = $posttypedata['posttype_title'];
    $sanitized_posttype_title = ttcms()->obj['sanitizer']->{'item'}($raw_posttype_title);
    /**
     * Filters a posttypes's title before the posttype is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_posttype_title Posttype title after it has been sanitized.
     * @param string $raw_posttype_title The posttype's title.
     */
    $posttype_title = hook::getInstance()->{'applyFilter'}(
        'pre_posttype_title',
        (string) $sanitized_posttype_title,
        (string) $raw_posttype_title
    );
    $posttype->setTitle($posttype_title);

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
    $sanitized_posttype_slug = ttcms()->obj['sanitizer']->{'item'}($raw_posttype_slug);
    /**
     * Filters a posttypes's slug before the posttype is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_posttype_slug Posttype slug after it has been sanitized.
     * @param string $raw_posttype_slug The posttype's slug.
     */
    $posttype_slug = hook::getInstance()->{'applyFilter'}(
        'pre_posttype_slug',
        (string) $sanitized_posttype_slug,
        (string) $raw_posttype_slug
    );
    $posttype->setSlug($posttype_slug);

    $raw_posttype_description = $posttypedata['posttype_description'];
    $sanitized_posttype_description = ttcms()->obj['sanitizer']->{'item'}($raw_posttype_description);
    /**
     * Filters a posttypes's description before the posttype is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_posttype_description Posttype description after it has been sanitized.
     * @param string $raw_posttype_description The posttype's description.
     */
    $posttype_description = hook::getInstance()->{'applyFilter'}(
        'pre_posttype_description',
        (string) $sanitized_posttype_description,
        (string) $raw_posttype_description
    );
    $posttype->setDescription($posttype_description);

    /*
     * Filters whether the posttype is null.
     *
     * @since 0.9.9
     * @param bool  $maybe_empty Whether the posttype should be considered "null".
     * @param array $_postdata   Array of post data.
     */
    $maybe_null = !$posttype_title && !$posttype_slug;
    if (hook::getInstance()->{'applyFilter'}('ttcms_insert_posttype_empty_content', $maybe_null, $posttypedata)) {
        if ($exception) {
            throw new Exception(t__('The title and slug are null'), 'empty_content');
        } else {
            return null;
        }
    }

    $compacted = compact('posttype_title', 'posttype_slug', 'posttype_description');
    $data = ttcms()->obj['util']->{'unslash'}($compacted);

    /**
     * Filters posttype data before the record is created or updated.
     *
     * @since 0.9.9
     * @param array    $data
     *     Values and keys for the posttype.
     *
     *      @type string $posttype_title    The posttype's title.
     *      @type string $posttype_slug     The posttype's slug.
     *      @type string $posttype_author   The posttype's description.
     *
     * @param bool     $update          Whether the posttype is being updated rather than created.
     * @param int|null $posttype_id     ID of the posttype to be updated or created.
     */
    $data = hook::getInstance()->{'applyFilter'}('ttcms_before_insert_posttype_data', $data, $update, $posttype_id);

    if ($update) {
        $posttype_id = (
            new PosttypeRepository(
                new PosttypeMapper(
                    new Database(),
                    new HelperContext()
                )
            )
        )->{'update'}($posttype);
    } else {
        /**
         * Fires immediately before a posttype is inserted into the posttype document.
         *
         * @since 0.9.9
         * @param array $data           Array of posttype data.
         */
        hook::getInstance()->{'doAction'}('pre_posttype_insert', (int) $data);

        $posttype_id = (
            new PosttypeRepository(
                new PosttypeMapper(
                    new Database(),
                    new HelperContext()
                )
            )
        )->{'insert'}($posttype);
    }

    (new PosttypeCache(
        ttcms()->obj['cache'],
        hook::getInstance()
    ))->{'clean'}($posttype);
    
    $posttype = get_posttype((int) $posttype_id);

    if ($update) {
        /**
         * Action hook triggered after existing posttype has been updated.
         *
         * @since 0.9.9
         * @param int   $posttype_id    Posttype id.
         * @param array $posttype       Posttype object.
         */
        hook::getInstance()->{'doAction'}('update_posttype', (int) $posttype_id, $posttype);
        $posttype_after = get_posttype((int) $posttype_id);

        /**
         * If posttype slug has changed, update all posts that may be affected
         * by this change.
         *
         * @since 0.9.9
         */
        if (is_post_posttype_exist($posttype_id) && ((string) esc_html($posttype_before->getSlug()) != (string) esc_html($posttype_after->getSlug()))) {
            update_post_relative_url_posttype($posttype_id, esc_html($posttype_before->getSlug()), (string) esc_html($posttype_after->getSlug()));
        }

        (new PosttypeCache(
            ttcms()->obj['cache'],
            hook::getInstance()
        ))->{'clean'}($posttype);

        /**
         * Action hook triggered after existing post has been updated.
         *
         * @since 0.9.9
         * @param int       $posttype_id      Posttype id.
         * @param object    $posttype_after   Posttype object following the update.
         * @param object    $posttype_before  Posttype object before the update.
         */
        hook::getInstance()->{'doAction'}('posttype_updated', (int) $posttype_id, $posttype_after, $posttype_before);
    }

    /**
     * Action hook triggered after posttype has been saved.
     *
     * @since 0.9.9
     * @param int   $posttype_id    The posttype's id.
     * @param array $posttype       Posttype object.
     * @param bool  $update         Whether this is an existing posttype or a new posttype.
     */
    hook::getInstance()->{'doAction'}('ttcms_after_insert_posttype_data', (int) $posttype_id, $posttype, $update);

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
    $posttype = get_posttype((int) $posttypedata['posttype_id'], false);

    if (is_null($posttype)) {
        if ($exception) {
            throw new Exception(t__('Invalid posttype id.'), 'invalid_posttype_id');
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
    hook::getInstance()->{'doAction'}('before_delete_posttype', (int) $posttype_id);

    /**
     * Action hook fires immediately before a posttype is deleted from the
     * posttype document.
     *
     * @since 0.9.9
     * @param int $posttype_id Posttype ID.
     */
    hook::getInstance()->{'doAction'}('delete_posttype', (int) $posttype_id);

    (new PosttypeRepository(
        new PosttypeMapper(
            new Database(),
            new HelperContext()
        )
    ))->{'delete'}($posttype);

    /**
     * Action hook fires immediately after a posttype is deleted from the posttype document.
     *
     * @since 0.9.9
     * @param int $posttype_id Posttype id.
     */
    hook::getInstance()->{'doAction'}('deleted_posttype', (int) $posttype_id);

    (new PosttypeCache(
        ttcms()->obj['cache'],
        hook::getInstance()
    ))->{'clean'}($posttype);

    /**
     * Action hook fires after a posttype is deleted.
     *
     * @since 0.9.9
     * @param int $posttype_id Posttype id.
     */
    hook::getInstance()->{'doAction'}('after_delete_posttype', (int) $posttype_id);

    return $posttype;
}

/**
 * Function used to dynamically generate post screens
 * based on post type.
 *
 * @file app/functions/posttype-function.php
 *
 * @since 0.9
 * @return array
 */
function get_all_post_types()
{
    $posttypes = (
        new \TriTan\Common\Posttype\PosttypeRepository(
            new TriTan\Common\Posttype\PosttypeMapper(
                new \TriTan\Database(),
                new TriTan\Common\Context\HelperContext()
            )
        )
    )->{'findAll'}();
    return $posttypes;
}
