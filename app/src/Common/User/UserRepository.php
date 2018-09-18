<?php
namespace TriTan\Common\User;

use TriTan\Interfaces\User\UserRepositoryInterface;
use TriTan\Interfaces\User\UserMapperInterface;
use TriTan\Common\User\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * User Mapper Object
     *
     * @var object
     */
    private $mapper;

    public function __construct(UserMapperInterface $mapper)
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
    
    public function findAll()
    {
        return $this->mapper->{'findAll'}();
    }

    public function insert(User $user)
    {
        return $this->mapper->{'insert'}($user);
    }

    public function update(User $user)
    {
        return $this->mapper->{'update'}($user);
    }

    public function save(User $user)
    {
        $this->mapper->{'save'}($user);
    }

    public function delete(User $user)
    {
        return $this->mapper->{'delete'}($user);
    }
}
