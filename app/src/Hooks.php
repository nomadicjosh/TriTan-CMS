<?php namespace TriTan;
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;

/**
 * Liten - PHP 5 micro framework
 *
 * @link http://www.litenframework.com
 * @version 1.0.1
 * @package Liten
 *         
 *          The MIT License (MIT)
 *          Copyright (c) 2015 Joshua Parker
 *         
 *          Permission is hereby granted, free of charge, to any person obtaining a copy
 *          of this software and associated documentation files (the "Software"), to deal
 *          in the Software without restriction, including without limitation the rights
 *          to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *          copies of the Software, and to permit persons to whom the Software is
 *          furnished to do so, subject to the following conditions:
 *         
 *          The above copyright notice and this permission notice shall be included in
 *          all copies or substantial portions of the Software.
 *         
 *          THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *          IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *          FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *          AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *          LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *          OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *          THE SOFTWARE.
 */
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

class Hooks
{

    /**
     *
     * @access public
     * @var object
     *
     */
    public $app;

    /**
     *
     * @access protected
     * @var string
     *
     */
    public $_plugins_dir;

    /**
     *
     * @access protected
     * @var array
     *
     */
    public $_filters = [];

    /**
     *
     * @access protected
     * @var string
     *
     */
    public $_actions = [];

    /**
     *
     * @access protected
     * @var array
     *
     */
    public $_merged_filters = [];

    /**
     *
     * @access protected
     * @var string
     *
     */
    public $_current_filter = [];

    /**
     * all plugins header information in an array.
     *
     * @access protected
     * @var array
     */
    public $_plugins_header = [];

    /**
     *
     * @access protected
     * @var string
     *
     */
    public $_error = [];

    /**
     * __construct class constructor
     *
     * @access public
     * @since 1.0.1
     */
    public function __construct(\Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();
    }

    /**
     * Returns the plugin header information
     *
     * @access public
     * @since 1.0.1
     * @param
     *            string (optional) $plugins_dir Loads plugins from specified folder
     * @return mixed
     *
     */
    public function get_plugins_header($plugins_dir = '')
    {
        if ($handle = opendir($plugins_dir)) {

            while ($file = readdir($handle)) {
                if (is_file($plugins_dir . $file)) {
                    if (strpos($plugins_dir . $file, '.plugin.php')) {
                        $fp = fopen($plugins_dir . $file, 'r');
                        // Pull only the first 8kiB of the file in.
                        $plugin_data = fread($fp, 8192);
                        fclose($fp);

                        preg_match('|Plugin Name:(.*)$|mi', $plugin_data, $name);
                        preg_match('|Plugin URI:(.*)$|mi', $plugin_data, $uri);
                        preg_match('|Version:(.*)|i', $plugin_data, $version);
                        preg_match('|Description:(.*)$|mi', $plugin_data, $description);
                        preg_match('|Author:(.*)$|mi', $plugin_data, $author_name);
                        preg_match('|Author URI:(.*)$|mi', $plugin_data, $author_uri);
                        preg_match('|Plugin Slug:(.*)$|mi', $plugin_data, $plugin_slug);

                        foreach (array(
                        'name',
                        'uri',
                        'version',
                        'description',
                        'author_name',
                        'author_uri',
                        'plugin_slug'
                        ) as $field) {
                            if (!empty(${$field}))
                                ${$field} = trim(${$field}[1]);
                            else
                                ${$field} = '';
                        }
                        $plugin_data = array(
                            'filename' => $file,
                            'Name' => $name,
                            'Title' => $name,
                            'PluginURI' => $uri,
                            'Description' => $description,
                            'Author' => $author_name,
                            'AuthorURI' => $author_uri,
                            'Version' => $version
                        );
                        $this->_plugins_header[] = $plugin_data;
                    }
                } else
                if ((is_dir($plugins_dir . $file)) && ($file != '.') && ($file != '..')) {
                    $this->get_plugins_header($plugins_dir . $file . '/');
                }
            }

            closedir($handle);
        }
        return $this->_plugins_header;
    }

    /**
     * Activates a specific plugin that is called by $_GET['id'] variable.
     *
     * @access public
     * @since 1.0.1
     * @param string $plugin
     *            ID of the plugin to activate
     * @return mixed
     *
     */
    public function activate_plugin($plugin)
    {
        $this->app->db->table(Config::get('tbl_prefix') . 'plugin')->insert([
            'plugin_id' => auto_increment(Config::get('tbl_prefix') . 'plugin', 'plugin_id'),
            'plugin_location' => $plugin
        ]);
    }

