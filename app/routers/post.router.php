<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
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

    foreach (func\get_all_post_types() as $post_type) :
        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/', function() {
            if (!func\current_user_can('manage_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });
        /**
         * Show a list of all of our posts in the backend.
         */
        $app->get('/' . func\_escape($post_type['posttype_slug']) . '/', function () use($app, $post_type) {
            $posts = func\get_all_posts(func\_escape($post_type['posttype_slug']));
            $_posts = func\ttcms_list_sort($posts, 'post_created', 'DESC', true);

            $app->foil->render('main::admin/post/index', [
                'title' => func\_escape($post_type['posttype_title']),
                'posts' => $_posts,
                'posttype' => func\_escape($post_type['posttype_slug'])
                    ]
            );
        });

        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/create/', function() {
            if (!func\current_user_can('create_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to create posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });
        /**
         * Shows the add new post form.
         */
        $app->match('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/create/', function () use($app, $post_type, $current_user) {

            if ($app->req->isPost()) {
                try {
                    $post_id = func\ttcms_insert_post($app->req->post, true);
                    func\ttcms_logger_activity_log_write(func\_t('Update Record', 'tritan-cms'), func\_t('Post', 'tritan-cms'), $app->req->post['post_title'], func\_escape($current_user->user_login));
                    func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/' . (string) $app->req->post['post_posttype'] . '/' . (int) $post_id . '/');
                } catch (Exception $ex) {
                    func\_ttcms_flash()->{'error'}(sprintf('Update error[%s]: %s', $ex->getCode(), $ex->getMessage()), $app->req->server['HTTP_REFERER']);
                }
            }

            $post_count = $app->db->table(Config::get('tbl_prefix') . 'post')->count();

            $app->foil->render('main::admin/post/create', [
                'title' => func\_t('Create', 'tritan-cms') . ' ' . func\_escape($post_type['posttype_title']),
                'posttype_title' => func\_escape($post_type['posttype_title']),
                'posttype' => func\_escape($post_type['posttype_slug']),
                'post_count' => (int) $post_count
                    ]
            );
        });

        /**
         * Before route checks to make sure the logged in
         * user has the permission to edit a post.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/', function() {
            if (!func\current_user_can('update_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to update posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });

        /**
         * Shows the edit form with the requested id.
         */
        $app->match('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/', function ($id) use($app, $post_type, $current_user) {

            if ($app->req->isPost()) {
                try {
                    func\ttcms_update_post($app->req->post, true);
                    func\ttcms_logger_activity_log_write(func\_t('Update Record', 'tritan-cms'), func\_t('Post', 'tritan-cms'), $app->req->post['post_title'], func\_escape($current_user->user_login));
                    func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/' . (string) $app->req->post['post_posttype'] . '/' . (int) $id . '/');
                } catch (Exception $ex) {
                    func\_ttcms_flash()->{'error'}(sprintf('Update error[%s]: %s', $ex->getCode(), $ex->getMessage()), $app->req->server['HTTP_REFERER']);
                }
            }

            $q = $app->db->table(Config::get('tbl_prefix') . 'post');
            $cache = func\ttcms_cache_get((int) $id, 'post');
            if (empty($cache)) {
                $cache = $q->where('post_id', (int) $id)
                        ->where('post_type.post_posttype', func\_escape((string) $post_type['posttype_slug']))
                        ->first();
                func\ttcms_cache_add((int) $id, $cache, 'post');
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

                $app->foil->render('main::admin/post/update-post', [
                    'title' => func\_t('Update', 'tritan-cms') . ' ' . func\_escape($post_type['posttype_title']),
                    'posttype_title' => func\_escape($post_type['posttype_title']),
                    'posttype' => func\_escape($post_type['posttype_slug']),
                    'post' => $cache
                        ]
                );
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET|POST', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function() {
            if (!func\current_user_can('update_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to update posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });

        $app->get('/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function($id) use($app) {
            $post = $app->db->table(Config::get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_id', (int) $id)->update([
                    'post_featured_image' => null
                ]);
                $post->commit();
                func\ttcms_cache_delete((int) $id, 'post');
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $post->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                func\_ttcms_flash()->{'error'}($ex->getMessage(), $app->req->server['HTTP_REFERER']);
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET', '/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/d/', function() {
            if (!func\current_user_can('delete_posts')) {
                func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to delete posts.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
                exit();
            }
        });

        $app->get('/' . func\_escape($post_type['posttype_slug']) . '/(\d+)/d/', function($id) use($post_type, $current_user) {
            $title = func\get_post_title($id);

            $post = func\ttcms_delete_post($id);
            if (func\is_ttcms_exception($post)) {
                func\_ttcms_flash()->{'error'}($post->getMessage(), func\get_base_url() . 'admin/' . (string) func\_escape($post_type['posttype_slug']) . '/');
            } else {
                func\ttcms_logger_activity_log_write(func\_t('Delete Record', 'tritan-cms'), func\_t('Post', 'tritan-cms'), $title, func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/' . (string) func\_escape($post_type['posttype_slug']) . '/');
            }
        });
    endforeach;

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/post-type/', function() {
        if (!func\current_user_can('manage_posts')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to manage posts or post types.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/', function () use($app, $current_user) {

        if ($app->req->isPost()) {
            $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
            $posttype->begin();
            try {
                $posttype_id = func\auto_increment(Config::get('tbl_prefix') . 'posttype', 'posttype_id');
                $posttype_slug = $app->req->post['posttype_slug'] != '' ? $app->req->post['posttype_slug'] : func\ttcms_slugify((string) $app->req->post['posttype_title'], 'posttype');
                $posttype->insert([
                    'posttype_id' => (int) $posttype_id,
                    'posttype_title' => func\if_null($app->req->post['posttype_title']),
                    'posttype_slug' => (string) $posttype_slug,
                    'posttype_description' => func\if_null($app->req->post['posttype_description'])
                ]);
                $posttype->commit();
                $lastId = $posttype_id;
                func\ttcms_cache_delete('posttype', 'posttype');
                /**
                 * Action hook triggered after the posttype is created.
                 * 
                 * @since 0.9
                 * @param int $lastId posttype ID.
                 */
                $app->hook->{'do_action'}('create_posttype', (int) $lastId);

                func\ttcms_logger_activity_log_write(func\_t('Create Record', 'tritan-cms'), func\_t('Post Type', 'tritan-cms'), $app->req->post['posttype_title'], func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                func\_ttcms_flash()->{'error'}(func\_ttcms_flash()->notice(409));
            }
        }

        $posttypes = $app->db->table(Config::get('tbl_prefix') . 'posttype')->all();

        $app->foil->render('main::admin/post/posttype', [
            'title' => func\_t('Post Types', 'tritan-cms'),
            'posttypes' => $posttypes
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/post-type/(\d+)/', function() {
        if (!func\current_user_can('update_posts')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to update posts or post types.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/(\d+)/', function ($id) use($app, $current_user) {
        $current_pt = $app->db->table(Config::get('tbl_prefix') . 'posttype')
                ->where('posttype_id', (int) $id)
                ->first();

        if ($app->req->isPost()) {
            $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
            $posttype->begin();
            try {
                $posttype_slug = $app->req->post['posttype_slug'] != '' ? $app->req->post['posttype_slug'] : func\ttcms_slugify((string) $app->req->post['posttype_title'], 'posttype');
                $posttype->where('posttype_id', (int) $id)->update([
                    'posttype_title' => (string) $app->req->post['posttype_title'],
                    'posttype_slug' => (string) $posttype_slug,
                    'posttype_description' => func\if_null($app->req->post['posttype_description'])
                ]);
                $posttype->commit();

                /**
                 * Update all post's relative url if the the posted data
                 * for posttype does not equal to the current posttype.
                 * 
                 * @since 0.9.6
                 */
                if ($current_pt['posttype_slug'] != (string) $posttype_slug) {
                    func\update_post_relative_url_posttype($id, $current_pt['posttype_slug'], (string) $posttype_slug);
                }
                func\ttcms_cache_delete((int) $id, 'posttype');
                /**
                 * Action hook triggered after the posttype is updated.
                 * 
                 * @since 0.9
                 * @param int $id Post Type ID.
                 */
                $app->hook->{'do_action'}('update_posttype', (int) $id);

                func\ttcms_logger_activity_log_write(func\_t('Update Record', 'tritan-cms'), func\_t('Post Type', 'tritan-cms'), $app->req->post['posttype_title'], func\_escape($current_user->user_login));
                func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                func\_ttcms_flash()->{'error'}(func\_ttcms_flash()->notice(409));
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

            $app->foil->render('main::admin/post/update-posttype', [
                'title' => func\_t('Update Post Type', 'tritan-cms'),
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
    $app->before('GET|POST', '/post-type/(\d+)/d/', function() {
        if (!func\current_user_can('delete_posts')) {
            func\_ttcms_flash()->{'error'}(func\_t('You do not have permission to delete posts or post types.', 'tritan-cms'), func\get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/post-type/(\d+)/d/', function($id) use($app, $current_user) {
        $title = func\get_posttype_title($id);

        $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
        $posttype->begin();
        try {
            $posttype->where('posttype_id', (int) $id)
                    ->delete();
            $posttype->commit();

            $post = $app->db->table(Config::get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_type.posttype_id', (int) $id)
                        ->delete();
                $post->commit();
                func\ttcms_cache_delete('posttype', 'posttype');
                func\ttcms_cache_delete('post', 'post');
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            }

            func\ttcms_logger_activity_log_write(func\_t('Delete Record', 'tritan-cms'), func\_t('Post Type', 'tritan-cms'), $title, func\_escape($current_user->user_login));
            func\_ttcms_flash()->{'success'}(func\_ttcms_flash()->notice(200), func\get_base_url() . 'admin/post-type/');
        } catch (Exception $ex) {
            $posttype->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            func\_ttcms_flash()->{'error'}($ex->getMessage(), func\get_base_url() . 'admin/post-type/');
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
