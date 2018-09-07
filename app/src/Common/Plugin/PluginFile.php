<?php
namespace TriTan\Common\Plugin;

use TriTan\Interfaces\Plugin\PluginFileInterface;
use TriTan\Interfaces\FileSystemInterface;

class PluginFile implements PluginFileInterface
{
    public $file;
    
    public function __construct(FileSystemInterface $file)
    {
        $this->file = $file;
    }
    /**
     * Plugin Basename
     *
     * The method extracts the file name of a specific plugin.
     *
     * @since 0.9.9
     * @param string $filename Plugin's file name.
     * @return string The file name of the plugin.
     */
    public function basename($filename)
    {
        $plugin_dir = $this->file->{'normalizePath'}(TTCMS_PLUGIN_DIR);
        $mu_plugin_dir = $this->file->{'normalizePath'}(TTCMS_MU_PLUGIN_DIR);

        $filename = preg_replace('#^' . preg_quote($plugin_dir, '#') . '/|^' . preg_quote($mu_plugin_dir, '#') . '/#', '', $filename);
        $filename = trim($filename, '/');
        return basename($filename);
    }
    
    /**
     * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
     *
     * @since 0.9.9
     * @param string $filename The filename of the plugin (__FILE__).
     * @return string The filesystem path of the directory that contains the plugin.
     */
    public function path($filename)
    {
        return $this->file->{'addTrailingSlash'}(dirname($filename));
    }
}
