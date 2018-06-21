<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Exception\Exception;
use TriTan\Exception\NotFoundException;
use Cascade\Cascade;
use TriTan\Functions as func;

$current_user = func\get_userdata(func\get_current_user_id());

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/admin(.*)', function() {
    if (!func\is_user_logged_in()) {
        func\_ttcms_flash()->{'error'}(func\_t('401 - Error: Unauthorized.', 'tritan-cms'), func\get_base_url() . 'login' . '/');
        exit();
    }
    if (!func\current_user_can('access_admin')) {
        func\_ttcms_flash()->{'error'}(func\_t('403 - Error: Forbidden.', 'tritan-cms'), func\get_base_url());
        exit();
    }
});

$app->group('/admin', function() use ($app, $current_user) {

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/profile/', function() {
        if (!func\is_user_logged_in()) {
            func\_ttcms_flash()->{'error'}(func\_t('You must be logged in to update your profile.', 'tritan-cms'), func\get_base_url() . 'login' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/profile/', function() use($app, $current_user) {
        if ($app->req->isPost()) {
            try {

                $user_id = func\ttcms_update_user($app->req->post);
                /**
                 * Set user's role.
                 */
                $new_user = new TriTan\User((int) $user_id);
                $new_user->set_role($app->req->post['user_role']);
                
                func\clean_user_cache($app->req->post['user_id']);

                func\ttcms_logger_activity_log_write(func\_t('Update Record', 'tritan-cms'), func\_t('Profile', 'tritan-cms'), func\get_name(func\_escape($current_user->user_id)), (string) func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/user/profile/');
            } catch (Exception $ex) {
                func\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }
        $app->foil->render('main::admin/user/profile', [
            'title' => func\_t('User Profile', 'tritan-cms'),
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/', function () {
        if (!func\current_user_can('manage_users')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to manage users.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/user/', function () use($app) {

        $app->foil->render('main::admin/user/index', [
            'title' => func\_t('Manage Users', 'tritan-cms'),
            'users' => func\get_multisite_users()
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/create/', function() {
        if (!func\current_user_can('create_users')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to create users.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/create/', function () use($app, $current_user) {

        if ($app->req->isPost()) {
            $user = func\get_user_by('email', $app->req->post['user_email']);

            $password = func\ttcms_generate_password();

            if ((int) func\_escape($user->user_id) > 0) {
                $update = true;
                $user_login = func\_escape($user->user_login);
                $extra = ['user_pass' => (string) $password, 'user_login' => (string) $user_login];
            } else {
                $update = false;
                $user_login = $app->req->post['user_login'];
                $extra = ['user_pass' => (string) $password];
            }

            if (empty($user_login)) {
                func\_ttcms_flash()->{'error'}(func\_t('Username cannot be null.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
                exit();
            }

            if (!func\validate_email($app->req->post['user_email'])) {
                func\ttcms_redirect($app->req->server['HTTP_REFERER']);
                exit();
            }

            if (!func\validate_username($user_login)) {
                func\ttcms_redirect($app->req->server['HTTP_REFERER']);
                exit();
            }

            try {
                $array_merge = array_merge($extra, $app->req->post);
                $object = func\array_to_object($array_merge);
                if ($update) {
                    $user_id = func\ttcms_update_user($object);
                } else {
                    $user_id = func\ttcms_insert_user($object);
                }
                /**
                 * Set user's role.
                 */
                $new_user = new TriTan\User((int) $user_id);
                $new_user->set_role($app->req->post['user_role']);
    
                if ($app->req->post['sendemail'] == '1') {
                    func\_ttcms_email()->sendNewUserEmail((int) $user_id, $password);
                }
                func\ttcms_logger_activity_log_write('Create Record', 'User', func\get_name((int) $user_id), (string) func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/user' . '/' . (int) $user_id . '/');
            } catch (Exception $ex) {
                func\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $app->foil->render('main::admin/user/create', [
            'title' => func\_t('Create New User', 'tritan-cms')
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/', function() {
        if (!func\current_user_can('update_users')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to update users.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
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
                $user_array = array_merge(['user_id' => $id], $app->req->post);
                $user_id = func\ttcms_update_user($user_array);
                /**
                 * Set user's role.
                 */
                $user = new TriTan\User((int) $user_id);
                $user->set_role($app->req->post['user_role']);
                func\ttcms_logger_activity_log_write('Update Record', 'User', func\get_name((int) $id), (string) func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                func\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $_user = func\get_userdata((int) $id);

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
         */ elseif ((int) func\_escape($_user->user_id) <= 0) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->foil->render('main::admin/user/update', [
                'title' => func\_t('Update User', 'tritan-cms'),
                'user' => $_user
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-to/', function() use($app) {
        if (!func\current_user_can('switch_user')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to log in as another user.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/user/(\d+)/switch-to/', function ($id) use($app, $current_user) {

        if (isset($app->req->cookie['TTCMS_COOKIENAME'])) {
            $switch_cookie = [
                'key' => 'SWITCH_USERBACK',
                'user_id' => (int) func\_escape($current_user->user_id),
                'user_login' => func\_escape($current_user->user_login),
                'remember' => $app->hook->{'get_option'}('cookieexpire') - time() > 86400 ? func\_t('yes', 'tritan-cms') : func\_t('no', 'tritan-cms'),
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
            if (func\ttcms_file_exists($file)) {
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
            'user_login' => func\get_user_value((int) $id, 'user_login'),
            'remember' => time() - (int) $app->hook->{'get_option'}('cookieexpire') > 86400 ? func\_t('yes', 'tritan-cms') : func\_t('no', 'tritan-cms'),
            'exp' => (int) $app->hook->{'get_option'}('cookieexpire') + time()
        ];

        $app->cookies->setSecureCookie($auth_cookie);

        func\_ttcms_flash()->{'success'}(func\_t('User switching was successful.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-back/', function() use($app) {
        if (!func\is_user_logged_in()) {
            func\_ttcms_flash()->{'error'}(func\_t('401 - Error: Unauthorized.', 'tritan-cms'), func\get_base_url() . 'login' . '/');
            exit();
        }
        if (!isset($app->req->cookie['SWITCH_USERBACK'])) {
            func\_ttcms_flash()->{'error'}(func\_t('Cookie is not properly set for user switching', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
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
            if (func\ttcms_file_exists($file1)) {
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
            if (func\ttcms_file_exists($file2)) {
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
            'user_login' => func\get_user_value((int) $id, 'user_login'),
            'remember' => time() - (int) $app->hook->{'get_option'}('cookieexpire') > 86400 ? func\_t('yes', 'tritan-cms') : func\_t('no', 'tritan-cms'),
            'exp' => (int) $app->hook->{'get_option'}('cookieexpire') + time()
        ];
        $app->cookies->setSecureCookie($switch_cookie);
        func\_ttcms_flash()->{'success'}(func\_t('Switching back to previous session was successful.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/d/', function() use($app) {
        if (!func\current_user_can('delete_users')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to delete users.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->post('/user/(\d+)/d/', function ($id) use($app, $current_user) {

        $user = func\ttcms_delete_user($id, $app->req->post['assign_id']);

        if ($user) {
            func\ttcms_logger_activity_log_write(func\_t('Delete Record', 'tritan-cms'), func\_t('User', 'tritan-cms'), func\get_name($id), (string) func\_escape($current_user->user_login));
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        } else {
            func\_ttcms_flash()->{'error'}(func\_ttcms_flash()->notice(409), $app->req->server['HTTP_REFERER']);
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/lookup/', function() use($app) {
        if (!func\is_user_logged_in()) {
            func\_ttcms_flash()->{'error'}(func\_t("401 - Error: Unauthorized.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->match('GET|POST', '/user/lookup/', function () use($app) {
        $user = $app->db->table('user')
                ->where('user_id', $app->req->post['user_id'])
                ->first();

        $json = [
            'input#fname' => func\_escape($user['user_fname']), 'input#lname' => func\_escape($user['user_lname']),
            'input#email' => func\_escape($user['user_email'])
        ];
        echo json_encode($json);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/reset-password/', function () use($app) {
        if (!func\current_user_can('update_users')) {
            func\_ttcms_flash()->{'error'}(func\_t("You are not allowed to reset user passwords.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/user/(\d+)/reset-password/', function ($id) use($app) {
        $user = new \TriTan\User($id);
        if (!$user->exists()) {
            func\_ttcms_flash()->{'error'}(sprintf(func\_t('Requested user does not exist.'), 'tritan-cms'), $app->req->server['HTTP_REFERER']);
        }
        
        $user_id = func\reset_password($id);

        if (func\is_ttcms_exception($user_id)) {
            func\_ttcms_flash()->{'error'}(sprintf('Update error[%s]: %s', $user_id->getCode(), $user_id->getMessage()), $app->req->server['HTTP_REFERER']);
        } elseif ($user_id > 0) {
            func\_ttcms_flash()->{'success'}(sprintf(func\_t('Password successfully updated for <strong>%s</strong>.', 'tritan-cms'), func\get_name($id)), $app->req->server['HTTP_REFERER']);
        } else {
            func\_ttcms_flash()->{'error'}(sprintf(func\_t('Could not update password for <strong>%s</strong>.'), 'tritan-cms', func\get_name($id)), $app->req->server['HTTP_REFERER']);
        }
    });

    /**
     * If the requested page does not exist,
     * return a 404.
     */
    $app->setError(function() use($app) {
        $app->res->_format('json', 404);
    });
});
