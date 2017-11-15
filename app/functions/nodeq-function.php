<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Exception\Exception;
use Cascade\Cascade;

/**
 * TriTan CMS NodeQ Functions
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Login Details Email
 * 
 * Function used to send login details to new
 * user.
 * 
 * @since 1.0.0
 */
function ttcms_nodeq_login_details()
{

    $sql = app()->db->table('login_details')->where('sent', (int) 0)->get();

    if ($sql->count() == 0) {
        $sql->delete();
    }

    if ($sql->count() > 0) {
        foreach ($sql as $r) {
            $message = _escape(get_option('person_login_details'));
            $message = str_replace('#login#', _escape($r['user_login']), $message);
            $message = str_replace('#fname#', _escape($r['user_fname']), $message);
            $message = str_replace('#lname#', _escape($r['user_lname']), $message);
            $message = str_replace('#name#', get_name((int) _escape($r['user_id'])), $message);
            $message = str_replace('#id#', _escape($r['user_id']), $message);
            $message = str_replace('#password#', _escape($r['user_pass']), $message);
            $message = str_replace('#url#', get_base_url(), $message);
            $message = str_replace('#sitename#', app()->hook->{'get_option'}('sitename'), $message);
            $message = process_email_html($message, _t("TriTan CMS Login Details"));
            $headers[] = sprintf("From: %s <auto-reply@%s>", _t('TriTan CMS :: ') . app()->hook->{'get_option'}('sitename'), get_domain_name());
            if (!function_exists('ttcms_smtp')) {
                $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                $headers[] = sprintf("X-Mailer: TriTan CMS %s", CURRENT_RELEASE);
            }

            try {
                _ttcms_email()->ttcmsMail(_escape($r['email']), _t("TriTan CMS Login Details"), $message, $headers);
            } catch (phpmailerException $e) {
                Cascade::getLogger('system_email')->alert(sprintf('PHPMAILER[%s]: %s', $e->getCode(), $e->getMessage()));
            } catch (Exception $e) {
                Cascade::getLogger('system_email')->alert(sprintf('PHPMAILER[%s]: %s', $e->getCode(), $e->getMessage()));
            }

            $upd = app()->db->table('login_details')->where('ld_id', (int) _escape($r['ld_id']));
            $upd->update([
                'sent' => 1
            ]);
        }
    }
}