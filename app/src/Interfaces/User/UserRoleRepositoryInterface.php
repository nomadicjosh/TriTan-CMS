<?php
namespace TriTan\Interfaces\User;

interface UserRoleRepositoryInterface
{
    public function has(string $role): bool;
}
