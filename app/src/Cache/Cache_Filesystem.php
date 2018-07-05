<?php
namespace TriTan\Cache;

use TriTan\Config;
use TriTan\Exception\Exception;
use TriTan\Exception\IOException;
use Cascade\Cascade;
use TriTan\Functions\Core;

/**
 * TriTan CMS Filesystem Cache Class.
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @subpackage Cache
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Cache_Filesystem extends \TriTan\Cache\Abstract_Cache
{

    /**
     * Application object.
     *
     * @since 0.9
     * @var object
     */
    public $app;

    /**
     * Cache directory object.
     *
     * @since 0.9
     * @var string
     */
    protected $_dir;

    /**
     * Holds the cached objects.
     *
     * @since 0.9
     * @var array
     */
    protected $_cache = [];

    /**
     * Holds the memory limit object.
     *
     * @since 0.9
     * @var int
     */
    protected $_memory_limit;

    /**
     * Holds the memory limit object
     *
     * @since 0.9
     * @var int
     */
    protected $_memory_low;

    /**
     * Should the cache persist or not.
     *
     * @since 0.9
     * @var bool
     */
    public $persist;

    /**
     * Sets if cache is enabled or not.
     *
     * @since 0.9
     * @var bool
     */
    public $enable;

    public function __construct(\Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();

        if (TTCMS_FILE_CACHE_LOW_RAM && function_exists('memory_get_usage')) {
            $limit = _trim(ini_get('memory_limit'));
            $mod = strtolower($limit[strlen($limit) - 1]);
            switch ($mod) {
                case 'g':
                    $limit *= 1073741824;
                    break;
                case 'm':
                    $limit *= 1048576;
                    break;
                case 'k':
                    $limit *= 1024;
                    break;
            }

            if ($limit <= 0) {
                $limit = 0;
            }

            $this->_memory_limit = $limit;

            $limit = _trim(TTCMS_FILE_CACHE_LOW_RAM);
            $mod = strtolower($limit[strlen($limit) - 1]);
            switch ($mod) {
                case 'g':
                    $limit *= 1073741824;
                    break;
                case 'm':
                    $limit *= 1048576;
                    break;
                case 'k':
                    $limit *= 1024;
                    break;
            }

            $this->_memory_low = $limit;
        } else {
            $this->_memory_limit = 0;
            $this->_memory_low = 0;
        }

        /**
         * Filter sets whether caching is enabled or not.
         *
         * @since 0.9
         * @var bool
         */
        $this->enable = $this->app->hook->{'apply_filter'}('enable_caching', true);

        $this->persist = $this->enable && true;

        /**
         * File system cache directory.
         */
        $dir = Config::get('cache_path');

        /**
         * Filter the file cache directory in order to override it
         * in case some systems are having issues.
         *
         * @since 0.9
         * @param string $dir
         *            The directory where file system cache files are saved.
         */
        $cacheDir = $this->app->hook->{'apply_filter'}('filesystem_cache_dir', $dir);

        /**
         * If the cache directory does not exist, the create it first
         * before trying to call it for use.
         */
        if (!is_dir($cacheDir) || !Core\ttcms_file_exists($cacheDir, false)) {
            try {
                Core\_mkdir($cacheDir);
            } catch (IOException $e) {
                Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
            } catch (Exception $e) {
                Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
            }
        }

        /**
         * If the directory isn't writable, throw an exception.
         */
        if (!Core\ttcms_is_writable($cacheDir)) {
            throw new IOException(Core\_t('Could not create the file cache directory.'));
        }

        /**
         * Cache directory is set.
         */
        $this->_dir = $cacheDir . DS;
    }

    /**
     * Adds data to the cache.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::create()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of the cache file.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the cache file. Default: 0 = expires immediately after request.
     */
    public function create($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (!$this->enable) {
            return false;
        }

        if (empty($namespace)) {
            $namespace = 'default';
        }

        return $this->set($key, $data, $namespace, (int) $ttl);
    }

    /**
     * Fetches cached data.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::read()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of the cache file.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    public function read($key, $namespace = 'default')
    {
        if (!$this->enable) {
            return false;
        }

        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!$this->exists($key, $namespace)) {
            $this->cacheMisses();
            return false;
        }

        if (isset($this->_cache[$namespace], $this->_cache[$namespace][$key])) {
            $this->cacheHits();
            return $this->_cache[$namespace][$key];
        }

        $filename = $this->keyToPath($key, $namespace);

        $get_data = Core\_file_get_contents($filename, LOCK_EX);

        $data = $this->app->hook->{'maybe_unserialize'}($get_data);

        if ($this->persist) {
            if ($this->_memory_limit) {
                $usage = memory_get_usage();
                if ($this->_memory_limit - $usage < $this->_memory_low) {
                    $this->cacheMisses();
                    return false;
                }
            }

            $files = glob($filename);
            if (empty($files) || !isset($files[0])) {
                $this->cacheMisses();
                return false;
            }

            if (is_readable($files[0])) {
                $result = $files[0];
                $time = $data[0] - filemtime($result);

                $now = time();
                if ((filemtime($result) + $time < $now)) {
                    $this->cacheMisses();
                    unlink($result);
                    return false;
                }

                if ((filemtime($result) + $time > $now)) {
                    $this->cacheHits();
                    settype($result, 'string');
                    $this->_cache[$namespace][$key] = $data[1];
                    $result = $this->_cache[$namespace][$key];
                    return is_object($result) ? clone ($result) : $result;
                }
            }

            unlink($files[0]);
        }
    }

    /**
     * Updates a cache file based on unique ID.
     * This method only exists for
     * CRUD completeness purposes and just basically calls the create method.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::update()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of the cache file.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the cache file. Default: 0 = expires immediately after request.
     */
    public function update($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (!$this->enable) {
            return false;
        }

        if (empty($namespace)) {
            $namespace = 'default';
        }

        return $this->create($key, $data, $namespace, (int) $ttl);
    }

    /**
     * Deletes a cache file based on unique key.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::delete()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of cache file.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @return bool Returns true if the cache was deleted or false otherwise.
     */
    public function delete($key, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        unset($this->_cache[$namespace][$key]);

        if (!$this->exists($key, $namespace)) {
            return false;
        }

        $filename = $this->keyToPath($key, $namespace);

        return rename($filename, $filename . $this->inc($key, 1, $namespace));
    }

    /**
     * Flushes the file system cache completely.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::flush()
     *
     * @since 0.9
     * @return bool Returns true if the cache was purged or false otherwise.
     */
    public function flush()
    {
        $this->remove_dir($this->_dir);
        $this->_cache = [];

        return true;
    }

    /**
     * Removes all cache items from a particular namespace.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::flushNamespace()
     *
     * @since 0.9
     * @param int|string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @return bool Returns true if the namespace was purged or false otherwise.
     */
    public function flushNamespace($namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $dir = $this->_dir . $namespace;
        $this->remove_dir($dir);

        return true;
    }

    /**
     * Sets the data contents into the cache.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::set()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of the cache file.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the cache file. Default: 0 = expires immediately after request.
     * @return bool Returns true if the cache was set and false otherwise.
     */
    public function set($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (!$this->enable) {
            return false;
        }

        if (empty($namespace)) {
            $namespace = 'default';
        }

        /**
         * Removes any and all stale items from the cache before
         * adding more items to the specified namespace.
         */
        $this->removeStaleCache($namespace, (int) $ttl);

        if ($this->_memory_limit) {
            $usage = memory_get_usage();
            if ($this->_memory_limit - $usage < $this->_memory_low) {
                unlink($this->keyToPath($key, $namespace));
                return false;
            }
        }

        if (is_object($data)) {
            $data = clone ($data);
        }

        $this->_cache[$namespace][$key] = $data;

        $filename = $this->keyToPath($key, $namespace);

        if ($this->exists($key, $namespace)) {
            return false;
        }
        // Opening the file in read/write mode
        $h = fopen($filename, 'a+');
        // If there is an issue with the handler, throw an exception.
        if (!$h) {
            throw new IOException(Core\_t('Could not write to cache.'));
        }
        // exclusive lock, will get released when the file is closed
        flock($h, LOCK_EX);
        // go to the start of the file
        fseek($h, 0);
        // truncate the file
        ftruncate($h, 0);
        // Serializing along with the TTL
        $data = $this->app->hook->{'maybe_serialize'}([
            time() + (int) $ttl,
            $data
        ]);
        if (fwrite($h, $data) === false) {
            throw new IOException(Core\_t('Could not write to cache.'));
        }
        fclose($h);

        return true;
    }

    /**
     * Echoes the stats of the cache.
     *
     * Gives the cache hits, cache misses and cache uptime.
     *
     * @since 0.9
     */
    public function getStats()
    {
        if (!$this->enable) {
            return false;
        }

        echo "<p>";
        echo "<strong>" . Core\_t('Cache Hits:', 'tritan-cms') . "</strong> " . Core\_file_get_contents($this->_dir . 'cache_hits.txt') . "<br />";
        echo "<strong>" . Core\_t('Cache Misses:', 'tritan-cms') . "</strong> " . Core\_file_get_contents($this->_dir . 'cache_misses.txt') . "<br />";
        echo "<strong>" . Core\_t('Uptime:', 'tritan-cms') . "</strong> " . time_ago(filemtime($this->_dir)) . "<br />";
        echo "</p>";
    }

    /**
     * Increments numeric cache item's value.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::inc()
     *
     * @since 0.9
     * @param int|string $key
     *            The cache key to increment
     * @param int $offset
     *            Optional. The amount by which to increment the item's value. Default: 1.
     * @param string $namespace
     *            Optional. The namespace the key is in. Default: 'default'.
     * @return false|int False on failure, the item's new value on success.
     */
    public function inc($key, $offset = 1, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!$this->exists($key, $namespace)) {
            return false;
        }

        if (!is_numeric($this->_cache[$namespace][$key])) {
            $this->_cache[$namespace][$key] = 0;
        }

        $offset = (int) $offset;

        $this->_cache[$namespace][$key] += $offset;

        if ($this->_cache[$namespace][$key] < 0) {
            $this->_cache[$namespace][$key] = 0;
        }

        return $this->_cache[$namespace][$key];
    }

    /**
     * Decrements numeric cache item's value.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::dec()
     *
     * @since 0.9
     * @param int|string $key
     *            The cache key to decrement.
     * @param int $offset
     *            Optional. The amount by which to decrement the item's value. Default: 1.
     * @param string $namespace
     *            Optional. The namespace the key is in. Default: 'default'.
     * @return false|int False on failure, the item's new value on success.
     */
    public function dec($key, $offset = 1, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!$this->exists($key, $namespace)) {
            return false;
        }

        if (!is_numeric($this->_cache[$namespace][$key])) {
            $this->_cache[$namespace][$key] = 0;
        }

        $offset = (int) $offset;

        $this->_cache[$namespace][$key] -= $offset;

        if ($this->_cache[$namespace][$key] < 0) {
            $this->_cache[$namespace][$key] = 0;
        }

        return $this->_cache[$namespace][$key];
    }

    /**
     * Generates a unique cache key.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::uniqueKey()
     *
     * @since 0.9
     * @access protected
     * @param int|string $key
     *            Unique key for cache file.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    protected function uniqueKey($key, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        return $this->_cache[$namespace][$key] = $namespace . ':' . $key;
    }

    /**
     * Serves as a utility method to determine whether a key exists in the cache.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::exists()
     *
     * @since 0.9
     * @access protected
     * @param int|string $key
     *            Cache key to check for existence.
     * @param string $namespace
     *            Cache namespace for the key existence check.
     * @return bool Whether the cache item exists in the cache for the given key and namespace.
     */
    protected function exists($key, $namespace)
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (is_readable($this->keyToPath($key, $namespace))) {
            return true;
        }
    }

    /**
     * Deletes cache/namespace directory.
     *
     * @since 0.9
     * @param string $dir
     *            Directory that should be removed.
     */
    protected function remove_dir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $dh = opendir($dir);
        if (!is_resource($dh)) {
            return;
        }

        _rmdir($dir);

        closedir($dh);
    }

    /**
     * Counts the number of cache hits
     * and writes it to a file.
     *
     * @since 0.9
     */
    protected function cacheHits()
    {
        $filename = $this->_dir . 'cache_hits.txt';

        if (!is_readable($filename)) {
            $fp = fopen($filename, 'w');
            fwrite($fp, 1);
            fclose($fp);
            return false;
        }

        $fp = fopen($filename, 'c+');
        flock($fp, LOCK_EX);

        $count = (int) fread($fp, filesize($filename));
        ftruncate($fp, 0);
        fseek($fp, 0);
        fwrite($fp, $count + 1);

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * Counts the number of cache misses
     * and writes it to a file.
     *
     * @since 0.9
     */
    protected function cacheMisses()
    {
        $filename = $this->_dir . 'cache_misses.txt';

        if (!is_readable($filename)) {
            $fp = fopen($filename, 'w');
            fwrite($fp, 1);
            fclose($fp);
            return false;
        }

        $fp = fopen($filename, 'c+');
        flock($fp, LOCK_EX);

        $count = (int) fread($fp, filesize($filename));
        ftruncate($fp, 0);
        fseek($fp, 0);
        fwrite($fp, $count + 1);

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * Removes any and all stale items from the cache.
     *
     * @since 0.9
     * @param int|string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the cache file. Default: 0.
     */
    protected function removeStaleCache($namespace = 'default', $ttl = 0)
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $stale = glob($this->_dir . $namespace . DS . '*');
        if (is_array($stale)) {
            foreach ($stale as $filename) {
                if (Core\ttcms_file_exists($filename, false)) {
                    if (time() - filemtime($filename) > (int) $ttl) {
                        unlink($filename);
                    }
                }
            }
        }
    }

    /**
     * Retrieve the cache file.
     *
     * @since 0.9
     * @access protected
     * @param int|string $key
     *            Unqiue key of cache.
     * @param int|string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    private function keyToPath($key, $namespace)
    {
        $dir = $this->_dir . urlencode($namespace);
        if (!Core\ttcms_file_exists($dir, false)) {
            try {
                Core\_mkdir($dir);
            } catch (IOException $e) {
                Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
                return;
            } catch (Exception $e) {
                Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
                return;
            }
        }
        return $this->_dir . urlencode($namespace) . DS . urlencode(md5($key));
    }
}
