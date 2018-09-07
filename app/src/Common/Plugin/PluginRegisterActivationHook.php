<?php
namespace TriTan\Common\Plugin;

use TriTan\Interfaces\Plugin\PluginRegisterActivationHookInterface;
use TriTan\Interfaces\Hooks\ActionFilterHookInterface;

class PluginRegisterActivationHook implements PluginRegisterActivationHookInterface
{
    public $hook;
    
    public function __construct(ActionFilterHookInterface $hook)
    {
        $this->hook = $hook;
    }
    
    /**
     * Register Activation Hook
     *
     * This method is used to run code that should be executed
     * when a plugin is being activated.
     *
     * @since 0.9.9
     * @param string $filename Plugin's file name.
     * @param string $function The function which should be executed.
     */
    public function activation($filename, $function)
    {
        $filename = $this->basename($filename);
        $this->hook->{'addAction'}('activate_' . $filename, $function);
    }
    
    /**
     * Register Deactivation Hook
     *
     * This method is used to run code that should be executed
     * when a plugin is being deactivated.
     *
     * @since 0.9.9
     * @param string $filename Plugin's file name.
     * @param string $function The function which should be executed.
     */
    public function deactivation($filename, $function)
    {
        $filename = $this->basename($filename);
        $this->hook->{'addAction'}('deactivate_' . $filename, $function);
    }
}
