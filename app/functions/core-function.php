<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * TriTan CMS Core Functions
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
define('CURRENT_RELEASE', _file_get_contents('RELEASE'));
define('REQUEST_TIME', app()->req->server['REQUEST_TIME']);
use TriTan\Config;
use TriTan\Exception\NotFoundException;
use TriTan\Exception\Exception;
use TriTan\Exception\IOException;
use Cascade\Cascade;
use Jenssegers\Date\Date;
use Respect\Validation\Validator as v;

/**
 * Retrieves TriTan CMS site root url.
 * 
 * Uses base_url filter.
 *
 * @since 0.9
 * @return string TriTan CMS root url.
 */
function get_base_url()
{
    $url = url('/');
    return app()->hook->{'apply_filter'}('base_url', $url);
}

/**
 * Custom make directory function.
 *
 * This function will check if the path is an existing directory,
 * if not, then it will be created with set permissions and also created
 * recursively if needed.
 *
 * @since 0.9
 * @param string $path
 *            Path to be created.
 * @return string
 * @throws IOException If session.savepath is not set, path is not writable, or
 * lacks permission to mkdir.
 */
function _mkdir($path)
{
    if ('' == _trim($path)) {
        $message = _t('Invalid directory path: Empty path given.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    if (session_save_path() == "") {
        throw new IOException(sprintf(_t('Session savepath is not set correctly. It is currently set to: %s', 'tritan-cms'), session_save_path()));
    }

    if (!is_writable(session_save_path())) {
        throw new IOException(sprintf(_t('"%s" is not writable or TriTan CMS does not have permission to create and write directories and files in this location.', 'tritan-cms'), session_save_path()));
    }

    if (!is_dir($path)) {
        if (!@mkdir($path, 0755, true)) {
            throw new IOException(sprintf(_t('The following directory could not be created: %s', 'tritan-cms'), $path));
        }
    }
}

/**
 * Displays the returned translated text.
 *
 * @since 0.9
 * @param type $msgid
 *            The translated string.
 * @param type $domain
 *            Domain lookup for translated text.
 * @return string Translated text according to current locale.
 */
function _t($msgid, $domain = '')
{
    if ($domain !== '') {
        return d__($domain, $msgid);
    } else {
        return d__('tritan-cms', $msgid);
    }
}

function get_path_info($relative)
{
    $base = basename(BASE_PATH);
    if (strpos(app()->req->server['REQUEST_URI'], DS . $base . $relative) === 0) {
        return $relative;
    } else {
        return app()->req->server['REQUEST_URI'];
    }
}

/**
 * Custom function to use curl, fopen, or use file_get_contents
 * if curl is not available.
 * 
 * Uses trigger_include_path_search, resource_context and stream_context_create_options
 * filters.
 *
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
    /**
     * Filter the boolean for include path.
     *
     * @since 0.9
     * @var bool $use_include_path
     * @return bool
     */
    $use_include_path = app()->hook->{'apply_filter'}('trigger_include_path_search', $use_include_path);

    /**
     * Filter the context resource.
     *
     * @since 0.9
     * @var bool $context
     * @return bool
     */
    $context = app()->hook->{'apply_filter'}('resource_context', $context);

    $opts = [
        'http' => [
            'timeout' => 360.0
        ]
    ];

    /**
     * Filters the stream context create options.
     *
     * @since 0.9
     * @param array $opts Array of options.
     * @return mixed
     */
    $opts = app()->hook->{'apply_filter'}('stream_context_create_options', $opts);

    if ($context === true) {
        $context = stream_context_create($opts);
    } else {
        $context = null;
    }

    $result = file_get_contents($filename, $use_include_path, $context);

    if ($result) {
        return $result;
    } else {
        $handle = fopen($filename, "r", $use_include_path, $context);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents) {
            return $contents;
        } else
        if (!function_exists('curl_init')) {
            return false;
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $filename);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 360);
            $output = curl_exec($ch);
            curl_close($ch);
            if ($output) {
                return $output;
            } else {
                return false;
            }
        }
    }
}

/**
 * Resize image function.
 * 
 * Example Usage:
 * 
 *      $size = getimagesize('static/assets/img/avatar.png');
 *      resize_image($size[0], $size[1], 80);
 *
 * @since 0.9
 * @param int $width Width of the image.
 * @param int $height Height of the image.
 * @param string $target Size of image.
 */
function resize_image($width, $height, $target)
{
    // takes the larger size of the width and height and applies the formula. Your function is designed to work with any image in any size.
    if ($width > $height) {
        $percentage = ($target / $width);
    } else {
        $percentage = ($target / $height);
    }

    // gets the new value and applies the percentage, then rounds the value
    $new_width = round($width * $percentage);
    $new_height = round($height * $percentage);
    // returns the new sizes in html image tag format...this is so you can plug this function inside an image tag so that it will set the image to the correct size, without putting a whole script into the tag.
    return 'width="' . $new_width . '" height="' . $new_height . '"';
}

/**
 * Turn all URLs into clickable links.
 * 
 * @since 0.9
 * @param string $value
 * @param array  $protocols  http/https, ftp, mail, twitter
 * @param array  $attributes
 * @param string $mode       normal or all
 * @return string
 */
function make_clickable($value, $protocols = ['http', 'mail'], array $attributes = [])
{
    // Link attributes
    $attr = '';
    foreach ($attributes as $key => $val) {
        $attr = ' ' . $key . '="' . htmlentities($val) . '"';
    }

    $links = [];

    // Extract existing links and tags
    $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
        return '<' . array_push($links, $match[1]) . '>';
    }, $value);

    // Extract text links for each protocol
    foreach ((array) $protocols as $protocol) {
        switch ($protocol) {
            case 'http':
            case 'https': $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
                    if ($match[1])
                        $protocol = $match[1];
                    $link = $match[2] ?: $match[3];
                    return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>';
                }, $value);
                break;
            case 'mail': $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) {
                    return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>';
                }, $value);
                break;
            case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) {
                    return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1] . "\">{$match[0]}</a>") . '>';
                }, $value);
                break;
            default: $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
                    return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>';
                }, $value);
                break;
        }
    }

    // Insert all link
    return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
        return $links[$match[1] - 1];
    }, $value);
}

function print_gzipped_page()
{
    global $HTTP_ACCEPT_ENCODING;
    if (headers_sent()) {
        $encoding = false;
    } elseif (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false) {
        $encoding = 'x-gzip';
    } elseif (strpos($HTTP_ACCEPT_ENCODING, 'gzip') !== false) {
        $encoding = 'gzip';
    } else {
        $encoding = false;
    }

    if ($encoding) {
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Encoding: ' . $encoding);
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        $size = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
        print($contents);
        exit();
    } else {
        ob_end_flush();
        exit();
    }
}

