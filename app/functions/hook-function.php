<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;

/**
 * TriTan CMS Hooks Helper & Wrapper
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Wrapper function for Hooks::register_admin_page() and
 * register's a plugin administration page.
 *
 * @see Hooks::register_admin_page()
 *
 * @since 0.9
 * @param string $slug
 *            Plugin's slug.
 * @param string $title
 *            Title that is show for the plugin's link.
 * @param string $function
 *            The function which prints the plugin's page.
 */
function register_admin_page($slug, $title, $function)
{
    return app()->hook->register_admin_page($slug, $title, $function);
}

/**
 * Wrapper function for Hooks::activate_plugin() and
 * activates plugin based on $_GET['id'].
 *
 * @see Hooks::activate_plugin()
 *
 * @since 0.9
 * @param string $id
 *            ID of the plugin to be activated.
 * @return mixed Activates plugin if it exists.
 */
function activate_plugin($id)
{
    return app()->hook->activate_plugin($id);
}

/**
 * Wrapper function for Hooks::deactivate_plugin() and
 * deactivates plugin based on $_GET['id'].
 *
 * @see Hooks::deactivate_plugin()
 *
 * @since 0.9
 * @param string $id
 *            ID of the plugin to be deactivated.
 * @return mixed Deactivates plugin if it exists and is active.
 */
function deactivate_plugin($id)
{
    return app()->hook->deactivate_plugin($id);
}

/**
 * Wrapper function for Hooks::load_activated_plugins() and
 * loads all activated plugins for inclusion.
 *
 * @see Hooks::load_activated_plugins()
 *
 * @since 0.9
 * @param string $plugins_dir
 *            Loads plugins from specified folder
 * @return mixed
 */
function load_activated_plugins($plugins_dir = '')
{
    return app()->hook->load_activated_plugins($plugins_dir);
}

/**
 * Wrapper function for Hooks::is_plugin_activated() and
 * checks if a particular plugin is activated
 *
 * @see Hooks::is_plugin_activated()
 *
 * @since 0.9
 * @param string $plugin
 *            Name of plugin file.
 * @return bool False if plugin is not activated and true if it is activated.
 */
function is_plugin_activated($plugin)
{
    return app()->hook->is_plugin_activated($plugin);
}

/**
 * Mark a function as deprecated and inform when it has been used.
 *
 * There is a hook deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if APP_ENV is DEV.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @since 0.9
 *       
 * @param string $function_name
 *            The function that was called.
 * @param string $release
 *            The release of TriTan CMS that deprecated the function.
 * @param string $replacement
 *            Optional. The function that should have been called. Default null.
 */
