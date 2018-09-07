<?php
namespace TriTan\Common\Site;

use TriTan\Interfaces\Site\SiteInterface;
use TriTan\Container as c;

/**
 * Site Domain
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Site implements SiteInterface
{
    /**
     * Site site_id.
     *
     * @since 0.9.9
     * @var int
     */
    private $site_id;

    /**
     * Site name.
     *
     * @since 0.9.9
     * @var string
     */
    private $site_name;

    /**
     * Site slug.
     *
     * @since 0.9.9
     * @var string
     */
    private $site_slug;

    /**
     * Site domain.
     *
     * @since 0.9.9
     * @var string
     */
    private $site_domain;

    /**
     * Site path.
     *
     * @since 0.9.9
     * @var string
     */
    private $site_path;

    /**
     * Site owner.
     *
     * @since 0.9.9
     * @var int
     */
    private $site_owner;

    /**
     * Site status.
     *
     * @since 0.9.9
     * @var string
     */
    private $site_status;

    /**
     * Site registered.
     *
     * @since 0.9.9
     * @var string
     */
    private $site_registered;

    /**
     * Site modified.
     *
     * @since 0.9.9
     * @var int
     */
    private $site_modified;

    public function getId(): int
    {
        return $this->site_id;
    }

    public function setId(int $id)
    {
        return $this->site_id = $id;
    }

    public function getName()
    {
        return $this->site_name;
    }

    public function setName(string $name)
    {
        return $this->site_name = $name;
    }

    public function getSlug()
    {
        return $this->site_slug;
    }

    public function setSlug(string $slug)
    {
        return $this->site_slug = $slug;
    }

    public function getDomain()
    {
        return $this->site_domain;
    }

    public function setDomain(string $domain)
    {
        return $this->site_domain = $domain;
    }

    public function getPath()
    {
        return $this->site_path;
    }

    public function setPath(string $path)
    {
        return $this->site_path = $path;
    }

    public function getOwner(): int
    {
        return $this->site_owner;
    }

    public function setOwner(int $owner)
    {
        return $this->site_owner = $owner;
    }

    public function getStatus()
    {
        return $this->site_status;
    }

    public function setStatus(string $status)
    {
        return $this->site_status = $status;
    }

    public function getRegistered()
    {
        return $this->site_registered;
    }

    public function setRegistered(string $registered)
    {
        return $this->site_registered = $registered;
    }

    public function getModified()
    {
        return $this->site_modified;
    }

    public function setModified(string $modified)
    {
        return $this->site_modified = $modified;
    }

    /**
     * Magic method for checking the existence of property.
     *
     * @since 0.9.9
     * @param string $key Site property to check if set.
     * @return bool Whether the given property is set.
     */
    public function __isset($key)
    {
        switch ($key) {
            case 'site_id':
                return (int) $this->site_id;
            case 'site_name':
                return $this->site_name;
            case 'site_slug':
                return $this->site_slug;
            case 'site_domain':
                return $this->site_domain;
            case 'site_path':
                return $this->site_path;
            case 'site_status':
                return $this->site_status;
            case 'site_registered':
                return $this->site_registered;
            case 'site_owner':
                return $this->site_owner;
            default:
                $details = $this->details();
                if (isset($details->$key)) {
                    return true;
                }
        }

        return false;
    }

    /**
     * Magic method for accessing properties.
     *
     * @since 0.9.9
     * @param string $key Site property to retrieve.
     * @return mixed Value of the given property (if set). If `$key` is 'site_id', the site ID.
     */
    public function __get($key)
    {
        switch ($key) {
            case 'site_id':
                return (int) $this->site_id;
            case 'site_name':
                return $this->site_name;
            case 'site_slug':
                return $this->site_slug;
            case 'site_domain':
                return $this->site_domain;
            case 'site_path':
                return $this->site_path;
            case 'site_status':
                return $this->site_status;
            case 'site_registered':
                return $this->site_registered;
            case 'site_owner':
                return $this->site_owner;
            default:
                $details = $this->details();
                if (isset($details->{$key})) {
                    return $details->{$key};
                }
        }

        return null;
    }

    /**
     * Determine whether the site exists in the database.
     *
     * @since 0.9.9
     * @return bool True if site exists in the database, false if not.
     */
    public function exists()
    {
        return !empty($this->site_id);
    }

    /**
     * Retrieve the value of a property.
     *
     * @since 0.9.9
     * @param string $key Property
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * Determine whether a property is set.
     *
     * @since 0.9.9
     * @param string $key Property
     * @return bool
     */
    public function hasProp($key)
    {
        return $this->__isset($key);
    }

    /**
     * Return an array representation.
     *
     * @since 0.9.9
     * @return array Array representation.
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Retrieves the details for this site.
     *
     * @since 0.9.9
     * @see Site::__get()
     * @return object Site details.
     */
    private function details()
    {
        $details = c::getInstance()->get('context')->obj['cache']->{'read'}($this->getId(), 'site_details');

        if (false === $details) {
            $details = new \stdClass();
            foreach (get_object_vars($this) as $key => $value) {
                $details->{$key} = $value;
            }

            c::getInstance()->get('context')->obj['cache']->{'set'}($this->getId(), $details, 'site_details');
        }

        /**
         * Filters a site's details.
         *
         * @since 0.9.9
         * @param object $details The site details.
         */
        $details = c::getInstance()->get('context')->obj['hook']->{'applyFilter'}('site_details', $details);

        return $details;
    }
}
