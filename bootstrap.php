<?php
/**
 * Step 1: Initial constants defined
 *
 * Several constants defined in order to help
 * with the autoloader and the loading of other
 * needed functions and files.
 */
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (! defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__FILE__) . DS);
}

/**
 * Step 2: Require the autoloader
 *
 * The autoloader includes the Liten framework as well as
 * other libraries TriTan CMS needs to work.
 */
require_once('vendor' . DS . 'autoload.php');

/**
 * Step 3: Include config file
 */
if (file_exists(BASE_PATH . 'config.php')) {
    /**
     * Our config file is located in its normal place.
     */
    require_once(BASE_PATH . 'config.php');
} elseif (file_exists(dirname(BASE_PATH) . DS . 'config.php') && !file_exists(dirname(BASE_PATH) . DS . 'settings.php')) {
    /**
     * Our config file is one level up from the base directory.
     */
    require_once(dirname(BASE_PATH) . DS . 'config.php');
}
