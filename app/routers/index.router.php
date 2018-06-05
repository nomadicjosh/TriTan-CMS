<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

$app->get('/logout/', function () use($app) {
    $user = ttcms_get_current_user();

    ttcms_logger_activity_log_write(_t('Authentication', 'tritan-cms'), _t('Logout', 'tritan-cms'), get_name(_escape($user->user_id)), _escape($user->user_login));

    if (strpos($app->req->server['HTTP_REFERER'], 'admin') !== FALSE) {
        $logout_link = $app->hook->{'apply_filter'}('user_logout_redirect', get_base_url() . 'login' . '/');
    } else {
        $logout_link = $app->hook->{'apply_filter'}('admin_logout_redirect', $app->req->server['HTTP_REFERER']);
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
    app()->hook->{'do_action'}('ttcms_logout');

    ttcms_redirect($logout_link);
});

$app->post('/reset-password/', function () use($app) {
    $user = $app->db->table('user')
            ->where('user_login', $app->req->post['username'])
            ->where('user_email', $app->req->post['email'])
            ->first();

    if ((int) _escape($user['user_id']) > 1) {
        $password = ttcms_generate_password();
        $reset = $app->db->table('user');
        $reset->begin();
        try {
            $reset->where('user_id', (int) _escape($user['user_id']))
                    ->update([
                        'user_pass' => ttcms_hash_password($password)
            ]);
            $reset->commit();
            /**
             * This action fires after user's password has been reset.
             * 
             * @since 0.9
             * @param array $user       User data array.
             * @param string $password  Plaintext password.
             */
            $app->hook->{'do_action'}('reset_password_route', $user, $password);
            _ttcms_flash()->{'success'}(_t('A new password was sent to your email.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
        } catch (Exception $ex) {
            $reset->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            _ttcms_flash()->{'error'}($ex->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    } else {
        _ttcms_flash()->{'error'}(_t('The username or email you entered was incorrect.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    }
});

/**
 * If the requested page does not exist,
 * return a 404.
 */
$app->setError(function() use($app) {
    $app->res->_format('json', 404);
});
