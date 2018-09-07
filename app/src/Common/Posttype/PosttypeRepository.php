<?php
namespace TriTan\Common\Posttype;

use TriTan\Interfaces\Posttype\PosttypeRepositoryInterface;
use TriTan\Interfaces\Posttype\PosttypeMapperInterface;
use TriTan\Common\Posttype\Posttype;

class PosttypeRepository implements PosttypeRepositoryInterface
{
    /**
     * Posttype Mapper Object
     *
     * @var object
     */
    private $mapper;

    public function __construct(PosttypeMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    public function findById($id)
    {
        return $this->mapper->{'findById'}($id);
    }
    
    public function findAll()
    {
        return $this->mapper->{'findAll'}();
    }

    public function insert(Posttype $posttype)
    {
        return $this->mapper->{'insert'}($posttype);
    }

    public function update(Posttype $posttype)
    {
        return $this->mapper->{'update'}($posttype);
    }

    public function save(Posttype $posttype)
    {
        $this->mapper->{'save'}($posttype);
    }

    public function delete(Posttype $posttype)
    {
        return $this->mapper->{'delete'}($posttype);
    }
}
