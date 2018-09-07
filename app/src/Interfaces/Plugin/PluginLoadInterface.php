<?php
namespace TriTan\Interfaces\Plugin;

interface PluginLoadInterface
{
    /**
     * Loads all activated plugin for inclusion.
     *
     * @access public
     * @since 0.9
     * @return mixed
     */
    public function load($plugins_dir);
}
