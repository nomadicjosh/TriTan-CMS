<?php
namespace TriTan\Cache;

use TriTan\Container as c;
use TriTan\Exception\Exception;
use TriTan\Exception\IOException;
use Cascade\Cascade;

/**
 * TriTan CMS Cookie Cache Class.
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @subpackage Cache
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class CacheCookie extends \TriTan\Cache\AbstractCache implements \TriTan\Interfaces\CacheInterface
{

    /**
     * Context object.
     *
     * @since 0.9.9
     * @var object
     */
    public $context;

    /**
     * Cache directory object.
     *
     * @since 0.9.9
     * @var string
     */
    protected $dir;

    /**
     * Holds the cached objects.
     *
     * @since 0.9.9
     * @var array
     */
    protected $cache = [];

    /**
     * Holds the memory limit object.
     *
     * @since 0.9.9
     * @var int
     */
    protected $memory_limit;

    /**
     * Holds the memory limit object
     *
     * @since 0.9.9
     * @var int
     */
    protected $memory_low;

    /**
     * Should the cache persist or not.
     *
     * @since 0.9.9
     * @var bool
     */
    public $persist;

    public function __construct(\TriTan\Interfaces\ContextInterface $context)
    {
        $this->context = $context;

        if (TTCMS_FILE_CACHE_LOW_RAM && function_exists('memory_get_usage')) {
            $limit = ini_get('memory_limit');
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

            $this->memory_limit = $limit;

            $limit = trim(TTCMS_FILE_CACHE_LOW_RAM);
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

            $this->memory_low = $limit;
        } else {
            $this->memory_limit = 0;
            $this->memory_low = 0;
        }

        $this->persist = true;

        /**
         * File system cache directory.
         */
        $dir = c::getInstance()->get('cache_path');

        $cacheDir = $dir;

        /**
         * If the cache directory does not exist, the create it first
         * before trying to call it for use.
         */
        if (!is_dir($cacheDir) || !file_exists($cacheDir, false)) {
            mkdir($cacheDir, 0755, true);
        }

        /**
         * If the directory isn't writable, throw an exception.
         */
        if (!is_writable($cacheDir)) {
            throw new IOException('Could not create the file cache directory.');
        }

        /**
         * Cache directory is set.
         */
        $this->dir = $cacheDir . DS;
    }

    /**
     * Creates the cache file.
     *
     * {@inheritDoc}
     *
     * @see Tritan\Cache\AbstractCache::create()
     *
     * @since 0.9.9
     * @param int|string $key
     *            Unique key of the cache file.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where to namespace the cache contents. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the cache file. Default: 0 = expires immediately after request.
     */
    public function create($key, $data, $namespace = 'default', $ttl = 0)
    {
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
     * @see TriTan\Cache\AbstractCache::read()
     *
     * @since 0.9.9
     * @param int|string $key
     *            Unique key of the cache file.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    public function read($key, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $x = isset($this->context->obj['app']->req->cookie[md5($key)]) ? $this->context->obj['app']->req->cookie[md5($key)] : false;
        if ($x == false) {
            $this->cacheMisses();
            return null;
        } else {
            if (!$this->exists($key, $namespace)) {
                $this->cacheMisses();
                return false;
            }

            if (isset($this->cache[$namespace], $this->cache[$namespace][$key])) {
                $this->cacheHits();
                return $this->cache[$namespace][$key];
            }
            $this->cacheHits();
            $filename = $this->keyToPath($key, $namespace);
        }

        $get_data = $this->context->obj['file']->{'getContents'}($filename, LOCK_EX);

        $data = $this->context->obj['serializer']->{'unserialize'}($get_data);

        if ($this->persist) {
            if ($this->memory_limit) {
                $usage = memory_get_usage();
                if ($this->memory_limit - $usage < $this->memory_low) {
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
                    $this->cache[$namespace][$key] = $data[1];
                    $result = $this->cache[$namespace][$key];
                    return is_object($result) ? clone ($result) : $result;
                }
            }
            $this->context->obj['app']->cookies->remove(md5($key));
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
     * @see TriTan\Cache\AbstractCache::update()
     *
     * @since 0.9.9
     * @param int|string $key
     *            Unique key of the cache file.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where to namespace the cache contents. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the cache file. Default: no expiration.
     */
    public function update($key, $data, $namespace = 'default', $ttl = 0)
    {
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
     * @see TriTan\Cache\AbstractCache::delete()
     *
     * @since 0.9.9
     * @param int|string $key
     *            Unique key of cache file.
     * @param string $namespace
     *            Optional. Where to namespace the cache contents. Default: 'default'.
     * @return bool Returns true if the cache was deleted or false otherwise.
     */
    public function delete($key, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        unset($this->cache[$namespace][$key]);

        if (!$this->exists($key, $namespace)) {
            return false;
        }

        $this->context->obj['app']->cookies->remove(md5($key));

        $filename = $this->keyToPath($key, $namespace);

        return rename($filename, $filename . $this->inc($key, 1, $namespace));
    }

    /**
     * Flushes the file system cache completely.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::flush()
     *
     * @since 0.9.9
     * @return bool Returns true if the cache was purged or false otherwise.
     */
    public function flush()
    {
        $this->removeDir($this->dir);
        $this->cache = [];

        return true;
    }

    /**
     * Removes all cache items from a particular namespace.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::flushNamespace()
     *
     * @since 0.9.9
     * @param int|string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @return bool Returns true if the namespace was purged or false otherwise.
     */
    public function flushNamespace($namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $dir = $this->dir . $namespace;
        $this->removeDir($dir);

        return true;
    }

    /**
     * Sets the data contents into the cache.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::set()
     *
     * @since 0.9.9
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
        if (empty($namespace)) {
            $namespace = 'default';
        }

        /**
         * Removes any and all stale items from the cache before
         * adding more items to the specified namespace.
         */
        $this->removeStaleCache($key, $namespace, (int) $ttl);

        if ($this->memory_limit) {
            $usage = memory_get_usage();
            if ($this->memory_limit - $usage < $this->memory_low) {
                $this->context->obj['app']->cookies->remove(md5($key));
                unlink($this->keyToPath($key, $namespace));
                return false;
            }
        }

        if (is_object($data)) {
            $data = clone ($data);
        }

        $this->cache[$namespace][$key] = $data;

        $filename = $this->keyToPath($key, $namespace);

        if ($this->exists($key, $namespace)) {
            return false;
        }

        // Opening the file in read/write mode
        $h = fopen($filename, 'a+');
        // If there is an issue with the handler, throw an exception.
        if (!$h) {
            throw new Exception('Could not write to cache', 'cookie_cache');
        }

        $this->context->obj['app']->cookies->set(md5($key), $key, $ttl);
        // exclusive lock, will get released when the file is closed
        flock($h, LOCK_EX);
        // go to the start of the file
        fseek($h, 0);
        // truncate the file
        ftruncate($h, 0);
        // Serializing along with the TTL
        $data = $this->context->obj['serializer']{'serialize'}([
            time() + (int) $ttl,
            $data
        ]);
        if (fwrite($h, $data) === false) {
            throw new IOException('Could not write to cache');
        }
        fclose($h);

        return true;
    }

    /**
     * Echoes the stats of the cache.
     *
     * Gives the cache hits, cache misses and cache uptime.
     *
     * @since 0.9.9
     */
    public function getStats()
    {
        echo "<p>";
        echo "<strong>Cache Hits:</strong> " . $this->context->obj['file']->{'getContents'}(
            $this->dir . 'cache_hits.txt'
        ) . "<br />";
        echo "<strong>Cache Misses:</strong> " . $this->context->obj['file']->{'getContents'}(
            $this->dir . 'cache_misses.txt'
        ) . "<br />";
        echo "<strong>Uptime:</strong> " . date('F d Y h:i A', filemtime($this->dir)) . "<br />";
        echo "</p>";
    }

    /**
     * Increments numeric cache item's value.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::inc()
     *
     * @since 0.9.9
     * @param int|string $key
     *            The cache key to increment
     * @param int $offset
     *            Optional. The amount by which to increment the item's value. Default: 1.
     * @param string $namespace
     *            Optional. The namespace the key is in. Default: 'default'.
     * @return false|int False on failure, the item's new value on success.
     */
    public function increment($key, $offset = 1, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!$this->exists($key, $namespace)) {
            return false;
        }

        if (!is_numeric($this->cache[$namespace][$key])) {
            $this->cache[$namespace][$key] = 0;
        }

        $offset = (int) $offset;

        $this->cache[$namespace][$key] += $offset;

        if ($this->cache[$namespace][$key] < 0) {
            $this->cache[$namespace][$key] = 0;
        }

        return $this->cache[$namespace][$key];
    }

    /**
     * Decrements numeric cache item's value.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::dec()
     *
     * @since 0.9.9
     * @param int|string $key
     *            The cache key to decrement.
     * @param int $offset
     *            Optional. The amount by which to decrement the item's value. Default: 1.
     * @param string $namespace
     *            Optional. The namespace the key is in. Default: 'default'.
     * @return false|int False on failure, the item's new value on success.
     */
    public function decrement($key, $offset = 1, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!$this->exists($key, $namespace)) {
            return false;
        }

        if (!is_numeric($this->cache[$namespace][$key])) {
            $this->cache[$namespace][$key] = 0;
        }

        $offset = (int) $offset;

        $this->cache[$namespace][$key] -= $offset;

        if ($this->cache[$namespace][$key] < 0) {
            $this->cache[$namespace][$key] = 0;
        }

        return $this->cache[$namespace][$key];
    }

    /**
     * Generates a unique cache key.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::uniqueKey()
     *
     * @since 0.9.9
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

        return $this->cache[$namespace][$key] = $namespace . ':' . $key;
    }

    /**
     * Serves as a utility method to determine whether a key exists in the cache.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::exists()
     *
     * @since 0.9.9
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
     * @since 0.9.9
     * @param string $dir
     *            Directory that should be removed.
     */
    protected function removeDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $dh = opendir($dir);
        if (!is_resource($dh)) {
            return;
        }

        $this->context->obj['file']->rmdir($dir);

        closedir($dh);
    }

    /**
     * Counts the number of cache hits
     * and writes it to a file.
     *
     * @since 0.9.9
     */
    protected function cacheHits()
    {
        $filename = $this->dir . 'cache_hits.txt';

        if (!is_readable($filename)) {
            $fp = fopen($filename, 'w');
            fwrite($fp, 0);
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
     * @since 0.9.9
     */
    protected function cacheMisses()
    {
        $filename = $this->dir . 'cache_misses.txt';

        if (!is_readable($filename)) {
            $fp = fopen($filename, 'w');
            fwrite($fp, 0);
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
     * @since 0.9.9
     * @param int|string $key
     *            Unqiue key of cache.
     * @param int|string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the cache file. Default: 0.
     */
    protected function removeStaleCache($key, $namespace = 'default', $ttl = 0)
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        if (!$this->exists($key, $namespace)) {
            $this->context->obj['app']->cookies->remove(md5($key));
        }

        $stale = glob($this->dir . $namespace . DS . '*');
        if (is_array($stale)) {
            foreach ($stale as $filename) {
                if ($this->context->obj['file']->fileExists($filename, false)) {
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
     * @since 0.9.9
     * @access protected
     * @param int|string $key
     *            Unqiue key of cache.
     * @param int|string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    private function keyToPath($key, $namespace)
    {
        $dir = $this->dir . urlencode($namespace);
        if (!$this->context->obj['file']->fileExists($dir, false)) {
            try {
                $this->context->obj['file']->mkdir($dir);
            } catch (IOException $e) {
                Cascade::getLogger('error')->error(
                    sprintf(
                        'IOSTATE[%s]: Forbidden: %s',
                        $e->getCode(),
                        $e->getMessage()
                    )
                );
                return;
            } catch (Exception $e) {
                Cascade::getLogger('error')->error(
                    sprintf(
                        'IOSTATE[%s]: Forbidden: %s',
                        $e->getCode(),
                        $e->getMessage()
                    )
                );
                return;
            }
        }
        return $this->dir . urlencode($namespace) . DS . urlencode(md5($key));
    }
}
