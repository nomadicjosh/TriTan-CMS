<?php
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;
use TriTan\Functions\Db;
use TriTan\Functions\Dependency;
use TriTan\Functions\Auth;
use TriTan\Functions\User;
use TriTan\Functions\Cache;
use TriTan\Functions\Core;
use TriTan\Functions\Logger;
use TriTan\Functions\Post;
use TriTan\Functions\Posttype;

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
    foreach (Db\get_all_post_types() as $post_type) :
        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . Core\_escape($post_type['posttype_slug']) . '/', function () {
            if (!Auth\current_user_can('manage_posts')) {
                Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to manage posts.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
                exit();
            }
        });
    /**
     * Show a list of all of our posts in the backend.
     */
    $app->get('/' . Core\_escape($post_type['posttype_slug']) . '/', function () use ($app, $post_type) {
        $posts = Db\get_all_posts(Core\_escape($post_type['posttype_slug']));
        $_posts = Core\ttcms_list_sort($posts, 'post_created', 'DESC', true);

        $app->foil->render(
                'main::admin/post/index',
                [
                'title' => Core\_escape($post_type['posttype_title']),
                'posts' => $_posts,
                'posttype' => Core\_escape($post_type['posttype_slug'])
                    ]
            );
    });

    /**
     * Before route checks to make sure the logged in user
     * has permission to create a new post.
     */
    $app->before('GET|POST', '/' . Core\_escape($post_type['posttype_slug']) . '/create/', function () {
        if (!Auth\current_user_can('create_posts')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to create posts.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });
    /**
     * Shows the add new post form.
     */
    $app->match('GET|POST', '/' . Core\_escape($post_type['posttype_slug']) . '/create/', function () use ($app, $post_type, $current_user) {
        if ($app->req->isPost()) {
            try {
                $post_id = Post\ttcms_insert_post($app->req->post, true);
                Logger\ttcms_logger_activity_log_write(Core\_t('Update Record', 'tritan-cms'), Core\_t('Post', 'tritan-cms'), $app->req->post['post_title'], Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/' . (string) $app->req->post['post_posttype'] . '/' . (int) $post_id . '/');
            } catch (Exception $ex) {
                Dependency\_ttcms_flash()->{'error'}(sprintf('Update error[%s]: %s', $ex->getCode(), $ex->getMessage()), $app->req->server['HTTP_REFERER']);
            }
        }

        $post_count = $app->db->table(Config::get('tbl_prefix') . 'post')->count();

        $app->foil->render(
                'main::admin/post/create',
                [
                'title' => Core\_t('Create', 'tritan-cms') . ' ' . Core\_escape($post_type['posttype_title']),
                'posttype_title' => Core\_escape($post_type['posttype_title']),
                'posttype' => Core\_escape($post_type['posttype_slug']),
                'post_count' => (int) $post_count
                    ]
            );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a post.
     */
    $app->before('GET|POST', '/' . Core\_escape($post_type['posttype_slug']) . '/(\d+)/', function () {
        if (!Auth\current_user_can('update_posts')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to update posts.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    /**
     * Shows the edit form with the requested id.
     */
    $app->match('GET|POST', '/' . Core\_escape($post_type['posttype_slug']) . '/(\d+)/', function ($id) use ($app, $post_type, $current_user) {
        if ($app->req->isPost()) {
            try {
                Post\ttcms_update_post($app->req->post, true);
                Logger\ttcms_logger_activity_log_write(Core\_t('Update Record', 'tritan-cms'), Core\_t('Post', 'tritan-cms'), $app->req->post['post_title'], Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/' . (string) $app->req->post['post_posttype'] . '/' . (int) $id . '/');
            } catch (Exception $ex) {
                Dependency\_ttcms_flash()->{'error'}(sprintf('Update error[%s]: %s', $ex->getCode(), $ex->getMessage()), $app->req->server['HTTP_REFERER']);
            }
        }

        $q = $app->db->table(Config::get('tbl_prefix') . 'post');
        $cache = Cache\ttcms_cache_get((int) $id, 'post');
        if (empty($cache)) {
            $cache = $q->where('post_id', (int) $id)
                        ->where('post_type.post_posttype', Core\_escape((string) $post_type['posttype_slug']))
                        ->first();
            Cache\ttcms_cache_add((int) $id, $cache, 'post');
        }

        /**
         * If the category doesn't exist, then it
         * is false and a 404 page should be displayed.
         */
        if ($cache === false) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but the
         * the category does not exist, then a 404
         * page should be displayed
         */ elseif (empty($cache) === true) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count($cache) <= 0) {
            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, then all is well
         * and it is ok to process the query and print
         * the results in a jhtml format.
         */ else {
            $app->foil->render(
                'main::admin/post/update-post',
                [
                    'title' => Core\_t('Update', 'tritan-cms') . ' ' . Core\_escape($post_type['posttype_title']),
                    'posttype_title' => Core\_escape($post_type['posttype_title']),
                    'posttype' => Core\_escape($post_type['posttype_slug']),
                    'post' => $cache
                        ]
            );
        }
    });

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/' . Core\_escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function () {
        if (!Auth\current_user_can('update_posts')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to update posts.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/' . Core\_escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function ($id) use ($app) {
        $post = $app->db->table(Config::get('tbl_prefix') . 'post');
        $post->begin();
        try {
            $post->where('post_id', (int) $id)->update([
                        'post_featured_image' => null
                    ]);
            $post->commit();
            Cache\ttcms_cache_delete((int) $id, 'post');
            Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        } catch (Exception $ex) {
            $post->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            Dependency\_ttcms_flash()->{'error'}($ex->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    });

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET', '/' . Core\_escape($post_type['posttype_slug']) . '/(\d+)/d/', function () {
        if (!Auth\current_user_can('delete_posts')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to delete posts.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/' . Core\_escape($post_type['posttype_slug']) . '/(\d+)/d/', function ($id) use ($post_type, $current_user) {
        $title = Post\get_post_title($id);

        $post = Post\ttcms_delete_post($id);
        if (Core\is_ttcms_exception($post)) {
            Dependency\_ttcms_flash()->{'error'}($post->getMessage(), Core\get_base_url() . 'admin/' . (string) Core\_escape($post_type['posttype_slug']) . '/');
        } else {
            Logger\ttcms_logger_activity_log_write(Core\_t('Delete Record', 'tritan-cms'), Core\_t('Post', 'tritan-cms'), $title, Core\_escape($current_user->user_login));
            Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/' . (string) Core\_escape($post_type['posttype_slug']) . '/');
        }
    });
    endforeach;

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/post-type/', function () {
        if (!Auth\current_user_can('manage_posts')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to manage posts or post types.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/', function () use ($app, $current_user) {
        if ($app->req->isPost()) {
            try {
                Posttype\ttcms_insert_posttype($app->req->post);
                Logger\ttcms_logger_activity_log_write(Core\_t('Create Record', 'tritan-cms'), Core\_t('Post Type', 'tritan-cms'), $app->req->post['posttype_title'], Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                Dependency\_ttcms_flash()->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()), $app->req->server['HTTP_REFERER']);
            }
        }

        $posttypes = $app->db->table(Config::get('tbl_prefix') . 'posttype')->all();

        $app->foil->render(
            'main::admin/post/posttype',
            [
            'title' => Core\_t('Post Types', 'tritan-cms'),
            'posttypes' => $posttypes
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/post-type/(\d+)/', function () {
        if (!Auth\current_user_can('update_posts')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to update posts or post types.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/(\d+)/', function ($id) use ($app, $current_user) {
        if ($app->req->isPost()) {
            try {
                $data = array_merge(['posttype_id' => (int) $id], $app->req->post);
                Posttype\ttcms_update_posttype($data);
                Logger\ttcms_logger_activity_log_write(Core\_t('Update Record', 'tritan-cms'), Core\_t('Post Type', 'tritan-cms'), $app->req->post['posttype_title'], Core\_escape($current_user->user_login));
                Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                Dependency\_ttcms_flash()->{'error'}(Dependency\_ttcms_flash()->notice(409));
            }
        }

        $q = $app->db->table(Config::get('tbl_prefix') . 'posttype')->where('posttype_id', (int) $id)->first();
        $posttypes = $app->db->table(Config::get('tbl_prefix') . 'posttype')->all();

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
            $app->foil->render(
                'main::admin/post/update-posttype',
                [
                'title' => Core\_t('Update Post Type', 'tritan-cms'),
                'posttype' => $q,
                'posttypes' => $posttypes
                    ]
            );
        }
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to delete posttypes.
     */
    $app->before('GET|POST', '/post-type/(\d+)/d/', function () {
        if (!Auth\current_user_can('delete_posts')) {
            Dependency\_ttcms_flash()->{'error'}(Core\_t('You do not have permission to delete posts or post types.', 'tritan-cms'), Core\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/post-type/(\d+)/d/', function ($id) use ($current_user) {
        $title = Posttype\get_posttype_title($id);

        $posttype = Posttype\ttcms_delete_posttype($id);

        if ($posttype) {
            Logger\ttcms_logger_activity_log_write(Core\_t('Delete Record', 'tritan-cms'), Core\_t('Post Type', 'tritan-cms'), $title, Core\_escape($current_user->user_login));
            Dependency\_ttcms_flash()->{'success'}(Dependency\_ttcms_flash()->notice(200), Core\get_base_url() . 'admin/post-type/');
        } else {
            Dependency\_ttcms_flash()->{'error'}(Dependency\_ttcms_flash()->notice(409), Core\get_base_url() . 'admin/post-type/');
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
