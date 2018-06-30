<?php

namespace TriTan\Functions\Auth;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use TriTan\Exception\UnauthorizedException;
use TriTan\Exception\NotFoundException;
use Cascade\Cascade;
use TriTan\Functions\Core;
use TriTan\Functions\User;
use TriTan\Functions\Db;
use TriTan\Functions\Dependency;
use TriTan\Functions\Logger;

/**
 * TriTan CMS Auth Helper
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Checks the permission of the logged in user.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9.8
 * @param string $perm Permission to check for.
 * @return bool Return true if permission matches or false otherwise.
 */
function current_user_can($perm)
{
    $acl = new \TriTan\ACL(User\get_current_user_id());

    if ($acl->hasPermission($perm) && is_user_logged_in()) {
        return true;
    }
    return false;
}

/**
 * Checks the main role of the user from the user document.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param int $role_id The id of the role to check for.
 * @return bool True if role id matches or false otherwise
 */
function hasRole($role_id)
{
    $user = get_userdata(User\get_current_user_id());
    if ((int) $role_id === (int) Core\_escape($user->user_role)) {
        return true;
    }
    return false;
}

/**
 * Returns the values of a requested role.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param int $role The id of the role to check for.
 * @return array Returned values of the role.
 */
function get_role_by_id($role = 0)
{
    $sql = app()->db->table('role')
            ->where('role_id', (int) $role)
            ->first();

    $data = [];
    $data['role'] = [
        'role_id' => Core\_escape($sql['role_id']),
        'role_name' => Core\_escape($sql['role_name']),
        'role_key' => Core\_escape($sql['role_key']),
        'role_permission' => Core\_escape($sql['role_permission'])
    ];

    return app()->hook->{'apply_filter'}('role_by_id', $data, $role);
}

/**
 * Retrieve user info by user_id.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param mixed $user_id User's id.
 * @return User|false User array on success, false on failure.
 */
function get_userdata($user_id)
{
    return get_user_by('id', $user_id);
}

/**
 * Checks if a visitor is logged in or not.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @return boolean
 */
function is_user_logged_in()
{
    $user = get_user_by('id', User\get_current_user_id());

    if ('' != (int) Core\_escape($user->user_id) && app()->cookies->{'verifySecureCookie'}('TTCMS_COOKIENAME')) {
        return true;
    }

    return false;
}

/**
 * Checks if logged in user can access menu, tab, or page.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param string $perm Permission to check for.
 * @return string
 */
function ae($perm)
{
    if (!current_user_can($perm)) {
        return ' style="display:none !important;"';
    }
}

/**
 * Retrieve user info by a given field from the user's table.
 *
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param string $field The field to retrieve the user with.
 * @param int|string $value A value for $field (id, uname or email).
 */
function get_user_by($field, $value)
{
    $userdata = \TriTan\User::get_data_by($field, $value);

    if (!$userdata) {
        return false;
    }

    $user = new \TriTan\User();
    $user->init($userdata);

    return $user;
}

/**
 * Logs a user in after the login information has checked out.
 *
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param string $login User's username or email address.
 * @param string $password User's password.
 * @param string $rememberme Whether to remember the user.
 */
function ttcms_authenticate($login, $password, $rememberme)
{
    $user = app()->db->table('user')
            ->where('user_login', $login)
            ->orWhere('user_email', $login)
            ->first();

    if (false == $user) {
        Dependency\_ttcms_flash()->{'error'}(sprintf(Core\_t('Sorry, an account for <strong>%s</strong> does not exist.', 'tritan-cms'), $login), app()->req->server['HTTP_REFERER']);
        return;
    }

    $ll = app()->db->table('last_login');
    $ll->begin();
    try {
        $ll->insert([
            'last_login_id' => Db\auto_increment('last_login', 'last_login_id'),
            'site_id' => (int) Config::get('site_id'),
            'user_id' => (int) Core\_escape($user['user_id']),
            'user_ip' => (string) app()->req->server['REMOTE_ADDR'],
            'login_timestamp' => (string) format_date()
        ]);
        $ll->commit();
    } catch (Exception $ex) {
        $ll->rollback();
        Cascade::getLogger('error')->{'error'}(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $ex->getCode(), $ex->getMessage()));
    }


    /**
     * Filters the authentication cookie.
     * 
     * @since 0.9
     * @param object $_user User data object.
     * @param string $rememberme Whether to remember the user.
     * @throws Exception If $user is not a database object.
     */
    try {
        app()->hook->{'apply_filter'}('ttcms_auth_cookie', $user, $rememberme);
    } catch (UnauthorizedException $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $e->getCode(), $e->getMessage()));
    }

    Logger\ttcms_logger_activity_log_write('Authentication', 'Login', User\get_name(Core\_escape($user['user_id'])), Core\_escape($user['user_login']));

    $redirect_to = (app()->req->post['redirect_to'] != null ? app()->req->post['redirect_to'] : Core\get_base_url());
    Core\ttcms_redirect($redirect_to);
}

