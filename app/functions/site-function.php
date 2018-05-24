<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * TriTan CMS Site Functions
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;

/**
 * Retrieves site data given a site ID or post array.
 *
 * @since 0.9
 * @param int|Site|null $site
 *            Site ID or site array.
 * @param bool $object
 *            If set to true, data will return as an object, else as an array.
 * @return array|object
 */
function get_site($site, $object = false)
{
    if ($site instanceof \TriTan\Site) {
        $_site = $site;
    } elseif (is_object($site)) {
        if (empty($site->site_id)) {
            $_site = new \TriTan\Site($site);
        } else {
            $_site = \TriTan\Site::get_instance($site->site_id);
        }
    } else {
        $_site = \TriTan\Site::get_instance($site);
    }

    if (!$_site) {
        return null;
    }

    if ($object === false) {
        $_site = (array) $_site;
    }

    /**
     * Fires after a site is retrieved.
     *
     * @since 0.9
     * @param Site $_site Site data.
     */
    $_site = app()->hook->{'apply_filter'}('get_site', $_site);

    return $_site;
}

/**
 * Checks whether the given site domain exists.
 *
 * @since 0.9
 * @param string $sitedomain Site domain to check against.
 * @return bool If site domain exists, return true otherwise return false.
 */
function site_domain_exists($sitedomain)
{
    $site = app()->db->table('site')->where('site_domain', $sitedomain)->get();

    if (count($site) > 0) {
        $exists = true;
    } else {
        $exists = false;
    }

    /**
     * Filters whether the given site domain exists or not.
     *
     * @since 0.9
     * @param bool $exists           Whether the site's domain is taken or not.
     * @param string $sitedomain    Site domain to check.
     */
    return app()->hook->{'apply_filter'}('site_domain_exists', $exists, $sitedomain);
}

/**
 * Checks whether the given site exists.
 *
 * @since 0.9
 * @param string $site_domain   Site domain to check against.
 * @param string $site_path     Site path to check against.
 * @return bool If site exists, return true otherwise return false.
 */
function site_exists($site_domain, $site_path)
{
    $site = app()->db->table('site')
        ->where('site_domain', $site_domain)
        ->where('site_path', $site_path)
        ->get();

    if (count($site) > 0) {
        $exists = true;
    } else {
        $exists = false;
    }

    /**
     * Filters whether the given sitedata exists or not.
     *
     * @since 0.9
     * @param bool $exists          Whether the site exists or not.
     * @param string $site_domain   Site domain to check against.
     * @param string $site_path     Site path to check against.
     */
    return app()->hook->{'apply_filter'}('site_exists', $exists, $site_domain, $site_path);
}

/**
 * Creates/updates user meta data for specified site.
 * 
 * @since 0.9
 * @param int $_site_id Site ID.
 * @param int $user_id  User ID.
 */
function update_site_user_meta($_site_id, $user_id)
{
    $user = get_userdata((int) $user_id);
    $data = [
        'user_login' => _escape($user['user_login']),
        'user_fname' => _escape($user['user_fname']),
        'user_lname' => _escape($user['user_lname']),
        'user_email' => _escape($user['user_email']),
        'user_url' => null,
        'user_bio' => null,
        'user_role' => (int) 2,
        'user_status' => _escape($user['user_status']),
        'user_admin_layout' => (int) '0',
        'user_admin_sidebar' => (int) '0',
        'user_admin_skin' => 'skin-red-light'
    ];
    foreach ($data as $meta_key => $meta_value) {
        $prefix = "ttcms_{$_site_id}_";
        update_user_meta((int) $user_id, $prefix . $meta_key, $meta_value);
    }
}

/**
 * Deletes user meta data when site/user is deleted.
 * 
 * @since 0.9
 * @param int $_site_id Site ID.
 */
