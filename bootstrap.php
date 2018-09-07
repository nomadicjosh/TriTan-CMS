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
 * Step 2: Check PHP version.
 */
if (version_compare($ver = PHP_VERSION, $req = 7.1, '<')) {
    die(sprintf('You are running PHP %s, but TriTan CMS requires at least <strong>PHP %s</strong> to run.', $ver, $req));
}

/**
 * Step 3: Require the autoloader
 *
 * The autoloader includes the Liten framework as well as
 * other libraries TriTan CMS needs to work.
 */
$autoload = BASE_PATH . 'vendor' . DS . 'autoload.php';
if (!is_file($autoload)) {
    die("Please run: <i>composer update</i> to install dependencies");
}
require_once($autoload);

/**
 * Step 4: Include config file
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
