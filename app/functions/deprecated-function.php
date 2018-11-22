<?php
/**
 * TriTan CMS Deprecated Functions
 *
 * @license GPLv3
 *
 * @since       0.9.8
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */

/**
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @param type $matches
 */
function clean_pre($matches)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::cleanPre");
    
    return (new TriTan\Common\Parsecode())->{'cleanPre'}($matches);
}

/**
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @global type $parsecode_tags
 * @param type $tag
 * @param type $func
 */
function add_parsecode($tag, $func)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::add");
    
    return (new TriTan\Common\Parsecode())->{'add'}($tag, $func);
}

/**
 * Removes hook for parsecode.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 * @param string $tag parsecode tag to remove hook for.
 */
function remove_parsecode($tag)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::remove");
    
    return (new TriTan\Common\Parsecode())->{'remove'}($tag);
}

/**
 * Clear all parsecodes.
 *
 * This function is simple, it clears all of the parsecode tags by replacing the
 * parsecodes global by a empty array. This is actually a very efficient method
 * for removing all parsecodes.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 */
function remove_all_parsecodes()
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::removeAll");
    
    return (new TriTan\Common\Parsecode())->{'removeAll'}();
}

/**
 * Search content for parsecodes and filter parsecodes through their hooks.
 *
 * If there are no parsecode tags defined, then the content will be returned
 * without any filtering. This might cause issues when plugins are disabled but
 * the parsecode will still show up in the post or content.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 * @uses get_parsecode_regex() Gets the search pattern for searching parsecodes.
 * @param string $content Content to search for parsecodes
 * @return string Content with parsecodes filtered out.
 */
function do_parsecode($content)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::doParsecode");
    
    return (new TriTan\Common\Parsecode())->{'doParsecode'}($content);
}

/**
 * Retrieve the parsecode regular expression for searching.
 *
 * The regular expression combines the parsecode tags in the regular expression
 * in a regex class.
 *
 * The regular expression contains 6 different sub matches to help with parsing.
 *
 * 1 - An extra [ to allow for escaping parsecodes with double [[]]
 * 2 - The parsecode name
 * 3 - The parsecode argument list
 * 4 - The self closing /
 * 5 - The content of a parsecode when it wraps some content.
 * 6 - An extra ] to allow for escaping parsecodes with double [[]]
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 * @return string The parsecode search regular expression
 */
function get_parsecode_regex()
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::getRegex");
    
    return (new TriTan\Common\Parsecode())->{'getRegex'}();
}

/**
 * Regular Expression callable for do_parsecode() for calling parsecode hook.
 * @see get_parsecode_regex for details of the match array contents.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @access private
 * @uses $parsecode_tags
 * @param array $m Regular expression match array
 * @return mixed False on failure.
 */
function do_parsecode_tag($m = 'deprecated')
{
    _deprecated_function(__FUNCTION__, '0.9.8');
}

/**
 * Retrieve all attributes from the parsecodes tag.
 *
 * The attributes list has the attribute name as the key and the value of the
 * attribute as the value in the key/value pair. This allows for easier
 * retrieval of the attributes, since all attributes have to be known.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @param string $text
 * @return array List of attributes and their value.
 */
function parsecode_parse_atts($text)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::parseAtts");
    
    return (new TriTan\Common\Parsecode())->{'parseAtts'}($text);
}

/**
 * Combine user attributes with known attributes and fill in defaults when needed.
 *
 * The pairs should be considered to be all of the attributes which are
 * supported by the caller and given as a list. The returned attributes will
 * only contain the attributes in the $pairs list.
 *
 * If the $atts list has unsupported attributes, then they will be ignored and
 * removed from the final returned list.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @param array $pairs Entire list of supported attributes and their defaults.
 * @param array $atts User defined attributes in parsecode tag.
 * @return array Combined and filtered attribute list.
 */
function parsecode_atts($pairs, $atts)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::atts");
    
    return (new TriTan\Common\Parsecode())->{'atts'}($pairs, $atts);
}

/**
 * Remove all parsecode tags from the given content.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 * @param string $content Content to remove parsecode tags.
 * @return string Content without parsecode tags.
 */
function strip_parsecodes($content)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::stripParsecodes");
    
    return (new TriTan\Common\Parsecode())->{'stripParsecodes'}($content);
}

/**
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @param type $m
 */
function strip_parsecode_tag($m)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::stripParsecodeTag");

    return (new TriTan\Common\Parsecode())->{'stripParsecodeTag'}($m);
}

