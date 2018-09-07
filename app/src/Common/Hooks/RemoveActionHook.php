<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\RemoveFilterHookInterface;
use TriTan\Interfaces\RemoveActionHookInterface;

class RemoveActionHook implements RemoveActionHookInterface
{
    /**
     *
     * access public
     * @var object
     */
    public $filter;
    
    public function __construct(RemoveFilterHookInterface $filter)
    {
        $this->filter = $filter;
    }
    /**
     * Default priority
     *
     * @access public
     * @const int
     */
    const PRIORITY_NEUTRAL = 10;

    /**
     * removeAction Removes a function/method from a specified action hook.
     *
     * @since   0.9.9
     * @param   string      $hook               The action hook to which the function to be removed is hooked.
     * @param   callback    $function_to_remove The name of the function/method which should be removed.
     * @param   int         $priority           Optional. The priority of the function/method (default: 10).
     * @return boolean Whether the function/method is removed.
     */
    public function remove($hook, $function_to_remove, $priority = self::PRIORITY_NEUTRAL)
    {
        return $this->filter->remove($hook, $function_to_remove, $priority);
    }

    /**
     * removeAllActions Remove all of the hooks from an action.
     *
     * @since   0.9.9
     * @param   string  $hook       The action to remove hooks from.
     * @param   int     $priority   The priority number to remove them from.
     * @return bool True when finished.
     */
    public function removeAll($hook, $priority = false)
    {
        return $this->filter->removeAll($hook, $priority);
    }
}
