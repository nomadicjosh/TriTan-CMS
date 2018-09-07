<?php
namespace TriTan\Cache;

use TriTan\Interfaces\Cache\ObjectCacheInterface;
use TriTan\Interfaces\Cache\CacheInterface;

/**
 * TriTan CMS Object Cache Class
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @subpackage Cache
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class ObjectCache implements ObjectCacheInterface
{
    /**
     * Cache object.
     *
     * @var object
     */
    public $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }


    /**
     * Adds data to the cache, if the cache key doesn't already exist.
     *
     * @since 0.9.9
     * @param int|string $key The cache key to use for retrieval later.
     * @param mixed $data The data to add to the cache.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @param int $expire Optional. When the cache data should expire, in seconds.
     *                    Default: 3600 seconds = 1 hour / 60 minutes.
     * @return bool False if cache key already exists, true on success.
     */
    public function create($key, $data, $namespace = 'default', $expire = 3600)
    {
        return $this->cache->{'create'}($key, $data, $namespace, (int) $expire);
    }

    /**
     * Retrieves the cache contents from the cache by key and group.
     *
     * @since 0.9.9
     * @param int|string $key The key under which the cache contents are stored.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @return bool|mixed False on failure to retrieve contents or the cache
     *                    contents on success.
     */
    public function read($key, $namespace = 'default')
    {
        return $this->cache->{'read'}($key, $namespace);
    }

    /**
     * Replaces the contents of the cache with new data.
     *
     * @since 0.9.9
     * @param int|string $key The key for the cache data that should be replaced.
     * @param mixed $data The new data to store in the cache.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @param int $expire Optional. When to expire the cache contents, in seconds.
     *                    Default: 3600 seconds = 1 hour / 60 minutes.
     * @return bool False if original value does not exist, true if contents were replaced
     */
    public function update($key, $data, $namespace = 'default', $expire = 3600)
    {
        return $this->cache->{'update'}($key, $data, $namespace, (int) $expire);
    }

    /**
     * Removes the cache contents matching key and group.
     *
     * @since 0.9.9
     * @param int|string $key What the contents in the cache are called.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @return bool True on successful removal, false on failure.
     */
    public function delete($key, $namespace = 'default')
    {
        return $this->cache->{'delete'}($key, $namespace);
    }

    /**
     * Removes all cache items.
     *
     * @since 0.9.9
     * @return bool False on failure, true on success
     */
    public function flush()
    {
        return $this->cache->{'flush'}();
    }

    /**
     * Removes all cache items from a particular namespace.
     *
     * @since 0.9.9
     * @param string $value The namespace to delete from.
     * @return bool False on failure, true on success
     */
    public function flushNamespace($value)
    {
        return $this->cache->{'flushNamespace'}($value);
    }

    /**
     * Sets the data contents into the cache.
     *
     * @since 0.9.9
     * @param int|string $key Unique key of the cache file.
     * @param mixed $data Data that should be cached.
     * @param string $namespace Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $expire Optional. When to expire the cache contents, in seconds.
     *                    Default: 3600 seconds = 1 hour / 60 minutes.
     * @return bool Returns true if the cache was set and false otherwise.
     */
    public function set($key, $data, $namespace = '', $expire = 3600)
    {
        return $this->cache->{'set'}($key, $data, $namespace, (int) $expire);
    }

    /**
     * Returns the stats of the cache.
     *
     * Gives the cache hits, cache misses and cache uptime.
     *
     * @since 0.9.9
     */
    public function getStats()
    {
        return $this->cache->{'getStats'}();
    }

    /**
     * Increments numeric cache item's value.
     *
     * @since 0.9.9
     * @param int|string $key The cache key to increment
     * @param int $offset Optional. The amount by which to increment the item's value. Default: 1.
     * @param string $namespace Optional. The namespace the key is in.
     * @return false|int False on failure, the item's new value on success.
     */
    public function increment($key, $offset = 1, $namespace = '')
    {
        return $this->cache->{'increment'}($key, (int) $offset, $namespace);
    }

    /**
     * Decrements numeric cache item's value.
     *
     * @since 0.9.9
     * @param int|string $key The cache key to decrement.
     * @param int $offset Optional. The amount by which to decrement the item's value. Default: 1.
     * @param string $namespace Optional. The namespace the key is in.
     * @return false|int False on failure, the item's new value on success.
     */
    public function decrement($key, $offset = 1, $namespace = '')
    {
        return $this->cache->{'decrement'}($key, (int) $offset, $namespace);
    }
}
