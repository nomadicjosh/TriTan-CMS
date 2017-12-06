<?php namespace TriTan\Queue;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;

/**
 * NodeQ Task Manager Queue
 *  
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class NodeqQueue implements ReliableQueueInterface, QueueGarbageCollectionInterface
{

    /**
     * The name of the queue this instance is working with.
     *
     * @var string
     */
    protected $name;

    /**
     * How long the processing is expected to take in seconds.
     */
    protected $lease_time;

    /**
     * Send NodeQ Queue internal messages to 'ttcms-error*.txt'
     */
    protected $debug;

    /**
     * When should the process run.
     */
    protected $schedule = '* * * * *';

    /**
     * The nodeq table name.
     */
    public $node = 'ttcms_queue';

    /**
     * Application object.
     * 
     * @var object
     */
    public $app;

    /**
     * Table prefix.
     * 
     * @var type 
     */
    public $prefix;

    /**
     * Constructs a \Liten\Liten $liten object.
     *
     * @param array $config
     *   The name of the queue.
     * @param \Liten\Liten $liten
     *   Liten framework object.
     */
    public function __construct(array $config = [], \Liten\Liten $liten = null)
    {
        $this->name = $config['name'];
        $this->lease_time = $config['lease_time'];
        $this->schedule = $config['schedule'];
        $this->debug = (bool) $config['debug'];
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();
        $this->prefix = Config::get('tbl_prefix');
    }

    public function node()
    {
        return $this->prefix . $this->node;
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

    /**
     * {@inheritdoc}
     */
    public function createItem($data)
    {
        $try_again = false;
        try {
            $id = $this->doCreateItem($data);
        } catch (Exception $e) {

            Cascade::getLogger('error')->error(sprintf('NODEQSTATE: %s', $e->getMessage()), ['Queue' => 'NodeQueue::createItem']);
        }
        /**
         * Now that the node has been created, try again if necessary.
         */
        if ($try_again) {
            $id = $this->doCreateItem($data);
        }
        return $id;
    }

    /**
     * Adds a queue item and store it directly to the queue.
     *
     * @param $data
     *   Arbitrary data to be associated with the new task in the queue.
     *
     * @return
     *   A unique ID if the item was successfully created and was (best effort)
     *   added to the queue, otherwise false. We don't guarantee the item was
     *   committed to disk etc, but as far as we know, the item is now in the
     *   queue.
     */
    protected function doCreateItem($data)
    {
        /**
         * Check if queue is due or not due.
         */
        if (!$this->isDue($this->schedule)) {
            return false;
        }

        $query = $this->app->db->table($this->node());
        $query->begin();
        $lastId = auto_increment($this->node(), 'queue_id');
        try {
            $query->insert([
                'queue_id' => (int) $lastId,
                'name' => if_null($this->name),
                'data' => if_null($this->app->hook->{'maybe_serialize'}($data)),
                'created' => if_null(time())
            ]);
            $query->commit();
        } catch (Exception $e) {
            $query->rollback();
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE: %s', $e->getMessage()), ['Queue' => 'NodeQueue::doCreateItem']);
        }
        /**
         * Return the new serial ID, or false on failure.
         */
        return $lastId;
    }

    /**
     * {@inheritdoc}
     */
    public function numberOfItems()
    {
        try {
            return count($this->app->db->table($this->node())->where('name', $this->name)->get());
        } catch (Exception $e) {
            $this->catchException($e);
            /**
             * If there is no node there cannot be any items.
             */
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function claimItem($lease_time = 30)
    {
        /**
         * Claim an item by updating its expire fields. If claim is not
         * successful another thread may have claimed the item in the meantime.
         * Therefore loop until an item is successfully claimed or we are
         * reasonably sure there are no unclaimed items left.
         */
        while (true) {
            try {
                $item = $this->app->db->table($this->node())
                    ->where('expire', (int) 0)
                    ->where('name', $this->name)
                    ->sortBy('created')
                    ->sortBy('queue_id')
                    ->first();
            } catch (Exception $e) {
                $this->catchException($e);
                /**
                 * If the node does not exist there are no items currently
                 * available to claim.
                 */
                return false;
            }
            if ($item) {
                $update = $this->app->db->table($this->node());
                $update->begin();
                try {
                    /**
                     * Try to update the item. Only one thread can succeed in
                     * UPDATEing the same row. We cannot rely on REQUEST_TIME
                     * because items might be claimed by a single consumer which
                     * runs longer than 1 second. If we continue to use REQUEST_TIME
                     * instead of the current time(), we steal time from the lease,
                     * and will tend to reset items before the lease should really
                     * expire.
                     */
                    $update->where('expire', (int) 0)->where('queue_id', (int) _escape($item['queue_id']))
                        ->update([
                            'expire' => (int) time() + ($this->lease_time <= (int) 0 ? (int) $lease_time : (int) $this->lease_time)
                    ]);
                    $update->commit();
                    return $item;
                } catch (Exception $e) {
                    $update->rollback();
                    $this->catchException($e);
                    /**
                     * If the node does not exist there are no items currently
                     * available to claim.
                     */
                    return false;
                }
            } else {
                /**
                 * No items currently available to claim.
                 */
                return false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function releaseItem($item)
    {
        $update = $this->app->db->table($this->node());
        $update->begin();
        try {
            $update->where('queue_id', (int) $item['queue_id'])
                ->update([
                    'expire' => (int) 0
            ]);
            $update->commit();
        } catch (Exception $e) {
            $update->rollback();
            $this->catchException($e);
            /**
             * If the node doesn't exist we should consider the item released.
             */
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($item)
    {
        $delete = $this->app->db->table($this->node());
        $delete->begin();
        try {
            $delete->where('queue_id', (int) $item['queue_id'])
                ->delete();
            $delete->commit();
        } catch (Exception $e) {
            $delete->rollback();
            $this->catchException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue()
    {
        /**
         * All tasks are stored in a single node (which is created on
         * demand) so there is nothing we need to do to create a new queue.
         */
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQueue()
    {
        $delete = $this->app->db->table($this->node());
        $delete->begin();
        try {
            $delete->where('name', $this->name)
                ->delete();
            $delete->commit();
        } catch (Exception $e) {
            $delete->rollback();
            $this->catchException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function garbageCollection()
    {
        $delete = $this->app->db->table($this->node());
        $delete->begin();
        try {
            /**
             * Clean up the queue for failed batches.
             */
            $delete->where('created', '<', REQUEST_TIME - 864000)
                ->where('name', $this->name)
                ->delete();
            $delete->commit();
        } catch (Exception $e) {
            $delete->rollback();
            $this->catchException($e);
        }



        $update = $this->app->db->table($this->node());
        $update->begin();


        try {
            /**
             * Reset expired items in the default queue implementation node. If
             * that's not used, this will simply be a no-op.
             */
            $update->where('expire', 'not in', (int) 0)
                ->where('expire', '<', REQUEST_TIME)
                ->update([
                    'expire' => (int) 0
            ]);
            $update->commit();
        } catch (Exception $e) {
            $update->rollback();
            $this->catchException($e);
        }
    }

    /**
     * Act on an exception when queue might be stale.
     *
     * If the node does not yet exist, that's fine, but if the node exists and
     * yet the query failed, then the queue is stale and the exception needs to
     * propagate.
     *
     * @param $e
     *   The exception.
     *
     * @throws Exception
     *   If the node exists the exception passed in is rethrown.
     */
    protected function catchException(Exception $e)
    {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE: %s', $e->getMessage()), ['Queue' => 'catchException']);
    }

    public function executeAction($data)
    {
        /**
         * At start of executing the action.
         */
        $time_start = microtime(true);
        /**
         * The action that should run when queue is called.
         */
        $this->app->hook->{'do_action'}($data['action_hook']);
        /**
         * At the end of executing the action.
         */
        $time_end = (microtime(true) - $time_start);

        $runs = $this->app->db->table($this->prefix . 'tasks')->where('pid', (int) $data['pid'])->first();

        $task = $this->app->db->table($this->prefix . 'tasks');
        $task->begin();
        try {
            $task->where('pid', (int) $data['pid'])
                ->update([
                    'executions' => if_null(_escape($runs['executions']) + 1),
                    'lastrun' => (string) \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s'),
                    'last_runtime' => (double) $time_end
            ]);
            $task->commit();
        } catch (Exception $e) {
            $task->rollback();
            $this->catchException($e);
        }
        return true;
    }
}
