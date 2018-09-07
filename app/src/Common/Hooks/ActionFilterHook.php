<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\Hooks\ActionFilterHookInterface;
use TriTan\Interfaces\Hooks\RemoveAllActionsHookInterface;
use TriTan\Interfaces\Hooks\RemoveAllFiltersHookInterface;
use TriTan\Interfaces\Hooks\ApplyFilterRefArrayHookInterface;
use TriTan\Interfaces\Hooks\DoActionRefArrayHookInterface;
use TriTan\Interfaces\Hooks\DidActionHookInterface;
use TriTan\Interfaces\Hooks\HasFilterHookInterface;
use TriTan\Interfaces\Hooks\HasActionHookInterface;

class ActionFilterHook implements ActionFilterHookInterface, RemoveAllActionsHookInterface, RemoveAllFiltersHookInterface, ApplyFilterRefArrayHookInterface, DoActionRefArrayHookInterface, DidActionHookInterface, HasFilterHookInterface, HasActionHookInterface
{
    /**
     *
     * @access public
     * @var array
     */
    public $filters = [];

    /**
     *
     * @access public
     * @var array
     */
    public $mergedfilters = [];

    /**
     *
     * @access public
     * @var array
     */
    public $current_filter = [];

    /**
     *
     * @access public
     * @var array
     */
    public $actions = [];

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
     * @var self The stored singleton instance
     */
    public static $instance;
    
    /**
     * Reset the Container instance.
     */
    public static function resetInstance()
    {
        if (self::$instance) {
            self::$instance = null;
        }
    }

    /**
     * Creates the original or retrieves the stored singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = (new \ReflectionClass(get_called_class()))
                ->newInstanceWithoutConstructor();
        }

        return static::$instance;
    }

    /**
     * Add a new action to the collection to be registered with TriTan CMS.
     *
     * @access public
     * @since    0.9.9
     * @param    string               $hook             The name of the TriTan CMS action that is being registered.
     * @param    callback             $function_to_add  The name of the function/method that is to be called.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function addAction($hook, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $accepted_args = self::ARGUMENT_NEUTRAL)
    {
        return $this->addFilter($hook, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Check if any action has been registered for a hook.
     *
     * @access public
     * @since 0.9.9
     * @param string        $hook              The name of the action hook.
     * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
     * @return bool|int If $function_to_check is omitted, returns boolean for whether the hook has
     *                  anything registered. When checking a specific function/method, the priority of that
     *                  hook is returned, or false if the function/method is not attached. When using the
     *                  $function_to_check argument, this function/method may return a non-boolean value
     *                  that evaluates to false (e.g.) 0, so use the === operator for testing the
     *                  return value.
     */
    public function hasAction($hook, $function_to_check = false)
    {
        return $this->hasFilter($hook, $function_to_check);
    }

    /**
     * Removes a function/method from a specified action hook.
     *
     * @access public
     * @since 0.9.9
     * @param string    $hook               The action hook to which the function/method to be removed is hooked.
     * @param callback  $function_to_remove The name of the function/method which should be removed.
     * @param int       $priority optional  The priority of the function/method (default: 10).
     * @return boolean Whether the function/method is removed.
     */
    public function removeAction($hook, $function_to_remove, $priority = self::PRIORITY_NEUTRAL)
    {
        return $this->removeFilter($hook, $function_to_remove, $priority);
    }

    /**
     * Remove all of the hooks from an action.
     *
     * @access public
     * @since 0.9
     * @param string    $hook       The action to remove hooks from.
     * @param int       $priority   The priority number to remove them from.
     * @return bool True when finished.
     */
    public function removeAllActions($hook, $priority = false)
    {
        return $this->removeAllFilters($hook, $priority);
    }

    /**
     * Registers a filtering function
     *
     * @access public
     * @since   0.9.9
     * @param   string      $hook            The name of the element to be filtered or action to be triggered
     * @param   callback    $function_to_add The name of the function/method that is to be called.
     * @param   integer     $priority        Optional. Used to specify the order in which the functions
     *                                       associated with a particular action are executed (default=10,
     *                                       lower=earlier execution, and functions with the same priority are
     *                                       executed in the order in which they were added to the filter)
     * @param   int         $accepted_args   Optional. The number of arguments the function accept (default is the number provided).
     */
    public function addFilter($hook, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $accepted_args = self::ARGUMENT_NEUTRAL)
    {
        // At this point, we cannot check if the function exists, as it may well be defined later (which is OK)
        $id = $this->buildUniqueId($hook, $function_to_add, $priority);

        $this->filters[$hook][$priority][$id] = [
            'function' => $function_to_add,
            'accepted_args' => $accepted_args
        ];
        unset($this->mergedfilters[$hook]);
        return true;
    }

