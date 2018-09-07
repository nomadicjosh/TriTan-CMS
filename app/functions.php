<?php
use TriTan\Container as c;
use TriTan\Common\Escape;
use TriTan\Common\Hooks\ActionFilterHook as hook;

/**
 * TriTan CMS Dependency Injection, Wrappers, and other functions.
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Call the application global scope.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @return object
 */
function app()
{
    $app = \Liten\Liten::getInstance();
    return $app;
}

function ttcms()
{
    return TriTan\Container::getInstance()->get('context');
}

/**
 * Hooks global scope.
 */
app()->inst->singleton('hook', function () {
    return new \TriTan\Hooks();
});

/**
 * Form global scope.
 */
app()->inst->singleton('form', function () {
    return new AdamWathan\Form\FormBuilder();
});

/**
 * Displays the returned translated text.
 *
 * @file app/functions/core-function.php
 *
 * @since 0.9
 * @param type $msgid The translated string.
 * @param type $domain Domain lookup for translated text.
 * @return string Translated text according to current locale.
 */
function t__($msgid, $domain = '')
{
    return (new TriTan\Common\TextDomain(
        new \TriTan\Common\Options\Options(
            new TriTan\Common\Options\OptionsMapper(
                new \TriTan\Database(),
                new TriTan\Common\Context\HelperContext()
            )
        ),
        hook::getInstance()
    ))->{'t__'}($msgid, $domain);
}

/**
 * Set Error Log for Debugging.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string|array $value The data to be catched.
 */
function ttcms_error_log($value)
{
    if (is_array($value) || is_object($value)) {
        error_log(var_export($value, true));
    } else {
        error_log($value);
    }
}

/**
 * Returns the url based on route.
 *
 * @file app/functions.php
 *
 * @since 0.9
 */
function url($route)
{
    $app = \Liten\Liten::getInstance();
    $url = $app->req->url_for($route);
    return $url;
}

/**
 *
 * @file app/functions.php
 *
 * @since 0.9
 */
function time_ago($original)
{
    return (
        new \TriTan\Common\Date()
    )->{'timeAgo'}($original);
}

/**
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @return bool
 */
function remote_file_exists($url)
{
    $curl = curl_init($url);
    //don't fetch the actual page, you only want to check the connection is ok
    curl_setopt($curl, CURLOPT_NOBODY, true);
    //do request
    $result = curl_exec($curl);
    $ret = false;
    //if request did not fail
    if ($result !== false) {
        //if request was ok, check response code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($statusCode == 200) {
            $ret = true;
        }
    }
    curl_close($curl);
    return $ret;
}

/**
 * Return the file extension of the given filename.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param  string $filename
 * @return string
 */
function get_file_ext($filename)
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

/**
 * Truncate a string to a specified length without cutting a word off
 *
 * @file app/functions.php
 *
 * @since  0.9
 * @param   string  $string  The string to truncate
 * @param   int     $length  The length to truncate the string to
 * @param   string  $append  Text to append to the string IF it gets
 *                           truncated, defaults to '...'
 * @return  string
 *
 * @access  public
 */
function safe_truncate($string, $length, $append = '...')
{
    $ret = substr($string, 0, $length);
    $last_space = strrpos($ret, ' ');

    if ($last_space !== false && $string != $ret) {
        $ret = substr($ret, 0, $last_space);
    }

    if ($ret != $string) {
        $ret .= $append;
    }

    return $ret;
}

/**
 * Special function for files including
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $file
 * @param bool $once
 * @param bool|Closure $show_errors If bool error will be processed, if Closure - only Closure will be called
 *
 * @return bool
 */
function _require($file, $once = false, $show_errors = true)
{
    if (file_exists($file)) {
        if ($once) {
            return require_once $file;
        } else {
            return require $file;
        }
    } elseif (is_bool($show_errors) && $show_errors) {
        $data = debug_backtrace()[0];
        trigger_error("File $file does not exists in $data[file] on line $data[line]", E_USER_ERROR);
    } elseif ($show_errors instanceof \Closure) {
        return (bool) $show_errors();
    }
    return false;
}

