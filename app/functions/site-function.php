<?php
namespace TriTan\Functions\Site;

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
use TriTan\Error;
use Cascade\Cascade;
use TriTan\Functions\User;
use TriTan\Functions\Cache;
use TriTan\Functions\Db;
use TriTan\Functions\Core;
use TriTan\Functions\Auth;
use TriTan\Functions\Dependency;
use TriTan\Functions\Meta;

/**
 * Retrieves site data given a site ID or post array.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @param int|Site|null $site
 *            Site ID or object.
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
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @param string $sitedomain Site domain to check against.
 * @return bool If site domain exists, return true otherwise return false.
 */
function site_domain_exists($sitedomain)
{
    $site = app()->db->table('site')->where('site_domain', $sitedomain)->count();

    $exists = $site > 0 ? true : false;

    /**
     * Filters whether the given site domain exists or not.
     *
     * @since 0.9
     * @param bool $exists          Whether the site's domain is taken or not.
     * @param string $sitedomain    Site domain to check.
     */
    return app()->hook->{'apply_filter'}('site_domain_exists', $exists, $sitedomain);
}

/**
 * Checks whether the given site exists.
 *
 * @file app/functions/site-function.php
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
            ->count();

    $exists = $site > 0 ? true : false;

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
 * Adds user meta data for specified site.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $site_id  Site ID.
 * @param array $params Parameters to set (assign_id or role).
 */
function add_site_user_meta($site_id, $params = [])
{
    $userdata = Auth\get_userdata((int) $params['assign_id']);
    $data = [
        'username' => Core\if_null(Core\_escape($userdata->user_login)),
        'fname' => Core\if_null(Core\_escape($userdata->user_fname)),
        'lname' => Core\if_null(Core\_escape($userdata->user_lname)),
        'email' => Core\if_null(Core\_escape($userdata->user_email)),
        'bio' => Core\if_null(Core\_escape($userdata->bio)),
        'status' => Core\if_null(Core\_escape($userdata->status)),
        'admin_layout' => Core\_escape($userdata->admin_layout) <= 0 ? (int) 0 : (int) Core\_escape($userdata->admin_layout),
        'admin_sidebar' => Core\_escape($userdata->admin_sidebar) <= 0 ? (int) 0 : (int) Core\_escape($userdata->admin_sidebar),
        'admin_skin' => Core\_escape($userdata->admin_skin) == null ? (string) 'skin-red-light' : (string) Core\_escape($userdata->admin_skin)
    ];
    foreach ($data as $meta_key => $meta_value) {
        $prefix = "ttcms_{$site_id}_";
        User\update_user_meta((int) $params['assign_id'], $prefix . $meta_key, $meta_value);
    }

    $user = new \TriTan\User($params['assign_id'], Core\if_null(Core\_escape($userdata->user_login)), $site_id);
    $user->set_role($params['role']);
}

/**
 * Create the needed directories when a new site is created.
 *
 * @file app/functions/hook-function.php
 *
 * @since 0.9
 * @param int $site_id Site ID.
 * @param object $site Site object.
 * @param bool $update Whether the site is being created or updated.
 * @return bool Returns true on success and false otherwise.
 */
function create_site_directories($site_id, $site, $update)
{
    if ($update) {
        return false;
    }

    $site = new \TriTan\Site($site);
    if ((int) $site->site_id <= (int) 0) {
        return false;
    }

    try {
        Core\_mkdir(Config::get('sites_dir') . (int) $site_id . DS . 'dropins' . DS);
        Core\_mkdir(Config::get('sites_dir') . (int) $site_id . DS . 'files' . DS . 'cache' . DS);
        Core\_mkdir(Config::get('sites_dir') . (int) $site_id . DS . 'files' . DS . 'logs' . DS);
        Core\_mkdir(Config::get('sites_dir') . (int) $site_id . DS . 'themes' . DS);
        Core\_mkdir(Config::get('sites_dir') . (int) $site_id . DS . 'uploads' . DS);
        Core\_mkdir(Config::get('sites_dir') . (int) $site_id . DS . 'uploads' . DS . '__optimized__' . DS);
    } catch (Exception $ex) {
        Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $ex->getCode(), $ex->getMessage()));
    }

    return true;
}

/**
 * Deletes user meta data when site/user is deleted.
 *
 * @file app/functions/site-function.php
 *
 * @access private
 * @since 0.9
 * @param int $site_id Site ID.
 * @param array $old_site Data array of site that was deleted.
 */
function delete_site_user_meta($site_id, $old_site)
{
    if (!is_numeric($site_id)) {
        return false;
    }

    if ((int) $site_id !== (int) $old_site['site_id']) {
        return false;
    }

    $umeta = app()->db->table('usermeta');
    $umeta->begin();
    try {
        $umeta->where('meta_key', 'match', "/ttcms_{$site_id}/")
                ->delete();
        $umeta->commit();

        $users = User\get_users_by_siteid($site_id);
        foreach ($users as $user) {
            User\clean_user_cache($user->user_id);
        }
    } catch (Exception $ex) {
        $umeta->rollback();
        Cascade::getLogger('error')->error(sprintf('ERROR[%s]: %s', $ex->getCode(), $ex->getMessage()));
    }
}

