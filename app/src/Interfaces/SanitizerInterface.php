<?php
namespace TriTan\Interfaces;

interface SanitizerInterface
{
    /**
     * Sanitizes an array of items.
     *
     * @since 0.9.9
     * @param array $items    Items to sanitize.
     * @param string $context The context for which the string is being sanitized.
     */
    public function items(array $items, $context = 'save');

    /**
     * Sanitizes an item according to type.
     *
     * @since 0.9.9
     * @param mixed $item     Item to sanitize.
     * @param string $type    Item type (i.e. string, float, int, etc.).
     * @param string $context The context for which the string is being sanitized.
     */
    public function item($item, $type = 'string', $context = 'save');
}
