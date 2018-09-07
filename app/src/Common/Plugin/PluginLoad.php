<?php
namespace TriTan\Common\Plugin;

use TriTan\Interfaces\Plugin\PluginLoadInterface;
use TriTan\Interfaces\Plugin\PluginDeactivateInterface;
use TriTan\Interfaces\Plugin\PluginGetActivatedInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Exception\NotFoundException;
use TriTan\Exception\Exception;

class PluginLoad implements PluginLoadInterface
{
    public $active;
    
    public $check;
    
    public $context;
    
    public function __construct(PluginGetActivatedInterface $active, PluginDeactivateInterface $check, ContextInterface $context)
    {
        $this->active = $active;
        $this->check = $check;
        $this->context = $context;
    }

    /**
     * Loads all activated plugin for inclusion.
     *
     * @access public
     * @since 0.9
     * @return mixed
     */
    public function load($plugins_dir)
    {
        $plugins = $this->active->{'get'}();
        
        foreach ($plugins as $plugin) {
            $pluginFile = $this->context->obj['util']->{'escape'}($plugin['plugin_location']);
            $_plugin = str_replace('.plugin.php', '', $pluginFile);

            if (!$this->context->obj['file']->{'exists'}($plugins_dir . $_plugin . DS . $pluginFile, false)) {
                $file = $plugins_dir . $pluginFile;
            } else {
                $file = $plugins_dir . $_plugin . DS . $pluginFile;
            }
            
            try {
                $this->context->obj['file']->{'checkSyntax'}($file);
            } catch (NotFoundException $ex) {
                $this->check->{'deactivate'}($this->context->obj['util']->{'escape'}($plugin['plugin_location']));
                $this->context->obj['flash']->{'error'}($ex->getMessage());
                return false;
            } catch (Exception $ex) {
                $this->check->{'deactivate'}($this->context->obj['util']->{'escape'}($plugin['plugin_location']));
                $this->context->obj['flash']->{'error'}($ex->getMessage());
                return false;
            }

            if ($this->context->obj['file']->{'exists'}($file, false)) {
                require_once($file);
            } else {
                $this->check->{'deactivate'}($this->context->obj['util']->{'escape'}($plugin['plugin_location']));
            }
        }
    }
}
