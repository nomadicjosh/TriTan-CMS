<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;

/**
 * TriTan CMS Dependency Injection, Wrappers, etc.
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Call the application global scope.
 * 
 * @since 1.0.0
 * @return object
 */
function app()
{
    $app = \Liten\Liten::getInstance();
    return $app;
}
/**
 * Autoload libraries installed via composer
 */
ttcms_load_file(BASE_PATH . 'vendor/autoload.php');

/**
 * Hooks global scope.
 */
app()->inst->singleton('hook', function () {
    return new TriTan\Hooks();
});

/**
 * Form global scope.
 */
app()->inst->singleton('form', function () {
    return new AdamWathan\Form\FormBuilder();
});

/**
 * Wrapper function for the core PHP function: trigger_error.
 *
 * This function makes the error a little more understandable for the
 * end user to track down the issue.
 *
 * @since 1.0.0
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
    trigger_error($message . ' used <strong>' . $caller['function'] . '()</strong> called from <strong>' . $caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong>' . "\n<br />error handler", $level);
    echo '</div>';
}

/**
 * Returns false.
 *
 * Apply to filters to return false.
 *
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
 * @return bool NULL
 */
function __return_null()
{
    return null;
}

/**
 * Return posted data if set.
 * 
 * @since 1.0.0
 * @param mixed $post
 * @return mixed
 */
function __return_post($post)
{
    return isset(app()->req->post[$post]) ? app()->req->post[$post] : '';
}

/**
 * Wrapper function for Plugin::plugin_basename() method and
 * extracts the file name of a specific plugin.
 *
 * @see Plugin::plugin_basename()
 *
 * @since 1.0.0
 * @param string $filename
 *            Plugin's file name.
 */
function plugin_basename($filename)
{
    return \TriTan\Plugin::inst()->plugin_basename($filename);
}

/**
 * Wrapper function for Plugin::register_activation_hook() method.
 * When a plugin
 * is activated, the action `activate_pluginname` hook is called. `pluginname`
 * is replaced by the actually file name of the plugin being activated. So if the
 * plugin is located at 'app/plugin/sample/sample.plugin.php', then the hook will
 * call 'activate_sample.plugin.php'.
 *
 * @see Plugin::register_activation_hook()
 *
 * @since 1.0.0
 * @param string $filename
 *            Plugin's filename.
 * @param string $function
 *            The function that should be triggered by the hook.
 */
function register_activation_hook($filename, $function)
{
    return \TriTan\Plugin::inst()->register_activation_hook($filename, $function);
}

/**
 * Wrapper function for Plugin::register_deactivation_hook() method.
 * When a plugin
 * is deactivated, the action `deactivate_pluginname` hook is called. `pluginname`
 * is replaced by the actually file name of the plugin being deactivated. So if the
 * plugin is located at 'app/plugin/sample/sample.plugin.php', then the hook will
 * call 'deactivate_sample.plugin.php'.
 *
 * @see Plugin::register_deactivation_hook()
 *
 * @since 1.0.0
 * @param string $filename
 *            Plugin's filename.
 * @param string $function
 *            The function that should be triggered by the hook.
 */
function register_deactivation_hook($filename, $function)
{
    return \TriTan\Plugin::inst()->register_deactivation_hook($filename, $function);
}

/**
 * Wrapper function for Plugin::plugin_dir_path() method.
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @see Plugin::plugin_dir_path()
 *
 * @since 1.0.0
 * @param string $filename
 *            The filename of the plugin (__FILE__).
 * @return string The filesystem path of the directory that contains the plugin.
 */
function plugin_dir_path($filename)
{
    return \TriTan\Plugin::inst()->plugin_dir_path($filename);
}

/**
 * Special function for file includes.
 *
 * @since 1.0.0
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
        _trigger_error(sprintf(_t('Invalid file name: <strong>%s</strong> does not exist. <br />', 'tritan-cms'), $file));
    } elseif ($show_errors instanceof \Closure) {
        return (bool) $show_errors();
    }
    return false;
}

/**
 * Removes directory recursively along with any files.
 *
 * @since 1.0.0
 * @param string $dir
 *            Directory that should be removed.
 */
function _rmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DS . $object)) {
                    _rmdir($dir . DS . $object);
                } else {
                    unlink($dir . DS . $object);
                }
            }
        }
        rmdir($dir);
    }
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
 * @access private
 * @return array Files to include
 */
function ttcms_get_site_dropins()
{
    $dropin_dir = Config::get('site_path') . 'dropins' . DS;
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
 * @since 1.0.0
 * @access private
 * @return array Files to include
 */
function ttcms_get_theme_routers()
{
    $theme_router_dir = Config::get('theme_path') . 'routers' . DS;
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
require( APP_PATH . 'functions' . DS . 'global-function.php' );
require( APP_PATH . 'functions' . DS . 'auth-function.php' );
require( APP_PATH . 'functions' . DS . 'cache-function.php' );
require( APP_PATH . 'functions' . DS . 'textdomain-function.php' );
require( APP_PATH . 'functions' . DS . 'core-function.php' );
require( APP_PATH . 'functions' . DS . 'meta-function.php' );
require( APP_PATH . 'functions' . DS . 'site-function.php' );
require( APP_PATH . 'functions' . DS . 'logger-function.php' );
require( APP_PATH . 'functions' . DS . 'db-function.php' );
require( APP_PATH . 'functions' . DS . 'post-function.php' );
require( APP_PATH . 'functions' . DS . 'posttype-function.php' );
require( APP_PATH . 'functions' . DS . 'user-function.php' );
require( APP_PATH . 'functions' . DS . 'parsecode-function.php' );