    /**
     * Deactivates a specific plugin that is called by $_GET['id'] variable.
     *
     * @access public
     * @since 1.0.1
     * @param string $plugin
     *            ID of the plugin to deactivate.
     *            
     */
    public function deactivate_plugin($plugin)
    {
        $this->app->db->table(Config::get('tbl_prefix') . 'plugin')
            ->where('plugin_location', $plugin)
            ->delete();
    }

    /**
     * Loads all activated plugin for inclusion.
     *
     * @access public
     * @since 1.0.1
     * @param
     *            string (optional) $plugins_dir Loads plugins from specified folder
     * @return mixed
     */
    public function load_activated_plugins($plugins_dir)
    {
        $plugin = $this->app->db->table(Config::get('tbl_prefix') . 'plugin');
        $q = $plugin->all();

        foreach ($q as $v) {
            $pluginFile = _escape($v['plugin_location']);
            $plugin = str_replace('.plugin.php', '', $pluginFile);

            if (!ttcms_file_exists($plugins_dir . $plugin . DS . $pluginFile, false)) {
                $file = $plugins_dir . $pluginFile;
            } else {
                $file = $plugins_dir . $plugin . DS . $pluginFile;
            }

            $error = ttcms_php_check_syntax($file);
            if (is_ttcms_exception($error)) {
                $this->deactivate_plugin(_escape($v['plugin_location']));
                _ttcms_flash()->error(sprintf(_t('The plugin <strong>%s</strong> has been deactivated because your changes resulted in a <strong>fatal error</strong>. <br /><br />') . $error->getMessage(), _escape($v['plugin_location'])));
                return false;
            }

            if (ttcms_file_exists($file, false)) {
                require_once ($file);
            } else {
                $this->deactivate_plugin(_escape($v['plugin_location']));
            }
        }
    }

