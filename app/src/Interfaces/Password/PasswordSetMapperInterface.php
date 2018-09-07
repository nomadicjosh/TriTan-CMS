<?php
namespace TriTan\Interfaces\Password;

interface PasswordSetMapperInterface
{
    /**
     * Used by PasswordCheck::check() in order to rehash
     * an old password that was hashed using MD5 function.
     *
     * @since 0.9.9
     * @param string $password User password.
     * @param int $user_id User ID.
     * @return mixed
     */
    public function set(string $password, int $user_id);
}
