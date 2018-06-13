<?php

namespace TriTan;

use TriTan\Config;
use TriTan\Functions as func;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * User API: User Class
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class User
{

    /**
     * User data container.
     *
     * @since 0.9
     * @var object
     */
    public $data;

    /**
     * ACL data container.
     * 
     * @since 0.9.8
     * @var object
     */
    public $acl;

    /**
     * User user_id.
     *
     * @since 0.9
     * @var int
     */
    public $user_id = 0;

    /**
     * The site ID the capabilities of this user are initialized for.
     *
     * @since 0.9.7
     * @var int
     */
    private $site_id = 0;

    /**
     * Constructor.
     *
     * Retrieves the userdata and passes it to User::init().
     *
     * @since 0.9
     * @param int|string|stdClass|User $user_id     User's ID, a User object, or a user array from the DB.
     * @param string $name                          Optional. User's username
     * @param int $site_id                          Optional Site ID, defaults to current site.
     */
    public function __construct($user_id = 0, $name = '', $site_id = '')
    {
        $this->acl = new ACL();

        if ($user_id instanceof User) {
            $this->init($user_id->data, $site_id);
            return;
        } elseif (is_object($user_id)) {
            $this->init($user_id, $site_id);
            return;
        }

        if (!empty($user_id) && !is_numeric($user_id)) {
            $name = $user_id;
            $user_id = 0;
        }

        if ($user_id) {
            $data = self::get_data_by('id', $user_id);
        } else {
            $data = self::get_data_by('login', $name);
        }

        if ($data) {
            $this->init($data, $site_id);
        } else {
            $this->data = new \stdClass;
        }
    }

    /**
     * Sets up object properties.
     *
     * @since  0.9
     * @param object $data              User DB row array.
     * @param int $site_id Optional.    The site ID to initialize
     */
    public function init($data, $site_id = '')
    {
        $this->data = func\array_to_object($data);
        $this->user_id = (int) $data['user_id'];

        $this->for_site($site_id);
    }

    /**
     * Return only the main user fields.
     *
     * @since 0.9
     * @param string $field The field to query against: 'id', 'ID', 'email' or 'login'.
     * @param string|int $value The field value
     * @return object|false Raw user object
     */
    public static function get_data_by($field, $value)
    {

        // 'ID' is an alias of 'id'.
        if ('ID' === $field) {
            $field = 'id';
        }

        if ('id' == $field) {
            // Make sure the value is numeric to avoid casting objects, for example,
            // to int 1.
            if (!is_numeric($value)) {
                return false;
            }
            $value = intval($value);
            if ($value < 1) {
                return false;
            }
        } else {
            $value = _trim($value);
        }

        if (!$value) {
            return false;
        }

        switch ($field) {
            case 'id':
                $user_id = (int) $value;
                $db_field = 'user_id';
                break;
            case 'email':
                $user_id = func\ttcms_cache_get($value, 'useremail');
                $db_field = 'user_email';
                break;
            case 'login':
                $value = func\sanitize_user($value);
                $user_id = func\ttcms_cache_get($value, 'userlogins');
                $db_field = 'user_login';
                break;
            default:
                return false;
        }

        if (false !== $user_id) {
            if ($user = func\ttcms_cache_get($user_id, 'users')) {
                return $user;
            }
        }

        if (!$user = app()->db->table('user')->where($db_field, sprintf('%s', $value))->first()) {
            return false;
        }

        func\update_user_caches($user);

        return $user;
    }

    /**
     * Magic method for checking the existence of a certain custom field.
     *
     * @since 0.9
     * @param string $key User meta key to check if set.
     * @return bool Whether the given user meta key is set.
     */
    public function __isset($key)
    {
        if (isset($this->data->$key)) {
            return true;
        }
        return func\metadata_exists('user', $this->user_id, Config::get('tbl_prefix') . $key);
    }

    /**
     * Magic method for accessing custom fields.
     *
     * @since 0.9
     * @param string $key User meta key to retrieve.
     * @return mixed Value of the given user meta key (if set). If `$key` is 'id', the user ID.
     */
    public function __get($key)
    {
        if ('id' == $key || 'ID' == $key) {
            return $this->user_id;
        }

        if (isset($this->data->$key)) {
            $value = $this->data->$key;
        } else {
            $value = func\get_user_meta($this->user_id, Config::get('tbl_prefix') . $key, true);
        }

        return $value;
    }

    /**
     * Magic method for setting custom user fields.
     *
     * This method does not update custom fields in the database. It only stores
     * the value on the User instance.
     *
     * @since 0.9
     * @param string $key   User meta key.
     * @param mixed  $value User meta value.
     */
    public function __set($key, $value)
    {
        if ('id' == $key || 'ID' == $key) {
            $this->user_id = $value;
            return;
        }

        $this->data->$key = $value;
    }

    /**
     * Magic method for unsetting a certain custom field.
     *
     * @since 0.9
     * @param string $key User meta key to unset.
     */
    public function __unset($key)
    {
        if (isset($this->data->$key)) {
            unset($this->data->$key);
        }
    }

    /**
     * Determine whether the user exists in the database.
     *
     * @since 0.9
     * @return bool True if user exists in the database, false if not.
     */
    public function exists()
    {
        return !empty($this->user_id);
    }

    /**
     * Retrieve the value of a property or meta key.
     *
     * Retrieves from the users and usermeta table.
     *
     * @since 0.9
     * @param string $key Property
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * Determine whether a property or meta key is set
     *
     * Consults the users and usermeta tables.
     *
     * @since 0.9
     * @param string $key Property
     * @return bool
     */
    public function has_prop($key)
    {
        return $this->__isset($key);
    }

    /**
     * Return an array representation.
     *
     * @since 0.9.7
     * @return array Array representation.
     */
    public function to_array()
    {
        return get_object_vars($this->data);
    }

    /**
     * Sets the site to operate on. Defaults to the current site.
     *
     * @since 0.9.7
     * @param int $site_id Site ID to initialize user capabilities for. Default is the current site.
     */
    public function for_site($site_id = '')
    {

        if (!empty($site_id)) {
            $this->site_id = absint($site_id);
        } else {
            $this->site_id = func\get_current_site_id();
        }
    }

    public function get_role_id($role)
    {
        
    }

    public function set_role($role)
    {
        $old_role = func\get_user_meta($this->user_id, Config::get('tbl_prefix') . 'role', true);

        if (is_numeric($role)) {
            $message = func\_t('Invalid role. Must use role_key (super, admin, editor, etc.) and not role_id.', 'tritan-cms');
            func\_incorrectly_called(__FUNCTION__, $message, '0.9.8');
            return;
        }

        $new_role = $this->acl->getRoleIDFromKey($role);

        func\update_user_meta($this->user_id, Config::get('tbl_prefix') . 'role', $new_role, $old_role);

        /**
         * Fires after the user's role has changed.
         *
         * @since 0.9.8
         * @param int       $user_id    The user id.
         * @param string    $role       The new role.
         * @param string    $old_role   The user's previous role.
         */
        app()->hook->{'do_action'}('set_user_role', $this->user_id, $new_role, $old_role);
    }

}
