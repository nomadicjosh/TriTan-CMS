<?php namespace TriTan\Plugin;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Plugin Deactivate Interface
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
interface PluginDeactivateInterface
{

    /**
     * Fired during plugin deactivation.
     * 
     * @since 1.0.0
     */
    public function deactivate();
}
