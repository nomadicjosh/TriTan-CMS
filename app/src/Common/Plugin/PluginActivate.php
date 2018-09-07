<?php
namespace TriTan\Common\Plugin;

use TriTan\Interfaces\Plugin\PluginActivateInterface;
use TriTan\Interfaces\Plugin\PluginActivateMapperInterface;

/**
 * Plugin Activate
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class PluginActivate implements PluginActivateInterface
{
    public $mapper;
    
    public function __construct(PluginActivateMapperInterface $mapper)
    {
        $this->mapper = $mapper;
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
        return $this->mapper->{'activate'}($plugin);
    }
}
