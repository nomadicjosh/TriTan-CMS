<?php
namespace TriTan\Common\Acl;

use TriTan\Interfaces\ACL\RoleInterface;

/**
 * Role Domain
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Role implements RoleInterface
{
    /**
     * Role id.
     *
     * @since 0.9.9
     * @var int
     */
    private $role_id;

    /**
     * Role name.
     *
     * @since 0.9.9
     * @var string
     */
    private $role_name;
    
    /**
     * Role permissions.
     * 
     * @since 0.9.9
     * @var string
     */
    private $role_permission;
    
    /**
     * Role key.
     * 
     * @since 0.9.9
     * @var string
     */
    private $role_key;

    public function getId()
    {
        return $this->role_id;
    }
    
    public function setId(int $id)
    {
        return $this->role_id = $id;
    }
    
    public function getName()
    {
        return $this->role_name;
    }
    
    public function setName(string $name)
    {
        return $this->role_name = $name;
    }
    
    public function getPermission()
    {
        return $this->role_permission;
    }
    
    public function setPermission(string $permission)
    {
        return $this->role_permission = $permission;
    }
    
    public function getKey()
    {
        return $this->role_key;
    }
    
    public function setKey(string $key)
    {
        return $this->role_key = $key;
    }
}
