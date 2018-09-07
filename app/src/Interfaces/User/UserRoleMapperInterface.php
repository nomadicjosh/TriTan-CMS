<?php
namespace TriTan\Interfaces\User;

interface UserRoleMapperInterface
{
    public function has(string $role): bool;
}