/**
 * Special function for files including
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $file
 * @param bool $once
 * @param bool|Closure $show_errors If bool error will be processed, if Closure - only Closure will be called
 *
 * @return bool
 */
function _include($file, $once = false, $show_errors = true)
{
    if (file_exists($file)) {
        if ($once) {
            return include_once $file;
        } else {
            return include $file;
        }
    } elseif (is_bool($show_errors) && $show_errors) {
        $data = debug_backtrace()[0];
        trigger_error("File $file does not exists in $data[file] on line $data[line]", E_USER_WARNING);
    } elseif ($show_errors instanceof \Closure) {
        return (bool) $show_errors();
    }
    return false;
}

/**
 * Special function for files including
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $file
 * @param bool|Closure $show_errors If bool error will be processed, if Closure - only Closure will be called
 *
 * @return bool
 */
function _require_once($file, $show_errors = true)
{
    return _require($file, true, $show_errors);
}

/**
 * Special function for files including
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $file
 * @param bool|Closure $show_errors If bool error will be processed, if Closure - only Closure will be called
 *
 * @return bool
 */
function _include_once($file, $show_errors = true)
{
    return _include($file, true, $show_errors);
}

/**
 * Removes all whitespace.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $str
 * @return mixed
 */
function _trim($str)
{
    return (
        new \TriTan\Common\Utils(
            hook::getInstance()
        )
    )->{'trim'}($str);
}

/**
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $file Filepath
 * @param int $digits Digits to display
 * @return string|bool Size (KB, MB, GB, TB) or boolean
 */
function get_file_size($file, $digits = 2)
{
    if (is_file($file)) {
        $fileSize = filesize($file);
        $sizes = ["TB", "GB", "MB", "KB", "B"];
        $total = count($sizes);
        while ($total-- && $fileSize > 1024) {
            $fileSize /= 1024;
        }
        return round($fileSize, $digits) . " " . $sizes[$total];
    }
    return false;
}

if (!function_exists('hash_equals')) {

    /**
     * Timing attack safe string comparison
     *
     * Compares two strings using the same time whether they're equal or not.
     * This function should be used to mitigate timing attacks; for instance, when testing crypt() password hashes.
     *
     * @file app/functions.php
     *
     * @since 0.9
     * @param string $known_string The string of known length to compare against
     * @param string $user_string The user-supplied string
     * @return boolean Returns TRUE when the two strings are equal, FALSE otherwise.
     */
    function hash_equals($known_string, $user_string)
    {
        if (func_num_args() !== 2) {
            // handle wrong parameter count as the native implentation
            trigger_error(
                'hash_equals() expects exactly 2 parameters, ' . func_num_args() . ' given',
                E_USER_WARNING
            );
            return null;
        }
        if (is_string($known_string) !== true) {
            trigger_error(
                'hash_equals(): Expected known_string to be a string, ' . gettype($known_string) . ' given',
                E_USER_WARNING
            );
            return false;
        }
        $known_string_len = strlen($known_string);
        $user_string_type_error = 'hash_equals(): Expected user_string to be a string, ' . gettype($user_string) . ' given'; // prepare wrong type error message now to reduce the impact of string concatenation and the gettype call
        if (is_string($user_string) !== true) {
            trigger_error($user_string_type_error, E_USER_WARNING);
            // prevention of timing attacks might be still possible if we handle $user_string as a
            // string of diffent length (the trigger_error() call increases the execution time a bit)
            $user_string_len = strlen($user_string);
            $user_string_len = $known_string_len + 1;
        } else {
            $user_string_len = $known_string_len + 1;
            $user_string_len = strlen($user_string);
        }
        if ($known_string_len !== $user_string_len) {
            // use $known_string instead of $user_string to handle strings of diffrent length.
            $res = $known_string ^ $known_string;
            $ret = 1; // set $ret to 1 to make sure false is returned
        } else {
            $res = $known_string ^ $user_string;
            $ret = 0;
        }
        for ($i = strlen($res) - 1; $i >= 0; $i--) {
            $ret |= ord($res[$i]);
        }
        return $ret === 0;
    }
}

