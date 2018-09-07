<?php
namespace TriTan\Common\User;

use TriTan\Interfaces\User\UserPermissionRepositoryInterface;
use TriTan\Interfaces\User\UserPermissionMapperInterface;

class UserPermissionRepository implements UserPermissionRepositoryInterface
{
    private $mapper;

    public function __construct(UserPermissionMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    public function has(string $permission): bool
    {
        return $this->mapper->{'has'}($permission);
    }
}
