<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\CurrentFilterHookInterface;

class CurrentFilterHook implements CurrentFilterHookInterface
{
    use \TriTan\Traits\HookCurrentFilterTrait;
    /**
     * Retrieve the name of the current filter or action.
     *
     * @since 0.9.9
     * @return string <p>Hook name of the current filter or action.</p>
     */
    public function current()
    {
        return end($this->current_filter);
    }
}
