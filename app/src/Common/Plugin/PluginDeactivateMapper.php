<?php
namespace TriTan\Common\Plugin;

use TriTan\Container as c;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Interfaces\Plugin\PluginDeactivateMapperInterface;

/**
 * Plugin Activate Mapper
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

class PluginDeactivateMapper implements PluginDeactivateMapperInterface
{
    /**
     * Database object.
     *
     * @var object
     */
    public $db;

    /**
     * __construct class constructor
     *
     * @since 0.9.9
     * @param object $db Database interface.
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Deactivates a specific plugin that is called by $_GET['id'] variable.
     *
     * @since 0.9.9
     * @param string $plugin ID of the plugin to deactivate.
     */
    public function deactivate($plugin)
    {
        $this->db->table(c::getInstance()->get('tbl_prefix') . 'plugin')
                 ->where('plugin_location', $plugin)
                 ->delete();
    }
}
