<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\CallAllHookInterface;

class CallAllHook implements CallAllHookInterface
{
    use \TriTan\Traits\HookFiltersTrait;
/**
 * Will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for ApplyFilterHook::apply(), DoActionHook::do, and
 * DoActionHook::doRefArray() and is not meant to be used from outside those
 * methods. This method does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this method call.
 *
 * @since 0.9.9
 * @param array $args The collected parameters from the hook that was called.
 */
    public function call($args)
    {
        reset($this->filters['all']);
        do {
            foreach ((array) current($this->filters['all']) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], $args);
                }
            }
        } while (next($this->filters['all']) !== false);
    }
}
