<?php

namespace TriTan\Functions\Logger;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions\Dependency;
/**
 * TriTan CMS Logging Functions
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
use Cascade\Cascade;

$config = [
    'version' => 1,
    'disable_existing_loggers' => false,
    'formatters' => [
        'spaced' => [
            'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'include_stacktraces' => true
        ],
        'dashed' => [
            'format' => "%datetime%-%channel%.%level_name% - %message% - %context% - %extra%\n"
        ],
        'exception' => [
            'format' => "[%datetime%] %message% %context% %extra%\n",
            'include_stacktraces' => true
        ]
    ],
    'handlers' => [
        'console' => [
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'DEBUG',
            'formatter' => 'exception',
            'stream' => 'php://stdout'
        ],
        'info_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'INFO',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => BASE_PATH . 'static' . DS . 'tmp' . DS . 'logs' . DS . 'ttcms-info.txt'
        ],
        'error_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'ERROR',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => BASE_PATH . 'static' . DS . 'tmp' . DS . 'logs' . DS . 'ttcms-error.txt'
        ],
        'notice_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'NOTICE',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => BASE_PATH . 'static' . DS . 'tmp' . DS . 'logs' . DS . 'ttcms-notice.txt'
        ],
        'critical_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'CRITICAL',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => BASE_PATH . 'static' . DS . 'tmp' . DS . 'logs' . DS . 'ttcms-critical.txt'
        ],
        'alert_file_handler' => [
            'class' => '\TriTan\MailHandler',
            'level' => 'ALERT',
            'formatter' => 'exception',
            'mailer' => new \TriTan\Email,
            'message' => 'This message will be replaced with the real one.',
            'email_to' => app()->hook->{'apply_filter'}('system_alert_email', app()->hook->{'get_option'}('admin_email')),
            'subject' => \TriTan\Functions\Core\_t('TriTan CMS System Alert!', 'tritan-cms')
        ]
    ],
    'processors' => [
        'tag_processor' => [
            'class' => 'Monolog\Processor\TagProcessor'
        ]
    ],
    'loggers' => [
        'info' => [
            'handlers' => ['console', 'info_file_handler']
        ],
        'error' => [
            'handlers' => ['console', 'error_file_handler']
        ],
        'notice' => [
            'handlers' => ['console', 'notice_file_handler']
        ],
        'critical' => [
            'handlers' => ['console', 'critical_file_handler']
        ],
        'system_email' => [
            'handlers' => ['console', 'alert_file_handler']
        ]
    ]
];

Cascade::fileConfig(app()->hook->{'apply_filter'}('monolog_cascade_config', $config));

/**
 * Default Error Handler
 * 
 * Sets the default error handler to handle
 * PHP errors and exceptions.
 *
 * @file app/functions/logger-function.php
 * 
 * @since 0.9
 */
function ttcms_error_handler($type, $string, $file, $line)
{
    $logger = Dependency\_ttcms_logger();
    $logger->logError($type, $string, $file, $line);
}

/**
 * Write Activity Logs to Database.
 *
 * @file app/functions/logger-function.php
 * 
 * @since 0.9
 */
function ttcms_logger_activity_log_write($action, $process, $record, $uname)
{
    $logger = Dependency\_ttcms_logger();
    $logger->writeLog($action, $process, $record, $uname);
}

/**
 * Purges the error log of old records.
 *
 * @file app/functions/logger-function.php
 * 
 * @since 0.9
 */
function ttcms_logger_error_log_purge()
{
    $logger = Dependency\_ttcms_logger();
    $logger->purgeErrorLog();
}

/**
 * Purges the activity log of old records.
 *
 * @file app/functions/logger-function.php
 * 
 * @since 0.9
 */
function ttcms_logger_activity_log_purge()
{
    $logger = Dependency\_ttcms_logger();
    $logger->purgeActivityLog();
}

/**
 * Custom error log function for better PHP logging.
 * 
 * @file app/functions/logger-function.php
 * 
 * @since 0.9
 * @param string $name
 *            Log channel and log file prefix.
 * @param string $message
 *            Message printed to log.
 */
function ttcms_monolog($name, $message)
{
    $log = new \Monolog\Logger(_trim($name));
    $log->pushHandler(new \Monolog\Handler\StreamHandler(BASE_PATH . 'static' . DS . 'tmp' . DS . 'logs' . DS . _trim($name) . '.' . format_date('now', 'm-d-Y') . '.txt'));
    $log->addError($message);
}

/**
 * Set the system environment.
 * 
 * @file app/functions/logger-function.php
 * 
 * @since 0.9
 */
function ttcms_set_environment()
{
    /**
     * Error log setting
     */
    if (APP_ENV == 'DEV') {
        /**
         * Print errors to the screen.
         */
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 'On');
    } else {
        /**
         * Log errors to a file.
         */
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', Config::get('site_path') . 'files' . DS . 'logs' . DS . 'ttcms-error-' . format_date('now', 'Y-m-d') . '.txt');
        set_error_handler('TriTan\\Functions\\Logger\\ttcms_error_handler', E_ALL & ~E_NOTICE);
    }
}
