<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\ExecuteActionHookInterface;
use TriTan\Interfaces\CallAllHookInterface;

class ExecuteActionHook implements ExecuteActionHookInterface
{
    
    use \TriTan\Traits\HookFiltersTrait;
    use \TriTan\Traits\HookMergedFiltersTrait;
    use \TriTan\Traits\HookCurrentFilterTrait;
    use \TriTan\Traits\HookActionsTrait;
    
    /**
     *
     * access public
     * @var object
     */
    public $all;
    
    public function __construct(CallAllHookInterface $all)
    {
        $this->all = $all;
    }

    /**
 * Execute functions/methods hooked on a specific action hook.
 *
 * This method invokes all functions/methods attached to action hook `$hook`. It is
 * possible to create new action hooks by simply calling this method,
 * specifying the name of the new hook using the `$hook` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with ApplyFilterHook::apply().
 *
 * @since 0.9.9
 * @param string $hook     The name of the action to be executed.
 * @param mixed  $arg      Optional. Additional arguments which are passed on to the
 *                         functions/methods hooked to the action. Default empty.
 */
    public function execute($hook, $arg = '')
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
            $this->all->call($all_args);
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
}