/**
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @param unknown $pee
 * @param number $br
 * @return string|mixed
 */
function ttcms_autop($pee, $br = 1)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::autop");
    
    return (new TriTan\Common\Parsecode())->{'autop'}($pee, $br);
}

/**
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @param type $matches
 */
function _autop_newline_preservation_helper($matches)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::autopNewlinePreservationHelper");
    
    return (new TriTan\Common\Parsecode())->{'autopNewlinePreservationHelper'}($matches);
}

/**
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @global type $parsecode_tags
 * @param type $pee
 */
function parsecode_unautop($pee)
{
    _deprecated_function(__FUNCTION__, '0.9.8', "Parsecode::unAutop");
    
    return (new TriTan\Common\Parsecode())->{'unAutop'}($pee);
}

/**
 * Checks the permission of the logged in user.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @param string $perm Permission to check for.
 * @return bool Return true if permission matches or false otherwise.
 */
function hasPermission($perm)
{
    _deprecated_function(__FUNCTION__, '0.9.8', 'current_user_can');

    return current_user_can($perm);
}

/**
 * A function which retrieves a TriTan CMS post posttype slug.
 *
 * Purpose of this function is for the `post_posttype_slug`
 * filter.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param int $post_id The unique id of a post.
 * @return string
 */
function get_post_posttype_slug($post_id = 0)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'get_post_posttype');

    return get_post_posttype($post_id);
}

/**
 * Returns the datetime of when the content of file was changed.
 *
 * file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $file Absolute path to file.
 */
function file_mod_time($file)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'filemtime');
    filemtime($file);
}

/**
 * Retrieves TriTan CMS site root url.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @return string TriTan CMS root url.
 */
function get_base_url()
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'site_url');

    return site_url();
}

/**
 * Displays the returned translated text.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param type $msgid The translated string.
 * @param type $domain Domain lookup for translated text.
 * @return string Translated text according to current locale.
 */
function _t($msgid, $domain = '')
{
    _deprecated_function(__FUNCTION__, '0.9.9', 't__');

    return t__($msgid, $domain);
}

/**
 * @deprecated since release 0.9.9
 * @param type $relative
 */
function get_path_info($relative)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'Uri::getPathInfo');

    return (
        new \TriTan\Common\Uri(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'getPathInfo'}($relative);
}

/**
 * Custom function to use curl, fopen, or use file_get_contents
 * if curl is not available.
 *
 * Uses `trigger_include_path_search`, `resource_context` and `stream_context_create_options`
 * filters.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename
 *            Resource to read.
 * @param bool $use_include_path
 *            Whether or not to use include path.
 * @param bool $context
 *            Whether or not to use a context resource.
 */
function _file_get_contents($filename, $use_include_path = false, $context = true)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::getContents');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'getContents'}($filename, $use_include_path, $context);
}

/**
 * Subdomain as directory function uses the subdomain
 * of the install as a directory.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @return string
 */
function subdomain_as_directory()
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::subdomainAsDirectory');
    
    return (
        new TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'subdomainAsDirectory'}();
}

/**
 * Returns an array of function names in a file.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename
 *            The path to the file.
 * @param bool $sort
 *            If true, sort results by function name.
 */
function get_functions_in_file($filename, $sort = false)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::getFunctions');
     
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'getFunctions'}($filename, $sort);
}

/**
 * Checks a given file for any duplicated named user functions.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename
 */
function is_duplicate_function($filename)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::isDuplicateFunction');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'isDuplicateFunction'}($filename);
}

/**
 * Performs a check within a php script and returns any other files
 * that might have been required or included.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename
 *            PHP script to check.
 */
function ttcms_php_check_includes($filename)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::checkIncludes');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'checkIncludes'}($filename);
}

/**
 * Performs a syntax and error check of a given PHP script.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename
 *            PHP script/file to check.
 * @param bool $check_includes
 *            If set to true, will check if other files have been included.
 * @return void|\TriTan\Exception\Exception
 * @throws NotFoundException If file does not exist or is not readable.
 * @throws Exception If file contains duplicate function names.
 */
function ttcms_php_check_syntax($filename, $check_includes = true)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::checkSyntax');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'checkSyntax'}($filename, $check_includes);
}

/**
 * Single file writable atribute check.
 * Thanks to legolas558.users.sf.net
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $path
 * @return bool
 */
function win_is_writable($path)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::winIsWritable');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'winIsWritable'}($path);
}

