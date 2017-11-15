<?php namespace TriTan;

use TriTan\Config;

/**
 * Liten - PHP 5 micro framework
 *
 * @link http://www.litenframework.com
 * @version 1.0.0
 * @package Liten
 *         
 *          The MIT License (MIT)
 *          Copyright (c) 2015 Joshua Parker
 *         
 *          Permission is hereby granted, free of charge, to any person obtaining a copy
 *          of this software and associated documentation files (the "Software"), to deal
 *          in the Software without restriction, including without limitation the rights
 *          to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *          copies of the Software, and to permit persons to whom the Software is
 *          furnished to do so, subject to the following conditions:
 *         
 *          The above copyright notice and this permission notice shall be included in
 *          all copies or substantial portions of the Software.
 *         
 *          THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *          IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *          FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *          AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *          LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *          OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *          THE SOFTWARE.
 */
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

class Cache
{

    // cache compression
    const CACHE_COMPRESSION = false;
    // cache compression level
    const CACHE_COMPRESSION_LEVEL = 9;
    // cache debug
    const CACHE_DEBUG = false;
    // cache file extension
    const CACHE_EXTENSION = '.php';
    // cache security key
    const CACHE_SECURITY = 'd15sdf8szefs698df15sd7';
    // cache will be kept so long (in seconds)
    const CACHE_TIME = 360;
    // cache is data
    const IS_DATA = 'data';
    // cache is output
    const IS_OUTPUT = 'view';

    /**
     * All settings for cache will be saved here
     *
     * @var array
     */
    protected static $cache;

    /**
     * Cancel saving output to cache
     */
    public static function cancel()
    {
        self::$cache['output'] = false;
    }

    /**
     * Convert to object
     *
     * @param mixed $data
     */
    private static function convertToObject($data)
    {
        // define new class
        $obj = new stdclass();
        // data is an object
        if (is_object($data)) {
            // loop data
            foreach ($data as $key => $value) {
                // add key to object
                $obj->$key = self::convertToObject($value);
            }
            // data is an array
        } elseif (is_array($data)) {
            // define keys
            $keys = array_keys($data);
            // we have keys
            if (count($keys) > 0) {
                // loop keys
                foreach ($keys as $key) {
                    // add key to object
                    $obj->$key = self::convertToObject($data[$key]);
                }
            }
            // else set data as object
        } else
            $obj = $data;
        // return object
        return $obj;
    }

    /**
     * Delete cache
     *
     * @param string $filePath
     */
    protected static function delete($filePath)
    {
        // delete file if exists
        if (file_exists($filePath))
            @unlink($filePath);
    }

    /**
     * Does cache exists?
     *
     * @param string $type
     * @param string $folder
     * @param string $name
     */
    public static function exists($type, $folder, $name)
    {
        // cache is enabled
        if (self::isCacheEnabled()) {
            // define cache file path
            $cacheFilePath = self::getCachePathToFile($type, $folder, $name);
            // it exists and is not yet over-time
            if (file_exists($cacheFilePath) && filemtime($cacheFilePath) > time())
                return true;
        }
        return false;
    }

    /**
     * Get cache extension
     *
     * @return string
     */
    private static function getCacheExtension()
    {
        // if no cache extension set
        if (!isset(self::$cache['extension'])) {
            // define to the default cache extension
            self::setCacheExtension(self::CACHE_EXTENSION);
        }
        return self::$cache['extension'];
    }

    /**
     * Get cache path where the caches will be saved to
     *
     * @return string
     */
    public static function getCachePath()
    {
        // cache path not defined
        if (!isset(self::$cache['path'])) {
            // redefine cache path to default path
            self::setCachePath($_SERVER['DOCUMENT_ROOT'] . '/cache/');
        }
        return self::$cache['path'];
    }

    /**
     * Get path to the cached file
     *
     * @param  string $type
     * @param  string $folder
     * @param  string $name
     * @return string
     */
    protected static function getCachePathToFile($type, $folder, $name)
    {
        // define id for filename
        $name = (is_array($name)) ? implode('_', $name) : $name;
        // define encrypted id
        $encryptedId = md5(self::CACHE_SECURITY . $name);
        // return path to the cached file
        return self::getCachePath() . $folder . '/' . $encryptedId . $type . self::getCacheExtension();
    }

    /**
     * Get data from cache
     *
     * @param  string         $folder
     * @param  string         $name
     * @param  bool[optional] $overwrite
     * @return mixed
     */
    public static function getData($folder, $name, $overwrite = false)
    {
        // cache is enabled, data-file exists and it should not be overridden
        if (self::isCacheEnabled() && !$overwrite && self::exists(self::IS_DATA, $folder, $name)) {
            // we return the cached data-file
            return self::unserialize(self::read(self::IS_DATA, $folder, $name));
        }
        // otherwise return false
        return false;
    }

    /**
     * Is cache enabled
     *
     * @return bool
     */
    public static function isCacheEnabled()
    {
        // cache enabled not set
        if (!isset(self::$cache['enabled'])) {
            // redefine and enable cache
            self::$cache['enabled'] = true;
        }
        return self::$cache['enabled'];
    }

