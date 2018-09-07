<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\AddFilterHookInterface;

class AddFilterHook implements AddFilterHookInterface
{
    use \TriTan\Traits\HookBuildIdTrait;
    use \TriTan\Traits\HookMergedFiltersTrait;

    /**
     * Registers a filtering function
     *
     * @since   0.9.9
     * @param   string      $hook            The name of the element to be filtered or action to be triggered
     * @param   callback    $function_to_add The name of the function/method that is to be called.
     * @param   integer     $priority        Optional. Used to specify the order in which the functions associated with a particular action are executed (default=10, lower=earlier execution, and functions with the same priority are executed in the order in which they were added to the filter)
     * @param   int         $accepted_args   Optional. The number of arguments the function accept (default is the number provided).
     */
    public function add($hook, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $accepted_args = self::ARGUMENT_NEUTRAL)
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
}
