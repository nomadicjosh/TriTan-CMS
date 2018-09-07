<?php
namespace TriTan\Interfaces\Post;

use TriTan\Common\Post\Post;

interface PostRepositoryInterface
{
    public function findById(int $id);
    public function findBy(string $field, $value);
    public function findByType(string $type);
    public function findAll();
    public function insert(Post $user);
    public function update(Post $user);
    public function delete(Post $user);
}