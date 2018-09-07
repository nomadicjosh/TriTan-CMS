<?php
namespace TriTan\Common;

use TriTan\Interfaces\Password\PasswordHashInterface;
use TriTan\Interfaces\Hooks\ActionFilterHookInterface;

class PasswordHash implements PasswordHashInterface
{
    public $hook;
    
    public function __construct(ActionFilterHookInterface $hook)
    {
        $this->hook = $hook;
    }
    
    /**
     * Hashes a plain text password.
     *
     * @since 0.9.9
     * @param string $password Plain text password
     * @return mixed
     */
    public function hash(string $password)
    {
        $options = [
            'cost' => 6,
        ];
        
        /**
         * Filters the password_hash() hashing algorithm.
         * 
         * @since 0.9.9
         * @param string $algo Hashing algorithm. Default: PASSWORD_BCRYPT
         */
        $algo = $this->hook->{'applyFilter'}('password_hash_algo', PASSWORD_BCRYPT);
        
        /**
         * Filters the password_hash() options parameter.
         * 
         * @since 0.9.9
         * @param array $options Options to pass to password_hash() function.
         */
        $options = $this->hook->{'applyFilter'}('password_hash_options', (array) $options);

        return password_hash($password, $algo, $options);
    }
}
