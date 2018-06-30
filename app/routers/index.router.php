<?php

use TriTan\Functions\Dependency;
use TriTan\Functions\Auth;
use TriTan\Functions\User;
use TriTan\Functions\Core;
use TriTan\Functions\Logger;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

$app->get('/logout/', function () use($app) {
    $user = User\ttcms_get_current_user();

    Logger\ttcms_logger_activity_log_write(Core\_t('Authentication', 'tritan-cms'), Core\_t('Logout', 'tritan-cms'), User\get_name(Core\_escape($user->user_id)), Core\_escape($user->user_login));

    if (strpos($app->req->server['HTTP_REFERER'], 'admin') !== FALSE) {
        $logout_link = $app->hook->{'apply_filter'}('user_logout_redirect', Core\get_base_url() . 'login' . '/');
    } else {
        $logout_link = $app->hook->{'apply_filter'}('admin_logout_redirect', $app->req->server['HTTP_REFERER']);
    }

    /**
     * This function is documented in app/functions/auth-function.php.
     * 
     * @since 0.9
     */
    Auth\ttcms_clear_auth_cookie();

    /**
     * Fires after a user has logged out.
     *
     * @since 0.9
     */
    app()->hook->{'do_action'}('ttcms_logout');

    Core\ttcms_redirect($logout_link);
});

$app->post('/reset-password/', function () use($app) {
    $user = $app->db->table('user')
            ->where('user_login', $app->req->post['username'])
            ->where('user_email', $app->req->post['email'])
            ->first();

    if ((int) Core\_escape($user['user_id']) >= 1) {
        $password = Core\ttcms_generate_password();
        $reset = $app->db->table('user');
        $reset->begin();
        try {
            $reset->where('user_id', (int) Core\_escape($user['user_id']))
                    ->update([
                        'user_pass' => Core\ttcms_hash_password($password)
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
            Logger\ttcms_logger_activity_log_write(Core\_t('Update Record', 'tritan-cms'), Core\_t('Reset Password', 'tritan-cms'), User\get_name(Core\_escape($user['user_id'])), User\get_user_value(User\get_current_user_id(), 'user_login'));
            Dependency\_ttcms_flash()->{'success'}(Core\_t('A new password was sent to your email. May take a few minutes to arrive, so please be patient', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
        } catch (Exception $ex) {
            $reset->rollback();
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            Dependency\_ttcms_flash()->{'error'}($ex->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    } else {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('The username or email you entered was incorrect.', 'tritan-cms'), $app->req->server['HTTP_REFERER']);
    }
});

/**
 * If the requested page does not exist,
 * return a 404.
 */
$app->setError(function() use($app) {
    $app->res->_format('json', 404);
});
