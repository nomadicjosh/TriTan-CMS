<?php
namespace TriTan;

use TriTan\Config;
use TriTan\Functions\User;
use TriTan\Functions\Core;

/**
 * Access Control Level Class
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class ACL
{

    /**
     * Stores the permissions for the user
     *
     * @access public
     * @var array
     */
    protected $perms = [];

    /**
     * Stores the ID of the current user
     *
     * @access public
     * @var integer
     */
    protected $user_id = 0;

    /**
     * Stores the roles of the current user
     *
     * @access public
     * @var array
     */
    protected $userRoles = [];
    public $app;

    public function __construct($user_id = '')
    {
        $this->app = \Liten\Liten::getInstance();

        if ($user_id != '') {
            $this->user_id = floatval($user_id);
        } else {
            $this->user_id = floatval(User\get_current_user_id());
        }
        $this->userRoles = $this->getUserRoles('ids');
        $this->buildACL();
    }

    public function ACL($user_id = '')
    {
        $this->__construct($user_id);
    }

    public function buildACL()
    {
        //first, get the rules for the user's role
        if (count($this->userRoles) > 0) {
            $this->perms = array_merge($this->perms, $this->getRolePerms($this->userRoles));
        }
        //then, get the individual user permissions
        $this->perms = array_merge($this->perms, $this->getUserPerms($this->user_id));
    }

    public function getPermKeyFromID($permID)
    {
        $permission = $this->app->db->table('permission')
                ->where('permission_id', floatval($permID))
                ->first();

        return Core\_escape($permission['permission_key']);
    }

    public function getPermNameFromID($permID)
    {
        $permission = $this->app->db->table('permission')
                ->where('permission_id', floatval($permID))
                ->first();

        return Core\_escape($permission['permission_name']);
    }

    public function getRoleNameFromID($roleID)
    {
        $role = $this->app->db->table('role')
                ->where('role_id', floatval($roleID))
                ->first();

        return Core\_escape($role['role_name']);
    }

    public function getRoleIDFromKey($roleKey)
    {
        $role = $this->app->db->table('role')
                ->where('role_key', (string) $roleKey)
                ->first();

        return (int) Core\_escape($role['role_id']);
    }

    public function getUserRoles()
    {
        $strSQL = $this->app->db->table('user_roles')
                ->where('user_id', floatval($this->user_id))
                ->sortBy('add_date', 'ASC')
                ->get();

        $resp = [];
        foreach ($strSQL as $r) {
            $resp[] = Core\_escape($r['role_id']);
        }

        return $resp;
    }

    public function getAllRoles($format = 'ids')
    {
        $_format = strtolower($format);

        $strSQL = $this->app->db->table('role')
                ->sortBy('role_name', 'ASC')
                ->get();

        $resp = [];
        foreach ($strSQL as $r) {
            if ($_format == 'full') {
                $resp[] = ["ID" => Core\_escape($r['role_id']), "Name" => Core\_escape($r['role_name']), "Key" => Core\_escape($r['role_key'])];
            } else {
                $resp[] = $r->id;
            }
        }
        return $resp;
    }

    public function getAllPerms($format = 'ids')
    {
        $_format = strtolower($format);

        $strSQL = $this->app->db->table('permission')
                ->sortBy('permission_name', 'ASC')
                ->get();

        $resp = [];
        foreach ($strSQL as $r) {
            if ($_format == 'full') {
                $resp[$r['permission_key']] = ['ID' => Core\_escape($r['permission_id']), 'Name' => Core\_escape($r['permission_name']), 'Key' => Core\_escape($r['permission_key'])];
            } else {
                $resp[] = Core\_escape($r->permission_id);
            }
        }
        return $resp;
    }

    public function getRolePerms($role)
    {
        if (is_array($role)) {
            $roleSQL = $this->app->db->table('role_perms')
                    ->where('role_id', 'IN', implode(",", $role))
                    ->sortBy('role_perms_id', 'ASC')
                    ->get();
        } else {
            $roleSQL = $this->app->db->table('role_perms')
                    ->where('role_id', '=', floatval($role))
                    ->sortBy('role_perms_id', 'ASC')
                    ->get();
        }

        $perms = [];
        foreach ($roleSQL as $r) {
            $pK = strtolower($this->getPermKeyFromID($r['permission_id']));
            if ($pK == '') {
                continue;
            }
            if ($r->value === '1') {
                $hP = true;
            } else {
                $hP = false;
            }
            $perms[$pK] = ['perm' => $pK, 'inheritted' => true, 'value' => $hP, 'Name' => $this->getPermNameFromID(Core\_escape($r['permission_id'])), 'ID' => Core\_escape($r['permission_id'])];
        }
        return $perms;
    }

    public function getUserPerms($user_id)
    {
        $strSQL = $this->app->db->table('user_perms')
                ->where('user_id', floatval($user_id))
                ->sortBy('modified', 'ASC')
                ->get();

        $perms = [];
        foreach ($strSQL as $r) {
            $pK = strtolower($this->getPermKeyFromID(Core\_escape($r['permission_id'])));
            if ($pK == '') {
                continue;
            }
            if ($r->value === '1') {
                $hP = true;
            } else {
                $hP = false;
            }
            $perms[$pK] = ['perm' => $pK, 'inheritted' => false, 'value' => $hP, 'Name' => $this->getPermNameFromID(Core\_escape($r['permission_id'])), 'ID' => Core\_escape($r['permission_id'])];
        }
        return $perms;
    }

    public function userHasRole($roleID)
    {
        foreach ($this->userRoles as $v) {
            if (floatval($v) === floatval($roleID)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission($permKey)
    {
        $user_role = $this->app->db->table('usermeta')
                ->where('meta_key', Config::get('tbl_prefix') . 'role')
                ->where('user_id', User\get_current_user_id())
                ->first();

        $perms = $this->app->db->table('role')
                ->where('role_id', (int) Core\_escape($user_role['meta_value']))
                ->first();

        $perm = app()->hook->{'maybe_unserialize'}(Core\_escape($perms['role_permission']));

        if (in_array($permKey, $perm)) {
            return true;
        }
        return false;
    }

    public function getUsername($id)
    {
        $strSQL = $this->app->db->table('user')
                ->where('user_id', floatval($id))
                ->first();
        return Core\_escape($strSQL['user_login']);
    }
}
