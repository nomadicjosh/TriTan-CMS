<?php

namespace TriTan;

use TriTan\Config;
use TriTan\Exception;
use Cascade\Cascade;
use TriTan\Functions as func;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Event Logger for Errors and Activity.
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Logger
{

    /**
     * Application object.
     * @var type 
     */
    public $app;

    public function __construct()
    {
        $this->app = \Liten\Liten::getInstance();
    }

    /**
     * Writes a log to the log table in the database.
     * 
     * @since 0.9
     */
    public function writeLog($action, $process, $record, $uname)
    {
        $create = date("Y-m-d H:i:s", time());
        $current_date = strtotime($create);
        /* 20 days after creation date */
        $expire = date("Y-m-d H:i:s", $current_date += 1728000);

        $expires_at = $this->app->hook->{'apply_filter'}('activity_log_expires', $expire);

        $log = $this->app->db->table(Config::get('tbl_prefix') . 'activity');
        $log->begin();
        try {
            $log->insert([
                'activity_id' => func\auto_increment(Config::get('tbl_prefix') . 'activity', 'activity_id'),
                'action' => $action,
                'process' => $process,
                'record' => $record,
                'uname' => $uname,
                'created_at' => $create,
                'expires_at' => $expires_at,
            ]);
            $log->commit();
        } catch (Exception $ex) {
            $log->rollback();
            Cascade::getLogger('error')->error($ex->getMessage());
            func\_ttcms_flash()->error(func\_ttcms_flash()->notice(409));
        }
    }

    /**
     * Purges audit trail logs that are older than 30 days old.
     * 
     * @since 0.9
     */
    public function purgeActivityLog()
    {
        $log_count = $this->app->db->table(Config::get('tbl_prefix') . 'activity')
                ->where('expires_at', '<=', date('Y-m-d H:i:s', time()))
                ->count();

        if ($log_count > 0) {
            $delete = $this->app->db->table(Config::get('tbl_prefix') . 'activity');
            $delete->begin();
            try {
                $delete->where('expires_at', '<=', date('Y-m-d H:i:s', time()))
                        ->delete();
                $delete->commit();
            } catch (Exception $ex) {
                $delete->rollback();
                Cascade::getLogger('error')->error($ex->getMessage());
                func\_ttcms_flash()->error(func\_ttcms_flash()->notice(409));
            }
        }
    }

    /**
     * Purges system error logs that are older than 30 days old.
     * 
     * @since 0.9
     */
    public function purgeErrorLog()
    {
        $logs = glob(Config::get('site_path') . 'files' . DS . 'logs' . DS . '*.txt');
        if (is_array($logs)) {
            foreach ($logs as $log) {
                $filelastmodified = filemtime($log);
                if ((time() - $filelastmodified) >= 30 * 24 * 3600 && is_file($log)) {
                    unlink($log);
                }
            }
        }
    }

    public function logError($type, $string, $file, $line)
    {
        $date = new \DateTime();
        $log = $this->app->db->table(Config::get('tbl_prefix') . 'error');
        $log->begin();
        try {
            $log->insert([
                'error_id' => func\auto_increment(Config::get('tbl_prefix') . 'error', 'error_id'),
                'time' => $date->getTimestamp(),
                'type' => (int) $type,
                'string' => (string) $string,
                'file' => (string) $file,
                'line' => (int) $line,
                'add_date' => (string) \Jenssegers\Date\Date::now()
            ]);
            $log->commit();
        } catch (Exception $ex) {
            $log->rollback();
            Cascade::getLogger('error')->error($ex->getMessage());
            func\_ttcms_flash()->error(func\_ttcms_flash()->notice(409));
        }
    }

    public function error_constant_to_name($value)
    {
        $values = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            E_ALL => 'E_ALL'
        );

        return $values[$value];
    }

}
