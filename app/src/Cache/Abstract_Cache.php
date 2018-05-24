<?php namespace TriTan\Cache;

if (! defined('BASE_PATH'))
    exit('No direct script access allowed');

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

    abstract function read($key, $namespace);

    abstract function create($key, $data, $namespace, $ttl);

    abstract function delete($key, $namespace);
    
    abstract function flush();
    
    abstract function flushNamespace($namespace);
    
    abstract function set($key, $data, $namespace, $ttl);
    
    abstract function getStats();
    
    abstract function inc($key, $offset, $namespace);
    
    abstract function dec($key, $offset, $namespace);

    abstract protected function uniqueKey($key, $namespace);

    abstract protected function _exists($key, $namespace);
}
