<?php namespace TriTan;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use TriTan\Exception\IOException;
use Cascade\Cascade;

/**
 * Task Manager Queue
 *  
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class Queue
{

    /**
     * Application object.
     * 
     * @var object
     */
    public $app;

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var array
     */
    protected $jobs = [];

    /**
     * Node where queues are saved.
     * 
     * @var type 
     */
    public $node = 'tasks';

    /**
     * Set the directory for where pid is found.
     * 
     * @var type 
     */
    public $dir = '';

    /**
     * ID of the running process.
     * 
     * @var type 
     */
    public $pid = 0;

    /**
     * Table prefix.
     * @var type 
     */
    public $prefix;

    /**
     * 
     * @param \Liten\Liten $liten
     */
    public function __construct(array $config = [], \Liten\Liten $liten = null)
    {
        $this->setConfig($this->getDefaultConfig());
        $this->setConfig($config);

        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();

        try {
            /**
             * Creates a directory with proper permissions.
             */
            _mkdir(Config::get('cache_path') . 'ttcms_queue');
        } catch (IOException $e) {
            Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
            Cascade::getLogger('system_email')->alert(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
        }
        $this->prefix = Config::get('tbl_prefix');
        $this->dir = Config::get('cache_path') . 'ttcms_queue' . DS;
    }

    /**
     * @return array
     */
    public function getDefaultConfig()
    {
        return [
            'task_callback' => null,
            'action_hook' => null,
            'schedule' => \Jenssegers\Date\Date::now(),
            'max_runtime' => null,
            'enabled' => true,
            'debug' => false,
        ];
    }

    /**
     * @param array
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function jobs()
    {
        return $this->jobs;
    }

    /**
     * Add a job.
     *
     * @param string $job
     * @param array  $config
     *
     * @throws Exception
     */
    public function add($job, array $config)
    {
        if (empty($config['schedule'])) {
            throw new Exception("'schedule' is required for '$job' job", 8176);
        }

        if (empty($config['task_callback'])) {
            throw new Exception("'task_callback' is required for '$job' job", 8662);
        }

        if (!function_exists($config['task_callback'])) {
            throw new Exception("'task_callback' must exist as a function", 8662);
        }

        if (empty($config['action_hook'])) {
            throw new Exception("'action_hook' is required for '$job' job", 8465);
        }

        $_config = array_merge($this->config, $config);
        $this->jobs[$job] = $_config;
    }

    public function node()
    {
        return $this->prefix . $this->node;
    }

    /**
     * Create a new job and save it to the queue or update the job if it exists.
     * 
     * @since 1.0.0
     */
    public function enqueue($args)
    {
        $tasks = ttcms_parse_args($args);

        $count = $this->app->db->table($this->node())
            ->where('pid', $tasks['task_worker']['pid'])
            ->get();
        if (count($count) >= 1) {
            $node = $this->app->db->table($this->node());
            $node->begin();
            try {
                $node->where('pid', $tasks['task_worker']['pid'])
                    ->update([
                        'pid' => if_null($tasks['task_worker']['pid']),
                        'name' => if_null($tasks['task_worker']['name']),
                        'task_callback' => if_null($tasks['task_worker']['task_callback']),
                        'action_hook' => if_null($tasks['task_worker']['action_hook']),
                        'schedule' => if_null($tasks['task_worker']['schedule']),
                        'debug' => if_null($tasks['task_worker']['debug']),
                        'max_runtime' => if_null($tasks['task_worker']['max_runtime']),
                        'enabled' => if_null($tasks['task_worker']['enabled'])
                ]);
                $node->commit();
            } catch (Exception $ex) {
                $node->rollback();
                Cascade::getLogger('error')->error(sprintf('QUEUESTATE[2646]: %s', $ex->getMessage()));
            }
        } else {
            $node = $this->app->db->table($this->node());
            $node->begin();
            try {
                $node->insert([
                    'tasks_id' => auto_increment($this->node(), 'tasks_id'),
                    'pid' => if_null($tasks['task_worker']['pid']),
                    'name' => if_null($tasks['task_worker']['name']),
                    'task_callback' => if_null($tasks['task_worker']['task_callback']),
                    'action_hook' => if_null($tasks['task_worker']['action_hook']),
                    'schedule' => if_null($tasks['task_worker']['schedule']),
                    'debug' => if_null($tasks['task_worker']['debug']),
                    'max_runtime' => if_null($tasks['task_worker']['max_runtime']),
                    'enabled' => if_null($tasks['task_worker']['enabled'])
                ]);
                $node->commit();
            } catch (Exception $ex) {
                $node->rollback();
                Cascade::getLogger('error')->error(sprintf('QUEUESTATE[2646]: %s', $e->getMessage()));
            }
        }
    }

    public function getMyPid()
    {
        return $this->pid;
    }

    /**
     * @param string $lockFile
     * @param array $config
     * @throws Exception
     */
    protected function checkMaxRuntime($lockFile, $config)
    {
        $max_runtime = $config['max_runtime'];
        if ($max_runtime === null) {
            return;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            throw new Exception('"max_runtime" is not supported on Windows.', 8712);
        }

        $runtime = $this->getLockLifetime($lockFile);
        if ($runtime < $max_runtime) {
            return;
        }

        throw new Exception("Max Runtime of $max_runtime secs exceeded! Current runtime: $runtime secs.", 8712);
    }

    /**
     * @param string $lockFile
     * @return int
     */
    public function getLockLifetime($lockFile)
    {
        if (!file_exists($lockFile)) {
            return 0;
        }

        $pid = _file_get_contents($lockFile);
        if (!empty($pid)) {
            return 0;
        }

        $stat = stat($lockFile);

        return (time() - $stat["mtime"]);
    }

    public function releaseLockFile($lockFile)
    {
        @unlink($lockFile);
        if (!file_exists($lockFile)) {
            $fh = fopen($lockFile, 'a');
            fclose($fh);
        }
    }

    /**
     * @param string|callable $schedule
     * @return bool
     */
    public function isDue($schedule)
    {
        if (is_callable($schedule)) {
            return call_user_func($schedule);
        }

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $schedule);
        if ($dateTime !== false) {
            return $dateTime->format('Y-m-d H:i') == (date('Y-m-d H:i'));
        }

        return \Cron\CronExpression::factory((string) $schedule)->isDue();
    }

    public function run()
    {
        foreach ($this->jobs as $config) {
            /**
             * The queue's lock file.
             */
            $lockFile = $this->dir . $config['pid'];
            /**
             * Check if queue is due or not due.
             */
            if (!$this->isDue($config['schedule'])) {
                continue;
            }
            /**
             * Deletes and recreates the queue's lock file.
             */
            $this->releaseLockFile($lockFile);
            /**
             * If config is not set or is false,
             * do not continue
             */
            if (!$config['enabled'] || $config['enabled'] == false) {
                continue;
            }
            /**
             * Checks max runtime.
             */
            try {
                $this->checkMaxRuntime($lockFile, $config);
            } catch (Exception $e) {
                if ($config['debug']) {
                    Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()), ['PID' => $config['pid'], 'Queue' => $config['name']]);
                    Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()), ['PID' => $config['pid'], 'Queue' => $config['name']]);
                }
                return;
            }
            /**
             * At start of executing the action.
             */
            $time_start = microtime(true);
            /**
             * The action that should run when queue is called.
             */
            $this->app->hook->{'do_action'}($config['action_hook']);
            /**
             * At the end of executing the action.
             */
            $time_end = (microtime(true) - $time_start);

            $upd = $this->app->db->table($this->node());
            $upd->begin();
            try {
                /**
                 * Update the queue's # of runs as well as the last
                 * time it ran.
                 */
                $upd->where('pid', $config['pid'])
                    ->update([
                        'executions' => +1,
                        'lastrun' => (string) \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s'),
                        'last_runtime' => (double) $time_end
                ]);
                $upd->commit();
            } catch (Exception $e) {
                $upd->rollback();
                Cascade::getLogger('error')->error(sprintf('QUEUESTATE[2646]: %s', $e->getMessage()));
            }
        }
    }
}
