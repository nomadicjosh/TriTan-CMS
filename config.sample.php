<?php
/**
 * Config
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */

/**
 * If set to PROD, errors will be generated in the logs
 * directory (static/tmp/logs/*.txt). If set to DEV, then
 * errors will be displayed on the screen. For security
 * reasons, when made live to the world, this should be
 * set to PROD.
 */
defined('APP_ENV') or define('APP_ENV', 'PROD');

/**
 * Application path.
 */
defined('APP_PATH') or define('APP_PATH', BASE_PATH . 'app' . DS);

/**
 * Must Use Plugins Path.
 */
defined('TTCMS_MU_PLUGIN_DIR') or define('TTCMS_MU_PLUGIN_DIR', BASE_PATH . 'mu-plugins' . DS);

/**
 * Plugins path.
 */
defined('TTCMS_PLUGIN_DIR') or define('TTCMS_PLUGIN_DIR', BASE_PATH . 'plugins' . DS);

/**
 * Set for low ram cache.
 */
defined('TTCMS_FILE_CACHE_LOW_RAM') or define('TTCMS_FILE_CACHE_LOW_RAM', '');

/**
 * Email encoding filter priority.
 */
defined('EAE_FILTER_PRIORITY') or define('EAE_FILTER_PRIORITY', 1000);

/**
 * Instantiate a Liten application
 *
 * You can update
 */
$app = new \Liten\Liten(
    [
    'cookies.lifetime' => '86400',
    'cookies.secret.key' => 'xBQZyAc6nhv9qwonHeei', //change this to something more unique for your install
    'private.savepath' => BASE_PATH . 'private' . DS,
    'db.savepath' => BASE_PATH . 'private' . DS . 'db' . DS,
    'cookies.savepath' => BASE_PATH . 'private' . DS . 'cookies' . DS,
    'file.savepath' => BASE_PATH . 'private' . DS . 'files' . DS
    ]
);

/**
 * NodeQ NoSQL details.
 */
defined('TTCMS_NODEQ_PATH') or define('TTCMS_NODEQ_PATH', $app->config('db.savepath'));

/**
 * Sets up the database global variable.
 */
$app->inst->singleton('db', function () {
    return new TriTan\Database(['more_entropy' => true]);
});

/**
 * Main site
 */
defined('TTCMS_MAINSITE') or define('TTCMS_MAINSITE', ''); //i.e. localhost:8888
defined('TTCMS_MAINSITE_PATH') or define('TTCMS_MAINSITE_PATH', ''); //i.e. /tritan/

/* * *************************************************
 * Do not edit anything from this point on.        *
 * ************************************************* */
require_once(BASE_PATH . 'settings.php');

/**
 * Run the Liten application
 *
 * This method should be called last. This executes the Liten application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
