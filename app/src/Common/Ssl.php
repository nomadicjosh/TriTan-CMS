<?php
namespace TriTan\Common;

use TriTan\Interfaces\SslInterface;

class Ssl implements SslInterface
{
    /**
     * Determines if SSL is used.
     *
     * @since 0.9.9
     * @return bool True if SSL, otherwise false.
     */
    public function isEnabled(): bool
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }
}