    /**
     * Check if any filter has been registered for a hook.
     *
     * @access public
     * @since 0.9.9
     * @param string    $hook               The name of the filter hook.
     * @param callback  $function_to_check  Optional. If specified, return the
     *                                      priority of that function on this hook
     *                                      or false if not attached.
     * @return int|boolean Optionally returns the priority on that hook for the specified function.
     */
    public function hasFilter($hook, $function_to_check = false)
    {
        $has = !empty($this->filters[$hook]);
        if (false === $function_to_check || false == $has) {
            return $has;
        }
        if (!$idx = $this->buildUniqueId($hook, $function_to_check, false)) {
            return false;
        }

        foreach ((array) array_keys($this->filters[$hook]) as $priority) {
            if (isset($this->filters[$hook][$priority][$idx])) {
                return $priority;
            }
        }
        return false;
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
     * @access public
     * @since 0.9.9
     * @param string    $hook               The filter hook to which the function to be removed is hooked.
     * @param callback  $function_to_remove The name of the function which should be removed.
     * @param int       $priority optional. The priority of the function (default: 10).
     * @return boolean Whether the function was registered as a filter before it was removed.
     */
    public function removeFilter($hook, $function_to_remove, $priority = self::PRIORITY_NEUTRAL)
    {
        $function_to_remove = $this->buildUniqueId($hook, $function_to_remove, $priority);

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
     * Remove all of the hooks from a filter.
     *
     * @access public
     * @since 0.9.9
     * @param string    $hook       The filter to remove hooks from.
     * @param int       $priority   The priority number to remove.
     * @return bool True when finished.
     */
    public function removeAllFilters($hook, $priority = false)
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
     * Performs a filtering operation on a PM element or event.
     *
     * Typical use:
     *
     * 1) Modify a variable if a function is attached to hook 'hook'
     * $var = "default value";
     * $var = $this->applyFilter( 'hook', $var );
     *
     * 2) Trigger functions is attached to event
     * $this->applyFilter( 'event' );
     * (see $this->doAction() )
     *
     * Returns an element which may have been filtered by a filter.
     *
     * @access public
     * @since 0.9.9
     * @param string $hook The name of the the element or action
     * @param mixed $value The value of the element before filtering
     * @return mixed
     */
    public function applyFilter($hook, $value)
    {
        $args = [];

        if (isset($this->filters['all'])) {
            $this->current_filter[] = $hook;
            $args = func_get_args();
            $this->callAllHook($args);
        }

        if (!isset($this->filters[$hook])) {
            if (isset($this->filters['all'])) {
                array_pop($this->current_filter);
            }
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
            foreach ((array) current($this->filters[$hook]) as $the_) {
                if (!is_null($the_['function'])) {
                    $args[1] = $value;
                    $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
                }
            }
        } while (next($this->filters[$hook]) !== false);

        array_pop($this->current_filter);

        return $value;
    }

