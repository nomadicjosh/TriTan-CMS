<?php
namespace TriTan\Interfaces\Hooks;

interface DidActionHookInterface
{
    /**
     * Retrieve the number of times an action has fired.
     *
     * @since 0.9.9
     * @param string $tag <p>The name of the action hook.</p>
     * @return integer <p>The number of times action hook <tt>$tag</tt> is fired.</p>
     */
    public function didAction($tag);
}