/**
 * Outputs the html checked attribute.
 *
 * Compares the first two arguments and if identical marks as checked
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param mixed $checked One of the values to compare
 * @param mixed $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @return string html attribute or empty string
 */
function checked($checked, $current = true, $echo = true)
{
    return checked_selected_helper($checked, $current, $echo, 'checked');
}

/**
 * Outputs the html selected attribute.
 *
 * Compares the first two arguments and if identical marks as selected
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param mixed $selected One of the values to compare
 * @param mixed $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @return string html attribute or empty string
 */
function selected($selected, $current = true, $echo = true)
{
    return checked_selected_helper($selected, $current, $echo, 'selected');
}

/**
 * Outputs the html disabled attribute.
 *
 * Compares the first two arguments and if identical marks as disabled
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param mixed $disabled One of the values to compare
 * @param mixed $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @return string html attribute or empty string
 */
function disabled($disabled, $current = true, $echo = true)
{
    return checked_selected_helper($disabled, $current, $echo, 'disabled');
}

/**
 * Private helper function for checked, selected, and disabled.
 *
 * Compares the first two arguments and if identical marks as $type
 *
 * @access private
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param any $helper One of the values to compare
 * @param any $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @param string $type The type of checked|selected|disabled we are doing
 * @return string html attribute or empty string
 */
function checked_selected_helper($helper, $current, $echo, $type)
{
    if ($helper === $current) {
        $result = " $type='$type'";
    } else {
        $result = '';
    }

    if ($echo) {
        echo $result;
    }

    return $result;
}

/**
 * Concatenation with separator.
 *
 * @since 0.9
 * @param string $separator Delimeter to used between strings.
 * @param type $string1 Left string.
 * @param type $string2 Right string.
 * @return type
 */
function concat_ws($separator, $string1, $string2)
{
    if (null == $separator) {
        $separator = ',';
    }
    return $string1 . $separator . $string2;
}

/**
 * Wrapper function for the core PHP function: trigger_error.
 *
 * This function makes the error a little more understandable for the
 * end user to track down the issue.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $message
 *            Custom message to print.
 * @param string $level
 *            Predefined PHP error constant.
 */
function _trigger_error($message, $level = E_USER_NOTICE)
{
    $debug = debug_backtrace();
    $caller = next($debug);
    echo '<div class="alerts alerts-error center">';
    trigger_error(
        $message . ' used <strong>' . $caller['function'] . '()</strong> called from <strong>' .
        $caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong>' . "\n<br />error handler",
        $level
    );
    echo '</div>';
}

/**
 * Returns false.
 *
 * Apply to filters to return false.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @return bool False
 */
function __return_false()
{
    return false;
}

/**
 * Returns true.
 *
 * Apply to filters to return true.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @return bool True
 */
function __return_true()
{
    return true;
}

/**
 * Returns null.
 *
 * Apply to filters to return null.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @return bool NULL
 */
function __return_null()
{
    return null;
}

/**
 * Return posted data if set.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param mixed $post
 * @return mixed
 */
function __return_post($post)
{
    return isset(app()->req->post[$post]) ? app()->req->post[$post] : '';
}

/**
 * Wrapper function for PluginFile::basename() method and
 * extracts the file name of a specific plugin.
 *
 * @see PluginFile::basename()
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $filename Plugin's file name.
 */
function plugin_basename($filename)
{
    return (
        new TriTan\Common\Plugin\PluginFile(
            new \TriTan\Common\FileSystem(
                hook::getInstance()
            )
        )
    )->{'basename'}($filename);
}

/**
 * Wrapper function for PluginRegisterActivationHook::activation() method.
 * When a plugin is activated, the action `activate_pluginname` hook is called.
 * `pluginname` is replaced by the actually file name of the plugin being activated.
 * So if the plugin is located at 'plugin/sample/sample.plugin.php', then the hook will
 * call 'activate_sample.plugin.php'.
 *
 * @see PluginRegisterActivationHook::activation()
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $filename Plugin's filename.
 * @param string $function The function that should be triggered by the hook.
 */
function register_activation_hook($filename, $function)
{
    return (
        new TriTan\Common\Plugin\PluginRegisterActivationHook(
            hook::getInstance()
        )
    )->{'activation'}($filename, $function);
}