/**
 * Deletes site tables when site is deleted.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @param int    $site_id  Site ID.
 * @param object $old_site Site object.
 */
function delete_site_tables($site_id, $old_site)
{
    if (!is_numeric($site_id)) {
        return false;
    }

    if ((int) $site_id !== (int) $old_site['site_id']) {
        return false;
    }

    $tables = glob(app()->config('db.savepath') . "ttcms_{$site_id}_*.json");
    if (is_array($tables)) {
        foreach ($tables as $table) {
            if (Core\ttcms_file_exists($table, false)) {
                unlink($table);
            }
        }
    }
}

/**
 * Deletes the site directory when the site is deleted.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @param int $_site_id Site ID.
 * @return bool Returns true on success and false otherwise.
 */
function delete_site_directories($site_id, $old_site)
{
    if ((int) $site_id <= (int) 0) {
        return false;
    }

    if ((int) $site_id !== (int) $old_site['site_id']) {
        return false;
    }

    _rmdir(Config::get('sites_dir') . (int) $site_id . DS);
}

/**
 * Retrieve the current site id.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @global int $site_id
 * @return int Site ID.
 */
function get_current_site_id()
{
    return Core\absint(Config::get('site_id'));
}

/**
 * Update main site based Constants in config file.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @return boolean
 */
function update_main_site()
{
    $main_site = app()->db->table('site')->where('site_id', (int) 1)->first();
    if (Core\_escape($main_site['site_domain']) === TTCMS_MAINSITE && Core\_escape($main_site['site_path']) === TTCMS_MAINSITE_PATH) {
        return false;
    }

    $site = app()->db->table('site');
    $site->begin();
    try {
        $site->where('site_id', (int) 1)
                ->update([
                    'site_domain' => (string) TTCMS_MAINSITE,
                    'site_path' => (string) TTCMS_MAINSITE_PATH,
                    'site_registered' => (string) format_date()
                ]);
        $site->commit();
    } catch (Exception $ex) {
        $site->rollback();
        Cascade::getLogger('error')->error(sprintf('ERROR[%s]: %s', $ex->getCode(), $ex->getMessage()));
    }
}

/**
 * Retrieve a list of users based on site.
 *
 * @file app/functions/site-function.php
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
        $users[] = Core\_escape($site_user['user_id']);
    }

    $list_users = app()->db->table('user')
            ->where('user_id', 'in', $users)
            ->get();

    return $list_users;
}

/**
 * Add user to a site.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @param object|int $user  User to add to a site.
 * @param object|int $site  Site to add user to.
 * @param string $role      Role to assign to user for this site.
 * @return int
 */
function add_user_to_site($user, $site, $role)
{
    $acl = new \TriTan\ACL();
    $role_id = $acl->getRoleIDFromKey($role);

    if ($user instanceof \TriTan\User) {
        $userdata = $user;
    } else {
        $userdata = Auth\get_userdata($user);
    }

    if ($site instanceof \TriTan\Site) {
        $_site = $site;
    } else {
        $_site = get_site($site);
    }

    if (!User\username_exists(Core\_escape($userdata->user_login))) {
        return false;
    }

    if (!site_exists(Core\_escape($_site['site_domain']), Core\_escape($_site['site_path']))) {
        return false;
    }

    // Store values to save in user meta.
    $meta = [];

    $meta['username'] = Core\if_null($userdata->user_login);

    $meta['fname'] = Core\if_null($userdata->user_fname);

    $meta['lname'] = Core\if_null($userdata->user_lname);

    $meta['email'] = Core\if_null($userdata->user_email);

    $meta['bio'] = null;

    $meta['role'] = (int) $role_id;

    $meta['status'] = (string) 'A';

    $meta['admin_layout'] = (int) 0;

    $meta['admin_sidebar'] = (int) 0;

    $meta['admin_skin'] = (string) 'skin-red-light';

    /**
     * Filters a user's meta values and keys immediately after the user is added
     * and before any user meta is inserted.
     *
     * @since 0.9
     * @param array $meta {
     *     Default meta values and keys for the user.
     *
     *     @type string $username       The user's username
     *     @type string $fname          The user's first name.
     *     @type string $lname          The user's last name.
     *     @type string $email          The user's email.
     *     @type string $bio            The user's bio.
     *     @type string $role           The user's role.
     *     @type string $status         The user's status.
     *     @type int    $admin_layout   The user's layout option.
     *     @type int    $admin_sidebar  The user's sidebar option.
     *     @type int    $admin_skin     The user's skin option.
     * }
     * @param $userdata User object.
     */
    $meta = app()->hook->{'apply_filter'}('add_user_user_meta', $meta, $userdata);

    // Make sure meta data doesn't already exist for this user.
    $prefix = "ttcms_{$_site['site_id']}_";
    if (!User\get_user_meta(Core\_escape($userdata->user_id), $prefix . $meta['role'], true)) {
        // Update user meta.
        foreach ($meta as $key => $value) {
            User\update_user_meta(Core\_escape($userdata->user_id), $prefix . $key, Core\if_null($value));
        }
    }

    return (int) Core\_escape($userdata->user_id);
}

