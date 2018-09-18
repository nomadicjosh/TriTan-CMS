<?php
namespace TriTan\Common\Post;

use TriTan\Interfaces\Post\PostRepositoryInterface;
use TriTan\Interfaces\Post\PostMapperInterface;
use TriTan\Common\Post\Post;

class PostRepository implements PostRepositoryInterface
{
    /**
     * Post Mapper Object
     *
     * @var object
     */
    private $mapper;

    public function __construct(PostMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    public function findById(int $id)
    {
        return $this->mapper->{'findById'}($id);
    }
    
    public function findBy(string $field, $value)
    {
        return $this->mapper->{'findBy'}($field, $value);
    }
    
    public function findByType(string $type)
    {
        return $this->mapper->findByType($type);
    }


    public function findAll()
    {
        return $this->mapper->{'findAll'}();
    }

    public function insert(Post $post)
    {
        return $this->mapper->{'insert'}($post);
    }

    public function update(Post $post)
    {
        return $this->mapper->{'update'}($post);
    }

    public function save(Post $post)
    {
        $this->mapper->{'save'}($post);
    }

    public function delete(Post $post)
    {
        return $this->mapper->{'delete'}($post);
    }
}