    /**
     * Checks if a particular plugin is activated
     *
     * @since 1.0.1
     * @return mixed
     *
     */
    public function is_plugin_activated($plugin)
    {
        $active = $this->app->db->table(Config::get('tbl_prefix') . 'plugin')->where('plugin_location', $plugin)->first();

        if (@count($active) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Registers a filtering function
     *
     * Typical use: hooks::add_filter('some_hook', 'function_handler_for_hook');
     *
     * @access public
     * @since 1.0.1
     * @global array $filters Storage for all of the filters
     * @param string $hook
     *            the name of the PM element to be filtered or PM action to be triggered
     * @param callback $function
     *            the name of the function that is to be called.
     * @param integer $priority
     *            optional. Used to specify the order in which the functions associated with a particular action are executed (default=10, lower=earlier execution, and functions with the same priority are executed in the order in which they were added to the filter)
     * @param int $accepted_args
     *            optional. The number of arguments the function accept (default is the number provided).
     */
    public function add_filter($hook, $function_to_add, $priority = 10, $accepted_args = 1)
    {

        // At this point, we cannot check if the function exists, as it may well be defined later (which is OK)
        $id = $this->filter_unique_id($hook, $function_to_add, $priority);

        $this->_filters[$hook][$priority][$id] = [
            'function' => $function_to_add,
            'accepted_args' => $accepted_args
        ];
        unset($this->_merged_filters[$hook]);
        return true;
    }

    /**
     * add_action
     * Adds a hook
     *
     * @access public
     * @since 1.0.1
     * @param string $hook            
     * @param string $function_to_add            
     * @param integer $priority
     *            (optional)
     * @param integer $accepted_args
     *            (optional)
     *            
     */
    public function add_action($hook, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return $this->add_filter($hook, $function_to_add, $priority, $accepted_args);
    }

    /**
     * remove_action Removes a function from a specified action hook.
     *
     * @access public
     * @since 1.0.1
     * @param string $hook
     *            The action hook to which the function to be removed is hooked.
     * @param callback $function_to_remove
     *            The name of the function which should be removed.
     * @param int $priority
     *            optional The priority of the function (default: 10).
     * @return boolean Whether the function is removed.
     */
    public function remove_action($hook, $function_to_remove, $priority = 10)
    {
        return $this->remove_filter($hook, $function_to_remove, $priority);
    }

    /**
     * remove_all_actions Remove all of the hooks from an action.
     *
     * @access public
     * @since 1.0.1
     * @param string $hook
     *            The action to remove hooks from.
     * @param int $priority
     *            The priority number to remove them from.
     * @return bool True when finished.
     */
    public function remove_all_actions($hook, $priority = false)
    {
        return $this->remove_all_filters($hook, $priority);
    }

    /**
     * Build Unique ID for storage and retrieval.
     *
     * Simply using a function name is not enough, as several functions can have the same name when they are enclosed in classes.
     *
     * @access public
     * @since 1.0.1
     * @param string $hook            
     * @param string|array $function
     *            used for creating unique id
     * @param int|bool $priority
     *            used in counting how many hooks were applied. If === false and $function is an object reference, we return the unique id only if it already has one, false otherwise.
     * @return string unique ID for usage as array key
     */
    public function filter_unique_id($hook, $function, $priority)
    {
        static $filter_id_count = 0;

        // If function then just skip all of the tests and not overwrite the following.
        if (is_string($function))
            return $function;
        if (is_object($function)) {
            // Closures are currently implemented as objects
            $function = array(
                $function,
                ''
            );
        } else {
            $function = (array) $function;
        }

        if (is_object($function[0])) {
            // Object Class Calling
            if (function_exists('spl_object_hash')) {
                return spl_object_hash($function[0]) . $function[1];
            } else {
                $obj_idx = get_class($function[0]) . $function[1];
                if (!isset($function[0]->_filters_id)) {
                    if (false === $priority)
                        return false;
                    $obj_idx .= isset($this->_filters[$hook][$priority]) ? count((array) $this->_filters[$hook][$priority]) : $filter_id_count;
                    $function[0]->_filters_id = $filter_id_count;
                    ++$filter_id_count;
                } else {
                    $obj_idx .= $function[0]->_filters_id;
                }

                return $obj_idx;
            }
        } else
        if (is_string($function[0])) {
            // Static Calling
            return $function[0] . '::' . $function[1];
        }
    }

    /**
     * Performs a filtering operation on a PM element or event.
     *
     * Typical use:
     *
     * 1) Modify a variable if a function is attached to hook 'hook'
     * $var = "default value";
     * $var = hooks::apply_filter( 'hook', $var );
     *
     * 2) Trigger functions is attached to event 'pm_event'
     * hooks::apply_filter( 'event' );
     * (see hooks::do_action() )
     *
     * Returns an element which may have been filtered by a filter.
     *
     * @access public
     * @since 1.0.1
     * @global array $filters storage for all of the filters
     * @param string $hook
     *            the name of the the element or action
     * @param mixed $value
     *            the value of the element before filtering
     * @return mixed
     */
    public function apply_filter($hook, $value)
    {
        $args = [];

        if (isset($this->_filters['all'])) {
            $this->_current_filter[] = $hook;
            $args = func_get_args();
            $this->_call_all_hook($args);
        }

        if (!isset($this->_filters[$hook])) {
            if (isset($this->_filters['all']))
                array_pop($this->_current_filter);
            return $value;
        }

        if (!isset($this->_filters['all'])) {
            $this->_current_filter[] = $hook;
        }

        if (!isset($this->_merged_filters[$hook])) {
            ksort($this->_filters[$hook]);
            $this->_merged_filters[$hook] = true;
        }

        // Loops through each filter
        reset($this->_filters[$hook]);

        if (empty($args)) {
            $args = func_get_args();
        }

        do {
            foreach ((array) current($this->_filters[$hook]) as $the_)
                if (!is_null($the_['function'])) {
                    $args[1] = $value;
                    $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
                }
        } while (next($this->_filters[$hook]) !== false);

        array_pop($this->_current_filter);

        return $value;
    }

    public function do_action($hook, $arg = '')
    {
        if (!isset($this->_actions))
            $this->_actions = [];

        if (!isset($this->_actions[$hook]))
            $this->_actions[$hook] = 1;
        else
            ++$this->_actions[$hook];

        // Do 'all' actions first
        if (isset($this->_filters['all'])) {
            $this->_current_filter[] = $hook;
            $all_args = func_get_args();
            $this->_call_all_hook($all_args);
        }

        if (!isset($this->_filters[$hook])) {
            if (isset($this->_filters['all']))
                array_pop($this->_current_filter);
            return;
        }

        if (!isset($this->_filters['all']))
            $this->_current_filter[] = $hook;

        $args = [];
        if (is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0])) // array(&$this)
            $args[] = & $arg[0];
        else
            $args[] = $arg;
        for ($a = 2; $a < func_num_args(); $a ++)
            $args[] = func_get_arg($a);

        // Sort
        if (!isset($this->_merged_filters[$hook])) {
            ksort($this->_filters[$hook]);
            $this->_merged_filters[$hook] = true;
        }

        reset($this->_filters[$hook]);

        do {
            foreach ((array) current($this->_filters[$hook]) as $the_)
                if (!is_null($the_['function']))
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
        } while (next($this->_filters[$hook]) !== false);

        array_pop($this->_current_filter);
    }

