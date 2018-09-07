<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\HookBuildIdInterface;

class HookBuildId implements HookBuildIdInterface
{
    use \TriTan\Traits\HookFiltersTrait;
    /**
     * Build Unique ID for storage and retrieval.
     *
     * Simply using a function name is not enough, as several functions can have the same name when they are enclosed in classes.
     *
     * @since 0.9.9
     * @param string $hook
     * @param string|array  $function Used for creating unique id
     * @param int|bool      $priority Used in counting how many hooks were applied. If === false and $function is an object reference, we return the unique id only if it already has one, false otherwise.
     * @return string unique ID for usage as array key
     */
    public function buildUniqueId($hook, $function, $priority)
    {
        static $filter_id_count = 0;

        // If function then just skip all of the tests and not overwrite the following.
        if (is_string($function)) {
            return $function;
        }
        if (is_object($function)) {
            // Closures are currently implemented as objects
            $function = [
                $function,
                ''
            ];
        } else {
            $function = (array) $function;
        }

        if (is_object($function[0])) {
            // Object Class Calling
            if (function_exists('spl_object_hash')) {
                return spl_object_hash($function[0]) . $function[1];
            } else {
                $obj_idx = get_class($function[0]) . $function[1];
                if (!isset($function[0]->filters_id)) {
                    if (false === $priority) {
                        return false;
                    }
                    $obj_idx .= isset($this->filters[$hook][$priority]) ? count((array) $this->filters[$hook][$priority]) : $filter_id_count;
                    $function[0]->filters_id = $filter_id_count;
                    ++$filter_id_count;
                } else {
                    $obj_idx .= $function[0]->filters_id;
                }

                return $obj_idx;
            }
        } elseif (is_string($function[0])) {
            // Static Calling
            return $function[0] . '::' . $function[1];
        }
    }
}
