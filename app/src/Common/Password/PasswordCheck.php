<?php
namespace TriTan\Common;

use TriTan\Interfaces\Password\PasswordCheckInterface;
use TriTan\Interfaces\Password\PasswordSetMapperInterface;
use TriTan\Interfaces\Password\PasswordHashInterface;
use TriTan\Interfaces\Hooks\ActionFilterHookInterface;

class PasswordCheck implements PasswordCheckInterface
{
    public $mapper;
    
    public $hasher;
    
    public $hook;
    
    public function __construct(PasswordSetMapperInterface $mapper, PasswordHashInterface $hasher, ActionFilterHookInterface $hook)
    {
        $this->mapper = $mapper;
        $this->hasher = $hasher;
        $this->hook = $hook;
    }
    
    /**
     * Checks a plain text password against a hashed password.
     *
     * Uses `check_password` filter.
     *
     * @since 0.9.9
     * @param string $password Plain test password.
     * @param string $hash Hashed password in the database to check against.
     * @param int $user_id User ID.
     * @return bool True if the password and hash match, or false otherwise.
     */
    public function check(string $password, string $hash, int $user_id = 0) : bool
    {
        // If the hash is still md5...
        if (strlen($hash) <= 32) {
            $check = ($hash == md5($password));
            if ($check && $user_id) {
                // Rehash using new hash.
                $this->mapper->{'set'}($password, $user_id);
                $hash = $this->hasher->{'hash'}($password);
            }
            /**
             * Filters the password check.
             *
             * @since 0.9.9
             * @param bool $check      Whether the passwords match.
             * @param string $password The plaintext password.
             * @param string $hash     The hashed password.
             * @param int $user_id     The user id.
             */
            return $this->hook->{'applyFilter'}('check_password', $check, $password, $hash, $user_id);
        }

        $check = password_verify($password, $hash);

        /**
             * Filters the password check.
             *
             * @since 0.9.9
             * @param bool $check      Whether the passwords match.
             * @param string $password The plaintext password.
             * @param string $hash     The hashed password.
             * @param int $user_id     The user id.
             */
        return $this->hook->{'applyFilter'}('check_password', $check, $password, $hash, $user_id);
    }
}
