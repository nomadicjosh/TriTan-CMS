<?php
namespace TriTan\Common;

use TriTan\Interfaces\EscapeInterface;

class Escape implements EscapeInterface
{
    /**
     * Convert special characters to HTML entities
     *
     * @since 0.9.9
     * @param string $string        The string being converted.
     * @param int $flags            A bitmask of one or more flags.
     * @param string $encoding      An optional argument defining the encoding used when converting characters.
     * @param bool $double_encoding When double_encode is turned off PHP will not encode existing html entities,
     *                              the default is to convert everything.
     * @return string
     */
    private function htmlSpecialChars(
        string $string,
        int $flags = ENT_QUOTES | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double_encoding = true
    ) {
        if (0 === strlen($string)) {
            return '';
        }

        if (in_array($encoding, ['utf8', 'utf-8', 'UTF8', 'UTF-8'])) {
            $encoding = 'UTF-8';
        }

        return htmlspecialchars($string, $flags, $encoding, $double_encoding);
    }

    /**
     * Escaping for HTML blocks.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped HTML block.
     */
    public function html($string)
    {
        $utf8_string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $safe_string = $this->htmlSpecialChars($utf8_string, ENT_QUOTES);
        return $safe_string;
    }

    /**
     * Escaping for textarea.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped string.
     */
    public function textarea($string)
    {
        $utf8_string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $safe_string = $this->htmlSpecialChars($utf8_string, ENT_QUOTES);
        return $safe_string;
    }

    /**
     * Escaping for url.
     *
     * @since 0.9.9
     * @param string $url The url to be escaped.
     * @param bool $encode  Whether url params should be encoded.
     * @return string The escaped $url after the `esc_url` filter is applied.
     */
    public function url(string $url, array $scheme = ['http', 'https'], bool $encode = false)
    {
        $raw_url = $url;

        if ('' == $url) {
            return $url;
        }

        /**
         * First step of defense is to strip all tags.
         */
        $esc_url = strip_tags($url);

        /**
         * Run url through a filter, and then validate it.
         */
        $_url = filter_var(urldecode($esc_url), FILTER_SANITIZE_SPECIAL_CHARS);
        if (!filter_var($_url, FILTER_VALIDATE_URL)) {
            return false;
        }

        /**
         * Break down the url into it's parts and then rebuild it.
         */
        $uri = parse_url($_url);

        if (!is_array($uri)) {
            return '#';
        }

        if (!in_array($uri['scheme'], $scheme, true)) {
            return '#';
        }

        $query = isset($uri['query']) ? $uri['query'] : '';
        $result = '';

        if ($uri['scheme']) {
            $result .= $uri['scheme'] . ':';
        }
        if ($uri['host']) {
            $result .= '//' . $uri['host'];
        }
        if ($uri['port']) {
            $result .= ':' . $uri['port'];
        }
        if ($uri['path']) {
            $result .= $uri['path'];
        }

        if ($query) {
            $_query = '?' . $query . ($uri['fragment'] ? $uri['fragment'] : '');
        }

        $clean_url = $result . $_query;

        if ($encode) {
            $clean_url = $result . $_query . urlencode(($uri['fragment'] ? $uri['fragment'] : ''));
        }
        
        return $clean_url;
    }

    /**
     * Escaping for HTML attributes.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped HTML attribute.
     */
    public function attr($string)
    {
        $utf8_string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $safe_string = $this->htmlSpecialChars($utf8_string, ENT_QUOTES);
        return $safe_string;
    }

    /**
     * Escaping for inline javascript.
     *
     * Example usage:
     *
     *      $esc_js = json_encode("Joshua's \"code\"");
     *      $attribute = $this->js("alert($esc_js);");
     *      echo '<input type="button" value="push" onclick="'.$attribute.'" />';
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped inline javascript.
     */
    public function js($string)
    {
        $safe_string = $this->attr($string);
        return $safe_string;
    }
}