/**
 * Wrapper function for PluginRegisterActivationHook::deactivation() method.
 *
 * When a plugin is deactivated, the action `deactivate_pluginname` hook is called.
 * `pluginname` is replaced by the actually file name of the plugin being deactivated.
 * So if the plugin is located at 'plugin/sample/sample.plugin.php', then the hook will
 * call 'deactivate_sample.plugin.php'.
 *
 * @see PluginRegisterActivationHook::deactivation()
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $filename Plugin's filename.
 * @param string $function The function that should be triggered by the hook.
 */
function register_deactivation_hook($filename, $function)
{
    return (
        new TriTan\Common\Plugin\PluginRegisterActivationHook(
            hook::getInstance()
        )
    )->{'deactivation'}($filename, $function);
}

/**
 * Wrapper function for PluginFile::path() method.
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @see PluginFile::path()
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $filename The filename of the plugin (__FILE__).
 * @return string The filesystem path of the directory that contains the plugin.
 */
function plugin_dir_path($filename)
{
    return (
        new TriTan\Common\Plugin\PluginFile(
            new TriTan\Common\FileSystem(
                hook::getInstance()
            )
        )
    )->{'path'}($filename);
}

/**
 * Special function for file includes.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $file
 *            File which should be included/required.
 * @param bool $once
 *            File should be included/required once. Default true.
 * @param bool|Closure $show_errors
 *            If true error will be processed, if Closure - only Closure will be called. Default true.
 * @return mixed
 */
function ttcms_load_file($file, $once = true, $show_errors = true)
{
    if (file_exists($file)) {
        if ($once) {
            return require_once($file);
        } else {
            return require($file);
        }
    } elseif (is_bool($show_errors) && $show_errors) {
        _trigger_error(
            sprintf(
                t__(
                    'Invalid file name: <strong>%s</strong> does not exist. <br />',
                    'tritan-cms'
                ),
                $file
            )
        );
    } elseif ($show_errors instanceof \Closure) {
        return (bool) $show_errors();
    }
    return false;
}

/**
 * Appends a trailing slash.
 *
 * Will remove trailing forward and backslashes if it exists already before adding
 * a trailing forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $string
 *            What to add the trailing slash to.
 * @return string String with trailing slash added.
 */
function add_trailing_slash($string)
{
    return remove_trailing_slash($string) . '/';
}

/**
 * Removes trailing forward slashes and backslashes if they exist.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @param string $string
 *            What to remove the trailing slashes from.
 * @return string String without the trailing slashes.
 */
function remove_trailing_slash($string)
{
    return rtrim($string, '/\\');
}

/**
 * Load an array of must-use plugin files
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @access private
 * @return array Files to include
 */
function ttcms_get_mu_plugins()
{
    $mu_plugins = [];
    if (!is_dir(TTCMS_MU_PLUGIN_DIR)) {
        return $mu_plugins;
    }
    if (!$handle = opendir(TTCMS_MU_PLUGIN_DIR)) {
        return $mu_plugins;
    }
    while (($plugin = readdir($handle)) !== false) {
        if (substr($plugin, -11) == '.plugin.php') {
            $mu_plugins[] = TTCMS_MU_PLUGIN_DIR . $plugin;
        }
    }
    closedir($handle);
    sort($mu_plugins);
    return $mu_plugins;
}

/**
 * Load an array of dropin files per site.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @access private
 * @return array Files to include
 */
function ttcms_get_site_dropins()
{
    $dropin_dir = c::getInstance()->get('site_path') . 'dropins' . DS;
    $site_dropins = [];
    if (!is_dir($dropin_dir)) {
        return $site_dropins;
    }
    if (!$handle = opendir($dropin_dir)) {
        return $site_dropins;
    }
    while (($dropins = readdir($handle)) !== false) {
        if (substr($dropins, -11) == '.dropin.php') {
            $site_dropins[] = $dropin_dir . $dropins;
        }
    }
    closedir($handle);
    sort($site_dropins);
    return $site_dropins;
}

/**
 * Load an array of theme routers per site.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @access private
 * @return array Files to include
 */
