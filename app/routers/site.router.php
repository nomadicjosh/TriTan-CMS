<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;

$user = get_userdata(get_current_user_id());

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

$app->group('/admin', function() use ($app, $user) {

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/site/', function() {
        if (!hasPermission('manage_sites')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to manage sites.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/site/', function () use($app) {

        if ($app->req->isPost()) {
            try {
                ttcms_insert_site($app->req->post);
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                _ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $sites = $app->db->table('site')->all();

        $app->view->display('admin/site/index', [
            'title' => _t('Sites', 'tritan-cms'),
            'sites' => $sites
            ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/site/(\d+)/', function() {
        if (!hasPermission('update_sites')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to update sites.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/site/(\d+)/', function ($id) use($app) {
        if ($app->req->isPost()) {
            try {
                $site = array_merge(['site_id' => $id], $app->req->post);
                ttcms_update_site($site);
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                _ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $q = get_site((int) $id);

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

            $app->view->display('admin/site/update', [
                'title' => _t('Update Site', 'tritan-cms'),
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
        if (!hasPermission('delete_sites')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to delete sites.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/site/(\d+)/d/', function($id) use($app) {
        if ((int) $id == (int) '1') {
            _ttcms_flash()->{'error'}(_t('You are not allowed to delete the main site.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
        $site = $app->db->table('site');
        $site->begin();
        try {
            $site->where('site_id', (int) $id)
                ->delete();
            $site->commit();
            /**
             * Action hook triggered after the site is deleted.
             * 
             * @since 1.0.0
             * @param int $id Site ID.
             */
            $app->hook->{'do_action'}('delete_site', (int) $id);
            ttcms_cache_delete($id, 'site');
            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/site/');
        } catch (Exception $ex) {
            $site->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            _ttcms_flash()->{'error'}($ex->getMessage(), get_base_url() . 'admin/site/');
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/site/users/', function () {
        if (!hasPermission('manage_sites')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to manage sites.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/site/users/', function () use($app) {
        $users = $app->db->table('user')->all();

        $app->view->display('admin/site/users', [
            'title' => _t('Manage Site Users', 'tritan-cms'),
            'users' => $users
            ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/site/users/(\d+)/d/', function() {
        if (!hasPermission('delete_users') && !hasPermission('manage_sites')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to delete site users.', 'tritan-cms'), get_base_url() . 'admin/site/users/');
            exit();
        }
    });

    $app->get('/site/users/(\d+)/d/', function($id) use($app) {
        $tbl_prefix = Config::get('tbl_prefix');
        if ((int) $id == (int) '1') {
            _ttcms_flash()->{'error'}(_t('You are not allowed to delete the super administrator.', 'tritan-cms'), get_base_url() . 'admin/site/users/');
            exit();
        }
        $user = $app->db->table('user');
        $user->begin();
        try {
            $user->where('user_id', (int) $id)
                ->delete();
            $user->commit();

            $check = $app->db->table('usermeta')
                ->where('user_id', (int) $id)
                ->where('meta_key', 'match', "/$tbl_prefix/")
                ->get();

            if (count($check) > 0) {

                $umeta = $app->db->table('usermeta');
                $umeta->begin();
                try {
                    $umeta->where('user_id', (int) $id)
                        ->where('meta_key', 'match', "/$tbl_prefix/")
                        ->delete();

                    $umeta->commit();
                } catch (Exception $ex) {
                    $umeta->rollback();
                    Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                }
            }
            /**
             * Action hook triggered after the user is deleted.
             * 
             * @since 1.0.0
             * @param int $id Site ID.
             */
            $app->hook->{'do_action'}('delete_user', (int) $id);
            ttcms_cache_delete($id, 'user');
            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/site/users/');
        } catch (Exception $ex) {
            $user->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            _ttcms_flash()->{'error'}($ex->getMessage(), get_base_url() . 'admin/site/');
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
