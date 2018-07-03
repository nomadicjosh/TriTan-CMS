<?php namespace TriTan\Cache;

/**
 * TriTan CMS Abstract Cache Class.
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @subpackage Cache
 * @author Joshua Parker <joshmac3@icloud.com>
 */
abstract class Abstract_Cache
{

    abstract public function read($key, $namespace);

    abstract public function create($key, $data, $namespace, $ttl);

    abstract public function delete($key, $namespace);

    abstract public function flush();

    abstract public function flushNamespace($namespace);

    abstract public function set($key, $data, $namespace, $ttl);

    abstract public function getStats();

    abstract public function inc($key, $offset, $namespace);

    abstract public function dec($key, $offset, $namespace);

    abstract protected function uniqueKey($key, $namespace);

    abstract protected function exists($key, $namespace);
}