function percent($num_amount, $num_total)
{
    $count1 = $num_amount / $num_total;
    $count2 = $count1 * 100;
    $count = number_format($count2, 0);
    return $count;
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout TriTan CMS to allow for both string or array
 * to be merged into another array.
 *
 * @since 0.9
 * @param string|array $args
 *            Value to merge with $defaults
 * @param array $defaults
 *            Optional. Array that serves as the defaults. Default empty.
 * @return array Merged user defined values with defaults.
 */
function ttcms_parse_args($args, $defaults = '')
{
    if (is_object($args)) {
        $r = get_object_vars($args);
    } elseif (is_array($args)) {
        $r = $args;
    } else {
        ttcms_parse_str($args, $r);
    }

    if (is_array($defaults)) {
        return array_merge($defaults, $r);
    }

    return $r;
}

function head_release_meta()
{
    echo "<meta name='generator' content='TriTan CMS " . CURRENT_RELEASE . "'>\n";
}

function foot_release()
{
    echo CURRENT_RELEASE;
}

/**
 * Hashes a plain text password.
 *
 * @since 0.9
 * @param string $password
 *            Plain text password
 * @return mixed
 */
function ttcms_hash_password($password)
{
    if ('' == _trim($password)) {
        $message = _t('Invalid password: empty password given.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    // By default, use the portable hash from phpass
    $hasher = new TriTan\PasswordHash(8, false);

    return $hasher->HashPassword($password);
}

/**
 * Checks a plain text password against a hashed password.
 *
 * Uses check_password filter.
 * 
 * @since 0.9
 * @param string $password
 *            Plain test password.
 * @param string $hash
 *            Hashed password in the database to check against.
 * @param int $user_id
 *            User ID.
 * @return mixed
 */
function ttcms_check_password($password, $hash, $user_id = '')
{
    // If the hash is still md5...
    if (strlen($hash) <= 32) {
        $check = ($hash == md5($password));
        if ($check && $user_id) {
            // Rehash using new hash.
            ttcms_set_password($password, $user_id);
            $hash = ttcms_hash_password($password);
        }
        return app()->hook->{'apply_filter'}('check_password', $check, $password, $hash, $user_id);
    }

    // If the stored hash is longer than an MD5, presume the
    // new style phpass portable hash.
    $hasher = new TriTan\PasswordHash(8, false);

    $check = $hasher->CheckPassword($password, $hash);

    return app()->hook->{'apply_filter'}('check_password', $check, $password, $hash, $user_id);
}

/**
 * Prints a list of timezones which includes
 * current time.
 *
 * @return array
 */
function generate_timezone_list()
{
    static $regions = array(
        \DateTimeZone::AFRICA,
        \DateTimeZone::AMERICA,
        \DateTimeZone::ANTARCTICA,
        \DateTimeZone::ASIA,
        \DateTimeZone::ATLANTIC,
        \DateTimeZone::AUSTRALIA,
        \DateTimeZone::EUROPE,
        \DateTimeZone::INDIAN,
        \DateTimeZone::PACIFIC
    );

    $timezones = array();
    foreach ($regions as $region) {
        $timezones = array_merge($timezones, \DateTimeZone::listIdentifiers($region));
    }

    $timezone_offsets = array();
    foreach ($timezones as $timezone) {
        $tz = new \DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime());
    }

    // sort timezone by timezone name
    ksort($timezone_offsets);

    $timezone_list = array();
    foreach ($timezone_offsets as $timezone => $offset) {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

        $t = new \DateTimeZone($timezone);
        $c = new \DateTime(null, $t);
        $current_time = $c->format('g:i A');

        $timezone_list[$timezone] = "(${pretty_offset}) $timezone - $current_time";
    }

    return $timezone_list;
}

/**
 * Get age by birthdate.
 *
 * @since 0.9
 * @param string $birthdate
 *            User's birth date.
 * @return mixed
 */
function get_age($birthdate = '0000-00-00')
{
    $date = new Date($birthdate);
    $age = $date->age;

    if ($birthdate <= '0000-00-00' || $age == \Jenssegers\Date\Date::now()->format('Y')) {
        return _t('Unknown', 'tritan-cms');
    }
    return $age;
}

/**
 * Converts a string into unicode values.
 *
 * @since 0.9
 * @param string $string            
 * @return mixed
 */
function unicoder($string)
{
    $p = str_split(trim($string));
    $new_string = '';
    foreach ($p as $val) {
        $new_string .= '&#' . ord($val) . ';';
    }
    return $new_string;
}

/**
 * Returns the layout header information
 *
 * @since 0.9
 * @param
 *            string (optional) $layout_dir loads layouts from specified folder
 * @return mixed
 */
function get_layouts_header($layout_dir = '')
{
    $layouts_header = [];
    if ($handle = opendir($layout_dir)) {

        while ($file = readdir($handle)) {
            if (is_file($layout_dir . $file)) {
                if (strpos($layout_dir . $file, '.layout.php')) {
                    $fp = fopen($layout_dir . $file, 'r');
                    // Pull only the first 8kiB of the file in.
                    $layout_data = fread($fp, 8192);
                    fclose($fp);

                    preg_match('|Layout Name:(.*)$|mi', $layout_data, $name);
                    preg_match('|Layout Slug:(.*)$|mi', $layout_data, $layout_slug);

                    foreach (array(
                'name',
                'layout_slug'
                    ) as $field) {
                        if (!empty(${$field}))
                            ${$field} = trim(${$field}[1]);
                        else
                            ${$field} = '';
                    }
                    $layout_data = array(
                        'filename' => $file,
                        'Name' => $name,
                        'Title' => $name,
                        'Slug' => $layout_slug
                    );
                    $layouts_header[] = $layout_data;
                }
            } else
            if ((is_dir($layout_dir . $file)) && ($file != '.') && ($file != '..')) {
                get_layouts_header($layout_dir . $file . '/');
            }
        }

        closedir($handle);
    }
    return $layouts_header;
}

/**
 * Subdomain as directory function uses the subdomain
 * of the install as a directory.
 *
 * @since 0.9
 * @return string
 */
function subdomain_as_directory()
{
    $subdomain = '';
    $domain_parts = explode('.', app()->req->server['SERVER_NAME']);
    if (count($domain_parts) == 3) {
        $subdomain = $domain_parts[0];
    } else {
        $subdomain = 'www';
    }
    return $subdomain;
}

/**
 * Strips out all duplicate values and compact the array.
 *
 * @since 0.9
 * @param mixed $a
 *            An array that be compacted.
 * @return mixed
 */
function array_unique_compact($a)
{
    $tmparr = array_unique($a);
    $i = 0;
    foreach ($tmparr as $v) {
        $newarr[$i] = $v;
        $i ++;
    }
    return $newarr;
}

/**
 * Checks the mime type of a file.
 * 
 * @since 0.9
 * @param string $file  File to check.
 * @param int $mode     Perform a full check or extension check only.
 * @return bool
 */
function check_mime_type($file, $mode = 0)
{
    if ('' == _trim($file)) {
        $message = _t('Invalid file: empty file given.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    // mode 0 = full check
    // mode 1 = extension check only
    $mime_types = array(
        'txt' => 'text/plain',
        'csv' => 'text/plain',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        // adobe
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint'
    );

    $ext = strtolower(array_pop(explode('.', $file)));

    if (function_exists('mime_content_type') && $mode == 0) {
        $mimetype = mime_content_type($file);
        return $mimetype;
    }

    if (function_exists('finfo_open') && $mode == 0) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mimetype;
    } elseif (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    }
}

/**
 * Check whether variable is an TriTan CMS Error.
 *
 * Returns true if $object is an object of the \TriTan\Error class.
 *
 * @since 0.9
 * @param mixed $object
 *            Check if unknown variable is an \TriTan\Error object.
 * @return bool True, if \TriTan\Error. False, if not \TriTan\Error.
 */
function is_ttcms_error($object)
{
    return ($object instanceof \TriTan\Error);
}

/**
 * Check whether variable is an TriTan CMS Exception.
 *
 * Returns true if $object is an object of the `\TriTan\Exception\BaseException` class.
 *
 * @since 0.9
 * @param mixed $object
 *            Check if unknown variable is an `\TriTan\Exception\BaseException` object.
 * @return bool True, if `\TriTan\Exception\BaseException`. False, if not `\TriTan\Exception\BaseException`.
 */
function is_ttcms_exception($object)
{
    return ($object instanceof \TriTan\Exception\BaseException);
}

/**
 * Returns the datetime of when the content of file was changed.
 *
 * @since 0.9
 * @param string $file
 *            Absolute path to file.
 */
function file_mod_time($file)
{
    filemtime($file);
}

/**
 * Returns an array of function names in a file.
 *
 * @since 0.9
 * @param string $filename
 *            The path to the file.
 * @param bool $sort
 *            If true, sort results by function name.
 */
function get_functions_in_file($filename, $sort = false)
{
    $file = file($filename);
    $functions = [];
    foreach ($file as $line) {
        $line = trim($line);
        if (substr($line, 0, 8) == 'function') {
            $functions[] = strtolower(substr($line, 9, strpos($line, '(') - 9));
        }
    }
    if ($sort) {
        asort($functions);
        $functions = array_values($functions);
    }
    return $functions;
}

/**
 * Checks a given file for any duplicated named user functions.
 *
 * @since 0.9
 * @param string $filename            
 */
function is_duplicate_function($filename)
{
    if ('' == _trim($filename)) {
        $message = _t('Invalid file name: empty file name given.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    $plugin = get_functions_in_file($filename);
    $functions = get_defined_functions();
    $merge = array_merge($plugin, $functions['user']);
    if (count($merge) !== count(array_unique($merge))) {
        $dupe = array_unique(array_diff_assoc($merge, array_unique($merge)));
        foreach ($dupe as $value) {
            return new \TriTan\Error('duplicate_function_error', sprintf(_t('The following function is already defined elsewhere: <strong>%s</strong>', 'tritan-cms'), $value));
        }
    }
    return false;
}

/**
 * Performs a check within a php script and returns any other files
 * that might have been required or included.
 *
 * @since 0.9
 * @param string $filename
 *            PHP script to check.
 */
function ttcms_php_check_includes($filename)
{
    if ('' == _trim($filename)) {
        $message = _t('Invalid file name: empty file name given.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    // NOTE that any file coming into this function has already passed the syntax check, so
    // we can assume things like proper line terminations
    $includes = [];
    // Get the directory name of the file so we can prepend it to relative paths
    $dir = dirname($filename);

    // Split the contents of $fileName about requires and includes
    // We need to slice off the first element since that is the text up to the first include/require
    $requireSplit = array_slice(preg_split('/require|include/i', _file_get_contents($filename)), 1);

    // For each match
    foreach ($requireSplit as $string) {
        // Substring up to the end of the first line, i.e. the line that the require is on
        $string = substr($string, 0, strpos($string, ";"));

        // If the line contains a reference to a variable, then we cannot analyse it
        // so skip this iteration
        if (strpos($string, "$") !== false) {
            continue;
        }

        // Split the string about single and double quotes
        $quoteSplit = preg_split('/[\'"]/', $string);

        // The value of the include is the second element of the array
        // Putting this in an if statement enforces the presence of '' or "" somewhere in the include
        // includes with any kind of run-time variable in have been excluded earlier
        // this just leaves includes with constants in, which we can't do much about
        if ($include = $quoteSplit[1]) {
            // If the path is not absolute, add the dir and separator
            // Then call realpath to chop out extra separators
            if (strpos($include, ':') === false)
                $include = realpath($dir . DS . $include);

            array_push($includes, $include);
        }
    }

    return $includes;
}

/**
 * Performs a syntax and error check of a given PHP script.
 *
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
    // If file does not exist or it is not readable, throw an exception
    if (!is_file($filename) || !is_readable($filename)) {
        throw new NotFoundException(sprintf(_t('"%s" is not found or is not a regular file.', 'tritan-cms'), $filename));
    }

    $dupe_function = is_duplicate_function($filename);

    if (is_ttcms_error($dupe_function)) {
        return new \TriTan\Exception\Exception($dupe_function->get_error_message(), 'php_check_syntax');
    }

    // Sort out the formatting of the filename
    $file_name = realpath($filename);

    // Get the shell output from the syntax check command
    $output = shell_exec('php -l "' . $file_name . '"');

    // Try to find the parse error text and chop it off
    $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, - 1, $count);

    // If the error text above was matched, throw an exception containing the syntax error
    if ($count > 0) {
        return new \TriTan\Exception\Exception(trim($syntaxError), 'php_check_syntax');
    }

    // If we are going to check the files includes
    if ($check_includes) {
        foreach (ttcms_php_check_includes($file_name) as $include) {
            // Check the syntax for each include
            if (is_file($include)) {
                ttcms_php_check_syntax($include);
            }
        }
    }
}

/**
 * Validates a plugin and checks to make sure there are no syntax and/or
 * parsing errors.
 * 
 * Uses activate_plugin, activate_$plugin_name, and activated_plugin
 * actions hooks.
 *
 * @since 0.9
 * @param string $plugin_name
 *            Name of the plugin file (i.e. disqus.plugin.php).
 */
function ttcms_validate_plugin($plugin_name)
{
    $plugin = str_replace('.plugin.php', '', $plugin_name);

    if (!ttcms_file_exists(TTCMS_PLUGIN_DIR . $plugin . DS . $plugin_name, false)) {
        $file = TTCMS_PLUGIN_DIR . $plugin_name;
    } else {
        $file = TTCMS_PLUGIN_DIR . $plugin . DS . $plugin_name;
    }

    $error = ttcms_php_check_syntax($file);
    if (is_ttcms_exception($error)) {
        _ttcms_flash()->error(_t('Plugin could not be activated because it triggered a <strong>fatal error</strong>. <br /><br />', 'tritan-cms') . $error->getMessage());
        return false;
    }

    try {
        if (ttcms_file_exists($file)) {
            include_once ($file);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    /**
     * Fires before a specific plugin is activated.
     *
     * $pluginName refers to the plugin's
     * name (i.e. disqus.plugin.php).
     *
     * @since 0.9
     * @param string $plugin_name
     *            The plugin's base name.
     */
    app()->hook->{'do_action'}('activate_plugin', $plugin_name);

    /**
     * Fires as a specifig plugin is being activated.
     *
     * $pluginName refers to the plugin's
     * name (i.e. disqus.plugin.php).
     *
     * @since 0.9
     * @param string $plugin_name
     *            The plugin's base name.
     */
    app()->hook->{'do_action'}('activate_' . $plugin_name);

    /**
     * Activate the plugin if there are no errors.
     *
     * @since 0.9
     * @param string $plugin_name
     *            The plugin's base name.
     */
    activate_plugin($plugin_name);

    /**
     * Fires after a plugin has been activated.
     *
     * $pluginName refers to the plugin's
     * name (i.e. disqus.plugin.php).
     *
     * @since 0.9
     * @param string $plugin_name
     *            The plugin's base name.
     */
    app()->hook->{'do_action'}('activated_plugin', $plugin_name);
}

/**
 * Single file writable atribute check.
 * Thanks to legolas558.users.sf.net
 *
 * @since 0.9
 * @param string $path            
 * @return true
 */
function win_is_writable($path)
{
    // will work in despite of Windows ACLs bug
    // NOTE: use a trailing slash for folders!!!
    // see http://bugs.php.net/bug.php?id=27609
    // see http://bugs.php.net/bug.php?id=30931
    if ($path{strlen($path) - 1} == '/') { // recursively return a temporary file path
        return win_is_writable($path . uniqid(mt_rand()) . '.tmp');
    } elseif (is_dir($path)) {
        return win_is_writable($path . DS . uniqid(mt_rand()) . '.tmp');
    }
    // check tmp file for read/write capabilities
    $rm = ttcms_file_exists($path, false);
    $f = fopen($path, 'a');
    if ($f === false) {
        return false;
    }
    fclose($f);
    if (!$rm) {
        unlink($path);
    }
    return true;
}

/**
 * Alternative to PHP's native is_writable function due to a Window's bug.
 *
 * @since 0.9
 * @param string $path
 *            Path to check.
 */
function ttcms_is_writable($path)
{
    if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
        return win_is_writable($path);
    } else {
        return is_writable($path);
    }
}

/**
 * Takes an array and turns it into an object.
 *
 * @param array $array
 *            Array of data.
 */
function array_to_object(array $array)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = array_to_object($value);
        }
    }
    return (object) $array;
}

/**
 * Strip close comment and close php tags from file headers.
 *
 * @since 0.9
 * @param string $str
 *            Header comment to clean up.
 * @return string
 */
function _ttcms_cleanup_file_header_comment($str)
{
    return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}

/**
 * Retrieve metadata from a file.
 *
 * Searches for metadata in the first 8kB of a file, such as a plugin or layout.
 * Each piece of metadata must be on its own line. Fields can not span multiple
 * lines, the value will get cut at the end of the first line.
 *
 * If the file data is not within that first 8kB, then the author should correct
 * their plugin file and move the data headers to the top.
 *
 * @since 0.9
 * @param string $file
 *            Path to the file.
 * @param array $default_headers
 *            List of headers, in the format array('HeaderKey' => 'Header Name').
 * @param string $context
 *            Optional. If specified adds filter hook "extra_{$context}_headers".
 *            Default empty.
 * @return array Array of file headers in `HeaderKey => Header Value` format.
 */
function ttcms_get_file_data($file, $default_headers, $context = '')
{
    // We don't need to write to the file, so just open for reading.
    $fp = fopen($file, 'r');
    // Pull only the first 8kB of the file in.
    $file_data = fread($fp, 8192);
    // PHP will close file handle.
    fclose($fp);
    // Make sure we catch CR-only line endings.
    $file_data = str_replace("\r", "\n", $file_data);
    /**
     * Filter extra file headers by context.
     *
     * The dynamic portion of the hook name, `$context`, refers to
     * the context where extra headers might be loaded.
     *
     * @since 0.9
     *       
     * @param array $extra_context_headers
     *            Empty array by default.
     */
    if ($context && $extra_headers = app()->hook->{'apply_filter'}("extra_{$context}_headers", [])) {
        $extra_headers = array_combine($extra_headers, $extra_headers); // keys equal values
        $all_headers = array_merge($extra_headers, (array) $default_headers);
    } else {
        $all_headers = $default_headers;
    }
    foreach ($all_headers as $field => $regex) {
        if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1])
            $all_headers[$field] = _ttcms_cleanup_file_header_comment($match[1]);
        else
            $all_headers[$field] = '';
    }
    return $all_headers;
}

/**
 * Parses the plugin contents to retrieve plugin's metadata.
 *
 * The metadata of the plugin's data searches for the following in the plugin's
 * header. All plugin data must be on its own line. For plugin description, it
 * must not have any newlines or only parts of the description will be displayed
 * and the same goes for the plugin data. The below is formatted for printing.
 *
 * /*
 * Plugin Name: Name of Plugin
 * Plugin URI: Link to plugin information
 * Description: Plugin Description
 * Author: Plugin author's name
 * Author URI: Link to the author's web site
 * Version: Plugin version value.
 * Text Domain: Optional. Unique identifier, should be same as the one used in
 * load_plugin_textdomain()
 *
 * The first 8kB of the file will be pulled in and if the plugin data is not
 * within that first 8kB, then the plugin author should correct their plugin
 * and move the plugin data headers to the top.
 *
 * The plugin file is assumed to have permissions to allow for scripts to read
 * the file. This is not checked however and the file is only opened for
 * reading.
 *
 * @since 0.9
 *       
 * @param string $plugin_file
 *            Path to the plugin file
 * @param bool $markup
 *            Optional. If the returned data should have HTML markup applied.
 *            Default true.
 * @param bool $translate
 *            Optional. If the returned data should be translated. Default true.
 * @return array {
 *         Plugin data. Values will be empty if not supplied by the plugin.
 *        
 *         @type string $Name Name of the plugin. Should be unique.
 *         @type string $Title Title of the plugin and link to the plugin's site (if set).
 *         @type string $Description Plugin description.
 *         @type string $Author Author's name.
 *         @type string $AuthorURI Author's website address (if set).
 *         @type string $Version Plugin version.
 *         @type string $TextDomain Plugin textdomain.
 *         @type string $DomainPath Plugins relative directory path to .mo files.
 *         @type bool $Network Whether the plugin can only be activated network-wide.
 *         }
 */
function get_plugin_data($plugin_file, $markup = true, $translate = true)
{
    $default_headers = array(
        'Name' => 'Plugin Name',
        'PluginURI' => 'Plugin URI',
        'Version' => 'Version',
        'Description' => 'Description',
        'Author' => 'Author',
        'AuthorURI' => 'Author URI',
        'TextDomain' => 'Text Domain'
    );
    $plugin_data = ttcms_get_file_data($plugin_file, $default_headers, 'plugin');
    if ($markup || $translate) {
        $plugin_data = _get_plugin_data_markup_translate($plugin_file, $plugin_data, $markup, $translate);
    } else {
        $plugin_data['Title'] = $plugin_data['Name'];
        $plugin_data['AuthorName'] = $plugin_data['Author'];
    }
    return $plugin_data;
}

/**
 * A wrapper for htmLawed which is a set of functions
 * for html purifier
 *
 * @since 0.9
 * @param string $str            
 * @return mixed
 */
function _escape($t, $C = 1, $S = [])
{
    return htmLawed($t, $C, $S);
}

/**
 * Converts seconds to time format.
 * 
 * @since 0.9
 * @param numeric $seconds
 */
function ttcms_seconds_to_time($seconds)
{
    $ret = "";

    /** get the days */
    $days = intval(intval($seconds) / (3600 * 24));
    if ($days > 0) {
        $ret .= "$days days ";
    }

    /** get the hours */
    $hours = (intval($seconds) / 3600) % 24;
    if ($hours > 0) {
        $ret .= "$hours hours ";
    }

    /** get the minutes */
    $minutes = (intval($seconds) / 60) % 60;
    if ($minutes > 0) {
        $ret .= "$minutes minutes ";
    }

    /** get the seconds */
    $seconds = intval($seconds) % 60;
    if ($seconds > 0) {
        $ret .= "$seconds seconds";
    }

    return $ret;
}

/**
 * Checks whether a file or directory exists.
 * 
 * @since 0.9
 * @param string $filename Path to the file or directory.
 * @param bool $throw Determines whether to do a simple check or throw an exception.
 * @return boolean <b>true</b> if the file or directory specified by
 * <i>$filename</i> exists; <b>false</b> otherwise.
 * @throws NotFoundException If file does not exist.
 */
function ttcms_file_exists($filename, $throw = true)
{
    if (!file_exists($filename)) {
        if ($throw == true) {
            throw new NotFoundException(sprintf(_t('"%s" does not exist.', 'tritan-cms'), $filename));
        }
        return false;
    }
    return true;
}

/**
 * Add the template to the message body.
 *
 * Looks for {content} into the template and replaces it with the message.
 * 
 * Uses email_template filter hook.
 *
 * @since 0.9
 * @param string $body The message to templatize.
 * @return string $email The email surrounded by template.
 */
function set_email_template($body)
{
    $tpl = _file_get_contents(APP_PATH . 'views' . DS . '_layouts' . DS . 'system_email.tpl');

    $template = app()->hook->{'apply_filter'}('email_template', $tpl);

    return str_replace('{content}', $body, $template);
}

/**
 * Replace variables in the template.
 * 
 * Uses email_template_tags filter hook.
 *
 * @since 0.9
 * @param string $template Template with variables.
 * @return string Template with variables replaced.
 */
function template_vars_replacement($template)
{
    $var_array = [
        'site_name' => app()->hook->{'get_option'}('sitename'),
        'site_url' => get_base_url(),
        'site_description' => app()->hook->{'get_option'}('site_description'),
        'admin_email' => app()->hook->{'get_option'}('admin_email'),
        'date_format' => app()->hook->{'get_option'}('date_format'),
        'time_format' => app()->hook->{'get_option'}('time_format')
    ];

    $to_replace = app()->hook->{'apply_filter'}('email_template_tags', $var_array);

    foreach ($to_replace as $tag => $var) {
        $template = str_replace('{' . $tag . '}', $var, $template);
    }

    return $template;
}

/**
 * Process the HTML version of the text.
 * 
 * Uses email_template_body filter hook.
 *
 * @since 0.9
 * @param string $text
 * @param string $title
 * @return string
 */
function process_email_html($text, $title)
{
    // Convert URLs to links
    $links = make_clickable($text);

    // Add template to message
    $template = set_email_template($links);

    // Replace title tag with $title.
    $body = str_replace('{title}', $title, $template);

    // Replace variables in email
    $message = app()->hook->{'apply_filter'}('email_template_body', template_vars_replacement($body));

    return $message;
}

/**
 * Retrieve the domain name.
 * 
 * @since 0.9
 * @return string
 */
function get_domain_name()
{
    $server_name = strtolower(app()->req->server['SERVER_NAME']);
    if (substr($server_name, 0, 4) == 'www.') {
        $server_name = substr($server_name, 4);
    }
    return $server_name;
}

/**
 * SQL Like operator in PHP.
 * 
 * Returns true if match else false.
 * 
 * Example Usage:
 * 
 *      php_like('%uc%','Lucy'); //true
 *      php_like('%cy', 'Lucy'); //true
 *      php_like('lu%', 'Lucy'); //true
 *      php_like('%lu', 'Lucy'); //false
 *      php_like('cy%', 'Lucy'); //false
 * 
 * @since 0.9
 * @param string $pattern
 * @param string $subject
 * @return bool
 */
function php_like($pattern, $subject)
{
    $match = str_replace('%', '.*', preg_quote($pattern, '/'));
    return (bool) preg_match("/^{$match}$/i", $subject);
}

/**
 * Url shortening function.
 * 
 * @since 0.9
 * @param string $url URL
 * @param int $length Characters to check against.
 * @return string
 */
function ttcms_url_shorten($url, $length = 80)
{
    if (strlen($url) > $length) {
        $strlen = $length - 30;
        $first = substr($url, 0, $strlen);
        $last = substr($url, -15);
        $short_url = $first . "[ ... ]" . $last;
        return $short_url;
    } else {
        return $url;
    }
}

/**
 * Redirects to another page.
 * 
 * Uses ttcms_redirect and ttcms_redirect_status filter hooks.
 * 
 * @since 0.9
 * @param string $location The path to redirect to
 * @param int $status Status code to use
 * @return bool False if $location is not set
 */
function ttcms_redirect($location, $status = 302)
{
    /**
     * Filters the redirect location.
     *
     * @since 0.9
     *
     * @param string $location The path to redirect to.
     * @param int    $status   Status code to use.
     */
    $_location = app()->hook->{'apply_filter'}('ttcms_redirect', $location, $status);
    /**
     * Filters the redirect status code.
     *
     * @since 0.9
     *
     * @param int    $status   Status code to use.
     * @param string $_location The path to redirect to.
     */
    $_status = app()->hook->{'apply_filter'}('ttcms_redirect_status', $status, $_location);

    if (!$_location)
        return false;

    header("Location: $_location", true, $_status);
    return true;
}

/**
 * Retrieves a modified URL query string.
 * 
 * Uses query_arg_port filter hook.
 * 
 * @since 0.9
 * @param string $key A query variable key.
 * @param string $value A query variable value, or a URL to act upon.
 * @param string $url A URL to act upon.
 * @return string Returns modified url query string.
 */
function add_query_arg($key, $value, $url)
{
    $uri = parse_url($url);
    $query = isset($uri['query']) ? $uri['query'] : '';
    parse_str($query, $params);
    $params[$key] = $value;
    $query = http_build_query($params);
    $result = '';
    if ($uri['scheme']) {
        $result .= $uri['scheme'] . ':';
    }
    if ($uri['host']) {
        $result .= '//' . $uri['host'];
    }
    if ($uri['port']) {
        $result .= app()->hook->{'apply_filter'}('query_arg_port', ':' . $uri['port']);
    }
    if ($uri['path']) {
        $result .= $uri['path'];
    }
    if ($query) {
        $result .= '?' . $query;
    }
    return $result;
}

/**
 * Retrieves the login url.
 * 
 * Uses login_url filter hook.
 * 
 * @since 0.9
 * @param string $redirect Path to redirect to on log in.
 * @return string Returns the login url.
 */
function ttcms_login_url($redirect = '')
{
    $login_url = get_base_url() . 'login' . '/';

    if (!empty($redirect)) {
        $login_url = add_query_arg('redirect_to', $redirect, $login_url);
    }

    /**
     * Validates & protects redirect url against XSS attacks.
     * 
     * @since 0.9
     */
    if (!empty($redirect) && !validate_url($redirect)) {
        $login_url = get_base_url() . 'login' . '/';
    }
    /**
     * Filters the login URL.
     *
     * @since 0.9
     *
     * @param string $login_url    The login URL. Not HTML-encoded.
     * @param string $redirect     The path to redirect to on login, if supplied.
     */
    return app()->hook->{'apply_filter'}('login_url', $login_url, $redirect);
}

/**
 * Create a backup of TriTan CMS install.
 * 
 * @since 0.9
 * @param type $source Path/directory to zip.
 * @param type $destination Target for zipped file.
 * @return mixed
 */
function ttcms_system_backup($source, $destination)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new \ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true) {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                continue;

            $file = realpath($file);

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } else if (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), _file_get_contents($file));
            }
        }
    } else if (is_file($source) === true) {
        $zip->addFromString(basename($source), _file_get_contents($source));
    }

    return $zip->close();
}

