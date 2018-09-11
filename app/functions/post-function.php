<?php
use TriTan\Database;
use TriTan\Container as c;
use TriTan\Exception\Exception;
use TriTan\Common\Post\Post;
use TriTan\Common\Post\PostRepository;
use TriTan\Common\Post\PostMapper;
use TriTan\Common\Hooks\ActionFilterHook as hook;
use TriTan\Common\Context\HelperContext;
use TriTan\Common\Options\Options;
use TriTan\Common\Options\OptionsMapper;
use TriTan\Common\MetaData;

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
    $db = new Database();
    $posts = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
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
    $db = new Database();
    $posts = $db->table(c::getInstance()->get('tbl_prefix') . 'post')
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
 * @param int|Post|null $post Post ID or post object.
 * @param bool $object  If set to true, data will return as an object, else as an array.
 *                      Default: true.
 * @return array|object
 */
function get_post($post, $object = true)
{
    if ($post instanceof Post) {
        $_post = $post;
    } elseif (is_object($post)) {
        if (empty($post->getId())) {
            $_post = null;
        } else {
            $_post = (
                new PostRepository(
                    new PostMapper(
                        new Database(),
                        new HelperContext()
                    )
                )
            )->{'findById'}($post->getId());
        }
    } else {
        $_post = (
            new PostRepository(
                new PostMapper(
                    new Database(),
                    new HelperContext()
                )
            )
        )->{'findById'}((int) $post);
    }

    if (!$_post) {
        return null;
    }

    if ($object === false) {
        $_post = $_post->toArray();
    }

    /**
     * Fires after a post is retrieved.
     *
     * @since 0.9
     * @param Post $_post Post data.
     */
    $_post = hook::getInstance()->{'applyFilter'}('get_post', $_post);

    return $_post;
}

/**
 * Retrieve post by a given field from the post table.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param string     $field The field to retrieve the post with.
 * @param int|string $value A value for $field (_id, post_id, post_slug).
 */
