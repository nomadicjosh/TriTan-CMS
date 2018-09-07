<?php
namespace TriTan\Interfaces\Posttype;

interface PosttypeInterface
{
    public function getId(): int;

    public function setId($id);

    public function getTitle();

    public function setTitle($title);

    public function getSlug();

    public function setSlug($slug);

    public function getDescription();

    public function setDescription($description);
}
