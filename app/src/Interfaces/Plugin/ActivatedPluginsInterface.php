<?php
namespace TriTan\Interfaces\Plugins;

interface ActivatedPluginsInterface
{
    /**
     * Returns a list of all plugins that have been activated.
     *
     * @since 0.9.9
     * @return mixed
     */
    public function get();
}
