<?php
namespace TriTan\Interfaces\Plugin;

interface PluginFileInterface
{
    /**
     * Plugin Basename
     *
     * The method extracts the file name of a specific plugin.
     *
     * @since 0.9.9
     * @param string $filename Plugin's file name.
     * @return string The file name of the plugin.
     */
    public function basename($filename);
    
    /**
     * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
     *
     * @since 0.9.9
     * @param string $filename The filename of the plugin (__FILE__).
     * @return string The filesystem path of the directory that contains the plugin.
     */
    public function path($filename);
}
