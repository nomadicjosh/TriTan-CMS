<?php
namespace TriTan\Interfaces;

interface SanitizeInterface
{
    /**
     * Sanitizes a string key.
     *
     * @since 0.9.9
     * @param string $key String key
     * @return string Sanitized key
     */
    public function key(string $key);
    
    /**
     * Sanitizes a string, or returns a fallback string.
     *
     * Specifically, HTML and PHP tags are stripped. Further actions can be added
     * via the plugin API. If $string is empty and $fallback_string is set, the latter
     * will be used.
     *
     * @since 0.9.9
     *
     * @param string $string          The string to be sanitized.
     * @param string $fallback_string Optional. A string to use if $string is empty.
     * @param string $context        Optional. The operation for which the string is sanitized
     * @return string The sanitized string.
     */
    public function string($string, $fallback_string = '', $context = 'save');

    /**
     * Sanitizes a username, stripping out unsafe characters.
     *
     * @since 0.9.9
     * @param string    $username The username to be sanitized.
     * @param bool      $strict If set, limits $username to specific characters. Default false.
     * @return string Sanitized username.
     */
    public function username($username, $strict = false);
}
