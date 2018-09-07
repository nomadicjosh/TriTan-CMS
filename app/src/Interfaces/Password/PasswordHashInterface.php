<?php
namespace TriTan\Interfaces\Password;

interface PasswordHashInterface
{
    /**
     * Hashes a plain text password.
     *
     * @since 0.9.9
     * @param string $password Plain text password
     * @return mixed
     */
    public function hash(string $password);
}
