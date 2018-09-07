<?php
use TriTan\Container as c;
use TriTan\Exception\Exception;
use TriTan\Exception\UnauthorizedException;
use TriTan\Exception\NotFoundException;
use Cascade\Cascade;
use TriTan\Database;
use TriTan\Common\Hooks\ActionFilterHook as hook;

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
function current_user_can($perm): bool
{
    $acl = new \TriTan\Common\User\UserPermissionRepository(
        new TriTan\Common\User\UserPermissionMapper(
            new Database(),
            new \TriTan\Common\Context\HelperContext()
        )
    );

    if ($acl->{'has'}($perm) && is_user_logged_in()) {
        return true;
    }
    return false;
}

/**
 * Checks the role of the logged in user.
 *
 * @file app/functions/auth-function.php
 *
 * @since 0.9.9
 * @param string $role The role to check for.
 * @return bool True if user has role, false otherwise.
 */
function current_user_has_role(string $role)
{
    return (
        new TriTan\Common\User\UserRoleRepository(
            new \TriTan\Common\User\UserRoleMapper(
                new Database(),
                new \TriTan\Common\Context\HelperContext()
            )
        )
    )->{'has'}($role);
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
    $repo = (
        new TriTan\Common\Acl\RoleRepository(
            new TriTan\Common\Acl\RoleMapper(
                new Database(),
                new \TriTan\Common\Context\HelperContext()
            )
        )
    )->{'findById'}((int) $role);

    $data = [];
    $data['role'] = [
        'role_id' => $repo->getId(),
        'role_name' => $repo->getName(),
        'role_key' => $repo->getKey(),
        'role_permission' => $repo->getPermission()
    ];

    return hook::getInstance()->{'applyFilter'}('role_by_id', $data, $role);
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
function is_user_logged_in(): bool
{
    $user = get_user_by('id', get_current_user_id());
    return false != $user && ttcms()->obj['app']->cookies->{'verifySecureCookie'}('TTCMS_COOKIENAME');
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
    $userdata = (
            new \TriTan\Common\User\UserRepository(
                new \TriTan\Common\User\UserMapper(
                    new Database(),
                    new \TriTan\Common\Context\HelperContext()
                )
            ))
            ->{'findBy'}($field, $value);

    if (!$userdata) {
        return false;
    }

    return $userdata;
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
    $db = new \TriTan\Database();
    $user = $db->table('user')
            ->where('user_login', $login)
            ->orWhere('user_email', $login)
            ->first();

    if (false == $user) {
        ttcms()->obj['flash']->{'error'}(
            sprintf(
                t__(
                    'Sorry, an account for <strong>%s</strong> does not exist.'
                ),
                $login
            ),
            ttcms()->obj['app']->req->server['HTTP_REFERER']
        );
        return;
    }

    $ll = $db->table('last_login');
    $ll->begin();
    try {
        $ll->insert([
            'site_id' => (int) c::getInstance()->get('site_id'),
            'user_id' => (int) esc_html($user['user_id']),
            'user_ip' => (string) ttcms()->obj['app']->req->server['REMOTE_ADDR'],
            'login_timestamp' => (string) ttcms()->obj['date']->{'current'}('laci')
        ]);
        $ll->commit();
    } catch (Exception $ex) {
        $ll->rollback();
        Cascade::getLogger('error')->{'error'}(
            sprintf(
                'AUTHSTATE[%s]: Unauthorized: %s',
                $ex->getCode(),
                $ex->getMessage()
            )
        );
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
        hook::getInstance()->{'applyFilter'}('ttcms_auth_cookie', $user, $rememberme);
    } catch (UnauthorizedException $e) {
        Cascade::getLogger('error')->{'error'}(
            sprintf(
                'AUTHSTATE[%s]: Unauthorized: %s',
                $e->getCode(),
                $e->getMessage()
            )
        );
    }

    ttcms_logger_activity_log_write(
        'Authentication',
        'Login',
        get_name((int) esc_html($user['user_id'])),
        esc_html($user['user_login'])
    );

    $redirect_to = (ttcms()->obj['app']->req->post['redirect_to'] != null ? ttcms()->obj['app']->req->post['redirect_to'] : site_url());
    ttcms()->obj['uri']->{'redirect'}($redirect_to);
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
            ttcms()->obj['flash']->{'error'}(
                t__(
                    '<strong>ERROR</strong>: The username/email field is empty.'
                ),
                ttcms()->obj['app']->req->server['HTTP_REFERER']
            );
        }

        if (empty($password)) {
            ttcms()->obj['flash']->{'error'}(
                t__(
                    '<strong>ERROR</strong>: The password field is empty.'
                ),
                ttcms()->obj['app']->req->server['HTTP_REFERER']
            );
        }
        return;
    }

    if (validate_email($login)) {
        $user = get_user_by('email', $login);

        if (false == $user) {
            ttcms()->obj['flash']->{'error'}(
                t__(
                    '<strong>ERROR</strong>: Invalid email address.'
                ),
                ttcms()->obj['app']->req->server['HTTP_REFERER']
            );
            return;
        }
    } else {
        $user = get_user_by('login', $login);

        if (false == $user) {
            ttcms()->obj['flash']->{'error'}(
                t__(
                    '<strong>ERROR</strong>: Invalid username.'
                ),
                ttcms()->obj['app']->req->server['HTTP_REFERER']
            );
            return;
        }
    }

    $auth = new TriTan\Common\Password\PasswordCheck(
        new \TriTan\Common\Password\PasswordSetMapper(
            new Database(),
            new \TriTan\Common\Password\PasswordHash(
                hook::getInstance()
            )
        ),
        new \TriTan\Common\Password\PasswordHash(
            hook::getInstance()
        ),
        hook::getInstance()
    );

    if (!$auth->{'check'}($password, $user->getPassword(), $user->getId())) {
        ttcms()->obj['flash']->{'error'}(
            t__(
                '<strong>ERROR</strong>: The password you entered is incorrect.'
            ),
            ttcms()->obj['app']->req->server['HTTP_REFERER']
        );
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
    $user = hook::getInstance()->{'applyFilter'}('ttcms_authenticate_user', $login, $password, $rememberme);

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
        throw new UnauthorizedException(esc_html__('"$user" should be an array.'), 4011);
    }

    if (isset($rememberme)) {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         *
         * @since 0.9
         */
        $expire = hook::getInstance()->{'applyFilter'}('auth_cookie_expiration', (c::getInstance()->get('option')->{'read'}('cookieexpire') !== '') ? c::getInstance()->get('option')->{'read'}('cookieexpire') : ttcms()->obj['app']->config('cookies.lifetime'));
    } else {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         *
         * @since 0.9
         */
        $expire = hook::getInstance()->{'applyFilter'}('auth_cookie_expiration', (ttcms()->obj['app']->config('cookies.lifetime') !== '') ? ttcms()->obj['app']->config('cookies.lifetime') : 86400);
    }

    $auth_cookie = [
        'key' => 'TTCMS_COOKIENAME',
        'user_id' => (int) esc_html($user['user_id']),
        'user_login' => (string) esc_html($user['user_login']),
        'remember' => (isset($rememberme) ? $rememberme : esc_html__('no')),
        'exp' => (int) $expire + time()
    ];

    /**
     * Fires immediately before the secure authentication cookie is set.
     *
     * @since 0.9
     * @param string $auth_cookie Authentication cookie.
     * @param int    $expire  Duration in seconds the authentication cookie should be valid.
     */
    hook::getInstance()->{'doAction'}('set_auth_cookie', $auth_cookie, $expire);

    ttcms()->obj['app']->cookies->{'setSecureCookie'}($auth_cookie);
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
    hook::getInstance()->{'doAction'}('clear_auth_cookie');

    $vars1 = [];
    parse_str(ttcms()->obj['app']->cookies->{'get'}('TTCMS_COOKIENAME'), $vars1);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file1 = ttcms()->obj['app']->config('cookies.savepath') . 'cookies.' . $vars1['data'];
    try {
        if (ttcms()->obj['file']->{'exists'}($file1)) {
            unlink($file1);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->{'error'}(
            sprintf(
                'FILESTATE[%s]: File not found: %s',
                $e->getCode(),
                $e->getMessage()
            )
        );
    }

    $vars2 = [];
    parse_str(ttcms()->obj['app']->cookies->{'get'}('SWITCH_USERBACK'), $vars2);
    /**
     * Checks to see if the cookie exists on the server.
     * It it exists, we need to delete it.
     */
    $file2 = ttcms()->obj['app']->config('cookies.savepath') . 'cookies.' . $vars2['data'];
    if (ttcms()->obj['file']->{'exists'}($file2, false)) {
        @unlink($file2);
    }

    /**
     * After the cookie is removed from the server,
     * we know need to remove it from the browser and
     * redirect the user to the login page.
     */
    ttcms()->obj['app']->cookies->{'remove'}('TTCMS_COOKIENAME');
    ttcms()->obj['app']->cookies->{'remove'}('SWITCH_USERBACK');
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
    echo hook::getInstance()->{'applyFilter'}('login_form_show_message', ttcms()->obj['flash']->showMessage());
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
    $data = ttcms()->obj['app']->cookies->{'getSecureCookie'}($key);
    return $data;
}
