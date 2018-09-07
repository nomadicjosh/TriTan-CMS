<?php
use TriTan\Common\Hooks\ActionFilterHook as hook;
use TriTan\Common\PasswordGenerate;
use TriTan\Common\PasswordHash;
use TriTan\Common\Uri;

$db = new \TriTan\Database();

hook::getInstance()->{'doAction'}('maintenance_mode', $app);

$app->get('/logout/', function () use ($app) {
    $user = ttcms_get_current_user();

    ttcms_logger_activity_log_write(
        t__('Authentication', 'tritan-cms'),
        t__('Logout', 'tritan-cms'),
        get_name((int) $user->getId()),
        $user->getLogin()
    );

    if (strpos($app->req->server['HTTP_REFERER'], 'admin') !== false) {
        $logout_link = hook::getInstance()->{'applyFilter'}(
            'user_logout_redirect',
            login_url()
        );
    } else {
        $logout_link = hook::getInstance()->{'applyFilter'}(
            'admin_logout_redirect',
            $app->req->server['HTTP_REFERER']
        );
    }

    /**
     * This function is documented in app/functions/auth-function.php.
     *
     * @since 0.9
     */
    ttcms_clear_auth_cookie();

    /**
     * Fires after a user has logged out.
     *
     * @since 0.9
     */
    hook::getInstance()->{'doAction'}('ttcms_logout');

    (new Uri(hook::getInstance()))->{'redirect'}($logout_link);
});

$app->post('/reset-password/', function () use ($app, $db) {
    $user = $db->table('user')
            ->where('user_login', $app->req->post['username'])
            ->where('user_email', $app->req->post['email'])
            ->first();

    if ((int) esc_html($user['user_id']) >= 1) {
        $password = (new PasswordGenerate(hook::getInstance()))->{'generate'}();
        $reset = $db->table('user');
        $reset->begin();
        try {
            $reset->where('user_id', (int) esc_html($user['user_id']))
                    ->update([
                        'user_pass' => (new PasswordHash(hook::getInstance()))->{'hash'}($password)
                    ]);
            $reset->commit();
            /**
             * This action fires after user's password has been reset.
             *
             * @since 0.9
             * @param array $user       User data array.
             * @param string $password  Plaintext password.
             */
            hook::getInstance()->{'doAction'}(
                'reset_password_route',
                $user,
                $password
            );

            ttcms_logger_activity_log_write(
                t__('Update Record', 'tritan-cms'),
                t__('Reset Password', 'tritan-cms'),
                get_name((int) esc_html($user['user_id'])),
                get_user_value(get_current_user_id(), 'user_login')
            );

            ttcms()->obj['flash']->{'success'}(
                t__('A new password was sent to your email. May take a few minutes to arrive, so please be patient', 'tritan-cms'),
                $app->req->server['HTTP_REFERER']
            );
        } catch (Exception $ex) {
            $reset->rollback();
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
    } else {
        ttcms()->obj['flash']->{'error'}(
            t__('The username or email you entered was incorrect.', 'tritan-cms'),
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
