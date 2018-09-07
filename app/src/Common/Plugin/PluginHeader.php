<?php
namespace TriTan\Common\Plugin;

use TriTan\Interfaces\Plugin\PluginHeaderInterface;

class PluginHeader implements PluginHeaderInterface
{
    /**
     * All plugins header information in an array.
     *
     * @access public
     * @var array
     */
    public $plugins_header = [];

    /**
     * Returns the plugin header information
     *
     * @since 0.9.9
     * @param string (optional) $plugins_dir Loads plugins from specified folder.
     * @return mixed
     */
    public function read($plugins_dir = '')
    {
        if ($handle = opendir($plugins_dir)) {
            while ($file = readdir($handle)) {
                if (is_file($plugins_dir . $file)) {
                    if (strpos($plugins_dir . $file, '.plugin.php')) {
                        $fp = fopen($plugins_dir . $file, 'r');
                        // Pull only the first 8kiB of the file in.
                        $plugin_data = fread($fp, 8192);
                        fclose($fp);

                        preg_match('|Plugin Name:(.*)$|mi', $plugin_data, $name);
                        preg_match('|Plugin URI:(.*)$|mi', $plugin_data, $uri);
                        preg_match('|Version:(.*)|i', $plugin_data, $version);
                        preg_match('|Description:(.*)$|mi', $plugin_data, $description);
                        preg_match('|Author:(.*)$|mi', $plugin_data, $author_name);
                        preg_match('|Author URI:(.*)$|mi', $plugin_data, $author_uri);
                        preg_match('|Plugin Slug:(.*)$|mi', $plugin_data, $plugin_slug);

                        foreach ([
                          'name',
                          'uri',
                          'version',
                          'description',
                          'author_name',
                          'author_uri',
                          'plugin_slug'
                        ] as $field) {
                            if (!empty(${$field})) {
                                ${$field} = trim(${$field}[1]);
                            } else {
                                ${$field} = '';
                            }
                        }
                        $plugin_data = [
                            'filename' => $file,
                            'Name' => $name,
                            'Title' => $name,
                            'PluginURI' => $uri,
                            'Description' => $description,
                            'Author' => $author_name,
                            'AuthorURI' => $author_uri,
                            'Version' => $version
                        ];
                        $this->plugins_header[] = $plugin_data;
                    }
                } elseif ((is_dir($plugins_dir . $file)) && ($file != '.') && ($file != '..')) {
                    $this->read($plugins_dir . $file . '/');
                }
            }

            closedir($handle);
        }
        return $this->plugins_header;
    }
}
