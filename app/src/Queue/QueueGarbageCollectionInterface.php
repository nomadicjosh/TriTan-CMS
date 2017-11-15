<?php namespace TriTan\Queue;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Interface for a garbage collection.
 * 
 * If the TriTan CMS 'queue' service implements this interface, the
 * garbageCollection() method will be called during master cron.
 * 
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
interface QueueGarbageCollectionInterface
{

    /**
     * Cleans queues of garbage.
     */
    public function garbageCollection();
}