function delete_site_user_meta($_site_id)
{
    $umeta = app()->db->table('usermeta');
    $umeta->begin();
    try {
        $umeta->where('meta_key', 'match', "/ttcms_{$_site_id}/")
            ->delete();
        $umeta->commit();
        ttcms_cache_flush_namespace('user_meta');
    } catch (Exception $ex) {
        $umeta->rollback();
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        _ttcms_flash()->error($ex->getMessage());
    }
}

/**
 * Deletes site tables when site is deleted.
 * 
 * @since 0.9
 * @param int $_site_id Site ID.
 */
function delete_site_tables($_site_id)
{
    $tables = glob(app()->config('db.savepath') . "ttcms_{$_site_id}_*.json");
    if (is_array($tables)) {
        foreach ($tables as $table) {
            if (file_exists($table)) {
                unlink($table);
            }
        }
    }
}

/**
 * Retrieve the current site id.
 * 
 * @since 0.9
 * @global int $site_id
 * @return int Site ID.
 */
function get_current_site_id()
{
    return absint(Config::get('site_id'));
}

/**
 * Update main site based Constants in config file.
 * 
 * @since 0.9
 * @return boolean
 */
function update_main_site()
{
    $main_site = app()->db->table('site')->where('site_id', (int) 1)->first();
    if (_escape($main_site['site_domain']) === TTCMS_MAINSITE && _escape($main_site['site_path']) === TTCMS_MAINSITE_PATH) {
        return false;
    }

    $site = app()->db->table('site');
    $site->begin();
    try {
        $site->where('site_id', (int) 1)
            ->update([
                'site_domain' => (string) TTCMS_MAINSITE,
                'site_path' => (string) TTCMS_MAINSITE_PATH,
                'site_registered' => (string) \Jenssegers\Date\Date::now()
        ]);
        $site->commit();
    } catch (Exception $ex) {
        $site->rollback();
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
    }
}

/**
 * Retrieve a list of users based on site.
 * 
 * @since 0.9
 * @return array Users data.
 */
function get_multisite_users()
{
    $tbl_prefix = Config::get('tbl_prefix');

    $users = [];
    $site_users = app()->db->table('usermeta')
        ->where('meta_key', 'match', "/$tbl_prefix/")
        ->get();
    foreach ($site_users as $site_user) {
        $users[] = _escape($site_user['user_id']);
    }

    $list_users = app()->db->table('user')
        ->where('user_id', 'in', $users)
        ->get();

    return $list_users;
}

function add_user_to_site($user, $site, $role)
{
    if ($user instanceof TriTan\User) {
        $_user = $user;
    } else {
        $_user = get_userdata($user);
    }

    if ($site instanceof \TriTan\Site) {
        $_site = $site;
    } else {
        $_site = get_site($site);
    }

    if (!username_exists(_escape($_user['user_login']))) {
        return false;
    }

    if (!site_exists($_site)) {
        return false;
    }

    // Store values to save in user meta.
    $meta = [];

    $meta['user_login'] = if_null($_user['user_login']);

    $meta['user_fname'] = if_null($_user['user_fname']);

    $meta['user_lname'] = if_null($_user['user_lname']);

    $meta['user_bio'] = if_null($_user['user_bio']);

    $meta['user_role'] = if_null($role);

    $meta['user_status'] = if_null($_user['user_status']);

    $meta['user_admin_layout'] = (int) 0;

    $meta['user_admin_sidebar'] = (int) 0;

    $meta['user_admin_skin'] = 'skin-red-light';

    /**
     * Filters a user's meta values and keys immediately after the user is added
     * and before any user meta is inserted.
     *
     * @since 0.9
     * @param array $meta {
     *     Default meta values and keys for the user.
     *
     *     @type string $user_login           The user's username
     *     @type string $user_fname           The user's first name.
     *     @type string $user_lname           The user's last name.
     *     @type string $user_bio             The user's bio.
     *     @type string $user_role            The user's role.
     *     @type string $user_status          The user's status.
     *     @type int    $user_admin_layout    The user's layout option.
     *     @type int    $user_admin_sidebar   The user's sidebar option.
     *     @type int    $user_admin_skin      The user's skin option.
     * }
     * @param User $user   User object.
     */
    $meta = app()->hook->{'apply_filter'}('add_user_user_meta', $meta, $user);

    // Update user meta.
    foreach ($meta as $key => $value) {
        update_user_option(_escape($user['user_id']), $key, if_null($value));
    }

    return (int) _escape($user['user_id']);
}

