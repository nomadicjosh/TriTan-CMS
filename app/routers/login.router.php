<?php
use TriTan\Common\Hooks\ActionFilterHook as hook;
use TriTan\Common\Uri;

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/login', function () {
    hook::getInstance()->{'doAction'}('before_router_login');
});

$app->group('/login', function () use ($app) {
    /**
     * Before route check.
     */
    $app->before('GET|POST', '/', function () use ($app) {
        if (is_user_logged_in()) {
            $redirect_to = (
                $app->req->get['redirect_to'] != null ? $app->req->get['redirect_to'] : admin_url()
            );
            (new Uri(hook::getInstance()))->{'redirect'}($redirect_to);
        }

        /**
         * Fires before a user has logged in.
         *
         * @since 0.9
         */
        hook::getInstance()->{'doAction'}('ttcms_login');
    });

    $app->match('GET|POST', '/', function () use ($app) {
        if ($app->req->isPost()) {
            /**
             * Filters where the admin should be redirected after successful login.
             */
            $login_link = hook::getInstance()->{'applyFilter'}(
                'admin_login_redirect',
                admin_url()
            );
            /**
             * This function is documented in app/functions/auth-function.php.
             *
             * @since 0.9
             */
            ttcms_authenticate_user(
                $app->req->post['user_login'],
                $app->req->post['user_pass'],
                $app->req->post['rememberme']
            );

            ttcms_logger_activity_log_write(
                t__('Authentication', 'tritan-cms'),
                t__('Login', 'tritan-cms'),
                $app->req->post['user_login'],
                $app->req->post['user_login']
            );
            
            (new Uri(hook::getInstance()))->{'redirect'}($login_link);
        }

        $app->foil->render(
            'main::login/index',
            [
                'title' => t__('Login', 'tritan-cms')
            ]
        );
    });
});
