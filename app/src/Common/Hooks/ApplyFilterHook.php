<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\ApplyFilterHookInterface;
use TriTan\Interfaces\CallAllHookInterface;
use TriTan\Interfaces\CurrentFilterHookInterface;

/**
 * Hooks API: Hook Class
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

class ApplyFilterHook implements ApplyFilterHookInterface
{
    use \TriTan\Traits\HookBuildIdTrait;
    use \TriTan\Traits\HookMergedFiltersTrait;
    use \TriTan\Traits\HookCurrentFilterTrait;
    
    /**
     *
     * @access public
     * @var object
     */
    public $all;
    
    /**
     *
     * @access public
     * @var object
     */
    public $action;
    
    public function __construct(CallAllHookInterface $all, CurrentFilterHookInterface $action)
    {
        $this->all = $all;
        $this->action = $action;
    }

    /**
     * Performs a filtering operation on a PM element or event.
     *
     * Typical use:
     *
     * 1) Modify a variable if a function is attached to hook 'hook'
     * $var = "default value";
     * $var = $this->apply( 'hook', $var );
     *
     * 2) Trigger functions is attached to event
     * $this->apply( 'event' );
     * (see DoActionHook::do() )
     *
     * Returns an element which may have been filtered by a filter.
     *
     * @since 0.9.9
     * @param string $hook The name of the the element or action
     * @param mixed $value The value of the element before filtering
     * @return mixed
     */
    public function apply($hook, $value)
    {
        $args = [];

        if (isset($this->filters['all'])) {
            $this->current_filter[] = $hook;
            $args = func_get_args();
            $this->all->call($args);
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
     * @since 0.9.9
     * @param    string $tag  <p>The name of the filter hook.</p>
     * @param    array  $args <p>The arguments supplied to the functions/methods hooked to <tt>$tag</tt></p>
     * @return   mixed        <p>The filtered value after all hooked functions/methods are applied to it.</p>
     */
    public function applyRefArray($tag, $args)
    {
        // Do 'all' actions first
        if (isset($this->filters['all'])) {
            $this->current_filter[] = $tag;
            $all_args = func_get_args();
            $this->all->call($all_args);
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
     * Retrieve the name of the current action.
     *
     * @since 0.9.9
     * @return string <p>Hook name of the current action.</p>
     */
    public function current()
    {
        return $this->action->current();
    }
}