    public function _call_all_hook($args)
    {
        reset($this->_filters['all']);
        do {
            foreach ((array) current($this->_filters['all']) as $the_)
                if (!is_null($the_['function']))
                    call_user_func_array($the_['function'], $args);
        } while (next($this->_filters['all']) !== false);
    }

    public function do_action_array($hook, $args)
    {
        if (!isset($this->_actions))
            $this->_actions = [];

        if (!isset($this->_actions[$hook]))
            $this->_actions[$hook] = 1;
        else
            ++$this->_actions[$hook];

        // Do 'all' actions first
        if (isset($this->_filters['all'])) {
            $this->_current_filter[] = $hook;
            $all_args = func_get_args();
            $this->_call_all_hook($all_args);
        }

        if (!isset($this->_filters[$hook])) {
            if (isset($this->_filters['all']))
                array_pop($this->_current_filter);
            return;
        }

        if (!isset($this->_filters['all']))
            $this->_current_filter[] = $hook;

        // Sort
        if (!isset($this->_merged_filters[$hook])) {
            ksort($this->_filters[$hook]);
            $this->_merged_filters[$hook] = true;
        }

        reset($this->_filters[$hook]);

        do {
            foreach ((array) current($this->_filters[$hook]) as $the_)
                if (!is_null($the_['function']))
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
        } while (next($this->_filters[$hook]) !== false);

        array_pop($this->_current_filter);
    }

    /**
     * Removes a function from a specified filter hook.
     *
     * This function removes a function attached to a specified filter hook. This
     * method can be used to remove default functions attached to a specific filter
     * hook and possibly replace them with a substitute.
     *
     * To remove a hook, the $function_to_remove and $priority arguments must match
     * when the hook was added.
     *
     * @global array $filters storage for all of the filters
     * @param string $hook
     *            The filter hook to which the function to be removed is hooked.
     * @param callback $function_to_remove
     *            The name of the function which should be removed.
     * @param int $priority
     *            optional. The priority of the function (default: 10).
     * @param int $accepted_args
     *            optional. The number of arguments the function accepts (default: 1).
     * @return boolean Whether the function was registered as a filter before it was removed.
     */
    public function remove_filter($hook, $function_to_remove, $priority = 10)
    {
        $function_to_remove = $this->filter_unique_id($hook, $function_to_remove, $priority);

        $remove = isset($this->_filters[$hook][$priority][$function_to_remove]);

        if (true === $remove) {
            unset($this->_filters[$hook][$priority][$function_to_remove]);
            if (empty($this->_filters[$hook][$priority]))
                unset($this->_filters[$hook][$priority]);
            unset($this->_merged_filters[$hook]);
        }
        return $remove;
    }

    /**
     * remove_all_filters Remove all of the hooks from a filter.
     *
     * @access public
     * @since 1.0.1
     * @param string $hook
     *            The filter to remove hooks from.
     * @param int $priority
     *            The priority number to remove.
     * @return bool True when finished.
     */
    public function remove_all_filters($hook, $priority = false)
    {
        if (isset($this->_filters[$hook])) {
            if (false !== $priority && isset($this->_filters[$hook][$priority]))
                unset($this->_filters[$hook][$priority]);
            else
                unset($this->_filters[$hook]);
        }

        if (isset($this->_merged_filters[$hook]))
            unset($this->_merged_filters[$hook]);

        return true;
    }

    /**
     * Check if any filter has been registered for a hook.
     *
     * @global array $filters storage for all of the filters
     * @param string $hook
     *            The name of the filter hook.
     * @param callback $function_to_check
     *            optional. If specified, return the priority of that function on this hook or false if not attached.
     * @return int|boolean Optionally returns the priority on that hook for the specified function.
     */
    public function has_filter($hook, $function_to_check = false)
    {
        $has = !empty($this->_filters[$hook]);
        if (false === $function_to_check || false == $has) {
            return $has;
        }
        if (!$idx = $this->filter_unique_id($hook, $function_to_check, false)) {
            return false;
        }

        foreach ((array) array_keys($this->_filters[$hook]) as $priority) {
            if (isset($this->_filters[$hook][$priority][$idx]))
                return $priority;
        }
        return false;
    }

