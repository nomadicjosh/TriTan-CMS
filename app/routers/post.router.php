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
    if (!current_user_can('access_admin')) {
        _ttcms_flash()->{'error'}(_t('403 - Error: Forbidden.', 'tritan-cms'), get_base_url());
        exit();
    }
});

$app->group('/admin', function() use ($app, $user) {

    foreach (get_all_post_types() as $post_type) :
        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . _escape($post_type['posttype_slug']) . '/', function() {
            if (!current_user_can('manage_posts')) {
                _ttcms_flash()->{'error'}(_t('You do not have permission to manage posts.', 'tritan-cms'), get_base_url() . 'admin' . '/');
                exit();
            }
        });
        /**
         * Show a list of all of our posts in the backend.
         */
        $app->get('/' . _escape($post_type['posttype_slug']) . '/', function () use($app, $post_type) {
            $posts = $app->db->table(Config::get('tbl_prefix') . 'post')
                    ->where('post_type.post_posttype', _escape($post_type['posttype_slug']))
                    ->sortBy('post_created', 'desc')
                    ->get();

            $app->foil->render('main::admin/post/index', [
                'title' => _escape($post_type['posttype_title']),
                'posts' => $posts,
                'posttype' => _escape($post_type['posttype_slug'])
                    ]
            );
        });

        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . _escape($post_type['posttype_slug']) . '/create/', function() {
            if (!current_user_can('create_posts')) {
                _ttcms_flash()->{'error'}(_t('You do not have permission to create posts.', 'tritan-cms'), get_base_url() . 'admin' . '/');
                exit();
            }
        });
        /**
         * Shows the add new post form.
         */
        $app->match('GET|POST', '/' . _escape($post_type['posttype_slug']) . '/create/', function () use($app, $post_type) {

            if ($app->req->isPost()) {
                $post = $app->db->table(Config::get('tbl_prefix') . 'post');
                $post->begin();
                try {
                    $post_id = auto_increment(Config::get('tbl_prefix') . 'post', 'post_id');
                    $posttype = get_posttype_by('posttype_slug', $app->req->post['post_posttype']);
                    $post_status = $app->req->post['post_status'];
                    $post_slug = $app->req->post['post_slug'] != '' ? $app->req->post['post_slug'] : ttcms_slugify($app->req->post['post_title']);
                    $relative_url = _escape($post_type['posttype_slug']) . '/' . $post_slug . '/';
                    $featured_image = ttcms_optimized_image_upload($app->req->post['post_featured_image']);
                    $post->insert([
                        'post_id' => (int) $post_id,
                        'post_title' => (string) $app->req->post['post_title'],
                        'post_slug' => (string) $post_slug,
                        'post_content' => if_null($app->req->post['post_content']),
                        'post_author' => (int) $app->req->post['post_author'],
                        'post_type' => [
                            'posttype_id' => (int) _escape($posttype['posttype_id']),
                            'post_posttype' => (string) $app->req->post['post_posttype']
                        ],
                        'post_attributes' => [
                            'parent' => [
                                'parent_id' => if_null(get_post_id($app->req->post['post_parent'])),
                                'post_parent' => if_null($app->req->post['post_parent'])
                            ],
                            'post_sidebar' => if_null($app->req->post['post_sidebar']),
                            'post_show_in_menu' => if_null($app->req->post['post_show_in_menu']),
                            'post_show_in_search' => if_null($app->req->post['post_show_in_search'])
                        ],
                        'post_relative_url' => (string) $relative_url,
                        'post_featured_image' => if_null($featured_image),
                        'post_status' => (string) $post_status,
                        'post_created' => (string) $app->req->post['post_created']
                    ]);
                    $post->commit();
                    $lastId = $post_id;
                    /**
                     * Action hook triggered after the post is created.
                     * 
                     * @since 0.9
                     * @param int $lastId Post ID.
                     */
                    $app->hook->{'do_action'}('create_post', $lastId);
                    /**
                     * Action hook triggered depending on page status.
                     * 
                     * @since 0.9
                     * @param string $page_status Posted status of page.
                     * @param int $lastId Post ID.
                     */
                    $app->hook->{'do_action'}("{$post_type['posttype_slug']}_{$post_status}_create", $lastId);
                    _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/' . $app->req->post['post_posttype'] . '/' . $lastId . '/');
                } catch (Exception $ex) {
                    $post->rollback();
                    Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                    _ttcms_flash()->{'error'}(_ttcms_flash()->notice(409));
                }
            }

            $post_count = $app->db->table(Config::get('tbl_prefix') . 'post')->count();

            $app->foil->render('main::admin/post/create', [
                'title' => _t('Create', 'tritan-cms') . ' ' . _escape($post_type['posttype_title']),
                'posttype_title' => _escape($post_type['posttype_title']),
                'posttype' => _escape($post_type['posttype_slug']),
                'post_count' => (int) $post_count
                    ]
            );
        });

        /**
         * Before route checks to make sure the logged in
         * user has the permission to edit a post.
         */
        $app->before('GET|POST', '/' . _escape($post_type['posttype_slug']) . '/(\d+)/', function() {
            if (!current_user_can('update_posts')) {
                _ttcms_flash()->{'error'}(_t('You do not have permission to update posts.', 'tritan-cms'), get_base_url() . 'admin' . '/');
                exit();
            }
        });

        /**
         * Shows the edit form with the requested id.
         */
        $app->match('GET|POST', '/' . _escape($post_type['posttype_slug']) . '/(\d+)/', function ($id) use($app, $post_type) {

            if ($app->req->isPost()) {
                $post = $app->db->table(Config::get('tbl_prefix') . 'post');
                $post->begin();
                try {
                    $posttype = get_posttype_by('posttype_slug', $app->req->post['post_posttype']);
                    $post_status = $app->req->post['post_status'];
                    $post_slug = $app->req->post['post_slug'] != '' ? $app->req->post['post_slug'] : ttcms_slugify($app->req->post['post_title']);
                    /**
                     * Can be used to filter the relative url.
                     * 
                     * @since 0.9
                     */
                    $url_filter = $app->hook->{'apply_filter'}('relative_url', _escape($post_type['posttype_slug']) . '/', $post_type);
                    $relative_url = $url_filter . $post_slug . '/';
                    $featured_image = ttcms_optimized_image_upload($app->req->post['post_featured_image']);
                    $post->where('post_id', (int) $id)->update([
                        'post_title' => (string) $app->req->post['post_title'],
                        'post_slug' => (string) $post_slug,
                        'post_content' => if_null($app->req->post['post_content']),
                        'post_author' => (int) $app->req->post['post_author'],
                        'post_type' => [
                            'posttype_id' => (int) _escape($posttype['posttype_id']),
                            'post_posttype' => (string) $app->req->post['post_posttype']
                        ],
                        'post_attributes' => [
                            'parent' => [
                                'parent_id' => if_null(get_post_id($app->req->post['post_parent'])),
                                'post_parent' => if_null($app->req->post['post_parent'])
                            ],
                            'post_sidebar' => if_null($app->req->post['post_sidebar']),
                            'post_show_in_menu' => if_null($app->req->post['post_show_in_menu']),
                            'post_show_in_search' => if_null($app->req->post['post_show_in_search'])
                        ],
                        'post_relative_url' => (string) $relative_url,
                        'post_featured_image' => if_null($featured_image),
                        'post_status' => (string) $post_status,
                        'post_created' => (string) $app->req->post['post_created'],
                        'post_modified' => (string) Jenssegers\Date\Date::now()
                    ]);
                    $post->commit();

                    $parent = $app->db->table(Config::get('tbl_prefix') . 'post');
                    $parent->where('post_attributes.parent.parent_id', (int) $id)
                            ->update([
                                'post_attributes.parent.post_parent' => (string) $post_slug
                    ]);
                    ttcms_cache_delete((int) $id, 'post');
                    /**
                     * Action hook triggered after the post is updated.
                     * 
                     * @since 0.9
                     * @param int $id Post ID.
                     */
                    $app->hook->{'do_action'}('update_post', (int) $id);
                    /**
                     * Action hook triggered depending on post status.
                     * 
                     * @since 0.9
                     * @param string $post_status Posted status of post.
                     * @param int $id Post ID.
                     */
                    $app->hook->{'do_action'}("{$post_type['posttype_slug']}_{$post_status}_update", (int) $id);
                    _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/' . (string) $app->req->post['post_posttype'] . '/' . (int) $id . '/');
                } catch (Exception $ex) {
                    $post->rollback();
                    Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                    _ttcms_flash()->{'error'}(_ttcms_flash()->notice(409));
                }
            }

            $q = $app->db->table(Config::get('tbl_prefix') . 'post');
            $cache = ttcms_cache_get((int) $id, 'post');
            if (empty($cache)) {
                $cache = $q->where('post_id', (int) $id)
                        ->where('post_type.post_posttype', _escape((string) $post_type['posttype_slug']))
                        ->first();
                ttcms_cache_add((int) $id, $cache, 'post');
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
                    'title' => _t('Update', 'tritan-cms') . ' ' . _escape($post_type['posttype_title']),
                    'posttype_title' => _escape($post_type['posttype_title']),
                    'posttype' => _escape($post_type['posttype_slug']),
                    'post' => $cache
                        ]
                );
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET|POST', '/' . _escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function() {
            if (!current_user_can('update_posts')) {
                _ttcms_flash()->{'error'}(_t('You do not have permission to update posts.', 'tritan-cms'), get_base_url() . 'admin' . '/');
                exit();
            }
        });

        $app->get('/' . _escape($post_type['posttype_slug']) . '/(\d+)/remove-featured-image/', function($id) use($app) {
            $post = $app->db->table(Config::get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_id', (int) $id)->update([
                    'post_featured_image' => null
                ]);
                $post->commit();
                ttcms_cache_delete((int) $id, 'post');
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $post->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                _ttcms_flash()->{'error'}($ex->getMessage(), $app->req->server['HTTP_REFERER']);
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET', '/' . _escape($post_type['posttype_slug']) . '/(\d+)/d/', function() {
            if (!current_user_can('delete_posts')) {
                _ttcms_flash()->{'error'}(_t('You do not have permission to delete posts.', 'tritan-cms'), get_base_url() . 'admin' . '/');
                exit();
            }
        });

        $app->get('/' . _escape($post_type['posttype_slug']) . '/(\d+)/d/', function($id) use($app, $post_type) {
            $post = $app->db->table(Config::get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_id', (int) $id)
                        ->delete();
                $post->commit();
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/' . (string) _escape($post_type['posttype_slug']) . '/');
            } catch (Exception $ex) {
                $post->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                _ttcms_flash()->{'error'}($ex->getMessage(), get_base_url() . 'admin/' . (string) _escape($post_type['posttype_slug']) . '/');
            }
        });
    endforeach;

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/post-type/', function() {
        if (!current_user_can('manage_posts')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to manage posts or post types.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/', function () use($app) {

        if ($app->req->isPost()) {
            $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
            $posttype->begin();
            try {
                $posttype_id = auto_increment(Config::get('tbl_prefix') . 'posttype', 'posttype_id');
                $posttype_slug = $app->req->post['posttype_slug'] != '' ? $app->req->post['posttype_slug'] : ttcms_slugify((string) $app->req->post['posttype_title'], 'posttype');
                $posttype->insert([
                    'posttype_id' => (int) $posttype_id,
                    'posttype_title' => if_null($app->req->post['posttype_title']),
                    'posttype_slug' => (string) $posttype_slug,
                    'posttype_description' => if_null($app->req->post['posttype_description'])
                ]);
                $posttype->commit();
                $lastId = $posttype_id;
                ttcms_cache_delete('posttype', 'posttype');
                /**
                 * Action hook triggered after the posttype is created.
                 * 
                 * @since 0.9
                 * @param int $lastId posttype ID.
                 */
                $app->hook->{'do_action'}('create_posttype', (int) $lastId);
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                _ttcms_flash()->{'error'}(_ttcms_flash()->notice(409));
            }
        }

        $posttypes = $app->db->table(Config::get('tbl_prefix') . 'posttype')->all();

        $app->foil->render('main::admin/post/posttype', [
            'title' => _t('Post Types', 'tritan-cms'),
            'posttypes' => $posttypes
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/post-type/(\d+)/', function() {
        if (!current_user_can('update_posts')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to update posts or post types.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/(\d+)/', function ($id) use($app) {
        $current_pt = $app->db->table(Config::get('tbl_prefix') . 'posttype')
                ->where('posttype_id', (int) $id)
                ->first();

        if ($app->req->isPost()) {
            $posttype = $app->db->table(Config::get('tbl_prefix') . 'posttype');
            $posttype->begin();
            try {
                $posttype_slug = $app->req->post['posttype_slug'] != '' ? $app->req->post['posttype_slug'] : ttcms_slugify((string) $app->req->post['posttype_title'], 'posttype');
                $posttype->where('posttype_id', (int) $id)->update([
                    'posttype_title' => (string) $app->req->post['posttype_title'],
                    'posttype_slug' => (string) $posttype_slug,
                    'posttype_description' => if_null($app->req->post['posttype_description'])
                ]);
                $posttype->commit();

                /**
                 * Update all post's relative url if the the posted data
                 * for posttype does not equal to the current posttype.
                 * 
                 * @since 0.9.6
                 */
                if ($current_pt['posttype_slug'] != (string) $posttype_slug) {
                    update_post_relative_url_posttype($id, $current_pt['posttype_slug'], (string) $posttype_slug);
                }
                ttcms_cache_delete((int) $id, 'posttype');
                /**
                 * Action hook triggered after the posttype is updated.
                 * 
                 * @since 0.9
                 * @param int $id Post Type ID.
                 */
                $app->hook->{'do_action'}('update_posttype', (int) $id);
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
                _ttcms_flash()->{'error'}(_ttcms_flash()->notice(409));
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
                'title' => _t('Update Post Type', 'tritan-cms'),
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
        if (!current_user_can('delete_posts')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to delete posts or post types.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/post-type/(\d+)/d/', function($id) use($app) {
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
                ttcms_cache_delete('posttype', 'posttype');
                ttcms_cache_delete('post', 'post');
            } catch (Exception $ex) {
                $posttype->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            }

            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/post-type/');
        } catch (Exception $ex) {
            $posttype->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            _ttcms_flash()->{'error'}($ex->getMessage(), get_base_url() . 'admin/post-type/');
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
