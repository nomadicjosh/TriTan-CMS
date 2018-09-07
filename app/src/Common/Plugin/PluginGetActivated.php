<?php
namespace TriTan\Common\Plugin;

use TriTan\Interfaces\Plugin\PluginGetActivatedMapperInterface;
use TriTan\Interfaces\Plugin\PluginGetActivatedInterface;

class PluginGetActivated implements PluginGetActivatedInterface
{
    public $mapper;
    
    public function __construct(PluginGetActivatedMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
    
    /**
     * Returns a list of all plugins that have been activated.
     *
     * @since 0.9.9
     * @return mixed
     */
    public function get()
    {
        return $this->mapper->{'get'}();
    }
}
