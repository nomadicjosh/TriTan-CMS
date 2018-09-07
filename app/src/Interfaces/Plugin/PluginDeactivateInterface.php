<?php
namespace TriTan\Interfaces\Plugin;

/**
 * Plugin Deactivate Interface
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
interface PluginDeactivateInterface
{

    /**
     * Fired during plugin deactivation.
     *
     * @since 0.9.9
     */
    public function deactivate($plugin);
}
