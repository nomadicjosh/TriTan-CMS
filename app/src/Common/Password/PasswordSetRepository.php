<?php
namespace TriTan\Common\Password;

use TriTan\Interfaces\Password\PasswordSetRepositoryInterface;
use TriTan\Interfaces\Password\PasswordSetMapperInterface;

class PasswordSetRepository implements PasswordSetRepositoryInterface
{
    public $mapper;

    public function __construct(PasswordSetMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
    
    /**
     * Used by PasswordCheck::check() in order to rehash
     * an old password that was hashed using MD5 function.
     *
     * @since 0.9.9
     * @param string $password User password.
     * @param int $user_id User ID.
     * @return mixed
     */
    public function set(string $password, int $user_id)
    {
        return $this->mapper->{'set'}($password, $user_id);
    }
}