    public function has_action($hook, $function_to_check = false)
    {
        return $this->has_filter($hook, $function_to_check);
    }

    /**
     * Display list of links to plugin admin pages, if any
     */
    public function list_plugin_admin_pages($url)
    {
        if (!property_exists($this->app->hook, 'plugin_pages') || !$this->app->hook->plugin_pages)
            return;

        foreach ((array) $this->app->hook->plugin_pages as $page) {
            echo '<li><a href="' . $url . '?page=' . $page['slug'] . '">' . $page['title'] . '</a></li>' . "\n";
        }
    }

    /**
     * Register a plugin administration page
     *
     * @param string $slug            
     * @param string $title            
     * @param string $function            
     */
    public function register_admin_page($slug, $title, $function)
    {
        if (!property_exists($this->app->hook, 'plugin_pages') || !$this->app->hook->plugin_pages)
            $this->app->hook->plugin_pages = [];

        $this->app->hook->plugin_pages[$slug] = [
            'slug' => $slug,
            'title' => $title,
            'function' => $function
        ];
    }

    /**
     * Handle plugin administration page
     *
     * @param string $plugin_page            
     */
    public function plugin_admin_page($plugin_page)
    {

        // Check the plugin page is actually registered
        if (!isset($this->app->hook->plugin_pages[$plugin_page])) {
            die('This page does not exist. Maybe a plugin you thought was activated is inactive?');
        }

        // Draw the page itself
        $this->do_action('load-' . $plugin_page);

        call_user_func($this->app->hook->plugin_pages[$plugin_page]['function']);
    }

    /**
     * Read an option from options_meta.
     * Return value or $default if not found
     */
    public function get_option($option_key, $default = false)
    {
        $option_key = trim($option_key);
        if (empty($option_key)) {
            return false;
        }

        /**
         * Filter the value of an existing option before it is retrieved.
         *
         * The dynamic portion of the hook name, `$option_key`, refers to the option_key name.
         *
         * Passing a truthy value to the filter will short-circuit retrieving
         * the option value, returning the passed value instead.
         *
         * @since 1.0.0
         *       
         * @param bool|mixed $pre_option
         *            Value to return instead of the option value.
         *            Default false to skip it.
         * @param string $option_key
         *            Meta key name.
         */
        $pre = $this->apply_filter('pre_option_' . $option_key, false);

        if (false !== $pre) {
            return $pre;
        }

        if (!isset($this->app->db->option[$option_key])) {
            $meta = $this->app->db->table(Config::get('tbl_prefix') . 'option');
            $result = ttcms_cache_get($option_key, 'option');
            if (empty($result)) {
                $result = $meta->where('option_key', $option_key)
                    ->first();
                ttcms_cache_add($option_key, $result, 'option');
            }

            if (is_object($meta)) {
                $value = _escape($result['option_value']);
                //return _escape($value);
            } else { // option does not exist, so we must cache its non-existence
                $value = _escape($default);
                //return _escape($value);
            }
            $this->app->db->option[$option_key] = $this->maybe_unserialize($value);
        }
        /**
         * Filter the value of an existing option.
         *
         * The dynamic portion of the hook name, `$option_key`, refers to the option name.
         *
         * @since 1.0.0 As 'get_option_' . $setting
         *       
         * @param mixed $value
         *            Value of the option. If stored serialized, it will be
         *            unserialized prior to being returned.
         * @param string $this->app->db->option[$option_key]
         *            Option name.
         */
        return $this->apply_filter('get_option_' . $option_key, $this->app->db->option[$option_key]);
    }