/**
 * Used to retrieve values within a range.
 * 
 * @since 0.9
 * @param mixed $val
 * @param mixed $min
 * @param mixed $max
 * @return bool
 */
function ttcms_between($val, $min, $max)
{
    return ($val - $min) * ($val - $max) <= 0;
}

/**
 * Sort array of objects by field.
 *
 * Example Usage:
 *      
 *      ttcms_list_sort($post,'post_id','ASC', false);
 * 
 * @since 0.9
 * @param array $objects        Array of objects to sort.
 * @param string/array $orderby Name of field or array of fields to filter by.
 * @param string $order         (ASC|DESC)
 * @param bool $preserve_keys   Whether to preserve keys.
 * @return array Returns a sorted array.
 */
function ttcms_list_sort(&$objects, $orderby = [], $order = 'ASC', $preserve_keys = false)
{
    if (!is_array($objects)) {
        return [];
    }

    $util = new \TriTan\ListUtil($objects);
    return $util->sort($orderby, $order, $preserve_keys);
}

/**
 * Pluralizes a word if quantity is not one.
 *
 * Example Usage:
 * 
 *      ttcms_pluralize(4, 'cat'); // cats
 *      ttcms_pluralize(3, 'kitty'); // kitties
 *      ttcms_pluralize(2, 'octopus', 'octopii'); // octopii
 *      ttcms_pluralize(1, 'mouse', 'mice'); // mouse
 * 
 * @since 0.9
 * @param int $quantity     Number of items.
 * @param string $singular  Singular form of word.
 * @param string $plural    Plural form of word; function will attempt to deduce plural form from singular if not provided.
 * @return string Pluralized word if quantity is not one, otherwise singular.
 */
