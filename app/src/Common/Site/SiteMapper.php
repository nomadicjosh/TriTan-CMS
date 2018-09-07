<?php
namespace TriTan\Common\Site;

use TriTan\Interfaces\Site\SiteMapperInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Exception\Exception;
use TriTan\Exception\InvalidArgumentException;
use TriTan\Common\Site\Site;
use Cascade\Cascade;

class SiteMapper implements SiteMapperInterface
{
    public $db;
    
    public $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }

    /**
     * Fetch a site object by ID
     *
     * @since 0.9.9
     * @param string $id
     * @return TriTan\Common\Site\Site|null Returns site object if exist and NULL otherwise.
     */
    public function findById($id)
    {
        if (!is_integer($id) || (int) $id < 1) {
            throw new InvalidArgumentException(
                'The ID of this entity is invalid.',
                'invalid_id'
            );
        }

        $site = $this->findBy('id', $id);

        return $site;
    }

    /**
     * Return only the main site fields.
     *
     * @since 0.9.9
     * @param string $field The field to query against: 'id', 'ID', 'email' or 'login'.
     * @param string|int $value The field value
     * @return object|false Raw site object
     */
    public function findBy($field, $value)
    {

        // 'ID' is an alias of 'id'.
        if ('ID' === $field) {
            $field = 'id';
        }

        if ('id' == $field) {
            // Make sure the value is numeric to avoid casting objects, for example,
            // to int 1.
            if (!is_numeric($value)) {
                return false;
            }
            $value = intval($value);
            if ($value < 1) {
                return false;
            }
        } else {
            $value = $this->context->obj['util']->{'trim'}($value);
        }

        if (!$value) {
            return false;
        }

        switch ($field) {
            case 'id':
                $site_id = (int) $value;
                $db_field = 'site_id';
                break;
            case 'slug':
                $site_id = $this->context->obj['cache']->{'read'}($value, 'siteslugs');
                $db_field = 'site_slug';
                break;
            case 'domain':
                $value = $this->context->obj['sanitizer']->{'item'}($value, '', '');
                $site_id = $this->context->obj['cache']->{'read'}($value, 'sitedomains');
                $db_field = 'site_domain';
                break;
            case 'path':
                $value = $this->context->obj['sanitizer']->{'item'}($value, '', '');
                $site_id = $this->context->obj['cache']->{'read'}($value, 'sitepaths');
                $db_field = 'site_path';
                break;
            default:
                return false;
        }

        $site = null;

        if (false !== $site_id) {
            if ($data = $this->context->obj['cache']->{'read'}($site_id, 'sites')) {
                is_array($data) ? $this->context->obj['util']->{'toObject'}($data) : $data;
            }
        }

        if (!$data = $this->db->table('site')->where($db_field, sprintf('%s', $value))->first()) {
            return false;
        }

        if ($data != null) {
            $site = $this->create($data);
            $this->context->obj['sitecache']->{'update'}($site);
        }

        if (is_array($site)) {
            $site = $this->context->obj['util']->{'toObject'}($site);
        }

        return $site;
    }
    
    public function findAll()
    {
        $data = $this->db->table('site')->all();
        $sites = [];
        if($data != null) {
            foreach($data as $site) {
                $sites[] = $this->create($site);
            }
        }
        return $sites;
    }

    /**
     * Create a new instance of Site. Optionally populating it
     * from a data array.
     *
     * @param array $data
     * @return TriTan\Common\Site\Site.
     */
    public function create(array $data = null) : Site
    {
        $site = $this->__create();
        if ($data) {
            $site = $this->populate($site, $data);
        }
        return $site;
    }

    /**
     * Populate the Site object with the data array.
     *
     * @param Site $site object.
     * @param array $data Site data.
     * @return TriTan\Common\Site\Site
     */
    public function populate(Site $site, array $data) : Site
    {
        $site->setId((int) $data['site_id']);
        $site->setName((string) $data['site_name']);
        $site->setSlug((string) $data['site_slug']);
        $site->setDomain((string) $data['site_domain']);
        $site->setPath((string) $data['site_path']);
        $site->setOwner((int) $data['site_owner']);
        $site->setStatus((string) $data['site_status']);
        $site->setRegistered((string) $data['site_registered']);
        $site->setModified((string) $data['site_modified']);
        return $site;
    }

    /**
     * Create a new Site object.
     *
     * @return TriTan\Common\Site\Site
     */
    protected function __create() : Site
    {
        return new Site();
    }

    /**
     * Inserts a new site into the site document.
     *
     * @since 0.9.9
     * @param Site $site Site object.
     * @return int Last insert id.
     */
    public function insert(Site $site)
    {
        $sql = $this->db->table('site');
        $sql->begin();
        try {
            $sql->insert([
                'site_name' => $this->db->{'ifNull'}($site->getName()),
                'site_slug' => $this->db->{'ifNull'}($site->getSlug()),
                'site_domain' => $this->db->{'ifNull'}($site->getDomain()),
                'site_path' => $this->db->{'ifNull'}($site->getPath()),
                'site_owner' => (int) $site->getOwner(),
                'site_status' => $this->db->{'ifNull'}($site->getStatus()),
                'site_registered' => (string) $site->getRegistered(),
            ]);
            $sql->commit();

            return (int) $sql->lastInsertId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('SITEMAPPER[insert]: %s', $ex->getMessage()));
        }
    }

    /**
     * Updates a Site object.
     *
     * @since 0.9.9
     * @param Site $site Site object.
     * @return Site's id.
     */
    public function update(Site $site)
    {
        $sql = $this->db->table('site');
        $sql->begin();
        try {
            $sql->where('site_id', (int) $site->getId())
                ->update([
                    'site_name' => $this->db->{'ifNull'}($site->getName()),
                    'site_slug' => $this->db->{'ifNull'}($site->getSlug()),
                    'site_domain' => $this->db->{'ifNull'}($site->getDomain()),
                    'site_path' => $this->db->{'ifNull'}($site->getPath()),
                    'site_owner' => (int) $site->getOwner(),
                    'site_status' => $this->db->{'ifNull'}($site->getStatus()),
                    'site_registered' => (string) $site->getRegistered(),
                    'site_modified' => (string) $site->getModified()
                ]);
            $sql->commit();
            return (int) $site->getId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('SITEMAPPER[update]: %s', $ex->getMessage()));
        }
    }

    /**
     * Save the Site object.
     *
     * @since 0.9.9
     * @param Site $site Site object.
     */
    public function save(Site $site)
    {
        if (is_null($site->getId())) {
            $this->insert($site);
        } else {
            $this->update($site);
        }
    }

    /**
     * Deletes site object.
     *
     * @since 0.9.9
     * @param Site $site Site object.
     * @return bool True if deleted, false otherwise.
     */
    public function delete(Site $site)
    {
        $sql = $this->db->table('site');
        $sql->begin();
        try {
            $sql->where('site_id', $site->getId())
                ->delete();
            $sql->commit();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('SITEMAPPER[delete]: %s', $ex->getMessage()));
        }
    }
}