    /**
     * Retrieve a site's option value based on the option name.
     *
     * @since 1.0.0
     * @param int      $site_id     ID of the site. Can be null to default to the current site.
     * @param string   $option      Name of option to retrieve.
     * @param mixed    $default     Optional. Value to return if the option doesn't exist. Default false.
     * @return mixed Value set for the option.
     */
    public function get_site_option($site_id, $option, $default = false)
    {
        if ($site_id && !is_numeric($site_id)) {
            return false;
        }

        // Fallback to the current site if a site_id is not specified.
        if (!$site_id) {
            $site_id = get_current_site_id();
        }

        $_site_id = (int) $site_id;

        /**
         * Filters an existing site option before it is retrieved.
         *
         * The dynamic portion of the hook name, `$option`, refers to the option name.
         *
         * Passing a truthy value to the filter will effectively short-circuit retrieval,
         * returning the passed value instead.
         *
         * @since 1.0.0
         * @param mixed  $pre_option    The value to return instead of the option value. This differs from
         *                              `$default`, which is used as the fallback value in the event the
         *                              option doesn't exist elsewhere in get_site_option(). Default
         *                              is false (to skip past the short-circuit).
         * @param string $option        Option name.
         * @param int    $_site_id      ID of the site.
         * @param mixed  $default       The fallback value to return if the option does not exist.
         *                              Default is false.
         */
        $pre = $this->apply_filter("pre_site_option_{$option}", false, $option, $_site_id, $default);

        if (false !== $pre) {
            return $pre;
        }

        $cache_key = "{$_site_id}_{$option}";
        $value = ttcms_cache_get($cache_key, 'site-options');

        if (!isset($value) || false === $value) {
            $row = $this->app->db->table('sitemeta')->where('meta_key', $option)->where('site_id', $_site_id)->first();

            if (is_array($row)) {
                $value = _escape($row['meta_value']);
                $value = $this->maybe_unserialize($value);
                ttcms_cache_set($cache_key, $value, 'site-options');
            } else {
                $value = $this->apply_filter("default_site_option_{$option}", $default, $option, $_site_id);
            }
        }

        /**
         * Filters the value of an existing site option.
         *
         * The dynamic portion of the hook name, `$option`, refers to the option name.
         *
         * @since 1.0.0
         * @param mixed  $value     Value of site option.
         * @param string $option    Option name.
         * @param int    $_site_id  ID of the site.
         */
        return $this->apply_filter("site_option_{$option}", $value, $option, $_site_id);
    }

    /**
     * Update (add if doesn't exist) an option to options_meta
     */
    public function update_option($option_key, $newvalue)
    {
        $oldvalue = $this->get_option($option_key);

        // If the new and old values are the same, no need to update.
        if ($newvalue === $oldvalue) {
            return false;
        }

        if ('' === $oldvalue) {
            $this->add_option($option_key, $newvalue);
            return true;
        }

        $_newvalue = $this->maybe_serialize($newvalue);

        ttcms_cache_delete($option_key, 'option');

        $this->do_action('update_option', $option_key, $oldvalue, $newvalue);

        $key = $this->app->db->table(Config::get('tbl_prefix') . 'option');
        $key->begin();
        $option_value = $_newvalue;
        try {
            $key->where('option_key', $option_key)->update([
                'option_value' => $option_value == '' ? 'null' : if_null($option_value)
            ]);

            if (count($key) > 0) {
                $this->app->db->option[$option_key] = $newvalue;
            }
            $key->commit();
        } catch (Exception $e) {
            $key->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
            _ttcms_flash()->error(_ttcms_flash()->notice(409));
        }
    }

