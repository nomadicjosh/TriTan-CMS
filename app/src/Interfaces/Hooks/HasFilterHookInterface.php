<?php
namespace TriTan\Interfaces\Hooks;

interface HasFilterHookInterface
{
    /**
     * Check if any filter has been registered for a hook.
     *
     * @param   string      $hook               The name of the filter hook.
     * @param   callback    $function_to_check  Optional. If specified, return
     *                                          the priority of that function/method
     *                                          on this hook or false if not attached.
     * @return int|boolean Optionally returns the priority on that hook for the specified function/method.
     */
    public function hasFilter($hook, $function_to_check = false);
}