function ttcms_pluralize($quantity, $singular, $plural = null)
{
    if ($quantity == 1 || !strlen($singular)) {
        return $singular;
    }

    if ($plural !== null) {
        return $plural;
    }

    $last_letter = strtolower($singular[strlen($singular) - 1]);
    switch ($last_letter) {
        case 'y':
            return substr($singular, 0, -1) . 'ies';
        case 's':
            return $singular . 'es';
        default:
            return $singular . 's';
    }
}

/**
 * Validates a url.
 * 
 * Example Usage:
 * 
 *      if(validate_url('https://google.com/')) {
 *          //do something;
 *      }
 * 
 * @since 0.9
 * @param string $url Url to validate.
 * @return bool True if valid, false otherwise.
 */
function validate_url($url)
{
    return v::filterVar(FILTER_VALIDATE_URL)->validate($url);
}

/**
 * Navigates through an array, object, or scalar, and removes slashes from the values.
 * 
 * @since 0.9
 * @param mixed $value  The value to be stripped.
 * @return mixed Stripped value.
 */
function stripslashes_deep($value)
{
    $_value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);

    return $_value;
}

/**
 * This should be used to remove slashes from data passed to core API that
 * expects data to be unslashed.
 * 
 * @since 0.9
 * @param string|array String or array of strings to unslash.
 * @return string|array Unslashed value.
 */
