<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
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
$app->before('GET|POST', '/admin(.*)', function() {
    if (!Auth\is_user_logged_in()) {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('401 - Error: Unauthorized.', 'tritan-cms'), Core\get_base_url() . 'login' . '/');
        exit();
    }
    if (!Auth\current_user_can('access_admin')) {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('403 - Error: Forbidden.', 'tritan-cms'), Core\get_base_url());
        exit();
    }
});

$app->group('/admin', function() use ($app, $current_user) {

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/site/', function() {
        if (!Auth\current_user_can('manage_sites')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to manage sites.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/site/', function () use($app, $current_user) {

        if ($app->req->isPost()) {
            $site = Site\ttcms_insert_site($app->req->post);

            if (Core\is_ttcms_error($site)) {
                Cascade::getLogger('error')->{'error'}(sprintf('ERROR[%s]: %s', $site->get_error_code(), $site->get_error_message()));
                Dependency\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site->get_error_code(), $site->get_error_message()));
            } else {
                $new_site = Site\get_site($site);
                Logger\ttcms_logger_activity_log_write(Core\_t('Create Record', 'tritan-cms'), Core\_t('Site', 'tritan-cms'), $new_site['site_domain'], Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            }
        }

        $sites = $app->db->table('site')->all();

        $app->foil->render('main::admin/site/index', [
            'title' => Core\_t('Sites', 'tritan-cms'),
            'sites' => $sites
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/site/(\d+)/', function() {
        if (!Auth\current_user_can('update_sites')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to update sites.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/site/(\d+)/', function ($id) use($app, $current_user) {
        if ($app->req->isPost()) {
            $site = array_merge(['site_id' => $id], $app->req->post);
            $site_id = Site\ttcms_update_site($site);

            if (Core\is_ttcms_error($site_id)) {
                Cascade::getLogger('error')->{'error'}(sprintf('ERROR[%s]: %s', $site_id->get_error_code(), $site_id->get_error_message()));
                Dependency\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site_id->get_error_code(), $site_id->get_error_message()));
            } else {
                Logger\ttcms_logger_activity_log_write(Core\_t('Update Record', 'tritan-cms'), Core\_t('Site', 'tritan-cms'), $site['site_domain'], Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            }
        }

        $q = Site\get_site((int) $id);

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
                'title' => Core\_t('Update Site', 'tritan-cms'),
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
        if (!Auth\current_user_can('delete_sites')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to delete sites.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/site/(\d+)/d/', function($id) use($current_user) {
        if ((int) $id == (int) '1') {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You are not allowed to delete the main site.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }

        $old_site = Site\get_site($id);

        $site = Site\ttcms_delete_site($id);

        if (Core\is_ttcms_error($site)) {
            Dependency\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site->get_error_code(), $site->get_error_message()), Core\get_base_url() . 'admin/site/');
        } else {
            Logger\ttcms_logger_activity_log_write(Core\_t('Delete Record', 'tritan-cms'), Core\_t('Site', 'tritan-cms'), $old_site['site_domain'], Core\_escape($current_user->user_login));
            Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/site/');
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/site/users/', function () {
        if (!Auth\current_user_can('manage_sites')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t("You don't have permission to manage sites.", 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/site/users/', function () use($app) {
        $users = $app->db->table('user')->all();

        $app->foil->render('main::admin/site/users', [
            'title' => Core\_t('Manage Site Users', 'tritan-cms'),
            'users' => $users
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/site/users/(\d+)/d/', function() {
        if (!Auth\current_user_can('delete_users') && !Auth\current_user_can('manage_sites')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to delete site users.', 'tritan-cms'), Core\get_base_url() . 'admin/site/users/');
            exit();
        }
    });

    $app->post('/site/users/(\d+)/d/', function($id) use($app, $current_user) {
        if ((int) $app->req->post['assign_id'] > 0) {
            $site_user = Site\ttcms_delete_site_user((int) $id, [
                'assign_id' => (int) $app->req->post['assign_id'],
                'role' => (string) $app->req->post['role']
            ]);
        } else {
            $site_user = Site\ttcms_delete_site_user((int) $id);
        }

        if (Core\is_ttcms_error($site_user)) {
            Dependency\_ttcms_flash()->{'error'}(sprintf('ERROR[%s]: %s', $site_user->get_error_code(), $site_user->get_error_message()), Core\get_base_url() . 'admin/site/users/');
        } else {
            Logger\ttcms_logger_activity_log_write(Core\_t('Delete Record', 'tritan-cms'), Core\_t('Site User', 'tritan-cms'), User\get_name($id), Core\_escape($current_user->user_login));
            Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/site/users/');
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