function ttcms_get_theme_routers()
{
    $theme_router_dir = c::getInstance()->get('theme_path') . 'routers' . DS;
    $theme_routers = [];
    if (!is_dir($theme_router_dir)) {
        return $theme_routers;
    }
    if (!$handle = opendir($theme_router_dir)) {
        return $theme_routers;
    }
    while (($routers = readdir($handle)) !== false) {
        if (substr($routers, -11) == '.router.php') {
            $theme_routers[] = $theme_router_dir . $routers;
        }
    }
    closedir($handle);
    sort($theme_routers);
    return $theme_routers;
}

/**
 * Retrieves a modified URL query string.
 *
 * Uses `query_arg_port` filter hook.
 *
 * @file app/functions.php
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
        $result .= hook::getInstance()->{'applyFilter'}('query_arg_port', ':' . $uri['port']);
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
 * Determines if the server is running Apache.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @return bool
 */
function is_apache(): bool
{
    if (strpos(ttcms()->obj['app']->req->server['SERVER_SOFTWARE'], 'Apache') !== false) {
        return true;
    }
}

/**
 * Whether the current request is for an administrative interface.
 *
 * @file app/functions.php
 *
 * e.g. `/admin/`
 *
 * @since 0.9
 * @return bool True if an admin screen, otherwise false.
 */
function is_admin(): bool
{
    return (
        new TriTan\Common\Uri(
            hook::getInstance()
        )
    )->{'isAdmin'}();
}

/**
 * Determines if SSL is used.
 *
 * Checks if `base_url` filter hook is present.
 *
 * @file app/functions.php
 *
 * @since 0.9
 * @return bool True if SSL, otherwise false.
 */
function is_ssl(): bool
{
    return (
        new TriTan\Common\Ssl()
    )->{'isEnabled'}();
}

/**
 * Whether the current request is for a login interface.
 *
 * @file app/functions.php
 *
 * e.g. `/login/`
 *
 * @since 0.9.9
 * @return bool True if login screen, otherwise false.
 */
function is_login(): bool
{
    return (
        new TriTan\Common\Uri(
            hook::getInstance()
        )
    )->{'isLogin'}();
}

/**
 * Escaping for HTML blocks.
 *
 * @file app/functions.php
 *
 * @since 0.9.9
 * @param string $string
 * @return string Escaped HTML block.
 */
function esc_html(string $string)
{
    $safe_string = (new Escape())->{'html'}($string);
    /**
     * Filters a clean and escaped string for HTML output.
     *
     * String passed to esc_html() is stripped of invalid utf-8 characters
     * before output.
     *
     * @since 0.9.9
     * @param string $safe_string String after it has been escaped.
     * @param string $string      String before it has been escaped.
     */
    return hook::getInstance()->{'applyFilter'}('esc_html', $safe_string, $string);
}

/**
 * Retrieve the translation of a string and escapes it for safe use in HTML blocks.
 *
 * @since 0.9.9
 * @param string $string String to translate.
 * @param type   $domain Optional. Unique identifier for retrieving translated string.
 *                                 Default: 'tritan-cms'.
 * @return string Translated string.
 */
function esc_html__(string $string, string $domain = 'tritan-cms')
{
    return esc_html(t__($string, $domain));
}

/**
 * Escaping for textarea.
 *
 * @file app/functions.php
 *
 * @since 0.9.9
 * @param string $string
 * @return string Escaped string.
 */
function esc_textarea(string $string)
{
    $safe_string = (new Escape())->{'textarea'}($string);
    /**
     * Filters a clean and escaped string for textarea output.
     *
     * String passed to esc_textarea() is stripped of invalid utf-8 characters
     * before output.
     *
     * @since 0.9.9
     * @param string $safe_string String after it has been escaped.
     * @param string $string      String before it has been escaped.
     */
    return hook::getInstance()->{'applyFilter'}('esc_textarea', $safe_string, $string);
}

/**
 * Escaping for url.
 *
 * @file app/functions.php
 *
 * @since 0.9.9
 * @param string $url The url to be escaped.
 * @param array $scheme Optional. An array of acceptable schemes.
 * @param bool $encode  Whether url params should be encoded.
 * @return string The escaped $url after the `esc_url` filter is applied.
 */
