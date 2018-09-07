<?php
namespace TriTan\Interfaces\Site;

interface SiteInterface
{
    public function getId(): int;

    public function setId(int $id);

    public function getName();

    public function setName(string $name);

    public function getSlug();

    public function setSlug(string $slug);

    public function getDomain();

    public function setDomain(string $domain);

    public function getPath();

    public function setPath(string $path);

    public function getOwner(): int;

    public function setOwner(int $owner);

    public function getStatus();

    public function setStatus(string $status);

    public function getRegistered();

    public function setRegistered(string $registered);

    public function getModified();

    public function setModified(string $modified);
}
