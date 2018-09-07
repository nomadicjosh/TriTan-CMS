<?php
namespace TriTan\Interfaces\Hooks;

interface ApplyFilterRefArrayHookInterface
{
    /**
     * Execute functions/methods hooked on a specific filter hook, specifying arguments in an array.
     *
     * @since 0.9.9
     * @param    string $tag  <p>The name of the filter hook.</p>
     * @param    array  $args <p>The arguments supplied to the functions/methods hooked to <tt>$tag</tt></p>
     * @return   mixed        <p>The filtered value after all hooked functions/methods are applied to it.</p>
     */
    public function applyFilterRefArray($tag, $args);
}