    /**
     * Read cache
     *
     * @param  string $type
     * @param  string $folder
     * @param  string $name
     * @return mixed
     */
    protected static function read($type, $folder, $name)
    {
        // define cache file path
        $cacheFilePath = self::getCachePathToFile($type, $folder, $name);
        // cache already exists
        if (self::exists($type, $folder, $name)) {
            // get content from existing cache
            $content = file_get_contents($cacheFilePath);
            // uncompress if necessary
            if (self::CACHE_COMPRESSION && function_exists('gzuncompress'))
                $content = gzuncompress($content);
            // return content
            return $content;
        }
        // delete cache
        self::delete($cacheFilePath);
        // return false
        return false;
    }

    /**
     * Set cache extension
     *
     * @param string $extension
     */
    public static function setCacheExtension($extension)
    {
        // redefine
        $extension = (string) $extension;
        // throw error when '.' not found
        if (strpos($extension, '.') === false) {
            throw new CacheException('The extension should contain a point.');
        }
        // redefine cache extension
        self::$cache['extension'] = $extension;
    }

    /**
     * Set cache path
     *
     * @param string $path
     */
    public static function setCachePath($path)
    {
        // redefine cache path
        self::$cache['path'] = (string) $path;
    }

    /**
     * Set data in cache
     *
     * @param string         $folder
     * @param string         $name
     * @param mixed          $data
     * @param bool[optional] $lifetime
     */
    public static function setData($folder, $name, $data, $lifetime = false)
    {
        // cache is enabled
        if (self::isCacheEnabled()) {
            // we should write data to a cache file
            self::write(self::IS_DATA, $folder, $name, self::serialize($data), $lifetime);
        }
    }

    /**
     * Start saving output to cache
     *
     * @param  string         $folder
     * @param  string         $name
     * @param  bool[optional] $lifetime
     * @param  bool[optional] $overwrite
     * @return bool
     */
    public static function start($folder, $name, $lifetime = false, $overwrite = false)
    {
        // define output per default as false
        self::$cache['output'] = false;
        // always override if debug is true
        if ((bool) self::CACHE_DEBUG)
            $overwrite = true;
        // cache is enabled
        if (self::isCacheEnabled()) {
            // cache exists and we should not override
            if (self::exists(self::IS_OUTPUT, $folder, $name) && !$overwrite) {
                // read in cache and output it
                echo self::read(self::IS_OUTPUT, $folder, $name);
                return false;
                // cache doesn't exists or we should override it
            } else {
                // start fetching output
                ob_start();
                // redefine variables
                self::$cache['folder'] = $folder;
                self::$cache['name'] = $name;
                self::$cache['time'] = $lifetime ? $lifetime : self::CACHE_TIME;
                self::$cache['output'] = !self::CACHE_DEBUG;
            }
        }
        // return true
        return true;
    }

    /**
     * Stop saving output to cache
     */
    public static function stop()
    {
        // cache is enabled
        if (self::isCacheEnabled()) {
            // we should save output
            if (self::$cache['output']) {
                // get page content from memory
                $content = ob_get_contents();
                // save content to a cache file
                self::write(self::IS_OUTPUT, self::$cache['folder'], self::$cache['name'], $content, self::$cache['time']);
            }
            // show output
            ob_end_flush();
            flush();
        }
    }

    /**
     * Serialize
     *
     * @param  mixed  $data
     * @return string
     */
    public static function serialize($data)
    {
        // is object
        if (is_object($data)) {
            $data = self::convertToObject($data);
            // is array
        } elseif (is_array($data)) {
            // define keys from array
            $keys = array_keys($data);
            // we have keys
            if (count($keys) > 0) {
                // loop keys
                foreach ($keys as $key) {
                    // add key and its serialized data
                    $data[$key] = self::serialize($data[$key]);
                }
            }
        }
        // return serialized data
        return serialize($data);
    }

    /**
     * Unserialize
     *
     * @param  mixed $data
     * @return array
     */
    public static function unserialize($data)
    {
        // unserialize data
        $data = unserialize($data);
        // data is array
        if (is_array($data)) {
            // define keys
            $keys = array_keys($data);
            // we have keys
            if (count($keys) > 0) {
                // loop keys
                foreach ($keys as $key) {
                    // add key and its unserialized data
                    $data[$key] = self::unserialize($data[$key]);
                }
            }
        }
        // return unserialized data
        return $data;
    }

    public static function flush()
    {
        $files = glob(Config::get('cache_path') . '*.tpl');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Write
     *
     * @param string          $type
     * @param string          $folder
     * @param string          $name
     * @param mixed           $content
     * @param mixed[optional] $lifetime
     */
    protected static function write($type, $folder, $name, $content, $lifetime = false)
    {
        // directory not exists
        if (!is_dir(self::getCachePath() . $folder . '/')) {
            // create directory
            _mkdir(self::getCachePath() . $folder . '/');
        }
        // define file path
        $filePath = self::getCachePathToFile($type, $folder, $name);
        // define file stream
        $fh = fopen($filePath, 'w');
        // compress content when necessary
        if (self::CACHE_COMPRESSION && function_exists('gzcompress'))
            $content = gzcompress($content, self::CACHE_COMPRESSION_LEVEL);
        // write data to file
        fwrite($fh, $content);
        // close file stream
        fclose($fh);
        // define file lifetime
        $lifetime = ($lifetime) ? $lifetime : self::CACHE_TIME;
        // set file lifetime
        touch($filePath, time() + $lifetime);
    }
}
