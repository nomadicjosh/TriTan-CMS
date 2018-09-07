<?php
namespace TriTan\Interfaces;

interface UtilsInterface
{
    /**
     * This should be used to remove slashes from data passed to core API that
     * expects data to be unslashed.
     *
     * @since 0.9.9
     * @param string|array String or array of strings to unslash.
     * @return string|array Unslashed value.
     */
    public function unslash($value);
    
    /**
     * Convert a value to non-negative integer.
     *
     * @since 0.9.9
     * @param mixed $maybeint   Data you wish to have converted to a non-negative integer.
     * @return int A non-negative integer.
     */
    public function absint($maybeint): int;
    
    /**
     * Removes all whitespace.
     *
     * @since 0.9.9
     * @param string $str
     * @return mixed
     */
    public function trim($str);
    
    /**
     * Renders any unwarranted special characters to HTML entities.
     *
     * @since 0.9.9
     * @param string $str
     * @return mixed
     */
    public function escape($str);
}
