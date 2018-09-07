<?php
namespace TriTan\Interfaces;

interface EscapeInterface
{
    /**
     * Escaping for HTML blocks.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped HTML block.
     */
    public function html($string);

    /**
     * Escaping for textarea.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped string.
     */
    public function textarea($string);

    /**
     * Escaping for url.
     *
     * @since 0.9.9
     * @param string $url
     * @return string Escaped url.
     */
    public function url(string $url, array $scheme = [], bool $encode = false);

    /**
     * Escaping for HTML attributes.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped HTML attribute.
     */
    public function attr($string);

    /**
     * Escaping for inline javascript.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped inline javascript.
     */
    public function js($string);
}
