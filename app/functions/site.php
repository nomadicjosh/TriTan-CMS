<?php
use TriTan\Container as c;
use TriTan\Database;
use TriTan\Common\Site\Site;
use TriTan\Common\Site\SiteRepository;
use TriTan\Common\Site\SiteMapper;
use TriTan\Common\Context\HelperContext;
use TriTan\Exception\Exception;
use TriTan\Error;
use Cascade\Cascade;
use TriTan\Common\Hooks\ActionFilterHook as hook;
use TriTan\Common\User\User;

/**
 * TriTan CMS Site Functions
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Retrieves site data given a site ID or post array.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9
 * @param int|Site|null $site
 *            Site ID or object.
 * @param bool $object If set to true, data will return as an object,
 *                     else as an array. Default: true.
 * @return array|object
 */
function get_site($site, $object = true)
{
    if ($site instanceof Site) {
        $_site = $site;
    } elseif (is_object($site)) {
        if (empty($site->getId())) {
            $_site = null;
        } else {
            $_site = (
                new SiteRepository(
                    new SiteMapper(
                        new Database(),
                        new HelperContext()
                    )
                )
            )->{'findById'}($site->getId());
        }
    } else {
        $_site = (
            new SiteRepository(
                new SiteMapper(
                    new Database(),
                    new HelperContext()
                )
            )
        )->{'findById'}((int) $site);
    }

    if (!$_site) {
        return null;
    }

    if ($object === false) {
        $_site = $_site->toArray();
    }

    /**
     * Fires after a site is retrieved.
     *
     * @since 0.9
     * @param array|Site $_site Site data.
     */
    $_site = hook::getInstance()->{'applyFilter'}('get_site', $_site);

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
function site_domain_exists(string $sitedomain) : bool
{
    $db = new Database();
    $site = $db->table('site')->where('site_domain', $sitedomain)->count();

    $exists = $site > 0 ? true : false;

    /**
     * Filters whether the given site domain exists or not.
     *
     * @since 0.9
     * @param bool $exists          Whether the site's domain is taken or not.
     * @param string $sitedomain    Site domain to check.
     */
    return hook::getInstance()->{'applyFilter'}('site_domain_exists', $exists, $sitedomain);
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
function site_exists(string $site_domain, string $site_path) : bool
{
    $db = new Database();
    $site = $db->table('site')
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
    return hook::getInstance()->{'applyFilter'}('site_exists', $exists, $site_domain, $site_path);
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
function add_site_user_meta(int $site_id, array $params = [])
{
    $db = new Database();
    $userdata = get_userdata((int) $params['assign_id']);
    $data = [
        'username' => $db->{'ifNull'}($userdata->getLogin()),
        'fname' => $db->{'ifNull'}($userdata->getFname()),
        'lname' => $db->{'ifNull'}($userdata->getLname()),
        'email' => $db->{'ifNull'}($userdata->getEmail()),
        'bio' => $db->{'ifNull'}($userdata->bio),
        'status' => $db->{'ifNull'}($userdata->status),
        'admin_layout' => $userdata->admin_layout <= 0 ? (int) 0 : (int) $userdata->admin_layout,
        'admin_sidebar' => $userdata->admin_sidebar <= 0 ? (int) 0 : (int) $userdata->admin_sidebar,
        'admin_skin' => $userdata->admin_skin == null ? (string) 'skin-red-light' : (string) $userdata->admin_skin
    ];
    foreach ($data as $meta_key => $meta_value) {
        $prefix = "ttcms_{$site_id}_";
        update_user_meta((int) $params['assign_id'], $prefix . $meta_key, $meta_value);
    }

    $user = new \TriTan\User($params['assign_id'], $db->{'ifNull'}(esc_html($userdata->user_login)), $site_id);
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
function create_site_directories(int $site_id, $site, bool $update) : bool
{
    if ($update) {
        return false;
    }

    $site = get_site($site_id);
    if ((int) $site->getId() <= (int) 0) {
        return false;
    }

    try {
        ttcms()->obj['file']->{'mkdir'}(c::getInstance()->get('sites_dir') . (int) $site_id . DS . 'dropins' . DS);
        ttcms()->obj['file']->{'mkdir'}(
            c::getInstance()->get('sites_dir') . (int) $site_id . DS . 'files' . DS . 'cache' . DS
        );
        ttcms()->obj['file']->{'mkdir'}(
            c::getInstance()->get('sites_dir') . (int) $site_id . DS . 'files' . DS . 'logs' . DS
        );
        ttcms()->obj['file']->{'mkdir'}(c::getInstance()->get('sites_dir') . (int) $site_id . DS . 'themes' . DS);
        ttcms()->obj['file']->{'mkdir'}(c::getInstance()->get('sites_dir') . (int) $site_id . DS . 'uploads' . DS);
        ttcms()->obj['file']->{'mkdir'}(
            c::getInstance()->get('sites_dir') . (int) $site_id . DS . 'uploads' . DS . '__optimized__' . DS
        );
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
 * @param object $old_site Site object of site that was deleted.
 */
function delete_site_user_meta(int $site_id, $old_site)
{
    $db = new Database();
    if (!is_numeric($site_id)) {
        return false;
    }

    if ((int) $site_id !== (int) $old_site->getId()) {
        return false;
    }

    $umeta = $db->table('usermeta');
    $umeta->begin();
    try {
        $umeta->where('meta_key', 'match', "/ttcms_{$site_id}/")
                ->delete();
        $umeta->commit();

        $users = get_users_by_siteid($site_id);
        foreach ($users as $user) {
            $_user = new User();
            $_user->setId($user['user_id']);
            $_user->setLogin($user['user_login']);
            $_user->setEmail($user['user_email']);
            ttcms()->obj['usercache']->{'clean'}($user);
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
function delete_site_tables(int $site_id, $old_site)
{
    if (!is_numeric($site_id)) {
        return false;
    }

    if ((int) $site_id !== (int) $old_site->getId()) {
        return false;
    }

    $tables = glob(ttcms()->obj['app']->{'config'}('db.savepath') . "ttcms_{$site_id}_*.json");
    if (is_array($tables)) {
        foreach ($tables as $table) {
            if (ttcms()->obj['file']->{'exists'}($table, false)) {
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
 * @param int $site_id Site ID.
 * @param object $old_site Site object.
 * @return bool Returns true on success and false otherwise.
 */
function delete_site_directories(int $site_id, $old_site)
{
    if ((int) $site_id <= (int) 0) {
        return false;
    }

    if ((int) $site_id !== (int) $old_site->getId()) {
        return false;
    }

    ttcms()->obj['file']->{'rmdir'}(c::getInstance()->get('sites_dir') . (int) $site_id . DS);
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
function get_current_site_id() : int
{
    return ttcms()->obj['util']->{'absint'}(c::getInstance()->get('site_id'));
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
    $db = new Database();
    $main_site = $db->table('site')->where('site_id', (int) 1)->first();
    if (esc_html($main_site['site_domain'])
        === TTCMS_MAINSITE
        && esc_html($main_site['site_path'])
        === TTCMS_MAINSITE_PATH
    ) {
        return false;
    }

    $site = $db->table('site');
    $site->begin();
    try {
        $site->where('site_id', (int) 1)
                ->update([
                    'site_domain' => (string) TTCMS_MAINSITE,
                    'site_path' => (string) TTCMS_MAINSITE_PATH,
                    'site_registered' => (string) current_time('Y-m-d H:i:s')
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
    $db = new Database();
    $tbl_prefix = c::getInstance()->get('tbl_prefix');

    $users = [];
    $site_users = $db->table('usermeta')
            ->where('meta_key', 'match', "/$tbl_prefix/")
            ->get();
    foreach ($site_users as $site_user) {
        $users[] = esc_html($site_user['user_id']);
    }

    $list_users = $db->table('user')
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
function add_user_to_site($user, $site, string $role)
{
    $db = new Database();
    $acl = new TriTan\Common\Acl\RoleRepository(
        new TriTan\Common\Acl\RoleMapper(
            $db,
            new HelperContext()
        )
    );
    $role_id = $acl->findIdByKey($role);

    if ($user instanceof User) {
        $userdata = $user;
    } else {
        $userdata = get_userdata($user);
    }

    if ($site instanceof Site) {
        $_site = $site;
    } else {
        $_site = get_site($site);
    }

    if (!username_exists($userdata->getLogin())) {
        return false;
    }

    if (!site_exists($_site->getDomain(), $_site->getPath())) {
        return false;
    }

    // Store values to save in user meta.
    $meta = [];

    $meta['username'] = $db->{'ifNull'}($userdata->getLogin());

    $meta['fname'] = $db->{'ifNull'}($userdata->getFname());

    $meta['lname'] = $db->{'ifNull'}($userdata->getLname());

    $meta['email'] = $db->{'ifNull'}($userdata->getEmail());

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
    $meta = hook::getInstance()->{'applyFilter'}('add_user_user_meta', $meta, $userdata);

    // Make sure meta data doesn't already exist for this user.
    $site_id = $_site->getId();
    $prefix = "ttcms_{$site_id}_";
    if (!get_user_meta($userdata->getId(), $prefix . $meta['role'], true)) {
        // Update user meta.
        foreach ($meta as $key => $value) {
            update_user_meta($userdata->getId(), $prefix . $key, $db->{'ifNull'}($value));
        }
    }

    return (int) $userdata->getId();
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
 * @param array|Site $sitedata {
 *     An array or Site array of user data arguments.
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
function ttcms_insert_site($sitedata, bool $exception = false)
{
    $db = new Database();

    if ($sitedata instanceof \TriTan\Common\Site) {
        $sitedata = get_object_vars($sitedata);
    }

    // Are we updating or creating?
    if (!empty($sitedata['site_id'])) {
        $update = true;
        $site_id = (int) $sitedata['site_id'];
        $site_before = get_site($site_id);

        if (is_null($site_before)) {
            if ($exception) {
                throw new Exception(
                    esc_html__(
                        'The ID of this entity is invalid.'
                    ),
                    'invalid_id'
                );
            } else {
                return new Error(
                    'invalid_id',
                    esc_html__(
                        'The ID of this entity is invalid.'
                    )
                );
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
        hook::getInstance()->{'doAction'}('site_previous_status', $previous_status, (int) $site_id, $update);

        /**
         * Create new site object.
         */
        $site = new \TriTan\Common\Site\Site();
        $site->setId((int) $site_id);
    } else {
        $update = false;

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
        hook::getInstance()->{'doAction'}('site_previous_status', $previous_status, (int) $site_id, $update);

        /**
         * Create new site object.
         */
        $site = new \TriTan\Common\Site\Site();
    }

    $raw_site_domain = isset($sitedata['subdomain']) ? trim($db->{'ifNull'}($sitedata['subdomain']) . '.' . ttcms()->obj['app']->req->server['HTTP_HOST']) : trim($db->{'ifNull'}($sitedata['site_domain']));
    $sanitized_site_domain = ttcms()->obj['sanitizer']->{'item'}($raw_site_domain);
    /**
     * Filters a site's domain before the site is created or updated.
     *
     * @since 0.9
     * @param string $sanitized_site_domain Site domain after it has been sanitized
     * @param string $pre_site_domain The sites domain.
     */
    $pre_site_domain = hook::getInstance()->{'applyFilter'}(
        'pre_site_domain',
        (string) $sanitized_site_domain,
        (string) $raw_site_domain
    );

    $site_domain = trim($pre_site_domain);

    // site_domain cannot be empty.
    if (empty($site_domain)) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'Cannot create a site with an empty domain name.'
                ),
                'empty_value'
            );
        } else {
            return new Error(
                'empty_value',
                esc_html__(
                    'Cannot create a site with an empty domain name.'
                )
            );
        }
    }

    if (!$update && site_domain_exists($site_domain)) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'Sorry, that site already exists!'
                ),
                'duplicate'
            );
        } else {
            return new Error(
                'duplicate',
                esc_html__(
                    'Sorry, that site already exists!'
                )
            );
        }
    }
    $site->setDomain($site_domain);

    $raw_site_name = $db->{'ifNull'}($sitedata['site_name']);
    $sanitized_site_name = ttcms()->obj['sanitizer']->{'item'}($raw_site_name);
    /**
     * Filters a site's name before the site is created or updated.
     *
     * @since 0.9
     * @param string $sanitized_site_name Site name after it has been sanitized
     * @param string $raw_site_name The site's name.
     */
    $site_name = hook::getInstance()->{'applyFilter'}(
        'pre_site_name',
        (string) $sanitized_site_name,
        (string) $raw_site_name
    );
    $site->setName($site_name);

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
        $site_slug = $site_before->getSlug();
    }

    $raw_site_slug = $site_slug;
    $sanitized_site_slug = ttcms()->obj['sanitizer']->{'item'}($raw_site_slug);
    /**
     * Filters a site's slug before created/updated.
     *
     * @since 0.9.9
     * @param string $sanitized_site_slug Site slug after it has been sanitized
     * @param string $raw_site_slug The site's slug.
     */
    $site_slug = hook::getInstance()->{'applyFilter'}(
        'pre_site_slug',
        (string) $sanitized_site_slug,
        (string) $raw_site_slug
    );
    $site->setSlug($site_slug);

    $raw_site_path = $db->{'ifNull'}($sitedata['site_path']);
    $sanitized_site_path = ttcms()->obj['sanitizer']->{'item'}($raw_site_path);
    /**
     * Filters a site's path before the site is created or updated.
     *
     * @since 0.9
     * @param string $sanitized_site_path Site path after it has been sanitized
     * @param string $raw_site_path The site's path.
     */
    $site_path = hook::getInstance()->{'applyFilter'}(
        'pre_site_path',
        (string) $sanitized_site_path,
        (string) $raw_site_path
    );
    $site->setPath($site_path);

    /*
     * If there is no update, just check for `email_exists`. If there is an update,
     * check if current email and new email are the same, or not, and check `email_exists`
     * accordingly.
     */
    if ((!$update || (!empty($site_before) && 0 !== strcasecmp($site_domain . $site_path, $site_before->getDomain() . $site_before->getPath()))) && site_exists($site_domain, $site_path)
    ) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'Sorry, that site domain and path is already used.'
                ),
                'duplicate'
            );
        } else {
            return new Error(
                'duplicate',
                esc_html__(
                    'Sorry, that site domain and path is already used.'
                )
            );
        }
    }

    $site_owner = $sitedata['site_owner'] == '' ? $db->{'ifNull'}(get_current_user_id()) : $db->{'ifNull'}($sitedata['site_owner']);
    $site->setOwner($site_owner);

    $raw_site_status = $sitedata['site_status'] == '' ? (string) 'public' : $db->{'ifNull'}($sitedata['site_status']);
    $sanitized_site_status = ttcms()->obj['sanitizer']->{'item'}($raw_site_status);
    /**
     * Filters a site's status before the site is created or updated.
     *
     * @since 0.9
     * @param string $sanitized_site_status Site status after it has been sanitized
     * @param string $raw_site_status The site's status.
     */
    $site_status = hook::getInstance()->{'applyFilter'}(
        'pre_site_status',
        (string) $sanitized_site_status,
        (string) $raw_site_status
    );
    $site->setStatus($site_status);

    $site_registered = (string) (new \TriTan\Common\Date())->{'current'}('laci');

    $site_modified = (string) (new \TriTan\Common\Date())->{'current'}('laci');

    $compacted = compact(
        'site_id',
        'site_name',
        'site_slug',
        'site_domain',
        'site_path',
        'site_owner',
        'site_status',
        'site_registered',
        'site_modified'
    );
    $data = ttcms()->obj['util']->{'unslash'}($compacted);

    /**
     * Filters site data before the record is created or updated.
     *
     * @since 0.9.9
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
    $data = hook::getInstance()->{'applyFilter'}(
        'ttcms_pre_insert_site_data',
        $data,
        $update,
        $update ? (int) $site_id : null
    );

    if ($update) {
        $site->setModified($site_modified);
        $site_id = (new \TriTan\Common\Site\SiteRepository(
            new TriTan\Common\Site\SiteMapper(
                new Database(),
                new TriTan\Common\Context\HelperContext()
            )
        ))->{'update'}($site);

        if (false === $site_id) {
            if ($exception) {
                throw new Exception(
                    esc_html__(
                        'Could not update site within the site document.'
                    ),
                    'write_error'
                );
            } else {
                return new Error(
                    'write_error',
                    esc_html__(
                        'Could not update site within the site document.'
                    )
                );
            }
        }
    } else {
        $site->setRegistered($site_registered);
        $site_id = (new \TriTan\Common\Site\SiteRepository(
            new TriTan\Common\Site\SiteMapper(
                new Database(),
                new TriTan\Common\Context\HelperContext()
            )
        ))->{'insert'}($site);

        if (false === $site_id) {
            if ($exception) {
                throw new Exception(
                    esc_html__(
                        'Could not write data to site document.'
                    ),
                    'write_error'
                );
            } else {
                return new Error(
                    'write_error',
                    esc_html__(
                        'Could not write data to site document.'
                    )
                );
            }
        }
    }

    $site = get_site((int) $site_id);
    ttcms()->obj['sitecache']->{'clean'}($site);

    if ($update) {
        /**
         * Fires immediately after an existing site is updated.
         *
         * @since 0.9
         * @param int  $site_id    Site ID.
         * @param Site $site       Site data object.
         */
        hook::getInstance()->{'doAction'}('update_site', $site_id, $site);
        $site_after = get_site((int) $site_id);
        /**
         * Action hook triggered after existing site has been updated.
         *
         * @since 0.9.9
         * @param int  $site_id      Site id.
         * @param Site $site_after   Site object following the update.
         * @param Site $site_before  Site object before the update.
         */
        hook::getInstance()->{'doAction'}('site_updated', (int) $site_id, $site_after, $site_before);
    }

    /**
     * Fires immediately after a new site is saved.
     *
     * @since 0.9
     * @param int  $site_id Site ID.
     * @param Site $site    Site object.
     * @param bool $update  Whether this is an existing site or a new site.
     */
    hook::getInstance()->{'doAction'}('save_site', $site_id, $site, $update);

    /**
     * Action hook triggered after site has been saved.
     *
     * The dynamic portion of this hook, `$site_status`,
     * is the site's status.
     *
     * @since 0.9.9
     * @param int  $site_id    The site's id.
     * @param Site $site       Site object.
     * @param bool $update     Whether this is an existing site or a new site.
     */
    hook::getInstance()->{'doAction'}("save_site_{$site_status}", (int) $site_id, $site, $update);

    /**
     * Action hook triggered after site has been saved.
     *
     * @since 0.9.9
     * @param int  $site_id    The site's id.
     * @param Site $site       Site object.
     * @param bool $update     Whether this is an existing site or a new site.
     */
    hook::getInstance()->{'doAction'}('ttcms_after_insert_site_data', (int) $site_id, $site, $update);

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
function ttcms_update_site($sitedata, bool $exception = false)
{
    $db = new Database();

    if ($sitedata instanceof \TriTan\Common\Site\Site) {
        $sitedata = get_object_vars($sitedata);
    }

    $details = $db->table('site')->where('site_id', $sitedata['site_id'])->first();
    if ((int) $details['site_owner'] !== (int) $sitedata['site_owner']) {
        $owner_change = true;
        $previous_owner = $details['site_owner'];
    } else {
        $owner_change = false;
    }

    $ID = isset($sitedata['site_id']) ? (int) $sitedata['site_id'] : (int) 0;
    if ($ID <= 0) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'The ID of this entity is invalid.'
                ),
                'invalid_id'
            );
        } else {
            return new Error(
                'invalid_id',
                esc_html__(
                    'The ID of this entity is invalid.'
                )
            );
        }
    }

    $site_id = ttcms_insert_site($sitedata);

    /**
     * If the site admin has changed, delete usermeta data of the old admin
     * and add usermeta data for the new
     */
    if ($site_id > 0 && $owner_change) {
        $meta_key = "ttcms_{$site_id}";
        $old_meta = $db->table('usermeta')
            ->where('user_id', (int) $previous_owner)
            ->where('meta_key', 'match', "/$meta_key/")
            ->get();
        foreach ($old_meta as $meta) {
            delete_user_meta((int) $previous_owner, $meta['meta_key'], $meta['meta_value']);
        }
        add_user_to_site((int) $sitedata['site_owner'], (int) $sitedata['site_id'], 'admin');
    }

    ttcms()->obj['sitecache']->{'clean'}($site_id);

    return $site_id;
}

/**
 * Deletes a site.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $site_id ID of site to delete.
 * @param bool $exception Whether to throw an exception.
 * @return bool|Error Returns true on delete or throw an Exception or Error if failed.
 * @throws Exception
 */
function ttcms_delete_site(int $site_id, bool $exception = false)
{
    if ((int) $site_id == (int) 1) {
        ttcms()->obj['flash']->{'error'}(
            esc_html__(
                'You are not allowed to delete the main site.'
            )
        );
        exit();
    }

    $old_site = get_site($site_id);

    if (!$old_site) {
        if ($exception) {
            throw new Exception(
                esc_html__(
                    'Site does not exist.'
                ),
                'not_found'
            );
        } else {
            return new Error(
                'not_found',
                esc_html__(
                    'Site does not exist.'
                )
            );
        }
    }

    /**
     * Action hook triggered before the site is deleted.
     *
     * @since 0.9
     * @param int  $id       Site ID.
     * @param Site $old_site Data object of site to be deleted.
     */
    hook::getInstance()->{'doAction'}('delete_site', (int) $site_id, $old_site);

    $delete = (
        new \TriTan\Common\Site\SiteRepository(
            new TriTan\Common\Site\SiteMapper(
                new Database(),
                new TriTan\Common\Context\HelperContext()
            )
        )
    )->{'delete'}($old_site);

    if (is_ttcms_exception($delete)) {
        $exception ? sprintf('ERROR[%s]: %s', $delete->getCode(), $delete->getMessage()) : new Error($delete->getCode(), $delete->getMessage());
    }

    /**
     * Action hook triggered after the site is deleted.
     *
     * @since 0.9.9
     * @param int $id        Site ID.
     * @param Site $old_site Site object that was deleted.
     */
    hook::getInstance()->{'doAction'}('deleted_site', (int) $site_id, $old_site);

    ttcms()->obj['sitecache']->{'clean'}($old_site);

    return true;
}

/**
 * Delete site user.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int $user_id      The id of user to be deleted.
 * @param array $params     User parameters (assign_id and role).
 * @param bool $exception   Whether or not to throw an exception.
 * @return bool|Exception|Error Returns true if successful or will throw and exception or error otherwise.
 */
function ttcms_delete_site_user(int $user_id, array $params = [], bool $exception = false)
{
    $db = new Database();
    if (!is_numeric($user_id)) {
        return false;
    }

    if ((int) $user_id == (int) 1) {
        ttcms()->obj['flash']->{'error'}(
            esc_html__(
                'You are not allowed to delete the super administrator account.'
            )
        );
        exit();
    }

    $user = get_userdata((int) $user_id);

    if (!$user) {
        return false;
    }

    $sites = get_users_sites((int) $user_id);

    if ((int) $params['assign_id'] > 0) {
        $assign_user = get_userdata((int) $params['assign_id']);
        /**
         * Clean cache of the assigned user.
         */
        ttcms()->obj['usercache']->{'clean'}($assign_user);
        /**
         * We need to reassign the site(s) to the selected user and create the
         * needed usermeta for the site.
         */
        if (null != $sites && false != $sites) {
            foreach ($sites as $site) {
                /**
                 * Create new site object from array.
                 */
                $_site = new Site();
                $_site->setId((int) $site['site_id']);
                $_site->setSlug($site['site_slug']);
                $_site->setDomain($site['site_domain']);
                $_site->setPath($site['site_path']);

                ttcms()->obj['sitecache']->{'clean'}($_site);
                add_user_to_site((int) $params['assign_id'], (int) $site['site_id'], $params['role']);
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
            $params = hook::getInstance()->{'applyFilter'}('reassign_sites', (int) $user_id, $params);
        }
    } else {
        if (null != $sites && false != $sites) {
            foreach ($sites as $old_site) {
                $site_delete = $db->table('site');
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

                $site = (
                    new SiteMapper(
                        new Database(),
                        new HelperContext()
                    )
                )->{'create'}($old_site);

                ttcms()->obj['sitecache']->{'clean'}((int) $site->getId());

                /**
                 * Action hook triggered after the site is deleted.
                 *
                 * @since 0.9.9
                 * @param int  $site_id    Site ID.
                 * @param Site $site       Site object that was deleted.
                 */
                hook::getInstance()->{'doAction'}('deleted_site', (int) $site->getId(), $site);
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
    hook::getInstance()->{'doAction'}('delete_site_user', (int) $user_id, $params);

    /**
     * Finally delete the user.
     */
    $user_delete = $db->table('user');
    $user_delete->begin();
    try {
        $user_delete->where('user_id', (int) $user_id)
                ->delete();
        $user_delete->commit();

        $meta = $db->table('usermeta')->where('user_id', $user_id)->get(['meta_id']);
        if ($meta) {
            foreach ($meta as $mid) {
                (new TriTan\Common\MetaData($db, new HelperContext()))->{'deleteByMid'}('user', $mid['meta_id']);
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
    ttcms()->obj['usercache']->{'clean'}($user);

    /**
     * Action hook fires immediately after a user has been deleted from the usermeta document.
     *
     * @since 0.9.9
     * @param int $user_id     ID of the user who was deleted.
     * @param array $params    User parameters (assign_id and role).
     */
    hook::getInstance()->{'doAction'}('deleted_site_user', (int) $user_id, $params);

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
function new_site_data(int $site_id, $site, bool $update)
{
    $db = new Database();

    if ($update) {
        return false;
    }

    $site = get_site($site_id);

    if ((int) $site->getId() <= (int) 0) {
        return false;
    }

    $userdata = get_userdata((int) $site->getOwner());
    $api_key = _ttcms_random_lib()->generateString(
        20,
        '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    );

    $prefix = "ttcms_{$site_id}_";

    $option = $db->table($prefix . 'option');
    $option->insert([
        'option_key' => 'sitename',
        'option_value' => $site->getName()
    ]);
    $option->insert([
        'option_key' => 'site_description',
        'option_value' => 'Just another TriTan CMS site.'
    ]);
    $option->insert([
        'option_key' => 'admin_email',
        'option_value' => $userdata->getEmail()
    ]);
    $option->insert([
        'option_key' => 'ttcms_core_locale',
        'option_value' => 'en_US'
    ]);
    $option->insert([
        'option_key' => 'cookieexpire',
        'option_value' => (int) 604800
    ]);
    $option->insert([
        'option_key' => 'cookiepath',
        'option_value' => '/'
    ]);
    $option->insert([
        'option_key' => 'cron_jobs',
        'option_value' => (int) 0
    ]);
    $option->insert([
        'option_key' => 'system_timezone',
        'option_value' => 'America/New_York'
    ]);
    $option->insert([
        'option_key' => 'api_key',
        'option_value' => (string) $api_key
    ]);
    $option->insert([
        'option_key' => 'date_format',
        'option_value' => 'd F Y'
    ]);
    $option->insert([
        'option_key' => 'time_format',
        'option_value' => 'h:m A'
    ]);
    $option->insert([
        'option_key' => 'admin_skin',
        'option_value' => 'skin-red-light'
    ]);
    $option->insert([
        'option_key' => 'site_cache',
        'option_value' => (int) 0
    ]);
    $option->insert([
        'option_key' => 'current_site_theme',
        'option_value' => ''
    ]);
    $option->insert([
        'option_key' => 'posts_per_page',
        'option_value' => (int) 6
    ]);
    $option->insert([
        'option_key' => 'maintenance_mode',
        'option_value' => (int) 0
    ]);

    // Store values to save in user meta.
    $meta = [];

    $meta['username'] = $db->{'ifNull'}($userdata->getLogin());

    $meta['fname'] = $db->{'ifNull'}($userdata->getFname());

    $meta['lname'] = $db->{'ifNull'}($userdata->getLname());

    $meta['email'] = $db->{'ifNull'}($userdata->getEmail());

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
    $meta = hook::getInstance()->{'applyFilter'}('new_site_user_meta', $meta, $userdata);

    // Update user meta.
    foreach ($meta as $key => $value) {
        update_user_meta($userdata->getId(), $prefix . $key, $db->{'ifNull'}($value));
    }

    return (int) $site->getId();
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
function ttcms_site_status_label(string $status)
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
    return hook::getInstance()->{'applyFilter'}('site_status_label', $label[$status], $status);
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
    $db = new Database();
    $base_url = site_url();
    $site_path = str_replace('index.php', '', ttcms()->obj['app']->req->server['PHP_SELF']);
    $site_domain = str_replace(['http://', 'https://', $site_path], '', $base_url);

    $site = $db->table('site')
            ->where('site_domain', $site_domain)
            ->where('site_path', $site_path)
            ->first();

    if (!$site) {
        ttcms()->obj['app']->res->_format('json', 404);
        exit();
    }

    if (esc_html($site['site_status']) === 'archive') {
        ttcms()->obj['app']->res->_format('json', 503);
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
function get_site_name(int $site_id = 0)
{
    $site = get_site($site_id);
    $name = $site->getName();
    /**
     * Filters the site name.
     *
     * @since 0.9.9
     *
     * @param string    $name The site's name.
     * @param int       $site_id The site ID.
     */
    return hook::getInstance()->{'applyFilter'}('site_name', $name, $site_id);
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
function get_site_domain(int $site_id = 0)
{
    $site = get_site($site_id);
    $domain = $site->getDomain();
    /**
     * Filters the site domain.
     *
     * @since 0.9.9
     *
     * @param string    $domain The site's domain.
     * @param int       $site_id The site ID.
     */
    return hook::getInstance()->{'applyFilter'}('site_domain', $domain, $site_id);
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
function get_site_path(int $site_id = 0)
{
    $site = get_site($site_id);
    $path = $site->getPath();
    /**
     * Filters the site path.
     *
     * @since 0.9.9
     *
     * @param string    $path The site's path.
     * @param int       $site_id The site ID.
     */
    return hook::getInstance()->{'applyFilter'}('site_path', $path, $site_id);
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
function get_site_owner(int $site_id = 0)
{
    $site = get_site($site_id);
    $owner = $site->getOwner();
    /**
     * Filters the site owner.
     *
     * @since 0.9.9
     *
     * @param string    $owner The site's owner.
     * @param int       $site_id The site ID.
     */
    return hook::getInstance()->{'applyFilter'}('site_owner', $owner, $site_id);
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
function get_site_status(int $site_id = 0)
{
    $site = get_site($site_id);
    $status = $site->getStatus();
    /**
     * Filters the site status.
     *
     * @since 0.9.9
     *
     * @param string    $status The site's status.
     * @param int       $site_id The site ID.
     */
    return hook::getInstance()->{'applyFilter'}('site_status', $status, $site_id);
}

/**
 * Retrieve the details of a site from the site document and site options.
 *
 * @file app/functions/site-function.php
 *
 * @since 0.9.9
 * @param int|string|array $fields A site's id or an array of site data.
 * @param bool $get_all Whether to retrieve all data or only data from site document.
 * @return bool|Error|\TriTan\Site Site details on success or false.
 */
function get_site_details($fields = null, bool $get_all = true)
{
    $db = new Database();
    if (is_array($fields)) {
        if (null !== $fields['site_id']) {
            $site_id = (int) $fields['site_id'];
        } elseif (null !== $fields['site_domain'] && null !== $fields['site_path']) {
            $key = md5($fields['site_domain'] . $fields['site_path']);
            $site = ttcms()->obj['cache']->{'read'}($key, 'site_lookup');

            if (false !== $site) {
                return $site;
            }

            if (substr($fields['site_domain'], 0, 4) == 'www.') {
                $nowww = substr($fields['site_domain'], 4);
                $site = $db->table('site')
                        ->where('site_domain', 'in', [$nowww, $fields['site_domain']])
                        ->where('site_path', $fields['site_path'])
                        ->sortBy('site_domain', 'DESC')
                        ->get();
            } else {
                $site = $db->table('site')
                        ->where('site_domain', $fields['site_domain'])
                        ->where('site_path', $fields['site_path'])
                        ->get();
            }

            if (null !== $site) {
                ttcms()->obj['cache']->{'set'}((int) $site['site_id'] . 'short', $site, 'site_details');
                $site_id = (int) $site['site_id'];
            } else {
                return false;
            }
        } elseif (null !== $fields['site_domain']) {
            $key = md5($fields['site_domain']);
            $site = ttcms()->obj['cache']->{'read'}($key, 'site_lookup');

            if (null !== $site) {
                return $site;
            }

            if (substr($fields['site_domain'], 0, 4) == 'www.') {
                $nowww = substr($fields['site_domain'], 4);
                $site = $db->table('site')
                        ->where('site_domain', 'in', [$nowww, $fields['site_domain']])
                        ->sortBy('site_domain', 'DESC')
                        ->get();
            } else {
                $site = $db->table('site')
                        ->where('site_domain', $fields['site_domain'])
                        ->get();
            }

            if ($site) {
                ttcms()->obj['cache']->{'set'}((int) $site['site_id'] . 'short', $site, 'site_details');
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
            $site_id = call_user_func_array("get_{$fields}", [(int) $site['site_id']]);
        } else {
            $site_id = $fields;
        }
    }

    $site_id = (int) $site_id;
    $all = $get_all == true ? '' : 'short';
    $details = ttcms()->obj['cache']->{'read'}($site_id . $all, 'site_details');

    if ($details) {
        if (!is_object($details)) {
            if ($details == -1) {
                return false;
            } else {
                // Clear old pre-json object. Cache clients do better with that.
                ttcms()->obj['cache']->{'delete'}($site_id . $all, 'site_details');
                unset($details);
            }
        } else {
            return $details;
        }
    }

    // Try the other cache.
    if ($get_all) {
        $details = ttcms()->obj['cache']->{'read'}($site_id . 'short', 'site_details');
    } else {
        $details = ttcms()->obj['cache']->{'read'}($site_id, 'site_details');
        // If short was requested and full cache is set, we can return.
        if ($details) {
            if (!is_object($details)) {
                if ($details == -1) {
                    return false;
                } else {
                    // Clear old pre-json object. Cache clients do better with that.
                    ttcms()->obj['cache']->{'delete'}($site_id, 'site_details');
                    unset($details);
                }
            } else {
                return $details;
            }
        }
    }

    if (empty($details)) {
        $details = (new \TriTan\Common\Site\SiteRepository(
            new TriTan\Common\Site\SiteMapper(
                new Database(),
                new TriTan\Common\Context\HelperContext()
            )
        ))->{'findById'}($site_id);
        if (!$details) {
            // Set the full cache.
            ttcms()->obj['cache']->{'set'}($site_id, -1, 'site_details');
            return false;
        }
    }

    if (!$details instanceof \TriTan\Common\Site\SiteRepository) {
        return null;
    }

    if (!$get_all) {
        ttcms()->obj['cache']->{'set'}($site_id . $all, $details, 'site_details');
        return $details;
    }

    /**
     * Filters a site's details.
     *
     * @since 0.9.9
     * @param object $details The site's details.
     */
    $details = hook::getInstance()->{'applyFilter'}('site_details', $details);

    ttcms()->obj['cache']->{'set'}($site_id . $all, $details, 'site_details');

    $key = md5($details->getDomain() . $details->getPath());
    ttcms()->obj['cache']->{'set'}($key, $details, 'site_lookup');

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
 * @param int|null $site_id         Unique site id or null.
 * @return string Unique site slug.
 */
function ttcms_unique_site_slug(string $original_slug, string $original_title, $site_id)
{
    if (ttcms_site_slug_exist($site_id, $original_slug)) {
        $site_slug = ttcms_slugify($original_title, 'site');
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
    return hook::getInstance()->{'applyFilter'}(
        'ttcms_unique_site_slug',
        $site_slug,
        $original_slug,
        $original_title,
        $site_id
    );
}
