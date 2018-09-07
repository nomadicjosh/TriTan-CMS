<?php
namespace TriTan\Interfaces\Plugin;

/**
 * Plugin Activate Interface
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
interface PluginActivateInterface
{

    /**
     * Activates a specific plugin that is called by $_GET['id'] variable.
     *
     * @since 0.9.9
     * @param string $plugin ID of the plugin to activate
     * @return mixed
     */
    public function activate($plugin);
}
