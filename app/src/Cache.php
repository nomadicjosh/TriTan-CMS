<?php
namespace TriTan;

use TriTan\Container as c;

/**
 * Liten - PHP 5 micro framework
 *
 * @link http://www.litenframework.com
 * @version 0.9
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

defined('CACHE_PATH') or define('CACHE_PATH', c::getInstance()->get('cache_path'));

class Cache
{

    /**
     * The path to the cache file folder
     *
     * @access protected
     * @since 1.0.1
     * @var string
     */
    protected $cachepath = CACHE_PATH;

    /**
     * The key name of the cache file
     *
     * @access private
     * @since 1.0.1
     * @var string
     */
    public $cachename = 'default';

    /**
     * The cache file extension
     *
     * @access protected
     * @since 1.0.1
     * @var string
     */
    protected $extension = '.screen';

    /**
     * Time to live for cache file
     *
     * @access private
     * @since 1.0.1
     * @var int
     */
    public $setTTL = '3600';

    /**
     * Full location of cache file
     *
     * @access protected
     * @since 1.0.1
     * @var string
     */
    protected $cachefile;

    /**
     * Execution Time
     *
     * @access protected
     * @since 1.0.1
     * @var float
     */
    protected $starttime;

    /**
     * Logs errors that may occur
     *
     * @access protected
     * @since 1.0.1
     * @var float
     */
    protected $log;

    public function __construct($name = '')
    {
        $this->cachename = $name;
        if (!is_dir($this->cachepath) || !is_writeable($this->cachepath)) {
            mkdir($this->cachepath, 0755);
        }
        $this->cachefile = $this->cachepath . md5($this->cachename) . $this->extension;
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $this->starttime = $mtime;
    }

    /**
     * Sets objects that should be cached.
     *
     * @access public
     * @since 1.0.1
     * @param string (required) $key Prefix of the cache file
     * @param mixed (required) $data The object that should be cached
     * @return mixed
     */
    public function set($key, $data)
    {
        $values = serialize($data);
        $cachefile = $this->cachepath . $key . $this->extension;
        $cache = fopen($cachefile, 'w');
        if ($cache) {
            fwrite($cache, $values);
            fclose($cache);
        } else {
            return $this->addLog('Unable to write key: ' . $key . ' file: ' . $cachefile);
        }
    }

    /**
     * Cached data by its Prefix
     *
     * @access public
     * @since 1.0.1
     * @param string (required) $key Returns cached objects by its key.
     * @return mixed
     */
    public function get($key)
    {
        $cachefile = $this->cachepath . $key . $this->extension;
        $file = fopen($cachefile, 'r');
        if (filemtime($cachefile) < (time() - $this->setTTL)) {
            $this->clearCache($key);
            return false;
        }
        if ($file) {
            $data = fread($file, filesize($cachefile));
            fclose($file);
            return unserialize($data);
        }
    }

    /**
     * Begins the section where caching begins
     *
     * @access public
     * @since 1.0.1
     * @return mixed
     */
    public function setCache()
    {
        if (!$this->isCacheValid($this->cachefile)) {
            ob_start();
            return $this->addLog('Could not find valid cachefile: ' . $this->cachefile);
        } else {
            return true;
        }
    }

    /**
     * Ends the section where caching stops and returns
     * the cached file.
     *
     * @access public
     * @since 1.0.1
     * @return mixed
     */
    public function getCache()
    {
        if (!$this->isCacheValid($this->cachefile)) {
            $output = ob_get_contents();
            ob_end_clean();
            $this->writeCache($output, $this->cachefile);
        } else {
            $output = $this->readCache($this->cachefile);
        }
        return $output;
    }

    /**
     * Reads a cache file if it exists and prints it out
     * to the screen.
     *
     * @access public
     * @since 1.0.1
     * @param string (required) $filename Full path to the requested cache file
     * @return mixed
     */
    public function readCache($filename)
    {
        if (file_exists($filename)) {
            $cache = fopen($filename, 'r');
            $output = fread($cache, filesize($filename));
            fclose($cache);
            return unserialize($output) . "\n" . $this->pageLoad();
        } else {
            return $this->addLog('Could not find filename: ' . $filename);
        }
    }

    /**
     * Writes cache data to be read
     *
     * @access public
     * @since 1.0.1
     * @param string (required) $data Data that should be cached
     * @param string (required) $filename Name of the cache file
     * @return mixed
     */
    public function writeCache($data, $filename)
    {
        $fp = fopen($filename, 'w');
        if ($fp) {
            $values = serialize($data);
            fwrite($fp, $values);
            fclose($fp);
        } else {
            return $this->addLog('Could not read filename: ' . $filename . ' data: ' . $data);
        }
    }

    /**
     * Checks if a cache file is valid
     *
     * @access public
     * @since 1.0.1
     * @param string (required) $filename Name of the cache file
     * @return mixed
     */
    public function isCacheValid($filename)
    {
        if (file_exists($filename) && (filemtime($filename) > (time() - $this->setTTL))) {
            return true;
        } else {
            return $this->addLog('Could not find filename: ' . $filename);
        }
    }

    /**
     * Execution time of the cached page
     *
     * @access public
     * @since 1.0.1
     * @return mixed
     */
    public function pageLoad()
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $endtime = $mtime;
        $totaltime = ($endtime - $this->starttime);
        return "<!-- This cache file was built for ( " . $_SERVER['SERVER_NAME'] . " ) in " . $totaltime . " seconds, on " . gmdate("M d, Y") . " @ " . gmdate("H:i:s A") . " UTC. -->" . "\n";
    }

    /**
     * Clears the cache base on cache file name/key
     *
     * @access public
     * @since 1.0.1
     * @param string (required) $filename Key name of cache
     * @return mixed
     */
    public function clearCache($filename)
    {
        $cachelog = $this->cachepath . md5($filename) . $this->extension;
        if (file_exists($cachelog)) {
            unlink($cachelog);
        }
    }

    /**
     * Clears all cache files
     *
     * @access public
     * @since 1.0.1
     * @return mixed
     */
    public function purge()
    {
        foreach (glob($this->cachepath . '*' . $this->extension) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Prints a log if error occurs
     *
     * @access public
     * @since 1.0.1
     * @param mixed (required) $value Message that should be returned
     * @return mixed
     */
    public function addLog($value)
    {
        $this->log = [];
        array_push($this->log, round((microtime(true) - $this->starttime), 5) . 's - ' . $value);
    }
}
