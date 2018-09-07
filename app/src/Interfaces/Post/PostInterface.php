<?php
namespace TriTan\Interfaces\Post;

interface PostInterface
{
    public function getId(): int;

    public function setId(int $id);

    public function getTitle();

    public function setTitle(string $title);

    public function getSlug();

    public function setSlug(string $slug);

    public function getContent();

    public function setContent(string $content);

    public function getAuthor(): int;

    public function setAuthor(int $author);

    public function getPosttypeId(): int;

    public function setPosttypeId(int $id);

    public function getPosttype();

    public function setPosttype(string $posttype);

    public function getParentId();

    public function setParentId(int $id);

    public function getParent();

    public function setParent(string $parent);

    public function getSidebar(): int;

    public function setSidebar(int $sidebar);

    public function getShowInMenu(): int;

    public function setShowInMenu(int $menu);

    public function getShowInSearch(): int;

    public function setShowInSearch(int $search);

    public function getRelativeUrl();

    public function setRelativeUrl(string $url);

    public function getFeaturedImage();

    public function setFeaturedImage(string $image);

    public function getStatus();

    public function setStatus(string $status);

    public function getCreated();

    public function setCreated(string $created);

    public function getPublished();

    public function setPublished(string $published);

    public function getModified();

    public function setModified(string $modified);
}
