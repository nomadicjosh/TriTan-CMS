<?php
namespace TriTan\Common\Plugin;

use TriTan\Container as c;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Interfaces\Plugin\PluginActivateMapperInterface;

/**
 * Plugin Activate Mapper
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

class PluginActivateMapper implements PluginActivateMapperInterface
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
     * Activates a specific plugin that is called by $_GET['id'] variable.
     *
     * @since 0.9.9
     * @param string $plugin ID of the plugin to activate
     * @return mixed
     */
    public function activate($plugin)
    {
        $this->db->table(c::getInstance()->get('tbl_prefix') . 'plugin')
             ->insert([
                  'plugin_location' => $plugin
              ]);
    }
}