function ttcms_unslash($value)
{
    return stripslashes_deep($value);
}

/**
 * Convert a value to non-negative integer.
 *
 * @since 0.9
 * @param mixed $maybeint   Data you wish to have converted to a non-negative integer.
 * @return int A non-negative integer.
 */
function absint($maybeint)
{
    return abs(intval($maybeint));
}

/**
 * Checks if a variable is null. If not null, check if integer or string.
 * 
 * @since 0.9
 * @param string|int $var   Variable to check.
 * @return string|int|null Returns null if empty otherwise a string or an integer.
 */
function if_null($var)
{
    $_var = ctype_digit($var) ? (int) $var : (string) $var;
    return $var === '' ? null : $_var;
}

/**
 * Sanitizes a string key.
 *
 * Keys are used as internal identifiers. Lowercase alphanumeric characters, dashes and underscores are allowed.
 * 
 * Uses sanitize_key filter hook.
 *
 * @since 0.9
 * @param string $key String key
 * @return string Sanitized key
 */
function sanitize_key($key)
{
    $raw_key = $key;
    $key = strtolower($key);
    $key = preg_replace('/[^a-z0-9_\-]/', '', $key);

    /**
     * Filters a sanitized key string.
     *
     * @since 0.9
     * @param string $key     Sanitized key.
     * @param string $raw_key The key prior to sanitization.
     */
    return app()->hook->{'apply_filter'}('sanitize_key', $key, $raw_key);
}

