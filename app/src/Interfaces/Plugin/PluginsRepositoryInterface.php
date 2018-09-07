<?php
namespace TriTan\Interfaces\Plugins;

interface PluginsRepositoryInterface
{
    /**
     * Activates a specific plugin that is called by $_GET['id'] variable.
     *
     * @since 0.9.9
     * @param string $plugin ID of the plugin to activate
     * @return mixed
     */
    public function activate($plugin);

    /**
     * Deactivates a specific plugin that is called by $_GET['id'] variable.
     *
     * @since 0.9.9
     * @param string $plugin ID of the plugin to deactivate.
     */
    public function deactivate($plugin);

    /**
     * Checks if a particular plugin has been activated.
     *
     * @since 0.9.9
     * @return bool True if plugin is activated, false otherwise
     */
    public function isActivated($plugin) : bool;
}
