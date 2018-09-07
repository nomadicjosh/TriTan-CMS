<?php
namespace TriTan\Common\User;

use TriTan\Container as c;
use TriTan\Interfaces\User\UserRoleMapperInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Interfaces\ContextInterface;

class UserRoleMapper implements UserRoleMapperInterface
{
    private $db;
    
    private $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }
    
    public function has(string $role): bool
    {
        $check = $this->db->table('role')
                ->where('role_key', $role)
                ->first();
        
        $exist = $this->db->table('usermeta')
                ->where('meta_key', c::getInstance()->get('tbl_prefix') . 'role')
                ->where('user_id', get_current_user_id())
                ->where('meta_value', $check['role_id'])
                ->count();

        return $exist > 0;
    }
}
