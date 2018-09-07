<?php
namespace TriTan\Interfaces\Hooks;

interface ActionFilterHookInterface
{
    /**
     * Add a new action to the collection to be registered with TriTan CMS.
     *
     * @since    0.9.9
     * @param    string               $hook             The name of the TriTan CMS action that is being registered.
     * @param    callback             $function_to_add  The name of the function/method that is to be called.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function addAction($hook, $function_to_add, $priority = 10, $accepted_args = 1);
    
    /**
     * Removes a function/method from a specified action hook.
     *
     * @since 0.9.9
     * @param string    $hook               The action hook to which the function/method to be removed is hooked.
     * @param callback  $function_to_remove The name of the function/method which should be removed.
     * @param int       $priority optional  The priority of the function/method (default: 10).
     * @return boolean Whether the function/method is removed.
     */
    public function removeAction($hook, $function_to_remove, $priority = 10);
    
    /**
     * Registers a filtering function
     *
     * @since   0.9.9
     * @param   string      $hook            The name of the element to be filtered or action to be triggered
     * @param   callback    $function_to_add The name of the function/method that is to be called.
     * @param   integer     $priority        Optional. Used to specify the order in which the functions
     *                                       associated with a particular action are executed (default=10,
     *                                       lower=earlier execution, and functions with the same priority are
     *                                       executed in the order in which they were added to the filter)
     * @param   int         $accepted_args   Optional. The number of arguments the function accept (default is the number provided).
     */
    public function addFilter($hook, $function_to_add, $priority = 10, $accepted_args = 1);
    
    /**
     * Removes a function from a specified filter hook.
     *
     * This function removes a function attached to a specified filter hook. This
     * method can be used to remove default functions attached to a specific filter
     * hook and possibly replace them with a substitute.
     *
     * To remove a hook, the $function_to_remove and $priority arguments must match
     * when the hook was added.
     *
     * @since 0.9.9
     * @param string    $hook               The filter hook to which the function to be removed is hooked.
     * @param callback  $function_to_remove The name of the function which should be removed.
     * @param int       $priority optional. The priority of the function (default: 10).
     * @return boolean Whether the function was registered as a filter before it was removed.
     */
    public function removeFilter($hook, $function_to_remove, $priority = 10);
    
    /**
     * Performs a filtering operation on a PM element or event.
     *
     * Returns an element which may have been filtered by a filter.
     *
     * @since 0.9.9
     * @param string $hook The name of the the element or action
     * @param mixed $value The value of the element before filtering
     * @return mixed
     */
    public function applyFilter($hook, $value);
    
    /**
    * Execute functions/methods hooked on a specific action hook.
    *
    * @since 0.9.9
    * @param string $hook     The name of the action to be executed.
    * @param mixed  $arg      Optional. Additional arguments which are passed on to the
    *                         functions/methods hooked to the action. Default empty.
    */
    public function doAction($hook, $arg = '');
}
