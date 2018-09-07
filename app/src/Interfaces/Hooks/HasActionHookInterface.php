<?php
namespace TriTan\Interfaces\Hooks;

interface HasActionHookInterface
{
    /**
     * Check if any action has been registered for a hook.
     *
     * @since 0.9.9
     *
     * @param string        $hook              The name of the action hook.
     * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
     * @return bool|int If $function_to_check is omitted, returns boolean for whether the hook has
     *                  anything registered. When checking a specific function/method, the priority of that
     *                  hook is returned, or false if the function/method is not attached. When using the
     *                  $function_to_check argument, this function/method may return a non-boolean value
     *                  that evaluates to false (e.g.) 0, so use the === operator for testing the
     *                  return value.
     */
    public function hasAction($hook, $function_to_check = false);
}
