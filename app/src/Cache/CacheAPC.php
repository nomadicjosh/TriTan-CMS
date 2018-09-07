<?php
namespace TriTan\Cache;

use TriTan\Exception\Exception;

/**
 * TriTan CMS APC Cache Class.
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @subpackage Cache
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class CacheAPC extends \TriTan\Cache\AbstractCache implements \TriTan\Interfaces\CacheInterface
{

    /**
     * Holds the cached objects.
     *
     * @since 0.9.9
     * @var array
     */
    protected $cache = [];

    public function __construct()
    {
        if (!extension_loaded('apc') && !ini_get('apc.enabled') || !function_exists('apc_fetch')) {
            throw new Exception('APC requires PHP APC extension to be installed and loaded.', 'php_apc_extension');
        }
    }

    /**
     * Creates the APC cache item.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::create()
     *
     * @since 0.9.9
     * @param int|string $key
     *            Unique key of the APC cached item.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the APC cached item. Default: 0 = will persist until cleared.
     */
    public function create($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $unique_key = $this->uniqueKey($key, $namespace);

        if ($this->exists($unique_key, $namespace)) {
            return false;
        }

        return set($key, $data, $namespace, (int) $ttl);
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
     *            Unique key of the APC cached item.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    public function read($key, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $unique_key = $this->uniqueKey($key, $namespace);

        return apc_fetch($unique_key);
    }

    /**
     * Updates the APC cache based on unique key.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::update()
     *
     * @since 0.9.9
     * @param int|string $key
     *            Unique key of the APC cache.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the APC cached item. Default: 0 = will persist until cleared.
     */
    public function update($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $unique_key = $this->uniqueKey($key, $namespace);

        return apc_store($unique_key, $data, (int) $ttl);
    }

    /**
     * Deletes cache based on unique key.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::delete()
     *
     * @since 0.9.9
     * @param int|string $key
     *            Unique key of APC cache.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    public function delete($key, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $unique_key = $this->uniqueKey($key, $namespace);

        return apc_delete($unique_key);
    }

    /**
     * Flushes the APC cache completely.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::flush()
     *
     * @since 0.9.9
     */
    public function flush()
    {
        apc_clear_cache();
        apc_clear_cache('user');
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
     */
    public function flushNamespace($namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        return $this->flush();
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
     */
    public function set($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        return apc_store($key, $data, (int) $ttl);
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
        $info = apc_cache_info('', true);
        $sma = apc_sma_info();

        if (PHP_VERSION_ID >= 50500) {
            $info['num_hits'] = isset($info['num_hits']) ? $info['num_hits'] : $info['nhits'];
            $info['num_misses'] = isset($info['num_misses']) ? $info['num_misses'] : $info['nmisses'];
            $info['start_time'] = isset($info['start_time']) ? $info['start_time'] : $info['stime'];
        }

        echo "<p>";
        echo "<strong>'Cache Hits:</strong> " . $info['num_hits'] . "<br />";
        echo "<strong>'Cache Misses:</strong> " . $info['num_misses'] . "<br />";
        echo "<strong>'Uptime:</strong> " . $info['start_time'] . "<br />";
        echo "<strong>'Memory Usage:</strong> " . $info['mem_size'] . "<br />";
        echo "<strong>'Memory Available:</strong> " . $sma['avail_mem'] . "<br />";
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
        $unique_key = $this->uniqueKey($key, $namespace);

        return apc_inc($unique_key, (int) $offset);
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
        $unique_key = $this->uniqueKey($key, $namespace);

        return apc_dec($unique_key, (int) $offset);
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
     * @see TriTanCache\AbstractCache::exists()
     *
     * @since 0.9.9
     * @access protected
     * @param int|string $key
     *            Cache key to check for existence.
     * @param string $namespace
     *            Cache namespace for the key existence check.
     * @return bool Whether the key exists in the cache for the given namespace.
     */
    protected function exists($key, $namespace)
    {
        return isset($this->cache[$namespace]) && (isset($this->cache[$namespace][$key]) || array_key_exists($key, $this->cache[$namespace]));
    }

    /**
     * Unique namespace for cache item.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\AbstractCache::namespace()
     *
     * @since 0.9.9
     * @param int|string $value
     *            The value to slice to get namespace.
     */
    protected function _namespace($value)
    {
        $namespace = explode(':', $value);
        return $namespace[0] . ':';
    }
}
