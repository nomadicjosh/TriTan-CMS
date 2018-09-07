<?php
use TriTan\Exception\Exception;
use TriTan\Exception\NotFoundException;
use Cascade\Cascade;
use TriTan\Common\Hooks\ActionFilterHook as hook;

$db = new \TriTan\Database();
$opt = new \TriTan\Common\Options\Options(
    new TriTan\Common\Options\OptionsMapper(
        $db,
        new TriTan\Common\Context\HelperContext()
    )
);

$current_user = get_userdata(get_current_user_id());

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/admin(.*)', function () {
    if (!is_user_logged_in()) {
        ttcms()->obj['flash']->{'error'}(
            t__('401 - Error: Unauthorized.', 'tritan-cms'),
            login_url()
        );
        exit();
    }
    if (!current_user_can('access_admin')) {
        ttcms()->obj['flash']->{'error'}(
            t__('403 - Error: Forbidden.', 'tritan-cms'),
            site_url()
        );
        exit();
    }
});

$app->group('/admin', function () use ($app, $opt, $db, $current_user) {

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/profile/', function () {
        if (!is_user_logged_in()) {
            ttcms()->obj['flash']->{'error'}(
                t__('You must be logged in to update your profile.', 'tritan-cms'),
                login_url()
            );
            exit();
        }
    });

    $app->match('GET|POST', '/user/profile/', function () use ($app, $current_user) {
        if ($app->req->isPost()) {
            try {
                $user_id = ttcms_update_user($app->req->post);
                /**
                 * Set user's role.
                 */
                $new_user = new TriTan\Common\User\User();
                $new_user->setId((int) $user_id);
                $new_user->setRole($app->req->post['user_role']);

                ttcms()->obj['usercache']->{'clean'}($app->req->post['user_id']);

                ttcms_logger_activity_log_write(
                    t__('Update Record', 'tritan-cms'),
                    t__('Profile', 'tritan-cms'),
                    get_name((int) esc_html($current_user->getId())),
                    (string) esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    admin_url('user/profile/')
                );
            } catch (Exception $ex) {
                ttcms()->obj['flash']->{'error'}($ex->getMessage());
            }
        }
        $app->foil->render(
            'main::admin/user/profile',
            [
                'title' => t__('User Profile', 'tritan-cms'),
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/', function () {
        if (!current_user_can('manage_users')) {
            ttcms()->obj['flash']->{'error'}(
                t__("You don't have permission to manage users.", 'tritan-cms'),
                admin_url()
            );
            exit();
        }
    });

    $app->get('/user/', function () use ($app) {
        $app->foil->render(
            'main::admin/user/index',
            [
                'title' => t__('Manage Users', 'tritan-cms'),
                'users' => get_multisite_users()
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/create/', function () {
        if (!current_user_can('create_users')) {
            ttcms()->obj['flash']->{'error'}(
                t__("You don't have permission to create users.", 'tritan-cms'),
                admin_url()
            );
            exit();
        }
    });

    $app->match('GET|POST', '/user/create/', function () use ($app, $current_user) {
        if ($app->req->isPost()) {
            $user = get_user_by('email', $app->req->post['user_email']);

            $password = $app->req->post['user_pass'];

            if (!$user) {
                $update = false;
                $user_login = $app->req->post['user_login'];
                $extra = ['user_pass' => (string) $password];
            } else {
                $update = true;
                $user_login = esc_html($user->getLogin());
                $extra = ['user_pass' => (string) $password, 'user_login' => (string) $user_login];
            }

            if (empty($user_login)) {
                ttcms()->obj['flash']->{'error'}(
                    t__('Username cannot be null.', 'tritan-cms'),
                    $app->req->server['HTTP_REFERER']
                );
                exit();
            }

            if (!validate_email($app->req->post['user_email'])) {
                (new TriTan\Common\Uri(hook::getInstance()))->{'redirect'}($app->req->server['HTTP_REFERER']);
                exit();
            }

            if (!validate_username($user_login)) {
                (new TriTan\Common\Uri(hook::getInstance()))->{'redirect'}($app->req->server['HTTP_REFERER']);
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
                /**
                 * Set user's role.
                 */
                $new_user = new TriTan\Common\User\User();
                $new_user->setId((int) $user_id);
                $new_user->setRole($app->req->post['user_role']);

                if ($app->req->post['sendemail'] == '1') {
                    send_new_user_email((int) $user_id, $password);
                }
                ttcms_logger_activity_log_write(
                    t__('Create Record'),
                    t__('User'),
                    get_name((int) $user_id),
                    (string) esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    admin_url('user' . '/' . (int) $user_id . '/')
                );
            } catch (Exception $ex) {
                ttcms()->obj['flash']->{'error'}($ex->getMessage());
            }
        }

        $app->foil->render(
            'main::admin/user/create',
            [
                'title' => t__('Create New User', 'tritan-cms')
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/', function () {
        if (!current_user_can('update_users')) {
            ttcms()->obj['flash']->{'error'}(
                t__("You don't have permission to update users.", 'tritan-cms'),
                admin_url()
            );
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
            hook::getInstance()->{'doAction'}('pre_update_user', (int) $id);

            try {
                $user_array = array_merge(['user_id' => $id], $app->req->post);
                $user_id = ttcms_update_user($user_array);
                /**
                 * Set user's role.
                 */
                $user = new TriTan\Common\User\User();
                $user->setId((int) $user_id);
                $user->setRole($app->req->post['user_role']);

                ttcms_logger_activity_log_write(
                    'Update Record',
                    'User',
                    get_name((int) $id),
                    (string) esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    $app->req->server['HTTP_REFERER']
                );
            } catch (Exception $ex) {
                ttcms()->obj['flash']->{'error'}($ex->getMessage());
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
        } elseif (empty($_user) == true) {
            $app->res->_format('json', 404);
            exit();
        } elseif ((int) esc_html($_user->getId()) <= 0) {
            $app->res->_format('json', 404);
            exit();
        } else {
            $app->foil->render(
                'main::admin/user/update',
                [
                    'title' => t__('Update User', 'tritan-cms'),
                    'user' => $_user
                ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-to/', function () use ($app) {
        if (!current_user_can('switch_user')) {
            ttcms()->obj['flash']->{'error'}(
                t__("You don't have permission to log in as another user.", 'tritan-cms'),
                $app->req->server['HTTP_REFERER']
            );
            exit();
        }
    });

    $app->get('/user/(\d+)/switch-to/', function ($id) use ($app, $opt, $current_user) {
        if (isset($app->req->cookie['TTCMS_COOKIENAME'])) {
            $switch_cookie = [
                'key' => 'SWITCH_USERBACK',
                'user_id' => (int) esc_html($current_user->getId()),
                'user_login' => esc_html($current_user->getLogin()),
                'remember' => $opt->read('cookieexpire') - time() > 86400 ? esc_html__('yes') : esc_html__('no'),
                'exp' => (int) $opt->read('cookieexpire') + time()
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
            Cascade::getLogger('error')->{'error'}(
                sprintf(
                    'FILESTATE[%s]: File not found: %s',
                    $e->getCode(),
                    $e->getMessage()
                )
            );
        }

        /**
         * Delete the old cookie.
         */
        $app->cookies->remove("TTCMS_COOKIENAME");

        $auth_cookie = [
            'key' => 'TTCMS_COOKIENAME',
            'user_id' => (int) $id,
            'user_login' => get_user_value((int) $id, 'username'),
            'remember' => time() - (int) $opt->read('cookieexpire') > 86400 ? t__('yes', 'tritan-cms') : t__('no', 'tritan-cms'),
            'exp' => (int) $opt->read('cookieexpire') + time()
        ];

        $app->cookies->setSecureCookie($auth_cookie);

        ttcms()->obj['flash']->{'success'}(
            t__('User switching was successful.', 'tritan-cms'),
            $app->req->server['HTTP_REFERER']
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/(\d+)/switch-back/', function () use ($app) {
        if (!is_user_logged_in()) {
            ttcms()->obj['flash']->{'error'}(
                t__('401 - Error: Unauthorized.', 'tritan-cms'),
                login_url()
            );
            exit();
        }
        if (!isset($app->req->cookie['SWITCH_USERBACK'])) {
            ttcms()->obj['flash']->{'error'}(
                t__('Cookie is not properly set for user switching', 'tritan-cms'),
                admin_url()
            );
            exit();
        }
    });

    $app->get('/user/(\d+)/switch-back/', function ($id) use ($app, $opt) {
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
            Cascade::getLogger('error')->{'error'}(
                sprintf(
                    'FILESTATE[%s]: File not found: %s',
                    $e->getCode(),
                    $e->getMessage()
                )
            );
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
            Cascade::getLogger('error')->{'error'}(
                sprintf(
                    'FILESTATE[%s]: File not found: %s',
                    $e->getCode(),
                    $e->getMessage()
                )
            );
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
            'user_login' => get_user_value((int) $id, 'username'),
            'remember' => time() - (int) $opt->read('cookieexpire') > 86400 ? t__('yes', 'tritan-cms') : t__('no', 'tritan-cms'),
            'exp' => (int) $opt->read('cookieexpire') + time()
        ];

        $app->cookies->setSecureCookie($switch_cookie);

        ttcms()->obj['flash']->{'success'}(
            t__('Switching back to previous session was successful.', 'tritan-cms'),
            $app->req->server['HTTP_REFERER']
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/d/', function () use ($app) {
        if (!current_user_can('delete_users')) {
            ttcms()->obj['flash']->{'error'}(
                t__("You don't have permission to delete users.", 'tritan-cms'),
                $app->req->server['HTTP_REFERER']
            );
            exit();
        }
    });

    $app->post('/user/(\d+)/d/', function ($id) use ($app, $current_user) {
        $user = ttcms_delete_user($id, $app->req->post['assign_id']);

        if ($user) {
            ttcms_logger_activity_log_write(
                t__('Delete Record', 'tritan-cms'),
                t__('User', 'tritan-cms'),
                get_name((int) $id),
                (string) esc_html($current_user->getLogin())
            );

            ttcms()->obj['flash']->{'success'}(
                ttcms()->obj['flash']->{'notice'}(
                    200
                ),
                $app->req->server['HTTP_REFERER']
            );
        } else {
            ttcms()->obj['flash']->{'error'}(
                ttcms()->obj['flash']->{'notice'}(
                    409
                ),
                $app->req->server['HTTP_REFERER']
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/user/lookup/', function () use ($app) {
        if (!is_user_logged_in()) {
            ttcms()->obj['flash']->{'error'}(
                t__("401 - Error: Unauthorized.", 'tritan-cms'),
                $app->req->server['HTTP_REFERER']
            );
            exit();
        }
    });

    $app->match('GET|POST', '/user/lookup/', function () use ($app, $db) {
        $user = $db->table('user')
                ->where('user_id', $app->req->post['user_id'])
                ->first();

        $json = [
            'input#fname' => esc_html($user['user_fname']), 'input#lname' => esc_html($user['user_lname']),
            'input#email' => esc_html($user['user_email'])
        ];
        echo json_encode($json);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/user/(\d+)/reset-password/', function () use ($app) {
        if (!current_user_can('update_users')) {
            ttcms()->obj['flash']->{'error'}(
                t__("You are not allowed to reset user passwords.", 'tritan-cms'),
                $app->req->server['HTTP_REFERER']
            );
            exit();
        }
    });

    $app->get('/user/(\d+)/reset-password/', function ($id) use ($app) {
        $user = get_userdata($id);
        if (!$user) {
            ttcms()->obj['flash']->{'error'}(
                t__('Requested user does not exist.', 'tritan-cms'),
                $app->req->server['HTTP_REFERER']
            );
        }

        $user_id = reset_password($id);

        if (is_ttcms_exception($user_id)) {
            ttcms()->obj['flash']->{'error'}(
                sprintf(
                    'Update error[%s]: %s',
                    $user_id->getCode(),
                    $user_id->getMessage()
                ),
                $app->req->server['HTTP_REFERER']
            );
        } elseif ($user_id > 0) {
            ttcms()->obj['flash']->{'success'}(
                sprintf(
                    t__('Password successfully updated for <strong>%s</strong>.', 'tritan-cms'),
                    get_name((int) $id)
                ),
                $app->req->server['HTTP_REFERER']
            );
        } else {
            ttcms()->obj['flash']->{'error'}(
                sprintf(
                    t__('Could not update password for <strong>%s</strong>.', 'tritan-cms'),
                    get_name((int) $id)
                ),
                $app->req->server['HTTP_REFERER']
            );
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
