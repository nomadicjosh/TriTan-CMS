<?php

namespace TriTan;

use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;

/**
 * Hooks API: Hook Class
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

class Hooks
{

    /**
     *
     * @access public
     * @var object
     */
    public $app;

    /**
     *
     * @access public
     * @var string
     */
    public $plugins_dir;

    /**
     *
     * @access public
     * @var array
     */
    public $filters = [];

    /**
     *
     * @access public
     * @var string
     */
    public $actions = [];

    /**
     *
     * @access public
     * @var array
     */
    public $mergedfilters = [];

    /**
     *
     * @access public
     * @var string
     */
    public $current_filter = [];

    /**
     * Container for storing parsecode tags and their hook to call for the parsecode
     *
     * @access public
     * @var array
     */
    public static $parsecode_tags = [];

    /**
     * Default priority
     *
     * @access public
     * @const int
     */
    const PRIORITY_NEUTRAL = 10;

    /**
     * Default arguments accepted
     *
     * @access public
     * @const int
     */
    const ARGUMENT_NEUTRAL = 1;

    /**
     * all plugins header information in an array.
     *
     * @access public
     * @var array
     */
    public $plugins_header = [];

    /**
     *
     * @access public
     * @var string
     */
    public $error = [];

    /**
     * __construct class constructor
     *
     * @access public
     * @since 0.9
     */
    public function __construct(\Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();
    }

    /**
     * Returns the plugin header information
     *
     * @access public
     * @since 0.9
     * @param
     *            string (optional) $plugins_dir Loads plugins from specified folder
     * @return mixed
     */
    public function getplugins_header($plugins_dir = '')
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

                        foreach ([
                    'name',
                    'uri',
                    'version',
                    'description',
                    'author_name',
                    'author_uri',
                    'plugin_slug'
                        ] as $field) {
                            if (!empty(${$field})) {
                                ${$field} = _trim(${$field}[1]);
                            } else {
                                ${$field} = '';
                            }
                        }
                        $plugin_data = [
                            'filename' => $file,
                            'Name' => $name,
                            'Title' => $name,
                            'PluginURI' => $uri,
                            'Description' => $description,
                            'Author' => $author_name,
                            'AuthorURI' => $author_uri,
                            'Version' => $version
                        ];
                        $this->plugins_header[] = $plugin_data;
                    }
                } else
                if ((is_dir($plugins_dir . $file)) && ($file != '.') && ($file != '..')) {
                    $this->getplugins_header($plugins_dir . $file . '/');
                }
            }

            closedir($handle);
        }
        return $this->plugins_header;
    }

    /**
     * Activates a specific plugin that is called by $_GET['id'] variable.
     *
     * @access public
     * @since 0.9
     * @param string $plugin
     *            ID of the plugin to activate
     * @return mixed
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
     * @since 0.9
     * @param string $plugin
     *            ID of the plugin to deactivate.         
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
     * @since 0.9
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
     * @since 0.9
     * @return mixed
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
     * @since 0.9
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
    public function add_filter($hook, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $accepted_args = self::ARGUMENT_NEUTRAL)
    {

        // At this point, we cannot check if the function exists, as it may well be defined later (which is OK)
        $id = $this->filter_unique_id($hook, $function_to_add, $priority);

        $this->filters[$hook][$priority][$id] = [
            'function' => $function_to_add,
            'accepted_args' => $accepted_args
        ];
        unset($this->mergedfilters[$hook]);
        return true;
    }

    /**
     * add_action
     * Adds a hook
     *
     * @access public
     * @since 0.9
     * @param string $hook            
     * @param string $function_to_add            
     * @param integer $priority
     *            (optional)
     * @param integer $accepted_args
     *            (optional)         
     */
    public function add_action($hook, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $accepted_args = self::ARGUMENT_NEUTRAL)
    {
        return $this->add_filter($hook, $function_to_add, $priority, $accepted_args);
    }

    /**
     * remove_action Removes a function from a specified action hook.
     *
     * @access public
     * @since 0.9
     * @param string $hook
     *            The action hook to which the function to be removed is hooked.
     * @param callback $function_to_remove
     *            The name of the function which should be removed.
     * @param int $priority
     *            optional The priority of the function (default: 10).
     * @return boolean Whether the function is removed.
     */
    public function remove_action($hook, $function_to_remove, $priority = self::PRIORITY_NEUTRAL)
    {
        return $this->remove_filter($hook, $function_to_remove, $priority);
    }

    /**
     * remove_all_actions Remove all of the hooks from an action.
     *
     * @access public
     * @since 0.9
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
     * @since 0.9
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
            $function = [
                $function,
                ''
            ];
        } else {
            $function = (array) $function;
        }

        if (is_object($function[0])) {
            // Object Class Calling
            if (function_exists('spl_object_hash')) {
                return spl_object_hash($function[0]) . $function[1];
            } else {
                $obj_idx = get_class($function[0]) . $function[1];
                if (!isset($function[0]->filters_id)) {
                    if (false === $priority)
                        return false;
                    $obj_idx .= isset($this->filters[$hook][$priority]) ? count((array) $this->filters[$hook][$priority]) : $filter_id_count;
                    $function[0]->filters_id = $filter_id_count;
                    ++$filter_id_count;
                } else {
                    $obj_idx .= $function[0]->filters_id;
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
     * @since 0.9
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

        if (isset($this->filters['all'])) {
            $this->current_filter[] = $hook;
            $args = func_get_args();
            $this->_call_all_hook($args);
        }

        if (!isset($this->filters[$hook])) {
            if (isset($this->filters['all']))
                array_pop($this->current_filter);
            return $value;
        }

        if (!isset($this->filters['all'])) {
            $this->current_filter[] = $hook;
        }

        if (!isset($this->mergedfilters[$hook])) {
            ksort($this->filters[$hook]);
            $this->mergedfilters[$hook] = true;
        }

        // Loops through each filter
        reset($this->filters[$hook]);

        if (empty($args)) {
            $args = func_get_args();
        }

        do {
            foreach ((array) current($this->filters[$hook]) as $the_)
                if (!is_null($the_['function'])) {
                    $args[1] = $value;
                    $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
                }
        } while (next($this->filters[$hook]) !== false);

        array_pop($this->current_filter);

        return $value;
    }

    /**
     * Execute functions hooked on a specific filter hook, specifying arguments in an array.
     *
     * @since 0.9.8
     * @uses $this->_call_all_hook()
     * @param    string $tag  <p>The name of the filter hook.</p>
     * @param    array  $args <p>The arguments supplied to the functions hooked to <tt>$tag</tt></p>
     * @return   mixed        <p>The filtered value after all hooked functions are applied to it.</p>
     */
    public function apply_filter_ref_array($tag, $args)
    {
        // Do 'all' actions first
        if (isset($this->filters['all'])) {
            $this->current_filter[] = $tag;
            $all_args = func_get_args();
            $this->_call_all_hook($all_args);
        }
        if (!isset($this->filters[$tag])) {
            if (isset($this->filters['all'])) {
                array_pop($this->current_filter);
            }
            return $args[0];
        }
        if (!isset($this->filters['all'])) {
            $this->current_filter[] = $tag;
        }
        // Sort
        if (!isset($this->merged_filters[$tag])) {
            ksort($this->filters[$tag]);
            $this->merged_filters[$tag] = true;
        }
        reset($this->filters[$tag]);
        do {
            foreach ((array) current($this->filters[$tag]) as $the_) {
                if (null !== $the_['function']) {
                    if (null !== $the_['include_path']) {
                        /** @noinspection PhpIncludeInspection */
                        include_once $the_['include_path'];
                    }
                    $args[0] = call_user_func_array($the_['function'], $args);
                }
            }
        } while (next($this->filters[$tag]) !== false);
        array_pop($this->current_filter);
        return $args[0];
    }

    public function do_action($hook, $arg = '')
    {
        if (!isset($this->actions))
            $this->actions = [];

        if (!isset($this->actions[$hook]))
            $this->actions[$hook] = 1;
        else
            ++$this->actions[$hook];

        // Do 'all' actions first
        if (isset($this->filters['all'])) {
            $this->current_filter[] = $hook;
            $all_args = func_get_args();
            $this->_call_all_hook($all_args);
        }

        if (!isset($this->filters[$hook])) {
            if (isset($this->filters['all']))
                array_pop($this->current_filter);
            return;
        }

        if (!isset($this->filters['all']))
            $this->current_filter[] = $hook;

        $args = [];
        if (is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0])) // array(&$this)
            $args[] = & $arg[0];
        else
            $args[] = $arg;
        for ($a = 2; $a < func_num_args(); $a ++)
            $args[] = func_get_arg($a);

        // Sort
        if (!isset($this->mergedfilters[$hook])) {
            ksort($this->filters[$hook]);
            $this->mergedfilters[$hook] = true;
        }

        reset($this->filters[$hook]);

        do {
            foreach ((array) current($this->filters[$hook]) as $the_)
                if (!is_null($the_['function']))
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
        } while (next($this->filters[$hook]) !== false);

        array_pop($this->current_filter);
    }

    public function _call_all_hook($args)
    {
        reset($this->filters['all']);
        do {
            foreach ((array) current($this->filters['all']) as $the_)
                if (!is_null($the_['function']))
                    call_user_func_array($the_['function'], $args);
        } while (next($this->filters['all']) !== false);
    }

    public function clean_pre($matches)
    {
        if (is_array($matches))
            $text = $matches[1] . $matches[2] . "</pre>";
        else
            $text = $matches;

        $text = str_replace('<br />', '', $text);
        $text = str_replace('<p>', "\n", $text);
        $text = str_replace('</p>', '', $text);

        return $text;
    }

    /**
     * Add hook for parsecode tag.
     *
     * <p>
     * <br />
     * There can only be one hook for each parsecode. Which means that if another
     * plugin has a similar parsecode, it will override yours or yours will override
     * theirs depending on which order the plugins are included and/or ran.
     * <br />
     * <br />
     * </p>
     *
     * Simplest example of a parsecode tag using the API:
     *
     *        <code>
     *            // [footag foo="bar"]
     *            function footag_func($atts) {
     *                return "foo = {$atts[foo]}";
     *            }
     *            add_parsecode('footag', 'footag_func');
     *        </code>
     *
     * Example with nice attribute defaults:
     *
     *        <code>
     *            // [bartag foo="bar"]
     *            function bartag_func($atts) {
     *                $args = parsecode_atts([
     *                'foo' => 'no foo',
     *                'baz' => 'default baz',
     *            ], $atts);
     *
     *            return "foo = {$args['foo']}";
     *            }
     *            add_parsecode('bartag', 'bartag_func');
     *        </code>
     *
     * Example with enclosed content:
     *
     *        <code>
     *            // [baztag]content[/baztag]
     *            function baztag_func($atts, $content='') {
     *                return "content = $content";
     *            }
     *            add_parsecode('baztag', 'baztag_func');
     *        </code>
     *
     * @since 0.9.8
     * @uses _incorrectly_called()
     * @param string   $tag  <p>Parsecode tag to be searched in post content.</p>
     * @param callable $func <p>Hook to run when parsecode is found.</p>
     * @return bool
     */
    public function add_parsecode($tag, $func)
    {
        if ('' == _trim($tag)) {
            $message = _t('Invalid parsecode name: empty name given.');
            _incorrectly_called(__METHOD__, $message, '0.9');
            return;
        }

        if (0 !== preg_match('@[<>&/\[\]\x00-\x20]@', $tag)) {
            /* translators: %s: parsecode name */
            $message = sprintf(_t('Invalid parsecode name: %s. Do not use spaces or reserved characters: & / < > [ ]'), $tag);
            _incorrectly_called(__METHOD__, $message, '0.9');
            return;
        }

        if (is_callable($func)) {
            self::$parsecode_tags[$tag] = $func;

            return true;
        }
        return false;
    }

    /**
     * Removes hook for parsecode.
     *
     * @since 0.9.8
     * @uses _incorrectly_called()
     * @uses self::$parsecode_tags
     * @param string $tag parsecode tag to remove hook for.
     */
    public function remove_parsecode($tag)
    {
        if ('' == _trim($tag)) {
            $message = _t('Invalid parsecode name: empty name given.');
            _incorrectly_called(__METHOD__, $message, '0.9');
            return;
        }

        if (isset(self::$parsecode_tags[$tag])) {
            unset(self::$parsecode_tags[$tag]);
            return true;
        }
        return false;
    }

    /**
     * Clear all parsecodes.
     *
     * This function is simple, it clears all of the parsecode tags by replacing the
     * parsecodes global by a empty array. This is actually a very efficient method
     * for removing all parsecodes.
     *
     * @since 0.9.8
     * @uses self::$parsecode_tags
     */
    public function remove_all_parsecodes()
    {
        self::$parsecode_tags = [];
        return true;
    }

    /**
     * Whether a registered parsecode exists named $tag
     *
     * @param string $tag
     *
     * @return boolean
     */
    public function parsecode_exists($tag)
    {
        return array_key_exists($tag, self::$parsecode_tags);
    }

    /**
     * Whether the passed content contains the specified parsecode.
     *
     * @since 0.9.8
     * @uses $this->parsecode_exists()
     * @uses $this->get_parsecode_regex()
     * @param string $content
     * @param string $tag
     * @return bool
     */
    public function has_parsecode($content, $tag)
    {
        if (false === strpos($content, '[')) {
            return false;
        }
        if ($this->parsecode_exists($tag)) {
            preg_match_all('/' . $this->get_parsecode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
            if (empty($matches)) {
                return false;
            }
            foreach ($matches as $parsecode) {
                if ($tag === $parsecode[2]) {
                    return true;
                }
                if (!empty($parsecode[5]) && $this->has_parsecode($parsecode[5], $tag)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Search content for parsecodes and filter parsecodes through their hooks.
     *
     * If there are no parsecode tags defined, then the content will be returned
     * without any filtering. This might cause issues when plugins are disabled but
     * the parsecode will still show up in the post or content.
     *
     * @since 0.9.8
     * @uses self::$parsecode_tags
     * @uses $this->get_parsecode_regex() Gets the search pattern for searching parsecodes.
     * @param string $content Content to search for parsecodes
     * @return string Content with parsecodes filtered out.
     */
    public function do_parsecode($content)
    {
        if (empty(self::$parsecode_tags) || !is_array(self::$parsecode_tags)) {
            return $content;
        }

        $pattern = $this->get_parsecode_regex();
        return preg_replace_callback("/$pattern/s", '_do_parsecode_tag', $content);
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
     * @since 0.9.8
     * @uses self::$parsecode_tags
     * @return string The parsecode search regular expression
     */
    public function get_parsecode_regex()
    {
        $tagnames = array_keys(self::$parsecode_tags);
        $tagregexp = join('|', array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing _do_parsecode_tag() and strip_parsecode_tag()
        return
                '\\['                              // Opening bracket
                . '(\\[?)'                           // 1: Optional second opening bracket for escaping parsecodes: [[tag]]
                . "($tagregexp)"                     // 2: parsecode name
                . '\\b'                              // Word boundary
                . '('                                // 3: Unroll the loop: Inside the opening parsecode tag
                . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
                . '(?:'
                . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
                . '[^\\]\\/]*'               // Not a closing bracket or forward slash
                . ')*?'
                . ')'
                . '(?:'
                . '(\\/)'                        // 4: Self closing tag ...
                . '\\]'                          // ... and closing bracket
                . '|'
                . '\\]'                          // Closing bracket
                . '(?:'
                . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing parsecode tags
                . '[^\\[]*+'             // Not an opening bracket
                . '(?:'
                . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing parsecode tag
                . '[^\\[]*+'         // Not an opening bracket
                . ')*+'
                . ')'
                . '\\[\\/\\2\\]'             // Closing parsecode tag
                . ')?'
                . ')'
                . '(\\]?)';                          // 6: Optional second closing brocket for escaping parsecodes: [[tag]]
    }

    /**
     * Regular Expression callable for do_parsecode() for calling parsecode hook.
     * @see get_parsecode_regex for details of the match array contents.
     *
     * @since 0.9.8
     * @access private
     * @uses $this->parsecode_parse_atts()
     * @uses self::$parsecode_tags
     * @param array $m Regular expression match array
     * @return mixed False on failure.
     */
    public function _do_parsecode_tag($m)
    {
        // allow [[foo]] syntax for escaping a tag
        if ($m[1] == '[' && $m[6] == ']') {
            return substr($m[0], 1, -1);
        }

        $tag = $m[2];
        $attr = $this->parsecode_parse_atts($m[3]);

        if (isset($m[5])) {
            // enclosing tag - extra parameter
            return $m[1] . call_user_func(self::$parsecode_tags[$tag], $attr, $m[5], $tag) . $m[6];
        } else {
            // self-closing tag
            return $m[1] . call_user_func(self::$parsecode_tags[$tag], $attr, NULL, $tag) . $m[6];
        }
    }

    /**
     * Retrieve all attributes from the parsecodes tag.
     *
     * The attributes list has the attribute name as the key and the value of the
     * attribute as the value in the key/value pair. This allows for easier
     * retrieval of the attributes, since all attributes have to be known.
     *
     * @since 0.9.8
     * @param string $text
     * @return array List of attributes and their value.
     */
    public function parsecode_parse_atts($text)
    {
        $atts = [];
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) and strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                }
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
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
     * @since 0.9.8
     * @param array $pairs Entire list of supported attributes and their defaults.
     * @param array $atts User defined attributes in parsecode tag.
     * @return array Combined and filtered attribute list.
     */
    public function parsecode_atts($pairs, $atts)
    {
        $atts = (array) $atts;
        $out = [];
        foreach ($pairs as $name => $default) {
            if (array_key_exists($name, $atts)) {
                $out[$name] = $atts[$name];
            } else {
                $out[$name] = $default;
            }
        }
        return $out;
    }

    /**
     * Remove all parsecode tags from the given content.
     *
     * @since 0.9.8
     * @uses self::$parsecode_tags
     * @uses $this->get_parsecode_regex()
     * @param string $content Content to remove parsecode tags.
     * @return string Content without parsecode tags.
     */
    public function strip_parsecodes($content)
    {
        if (empty(self::$parsecode_tags) || !is_array(self::$parsecode_tags)) {
            return $content;
        }

        $pattern = $this->get_parsecode_regex();

        return preg_replace_callback(
                "/$pattern/s", [
            $this,
            '_strip_parsecode_tag',
                ], $content
        );
    }

    function _strip_parsecode_tag($m)
    {
        // allow [[foo]] syntax for escaping a tag
        if ($m[1] == '[' && $m[6] == ']') {
            return substr($m[0], 1, -1);
        }

        return $m[1] . $m[6];
    }

    /**
     * @since 0.9.8
     * @uses _trim()
     * @param unknown $pee
     * @param number $br
     * @return string|mixed
     */
    public function parsecode_autop($pee, $br = 1)
    {

        if (_trim($pee) === '') {
            return '';
        }
        $pee = $pee . "\n"; // just to make things a little easier, pad the end
        $pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
        // Space things out a little
        $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|option|form|map|area|blockquote|address|math|style|input|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
        $pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
        $pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
        $pee = str_replace(["\r\n", "\r"], "\n", $pee); // cross-platform newlines
        if (strpos($pee, '<object') !== false) {
            $pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee); // no pee inside object/embed
            $pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
        }
        $pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
        // make paragraphs, including one at the end
        $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
        $pee = '';
        foreach ($pees as $tinkle) {
            $pee .= '<p>' . _trim($tinkle, "\n") . "</p>\n";
        }
        $pee = preg_replace('|<p>\s*</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
        $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
        $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
        $pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
        $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
        if ($br) {
            $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee);
            $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
            $pee = str_replace('<TTPreserveNewline />', "\n", $pee);
        }
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
        $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
        if (strpos($pee, '<pre') !== false) {
            $pee = preg_replace_callback('!(<pre[^>]*>)(.*?)</pre>!is', 'clean_pre', $pee);
        }
        $pee = preg_replace("|\n</p>$|", '</p>', $pee);

        return $pee;
    }

    public function _autop_newline_preservation_helper($matches)
    {
        return str_replace("\n", "<TTPreserveNewline />", $matches[0]);
    }

    public function parsecode_unautop($pee)
    {
        if (empty(self::$parsecode_tags) || !is_array(self::$parsecode_tags)) {
            return $pee;
        }

        $tagregexp = join('|', array_map('preg_quote', array_keys(self::$parsecode_tags)));

        $pattern = '/'
                . '<p>'                              // Opening paragraph
                . '\\s*+'                            // Optional leading whitespace
                . '('                                // 1: The parsecode
                . '\\['                          // Opening bracket
                . "($tagregexp)"                 // 2: parsecode name
                . '\\b'                          // Word boundary
                // Unroll the loop: Inside the opening parsecode tag
                . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
                . '(?:'
                . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
                . '[^\\]\\/]*'               // Not a closing bracket or forward slash
                . ')*?'
                . '(?:'
                . '\\/\\]'                   // Self closing tag and closing bracket
                . '|'
                . '\\]'                      // Closing bracket
                . '(?:'                      // Unroll the loop: Optionally, anything between the opening and closing parsecode tags
                . '[^\\[]*+'             // Not an opening bracket
                . '(?:'
                . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing parsecode tag
                . '[^\\[]*+'         // Not an opening bracket
                . ')*+'
                . '\\[\\/\\2\\]'         // Closing parsecode tag
                . ')?'
                . ')'
                . ')'
                . '\\s*+'                            // optional trailing whitespace
                . '<\\/p>'                           // closing paragraph
                . '/s';

        return preg_replace($pattern, '$1', $pee);
    }

    /**
     * Execute functions hooked on a specific action hook, specifying arguments in an array.
     *
     * @since 0.9
     * @param    string $hook <p>The name of the action to be executed.</p>
     * @param    array  $args <p>The arguments supplied to the functions hooked to <tt>$hook</tt></p>
     * @return   bool         <p>Will return false if $tag does not exist in $filter array.</p>
     */
    public function do_action_ref_array($hook, $args)
    {
        if (!isset($this->actions)) {
            $this->actions = [];
        }

        if (!isset($this->actions[$hook])) {
            $this->actions[$hook] = 1;
        } else {
            ++$this->actions[$hook];
        }

        // Do 'all' actions first
        if (isset($this->filters['all'])) {
            $this->current_filter[] = $hook;
            $all_args = func_get_args();
            $this->_call_all_hook($all_args);
        }

        if (!isset($this->filters[$hook])) {
            if (isset($this->filters['all'])) {
                array_pop($this->current_filter);
            }
            return;
        }

        if (!isset($this->filters['all'])) {
            $this->current_filter[] = $hook;
        }

        // Sort
        if (!isset($this->mergedfilters[$hook])) {
            ksort($this->filters[$hook]);
            $this->mergedfilters[$hook] = true;
        }

        reset($this->filters[$hook]);

        do {
            foreach ((array) current($this->filters[$hook]) as $the_)
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
        } while (next($this->filters[$hook]) !== false);

        array_pop($this->current_filter);
    }

    /**
     * Retrieve the number of times an action has fired.
     *
     * @since 0.9.8
     * @param string $tag <p>The name of the action hook.</p>
     * @return integer <p>The number of times action hook <tt>$tag</tt> is fired.</p>
     */
    public function did_action($tag)
    {
        if (!is_array($this->actions) || !isset($this->actions[$tag])) {
            return 0;
        }
        return $this->actions[$tag];
    }

    /**
     * Retrieve the name of the current filter or action.
     *
     * @since 0.9.8
     * @return string <p>Hook name of the current filter or action.</p>
     */
    public function current_filter()
    {
        return end($this->current_filter);
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
    public function remove_filter($hook, $function_to_remove, $priority = self::PRIORITY_NEUTRAL)
    {
        $function_to_remove = $this->filter_unique_id($hook, $function_to_remove, $priority);

        $remove = isset($this->filters[$hook][$priority][$function_to_remove]);

        if (true === $remove) {
            unset($this->filters[$hook][$priority][$function_to_remove]);
            if (empty($this->filters[$hook][$priority])) {
                unset($this->filters[$hook][$priority]);
            }
            unset($this->mergedfilters[$hook]);
        }
        return $remove;
    }

    /**
     * remove_all_filters Remove all of the hooks from a filter.
     *
     * @access public
     * @since 0.9
     * @param string $hook
     *            The filter to remove hooks from.
     * @param int $priority
     *            The priority number to remove.
     * @return bool True when finished.
     */
    public function remove_all_filters($hook, $priority = false)
    {
        if (isset($this->filters[$hook])) {
            if (false !== $priority && isset($this->filters[$hook][$priority])) {
                unset($this->filters[$hook][$priority]);
            } else {
                unset($this->filters[$hook]);
            }
        }

        if (isset($this->mergedfilters[$hook])) {
            unset($this->mergedfilters[$hook]);
        }

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
        $has = !empty($this->filters[$hook]);
        if (false === $function_to_check || false == $has) {
            return $has;
        }
        if (!$idx = $this->filter_unique_id($hook, $function_to_check, false)) {
            return false;
        }

        foreach ((array) array_keys($this->filters[$hook]) as $priority) {
            if (isset($this->filters[$hook][$priority][$idx])) {
                return $priority;
            }
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
        if (!property_exists($this->app->hook, 'plugin_pages') || !$this->app->hook->plugin_pages) {
            return;
        }

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
        if (!property_exists($this->app->hook, 'plugin_pages') || !$this->app->hook->plugin_pages) {
            $this->app->hook->plugin_pages = [];
        }

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
        $option_key = _trim($option_key);
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
         * @since 0.9
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
                $result = $meta->where('option_key', '=', $option_key)
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
         * @since 0.9 As 'get_option_' . $setting
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
     * @since 0.9
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
         * @since 0.9
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
         * @since 0.9
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

        if (!is_option_exist($option_key)) {
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
                'option_value' => if_null($option_value)
            ]);

            if (@count($key) > 0) {
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
     * @since 0.9
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
         * @since 0.9
         * @param mixed  $value     New value of the site option.
         * @param mixed  $old_value Old value of the site option.
         * @param string $option    Option name.
         * @param int    $_site_id  ID of the site.
         */
        $value = applyfilters("pre_update_site_option_{$option}", $value, $old_value, $option, $_site_id);

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
             * @since 0.9
             * @param string $option    Name of the site option.
             * @param mixed  $value     Current value of the site option.
             * @param mixed  $old_value Old value of the site option.
             * @param int    $_site_id  ID of the site.
             */
            $this->do_action("update_site_option_{$option}", $option, $value, $old_value, $_site_id);

            /**
             * Fires after the value of a site option has been successfully updated.
             *
             * @since 0.9
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
        if (is_option_exist($name)) {
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
     * @since 0.9
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
             * @since 0.9
             * @param string $option    Name of the site option.
             * @param mixed  $value     Value of the site option.
             * @param int    $_site_id  ID of the site.
             */
            $this->do_action("add_site_option_{$option}", $option, $value, $_site_id);

            /**
             * Fires after a site option has been successfully added.
             *
             * @since 0.9
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
     * @since 0.9
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
         * @since 0.9
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
             * @since 0.9
             * @param string $option    Name of the site option.
             * @param int    $_site_id  ID of the site.
             */
            $this->do_action("delete_site_option_{$option}", $option, $_site_id);

            /**
             * Fires after a site option has been deleted.
             *
             * @since 0.9
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
        $data = _trim($data);
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
