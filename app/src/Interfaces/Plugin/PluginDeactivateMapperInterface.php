<?php
namespace TriTan\Interfaces\Plugin;

interface PluginDeactivateMapperInterface
{
    /**
     * Deactivates a specific plugin that is called by $_GET['id'] variable.
     *
     * @since 0.9.9
     * @param string $plugin ID of the plugin to deactivate.
     */
    public function deactivate($plugin);
}
