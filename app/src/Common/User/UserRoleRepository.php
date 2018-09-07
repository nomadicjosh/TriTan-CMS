<?php
namespace TriTan\Common\User;

use TriTan\Interfaces\User\UserRoleRepositoryInterface;
use TriTan\Interfaces\User\UserRoleMapperInterface;

class UserRoleRepository implements UserRoleRepositoryInterface
{
    private $mapper;

    public function __construct(UserRoleMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
    
    public function has(string $role): bool
    {
        return $this->mapper->{'has'}($role);
    }
}
