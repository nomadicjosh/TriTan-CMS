<?php
namespace TriTan\Common\User;

use TriTan\Container as c;
use TriTan\Interfaces\User\UserPermissionMapperInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Interfaces\ContextInterface;

class UserPermissionMapper implements UserPermissionMapperInterface
{
    private $db;
    
    private $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }
    
    public function has(string $permission): bool
    {
        $user_role = $this->db->table('usermeta')
                ->where('meta_key', c::getInstance()->get('tbl_prefix') . 'role')
                ->where('user_id', get_current_user_id())
                ->first();

        $perms = $this->db->table('role')
                ->where('role_id', (int) $this->context->obj['escape']->{'html'}($user_role['meta_value']))
                ->first();
                
        $clean_permission = $this->context->obj['html']->{'purify'}($perms['role_permission']);
        $perm = $this->context->obj['serializer']->{'unserialize'}($clean_permission);

        if (in_array($permission, $perm)) {
            return true;
        }
        return false;
    }
}
