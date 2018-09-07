<?php
namespace TriTan\Interfaces\Post;

use TriTan\Common\Post\Post;

interface PostMapperInterface
{
    public function findById(int $id);
    public function findBy(string $field, $value);
    public function findByType(string $type);
    public function findAll();
    public function insert(Post $post);
    public function update(Post $post);
    public function delete(Post $post);
}
