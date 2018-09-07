<?php
namespace TriTan\Interfaces\Acl;

use TriTan\Common\Acl\Permission;

interface PermissionRepositoryInterface
{
    public function findById(int $id);
    
    public function findNameById(int $id);
    
    public function findKeyById(int $id);
    
    public function findAll($format);
    
    public function insert(Permission $permission);
    
    public function update(Permission $permission);
    
    public function delete(Permission $permission);
}
