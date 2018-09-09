<?php
namespace TriTan\Common;

use TriTan\Container as c;

class TriggerError
{
    /**
     * Wrapper function for the core PHP function: trigger_error.
     *
     * This function makes the error a little more understandable for the
     * end user to track down the issue.
     *
     * @since 0.9.9
     * @param string $message
     *            Custom message to print.
     * @param string $level
     *            Predefined PHP error constant.
     */
    public function trigger($message, $level = E_USER_NOTICE)
    {
        $debug = debug_backtrace();
        $caller = next($debug);
        echo '<div class="alert alert-danger center">';
        trigger_error($message . ' used <strong>' . $caller['function'] . '()</strong> called from <strong>' . $caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong>' . "\n<br />error handler", $level);
        echo '</div>';
    }

    /**
     * Mark a function as deprecated and inform when it has been used.
     *
     * There is a hook deprecated_function_run that will be called that can be used
     * to get the backtrace up to what file and function called the deprecated
     * function.
     *
     * Default behavior is to trigger a user error if `APP_ENV` is set to `DEV`.
     *
     * This function is to be used in every function that is deprecated.
     *
     * @since 0.9.9
     *
     * @param string $function_name
     *            The function that was called.
     * @param string $release
     *            The release of TriTan CMS that deprecated the function.
     * @param string $replacement
     *            Optional. The function that should have been called. Default null.
     */
    public function deprecatedFunction($function_name, $release, $replacement = null)
    {
        /**
         * Fires when a deprecated function is called.
         *
         * @since 0.9.9
         *
         * @param string $function_name
         *            The function that was called.
         * @param string $replacement
         *            The function that should have been called.
         * @param string $release
         *            The release of TriTan CMS that deprecated the function.
         */
        c::getInstance()->get('hook')->{'doAction'}('deprecated_function_run', $function_name, $replacement, $release);

        /**
         * Filter whether to trigger an error for deprecated functions.
         *
         * @since 0.9.9
         *
         * @param bool $trigger
         *            Whether to trigger the error for deprecated functions. Default true.
         */
        if (APP_ENV == 'DEV' && c::getInstance()->get('hook')->{'applyFilter'}(
            'deprecated_function_trigger_error',
            true
        )) {
            if (function_exists('t__')) {
                if (!is_null($replacement)) {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />',
                                'tritan-cms'
                            ),
                            $function_name,
                            $release,
                            $replacement
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                                'tritan-cms'
                            ),
                            $function_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            } else {
                if (!is_null($replacement)) {
                    $this->trigger(
                        sprintf(
                            '%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />',
                            $function_name,
                            $release,
                            $replacement
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            '%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                            $function_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            }
        }
    }

    /**
     * Mark a class as deprecated and inform when it has been used.
     *
     * There is a hook deprecated_class_run that will be called that can be used
     * to get the backtrace up to what file, function/class called the deprecated
     * class.
     *
     * Default behavior is to trigger a user error if `APP_ENV` is set to `DEV`.
     *
     * This function is to be used in every class that is deprecated.
     *
     * @since 0.9.9
     *
     * @param string $class_name
     *            The class that was called.
     * @param string $release
     *            The release of TriTan CMS that deprecated the class.
     * @param string $replacement
     *            Optional. The class that should have been called. Default null.
     */
    public function deprecatedClass($class_name, $release, $replacement = null)
    {
        /**
         * Fires when a deprecated class is called.
         *
         * @since 0.9.9
         *
         * @param string $class_name
         *            The class that was called.
         * @param string $replacement
         *            The class that should have been called.
         * @param string $release
         *            The release of TriTan CMS that deprecated the class.
         */
        c::getInstance()->get('hook')->{'doAction'}('deprecated_class_run', $class_name, $replacement, $release);

        /**
         * Filter whether to trigger an error for deprecated classes.
         *
         * @since 0.9.9
         *
         * @param bool $trigger
         *            Whether to trigger the error for deprecated classes. Default true.
         */
        if (APP_ENV == 'DEV' && c::getInstance()->get('hook')->{'applyFilter'}(
            'deprecated_class_trigger_error',
            true
        )) {
            if (function_exists('t__')) {
                if (!is_null($replacement)) {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s instead. <br />',
                                'tritan-cms'
                            ),
                            $class_name,
                            $release,
                            $replacement
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                                'tritan-cms'
                            ),
                            $class_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            } else {
                if (!is_null($replacement)) {
                    $this->trigger(
                        sprintf(
                            '%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s instead. <br />',
                            $class_name,
                            $release,
                            $replacement
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            '%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                            $class_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            }
        }
    }

    /**
     * Mark a class's method as deprecated and inform when it has been used.
     *
     * There is a hook deprecated_class_method_run that will be called that can be used
     * to get the backtrace up to what file, function/class called the deprecated
     * method.
     *
     * Default behavior is to trigger a user error if `APP_ENV` is set to `DEV`.
     *
     * This function is to be used in every class's method that is deprecated.
     *
     * @since 0.9.9
     *
     * @param string $method_name
     *            The class method that was called.
     * @param string $release
     *            The release of TriTan CMS that deprecated the class's method.
     * @param string $replacement
     *            Optional. The class method that should have been called. Default null.
     */
    public function deprecatedMethod($method_name, $release, $replacement = null)
    {
        /**
         * Fires when a deprecated class method is called.
         *
         * @since 0.9.9
         *
         * @param string $method_name
         *            The class's method that was called.
         * @param string $replacement
         *            The class method that should have been called.
         * @param string $release
         *            The release of TriTan CMS that deprecated the class's method.
         */
        c::getInstance()->get('hook')->{'doAction'}(
            'deprecated_class_method_run',
            $method_name,
            $replacement,
            $release
        );

        /**
         * Filter whether to trigger an error for deprecated class methods.
         *
         * @since 0.9.9
         *
         * @param bool $trigger
         *            Whether to trigger the error for deprecated class methods. Default true.
         */
        if (APP_ENV == 'DEV' && c::getInstance()->get('hook')->{'applyFilter'}(
            'deprecated_class_method_trigger_error',
            true
        )) {
            if (function_exists('t__')) {
                if (!is_null($replacement)) {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />',
                                'tritan-cms'
                            ),
                            $method_name,
                            $release,
                            $replacement
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                                'tritan-cms'
                            ),
                            $method_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            } else {
                if (!is_null($replacement)) {
                    $this->trigger(
                        sprintf(
                            '%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />',
                            $method_name,
                            $release,
                            $replacement
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            '%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                            $method_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            }
        }
    }

    /**
     * Mark a function argument as deprecated and inform when it has been used.
     *
     * This function is to be used whenever a deprecated function argument is used.
     * Before this function is called, the argument must be checked for whether it was
     * used by comparing it to its default value or evaluating whether it is empty.
     *
     * There is a hook `deprecated_argument_run` that will be called that can be used
     * to get the backtrace up to what file and function used the deprecated
     * argument.
     *
     * Default behavior is to trigger a user error if `APP_ENV` is set to `DEV`.
     *
     * Example Usage:
     *
     *      $trigger = new TriTan\Common\TriggerError(new TriTan\Common\Context\GlobalContext());
     *      if ( ! empty( $deprecated ) ) {
     *          $trigger->deprecatedArgument( __FUNCTION__, '0.9' );
     *      }
     *
     * @since 0.9.9
     *
     * @param string $function_name
     *            The function that was called.
     * @param string $release
     *            The release of TriTan CMS that deprecated the argument used.
     * @param string $message
     *            Optional. A message regarding the change. Default null.
     */
    public function deprecatedArgument($function_name, $release, $message = null)
    {
        /**
         * Fires when a deprecated argument is called.
         *
         * @since 0.9.9
         *
         * @param string $function_name
         *            The function that was called.
         * @param string $message
         *            A message regarding the change.
         * @param string $release
         *            The release of TriTan CMS that deprecated the argument used.
         */
        c::getInstance()->get('hook')->{'doAction'}('deprecated_argument_run', $function_name, $message, $release);
        /**
         * Filter whether to trigger an error for deprecated arguments.
         *
         * @since 0.9.9
         *
         * @param bool $trigger
         *            Whether to trigger the error for deprecated arguments. Default true.
         */
        if (APP_ENV == 'DEV' && c::getInstance()->get('hook')->{'applyFilter'}(
            'deprecated_argument_trigger_error',
            true
        )) {
            if (function_exists('t__')) {
                if (!is_null($message)) {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s! %3$s. <br />',
                                'tritan-cms'
                            ),
                            $function_name,
                            $release,
                            $message
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            t__(
                                '%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                                'tritan-cms'
                            ),
                            $function_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            } else {
                if (!is_null($message)) {
                    $this->trigger(
                        sprintf(
                            '%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s! %3$s. <br />',
                            $function_name,
                            $release,
                            $message
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    $this->trigger(
                        sprintf(
                            '%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s with no alternative available. <br />',
                            $function_name,
                            $release
                        ),
                        E_USER_DEPRECATED
                    );
                }
            }
        }
    }

    /**
     * Marks a deprecated action or filter hook as deprecated and throws a notice.
     *
     * Default behavior is to trigger a user error if `APP_ENV` is set to `DEV`.
     *
     * @since 0.9.9
     *
     * @param string $hook        The hook that was used.
     * @param string $release     The release of TriTan CMS that deprecated the hook.
     * @param string $replacement Optional. The hook that should have been used.
     * @param string $message     Optional. A message regarding the change.
     */
    public function deprecatedHook($hook, $release, $replacement = null, $message = null)
    {
        /**
         * Fires when a deprecated hook is called.
         *
         * @since 0.9.9
         *
         * @param string $hook        The hook that was called.
         * @param string $replacement The hook that should be used as a replacement.
         * @param string $release     The release of TriTan CMS that deprecated the argument used.
         * @param string $message     A message regarding the change.
         */
        c::getInstance()->get('hook')->{'doAction'}('deprecated_hook_run', $hook, $replacement, $release, $message);

        /**
         * Filters whether to trigger deprecated hook errors.
         *
         * @since 0.9.9
         *
         * @param bool $trigger Whether to trigger deprecated hook errors. Requires
         *                      `APP_DEV` to be defined DEV.
         */
        if (APP_ENV == 'DEV' && c::getInstance()->get('hook')->{'applyFilter'}(
            'deprecated_hook_trigger_error',
            true
        )) {
            $message = empty($message) ? '' : ' ' . $message;
            if (!is_null($replacement)) {
                $this->trigger(
                    sprintf(
                        __(
                            '%1$s is <strong>deprecated</strong> since release %2$s! Use %3$s instead.'
                        ),
                        $hook,
                        $release,
                        $replacement
                    ) . $message,
                    E_USER_DEPRECATED
                );
            } else {
                $this->trigger(
                    sprintf(
                        __(
                            '%1$s is <strong>deprecated</strong> since release %2$s with no alternative available.'
                        ),
                        $hook,
                        $release
                    ) . $message,
                    E_USER_DEPRECATED
                );
            }
        }
    }

    /**
     * Mark something as being incorrectly called.
     *
     * There is a hook incorrectly_called_run that will be called that can be used
     * to get the backtrace up to what file and function called the deprecated
     * function.
     *
     * Default behavior is to trigger a user error if `APP_ENV` is set to `DEV`.
     *
     * @since 0.9.9
     *
     * @param string $function_name
     *            The function that was called.
     * @param string $message
     *            A message explaining what has been done incorrectly.
     * @param string $release
     *            The release of TriTan CMS where the message was added.
     */
    public function incorrectlyCalled($function_name, $message, $release)
    {
        /**
         * Fires when the given function is being used incorrectly.
         *
         * @since 0.9.9
         *
         * @param string $function_name
         *            The function that was called.
         * @param string $message
         *            A message explaining what has been done incorrectly.
         * @param string $release
         *            The release of TriTan CMS where the message was added.
         */
        c::getInstance()->get('hook')->{'doAction'}('incorrectly_called_run', $function_name, $message, $release);

        /**
         * Filter whether to trigger an error for _incorrectly_called() calls.
         *
         * @since 0.9.9
         *
         * @param bool $trigger
         *            Whether to trigger the error for _incorrectly_called() calls. Default true.
         */
        if (APP_ENV == 'DEV' && c::getInstance()->get('hook')->{'applyFilter'}(
            'incorrectly_called_trigger_error',
            true
        )) {
            if (function_exists('t__')) {
                $release = is_null($release) ? '' : sprintf(
                    t__(
                        '(This message was added in release %s.) <br /><br />', 'tritan-cms'
                    ),
                    $release
                );
                /* translators: %s: Codex URL */
                $message .= ' ' . sprintf(
                    t__(
                        'Please see <a href="%s">Debugging in TriTan CMS</a> for more information.',
                        'tritan-cms'
                    ),
                    'https://learn.tritancms.com/start.html#debugging'
                );
                $this->trigger(
                    sprintf(
                        t__(
                            '%1$s() was called <strong>incorrectly</strong>. %2$s %3$s <br />',
                            'tritan-cms'
                        ),
                        $function_name,
                        $message,
                        $release
                    )
                );
            } else {
                $release = is_null($release) ? '' : sprintf(
                    '(This message was added in release %s.) <br /><br />',
                    $release
                );
                $message .= sprintf(
                    ' Please see <a href="%s">Debugging in TriTan CMS</a> for more information.',
                    'https://learn.tritancms.com/start.html#debugging'
                );
                $this->trigger(
                    sprintf(
                        '%1$s() was called <strong>incorrectly</strong>. %2$s %3$s <br />',
                        $function_name,
                        $message,
                        $release
                    )
                );
            }
        }
    }
}