function get_post_by(string $field, $value)
{
    $post = (
        new \TriTan\Common\Post\PostRepository(
            new TriTan\Common\Post\PostMapper(
                new \TriTan\Database(),
                new TriTan\Common\Context\HelperContext()
            )
        )
    )->{'findBy'}($field, $value);

    return $post;
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
function get_post_datetime(int $post_id = 0)
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
    return hook::getInstance()->{'applyFilter'}('post_datetime', $datetime, $post_id);
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
function get_post_modified(int $post_id = 0)
{
    $option = new Options(
        new Database(),
        new HelperContext()
    );
    $post = get_post($post_id);
    $format = $option->{'read'}('date_format') . ' ' . $option->{'read'}('time_format');

    $modified = (
        new \TriTan\Common\Date()
    )->{'laci2date'}(
        $format,
        $post->getModified()
    );

    /**
     * Filters the post date.
     *
     * @since 0.9
     *
     * @param string $modified The post's modified datetime.
     * @param int  $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_modified', $modified, $post_id);
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
function get_post_content(int $post_id = 0)
{
    $post = get_post($post_id);
    $content = $post->getContent();
    /**
     * Filters the post date.
     *
     * @since 0.9.9
     *
     * @param string $content The post's content.
     * @param int  $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_content', $content, (int) $post_id);
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
function get_post_type_name(int $post_id = 0)
{
    $post = get_post($post_id);
    $posttype = get_posttype($post->getPosttypeId());
    $posttype_name = $posttype->getTitle();
    /**
     * Filters the post posttype name.
     *
     * @since 0.9
     *
     * @param string $posttype_name The post's posttype name.
     * @param string  $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_posttype_name', $posttype_name, $post_id);
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
function get_post_posttype_link(int $post_id = 0)
{
    $link = site_url(get_post_posttype($post_id) . '/');
    /**
     * Filters the post posttype link.
     *
     * @since 0.9
     *
     * @param string $link The post's posttype link.
     * @param string  $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_posttype_link', $link, $post_id);
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
function get_post_title(int $post_id = 0)
{
    $post = get_post($post_id);
    $title = $post->getTitle();
    /**
     * Filters the post title.
     *
     * @since 0.9
     *
     * @param string $title The post's title.
     * @param string  $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_title', $title, $post_id);
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
function get_post_slug(int $post_id = 0)
{
    $post = get_post($post_id);
    $slug = $post->getSlug();
    /**
     * Filters the post's slug.
     *
     * @since 0.9
     *
     * @param string $slug The post's slug.
     * @param int  $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_slug', $slug, $post_id);
}

/**
 * A function which retrieves a TriTan CMS post's relative url.
 *
 * Purpose of this function is for the `{$posttype}_relative_url`
 * filter.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9.5
 * @param int|Post $post Post id or Post.
 * @return string
 */
function get_relative_url($post = 0)
{
    if (!is_object($post) && !is_numeric($post)) {
        return '';
    }

    $post = get_post($post);

    if ((int) $post->getId() <= 0) {
        return false;
    }

    $relative_url = $post->getRelativeUrl();
    /**
     * Filters the post's relative_url.
     *
     * @since 0.9.5
     *
     * @param string $relative_url The post's relative url.
     * @param int    $post_id      The post id.
     */
    return hook::getInstance()->{'applyFilter'}(
        $post->getPosttype() . '_relative_url',
        $relative_url,
        $post->getId()
    );
}

/**
 * A function which retrieves a TriTan CMS post's permalink.
 *
 * Purpose of this function is for the `{$posttype}_link`
 * filter.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param int|Post $post Post id or Post.
 * @return string
 */
function get_permalink($post = 0)
{
    if (!is_object($post) && !is_numeric($post)) {
        return '';
    }

    $post = get_post($post);

    if (empty($post->getId())) {
        return false;
    }

    $link = home_url(get_relative_url($post));
    /**
     * Filters the post's link based on its posttype.
     *
     * @since 0.9
     *
     * @param string $link    The post's link.
     * @param int    $post_id The post id.
     */
    return hook::getInstance()->{'applyFilter'}($post->getPosttype() . '_link', $link, $post->getId());
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
function the_content(int $post_id = 0)
{
    $post_content = get_post_content($post_id);
    $post_content = hook::getInstance()->{'applyFilter'}('the_content', $post_content);
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
    return get_all_posts($post_type, $limit, $offset, $status);
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
function ttcms_post_status_label(string $status)
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
function get_post_meta(int $post_id, string $key = '', bool $single = false)
{
    return (
        new MetaData(
            new Database(),
            new HelperContext()
        )
    )->{'read'}(
        c::getInstance()->get('tbl_prefix') . 'post',
        $post_id,
        $key,
        $single
    );
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
function get_post_meta_by_mid(int $mid)
{
    return (
        new MetaData(
            new Database(),
            new HelperContext()
        )
    )->{'readByMid'}(
        c::getInstance()->get('tbl_prefix') . 'post',
        $mid
    );
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
function update_post_meta(int $post_id, string $meta_key, $meta_value, $prev_value = '')
{
    return (
        new MetaData(
            new Database(),
            new HelperContext()
        )
    )->{'update'}(
        c::getInstance()->get('tbl_prefix') . 'post',
        $post_id,
        $meta_key,
        $meta_value,
        $prev_value
    );
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
function update_post_meta_by_mid(int $mid, string $meta_key, $meta_value)
{
    $_meta_key = ttcms()->obj['util']->{'unslash'}($meta_key);
    $_meta_value = ttcms()->obj['util']->{'unslash'}($meta_value);
    return (
        new MetaData(
            new Database(),
            new HelperContext()
        )
    )->{'updateByMid'}(
        c::getInstance()->get('tbl_prefix') . 'post',
        $mid,
        $_meta_key,
        $_meta_value
    );
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
function add_post_meta(int $post_id, string $meta_key, $meta_value, $unique = false)
{
    return (
        new MetaData(
            new Database(),
            new HelperContext()
        )
    )->{'create'}(
        c::getInstance()->get('tbl_prefix') . 'post',
        $post_id,
        $meta_key,
        $meta_value,
        $unique
    );
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
function delete_post_meta(int $post_id, string $meta_key, $meta_value = '')
{
    return (
        new MetaData(
            new Database(),
            new HelperContext()
        )
    )->{'delete'}(
        c::getInstance()->get('tbl_prefix') . 'post',
        $post_id,
        $meta_key,
        $meta_value
    );
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
function delete_post_meta_by_mid(int $mid)
{
    return (
        new MetaData(
            new Database(),
            new HelperContext()
        )
    )->{'deleteByMid'}(
        c::getInstance()->get('tbl_prefix') . 'post',
        $mid
    );
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
function get_post_custom(int $post_id = 0)
{
    $_post_id = ttcms()->obj['util']->{'absint'}($post_id);
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
function get_post_custom_keys(int $post_id = 0)
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
function get_post_custom_values(string $key = '', int $post_id = 0)
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
    echo hook::getInstance()->{'applyFilter'}('the_permalink', get_permalink($post), $post);
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
function get_post_author_id(int $post_id = 0)
{
    $post = get_post($post_id);
    $author_id = $post->getAuthor();
    /**
     * Filters the post author id.
     *
     * @since 0.9.9
     *
     * @param string    $author_id The post's author id.
     * @param string    $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_author_id', (int) $author_id, $post_id);
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
function get_post_author(int $post_id = 0, bool $reverse = false)
{
    $post = get_post($post_id);
    $author = get_name($post->getAuthor(), $reverse);
    /**
     * Filters the post author.
     *
     * @since 0.9.9
     *
     * @param string    $author The post's author.
     * @param string    $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_author', $author, $post_id);
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
function get_post_status(int $post_id = 0)
{
    $post = get_post($post_id);
    $status = $post->getStatus();
    /**
     * Filters the post status.
     *
     * @since 0.9.9
     *
     * @param string    $status The post's status.
     * @param string    $post_id The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_status', $status, $post_id);
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
function get_post_date(int $post_id = 0, string $type = 'published')
{
    return call_user_func_array("the_{$type}_date", ['Y-m-d',&$post_id]);
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
function get_post_time(int $post_id = 0, string $type = 'published')
{
    return call_user_func_array("the_{$type}_time", ['h:i A',&$post_id]);
}

/**
 * Retrieves TriTan CMS post created date.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param string $format
 * @param int $post The unique id of a post.
 * @return string
 */
function get_post_created_date(string $format = 'U', bool $gmt = false, $post = null, bool $translate = false)
{
    $post = get_post($post);
    $date = new \TriTan\Common\Date();

    if (!$post) {
        return false;
    }

    if ($gmt) {
        $the_date = $date->{'gmtdate'}($post->getCreated());
    } else {
        $the_date = $post->getCreated();
    }

    $the_date = $date->{'laci2Date'}($format, $the_date, $translate);
    /**
     * Filters the post created date.
     *
     * @since 0.9.9
     *
     * @param string $the_date The post's formatted date.
     * @param bool $format Format to use for retrieving the date the post was written.
     *                     Accepts 'G', 'U', or php date format. Default 'U'.
     * @param bool   $gmt  Whether to retrieve the GMT date. Default false.
     */
    return hook::getInstance()->{'applyFilter'}('get_post_created_date', $the_date, $format, $gmt);
}

/**
 * Retrieves TriTan CMS post created date.
 *
 * @since 0.9.9
 * @param string $format Format to use for retrieving the date the post was written.
     *                   Accepts 'G', 'U', or php date format value specified
     *                   in 'date_format' option. Default empty.
 * @param int|Post $post Post array or post id.
 * @return string|int|false Formatted date string or Unix timestamp if $format is 'U' or 'G'. False on failure.
 */
function the_created_date(string $format = '', $post = null)
{
    $post = get_post($post, true);

    if (!$post) {
        return false;
    }

    if ('' == $format) {
        $the_date = get_post_created_date(
            (
            new Options(
                new OptionsMapper(
                    new Database(),
                    new HelperContext()
                )
            )
            )->{'read'}(
                'date_format'
            ),
            false,
            $post,
            true
        );
    } else {
        $the_date = get_post_created_date($format, false, $post, true);
    }

    /**
     * Filters the date the post was written.
     *
     * @since 0.9.9
     * @param string    $the_date The formatted date.
     * @param string    $format   Format to use for retrieving the date the post was written.
     *                            Accepts 'G', 'U', or php date format value specified
     *                            in 'date_format' option. Default empty.
     * @param int|Post  $post     Post array or post id.
     */
    return hook::getInstance()->{'applyFilter'}('the_created_date', $the_date, $format, $post);
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
function get_post_created_time(string $format = 'U', bool $gmt = false, $post = null, bool $translate = false)
{
    $post = get_post($post);
    $date = new \TriTan\Common\Date();

    if (!$post) {
        return false;
    }

    if ($gmt) {
        $time = $date->{'gmtdate'}($post->getCreated());
    } else {
        $time = $post->getCreated();
    }

    $time = $date->{'laci2Date'}($format, $time, $translate);
    /**
     * Filters the post created time.
     *
     * @since 0.9.9
     *
     * @param string $time The post's formatted time.
     * @param bool $format Format to use for retrieving the time the post was written.
     *                     Accepts 'G', 'U', or php date format. Default 'U'.
     * @param bool   $gmt  Whether to retrieve the GMT time. Default false.
     */
    return hook::getInstance()->{'applyFilter'}('get_post_created_time', $time, $format, $gmt);
}

/**
 * Retrieves TriTan CMS post created time.
 *
 * @since 0.9.9
 * @param string $format Format to use for retrieving the time the post was written.
     *                   Accepts 'G', 'U', or php date format value specified
     *                   in 'time_format' option. Default empty.
 * @param int|Post $post Post array or post id.
 * @return string|int|false Formatted date string or Unix timestamp if $format is 'U' or 'G'. False on failure.
 */
function the_created_time(string $format = '', $post = null)
{
    $post = get_post($post, true);

    if (!$post) {
        return false;
    }

    if ('' == $format) {
        $the_time = get_post_created_time(
            (
            new Options(
                new OptionsMapper(
                    new Database(),
                    new HelperContext()
                )
            )
            )->{'read'}(
                'time_format'
            ),
            false,
            $post,
            true
        );
    } else {
        $the_time = get_post_created_time($format, false, $post, true);
    }

    /**
     * Filters the time the post was written.
     *
     * @since 0.9.9
     * @param string    $the_time The formatted time.
     * @param string    $format   Format to use for retrieving the time the post was written.
     *                            Accepts 'G', 'U', or php date format value specified
     *                            in 'time_format' option. Default empty.
     * @param int|Post  $post     Post array or post id.
     */
    return hook::getInstance()->{'applyFilter'}('the_created_date', $the_time, $format, $post);
}

/**
 * A function which retrieves TriTan CMS post published date.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_published_date(string $format = 'U', bool $gmt = false, $post = null, bool $translate = false)
{
    $post = get_post($post);
    $date = new \TriTan\Common\Date();

    if (!$post) {
        return false;
    }

    if ($gmt) {
        $the_date = $date->{'gmtdate'}($post->getPublished());
    } else {
        $the_date = $post->getPublished();
    }

    $the_date = $date->{'laci2Date'}($format, $the_date, $translate);
    /**
     * Filters the post published date.
     *
     * @since 0.9.9
     *
     * @param string $the_date The post's formatted date.
     * @param bool $format Format to use for retrieving the date the post was published.
     *                     Accepts 'G', 'U', or php date format. Default 'U'.
     * @param bool   $gmt  Whether to retrieve the GMT date. Default false.
     */
    return hook::getInstance()->{'applyFilter'}('get_post_published_date', $the_date, $format, $gmt);
}

/**
 * Retrieves TriTan CMS post published date.
 *
 * @since 0.9.9
 * @param string $format Format to use for retrieving the date the post was published.
     *                   Accepts 'G', 'U', or php date format value specified
     *                   in 'date_format' option. Default empty.
 * @param int|Post $post Post array or post id.
 * @return string|int|false Formatted date string or Unix timestamp if $format is 'U' or 'G'. False on failure.
 */
function the_published_date(string $format = '', $post = null)
{
    $post = get_post($post, true);

    if (!$post) {
        return false;
    }

    if ('' == $format) {
        $the_date = get_post_published_date(
            (
            new Options(
                new OptionsMapper(
                    new Database(),
                    new HelperContext()
                )
            )
            )->{'read'}(
                'date_format'
            ),
            false,
            $post,
            true
        );
    } else {
        $the_date = get_post_published_date($format, false, $post, true);
    }

    /**
     * Filters the time the post was written.
     *
     * @since 0.9.9
     * @param string    $the_date The formatted date.
     * @param string    $format   Format to use for retrieving the date the post was published.
     *                            Accepts 'G', 'U', or php date format value specified
     *                            in 'date_format' option. Default empty.
     * @param int|Post  $post     Post array or post id.
     */
    return hook::getInstance()->{'applyFilter'}('the_published_date', $the_date, $format, $post);
}

/**
 * A function which retrieves TriTan CMS post published time.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_published_time(string $format = 'U', bool $gmt = false, $post = null, bool $translate = false)
{
    $post = get_post($post);
    $date = new \TriTan\Common\Date();

    if (!$post) {
        return false;
    }

    if ($gmt) {
        $time = $date->{'gmtdate'}($post->getPublished());
    } else {
        $time = $post->getPublished();
    }

    $time = $date->{'laci2Date'}($format, $time, $translate);
    /**
     * Filters the post published time.
     *
     * @since 0.9.9
     *
     * @param string $time The post's formatted time.
     * @param bool $format Format to use for retrieving the time the post was written.
     *                     Accepts 'G', 'U', or php date format. Default 'U'.
     * @param bool   $gmt  Whether to retrieve the GMT time. Default false.
     */
    return hook::getInstance()->{'applyFilter'}('get_post_published_time', $time, $format, $gmt);
}

/**
 * Retrieves TriTan CMS post published time.
 *
 * @since 0.9.9
 * @param string $format Format to use for retrieving the time the post was published.
     *                   Accepts 'G', 'U', or php date format value specified
     *                   in 'time_format' option. Default empty.
 * @param int|Post $post Post array or post id.
 * @return string|int|false Formatted date string or Unix timestamp if $format is 'U' or 'G'. False on failure.
 */
function the_published_time(string $format = '', $post = null)
{
    $post = get_post($post, true);

    if (!$post) {
        return false;
    }

    if ('' == $format) {
        $the_time = get_post_published_time(
            (
            new Options(
                new OptionsMapper(
                    new Database(),
                    new HelperContext()
                )
            )
            )->{'read'}(
                'time_format'
            ),
            false,
            $post,
            true
        );
    } else {
        $the_time = get_post_published_time($format, false, $post, true);
    }

    /**
     * Filters the time the post was published.
     *
     * @since 0.9.9
     * @param string    $the_time The formatted time.
     * @param string    $format   Format to use for retrieving the time the post was published.
     *                            Accepts 'G', 'U', or php date format value specified
     *                            in 'time_format' option. Default empty.
     * @param int|Post  $post     Post array or post id.
     */
    return hook::getInstance()->{'applyFilter'}('the_published_time', $the_time, $format, $post);
}

/**
 * A function which retrieves TriTan CMS post modified date.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_modified_date(string $format = 'U', bool $gmt = false, $post = null, bool $translate = false)
{
    $post = get_post($post);
    $date = new \TriTan\Common\Date();

    if (!$post) {
        return false;
    }

    if ($gmt) {
        $the_date = $date->{'gmtdate'}($post->getModified());
    } else {
        $the_date = $post->getModified();
    }

    $the_date = $date->{'laci2Date'}($format, $the_date, $translate);
    /**
     * Filters the post modified date.
     *
     * @since 0.9.9
     *
     * @param string $the_date The post's formatted date.
     * @param bool $format Format to use for retrieving the date the post was published.
     *                     Accepts 'G', 'U', or php date format. Default 'U'.
     * @param bool   $gmt  Whether to retrieve the GMT date. Default false.
     */
    return hook::getInstance()->{'applyFilter'}('get_post_modified_date', $the_date, $format, $gmt);
}

/**
 * Retrieves TriTan CMS post published date.
 *
 * @since 0.9.9
 * @param string $format Format to use for retrieving the date the post was published.
     *                   Accepts 'G', 'U', or php date format value specified
     *                   in 'date_format' option. Default empty.
 * @param int|Post $post Post array or post id.
 * @return string|int|false Formatted date string or Unix timestamp if $format is 'U' or 'G'. False on failure.
 */
function the_modified_date(string $format = '', $post = null)
{
    $post = get_post($post, true);

    if (!$post) {
        return false;
    }

    if ('' == $format) {
        $the_date = get_post_modified_date(
            (
            new Options(
                new OptionsMapper(
                    new Database(),
                    new HelperContext()
                )
            )
            )->{'read'}(
                'date_format'
            ),
            false,
            $post,
            true
        );
    } else {
        $the_date = get_post_modified_date($format, false, $post, true);
    }

    /**
     * Filters the date the post was modified.
     *
     * @since 0.9.9
     * @param string    $the_date The formatted date.
     * @param string    $format   Format to use for retrieving the date the post was modified.
     *                            Accepts 'G', 'U', or php date format value specified
     *                            in 'date_format' option. Default empty.
     * @param int|Post  $post     Post array or post id.
     */
    return hook::getInstance()->{'applyFilter'}('the_modified_date', $the_date, $format, $post);
}

/**
 * A function which retrieves TriTan CMS post modified time.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_modified_time(string $format = 'U', bool $gmt = false, $post = null, bool $translate = false)
{
    $post = get_post($post);
    $date = new \TriTan\Common\Date();

    if (!$post) {
        return false;
    }

    if ($gmt) {
        $time = $date->{'gmtdate'}($post->getModified());
    } else {
        $time = $post->getModified();
    }

    $time = $date->{'laci2Date'}($format, $time, $translate);
    /**
     * Filters the post modified time.
     *
     * @since 0.9.9
     *
     * @param string $time The post's formatted time.
     * @param bool $format Format to use for retrieving the time the post was modified.
     *                     Accepts 'G', 'U', or php date format. Default 'U'.
     * @param bool   $gmt  Whether to retrieve the GMT time. Default false.
     */
    return hook::getInstance()->{'applyFilter'}('get_post_modified_time', $time, $format, $gmt);
}

/**
 * Retrieves TriTan CMS post modified time.
 *
 * @since 0.9.9
 * @param string $format Format to use for retrieving the time the post was modified.
     *                   Accepts 'G', 'U', or php date format value specified
     *                   in 'time_format' option. Default empty.
 * @param int|Post $post Post array or post id.
 * @return string|int|false Formatted date string or Unix timestamp if $format is 'U' or 'G'. False on failure.
 */
function the_modified_time(string $format = '', $post = null)
{
    $post = get_post($post, true);

    if (!$post) {
        return false;
    }

    if ('' == $format) {
        $the_time = get_post_modified_time(
            (
            new Options(
                new OptionsMapper(
                    new Database(),
                    new HelperContext()
                )
            )
            )->{'read'}(
                'time_format'
            ),
            false,
            $post,
            true
        );
    } else {
        $the_time = get_post_modified_time($format, false, $post, true);
    }

    /**
     * Filters the time the post was modified.
     *
     * @since 0.9.9
     * @param string    $the_time The formatted time.
     * @param string    $format   Format to use for retrieving the time the post was modified.
     *                            Accepts 'G', 'U', or php date format value specified
     *                            in 'time_format' option. Default empty.
     * @param int|Post  $post     Post array or post id.
     */
    return hook::getInstance()->{'applyFilter'}('the_modified_time', $the_time, $format, $post);
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
function get_post_posttype_id(int $post_id = 0)
{
    $post = get_post_by('id', $post_id);
    $posttype_id = $post->getPosttypeId();
    /**
     * Filters the post posttype id.
     *
     * @since 0.9.9
     *
     * @param int   $posttype_id    The post's posttype id.
     * @param int   $post_id        The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_posttype_id', (int) $posttype_id, (int) $post_id);
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
function get_post_posttype(int $post_id = 0)
{
    $post = get_post_by('id', $post_id);
    $posttype = $post->getPosttype();
    /**
     * Filters the post posttype.
     *
     * @since 0.9.9
     *
     * @param string    $posttype   The post's posttype.
     * @param int       $post_id    The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_posttype', $posttype, (int) $post_id);
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
function get_post_parent_id(int $post_id = 0)
{
    $post = get_post_by('id', $post_id);
    $parent_id = $post->getParentId();
    /**
     * Filters the post parent id.
     *
     * @since 0.9.9
     *
     * @param int   $parent_id  The post's parent id.
     * @param int   $post_id    The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_parent_id', (int) $parent_id, (int) $post_id);
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
function get_post_parent(int $post_id = 0)
{
    $post = get_post_by('id', $post_id);
    $parent = $post->getParent();
    /**
     * Filters the post parent.
     *
     * @since 0.9.9
     *
     * @param string    $parent     The post's parent.
     * @param int       $post_id    The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_parent', $parent, (int) $post_id);
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
function get_post_sidebar(int $post_id = 0)
{
    $post = get_post_by('id', $post_id);
    $sidebar = $post->getSidebar();
    /**
     * Filters the post sidebar.
     *
     * @since 0.9.9
     *
     * @param int   $sidebar    The post's sidebar option.
     * @param int   $post_id    The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_sidebar', (int) $sidebar, (int) $post_id);
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
function get_post_show_in_menu(int $post_id = 0)
{
    $post = get_post_by('id', $post_id);
    $menu = $post->getShowInMenu();
    /**
     * Filters the post show in menu.
     *
     * @since 0.9.9
     *
     * @param int   $menu       The post's show in menu option.
     * @param int   $post_id    The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_show_in_menu', (int) $menu, (int) $post_id);
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
function get_post_show_in_search(int $post_id = 0)
{
    $post = get_post_by('id', $post_id);
    $search = $post->getShowInSearch();
    /**
     * Filters the post show in search.
     *
     * @since 0.9.9
     *
     * @param int   $search     The post's show in search option.
     * @param int   $post_id    The post ID.
     */
    return hook::getInstance()->{'applyFilter'}('post_show_in_search', (int) $search, (int) $post_id);
}

/**
 * Creates a unique post slug.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9.8
 * @param string $original_slug     Original slug of post.
 * @param string $original_title    Original title of post.
 * @param int|null $post_id         Unique post id or null.
 * @param string $post_type         Post type of post.
 * @return string Unique post slug.
 */
function ttcms_unique_post_slug(string $original_slug, string $original_title, $post_id, string $post_type)
{
    if (ttcms_post_slug_exist($post_id, $original_slug, $post_type)) {
        $post_slug = ttcms_slugify($original_title, 'post');
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
    return hook::getInstance()->{'applyFilter'}(
        'ttcms_unique_post_slug',
        $post_slug,
        $original_slug,
        $original_title,
        $post_id,
        $post_type
    );
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
function ttcms_insert_post(array $postdata, bool $exception = false)
{
    $user_id = get_current_user_id();
    $date = new \TriTan\Common\Date();

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

    $_postdata = ttcms()->obj['util']->{'parseArgs'}($postdata, $defaults);

    // Are we updating or creating?
    if (!empty($_postdata['post_id'])) {
        $update = true;
        $post_id = (int) $_postdata['post_id'];
        $post_before = get_post((int) $post_id);

        if (is_null($post_before)) {
            if ($exception) {
                throw new Exception(
                    esc_html__(
                        'Invalid post id.'
                    ),
                    'invalid_post_id'
                );
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
        hook::getInstance()->{'doAction'}('post_previous_status', $previous_status, (int) $post_id, $update);

        /**
         * Create new post object.
         */
        $post = new Post();
        $post->setId((int) $post_id);
    } else {
        $update = false;

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
        hook::getInstance()->{'doAction'}('post_previous_status', $previous_status, (int) $post_id, $update);

        /**
         * Create new post object.
         */
        $post = new Post();
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
    $sanitized_post_posttype = ttcms()->obj['sanitizer']->{'item'}($raw_post_posttype);
    /**
     * Filters a post's posttype before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_posttype Post posttype after it has been sanitized.
     * @param string $raw_post_posttype The post's post type.
     */
    $post_posttype = hook::getInstance()->{'applyFilter'}(
        'pre_post_posttype',
        $sanitized_post_posttype,
        $raw_post_posttype
    );
    $post->setPosttype($post_posttype);

    $raw_post_title = $post_title;
    $sanitized_post_title = ttcms()->obj['sanitizer']->{'item'}($raw_post_title);
    /**
     * Filters a post's title before created/updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_title Post title after it has been sanitized.
     * @param string $raw_post_title The post's title.
     */
    $post_title = hook::getInstance()->{'applyFilter'}(
        'pre_post_title',
        (string) $sanitized_post_title,
        (string) $raw_post_title
    );
    $post->setTitle($post_title);

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
        $post_slug = $post_before->getSlug();
    }

    $raw_post_slug = $post_slug;
    $sanitized_post_slug = ttcms()->obj['sanitizer']->{'item'}($raw_post_slug);
    /**
     * Filters a post's slug before created/updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_slug Post slug after it has been sanitized.
     * @param string $raw_post_slug The post's slug.
     */
    $post_slug = hook::getInstance()->{'applyFilter'}(
        'pre_post_slug',
        (string) $sanitized_post_slug,
        (string) $raw_post_slug
    );
    $post->setSlug($post_slug);

    $raw_post_content = $_postdata['post_content'];
    $sanitized_post_content = ttcms()->obj['sanitizer']->{'item'}($raw_post_content);
    /**
     * Filters a post's content before created/updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_content Post content after it has been sanitized.
     * @param string $raw_post_slug The post's slug.
     */
    $post_content = hook::getInstance()->{'applyFilter'}(
        'pre_post_content',
        $sanitized_post_content,
        $raw_post_content
    );
    $post->setContent($post_content);

    /**
     * Check for post author
     *
     * @since 0.9.9
     * @param int $post_author Post author id.
     */
    $post_author = (int) $_postdata['post_author'];

    if ($post_author <= 0 || $post_author === null) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'Post author cannot be zero or null.'
                ),
                'empty_post_author'
            );
        } else {
            return null;
        }
    }

    $post->setAuthor((int) $post_author);

    $raw_post_parent = $_postdata['post_parent'];
    $sanitized_post_parent = ttcms()->obj['sanitizer']->{'item'}($raw_post_parent);
    /**
     * Filters a post's parent before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_parent Post parent after it has been sanitized.
     * @param string $raw_post_parent The post's parent.
     */
    $post_parent = hook::getInstance()->{'applyFilter'}(
        'pre_post_parent',
        (string) $sanitized_post_parent,
        (string) $raw_post_parent
    );
    $post->setParent($post_parent);

    $raw_post_sidebar = $_postdata['post_sidebar'];
    $sanitized_post_sidebar = ttcms()->obj['sanitizer']->{'item'}($raw_post_sidebar, 'int');
    /**
     * Filters a post's sidebar before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_sidebar Post sidebar after it has been sanitized.
     * @param int $raw_post_sidebar The post's sidebar.
     */
    $post_sidebar = hook::getInstance()->{'applyFilter'}(
        'pre_post_sidebar',
        (int) $sanitized_post_sidebar,
        (int) $raw_post_sidebar
    );
    $post->setSidebar((int) $post_sidebar);

    $raw_post_show_in_menu = $_postdata['post_show_in_menu'];
    $sanitized_post_show_in_menu = ttcms()->obj['sanitizer']->{'item'}($raw_post_show_in_menu);
    /**
     * Filters a post's show in menu before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_show_in_menu Post show in menu after it has been sanitized.
     * @param int $raw_post_show_in_menu The post's show in menu.
     */
    $post_show_in_menu = hook::getInstance()->{'applyFilter'}(
        'pre_post_show_in_menu',
        (int) $sanitized_post_show_in_menu,
        (int) $raw_post_show_in_menu
    );
    $post->setShowInMenu((int) $post_show_in_menu);

    $raw_post_show_in_search = $_postdata['post_show_in_search'];
    $sanitized_post_show_in_search = ttcms()->obj['sanitizer']->{'item'}($raw_post_show_in_search, 'int');
    /**
     * Filters a post's show in search before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_show_in_search Post show in search after it has been sanitized.
     * @param int $raw_post_show_in_search The post's show in search.
     */
    $post_show_in_search = hook::getInstance()->{'applyFilter'}(
        'pre_post_show_in_search',
        (int) $sanitized_post_show_in_search,
        (int) $raw_post_show_in_search
    );
    $post->setShowInSearch((int) $post_show_in_search);

    $raw_post_relative_url = $post_posttype . '/' . $post_slug . '/';
    $sanitized_post_relative_url = ttcms()->obj['sanitizer']->{'item'}($raw_post_relative_url);
    /**
     * Filters a post's relative url before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_relative_url Post relative url after it has been sanitized.
     * @param string $raw_post_relative_url The post's relative url.
     */
    $post_relative_url = hook::getInstance()->{'applyFilter'}(
        'pre_post_relative_url',
        (string) $sanitized_post_relative_url,
        (string) $raw_post_relative_url
    );
    $post->setRelativeUrl($post_relative_url);

    $raw_post_featured_image = ttcms_optimized_image_upload($_postdata['post_featured_image']);
    $sanitized_post_featured_image = ttcms()->obj['sanitizer']->{'item'}($raw_post_featured_image);
    /**
     * Filters a post's featured image before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_featured_image Post featured image after it has been sanitized.
     * @param string $raw_post_featured_image The post's featured image.
     */
    $post_featured_image = hook::getInstance()->{'applyFilter'}(
        'pre_post_featured_image',
        (string) $sanitized_post_featured_image,
        (string) $raw_post_featured_image
    );
    $post->setFeaturedImage($post_featured_image);

    $raw_post_status = $_postdata['post_status'];
    $sanitized_post_status = ttcms()->obj['sanitizer']->{'item'}($raw_post_status);
    /**
     * Filters a post's status before the post is created or updated.
     *
     * @since 0.9.9
     * @param string $sanitized_post_status Post status after it has been sanitized.
     * @param string $raw_post_status The post's status.
     */
    $post_status = hook::getInstance()->{'applyFilter'}(
        'pre_post_status',
        (string) $sanitized_post_status,
        (string) $raw_post_status
    );
    $post->setStatus($post_status);

    /*
     * Filters whether the post is null.
     *
     * @since 0.9.9
     * @param bool  $maybe_empty Whether the post should be considered "null".
     * @param array $_postdata   Array of post data.
     */
    $maybe_null = !$post_title && !$post_content;
    if (hook::getInstance()->{'applyFilter'}('ttcms_insert_post_empty_content', $maybe_null, $_postdata)) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'The title and content are null'
                ),
                'empty_content'
            );
        } else {
            return null;
        }
    }

    if (!$update) {
        if (empty($_postdata['post_published']) || php_like('%0000-00-00 00:00', $_postdata['post_published'])) {
            $post_published = (string) $date->{'current'}('Y-m-d h:i A');
        } else {
            $post_published = (string) $date->{'laci2Date'}('Y-m-d h:i A', $_postdata['post_published']);
        }
    } else {
        $post_published = (string) $date->{'laci2Date'}('Y-m-d h:i A', $_postdata['post_published']);
    }

    $compacted = compact(
        'post_title',
        'post_slug',
        'post_content',
        'post_author',
        'post_posttype',
        'post_parent',
        'post_sidebar',
        'post_show_in_menu',
        'post_show_in_search',
        'post_relative_url',
        'post_featured_image',
        'post_status',
        'post_published'
    );
    $data = ttcms()->obj['util']->{'unslash'}($compacted);

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
    $data = hook::getInstance()->{'applyFilter'}(
        'ttcms_before_insert_post_data',
        $data,
        $update,
        $update ? (int) $post_id : null
    );

    if (!$update) {
        /**
         * Fires immediately before a post is inserted into the post document.
         *
         * @since 0.9.9
         * @param Post $post Post object.
         */
        hook::getInstance()->{'doAction'}('pre_post_insert', $post);

        $parent_id = 0 || null == $post->getParent() ? 0 : get_post_id($post->getParent());

        $posttype = get_posttype_by('posttype_slug', $post->getPosttype());
        $post->setPosttypeId((int) $posttype['posttype_id']);
        $post->setParentId($parent_id);
        $post->setCreated($date->{'format'}());
        $post->setPublished($post_published);

        $post_id = (
            new PostRepository(
                new PostMapper(
                    new Database(),
                    new HelperContext()
                )
            )
        )->{'insert'}($post);

        if (false === $post_id) {
            if ($exception) {
                throw new Exception(
                    esc_html__(
                        'Could not insert post into the post document.'
                    ),
                    'post_document_insert_error'
                );
            } else {
                return null;
            }
        }
    } else {
        /**
         * Fires immediately before an existing post is updated in the post document.
         *
         * @since 0.9.9
         * @param int   $post_id Post id.
         * @param Post  $post    Post object.
         */
        hook::getInstance()->{'doAction'}('pre_post_update', (int) $post_id, $post);

        $parent_id = 0 || null == $post->getParent() ? 0 : get_post_id($post->getParent());

        $posttype = get_posttype_by('posttype_slug', $post->getPosttype());
        $post->setPosttypeId((int) $posttype['posttype_id']);
        $post->setParentId($parent_id);
        $post->setPublished($post_published);
        $post->setModified($date->format());

        $post_id = (
            new PostRepository(
                new PostMapper(
                    new Database(),
                    new HelperContext()
                )
            )
        )->{'update'}($post);

        if (false === $post_id) {
            if ($exception) {
                throw new Exception(
                    esc_html__(
                        'Could not update post within the post document.'
                    ),
                    'post_document_update_error'
                );
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

    $post = get_post((int) $post_id, true);

    (new \TriTan\Common\Post\PostCache(
        ttcms()->obj['cache'],
        hook::getInstance()
    ))->{'clean'}($post);

    if ($update) {
        /**
         * Action hook triggered after existing post has been updated.
         *
         * @since 0.9.9
         * @param int   $post_id    Post id.
         * @param array $post       Post object.
         */
        hook::getInstance()->{'doAction'}('update_post', (int) $post_id, $post);
        $post_after = get_post((int) $post_id, true);
        /**
         * Action hook triggered after existing post has been updated.
         *
         * @since 0.9.9
         * @param int       $post_id      Post id.
         * @param object    $post_after   Post object following the update.
         * @param object    $post_before  Post object before the update.
         */
        hook::getInstance()->{'doAction'}('post_updated', (int) $post_id, $post_after, $post_before);
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
    hook::getInstance()->{'doAction'}("save_post_{$post_posttype}", (int) $post_id, $post, $update);

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
    hook::getInstance()->{'doAction'}("save_post_{$post_posttype}_{$post_status}", (int) $post_id, $post, $update);

    /**
     * Action hook triggered after post has been saved.
     *
     * @since 0.9.9
     * @param int   $post_id    The post's id.
     * @param array $post       Post object.
     * @param bool  $update     Whether this is an existing post or a new post.
     */
    hook::getInstance()->{'doAction'}('ttcms_after_insert_post_data', (int) $post_id, $post, $update);

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
function ttcms_update_post($postdata, bool $exception = false)
{
    if ($postdata instanceof \stdClass) {
        $postdata = get_object_vars($postdata);
    } elseif ($postdata instanceof Post) {
        $postdata = $postdata->toArray();
    }

    // First, get all of the original fields.
    $post = get_post((int) $postdata['post_id'], false);

    if (is_null($post)) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'Invalid post ID.'
                ),
                'invalid_post'
            );
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
 * @return bool|Post Post on success, false otherwise.
 */
function ttcms_delete_post(int $post_id = 0)
{
    $post = get_post($post_id);
    $db = new Database();

    if (!$post) {
        return false;
    }

    /**
     * Action hook fires before a post is deleted.
     *
     * @since 0.9.9
     * @param int $post_id Post id.
     */
    hook::getInstance()->{'doAction'}('before_delete_post', (int) $post_id);

    if (is_post_parent($post_id)) {
        foreach (is_post_parent($post_id) as $children) {
            $update_children = $db->table(c::getInstance()->get('tbl_prefix') . 'post');
            $update_children->begin();
            try {
                $update_children->where(
                    'post_attributes.parent.parent_id',
                    esc_html($children['post_attributes']['parent']['parent_id'])
                )
                ->where(
                    'post_attributes.parent.post_parent',
                    esc_html($children['post_attributes']['parent']['post_parent'])
                )
                ->update(
                    [
                        'post_attributes.parent.parent_id' => null,
                        'post_attributes.parent.post_parent' => null
                    ]
                );
                $update_children->commit();
            } catch (Exception $ex) {
                $update_children->rollback();
                Cascade::getLogger('error')->{'error'}(
                    sprintf(
                        'SQLSTATE[%s]: %s',
                        $ex->getCode(),
                        $ex->getMessage()
                    )
                );
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
    hook::getInstance()->{'doAction'}('delete_post', (int) $post_id);

    (new PostRepository(
        new PostMapper(
            new Database(),
            new HelperContext()
        )
    ))->{'delete'}($post);

    /**
     * Action hook fires immediately after a post is deleted from the post document.
     *
     * @since 0.9.9
     * @param int $post_id Post id.
     */
    hook::getInstance()->{'doAction'}('deleted_post', (int) $post_id);

    if (is_post_parent($post_id)) {
        foreach (is_post_parent($post_id) as $children) {
            ttcms()->obj['postcache']->{'clean'}((object) $children);
        }
    }

    /**
     * Action hook fires after a post is deleted.
     *
     * @since 0.9.9
     * @param int $post_id Post id.
     */
    hook::getInstance()->{'doAction'}('after_delete_post', (int) $post_id);

    return $post;
}

/**
 * Returns the number of posts within a given post type.
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @param string $slug Post type slug.
 * @return int
 */
function number_posts_per_type(string $slug) : int
{
    $count = (
        new PostRepository(
            new PostMapper(
                new Database(),
                new HelperContext()
            )
        )
    )->{'findByType'}($slug);
    return @count($count);
}

/**
 * Retrieves all posts
 *
 * @file app/functions/post-function.php
 *
 * @since 0.9
 * @access private
 * @param string $slug  Post slug.
 * @param int $post_id  Post id.
 * @return array
 */
function get_post_dropdown_list($slug = null, int $post_id = 0)
{
    $posts = (
        new \TriTan\Common\Post\PostRepository(
            new TriTan\Common\Post\PostMapper(
                new \TriTan\Database(),
                new TriTan\Common\Context\HelperContext()
            )
        )
    )->{'findAll'}();
    foreach ($posts as $post) {
        if ($post_id != $post->getId()) {
            echo '<option value="' . $post->getSlug() . '"' . selected($slug, $post->getSlug(), false) . '>' . $post->getTitle() . '</option>';
        }
    }
}

/**
 * Function which makes the post create view actionable. You can override this
 * is you decide to create your own view and re-design the screen and elements to
 * your liking.
 * 
 * @since 1.0
 * @param object $app Application object.
 * @param Posttype $posttype Posttype object.
 * @param int $post_count Post count.
 * @return mixed Returns the post create view.
 */
function ttcms_post_create_view($app, $posttype, $post_count)
{
    $app->foil->render(
        'main::admin/post/create',
        [
            'title' => esc_html__('Create') . ' ' . $posttype->getTitle(),
            'posttype_title' => $posttype->getTitle(),
            'posttype' => $posttype->getSlug(),
            'post_count' => (int) $post_count
        ]
    );
    return $app;
}

/**
 * Function which makes the post update view actionable. You can override this
 * is you decide to create your own view and re-design the screen and elements to
 * your liking.
 * 
 * @since 1.0
 * @param object $app Application object.
 * @param Posttype $posttype Posttype object.
 * @param Post $post Post object.
 * @return mixed Returns the post update view.
 */
function ttcms_post_update_view($app, $posttype, $post)
{
    $app->foil->render(
        'main::admin/post/update-post',
        [
            'title' => esc_html__('Update') . ' ' . $posttype->getTitle(),
            'posttype_title' => $posttype->getTitle(),
            'posttype' => $posttype->getSlug(),
            'post' => $post
        ]
    );
    return $app;
}