/**
 * Insert a site into the database.
 *
 * Some of the `$sitedata` array fields have filters associated with the values. Exceptions are
 * 'site_owner', 'site_registered' and 'site_modified' The filters have the prefix 'pre_'
 * followed by the field name. An example using 'site_name' would have the filter called,
 * 'pre_site_name' that can be hooked into.
 *
 * @since 0.9
 * @param array|User $sitedata {
 *     An array or User array of user data arguments.
 *
 *     @type int        $site_id            Sites's ID. If supplied, the site will be updated.
 *     @type string     $site_domain        The site's domain.
 *     @type string     $site_name          The site's name/title.
 *     @type string     $site_path          The site's path.
 *     @type int        $site_owner         The site's owner.
 *     @type string     $site_status        The site's status.
 *     @type string     $site_registered    Date the site registered. Format is 'Y-m-d H:i:s'.
 *     @type string     $site_modified      Date the site's record was updated. Format is 'Y-m-d H:i:s'.
 * }
 * @return int|Exception The newly created site's site_id or throws an exception if the site could not
 *                      be created.
 */
function ttcms_insert_site($sitedata)
{
    if ($sitedata instanceof \TriTan\Site) {
        $sitedata = get_object_vars($sitedata);
    }

    // Are we updating or creating?
    if (!empty($sitedata['site_id'])) {
        $site_id = (int) $sitedata['site_id'];
        $update = true;
        $old_site_data = get_site($site_id);

        if (!$old_site_data) {
            throw new Exception(_t('Invalid site id.', 'tritan-cms'), 'invalid_site_id');
        }
    } else {
        $update = false;
    }

    $raw_site_domain = isset($sitedata['subdomain']) ? if_null($sitedata['subdomain'] . '.' . app()->req->server['HTTP_HOST']) : if_null($sitedata['site_domain']);
    /**
     * Filters a username after it has been sanitized.
     *
     * This filter is called before the user is created or updated.
     *
     * @since 0.9
     * @param string $sanitized_user_login Username after it has been sanitized.
     */
    $pre_site_domain = app()->hook->{'apply_filter'}('pre_site_domain', $raw_site_domain);

    $site_domain = _trim($pre_site_domain);

    // site_domain cannot be empty.
    if (empty($site_domain)) {
        throw new Exception(_t('Cannot create a site with an empty domain name.', 'tritan-cms'), 'empty_site_domain');
    }

    if (!$update && site_domain_exists($site_domain)) {
        throw new Exception(_t('Sorry, that site already exists!', 'tritan-cms'), 'existing_site_doamin');
    }

    $raw_site_name = if_null($sitedata['site_name']);
    /**
     * Filters a site's name before the site is created or updated.
     *
     * @since 0.9
     * @param string $raw_site_name The site's name.
     */
    $site_name = app()->hook->{'apply_filter'}('pre_site_name', $raw_site_name);

    $raw_site_path = if_null($sitedata['site_path']);
    /**
     * Filters a site's path before the site is created or updated.
     *
     * @since 0.9
     * @param string $raw_site_path The site's path.
     */
    $site_path = app()->hook->{'apply_filter'}('pre_site_path', $raw_site_path);

    /*
     * If there is no update, just check for `email_exists`. If there is an update,
     * check if current email and new email are the same, or not, and check `email_exists`
     * accordingly.
     */
    if ((!$update || (!empty($old_site_data) && 0 !== strcasecmp($site_domain . $site_path, _escape($old_site_data['site_domain']) . _escape($old_site_data['site_path'])) ) ) && site_exists($site_domain, $site_path)
    ) {
        throw new Exception(_t('Sorry, that site domain and path is already used.', 'tritan-cms'), 'existing_site_domainpath');
    }

    $site_owner = $sitedata['site_owner'] == '' ? if_null(get_current_user_id()) : if_null($sitedata['site_owner']);

    $raw_site_status = (string) 'public';
    /**
     * Filters a site's status before the site is created or updated.
     *
     * @since 0.9
     * @param string $raw_site_status The site's status.
     */
    $site_status = app()->hook->{'apply_filter'}('pre_site_status', $raw_site_status);

    $site_registered = (string) \Jenssegers\Date\Date::now();

    $site_modified = (string) \Jenssegers\Date\Date::now();

    $compacted = compact('site_name', 'site_domain', 'site_path', 'site_owner', 'site_status');
    $data = ttcms_unslash($compacted);

    /**
     * Filters site data before the record is created or updated.
     *
     * @since 0.9
     * @param array    $data {
     *     Values and keys for the site.
     *
     *     @type string $site_domain    The site's domain
     *     @type string $site_name      The site's name/title.
     *     @type int    $site_owner     The site's owner.
     *     @type string $site_status    The site's status.
     * }
     * @param bool     $update      Whether the site is being updated rather than created.
     * @param int|null $site_id     ID of the site to be updated, or NULL if the site is being created.
     */
    $data = app()->hook->{'apply_filter'}('ttcms_pre_insert_site_data', $data, $update, $update ? (int) $site_id : null );

    if (!$update) {
        $_data = $data + compact('site_registered');
        $site_id = auto_increment('site', 'site_id');
        $_site_id = ['site_id' => $site_id];
        $data = array_merge($_site_id, $_data);
    } else {
        $data = $data + compact('site_modified');
    }

    if ($update) {

        $update = app()->db->table('site');
        $update->begin();
        try {
            $update->where('site_id', $site_id)
                ->update($data);
            $update->commit();
        } catch (Exception $ex) {
            $update->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }
        $site_id = (int) $site_id;
    } else {

        $insert = app()->db->table('site');
        $insert->begin();
        try {
            $insert->insert($data);
            $insert->commit();
        } catch (Exception $ex) {
            $insert->rollback();
            Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }
        $site_id = (int) $site_id;
    }

    ttcms_cache_delete($site_id, 'sites');
    ttcms_cache_delete($site_id, 'site-details');

    if ($update) {
        /**
         * Fires immediately after an existing site is updated.
         *
         * @since 0.9
         * @param int     $site_id      Site ID.
         * @param User $old_site_data   Array containing site's data prior to update.
         */
        app()->hook->{'do_action'}('site_update', $site_id, $old_site_data);
    } else {
        /**
         * Fires immediately after a new site is registered.
         *
         * @since 0.9
         * @param int $site_id      Site ID.
         * @param int $site_owner   Site owner.
         */
        app()->hook->{'do_action'}('site_register', $site_id, $site_owner);
    }

    return $site_id;
}

