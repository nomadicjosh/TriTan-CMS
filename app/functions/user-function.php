<?php

namespace TriTan\Functions;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;

/**
 * TriTan CMS User Functions
 *
 * @license GPLv3
 *         
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Used on the Role screen for permissions.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param int $id Role id.
 */
function role_perm($id = 0)
{
    $role = app()->db->table('role')
            ->where('role_id', (int) $id)
            ->first();
    $perm = app()->hook->{'maybe_unserialize'}(_escape($role['role_permission']));

    $sql = app()->db->table('permission')->all();
    foreach ($sql as $r) {
        echo '<tr>
					<td>' . $r['permission_name'] . '</td>
					<td class="text-center">';
        if (is_array($perm) && in_array($r['permission_key'], $perm)) {
            echo '<input type="checkbox" class="flat-red" name="role_permission[]" value="' . $r['permission_key'] . '" checked="checked" />';
        } else {
            echo '<input type="checkbox" class="flat-red" name="role_permission[]" value="' . $r['permission_key'] . '" />';
        }
        echo '</td>
            </tr>';
    }
}

function user_perm($id)
{
    $array = [];

    $pp = app()->db->table('user_perms')
            ->where('user_id', $id)
            ->first();

    foreach ($pp as $r) {
        $array[] = $r;
    }

    $userPerm = app()->hook->{'maybe_unserialize'}(_escape($r['user_perms_permission']));
    /**
     * Select the role(s) of the user who's
     * userID = $id
     */
    $array1 = [];

    $pr = app()->db->table('user_roles')
            ->where('user_id', $id)
            ->first();

    foreach ($pr as $r1) {
        $array1[] = $r1;
    }
    /**
     * Select all the permissions from the role(s)
     * that are connected to the selected user.
     */
    //$array2 = [];
    $role = app()->db->table('role')
                    ->where('role_id', (int) $r1['role_id'])->first();
    /* foreach ($role as $r2) {
      $array2[] = $r2;
      } */
    $perm = app()->hook->{'maybe_unserialize'}(_escape($role['role_permission']));
    $permission = app()->db->table('permission')->all();
    foreach ($permission as $row) {
        echo '
            <tr>
                <td>' . $row['permission_name'] . '</td>
                <td class="text-center">';
        if (is_array($perm) && in_array($row['permission_key'], $perm)) {
            echo '<input type="checkbox" name="user_perms_permission[]" value="' . $row['permission_key'] . '" checked="checked" disabled="disabled" />';
        } elseif ($userPerm != '' && in_array($row['permission_key'], $userPerm)) {
            echo '<input type="checkbox" name="user_perms_permission[]" value="' . $row['permission_key'] . '" checked="checked" />';
        } else {
            echo '<input type="checkbox" name="user_perms_permission[]" value="' . $row['permission_key'] . '" />';
        }
        echo '</td>
            </tr>';
    }
}

/**
 * Print a dropdown list of users.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param int $active If working with active record, it will be the user's id.
 * @return array Dropdown list of users.
 */
function get_users_dropdown($active = null)
{
    $tbl_prefix = Config::get('tbl_prefix');

    $users = [];
    $site_users = app()->db->table('usermeta')
            ->where('meta_key', 'match', "/$tbl_prefix/")
            ->get();
    foreach ($site_users as $site_user) {
        $users[] = _escape($site_user['user_id']);
    }

    $list_users = app()->db->table('user')
            ->where('user_id', 'not in', $users)
            ->get();

    foreach ($list_users as $user) {
        echo '<option value="' . (int) _escape($user['user_id']) . '"' . selected((int) _escape($user['user_id']), $active, false) . '>' . get_name((int) _escape($user['user_id'])) . '</option>';
    }
}

/**
 * Sanitizes a username, stripping out unsafe characters.
 *
 * Removes tags, octets, entities, and if strict is enabled, will only keep
 * alphanumeric, _, space, ., -, @. After sanitizing, it passes the username,
 * raw username (the username in the parameter), and the value of $strict as
 * parameters for the `sanitize_user` filter.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param string    $username The username to be sanitized.
 * @param bool      $strict If set, limits $username to specific characters. Default false.
 * @return string The sanitized username, after passing through filters.
 */
function sanitize_user($username, $strict = false)
{
    $raw_username = $username;
    $username = ttcms_strip_tags($username);
    $username = ttcms_remove_accents($username);
    // Kill octets
    $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
    // Kill entities
    $username = preg_replace('/&.+?;/', '', $username);

    // If strict, reduce to ASCII for max portability.
    if ($strict) {
        $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
    }

    $username = _trim($username);

    /**
     * Filters a sanitized username string.
     *
     * @since 0.9
     * @param string $username     Sanitized username.
     * @param string $raw_username The username prior to sanitization.
     * @param bool   $strict       Whether to limit the sanitization to specific characters. Default false.
     */
    return app()->hook->{'apply_filter'}('sanitize_user', $username, $raw_username, $strict);
}

/**
 * Get the current user's ID
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @return int The current user's ID, or 0 if no user is logged in.
 */
function get_current_user_id()
{
    $cookie = get_secure_cookie_data('TTCMS_COOKIENAME');
    if ($cookie->user_id <= 0) {
        return (int) 0;
    }
    return (int) $cookie->user_id;
}

/**
 * Returns object of data for current user.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @return object
 */
function ttcms_get_current_user()
{
    $user = get_userdata(get_current_user_id());
    return $user;
}

/**
 * Returns the name of a particular user.
 * 
 * Uses `get_name` filter.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int $id
 *            User ID.
 * @return string
 */
