<?php
namespace TriTan;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MailHandler as ttcms_MailHandler;
use TriTan\Database;
use TriTan\Common\Mailer;
use TriTan\Exception\InvalidArgumentException;
use TriTan\Common\Options\Options;
use TriTan\Common\Options\OptionsMapper;
use TriTan\Common\Context\HelperContext;

/**
 * Monolog Handler Email Class
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class MailHandler extends ttcms_MailHandler
{
    protected $mailer;
    protected $email_to;
    protected $subject;
    private $messageTemplate;
    public $app;

    public function __construct(Mailer $mailer, $message, $email_to, $subject, $level = Logger::ALERT, $bubble = true, \Liten\Liten $liten = null)
    {
        parent::__construct($level, $bubble);

        $this->mailer = $mailer;
        $this->email_to = $email_to;
        $this->subject = $subject;
        $this->messageTemplate = $message;
    }

    protected function send($content, array $records)
    {
        return $this->buildMessage((string) $content, $records);
    }

    /**
     * Creates instance of Email to be sent
     *
     * @param  string        $content formatted email body to be sent
     * @param  array         $records Log records that formed the content
     * @return Email
     */
    protected function buildMessage($content, array $records)
    {
        $sitename = get_domain_name();

        $site = (
            new Options(
                new OptionsMapper(
                    new Database(),
                    new HelperContext()
                )
            )
        )->{'read'}('sitename');

        $message = null;
        if ($this->mailer instanceof Mailer) {
            $message = clone $this->mailer;
        } elseif (is_callable($this->mailer)) {
            $message = call_user_func($this->mailer, $content, $records);
        }
        if (!$message instanceof Mailer) {
            throw new InvalidArgumentException('Could not resolve message as instance of Email or a callable returning it');
        }
        if ($records) {
            $subjectFormatter = new LineFormatter($this->subject);
            $headers = "From: $site <auto-reply@$sitename>\r\n";
            $headers .= sprintf("X-Mailer: TriTan CMS %s\r\n", CURRENT_RELEASE);
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $body = process_email_html($content, $subjectFormatter->format($this->getHighestRecord($records)));
            $message = $this->mailer->{'mail'}($this->email_to, $subjectFormatter->format($this->getHighestRecord($records)), $body, $headers);
        }
        return $message;
    }
}
