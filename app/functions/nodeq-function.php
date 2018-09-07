<?php
use TriTan\Exception\Exception;
use Cascade\Cascade;
use TriTan\Common\Options\Options;
use TriTan\Database;
use TriTan\Common\Context\HelperContext;
use TriTan\Common\Mailer;

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
    $db = new \TriTan\Database();
    $option = new Options(new Database(), new HelperContext());
    $sql = $db->table('login_details')->where('sent', (int) 0)->get();

    if ($sql->count() == 0) {
        $sql->delete();
    }

    if ($sql->count() > 0) {
        foreach ($sql as $r) {
            $message = escape($option->{'read'}('person_login_details'));
            $message = str_replace('#login#', esc_html($r['user_login']), $message);
            $message = str_replace('#fname#', esc_html($r['user_fname']), $message);
            $message = str_replace('#lname#', esc_html($r['user_lname']), $message);
            $message = str_replace('#name#', get_name((int) esc_html($r['user_id'])), $message);
            $message = str_replace('#id#', esc_html($r['user_id']), $message);
            $message = str_replace('#password#', esc_html($r['user_pass']), $message);
            $message = str_replace('#url#', site_url(), $message);
            $message = str_replace('#sitename#', $option->{'read'}('sitename'), $message);
            $message = process_email_html($message, esc_html__("TriTan CMS Login Details"));
            $headers[] = sprintf(
                "From: %s <auto-reply@%s>",
                esc_html__('TriTan CMS :: ') . $option->{'read'}('sitename'),
                get_domain_name()
            );
            if (!function_exists('ttcms_mail_send')) {
                $headers[] = 'Content-Type: text/html; charset="UTF-8"';
                $headers[] = sprintf("X-Mailer: TriTan CMS %s", CURRENT_RELEASE);
            }

            try {
                (new Mailer())->{'mail'}(
                    esc_html($r['email']),
                    esc_html__("TriTan CMS Login Details"),
                    $message,
                    $headers
                );
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                Cascade::getLogger('system_email')->{'alert'}(
                    sprintf(
                        'PHPMAILER[%s]: %s',
                        $e->getCode(),
                        $e->getMessage()
                    )
                );
            } catch (Exception $e) {
                Cascade::getLogger('system_email')->{'alert'}(
                    sprintf(
                        'PHPMAILER[%s]: %s',
                        $e->getCode(),
                        $e->getMessage()
                    )
                );
            }

            $upd = $db->table('login_details')->where('login_details_id', (int) esc_html($r['login_details_id']));
            $upd->update([
                'sent' => 1
            ]);
        }
    }
}
