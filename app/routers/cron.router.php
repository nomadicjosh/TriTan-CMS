<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Exception\Exception;
use TriTan\Config;
use TriTan\Queue\NodeqQueue as Queue;
use Cascade\Cascade;
use TriTan\Functions\Core;
use TriTan\Functions\Logger;

/**
 * Cron Router
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
$app->before('POST|PUT|DELETE|OPTIONS', '/cronjob/', function () use($app) {
    header('Content-Type: application/json');
    $app->res->_format('json', 404);
    exit();
});

$app->get('/cronjob/', function () use($app) {
    if ($app->hook->{'get_option'}('enable_cron_jobs') != (int) 1) {
        exit();
    }

    $app->hook->{'do_action'}('ttcms_task_worker_cron');

    $error_count = $app->db->table(Config::get('tbl_prefix') . 'error')
            ->where('add_date', '<=', strtotime('-5 days'))
            ->count();

    if ((int) $error_count > 0) {
        $error = $app->db->table(Config::get('tbl_prefix') . 'error');
        $error->begin();
        try {
            $error->where('add_date', '<=', strtotime('-5 days'))
                    ->delete();
            $error->commit();
        } catch (Exception $e) {
            $error->rollback();
            Cascade::getLogger('system_email')->alert($e->getMessage(), ['Cron' => 'purgeErrorLog']);
        }
    }

    Logger\ttcms_logger_error_log_purge();
    Logger\ttcms_logger_activity_log_purge();

    try {

        $tasks = $app->db->table(Config::get('tbl_prefix') . 'tasks')
                ->where('enabled', '1');
        if ((int) $tasks->count() > 0) {

            $array = [];
            foreach ($tasks->get() as $task) {
                $array[] = (array) $task;
            }

            foreach ($array as $queue) {
                if (!function_exists($queue['task_callback'])) {
                    $delete = $app->db->table(Config::get('tbl_prefix') . 'tasks');
                    $delete->begin();
                    try {
                        $delete->where('tasks_id', (int) Core\_escape($queue['tasks_id']))
                                ->delete();
                        $delete->commit();
                    } catch (Exception $e) {
                        $delete->rollback();
                        Cascade::getLogger('system_email')->alert(sprintf('QUEUE: %s', $e->getMessage()));
                    }
                }
                $task = new Queue($queue);
                $task->createItem($queue);

                $jobs_to_do = true;
                $start = microtime(true);

                try {
                    while ($jobs_to_do) {
                        $item = $task->claimItem();
                        $data = $app->hook->{'maybe_unserialize'}($item['data']);

                        if ($item) {
                            Cascade::getLogger('info')->info(sprintf('QUEUESTATE[8190]: Processing item %s . . .', $data['pid']), ['Cron' => 'Item']);
                            // Execute the job task in a different function.
                            if ($task->executeAction($data)) {
                                // Delete the item.
                                $task->deleteItem($item);
                                Cascade::getLogger('info')->info(sprintf('QUEUESTATE[8190]: Item %s processed.', $data['pid']), ['Cron' => 'Action Hook']);
                            } else {
                                // Release the item to execute the job task again later.
                                $task->releaseItem($item);
                                Cascade::getLogger('info')->info(sprintf('QUEUESTATE[8190]: Item %s NOT processed.', $data['pid']), ['Cron' => 'Release Item']);
                                $jobs_to_do = false;
                                Cascade::getLogger('info')->info('QUEUESTATE[8190]: Queue not completed. Item not executed.', ['Cron' => 'Release Item']);
                            }
                        } else {
                            $jobs_to_do = false;
                            $time_elapsed = microtime(true) - $start;
                            $number_of_items = $task->numberOfItems();
                            if ($number_of_items == 0) {
                                Cascade::getLogger('info')->info(sprintf('QUEUESTATE[8190]: Queue completed in %s seconds.', $time_elapsed), ['Cron' => '# of Items']);
                            } else {
                                Cascade::getLogger('info')->info(sprintf('QUEUESTATE[8190]: Queue not completed, there are %s items left.', $number_of_items), ['Cron' => '# of Items']);
                            }
                        }
                    }
                } catch (Exception $e) {
                    if ($queue['debug']) {
                        Cascade::getLogger('error')->{'error'}(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()), ['Queue' => 'Claim Queue Item']);
                        Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()), ['Queue' => 'Claim Queue Item']);
                    }
                }
            }
        }
    } catch (Exception $e) {
        Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()), ['Cron' => 'Master']);
    }
});
