<?php
namespace TriTan\Common\Acl;

use TriTan\Interfaces\DatabaseInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Interfaces\Acl\RoleMapperInterface;
use TriTan\Common\Acl\Role;
use TriTan\Exception\InvalidArgumentException;

class RoleMapper implements RoleMapperInterface
{
    public $db;

    public $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }

    public function findById(int $id)
    {
        if (!is_integer($id) || (int) $id < 1) {
            throw new InvalidArgumentException(
                'The ID of this entity is invalid.',
                'invalid_id'
            );
        }

        $data = $this->db->table('role')
                ->where('role_id', $id)
                ->first();

        $permission = null;
        if ($data != null) {
            $permission = $this->create($data);
        }
        return $permission;
    }

    public function findAll($format = 'ids')
    {
        $_format = strtolower($format);

        $data = $this->db->table('role')
                ->sortBy('role_name', 'ASC')
                ->get();

        $roles = [];
        if ($data != null) {
            foreach ($data as $role) {
                $roles[] = $this->create($role);
            }
        }

        $resp = [];
        foreach ($roles as $r) {
            if ($_format == 'full') {
                $resp[] = ["ID" => $r->getId(), "Name" => $r->getName(), "Key" => $r->getKey()];
            } else {
                $resp[] = $r->getId();
            }
        }
        return $resp;
    }

    public function findNameById(int $id): string
    {
        $data = $this->db->table('role')
                ->where('role_id', $id)
                ->first();

        $role_name = null;
        if ($data != null) {
            $role_name = $this->create($data);
        }
        return $role_name->getName();
    }

    public function findIdByKey(string $key): int
    {
        $data = $this->db->table('role')
                ->where('role_key', $key)
                ->first();

        $role_id = null;
        if ($data != null) {
            $role_id = $this->create($data);
        }
        return $role_id->getId();
    }

    /**
    * Create a new instance of Role. Optionally populating it
    * from a data array.
    *
    * @param array $data
    * @return TriTan\Common\Acl\Role.
    */
    public function create(array $data = null) : Role
    {
        $permission = $this->__create();
        if ($data) {
            $permission = $this->populate($permission, $data);
        }
        return $permission;
    }

    /**
     * Populate the Role object with the data array.
     *
     * @param Role $permission object.
     * @param array $data Role data.
     * @return TriTan\Common\Acl\Role
     */
    public function populate(Role $permission, array $data) : Role
    {
        $permission->setId((int) $this->context->obj['escape']->{'html'}($data['role_id']));
        $permission->setName((string) $this->context->obj['escape']->{'html'}($data['role_name']));
        $permission->setPermission((string) $this->context->obj['html']->{'purify'}($data['role_permission']));
        $permission->setKey((string) $this->context->obj['escape']->{'html'}($data['role_key']));
        return $permission;
    }

    /**
     * Create a new Role object.
     *
     * @return TriTan\Common\Acl\Role
     */
    protected function __create() : Role
    {
        return new Role();
    }

    public function insert(Role $role)
    {
        $sql = $this->db->table('role');
        $sql->begin();
        try {
            $sql->insert([
                'role_key' => $role->getKey(),
                'role_name' => $role->getName(),
                'role_permission'   => $role->getPermission()
            ]);
            $sql->commit();

            return (int) $sql->lastInsertId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('ROLEMAPPER[insert]: %s', $ex->getMessage()));
        }
    }

    public function update(Role $role)
    {
        $sql = $this->db->table('role');
        $sql->begin();
        try {
            $sql->where('role_id', $role->getId())
                ->update([
                    'role_key' => $role->getKey(),
                    'role_name' => $role->getName(),
                    'role_permission'   => $role->getPermission()
                ]);
            $sql->commit();

            return (int) $role->getId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('ROLEMAPPER[insert]: %s', $ex->getMessage()));
        }
    }

    /**
     * Save the Role object.
     *
     * @since 0.9.9
     * @param Role $role Role object.
     */
    public function save(Role $role)
    {
        if (is_null($role->getId())) {
            $this->insert($role);
        } else {
            $this->update($role);
        }
    }

    public function delete(Role $role)
    {
        $sql = $this->db->table('role');
        $sql->begin();
        try {
            $sql->where('role_id', $role->getId())
                ->delete();
            $sql->commit();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('ROLEMAPPER[delete]: %s', $ex->getMessage()));
        }
    }
}