    /**
     * Execute functions/methods hooked on a specific filter hook, specifying arguments in an array.
     *
     * @access public
     * @since 0.9.9
     * @param    string $tag  <p>The name of the filter hook.</p>
     * @param    array  $args <p>The arguments supplied to the functions/methods hooked to <tt>$tag</tt></p>
     * @return   mixed        <p>The filtered value after all hooked functions/methods are applied to it.</p>
     */
    public function applyFilterRefArray($tag, $args)
    {
        // Do 'all' actions first
        if (isset($this->filters['all'])) {
            $this->current_filter[] = $tag;
            $all_args = func_get_args();
            $this->callAllHook($all_args);
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
        if (!isset($this->mergedfilters[$tag])) {
            ksort($this->filters[$tag]);
            $this->mergedfilters[$tag] = true;
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

    /**
    * Execute functions/methods hooked on a specific action hook.
    *
    * This method invokes all functions/methods attached to action hook `$hook`. It is
    * possible to create new action hooks by simply calling this method,
    * specifying the name of the new hook using the `$hook` parameter.
    *
    * You can pass extra arguments to the hooks, much like you can with $this->applyFilter().
    *
    * @access public
    * @since 0.9.9
    * @param string $hook     The name of the action to be executed.
    * @param mixed  $arg      Optional. Additional arguments which are passed on to the
    *                         functions/methods hooked to the action. Default empty.
    */
    public function doAction($hook, $arg = '')
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
            $this->callAllHook($all_args);
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

        $args = [];
        if (is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0])) { // array(&$this)
            $args[] = & $arg[0];
        } else {
            $args[] = $arg;
        }
        for ($a = 2; $a < func_num_args(); $a ++) {
            $args[] = func_get_arg($a);
        }

        // Sort
        if (!isset($this->mergedfilters[$hook])) {
            ksort($this->filters[$hook]);
            $this->mergedfilters[$hook] = true;
        }

        reset($this->filters[$hook]);

        do {
            foreach ((array) current($this->filters[$hook]) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }
        } while (next($this->filters[$hook]) !== false);

        array_pop($this->current_filter);
    }

    /**
     * Execute functions hooked on a specific action hook, specifying arguments in an array.
     *
     * @access public
     * @since 0.9
     * @param    string $hook <p>The name of the action to be executed.</p>
     * @param    array  $args <p>The arguments supplied to the functions hooked to <tt>$hook</tt></p>
     * @return   bool         <p>Will return false if $tag does not exist in $filter array.</p>
     */
    public function doActionRefArray($hook, $args)
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
            $this->callAllHook($all_args);
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
            foreach ((array) current($this->filters[$hook]) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }
        } while (next($this->filters[$hook]) !== false);

        array_pop($this->current_filter);
    }

    /**
     * Retrieve the number of times an action has fired.
     *
     * @access public
     * @since 0.9.9
     * @param string $tag <p>The name of the action hook.</p>
     * @return integer <p>The number of times action hook <tt>$tag</tt> is fired.</p>
     */
    public function didAction($tag)
    {
        if (!is_array($this->actions) || !isset($this->actions[$tag])) {
            return 0;
        }
        return $this->actions[$tag];
    }

    /**
     * Build Unique ID for storage and retrieval.
     *
     * Simply using a function name is not enough, as several functions can have the same name when they are enclosed in classes.
     *
     * @access private
     * @since 0.9.9
     * @param string $hook
     * @param string|array  $function Used for creating unique id
     * @param int|bool      $priority Used in counting how many hooks were applied. If === false and $function is an object reference, we return the unique id only if it already has one, false otherwise.
     * @return string unique ID for usage as array key
     */
    private function buildUniqueId($hook, $function, $priority)
    {
        static $filter_id_count = 0;

        // If function then just skip all of the tests and not overwrite the following.
        if (is_string($function)) {
            return $function;
        }
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
                    if (false === $priority) {
                        return false;
                    }
                    $obj_idx .= isset($this->filters[$hook][$priority]) ? count((array) $this->filters[$hook][$priority]) : $filter_id_count;
                    $function[0]->filters_id = $filter_id_count;
                    ++$filter_id_count;
                } else {
                    $obj_idx .= $function[0]->filters_id;
                }

                return $obj_idx;
            }
        } elseif (is_string($function[0])) {
            // Static Calling
            return $function[0] . '::' . $function[1];
        }
    }

    /**
     * Will process the functions hooked into it.
     *
     * The 'all' hook passes all of the arguments or parameters that were used for
     * the hook, which this function was called for.
     *
     * This function is used internally for $this->applyFilter(), $this->doAction(), and
     * $this->doActionRefArray() and is not meant to be used from outside those
     * methods. This method does not check for the existence of the all hook, so
     * it will fail unless the all hook exists prior to this method call.
     *
     * @access private
     * @since 0.9.9
     * @param array $args The collected parameters from the hook that was called.
     */
    private function callAllHook($args)
    {
        reset($this->filters['all']);
        do {
            foreach ((array) current($this->filters['all']) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], $args);
                }
            }
        } while (next($this->filters['all']) !== false);
    }

    /**
     * Retrieve the name of the current filter or action.
     *
     * @access public
     * @since 0.9.9
     * @return string <p>Hook name of the current filter or action.</p>
     */
    public function current()
    {
        return end($this->current_filter);
    }
}