/**
 * Get an array that represents directory tree
 * 
 * @since 0.9
 * @param string $dir   Directory path
 * @param string $bool  Include sub directories
 */
function directory_listing($dir, $bool = "dirs")
{
    $truedir = $dir;
    $dir = scandir($dir);
    if ($bool == "files") { // dynamic function based on second pram
        $direct = 'is_dir';
    } elseif ($bool == "dirs") {
        $direct = 'is_file';
    }
    foreach ($dir as $k => $v) {
        if (($direct($truedir . $dir[$k])) || $dir[$k] == '.' || $dir[$k] == '..') {
            unset($dir[$k]);
        }
    }
    $dir = array_values($dir);
    return $dir;
}

/**
 * Get a list of themes available for a specific site.
 * 
 * @since 0.9
 * @param string $active The name to check against.
 * @return array Theme options to choose from.
 */
function get_site_themes($active = null)
{
    $themes = directory_listing(Config::get('theme_dir'));
    if (is_array($themes)) {
        foreach ($themes as $theme) {
            echo '<option value="' . $theme . '"' . selected($theme, $active, false) . '>' . $theme . '</option>';
        }
    }
}

/**
 * Determines if the server is running Apache.
 * 
 * @since 0.9
 * @return bool
 */
function is_apache()
{
    if (strpos(app()->req->server['SERVER_SOFTWARE'], 'Apache') !== false) {
        return true;
    }
}

