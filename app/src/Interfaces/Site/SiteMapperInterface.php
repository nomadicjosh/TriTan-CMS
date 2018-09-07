<?php
namespace TriTan\Interfaces\Site;

use TriTan\Common\Site\Site;

interface SiteMapperInterface
{
    public function findById($id);
    public function findBy($field, $value);
    public function findAll();
    public function insert(Site $post);
    public function update(Site $post);
    public function delete(Site $post);
}