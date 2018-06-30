<?php

namespace TriTan\Functions\NodeQ;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Exception\Exception;
use Cascade\Cascade;
use TriTan\Functions\Core;
use TriTan\Functions\User;
use TriTan\Functions\Dependency;

/**
 * TriTan CMS NodeQ Functions
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Login Details Email
 * 
 * Function used to send login details to new
 * user.
 * 
 * @file app/functions/nodeq-function.php
 * 
 * @since 0.9
 */
function ttcms_nodeq_login_details()
{

    $sql = app()->db->table('login_details')->where('sent', (int) 0)->get();

    if ($sql->count() == 0) {
        $sql->delete();
    }

    if ($sql->count() > 0) {
        foreach ($sql as $r) {
            $message = Core\_escape(app()->hook->{'get_option'}('person_login_details'));
            $message = str_replace('#login#', Core\_escape($r['user_login']), $message);
            $message = str_replace('#fname#', Core\_escape($r['user_fname']), $message);
            $message = str_replace('#lname#', Core\_escape($r['user_lname']), $message);
            $message = str_replace('#name#', User\get_name((int) Core\_escape($r['user_id'])), $message);
            $message = str_replace('#id#', Core\_escape($r['user_id']), $message);
            $message = str_replace('#password#', Core\_escape($r['user_pass']), $message);
            $message = str_replace('#url#', Core\get_base_url(), $message);
            $message = str_replace('#sitename#', app()->hook->{'get_option'}('sitename'), $message);
            $message = Core\process_email_html($message, Core\_t("TriTan CMS Login Details", 'tritan-cms'));
            $headers[] = sprintf("From: %s <auto-reply@%s>", Core\_t('TriTan CMS :: ', 'tritan-cms') . app()->hook->{'get_option'}('sitename'), Core\get_domain_name());
            if (!function_exists('ttcms_smtp')) {
                $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                $headers[] = sprintf("X-Mailer: TriTan CMS %s", CURRENT_RELEASE);
            }

            try {
                Dependency\_ttcms_email()->ttcmsMail(Core\_escape($r['email']), _t("TriTan CMS Login Details", 'tritan-cms'), $message, $headers);
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                Cascade::getLogger('system_email')->alert(sprintf('PHPMAILER[%s]: %s', $e->getCode(), $e->getMessage()));
            } catch (Exception $e) {
                Cascade::getLogger('system_email')->alert(sprintf('PHPMAILER[%s]: %s', $e->getCode(), $e->getMessage()));
            }

            $upd = app()->db->table('login_details')->where('ld_id', (int) Core\_escape($r['ld_id']));
            $upd->update([
                'sent' => 1
            ]);
        }
    }
}
