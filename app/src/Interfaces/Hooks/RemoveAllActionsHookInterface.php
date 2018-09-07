<?php
namespace TriTan\Interfaces\Hooks;

interface RemoveAllActionsHookInterface
{
    /**
     * Remove all of the hooks from an action.
     *
     * @since   0.9.9
     * @param   string  $hook       The action to remove hooks from.
     * @param   int     $priority   The priority number to remove them from.
     * @return bool True when finished.
     */
    public function removeAllActions($hook, $priority = 10);
}
