<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Validators;
use TriTan\Config;
use TriTan\Exception\Exception;
use TriTan\Exception\NotFoundException;
use Cascade\Cascade;

$current_user = get_userdata(get_current_user_id());

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/admin(.*)', function() {
    if (!is_user_logged_in()) {
        _ttcms_flash()->{'error'}(_t('401 - Error: Unauthorized.', 'tritan-cms'), get_base_url() . 'login' . '/');
        exit();
    }
    if (!hasPermission('access_admin')) {
        _ttcms_flash()->{'error'}(_t('403 - Error: Forbidden.', 'tritan-cms'), get_base_url());
        exit();
    }
});

$app->group('/admin', function() use ($app, $current_user) {

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/profile/', function() {
        if (!is_user_logged_in()) {
            _ttcms_flash()->{'error'}(_t('You must be logged in to update your profile.', 'tritan-cms'), get_base_url() . 'login' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/profile/', function() use($app) {
        if ($app->req->isPost()) {
            try {
                $post = $app->req->post + compact('user_id');
                $user_id = get_current_user_id();
                ttcms_update_user($post);

                unset($post['user_pass']);
                unset($post['user_id']);
                foreach ($post as $key => $value) {
                    update_user_option((int) $user_id, $key, if_null($value));
                }

                clean_user_cache($user_id);

                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/user/profile/');
            } catch (Exception $ex) {
                _ttcms_flash()->{'error'}($ex->getMessage());
            }
        }
        $app->foil->render('main::admin/user/profile', [
            'title' => _t('User Profile', 'tritan-cms'),
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/', function () {
        if (!hasPermission('manage_users')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to manage users.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/user/', function () use($app) {

        $app->foil->render('main::admin/user/index', [
            'title' => _t('Manage Users', 'tritan-cms'),
            'users' => get_multisite_users()
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/create/', function() {
        if (!hasPermission('create_users')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to create users.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/create/', function () use($app, $current_user) {

        if ($app->req->isPost()) {
            $user = get_user_by('email', $app->req->post['user_email']);

            $password = ttcms_generate_password();

            if ((int) _escape($user->user_id) > 0) {
                $update = true;
                $user_login = _escape($user->user_login);
                $extra = ['user_pass' => (string) $password, 'user_login' => (string) $user_login];
            } else {
                $update = false;
                $user_login = $app->req->post['user_login'];
                $extra = ['user_pass' => (string) $password];
            }

            if (empty($user_login)) {
                _ttcms_flash()->{'error'}(_t('Username cannot be null.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
                exit();
            }

            if (!validate_email($app->req->post['user_email'])) {
                ttcms_redirect($app->req->server['HTTP_REFERER']);
                exit();
            }

            if (!validate_username($user_login)) {
                ttcms_redirect($app->req->server['HTTP_REFERER']);
                exit();
            }

            try {
                $array_merge = array_merge($extra, $app->req->post);
                $object = array_to_object($array_merge);
                if ($update) {
                    $user_id = ttcms_update_user($object);
                } else {
                    $user_id = ttcms_insert_user($object);
                }

                if ($app->req->post['sendemail'] == '1') {
                    _ttcms_email()->sendNewUserEmail((int) $user_id, $password);
                }
                ttcms_logger_activity_log_write('New Record', 'User', get_name((int) $user_id), (string) _escape($current_user->user_login));
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/user' . '/' . (int) $user_id . '/');
            } catch (Exception $ex) {
                _ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $app->foil->render('main::admin/user/create', [
            'title' => _t('Create New User', 'tritan-cms')
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/', function() {
        if (!hasPermission('update_users')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to update users.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/(\d+)/', function ($id) use($app, $current_user) {

        if ($app->req->isPost()) {
            /**
             * Fires before user record is updated.
             *
             * @since 0.9
             * @param int $id
             *            User's id.
             */
            $app->hook->{'do_action'}('pre_update_user', (int) $id);

            try {
                $user = array_merge(['user_id' => $id], $app->req->post);
                ttcms_update_user($user);
                ttcms_logger_activity_log_write('Update Record', 'User', get_name((int) $id), (string) _escape($current_user->user_login));
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                _ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $_user = get_userdata((int) $id);

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($_user == false) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($_user) == true) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If data is zero, 404 not found.
         */ elseif ((int) _escape($_user->user_id) <= 0) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->foil->render('main::admin/user/update', [
                'title' => _t('Update User', 'tritan-cms'),
                'user' => $_user
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-to/', function() use($app) {
        if (!hasPermission('switch_user')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to log in as another user.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/user/(\d+)/switch-to/', function ($id) use($app, $current_user) {

        if (isset($app->req->cookie['TTCMS_COOKIENAME'])) {
            $switch_cookie = [
                'key' => 'SWITCH_USERBACK',
                'user_id' => (int) get_current_user_id(),
                'user_login' => _escape($current_user->user_login),
                'remember' => $app->hook->{'get_option'}('cookieexpire') - time() > 86400 ? _t('yes', 'tritan-cms') : _t('no', 'tritan-cms'),
                'exp' => (int) $app->hook->{'get_option'}('cookieexpire') + time()
            ];
            $app->cookies->setSecureCookie($switch_cookie);
        }

        $vars = [];
        parse_str($app->cookies->get('TTCMS_COOKIENAME'), $vars);
        /**
         * Checks to see if the cookie is exists on the server.
         * It it exists, we need to delete it.
         */
        $file = $app->config('cookies.savepath') . 'cookies.' . $vars['data'];
        try {
            if (ttcms_file_exists($file)) {
                unlink($file);
            }
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
        }

        /**
         * Delete the old cookie.
         */
        $app->cookies->remove("TTCMS_COOKIENAME");

        $auth_cookie = [
            'key' => 'TTCMS_COOKIENAME',
            'user_id' => (int) $id,
            'user_login' => get_user_value((int) $id, 'user_login'),
            'remember' => time() - (int) $app->hook->{'get_option'}('cookieexpire') > 86400 ? _t('yes', 'tritan-cms') : _t('no', 'tritan-cms'),
            'exp' => (int) $app->hook->{'get_option'}('cookieexpire') + time()
        ];

        $app->cookies->setSecureCookie($auth_cookie);

        _ttcms_flash()->{'success'}(_t('User switching was successful.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-back/', function() use($app) {
        if (!is_user_logged_in()) {
            _ttcms_flash()->{'error'}(_t('401 - Error: Unauthorized.', 'tritan-cms'), get_base_url() . 'login' . '/');
            exit();
        }
        if (!isset($app->req->cookie['SWITCH_USERBACK'])) {
            _ttcms_flash()->{'error'}(_t('Cookie is not properly set for user switching', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/user/(\d+)/switch-back/', function ($id) use($app) {
        $vars1 = [];
        parse_str($app->cookies->get('TTCMS_COOKIENAME'), $vars1);
        /**
         * Checks to see if the cookie is exists on the server.
         * It it exists, we need to delete it.
         */
        $file1 = $app->config('cookies.savepath') . 'cookies.' . $vars1['data'];
        try {
            if (ttcms_file_exists($file1)) {
                unlink($file1);
            }
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
        }

        $app->cookies->remove("TTCMS_COOKIENAME");

        $vars2 = [];
        parse_str($app->cookies->get('SWITCH_USERBACK'), $vars2);
        /**
         * Checks to see if the cookie is exists on the server.
         * It it exists, we need to delete it.
         */
        $file2 = $app->config('cookies.savepath') . 'cookies.' . $vars2['data'];
        try {
            if (ttcms_file_exists($file2)) {
                unlink($file2);
            }
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
        }

        $app->cookies->remove("SWITCH_USERBACK");

        /**
         * After the login as user cookies have been
         * removed from the server and the browser,
         * we need to set fresh cookies for the
         * original logged in user.
         */
        $switch_cookie = [
            'key' => 'TTCMS_COOKIENAME',
            'user_id' => (int) $id,
            'user_login' => get_user_value((int) $id, 'user_login'),
            'remember' => time() - (int) $app->hook->{'get_option'}('cookieexpire') > 86400 ? _t('yes', 'tritan-cms') : _t('no', 'tritan-cms'),
            'exp' => (int) $app->hook->{'get_option'}('cookieexpire') + time()
        ];
        $app->cookies->setSecureCookie($switch_cookie);
        _ttcms_flash()->{'success'}(_t('Switching back to previous session was successful.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/d/', function() use($app) {
        if (!hasPermission('delete_users')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to delete users.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/user/(\d+)/d/', function ($id) use($app) {
        if ((int) $id == (int) '1') {
            _ttcms_flash()->{'error'}(_t('You are not allowed to delete the super administrator account.', 'tritan-cms'), get_base_url() . 'user/');
            exit();
        }

        $tbl_prefix = Config::get('tbl_prefix');

        $check = $app->db->table('usermeta')
                ->where('user_id', (int) $id)
                ->where('meta_key', 'match', "/$tbl_prefix/")
                ->count();

        if ((int) $check > 0) {
            $user = $app->db->table('usermeta');
            $user->begin();
            try {

                $user->where('user_id', (int) $id)
                        ->where('meta_key', 'match', "/$tbl_prefix/")
                        ->delete();

                $user->commit();
                clean_user_cache($id);
                ttcms_cache_flush_namespace('user_meta');
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $e) {
                $user->rollback();
                _ttcms_flash()->{'error'}($e->getMessage(), $app->req->server['HTTP_REFERER']);
            }
        } else {
            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/lookup/', function() use($app) {
        if (!is_user_logged_in()) {
            _ttcms_flash()->{'error'}(_t("401 - Error: Unauthorized.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->match('GET|POST', '/user/lookup/', function () use($app) {
        $user = $app->db->table('user')
                ->where('user_id', $app->req->post['user_id'])
                ->first();

        $json = [
            'input#fname' => _escape($user['user_fname']), 'input#lname' => _escape($user['user_lname']),
            'input#email' => _escape($user['user_email'])
        ];
        echo json_encode($json);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/reset-password/', function () use($app) {
        if (!hasPermission('update_users')) {
            _ttcms_flash()->{'error'}(_t("You are not allowed to reset user passwords.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/user/(\d+)/reset-password/', function ($id) use($app) {
        $password = ttcms_generate_password();
        $data = ['user_id' => $id, 'user_pass' => $password];

        try {
            $user = ttcms_update_user($data);

            if ($user > 0) {
                _ttcms_flash()->{'success'}(sprintf(_t('Password successfully updated for <strong>%s</strong>.'), get_name($id)));
            } else {
                _ttcms_flash()->{'error'}(_t('Could not update password.'));
            }
        } catch (Exception $ex) {
            _ttcms_flash()->{'error'}($ex->getMessage());
        }

        ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * If the requested page does not exist,
     * return a 404.
     */
    $app->setError(function() use($app) {
        $app->res->_format('json', 404);
    });
});
