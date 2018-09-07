<?php
namespace TriTan\Interfaces\Password;

interface PasswordCheckInterface
{
    /**
     * Checks a plain text password against a hashed password.
     *
     * @since 0.9.9
     * @param string $password Plain test password.
     * @param string $hash Hashed password in the database to check against.
     * @param int $user_id User ID.
     * @return bool True if the password and hash match, or false otherwise.
     */
    public function check(string $password, string $hash, int $user_id = 0) : bool;
}