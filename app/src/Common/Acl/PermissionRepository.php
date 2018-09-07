<?php
namespace TriTan\Common\Acl;

use TriTan\Interfaces\Acl\PermissionRepositoryInterface;
use TriTan\Interfaces\Acl\PermissionMapperInterface;
use TriTan\Common\Acl\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    private $mapper;

    public function __construct(PermissionMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
    
    public function findById(int $id)
    {
        return $this->mapper->{'findById'}($id);
    }
    
    public function findAll($format)
    {
        return $this->mapper->{'findAll'}($format);
    }

    public function findNameById(int $id)
    {
        return $this->mapper->{'findNameById'}($id);
    }
    
    public function findKeyById(int $id)
    {
        return $this->mapper->{'findKeyById'}($id);
    }
    
    public function insert(Permission $permission)
    {
        return $this->mapper->{'insert'}($permission);
    }
    
    public function update(Permission $permission)
    {
        return $this->mapper->{'update'}($permission);
    }
    
    public function save(Permission $permission)
    {
        return $this->mapper->{'save'}($permission);
    }

    public function delete(Permission $permission)
    {
        return $this->mapper->{'delete'}($permission);
    }
}
