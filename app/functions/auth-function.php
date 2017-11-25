<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use TriTan\Exception\UnauthorizedException;
use Cascade\Cascade;

/**
 * TriTan CMS Auth Helper
 *
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Checks the permission of the logged in user.
 * 
 * @since 1.0.0
 * @param string $perm Permission to check for.
 * @return bool Return true if permission matches or false otherwise.
 */
function hasPermission($perm)
{
    $acl = new TriTan\ACL(get_current_user_id());

    if ($acl->hasPermission($perm) && is_user_logged_in()) {
        return true;
    }
    return false;
}

/**
 * Checks the main role of the user from the user document.
 * 
 * @since 1.0.0
 * @param int $role_id The id of the role to check for.
 * @return bool True if role id matches or false otherwise
 */
function hasRole($role_id)
{
    $user = get_userdata(get_current_user_id());
    if ((int) $role_id === (int) _escape($user['user_role'])) {
        return true;
    }
    return false;
}

/**
 * Returns the values of a requested role.
 * 
 * @since 1.0.0
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
        'role_id' => _escape($sql['role_id']),
        'role_name' => _escape($sql['role_name']),
        'role_permission' => _escape($sql['role_permission'])
    ];

    return app()->hook->{'apply_filter'}('role_by_id', $data, $role);
}

/**
 * Retrieve user info by user_id.
 * 
 * @since 1.0.0
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
 * @since 1.0.0
 * @return boolean
 */
function is_user_logged_in()
{
    $user = get_user_by('id', get_current_user_id());

    if ('' != (int) _escape($user['user_id']) && app()->cookies->{'verifySecureCookie'}('TTCMS_COOKIENAME')) {
        return true;
    }

    return false;
}

/**
 * Checks if logged in user can access menu, tab, or page.
 * 
 * @since 1.0.0
 * @param string $perm Permission to check for.
 * @return string
 */
function ae($perm)
{
    if (!hasPermission($perm)) {
        return ' style="display:none !important;"';
    }
}

/**
 * Retrieve user info by a given field from the user's table.
 *
 * @since 1.0.0
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

    return $user->data;
}

/**
 * Logs a user in after the login information has checked out.
 *
 * @since 1.0.0
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
        _ttcms_flash()->{'error'}(sprintf(_t('Sorry, an account for <strong>%s</strong> does not exist.', 'tritan-cms'), $login), app()->req->server['HTTP_REFERER']);
        return;
    }

    $ll = app()->db->table('last_login');
    $ll->begin();
    try {
        $ll->insert([
            'last_login_id' => auto_increment('last_login', 'last_login_id'),
            'site_id' => (int) Config::get('site_id'),
            'user_id' => (int) _escape($user['user_id']),
            'user_ip' => (string) app()->req->server['REMOTE_ADDR'],
            'login_timestamp' => (string) \Jenssegers\Date\Date::now()
        ]);
        $ll->commit();
    } catch (Exception $ex) {
        $ll->rollback();
        Cascade::getLogger('error')->{'error'}(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $ex->getCode(), $ex->getMessage()));
    }


    /**
     * Filters the authentication cookie.
     * 
     * @since 1.0.0
     * @param object $_user User data object.
     * @param string $rememberme Whether to remember the user.
     * @throws Exception If $user is not a database object.
     */
    try {
        app()->hook->{'apply_filter'}('ttcms_auth_cookie', $user, $rememberme);
    } catch (UnauthorizedException $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $e->getCode(), $e->getMessage()));
    }

    ttcms_logger_activity_log_write('Authentication', 'Login', get_name(_escape($user['user_id'])), _escape($user['user_login']));

    $redirect_to = (app()->req->post['redirect_to'] != null ? app()->req->post['redirect_to'] : get_base_url());
    ttcms_redirect($redirect_to);
}

/**
 * Checks a user's login information.
 *
 * @since 1.0.0
 * @param string $login User's username or email address.
 * @param string $password User's password.
 * @param string $rememberme Whether to remember the user.
 */