/**
 * Update a site in the database.
 * 
 * See ttcms_insert_site() For what fields can be set in $sitedata.
 *
 * @since 0.9
 * @param int|object|Site $sitedata An array of site data or a site object or site id.
 * @return int|Exception The updated site's id or throw an Exception if the site could not be updated.
 */
function ttcms_update_site($sitedata)
{
    if ($sitedata instanceof \TriTan\Site) {
        $sitedata = get_object_vars($sitedata);
    }

    $ID = isset($sitedata['site_id']) ? (int) $sitedata['site_id'] : (int) 0;
    if (!$ID) {
        throw new Exception(_t('Invalid site id.', 'tritan-cms'), 'invalid_site_id');
    }

    ttcms_cache_delete(_escape($sitedata['site_id']), 'sites');
    ttcms_cache_delete(_escape($sitedata['site_id']), 'site-details');

    $site_id = ttcms_insert_site($sitedata);

    return $site_id;
}

/**
 * Populates site options and user meta for site admin after new site
 * is created.
 * 
 * @since 0.9
 * @access private Used when the action hook `site_register` is called.
 * @param int $site_id      Site id of the newly created site.
 * @param int $site_owner   User id of the site owner.
 * @return int|bool Returns the site id if successful and false otherwise.
 */
function new_site_data($site_id, $site_owner)
{
    $sitedata = get_site((int) $site_id);
    $userdata = get_userdata((int) $site_owner);
    $api_key = _ttcms_random_lib()->generateString(20, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

    $prefix = 'ttcms_' . _escape($sitedata['site_id']) . '_';

    $option = app()->db->table($prefix . 'option');
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'sitename',
        'option_value' => _escape($sitedata['site_name'])
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'site_description',
        'option_value' => 'Just another TriTan CMS site.'
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'admin_email',
        'option_value' => _escape($userdata['user_email'])
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'ttcms_core_locale',
        'option_value' => 'en_US'
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'cookieexpire',
        'option_value' => (int) 604800
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'cookiepath',
        'option_value' => '/'
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'enable_cron_jobs',
        'option_value' => (int) 0
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'system_timezone',
        'option_value' => 'America/New_York'
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'api_key',
        'option_value' => (string) $api_key
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'date_format',
        'option_value' => 'd F Y'
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'time_format',
        'option_value' => 'h:m A'
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'admin_skin',
        'option_value' => 'skin-red-light'
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'site_cache',
        'option_value' => (int) 0
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'current_site_theme',
        'option_value' => ''
    ]);
    $option->insert([
        'option_id' => auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'posts_per_page',
        'option_value' => (int) 6
    ]);

    // Store values to save in user meta.
    $meta = [];

    $meta['user_login'] = if_null($userdata['user_login']);

    $meta['user_fname'] = if_null($userdata['user_fname']);

    $meta['user_lname'] = if_null($userdata['user_lname']);

    $meta['user_bio'] = if_null($userdata['user_bio']);

    $meta['user_role'] = (int) 2;

    $meta['user_status'] = if_null($userdata['user_status']);

    $meta['user_admin_layout'] = (int) 0;

    $meta['user_admin_sidebar'] = (int) 0;

    $meta['user_admin_skin'] = 'skin-red-light';

    /**
     * Filters a user's meta values and keys immediately after the user is added
     * and before any user meta is inserted.
     *
     * @since 0.9
     * @param array $meta {
     *     Default meta values and keys for the user.
     *
     *     @type string $user_login           The user's username
     *     @type string $user_fname           The user's first name.
     *     @type string $user_lname           The user's last name.
     *     @type string $user_bio             The user's bio.
     *     @type string $user_role            The user's role.
     *     @type string $user_status          The user's status.
     *     @type int    $user_admin_layout    The user's layout option.
     *     @type int    $user_admin_sidebar   The user's sidebar option.
     *     @type int    $user_admin_skin      The user's skin option.
     * }
     * @param User $user   User object.
     */
    $meta = app()->hook->{'apply_filter'}('new_site_user_meta', $meta, $userdata);

    // Update user meta.
    foreach ($meta as $key => $value) {
        update_user_meta(_escape($userdata['user_id']), $prefix . $key, if_null($value));
    }

    return (int) _escape($sitedata['site_id']);
}

/**
 * Adds status label for site's table.
 * 
 * @since 0.9
 * @param string $status Status to check for.
 * @return string Sites status.
 */
function ttcms_site_status_label($status)
{
    $label = [
        'public' => 'label-success',
        'archive' => 'label-danger'
    ];

    /**
     * Filters the label result.
     * 
     * @since 0.9
     * @param
     */
    return app()->hook->{'apply_filter'}('site_status_label', $label[$status], $status);
}
