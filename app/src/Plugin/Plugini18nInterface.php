<?php namespace TriTan\Plugin;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Plugin Textdomain Interface
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
interface Plugini18nInterface
{

    /**
     * Load the plugin text domain for translation.
     *
     * @since    0.9
     */
    public function loadPluginTextdomain();
}
