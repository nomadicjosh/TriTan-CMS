<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * TriTan CMS Menu Functions
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
use TriTan\Config;

/**
 * Add an admin submenu page link.
 * 
 * Uses admin_submenu_$location filter hook.
 * 
 * @since 1.0.0
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
        if (!hasPermission($permission)) {
            return false;
        }
    }
    $menu = '<li' . (Config::get('screen_child') === $screen ? ' class="active"' : '') . '><a href="' . get_base_url() . 'admin/' . $menu_route . '/"><i class="fa fa-circle-o"></i> ' . $menu_title . '</a></li>';
    echo app()->hook->{'apply_filter'}("admin_submenu_{$location}", $menu);
}

/**
 * Adds an admin dashboard submenu page link.
 * 
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
