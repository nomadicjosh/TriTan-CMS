<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\HasFilterHookInterface;

class HasFilterHook implements HasFilterHookInterface
{
    use \TriTan\Traits\HookBuildIdTrait;

    /**
     *
     * @access public
     * @var array
     */
    public $filters = [];

    /**
     * Check if any filter has been registered for a hook.
     *
     * @param   string      $hook               The name of the filter hook.
     * @param   callback    $function_to_check  Optional. If specified, return the priority of that function on this hook or false if not attached.
     * @return int|boolean Optionally returns the priority on that hook for the specified function.
     */
    public function has($hook, $function_to_check = false)
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
}
