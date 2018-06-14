<?php

namespace TriTan;

use TriTan\Functions as func;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Site API: Site Class
 *
 * @license GPLv3
 *         
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
final class Site
{

    /**
     * Site id.
     *
     * @since 0.9
     * @var int
     */
    public $site_id;

    /**
     * Name of site.
     *
     * @since 0.9
     * @var string
     */
    public $site_name;

    /**
     * Domain of site.
     *
     * @since 0.9
     * @var string
     */
    public $site_domain;

    /**
     * Path of site.
     *
     * @since 0.9
     * @var string
     */
    public $site_path;

    /**
     * Owner of site.
     *
     * @since 0.9
     * @var int
     */
    public $site_owner;

    /**
     * Status of site.
     *
     * @since 0.9
     * @var string
     */
    public $site_status;

    /**
     * Timestamp when site was created.
     *
     * @since 0.9
     * @var string
     */
    public $site_registered = '0000-00-00 00:00:00';

    /**
     * Timestamp when site was updated.
     *
     * @since 0.9
     * @var string
     */
    public $site_modified = '0000-00-00 00:00:00';

    /**
     * Retrieves a site from the database by its site_id.
     *
     * @since 0.9
     * @param int $site_id The site_id of the site to retrieve.
     * @return Site|false The site's object if found. False if not.
     */
    public static function get_instance($site_id)
    {
        $site_id = (int) $site_id;
        if (!$site_id) {
            return false;
        }

        $_site = func\ttcms_cache_get($site_id, 'sites');

        if (!$_site) {
            $_site = app()->db->table('site')
                    ->where('site_id', (int) $site_id)
                    ->first();

            if (!$_site) {
                return false;
            }

            func\ttcms_cache_add($site_id, $_site, 'sites');
        }

        return new Site($_site);
    }

    /**
     * Creates a new Site object.
     *
     * Will populate object properties from the object provided and assign other
     * default properties based on that information.
     *
     * @since 0.9
     * @param Site|object $site A site object.
     */
    public function __construct($site)
    {
        if (!is_object($site)) {
            foreach ($site as $key => $value) {
                $this->$key = $value;
            }
        } else {
            foreach (get_object_vars($site) as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Getter.
     *
     * Allows current multisite naming conventions when getting properties.
     * Allows access to extended site properties.
     *
     * @since 0.9
     * @param string $key Property to get.
     * @return mixed Value of the property. Null if not available.
     */
    public function __get($key)
    {
        switch ($key) {
            case 'site_id':
                return (int) $this->site_id;
            case 'site_name':
                return $this->site_name;
            case 'site_domain':
                return $this->site_domain;
            case 'site_path':
                return $this->site_path;
            case 'site_status':
            default:
                $details = $this->get_details();
                if (isset($details->$key)) {
                    return $details->$key;
                }
        }

        return null;
    }

    /**
     * Isset-er.
     *
     * Allows current multisite naming conventions when checking for properties.
     * Checks for extended site properties.
     *
     * @since 0.9
     * @param string $key Property to check if set.
     * @return bool Whether the property is set.
     */
    public function __isset($key)
    {
        switch ($key) {
            case 'site_id':
                return (int) $this->site_id;
            case 'site_name':
                return $this->site_name;
            case 'site_domain':
                return $this->site_domain;
            case 'site_path':
                return $this->site_path;
            case 'site_status':
            default:
                $details = $this->get_details();
                if (isset($details->$key)) {
                    return true;
                }
        }

        return false;
    }

    /**
     * Setter.
     *
     * Allows current multisite naming conventions while setting properties.
     *
     * @since 0.9
     * @param string $key   Property to set.
     * @param mixed  $value Value to assign to the property.
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case 'site_id':
                $this->site_id = (int) $value;
                break;
            default:
                $this->$key = $value;
        }
    }

    /**
     * Retrieves the details for this site.
     *
     * This method is used internally to lazy-load the extended properties of a site.
     *
     * @since 0.9
     * @see Site::__get()
     * @return stdClass A raw site object with all details included.
     */
    private function get_details()
    {
        $details = func\ttcms_cache_get($this->site_id, 'site-details');

        if (false === $details) {

            foreach (get_object_vars($this) as $key => $value) {
                $details->$key = $value;
            }

            func\ttcms_cache_set($this->site_id, $details, 'site-details');
        }

        /**
         * Filters a site's extended properties.
         *
         * @since 0.9
         * @param array $details The site details.
         */
        $details = app()->hook->{'apply_filter'}('site_details', $details);

        return $details;
    }

}
