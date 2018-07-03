<?php
namespace TriTan\Plugin;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the TriTan CMS API. Call the
 * run function to execute the list of actions and filters.
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
interface PluginLoaderInterface
{
    /**
     * Add a new action to the collection to be registered with TriTan CMS.
     *
     * @since    0.9
     * @param    string               $hook             The name of the TriTan CMS action that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the action is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. he priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1);

    /**
     * Add a new filter to the collection to be registered with TriTan CMS.
     *
     * @since    0.9
     * @param    string               $hook             The name of the TriTan CMS filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. he priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1);

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    0.9
     * @access   private
     * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string               $hook             The name of the TriTan CMS filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         The priority at which the function should be fired.
     * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with TriTan CMS.
     */
    public function add($hooks, $hook, $component, $callback, $priority, $accepted_args);

    /**
     * Register the filters and actions with TriTan CMS.
     *
     * @since    0.9
     */
    public function run();
}
