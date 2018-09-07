<?php
namespace TriTan\Common\Acl;

use TriTan\Interfaces\DatabaseInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Interfaces\Acl\PermissionMapperInterface;
use TriTan\Common\Acl\Permission;

class PermissionMapper implements PermissionMapperInterface
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
        
        $data = $this->db->table('permission')
                ->where('permission_id', $id)
                ->first();
        
        $permission = null;
        if($data != null) {
            $permission = $this->create($data);
        }
        return $permission;
    }
    
    public function findAll($format = 'ids')
    {
        $_format = strtolower($format);
        
        $data = $this->db->table('permission')
                ->sortBy('permission_name', 'ASC')
                ->get();
        
        $permissions = [];
        if($data != null) {
            foreach($data as $permission) {
                $permissions[] = $this->create($permission);
            }
        }
        
        $resp = [];
        foreach($permissions as $r) {
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
        $data = $this->db->table('permission')
                ->where('permission_id', $id)
                ->first();
        
        $permission_name = null;
        if($data != null) {
            $permission_name = $this->create($data);
        }
        return $permission_name->getName();
    }
    
    public function findKeyById(int $id)
    {
        $data = $this->db->table('permission')
                ->where('permission_id', $id)
                ->first();
        
        $permission_id = null;
        if($data != null) {
            $permission_id = $this->create($data);
        }
        return $permission_id->getKey();
    }
    
     /**
     * Create a new instance of Permission. Optionally populating it
     * from a data array.
     *
     * @param array $data
     * @return TriTan\Common\Acl\Permission.
     */
    public function create(array $data = null) : Permission
    {
        $permission = $this->__create();
        if ($data) {
            $permission = $this->populate($permission, $data);
        }
        return $permission;
    }

    /**
     * Populate the Permission object with the data array.
     *
     * @param Permission $permission object.
     * @param array $data Permission data.
     * @return TriTan\Common\Acl\Permission
     */
    public function populate(Permission $permission, array $data) : Permission
    {
        $permission->setId((int) $this->context->obj['escape']->{'html'}($data['permission_id']));
        $permission->setName((string) $this->context->obj['escape']->{'html'}($data['permission_name']));
        $permission->setKey((string) $this->context->obj['escape']->{'html'}($data['permission_key']));
        return $permission;
    }

    /**
     * Create a new Permission object.
     *
     * @return TriTan\Common\Acl\Permission
     */
    protected function __create() : Permission
    {
        return new Permission();
    }
    
    public function insert(Permission $permission)
    {
        $sql = $this->db->table('permission');
        $sql->begin();
        try{
            $sql->insert([
                'permission_key' => $permission->getKey(),
                'permission_name' => $permission->getName()
            ]);
            $sql->commit();
            
            return (int) $sql->lastInsertId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('PERMISSIONMAPPER[insert]: %s', $ex->getMessage()));
        }
    }
    
    public function update(Permission $permission)
    {
        $sql = $this->db->table('permission');
        $sql->begin();
        try{
            $sql->where('permission_id', $permission->getId())
                ->update([
                    'permission_key' => $permission->getKey(),
                    'permission_name' => $permission->getName(),
                ]);
            $sql->commit();
            
            return (int) $permission->getId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('PERMISSIONMAPPER[insert]: %s', $ex->getMessage()));
        }
    }
    
    /**
     * Save the Permission object.
     *
     * @since 0.9.9
     * @param Permission $permission Permission object.
     */
    public function save(Permission $permission)
    {
        if (is_null($permission->getId())) {
            $this->insert($permission);
        } else {
            $this->update($permission);
        }
    }
    
    public function delete(Permission $permission)
    {
        $sql = $this->db->table('permission');
        $sql->begin();
        try {
            $sql->where('permission_id', $permission->getId())
                ->delete();
            $sql->commit();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('PERMISSIONMAPPER[delete]: %s', $ex->getMessage()));
        }
    }
}