function esc_url(string $url, array $scheme = ['http', 'https'], bool $encode = false)
{
    $safe_url = (new Escape())->{'url'}($url, $scheme, $encode);
    /**
     * Filters a clean and escaped url for output.
     *
     * @since 0.9.8
     * @param string $safe_url The escaped url to be returned.
     * @param string $url      The url prior to being escaped.
     */
    return hook::getInstance()->{'applyFilter'}('esc_url', $safe_url, $url);
}

/**
 * Escaping for HTML attributes.
 *
 * @file app/functions.php
 *
 * @since 0.9.9
 * @param string $string
 * @return string Escaped HTML attribute.
 */
function esc_attr(string $string)
{
    $safe_string = (new Escape())->{'attr'}($string);
    /**
     * Filters a clean and escaped string for HTML attributes output.
     *
     * String passed to esc_attr() is stripped of invalid utf-8 characters
     * before output.
     *
     * @since 0.9.9
     * @param string $safe_string String after it has been escaped.
     * @param string $string      String before it has been escaped.
     */
    return hook::getInstance()->{'applyFilter'}('esc_attr', $safe_string, $string);
}

/**
 * Retrieve the translation of a string and escapes it for safe use in an attribute.
 *
 * @since 0.9.9
 * @param string $string String to translate.
 * @param type   $domain Optional. Unique identifier for retrieving translated string.
 *                                 Default: 'tritan-cms'.
 * @return string Translated string.
 */
function esc_attr__(string $string, string $domain = 'tritan-cms')
{
    return esc_attr(t__($string, $domain));
}

/**
 * Escaping for inline javascript.
 *
 * Example usage:
 *
 *      $esc_js = json_encode("Joshua's \"code\"");
 *      $attribute = esc_js("alert($esc_js);");
 *      echo '<input type="button" value="push" onclick="'.$attribute.'" />';
 *
 * @file app/functions.php
 *
 * @since 0.9.9
 * @param string $string
 * @return string Escaped inline javascript.
 */
function esc_js(string $string)
{
    $safe_string = (new Escape())->{'js'}($string);
    /**
     * Filters a clean and escaped string for inline javascript output.
     *
     * String passed to esc_js() is stripped of invalid utf-8 characters
     * before output.
     *
     * @since 0.9.9
     * @param string $safe_string String after it has been escaped.
     * @param string $string      String before it has been escaped.
     */
    return hook::getInstance()->{'applyFilter'}('esc_js', $safe_string, $string);
}

/**
 * Makes content safe to print on screen.
 *
 * This function should only be used on output. With the exception of uploading
 * images, never use this function on input. All inputted data should be
 * accepted and then purified on output for optimal results. For output of images,
 * make sure to escape with esc_url().
 *
 * @file app/functions.php
 *
 * @since 0.9.9
 * @param string $string Text to purify.
 * @param bool $is_image
 * @return string
 */
function html_purify(string $string, bool $is_image = false)
{
    return (
        new TriTan\Common\HtmlPurifier()
    )->{'purify'}($string, $is_image);
}

require(APP_PATH . 'functions' . DS . 'link-function.php');
require(APP_PATH . 'functions' . DS . 'hook-function.php');
require(APP_PATH . 'functions' . DS . 'dependency-function.php');
require(APP_PATH . 'functions' . DS . 'menu-function.php');
require(APP_PATH . 'functions' . DS . 'auth-function.php');
require(APP_PATH . 'functions' . DS . 'domain-function.php');
require(APP_PATH . 'functions' . DS . 'core-function.php');
require(APP_PATH . 'functions' . DS . 'site-function.php');
require(APP_PATH . 'functions' . DS . 'logger-function.php');
require(APP_PATH . 'functions' . DS . 'db-function.php');
require(APP_PATH . 'functions' . DS . 'post-function.php');
require(APP_PATH . 'functions' . DS . 'posttype-function.php');
require(APP_PATH . 'functions' . DS . 'user-function.php');
require(APP_PATH . 'functions' . DS . 'deprecated-function.php');
require(APP_PATH . 'application.php');
