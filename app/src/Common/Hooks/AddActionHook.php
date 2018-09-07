<?php
namespace TriTan\Common\Hooks;

use TriTan\Interfaces\AddActionHookInterface;
use TriTan\Interfaces\AddFilterHookInterface;

/**
 * Hooks API: Hook Class
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

class AddActionHook implements AddActionHookInterface
{

    /**
     *
     * @access public
     * @var object
     */
    public $filter;

    /**
     * __construct class constructor
     *
     * @access public
     * @since 0.9
     */
    public function __construct(AddFilterHookInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Add a new action to the collection to be registered with TriTan CMS.
     *
     * @since    0.9.9
     * @param    string               $hook             The name of the TriTan CMS action that is being registered.
     * @param    callback             $function_to_add  The name of the function/method that is to be called.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add($hook, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $accepted_args = self::ARGUMENT_NEUTRAL)
    {
        return $this->filter->add($hook, $function_to_add, $priority, $accepted_args);
    }
}
