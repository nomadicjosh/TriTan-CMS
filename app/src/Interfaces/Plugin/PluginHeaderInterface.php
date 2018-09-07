<?php
namespace TriTan\Interfaces\Plugin;

interface PluginHeaderInterface
{
    /**
     * Returns the plugin header information
     *
     * @since 0.9.9
     * @param string (optional) $plugins_dir Loads plugins from specified folder.
     * @return mixed
     */
    public function read($plugins_dir = '');
}
