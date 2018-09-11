<?php
use TriTan\Container as c;
use TriTan\Exception\Exception;
use TriTan\Exception\InvalidArgumentException;
use Cascade\Cascade;
use TriTan\Common\Post\PostRepository;
use TriTan\Common\Post\PostMapper;
use TriTan\Common\Posttype\PosttypeRepository;
use TriTan\Common\Posttype\PosttypeMapper;
use TriTan\Common\Context\HelperContext;
use TriTan\Common\Hooks\ActionFilterHook as hook;

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
    foreach (get_all_post_types() as $posttype) :
        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . $posttype->getSlug() . '/', function () {
            if (!current_user_can('manage_posts')) {
                ttcms()->obj['flash']->{'error'}(
                    esc_html__('You do not have permission to manage posts.'),
                    admin_url()
                );
                exit();
            }
        });
        /**
         * Show a list of all of our posts in the backend.
         */
        $app->get('/' . $posttype->getSlug() . '/', function () use ($app, $db, $posttype) {
            $posts = (
                    new PostRepository(
                        new PostMapper(
                            $db,
                            new HelperContext()
                        )
                    )
                    )->{'findByType'}($posttype->getSlug());
            $_posts = ttcms_list_sort($posts, 'post_created', 'DESC', true);

            $app->foil->render(
                'main::admin/post/index',
                [
                    'title' => $posttype->getTitle(),
                    'posts' => $_posts,
                    'posttype' => $posttype->getSlug()
                ]
            );
        });

        /**
         * Before route checks to make sure the logged in user
         * has permission to create a new post.
         */
        $app->before('GET|POST', '/' . $posttype->getSlug() . '/create/', function () {
            if (!current_user_can('create_posts')) {
                ttcms()->obj['flash']->{'error'}(
                    esc_html__('You do not have permission to create posts.'),
                    admin_url()
                );
                exit();
            }
        });
        /**
         * Shows the add new post form.
         */
        $app->match('GET|POST', '/' . $posttype->getSlug() . '/create/', function () use ($app, $db, $posttype, $current_user) {
            if ($app->req->isPost()) {
                try {
                    $post_id = ttcms_insert_post($app->req->post, true);

                    ttcms_logger_activity_log_write(
                        esc_html__('Update Record'),
                        esc_html__('Post'),
                        $app->req->post['post_title'],
                        esc_html($current_user->getLogin())
                    );

                    ttcms()->obj['flash']->{'success'}(
                        ttcms()->obj['flash']->{'notice'}(
                            200
                        ),
                        admin_url((string) $app->req->post['post_posttype'] . '/' . (int) $post_id . '/')
                    );
                } catch (Exception $ex) {
                    ttcms()->obj['flash']->{'error'}(
                        sprintf(
                            'POSTMAPPER[insert]: %s',
                            $ex->getMessage()
                        ),
                        $app->req->server['HTTP_REFERER']
                    );
                }
            }

            $post_count = $db->table(c::getInstance()->get('tbl_prefix') . 'post')->count();
            
            hook::getInstance()->{'doAction'}(
                'post_create_view',
                $app,
                $posttype,
                $post_count
            );
        });

        /**
         * Before route checks to make sure the logged in
         * user has the permission to edit a post.
         */
        $app->before('GET|POST', '/' . $posttype->getSlug() . '/(\d+)/', function () {
            if (!current_user_can('update_posts')) {
                ttcms()->obj['flash']->{'error'}(
                    esc_html__(
                        'You do not have permission to update posts.'
                    ),
                    admin_url()
                );
                exit();
            }
        });

        /**
         * Shows the edit form with the requested id.
         */
        $app->match('GET|POST', '/' . esc_html($posttype->getSlug()) . '/(\d+)/', function ($id) use ($app, $db, $posttype, $current_user) {
            if ($app->req->isPost()) {
                try {
                    ttcms_update_post($app->req->post, true);

                    ttcms_logger_activity_log_write(
                        esc_html__('Update Record'),
                        esc_html__('Post'),
                        $app->req->post['post_title'],
                        esc_html($current_user->getLogin())
                    );

                    ttcms()->obj['flash']->{'success'}(
                        ttcms()->obj['flash']->{'notice'}(
                            200
                        ),
                        admin_url((string) $app->req->post['post_posttype'] . '/' . (int) $id . '/')
                    );
                } catch (Exception $ex) {
                    ttcms()->obj['flash']->{'error'}(
                        sprintf(
                            'POSTMAPPER[update]: %s',
                            $ex->getMessage()
                        ),
                        $app->req->server['HTTP_REFERER']
                    );
                }
            }

            try {
                $post = (
                        new PostRepository(
                            new PostMapper(
                                $db,
                                new HelperContext()
                            )
                        )
                        )->{'findById'}((int) $id);
            } catch (InvalidArgumentException $ex) {
                echo sprintf(
                    'POSTMAPPER[%s]: %s',
                    $ex->getCode(),
                    $ex->getMessage()
                );
                exit();
            } catch (Exception $ex) {
                echo sprintf(
                    'POSTMAPPER[%s]: %s',
                    $ex->getCode(),
                    $ex->getMessage()
                );
                exit();
            }


            /**
             * If the category doesn't exist, then it
             * is false and a 404 page should be displayed.
             */
            if ($post === false) {
                $app->res->_format('json', 404);
                exit();
            } elseif (empty($post) === true) {
                $app->res->_format('json', 404);
                exit();
            } elseif ($post->post_id <= 0) {
                $app->res->_format('json', 404);
                exit();
            } else {
                hook::getInstance()->{'doAction'}(
                    'post_update_view',
                    $app,
                    $posttype,
                    $post
                );
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET|POST', '/' . $posttype->getSlug() . '/(\d+)/remove-featured-image/', function () {
            if (!current_user_can('update_posts')) {
                ttcms()->obj['flash']->{'error'}(
                    esc_html__(
                        'You do not have permission to update posts.'
                    ),
                    admin_url()
                );
                exit();
            }
        });

        $app->get('/' . $posttype->getSlug() . '/(\d+)/remove-featured-image/', function ($id) use ($app, $db) {
            $post = $db->table(c::getInstance()->get('tbl_prefix') . 'post');
            $post->begin();
            try {
                $post->where('post_id', (int) $id)->update([
                    'post_featured_image' => null
                ]);
                $post->commit();

                ttcms()->obj['cache']->{'delete'}((int) $id, 'post');

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    $app->req->server['HTTP_REFERER']
                );
            } catch (Exception $ex) {
                $post->rollback();
                Cascade::getLogger('error')->{'error'}(
                    sprintf(
                        'SQLSTATE[%s]: %s',
                        $ex->getCode(),
                        $ex->getMessage()
                    )
                );

                ttcms()->obj['flash']->{'error'}(
                    $ex->getMessage(),
                    $app->req->server['HTTP_REFERER']
                );
            }
        });

        /**
         * Before route checks to make sure the logged in user
         * is allowed to delete posts.
         */
        $app->before('GET', '/' . $posttype->getSlug() . '/(\d+)/d/', function () {
            if (!current_user_can('delete_posts')) {
                ttcms()->obj['flash']->{'error'}(
                    esc_html__(
                        'You do not have permission to delete posts.'
                    ),
                    admin_url()
                );
                exit();
            }
        });

        $app->get('/' . $posttype->getSlug() . '/(\d+)/d/', function ($id) use ($posttype, $current_user) {
            $title = get_post_title($id);

            $post = ttcms_delete_post($id);
            if (is_ttcms_exception($post)) {
                ttcms()->obj['flash']->{'error'}(
                    $post->getMessage(),
                    admin_url((string) $posttype->getSlug() . '/')
                );
            } else {
                ttcms_logger_activity_log_write(
                    esc_html__('Delete Record'),
                    esc_html__('Post'),
                    $title,
                    esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    admin_url((string) $posttype->getSlug() . '/')
                );
            }
        });
    endforeach;

    /**
     * Before route checks to make sure the logged in user
     * is allowed to delete posts.
     */
    $app->before('GET|POST', '/post-type/', function () {
        if (!current_user_can('manage_posts')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__(
                    'You do not have permission to manage posts or post types.'
                ),
                admin_url()
            );
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/', function () use ($app, $db, $current_user) {
        if ($app->req->isPost()) {
            try {
                ttcms_insert_posttype($app->req->post);

                ttcms_logger_activity_log_write(
                    esc_html__('Create Record'),
                    esc_html__('Post Type'),
                    $app->req->post['posttype_title'],
                    esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    $app->req->server['HTTP_REFERER']
                );
            } catch (Exception $ex) {
                ttcms()->obj['flash']->{'error'}(
                    sprintf(
                        'SQLSTATE[%s]: %s',
                        $ex->getCode(),
                        $ex->getMessage()
                    ),
                    $app->req->server['HTTP_REFERER']
                );
            }
        }

        $posttypes = (
                new PosttypeRepository(
                    new PosttypeMapper(
                        $db,
                        new HelperContext()
                    )
                )
                )->{'findAll'}();

        $app->foil->render(
            'main::admin/post/posttype',
            [
                'title' => esc_html__('Post Types'),
                'posttypes' => $posttypes
            ]
        );
    });

    /**
     * Before route checks to make sure the logged in
     * user has the permission to edit a posttype.
     */
    $app->before('GET|POST', '/post-type/(\d+)/', function () {
        if (!current_user_can('update_posts')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__(
                    'You do not have permission to update posts or post types.'
                ),
                admin_url()
            );
            exit();
        }
    });

    $app->match('GET|POST', '/post-type/(\d+)/', function ($id) use ($app, $db, $current_user) {
        if ($app->req->isPost()) {
            try {
                $data = array_merge(['posttype_id' => (int) $id], $app->req->post);

                ttcms_update_posttype($data);

                ttcms_logger_activity_log_write(
                    esc_html__('Update Record'),
                    esc_html__('Post Type'),
                    $app->req->post['posttype_title'],
                    esc_html($current_user->getLogin())
                );

                ttcms()->obj['flash']->{'success'}(
                    ttcms()->obj['flash']->{'notice'}(
                        200
                    ),
                    $app->req->server['HTTP_REFERER']
                );
            } catch (Exception $ex) {
                Cascade::getLogger('error')->{'error'}(
                    sprintf(
                        'SQLSTATE[%s]: %s',
                        $ex->getCode(),
                        $ex->getMessage()
                    )
                );

                ttcms()->obj['flash']->{'error'}(
                    ttcms()->obj['flash']->{'notice'}(
                        409
                    )
                );
            }
        }

        $posttype = (
            new PosttypeRepository(
                new PosttypeMapper(
                    $db,
                    new HelperContext()
                )
            )
        )->{'findById'}((int) $id);

        $posttypes = (
            new PosttypeRepository(
                new PosttypeMapper(
                    $db,
                    new HelperContext()
                )
            )
        )->{'findAll'}();

        /**
         * If the posttype doesn't exist, then it
         * is false and a 404 page should be displayed.
         */
        if ($posttype === false) {
            $app->res->_format('json', 404);
            exit();
        } elseif (empty($posttype) === true) {
            $app->res->_format('json', 404);
            exit();
        } else {
            $app->foil->render(
                'main::admin/post/update-posttype',
                [
                    'title' => esc_html__('Update Post Type'),
                    'posttype' => $posttype,
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
        if (!current_user_can('delete_posts')) {
            ttcms()->obj['flash']->{'error'}(
                esc_html__(
                    'You do not have permission to delete posts or post types.'
                ),
                admin_url()
            );
            exit();
        }
    });

    $app->get('/post-type/(\d+)/d/', function ($id) use ($current_user) {
        $title = get_posttype_title($id);

        $posttype = ttcms_delete_posttype($id);

        if ($posttype) {
            ttcms_logger_activity_log_write(
                esc_html__('Delete Record'),
                esc_html__('Post Type'),
                $title,
                esc_html($current_user->getLogin())
            );

            ttcms()->obj['flash']->{'success'}(
                ttcms()->obj['flash']->{'notice'}(
                    200
                ),
                admin_url('post-type/')
            );
        } else {
            ttcms()->obj['flash']->{'error'}(
                ttcms()->obj['flash']->{'notice'}(
                    409
                ),
                admin_url('post-type/')
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
