<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
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
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/site/', function() {
        if (!func\current_user_can('manage_sites')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage sites.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/site/', function () use($app, $current_user) {

        if ($app->req->isPost()) {
            $site = func\ttcms_insert_site($app->req->post);

            if (func\is_ttcms_error($site)) {
                Cascade::getLogger('error')->{'error'}(sprintf('ERROR[%s]: %s', $site->get_error_code(), $site->get_error_message()));
                func\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site->get_error_code(), $site->get_error_message()));
            } else {
                $new_site = func\get_site($site);
                func\ttcms_logger_activity_log_write(func\_t('Create Record', 'tritan-cms'), func\_t('Site', 'tritan-cms'), $new_site['site_domain'], func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            }
        }

        $sites = $app->db->table('site')->all();

        $app->foil->render('main::admin/site/index', [
            'title' => func\_t('Sites', 'tritan-cms'),
            'sites' => $sites
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/site/(\d+)/', function() {
        if (!func\current_user_can('update_sites')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to update sites.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/site/(\d+)/', function ($id) use($app, $current_user) {
        if ($app->req->isPost()) {
            $site = array_merge(['site_id' => $id], $app->req->post);
            $site_id = func\ttcms_update_site($site);

            if (func\is_ttcms_error($site_id)) {
                Cascade::getLogger('error')->{'error'}(sprintf('ERROR[%s]: %s', $site_id->get_error_code(), $site_id->get_error_message()));
                func\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site_id->get_error_code(), $site_id->get_error_message()));
            } else {
                func\ttcms_logger_activity_log_write(func\_t('Update Record', 'tritan-cms'), func\_t('Site', 'tritan-cms'), $site['site_domain'], func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            }
        }

        $q = func\get_site((int) $id);

        /**
         * If the posttype doesn't exist, then it
         * is false and a 404 page should be displayed.
         */
        if ($q === false) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but the
         * the posttype does not exist, then a 404
         * page should be displayed
         */ elseif (empty($q) === true) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, then all is well
         * and it is ok to process the query and print
         * the results in a jhtml format.
         */ else {

            $app->foil->render('main::admin/site/update', [
                'title' => func\_t('Update Site', 'tritan-cms'),
                'site' => $q,
                    ]
            );
        }
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/site/(\d+)/d/', function() {
        if (!func\current_user_can('delete_sites')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to delete sites.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/site/(\d+)/d/', function($id) use($current_user) {
        if ((int) $id == (int) '1') {
            func\_ttcms_flash()->{'error'}(func\_t('You are not allowed to delete the main site.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }

        $old_site = func\get_site($id);

        $site = func\ttcms_delete_site($id);

        if (func\is_ttcms_error($site)) {
            func\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site->get_error_code(), $site->get_error_message()), func\get_base_url() . 'admin/site/');
        } else {
            func\ttcms_logger_activity_log_write(func\_t('Delete Record', 'tritan-cms'), func\_t('Site', 'tritan-cms'), $old_site['site_domain'], func\_escape($current_user->user_login));
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/site/');
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/site/users/', function () {
        if (!func\current_user_can('manage_sites')) {
            func\_ttcms_flash()->{'error'}(func\_t("You don't have permission to manage sites.", 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/site/users/', function () use($app) {
        $users = $app->db->table('user')->all();

        $app->foil->render('main::admin/site/users', [
            'title' => func\_t('Manage Site Users', 'tritan-cms'),
            'users' => $users
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/site/users/(\d+)/d/', function() {
        if (!func\current_user_can('delete_users') && !func\current_user_can('manage_sites')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to delete site users.', 'tritan-cms'), func\get_base_url() . 'admin/site/users/');
            exit();
        }
    });

    $app->post('/site/users/(\d+)/d/', function($id) use($app, $current_user) {
        if ((int) $app->req->post['assign_id'] > 0) {
            $site_user = func\ttcms_delete_site_user((int) $id, [
                'assign_id' => (int) $app->req->post['assign_id'],
                'role' => (string) $app->req->post['role']
            ]);
        } else {
            $site_user = func\ttcms_delete_site_user((int) $id);
        }

        if (func\is_ttcms_error($site_user)) {
            func\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site_user->get_error_code(), $site_user->get_error_message()), func\get_base_url() . 'admin/site/users/');
        } else {
            func\ttcms_logger_activity_log_write(func\_t('Delete Record', 'tritan-cms'), func\_t('Site User', 'tritan-cms'), func\get_name($id), func\_escape($current_user->user_login));
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/site/users/');
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