/**
 * Whether the current request is for an administrative interface.
 * 
 * e.g. `/admin/`
 * 
 * @since 0.9
 * @return bool True if an admin screen, otherwise false.
 */
function is_admin()
{
    if (strpos(get_path_info('/admin'), "/admin") === 0) {
        return true;
    }
    return false;
}

/**
 * Determines if SSL is used.
 * 
 * Checks if base_url filter hook is present.
 *
 * @since 0.9
 * @return bool True if SSL, otherwise false.
 */
function is_ssl()
{
    if (isset(app()->req->server['HTTPS'])) {
        if ('on' == strtolower(app()->req->server['HTTPS'])) {
            return true;
        }
        if ('1' == app()->req->server['HTTPS']) {
            return true;
        }
    } elseif (isset(app()->req->server['SERVER_PORT']) && ( '443' == app()->req->server['SERVER_PORT'] )) {
        return true;
    }

    if (app()->hook->{'has_filter'}('base_url')) {
        return true;
    }
    return false;
}

/**
 * Enqueues stylesheets.
 * 
 * Example Usage:
 * 
 *      ttcms_enqueue_css('default', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css')
 *      ttcms_enqueue_css('plugin', ['fontawesome','select2-css'], false, plugin_basename( dirname(__FILE__) ))
 *      ttcms_enqueue_css('theme', 'style.css')
 * 
 * Uses default_css_pipeline, plugin_css_pipeline and theme_css_pipeline
 * filter hooks.
 * 
 * @since 0.9
 * @param string $config                Set whether to use `default` config or `plugin` config.
 * @param string|array $asset           Relative path to stylesheet(s) to enqueue.
 * @param bool $minify                  Set whether to minify asset or not.
 * @param string|null $plugin_slug      Plugin slug to set plugin's asset location
 * @return string Stylesheet asset(s).
 */
function ttcms_enqueue_css($config, $asset, $minify = false, $plugin_slug = null)
{
    if ($config === 'default') {
        $options = [
            'public_dir' => remove_trailing_slash(BASE_PATH),
            'css_dir' => 'static' . DS . 'assets' . DS . 'css',
            'pipeline' => app()->hook->{'apply_filter'}('default_css_pipeline', $minify),
            'pipeline_dir' => 'minify',
            'collections' => [
                'colorpicker-css' => 'bootstrap-colorpicker/bootstrap-colorpicker.min.css',
                'fontawesome' => '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
                'ionicons' => '//cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css',
                'datatables-css' => 'datatables/dataTables.bootstrap.css',
                'select2-css' => 'select2/select2.min.css',
                'datetimepicker-css' => 'bootstrap-datetimepicker/bootstrap-datetimepicker.min.css',
                'switchery-css' => 'bootstrap-switchery/switchery.min.css'
            ]
        ];
        $default = new Stolz\Assets\Manager($options);
        $default->reset()->add($asset);
    } elseif ($config === 'plugin') {
        $options = [
            'public_dir' => remove_trailing_slash(BASE_PATH),
            'css_dir' => 'plugins' . DS . $plugin_slug . DS . 'assets' . DS . 'css',
            'pipeline' => app()->hook->{'apply_filter'}('plugin_css_pipeline', $minify),
            'pipeline_dir' => 'minify'
        ];
        $default = new Stolz\Assets\Manager($options);
        $default->reset()->add($asset);
    } elseif ($config === 'theme') {
        $options = [
            'public_dir' => remove_trailing_slash(BASE_PATH),
            'css_dir' => 'private' . DS . 'sites' . DS . Config::get('site_id') . DS . 'themes' . DS . Config::get('active_theme') . DS . 'assets' . DS . 'css',
            'pipeline' => app()->hook->{'apply_filter'}('theme_css_pipeline', $minify),
            'pipeline_dir' => 'minify'
        ];
        $default = new Stolz\Assets\Manager($options);
        $default->reset()->add($asset);
    }
    echo $default->css();
}

/**
 * Enqueues javascript.
 * 
 * Example Usage:
 * 
 *      ttcms_enqueue_js('default', 'jquery-ui')
 *      ttcms_enqueue_js('plugin', 'select2-js', false, plugin_basename( dirname(__FILE__) ))
 *      ttcms_enqueue_js('theme', 'config.js')
 * 
 * Uses default_js_pipeline, plugin_js_pipeline and theme_js_pipeline
 * filter hooks.
 * 
 * @since 0.9
 * @param string $config            Set whether to use `default` config or `plugin` config.
 * @param string|array $asset       Javascript(s) to enqueue.
 * @param bool $minify              Set whether to minify asset or not.
 * @param string|null $plugin_slug  Plugin slug to set plugin's asset location.
 * @return string Javascript asset(s).
 */
