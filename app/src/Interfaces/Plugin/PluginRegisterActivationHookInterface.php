<?php
namespace TriTan\Interfaces\Plugin;

interface PluginRegisterActivationHookInterface
{

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
    public function activation($filename, $function);
    
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
    public function deactivation($filename, $function);
}
