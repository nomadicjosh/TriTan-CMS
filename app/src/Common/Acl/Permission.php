<?php
namespace TriTan\Common\Acl;

use TriTan\Interfaces\ACL\PermissionInterface;

/**
 * Permission Domain
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Permission implements PermissionInterface
{
    /**
     * Permission id.
     *
     * @since 0.9.9
     * @var int
     */
    private $permission_id;

    /**
     * Permission name.
     *
     * @since 0.9.9
     * @var string
     */
    private $permission_name;
    
    /**
     * Permission key.
     * 
     * @since 0.9.9
     * @var string
     */
    private $permission_key;

    public function getId(): int
    {
        return $this->permission_id;
    }
    
    public function setId(int $id)
    {
        return $this->permission_id = $id;
    }
    
    public function getName()
    {
        return $this->permission_name;
    }
    
    public function setName(string $name)
    {
        return $this->permission_name = $name;
    }
    
    public function getKey()
    {
        return $this->permission_key;
    }
    
    public function setKey(string $key)
    {
        return $this->permission_key = $key;
    }
}