/**
 * Insert a site into the database.
 *
 * Some of the `$sitedata` array fields have filters associated with the values. Exceptions are
 * 'site_owner', 'site_registered' and 'site_modified' The filters have the prefix 'pre_'
 * followed by the field name. An example using 'site_name' would have the filter called,
 * 'pre_site_name' that can be hooked into.
 *
 * @file app/functions/site-function.php
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
 * @param bool $exception       Whether to throw and exception or error.
 * @return int|Exception|Error  The newly created site's site_id or throws an Exception or Error if the site could not
 *                              be created.
 */
function ttcms_insert_site($sitedata, $exception = false)
{
    if ($sitedata instanceof \TriTan\Site) {
        $sitedata = get_object_vars($sitedata);
    }

    // Are we updating or creating?
    if (!empty($sitedata['site_id'])) {
        $update = true;
        $site_id = (int) $sitedata['site_id'];
        $site_before = get_site($site_id, true);

        if (is_null($site_before)) {
            if ($exception) {
                throw new Exception(Core\_t('Invalid site id.', 'tritan-cms'), 'invalid_site_id');
            } else {
                return new Error('invalid_site_id', Core\_t('Invalid site id.', 'tritan-cms'));
            }
        }
        $previous_status = get_site_status((int) $site_id);
        /**
         * Fires immediately before a site is inserted into the site document.
         *
         * @since 0.9.9
         * @param string    $previous_status    Status of the site before it is created.
         *                                      or updated.
         * @param int       $site_id            The site's site_id.
         * @param bool      $update             Whether this is an existing site or a new site.
         */
        app()->hook->{'do_action'}('site_previous_status', $previous_status, (int) $site_id, $update);
    } else {
        $update = false;
        $site_id = Db\auto_increment('site', 'site_id');

        $previous_status = 'new';
        /**
         * Fires immediately before a site is inserted into the site document.
         *
         * @since 0.9.9
         * @param string    $previous_status    Status of the site before it is created.
         *                                      or updated.
         * @param int       $site_id            The site's site_id.
         * @param bool      $update             Whether this is an existing site or a new site.
         */
        app()->hook->{'do_action'}('site_previous_status', $previous_status, (int) $site_id, $update);
    }

    $raw_site_domain = isset($sitedata['subdomain']) ? _trim(Core\if_null($sitedata['subdomain']) . '.' . app()->req->server['HTTP_HOST']) : _trim(Core\if_null($sitedata['site_domain']));
    /**
     * Filters a site's domain before the site is created or updated.
     *
     * @since 0.9
     * @param string $pre_site_domain The sites domain.
     */
    $pre_site_domain = app()->hook->{'apply_filter'}('pre_site_domain', $raw_site_domain);

    $site_domain = _trim($pre_site_domain);

    // site_domain cannot be empty.
    if (empty($site_domain)) {
        if ($exception) {
            throw new Exception(Core\_t('Cannot create a site with an empty domain name.', 'tritan-cms'), 'empty_site_domain');
        } else {
            return new Error('empty_site_domain', Core\_t('Cannot create a site with an empty domain name.', 'tritan-cms'));
        }
    }

    if (!$update && site_domain_exists($site_domain)) {
        if ($exception) {
            throw new Exception(Core\_t('Sorry, that site already exists!', 'tritan-cms'), 'existing_site_doamin');
        } else {
            return new Error('existing_site_doamin', Core\_t('Sorry, that site already exists!', 'tritan-cms'));
        }
    }

    $raw_site_name = Core\if_null($sitedata['site_name']);
    /**
     * Filters a site's name before the site is created or updated.
     *
     * @since 0.9
     * @param string $raw_site_name The site's name.
     */
    $site_name = app()->hook->{'apply_filter'}('pre_site_name', $raw_site_name);

    if (isset($sitedata['site_slug'])) {
        /**
         * ttcms_unique_site_slug will take the original slug supplied and check
         * to make sure that it is unique. If not unique, it will make it unique
         * by adding a number at the end.
         */
        $site_slug = ttcms_unique_site_slug($sitedata['site_slug'], $site_name, $site_id);
    } else {
        /**
         * For an update, don't modify the site_slug if it
         * wasn't supplied as an argument.
         */
        $site_slug = $site_before->site_slug;
    }

    $raw_site_slug = $site_slug;
    /**
     * Filters a site's slug before created/updated.
     *
     * @since 0.9.9
     * @param string $raw_site_slug The site's slug.
     */
    $site_slug = app()->hook->{'apply_filter'}('pre_site_slug', (string) $raw_site_slug);

    $raw_site_path = Core\if_null($sitedata['site_path']);
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
    if ((!$update || (!empty($site_before) && 0 !== strcasecmp($site_domain . $site_path, Core\_escape($site_before->site_domain) . Core\_escape($site_before->site_path)))) && site_exists($site_domain, $site_path)
    ) {
        if ($exception) {
            throw new Exception(Core\_t('Sorry, that site domain and path is already used.', 'tritan-cms'), 'existing_site_domainpath');
        } else {
            return new Error('existing_site_domainpath', Core\_t('Sorry, that site domain and path is already used.', 'tritan-cms'));
        }
    }

    $site_owner = $sitedata['site_owner'] == '' ? Core\if_null(User\get_current_user_id()) : Core\if_null($sitedata['site_owner']);

    $raw_site_status = $sitedata['site_status'] == '' ? (string) 'public' : Core\if_null($sitedata['site_status']);
    /**
     * Filters a site's status before the site is created or updated.
     *
     * @since 0.9
     * @param string $raw_site_status The site's status.
     */
    $site_status = app()->hook->{'apply_filter'}('pre_site_status', $raw_site_status);

    $site_registered = (string) format_date();

    $site_modified = (string) format_date();

    $compacted = compact('site_id', 'site_name', 'site_slug', 'site_domain', 'site_path', 'site_owner', 'site_status', 'site_registered', 'site_modified');
    $data = Core\ttcms_unslash($compacted);

    /**
     * Filters site data before the record is created or updated.
     *
     * @since 0.9
     * @param array    $data {
     *     Values and keys for the site.
     *
     *     @type string $site_id        The site's id
     *     @type string $site_domain    The site's domain
     *     @type string $site_name      The site's name/title.
     *     @type string $site_slug      The site's slug.
     *     @type string $site_path      The site's path.
     *     @type int    $site_owner     The site's owner.
     *     @type string $site_status    The site's status.
     * }
     * @param bool     $update      Whether the site is being updated rather than created.
     * @param int|null $site_id     ID of the site to be updated, or NULL if the site is being created.
     */
    $data = app()->hook->{'apply_filter'}('ttcms_pre_insert_site_data', $data, $update, $update ? (int) $site_id : null);

    if ($update) {
        if (false === Db\ttcms_site_update_document($data)) {
            if ($exception) {
                throw new Exception(Core\_t('Could not update site within the site document.'), 'site_document_update_error');
            } else {
                return new Error('site_document_update_error', Core\_t('Could not update site within the site document.'));
            }
        }
    } else {
        if (false === Db\ttcms_site_insert_document($data)) {
            if ($exception) {
                throw new Exception(Core\_t('Could not insert site into the site document.'), 'site_document_insert_error');
            } else {
                return new Error('site_document_insert_error', Core\_t('Could not insert site into the site document.'));
            }
        }
    }

    clean_site_cache($site_id);
    $site = get_site((int) $site_id, true);

    if ($update) {
        /**
         * Fires immediately after an existing site is updated.
         *
         * @since 0.9
         * @param int       $site_id    Site ID.
         * @param object    $site       Site data object.
         */
        app()->hook->{'do_action'}('update_site', $site_id, $site);
        $site_after = get_site((int) $site_id, true);
        /**
         * Action hook triggered after existing site has been updated.
         *
         * @since 0.9.9
         * @param int       $site_id      Site id.
         * @param object    $site_after   Site object following the update.
         * @param object    $site_before  Site object before the update.
         */
        app()->hook->{'do_action'}('site_updated', (int) $site_id, $site_after, $site_before);
    }

    /**
     * Fires immediately after a new site is saved.
     *
     * @since 0.9
     * @param int   $site_id Site ID.
     * @param int   $site    Site object.
     * @param bool  $update  Whether this is an existing site or a new site.
     */
    app()->hook->{'do_action'}('save_site', $site_id, $site, $update);

    /**
     * Action hook triggered after site has been saved.
     *
     * The dynamic portion of this hook, `$site_status`,
     * is the site's status.
     *
     * @since 0.9.9
     * @param int   $site_id    The site's id.
     * @param array $site       Site object.
     * @param bool  $update     Whether this is an existing site or a new site.
     */
    app()->hook->{'do_action'}("save_site_{$site_status}", (int) $site_id, $site, $update);

    /**
     * Action hook triggered after site has been saved.
     *
     * @since 0.9.9
     * @param int   $site_id    The site's id.
     * @param array $site       Site object.
     * @param bool  $update     Whether this is an existing site or a new site.
     */
    app()->hook->{'do_action'}('ttcms_after_insert_site_data', (int) $site_id, $site, $update);

    return $site_id;
}

