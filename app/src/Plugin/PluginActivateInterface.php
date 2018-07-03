<?php
namespace TriTan\Plugin;

/**
 * Plugin Activate Interface
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
interface PluginActivateInterface
{

    /**
     * Fired during plugin activation.
     *
     * @since 0.9
     */
    public function activate();
}
