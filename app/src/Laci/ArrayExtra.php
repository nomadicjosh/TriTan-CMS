<?php

namespace TriTan\Laci;

use ArrayAccess;

class ArrayExtra implements ArrayAccess
{

    protected $items = [];

    /**
     * Constructor
     *
     * @param   array $items
     * @return  void
     */
    public function __construct($items)
    {
        $this->items = $this->getArrayValue($items, 'Items must be array or ArrayExtra object');
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Arr.php#L81
     *
     * @param  array  $array
     * @param  string|array  $keys
     * @return bool
     */
    public static function arrayHas(array $array, $key)
    {
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Get an item from an array using "dot" notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Arr.php#L246
     *
     * @param  array  $array
     * @param  string  $key
     * @return mixed
     */
    public static function arrayGet(array $array, $key)
    {
        if (is_null($key)) {
            return $array;
        }
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }
        return $array;
    }

    /**
     * Set an item on an array or object using dot notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/helpers.php#L437
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return mixed
     */
    public static function arraySet(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);
        if (($segment = array_shift($segments)) === '*') {
            if (!is_array($target)) {
                $target = [];
            }
            if ($segments) {
                foreach ($target as &$inner) {
                    static::arraySet($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (is_array($target)) {
            if ($segments) {
                if (!array_key_exists($segment, $target)) {
                    $target[$segment] = [];
                }
                static::arraySet($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !array_key_exists($segment, $target)) {
                $target[$segment] = $value;
            }
        } else {
            $target = [];
            if ($segments) {
                static::arraySet($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }
        return $target;
    }

    /**
     * Remove item in array
     *
     * @param array $array
     * @param string $key
     */
    public static function arrayRemove(array &$array, $key)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) OR ! is_array($array[$key])) {
                $array[$key] = array();
            }
            $array = & $array[$key];
        }
        unset($array[array_shift($keys)]);
    }

    public function merge($value)
    {
        $array = $this->getArrayValue($value, "Value is not mergeable.");
        foreach ($value as $key => $val) {
            $this->items = static::arraySet($this->items, $key, $val, true);
        }
    }

    protected function getArrayValue($value, $message)
    {
        if (!is_array($value) AND false == $value instanceof ArrayExtra) {
            throw new \InvalidArgumentException($message);
        }
        return is_array($value) ? $value : $value->toArray();
    }

    public function toArray()
    {
        return $this->items;
    }

    public function offsetSet($key, $value)
    {
        $this->items = static::arraySet($this->items, $key, $value, true);
    }

    public function offsetExists($key)
    {
        return static::arrayHas($this->items, $key);
    }

    public function offsetUnset($key)
    {
        static::arrayRemove($this->items, $key);
    }

    public function offsetGet($key)
    {
        return static::arrayGet($this->items, $key);
    }

}