/**
 * Update a site in the database.
 *
 * See ttcms_insert_site() For what fields can be set in $sitedata.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @param int|object|Site $sitedata An array of site data or a site object or site id.
 * @return int|Exception|Error      The updated site's id or throw an Exception or Error if the site could not be updated.
 * @throws Exception
 */
function ttcms_update_site($sitedata, $exception = false)
{
    if ($sitedata instanceof \TriTan\Site) {
        $sitedata = get_object_vars($sitedata);
    }

    $details = app()->db->table('site')->where('site_id', $sitedata['site_id'])->first();
    if ((int) $details['site_owner'] !== (int) $sitedata['site_owner']) {
        $owner_change = true;
        $previous_owner = $details['site_owner'];
    } else {
        $owner_change = false;
    }

    $ID = isset($sitedata['site_id']) ? (int) $sitedata['site_id'] : (int) 0;
    if ($ID <= 0) {
        if ($exception) {
            throw new Exception(Core\_t('Invalid site id.', 'tritan-cms'), 'invalid_site_id');
        } else {
            return new Error('invalid_site_id', Core\_t('Invalid site id.', 'tritan-cms'));
        }
    }

    $site_id = ttcms_insert_site($sitedata);

    /**
     * If the site admin has changed, delete usermeta data of the old admin
     * and add usermeta data for the new
     */
    if ($site_id > 0 && $owner_change) {
        $meta_key = "ttcms_{$site_id}";
        $old_meta = app()->db->table('usermeta')->where('user_id', (int) $previous_owner)->where('meta_key', 'match', "/$meta_key/")->get();
        foreach ($old_meta as $meta) {
            User\delete_user_meta((int) $previous_owner, $meta['meta_key'], $meta['meta_value']);
        }
        add_user_to_site((int) $sitedata['site_owner'], (int) $sitedata['site_id'], 'admin');
    }

    clean_site_cache($site_id);

    return $site_id;
}