    /**
     * Update the value of a site option that was already added.
     *
     * @since 1.0.0
     * @param int      $site_id ID of the site. Can be null to default to the current site ID.
     * @param string   $option  Name of option. Expected to not be SQL-escaped.
     * @param mixed    $value   Option value. Expected to not be SQL-escaped.
     * @return bool False if value was not updated and true if value was updated.
     */
    public function update_site_option($site_id, $option, $value)
    {
        if ($site_id && !is_numeric($site_id)) {
            return false;
        }

        // Fallback to the current site if a site ID is not specified.
        if (!$site_id) {
            $site_id = get_current_site_id();
        }

        $_site_id = (int) $site_id;

        $old_value = $this->get_site_option($_site_id, $option, false);

        /**
         * Filters a specific site option before its value is updated.
         *
         * The dynamic portion of the hook name, `$option`, refers to the option name.
         *
         * @since 1.0.0
         * @param mixed  $value     New value of the site option.
         * @param mixed  $old_value Old value of the site option.
         * @param string $option    Option name.
         * @param int    $_site_id  ID of the site.
         */
        $value = apply_filters("pre_update_site_option_{$option}", $value, $old_value, $option, $_site_id);

        if ($value === $old_value) {
            return false;
        }

        if (false === $old_value) {
            return $this->add_site_option($_site_id, $option, $value);
        }

        $result = $this->app->db->table('sitemeta');
        $result->begin();
        try {
            $result->where('site_id', $_site_id)
                ->where('meta_key', $option)
                ->update([
                    'meta_value' => $this->maybe_serialize($value)
            ]);
            $result->commit();
        } catch (Exception $ex) {
            $result->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: Error: %s', $ex->getCode(), $ex->getMessage()));
        }


        if ($result) {
            $cache_key = "{$_site_id}_{$option}";
            ttcms_cache_set($cache_key, $value, 'site-options');
        }

        if ($result) {

            /**
             * Fires after the value of a specific site option has been successfully updated.
             *
             * The dynamic portion of the hook name, `$option`, refers to the option name.
             *
             * @since 1.0.0
             * @param string $option    Name of the site option.
             * @param mixed  $value     Current value of the site option.
             * @param mixed  $old_value Old value of the site option.
             * @param int    $_site_id  ID of the site.
             */
            $this->do_action("update_site_option_{$option}", $option, $value, $old_value, $_site_id);

            /**
             * Fires after the value of a site option has been successfully updated.
             *
             * @since 1.0.0
             * @param string $option        Name of the site option.
             * @param mixed  $value         Current value of the site option.
             * @param mixed  $old_value     Old value of the site option.
             * @param int    $_site_id      ID of the site.
             */
            $this->do_action('update_site_option', $option, $value, $old_value, $_site_id);

            return true;
        }

        return false;
    }

    /**
     * Add an option to the table
     */
    public function add_option($name, $value = '')
    {
        // Make sure the option doesn't already exist
        if ('' !== $this->get_option($name)) {
            return;
        }

        $_value = $this->maybe_serialize($value);

        ttcms_cache_delete($name, 'option');

        $this->do_action('add_option', $name, $_value);

        $add = $this->app->db->table(Config::get('tbl_prefix') . 'option');
        $add->begin();
        try {
            $option_value = $_value;
            $add->insert([
                'option_id' => (int) auto_increment(Config::get('tbl_prefix') . 'option', 'option_id'),
                'option_key' => (string) $name,
                'option_value' => if_null($option_value)
            ]);
            $this->app->db->option[$name] = $value;
            $add->commit();
            return;
        } catch (Exception $e) {
            $add->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
            _ttcms_flash()->error(_ttcms_flash()->notice(409));
        }
    }

    /**
     * Add a new site option.
     *
     * Existing options will not be updated.
     *
     * @since 1.0.0
     * @param int    $site_id   ID of the site. Can be null to default to the current site ID.
     * @param string $option    Name of option to add. Expected to not be SQL-escaped.
     * @param mixed  $value     Option value, can be anything. Expected to not be SQL-escaped.
     * @return bool False if option was not added and true if option was added.
     */
    public function add_site_option($site_id, $option, $value)
    {
        if ($site_id && !is_numeric($site_id)) {
            return false;
        }

        // Fallback to the current site if a site ID is not specified.
        if (!$site_id) {
            $site_id = get_current_site_id();
        }

        $_site_id = (int) $site_id;

        /**
         * Filters the value of a specific site option before it is added.
         *
         * The dynamic portion of the hook name, `$option`, refers to the option name.
         *
         * @since 2.9.0 As 'pre_add_site_option_' . $key
         * @since 3.0.0
         * @since 4.4.0 The `$option` parameter was added.
         * @since 4.7.0 The `$_site_id` parameter was added.
         *
         * @param mixed  $value     Value of site option.
         * @param string $option    Option name.
         * @param int    $_site_id  ID of the site.
         */
        $value = $this->apply_filter("pre_add_site_option_{$option}", $value, $option, $_site_id);

        $cache_key = "{$_site_id}_{$option}";
        if (false !== $this->get_site_option($_site_id, $option, false)) {
            return false;
        }

        $result = $this->app->db->table('sitemeta');
        $result->begin();
        try {
            $result->insert([
                'meta_id' => auto_increment('sitemeta', 'meta_id'),
                'site_id' => (int) $_site_id,
                'meta_key' => (string) $option,
                'meta_value' => $this->maybe_serialize($value)
            ]);
            $result->commit();
        } catch (Exception $ex) {
            $result->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: Error: %s', $ex->getCode(), $ex->getMessage()));
        }


        if (!$result) {
            return false;
        }

        ttcms_cache_set($cache_key, $value, 'site-options');

        if ($result) {

            /**
             * Fires after a specific site option has been successfully added.
             *
             * The dynamic portion of the hook name, `$option`, refers to the option name.
             *
             * @since 1.0.0
             * @param string $option    Name of the site option.
             * @param mixed  $value     Value of the site option.
             * @param int    $_site_id  ID of the site.
             */
            $this->do_action("add_site_option_{$option}", $option, $value, $_site_id);

            /**
             * Fires after a site option has been successfully added.
             *
             * @since 1.0.0
             * @param string $option    Name of the site option.
             * @param mixed  $value     Value of the site option.
             * @param int    $_site_id  ID of the site.
             */
            $this->do_action('add_site_option', $option, $value, $_site_id);

            return true;
        }

        return false;
    }

