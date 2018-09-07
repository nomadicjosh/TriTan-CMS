<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\DidActionHookInterface;

class DidActionHook implements DidActionHookInterface
{
    /**
     *
     * @access public
     * @var array
     */
    public $actions = [];

    /**
     * Retrieve the number of times an action has fired.
     *
     * @since 0.9.9
     * @param string $tag <p>The name of the action hook.</p>
     * @return integer <p>The number of times action hook <tt>$tag</tt> is fired.</p>
     */
    public function did($tag)
    {
        if (!is_array($this->actions) || !isset($this->actions[$tag])) {
            return 0;
        }
        return $this->actions[$tag];
    }
}
