<?php namespace TriTan;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * User API: User Class
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class User
{

    /**
     * User data container.
     *
     * @since 1.0.0
     * @var object
     */
    public $data;

    /**
     * User user_id.
     *
     * @since 1.0.0
     * @var int
     */
    public $user_id = 0;

    /**
     * Constructor.
     *
     * Retrieves the userdata and passes it to User::init().
     *
     * @since 1.0.0
     * @param int|string|stdClass|User $user_id     User's ID, a User object, or a user object from the DB.
     * @param string $name                          Optional. User's username
     * @param int $site_id                          Optional Site ID, defaults to current site.
     */
    public function __construct($user_id = 0, $name = '')
    {
        if ($user_id instanceof User) {
            $this->init($user_id->data);
            return;
        } elseif (is_object($user_id)) {
            $this->init($user_id);
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
            $this->init($data);
        } else {
            $this->data = new \stdClass;
        }
    }

    /**
     * Sets up object properties.
     *
     * @since  1.0.0
     * @param object $data    User DB row object.
     */
    public function init($data)
    {
        $this->data = $data;
        $this->user_id = (int) $data['user_id'];
    }

    /**
     * Return only the main user fields.
     *
     * @since 1.0.0
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
                $user_id = ttcms_cache_get($value, 'useremail');
                $db_field = 'user_email';
                break;
            case 'login':
                $value = sanitize_user($value);
                $user_id = ttcms_cache_get($value, 'userlogins');
                $db_field = 'user_login';
                break;
            default:
                return false;
        }

        if (false !== $user_id) {
            if ($user = ttcms_cache_get($user_id, 'users')) {
                return $user;
            }
        }

        if (!$user = app()->db->table('user')->where($db_field, sprintf('%s', $value))->first()) {
            return false;
        }

        update_user_caches($user);

        return $user;
    }

    /**
     * Magic method for checking the existence of a certain custom field.
     *
     * @since 1.0.0
     * @param string $key User meta key to check if set.
     * @return bool Whether the given user meta key is set.
     */
    public function __isset($key)
    {
        if (isset($this->data->$key)) {
            return true;
        }
        return metadata_exists('users', $this->user_id, Config::get('tbl_prefix') . $key);
    }

    /**
     * Magic method for accessing custom fields.
     *
     * @since 1.0.0
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
            $value = get_user_meta($this->user_id, Config::get('tbl_prefix') . $key, true);
        }

        return $value;
    }

    /**
     * Magic method for setting custom user fields.
     *
     * This method does not update custom fields in the database. It only stores
     * the value on the User instance.
     *
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
     * @param string $key Property
     * @return bool
     */
    public function has_prop($key)
    {
        return $this->__isset($key);
    }
}