function ttcms_authenticate_user($login, $password, $rememberme)
{
    if (empty($login) || empty($password)) {

        if (empty($login)) {
            _ttcms_flash()->{'error'}(_t('<strong>ERROR</strong>: The username/email field is empty.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
        }

        if (empty($password)) {
            _ttcms_flash()->{'error'}(_t('<strong>ERROR</strong>: The password field is empty.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
        }
        return;
    }

    if (validate_email($login)) {
        $user = get_user_by('email', $login);

        if (false == _escape($user['user_email'])) {
            _ttcms_flash()->{'error'}(_t('<strong>ERROR</strong>: Invalid email address.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
            return;
        }
    } else {
        $user = get_user_by('login', $login);

        if (false == _escape($user['user_login'])) {
            _ttcms_flash()->{'error'}(_t('<strong>ERROR</strong>: Invalid username.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
            return;
        }
    }

    if (!ttcms_check_password($password, $user['user_pass'], $user['user_id'])) {
        _ttcms_flash()->{'error'}(_t('<strong>ERROR</strong>: The password you entered is incorrect.', 'tritan-cms'), app()->req->server['HTTP_REFERER']);
        return;
    }

    /**
     * Filters log in details.
     * 
     * @since 1.0.0
     * @param string $login User's username or email address.
     * @param string $password User's password.
     * @param string $rememberme Whether to remember the user.
     */
    $user = app()->hook->{'apply_filter'}('ttcms_authenticate_user', $login, $password, $rememberme);

    return $user;
}

function ttcms_set_auth_cookie($user, $rememberme = '')
{
    if (!is_array($user)) {
        throw new UnauthorizedException(_t('"$user" should be an array.', 'tritan-cms'), 4011);
    }

    if (isset($rememberme)) {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         * 
         * @since 1.0.0
         */
        $expire = app()->hook->{'apply_filter'}('auth_cookie_expiration', (app()->hook->{'get_option'}('cookieexpire') !== '') ? app()->hook->{'get_option'}('cookieexpire') : app()->config('cookies.lifetime'));
    } else {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         *
         * @since 1.0.0
         */
        $expire = app()->hook->{'apply_filter'}('auth_cookie_expiration', (app()->config('cookies.lifetime') !== '') ? app()->config('cookies.lifetime') : 86400);
    }

    $auth_cookie = [
        'key' => 'TTCMS_COOKIENAME',
        'user_id' => (int) _escape($user['user_id']),
        'user_login' => (string) _escape($user['user_login']),
        'remember' => (isset($rememberme) ? $rememberme : _t('no', 'tritan-cms')),
        'exp' => (int) $expire + time()
    ];

    /**
     * Fires immediately before the secure authentication cookie is set.
     *
     * @since 1.0.0
     * @param string $auth_cookie Authentication cookie.
     * @param int    $expire  Duration in seconds the authentication cookie should be valid.
     */
    app()->hook->{'do_action'}('set_auth_cookie', $auth_cookie, $expire);

    app()->cookies->{'setSecureCookie'}($auth_cookie);
}

/**
 * Removes all cookies associated with authentication.
 * 
 * @since 1.0.0
 */
function ttcms_clear_auth_cookie()
{
    /**
     * Fires just before the authentication cookies are cleared.
     *
     * @since 1.0.0
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
        if (ttcms_file_exists($file1)) {
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
    if (ttcms_file_exists($file2, false)) {
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
 * @since 1.0.0
 */
function ttcms_login_form_show_message()
{
    echo app()->hook->{'apply_filter'}('login_form_show_message', _ttcms_flash()->showMessage());
}

/**
 * Retrieves data from a secure cookie.
 * 
 * @since 1.0.0
 * @param string $key COOKIE key.
 * @return mixed
 */
function get_secure_cookie_data($key)
{
    $data = app()->cookies->{'getSecureCookie'}($key);
    return $data;
}
