<?php
namespace TriTan\Interfaces\Acl;

use TriTan\Common\Acl\Role;

interface RoleRepositoryInterface
{
    public function findById(int $id);
    
    public function findAll($format);

    public function findNameById(int $id): string;
    
    public function findIdByKey(string $key): int;
    
    public function insert(Role $role);
    
    public function update(Role $role);
    
    public function delete(Role $role);
}
