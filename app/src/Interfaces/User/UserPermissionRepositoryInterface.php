<?php
namespace TriTan\Interfaces\User;

interface UserPermissionRepositoryInterface
{
    public function has(string $permission): bool;
}