function ttcms_enqueue_js($config, $asset, $minify = false, $plugin_slug = null)
{
    if ($config === 'default') {
        $options = [
            'public_dir' => remove_trailing_slash(BASE_PATH),
            'js_dir' => 'static' . DS . 'assets' . DS . 'js',
            'pipeline' => app()->hook->{'apply_filter'}('default_js_pipeline', $minify),
            'pipeline_dir' => 'minify',
            'collections' => [
                'jquery' => '//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.js',
                'jquery-ui' => [
                    'jquery',
                    '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'
                ],
                'colorpicker-js' => [
                    'bootstrap-colorpicker/bootstrap-colorpicker.min.js',
                    'bootstrap-colorpicker/config.js'
                ],
                'datatables-js' => [
                    'datatables/jquery.dataTables.min.js',
                    'datatables/dataTables.bootstrap.min.js',
                    'pages/datatable.js'
                ],
                'datetimepicker-js' => 'bootstrap-datetimepicker/bootstrap-datetimepicker.min.js',
                'select2-js' => [
                    'select2/select2.full.min.js',
                    'pages/select2.js'
                ],
                'switchery-js' => 'bootstrap-switchery/switchery.min.js'
            ]
        ];
        $default = new Stolz\Assets\Manager($options);
        $default->reset()->add($asset);
    } elseif ($config === 'plugin') {
        $options = [
            'public_dir' => remove_trailing_slash(BASE_PATH),
            'js_dir' => 'plugins' . DS . $plugin_slug . DS . 'assets' . DS . 'js',
            'pipeline' => app()->hook->{'apply_filter'}('plugin_js_pipeline', $minify),
            'pipeline_dir' => 'minify'
        ];
        $default = new Stolz\Assets\Manager($options);
        $default->reset()->add($asset);
    } elseif ($config === 'theme') {
        $options = [
            'public_dir' => remove_trailing_slash(BASE_PATH),
            'js_dir' => 'private' . DS . 'sites' . DS . Config::get('site_id') . DS . 'themes' . DS . Config::get('active_theme') . DS . 'assets' . DS . 'js',
            'pipeline' => app()->hook->{'apply_filter'}('theme_js_pipeline', $minify),
            'pipeline_dir' => 'minify'
        ];
        $default = new Stolz\Assets\Manager($options);
        $default->reset()->add($asset);
    }
    echo $default->js();
}

/**
 * Normalize a filesystem path.
 * 
 * @since 0.9
 * @param string $path Path to normalize.
 * @return string Normalized path.
 */
function ttcms_normalize_path($path)
{
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('|(?<=.)/+|', '/', $path);
    if (':' === substr($path, 1, 1)) {
        $path = ucfirst($path);
    }
    return $path;
}

/**
 * Properly strip all HTML tags including script and style
 *
 * This differs from PHP's native strip_tags() function because it removes the contents of
 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
 * will return 'something'. ttcms_strip_tags will return ''
 *
 * @since 0.9
 * @param string $string        String containing HTML tags
 * @param bool   $remove_breaks Optional. Whether to remove left over line breaks and white space chars
 * @return string The processed string.
 */
function ttcms_strip_tags($string, $remove_breaks = false)
{
    $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
    $string = strip_tags($string);

    if ($remove_breaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }

    return $string;
}

/**
 * Beautifies a filename for use.
 * 
 * Uses beautified_filename filter hook.
 * 
 * @since 0.9
 * @param string $filename Filename to beautify.
 * @return string Beautified filename.
 */
function beautify_filename($filename)
{
    $filename_raw = $filename;

    // reduce consecutive characters
    $filename = preg_replace([
        // "file   name.zip" becomes "file-name.zip"
        '/ +/',
        // "file___name.zip" becomes "file-name.zip"
        '/_+/',
        // "file---name.zip" becomes "file-name.zip"
        '/-+/'
            ], '-', $filename_raw);
    $filename = preg_replace([
        // "file--.--.-.--name.zip" becomes "file.name.zip"
        '/-*\.-*/',
        // "file...name..zip" becomes "file.name.zip"
        '/\.{2,}/'
            ], '.', $filename);

    /**
     * Filters a beautified filename.
     * 
     * @since 0.9
     * @param string $filename     Beautified filename.
     * @param string $filename_raw The filename prior to beautification.
     */
    $filename = app()->hook->{'apply_filter'}('beautified_filename', $filename, $filename_raw);

    // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
    $filename = mb_strtolower($filename, mb_detect_encoding($filename));
    // ".file-name.-" becomes "file-name"
    $filename = trim($filename, '.-');
    return $filename;
}

/**
 * Sanitizes a filename.
 * 
 * Uses sanitized_filename filter hook.
 * 
 * @since 0.9
 * @param string $filename  Name of file to sanitize.
 * @param bool $beautify    Whether or not to beautify the sanitized filename.
 * @return string Sanitized filename for use.
 */
function sanitize_filename($filename, $beautify = true)
{
    $filename_raw = $filename;
    // sanitize filename
    $filename = preg_replace(
            '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x', '-', $filename_raw);
    // avoids ".", ".." or ".hiddenFiles"
    $filename = ltrim($filename, '.-');
    // optional beautification
    if ($beautify) {
        $filename = beautify_filename($filename);
    }

    /**
     * Filters a sanitized filename.
     * 
     * @since 0.9
     * @param string $filename     Sanitized filename.
     * @param string $filename_raw The filename prior to sanitization.
     */
    $filename = app()->hook->{'apply_filter'}('sanitized_filename', $filename, $filename_raw);

    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
    return $filename;
}

/**
 * Generates a random password drawn from the defined set of characters.
 *
 * Uses random_lib library to create passwords with far less predictability.
 *
 * @since 0.9.7
 * @param int  $length              Optional. The length of password to generate. Default 12.
 * @param bool $special_chars       Optional. Whether to include standard special characters.
 *                                  Default true.
 * @param bool $extra_special_chars Optional. Whether to include other special characters.
 *                                  Default false.
 * @return string The system generated password.
 */
function ttcms_generate_password($length = 12, $special_chars = true, $extra_special_chars = false)
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    if ($special_chars) {
        $chars .= '!@#$%^&*()';
    }

    if ($extra_special_chars) {
        $chars .= '-_ []{}<>~`+=,.;:/?|';
    }

    $password = _ttcms_random_lib()->generate($length, $chars);

    /**
     * Filters the system generated password.
     *
     * @since 0.9.7
     * @param string $password The generated password.
     */
    return app()->hook->{'apply_filter'}('random_password', $password);
}
