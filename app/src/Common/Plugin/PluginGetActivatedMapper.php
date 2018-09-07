<?php
namespace TriTan\Common\Plugin;

use TriTan\Container as c;
use TriTan\Interfaces\Plugin\PluginGetActivatedMapperInterface;
use TriTan\Interfaces\DatabaseInterface;

class PluginGetActivatedMapper implements PluginGetActivatedMapperInterface
{
    public $db;
    
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }
    
    /**
     * Returns a list of all plugins that have been activated.
     *
     * @since 0.9.9
     * @return mixed
     */
    public function get()
    {
        $plugin = $this->db->table(c::getInstance()->get('tbl_prefix') . 'plugin');
        return $plugin->all();
    }
}
