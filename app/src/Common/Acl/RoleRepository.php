<?php
namespace TriTan\Common\Acl;

use TriTan\Interfaces\Acl\RoleRepositoryInterface;
use TriTan\Interfaces\Acl\RoleMapperInterface;
use TriTan\Common\Acl\Role;

class RoleRepository implements RoleRepositoryInterface
{
    private $mapper;

    public function __construct(RoleMapperInterface $mapper)
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

    public function findNameById(int $id): string
    {
        return $this->mapper->{'findNameById'}($id);
    }
    
    public function findIdByKey(string $key): int
    {
        return $this->mapper->{'findIdByKey'}($key);
    }
    
    public function insert(Role $role)
    {
        return $this->mapper->{'insert'}($role);
    }
    
    public function update(Role $role)
    {
        return $this->mapper->{'update'}($role);
    }
    
    public function save(Role $role)
    {
        return $this->mapper->{'save'}($role);
    }

    public function delete(Role $role)
    {
        return $this->mapper->{'delete'}($role);
    }
}