function _deprecated_function($function_name, $release, $replacement = null)
{
    /**
     * Fires when a deprecated function is called.
     *
     * @since 0.9
     *       
     * @param string $function_name
     *            The function that was called.
     * @param string $replacement
     *            The function that should have been called.
     * @param string $release
     *            The release of TriTan CMS that deprecated the function.
     */
    app()->hook->{'do_action'}('deprecated_function_run', $function_name, $replacement, $release);

    /**
     * Filter whether to trigger an error for deprecated functions.
     *
     * @since 0.9
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated functions. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_function_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($replacement)) {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />', 'tritan-cms'), $function_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', 'tritan-cms'), $function_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($replacement)) {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />', $function_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $function_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Mark a class as deprecated and inform when it has been used.
 *
 * There is a hook deprecated_class_run that will be called that can be used
 * to get the backtrace up to what file, function/class called the deprecated
 * class.
 *
 * The current behavior is to trigger a user error if APP_ENV is DEV.
 *
 * This function is to be used in every class that is deprecated.
 *
 * @since 0.9
 *       
 * @param string $class_name
 *            The class that was called.
 * @param string $release
 *            The release of TriTan CMS that deprecated the class.
 * @param string $replacement
 *            Optional. The class that should have been called. Default null.
 */
function _deprecated_class($class_name, $release, $replacement = null)
{
    /**
     * Fires when a deprecated class is called.
     *
     * @since 0.9
     *       
     * @param string $class_name
     *            The class that was called.
     * @param string $replacement
     *            The class that should have been called.
     * @param string $release
     *            The release of TriTan CMS that deprecated the class.
     */
    app()->hook->{'do_action'}('deprecated_class_run', $class_name, $replacement, $release);

    /**
     * Filter whether to trigger an error for deprecated classes.
     *
     * @since 0.9
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated classes. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_class_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($replacement)) {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s instead. <br />', 'tritan-cms'), $class_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', 'tritan-cms'), $class_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($replacement)) {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s instead. <br />', $class_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $class_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Mark a class's method as deprecated and inform when it has been used.
 *
 * There is a hook deprecated_class_method_run that will be called that can be used
 * to get the backtrace up to what file, function/class called the deprecated
 * method.
 *
 * The current behavior is to trigger a user error if APP_ENV is DEV.
 *
 * This function is to be used in every class's method that is deprecated.
 *
 * @since 0.9
 *       
 * @param string $method_name
 *            The class method that was called.
 * @param string $release
 *            The release of TriTan CMS that deprecated the class's method.
 * @param string $replacement
 *            Optional. The class method that should have been called. Default null.
 */
function _deprecated_class_method($method_name, $release, $replacement = null)
{
    /**
     * Fires when a deprecated class method is called.
     *
     * @since 0.9
     *       
     * @param string $method_name
     *            The class's method that was called.
     * @param string $replacement
     *            The class method that should have been called.
     * @param string $release
     *            The release of TriTan CMS that deprecated the class's method.
     */
    app()->hook->{'do_action'}('deprecated_class_method_run', $method_name, $replacement, $release);

    /**
     * Filter whether to trigger an error for deprecated class methods.
     *
     * @since 0.9
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated class methods. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_class_method_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($replacement)) {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />', 'tritan-cms'), $method_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', 'tritan-cms'), $method_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($replacement)) {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />', $method_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $method_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Mark a function argument as deprecated and inform when it has been used.
 *
 * This function is to be used whenever a deprecated function argument is used.
 * Before this function is called, the argument must be checked for whether it was
 * used by comparing it to its default value or evaluating whether it is empty.
 * For example:
 *
 * if ( ! empty( $deprecated ) ) {
 * _deprecated_argument( __FUNCTION__, '0.9' );
 * }
 *
 *
 * There is a hook deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function used the deprecated
 * argument.
 *
 * The current behavior is to trigger a user error if APP_ENV is set to DEV.
 *
 * @since 0.9
 *       
 * @param string $function_name
 *            The function that was called.
 * @param string $release
 *            The release of TriTan CMS that deprecated the argument used.
 * @param string $message
 *            Optional. A message regarding the change. Default null.
 */
function _deprecated_argument($function_name, $release, $message = null)
{
    /**
     * Fires when a deprecated argument is called.
     *
     * @since 0.9
     *       
     * @param string $function_name
     *            The function that was called.
     * @param string $message
     *            A message regarding the change.
     * @param string $release
     *            The release of TriTan CMS that deprecated the argument used.
     */
    app()->hook->{'do_action'}('deprecated_argument_run', $function_name, $message, $release);
    /**
     * Filter whether to trigger an error for deprecated arguments.
     *
     * @since 0.9
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated arguments. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_argument_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($message)) {
                _trigger_error(sprintf(_t('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s! %3$s. <br />', 'tritan-cms'), $function_name, $release, $message), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', 'tritan-cms'), $function_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($message)) {
                _trigger_error(sprintf('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s! %3$s. <br />', $function_name, $release, $message), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $function_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Marks a deprecated action or filter hook as deprecated and throws a notice.
 *
 * Default behavior is to trigger a user error if `APP_ENV` is set to DEV.
 *
 * This function is called by the hook::do_action_deprecated() and Hook::apply_filter_deprecated()
 * functions, and so generally does not need to be called directly.
 *
 * @since 0.9
 * 
 * @param string $hook        The hook that was used.
 * @param string $release     The release of TriTan CMS that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message     Optional. A message regarding the change.
 */
function _deprecated_hook($hook, $release, $replacement = null, $message = null)
{
    /**
     * Fires when a deprecated hook is called.
     *
     * @since 0.9
     * 
     * @param string $hook        The hook that was called.
     * @param string $replacement The hook that should be used as a replacement.
     * @param string $release     The release of TriTan CMS that deprecated the argument used.
     * @param string $message     A message regarding the change.
     */
    app()->hook->{'do_action'}('deprecated_hook_run', $hook, $replacement, $release, $message);

    /**
     * Filters whether to trigger deprecated hook errors.
     *
     * @since 0.9
     * 
     * @param bool $trigger Whether to trigger deprecated hook errors. Requires
     *                      `APP_DEV` to be defined DEV.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_hook_trigger_error', true)) {
        $message = empty($message) ? '' : ' ' . $message;
        if (!is_null($replacement)) {
            _trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since release %2$s! Use %3$s instead.'), $hook, $release, $replacement) . $message, E_USER_DEPRECATED);
        } else {
            _trigger_error(sprintf(__('%1$s is <strong>deprecated</strong> since release %2$s with no alternative available.'), $hook, $release) . $message, E_USER_DEPRECATED);
        }
    }
}

/**
 * Mark something as being incorrectly called.
 *
 * There is a hook incorrectly_called_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if APP_ENV is set to DEV.
 *
 * @since 0.9
 *       
 * @param string $function_name
 *            The function that was called.
 * @param string $message
 *            A message explaining what has been done incorrectly.
 * @param string $release
 *            The release of TriTan CMS where the message was added.
 */
function _incorrectly_called($function_name, $message, $release)
{
    /**
     * Fires when the given function is being used incorrectly.
     *
     * @since 0.9
     *       
     * @param string $function_name
     *            The function that was called.
     * @param string $message
     *            A message explaining what has been done incorrectly.
     * @param string $release
     *            The release of TriTan CMS where the message was added.
     */
    app()->hook->{'do_action'}('incorrectly_called_run', $function_name, $message, $release);

    /**
     * Filter whether to trigger an error for _incorrectly_called() calls.
     *
     * @since 3.1.0
     *       
     * @param bool $trigger
     *            Whether to trigger the error for _incorrectly_called() calls. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('incorrectly_called_trigger_error', true)) {
        if (function_exists('_t')) {
            $release = is_null($release) ? '' : sprintf(_t('(This message was added in release %s.) <br /><br />', 'tritan-cms'), $release);
            /* translators: %s: Codex URL */
            $message .= ' ' . sprintf(_t('Please see <a href="%s">Debugging in TriTan CMS</a> for more information.', 'tritan-cms'), 'https://learn.tritancms.com/start.html#debugging');
            _trigger_error(sprintf(_t('%1$s() was called <strong>incorrectly</strong>. %2$s %3$s <br />', 'tritan-cms'), $function_name, $message, $release));
        } else {
            $release = is_null($release) ? '' : sprintf('(This message was added in release %s.) <br /><br />', $release);
            $message .= sprintf(' Please see <a href="%s">Debugging in TriTan CMS</a> for more information.', 'https://learn.tritancms.com/start.html#debugging');
            _trigger_error(sprintf('%1$s() was called <strong>incorrectly</strong>. %2$s %3$s <br />', $function_name, $message, $release));
        }
    }
}

/**
 * Prints copyright in the admin footer.
 *
 * @since 0.9
 */
function ttcms_admin_copyright_footer()
{
    $copyright = '<!--  Copyright Line -->' . "\n";
    $copyright .= '<strong>&#169; ' . _t('Copyright 2017', 'tritan-cms') . ' | ' . _t('Powered by', 'tritan-cms') . ' <a href="//www.tritancms.com/">' . _t('TriTan CMS', 'tritan-cms') . '</a></strong>' . "\n";
    $copyright .= '<!--  End Copyright Line -->' . "\n";

    return app()->hook->{'apply_filter'}('admin_copyright_footer', $copyright);
}

/**
 * Includes and loads all activated plugins.
 *
 * @since 0.9
 */
load_activated_plugins(BASE_PATH . 'plugins' . DS);

/**
 * An action called to add the plugin's link
 * to the menu structure.
 *
 * @since 0.9
 * @uses app()->hook->{'do_action'}() Calls 'admin_menu' hook.
 */
app()->hook->{'do_action'}('admin_menu');

/**
 * Fires once activated plugins have loaded.
 *
 * @since 0.9
 */
app()->hook->{'do_action'}('plugins_loaded');

/**
 * Fires the admin_head action.
 *
 * @since 0.9
 */
function admin_head()
{
    /**
     * Registers & enqueues a stylesheet to be printed in backend head section.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('enqueue_admin_css');
    /**
     * Fires in head section of all admin screens.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('ttcms_admin_head');
}

/**
 * Fires the ttcms_head action.
 *
 * @since 0.9
 */
function ttcms_head()
{
    /**
     * Registers & enqueues a stylesheet to be printed in frontend head section.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('enqueue_css');
    /**
     * Prints scripts and/or data in the head of the front end.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('ttcms_head');
}

/**
 * Fires the admin_footer action via backend.
 *
 * @since 0.9
 */
function admin_footer()
{
    /**
     * Registers & enqueues javascript to be printed in backend footer section.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('enqueue_admin_js');
    /**
     * Prints scripts and/or data before the ending body tag of the backend.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('ttcms_admin_footer');
}

/**
 * Fires the ttcms_footer action via the admin.
 *
 * @since 0.9
 */
function ttcms_footer()
{
    /**
     * Registers & enqueues javascript to be printed in frontend footer section.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('enqueue_js');
    /**
     * Prints scripts and/or data before the ending body tag
     * of the front end.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('ttcms_footer');
}

/**
 * Fires the ttcms_release action.
 *
 * @since 0.9
 */
function ttcms_release()
{
    /**
     * Prints TriTan CMS release information.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('ttcms_release');
}

/**
 * Fires the admin_top_widgets action.
 *
 * @since 0.9
 */
function admin_top_widgets()
{
    /**
     * Prints widgets at the top portion of the admin.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('admin_top_widgets');
}

/**
 * Large logo. Filterable.
 * 
 * @since 0.9
 * @return string
 */
function get_logo_large()
{
    $logo = '<strong>' . _t('TriTan', 'tritan-cms') . '</strong>' . _t('CMS', 'tritan-cms');
    return app()->hook->{'apply_filter'}('logo_large', $logo);
}

/**
 * Mini logo. Filterable.
 * 
 * @since 0.9
 * @return string
 */
function get_logo_mini()
{
    $logo = '<strong>' . _t('Tri', 'tritan-cms') . '</strong>' . _t('Tan', 'tritan-cms');
    return app()->hook->{'apply_filter'}('logo_mini', $logo);
}

/**
 * Checks data to make sure it is a valid request.
 * 
 * @since 0.9
 * @param mixed $data
 */
function ttcms_validation_check($data)
{
    if ($data['m6qIHt4Z5evV'] != '' || !empty($data['m6qIHt4Z5evV'])) {
        _ttcms_flash()->{'error'}(_t('Spam is not allowed.', 'tritan-cms'), get_base_url() . 'spam' . '/');
        exit();
    }

    if ($data['YgexGyklrgi1'] != '' || !empty($data['YgexGyklrgi1'])) {
        _ttcms_flash()->{'error'}(_t('Spam is not allowed.', 'tritan-cms'), get_base_url() . 'spam' . '/');
        exit();
    }
}

/**
 * Retrieve name of the current theme.
 * 
 * @since 0.9
 * @return string Theme name.
 */
function get_theme()
{
    /**
     * Filters the name of the current theme.
     *
     * @since 0.9
     * @param string $theme Current theme's directory name.
     */
    return app()->hook->{'apply_filter'}('theme', app()->hook->{'get_option'}('current_site_theme'));
}

/**
 * Retrieve theme directory URI.
 *
 * @since 0.9
 * @uses app()->hook->{'apply_filter'}() Calls 'theme_directory_uri' filter.
 * @return string TriTan CMS theme directory uri.
 */
function get_theme_directory_uri()
{
    $theme = str_replace('%2F', '/', rawurlencode(get_theme()));
    $theme_root_uri = get_theme_url();
    $theme_dir_uri = $theme_root_uri . $theme . '/';
    return app()->hook->{'apply_filter'}('theme_directory_uri', $theme_dir_uri, $theme, $theme_root_uri);
}

/**
 * Retrieve javascript directory uri.
 *
 * @since 0.9
 * @uses app()->hook->{'apply_filter'}() Calls 'javascript_directory_uri' filter. 
 * @return string TriTan CMS javascript url.
 */
function get_javascript_directory_uri()
{
    $theme = str_replace('%2F', '/', rawurlencode(get_theme()));
    $javascript_root_uri = get_theme_url();
    $javascript_dir_uri = $javascript_root_uri . $theme . '/assets/js/';
    return app()->hook->{'apply_filter'}('javascript_directory_uri', $javascript_dir_uri, $theme, $javascript_root_uri);
}

/**
 * Retrieve less directory uri.
 *
 * @since 0.9
 * @uses app()->hook->{'apply_filter'}() Calls 'less_directory_uri' filter.   
 * @return string TriTan CMS less url.
 */
function get_less_directory_uri()
{
    $theme = str_replace('%2F', '/', rawurlencode(get_theme()));
    $less_root_uri = get_theme_url();
    $less_dir_uri = $less_root_uri . $theme . '/assets/less/';
    return app()->hook->{'apply_filter'}('less_directory_uri', $less_dir_uri, $theme, $less_root_uri);
}

/**
 * Retrieve css directory uri.
 *
 * @since 0.9
 * @uses app()->hook->{'apply_filter'}() Calls 'css_directory_uri' filter.   
 * @return string TriTan CMS css url.
 */
function get_css_directory_uri()
{
    $theme = str_replace('%2F', '/', rawurlencode(get_theme()));
    $css_root_uri = get_theme_url();
    $css_dir_uri = $css_root_uri . $theme . '/assets/css/';
    return app()->hook->{'apply_filter'}('css_directory_uri', $css_dir_uri, $theme, $css_root_uri);
}

/**
 * Retrieve image directory uri.
 *
 * @since 0.9.4
 * @uses app()->hook->{'apply_filter'}() Calls 'image_directory_uri' filter.   
 * @return string TriTan CMS image url.
 */
function get_image_directory_uri()
{
    $theme = str_replace('%2F', '/', rawurlencode(get_theme()));
    $image_root_uri = get_theme_url();
    $image_dir_uri = $image_root_uri . $theme . '/assets/images/';
    return app()->hook->{'apply_filter'}('image_directory_uri', $image_dir_uri, $theme, $image_root_uri);
}

/**
 * Parses a string into variables to be stored in an array.
 *
 * Uses {@link http://www.php.net/parse_str parse_str()}
 *
 * @since 0.9
 * @param string $string
 *            The string to be parsed.
 * @param array $array
 *            Variables will be stored in this array.
 */
function ttcms_parse_str($string, $array)
{
    parse_str($string, $array);
    /**
     * Filter the array of variables derived from a parsed string.
     *
     * @since 4.2.0
     * @param array $array
     *            The array populated with variables.
     */
    $array = app()->hook->{'apply_filter'}('ttcms_parse_str', $array);
}

/**
 * Frontend portal footer powered by and release.
 *
 * @since 0.9
 * @uses app()->hook->{'apply_filter'}() Calls 'met_footer_release' filter.
 *      
 * @return mixed.
 */
function get_footer_release()
{
    $release = _t('Powered by TriTan CMS r', 'tritan-cms') . CURRENT_RELEASE;
    return app()->hook->{'apply_filter'}('footer_release', $release);
}

/**
 * Retrieve the avatar `<img>` tag for user.
 * 
 * @since 0.9
 * @param string $email User's email address.
 * @param int $s        Height and width of the avatar image file in pixels. Default 80.
 * @param string $class Class to add to `<img>` element.
 * @return string `<img>` tag for user's avatar or default otherwise.
 */
function get_user_avatar($email, $s = 80, $class = '')
{
    $email_hash = md5(strtolower(_trim($email)));

    if (is_ssl() || app()->hook->{'has_filter'}('base_url')) {
        $url = 'https://secure.gravatar.com/avatar/' . $email_hash . "?s=200";
    } else {
        $url = 'http://www.gravatar.com/avatar/' . $email_hash . "?s=200";
    }

    if (get_http_response_code('http://www.gravatar.com/') != 302) {
        $static_image_url = get_base_url() . "static/assets/img/avatar.png?s=200";
        $avatarsize = getimagesize($static_image_url);
        $avatar = '<img src="' . get_base_url() . 'static/assets/img/avatar.png" ' . resize_image($avatarsize[1], $avatarsize[1], $s) . ' class="' . $class . '" alt="' . $email . '" />';
    } else {
        $avatarsize = getimagesize($url);
        $avatar = '<img src="' . $url . '" ' . resize_image($avatarsize[1], $avatarsize[1], $s) . ' class="' . $class . '" alt="' . $email . '" />';
    }

    return app()->hook->{'apply_filter'}('user_avatar', $avatar, $email, $s, $class);
}

/**
 * Retrieves the avatar url.
 * 
 * @since 0.9
 * @param string $email Email address of user.
 * @return string The url of the avatar that was found, or default if not found.
 */
function get_user_avatar_url($email)
{
    $email_hash = md5(strtolower(_trim($email)));

    if (is_ssl() || app()->hook->{'has_filter'}('base_url')) {
        $url = 'https://secure.gravatar.com/avatar/' . $email_hash;
    } else {
        $url = 'http://www.gravatar.com/avatar/' . $email_hash;
    }

    if (get_http_response_code('http://www.gravatar.com/') != 302) {
        $avatar = get_base_url() . 'static/assets/img/avatar.png';
    } else {
        $avatar = $url;
    }

    return app()->hook->{'apply_filter'}('user_avatar_url', $avatar, $email);
}

function nocache_headers()
{
    $headers = [
        'Expires' => 'Sun, 01 Jan 2014 00:00:00 GMT',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache'
    ];
    foreach ($headers as $k => $v) {
        header("{$k}: {$v}");
    }
    return app()->hook->{'apply_filter'}('nocache_headers', $headers);
}

/**
 * Upload image button.
 *
 * @since 0.9
 */
function ttcms_upload_image()
{
    $elfinder = '<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
            <link href="vendor/studio-42/elfinder/css/elfinder.full.css" type="text/css" rel="stylesheet" />
            <link href="vendor/studio-42/elfinder/css/theme.css" type="text/css" rel="stylesheet" />
            <script src="vendor/studio-42/elfinder/js/elfinder.full.js" type="text/javascript"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.7/js/jquery.fancybox.min.js" type="text/javascript"></script>
            <script>
                $(document).ready(function () {
                
                    $("#remove_image").hide();
                    $("#set_image").show();
                    
                    $("#set_image").click(function (e) {
                        var elfinder = $("#elfinder").elfinder({
                            url: "' . get_base_url() . 'admin/connector",
                            resizable: false,
                            onlyMimes: ["image"],
                            uiOptions: {
                                // toolbar configuration
                                toolbar: [
                                    ["reload"],
                                    ["open", "download", "getfile"],
                                    ["duplicate", "rename", "edit", "resize"],
                                    ["quicklook", "info"],
                                    ["search"],
                                    ["view", "sort"]
                                ]
                            },
                            getfile: {
                                onlyURL: true,
                                multiple: false,
                                folders: false,
                                oncomplete: "destroy"
                            },
                            handlers: {
                                dblclick: function (event, elfinderInstance) {
                                    fileInfo = elfinderInstance.file(event.data.file);

                                    if (fileInfo.mime != "directory") {
                                        var imgURL = elfinderInstance.url(event.data.file);
                                        $("#upload_image").val(imgURL);

                                        var imgPath = "<img src=\'"+imgURL+"\' id=\"append-image\" style=\"width:260px;height:auto;background-size:contain;margin-bottom:.9em;background-repeat:no-repeat\"/>";
                                        $("#elfinder_image").append(imgPath); //add the image to a div so you can see the selected images
                                        
                                        $("#remove_image").show();
                                        $("#set_image").hide();

                                        elfinderInstance.destroy();
                                        return false; // stop elfinder
                                    };
                                },
                                destroy: function () {
                                    elfinder.dialog("close");

                                }
                            }
                        }).dialog({
                            title: "filemanager",
                            resizable: true,
                            width: 920,
                            height: 500
                        });
                        $("#remove_image").click(function () {
                        
                            $("#upload_image").val("");
                            $("#elfinder_image").find("#append-image").remove(); //remove image from div when user clicks remove image button.
                            
                            $("#remove_image").hide();
                            $("#set_image").show();
                                        
                            return false;
                        });
                    });
                });
            </script>';
    return app()->hook->{'apply_filter'}('ttcms_upload_image', $elfinder);
}

/**
 * Compares release values.
 *
 * @since 0.9
 * @param string $current
 *            Current release value.
 * @param string $latest
 *            Latest release value.
 * @param string $operator
 *            Operand use to compare current and latest release values.
 * @return bool
 */
function compare_releases($current, $latest, $operator = '>')
{
    $php_function = version_compare($latest, $current, $operator);
    /**
     * Filters the comparison between two release.
     *
     * @since 0.9
     * @param $php_function PHP
     *            function for comparing two release values.
     */
    $release = app()->hook->{'apply_filter'}('compare_releases', $php_function);

    if ($release) {
        return $latest;
    } else {
        return false;
    }
}

/**
 * Retrieves a response code from the header
 * of a given resource.
 *
 * @since 0.9
 * @param string $url
 *            URL of resource/website.
 * @return int HTTP response code.
 */
function get_http_response_code($url)
{
    $headers = get_headers($url);
    $status = substr($headers[0], 9, 3);
    /**
     * Filters the http response code.
     *
     * @since 0.9
     * @param
     *            string
     */
    return app()->hook->{'apply_filter'}('http_response_code', $status);
}

/**
 * Plugin success message when plugin is activated successfully.
 *
 * @since 0.9
 * @param string $plugin_name
 *            The name of the plugin that was just activated.
 */
function ttcms_plugin_activate_message($plugin_name)
{
    $success = _ttcms_flash()->{'success'}(_t('Plugin <strong>activated</strong>.', 'tritan-cms'));
    /**
     * Filter the default plugin success activation message.
     *
     * @since 0.9
     * @param string $success
     *            The success activation message.
     * @param string $plugin_name
     *            The name of the plugin that was just activated.
     */
    return app()->hook->{'apply_filter'}('ttcms_plugin_activate_message', $success, $plugin_name);
}

/**
 * Plugin success message when plugin is deactivated successfully.
 *
 * @since 0.9
 * @param string $plugin_name
 *            The name of the plugin that was just deactivated.
 */
function ttcms_plugin_deactivate_message($plugin_name)
{
    $success = _ttcms_flash()->{'success'}(_t('Plugin <strong>deactivated</strong>.', 'tritan-cms'));
    /**
     * Filter the default plugin success deactivation message.
     *
     * @since 0.9
     * @param string $success
     *            The success deactivation message.
     * @param string $plugin_name
     *            The name of the plugin that was just deactivated.
     */
    return app()->hook->{'apply_filter'}('ttcms_plugin_deactivate_message', $success, $plugin_name);
}

/**
 * Shows an error message when system is in DEV mode.
 * 
 * @since 0.9
 */
function ttcms_dev_mode()
{
    if (APP_ENV === 'DEV') {
        echo '<div class="alert dismissable alert-danger center sticky">' . _t('Your system is currently in DEV mode. Please remember to set your system back to PROD mode after testing. When PROD mode is set, this warning message will disappear.', 'tritan-cms') . '</div>';
    }
}

/**
 * Returns full base url of MU Plugins.
 * 
 * @since 0.9
 * @return string MU Plugin base url.
 */
function get_mu_plugin_url()
{
    $url = get_base_url() . 'mu-plugins' . '/';
    return app()->hook->{'apply_filter'}('the_mu_plugin_url', $url);
}

/**
 * Returns full base url of Plugins.
 * 
 * @since 0.9
 * @return string Plugin base url.
 */
function get_plugin_url()
{
    $url = get_base_url() . 'plugins' . '/';
    return app()->hook->{'apply_filter'}('the_plugin_url', $url);
}

/**
 * Retrieves a URL within the plugins or mu-plugins directory.
 *
 * Defaults to the plugins directory URL if no arguments are supplied.
 *
 * @since 0.9
 * @param  string $path   Optional. Extra path appended to the end of the URL, including
 *                        the relative directory if $plugin is supplied. Default empty.
 * @param  string $plugin Optional. A full path to a file inside a plugin or mu-plugin.
 *                        The URL will be relative to its directory. Default empty.
 *                        Typically this is done by passing `__FILE__` as the argument.
 * @return string Plugins URL link with optional paths appended.
 */
function plugins_url($path = '', $plugin = '')
{
    $_path = ttcms_normalize_path($path);
    $_plugin = ttcms_normalize_path($plugin);
    $mu_plugin_dir = ttcms_normalize_path(TTCMS_MU_PLUGIN_DIR);

    if (!empty($_plugin) && 0 === strpos($_plugin, $mu_plugin_dir)) {
        $url = get_mu_plugin_url();
    } else {
        $url = get_plugin_url();
    }

    $url = set_url_scheme($url);

    if (!empty($_plugin) && is_string($_plugin)) {
        $folder = plugin_basename(dirname($_plugin));
        if ('.' != $folder) {
            $url .= ltrim($folder, '/');
        }
    }

    if ($_path && is_string($_path)) {
        $url .= '/' . ltrim($_path, '/');
    }

    /**
     * Filters the URL to the plugins or mu-plugins directory.
     *
     * @since 0.9
     * @param string $url       The complete URL to the plugins directory including scheme and path.
     * @param string $_path     Path relative to the URL to the plugins directory. Blank string
     *                          if no path is specified.
     * @param string $_plugin   The plugin file path to be relative to. Blank string if no plugin
     *                          is specified.
     */
    return app()->hook->{'apply_filter'}('plugins_url', $url, $_path, $_plugin);
}

/**
 * Get the URL directory path (with trailing slash) for the plugin __FILE__ passed in.
 * 
 * @since 0.9
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the URL path of the directory that contains the plugin.
 */
function plugin_dir_url($file)
{
    $url = add_trailing_slash(plugins_url('', $file));
    return app()->hook->{'apply_filter'}('plugin_dir_url', $url, $file);
}

/**
 * Returns full base url of a site's theme.
 * 
 * @since 0.9
 * @return string Site's theme base url.
 */
function get_theme_url()
{
    $site_id = Config::get('site_id');
    $url = get_base_url() . 'private/sites/' . $site_id . '/themes/';
    return app()->hook->{'apply_filter'}("the_theme_url_site{$site_id}", $url);
}

/**
 * Retrieves a URL within a site's theme directory.
 *
 * Defaults to the site's theme directory URL if no arguments are supplied.
 *
 * @since 0.9
 * @param  string $path   Optional. Extra path appended to the end of the URL, including
 *                        the relative directory if $theme is supplied. Default empty.
 * @param  string $theme  Optional. A full path to a file inside a theme.
 *                        The URL will be relative to its directory. Default empty.
 *                        Typically this is done by passing `__FILE__` as the argument.
 * @return string Site's theme URL link with optional paths appended.
 */
function themes_url($path = '', $theme = '')
{
    $site_id = Config::get('site_id');
    $_path = ttcms_normalize_path($path);
    $_theme = ttcms_normalize_path($theme);

    $url = get_theme_url();
    $url = set_url_scheme($url);

    if (!empty($_theme) && is_string($_theme)) {
        $folder = basename(dirname($_theme));
        if ('.' != $folder) {
            $url .= ltrim($folder, '/');
        }
    }

    if ($_path && is_string($_path)) {
        $url .= '/' . ltrim($_path, '/');
    }

    /**
     * Filters the URL to a site's theme directory.
     *
     * @since 0.9
     * @param string $url       The complete URL to a site's theme directory including scheme and path.
     * @param string $_path     Path relative to the URL to a site's theme directory. Blank string
     *                          if no path is specified.
     * @param string $_theme    A site's theme file path to be relative to. Blank string if no site's theme
     *                          is specified.
     */
    return app()->hook->{'apply_filter'}("themes_url_site{$site_id}", $url, $_path, $_theme);
}

/**
 * Returns full base url of a site's private url.
 * 
 * @since 0.9
 * @return string Site's private base url.
 */
function get_private_site_url()
{
    $site_id = Config::get('site_id');
    $url = get_base_url() . 'private/sites/' . $site_id . '/';
    return app()->hook->{'apply_filter'}("private_site_url{$site_id}", $url);
}

/**
 * Returns full base url of a site's private upload url.
 * 
 * @since 0.9
 * @return string Site's private upload base url.
 */
function get_private_site_upload_url()
{
    $site_id = Config::get('site_id');
    $url = get_private_site_url() . 'uploads/';
    return app()->hook->{'apply_filter'}("private_site_upload_url{$site_id}", $url);
}

/**
 * Searches for plain email addresses in given $string and
 * encodes them (by default) with the help of eae_encode_str().
 *
 * Regular expression is based on based on John Gruber's Markdown.
 * http://daringfireball.net/projects/markdown/
 *
 * @since 0.9
 * @param string $string
 *            Text with email addresses to encode
 * @return string $string Given text with encoded email addresses
 */
function eae_encode_emails($string)
{
    // abort if $string doesn't contain a @-sign
    if (app()->hook->{'apply_filter'}('eae_at_sign_check', true)) {
        if (strpos($string, '@') === false)
            return $string;
    }

    // override encoding function with the 'eae_method' filter
    $method = app()->hook->{'apply_filter'}('eae_method', 'eae_encode_str');

    // override regex pattern with the 'eae_regexp' filter
    $regexp = app()->hook->{'apply_filter'}('eae_regexp', '{
			(?:mailto:)?
			(?:
				[-!#$%&*+/=?^_`.{|}~\w\x80-\xFF]+
			|
				".*?"
			)
			\@
			(?:
				[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
			|
				\[[\d.a-fA-F:]+\]
			)
		}xi');

    return preg_replace_callback($regexp, create_function('$matches', 'return ' . $method . '($matches[0]);'), $string);
}

/**
 * Encodes each character of the given string as either a decimal
 * or hexadecimal entity, in the hopes of foiling most email address
 * harvesting bots.
 *
 * Based on Michel Fortin's PHP Markdown:
 * http://michelf.com/projects/php-markdown/
 * Which is based on John Gruber's original Markdown:
 * http://daringfireball.net/projects/markdown/
 * Whose code is based on a filter by Matthew Wickline, posted to
 * the BBEdit-Talk with some optimizations by Milian Wolff.
 *
 * @since 0.9
 * @param string $string
 *            Text with email addresses to encode
 * @return string $string Given text with encoded email addresses
 */
function eae_encode_str($string)
{
    $chars = str_split($string);
    $seed = mt_rand(0, (int) abs(crc32($string) / strlen($string)));

    foreach ($chars as $key => $char) {

        $ord = ord($char);

        if ($ord < 128) { // ignore non-ascii chars
            $r = ($seed * (1 + $key)) % 100; // pseudo "random function"

            if ($r > 60 && $char != '@')
                ; // plain character (not encoded), if not @-sign
            else
            if ($r < 45)
                $chars[$key] = '&#x' . dechex($ord) . ';'; // hexadecimal
            else
                $chars[$key] = '&#' . $ord . ';'; // decimal (ascii)
        }
    }

    return implode('', $chars);
}

/**
 * Create the needed directories when a new site is created.
 * 
 * @since 0.9
 * @param int $_site_id Site ID.
 * @return bool Returns true on success and false otherwise.
 */
function create_site_directories($_site_id)
{
    try {
        _mkdir(Config::get('sites_dir') . (int) $_site_id . DS . 'dropins' . DS);
        _mkdir(Config::get('sites_dir') . (int) $_site_id . DS . 'files' . DS . 'cache' . DS);
        _mkdir(Config::get('sites_dir') . (int) $_site_id . DS . 'files' . DS . 'logs' . DS);
        _mkdir(Config::get('sites_dir') . (int) $_site_id . DS . 'themes' . DS);
        _mkdir(Config::get('sites_dir') . (int) $_site_id . DS . 'uploads' . DS);
        _mkdir(Config::get('sites_dir') . (int) $_site_id . DS . 'uploads' . DS . '__optimized__' . DS);
    } catch (Exception $ex) {
        Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $ex->getCode(), $ex->getMessage()));
    }

    return true;
}

/**
 * Deletes the site directory when the site is deleted.
 * 
 * @since 0.9
 * @param int $_site_id Site ID.
 * @return bool Returns true on success and false otherwise.
 */
function delete_site_directories($_site_id)
{
    _rmdir(Config::get('sites_dir') . (int) $_site_id . DS);
}

/**
 * Renders an editor.
 * 
 * @since 0.9
 * @param string $selector HTML ID attribute value for the textarea and TinyMCE. Can only be /[a-z]+/.
 */
function ttcms_editor($selector = null)
{
    ttcms_enqueue_js('default', '//cdn.tinymce.com/4/tinymce.min.js');

    if ($selector == null) {
        $mce_selector = '#tinymce_editor';
    } else {
        $mce_selector = $selector;
    }

    /**
     * Filters the default theme for TinyMCE.
     * 
     * @since 0.9
     * @param string $theme Theme used for TinyMCE.
     */
    $mce_theme = app()->hook->{'apply_filter'}('tiny_mce_theme', 'modern');

    $plugins = [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'print', 'preview', 'anchor',
        'searchreplace', 'visualblocks', 'code', 'codesample',
        'insertdatetime', 'media', 'table', 'contextmenu', 'paste'
    ];
    /**
     * Filters the list of default TinyMCE plugins.
     * 
     * @since 0.9
     * @param array $plugins An array of default TinyMCE plugins.
     */
    $mce_plugins = app()->hook->{'apply_filter'}('tiny_mce_plugins', $plugins);

    $mce_buttons = ['undo', 'redo', 'styleselect', 'bold', 'italic', 'alignleft', 'aligncenter', 'alignright', 'alignjustify', 'bullist', 'numlist', 'blockquote', 'outdent', 'indent', 'link', 'image', 'media', 'codesample', 'preview'];
    /**
     * Filters the first-row list of TinyMCE buttons.
     *
     * @since 0.9
     * @param array  $buttons       First-row list of buttons.
     * @param string $mce_selector  Unique editor identifier, e.g. 'textarea'.
     */
    $mce_buttons_1 = app()->hook->{'apply_filter'}('tiny_mce_buttons_1', $mce_buttons, $mce_selector);
    /**
     * Filters the second-row list of TinyMCE buttons.
     *
     * @since 0.9
     * @param array  $buttons       First-row list of buttons.
     * @param string $mce_selector  Unique editor identifier, e.g. 'textarea'.
     */
    $mce_buttons_2 = app()->hook->{'apply_filter'}('tiny_mce_buttons_2', [], $mce_selector);
    /**
     * Filters the third-row list of TinyMCE buttons.
     *
     * @since 0.9
     * @param array  $buttons       First-row list of buttons.
     * @param string $mce_selector  Unique editor identifier, e.g. 'textarea'.
     */
    $mce_buttons_3 = app()->hook->{'apply_filter'}('tiny_mce_buttons_3', [], $mce_selector);
    /**
     * Filters the default stylesheets.
     *
     * @since 0.9
     * @param array  $css           CSS stylesheets to include.
     * @param string $mce_selector  Unique editor identifier, e.g. 'textarea'.
     */
    $mce_css = app()->hook->{'apply_filter'}('tiny_mce_css', ['//fonts.googleapis.com/css?family=Lato:300,300i,400,400i', get_base_url() . 'static/assets/css/tinymce.css'], $mce_selector);

    /**
     * Fires immediately before TinyMCE is printed.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('before_ttcms_tiny_mce');
    ?>
    <script type="text/javascript">
        tinymce.init({
            selector: "<?= $mce_selector; ?>",
            theme: "<?= $mce_theme; ?>",
            browser_spellcheck: true,
            relative_urls: false,
            remove_script_host: false,
            height: 325,
            media_live_embeds: true,
            plugins: ["<?= implode(',', $mce_plugins); ?>"],
            link_list: [
    <?php
    foreach (tinymce_link_list() as $link) : {
            echo "{title: '" . _escape($link['post_title']) . "', value: '" . get_base_url() . _escape($link['post_relative_url']) . "'}," . "\n";
        } endforeach;
    ?>
            ],
            toolbar1: "<?= implode(' ', $mce_buttons_1); ?>",
            toolbar2: "<?= implode(' ', $mce_buttons_2); ?>",
            toolbar3: "<?= implode(' ', $mce_buttons_3); ?>",
            autosave_ask_before_unload: false,
            content_css: [<?= '"' . implode('", "', $mce_css) . '"'; ?>],
            file_picker_callback: elFinderBrowser
        });
        function elFinderBrowser(callback, value, meta) {
            tinymce.activeEditor.windowManager.open({
                file: "<?= get_base_url(); ?>admin/elfinder/",
                title: "elFinder 2.1",
                width: 900,
                height: 600,
                resizable: "yes"
            }, {
                oninsert: function (file) {
                    // Provide file and text for the link dialog
                    if (meta.filetype == "file") {
                        //callback("mypage.html", {text: "My text"});
                        callback(file.url);
                    }

                    // Provide image and alt text for the image dialog
                    if (meta.filetype == "image") {
                        //callback("myimage.jpg", {alt: "My alt text"});
                        callback(file.url, {alt: file.name});
                    }

                    // Provide alternative source and posted for the media dialog
                    if (meta.filetype == "media") {
                        //callback("movie.mp4", {source2: "alt.ogg", poster: "image.jpg"});
                        callback(file.url, {alt: file.name});
                    }
                }
            });
            return false;
        }
        ;
    </script>
    <?php
    /**
     * Fires immediately after TinyMCE is printed.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('after_ttcms_tiny_mce');
}

/**
 * Returns an optimized image for use.
 * 
 * @since 0.9
 * @param string $image Original image file.
 * @return string Optimized image file.
 */
function ttcms_optimized_image_upload($image)
{
    if ($image === '') {
        return null;
    }
    $site_id = Config::get('site_id');
    $raw_filename = str_replace(get_base_url(), '', $image);
    $new_filename = str_replace(get_private_site_upload_url(), 'private/sites/' . $site_id . '/uploads/__optimized__/', $image);
    if (!file_exists($new_filename)) {
        _ttcms_image_optimizer($raw_filename, $new_filename);
    }
    return app()->hook->{'apply_filter'}('optimized_image', $new_filename, $image, $raw_filename);
}

/**
 * Checks if site exists or is archived.
 * 
 * @since 0.9
 */
function is_site_exist()
{
    $base_url = get_base_url();
    $site_path = str_replace('index.php', '', app()->req->server['PHP_SELF']);
    $site_domain = str_replace(['http://', 'https://', $site_path], '', $base_url);

    $site = app()->db->table('site')
            ->where('site_domain', $site_domain)
            ->where('site_path', $site_path)
            ->first();

    if (!$site) {
        app()->res->_format('json', 404);
        exit();
    }

    if (_escape($site['site_status']) === 'archive') {
        app()->res->_format('json', 503);
        exit();
    }
}

/**
 * Post router function.
 *
 * @since 0.9.4
 */
function _ttcms_post_router()
{
    $app = \Liten\Liten::getInstance();
    $router = $app->config('routers_dir') . 'post.router.php';
    if (!$app->hook->{'has_filter'}('post_router')) {
        require($router);
    }
    return $app->hook->{'apply_filter'}('post_router', $router);
}

/**
 * Sets the scheme for a URL.
 *
 * @since 0.9.4
 * @param string      $url    Absolute URL that includes a scheme
 * @param string|null $scheme Optional. Scheme to give $url. Currently 'http', 'https', 'login',
 *                            'admin', 'relative', 'rest' or null. Default null.
 * @return string $url URL with chosen scheme.
 */
function set_url_scheme($url, $scheme = null)
{
    $orig_scheme = $scheme;

    if (!$scheme) {
        $scheme = is_ssl() ? 'https' : 'http';
    } elseif ($scheme === 'admin' || $scheme === 'login') {
        $scheme = is_ssl() ? 'https' : 'http';
    } elseif ($scheme !== 'http' && $scheme !== 'https' && $scheme !== 'relative') {
        $scheme = is_ssl() ? 'https' : 'http';
    }

    $url = trim($url);
    if (substr($url, 0, 2) === '//') {
        $url = 'http:' . $url;
    }

    if ('relative' == $scheme) {
        $url = ltrim(preg_replace('#^\w+://[^/]*#', '', $url));
        if ($url !== '' && $url[0] === '/') {
            $url = '/' . ltrim($url, "/ \t\n\r\0\x0B");
        }
    } else {
        $url = preg_replace('#^\w+://#', $scheme . '://', $url);
    }

    /**
     * Filters the resulting URL after setting the scheme.
     *
     * @since 0.9.4
     *
     * @param string      $url         The complete URL including scheme and path.
     * @param string      $scheme      Scheme applied to the URL. One of 'http', 'https', or 'relative'.
     * @param string|null $orig_scheme Scheme requested for the URL. One of 'http', 'https', 'login',
     *                                 'admin', 'relative', 'rest' or null.
     */
    return app()->hook->{'apply_filter'}('set_url_scheme', $url, $scheme, $orig_scheme);
}

/**
 * Adds missing files to site's cache directory.
 *
 * @since 0.9.5
 */
function add_files_cache_directory()
{
    $dir = Config::get('cache_path');

    try {
        /**
         * Re-creates the cache directory with proper permissions.
         */
        _mkdir($dir);
    } catch (IOException $e) {
        Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
    }

    $key = _ttcms_random_lib()->generate(25, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

    if (!file_exists($dir . '.htaccess')) {
        $content = "# BEGIN Privatization" . "\n";
        $content .= "# This .htaccess file ensures that other people cannot download your files." . "\n";
        $content .= "<IfModule mod_rewrite.c>" . "\n";
        $content .= "RewriteEngine On" . "\n";
        $content .= "RewriteCond %{QUERY_STRING} !key=$key" . "\n";
        $content .= "RewriteRule (.*) - [F]" . "\n";
        $content .= "</IfModule>" . "\n";
        $content .= "# END Privatization";
        file_put_contents($dir . '.htaccess', $content);
    }

    if (!file_exists($dir . '.gitignore')) {
        $content = "*" . "\n";
        $content .= "*/" . "\n";
        $content .= "!.gitignore";
        file_put_contents($dir . '.gitignore', $content);
    }
}

/**
 * Default actions and filters.
 * 
 * @since 0.9
 */
app()->hook->{'add_action'}('ttcms_admin_head', 'head_release_meta', 5);
app()->hook->{'add_action'}('ttcms_head', 'head_release_meta', 5);
app()->hook->{'add_action'}('ttcms_head', 'post_css', 5, 2);
app()->hook->{'add_action'}('ttcms_footer', 'post_js', 5, 2);
app()->hook->{'add_action'}('ttcms_release', 'foot_release', 5);
app()->hook->{'add_action'}('activated_plugin', 'ttcms_plugin_activate_message', 5);
app()->hook->{'add_action'}('deactivated_plugin', 'ttcms_plugin_deactivate_message', 5);
app()->hook->{'add_action'}('login_form_top', 'ttcms_login_form_show_message', 5);
app()->hook->{'add_action'}('admin_notices', 'ttcms_dev_mode', 5);
app()->hook->{'add_action'}('site_register', 'new_site_data', 5, 2);
app()->hook->{'add_action'}('site_register', 'create_site_directories', 5);
app()->hook->{'add_action'}('delete_site', 'delete_site_user_meta', 5);
app()->hook->{'add_action'}('delete_site', 'delete_site_tables', 5);
app()->hook->{'add_action'}('delete_site', 'delete_site_directories', 5);
app()->hook->{'add_action'}('init', 'update_main_site', 5);
app()->hook->{'add_action'}('reset_password_route', 'send_reset_password_email', 5, 2);
app()->hook->{'add_action'}('password_change_email', 'send_password_change_email', 5, 3);
app()->hook->{'add_action'}('email_change_email', 'send_email_change_email', 5, 2);
app()->hook->{'add_action'}('before_router_login', 'update_main_site', 5);
app()->hook->{'add_action'}('before_router_login', 'is_site_exist', 6);
app()->hook->{'add_action'}('ttcms_login', 'generate_php_encryption', 5);
app()->hook->{'add_action'}('enqueue_ttcms_editor', 'ttcms_editor', 5);
app()->hook->{'add_action'}('protect_cache_dir', 'add_files_cache_directory', 5);
app()->hook->{'add_filter'}('the_content', 'ttcms_autop');
app()->hook->{'add_filter'}('the_content', 'parsecode_unautop');
app()->hook->{'add_filter'}('the_content', 'do_parsecode', 5);
app()->hook->{'add_filter'}('the_content', 'eae_encode_emails', EAE_FILTER_PRIORITY);
app()->hook->{'add_filter'}('ttcms_authenticate_user', 'ttcms_authenticate', 5, 3);
app()->hook->{'add_filter'}('ttcms_auth_cookie', 'ttcms_set_auth_cookie', 5, 2);
