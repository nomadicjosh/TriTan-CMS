<?php
namespace TriTan\Interfaces;

interface HtmlPurifierInterface
{
    /**
     * Escaping for rich text.
     *
     * @since 0.9.9
     * @param string $string
     * @return string Escaped rich text.
     */
    public function purify($string, $is_image = false);
}
