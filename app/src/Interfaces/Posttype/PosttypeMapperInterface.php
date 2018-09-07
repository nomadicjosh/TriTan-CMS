<?php
namespace TriTan\Interfaces\Posttype;

use TriTan\Common\Posttype\Posttype;

interface PosttypeMapperInterface
{
    public function findById($id);
    public function findAll();
    public function insert(Posttype $posttype);
    public function update(Posttype $posttype);
    public function delete(Posttype $posttype);
}
