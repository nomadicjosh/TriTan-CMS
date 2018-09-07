<?php
namespace TriTan\Common;

use TriTan\Interfaces\Hooks\ActionFilterHookInterface;
use TriTan\Interfaces\UtilsInterface;

class Utils implements UtilsInterface
{
    public $hook;
    
    public function __construct(ActionFilterHookInterface $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Navigates through an array, object, or scalar, and removes slashes from the values.
     *
     * @since 0.9.9
     * @param mixed $value  The value to be stripped.
     * @return mixed Stripped value.
     */
    public function stripslashesDeep($value)
    {
        $_value = is_array($value) ?
            array_map([$this, 'stripslashesDeep'], $value) :
            stripslashes($value);

        return $_value;
    }

    /**
     * This should be used to remove slashes from data passed to core API that
     * expects data to be unslashed.
     *
     * @since 0.9.9
     * @param string|array String or array of strings to unslash.
     * @return string|array Unslashed value.
     */
    public function unslash($value)
    {
        return $this->stripslashesDeep($value);
    }

    /**
     * Convert a value to non-negative integer.
     *
     * @since 0.9.9
     * @param mixed $maybeint   Data you wish to have converted to a non-negative integer.
     * @return int A non-negative integer.
     */
    public function absint($maybeint): int
    {
        return abs(intval($maybeint));
    }

    /**
     * Checks if a variable is null. If not null, check if integer or string.
     *
     * @since 0.9.9
     * @param string|int $var   Variable to check.
     * @return string|int|null Returns null if empty otherwise a string or an integer.
     */
    public function ifNull($var)
    {
        $_var = ctype_digit($var) ? (int) $var : (string) $var;
        return $var === '' ? null : $_var;
    }

    /**
     * Turns multi-dimensional array into a regular array.
     *
     * @since 0.9.9
     * @param array $array The array to convert.
     * @return array
     */
    public function flattenArray(array $array): array
    {
        $flat_array = [];
        foreach ($array as $element) {
            if (is_array($element)) {
                $flat_array = array_merge($flat_array, $this->flattenArray($element));
            } else {
                $flat_array[] = $element;
            }
        }
        return $flat_array;
    }

    /**
     * Removes all whitespace.
     *
     * @since 0.9.9
     * @param string $str
     * @return mixed
     */
    public function trim($str)
    {
        return preg_replace('/\s/', '', $str);
    }

    /**
     * Renders any unwarranted special characters to HTML entities.
     *
     * @since 0.9.9
     * @param string $str
     * @return mixed
     */
    public function escape($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Parses a string into variables to be stored in an array.
     *
     * Uses {@link http://www.php.net/parse_str parse_str()}
     *
     * @since 0.9.9
     * @param string $string The string to be parsed.
     * @param array $array Variables will be stored in this array.
     */
    public function parseStr($string, $array)
    {
        parse_str($string, $array);
        /**
         * Filter the array of variables derived from a parsed string.
         *
         * @since 0.9.9
         * @param array $array
         *            The array populated with variables.
         */
        $array = $this->hook->{'applyFilter'}('ttcms_parse_str', $array);
    }

    /**
     * Merge user defined arguments into defaults array.
     *
     * This method is used throughout TriTan CMS to allow for both string or array
     * to be merged into another array.
     *
     * @since 0.9
     * @param string|array $args Value to merge with $defaults
     * @param array $defaults Optional. Array that serves as the defaults. Default empty.
     * @return array Merged user defined values with defaults.
     */
    public function parseArgs($args, $defaults = '')
    {
        if (is_object($args)) {
            $r = get_object_vars($args);
        } elseif (is_array($args)) {
            $r = $args;
        } else {
            $this->parseStr($args, $r);
        }

        if (is_array($defaults)) {
            return array_merge($defaults, $r);
        }

        return $r;
    }

    /**
     * Properly strip all HTML tags including script and style (default).
     *
     * This differs from PHP's native strip_tags() function because this function removes the contents of
     * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
     * will return `'something'`. By default, $this->stripTags() will return `''`.
     *
     * Example Usage:
     *
     *      $string = '<b>sample</b> text with <div>tags</div>';
     *
     *      $this->stripTags($string); //returns 'text with'
     *      $this->stripTags($string, false, '<b>'); //returns '<b>sample</b> text with'
     *      $this->stripTags($string, false, '<b>', true); //returns 'text with <div>tags</div>'
     *
     * @since 0.9.9
     * @param string $string        String containing HTML tags
     * @param bool   $remove_breaks Optional. Whether to remove left over line breaks and white space chars
     * @param string $tags          Tags that should be removed.
     * @param bool   $invert        Instead of removing tags, this option checks for which tags to not remove.
     *                              Default: false;
     * @return string The processed string.
     */
    public function stripTags($string, $remove_breaks = false, $tags = '', $invert = false)
    {
        $raw_string = $string;

        $_string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', $this->trim($tags), $tags);
        $_tags = array_unique($tags[1]);

        if (is_array($_tags) && count($_tags) > 0) {
            if ($invert == false) {
                return preg_replace('@<(?!(?:' . implode('|', $_tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $_string);
            } else {
                return preg_replace('@<(' . implode('|', $_tags) . ')\b.*?>.*?</\1>@si', '', $_string);
            }
        } elseif ($invert == false) {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $_string);
        }

        if ($remove_breaks) {
            $_string = preg_replace('/[\r\n\t ]+/', ' ', $_string);
        }

        return $this->hook->{'applyFilter'}('strip_tags', $_string, $raw_string, $remove_breaks, $tags, $invert);
    }

    /**
     * Takes an array and turns it into an object.
     *
     * @since 0.9.9
     * @param array $array Array of data.
     */
    public function toObject(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->toObject($value);
            }
        }
        return (object) $array;
    }
}
