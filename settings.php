<?php

/**
 * Settings
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
use TriTan\Config;

if (defined('TTCMS_MEMORY_LIMIT')) {
    ini_set('memory_limit', TTCMS_MEMORY_LIMIT);
}

/**
 * Set the current site based on HTTP_HOST.
 */
$current_site_id = $app->db->table('site')
        ->where('site_domain', $app->req->server['HTTP_HOST'])
        ->where('site_path', str_replace('index.php', '', $app->req->server['PHP_SELF']))
        ->first();

$site_id = (int) $current_site_id['site_id'];

$tbl_prefix = $site_id > 0 ? "ttcms_{$site_id}_" : 0;

Config::set('site_id', $site_id);

Config::set('tbl_prefix', $tbl_prefix);

/**
 * Site directory.
 */
Config::set('sites_dir', BASE_PATH . 'private' . DS . 'sites' . DS);

/**
 * Absolute site path.
 */
Config::set('site_path', Config::get('sites_dir') . Config::get('site_id') . DS);

/**
 * Cache path.
 */
Config::set('cache_path', Config::get('site_path') . 'files' . DS . 'cache' . DS);

/**
 * Themes directory.
 */
Config::set('theme_dir', Config::get('site_path') . 'themes' . DS);

/**
 * Require a functions file
 *
 * A functions file may include any dependency injections
 * or preliminary functions for your application.
 */
require(APP_PATH . 'functions.php');

/**
 * Fires before the site's theme is loaded.
 *
 * @since 0.9
 */
$app->hook->{'do_action'}('before_setup_theme');

/**
 * The name of the site specific theme.
 */
Config::set('active_theme', $app->hook->{'get_option'}('current_site_theme'));

/**
 * Absolute themes path.
 */
Config::set('theme_path', Config::get('theme_dir') . Config::get('active_theme') . DS);

/**
 * Sets up the Fenom global variable.
 */
$app->inst->singleton('fenom', function () use ($app) {
    $fenom = new Fenom(new Fenom\Provider(Config::get('theme_path') . 'views' . DS));
    $fenom->setCompileDir(Config::get('cache_path'));
    $app->hook->{'get_option'}('site_cache') == 0 ? $fenom->setOptions(Fenom::DISABLE_CACHE) : '';
    return $fenom;
});

if (TriTan\Functions\Core\ttcms_file_exists(Config::get('theme_path') . 'views' . DS, false)) {
    $templates = [
      'main' => APP_PATH . 'views' . DS,
      'theme' => Config::get('theme_path') . 'views' . DS,
      'plugin' => TTCMS_PLUGIN_DIR
    ];
} else {
    $templates = [
      'main' => APP_PATH . 'views' . DS,
      'plugin' => TTCMS_PLUGIN_DIR
    ];
}

/**
 * Sets up the Foil global variable.
 */
$app->inst->singleton('foil', function () use ($app, $templates) {
    $engine = Foil\engine([
        'folders' => $templates
    ]);
    $engine->useData(['app' => $app, 'current_user_id' => TriTan\Functions\User\get_current_user_id()]);
    return $engine;
});

/**
 * Fires after the site's theme is loaded.
 *
 * @since 0.9
 */
$app->hook->{'do_action'}('after_setup_theme');

/**
 * Autoload Sitewide Must-Use plugins
 *
 * Must-Use are snippets of code that must be
 * loaded every time each site is loaded.
 */
foreach (ttcms_get_mu_plugins() as $mu_plugin) {
    include_once($mu_plugin);
}
unset($mu_plugin);

/**
 * Fires once all must-use plugins have loaded.
 *
 * @since 0.9
 */
$app->hook->{'do_action'}('muplugins_loaded');

/**
 * Fires once activated plugins have loaded.
 *
 * @since 0.9
 */
$app->hook->{'do_action'}('plugins_loaded');

/**
 * Include the routers needed
 *
 * Lazy load the routers. A router is loaded
 * only when it is needed.
 */
include(APP_PATH . 'routers.php');

/**
 * Autoload specific site dropins.
 *
 * Dropins are just site specific snippets of code to include
 * without the hassle of creating a full fledge
 * plugin.
 */
foreach (ttcms_get_site_dropins() as $site_dropin) {
    include_once($site_dropin);
}
unset($site_dropin);

/**
 * Fires once all dropins have loaded.
 *
 * @since 0.9
 */
$app->hook->{'do_action'}('dropins_loaded');

/**
 * Autoload theme function file if it exist.
 */
if (TriTan\Functions\Core\ttcms_file_exists(Config::get('theme_path') . 'functions.php', false)) {
    include(Config::get('theme_path') . 'functions.php');
}

/**
 * Autoload specific site Theme Routers if they exist.
 */
foreach (ttcms_get_theme_routers() as $theme_router) {
    include($theme_router);
}

/**
 * Set the timezone for the application.
 */
date_default_timezone_set($app->hook->{'get_option'}('system_timezone'));

/**
 * Fires after TriTan CMS has finished loading but before any headers are sent.
 *
 * @since 0.9
 */
$app->hook->{'do_action'}('init');

/**
 * This hook is fired once TriTan, all plugins, and the theme are fully loaded and instantiated.
 *
 * @since 0.9
 */
$app->hook->{'do_action'}('ttcms_loaded');