/**
 * Deletes a site.
 *
 * @since 0.9.9
 * @param int $site_id ID of site to delete.
 * @param bool $exception Whether to throw an exception.
 * @return bool|Error Returns true on delete or throw an Exception or Error if failed.
 * @throws Exception
 */
function ttcms_delete_site($site_id, $exception = false)
{
    if ((int) $site_id == (int) 1) {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('You are not allowed to delete the main site.', 'tritan-cms'));
        exit();
    }

    $old_site = get_site($site_id);

    if (!$old_site) {
        if ($exception) {
            throw new Exception(Core\_t('Site does not exist.'), 'no_site_exists');
        } else {
            return new Error('no_site_exists', Core\_t('Site does not exist.'));
        }
    }

    /**
     * Action hook triggered before the site is deleted.
     *
     * @since 0.9
     * @param int $id Site ID.
     * @param array $old_site Data array of site to be deleted.
     */
    app()->hook->{'do_action'}('delete_site', (int) $site_id, $old_site);

    $site = app()->db->table('site');
    $site->begin();
    try {
        $site->where('site_id', (int) $site_id)
                ->delete();
        $site->commit();
        /**
         * Action hook triggered after the site is deleted.
         *
         * @since 0.9.9
         * @param int $id           Site ID.
         * @param array $old_site   Data array of site that was deleted.
         */
        app()->hook->{'do_action'}('deleted_site', (int) $site_id, $old_site);
    } catch (Exception $ex) {
        $site->rollback();
        if ($exception) {
            return sprintf('ERROR[%s]: %s', $ex->getCode(), $ex->getMessage());
        } else {
            return new Error($ex->getCode(), $ex->getMessage());
        }
    }

    clean_site_cache($site_id);

    return true;
}

/**
 * Delete site user.
 *
 * @since 0.9.9
 * @param int $user_id      The id of user to be deleted.
 * @param array $params     User parameters (assign_id and role).
 * @param bool $exception   Whether or not to throw an exception.
 * @return bool|Exception|Error Returns true if successful or will throw and exception or error otherwise.
 */
