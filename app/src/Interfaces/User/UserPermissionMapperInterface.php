<?php
namespace TriTan\Interfaces\User;

interface UserPermissionMapperInterface
{
    public function has(string $permission): bool;
}
