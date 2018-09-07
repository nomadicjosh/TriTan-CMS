<?php
namespace TriTan\Common;

use TriTan\Interfaces\Password\PasswordSetMapperInterface;
use TriTan\Interfaces\Password\PasswordHashInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Exception\Exception;
use Cascade\Cascade;

class PasswordSetMapper implements PasswordSetMapperInterface
{
    public $db;

    public $hasher;

    public function __construct(DatabaseInterface $db, PasswordHashInterface $hasher)
    {
        $this->db = $db;
        $this->hasher = $hasher;
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
        $hash = $this->hasher->{'hash'}($password);

        $user = $this->db->table('user');
        $user->begin();
        try {
            $user->where('user_id', $user_id)
                 ->update([
                    'user_pass' => $hash
                 ]);
            $user->commit();
        } catch (Exception $ex) {
            $user->rollback();
            Cascade::getLogger('error')->error($ex->getMessage());
            return $ex->getMessage();
        }
    }
}
