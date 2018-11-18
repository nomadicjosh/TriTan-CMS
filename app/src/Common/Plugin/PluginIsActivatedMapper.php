<?php
namespace TriTan\Common\Plugin;

use TriTan\Container as c;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Interfaces\Plugin\PluginIsActivatedMapperInterface;

/**
 * Plugin Activate Mapper
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

class PluginIsActivatedMapper implements PluginIsActivatedMapperInterface
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
     * Checks if a particular plugin has been activated.
     *
     * @since 0.9.9
     * @return mixed
     */
    public function isActivated($plugin) : bool
    {
        $count = $this->db->table(c::getInstance()->get('tbl_prefix') . 'plugin')->where('plugin_location', $plugin)->count();
        
        return $count > 0 ? true : false;
    }
}
