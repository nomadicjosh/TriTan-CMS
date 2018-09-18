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
use TriTan\Container as c;
use TriTan\Common\FileSystem;
use TriTan\Common\Hooks\ActionFilterHook as hook;
use TriTan\Common\Context\HelperContext;

if (defined('TTCMS_MEMORY_LIMIT')) {
    ini_set('memory_limit', TTCMS_MEMORY_LIMIT);
}

/**
 * Set the current site based on HTTP_HOST.
 */
$current_site_id = (new TriTan\Database())->table('site')
        ->where('site_domain', $app->req->server['HTTP_HOST'])
        ->where('site_path', str_replace('index.php', '', $app->req->server['PHP_SELF']))
        ->first();
$site_id = (int) $current_site_id['site_id'];

/**
 * Set site id.
 */
c::getInstance()->set('site_id', $site_id);

/**
 * Set table prefix.
 */
$tbl_prefix = $site_id > 0 ? "ttcms_{$site_id}_" : 0;
c::getInstance()->set('tbl_prefix', $tbl_prefix);

/**
 * Site directory.
 */
c::getInstance()->set('sites_dir', BASE_PATH . 'private' . DS . 'sites' . DS);

/**
 * Absolute site path.
 */
c::getInstance()->set('site_path', c::getInstance()->get('sites_dir') . c::getInstance()->get('site_id') . DS);

/**
 * Cache path.
 */
c::getInstance()->set('cache_path', c::getInstance()->get('site_path') . 'files' . DS . 'cache' . DS);

/**
 * Themes directory.
 */
c::getInstance()->set('theme_dir', c::getInstance()->get('site_path') . 'themes' . DS);

/**
 * Set helper context container.
 */
$helper = new TriTan\Common\Context\HelperContext();
c::getInstance()->set('context', $helper);

/**
 * Set meta data container.
 */
$meta = new TriTan\Common\MetaData(new \TriTan\Database(), new HelperContext());
c::getInstance()->set('meta', $meta);

/**
 * Set usermeta data container.
 */
$usermeta = new \TriTan\Common\User\UserMetaData($meta, new TriTan\Common\Utils(hook::getInstance()));
c::getInstance()->set('usermeta', $usermeta);

/**
 * Set postmeta data container.
 */
$postmeta = new TriTan\Common\Post\PostMetaData($meta, new TriTan\Common\Utils(hook::getInstance()));
c::getInstance()->set('postmeta', $usermeta);

/**
 * Set option data container.
 */
$option = new \TriTan\Common\Options\Options(
    new TriTan\Common\Options\OptionsMapper(
        new \TriTan\Database(),
        new HelperContext()
    )
);
c::getInstance()->set('option', $option);

/**
 * Require a functions file
 *
 * A functions file may include any dependency injections
 * or preliminary functions for your application.
 */
require(APP_PATH . 'functions.php');

hook::getInstance()->{'doAction'}('update_user_init');
hook::getInstance()->{'doAction'}('update_post_init');

/**
 * Fires before the site's theme is loaded.
 *
 * @since 0.9
 */
hook::getInstance()->{'doAction'}('before_setup_theme');

/**
 * The name of the site's specific theme.
 */
c::getInstance()->set('active_theme', c::getInstance()->get('option')->{'read'}('current_site_theme'));

/**
 * Absolute themes path.
 */
c::getInstance()->set('theme_path', c::getInstance()->get('theme_dir') . c::getInstance()->get('active_theme') . DS);

/**
 * Sets up the Fenom global variable.
 */
$app->inst->singleton('fenom', function () {
    $fenom = new Fenom(new Fenom\Provider(c::getInstance()->get('theme_path') . 'views' . DS));
    $fenom->setCompileDir(c::getInstance()->get('cache_path'));
    c::getInstance()->get('option')->{'read'}('site_cache') == 0 ? $fenom->setOptions(Fenom::DISABLE_CACHE) : '';
    return $fenom;
});

if ((new FileSystem(hook::getInstance()))->{'exists'}(c::getInstance()->get('theme_path') . 'views' . DS, false)) {
    $templates = [
      'main' => APP_PATH . 'views' . DS,
      'theme' => c::getInstance()->get('theme_path') . 'views' . DS,
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
        'folders' => $templates,
        'autoescape' => false
    ]);
    $engine->useData(['app' => $app, 'current_user_id' => get_current_user_id()]);
    return $engine;
});

/**
 * Fires after the site's theme is loaded.
 *
 * @since 0.9
 */
hook::getInstance()->{'doAction'}('after_setup_theme');

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
hook::getInstance()->{'doAction'}('muplugins_loaded');

/**
 * Fires once activated plugins have loaded.
 *
 * @since 0.9
 */
hook::getInstance()->{'doAction'}('plugins_loaded');

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
hook::getInstance()->{'doAction'}('dropins_loaded');

/**
 * Autoload theme function file if it exist.
 */
if ((new FileSystem(hook::getInstance()))->{'exists'}(c::getInstance()->get('theme_path') . 'functions.php', false)) {
    include(c::getInstance()->get('theme_path') . 'functions.php');
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
date_default_timezone_set(c::getInstance()->get('option')->{'read'}('system_timezone'));

/**
 * Fires after TriTan CMS has finished loading but before any headers are sent.
 *
 * @since 0.9
 */
hook::getInstance()->{'doAction'}('init');

/**
 * This hook is fired once TriTan, all plugins, and the theme are fully loaded and instantiated.
 *
 * @since 0.9
 */
hook::getInstance()->{'doAction'}('ttcms_loaded');