/**
 * Alternative to PHP's native is_writable function due to a Window's bug.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $path Path to check.
 */
function ttcms_is_writable($path)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::isWritable');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'isWritable'}($path);
}

/**
 * A wrapper for htmLawed which is a set of functions
 * for html purifier
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $string
 * @return mixed
 */
function _escape($string)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'esc_html');
    
    return esc_html($string);
}

/**
 * Checks whether a file or directory exists.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename  Path to the file or directory.
 * @param bool $throw       Determines whether to do a simple check or throw an exception.
 *                          Default: true.
 * @return boolean <b>true</b> if the file or directory specified by
 * <i>$filename</i> exists; <b>false</b> otherwise.
 * @throws NotFoundException If file does not exist.
 */
function ttcms_file_exists($filename, $throw = true)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::exists');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'exists'}($filename, $throw);
}

/**
 * Get an array that represents directory tree.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $dir   Directory path
 * @param string $bool  Include sub directories
 */
function directory_listing($dir, $bool = "dirs")
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::directoryListing');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'directoryListing'}($dir, $bool);
}

/**
 * Normalize a filesystem path.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $path Path to normalize.
 * @return string Normalized path.
 */
function ttcms_normalize_path($path)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::normalizePath');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'normalizePath'}($path);
}

/**
 * Beautifies a filename for use.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename Filename to beautify.
 * @return string Beautified filename.
 */
function beautify_filename($filename)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::beautifyFilename');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'beautifyFilename'}($filename);
}

/**
 * Sanitizes a filename.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $filename  Name of file to sanitize.
 * @param bool $beautify    Whether or not to beautify the sanitized filename.
 * @return string Sanitized filename for use.
 */
function sanitize_filename($filename, $beautify = true)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::sanitizeFilename');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'sanitizeFilename'}($filename, $beautify);
}

/**
 * Sanitizes a url to be used safely.
 *
 * Example usage:
 *
 *      $url = "http://www.example.com/?message=test&<script>alert('XSS');</script>";
 *
 *      sanitize_url($url); //returns 'http://www.example.com/?message=test&38;';
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9.8
 * @param string $url   The url to be sanitized.
 * @param bool $encode  Whether url params should be encoded.
 * @return string The sanitized $url after the `sanitize_url` filter is applied.
 */
function sanitize_url($url, $encode = false)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'esc_url');
    
    return esc_url($url, ['http','https'], $encode);
}

/**
 * Turns multi-dimensional array into a regular array.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9.9
 * @param array $array The array to convert.
 * @return array
 */
function flatten_array($array)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'Utils::flattenArray');
    
    return (
        new \TriTan\Common\Utils(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'flattenArray'}($array);
}

/**
 * Removes directory recursively along with any files.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $dir Directory that should be removed.
 */
function _rmdir($dir)
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'FileSystem::rmdir');
    
    return (
        new \TriTan\Common\FileSystem(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'rmdir'}($dir);
}

/**
 * Sanitizes a string, or returns a fallback string.
 *
 * Specifically, HTML and PHP tags are stripped. Further actions can be added
 * via the plugin API. If $string is empty and $fallback_string is set, the latter
 * will be used.
 *
 * @file app/functions/deprecated-function.php
 *
 * @deprecated since release 0.9.9
 * @since 0.9
 * @param string $string          The string to be sanitized.
 * @param string $fallback_string Optional. A string to use if $string is empty.
 * @param string $context        Optional. The operation for which the string is sanitized
 * @return string The sanitized string.
 */
function ttcms_sanitize_string($string, $fallback_string = 'deprecated', $context = 'save')
{
    _deprecated_function(__FUNCTION__, '0.9.9', 'Sanitizer::item');
    
    return (
        new \TriTan\Common\Sanitizer(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'item'}($string, 'string', $context);
}

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
function auto_increment($table = 'deprecated', $pk = 'deprecated')
{
    _deprecated_function(__FUNCTION__, '0.9.9');
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
    _deprecated_function(__FUNCTION__, '0.9.9', 'MetaData::updateMetaDataCache');
    
    return (
        new TriTan\Common\MetaData(
            new TriTan\Database(),
            new TriTan\Common\Context\HelperContext()
        )
    )->{'updateMetaDataCache'}($meta_type, $array_ids);
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
function does_option_exist($option_key = 'deprecated')
{
    _deprecated_function(__FUNCTION__, '0.9.9');
}
