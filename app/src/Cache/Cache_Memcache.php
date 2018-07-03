<?php
namespace TriTan\Cache;

use TriTan\Exception\Exception;
use TriTan\Functions\Core;

/**
 * TriTan CMS \Memcache|\Memcached Class.
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @subpackage Cache
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Cache_Memcache extends \TriTan\Cache\Abstract_Cache
{

    /**
     * `\Memcache`|`\Memcached` object;
     *
     * @var object;
     */
    public $connection;

    /**
     *
     * @var bool
     */
    public $useMemcached;

    /**
     * Holds the cached objects.
     *
     * @since 0.9
     * @var array
     */
    protected $_cache = [];

    /**
     * Holds the application object
     *
     * @since 0.9
     * @var object
     */
    public $app;

    /**
     * Sets if cache is enabled or not.
     *
     * @since 0.9
     * @var bool
     */
    public $enable;

    public function __construct($useMemcached, \Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();

        $this->useMemcached = $useMemcached;

        $ext = $this->useMemcached ? 'memcached' : 'memcache';

        if ($ext == 'memcached' && !class_exists('memcached')) {
            throw new Exception(sprintf(Core\_t('Memcached requires PHP <strong>%s</strong> extension to be loaded.'), $ext), 'php_memcache_extension');
        }

        if ($ext == 'memcache' && !function_exists('memcache_connect')) {
            throw new Exception(sprintf(Core\_t('Memcached requires PHP <strong>%s</strong> extension to be loaded.'), $ext), 'php_memcache_extension');
        }

        if ($ext == 'memcache') {
            $this->connection = new \Memcache();
        } else {
            $this->connection = new \Memcached('ttcms');
        }

        /**
         * Filter sets whether caching is enabled or not.
         *
         * @since 0.9
         * @var bool
         */
        $this->enable = $this->app->hook->{'apply_filter'}('enable_caching', true);
    }

    /**
     * Creates the \Memcache|\Memcached item.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::create()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of the \Memcache|\Memcached item.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the \Memcache|\Memcached item. Default: 0 = will persist until cleared.
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
     *            Unique key of the \Memcache|\Memcached item.
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

        $unique_key = $this->uniqueKey($key, $namespace);

        return $this->connection->get($unique_key);
    }

    /**
     * Updates the \Memcache|\Memcached based on unique key.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::update()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of the \Memcache|\Memcached.
     * @param mixed $data
     *            Data that should be cached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $ttl
     *            Time to live sets the life of the \Memcache|\Memcached item. Default: 0 = will persist until cleared.
     */
    public function update($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (!$this->enable) {
            return false;
        }

        if (empty($namespace)) {
            $namespace = 'default';
        }

        $unique_key = $this->uniqueKey($key, $namespace);

        if ($this->exists($unique_key, $namespace)) {
            return false;
        }

        return $this->useMemcached ? $this->connection->replace($unique_key, $data, (int) $ttl) : $this->connection->replace($unique_key, $data, 0, (int) $ttl);
    }

    /**
     * Deletes \Memcache|\Memcached based on unique key.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::delete()
     *
     * @since 0.9
     * @param int|string $key
     *            Unique key of \Memcache|\Memcached.
     * @param string $namespace
     *            Optional. Where the cache contents are namespaced. Default: 'default'.
     */
    public function delete($key, $namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        $unique_key = $this->uniqueKey($key, $namespace);

        return $this->connection->delete($unique_key);
    }

    /**
     * Flushes \Memcache|\Memcached completely.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::flush()
     *
     * @since 0.9
     */
    public function flush()
    {
        return $this->connection->flush();
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
     */
    public function flushNamespace($namespace = 'default')
    {
        if (empty($namespace)) {
            $namespace = 'default';
        }

        return $this->connection->increment($namespace, 10);
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
     */
    public function set($key, $data, $namespace = 'default', $ttl = 0)
    {
        if (!$this->enable) {
            return false;
        }

        if (empty($namespace)) {
            $namespace = 'default';
        }

        $unique_key = $this->uniqueKey($key, $namespace);

        if ($this->exists($unique_key, $namespace)) {
            return false;
        }

        return $this->useMemcached ? $this->connection->set($unique_key, $data, (int) $ttl) : $this->connection->set($unique_key, $data, 0, (int) $ttl);
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

        if ($this->useMemcached == false) {
            $stats = $this->connection->getStats();
            echo "<p>";
            echo "<strong>" . Core\_t('Cache Hits:', 'tritan-cms') . "</strong> " . $stats['get_hits'] . "<br />";
            echo "<strong>" . Core\_t('Cache Misses:', 'tritan-cms') . "</strong> " . $stats['get_misses'] . "<br />";
            echo "<strong>" . Core\_t('Uptime:', 'tritan-cms') . "</strong> " . $stats['uptime'] . "<br />";
            echo "<strong>" . Core\_t('Memory Usage:', 'tritan-cms') . "</strong> " . $stats['bytes'] . "<br />";
            echo "<strong>" . Core\_t('Memory Available:', 'tritan-cms') . "</strong> " . $stats['limit_maxbytes'] . "<br />";
            echo "</p>";
        }

        if ($this->useMemcached == true) {
            $stats = $this->connection->getStats();
            $servers = $this->connection->getServerList();
            $key = $servers[0]['host'] . ':' . $servers[0]['port'];
            $stats = $stats[$key];
            echo "<p>";
            echo "<strong>" . Core\_t('Cache Hits:', 'tritan-cms') . "</strong> " . $stats['get_hits'] . "<br />";
            echo "<strong>" . Core\_t('Cache Misses:', 'tritan-cms') . "</strong> " . $stats['get_misses'] . "<br />";
            echo "<strong>" . Core\_t('Uptime:', 'tritan-cms') . "</strong> " . $stats['uptime'] . "<br />";
            echo "<strong>" . Core\_t('Memory Usage:', 'tritan-cms') . "</strong> " . $stats['bytes'] . "<br />";
            echo "<strong>" . Core\_t('Memory Available:', 'tritan-cms') . "</strong> " . $stats['limit_maxbytes'] . "<br />";
            echo "</p>";
        }
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
        $unique_key = $this->uniqueKey($key, $namespace);

        return $this->connection->increment($unique_key, (int) $offset);
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
        $unique_key = $this->uniqueKey($key, $namespace);

        return $this->connection->decrement($unique_key, (int) $offset);
    }

    /**
     * Add \Memcache|\Memcached servers.
     *
     * @since 0.9
     * @param array $servers
     *            An array of \Memcache|\Memcached servers.
     */
    public function addServer($servers)
    {
        if ($this->useMemcached == true) {
            $existingServers = [];

            foreach ($this->connection->getServerList() as $s) {
                $existingServers[$s['host'] . ':' . $s['port']] = true;
            }

            foreach ($servers as $server) {
                if (empty($existingServers) || !isset($existingServers[$server->host . ':' . $server->port])) {
                    $this->connection->addServer($server->host, $server->port, $server->weight);
                }
            }
        }

        if ($this->useMemcached == false) {
            foreach ($servers as $server) {
                $this->connection->addServer($server['host'], $server['port'], true, $server['weight']);
            }
        }
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
     * @return bool Whether the key exists in the cache for the given namespace.
     */
    protected function exists($key, $namespace)
    {
        return isset($this->_cache[$namespace]) && (isset($this->_cache[$namespace][$key]) || array_key_exists($key, $this->_cache[$namespace]));
    }

    /**
     * Unique namespace for cache item.
     *
     * {@inheritDoc}
     *
     * @see TriTan\Cache\Abstract_Cache::_namespace()
     *
     * @since 0.9
     * @param int|string $value
     *            The value to slice to get namespace.
     */
    protected function _namespace($value)
    {
        $namespace = explode(':', $value);
        return $namespace[0] . ':';
    }
}
