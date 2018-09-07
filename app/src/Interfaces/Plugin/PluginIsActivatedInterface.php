<?php
namespace TriTan\Interfaces\Plugin;

interface PluginIsActivatedInterface
{
    /**
     * Checks if a particular plugin has been activated.
     *
     * @since 0.9.9
     * @return mixed
     */
    public function isActivated($plugin) : bool;
}
