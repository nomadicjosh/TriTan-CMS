<?php
namespace TriTan\Interfaces;

interface SslInterface
{
    /**
     * Determines if SSL is used.
     *
     * @since 0.9.9
     * @return bool True if SSL, otherwise false.
     */
    public function isEnabled(): bool;
}
