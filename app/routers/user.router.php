<?php
use TriTan\Exception\Exception;
use TriTan\Exception\NotFoundException;
use Cascade\Cascade;
use TriTan\Functions\Dependency;
use TriTan\Functions\Auth;
use TriTan\Functions\User;
use TriTan\Functions\Core;
use TriTan\Functions\Logger;
use TriTan\Functions\Site;

$current_user = Auth\get_userdata(User\get_current_user_id());

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/admin(.*)', function () {
    if (!Auth\is_user_logged_in()) {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('401 - Error: Unauthorized.', 'tritan-cms'), Core\get_base_url() . 'login' . '/');
        exit();
    }
    if (!Auth\current_user_can('access_admin')) {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('403 - Error: Forbidden.', 'tritan-cms'), Core\get_base_url());
        exit();
    }
});

$app->group('/admin', function () use ($app, $current_user) {

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/profile/', function () {
        if (!Auth\is_user_logged_in()) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You must be logged in to update your profile.', 'tritan-cms'), Core\get_base_url() . 'login' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/profile/', function () use ($app, $current_user) {
        if ($app->req->isPost()) {
            try {
                $user_id = User\ttcms_update_user($app->req->post);
                /**
                 * Set user's role.
                 */
                $new_user = new TriTan\User((int) $user_id);
                $new_user->set_role($app->req->post['user_role']);

                User\clean_user_cache($app->req->post['user_id']);

                Logger\ttcms_logger_activity_log_write(Core\_t('Update Record', 'tritan-cms'), Core\_t('Profile', 'tritan-cms'), User\get_name(Core\_escape($current_user->user_id)), (string) Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/user/profile/');
            } catch (Exception $ex) {
                Dependency\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }
        $app->foil->render(
            'main::admin/user/profile',
            [
            'title' => Core\_t('User Profile', 'tritan-cms'),
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/', function () {
        if (!Auth\current_user_can('manage_users')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("You don't have permission to manage users.", 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/user/', function () use ($app) {
        $app->foil->render(
            'main::admin/user/index',
            [
            'title' => Core\_t('Manage Users', 'tritan-cms'),
            'users' => Site\get_multisite_users()
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/create/', function () {
        if (!Auth\current_user_can('create_users')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("You don't have permission to create users.", 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/create/', function () use ($app, $current_user) {
        if ($app->req->isPost()) {
            $user = Auth\get_user_by('email', $app->req->post['user_email']);

            $password = Core\ttcms_generate_password();

            if ((int) Core\_escape($user->user_id) > 0) {
                $update = true;
                $user_login = Core\_escape($user->user_login);
                $extra = ['user_pass' => (string) $password, 'user_login' => (string) $user_login];
            } else {
                $update = false;
                $user_login = $app->req->post['user_login'];
                $extra = ['user_pass' => (string) $password];
            }

            if (empty($user_login)) {
                Dependency\_ttcms_flash()->{'error'}(Core\_t('Username cannot be null.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
                exit();
            }

            if (!User\validate_email($app->req->post['user_email'])) {
                Core\ttcms_redirect($app->req->server['HTTP_REFERER']);
                exit();
            }

            if (!User\validate_username($user_login)) {
                Core\ttcms_redirect($app->req->server['HTTP_REFERER']);
                exit();
            }

            try {
                $array_merge = array_merge($extra, $app->req->post);
                $object = Core\array_to_object($array_merge);
                if ($update) {
                    $user_id = User\ttcms_update_user($object);
                } else {
                    $user_id = User\ttcms_insert_user($object);
                }
                /**
                 * Set user's role.
                 */
                $new_user = new TriTan\User((int) $user_id);
                $new_user->set_role($app->req->post['user_role']);

                if ($app->req->post['sendemail'] == '1') {
                    Dependency\_ttcms_email()->sendNewUserEmail((int) $user_id, $password);
                }
                Logger\ttcms_logger_activity_log_write('Create Record', 'User', User\get_name((int) $user_id), (string) Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/user' . '/' . (int) $user_id . '/');
            } catch (Exception $ex) {
                Dependency\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $app->foil->render(
            'main::admin/user/create',
            [
            'title' => Core\_t('Create New User', 'tritan-cms')
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/', function () {
        if (!Auth\current_user_can('update_users')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("You don't have permission to update users.", 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/user/(\d+)/', function ($id) use ($app, $current_user) {
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
                $user_id = User\ttcms_update_user($user_array);
                /**
                 * Set user's role.
                 */
                $user = new TriTan\User((int) $user_id);
                $user->set_role($app->req->post['user_role']);
                Logger\ttcms_logger_activity_log_write('Update Record', 'User', User\get_name((int) $id), (string) Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                Dependency\_ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $_user = Auth\get_userdata((int) $id);

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
         */ elseif ((int) Core\_escape($_user->user_id) <= 0) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {
            $app->foil->render(
                'main::admin/user/update',
                [
                'title' => Core\_t('Update User', 'tritan-cms'),
                'user' => $_user
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-to/', function () use ($app) {
        if (!Auth\current_user_can('switch_user')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("You don't have permission to log in as another user.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/user/(\d+)/switch-to/', function ($id) use ($app, $current_user) {
        if (isset($app->req->cookie['TTCMS_COOKIENAME'])) {
            $switch_cookie = [
                'key' => 'SWITCH_USERBACK',
                'user_id' => (int) Core\_escape($current_user->user_id),
                'user_login' => Core\_escape($current_user->user_login),
                'remember' => $app->hook->{'get_option'}('cookieexpire') - time() > 86400 ? Core\_t('yes', 'tritan-cms') : Core\_t('no', 'tritan-cms'),
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
            if (Core\ttcms_file_exists($file)) {
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
            'user_login' => User\get_user_value((int) $id, 'user_login'),
            'remember' => time() - (int) $app->hook->{'get_option'}('cookieexpire') > 86400 ? Core\_t('yes', 'tritan-cms') : Core\_t('no', 'tritan-cms'),
            'exp' => (int) $app->hook->{'get_option'}('cookieexpire') + time()
        ];

        $app->cookies->setSecureCookie($auth_cookie);

        Dependency\_ttcms_flash()->{'success'}(Core\_t('User switching was successful.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-back/', function () use ($app) {
        if (!Auth\is_user_logged_in()) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('401 - Error: Unauthorized.', 'tritan-cms'), Core\get_base_url() . 'login' . '/');
            exit();
        }
        if (!isset($app->req->cookie['SWITCH_USERBACK'])) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('Cookie is not properly set for user switching', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/user/(\d+)/switch-back/', function ($id) use ($app) {
        $vars1 = [];
        parse_str($app->cookies->get('TTCMS_COOKIENAME'), $vars1);
        /**
         * Checks to see if the cookie is exists on the server.
         * It it exists, we need to delete it.
         */
        $file1 = $app->config('cookies.savepath') . 'cookies.' . $vars1['data'];
        try {
            if (Core\ttcms_file_exists($file1)) {
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
            if (Core\ttcms_file_exists($file2)) {
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
            'user_login' => User\get_user_value((int) $id, 'user_login'),
            'remember' => time() - (int) $app->hook->{'get_option'}('cookieexpire') > 86400 ? Core\_t('yes', 'tritan-cms') : Core\_t('no', 'tritan-cms'),
            'exp' => (int) $app->hook->{'get_option'}('cookieexpire') + time()
        ];
        $app->cookies->setSecureCookie($switch_cookie);
        Dependency\_ttcms_flash()->{'success'}(Core\_t('Switching back to previous session was successful.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/d/', function () use ($app) {
        if (!Auth\current_user_can('delete_users')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("You don't have permission to delete users.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->post('/user/(\d+)/d/', function ($id) use ($app, $current_user) {
        $user = User\ttcms_delete_user($id, $app->req->post['assign_id']);

        if ($user) {
            Logger\ttcms_logger_activity_log_write(Core\_t('Delete Record', 'tritan-cms'), Core\_t('User', 'tritan-cms'), User\get_name($id), (string) Core\_escape($current_user->user_login));
            Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        } else {
            Dependency\_ttcms_flash()->{'error'}(Dependency\_ttcms_flash()->notice(409), $app->req->server['HTTP_REFERER']);
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/lookup/', function () use ($app) {
        if (!Auth\is_user_logged_in()) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("401 - Error: Unauthorized.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->match('GET|POST', '/user/lookup/', function () use ($app) {
        $user = $app->db->table('user')
                ->where('user_id', $app->req->post['user_id'])
                ->first();

        $json = [
            'input#fname' => Core\_escape($user['user_fname']), 'input#lname' => Core\_escape($user['user_lname']),
            'input#email' => Core\_escape($user['user_email'])
        ];
        echo json_encode($json);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/reset-password/', function () use ($app) {
        if (!Auth\current_user_can('update_users')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("You are not allowed to reset user passwords.", 'tritan-cms'), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/user/(\d+)/reset-password/', function ($id) use ($app) {
        $user = new \TriTan\User($id);
        if (!$user->exists()) {
            Dependency\_ttcms_flash()->{'error'}(sprintf(Core\_t('Requested user does not exist.'), 'tritan-cms'), $app->req->server['HTTP_REFERER']);
        }

        $user_id = User\reset_password($id);

        if (Core\is_ttcms_exception($user_id)) {
            Dependency\_ttcms_flash()->{'error'}(sprintf('Update error[%s]: %s', $user_id->getCode(), $user_id->getMessage()), $app->req->server['HTTP_REFERER']);
        } elseif ($user_id > 0) {
            Dependency\_ttcms_flash()->{'success'}(sprintf(Core\_t('Password successfully updated for <strong>%s</strong>.', 'tritan-cms'), User\get_name($id)), $app->req->server['HTTP_REFERER']);
        } else {
            Dependency\_ttcms_flash()->{'error'}(sprintf(Core\_t('Could not update password for <strong>%s</strong>.'), 'tritan-cms', User\get_name($id)), $app->req->server['HTTP_REFERER']);
        }
    });

    /**
     * If the requested page does not exist,
     * return a 404.
     */
    $app->setError(function () use ($app) {
        $app->res->_format('json', 404);
    });
});