/**
 * Checks a user's login information.
 *
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param string $login User's username or email address.
 * @param string $password User's password.
 * @param string $rememberme Whether to remember the user.
 */
function ttcms_authenticate_user($login, $password, $rememberme)
{
    if (empty($login) || empty($password)) {

        if (empty($login)) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('<strong>ERROR</strong>: The username/email field is empty.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
        }

        if (empty($password)) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('<strong>ERROR</strong>: The password field is empty.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
        }
        return;
    }

    if (User\validate_email($login)) {
        $user = get_user_by('email', $login);

        if (false == Core\_escape($user->user_email)) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('<strong>ERROR</strong>: Invalid email address.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
            return;
        }
    } else {
        $user = get_user_by('login', $login);

        if (false == Core\_escape($user->user_login)) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('<strong>ERROR</strong>: Invalid username.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
            return;
        }
    }

    if (!Core\ttcms_check_password($password, $user->user_pass, $user->user_id)) {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('<strong>ERROR</strong>: The password you entered is incorrect.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
        return;
    }

    /**
     * Filters log in details.
     * 
     * @since 0.9
     * @param string $login User's username or email address.
     * @param string $password User's password.
     * @param string $rememberme Whether to remember the user.
     */
    $user = app()->hook->{'apply_filter'}('ttcms_authenticate_user', $login, $password, $rememberme);

    return $user;
}

/**
 * Sets auth cookie.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param array $user           User data array.
 * @param string $rememberme    Should user be remembered for a length of time?
 * @throws UnauthorizedException
 */
function ttcms_set_auth_cookie($user, $rememberme = '')
{
    if (!is_array($user)) {
        throw new UnauthorizedException(_t('"$user" should be an array.', 'tritan-cms'), 4011);
    }

    if (isset($rememberme)) {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         * 
         * @since 0.9
         */
        $expire = app()->hook->{'apply_filter'}('auth_cookie_expiration', (app()->hook->{'get_option'}('cookieexpire') !== '') ? app()->hook->{'get_option'}('cookieexpire') : app()->config('cookies.lifetime'));
    } else {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         *
         * @since 0.9
         */
        $expire = app()->hook->{'apply_filter'}('auth_cookie_expiration', (app()->config('cookies.lifetime') !== '') ? app()->config('cookies.lifetime') : 86400);
    }

    $auth_cookie = [
        'key' => 'TTCMS_COOKIENAME',
        'user_id' => (int) Core\_escape($user['user_id']),
        'user_login' => (string) Core\_escape($user['user_login']),
        'remember' => (isset($rememberme) ? $rememberme : _t('no', 'tritan-cms')),
        'exp' => (int) $expire + time()
    ];

    /**
     * Fires immediately before the secure authentication cookie is set.
     *
     * @since 0.9
     * @param string $auth_cookie Authentication cookie.
     * @param int    $expire  Duration in seconds the authentication cookie should be valid.
     */
    app()->hook->{'do_action'}('set_auth_cookie', $auth_cookie, $expire);

    app()->cookies->{'setSecureCookie'}($auth_cookie);
}

/**
 * Removes all cookies associated with authentication.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 */
function ttcms_clear_auth_cookie()
{
    /**
     * Fires just before the authentication cookies are cleared.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('clear_auth_cookie');

    $vars1 = [];
    parse_str(app()->cookies->{'get'}('TTCMS_COOKIENAME'), $vars1);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file1 = app()->config('cookies.savepath') . 'cookies.' . $vars1['data'];
    try {
        if (Core\ttcms_file_exists($file1)) {
            unlink($file1);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    $vars2 = [];
    parse_str(app()->cookies->{'get'}('SWITCH_USERBACK'), $vars2);
    /**
     * Checks to see if the cookie exists on the server.
     * It it exists, we need to delete it.
     */
    $file2 = app()->config('cookies.savepath') . 'cookies.' . $vars2['data'];
    if (Core\ttcms_file_exists($file2, false)) {
        @unlink($file2);
    }

    /**
     * After the cookie is removed from the server,
     * we know need to remove it from the browser and
     * redirect the user to the login page.
     */
    app()->cookies->{'remove'}('TTCMS_COOKIENAME');
    app()->cookies->{'remove'}('SWITCH_USERBACK');
}

/**
 * Shows error messages on login form.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 */
function ttcms_login_form_show_message()
{
    echo app()->hook->{'apply_filter'}('login_form_show_message', Dependency\_ttcms_flash()->showMessage());
}

/**
 * Retrieves data from a secure cookie.
 * 
 * @file app/functions/auth-function.php
 * 
 * @since 0.9
 * @param string $key COOKIE key.
 * @return mixed
 */
function get_secure_cookie_data($key)
{
    $data = app()->cookies->{'getSecureCookie'}($key);
    return $data;
}
