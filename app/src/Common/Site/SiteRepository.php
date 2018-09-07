<?php
namespace TriTan\Common\Site;

use TriTan\Interfaces\Site\SiteRepositoryInterface;
use TriTan\Interfaces\Site\SiteMapperInterface;
use TriTan\Common\Site\Site;

class SiteRepository implements SiteRepositoryInterface
{
    /**
     * Site Mapper Object
     *
     * @var object
     */
    private $mapper;

    public function __construct(SiteMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    public function findById($id)
    {
        return $this->mapper->{'findById'}($id);
    }
    
    public function findBy($field, $value)
    {
        return $this->mapper->{'findBy'}($field, $value);
    }
    
    public function findAll()
    {
        return $this->mapper->{'findAll'}();
    }

    public function insert(Site $site)
    {
        return $this->mapper->{'insert'}($site);
    }

    public function update(Site $site)
    {
        return $this->mapper->{'update'}($site);
    }

    public function save(Site $site)
    {
        $this->mapper->{'save'}($site);
    }

    public function delete(Site $site)
    {
        return $this->mapper->{'delete'}($site);
    }
}
