<?php
namespace TriTan\Interfaces;

interface AclInterface
{
    /**
     * @param int $permID Permission id.
     *
     * @return string Permission key.
     */
    public function getPermKeyFromId($permID);

    /**
     * @param int $permID Permission id.
     *
     * @return string Permission name.
     */
    public function getPermNameFromId($permID);

    /**
     * @param int $roleID Role id.
     *
     * @return string Role name.
     */
    public function getRoleNameFromId($roleID);

    /**
     * @param string $roleKey Role key.
     *
     * @return int Role id.
     */
    public function getRoleIDFromKey($roleKey);

    /**
     * @return array Roles of current user.
     */
    public function getUserRoles();

    /**
     * @param string $format How roles are returned.
     */
    public function getAllRoles($format);

    /**
     *
     * @param string $format How permissions are returned.
     */
    public function getAllPerms($format);

    /**
     *
     * @param int $role Role id.
     *
     * @return array The permissions of the role.
     */
    public function getRolePerms($role);

    /**
     *
     * @param int $user_id User id.
     *
     * @return array The permissions associated with user.
     */
    public function getUserPerms($user_id);

    /**
     * @param int $roleID Role id.
     *
     * @return bool True if user has role, false otherwise.
     */
    public function doesUserHaveRole($roleID);

    /**
     * @param string $permKey Permission key.
     *
     * @return bool True if user has permission, false otherwise.
     */
    public function currentUserCan($permKey);
}