    /**
     * Delete an option from the table
     */
    public function delete_option($name)
    {
        $delete = $this->app->db->table(Config::get('tbl_prefix') . 'option');
        $delete->begin();
        try {
            $key = $this->app->db->table(Config::get('tbl_prefix') . 'option')->where('option_key', $name);
            $results = $key->first();

            if (is_null($results) || !$results) {
                return false;
            }

            ttcms_cache_delete($name, 'option');

            $this->do_action('delete_option', $name);

            $delete->where('option_key', $name)
                ->delete();
            $delete->commit();
            return true;
        } catch (Exception $e) {
            $delete->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
            _ttcms_flash()->error(_ttcms_flash()->notice(409));
        }
    }

    /**
     * Removes a site option by name.
     *
     * @since 1.0.0
     * @param int    $site_id   ID of the site. Can be null to default to the current site ID.
     * @param string $option    Name of option to remove. Expected to not be SQL-escaped.
     * @return bool True, if succeed. False, if failure.
     */
    public function delete_site_option($site_id, $option)
    {
        if ($site_id && !is_numeric($site_id)) {
            return false;
        }

        // Fallback to the current site if a site ID is not specified.
        if (!$site_id) {
            $site_id = get_current_site_id();
        }

        $_site_id = (int) $site_id;

        /**
         * Fires immediately before a specific site option is deleted.
         *
         * The dynamic portion of the hook name, `$option`, refers to the option name.
         *
         * @since 1.0.0
         * @param string $option    Option name.
         * @param int    $_site_id  ID of the site.
         */
        $this->do_action("pre_delete_site_option_{$option}", $option, $_site_id);

        $row = $this->app->db->table('sitemeta')->where('meta_key', $option)->where('site_id', (int) $_site_id)->first();
        if (is_null($row) || !$row['meta_id']) {
            return false;
        }
        $cache_key = "{$_site_id}_{$option}";
        ttcms_cache_delete($cache_key, 'site-options');

        $result = $this->app->db->table('sitemeta');
        $result->begin();
        try {
            $result->where('meta_key', $option)->where('site_id', (int) $_site_id)->delete();
            $result->commit();
        } catch (Exception $ex) {
            $result->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: Error: %s', $ex->getCode(), $ex->getMessage()));
        }


        if ($result) {

            /**
             * Fires after a specific site option has been deleted.
             *
             * The dynamic portion of the hook name, `$option`, refers to the option name.
             *
             * @since 1.0.0
             * @param string $option    Name of the site option.
             * @param int    $_site_id  ID of the site.
             */
            $this->do_action("delete_site_option_{$option}", $option, $_site_id);

            /**
             * Fires after a site option has been deleted.
             *
             * @since 1.0.0
             * @param string $option    Name of the site option.
             * @param int    $_site_id   ID of the site.
             */
            $this->do_action('delete_site_option', $option, $_site_id);

            return true;
        }

        return false;
    }

    // Serialize data if needed. Stolen from WordPress
    public function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data))
            return serialize($data);

        if ($this->is_serialized($data))
            return serialize($data);

        return $data;
    }

    // Check value to find if it was serialized. Stolen from WordPress
    public function is_serialized($data)
    {
        // if it isn't a string, it isn't serialized
        if (!is_string($data))
            return false;
        $data = trim($data);
        if ('N;' == $data)
            return true;
        if (!preg_match('/^([adObis]):/', $data, $badions))
            return false;
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                    return true;
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                    return true;
                break;
        }
        return false;
    }

    // Unserialize value only if it was serialized. Stolen from WP
    public function maybe_unserialize($original)
    {
        if ($this->is_serialized($original)) // don't attempt to unserialize data that wasn't serialized going in
            return unserialize($original);
        return $original;
    }
}
