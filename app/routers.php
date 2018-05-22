<?php

/**
 * This file is used for lazy loading of the routers
 * and modules when called.
 */

if (strpos(get_path_info('/rest'), "/rest") === 0)
{
    require($app->config('routers_dir') . 'rest.router.php');
}

elseif (strpos(get_path_info('/admin/user'), "/admin/user") === 0)
{
    require($app->config('routers_dir') . 'user.router.php');
}

elseif (strpos(get_path_info('/admin/site'), "/admin/site") === 0)
{
    require($app->config('routers_dir') . 'site.router.php');
}

elseif (strpos(get_path_info('/admin'), "/admin") === 0)
{
    require($app->config('routers_dir') . 'admin.router.php');
    _ttcms_post_router();
}

elseif (strpos(get_path_info('/login'), "/login") === 0)
{
    require($app->config('routers_dir') . 'login.router.php');
}

elseif (strpos(get_path_info('/cronjob'), "/cronjob") === 0)
{
    require($app->config('routers_dir') . 'cron.router.php');
}

else {
    
    require($app->config('routers_dir') . 'index.router.php');
    // default routes
}
