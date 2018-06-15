<?php

use TriTan\Functions as func;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

$app->get('/logout/', function () use($app) {
    $user = func\ttcms_get_current_user();

    func\ttcms_logger_activity_log_write(func\_t('Authentication', 'tritan-cms'), func\_t('Logout', 'tritan-cms'), func\get_name(func\_escape($user->user_id)), func\_escape($user->user_login));

    if (strpos($app->req->server['HTTP_REFERER'], 'admin') !== FALSE) {
        $logout_link = $app->hook->{'apply_filter'}('user_logout_redirect', func\get_base_url() . 'login' . '/');
    } else {
        $logout_link = $app->hook->{'apply_filter'}('admin_logout_redirect', $app->req->server['HTTP_REFERER']);
    }

    /**
     * This function is documented in app/functions/auth-function.php.
     * 
     * @since 0.9
     */
    func\ttcms_clear_auth_cookie();

    /**
     * Fires after a user has logged out.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('ttcms_logout');

    func\ttcms_redirect($logout_link);
});

$app->post('/reset-password/', function () use($app) {
    $user = $app->db->table('user')
            ->where('user_login', $app->req->post['username'])
            ->where('user_email', $app->req->post['email'])
            ->first();

    if ((int) func\_escape($user['user_id']) >= 1) {
        $password = func\ttcms_generate_password();
        $reset = $app->db->table('user');
        $reset->begin();
        try {
            $reset->where('user_id', (int) func\_escape($user['user_id']))
                    ->update([
                        'user_pass' => func\ttcms_hash_password($password)
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
            func\ttcms_logger_activity_log_write(func\_t('Update Record', 'tritan-cms'), func\_t('Reset Password', 'tritan-cms'), func\get_name(func\_escape($user['user_id'])), func\get_user_value(func\get_current_user_id(), 'user_login'));
            func\_ttcms_flash()->{'success'}(func\_t('A new password was sent to your email. May take a few minutes to arrive, so please be patient', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
        } catch (Exception $ex) {
            $reset->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            func\_ttcms_flash()->{'error'}($ex->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    } else {
        func\_ttcms_flash()->{'error'}(func\_t('The username or email you entered was incorrect.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    }
});

/**
 * If the requested page does not exist,
 * return a 404.
 */
$app->setError(function() use($app) {
    $app->res->_format('json', 404);
});
