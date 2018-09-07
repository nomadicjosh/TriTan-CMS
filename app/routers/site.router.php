<?php
use TriTan\Common\Site\SiteRepository;
use TriTan\Common\Site\SiteMapper;
use TriTan\Common\Context\HelperContext;
use Cascade\Cascade;

$db = new \TriTan\Database();

$current_user = get_userdata(get_current_user_id());

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/admin(.*)', function () {
    if (!is_user_logged_in()) {
        ttcms()->obj['flash']->{'error'}(
            esc_html__('401 - Error: Unauthorized.'),
            login_url()
        );
        exit();
    }
    if (!current_user_can('access_admin')) {
        ttcms()->obj['flash']->{'error'}(
            esc_html__('403 - Error: Forbidden.'),
            home_url()
        );
        exit();
    }
});

$app->group('/admin', function () use ($app, $db, $current_user) {

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/site/', function () {
        if (!current_user_can('manage_sites')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__('You do not have permission to manage sites.'),
                admin_url()
            );
            exit();
        }
    });

    $app->match('GET|POST', '/site/', function () use ($app, $db, $current_user) {
        if ($app->req->isPost()) {
            $site = ttcms_insert_site($app->req->post);

            if (is_ttcms_error($site)) {
                Cascade::getLogger('error')->{'error'}(
                    sprintf(
                        'ERROR[%s]: %s',
                        $site->getErrorCode(),
                        $site->getErrorMessage()
                    )
                );
                ttcms()->obj['flash']->{'error'}(
                    sprintf(
                        'ERROR[%s]: %s',
                        $site->getErrorCode(),
                        $site->getErrorMessage()
                    )
                );
            } else {
                $new_site = get_site($site);
                ttcms_logger_activity_log_write(
                    esc_html__('Create Record'),
                    esc_html__('Site'),
                    $new_site->getDomain(),
                    esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    $app->req->server['HTTP_REFERER']
                );
            }
        }

        $sites = (
            new SiteRepository(
                new SiteMapper(
                    $db,
                    new HelperContext()
                )
            )
        )->{'findAll'}();
        $sites = ttcms_list_sort($sites, 'site_registered', 'DESC', true);

        $app->foil->render(
            'main::admin/site/index',
            [
                'title' => esc_html__('Sites'),
                'sites' => $sites
            ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/site/(\d+)/', function () {
        if (!current_user_can('update_sites')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__('You do not have permission to update sites.'),
                admin_url()
            );
            exit();
        }
    });

    $app->match('GET|POST', '/site/(\d+)/', function ($id) use ($app, $db, $current_user) {
        if ($app->req->isPost()) {
            $site = array_merge(
                [
                    'site_id' => $id
                ],
                $app->req->post
            );

            $site_id = ttcms_update_site($site);

            if (is_ttcms_error($site_id)) {
                Cascade::getLogger('error')->{'error'}(
                    sprintf(
                        'ERROR[%s]: %s',
                        $site_id->getErrorCode(),
                        $site_id->getErrorMessage()
                    )
                );

                ttcms()->obj['flash']->{'error'}(
                    sprintf(
                        'ERROR[%s]: %s',
                        $site_id->getErrorCode(),
                        $site_id->getErrorMessage()
                    )
                );
            } else {
                ttcms_logger_activity_log_write(
                    esc_html__('Update Record'),
                    esc_html__('Site'),
                    $site['site_domain'],
                    esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    $app->req->server['HTTP_REFERER']
                );
            }
        }

        $q = (
            new SiteRepository(
                new SiteMapper(
                    $db,
                    new HelperContext()
                )
            )
        )->{'findById'}((int) $id);

        /**
         * If the posttype doesn't exist, then it
         * is false and a 404 page should be displayed.
         */
        if ($q === false) {
            $app->res->_format('json', 404);
            exit();
        } elseif (empty($q) === true) {
            $app->res->_format('json', 404);
            exit();
        } else {
            $app->foil->render(
                'main::admin/site/update',
                [
                    'title' => esc_html__('Update Site'),
                    'site' => $q,
                ]
            );
        }
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/site/(\d+)/d/', function () {
        if (!current_user_can('delete_sites')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__('You do not have permission to delete sites.'),
                admin_url()
            );
            exit();
        }
    });

    $app->get('/site/(\d+)/d/', function ($id) use ($current_user) {
        if ((int) $id == (int) '1') {
            ttcms()->obj['flash']->{'error'}(
                esc_html__('You are not allowed to delete the main site.'),
                admin_url()
            );
            exit();
        }

        $old_site = get_site($id);
        $site = ttcms_delete_site($id);

        if (is_ttcms_error($site)) {
            ttcms()->obj['flash']->{'error'}(
                sprintf(
                    'ERROR[%s]: %s',
                    $site->getErrorCode(),
                    $site->getErrorMessage()
                ),
                admin_url('site/')
            );
        } else {
            ttcms_logger_activity_log_write(
                esc_html__('Delete Record'),
                esc_html__('Site'),
                $old_site->getDomain(),
                esc_html($current_user->getLogin())
            );

            ttcms()->obj['flash']->{'success'}(
                ttcms()->obj['flash']->{'notice'}(
                    200
                ),
                admin_url('site/')
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/site/users/', function () {
        if (!current_user_can('manage_sites')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__("You don't have permission to manage sites."),
                admin_url()
            );
            exit();
        }
    });

    $app->get('/site/users/', function () use ($app, $db) {
        $users = (
            new TriTan\Common\User\UserRepository(
                new TriTan\Common\User\UserMapper(
                    $db,
                    new HelperContext()
                )
            )
        )->{'findAll'}();
        $users = ttcms_list_sort($users, 'user_lname', 'ASC', true);

        $app->foil->render(
            'main::admin/site/users',
            [
                'title' => esc_html__('Manage Site Users'),
                'users' => $users
            ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/site/users/(\d+)/d/', function () {
        if (!current_user_can('delete_users') && !current_user_can('manage_sites')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__('You do not have permission to delete site users.'),
                admin_url('site/users/')
            );
            exit();
        }
    });

    $app->post('/site/users/(\d+)/d/', function ($id) use ($app, $current_user) {
        if (isset($app->req->post['assign_id']) && (int) $app->req->post['assign_id'] > 0) {
            $site_user = ttcms_delete_site_user((int) $id, [
                'assign_id' => (int) $app->req->post['assign_id'],
                'role' => (string) $app->req->post['role']
            ]);
        } else {
            $site_user = ttcms_delete_site_user((int) $id);
        }

        if (is_ttcms_error($site_user)) {
            ttcms()->obj['flash']->{'error'}(
                sprintf(
                    'ERROR[%s]: %s',
                    $site_user->getErrorCode(),
                    $site_user->getErrorMessage()
                ),
                admin_url('site/users/')
            );
        } else {
            ttcms()->obj['flash']->{'success'}(
                ttcms()->obj['flash']->{'notice'}(
                    200
                ),
                admin_url('site/users/')
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
