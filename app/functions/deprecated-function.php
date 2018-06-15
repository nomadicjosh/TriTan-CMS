<?php

namespace TriTan\Functions;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * TriTan CMS Deprecated Functions
 *  
 * @license GPLv3
 * 
 * @since       0.9.8
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$parsecode_tags = [];

/**
 * @deprecated since release 0.9.8
 * @param type $matches
 * @return type
 */
function clean_pre($matches)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'clean_pre']);

    return app()->hook->{'clean_pre'}($matches);
}

/**
 * @deprecated since release 0.9.8
 * @global type $parsecode_tags
 * @param type $tag
 * @param type $func
 * @return type
 */
function add_parsecode($tag, $func)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'add_parsecode']);

    return app()->hook->{'add_parsecode'}($tag, $func);
}

/**
 * Removes hook for parsecode.
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 * @param string $tag parsecode tag to remove hook for.
 */
function remove_parsecode($tag)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'remove_parsecode']);

    return app()->hook->{'remove_parsecode'}($tag);
}

/**
 * Clear all parsecodes.
 *
 * This function is simple, it clears all of the parsecode tags by replacing the
 * parsecodes global by a empty array. This is actually a very efficient method
 * for removing all parsecodes.
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 */
function remove_all_parsecodes()
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'remove_all_parsecodes']);

    return app()->hook->{'remove_all_parsecodes'}();
}

/**
 * Search content for parsecodes and filter parsecodes through their hooks.
 *
 * If there are no parsecode tags defined, then the content will be returned
 * without any filtering. This might cause issues when plugins are disabled but
 * the parsecode will still show up in the post or content.
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
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'do_parsecode']);

    return app()->hook->{'do_parsecode'}($content);
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
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 * @return string The parsecode search regular expression
 */
function get_parsecode_regex()
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'get_parsecode_regex']);

    return app()->hook->{'get_parsecode_regex'}();
}

/**
 * Regular Expression callable for do_parsecode() for calling parsecode hook.
 * @see get_parsecode_regex for details of the match array contents.
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @access private
 * @uses $parsecode_tags
 * @param array $m Regular expression match array
 * @return mixed False on failure.
 */
function do_parsecode_tag($m)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'do_parsecode_tag']);

    return app()->hook->{'do_parsecode_tag'}($m);
}

/**
 * Retrieve all attributes from the parsecodes tag.
 *
 * The attributes list has the attribute name as the key and the value of the
 * attribute as the value in the key/value pair. This allows for easier
 * retrieval of the attributes, since all attributes have to be known.
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @param string $text
 * @return array List of attributes and their value.
 */
function parsecode_parse_atts($text)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'parsecode_parse_atts']);

    return app()->hook->{'parsecode_parse_atts'}($text);
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
 * @deprecated since release 0.9.8
 * @since 0.9
 * @param array $pairs Entire list of supported attributes and their defaults.
 * @param array $atts User defined attributes in parsecode tag.
 * @return array Combined and filtered attribute list.
 */
function parsecode_atts($pairs, $atts)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'parsecode_atts']);

    return app()->hook->{'parsecode_atts'}($pairs, $atts);
}

/**
 * Remove all parsecode tags from the given content.
 *
 * @deprecated since release 0.9.8
 * @since 0.9
 * @uses $parsecode_tags
 * @param string $content Content to remove parsecode tags.
 * @return string Content without parsecode tags.
 */
function strip_parsecodes($content)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'strip_parsecodes']);

    return app()->hook->{'strip_parsecodes'}($content);
}

/**
 * @deprecated since release 0.9.8
 * @param type $m
 * @return type
 */
function strip_parsecode_tag($m)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'strip_parsecode_tag']);

    return app()->hook->{'strip_parsecode_tag'}($m);
}

/**
 * @deprecated since release 0.9.8
 * @since 0.9
 * @param unknown $pee
 * @param number $br
 * @return string|mixed
 */
function ttcms_autop($pee, $br = 1)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'ttcms_autop']);

    return app()->hook->{'ttcms_autop'}($pee, $br);
}

/**
 * @deprecated since release 0.9.8
 * @param type $matches
 * @return type
 */
function _autop_newline_preservation_helper($matches)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), '_autop_newline_preservation_helper']);

    return app()->hook->{'_autop_newline_preservation_helper'}($matches);
}

/**
 * @deprecated since release 0.9.8
 * @global type $parsecode_tags
 * @param type $pee
 * @return type
 */
function parsecode_unautop($pee)
{
    _deprecated_function(__FUNCTION__, '0.9.8', [new \TriTan\Hooks(), 'parsecode_unautop']);

    return app()->hook->{'parsecode_unautop'}($pee);
}

/**
 * Checks the permission of the logged in user.
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
