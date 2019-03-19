<?php
use TriTan\Common\Hooks\ActionFilterHook as hook;
/**
 * TriTan CMS Menu Functions
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
use TriTan\Container as c;

/**
 * Add an admin submenu page link.
 *
 * Uses admin_submenu_$location_{$menu_route} filter hook.
 *
 * @file app/functions/menu-function.php
 *
 * @since 0.9
 * @param string $location      Submenu location.
 * @param string $menu_title    The text to be used for the menu.
 * @param string $menu_route    The route part of the url.
 * @param string $screen        Unique name of menu's screen.
 * @param string $permission    The permission required for this menu to be displayed to the user.
 * @return false|string         Return the new menu or false if permission is not met.
 */
function add_admin_submenu($location, $menu_title, $menu_route, $screen, $permission = null)
{
    if ($permission !== null) {
        if (!current_user_can($permission)) {
            return false;
        }
    }
    $menu_route = add_trailing_slash($menu_route);
    $menu = '<li' . (c::getInstance()->get('screen_child') === $screen ? ' class="active"' : '') . '><a href="' . admin_url($menu_route) . '"><i class="fa fa-circle-o"></i> ' . $menu_title . '</a></li>' . "\n";
    /**
     * Filter's the admin menu.
     *
     * The dynamic parts of this filter are `location` (where menu will appear), and
     * $_menu_route with the removed slash if present.
     *
     * @since 0.9
     * @param string $menu The menu to return.
     */
    echo hook::getInstance()->{'applyFilter'}("admin_submenu_{$location}_{$menu_route}", $menu);
}

/**
 * Adds an admin dashboard submenu page link.
 *
 * @file app/functions/menu-function.php
 *
 * @since 0.9
 * @param string $menu_title    The text to be used for the menu.
 * @param string $menu_route    The route part of the url.
 * @param string $screen        Unique name of menu's screen.
 * @param string $permission    The permission required for this menu to be displayed to the user.
 * @return false|string         Return the new menu or false if permission is not met.
 */
function add_dashboard_submenu($menu_title, $menu_route, $screen, $permission = null)
{
    return add_admin_submenu('dashboard', $menu_title, $menu_route, $screen, $permission);
}

/**
 * Adds a sites submenu page link.
 *
 * @file app/functions/menu-function.php
 *
 * @since 0.9
 * @param string $menu_title    The text to be used for the menu.
 * @param string $menu_route    The route part of the url.
 * @param string $screen        Unique name of menu's screen.
 * @param string $permission    The permission required for this menu to be displayed to the user.
 * @return false|string         Return the new menu or false if permission is not met.
 */
function add_sites_submenu($menu_title, $menu_route, $screen, $permission = null)
{
    return add_admin_submenu('sites', $menu_title, $menu_route, $screen, $permission);
}

/**
 * Adds a plugin submenu page link.
 *
 * @file app/functions/menu-function.php
 *
 * @since 0.9
 * @param string $menu_title    The text to be used for the menu.
 * @param string $menu_route    The route part of the url.
 * @param string $screen        Unique name of menu's screen.
 * @param string $permission    The permission required for this menu to be displayed to the user.
 * @return false|string         Return the new menu or false if permission is not met.
 */
function add_plugins_submenu($menu_title, $menu_route, $screen, $permission = null)
{
    return add_admin_submenu('plugins', $menu_title, $menu_route, $screen, $permission);
}

/**
 * Adds a users submenu page link.
 *
 * @file app/functions/menu-function.php
 *
 * @since 0.9
 * @param string $menu_title    The text to be used for the menu.
 * @param string $menu_route    The route part of the url.
 * @param string $screen        Unique name of menu's screen.
 * @param string $permission    The permission required for this menu to be displayed to the user.
 * @return false|string         Return the new menu or false if permission is not met.
 */
function add_users_submenu($menu_title, $menu_route, $screen, $permission = null)
{
    return add_admin_submenu('users', $menu_title, $menu_route, $screen, $permission);
}

/**
 * Adds an options submenu page link.
 *
 * @file app/functions/menu-function.php
 *
 * @since 0.9
 * @param string $menu_title    The text to be used for the menu.
 * @param string $menu_route    The route part of the url.
 * @param string $screen        Unique name of menu's screen.
 * @param string $permission    The permission required for this menu to be displayed to the user.
 * @return false|string         Return the new menu or false if permission is not met.
 */
function add_options_submenu($menu_title, $menu_route, $screen, $permission = null)
{
    return add_admin_submenu('options', $menu_title, $menu_route, $screen, $permission);
}