function get_name($id, $reverse = false)
{
    if ('' == _trim($id)) {
        $message = _t('Invalid user ID: empty ID given.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    if (!is_numeric($id)) {
        $message = _t('Invalid user id: user id must be numeric.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    $name = get_user_by('id', $id);

    if ($reverse) {
        $_name = _escape($name->user_fname) . ' ' . _escape($name->user_lname);
    } else {
        $_name = _escape($name->user_lname) . ', ' . _escape($name->user_fname);
    }

    return app()->hook->{'apply_filter'}('get_name', $_name);
}

/**
 * Shows selected user's initials instead of
 * his/her's full name.
 * 
 * Uses `get_initials` filter.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int $id
 *            User ID
 * @param int $initials
 *            Number of initials to show.
 * @return string
 */
function get_initials($id, $initials = 2)
{
    if ('' == _trim($id)) {
        $message = _t('Invalid user ID: empty ID given.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    if (!is_numeric($id)) {
        $message = _t('Invalid user id: user id must be numeric.', 'tritan-cms');
        _incorrectly_called(__FUNCTION__, $message, '0.9');
        return;
    }

    $name = get_user_by('user_id', $id);

    if ($initials == 2) {
        $_initials = mb_substr(_escape($name->user_fname), 0, 1, 'UTF-8') . '. ' . mb_substr(_escape($name->user_lname), 0, 1, 'UTF-8') . '.';
    } else {
        $_initials = _escape($name->user_lname) . ', ' . mb_substr(_escape($name->user_fname), 0, 1, 'UTF-8') . '.';
    }

    return app()->hook->{'apply_filter'}('get_initials', $_initials);
}

/**
 * Retrieve requested field from user table
 * based on user's id.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param string $id
 *            User ID.
 * @param mixed $field
 *            Data requested of particular user.
 * @return mixed
 */
function get_user_value($id, $field)
{
    $value = get_user_by('id', $id);

    return $value->{$field};
}

/**
 * Retrieves a list of roles from the roles table.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @return mixed
 */
function get_perm_roles()
{
    $query = app()->db->table('role')->all();

    foreach ($query as $row) {
        echo '<option value="' . (int) _escape($row['role_id']) . '">' . _escape($row['role_name']) . '</option>' . "\n";
    }
}

/**
 * Checks whether the given username exists.
 * 
 * Uses `username_exists` filter.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param string $username
 *            Username to check.
 * @return string|false The user's ID on success, and false on failure.
 */
function username_exists($username)
{
    if ($user = get_user_by('login', $username)) {
        $user_id = (int) _escape($user->user_id);
    } else {
        $user_id = false;
    }

    /**
     * Filters whether the given username exists or not.
     *
     * @since 0.9
     * @param int|false $user_id    The user's user_id on success, and false on failure.
     * @param string    $username   Username to check.
     */
    return app()->hook->{'apply_filter'}('username_exists', $user_id, $username);
}

/**
 * Checks whether the given email exists.
 * 
 * Uses `email_exists` filter.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param string $email
 *            Email to check.
 * @return string|false The user's ID on success, and false on failure.
 */
function email_exists($email)
{
    if ($user = get_user_by('email', $email)) {
        $user_id = (int) _escape($user->user_id);
    } else {
        $user_id = false;
    }

    /**
     * Filters whether the given email exists or not.
     *
     * @since 0.9
     * @param int|false $user_id    The user's user_id on success, and false on failure.
     * @param string    $email      Email to check.
     */
    return app()->hook->{'apply_filter'}('email_exists', $user_id, $email);
}

/**
 * Checks whether the given username is valid.
 * 
 * Uses `validate_username` filter.
 * 
 * Example Usage:
 * 
 *      if(validate_username('batman')) {
 *          //do something;
 *      }
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param string $username
 *            Username to check.
 * @return bool Whether given username is valid.
 */
function validate_username($username)
{
    $sanitize = sanitize_user($username, true);
    $valid = \TriTan\Validators::validateUsername($sanitize);

    /**
     * Filters whether the given username is valid or not.
     *
     * @since 0.9
     * @param bool      $valid      Whether given username is valid.
     * @param string    $username   Username to check.
     */
    return app()->hook->{'apply_filter'}('validate_username', $valid, $username);
}

/**
 * Validates an email address.
 * 
 * Uses `validate_email` filter.
 * 
 * Example Usage:
 * 
 *      if(validate_email('email@gmail.com')) {
 *          //do something;
 *      }
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param string $email Email address to validate.
 * @return bool True if valid, false otherwise.
 */
function validate_email($email)
{
    $valid = \TriTan\Validators::validateEmail($email);

    /**
     * Filters whether the given email is valid or not.
     *
     * @since 0.9
     * @param bool      $valid  Whether given email is valid.
     * @param string    $email  Email to check.
     */
    return app()->hook->{'apply_filter'}('validate_email', $valid, $email);
}

/**
 * Adds label to user's status.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param string $status
 * @return string
 */
function ttcms_user_status_label($status)
{
    $label = [
        'A' => 'label-success',
        'I' => 'label-danger'
    ];

    return $label[$status];
}

/**
 * Retrieve a list of available user roles.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param type $active
 */
function get_user_roles($active = null)
{
    $roles = app()->db->table('role')
            ->all();

    foreach ($roles as $role) {
        echo '<option value="' . (int) _escape($role['role_id']) . '"' . selected($active, (int) _escape($role['role_id']), false) . '>' . _escape($role['role_name']) . '</option>';
    }
}

/**
 * Retrieve a list of all users.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param type $active
 */
function get_users_list($active = null)
{
    $users = app()->db->table('user')
            ->all();

    foreach ($users as $user) {
        echo '<option value="' . (int) _escape($user['user_id']) . '"' . selected($active, (int) _escape($user['user_id']), false) . '>' . get_name((int) _escape($user['user_id'])) . '</option>';
    }
}

/**
 * Retrieve user meta field for a user.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int    $user_id User ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool   $single  Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function get_user_meta($user_id, $key = '', $single = false)
{
    return get_metadata('user', $user_id, $key, $single);
}

/**
 * Get user meta data by meta ID.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int $mid
 * @return array|bool
 */
function get_user_meta_by_mid($mid)
{
    return get_metadata_by_mid('user', $mid);
}

/**
 * Update user meta field based on user ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and user ID.
 *
 * If the meta field for the user does not exist, it will be added.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int    $user_id    User ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '')
{
    return update_metadata('user', $user_id, $meta_key, $meta_value, $prev_value);
}

/**
 * Update user meta data by meta ID.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int $mid
 * @param string $meta_key
 * @param string $meta_value
 * @return bool
 */
function update_user_meta_by_mid($mid, $meta_key, $meta_value)
{
    $_meta_key = ttcms_unslash($meta_key);
    $_meta_value = ttcms_unslash($meta_value);
    return update_metadata_by_mid('user', $mid, $_meta_key, $_meta_value);
}

/**
 * Adds meta data to a user.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int    $user_id    User ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value.
 * @param bool   $unique     Optional. Whether the same key should not be added. Default false.
 * @return int|false Meta ID on success, false on failure.
 */
function add_user_meta($user_id, $meta_key, $meta_value, $unique = false)
{
    return add_metadata('user', $user_id, $meta_key, $meta_value, $unique);
}

/**
 * Remove metadata matching criteria from a user.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int    $user_id    User ID
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value.
 * @return bool True on success, false on failure.
 */
function delete_user_meta($user_id, $meta_key, $meta_value = '')
{
    return delete_metadata('user', $user_id, $meta_key, $meta_value);
}

/**
 * Delete user meta data by meta ID.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int $mid
 * @return bool
 */
function delete_user_meta_by_mid($mid)
{
    return delete_metadata_by_mid('user', $mid);
}

/**
 * Retrieve user option that can be either per Site or global.
 *
 * If the user ID is not given, then the current user will be used instead. If
 * the user ID is given, then the user data will be retrieved. The filter for
 * the result, will also pass the original option name and finally the user data
 * object as the third parameter.
 *
 * The option will first check for the per site name and then the global name.
 * 
 * Uses `get_user_option_$option` filter.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param string $option     User option name.
 * @param int    $user       Optional. User ID.
 * @return mixed User option value on success, false on failure.
 */
function get_user_option($option, $user = 0)
{
    if (empty($user)) {
        $user = get_current_user_id();
    }

    $udata = get_userdata($user);

    if (!$user = (int) _escape($udata->user_id)) {
        return false;
    }

    if (null != metadata_exists('user', (int) $user, Config::get('tbl_prefix') . $option)) {
        $result = get_user_meta((int) $user, Config::get('tbl_prefix') . $option, true);
    } elseif (null != metadata_exists('user', (int) $user, $option)) {
        $result = get_user_meta((int) $user, $option, true);
    } else {
        $result = false;
    }

    /**
     * Filters a specific user option value.
     *
     * The dynamic portion of the hook name, `$option`, refers to the user option name.
     *
     * @since 0.9
     * @param mixed     $result Value for the user's option.
     * @param string    $option Name of the option being retrieved.
     * @param int       $user   ID of the user whose option is being retrieved.
     */
    return app()->hook->{'apply_filter'}("get_user_option_{$option}", $result, $option, $user);
}

/**
 * Update user option with global site capability.
 *
 * User options are just like user metadata except that they have support for
 * global site options. If the 'global' parameter is false, which it is by default
 * it will prepend the TriTan CMS table prefix to the option name.
 *
 * Deletes the user option if $newvalue is empty.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int    $user_id     User ID.
 * @param string $option_name User option name.
 * @param mixed  $newvalue    User option value.
 * @param bool   $global      Optional. Whether option name is global or site specific.
 *                            Default false (site specific).
 * @return int|bool User meta ID if the option didn't exist, true on successful update,
 *                  false on failure.
 */
function update_user_option($user_id, $option_name, $newvalue, $global = false)
{
    if (!$global) {
        $option_name = Config::get('tbl_prefix') . $option_name;
    }

    return update_user_meta($user_id, $option_name, $newvalue);
}

/**
 * Delete user option with global site capability.
 *
 * User options are just like user metadata except that they have support for
 * global site options. If the 'global' parameter is false, which it is by default
 * it will prepend the TriTan CMS table prefix to the option name.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param int    $user_id           User ID
 * @param string $option_name       User option name.
 * @param bool   $global            Optional. Whether option name is global or site specific.
 *                                  Default false (site specific).
 * @return bool True on success, false on failure.
 */
function delete_user_option($user_id, $option_name, $global = false)
{
    if (!$global) {
        $option_name = Config::get('tbl_prefix') . $option_name;
    }

    return delete_user_meta($user_id, $option_name);
}

/**
 * Insert a user into the database.
 *
 * Most of the `$userdata` array fields have filters associated with the values. Exceptions are
 * 'user_id', 'user_url', 'user_admin_layout', 'user_admin_sidebar', 'user_admin_skin',
 * 'user_registered' and 'user_modified'. The filters have the prefix 'pre_' followed by
 * the field name. An example using 'user_bio' would have the filter called, 'pre_user_bio' that
 * can be hooked into.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param array|object|User $userdata {
 *     An array, object or User object of user data arguments.
 *
 *     @type int        $user_id               User's ID. If supplied, the user will be updated.
 *     @type string     $user_pass             The plain-text user password.
 *     @type string     $user_login            The user's login username.
 *     @type string     $user_fname            The user's first name.
 *     @type string     $user_lname            The user's last name.
 *     @type string     $user_bio              The user's biographical description.
 *     @type string     $user_email            The user's email address.
 *     @type string     $user_url              The user's url.
 *     @type int        $user_role             The User's role.
 *     @type string     $user_status           The user's status.
 *     @type int        $user_admin_layout     The user's admin layout option.
 *     @type int        $user_admin_sidebar    The user's admin sidebar option
 *     @type string     $user_admin_skin       The user's admin skin option.
 *     @type string     $user_registered       Date the user registered. Format is 'Y-m-d H:i:s'.
 *     @type string     $user_modified         Date the user's account was updated. Format is 'Y-m-d H:i:s'.
 * }
 * @return int|Exception The newly created user's user_id or throws an exception if the user could not
 *                      be created.
 */
function ttcms_insert_user($userdata)
{
    if ($userdata instanceof \stdClass) {
        $userdata = get_object_vars($userdata);
    } elseif ($userdata instanceof \TriTan\User) {
        $userdata = $userdata->to_array();
    }

    // Are we updating or creating?
    if (!empty($userdata['user_id'])) {
        $user_id = (int) $userdata['user_id'];
        $update = true;
        $old_user_data = get_userdata($user_id);

        if (!$old_user_data) {
            throw new Exception(_t('Invalid user id.', 'tritan-cms'), 'invalid_user_id');
        }

        // hashed in ttcms_update_user(), plaintext if called directly
        $user_pass = !empty($userdata['user_pass']) ? $userdata['user_pass'] : $old_user_data->user_pass;
    } else {
        $update = false;
        // Hash the password
        $user_pass = ttcms_hash_password($userdata['user_pass']);
    }

    $sanitized_user_login = sanitize_user($userdata['user_login'], true);

    /**
     * Filters a username after it has been sanitized.
     *
     * This filter is called before the user is created or updated.
     *
     * @since 0.9
     * @param string $sanitized_user_login Username after it has been sanitized.
     */
    $pre_user_login = app()->hook->{'apply_filter'}('pre_user_login', $sanitized_user_login);

    //Remove any non-printable chars from the login string to see if we have ended up with an empty username
    $user_login = _trim($pre_user_login);

    // user_login must be between 0 and 60 characters.
    if (empty($user_login)) {
        throw new Exception(_t('Cannot create a user with an empty login name.', 'tritan-cms'), 'empty_user_login');
    } elseif (mb_strlen($user_login) > 60) {
        throw new Exception(_t('Username may not be longer than 60 characters.', 'tritan-cms'), 'user_login_too_long');
    }

    if (!$update && username_exists($user_login)) {
        throw new Exception(_t('Sorry, that username already exists!', 'tritan-cms'), 'existing_user_login');
    }

    /**
     * Filters the list of blacklisted usernames.
     *
     * @since 0.9
     * @param array $usernames Array of blacklisted usernames.
     */
    $illegal_logins = (array) app()->hook->{'apply_filter'}('illegal_user_logins', blacklisted_usernames());

    if (in_array(strtolower($user_login), array_map('strtolower', $illegal_logins))) {
        throw new Exception(sprintf(_t('Sorry, the username <strong>%s</strong> is not allowed.', 'tritan-cms'), $user_login), 'invalid_username');
    }

    $raw_user_url = if_null($userdata['user_url']);
    /**
     * Filters a user's URL before the user is created or updated.
     *
     * @since 0.9
     * @param string $raw_user_url The user's URL.
     */
    $user_url = app()->hook->{'apply_filter'}('pre_user_url', $raw_user_url);

    $raw_user_email = if_null($userdata['user_email']);
    /**
     * Filters a user's email before the user is created or updated.
     *
     * @since 0.9
     * @param string $raw_user_email The user's email.
     */
    $user_email = app()->hook->{'apply_filter'}('pre_user_email', $raw_user_email);

    /*
     * If there is no update, just check for `email_exists`. If there is an update,
     * check if current email and new email are the same, or not, and check `email_exists`
     * accordingly.
     */
    if ((!$update || (!empty($old_user_data) && 0 !== strcasecmp($user_email, _escape($old_user_data->user_email)) ) ) && email_exists($user_email)) {
        throw new Exception(_t('Sorry, that email address is already used.', 'tritan-cms'), 'existing_user_email');
    }

    // Store values to save in user meta.
    $meta = [];

    $meta['username'] = $user_login;

    $user_fname = if_null($userdata['user_fname']);
    /**
     * Filters a user's first name before the user is created or updated.
     *
     * @since 0.9
     * @param string $user_fname The user's first name.
     */
    $meta['fname'] = app()->hook->{'apply_filter'}('pre_user_fname', $user_fname);

    $user_lname = if_null($userdata['user_lname']);
    /**
     * Filters a user's last name before the user is created or updated.
     *
     * @since 0.9
     * @param string $user_lname The user's last name.
     */
    $meta['lname'] = app()->hook->{'apply_filter'}('pre_user_lname', $user_lname);

    $meta['email'] = $user_email;

    $user_bio = if_null($userdata['user_bio']);
    /**
     * Filters a user's bio before the user is created or updated.
     *
     * @since 0.9
     * @param string $user_bio The user's bio.
     */
    $meta['bio'] = app()->hook->{'apply_filter'}('pre_user_bio', $user_bio);

    $user_status = if_null($userdata['user_status']);
    /**
     * Filters a user's status before the user is created or updated.
     *
     * @since 0.9
     * @param string $user_status The user's status.
     */
    $meta['status'] = app()->hook->{'apply_filter'}('pre_user_status', $user_status);

    $user_admin_layout = 0;

    $meta['admin_layout'] = if_null($user_admin_layout);

    $user_admin_sidebar = 0;

    $meta['admin_sidebar'] = if_null($user_admin_sidebar);

    $user_admin_skin = 'skin-red-light';

    $meta['admin_skin'] = if_null($user_admin_skin);

    $user_addedby = (int) get_current_user_id() <= (int) 0 ? (int) 1 : (int) get_current_user_id();

    $user_registered = (string) \Jenssegers\Date\Date::now();

    $user_modified = (string) \Jenssegers\Date\Date::now();

    $compacted = compact('user_login', 'user_fname', 'user_lname', 'user_pass', 'user_email', 'user_url');
    $data = ttcms_unslash($compacted);

    /**
     * Filters user data before the record is created or updated.
     *
     * It only includes data in the user's table, not any user metadata.
     *
     * @since 0.9
     * @param array    $data {
     *     Values and keys for the user.
     *
     *      @type string $user_login        The user's login.
     *      @type string $user_fname        The user's first name.
     *      @type string $user_lname        The user's last name.
     *      @type string $user_pass         The user's password.
     *      @type string $user_email        The user's email.
     *      @type string $user_url          The user's url.
     *      @type string $user_addedby      User who registered user.
     *      @type string $user_registered   Timestamp describing the moment when the user registered. Defaults to
     *                                      Y-m-d h:i:s
     * }
     * @param bool     $update Whether the user is being updated rather than created.
     * @param int|null $id     ID of the user to be updated, or NULL if the user is being created.
     */
    $data = app()->hook->{'apply_filter'}('ttcms_pre_insert_user_data', $data, $update, $update ? (int) $user_id : null);

    if (!$update) {
        $_data = $data + compact('user_addedby', 'user_registered');
        $user_id = auto_increment('user', 'user_id');
        $_user_id = ['user_id' => $user_id];
        $data = array_merge($_user_id, $_data);
    } else {
        $data = $data + compact('user_modified');
    }

    if ($update) {

        if ($user_email !== $old_user_data->user_email) {
            $data['user_activation_key'] = null;
        }

        $update = app()->db->table('user');
        $update->begin();
        try {
            $update->where('user_id', $user_id)
                    ->update($data);
            $update->commit();
        } catch (Exception $ex) {
            $update->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }
        $user_id = (int) $user_id;
    } else {

        $insert = app()->db->table('user');
        $insert->begin();
        try {
            $insert->insert($data);
            $insert->commit();
        } catch (Exception $ex) {
            $insert->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }
        $user_id = (int) $user_id;
    }

    $user = new \TriTan\User($user_id);

    /**
     * Filters a user's meta values and keys immediately after the user is created or updated
     * and before any user meta is inserted or updated.
     *
     * @since 0.9
     * @param array $meta {
     *     Default meta values and keys for the user.
     *
     *     @type string $username       The user's username
     *     @type string $fname          The user's first name.
     *     @type string $lname          The user's last name.
     *     @type string $email          The user's email.
     *     @type string $bio            The user's bio.
     *     @type string $status         The user's status.
     *     @type int    $admin_layout   The user's layout option.
     *     @type int    $admin_sidebar  The user's sidebar option.
     *     @type int    $admin_skin     The user's skin option.
     * }
     * @param object $user  User object.
     * @param bool $update  Whether the user is being updated rather than created.
     */
    $meta = app()->hook->{'apply_filter'}('insert_user_meta', $meta, $user, $update);

    // Update user meta.
    foreach ($meta as $key => $value) {
        update_user_option($user_id, $key, if_null($value));
    }

    ttcms_cache_delete($user_id, 'users');
    ttcms_cache_delete($user_login, 'userlogins');

    if ($update) {
        /**
         * Fires immediately after an existing user is updated.
         *
         * @since 0.9
         * @param int     $user_id      User ID.
         * @param object $old_user_data   Object containing user's data prior to update.
         */
        app()->hook->{'do_action'}('profile_update', $user_id, $old_user_data);
    } else {
        /**
         * Fires immediately after a new user is registered.
         *
         * @since 0.9
         * @param int $user_id User ID.
         */
        app()->hook->{'do_action'}('user_register', $user_id);
    }

    return $user_id;
}

/**
 * Update a user in the database.
 *
 * It is possible to update a user's password by specifying the 'user_pass'
 * value in the $userdata parameter array.
 * 
 * See {@see ttcms_insert_user()} For what fields can be set in $userdata.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param object|User $userdata An array of user data or a user object of type stdClass or User.
 * @return int|Exception The updated user's id or throw an Exception if the user could not be updated.
 */
function ttcms_update_user($userdata)
{
    if ($userdata instanceof \stdClass) {
        $userdata = get_object_vars($userdata);
    } elseif ($userdata instanceof \TriTan\User) {
        $userdata = $userdata->to_array();
    }

    $ID = isset($userdata['user_id']) ? (int) $userdata['user_id'] : (int) 0;
    if (!$ID) {
        throw new Exception(_t('Invalid user id.', 'tritan-cms'), 'invalid_user_id');
    }

    // First, get all of the original fields
    $user_obj = get_userdata($ID);
    if (!$user_obj) {
        throw new Exception(_t('Invalid user id.', 'tritan-cms'), 'invalid_user_id');
    }

    $user = $user_obj->to_array();

    $additional_user_keys = ['username', 'fname', 'lname', 'email', 'bio', 'role', 'status', 'admin_layout', 'admin_sidebar', 'admin_skin'];
    // Add additional custom fields
    foreach ($additional_user_keys as $key) {
        $user[$key] = get_user_option($key, (int) _escape($user['user_id']));
    }

    if (!empty($userdata['user_pass']) && $userdata['user_pass'] !== $user_obj->user_pass) {
        // If password is changing, hash it now
        $plaintext_pass = $userdata['user_pass'];
        $userdata['user_pass'] = ttcms_hash_password($userdata['user_pass']);

        /**
         * Filters whether to send the password change email.
         * 
         * @see ttcms_insert_user() For `$user` and `$userdata` fields.
         *
         * @since 0.9
         * @param bool  $send     Whether to send the email.
         * @param array $user     The original user array.
         * @param array $userdata The updated user array.
         *
         */
        $send_password_change_email = app()->hook->{'apply_filter'}('send_password_change_email', true, $user, $userdata);
    }

    if (isset($userdata['user_email']) && $user['user_email'] !== $userdata['user_email']) {
        /**
         * Filters whether to send the email change email.
         * 
         * @see ttcms_insert_user() For `$user` and `$userdata` fields.
         *
         * @since 0.9
         * @param bool  $send     Whether to send the email.
         * @param array $user     The original user array.
         * @param array $userdata The updated user array.
         *
         */
        $send_email_change_email = app()->hook->{'apply_filter'}('send_email_change_email', true, $user, $userdata);
    }

    ttcms_cache_delete(_escape($user['user_email']), 'useremail');

    // Merge old and new fields with new fields overwriting old ones.
    $userdata = array_merge($user, $userdata);
    $user_id = ttcms_insert_user($userdata);

    if (!is_ttcms_exception($user_id)) {

        if (!empty($send_password_change_email)) {
            app()->hook->{'do_action'}('password_change_email', $user, $plaintext_pass, $userdata);
        }

        if (!empty($send_email_change_email)) {
            app()->hook->{'do_action'}('email_change_email', $user, $userdata);
        }
    }

    return $user_id;
}

/**
 * Email sent to user with new generated password.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param int $user
 *            User array.
 * @param string $password
 *            Plaintext password.
 * @return bool|Error
 */
function send_reset_password_email($user, $password)
{
    $site_name = app()->hook->{'get_option'}('sitename');

    $message .= sprintf(_t("<p>Hello %s! You requested that your password be reset. Please see your new password below: <br />", 'tritan-cms'), _escape($user['user_fname']));
    $message .= sprintf(_t('Password: %s', 'tritan-cms'), $password) . "</p>";
    $message .= sprintf(_t('<p>If you still have problems with logging in, please contact us at %s.', 'tritan-cms'), app()->hook->{'get_option'}('admin_email')) . "</p>";

    $message = process_email_html($message, sprintf(_t('[%s] Password Reset', 'tritan-cms'), $site_name));
    $headers[] = sprintf("From: %s <auto-reply@%s>", $site_name, get_domain_name());
    if (!function_exists('ttcms_smtp')) {
        $headers[] = 'Content-Type: text/html; charset="UTF-8"';
        $headers[] = sprintf("X-Mailer: TriTan CMS %s", CURRENT_RELEASE);
    }
    try {
        _ttcms_email()->ttcmsMail(_escape($user['user_email']), sprintf(_t('[%s] Notice of Password Reset', 'tritan-cms'), $site_name), $message, $headers);
    } catch (\PHPMailer\PHPMailer\Exception $ex) {
        _ttcms_flash()->error($ex->getMessage());
    } catch (Exception $ex) {
        _ttcms_flash()->error($ex->getMessage());
    }
}

/**
 * Email sent to user with changed/updated password.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param int $user
 *            User array.
 * @param string $password
 *            Plaintext password.
 * @param array $userdata Updated user array.
 * @return bool|Error
 */
function send_password_change_email($user, $password, $userdata)
{
    $site_name = app()->hook->{'get_option'}('sitename');

    $message .= sprintf(_t("<p>Hello %s! This is confirmation that your password on %s was updated to: <br />", 'tritan-cms'), _escape($user['user_fname']), app()->hook->{'get_option'}('sitename'));
    $message .= sprintf(_t('Password: %s', 'tritan-cms'), $password) . "</p>";
    $message .= sprintf(_t('<p>If you did not initiate a password change/update, please contact us at %s.', 'tritan-cms'), app()->hook->{'get_option'}('admin_email')) . "</p>";

    $message = process_email_html($message, sprintf(_t('[%s] Notice of Password Change', 'tritan-cms'), $site_name));
    $headers[] = sprintf("From: %s <auto-reply@%s>", $site_name, get_domain_name());
    if (!function_exists('ttcms_smtp')) {
        $headers[] = 'Content-Type: text/html; charset="UTF-8"';
        $headers[] = sprintf("X-Mailer: TriTan CMS %s", CURRENT_RELEASE);
    }
    try {
        _ttcms_email()->ttcmsMail(_escape($user['user_email']), sprintf(_t('[%s] Notice of Password Change', 'tritan-cms'), $site_name), $message, $headers);
    } catch (\PHPMailer\PHPMailer\Exception $ex) {
        _ttcms_flash()->error($ex->getMessage());
    } catch (Exception $ex) {
        _ttcms_flash()->error($ex->getMessage());
    }
}

/**
 * Email sent to user with changed/updated email.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @param array $user       Original user array.
 * @param array $userdata   Updated user array.
 * @return bool|Error
 */
function send_email_change_email($user, $userdata)
{
    $site_name = app()->hook->{'get_option'}('sitename');

    $message .= sprintf(_t("<p>Hello %s! This is confirmation that your email on %s was updated to: <br />", 'tritan-cms'), _escape($user['user_fname']), $site_name);
    $message .= sprintf(_t('Email: %s', 'tritan-cms'), _escape($userdata['user_email'])) . "</p>";
    $message .= sprintf(_t('<p>If you did not initiate an email change/update, please contact us at %s.', 'tritan-cms'), app()->hook->{'get_option'}('admin_email')) . "</p>";

    $message = process_email_html($message, sprintf(_t('[%s] Notice of Email Change', 'tritan-cms'), $site_name));
    $headers[] = sprintf("From: %s <auto-reply@%s>", $site_name, get_domain_name());
    if (!function_exists('ttcms_smtp')) {
        $headers[] = 'Content-Type: text/html; charset="UTF-8"';
        $headers[] = sprintf("X-Mailer: TriTan CMS %s", CURRENT_RELEASE);
    }
    try {
        _ttcms_email()->ttcmsMail(_escape($userdata['user_email']), sprintf(_t('[%s] Notice of Email Change', 'tritan-cms'), $site_name), $message, $headers);
    } catch (\PHPMailer\PHPMailer\Exception $ex) {
        _ttcms_flash()->error($ex->getMessage());
    } catch (Exception $ex) {
        _ttcms_flash()->error($ex->getMessage());
    }
}

/**
 * Update user caches.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param object $user User object to be cached.
 * @return bool|null Returns false on failure.
 */
function update_user_caches($user)
{
    if ($user instanceof \TriTan\User) {
        if (!$user->exists()) {
            return false;
        }

        $user = $user;
    }

    ttcms_cache_add(_escape($user->user_id), $user, 'users');
    ttcms_cache_add(_escape($user->user_login), (int) _escape($user->user_id), 'userlogins');
    ttcms_cache_add(_escape($user->user_email), (int) _escape($user->user_id), 'useremail');
}

/**
 * Clean user caches.
 * 
 * Uses `clean_user_cache` action.
 * 
 * @file app/functions/user-function.php
 *
 * @since 0.9
 * @param object|int $user User object or user_id to be cleaned from the cache.
 */
function clean_user_cache($user)
{
    if (is_numeric($user)) {
        $user = new \TriTan\User($user);
    }

    if (!$user->exists()) {
        return;
    }

    ttcms_cache_delete(_escape($user->user_id), 'users');
    ttcms_cache_delete(_escape($user->user_login), 'userlogins');
    ttcms_cache_delete(_escape($user->user_email), 'useremail');

    /**
     * Fires immediately after the given user's cache is cleaned.
     *
     * @since 0.9
     * @param int   $user_id User user_id.
     * @param User  $user    User object.
     */
    app()->hook->{'do_action'}('clean_user_cache', _escape($user->user_id), $user);
}

/**
 * An extensive list of blacklisted usernames.
 * 
 * Uses `blacklisted_usernames` filter.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9
 * @return array Array of blacklisted usernames.
 */
function blacklisted_usernames()
{
    $blacklist = [
        '400', '401', '403', '404', '405', '406', '407', '408', '409', '410',
        '411', '412', '413', '414', '415', '416', '417', '421', '422', '423',
        '424', '426', '428', '429', '431', '500', '501', '502', '503', '504',
        '505', '506', '507', '508', '509', '510', '511', 'about', 'about-us',
        'abuse', 'access', 'account', 'accounts', 'ad', 'add', 'admin',
        'administration', 'administrator', 'ads', 'advertise', 'advertising',
        'aes128-ctr', 'aes128-gcm', 'aes192-ctr', 'aes256-ctr', 'aes256-gcm',
        'affiliate', 'affiliates', 'ajax', 'alert', 'alerts', 'alpha', 'amp',
        'analytics', 'api', 'app', 'apps', 'asc', 'assets', 'atom', 'auth',
        'authentication', 'authorize', 'autoconfig', 'avatar', 'backup',
        'banner', 'banners', 'beta', 'billing', 'billings', 'blog', 'blogs',
        'board', 'bookmark', 'bookmarks', 'broadcasthost', 'business', 'buy',
        'cache', 'calendar', 'campaign', 'captcha', 'careers', 'cart', 'cas',
        'categories', 'category', 'cdn', 'cgi', 'cgi-bin', 'chacha20-poly1305',
        'change', 'channel', 'channels', 'chart', 'chat', 'checkout', 'clear',
        'client', 'close', 'cms', 'com', 'comment', 'comments', 'community',
        'compare', 'compose', 'config', 'connect', 'contact', 'contest',
        'cookies', 'copy', 'copyright', 'count', 'create', 'css',
        'curve25519-sha256', 'customer', 'customers', 'customize', 'dashboard',
        'db', 'deals', 'debug', 'delete', 'desc', 'dev', 'developer',
        'developers', 'diffie-hellman-group-exchange-sha256',
        'diffie-hellman-group14-sha1', 'disconnect', 'discuss', 'dns', 'dns0',
        'dns1', 'dns2', 'dns3', 'dns4', 'docs', 'documentation', 'domain',
        'download', 'downloads', 'downvote', 'draft', 'drop',
        'ecdh-sha2-nistp256', 'ecdh-sha2-nistp384', 'ecdh-sha2-nistp521',
        'edit', 'editor', 'email', 'enterprise', 'error', 'errors', 'event',
        'events', 'example', 'exception', 'exit', 'explore', 'export',
        'extensions', 'false', 'family', 'faq', 'faqs', 'features', 'feed',
        'feedback', 'feeds', 'feeds', 'file', 'files', 'filter', 'follow',
        'follower', 'followers', 'following', 'fonts', 'forgot',
        'forgot-password', 'forgotpassword', 'form', 'forms', 'forum', 'forums',
        'friend', 'friends', 'ftp', 'get', 'git', 'go', 'group', 'groups',
        'guest', 'guidelines', 'guides', 'head', 'header', 'help', 'hide',
        'hmac-sha', 'hmac-sha1', 'hmac-sha1-etm', 'hmac-sha2-256',
        'hmac-sha2-256-etm', 'hmac-sha2-512', 'hmac-sha2-512-etm', 'home',
        'host', 'hosting', 'hostmaster', 'htpasswd', 'http', 'httpd', 'https',
        'icons', 'images', 'imap', 'img', 'import', 'info', 'insert',
        'investors', 'invitations', 'invite', 'invite', 'invites', 'invoice',
        'is', 'isatap', 'issues', 'it', 'jobs', 'join', 'js', 'json', 'learn',
        'legal', 'licensing', 'limit', 'live', 'load', 'local', 'localdomain',
        'localhost', 'lock', 'login', 'logout', 'lost-password', 'mail',
        'mail0', 'mail1', 'mail2', 'mail3', 'mail4', 'mail5', 'mail6', 'mail7',
        'mail8', 'mail9', 'mailer-daemon', 'mailerdaemon', 'map', 'marketing',
        'marketplace', 'master', 'me', 'media', 'member', 'members', 'message',
        'messages', 'metrics', 'mis', 'mobile', 'moderator', 'modify', 'more',
        'mx', 'my', 'net', 'network', 'new', 'news', 'newsletter',
        'newsletters', 'next', 'nil', 'no-reply', 'nobody', 'noc', 'none',
        'noreply', 'notification', 'notifications', 'ns', 'ns0', 'ns1', 'ns2',
        'ns3', 'ns4', 'ns5', 'ns6', 'ns7', 'ns8', 'ns9', 'null', 'oauth',
        'oauth2', 'offer', 'offers', 'online', 'openid', 'order', 'orders',
        'overview', 'owner', 'page', 'pages', 'partners', 'passwd', 'password',
        'pay', 'payment', 'payments', 'photo', 'photos', 'pixel', 'plans',
        'plugins', 'policies', 'policy', 'pop', 'pop3', 'popular', 'portfolio',
        'post', 'postfix', 'postmaster', 'poweruser', 'preferences', 'premium',
        'press', 'previous', 'pricing', 'print', 'privacy', 'privacy-policy',
        'private', 'prod', 'product', 'production', 'profile', 'profiles',
        'project', 'projects', 'public', 'purchase', 'put', 'quota', 'redirect',
        'reduce', 'refund', 'refunds', 'register', 'registration', 'remove',
        'replies', 'reply', 'report', 'request', 'request-password', 'reset',
        'reset-password', 'response', 'return', 'returns', 'review', 'reviews',
        'root', 'rootuser', 'rsa-sha2-2', 'rsa-sha2-512', 'rss', 'rules',
        'sales', 'save', 'script', 'sdk', 'search', 'secure', 'security',
        'select', 'services', 'session', 'sessions', 'settings', 'setup',
        'share', 'shift', 'shop', 'signin', 'signup', 'site', 'sitemap',
        'sites', 'smtp', 'sort', 'source', 'sql', 'ssh', 'ssh-rsa', 'ssl',
        'ssladmin', 'ssladministrator', 'sslwebmaster', 'stage', 'staging',
        'stat', 'static', 'statistics', 'stats', 'status', 'store', 'style',
        'styles', 'stylesheet', 'stylesheets', 'subdomain', 'subscribe', 'sudo',
        'super', 'superuser', 'support', 'survey', 'sync', 'sysadmin', 'system',
        'tablet', 'tag', 'tags', 'team', 'telnet', 'terms', 'terms-of-use',
        'test', 'testimonials', 'theme', 'themes', 'today', 'tools', 'topic',
        'topics', 'tour', 'training', 'translate', 'translations', 'trending',
        'trial', 'true', 'umac-128', 'umac-128-etm', 'umac-64', 'umac-64-etm',
        'undefined', 'unfollow', 'unsubscribe', 'update', 'upgrade', 'usenet',
        'user', 'username', 'users', 'uucp', 'var', 'verify', 'video', 'view',
        'void', 'vote', 'webmail', 'webmaster', 'website', 'widget', 'widgets',
        'wiki', 'wpad', 'write', 'www', 'www-data', 'www1', 'www2', 'www3',
        'www4', 'you', 'yourname', 'yourusername', 'zlib', 'tritan', 'ttcms'
    ];

    return app()->hook->{'apply_filter'}('blacklisted_usernames', $blacklist);
}

/**
 * Recently published widget.
 * 
 * @file app/functions/user-function.php
 * 
 * @since 0.9.8
 * @return 5 recently published posts.
 */
function recently_published_widget()
{
    $posts = get_all_posts(null, 5);
    $_posts = ttcms_list_sort($posts, 'post_created', 'DESC');

    foreach ($_posts as $post) {
        echo '<div class="text-muted">' . get_post_datetime(_escape($post['post_id'])) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . sprintf('<a href="%s">%s</a>', get_base_url() . 'admin' . '/' . _escape($post['post_type']['post_posttype']) . '/' . _escape($post['post_id']) . '/', _escape($post['post_title'])) . '</div>';
    }
}

function tritan_cms_feed_widget()
{
    $cache = new \TriTan\Cache('rss');
    if (!$cache->setCache()) :
        $rss1 = new \DOMDocument();
        $rss1->load('https://www.tritancms.com/blog/rss/');
        $feed = [];
        foreach ($rss1->getElementsByTagName('item') as $node) {
            $item = [
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
            ];
            array_push($feed, $item);
        }
        $limit = 3;
        for ($x = 0; $x < $limit; $x++) {
            $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
            $link = $feed[$x]['link'];
            $description = $feed[$x]['desc'];
            $date = date('l F d, Y', strtotime($feed[$x]['date']));
            echo '<p><strong><a href="' . $link . '" title="' . $title . '">' . $title . '</a></strong><br />';
            echo '<small><em>Posted on ' . $date . '</em></small></p>';
            echo '<p>' . $description . '</p>';
        }
    endif;
    echo $cache->getCache();
}
