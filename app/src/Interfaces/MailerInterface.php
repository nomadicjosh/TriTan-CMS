<?php
namespace TriTan\Interfaces;

interface MailerInterface
{
    /**
     * Send mail, similar to PHP's mail function.
     *
     * @since 0.9.9
     * @param string $to Array or comma-separated list of email addresses to send message.
     * @param string $subject Subject of the email.
     * @param mixed $message The body of the email.
     * @param mixed $headers Email headers sent.
     * @param mixed $attachments Attachments to be sent with the email.
     * @return mixed
     */
    public function mail($to, $subject, $message, $headers = '', $attachments = []);
}
