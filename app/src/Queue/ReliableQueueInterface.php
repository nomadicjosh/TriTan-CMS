<?php namespace TriTan\Queue;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Reliable queue interface.
 * 
 * Classes implementing this interface preserve the order of messages and
 * guarantee that every item will be executed at least once.
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
interface ReliableQueueInterface extends QueueInterface
{
    
}