function ttcms_delete_site_user($user_id, $params = [], $exception = false)
{
    if (!is_numeric($user_id)) {
        return false;
    }

    if ((int) $user_id == (int) 1) {
        Dependency\_ttcms_flash()->{'error'}(Core\_t('You are not allowed to delete the super administrator account.', 'tritan-cms'));
        exit();
    }

    $user = new \TriTan\User((int) $user_id);

    if (!$user->exists()) {
        return false;
    }

    $sites = Db\get_users_sites((int) $user_id);

    if ((int) $params['assign_id'] > 0) {
        /**
         * Clean cache of the assigned user.
         */
        User\clean_user_cache((int) $params['assign_id']);
        /**
         * We need to reassign the site(s) to the selected user and create the
         * needed usermeta for the site.
         */
        if (null != $sites && false != $sites) {
            foreach ($sites as $site) {
                clean_site_cache((int) $site['site_id']);
                add_user_to_site((int) $params['assign_id'], (int) Core\_escape($site['site_id']), $params['role']);
            }
            /**
             * Filter hook is triggered when assign_id is greater than zero.
             *
             * Sites will be reassigned before the user is deleted.
             *
             * @since 0.9.9
             * @param int $user_id    ID of user to be deleted.
             * @param array $params   User parameters (assign_id and role).
             */
            $params = app()->hook->{'apply_filter'}('reassign_sites', (int) $user_id, $params);
        }
    } else {
        if (null != $sites && false != $sites) {
            foreach ($sites as $old_site) {
                $site_delete = app()->db->table('site');
                $site_delete->begin();
                try {
                    $site_delete->where('site_owner', $user_id)->delete();
                    $site_delete->commit();
                } catch (Exception $ex) {
                    $site_delete->rollback();
                    if ($exception) {
                        sprintf('ERROR[%s]: %s', $ex->getCode(), $ex->getMessage());
                    } else {
                        return new Error($ex->getCode(), $ex->getMessage());
                    }
                }

                clean_site_cache((int) $old_site['site_id']);

                /**
                 * Action hook triggered after the site is deleted.
                 *
                 * @since 0.9.9
                 * @param int $id           Site ID.
                 * @param array $old_site   Data array of site that was deleted.
                 */
                app()->hook->{'do_action'}('deleted_site', (int) $old_site['site_id'], $old_site);
            }
        }
    }

    /**
     * Action hook fires immediately before a user is deleted from the usermeta document.
     *
     * @since 0.9.9
     * @param int   $user_id  ID of the user to delete.
     * @param array $params   User parameters (assign_id and role).
     */
    app()->hook->{'do_action'}('delete_site_user', (int) $user_id, $params);

    /**
     * Finally delete the user.
     */
    $user_delete = app()->db->table('user');
    $user_delete->begin();
    try {
        $user_delete->where('user_id', (int) $user_id)
                ->delete();
        $user_delete->commit();

        $meta = app()->db->table('usermeta')->where('user_id', $user_id)->get(['meta_id']);
        if ($meta) {
            foreach ($meta as $mid) {
                Meta\delete_metadata_by_mid('user', $mid['meta_id']);
            }
        }
    } catch (Exception $ex) {
        $user_delete->rollback();
        if ($exception) {
            return sprintf('ERROR[%s]: %s', $ex->getCode(), $ex->getMessage());
        } else {
            return new Error(sprintf('ERROR[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }
    }

    /**
     * Clear the cache of the deleted user.
     */
    User\clean_user_cache($user_id);

    /**
     * Action hook fires immediately after a user has been deleted from the usermeta document.
     *
     * @since 0.9.9
     * @param int $user_id     ID of the user who was deleted.
     * @param array $params    User parameters (assign_id and role).
     */
    app()->hook->{'do_action'}('deleted_site_user', (int) $user_id, $params);

    return true;
}

/**
 * Populates site options and user meta for site admin after new site
 * is created.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @access private Used when the action hook `site_register` is called.
 * @param int $site_id  Site id of the newly created site.
 * @param object $site  Site object of newly created site.
 * @param bool $update  Whether the site is being created or updated.
 * @return int|bool Returns the site id if successful and false otherwise.
 */
function new_site_data($site_id, $site, $update)
{
    if ($update) {
        return false;
    }

    $site = new \TriTan\Site($site);

    if ((int) $site->site_id <= (int) 0) {
        return false;
    }

    $sitedata = get_site((int) $site_id);
    $userdata = Auth\get_userdata((int) $site->site_owner);
    $api_key = Dependency\_ttcms_random_lib()->generateString(20, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

    $prefix = "ttcms_{$sitedata['site_id']}_";

    $option = app()->db->table($prefix . 'option');
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'sitename',
        'option_value' => $sitedata['site_name']
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'site_description',
        'option_value' => 'Just another TriTan CMS site.'
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'admin_email',
        'option_value' => $userdata->user_email
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'ttcms_core_locale',
        'option_value' => 'en_US'
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'cookieexpire',
        'option_value' => (int) 604800
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'cookiepath',
        'option_value' => '/'
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'cron_jobs',
        'option_value' => (int) 0
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'system_timezone',
        'option_value' => 'America/New_York'
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'api_key',
        'option_value' => (string) $api_key
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'date_format',
        'option_value' => 'd F Y'
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'time_format',
        'option_value' => 'h:m A'
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'admin_skin',
        'option_value' => 'skin-red-light'
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'site_cache',
        'option_value' => (int) 0
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'current_site_theme',
        'option_value' => ''
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'posts_per_page',
        'option_value' => (int) 6
    ]);
    $option->insert([
        'option_id' => Db\auto_increment($prefix . 'option', 'option_id'),
        'option_key' => 'maintenance_mode',
        'option_value' => (int) 0
    ]);

    // Store values to save in user meta.
    $meta = [];

    $meta['username'] = Core\if_null($userdata->user_login);

    $meta['fname'] = Core\if_null($userdata->user_fname);

    $meta['lname'] = Core\if_null($userdata->user_lname);

    $meta['email'] = Core\if_null($userdata->user_email);

    $meta['bio'] = null;

    $meta['role'] = (int) 2;

    $meta['status'] = (string) 'A';

    $meta['admin_layout'] = (int) 0;

    $meta['admin_sidebar'] = (int) 0;

    $meta['admin_skin'] = (string) 'skin-red-light';

    /**
     * Filters a user's meta values and keys immediately after the user is added
     * and before any user meta is inserted.
     *
     * @since 0.9
     * @param array $meta {
     *     Default meta values and keys for the user.
     *
     *     @type string $username       The user's username
     *     @type string $fname          The user's first name.
     *     @type string $lname          The user's last name.
     *     @type string $email          The user's email.
     *     @type string $bio            The user's bio.
     *     @type string $role           The user's role.
     *     @type string $status         The user's status.
     *     @type int    $admin_layout   The user's layout option.
     *     @type int    $admin_sidebar  The user's sidebar option.
     *     @type int    $admin_skin     The user's skin option.
     * }
     * @param object $userdata   User object.
     */
    $meta = app()->hook->{'apply_filter'}('new_site_user_meta', $meta, $userdata);

    // Update user meta.
    foreach ($meta as $key => $value) {
        User\update_user_meta(Core\_escape($userdata->user_id), $prefix . $key, Core\if_null($value));
    }

    return (int) Core\_escape($sitedata['site_id']);
}

/**
 * Adds status label for site's table.
 *
 * @file app/functions/site-function.php
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

/**
 * Checks if site exists or is archived.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 */
function does_site_exist()
{
    $base_url = Core\get_base_url();
    $site_path = str_replace('index.php', '', app()->req->server['PHP_SELF']);
    $site_domain = str_replace(['http://', 'https://', $site_path], '', $base_url);

    $site = app()->db->table('site')
            ->where('site_domain', $site_domain)
            ->where('site_path', $site_path)
            ->first();

    if (!$site) {
        app()->res->_format('json', 404);
        exit();
    }

    if (Core\_escape($site['site_status']) === 'archive') {
        app()->res->_format('json', 503);
        exit();
    }
}

/**
 * A function which retrieves TriTan CMS site name.
 *
 * Purpose of this function is for the `site_name`
 * filter.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $site_id The unique id of a site.
 * @return string
 */
function get_site_name($site_id = 0)
{
    $site = get_site($site_id);
    $name = Core\_escape($site['site_name']);
    /**
     * Filters the site name.
     *
     * @since 0.9.9
     *
     * @param string    $name The site's name.
     * @param int       $site_id The site ID.
     */
    return app()->hook->{'apply_filter'}('site_name', $name, $site_id);
}

/**
 * A function which retrieves TriTan CMS site domain.
 *
 * Purpose of this function is for the `site_domain`
 * filter.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $site_id The unique id of a site.
 * @return string
 */
function get_site_domain($site_id = 0)
{
    $site = get_site($site_id);
    $domain = Core\_escape($site['site_domain']);
    /**
     * Filters the site domain.
     *
     * @since 0.9.9
     *
     * @param string    $domain The site's domain.
     * @param int       $site_id The site ID.
     */
    return app()->hook->{'apply_filter'}('site_domain', $domain, $site_id);
}

/**
 * A function which retrieves TriTan CMS site path.
 *
 * Purpose of this function is for the `site_path`
 * filter.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $site_id The unique id of a site.
 * @return string
 */
function get_site_path($site_id = 0)
{
    $site = get_site($site_id);
    $path = Core\_escape($site['site_path']);
    /**
     * Filters the site path.
     *
     * @since 0.9.9
     *
     * @param string    $path The site's path.
     * @param int       $site_id The site ID.
     */
    return app()->hook->{'apply_filter'}('site_path', $path, $site_id);
}

/**
 * A function which retrieves TriTan CMS site owner.
 *
 * Purpose of this function is for the `site_owner`
 * filter.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $site_id The unique id of a site.
 * @return string
 */
function get_site_owner($site_id = 0)
{
    $site = get_site($site_id);
    $owner = Core\_escape($site['site_owner']);
    /**
     * Filters the site owner.
     *
     * @since 0.9.9
     *
     * @param string    $owner The site's owner.
     * @param int       $site_id The site ID.
     */
    return app()->hook->{'apply_filter'}('site_owner', $owner, $site_id);
}

/**
 * A function which retrieves TriTan CMS site status.
 *
 * Purpose of this function is for the `site_status`
 * filter.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $site_id The unique id of a site.
 * @return string
 */
function get_site_status($site_id = 0)
{
    $site = get_site($site_id);
    $status = Core\_escape($site['site_status']);
    /**
     * Filters the site status.
     *
     * @since 0.9.9
     *
     * @param string    $status The site's status.
     * @param int       $site_id The site ID.
     */
    return app()->hook->{'apply_filter'}('site_status', $status, $site_id);
}

/**
 * Clean site caches.
 *
 * Uses `clean_site_cache` action.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int|object $site Site_id or site object to be cleaned from the cache.
 */
function clean_site_cache($site)
{
    $site_id = $site;
    $site = get_site($site_id, true);
    if (!$site) {
        if (!is_numeric($site_id)) {
            return;
        }

        // Make sure a Site object exists even when the site has been deleted.
        $site = new \TriTan\Site((object) [
                    'site_id' => $site_id,
                    'site_domain' => null,
                    'site_path' => null,
        ]);
    }

    $site_id = Core\_escape($site->site_id);
    $site_domain_path_key = md5(Core\_escape($site->site_domain) . Core\_escape($site->site_path));

    Cache\ttcms_cache_delete((int) Core\_escape($site->site_id), 'sites');
    Cache\ttcms_cache_delete((int) Core\_escape($site->site_id), 'site_details');
    Cache\ttcms_cache_delete((int) Core\_escape($site->site_id) . 'short', 'site_details');
    Cache\ttcms_cache_delete($site_domain_path_key, 'site_lookup');
    Cache\ttcms_cache_delete($site_domain_path_key, 'site_id_cache');
    Cache\ttcms_cache_delete('current_site_' . Core\_escape($site->site_domain), 'site_options');
    Cache\ttcms_cache_delete('current_site_' . Core\_escape($site->site_domain) . Core\_escape($site->site_path), 'site_options');

    /**
     * Fires immediately after the given site's cache is cleaned.
     *
     * @since 0.9.9
     * @param int    $site_id              Site id.
     * @param object $site                 Site object.
     * @param string $site_domain_path_key md5 hash of site_domain and site_path.
     */
    app()->hook->{'do_action'}('clean_site_cache', (int) $site_id, $site, $site_domain_path_key);

    Cache\ttcms_cache_set('last_changed', microtime(), 'sites');
}

/**
 * Retieve site data from the site document and site options.
 *
 * @since 0.9.9
 * @param in|string|array $fields A site's id or an array of site data.
 * @param bool $get_all Whether to retrieve all data or only data from site document.
 * @return bool|Error|\TriTan\Site Site details on success or false.
 */
function get_sitedata($fields = null, $get_all = true)
{
    if (is_array($fields)) {
        if (null !== $fields['site_id']) {
            $site_id = (int) $fields['site_id'];
        } elseif (null !== $fields['site_domain'] && null !== $fields['site_path']) {
            $key = md5($fields['site_domain'] . $fields['site_path']);
            $site = Cache\ttcms_cache_get($key, 'site_lookup');

            if (false !== $site) {
                return $site;
            }

            if (substr($fields['site_domain'], 0, 4) == 'www.') {
                $nowww = substr(Core\_escape($fields['site_domain']), 4);
                $site = app()->db->table('site')
                        ->where('site_domain', 'in', [$nowww, Core\_escape($fields['site_domain'])])
                        ->where('site_path', Core\_escape($fields['site_path']))
                        ->sortBy('site_domain', 'DESC')
                        ->get();
            } else {
                $site = app()->db->table('site')
                        ->where('site_domain', Core\_escape($fields['site_domain']))
                        ->where('site_path', Core\_escape($fields['site_path']))
                        ->get();
            }

            if (null !== $site) {
                Cache\ttcms_cache_set((int) $site['site_id'] . 'short', $site, 'site_details');
                $site_id = (int) $site['site_id'];
            } else {
                return false;
            }
        } elseif (null !== $fields['site_domain']) {
            $key = md5($fields['site_domain']);
            $site = Cache\ttcms_cache_get($key, 'site_lookup');

            if (null !== $site) {
                return $site;
            }

            if (substr($fields['site_domain'], 0, 4) == 'www.') {
                $nowww = substr(Core\_escape($fields['site_domain']), 4);
                $site = app()->db->table('site')
                        ->where('site_domain', 'in', [$nowww, Core\_escape($fields['site_domain'])])
                        ->sortBy('site_domain', 'DESC')
                        ->get();
            } else {
                $site = app()->db->table('site')
                        ->where('site_domain', Core\_escape($fields['site_domain']))
                        ->get();
            }

            if ($site) {
                Cache\ttcms_cache_set((int) $site['site_id'] . 'short', $site, 'site_details');
                $site_id = (int) $site['site_id'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        if (null === $fields) {
            $site_id = get_current_site_id();
        } elseif (!is_numeric($fields)) {
            $site_id = call_user_func_array("TriTan\Functions\get_{$fields}", [$site->site_id]);
        } else {
            $site_id = $fields;
        }
    }

    $site_id = (int) $site_id;
    $all = $get_all == true ? '' : 'short';
    $details = Cache\ttcms_cache_get($site_id . $all, 'site_details');

    if ($details) {
        if (!is_object($details)) {
            if ($details == -1) {
                return false;
            } else {
                // Clear old pre-json object. Cache clients do better with that.
                Cache\ttcms_cache_delete($site_id . $all, 'site_details');
                unset($details);
            }
        } else {
            return $details;
        }
    }

    // Try the other cache.
    if ($get_all) {
        $details = Cache\ttcms_cache_get($site_id . 'short', 'site_details');
    } else {
        $details = Cache\ttcms_cache_get($site_id, 'site_details');
        // If short was requested and full cache is set, we can return.
        if ($details) {
            if (!is_object($details)) {
                if ($details == -1) {
                    return false;
                } else {
                    // Clear old pre-json object. Cache clients do better with that.
                    Cache\ttcms_cache_delete($site_id, 'site_details');
                    unset($details);
                }
            } else {
                return $details;
            }
        }
    }

    if (empty($details)) {
        $details = \TriTan\Site::get_instance($site_id);
        if (!$details) {
            // Set the full cache.
            Cache\ttcms_cache_set($site_id, -1, 'site_details');
            return false;
        }
    }

    if (!$details instanceof \TriTan\Site) {
        $details = new \TriTan\Site($details);
    }

    if (!$get_all) {
        Cache\ttcms_cache_set($site_id . $all, $details, 'site_details');
        return $details;
    }

    /**
     * Filters a blog's details.
     *
     * @since 0.9.9
     * @param object $details The site's details.
     */
    $details = app()->hook->{'apply_filter'}('site_details', $details);

    Cache\ttcms_cache_set($site_id . $all, $details, 'site_details');

    $key = md5($details->site_domain . $details->site_path);
    Cache\ttcms_cache_set($key, $details, 'site_lookup');

    return $details;
}

/**
 * Creates a unique site slug.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.8
 * @param string $original_slug     Original slug of site.
 * @param string $original_title    Original title of site.
 * @param int $site_id              Unique site id.
 * @return string Unique site slug.
 */
function ttcms_unique_site_slug($original_slug, $original_title, $site_id)
{
    if (Db\ttcms_site_slug_exist($site_id, $original_slug)) {
        $site_slug = Db\ttcms_slugify($original_title, 'site');
    } else {
        $site_slug = $original_slug;
    }
    /**
     * Filters the unique site slug before returned.
     *
     * @since 0.9.9
     * @param string    $site_slug      Unique site slug.
     * @param string    $original_slug  The site's original slug.
     * @param string    $original_title The site's original title before slugified.
     * @param int       $post_id        The site's unique id.
     */
    return app()->hook->{'apply_filter'}('ttcms_unique_site_slug', $site_slug, $original_slug, $original_title, $site_id);
}
