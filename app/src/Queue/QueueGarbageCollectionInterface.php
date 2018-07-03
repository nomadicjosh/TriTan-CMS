<?php
namespace TriTan\Queue;

/**
 * Interface for a garbage collection.
 *
 * If the TriTan CMS 'queue' service implements this interface, the
 * garbageCollection() method will be called during master cron.
 *
 * @since       0.9
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
