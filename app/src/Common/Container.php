<?php
namespace TriTan;

use Psr\Container\ContainerInterface;

/**
 * Container API: Implements a simple PSR-11 container.
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Container implements ContainerInterface
{
    protected $container = [];

    /**
     * @var self The stored singleton instance
     */
    protected static $instance;

    /**
     * Reset the Container instance.
     */
    public static function resetInstance()
    {
        if (self::$instance) {
            self::$instance = null;
        }
    }

    /**
     * Creates the original or retrieves the stored singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = (new \ReflectionClass(get_called_class()))
                ->newInstanceWithoutConstructor();
        }

        return static::$instance;
    }
    
    /**
     * Retrieves a sub-key from the container.
     * 
     * @since 1.0
     * @param string $object  name of an object to retrieve a key from.
     * @param bool   $key     (optional) key to retrieve from the object.
     * @param null   $default (optional) default value for missing objects or keys.
     */

    public function prop($object, $key = false, $default = null)
    {
        if ($obj = $this->get($object)) {
            return ($key != false ? $obj->{$key} : $obj);
        }
        return $default;
    }
    /**
     * Retrieves a container parameter.
     *
     * @param string $name      A container parameter name
     * @param mixed $default    A default container parameter value
     * @return mixed A container parameter value, if the container parameter exists, otherwise null
     */
    public function get($name, $default = null)
    {
        return $this->container[$name] ?? $default;
    }

    /**
     * Indicates whether or not a container parameter exists.
     *
     * @param string $name  A container parameter name
     * @return bool true, if the container parameter exists, otherwise false
     */
    public function has($name)
    {
        return array_key_exists($name, $this->container);
    }

    /**
     * Sets a container parameter.
     *
     * If a container parameter with the name already exists the value will be overridden.
     *
     * @param string $name  A container parameter name
     * @param mixed $value  A container parameter value
     */
    public function set($name, $value)
    {
        $this->container[$name] = $value;
    }

    /**
     * Sets an array of container parameters.
     *
     * If an existing container parameter name matches any of the keys in the supplied
     * array, the associated value will be overridden.
     *
     * @param array $parameters An associative array of container parameters and their associated values
     */
    public function add($parameters = [])
    {
        $this->container = array_merge($this->container, $parameters);
    }

    /**
     * Retrieves all configuration parameters.
     *
     * @return array An associative array of configuration parameters.
     */
    public function getAll()
    {
        return $this->container;
    }

    /**
     * Clears all current container parameters.
     */
    public function clear()
    {
        $this->container = null;
        $this->container = [];
    }
}
