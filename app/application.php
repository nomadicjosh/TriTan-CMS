<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Exception\IOException;
use Cascade\Cascade;
use TriTan\Functions as func;

/**
 * Bootstrap for the application
 *  
 * @license GPLv3
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
try {
    /**
     * Creates a cookies directory with proper permissions.
     */
    func\_mkdir(app()->config('cookies.savepath'));
} catch (IOException $e) {
    Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
}

try {
    /**
     * Creates a file directory with proper permissions.
     */
    func\_mkdir(app()->config('file.savepath'));
} catch (IOException $e) {
    Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
}

/**
 * Error log setting
 */
func\ttcms_set_environment();

/**
 * Loads the default textdomain.
 * 
 * @since 0.9
 */
func\load_default_textdomain('tritan-cms', BASE_PATH . 'languages' . DS);
