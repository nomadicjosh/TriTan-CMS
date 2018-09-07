<?php
namespace TriTan\Interfaces\Hooks;

interface RemoveAllFiltersHookInterface
{
    /**
     * Remove all of the hooks from a filter.
     *
     * @since   0.9.9
     * @param   string  $hook       The filter to remove hooks from.
     * @param   int     $priority   The priority number to remove.
     * @return bool True when finished.
     */
    public function removeAllFilters($hook, $priority = 10);
}
