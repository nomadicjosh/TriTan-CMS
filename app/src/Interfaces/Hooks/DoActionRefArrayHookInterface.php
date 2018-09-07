<?php
namespace TriTan\Interfaces\Hooks;

interface DoActionRefArrayHookInterface
{
    /**
     * Execute functions hooked on a specific action hook, specifying arguments in an array.
     *
     * @since 0.9
     * @param    string $hook <p>The name of the action to be executed.</p>
     * @param    array  $args <p>The arguments supplied to the functions hooked to <tt>$hook</tt></p>
     * @return   bool         <p>Will return false if $tag does not exist in $filter array.</p>
     */
    public function doActionRefArray($hook, $args);
}
