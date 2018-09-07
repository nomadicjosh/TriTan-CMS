<?php
namespace TriTan\Interfaces\Acl;

interface RoleInterface
{
    public function getId();

    public function setId(int $id);

    public function getName();

    public function setName(string $name);

    public function getPermission();

    public function setPermission(string $permission);

    public function getKey();

    public function setKey(string $key);
}
